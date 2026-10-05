<?php

use App\Models\Agreement;
use App\Models\Obligation;
use App\Models\Partner;
use App\Models\User;
use App\Notifications\AgreementAttentionNotification;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

test('scheduled reminder command notifies responsible users about expiring and overdue records', function () {
    $this->seed(DatabaseSeeder::class);
    Notification::fake();
    $officer = User::where('email', 'officer@example.com')->firstOrFail();
    $admin = User::where('email', 'admin@example.com')->firstOrFail();
    $agreement = Agreement::create([
        'reference_number' => 'MOU/REMINDER/001',
        'title' => 'Reminder Test Agreement',
        'partner_id' => Partner::firstOrFail()->id,
        'department_id' => $officer->department_id,
        'responsible_officer_id' => $officer->id,
        'agreement_type' => 'MoU',
        'expiry_date' => today()->addDays(10),
        'status' => 'active',
        'renewal_status' => 'due_soon',
        'created_by' => $admin->id,
    ]);
    Obligation::create([
        'agreement_id' => $agreement->id,
        'title' => 'Overdue report',
        'type' => 'deliverable',
        'due_date' => today()->subDay(),
        'priority' => 'high',
        'status' => 'in_progress',
        'progress' => 50,
        'created_by' => $admin->id,
    ]);

    $this->artisan('agreements:send-reminders')->assertSuccessful();

    Notification::assertSentTo($officer, AgreementAttentionNotification::class, fn ($notification) => $notification->expiringCount === 1 && $notification->overdueCount === 1);
});
