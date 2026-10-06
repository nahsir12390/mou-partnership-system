<?php

namespace App\Http\Controllers;

use App\Models\Agreement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RenewalController extends Controller
{
    public function create(Agreement $agreement): View
    {
        $this->ensureRenewable($agreement);

        return view('renewals.create', compact('agreement'));
    }

    public function store(Request $request, Agreement $agreement): RedirectResponse
    {
        $this->ensureRenewable($agreement);

        $data = $request->validate([
            'decision' => ['required', Rule::in(['renewed', 'extended', 'not_renewing'])],
            'decision_date' => ['required', 'date'],
            'new_expiry_date' => [Rule::requiredIf($request->input('decision') !== 'not_renewing'), 'nullable', 'date', 'after:decision_date'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ]);

        DB::transaction(function () use ($agreement, $data, $request): void {
            $agreement->renewalRecords()->create([
                ...$data,
                'previous_expiry_date' => $agreement->expiry_date?->format('Y-m-d'),
                'recorded_by' => $request->user()->id,
            ]);

            if ($data['decision'] === 'not_renewing') {
                $agreement->update(['renewal_status' => 'not_renewing']);
            } else {
                $agreement->update([
                    'expiry_date' => $data['new_expiry_date'],
                    'renewal_status' => 'renewed',
                    'status' => 'active',
                    'approval_stage' => 'Active — renewed',
                ]);
            }
        });

        return redirect()->route('agreements.show', $agreement)->with('success', 'Renewal decision recorded successfully.');
    }

    private function ensureRenewable(Agreement $agreement): void
    {
        abort_unless(
            in_array($agreement->status, ['active', 'expired', 'renewed'], true),
            422,
            'Renewal decisions can only be recorded for agreements that have reached implementation.',
        );
    }
}
