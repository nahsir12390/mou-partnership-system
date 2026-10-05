<?php

namespace App\Http\Controllers;

use App\Models\Agreement;
use App\Models\Obligation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AlertController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $agreementScope = Agreement::query();
        $obligationScope = Obligation::query();

        if (! $user->hasRole('system-administrator', 'management', 'legal-review-officer')) {
            $visibleAgreement = function (Builder $query) use ($user): void {
                $query->where(function (Builder $scope) use ($user): void {
                    if ($user->department_id) {
                        $scope->where('department_id', $user->department_id);
                    }

                    $scope->orWhere('responsible_officer_id', $user->id)
                        ->orWhere('created_by', $user->id);
                });
            };

            $agreementScope->where($visibleAgreement);
            $obligationScope->whereHas('agreement', $visibleAgreement);
        }

        $expiring = (clone $agreementScope)->with('partner')->whereNotNull('expiry_date')
            ->whereBetween('expiry_date', [today(), today()->addDays(90)])
            ->whereNotIn('status', ['expired', 'terminated'])->orderBy('expiry_date')->get();

        $expired = (clone $agreementScope)->with('partner')->whereNotNull('expiry_date')
            ->whereDate('expiry_date', '<', today())->whereNotIn('status', ['expired', 'terminated'])->orderByDesc('expiry_date')->get();

        $overdue = (clone $obligationScope)->with(['agreement', 'responsibleOfficer'])
            ->whereNotNull('due_date')->whereDate('due_date', '<', today())
            ->whereNotIn('status', ['completed', 'cancelled'])->orderBy('due_date')->get();

        $dueSoon = (clone $obligationScope)->with(['agreement', 'responsibleOfficer'])
            ->whereNotNull('due_date')->whereBetween('due_date', [today(), today()->addDays(30)])
            ->whereNotIn('status', ['completed', 'cancelled'])->orderBy('due_date')->get();

        $approval = collect();
        if ($user->hasPermission('approvals.view')) {
            $approval = (clone $agreementScope)->with('partner')->whereIn('status', ['submitted', 'under_review'])->latest('updated_at')->get();
        }

        return view('alerts.index', compact('expiring', 'expired', 'overdue', 'dueSoon', 'approval'));
    }
}
