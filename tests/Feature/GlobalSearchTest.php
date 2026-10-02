<?php

use App\Models\Agreement;
use App\Models\Partner;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);
beforeEach(function () { $this->seed(DatabaseSeeder::class); });

function searchUser(string $slug): User { return User::whereHas('role',fn($q)=>$q->where('slug',$slug))->firstOrFail(); }

test('authenticated authorized user can open global search', function () {
    $this->actingAs(searchUser('management'))->get(route('search.index'))->assertOk()->assertSee('Global Search');
});

test('global search finds agreement by reference and title', function () {
    $agreement = Agreement::create(['reference_number'=>'MOU/SEARCH/777','title'=>'Digital Research Collaboration','partner_id'=>Partner::firstOrFail()->id,'department_id'=>searchUser('department-officer')->department_id,'responsible_officer_id'=>searchUser('department-officer')->id,'agreement_type'=>'Memorandum of Understanding','status'=>'active','renewal_status'=>'not_due','created_by'=>searchUser('system-administrator')->id]);
    $this->actingAs(searchUser('management'))->get(route('search.index',['q'=>'SEARCH/777']))->assertOk()->assertSee($agreement->title)->assertSee($agreement->reference_number);
});

test('global search requires at least two characters', function () {
    $this->actingAs(searchUser('management'))->get(route('search.index',['q'=>'x']))->assertOk()->assertSee('Enter at least two characters');
});

test('inactive user cannot use global search', function () {
    $user=searchUser('read-only-user'); $user->update(['is_active'=>false]);
    $this->actingAs($user)->get(route('search.index',['q'=>'MOU']))->assertForbidden();
});