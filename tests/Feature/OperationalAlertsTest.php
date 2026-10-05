<?php

use App\Models\Agreement;
use App\Models\Department;
use App\Models\Obligation;
use App\Models\Partner;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);
beforeEach(function () {
    $this->seed(DatabaseSeeder::class);
});
function alertUser(string $slug): User
{
    return User::whereHas('role', fn ($q) => $q->where('slug', $slug))->firstOrFail();
}
function alertAgreement(array $overrides = []): Agreement
{
    return Agreement::create(array_merge(['reference_number' => 'MOU/ALERT/'.fake()->unique()->numberBetween(100, 999), 'title' => 'Alert Test Agreement', 'partner_id' => Partner::firstOrFail()->id, 'department_id' => Department::firstOrFail()->id, 'responsible_officer_id' => alertUser('department-officer')->id, 'agreement_type' => 'Memorandum of Understanding', 'status' => 'active', 'renewal_status' => 'not_due', 'created_by' => alertUser('system-administrator')->id], $overrides));
}

test('authorized user can open operational alerts center', function () {
    $this->actingAs(alertUser('management'))->get(route('alerts.index'))->assertOk()->assertSee('Operational Alerts')->assertSee('Renewal & Expiry Watch', false);
});

test('alerts surface overdue obligations and upcoming expiries', function () {
    $agreement = alertAgreement(['expiry_date' => today()->addDays(20)]);
    Obligation::create(['agreement_id' => $agreement->id, 'title' => 'Submit quarterly implementation report', 'type' => 'deliverable', 'department_id' => $agreement->department_id, 'responsible_officer_id' => $agreement->responsible_officer_id, 'due_date' => today()->subDays(5), 'priority' => 'high', 'status' => 'in_progress', 'progress' => 50, 'created_by' => alertUser('system-administrator')->id]);
    $this->actingAs(alertUser('management'))->get(route('alerts.index'))->assertOk()->assertSee('Submit quarterly implementation report')->assertSee('Alert Test Agreement');
});

test('department user only sees alerts from their department', function () {
    $officer = alertUser('department-officer');
    $own = alertAgreement(['title' => 'Visible Department Agreement', 'department_id' => $officer->department_id, 'expiry_date' => today()->addDays(15)]);
    $otherDepartment = Department::whereKeyNot($officer->department_id)->first();
    if (! $otherDepartment) {
        $otherDepartment = Department::create(['name' => 'Separate Unit', 'code' => 'SU', 'is_active' => true]);
    }
    alertAgreement(['title' => 'Hidden Other Department Agreement', 'department_id' => $otherDepartment->id, 'responsible_officer_id' => alertUser('system-administrator')->id, 'expiry_date' => today()->addDays(15)]);
    $this->actingAs($officer)->get(route('alerts.index'))->assertOk()->assertSee($own->title)->assertDontSee('Hidden Other Department Agreement');
});

test('department alert scope follows agreement access when obligation assignment is empty', function () {
    $officer = alertUser('department-officer');
    $otherDepartment = Department::whereKeyNot($officer->department_id)->first()
        ?? Department::create(['name' => 'Private Unit', 'code' => 'PU', 'is_active' => true]);
    $agreement = alertAgreement([
        'title' => 'Hidden Alert Agreement',
        'department_id' => $otherDepartment->id,
        'responsible_officer_id' => alertUser('system-administrator')->id,
    ]);
    Obligation::create([
        'agreement_id' => $agreement->id,
        'title' => 'Hidden unassigned overdue task',
        'type' => 'obligation',
        'due_date' => today()->subDay(),
        'priority' => 'high',
        'status' => 'in_progress',
        'progress' => 10,
        'created_by' => alertUser('system-administrator')->id,
    ]);

    $this->actingAs($officer)->get(route('alerts.index'))
        ->assertOk()
        ->assertDontSee('Hidden unassigned overdue task');
});
