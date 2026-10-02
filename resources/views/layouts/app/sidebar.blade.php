<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>@include('partials.head')</head>
<body class="min-h-screen bg-zinc-50 dark:bg-zinc-950">
<flux:sidebar sticky collapsible="mobile" class="border-e border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
    <flux:sidebar.header>
        <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-1 py-2" wire:navigate>
            <div class="flex size-10 items-center justify-center rounded-xl bg-zinc-950 text-sm font-bold tracking-tight text-white shadow-sm dark:bg-white dark:text-zinc-950">MP</div>
            <div class="min-w-0"><p class="truncate text-sm font-semibold text-zinc-950 dark:text-white">MoU Partnership</p><p class="truncate text-xs text-zinc-500">Management System</p></div>
        </a>
        <flux:sidebar.collapse class="lg:hidden" />
    </flux:sidebar.header>

    <div class="mx-3 mb-3 rounded-xl border border-zinc-200 bg-zinc-50 p-3 dark:border-zinc-800 dark:bg-zinc-950/60">
        <div class="flex items-center gap-2"><span class="size-2 rounded-full bg-emerald-500"></span><span class="text-xs font-medium text-zinc-700 dark:text-zinc-200">System operational</span></div>
        <p class="mt-1 text-[11px] leading-4 text-zinc-500">Secure institutional partnership workspace</p>
    </div>

    <flux:sidebar.nav>
        @can('dashboard.view')
        <flux:sidebar.group :heading="__('Overview')" class="grid">
            <flux:sidebar.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>{{ __('Executive Dashboard') }}</flux:sidebar.item>
        </flux:sidebar.group>
        @endcan

        <flux:sidebar.group :heading="__('Partnership Management')" class="grid">
            @can('partners.view')<flux:sidebar.item icon="building-office" :href="route('partners.index')" :current="request()->routeIs('partners.*')" wire:navigate>{{ __('Partner Registry') }}</flux:sidebar.item>@endcan
            @can('agreements.view')<flux:sidebar.item icon="document-text" :href="route('agreements.index')" :current="request()->routeIs('agreements.*')" wire:navigate>{{ __('MoUs & Agreements') }}</flux:sidebar.item>@endcan
        </flux:sidebar.group>

        <flux:sidebar.group :heading="__('Workspace')" class="grid">
            @can('approvals.view')<div class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm text-zinc-400 dark:text-zinc-600"><flux:icon name="check-circle" class="size-4"/><span>Approvals</span><span class="ms-auto rounded-full bg-zinc-100 px-2 py-0.5 text-[10px] font-medium text-zinc-400 dark:bg-zinc-800">Next phase</span></div>@endcan
            @can('obligations.view')<div class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm text-zinc-400 dark:text-zinc-600"><flux:icon name="clipboard-document-check" class="size-4"/><span>Obligations</span><span class="ms-auto rounded-full bg-zinc-100 px-2 py-0.5 text-[10px] font-medium text-zinc-400 dark:bg-zinc-800">Next phase</span></div>@endcan
            @can('documents.view')<div class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm text-zinc-400 dark:text-zinc-600"><flux:icon name="folder" class="size-4"/><span>Documents</span><span class="ms-auto rounded-full bg-zinc-100 px-2 py-0.5 text-[10px] font-medium text-zinc-400 dark:bg-zinc-800">Next phase</span></div>@endcan
        </flux:sidebar.group>

        @if(auth()->user()->hasPermission('reports.view') || auth()->user()->hasPermission('audit.view'))
        <flux:sidebar.group :heading="__('Monitoring & Intelligence')" class="grid">
            @can('reports.view')<div class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm text-zinc-400 dark:text-zinc-600"><flux:icon name="calendar-days" class="size-4"/><span>Deadlines & Renewals</span><span class="ms-auto rounded-full bg-zinc-100 px-2 py-0.5 text-[10px] font-medium text-zinc-400 dark:bg-zinc-800">Planned</span></div><div class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm text-zinc-400 dark:text-zinc-600"><flux:icon name="chart-bar" class="size-4"/><span>Reports & Analytics</span><span class="ms-auto rounded-full bg-zinc-100 px-2 py-0.5 text-[10px] font-medium text-zinc-400 dark:bg-zinc-800">Planned</span></div>@endcan
            @can('audit.view')<div class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm text-zinc-400 dark:text-zinc-600"><flux:icon name="clock" class="size-4"/><span>Audit Trail</span><span class="ms-auto rounded-full bg-zinc-100 px-2 py-0.5 text-[10px] font-medium text-zinc-400 dark:bg-zinc-800">Planned</span></div>@endcan
        </flux:sidebar.group>
        @endif

        <flux:sidebar.group :heading="__('Account')" class="grid">
            @can('users.manage')<div class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm text-zinc-400 dark:text-zinc-600"><flux:icon name="users" class="size-4"/><span>Users & Access</span><span class="ms-auto rounded-full bg-zinc-100 px-2 py-0.5 text-[10px] font-medium text-zinc-400 dark:bg-zinc-800">Planned</span></div>@endcan
            <flux:sidebar.item icon="cog-6-tooth" :href="route('profile.edit')" :current="request()->routeIs('profile.*')" wire:navigate>{{ __('Settings') }}</flux:sidebar.item>
        </flux:sidebar.group>
    </flux:sidebar.nav>

    <flux:spacer />
    <div class="mx-3 mb-3 rounded-xl border border-amber-200 bg-amber-50 p-3 dark:border-amber-900/60 dark:bg-amber-950/30">
        <p class="text-[11px] font-semibold uppercase tracking-wider text-amber-700 dark:text-amber-400">Demonstration Environment</p>
        <p class="mt-1 text-xs leading-5 text-amber-700/80 dark:text-amber-300/70">Prototype data is used for product demonstration and evaluation.</p>
    </div>
    <x-desktop-user-menu class="hidden lg:block" :name="auth()->user()->name" />
</flux:sidebar>

<flux:header class="border-b border-zinc-200 bg-white/90 backdrop-blur lg:hidden dark:border-zinc-800 dark:bg-zinc-900/90">
    <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" /><span class="ms-2 text-sm font-semibold">MoU Partnership</span><flux:spacer />
    <flux:dropdown position="top" align="end"><flux:profile :initials="auth()->user()->initials()" icon-trailing="chevron-down" /><flux:menu><div class="p-2"><div class="flex items-center gap-2"><flux:avatar :name="auth()->user()->name" :initials="auth()->user()->initials()" /><div class="min-w-0"><flux:heading class="truncate">{{ auth()->user()->name }}</flux:heading><flux:text class="truncate">{{ auth()->user()->email }}</flux:text>@if(auth()->user()->role)<flux:text class="truncate text-xs">{{ auth()->user()->role->name }}</flux:text>@endif</div></div></div><flux:menu.separator /><flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>{{ __('Settings') }}</flux:menu.item><flux:menu.separator /><form method="POST" action="{{ route('logout') }}" class="w-full">@csrf<flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full cursor-pointer">{{ __('Log out') }}</flux:menu.item></form></flux:menu></flux:dropdown>
</flux:header>
{{ $slot }}
@persist('toast')<flux:toast.group><flux:toast /></flux:toast.group>@endpersist
@fluxScripts
</body></html>