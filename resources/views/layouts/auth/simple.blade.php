<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>@include('partials.head')</head>
<body class="min-h-screen bg-zinc-50 antialiased dark:bg-zinc-950">
<div class="grid min-h-svh lg:grid-cols-[1.08fr_.92fr]">
    <section class="relative hidden overflow-hidden bg-zinc-950 p-10 text-white lg:flex lg:flex-col lg:justify-between xl:p-14">
        <div class="absolute -right-28 -top-28 size-96 rounded-full border border-white/10"></div>
        <div class="absolute -right-8 -top-8 size-64 rounded-full border border-white/10"></div>
        <div class="absolute bottom-0 left-0 h-40 w-full bg-gradient-to-t from-black/30 to-transparent"></div>
        <div class="relative z-10 flex items-center gap-3">
            <div class="flex size-11 items-center justify-center rounded-xl bg-white text-sm font-bold tracking-tight text-zinc-950">MP</div>
            <div><p class="font-semibold">MoU Partnership</p><p class="text-xs text-zinc-400">Management System</p></div>
        </div>
        <div class="relative z-10 max-w-xl">
            <p class="mb-4 text-xs font-semibold uppercase tracking-[.2em] text-zinc-400">Institutional Partnership Intelligence</p>
            <h1 class="text-4xl font-semibold leading-tight tracking-tight xl:text-5xl">Manage partnerships with clarity, accountability and control.</h1>
            <p class="mt-5 max-w-lg text-base leading-7 text-zinc-400">A centralized workspace for institutional partners, MoUs, approvals, obligations, documents, deadlines and management reporting.</p>
            <div class="mt-10 grid grid-cols-3 gap-3">
                <div class="rounded-xl border border-white/10 bg-white/5 p-4"><p class="text-sm font-semibold">Centralized</p><p class="mt-1 text-xs leading-5 text-zinc-400">One source of truth for agreements.</p></div>
                <div class="rounded-xl border border-white/10 bg-white/5 p-4"><p class="text-sm font-semibold">Accountable</p><p class="mt-1 text-xs leading-5 text-zinc-400">Clear ownership and approvals.</p></div>
                <div class="rounded-xl border border-white/10 bg-white/5 p-4"><p class="text-sm font-semibold">Proactive</p><p class="mt-1 text-xs leading-5 text-zinc-400">Track milestones and renewals.</p></div>
            </div>
        </div>
        <div class="relative z-10 flex items-center justify-between text-xs text-zinc-500"><span>Secure institutional workspace</span><span>Demonstration Environment</span></div>
    </section>

    <main class="flex min-h-svh items-center justify-center p-6 sm:p-10 lg:p-14">
        <div class="w-full max-w-md">
            <a href="{{ route('home') }}" class="mb-10 flex items-center gap-3 lg:hidden" wire:navigate><div class="flex size-10 items-center justify-center rounded-xl bg-zinc-950 text-sm font-bold text-white dark:bg-white dark:text-zinc-950">MP</div><div><p class="text-sm font-semibold text-zinc-950 dark:text-white">MoU Partnership</p><p class="text-xs text-zinc-500">Management System</p></div></a>
            <div class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm sm:p-8 dark:border-zinc-800 dark:bg-zinc-900">{{ $slot }}</div>
            <div class="mt-6 flex items-center justify-center gap-2 text-xs text-zinc-400"><svg viewBox="0 0 20 20" fill="currentColor" class="size-3.5"><path fill-rule="evenodd" d="M10 1.944A11.954 11.954 0 0 1 3 4.61V9c0 5.591 3.824 8.838 7 9.944C13.176 17.838 17 14.591 17 9V4.61a11.954 11.954 0 0 1-7-2.666ZM8.293 9.707a1 1 0 0 1 1.414-1.414L10 8.586l1.793-1.793a1 1 0 1 1 1.414 1.414l-2.5 2.5a1 1 0 0 1-1.414 0l-1-1Z" clip-rule="evenodd"/></svg><span>Protected access · Authorized users only</span></div>
        </div>
    </main>
</div>
@persist('toast')<flux:toast.group><flux:toast /></flux:toast.group>@endpersist
@fluxScripts
</body></html>