<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserManagementController extends Controller
{
    public function index(Request $request): View
    {
        $users = User::with(['role', 'department'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search')->toString();
                $query->where(fn ($q) => $q->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%")->orWhere('job_title', 'like', "%{$search}%"));
            })
            ->when($request->filled('role'), fn ($q) => $q->whereHas('role', fn ($r) => $r->where('slug', $request->role)))
            ->when($request->filled('status'), fn ($q) => $q->where('is_active', $request->status === 'active'))
            ->orderBy('name')->paginate(15)->withQueryString();

        return view('users.index', ['users' => $users, 'roles' => Role::orderBy('name')->get()]);
    }

    public function create(): View
    {
        return view('users.create', ['roles' => Role::orderBy('name')->get(), 'departments' => Department::where('is_active', true)->orderBy('name')->get()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['password'] = Hash::make($data['password']);
        $data['email_verified_at'] = now();
        User::create($data);

        return redirect()->route('users.index')->with('success', 'User account created successfully.');
    }

    public function edit(User $user): View
    {
        return view('users.edit', ['managedUser' => $user, 'roles' => Role::orderBy('name')->get(), 'departments' => Department::where('is_active', true)->orderBy('name')->get()]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $this->validated($request, $user);
        if (empty($data['password'])) {
            unset($data['password']);
        } else {
            $data['password'] = Hash::make($data['password']);
        }
        if ($request->user()->is($user) && array_key_exists('is_active', $data) && ! $data['is_active']) {
            return back()->withErrors(['is_active' => 'You cannot deactivate your own administrator account.']);
        }
        $user->update($data);

        return redirect()->route('users.index')->with('success', 'User access updated successfully.');
    }

    private function validated(Request $request, ?User $user = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user)],
            'job_title' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'role_id' => ['required', 'exists:roles,id'],
            'is_active' => ['required', 'boolean'],
            'password' => [$user ? 'nullable' : 'required', 'string', 'min:8', 'confirmed'],
        ]);
    }
}
