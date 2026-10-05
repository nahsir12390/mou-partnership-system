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

function reportUser(string $slug): User
{
    return User::whereHas('role', fn ($q) => $q->where('slug', $slug))->firstOrFail();
}
function lifecycleAgreement(array $overrides = []): Agreement
{
    return Agreement::create(array_merge(['reference_number' => 'MOU/LIFE/'.fake()->unique()->numberBetween(100, 999), 'title' => 'Lifecycle Test', 'partner_id' => Partner::firstOrFail()->id, 'department_id' => Department::firstOrFail()->id, 'responsible_officer_id' => reportUser('department-officer')->id, 'agreement_type' => 'Memorandum of Understanding', 'status' => 'active', 'renewal_status' => 'not_due', 'created_by' => reportUser('system-administrator')->id], $overrides));
}

test('management can open live reports', function () {
    $this->actingAs(reportUser('management'))
        ->get(route('reports.index'))
        ->assertOk()
        ->assertSee('Management Reports')
        ->assertSee('Renewal & Expiry Forecast', false);
});

test('department officer cannot access management reports', function () {
    $this->actingAs(reportUser('department-officer'))->get(route('reports.index'))->assertForbidden();
});

test('lifecycle command marks expired active agreement as expired and renewal due', function () {
    $agreement = lifecycleAgreement(['expiry_date' => today()->subDay()]);
    $this->artisan('agreements:sync-lifecycle')->assertSuccessful();
    expect($agreement->fresh()->status)->toBe('expired')->and($agreement->fresh()->renewal_status)->toBe('due');
});

test('lifecycle command flags agreements expiring within ninety days', function () {
    $agreement = lifecycleAgreement(['expiry_date' => today()->addDays(30)]);
    $this->artisan('agreements:sync-lifecycle')->assertSuccessful();
    expect($agreement->fresh()->status)->toBe('active')->and($agreement->fresh()->renewal_status)->toBe('due_soon');
});

test('lifecycle command does not overwrite explicit not renewing decision', function () {
    $agreement = lifecycleAgreement(['expiry_date' => today()->subDay(), 'renewal_status' => 'not_renewing']);
    $this->artisan('agreements:sync-lifecycle')->assertSuccessful();
    expect($agreement->fresh()->renewal_status)->toBe('not_renewing');
});
