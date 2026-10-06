<?php

use App\Models\Agreement;
use App\Models\Department;
use App\Models\Partner;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);
beforeEach(function () {
    $this->seed(DatabaseSeeder::class);
});
function workflowUser(string $slug): User
{
    return User::whereHas('role', fn ($query) => $query->where('slug', $slug))->firstOrFail();
}
function workflowAgreement(string $status = 'draft', array $overrides = []): Agreement
{
    $admin = workflowUser('system-administrator');

    return Agreement::create(array_merge(['reference_number' => 'MOU/TEST/'.fake()->unique()->numberBetween(100, 999), 'title' => 'Research Collaboration Memorandum', 'partner_id' => Partner::firstOrFail()->id, 'department_id' => Department::where('code', 'RAP')->firstOrFail()->id, 'responsible_officer_id' => workflowUser('department-officer')->id, 'agreement_type' => 'Memorandum of Understanding', 'purpose' => 'Prototype approval workflow test.', 'status' => $status, 'renewal_status' => 'not_due', 'created_by' => $admin->id], $overrides));
}

test('authorized officer can submit a draft for review and audit action is recorded', function () {
    $agreement = workflowAgreement();
    $officer = workflowUser('department-officer');
    $this->actingAs($officer)->post(route('agreements.submit', $agreement))->assertRedirect();
    $agreement->refresh();
    expect($agreement->status)->toBe('submitted')->and($agreement->approval_stage)->toBe('Awaiting legal review')->and($agreement->approvalActions()->count())->toBe(1);
    $action = $agreement->approvalActions()->first();
    expect($action->action)->toBe('submitted')->and($action->from_status)->toBe('draft')->and($action->to_status)->toBe('submitted')->and($action->user_id)->toBe($officer->id);
});

test('agreement passes through legal review before management approval', function () {
    $agreement = workflowAgreement('submitted');
    $reviewer = workflowUser('legal-review-officer');
    $this->actingAs($reviewer)->post(route('agreements.review', $agreement), ['decision' => 'start_review', 'comment' => 'Legal review commenced.'])->assertRedirect();
    expect($agreement->fresh()->status)->toBe('under_review')->and($agreement->fresh()->approval_stage)->toBe('Legal review');

    $this->actingAs($reviewer)->post(route('agreements.review', $agreement->fresh()), ['decision' => 'legal_clear', 'comment' => 'Terms are legally acceptable.'])->assertRedirect();
    expect($agreement->fresh()->status)->toBe('under_review')->and($agreement->fresh()->approval_stage)->toBe('Management approval');

    $this->actingAs(workflowUser('management'))->post(route('agreements.review', $agreement->fresh()), ['decision' => 'approve', 'comment' => 'Management approval granted.'])->assertRedirect();
    expect($agreement->fresh()->status)->toBe('approved')->and($agreement->fresh()->approvalActions()->count())->toBe(3);
});

test('review decisions are restricted to the assigned institutional stage', function () {
    $legalAgreement = workflowAgreement('under_review', ['approval_stage' => 'Legal review']);
    $managementAgreement = workflowAgreement('under_review', ['approval_stage' => 'Management approval']);

    $this->actingAs(workflowUser('management'))
        ->post(route('agreements.review', $legalAgreement), ['decision' => 'approve'])
        ->assertForbidden();

    $this->actingAs(workflowUser('legal-review-officer'))
        ->post(route('agreements.review', $managementAgreement), ['decision' => 'legal_clear'])
        ->assertForbidden();
});

test('requesting changes requires a reason and returns agreement to draft', function () {
    $agreement = workflowAgreement('under_review', ['approval_stage' => 'Legal review']);
    $reviewer = workflowUser('legal-review-officer');
    $this->actingAs($reviewer)->post(route('agreements.review', $agreement), ['decision' => 'request_changes'])->assertSessionHasErrors('comment');
    expect($agreement->fresh()->status)->toBe('under_review');
    $this->actingAs($reviewer)->post(route('agreements.review', $agreement), ['decision' => 'request_changes', 'comment' => 'Clarify the reporting obligations.'])->assertRedirect();
    expect($agreement->fresh()->status)->toBe('draft')->and($agreement->fresh()->approval_stage)->toBe('Changes requested');
});

test('read only user cannot submit or review agreements', function () {
    $agreement = workflowAgreement();
    $viewer = workflowUser('read-only-user');
    $this->actingAs($viewer)->post(route('agreements.submit', $agreement))->assertForbidden();
    $agreement->update(['status' => 'submitted']);
    $this->actingAs($viewer)->post(route('agreements.review', $agreement), ['decision' => 'approve'])->assertForbidden();
});

test('agreement cannot be approved from an invalid workflow stage', function () {
    $agreement = workflowAgreement('draft');
    $reviewer = workflowUser('legal-review-officer');
    $this->actingAs($reviewer)->post(route('agreements.review', $agreement), ['decision' => 'approve'])->assertStatus(422);
    expect($agreement->fresh()->status)->toBe('draft')->and($agreement->approvalActions()->count())->toBe(0);
});

test('submitted agreement must enter review before a decision can be made', function () {
    $agreement = workflowAgreement('submitted');

    $this->actingAs(workflowUser('legal-review-officer'))
        ->post(route('agreements.review', $agreement), ['decision' => 'approve'])
        ->assertStatus(422);

    expect($agreement->fresh()->status)->toBe('submitted')
        ->and($agreement->approvalActions()->count())->toBe(0);
});

test('agreement creation always starts as a draft regardless of submitted workflow fields', function () {
    $admin = workflowUser('system-administrator');

    $this->actingAs($admin)->post(route('agreements.store'), [
        'reference_number' => 'MOU/PROCESS/001',
        'title' => 'Controlled Process Agreement',
        'partner_id' => Partner::firstOrFail()->id,
        'department_id' => Department::firstOrFail()->id,
        'responsible_officer_id' => $admin->id,
        'agreement_type' => 'MoU',
        'status' => 'terminated',
        'approval_stage' => 'Approved',
        'renewal_status' => 'renewed',
    ])->assertRedirect();

    $agreement = Agreement::where('reference_number', 'MOU/PROCESS/001')->firstOrFail();
    expect($agreement->status)->toBe('draft')
        ->and($agreement->approval_stage)->toBeNull()
        ->and($agreement->renewal_status)->toBe('not_due');
});

test('department officer cannot submit agreement belonging to another department', function () {
    $officer = workflowUser('department-officer');
    $other = Department::whereKeyNot($officer->department_id)->first() ?? Department::create(['name' => 'External Department', 'code' => 'ED', 'is_active' => true]);
    $agreement = workflowAgreement('draft', ['department_id' => $other->id, 'responsible_officer_id' => workflowUser('system-administrator')->id]);
    $this->actingAs($officer)->post(route('agreements.submit', $agreement))->assertForbidden();
    expect($agreement->fresh()->status)->toBe('draft')->and($agreement->approvalActions()->count())->toBe(0);
});

test('institution wide reviewer can review agreement from any department', function () {
    $officer = workflowUser('department-officer');
    $other = Department::whereKeyNot($officer->department_id)->first() ?? Department::create(['name' => 'Review Department', 'code' => 'RD', 'is_active' => true]);
    $agreement = workflowAgreement('submitted', ['department_id' => $other->id]);
    $this->actingAs(workflowUser('legal-review-officer'))->post(route('agreements.review', $agreement), ['decision' => 'start_review'])->assertRedirect();
    expect($agreement->fresh()->status)->toBe('under_review');
});
