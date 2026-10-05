<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditController extends Controller
{
    public function index(Request $request): View
    {
        $events = ActivityLog::with(['actor', 'agreement'])
            ->when($request->filled('event'), fn ($query) => $query->where('event', $request->string('event')))
            ->when($request->filled('search'), function ($query) use ($request): void {
                $search = $request->string('search')->toString();
                $query->where(function ($scope) use ($search): void {
                    $scope->where('description', 'like', "%{$search}%")
                        ->orWhereHas('agreement', fn ($agreement) => $agreement->where('title', 'like', "%{$search}%")->orWhere('reference_number', 'like', "%{$search}%"))
                        ->orWhereHas('actor', fn ($actor) => $actor->where('name', 'like', "%{$search}%"));
                });
            })
            ->latest()
            ->paginate(30)
            ->withQueryString();

        return view('audit.index', compact('events'));
    }
}
