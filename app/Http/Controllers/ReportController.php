<?php

namespace App\Http\Controllers;

use App\Models\Agreement;
use App\Models\Department;
use App\Models\Document;
use App\Models\Obligation;
use App\Models\Partner;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        $statusCounts = Agreement::selectRaw('status, count(*) as total')->groupBy('status')->pluck('total','status');
        $departmentCounts = Department::withCount('agreements')->orderByDesc('agreements_count')->take(8)->get();

        $metrics = [
            'partners' => Partner::count(), 'agreements' => Agreement::count(),
            'active' => Agreement::where('status','active')->count(),
            'review' => Agreement::whereIn('status',['submitted','under_review'])->count(),
            'documents' => Document::count(), 'obligations' => Obligation::count(),
            'completed_obligations' => Obligation::where('status','completed')->count(),
            'overdue_obligations' => Obligation::whereNotNull('due_date')->whereDate('due_date','<',today())->whereNotIn('status',['completed','cancelled'])->count(),
        ];

        $renewals = Agreement::with(['partner','department'])->whereNotNull('expiry_date')->whereDate('expiry_date','>=',today())->whereDate('expiry_date','<=',today()->addDays(180))->orderBy('expiry_date')->get();
        $overdue = Obligation::with(['agreement.partner','responsibleOfficer','department'])->whereNotNull('due_date')->whereDate('due_date','<',today())->whereNotIn('status',['completed','cancelled'])->orderBy('due_date')->take(12)->get();
        $recentApprovals = Agreement::with(['partner'])->whereIn('status',['approved','active'])->latest('updated_at')->take(8)->get();

        return view('reports.index', compact('metrics','statusCounts','departmentCounts','renewals','overdue','recentApprovals'));
    }

    public function export(Request $request, string $type): StreamedResponse
    {
        abort_unless(in_array($type, ['agreements','renewals','obligations'], true), 404);
        $filename = 'mou-'.$type.'-report-'.now()->format('Y-m-d').'.csv';

        return response()->streamDownload(function () use ($type) {
            $out = fopen('php://output', 'w');
            if ($type === 'agreements') {
                fputcsv($out, ['Reference','Agreement','Partner','Department','Status','Effective Date','Expiry Date']);
                Agreement::with(['partner','department'])->orderBy('reference_number')->chunk(200, function ($rows) use ($out) {
                    foreach ($rows as $row) fputcsv($out, [$row->reference_number,$row->title,$row->partner?->name,$row->department?->name,ucwords(str_replace('_',' ',$row->status)),$row->effective_date?->format('Y-m-d'),$row->expiry_date?->format('Y-m-d')]);
                });
            } elseif ($type === 'renewals') {
                fputcsv($out, ['Reference','Agreement','Partner','Department','Expiry Date','Renewal Status']);
                Agreement::with(['partner','department'])->whereNotNull('expiry_date')->orderBy('expiry_date')->chunk(200, function ($rows) use ($out) {
                    foreach ($rows as $row) fputcsv($out, [$row->reference_number,$row->title,$row->partner?->name,$row->department?->name,$row->expiry_date?->format('Y-m-d'),ucwords(str_replace('_',' ',$row->renewal_status ?? 'not_due'))]);
                });
            } else {
                fputcsv($out, ['Agreement','Obligation','Department','Responsible Officer','Due Date','Priority','Status','Progress']);
                Obligation::with(['agreement','department','responsibleOfficer'])->orderBy('due_date')->chunk(200, function ($rows) use ($out) {
                    foreach ($rows as $row) fputcsv($out, [$row->agreement?->reference_number,$row->title,$row->department?->name,$row->responsibleOfficer?->name,$row->due_date?->format('Y-m-d'),ucfirst($row->priority),ucwords(str_replace('_',' ',$row->status)),$row->progress.'%']);
                });
            }
            fclose($out);
        }, $filename, ['Content-Type'=>'text/csv; charset=UTF-8']);
    }
}
