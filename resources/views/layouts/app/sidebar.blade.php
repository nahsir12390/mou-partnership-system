<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>@include('partials.head')</head>
<body class="min-h-screen bg-[#f5f7fa]">
<flux:sidebar sticky collapsible="mobile" class="nsuk-sidebar">
    <flux:sidebar.header>
        <a href="{{ route('dashboard') }}" class="flex items-center gap-3 rounded-xl px-1 py-2" wire:navigate>
            <img src="https://ug.nsuk.edu.ng/api/global/logo" alt="NSUK crest" class="nsuk-crest size-11 shrink-0">
            <div class="min-w-0"><p class="truncate text-sm font-semibold text-white">NSUK Partnership</p><p class="truncate text-[11px] text-white/60">MoU Management System</p></div>
        </a>
        <flux:sidebar.collapse class="lg:hidden"/>
    </flux:sidebar.header>

    <div class="mx-3 mb-4 rounded-xl border border-white/10 bg-white/10 p-3">
        <div class="flex items-center gap-2"><span class="size-2 rounded-full bg-[#23d9a0]"></span><span class="text-xs font-medium text-white">System operational</span></div>
        <p class="mt-1 text-[11px] text-white/55">Nasarawa State University, Keffi</p>
    </div>

    <flux:sidebar.nav>
        @can('dashboard.view')
        <flux:sidebar.group :heading="__('Overview')" class="grid">
            <flux:sidebar.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>Executive Dashboard</flux:sidebar.item>
            @can('agreements.view')<flux:sidebar.item icon="magnifying-glass" :href="route('search.index')" :current="request()->routeIs('search.*')" wire:navigate>Global Search</flux:sidebar.item>@endcan
            @can('alerts.view')<flux:sidebar.item icon="bell-alert" :href="route('alerts.index')" :current="request()->routeIs('alerts.*')" wire:navigate>Operational Alerts</flux:sidebar.item>@endcan
        </flux:sidebar.group>
        @endcan

        <flux:sidebar.group :heading="__('Partnership Management')" class="grid">
            @can('partners.view')<flux:sidebar.item icon="building-office" :href="route('partners.index')" :current="request()->routeIs('partners.*')" wire:navigate>Partner Registry</flux:sidebar.item>@endcan
            @can('agreements.view')<flux:sidebar.item icon="document-text" :href="route('agreements.index')" :current="request()->routeIs('agreements.*') || request()->routeIs('renewals.*')" wire:navigate>MoUs & Agreements</flux:sidebar.item>@endcan
        </flux:sidebar.group>

        <flux:sidebar.group :heading="__('Workflow & Delivery')" class="grid">
            @can('approvals.view')<flux:sidebar.item icon="check-circle" :href="route('approvals.index')" :current="request()->routeIs('approvals.*')" wire:navigate>Approval Queue</flux:sidebar.item>@endcan
            @can('obligations.view')<flux:sidebar.item icon="clipboard-document-check" :href="route('obligations.index')" :current="request()->routeIs('obligations.*')" wire:navigate>Obligations & Milestones</flux:sidebar.item>@endcan
            @can('documents.view')<flux:sidebar.item icon="folder" :href="route('documents.index')" :current="request()->routeIs('documents.*')" wire:navigate>Document Repository</flux:sidebar.item>@endcan
        </flux:sidebar.group>

        @if(auth()->user()->hasPermission('reports.view') || auth()->user()->hasPermission('audit.view'))
        <flux:sidebar.group :heading="__('Monitoring & Intelligence')" class="grid">
            @can('reports.view')<flux:sidebar.item icon="chart-bar" :href="route('reports.index')" :current="request()->routeIs('reports.*')" wire:navigate>Reports & Renewals</flux:sidebar.item>@endcan
            @can('audit.view')<flux:sidebar.item icon="clock" :href="route('audit.index')" :current="request()->routeIs('audit.*')" wire:navigate>Audit Trail</flux:sidebar.item>@endcan
        </flux:sidebar.group>
        @endif

        <flux:sidebar.group :heading="__('Administration')" class="grid">
            @can('users.manage')<flux:sidebar.item icon="users" :href="route('users.index')" :current="request()->routeIs('users.*')" wire:navigate>Users & Access</flux:sidebar.item>@endcan
            <flux:sidebar.item icon="cog-6-tooth" :href="route('profile.edit')" :current="request()->routeIs('profile.*')" wire:navigate>Settings</flux:sidebar.item>
        </flux:sidebar.group>
    </flux:sidebar.nav>

    <flux:spacer/>
    <div class="mx-3 mb-3 rounded-xl border border-white/10 bg-white/8 p-3"><p class="text-[11px] font-semibold uppercase tracking-wider text-[#86f2d1]">Knowledge for development</p><p class="mt-1 text-xs text-white/55">Institutional partnership workspace</p></div>
    <x-desktop-user-menu class="hidden lg:block" :name="auth()->user()->name"/>
</flux:sidebar>

<flux:header class="border-b border-emerald-900/10 bg-white/95 shadow-sm backdrop-blur lg:hidden">
    <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left"/>
    <img src="https://ug.nsuk.edu.ng/api/global/logo" alt="NSUK" class="ms-2 size-8 rounded-full">
    <span class="ms-2 text-sm font-semibold text-[#016b4b]">NSUK Partnership</span>
    <flux:spacer/>
    <flux:dropdown position="top" align="end"><flux:profile :initials="auth()->user()->initials()" icon-trailing="chevron-down"/><flux:menu><div class="p-2"><p class="font-medium">{{ auth()->user()->name }}</p><p class="text-xs text-zinc-500">{{ auth()->user()->email }}</p></div><flux:menu.separator/><flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>Settings</flux:menu.item><form method="POST" action="{{ route('logout') }}">@csrf<flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle">Log out</flux:menu.item></form></flux:menu></flux:dropdown>
</flux:header>

{{ $slot }}
@persist('toast')<flux:toast.group><flux:toast/></flux:toast.group>@endpersist
@fluxScripts
</body>
</html>
