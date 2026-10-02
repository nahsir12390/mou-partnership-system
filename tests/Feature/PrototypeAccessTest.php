<?php

use App\Models\Role;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(DatabaseSeeder::class);
});

function prototypeUser(string $role): User
{
    return User::whereHas('role', fn ($query) => $query->where('slug', $role))->firstOrFail();
}

test('guests are redirected away from the dashboard', function () {
    $this->get(route('dashboard'))->assertRedirect(route('login'));
});

test('public registration is disabled', function () {
    $this->get('/register')->assertNotFound();
});

test('administrator can access core management screens', function () {
    $user = prototypeUser('system-administrator');

    $this->actingAs($user)->get(route('dashboard'))->assertOk();
    $this->actingAs($user)->get(route('partners.index'))->assertOk();
    $this->actingAs($user)->get(route('partners.create'))->assertOk();
    $this->actingAs($user)->get(route('agreements.index'))->assertOk();
    $this->actingAs($user)->get(route('agreements.create'))->assertOk();
});

test('read only user can view registries but cannot create records', function () {
    $user = prototypeUser('read-only-user');

    $this->actingAs($user)->get(route('dashboard'))->assertOk();
    $this->actingAs($user)->get(route('partners.index'))->assertOk();
    $this->actingAs($user)->get(route('agreements.index'))->assertOk();
    $this->actingAs($user)->get(route('partners.create'))->assertForbidden();
    $this->actingAs($user)->get(route('agreements.create'))->assertForbidden();
});

test('inactive users have no application permissions', function () {
    $user = prototypeUser('system-administrator');
    $user->update(['is_active' => false]);

    expect($user->fresh()->hasPermission('partners.view'))->toBeFalse()
        ->and($user->fresh()->hasPermission('agreements.create'))->toBeFalse();
});
