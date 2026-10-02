<?php

use App\Models\Agreement;
use App\Models\Department;
use App\Models\Obligation;
use App\Models\Partner;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);
beforeEach(function(){ $this->seed(DatabaseSeeder::class); });

function obligationUser(string $slug): User { return User::whereHas('role',fn($q)=>$q->where('slug',$slug))->firstOrFail(); }
function obligationAgreement(): Agreement {
    $admin=obligationUser('system-administrator');
    return Agreement::create(['reference_number'=>'MOU/OBL/001','title'=>'Implementation Partnership','partner_id'=>Partner::firstOrFail()->id,'department_id'=>Department::where('code','RAP')->firstOrFail()->id,'responsible_officer_id'=>obligationUser('department-officer')->id,'agreement_type'=>'Memorandum of Understanding','status'=>'active','renewal_status'=>'not_due','created_by'=>$admin->id]);
}

test('department officer can create an obligation', function(){
    $agreement=obligationAgreement(); $officer=obligationUser('department-officer');
    $this->actingAs($officer)->post(route('obligations.store',$agreement),['title'=>'Submit quarterly implementation report','type'=>'deliverable','priority'=>'high','status'=>'not_started','progress'=>0,'due_date'=>now()->addMonth()->format('Y-m-d'),'responsible_officer_id'=>$officer->id])->assertRedirect(route('agreements.show',$agreement));
    $this->assertDatabaseHas('obligations',['agreement_id'=>$agreement->id,'title'=>'Submit quarterly implementation report','status'=>'not_started']);
});

test('completing obligation forces progress to one hundred and records completion time', function(){
    $agreement=obligationAgreement(); $officer=obligationUser('department-officer');
    $item=Obligation::create(['agreement_id'=>$agreement->id,'title'=>'Launch programme','type'=>'milestone','priority'=>'critical','status'=>'in_progress','progress'=>60,'created_by'=>$officer->id]);
    $this->actingAs($officer)->patch(route('obligations.progress',$item),['status'=>'completed','progress'=>80,'completion_notes'=>'Programme launched successfully.'])->assertRedirect();
    $item->refresh(); expect($item->status)->toBe('completed')->and($item->progress)->toBe(100)->and($item->completed_at)->not->toBeNull();
});

test('overdue accessor identifies unfinished past due commitments', function(){
    $agreement=obligationAgreement(); $officer=obligationUser('department-officer');
    $item=Obligation::create(['agreement_id'=>$agreement->id,'title'=>'Past due report','type'=>'obligation','priority'=>'high','status'=>'in_progress','progress'=>25,'due_date'=>now()->subDay(),'created_by'=>$officer->id]);
    expect($item->is_overdue)->toBeTrue(); $item->update(['status'=>'completed','progress'=>100,'completed_at'=>now()]); expect($item->fresh()->is_overdue)->toBeFalse();
});

test('read only user can view but cannot manage obligations', function(){
    $agreement=obligationAgreement(); $viewer=obligationUser('read-only-user');
    $this->actingAs($viewer)->get(route('obligations.index'))->assertOk();
    $this->actingAs($viewer)->post(route('obligations.store',$agreement),['title'=>'Unauthorized','type'=>'obligation','priority'=>'low','status'=>'not_started','progress'=>0])->assertForbidden();
});

test('progress validation prevents values above one hundred', function(){
    $agreement=obligationAgreement(); $officer=obligationUser('department-officer');
    $item=Obligation::create(['agreement_id'=>$agreement->id,'title'=>'Progress test','type'=>'obligation','priority'=>'medium','status'=>'in_progress','progress'=>50,'created_by'=>$officer->id]);
    $this->actingAs($officer)->patch(route('obligations.progress',$item),['status'=>'in_progress','progress'=>120])->assertSessionHasErrors('progress');
    expect($item->fresh()->progress)->toBe(50);
});