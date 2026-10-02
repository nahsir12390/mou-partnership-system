<?php

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);
beforeEach(function () { $this->seed(DatabaseSeeder::class); });

function auditUser(string $slug): User { return User::whereHas('role',fn($q)=>$q->where('slug',$slug))->firstOrFail(); }

test('management can view institutional audit trail', function () {
    $this->actingAs(auditUser('management'))->get(route('audit.index'))->assertOk()->assertSee('Institutional Audit Trail');
});

test('ordinary department officer cannot view governance audit trail', function () {
    $this->actingAs(auditUser('department-officer'))->get(route('audit.index'))->assertForbidden();
});