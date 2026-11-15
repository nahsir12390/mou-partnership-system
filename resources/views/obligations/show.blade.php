<x-layouts::app :title="$obligation->title">
    <div class="mx-auto flex w-full max-w-4xl flex-1 flex-col gap-6">
        <header>
            <a href="{{ route('agreements.show', $obligation->agreement) }}" class="text-sm text-[#016b4b] hover:underline">← {{ $obligation->agreement->reference_number }}</a>
            <p class="mt-4 text-xs font-semibold uppercase tracking-wider text-zinc-500">{{ ucfirst($obligation->type) }}</p>
            <h1 class="mt-1 text-3xl font-semibold tracking-tight">{{ $obligation->title }}</h1>
            <p class="mt-2 text-sm text-zinc-600">{{ $obligation->agreement->title }}</p>
        </header>

        <section class="rounded-xl border border-zinc-200 bg-white p-6 shadow-sm">
            <dl class="grid gap-5 sm:grid-cols-2">
                <div><dt class="text-xs font-semibold uppercase tracking-wide text-zinc-500">Status</dt><dd class="mt-2"><x-status-badge :status="$obligation->status" /></dd></div>
                <div><dt class="text-xs font-semibold uppercase tracking-wide text-zinc-500">Due date</dt><dd class="mt-2 font-medium">{{ $obligation->due_date?->format('d M Y') ?? 'Not set' }}</dd></div>
                <div><dt class="text-xs font-semibold uppercase tracking-wide text-zinc-500">Responsible officer</dt><dd class="mt-2 font-medium">{{ $obligation->responsibleOfficer?->name ?? 'Unassigned' }}</dd></div>
                <div><dt class="text-xs font-semibold uppercase tracking-wide text-zinc-500">Progress</dt><dd class="mt-2 font-medium">{{ $obligation->progress }}%</dd></div>
            </dl>
            <div class="mt-6 border-t border-zinc-100 pt-5"><h2 class="font-semibold">Description</h2><p class="mt-2 text-sm leading-6 text-zinc-600">{{ $obligation->description ?: 'No additional description has been recorded.' }}</p></div>
            @if($obligation->completion_notes)<div class="mt-6 border-t border-zinc-100 pt-5"><h2 class="font-semibold">Completion notes</h2><p class="mt-2 text-sm leading-6 text-zinc-600">{{ $obligation->completion_notes }}</p></div>@endif
            @can('obligations.manage')<div class="mt-6"><flux:button variant="primary" :href="route('obligations.edit', $obligation)">Update Obligation</flux:button></div>@endcan
        </section>
    </div>
</x-layouts::app>
