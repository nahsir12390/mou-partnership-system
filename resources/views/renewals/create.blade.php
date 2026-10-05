<x-layouts::app :title="'Record Renewal · '.$agreement->reference_number">
<div class="mx-auto w-full max-w-3xl">
    <a href="{{ route('agreements.show', $agreement) }}" class="text-sm text-zinc-500 hover:text-zinc-900">← Back to agreement</a>
    <header class="mt-4"><p class="text-sm font-medium text-zinc-500">{{ $agreement->reference_number }}</p><h1 class="mt-1 text-3xl font-semibold tracking-tight">Record Renewal Decision</h1><p class="mt-1 text-sm text-zinc-500">The current expiry date is {{ $agreement->expiry_date?->format('d M Y') ?? 'not set' }}.</p></header>
    @if($errors->any())<div class="mt-5 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-700"><ul class="list-disc pl-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    <form method="POST" action="{{ route('renewals.store', $agreement) }}" class="mt-6 grid gap-5 rounded-xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
        @csrf
        <div><label class="mb-1.5 block text-sm font-medium">Decision *</label><select name="decision" class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2.5 dark:border-zinc-700 dark:bg-zinc-800"><option value="renewed" @selected(old('decision')==='renewed')>Renewed</option><option value="extended" @selected(old('decision')==='extended')>Extended</option><option value="not_renewing" @selected(old('decision')==='not_renewing')>Will not renew</option></select></div>
        <div class="grid gap-5 sm:grid-cols-2"><flux:input name="decision_date" type="date" label="Decision date" value="{{ old('decision_date', today()->format('Y-m-d')) }}" required/><flux:input name="new_expiry_date" type="date" label="New expiry date" value="{{ old('new_expiry_date') }}"/></div>
        <flux:textarea name="notes" label="Decision notes" rows="4" placeholder="Record the authority, conditions, or context for this decision.">{{ old('notes') }}</flux:textarea>
        <div class="flex justify-end gap-2"><flux:button variant="ghost" :href="route('agreements.show', $agreement)">Cancel</flux:button><flux:button type="submit" variant="primary">Record Decision</flux:button></div>
    </form>
</div>
</x-layouts::app>
