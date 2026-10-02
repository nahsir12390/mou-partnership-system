<?php

use App\Models\Agreement;
use App\Models\Department;
use App\Models\Partner;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);
beforeEach(function () { $this->seed(DatabaseSeeder::class); });
function partnerScopeUser(string $slug): User { return User::whereHas('role',fn($q)=>$q->where('slug',$slug))->firstOrFail(); }

test('department officer only sees partners connected to accessible agreements', function () {
    $officer=partnerScopeUser('department-officer');
    $visible=Partner::create(['name'=>'Visible Partner','status'=>'active','created_by'=>partnerScopeUser('system-administrator')->id]);
    $hidden=Partner::create(['name'=>'Hidden Partner','status'=>'active','created_by'=>partnerScopeUser('system-administrator')->id]);
    $other=Department::whereKeyNot($officer->department_id)->first() ?? Department::create(['name'=>'Other Unit','code'=>'OU','is_active'=>true]);
    Agreement::create(['reference_number'=>'MOU/PARTNER/V','title'=>'Visible Partnership','partner_id'=>$visible->id,'department_id'=>$officer->department_id,'responsible_officer_id'=>$officer->id,'agreement_type'=>'Memorandum of Understanding','status'=>'active','renewal_status'=>'not_due','created_by'=>partnerScopeUser('system-administrator')->id]);
    Agreement::create(['reference_number'=>'MOU/PARTNER/H','title'=>'Hidden Partnership','partner_id'=>$hidden->id,'department_id'=>$other->id,'responsible_officer_id'=>partnerScopeUser('system-administrator')->id,'agreement_type'=>'Memorandum of Understanding','status'=>'active','renewal_status'=>'not_due','created_by'=>partnerScopeUser('system-administrator')->id]);
    $this->actingAs($officer)->get(route('partners.index'))->assertOk()->assertSee('Visible Partner')->assertDontSee('Hidden Partner');
});

test('department officer cannot open another departments partner directly', function () {
    $officer=partnerScopeUser('department-officer');
    $partner=Partner::create(['name'=>'Restricted Partner','status'=>'active','created_by'=>partnerScopeUser('system-administrator')->id]);
    $this->actingAs($officer)->get(route('partners.show',$partner))->assertForbidden();
});

test('management retains institution wide partner visibility', function () {
    $partner=Partner::create(['name'=>'Institution Wide Partner','status'=>'active','created_by'=>partnerScopeUser('system-administrator')->id]);
    $this->actingAs(partnerScopeUser('management'))->get(route('partners.show',$partner))->assertOk()->assertSee('Institution Wide Partner');
});
