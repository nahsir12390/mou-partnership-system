<?php

namespace App\Http\Controllers;

use App\Models\ApprovalAction;
use App\Models\Document;
use App\Models\Obligation;
use Illuminate\View\View;

class AuditController extends Controller
{
    public function index(): View
    {
        $events = collect();

        ApprovalAction::with(['agreement.partner','user'])->latest()->take(75)->get()->each(function ($item) use ($events) {
            $events->push(['at'=>$item->created_at,'kind'=>'Approval','title'=>ucwords(str_replace('_',' ',$item->action)),'detail'=>$item->agreement->reference_number.' · '.$item->agreement->title,'actor'=>$item->user->name,'agreement'=>$item->agreement,'note'=>$item->comment]);
        });
        Document::with(['agreement.partner','uploader'])->latest()->take(75)->get()->each(function ($item) use ($events) {
            $events->push(['at'=>$item->created_at,'kind'=>'Document','title'=>'Document uploaded: '.$item->title,'detail'=>$item->agreement->reference_number.' · Version '.$item->version,'actor'=>$item->uploader->name,'agreement'=>$item->agreement,'note'=>$item->original_name]);
        });
        Obligation::with(['agreement.partner','creator'])->latest()->take(75)->get()->each(function ($item) use ($events) {
            $events->push(['at'=>$item->created_at,'kind'=>'Commitment','title'=>ucfirst($item->type).' recorded: '.$item->title,'detail'=>$item->agreement->reference_number.' · '.ucwords(str_replace('_',' ',$item->status)),'actor'=>$item->creator->name,'agreement'=>$item->agreement,'note'=>$item->due_date ? 'Due '.$item->due_date->format('d M Y') : null]);
        });

        $events = $events->sortByDesc('at')->take(100)->values();
        return view('audit.index', compact('events'));
    }
}