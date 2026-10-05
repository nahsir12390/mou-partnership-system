<?php

namespace App\Observers;

use App\Models\ActivityLog;
use App\Models\Agreement;
use App\Models\ApprovalAction;
use App\Models\Document;
use App\Models\Obligation;
use App\Models\Partner;
use App\Models\RenewalRecord;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class AuditableObserver
{
    public function created(Model $model): void
    {
        $this->record($model, 'created', [], $this->safeAttributes($model));
    }

    public function updated(Model $model): void
    {
        $changes = $this->safeChanges($model);

        if ($changes !== []) {
            $this->record($model, 'updated', $this->safeOriginal($model, array_keys($changes)), $changes);
        }
    }

    public function deleted(Model $model): void
    {
        $this->record($model, 'deleted', $this->safeAttributes($model), []);
    }

    private function record(Model $model, string $event, array $before, array $after): void
    {
        if (! Schema::hasTable('activity_logs')) {
            return;
        }

        ActivityLog::create([
            'actor_id' => auth()->id(),
            'agreement_id' => $this->agreementId($model),
            'event' => $event,
            'subject_type' => $model::class,
            'subject_id' => $model->getKey(),
            'description' => ucfirst($event).' '.$this->label($model),
            'metadata' => array_filter(['before' => $before, 'after' => $after]),
            'ip_address' => app()->runningInConsole() ? null : request()->ip(),
        ]);
    }

    private function agreementId(Model $model): ?int
    {
        if ($model instanceof Agreement) {
            return $model->getKey();
        }

        if ($model instanceof ApprovalAction || $model instanceof Document || $model instanceof Obligation || $model instanceof RenewalRecord) {
            return $model->agreement_id;
        }

        return null;
    }

    private function label(Model $model): string
    {
        $name = match (true) {
            $model instanceof Agreement => $model->reference_number,
            $model instanceof Partner, $model instanceof User => $model->name,
            $model instanceof Document, $model instanceof Obligation => $model->title,
            $model instanceof ApprovalAction => str_replace('_', ' ', $model->action),
            $model instanceof RenewalRecord => str_replace('_', ' ', $model->decision).' renewal record',
            default => class_basename($model),
        };

        return class_basename($model).' “'.$name.'”';
    }

    private function safeAttributes(Model $model): array
    {
        return collect($model->getAttributes())
            ->except(['password', 'remember_token', 'two_factor_secret', 'two_factor_recovery_codes'])
            ->all();
    }

    private function safeChanges(Model $model): array
    {
        return collect($model->getChanges())
            ->except(['password', 'remember_token', 'two_factor_secret', 'two_factor_recovery_codes', 'updated_at'])
            ->all();
    }

    private function safeOriginal(Model $model, array $keys): array
    {
        return collect($model->getRawOriginal())
            ->only($keys)
            ->except(['password', 'remember_token', 'two_factor_secret', 'two_factor_recovery_codes'])
            ->all();
    }
}
