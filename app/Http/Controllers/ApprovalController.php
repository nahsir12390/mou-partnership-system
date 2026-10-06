<?php

namespace App\Http\Controllers;

use App\Models\Agreement;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ApprovalController extends Controller
{
    private function isInstitutionWide($user): bool
    {
        return $user->hasRole('system-administrator', 'management', 'legal-review-officer');
    }

    private function scopeVisible(Builder $query, $user): Builder
    {
        if ($this->isInstitutionWide($user)) {
            return $query;
        }

        return $query->where(function (Builder $scope) use ($user) {
            if ($user->department_id) {
                $scope->where('department_id', $user->department_id);
            }
            $scope->orWhere('responsible_officer_id', $user->id)->orWhere('created_by', $user->id);
        });
    }

    private function ensureVisible(Agreement $agreement, $user): void
    {
        if ($this->isInstitutionWide($user)) {
            return;
        }
        abort_unless(($user->department_id && $agreement->department_id === $user->department_id) || $agreement->responsible_officer_id === $user->id || $agreement->created_by === $user->id, 403);
    }

    public function index(Request $request): View
    {
        $user = $request->user();
        $query = $this->scopeVisible(Agreement::with(['partner', 'department', 'responsibleOfficer']), $user)
            ->whereIn('status', ['submitted', 'under_review']);

        if ($user->hasRole('legal-review-officer')) {
            $query->where(function (Builder $stage) {
                $stage->where('status', 'submitted')
                    ->orWhere(fn (Builder $review) => $review->where('status', 'under_review')->where('approval_stage', 'Legal review'));
            });
        } elseif ($user->hasRole('management')) {
            $query->where('status', 'under_review')->where('approval_stage', 'Management approval');
        }

        $queueSummary = [
            'assigned' => (clone $query)->count(),
            'legal' => (clone $query)->where(function (Builder $stage) {
                $stage->where('status', 'submitted')->orWhere('approval_stage', 'Legal review');
            })->count(),
            'management' => (clone $query)->where('approval_stage', 'Management approval')->count(),
        ];

        $agreements = $query
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->latest('updated_at')->paginate(12)->withQueryString();

        return view('approvals.index', compact('agreements', 'queueSummary'));
    }

    public function submit(Request $request, Agreement $agreement): RedirectResponse
    {
        abort_unless($request->user()->hasPermission('agreements.update'), 403);
        $this->ensureVisible($agreement, $request->user());
        abort_unless($agreement->status === 'draft', 422, 'Only draft agreements can be submitted for review.');
        $this->transition($request, $agreement, 'submitted', 'submitted', 'Awaiting legal review', $request->input('comment'));

        return back()->with('success', 'Agreement submitted for review.');
    }

    public function review(Request $request, Agreement $agreement): RedirectResponse
    {
        abort_unless($request->user()->hasPermission('approvals.review'), 403);
        $this->ensureVisible($agreement, $request->user());
        $data = $request->validate([
            'decision' => ['required', Rule::in(['start_review', 'legal_clear', 'approve', 'request_changes', 'reject'])],
            'comment' => [Rule::requiredIf(fn () => in_array($request->input('decision'), ['request_changes', 'reject'], true)), 'nullable', 'string', 'max:3000'],
        ], ['comment.required' => 'Please provide a reason for this decision.']);
        [$toStatus, $stage, $message] = match ($data['decision']) {
            'start_review' => ['under_review', 'Legal review', 'Legal review started.'],
            'legal_clear' => ['under_review', 'Management approval', 'Legal review completed and forwarded to management.'],
            'approve' => ['approved', 'Approved', 'Agreement approved.'],
            'request_changes' => ['draft', 'Changes requested', 'Agreement returned for changes.'],
            'reject' => ['closed', 'Rejected', 'Agreement rejected and closed.'],
            default => abort(422, 'Unsupported approval decision.'),
        };
        $allowed = match ($data['decision']) {
            'start_review' => ['submitted'],
            'legal_clear', 'approve', 'request_changes', 'reject' => ['under_review'],
            default => abort(422, 'Unsupported approval decision.'),
        };
        abort_unless(in_array($agreement->status, $allowed, true), 422, 'This decision is not available at the current workflow stage.');
        $this->authorizeStageDecision($request, $agreement, $data['decision']);
        $this->transition($request, $agreement, $toStatus, $data['decision'], $stage, $data['comment'] ?? null);

        return back()->with('success', $message);
    }

    private function authorizeStageDecision(Request $request, Agreement $agreement, string $decision): void
    {
        $user = $request->user();
        $isAdministrator = $user->hasRole('system-administrator');

        if ($decision === 'start_review') {
            abort_unless($isAdministrator || $user->hasRole('legal-review-officer'), 403, 'Only Legal Services can begin the legal review.');

            return;
        }

        if ($agreement->approval_stage === 'Legal review') {
            abort_unless($isAdministrator || $user->hasRole('legal-review-officer'), 403, 'This agreement is currently assigned to Legal Services.');
            abort_if($decision === 'approve', 422, 'Legal Services must clear the agreement for management approval.');

            return;
        }

        if ($agreement->approval_stage === 'Management approval') {
            abort_unless($isAdministrator || $user->hasRole('management'), 403, 'This agreement is awaiting management approval.');
            abort_if($decision === 'legal_clear', 422, 'Legal review has already been completed.');

            return;
        }

        abort(422, 'The agreement does not have a valid current review stage.');
    }

    private function transition(Request $request, Agreement $agreement, string $toStatus, string $action, string $stage, ?string $comment): void
    {
        DB::transaction(function () use ($request, $agreement, $toStatus, $action, $stage, $comment) {
            $fromStatus = $agreement->status;
            $agreement->update(['status' => $toStatus, 'approval_stage' => $stage]);
            $agreement->approvalActions()->create(['user_id' => $request->user()->id, 'action' => $action, 'from_status' => $fromStatus, 'to_status' => $toStatus, 'comment' => $comment]);
        });
    }
}
