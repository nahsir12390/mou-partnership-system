<?php

namespace App\Console\Commands;

use App\Models\Agreement;
use App\Models\Obligation;
use App\Models\User;
use App\Notifications\AgreementAttentionNotification;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;

class SendAgreementReminders extends Command
{
    protected $signature = 'agreements:send-reminders';

    protected $description = 'Email daily expiry and overdue-obligation reminders to responsible users';

    public function handle(): int
    {
        $sent = 0;
        $recipients = User::with('role')->where('is_active', true)
            ->whereHas('role', fn (Builder $query) => $query->whereIn('slug', ['system-administrator', 'management', 'legal-review-officer', 'department-officer']))
            ->get();

        foreach ($recipients as $user) {
            if ($user->notifications()->where('type', AgreementAttentionNotification::class)->whereDate('created_at', today())->exists()) {
                continue;
            }

            $institutionWide = $user->hasRole('system-administrator', 'management', 'legal-review-officer');
            $agreementIds = Agreement::query()
                ->when(! $institutionWide, function (Builder $query) use ($user): void {
                    $query->where(function (Builder $scope) use ($user): void {
                        if ($user->department_id) {
                            $scope->where('department_id', $user->department_id);
                        }
                        $scope->orWhere('responsible_officer_id', $user->id)->orWhere('created_by', $user->id);
                    });
                })
                ->select('id');

            $expiringCount = Agreement::whereIn('id', clone $agreementIds)
                ->whereBetween('expiry_date', [today(), today()->addDays(30)])
                ->whereNotIn('status', ['closed', 'terminated', 'expired'])
                ->count();
            $overdueCount = Obligation::whereIn('agreement_id', clone $agreementIds)
                ->whereDate('due_date', '<', today())
                ->whereNotIn('status', ['completed', 'cancelled'])
                ->count();

            if ($expiringCount + $overdueCount === 0) {
                continue;
            }

            $user->notify(new AgreementAttentionNotification($expiringCount, $overdueCount, today()->format('Y-m-d')));
            $sent++;
        }

        $this->info("Reminder notifications sent to {$sent} user(s).");

        return self::SUCCESS;
    }
}
