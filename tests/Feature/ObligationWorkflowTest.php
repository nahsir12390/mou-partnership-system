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
function hiddenObligationAgreement(): Agreement {
    $admin=obligationUser('system-administrator');
    $department=Department::where('code','RAP')->firstOrFail();
    $other=Department::whereKeyNot($department->id)->first();
    if (! $other) $other=Department::create(['name'=>'Separate Unit','code'=>'SU','is_active'=>true]);
    return Agreement::create(['reference_number'=>'MOU/OBL/HIDDEN','title'=>'Hidden Department Partnership','partner_id'=>Partner::firstOrFail()->id,'department_id'=>$other->id,'responsible_officer_id'=>$admin->id,'agreement_type'=>'Memorandum of Understanding','status'=>'active','renewal_status'=>'not_due','created_by'=>$admin->id]);
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

test('department officer cannot discover obligations belonging to another department', function(){
    $officer=obligationUser('department-officer');
    $visible=obligationAgreement();
    $hidden=hiddenObligationAgreement();
    Obligation::create(['agreement_id'=>$visible->id,'title'=>'Visible implementation item','type'=>'obligation','priority'=>'medium','status'=>'not_started','progress'=>0,'created_by'=>$officer->id]);
    Obligation::create(['agreement_id'=>$hidden->id,'title'=>'Confidential other department item','type'=>'obligation','priority'=>'high','status'=>'not_started','progress'=>0,'created_by'=>obligationUser('system-administrator')->id]);

    $this->actingAs($officer)->get(route('obligations.index'))->assertOk()->assertSee('Visible implementation item')->assertDontSee('Confidential other department item');
});

test('department officer cannot manage an obligation through a direct url from another department', function(){
    $officer=obligationUser('department-officer');
    $agreement=hiddenObligationAgreement();
    $item=Obligation::create(['agreement_id'=>$agreement->id,'title'=>'Protected milestone','type'=>'milestone','priority'=>'high','status'=>'in_progress','progress'=>40,'created_by'=>obligationUser('system-administrator')->id]);

    $this->actingAs($officer)->get(route('obligations.edit',$item))->assertForbidden();
    $this->actingAs($officer)->patch(route('obligations.progress',$item),['status'=>'completed','progress'=>100])->assertForbidden();
    $this->actingAs($officer)->delete(route('obligations.destroy',$item))->assertForbidden();
    $this->assertDatabaseHas('obligations',['id'=>$item->id,'progress'=>40]);
});

test('department officer cannot create an obligation on an agreement from another department', function(){
    $officer=obligationUser('department-officer');
    $agreement=hiddenObligationAgreement();

    $this->actingAs($officer)->post(route('obligations.store',$agreement),['title'=>'Unauthorized cross department item','type'=>'obligation','priority'=>'medium','status'=>'not_started','progress'=>0])->assertForbidden();
    $this->assertDatabaseMissing('obligations',['agreement_id'=>$agreement->id,'title'=>'Unauthorized cross department item']);
});

test('department officer cannot assign an obligation to an officer in another department', function(){
    $officer=obligationUser('department-officer');
    $agreement=obligationAgreement();
    $admin=obligationUser('system-administrator');

    $this->actingAs($officer)->post(route('obligations.store',$agreement),['title'=>'Cross department assignment','type'=>'deliverable','priority'=>'high','status'=>'not_started','progress'=>0,'responsible_officer_id'=>$admin->id])->assertForbidden();
    $this->assertDatabaseMissing('obligations',['agreement_id'=>$agreement->id,'title'=>'Cross department assignment']);
});

test('institution wide user can see obligations across departments', function(){
    $management=obligationUser('management');
    $agreement=hiddenObligationAgreement();
    Obligation::create(['agreement_id'=>$agreement->id,'title'=>'Institution wide visible milestone','type'=>'milestone','priority'=>'medium','status'=>'not_started','progress'=>0,'created_by'=>obligationUser('system-administrator')->id]);

    $this->actingAs($management)->get(route('obligations.index'))->assertOk()->assertSee('Institution wide visible milestone');
});
