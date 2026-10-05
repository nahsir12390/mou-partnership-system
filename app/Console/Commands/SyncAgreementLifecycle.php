<?php

namespace App\Console\Commands;

use App\Models\Agreement;
use Illuminate\Console\Command;

class SyncAgreementLifecycle extends Command
{
    protected $signature = 'agreements:sync-lifecycle';

    protected $description = 'Refresh agreement expiry and renewal indicators from their expiry dates';

    public function handle(): int
    {
        $updated = 0;

        Agreement::whereNotNull('expiry_date')->chunkById(100, function ($agreements) use (&$updated) {
            foreach ($agreements as $agreement) {
                if (in_array($agreement->status, ['closed', 'renewed', 'terminated'], true)) {
                    continue;
                }

                $changes = [];
                if ($agreement->expiry_date->isPast()) {
                    if ($agreement->status === 'active') {
                        $changes['status'] = 'expired';
                    }
                    if (! in_array($agreement->renewal_status, ['renewed', 'not_renewing'], true)) {
                        $changes['renewal_status'] = 'due';
                    }
                } elseif ($agreement->expiry_date->lte(today()->addDays(90))) {
                    if ($agreement->renewal_status === 'not_due') {
                        $changes['renewal_status'] = 'due_soon';
                    }
                }

                if ($changes) {
                    $agreement->update($changes);
                    $updated++;
                }
            }
        });

        $this->info("Agreement lifecycle synchronized. {$updated} record(s) updated.");

        return self::SUCCESS;
    }
}
