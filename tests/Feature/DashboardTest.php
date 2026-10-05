<?php

use App\Models\Agreement;
use App\Models\Department;
use App\Models\Partner;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('guests are redirected to the login page', function () {
    $this->get(route('dashboard'))->assertRedirect(route('login'));
});

test('authorized authenticated users can visit the dashboard', function () {
    $role = Role::create([
        'name' => 'Read-only User',
        'slug' => 'read-only-user',
        'description' => 'Can view authorized partnership records without modifying them.',
        'is_system' => true,
    ]);

    $user = User::factory()->create([
        'role_id' => $role->id,
        'is_active' => true,
        'email_verified_at' => now(),
    ]);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk();
});

test('authenticated users without an assigned role cannot visit the dashboard', function () {
    $user = User::factory()->create([
        'role_id' => null,
        'is_active' => true,
        'email_verified_at' => now(),
    ]);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertForbidden();
});

test('department dashboard does not expose another departments agreements or partners', function () {
    $this->seed(DatabaseSeeder::class);
    $officer = User::whereHas('role', fn ($query) => $query->where('slug', 'department-officer'))->firstOrFail();
    $admin = User::whereHas('role', fn ($query) => $query->where('slug', 'system-administrator'))->firstOrFail();
    $otherDepartment = Department::whereKeyNot($officer->department_id)->first()
        ?? Department::create(['name' => 'Confidential Unit', 'code' => 'CU', 'is_active' => true]);
    $partner = Partner::create(['name' => 'Hidden Dashboard Partner', 'status' => 'active', 'created_by' => $admin->id]);
    Agreement::create([
        'reference_number' => 'MOU/DASHBOARD/HIDDEN',
        'title' => 'Hidden Dashboard Agreement',
        'partner_id' => $partner->id,
        'department_id' => $otherDepartment->id,
        'responsible_officer_id' => $admin->id,
        'agreement_type' => 'Memorandum of Understanding',
        'status' => 'active',
        'renewal_status' => 'not_due',
        'created_by' => $admin->id,
    ]);

    $this->actingAs($officer)->get(route('dashboard'))
        ->assertOk()
        ->assertDontSee('Hidden Dashboard Agreement')
        ->assertDontSee('Hidden Dashboard Partner');
});
