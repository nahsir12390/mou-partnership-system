<?php

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);
beforeEach(function () {
    $this->seed(DatabaseSeeder::class);
});

function reportExportUser(string $slug): User
{
    return User::whereHas('role', fn ($query) => $query->where('slug', $slug))->firstOrFail();
}

test('management can export agreement register as csv', function () {
    $response = $this->actingAs(reportExportUser('management'))->get(route('reports.export', 'agreements'));
    $response->assertOk();
    expect($response->headers->get('content-type'))->toContain('text/csv');
    expect($response->streamedContent())->toContain('Reference,Agreement,Partner,Department,Status');
});

test('management can export renewal and obligation reports', function () {
    $this->actingAs(reportExportUser('management'))->get(route('reports.export', 'renewals'))->assertOk();
    $this->actingAs(reportExportUser('management'))->get(route('reports.export', 'obligations'))->assertOk();
});

test('department officer cannot export management reports', function () {
    $this->actingAs(reportExportUser('department-officer'))->get(route('reports.export', 'agreements'))->assertForbidden();
});

test('unknown report export type returns not found', function () {
    $this->actingAs(reportExportUser('management'))->get(route('reports.export', 'unknown'))->assertNotFound();
});
