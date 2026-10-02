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
        return $user->hasRole('system-administrator','management','legal-review-officer');
    }

    private function scopeVisible(Builder $query, $user): Builder
    {
        if ($this->isInstitutionWide($user)) return $query;
        return $query->where(function (Builder $scope) use ($user) {
            if ($user->department_id) $scope->where('department_id',$user->department_id);
            $scope->orWhere('responsible_officer_id',$user->id)->orWhere('created_by',$user->id);
        });
    }

    private function ensureVisible(Agreement $agreement, $user): void
    {
        if ($this->isInstitutionWide($user)) return;
        abort_unless(($user->department_id && $agreement->department_id === $user->department_id) || $agreement->responsible_officer_id === $user->id || $agreement->created_by === $user->id,403);
    }

    public function index(Request $request): View
    {
        $agreements = $this->scopeVisible(Agreement::with(['partner','department','responsibleOfficer']),$request->user())
            ->whereIn('status', ['submitted','under_review'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->latest('updated_at')->paginate(12)->withQueryString();
        return view('approvals.index', compact('agreements'));
    }

    public function submit(Request $request, Agreement $agreement): RedirectResponse
    {
        abort_unless($request->user()->hasPermission('agreements.update'), 403);
        $this->ensureVisible($agreement,$request->user());
        abort_unless($agreement->status === 'draft', 422, 'Only draft agreements can be submitted for review.');
        $this->transition($request, $agreement, 'submitted', 'submitted', 'Legal review', $request->input('comment'));
        return back()->with('success', 'Agreement submitted for review.');
    }

    public function review(Request $request, Agreement $agreement): RedirectResponse
    {
        abort_unless($request->user()->hasPermission('approvals.review'), 403);
        $this->ensureVisible($agreement,$request->user());
        $data = $request->validate([
            'decision' => ['required', Rule::in(['start_review','approve','request_changes','reject'])],
            'comment' => [Rule::requiredIf(fn () => in_array($request->input('decision'), ['request_changes','reject'], true)),'nullable','string','max:3000'],
        ], ['comment.required' => 'Please provide a reason for this decision.']);
        [$toStatus, $stage, $message] = match ($data['decision']) {
            'start_review' => ['under_review','Legal review','Review started.'],
            'approve' => ['approved','Approved','Agreement approved.'],
            'request_changes' => ['draft','Changes requested','Agreement returned for changes.'],
            'reject' => ['closed','Rejected','Agreement rejected and closed.'],
        };
        $allowed = match ($data['decision']) {
            'start_review' => ['submitted'],
            'approve', 'request_changes', 'reject' => ['submitted','under_review'],
        };
        abort_unless(in_array($agreement->status, $allowed, true), 422, 'This decision is not available at the current workflow stage.');
        $this->transition($request, $agreement, $toStatus, $data['decision'], $stage, $data['comment'] ?? null);
        return back()->with('success', $message);
    }

    private function transition(Request $request, Agreement $agreement, string $toStatus, string $action, string $stage, ?string $comment): void
    {
        DB::transaction(function () use ($request, $agreement, $toStatus, $action, $stage, $comment) {
            $fromStatus = $agreement->status;
            $agreement->update(['status' => $toStatus, 'approval_stage' => $stage]);
            $agreement->approvalActions()->create(['user_id'=>$request->user()->id,'action'=>$action,'from_status'=>$fromStatus,'to_status'=>$toStatus,'comment'=>$comment]);
        });
    }
}
