<?php

use App\Models\Agreement;
use App\Models\Department;
use App\Models\Partner;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);
beforeEach(function(){ $this->seed(DatabaseSeeder::class); });
function agreementScopeUser(string $slug): User { return User::whereHas('role',fn($q)=>$q->where('slug',$slug))->firstOrFail(); }
function scopedAgreement(array $overrides=[]): Agreement { $officer=agreementScopeUser('department-officer'); return Agreement::create(array_merge(['reference_number'=>'MOU/SCOPE/'.fake()->unique()->numberBetween(100,999),'title'=>'Scoped Agreement','partner_id'=>Partner::firstOrFail()->id,'department_id'=>$officer->department_id,'responsible_officer_id'=>$officer->id,'agreement_type'=>'Memorandum of Understanding','status'=>'active','renewal_status'=>'not_due','created_by'=>agreementScopeUser('system-administrator')->id],$overrides)); }

test('department officer cannot see another departments agreement in registry',function(){
    $officer=agreementScopeUser('department-officer');
    $own=scopedAgreement(['title'=>'Visible Agreement']);
    $other=Department::whereKeyNot($officer->department_id)->first() ?? Department::create(['name'=>'Foreign Unit','code'=>'FU','is_active'=>true]);
    scopedAgreement(['title'=>'Hidden Agreement','department_id'=>$other->id,'responsible_officer_id'=>agreementScopeUser('system-administrator')->id]);
    $this->actingAs($officer)->get(route('agreements.index'))->assertOk()->assertSee($own->title)->assertDontSee('Hidden Agreement');
});

test('department officer cannot open another departments agreement directly',function(){
    $officer=agreementScopeUser('department-officer'); $other=Department::whereKeyNot($officer->department_id)->first() ?? Department::create(['name'=>'Restricted Unit','code'=>'RX','is_active'=>true]);
    $agreement=scopedAgreement(['department_id'=>$other->id,'responsible_officer_id'=>agreementScopeUser('system-administrator')->id]);
    $this->actingAs($officer)->get(route('agreements.show',$agreement))->assertForbidden();
});

test('management can open agreements institution wide',function(){ $agreement=scopedAgreement(); $this->actingAs(agreementScopeUser('management'))->get(route('agreements.show',$agreement))->assertOk(); });
