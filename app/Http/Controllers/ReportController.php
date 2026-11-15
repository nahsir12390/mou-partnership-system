<?php

namespace App\Http\Controllers;

use App\Models\Agreement;
use App\Models\Department;
use App\Models\Document;
use App\Models\Obligation;
use App\Models\Partner;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $this->filters($request);
        $agreements = $this->filteredAgreements($filters);
        $agreementIds = (clone $agreements)->select('id');
        $obligations = Obligation::query()->whereIn('agreement_id', $agreementIds);

        $statusCounts = (clone $agreements)->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');
        $departmentCounts = Department::query()->withCount(['agreements' => fn (Builder $query) => $this->applyFilters($query, $filters)])->orderByDesc('agreements_count')->take(8)->get();
        $metrics = [
            'partners' => Partner::whereIn('id', (clone $agreements)->select('partner_id'))->count(),
            'agreements' => (clone $agreements)->count(),
            'active' => (clone $agreements)->where('status', 'active')->count(),
            'review' => (clone $agreements)->whereIn('status', ['submitted', 'under_review'])->count(),
            'documents' => Document::whereIn('agreement_id', (clone $agreements)->select('id'))->count(),
            'obligations' => (clone $obligations)->count(),
            'completed_obligations' => (clone $obligations)->where('status', 'completed')->count(),
            'overdue_obligations' => (clone $obligations)->whereNotNull('due_date')->whereDate('due_date', '<', today())->whereNotIn('status', ['completed', 'cancelled'])->count(),
        ];
        $renewals = (clone $agreements)->with(['partner', 'department'])->whereNotNull('expiry_date')->whereDate('expiry_date', '>=', today())->whereDate('expiry_date', '<=', today()->addDays(180))->orderBy('expiry_date')->get();
        $overdue = (clone $obligations)->with(['agreement.partner', 'responsibleOfficer', 'department'])->whereNotNull('due_date')->whereDate('due_date', '<', today())->whereNotIn('status', ['completed', 'cancelled'])->orderBy('due_date')->take(12)->get();
        $recentApprovals = (clone $agreements)->with(['partner'])->whereIn('status', ['approved', 'active'])->latest('updated_at')->take(8)->get();

        return view('reports.index', compact('metrics', 'statusCounts', 'departmentCounts', 'renewals', 'overdue', 'recentApprovals', 'filters'));
    }

    public function export(Request $request, string $type): StreamedResponse
    {
        abort_unless(in_array($type, ['agreements', 'renewals', 'obligations'], true), 404);
        $filters = $this->filters($request);
        $filename = 'mou-'.$type.'-report-'.now()->format('Y-m-d').'.csv';

        return response()->streamDownload(function () use ($type, $filters) {
            $out = fopen('php://output', 'w');
            $agreements = $this->filteredAgreements($filters);
            if ($type === 'agreements') {
                fputcsv($out, ['Reference', 'Agreement', 'Partner', 'Department', 'Status', 'Effective Date', 'Expiry Date']);
                $agreements->with(['partner', 'department'])->orderBy('reference_number')->chunk(200, function ($rows) use ($out): void {
                    foreach ($rows as $row) {
                        fputcsv($out, [$row->reference_number, $row->title, $row->partner?->name, $row->department?->name, ucwords(str_replace('_', ' ', $row->status)), $row->start_date?->format('Y-m-d'), $row->expiry_date?->format('Y-m-d')]);
                    }
                });
            } elseif ($type === 'renewals') {
                fputcsv($out, ['Reference', 'Agreement', 'Partner', 'Department', 'Expiry Date', 'Renewal Status']);
                $agreements->with(['partner', 'department'])->whereNotNull('expiry_date')->orderBy('expiry_date')->chunk(200, function ($rows) use ($out): void {
                    foreach ($rows as $row) {
                        fputcsv($out, [$row->reference_number, $row->title, $row->partner?->name, $row->department?->name, $row->expiry_date?->format('Y-m-d'), ucwords(str_replace('_', ' ', $row->renewal_status ?? 'not_due'))]);
                    }
                });
            } else {
                fputcsv($out, ['Agreement', 'Obligation', 'Department', 'Responsible Officer', 'Due Date', 'Priority', 'Status', 'Progress']);
                Obligation::with(['agreement', 'department', 'responsibleOfficer'])->whereIn('agreement_id', $agreements->select('id'))->orderBy('due_date')->chunk(200, function ($rows) use ($out): void {
                    foreach ($rows as $row) {
                        fputcsv($out, [$row->agreement?->reference_number, $row->title, $row->department?->name, $row->responsibleOfficer?->name, $row->due_date?->format('Y-m-d'), ucfirst($row->priority), ucwords(str_replace('_', ' ', $row->status)), $row->progress.'%']);
                    }
                });
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function filters(Request $request): array
    {
        return $request->validate([
            'department' => ['nullable', 'integer', 'exists:departments,id'],
            'partner' => ['nullable', 'integer', 'exists:partners,id'],
            'status' => ['nullable', 'string', 'in:draft,submitted,under_review,approved,awaiting_signature,active,expired,renewed,closed,terminated'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
        ]);
    }

    private function filteredAgreements(array $filters): Builder
    {
        return $this->applyFilters(Agreement::query(), $filters);
    }

    private function applyFilters(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['department'] ?? null, fn (Builder $builder, $id) => $builder->where('department_id', $id))
            ->when($filters['partner'] ?? null, fn (Builder $builder, $id) => $builder->where('partner_id', $id))
            ->when($filters['status'] ?? null, fn (Builder $builder, $status) => $builder->where('status', $status))
            ->when($filters['date_from'] ?? null, fn (Builder $builder, $date) => $builder->whereDate('expiry_date', '>=', $date))
            ->when($filters['date_to'] ?? null, fn (Builder $builder, $date) => $builder->whereDate('expiry_date', '<=', $date));
    }
}
