<?php

namespace App\Http\Controllers;

use App\Models\Agreement;
use App\Models\Department;
use App\Models\Obligation;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ObligationController extends Controller
{
    public function index(Request $request): View
    {
        $obligations = Obligation::with(['agreement.partner','department','responsibleOfficer'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('type'), fn ($q) => $q->where('type', $request->type))
            ->when($request->boolean('overdue'), fn ($q) => $q->whereDate('due_date','<',today())->whereNotIn('status',['completed','cancelled']))
            ->orderByRaw('due_date IS NULL, due_date ASC')->paginate(15)->withQueryString();

        return view('obligations.index', compact('obligations'));
    }

    public function create(Agreement $agreement): View
    {
        return view('obligations.create', [
            'agreement'=>$agreement,
            'departments'=>Department::where('is_active',true)->orderBy('name')->get(),
            'officers'=>User::where('is_active',true)->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request, Agreement $agreement): RedirectResponse
    {
        $data = $this->validated($request);
        $data['created_by'] = $request->user()->id;
        $agreement->obligations()->create($data);
        return redirect()->route('agreements.show',$agreement)->with('success','Obligation / milestone added successfully.');
    }

    public function edit(Obligation $obligation): View
    {
        return view('obligations.edit', [
            'obligation'=>$obligation,
            'agreement'=>$obligation->agreement,
            'departments'=>Department::where('is_active',true)->orderBy('name')->get(),
            'officers'=>User::where('is_active',true)->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Obligation $obligation): RedirectResponse
    {
        $data = $this->validated($request);
        $data = $this->normalizeProgress($data);
        $obligation->update($data);
        return redirect()->route('agreements.show',$obligation->agreement_id)->with('success','Obligation / milestone updated.');
    }

    public function progress(Request $request, Obligation $obligation): RedirectResponse
    {
        $data = $request->validate([
            'status'=>['required',Rule::in(['not_started','in_progress','blocked','completed','cancelled'])],
            'progress'=>['required','integer','min:0','max:100'],
            'completion_notes'=>['nullable','string','max:5000'],
        ]);
        $obligation->update($this->normalizeProgress($data));
        return back()->with('success','Progress updated successfully.');
    }

    public function destroy(Obligation $obligation): RedirectResponse
    {
        $agreement = $obligation->agreement_id;
        $obligation->delete();
        return redirect()->route('agreements.show',$agreement)->with('success','Obligation / milestone removed.');
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