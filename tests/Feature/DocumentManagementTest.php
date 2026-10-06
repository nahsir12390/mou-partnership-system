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
beforeEach(function () {
    $this->seed(DatabaseSeeder::class);
    Storage::fake('local');
});
function documentUser(string $slug): User
{
    return User::whereHas('role', fn ($q) => $q->where('slug', $slug))->firstOrFail();
}
function documentAgreement(): Agreement
{
    $officer = documentUser('department-officer');

    return Agreement::create(['reference_number' => 'MOU/DOC/001', 'title' => 'Document Test Agreement', 'partner_id' => Partner::firstOrFail()->id, 'department_id' => $officer->department_id, 'responsible_officer_id' => $officer->id, 'agreement_type' => 'Memorandum of Understanding', 'status' => 'draft', 'renewal_status' => 'not_due', 'created_by' => documentUser('system-administrator')->id]);
}

test('document manager can upload an agreement document', function () {
    $agreement = documentAgreement();
    $user = documentUser('department-officer');
    $response = $this->actingAs($user)->post(route('documents.store', $agreement), ['title' => 'Draft MoU', 'type' => 'draft_mou', 'version' => 1, 'file' => UploadedFile::fake()->create('draft.pdf', 200, 'application/pdf')]);
    $response->assertRedirect(route('agreements.show', $agreement));
    expect(Document::count())->toBe(1);
    Storage::disk('local')->assertExists(Document::first()->path);
});
test('read only user can download but cannot upload documents', function () {
    $agreement = documentAgreement();
    $viewer = documentUser('read-only-user');
    $this->actingAs($viewer)->get(route('documents.create', $agreement))->assertForbidden();
    $this->actingAs($viewer)->post(route('documents.store', $agreement), [])->assertForbidden();
});
test('document upload rejects unsupported files', function () {
    $agreement = documentAgreement();
    $user = documentUser('department-officer');
    $this->actingAs($user)->post(route('documents.store', $agreement), ['title' => 'Unsafe File', 'type' => 'other', 'version' => 1, 'file' => UploadedFile::fake()->create('script.exe', 50, 'application/octet-stream')])->assertSessionHasErrors('file');
    expect(Document::count())->toBe(0);
});
test('deleting a document removes its stored file and database record', function () {
    $agreement = documentAgreement();
    $user = documentUser('department-officer');
    $this->actingAs($user)->post(route('documents.store', $agreement), ['title' => 'Signed Copy', 'type' => 'signed_mou', 'version' => 1, 'is_final' => 1, 'file' => UploadedFile::fake()->create('signed.pdf', 200, 'application/pdf')]);
    $document = Document::firstOrFail();
    $path = $document->path;
    $this->actingAs($user)->delete(route('documents.destroy', $document))->assertRedirect(route('agreements.show', $agreement));
    expect(Document::count())->toBe(0);
    Storage::disk('local')->assertMissing($path);
});

test('department officer cannot browse another departments documents', function () {
    $officer = documentUser('department-officer');
    $other = Department::whereKeyNot($officer->department_id)->first() ?? Department::create(['name' => 'Other Department', 'code' => 'OD', 'is_active' => true]);
    $agreement = Agreement::create(['reference_number' => 'MOU/PRIVATE/001', 'title' => 'Private Department Agreement', 'partner_id' => Partner::firstOrFail()->id, 'department_id' => $other->id, 'responsible_officer_id' => documentUser('system-administrator')->id, 'agreement_type' => 'Memorandum of Understanding', 'status' => 'active', 'renewal_status' => 'not_due', 'created_by' => documentUser('system-administrator')->id]);
    Document::create(['agreement_id' => $agreement->id, 'title' => 'Confidential Department File', 'type' => 'supporting_document', 'version' => 1, 'original_name' => 'confidential.pdf', 'stored_name' => 'confidential.pdf', 'path' => 'private/confidential.pdf', 'mime_type' => 'application/pdf', 'size' => 100, 'uploaded_by' => documentUser('system-administrator')->id]);
    $this->actingAs($officer)->get(route('documents.index'))->assertOk()->assertDontSee('Confidential Department File');
});

test('department officer cannot download another departments document by url', function () {
    $officer = documentUser('department-officer');
    $other = Department::whereKeyNot($officer->department_id)->first() ?? Department::create(['name' => 'Restricted Unit', 'code' => 'RU', 'is_active' => true]);
    $agreement = Agreement::create(['reference_number' => 'MOU/PRIVATE/002', 'title' => 'Restricted Agreement', 'partner_id' => Partner::firstOrFail()->id, 'department_id' => $other->id, 'responsible_officer_id' => documentUser('system-administrator')->id, 'agreement_type' => 'Memorandum of Understanding', 'status' => 'active', 'renewal_status' => 'not_due', 'created_by' => documentUser('system-administrator')->id]);
    $document = Document::create(['agreement_id' => $agreement->id, 'title' => 'Restricted File', 'type' => 'supporting_document', 'version' => 1, 'original_name' => 'restricted.pdf', 'stored_name' => 'restricted.pdf', 'path' => 'private/restricted.pdf', 'mime_type' => 'application/pdf', 'size' => 100, 'uploaded_by' => documentUser('system-administrator')->id]);
    Storage::disk('local')->put($document->path, 'secret');
    $this->actingAs($officer)->get(route('documents.download', $document))->assertForbidden();
});
