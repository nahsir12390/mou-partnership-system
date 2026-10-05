<?php

namespace App\Http\Controllers;

use App\Models\Partner;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PartnerController extends Controller
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
            $scope->where('created_by', $user->id)
                ->orWhereHas('agreements', function (Builder $agreements) use ($user) {
                    $agreements->where(function (Builder $visible) use ($user) {
                        if ($user->department_id) {
                            $visible->where('department_id', $user->department_id);
                        }
                        $visible->orWhere('responsible_officer_id', $user->id)
                            ->orWhere('created_by', $user->id);
                    });
                });
        });
    }

    private function ensureVisible(Partner $partner, $user): void
    {
        if ($this->isInstitutionWide($user)) {
            return;
        }

        $visible = $partner->created_by === $user->id || $partner->agreements()
            ->where(function (Builder $query) use ($user) {
                if ($user->department_id) {
                    $query->where('department_id', $user->department_id);
                }
                $query->orWhere('responsible_officer_id', $user->id)->orWhere('created_by', $user->id);
            })->exists();

        abort_unless($visible, 403);
    }

    public function index(Request $request): View
    {
        $partners = $this->scopeVisible(Partner::query(), $request->user())
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search')->toString();
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('category', 'like', "%{$search}%")
                        ->orWhere('country', 'like', "%{$search}%")
                        ->orWhere('contact_name', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('category'), fn ($query) => $query->where('category', $request->category))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->status))
            ->latest()->paginate(10)->withQueryString();

        $categories = $this->scopeVisible(Partner::query(), $request->user())
            ->whereNotNull('category')->distinct()->orderBy('category')->pluck('category');

        return view('partners.index', compact('partners', 'categories'));
    }

    public function create(): View
    {
        return view('partners.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['created_by'] = $request->user()->id;
        $partner = Partner::create($data);

        return redirect()->route('partners.show', $partner)->with('success', 'Partner added successfully.');
    }

    public function show(Request $request, Partner $partner): View
    {
        $this->ensureVisible($partner, $request->user());

        return view('partners.show', compact('partner'));
    }

    public function edit(Request $request, Partner $partner): View
    {
        $this->ensureVisible($partner, $request->user());

        return view('partners.edit', compact('partner'));
    }

    public function update(Request $request, Partner $partner): RedirectResponse
    {
        $this->ensureVisible($partner, $request->user());
        $partner->update($this->validated($request));

        return redirect()->route('partners.show', $partner)->with('success', 'Partner updated successfully.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'], 'category' => ['nullable', 'string', 'max:100'], 'country' => ['nullable', 'string', 'max:100'], 'state' => ['nullable', 'string', 'max:100'], 'city' => ['nullable', 'string', 'max:100'], 'address' => ['nullable', 'string', 'max:1000'], 'website' => ['nullable', 'url', 'max:255'], 'contact_name' => ['nullable', 'string', 'max:255'], 'contact_email' => ['nullable', 'email', 'max:255'], 'contact_phone' => ['nullable', 'string', 'max:50'], 'status' => ['required', 'in:active,inactive,prospective'], 'notes' => ['nullable', 'string', 'max:3000'],
        ]);
    }
}
