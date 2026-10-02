<?php

namespace App\Http\Controllers;

use App\Models\Agreement;
use App\Models\Department;
use App\Models\Obligation;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ObligationController extends Controller
{
    private function isInstitutionWide($user): bool
    {
        return $user->hasRole('system-administrator','management','legal-review-officer');
    }

    private function scopeVisible(Builder $query, $user): Builder
    {
        if ($this->isInstitutionWide($user)) return $query;

        return $query->whereHas('agreement', function (Builder $agreement) use ($user) {
            $agreement->where(function (Builder $scope) use ($user) {
                if ($user->department_id) $scope->where('department_id',$user->department_id);
                $scope->orWhere('responsible_officer_id',$user->id)->orWhere('created_by',$user->id);
            });
        });
    }

    private function ensureAgreementVisible(Agreement $agreement, $user): void
    {
        if ($this->isInstitutionWide($user)) return;

        abort_unless(
            ($user->department_id && $agreement->department_id === $user->department_id)
            || $agreement->responsible_officer_id === $user->id
            || $agreement->created_by === $user->id,
            403
        );
    }

    private function ensureVisible(Obligation $obligation, $user): void
    {
        $this->ensureAgreementVisible($obligation->agreement, $user);
    }

    public function index(Request $request): View
    {
        $obligations = $this->scopeVisible(
            Obligation::with(['agreement.partner','department','responsibleOfficer']),
            $request->user()
        )
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('type'), fn ($q) => $q->where('type', $request->type))
            ->when($request->boolean('overdue'), fn ($q) => $q->whereDate('due_date','<',today())->whereNotIn('status',['completed','cancelled']))
            ->orderByRaw('due_date IS NULL, due_date ASC')->paginate(15)->withQueryString();

        return view('obligations.index', compact('obligations'));
    }

    public function create(Request $request, Agreement $agreement): View
    {
        $this->ensureAgreementVisible($agreement,$request->user());

        return view('obligations.create', array_merge(
            ['agreement'=>$agreement],
            $this->assignmentOptions($request)
        ));
    }

    public function store(Request $request, Agreement $agreement): RedirectResponse
    {
        $this->ensureAgreementVisible($agreement,$request->user());
        $data = $this->validated($request);
        $this->enforceAssignmentScope($request,$agreement,$data);
        $data = $this->normalizeProgress($data);
        $data['created_by'] = $request->user()->id;
        $agreement->obligations()->create($data);
        return redirect()->route('agreements.show',$agreement)->with('success','Obligation / milestone added successfully.');
    }

    public function edit(Request $request, Obligation $obligation): View
    {
        $this->ensureVisible($obligation,$request->user());

        return view('obligations.edit', array_merge([
            'obligation'=>$obligation,
            'agreement'=>$obligation->agreement,
        ], $this->assignmentOptions($request)));
    }

    public function update(Request $request, Obligation $obligation): RedirectResponse
    {
        $this->ensureVisible($obligation,$request->user());
        $data = $this->validated($request);
        $this->enforceAssignmentScope($request,$obligation->agreement,$data);
        $data = $this->normalizeProgress($data);
        $obligation->update($data);
        return redirect()->route('agreements.show',$obligation->agreement_id)->with('success','Obligation / milestone updated.');
    }

    public function progress(Request $request, Obligation $obligation): RedirectResponse
    {
        $this->ensureVisible($obligation,$request->user());
        $data = $request->validate([
            'status'=>['required',Rule::in(['not_started','in_progress','blocked','completed','cancelled'])],
            'progress'=>['required','integer','min:0','max:100'],
            'completion_notes'=>['nullable','string','max:5000'],
        ]);
        $obligation->update($this->normalizeProgress($data));
        return back()->with('success','Progress updated successfully.');
    }

    public function destroy(Request $request, Obligation $obligation): RedirectResponse
    {
        $this->ensureVisible($obligation,$request->user());
        $agreement = $obligation->agreement_id;
        $obligation->delete();
        return redirect()->route('agreements.show',$agreement)->with('success','Obligation / milestone removed.');
    }

    private function assignmentOptions(Request $request): array
    {
        $user=$request->user();
        $departments=Department::where('is_active',true);
        $officers=User::where('is_active',true);

        if (! $this->isInstitutionWide($user)) {
            if ($user->department_id) {
                $departments->whereKey($user->department_id);
                $officers->where('department_id',$user->department_id);
            } else {
                $departments->whereRaw('1 = 0');
                $officers->whereKey($user->id);
            }
        }

        return [
            'departments'=>$departments->orderBy('name')->get(),
            'officers'=>$officers->orderBy('name')->get(),
        ];
    }

    private function enforceAssignmentScope(Request $request, Agreement $agreement, array $data): void
    {
        $user=$request->user();
        if ($this->isInstitutionWide($user)) return;

        if ($user->department_id) {
            if (! empty($data['department_id'])) abort_unless((int)$data['department_id'] === $user->department_id,403);
            abort_unless($agreement->department_id === $user->department_id || $agreement->responsible_officer_id === $user->id || $agreement->created_by === $user->id,403);
        } elseif (! empty($data['department_id'])) {
            abort(403);
        }

        if (! empty($data['responsible_officer_id'])) {
            $officer=User::whereKey($data['responsible_officer_id'])->where('is_active',true);
            if ($user->department_id) $officer->where('department_id',$user->department_id);
            else $officer->whereKey($user->id);
            abort_unless($officer->exists(),403);
        }
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'title'=>['required','string','max:255'], 'description'=>['nullable','string','max:5000'],
            'type'=>['required',Rule::in(['obligation','milestone','deliverable'])],
            'department_id'=>['nullable','exists:departments,id'], 'responsible_officer_id'=>['nullable','exists:users,id'],
            'due_date'=>['nullable','date'], 'priority'=>['required',Rule::in(['low','medium','high','critical'])],
            'status'=>['required',Rule::in(['not_started','in_progress','blocked','completed','cancelled'])],
            'progress'=>['required','integer','min:0','max:100'], 'completion_notes'=>['nullable','string','max:5000'],
        ]);
    }

    private function normalizeProgress(array $data): array
    {
        if (($data['status'] ?? null) === 'completed') {
            $data['progress'] = 100;
            $data['completed_at'] = now();
        } elseif (($data['status'] ?? null) !== 'completed') {
            $data['completed_at'] = null;
            if (($data['progress'] ?? 0) === 100) $data['progress'] = 99;
        }
        return $data;
    }
}
