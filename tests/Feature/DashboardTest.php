<?php

use App\Models\Role;
use App\Models\User;
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
