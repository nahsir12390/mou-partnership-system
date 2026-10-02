<?php

namespace App\Http\Controllers;

use App\Models\Agreement;
use App\Models\Department;
use App\Models\Document;
use App\Models\Obligation;
use App\Models\Partner;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        $agreements = Agreement::query();
        $statusCounts = Agreement::selectRaw('status, count(*) as total')->groupBy('status')->pluck('total','status');
        $departmentCounts = Department::withCount('agreements')->orderByDesc('agreements_count')->take(8)->get();

        $metrics = [
            'partners' => Partner::count(),
            'agreements' => Agreement::count(),
            'active' => Agreement::where('status','active')->count(),
            'review' => Agreement::whereIn('status',['submitted','under_review'])->count(),
            'documents' => Document::count(),
            'obligations' => Obligation::count(),
            'completed_obligations' => Obligation::where('status','completed')->count(),
            'overdue_obligations' => Obligation::whereNotNull('due_date')->whereDate('due_date','<',today())->whereNotIn('status',['completed','cancelled'])->count(),
        ];

        $renewals = Agreement::with(['partner','department'])
            ->whereNotNull('expiry_date')->whereDate('expiry_date','>=',today())
            ->whereDate('expiry_date','<=',today()->addDays(180))->orderBy('expiry_date')->get();

        $overdue = Obligation::with(['agreement.partner','responsibleOfficer','department'])
            ->whereNotNull('due_date')->whereDate('due_date','<',today())
            ->whereNotIn('status',['completed','cancelled'])->orderBy('due_date')->take(12)->get();

        $recentApprovals = Agreement::with(['partner'])->whereIn('status',['approved','active'])->latest('updated_at')->take(8)->get();

        return view('reports.index', compact('metrics','statusCounts','departmentCounts','renewals','overdue','recentApprovals'));
    }
}