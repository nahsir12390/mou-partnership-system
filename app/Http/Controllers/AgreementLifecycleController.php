<?php

namespace App\Http\Controllers;

use App\Models\Agreement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AgreementLifecycleController extends Controller
{
    public function update(Request $request, Agreement $agreement): RedirectResponse
    {
        $data = $request->validate([
            'action' => ['required', Rule::in(['await_signature', 'activate', 'close', 'withdraw', 'terminate'])],
            'comment' => [Rule::requiredIf(fn () => in_array($request->input('action'), ['withdraw', 'terminate'], true)), 'nullable', 'string', 'max:3000'],
        ]);

        [$allowed, $status, $stage, $message] = match ($data['action']) {
            'await_signature' => [['approved'], 'awaiting_signature', 'Awaiting signature', 'Agreement moved to signature.'],
            'activate' => [['awaiting_signature'], 'active', 'Active', 'Agreement activated successfully.'],
            'close' => [['active', 'expired', 'renewed'], 'closed', 'Closed', 'Agreement closed successfully.'],
            'withdraw' => [['approved', 'awaiting_signature'], 'closed', 'Withdrawn', 'Agreement withdrawn before execution.'],
            'terminate' => [['active'], 'terminated', 'Terminated', 'Active agreement terminated.'],
            default => abort(422, 'Unsupported lifecycle action.'),
        };

        abort_unless(in_array($agreement->status, $allowed, true), 422, 'This lifecycle action is not available at the current stage.');

        if ($data['action'] === 'activate') {
            abort_unless($agreement->documents()->where('type', 'signed_mou')->where('is_final', true)->exists(), 422, 'Upload and mark a signed MoU as the final copy before activation.');
        }

        $fromStatus = $agreement->status;
        $updates = ['status' => $status, 'approval_stage' => $stage];
        if ($data['action'] === 'activate' && ! $agreement->start_date) {
            $updates['start_date'] = today();
        }

        $agreement->update($updates);
        $agreement->approvalActions()->create([
            'user_id' => $request->user()->id,
            'action' => $data['action'],
            'from_status' => $fromStatus,
            'to_status' => $status,
            'comment' => $data['comment'] ?? null,
        ]);

        return back()->with('success', $message);
    }
}
