<x-layouts::app :title="__('Dashboard')">
@php
$user = auth()->user();
$agreementBase = \App\Models\Agreement::query();
$obligationBase = \App\Models\Obligation::query();
$partnerBase = \App\Models\Partner::query();
if ($user->hasRole('department-officer','read-only-user') && $user->department_id) {
    $agreementBase->where('department_id',$user->department_id);
    $obligationBase->where('department_id',$user->department_id);
}
$stats = [
    ['label'=>'Total Partners','value'=>(clone $partnerBase)->count(),'note'=>'Institutional relationships','icon'=>'building-office'],
    ['label'=>'Active MoUs','value'=>(clone $agreementBase)->where('status','active')->count(),'note'=>'Currently active agreements','icon'=>'document-check'],
    ['label'=>'In Approval','value'=>(clone $agreementBase)->whereIn('status',['submitted','under_review'])->count(),'note'=>'Awaiting review decisions','icon'=>'check-circle'],
    ['label'=>'Overdue Delivery','value'=>(clone $obligationBase)->whereNotNull('due_date')->whereDate('due_date','<',today())->whereNotIn('status',['completed','cancelled'])->count(),'note'=>'Commitments requiring attention','icon'=>'exclamation-triangle'],
];
$recentAgreements=(clone $agreementBase)->with(['partner','department'])->latest()->take(6)->get();
$renewals=(clone $agreementBase)->with('partner')->whereNotNull('expiry_date')->whereBetween('expiry_date',[today(),today()->addDays(90)])->whereNotIn('status',['expired','terminated'])->orderBy('expiry_date')->take(5)->get();
$delivery=(clone $obligationBase)->with(['agreement','responsibleOfficer','department'])->whereNotIn('status',['completed','cancelled'])->orderByRaw('due_date IS NULL, due_date ASC')->take(5)->get();
$statusCounts=(clone $agreementBase)->selectRaw('status,count(*) total')->groupBy('status')->pluck('total','status');
$statusMax=max(1,(int)$statusCounts->max());
$criticalCount=(clone $obligationBase)->whereNotNull('due_date')->whereDate('due_date','<',today())->whereNotIn('status',['completed','cancelled'])->count() + (clone $agreementBase)->whereNotNull('expiry_date')->whereDate('expiry_date','<',today())->whereNotIn('status',['expired','terminated'])->count();
@endphp
<div class="flex w-full flex-1 flex-col gap-6">
    <header class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <p class="text-sm font-medium text-zinc-500">Institutional Partnership Management · Executive Workspace</p>
            <h1 class="mt-1 text-3xl font-semibold tracking-tight">Executive Dashboard</h1>
            <p class="mt-1 text-sm text-zinc-500">Live partnership, approval, delivery and renewal intelligence.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            @can('alerts.view')<flux:button variant="ghost" icon="bell" :href="route('alerts.index')">Operational Alerts @if($criticalCount > 0)<span class="ml-1 rounded-full bg-red-600 px-1.5 py-0.5 text-[10px] font-bold text-white">{{ $criticalCount }}</span>@endif</flux:button>@endcan
            @can('reports.view')<flux:button variant="ghost" :href="route('reports.index')">Management Reports</flux:button>@endcan
            @can('agreements.create')<flux:button variant="primary" :href="route('agreements.create')">New MoU</flux:button>@endcan
        </div>
    </header>

    @if($criticalCount > 0 && $user->hasPermission('alerts.view'))
        <a href="{{ route('alerts.index') }}" class="flex items-center justify-between gap-4 rounded-xl border border-red-200 bg-red-50 px-5 py-4 text-red-900 transition hover:bg-red-100 dark:border-red-900/60 dark:bg-red-950/30 dark:text-red-200">
            <div class="flex items-center gap-3"><div class="flex size-10 items-center justify-center rounded-full bg-red-100 dark:bg-red-900/50"><flux:icon name="exclamation-triangle" class="size-5"/></div><div><p class="font-semibold">{{ $criticalCount }} critical {{ Str::plural('item',$criticalCount) }} require attention</p><p class="text-sm opacity-75">Overdue delivery or lifecycle items have been detected.</p></div></div><span class="text-sm font-medium">Review alerts →</span>
        </a>
    @endif

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach($stats as $stat)
            <article class="rounded-xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900"><div class="flex items-start justify-between"><div><p class="text-sm font-medium text-zinc-500">{{ $stat['label'] }}</p><p class="mt-2 text-3xl font-semibold">{{ $stat['value'] }}</p></div><div class="flex size-10 items-center justify-center rounded-xl bg-zinc-100 dark:bg-zinc-800"><flux:icon :name="$stat['icon']" class="size-5 text-zinc-600 dark:text-zinc-300"/></div></div><p class="mt-3 text-xs text-zinc-500">{{ $stat['note'] }}</p></article>
        @endforeach
    </div>

    <div class="grid gap-6 xl:grid-cols-3">
        <section class="rounded-xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900"><div class="flex items-start justify-between"><div><h2 class="font-semibold">Agreement Portfolio</h2><p class="mt-1 text-sm text-zinc-500">Live lifecycle distribution</p></div><span class="text-xs text-zinc-400">{{ $statusCounts->sum() }} records</span></div><div class="mt-5 space-y-3">@forelse($statusCounts as $status=>$total)<div><div class="mb-1 flex justify-between text-sm"><span class="capitalize">{{ str_replace('_',' ',$status) }}</span><span class="font-medium">{{ $total }}</span></div><div class="h-2 rounded-full bg-zinc-100 dark:bg-zinc-800"><div class="h-2 rounded-full bg-zinc-900 dark:bg-white" style="width:{{ ($total/$statusMax)*100 }}%"></div></div></div>@empty<p class="py-8 text-center text-sm text-zinc-500">No agreements recorded.</p>@endforelse</div></section>
        <section class="rounded-xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900"><div class="flex items-start justify-between"><div><h2 class="font-semibold">Renewal Watch</h2><p class="mt-1 text-sm text-zinc-500">Expiring within 90 days</p></div>@can('alerts.view')<a href="{{ route('alerts.index') }}" class="text-xs font-medium text-zinc-500 hover:text-zinc-900 dark:hover:text-white">View alerts</a>@endcan</div><div class="mt-5 space-y-3">@forelse($renewals as $item)<a href="{{ route('agreements.show',$item) }}" class="block rounded-lg border border-zinc-100 p-3 transition hover:bg-zinc-50 dark:border-zinc-800 dark:hover:bg-zinc-800"><div class="flex justify-between gap-3"><div class="min-w-0"><p class="truncate text-sm font-medium">{{ $item->title }}</p><p class="mt-1 truncate text-xs text-zinc-500">{{ $item->partner->name }}</p></div><span class="shrink-0 rounded-full bg-amber-50 px-2 py-1 text-xs font-semibold text-amber-700 dark:bg-amber-950/50">{{ (int)today()->diffInDays($item->expiry_date) }}d</span></div></a>@empty<p class="py-8 text-center text-sm text-zinc-500">No renewals due soon.</p>@endforelse</div></section>
        <section class="rounded-xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900"><div class="flex items-start justify-between"><div><h2 class="font-semibold">Delivery Watch</h2><p class="mt-1 text-sm text-zinc-500">Nearest open commitments</p></div><a href="{{ route('obligations.index') }}" class="text-xs font-medium text-zinc-500 hover:text-zinc-900 dark:hover:text-white">View all</a></div><div class="mt-5 space-y-3">@forelse($delivery as $item)<a href="{{ route('agreements.show',$item->agreement) }}" class="block rounded-lg border border-zinc-100 p-3 transition hover:bg-zinc-50 dark:border-zinc-800 dark:hover:bg-zinc-800"><div class="flex justify-between gap-3"><div class="min-w-0"><p class="truncate text-sm font-medium">{{ $item->title }}</p><p class="mt-1 truncate text-xs text-zinc-500">{{ $item->responsibleOfficer?->name ?? $item->department?->name ?? 'Unassigned' }}</p></div><span class="shrink-0 text-xs {{ $item->is_overdue ? 'font-semibold text-red-600':'text-zinc-500' }}">{{ $item->due_date?->format('d M') ?? 'No date' }}</span></div></a>@empty<p class="py-8 text-center text-sm text-zinc-500">No open commitments.</p>@endforelse</div></section>
    </div>

    <section class="overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-900"><div class="flex items-center justify-between border-b border-zinc-100 px-5 py-4 dark:border-zinc-800"><div><h2 class="font-semibold">Recent Agreements</h2><p class="mt-1 text-sm text-zinc-500">Latest records from the institutional registry</p></div><flux:button size="sm" variant="ghost" :href="route('agreements.index')">View all</flux:button></div><div class="overflow-x-auto"><table class="w-full text-left text-sm"><thead class="bg-zinc-50 text-xs uppercase text-zinc-500 dark:bg-zinc-800/60"><tr><th class="px-5 py-3">Agreement</th><th class="px-5 py-3">Partner</th><th class="px-5 py-3">Department</th><th class="px-5 py-3">Status</th><th class="px-5 py-3">Expiry</th></tr></thead><tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">@forelse($recentAgreements as $agreement)<tr><td class="px-5 py-4"><a href="{{ route('agreements.show',$agreement) }}" class="font-medium hover:underline">{{ $agreement->title }}</a><p class="text-xs text-zinc-500">{{ $agreement->reference_number }}</p></td><td class="px-5 py-4">{{ $agreement->partner->name }}</td><td class="px-5 py-4">{{ $agreement->department?->name ?? '—' }}</td><td class="px-5 py-4"><x-status-badge :status="$agreement->status"/></td><td class="px-5 py-4">{{ $agreement->expiry_date?->format('d M Y') ?? '—' }}</td></tr>@empty<tr><td colspan="5" class="px-5 py-12 text-center text-zinc-500">No agreements yet.</td></tr>@endforelse</tbody></table></div></section>
</div>
</x-layouts::app>
