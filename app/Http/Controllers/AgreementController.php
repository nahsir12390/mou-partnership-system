<?php

namespace App\Http\Controllers;

use App\Models\Agreement;
use App\Models\Department;
use App\Models\Partner;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AgreementController extends Controller
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
        $agreements = $this->scopeVisible(Agreement::with(['partner', 'department', 'responsibleOfficer']), $request->user())
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search')->toString();
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")->orWhere('reference_number', 'like', "%{$search}%")->orWhereHas('partner', fn ($p) => $p->where('name', 'like', "%{$search}%"));
                });
            })->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))->when($request->filled('type'), fn ($q) => $q->where('agreement_type', $request->type))->latest()->paginate(10)->withQueryString();

        return view('agreements.index', compact('agreements'));
    }

    public function create(Request $request): View
    {
        $user = $request->user();
        $partners = Partner::query();
        if (! $this->isInstitutionWide($user)) {
            $partners->where(function (Builder $q) use ($user) {
                $q->where('created_by', $user->id)->orWhereHas('agreements', function (Builder $a) use ($user) {
                    $a->where(function (Builder $v) use ($user) {
                        if ($user->department_id) {
                            $v->where('department_id', $user->department_id);
                        } $v->orWhere('responsible_officer_id', $user->id)->orWhere('created_by', $user->id);
                    });
                });
            });
        }
        $departments = Department::where('is_active', true);
        $officers = User::where('is_active', true);
        if (! $this->isInstitutionWide($user) && $user->department_id) {
            $departments->whereKey($user->department_id);
            $officers->where('department_id', $user->department_id);
        }

        return view('agreements.create', ['partners' => $partners->orderBy('name')->get(), 'departments' => $departments->orderBy('name')->get(), 'officers' => $officers->orderBy('name')->get(), 'selectedPartner' => $request->integer('partner')]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $this->enforceAssignmentScope($request, $data);
        $data['created_by'] = $request->user()->id;
        $data['status'] = 'draft';
        $data['renewal_status'] = 'not_due';
        $data['approval_stage'] = null;
        $agreement = Agreement::create($data);

        return redirect()->route('agreements.show', $agreement)->with('success', 'Agreement created successfully.');
    }

    public function show(Request $request, Agreement $agreement): View
    {
        $this->ensureVisible($agreement, $request->user());
        $agreement->load(['partner', 'department', 'responsibleOfficer', 'creator', 'approvalActions.user', 'obligations.department', 'obligations.responsibleOfficer', 'documents.uploader', 'renewalRecords.recorder']);

        return view('agreements.show', compact('agreement'));
    }

    public function edit(Request $request, Agreement $agreement): View
    {
        $this->ensureVisible($agreement, $request->user());
        $user = $request->user();
        $partners = Partner::query();
        if (! $this->isInstitutionWide($user)) {
            $partners->where(function (Builder $q) use ($user) {
                $q->where('created_by', $user->id)->orWhereHas('agreements', fn (Builder $a) => $a->where(function (Builder $v) use ($user) {
                    if ($user->department_id) {
                        $v->where('department_id', $user->department_id);
                    } $v->orWhere('responsible_officer_id', $user->id)->orWhere('created_by', $user->id);
                }));
            });
        }
        $departments = Department::where('is_active', true);
        $officers = User::where('is_active', true);
        if (! $this->isInstitutionWide($user) && $user->department_id) {
            $departments->whereKey($user->department_id);
            $officers->where('department_id', $user->department_id);
        }

        return view('agreements.edit', ['agreement' => $agreement, 'partners' => $partners->orderBy('name')->get(), 'departments' => $departments->orderBy('name')->get(), 'officers' => $officers->orderBy('name')->get()]);
    }

    public function update(Request $request, Agreement $agreement): RedirectResponse
    {
        $this->ensureVisible($agreement, $request->user());
        $data = $this->validated($request, $agreement);
        $this->enforceAssignmentScope($request, $data);
        $agreement->update($data);

        return redirect()->route('agreements.show', $agreement)->with('success', 'Agreement updated successfully.');
    }

    private function enforceAssignmentScope(Request $request, array $data): void
    {
        $user = $request->user();
        if ($this->isInstitutionWide($user)) {
            return;
        }
        if ($user->department_id) {
            abort_unless((int) ($data['department_id'] ?? 0) === $user->department_id, 403);
        }
        if (! empty($data['responsible_officer_id'])) {
            abort_unless(User::whereKey($data['responsible_officer_id'])->where('department_id', $user->department_id)->exists(), 403);
        }
        abort_unless(Partner::whereKey($data['partner_id'])->where(function (Builder $q) use ($user) {
            $q->where('created_by', $user->id)->orWhereHas('agreements', function (Builder $a) use ($user) {
                $a->where(function (Builder $v) use ($user) {
                    if ($user->department_id) {
                        $v->where('department_id', $user->department_id);
                    }$v->orWhere('responsible_officer_id', $user->id)->orWhere('created_by', $user->id);
                });
            });
        })->exists(), 403);
    }

    private function validated(Request $request, ?Agreement $agreement = null): array
    {
        return $request->validate(['reference_number' => ['required', 'string', 'max:100', Rule::unique('agreements')->ignore($agreement)], 'title' => ['required', 'string', 'max:255'], 'partner_id' => ['required', 'exists:partners,id'], 'department_id' => ['nullable', 'exists:departments,id'], 'responsible_officer_id' => ['nullable', 'exists:users,id'], 'agreement_type' => ['required', 'string', 'max:100'], 'purpose' => ['nullable', 'string', 'max:5000'], 'start_date' => ['nullable', 'date'], 'expiry_date' => ['nullable', 'date', 'after_or_equal:start_date'], 'notes' => ['nullable', 'string', 'max:5000']]);
    }
}
