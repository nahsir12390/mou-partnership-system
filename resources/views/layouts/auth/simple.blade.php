<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>@include('partials.head')</head>
<body class="min-h-screen bg-[#f5f7fa] antialiased">
<div class="grid min-h-svh lg:grid-cols-[1.1fr_.9fr]">
    <main class="flex min-h-svh items-center justify-center p-6 sm:p-10 lg:p-14">
        <div class="w-full max-w-[500px]">
            <a href="{{ route('home') }}" class="mb-8 flex items-center gap-3 lg:hidden" wire:navigate><img src="https://ug.nsuk.edu.ng/api/global/logo" alt="NSUK crest" class="nsuk-crest size-12"><div><p class="font-semibold text-[#016b4b]">Nasarawa State University, Keffi</p><p class="text-xs text-zinc-500">MoU & Partnership Management System</p></div></a>
            <div class="nsuk-card rounded-2xl border border-zinc-200 bg-white p-7 shadow-[0_10px_30px_rgba(0,0,0,.10)] sm:p-10">{{ $slot }}</div>
            <div class="mt-6 flex items-center justify-center gap-2 text-xs text-zinc-500"><flux:icon name="shield-check" class="size-4 text-[#016b4b]"/><span>Protected access · Authorized NSUK users only</span></div>
        </div>
    </main>

    <section class="nsuk-brand-panel relative hidden overflow-hidden p-10 text-white lg:flex lg:flex-col lg:justify-between xl:p-14">
        <div class="nsuk-glass relative z-10 flex items-center gap-5 rounded-2xl p-4">
            <img src="https://ug.nsuk.edu.ng/api/global/logo" alt="NSUK crest" class="nsuk-crest size-20 shrink-0">
            <div><p class="text-xl font-semibold uppercase leading-8 text-white xl:text-2xl">Nasarawa State<br>University, Keffi</p></div>
        </div>
        <div class="nsuk-glass relative z-10 mx-auto max-w-md rounded-2xl p-8 text-center"><flux:icon name="building-library" class="mx-auto size-9 text-white"/><h1 class="mt-5 text-2xl font-semibold leading-9 text-white">Institutional MoU & Partnership Management System</h1><p class="mt-4 text-sm leading-6 text-white/75">A secure workspace for partnerships, agreements, approvals, obligations, documents, renewals, and institutional reporting.</p></div>
        <div class="nsuk-glass relative z-10 rounded-2xl p-5 text-center"><p class="text-2xl leading-none text-white">“</p><p class="mt-1 text-lg italic text-white">Knowledge for development</p></div>
    </section>
</div>
@persist('toast')<flux:toast.group><flux:toast /></flux:toast.group>@endpersist
@fluxScripts
</body>
</html>
