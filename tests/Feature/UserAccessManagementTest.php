<?php

use App\Models\Department;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);
beforeEach(function () {
    $this->seed(DatabaseSeeder::class);
});

function accessUser(string $slug): User
{
    return User::whereHas('role', fn ($q) => $q->where('slug', $slug))->firstOrFail();
}

test('administrator can open users and access workspace', function () {
    $this->actingAs(accessUser('system-administrator'))->get(route('users.index'))->assertOk()->assertSee('Users & Access');
});

test('ordinary officers cannot manage institutional users', function () {
    $this->actingAs(accessUser('department-officer'))->get(route('users.index'))->assertForbidden();
});

test('administrator can create a role controlled user account', function () {
    $admin = accessUser('system-administrator');
    $role = Role::where('slug', 'read-only-user')->firstOrFail();
    $department = Department::firstOrFail();
    $this->actingAs($admin)->post(route('users.store'), ['name' => 'New Monitoring User', 'email' => 'monitor@example.com', 'job_title' => 'Monitoring Officer', 'phone' => '08000000000', 'department_id' => $department->id, 'role_id' => $role->id, 'is_active' => 1, 'password' => 'password123', 'password_confirmation' => 'password123'])->assertRedirect(route('users.index'));
    $user = User::where('email', 'monitor@example.com')->firstOrFail();
    expect($user->role_id)->toBe($role->id)->and($user->is_active)->toBeTrue()->and($user->email_verified_at)->not->toBeNull();
});

test('administrator can suspend another user account', function () {
    $admin = accessUser('system-administrator');
    $target = accessUser('read-only-user');
    $this->actingAs($admin)->put(route('users.update', $target), ['name' => $target->name, 'email' => $target->email, 'job_title' => $target->job_title, 'phone' => $target->phone, 'department_id' => $target->department_id, 'role_id' => $target->role_id, 'is_active' => 0, 'password' => '', 'password_confirmation' => ''])->assertRedirect(route('users.index'));
    expect($target->fresh()->is_active)->toBeFalse();
});

test('administrator cannot suspend their own account', function () {
    $admin = accessUser('system-administrator');
    $this->actingAs($admin)->put(route('users.update', $admin), ['name' => $admin->name, 'email' => $admin->email, 'job_title' => $admin->job_title, 'phone' => $admin->phone, 'department_id' => $admin->department_id, 'role_id' => $admin->role_id, 'is_active' => 0, 'password' => '', 'password_confirmation' => ''])->assertSessionHasErrors('is_active');
    expect($admin->fresh()->is_active)->toBeTrue();
});
