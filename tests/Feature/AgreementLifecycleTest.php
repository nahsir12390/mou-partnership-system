<?php

use App\Models\ActivityLog;
use App\Models\Agreement;
use App\Models\Document;
use App\Models\Partner;
use App\Models\RenewalRecord;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);
beforeEach(function () {
    $this->seed(DatabaseSeeder::class);
});

function lifecycleUser(string $slug): User
{
    return User::whereHas('role', fn ($query) => $query->where('slug', $slug))->firstOrFail();
}
function completionAgreement(string $status): Agreement
{
    $officer = lifecycleUser('department-officer');

    return Agreement::create([
        'reference_number' => 'MOU/LIFE/'.fake()->unique()->numberBetween(100, 999),
        'title' => 'Lifecycle Test Agreement',
        'partner_id' => Partner::firstOrFail()->id,
        'department_id' => $officer->department_id,
        'responsible_officer_id' => $officer->id,
        'agreement_type' => 'MoU',
        'status' => $status,
        'renewal_status' => 'not_due',
        'created_by' => lifecycleUser('system-administrator')->id,
    ]);
}

test('approved agreement can advance to awaiting signature', function () {
    $agreement = completionAgreement('approved');
    $this->actingAs(lifecycleUser('legal-review-officer'))->patch(route('agreements.lifecycle', $agreement), ['action' => 'await_signature'])->assertRedirect();
    expect($agreement->fresh()->status)->toBe('awaiting_signature');
});

test('agreement activation requires a final signed copy', function () {
    $agreement = completionAgreement('awaiting_signature');
    $reviewer = lifecycleUser('legal-review-officer');
    $this->actingAs($reviewer)->patch(route('agreements.lifecycle', $agreement), ['action' => 'activate'])->assertStatus(422);

    Document::create(['agreement_id' => $agreement->id, 'title' => 'Signed MoU', 'type' => 'signed_mou', 'version' => 1, 'original_name' => 'signed.pdf', 'stored_name' => 'signed.pdf', 'path' => 'agreements/signed.pdf', 'mime_type' => 'application/pdf', 'size' => 100, 'is_final' => true, 'uploaded_by' => $reviewer->id]);
    $this->actingAs($reviewer)->patch(route('agreements.lifecycle', $agreement), ['action' => 'activate'])->assertRedirect();
    expect($agreement->fresh()->status)->toBe('active')->and($agreement->fresh()->start_date)->not->toBeNull();
});

test('renewal decision preserves expiry history and updates agreement', function () {
    $agreement = completionAgreement('active');
    $agreement->update(['expiry_date' => '2026-12-31']);

    $this->actingAs(lifecycleUser('management'))->post(route('renewals.store', $agreement), [
        'decision' => 'renewed',
        'decision_date' => '2026-10-05',
        'new_expiry_date' => '2027-12-31',
        'notes' => 'Renewed by management committee.',
    ])->assertRedirect(route('agreements.show', $agreement));

    expect(RenewalRecord::count())->toBe(1)
        ->and(RenewalRecord::firstOrFail()->previous_expiry_date->format('Y-m-d'))->toBe('2026-12-31')
        ->and($agreement->fresh()->expiry_date->format('Y-m-d'))->toBe('2027-12-31')
        ->and($agreement->fresh()->renewal_status)->toBe('renewed');
});

test('model edits are retained in permanent audit history', function () {
    $agreement = completionAgreement('draft');
    $this->actingAs(lifecycleUser('system-administrator'));
    $agreement->update(['title' => 'Updated Lifecycle Agreement']);

    $log = ActivityLog::where('subject_type', Agreement::class)->where('subject_id', $agreement->id)->where('event', 'updated')->latest()->firstOrFail();
    expect($log->actor_id)->toBe(lifecycleUser('system-administrator')->id)
        ->and($log->metadata['after']['title'])->toBe('Updated Lifecycle Agreement');
});
