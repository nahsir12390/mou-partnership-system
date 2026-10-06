<?php

namespace App\Providers;

use App\Models\Agreement;
use App\Models\ApprovalAction;
use App\Models\Document;
use App\Models\Obligation;
use App\Models\Partner;
use App\Models\RenewalRecord;
use App\Models\User;
use App\Observers\AuditableObserver;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        if (app()->isProduction()) {
            URL::forceScheme('https');
        }

        $this->configureDefaults();
        $this->configureAuthorization();
        $this->configureAuditing();
    }

    protected function configureAuthorization(): void
    {
        $permissions = [
            'dashboard.view', 'partners.view', 'partners.create', 'partners.update',
            'agreements.view', 'agreements.create', 'agreements.update',
            'approvals.view', 'approvals.review', 'obligations.view', 'obligations.manage',
            'documents.view', 'documents.manage', 'reports.view', 'audit.view', 'alerts.view', 'users.manage',
            'agreements.lifecycle', 'renewals.manage',
        ];

        foreach ($permissions as $permission) {
            Gate::define($permission, fn ($user) => $user->hasPermission($permission));
        }
    }

    protected function configureAuditing(): void
    {
        foreach ([Agreement::class, ApprovalAction::class, Document::class, Obligation::class, Partner::class, RenewalRecord::class, User::class] as $model) {
            $model::observe(AuditableObserver::class);
        }
    }

    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);
        DB::prohibitDestructiveCommands(app()->isProduction());
        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)->mixedCase()->letters()->numbers()->symbols()->uncompromised()
            : null);
    }
}
