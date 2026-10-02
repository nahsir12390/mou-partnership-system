<?php

namespace App\Http\Controllers;

use App\Models\Agreement;
use App\Models\Department;
use App\Models\Partner;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AgreementController extends Controller
{
    public function index(Request $request): View
    {
        $agreements = Agreement::with(['partner','department','responsibleOfficer'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search')->toString();
                $query->where(function ($q) use ($search) {
                    $q->where('title','like',"%{$search}%")->orWhere('reference_number','like',"%{$search}%")
                      ->orWhereHas('partner', fn($p) => $p->where('name','like',"%{$search}%"));
                });
            })
            ->when($request->filled('status'), fn($q) => $q->where('status',$request->status))
            ->when($request->filled('type'), fn($q) => $q->where('agreement_type',$request->type))
            ->latest()->paginate(10)->withQueryString();
        return view('agreements.index', compact('agreements'));
    }

    public function create(Request $request): View
    {
        return view('agreements.create', ['partners'=>Partner::orderBy('name')->get(),'departments'=>Department::where('is_active',true)->orderBy('name')->get(),'officers'=>User::where('is_active',true)->orderBy('name')->get(),'selectedPartner'=>$request->integer('partner')]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data=$this->validated($request); $data['created_by']=$request->user()->id; $agreement=Agreement::create($data);
        return redirect()->route('agreements.show',$agreement)->with('success','Agreement created successfully.');
    }

    public function show(Agreement $agreement): View
    {
        $agreement->load(['partner','department','responsibleOfficer','creator','approvalActions.user','obligations.department','obligations.responsibleOfficer','documents.uploader']);
        return view('agreements.show', compact('agreement'));
    }

    public function edit(Agreement $agreement): View
    {
        return view('agreements.edit',['agreement'=>$agreement,'partners'=>Partner::orderBy('name')->get(),'departments'=>Department::where('is_active',true)->orderBy('name')->get(),'officers'=>User::where('is_active',true)->orderBy('name')->get()]);
    }

    public function update(Request $request, Agreement $agreement): RedirectResponse
    {
        $agreement->update($this->validated($request,$agreement));
        return redirect()->route('agreements.show',$agreement)->with('success','Agreement updated successfully.');
    }

    private function validated(Request $request, ?Agreement $agreement=null): array
    {
        return $request->validate([
            'reference_number'=>['required','string','max:100',Rule::unique('agreements')->ignore($agreement)],'title'=>['required','string','max:255'],'partner_id'=>['required','exists:partners,id'],'department_id'=>['nullable','exists:departments,id'],'responsible_officer_id'=>['nullable','exists:users,id'],'agreement_type'=>['required','string','max:100'],'purpose'=>['nullable','string','max:5000'],'start_date'=>['nullable','date'],'expiry_date'=>['nullable','date','after_or_equal:start_date'],'status'=>['required',Rule::in(['draft','submitted','under_review','approved','awaiting_signature','active','expired','renewed','closed'])],'approval_stage'=>['nullable','string','max:100'],'renewal_status'=>['required',Rule::in(['not_due','due_soon','due','renewed','not_renewing'])],'notes'=>['nullable','string','max:5000'],
        ]);
    }
}