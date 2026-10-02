<?php

use App\Models\Agreement;
use App\Models\Department;
use App\Models\Document;
use App\Models\Partner;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () { $this->seed(DatabaseSeeder::class); Storage::fake('local'); });

function documentUser(string $slug): User { return User::whereHas('role', fn($q)=>$q->where('slug',$slug))->firstOrFail(); }
function documentAgreement(): Agreement {
    return Agreement::create(['reference_number'=>'MOU/DOC/001','title'=>'Document Test Agreement','partner_id'=>Partner::firstOrFail()->id,'department_id'=>Department::firstOrFail()->id,'responsible_officer_id'=>documentUser('department-officer')->id,'agreement_type'=>'Memorandum of Understanding','status'=>'draft','renewal_status'=>'not_due','created_by'=>documentUser('system-administrator')->id]);
}

test('document manager can upload an agreement document', function () {
    $agreement=documentAgreement(); $user=documentUser('department-officer');
    $response=$this->actingAs($user)->post(route('documents.store',$agreement),['title'=>'Draft MoU','type'=>'draft_mou','version'=>1,'file'=>UploadedFile::fake()->create('draft.pdf',200,'application/pdf')]);
    $response->assertRedirect(route('agreements.show',$agreement));
    expect(Document::count())->toBe(1);
    Storage::disk('local')->assertExists(Document::first()->path);
});

test('read only user can download but cannot upload documents', function () {
    $agreement=documentAgreement(); $viewer=documentUser('read-only-user');
    $this->actingAs($viewer)->get(route('documents.create',$agreement))->assertForbidden();
    $this->actingAs($viewer)->post(route('documents.store',$agreement),[])->assertForbidden();
});

test('document upload rejects unsupported files', function () {
    $agreement=documentAgreement(); $user=documentUser('department-officer');
    $this->actingAs($user)->post(route('documents.store',$agreement),['title'=>'Unsafe File','type'=>'other','version'=>1,'file'=>UploadedFile::fake()->create('script.exe',50,'application/octet-stream')])->assertSessionHasErrors('file');
    expect(Document::count())->toBe(0);
});

test('deleting a document removes its stored file and database record', function () {
    $agreement=documentAgreement(); $user=documentUser('department-officer');
    $this->actingAs($user)->post(route('documents.store',$agreement),['title'=>'Signed Copy','type'=>'signed_mou','version'=>1,'is_final'=>1,'file'=>UploadedFile::fake()->create('signed.pdf',200,'application/pdf')]);
    $document=Document::firstOrFail(); $path=$document->path;
    $this->actingAs($user)->delete(route('documents.destroy',$document))->assertRedirect(route('agreements.show',$agreement));
    expect(Document::count())->toBe(0); Storage::disk('local')->assertMissing($path);
});