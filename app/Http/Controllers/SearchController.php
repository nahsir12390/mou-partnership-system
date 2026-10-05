<?php

namespace App\Http\Controllers;

use App\Models\Agreement;
use App\Models\Document;
use App\Models\Obligation;
use App\Models\Partner;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SearchController extends Controller
{
    public function index(Request $request): View
    {
        $term = trim((string) $request->query('q', ''));
        $agreements = collect();
        $partners = collect();
        $obligations = collect();
        $documents = collect();

        if (mb_strlen($term) >= 2) {
            $like = '%'.$term.'%';
            $user = $request->user();
            $departmentId = $user->department_id;
            $isInstitutionWide = $user->hasRole('system-administrator', 'management', 'legal-review-officer');

            $agreementScope = function (Builder $query) use ($isInstitutionWide, $departmentId, $user): void {
                if ($isInstitutionWide) {
                    return;
                }

                $query->where(function (Builder $scope) use ($departmentId, $user): void {
                    if ($departmentId) {
                        $scope->where('department_id', $departmentId);
                    }
                    $scope->orWhere('responsible_officer_id', $user->id)
                        ->orWhere('created_by', $user->id);
                });
            };

            $agreements = Agreement::with(['partner', 'department'])
                ->where(fn ($q) => $q->where('title', 'like', $like)->orWhere('reference_number', 'like', $like)->orWhere('purpose', 'like', $like))
                ->where($agreementScope)
                ->latest()->limit(15)->get();

            $visibleAgreementIds = Agreement::query()->where($agreementScope)->select('id');

            $partners = Partner::query()
                ->where(fn ($q) => $q->where('name', 'like', $like)->orWhere('contact_name', 'like', $like)->orWhere('contact_email', 'like', $like))
                ->when(! $isInstitutionWide, fn ($q) => $q->whereHas('agreements', fn ($a) => $a->where($agreementScope)))
                ->orderBy('name')->limit(15)->get();

            $obligations = Obligation::with('agreement')
                ->where(fn ($q) => $q->where('title', 'like', $like)->orWhere('description', 'like', $like))
                ->whereIn('agreement_id', clone $visibleAgreementIds)
                ->latest()->limit(15)->get();

            $documents = Document::with('agreement')
                ->where(fn ($q) => $q->where('title', 'like', $like)->orWhere('original_name', 'like', $like))
                ->whereIn('agreement_id', clone $visibleAgreementIds)
                ->latest()->limit(15)->get();
        }

        return view('search.index', compact('term','agreements','partners','obligations','documents'));
    }
}
