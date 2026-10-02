<x-layouts::app :title="__('Dashboard')">
    @php
        $stats = [
            ['label' => 'Total Partners', 'value' => '24', 'note' => '+3 this quarter', 'icon' => 'building-office'],
            ['label' => 'Active MoUs', 'value' => '18', 'note' => 'Across 9 departments', 'icon' => 'document-check'],
            ['label' => 'Pending Approvals', 'value' => '6', 'note' => '2 require attention', 'icon' => 'clock'],
            ['label' => 'Expiring Soon', 'value' => '4', 'note' => 'Within the next 90 days', 'icon' => 'calendar-days'],
        ];
        $deadlines = [
            ['date' => '08', 'month' => 'OCT', 'title' => 'Submit annual partnership progress report', 'meta' => 'Research Collaboration MoU', 'status' => 'Due soon'],
            ['date' => '21', 'month' => 'OCT', 'title' => 'Management review of renewal request', 'meta' => 'Academic Exchange Agreement', 'status' => 'Scheduled'],
            ['date' => '12', 'month' => 'NOV', 'title' => 'Agreement expiry and renewal decision', 'meta' => 'ICT Capacity Development MoU', 'status' => 'Upcoming'],
        ];
        $agreements = [
            ['ref' => 'MOU-2026-018', 'title' => 'Research Collaboration MoU', 'partner' => 'Global Research Institute', 'department' => 'Research & Partnerships', 'status' => 'Legal Review', 'expiry' => '14 Sep 2029'],
            ['ref' => 'MOU-2026-014', 'title' => 'Academic Exchange Agreement', 'partner' => 'West Africa Academic Network', 'department' => 'Academic Affairs', 'status' => 'Active', 'expiry' => '30 Jun 2028'],
            ['ref' => 'MOU-2025-031', 'title' => 'ICT Capacity Development MoU', 'partner' => 'Digital Skills Foundation', 'department' => 'ICT Directorate', 'status' => 'Renewal Due', 'expiry' => '12 Nov 2026'],
            ['ref' => 'MOU-2026-021', 'title' => 'Industry Internship Partnership', 'partner' => 'Enterprise Development Group', 'department' => 'Student Affairs', 'status' => 'Pending Approval', 'expiry' => '—'],
        ];
        $activities = [
            ['title' => 'Research Collaboration MoU submitted for legal review', 'meta' => 'Research & Partnerships · 35 minutes ago', 'icon' => 'document-text'],
            ['title' => 'Partnership agreement approved by Management', 'meta' => 'Office of the Vice-Chancellor · 2 hours ago', 'icon' => 'check-circle'],
            ['title' => 'New partner profile added', 'meta' => 'International Relations Office · Yesterday', 'icon' => 'building-office'],
            ['title' => 'Signed agreement document uploaded', 'meta' => 'Legal Services · Yesterday', 'icon' => 'arrow-up-tray'],
        ];
    @endphp

    <div class="flex w-full flex-1 flex-col gap-6">
        <header class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <div class="mb-2 flex items-center gap-2 text-sm text-zinc-500 dark:text-zinc-400">
                    <span>Institutional Partnership Management</span><span>•</span><span>Prototype</span>
                </div>
                <h1 class="text-3xl font-semibold tracking-tight text-zinc-950 dark:text-white">Executive Dashboard</h1>
                <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Monitor partnership performance, agreement lifecycle and actions requiring attention.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <flux:button variant="ghost" icon="arrow-down-tray">Export</flux:button>
                <flux:button variant="ghost" icon="magnifying-glass">Search</flux:button>
                <flux:button variant="primary" icon="plus">New MoU</flux:button>
            </div>
        </header>

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ($stats as $stat)
                <article class="group rounded-xl border border-zinc-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md dark:border-zinc-700 dark:bg-zinc-900">
                    <div class="flex items-start justify-between">
                        <div><p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">{{ $stat['label'] }}</p><p class="mt-2 text-3xl font-semibold tracking-tight text-zinc-950 dark:text-white">{{ $stat['value'] }}</p></div>
                        <div class="flex size-10 items-center justify-center rounded-lg bg-zinc-100 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300"><flux:icon :name="$stat['icon']" class="size-5" /></div>
                    </div>
                    <div class="mt-4 flex items-center gap-1.5 text-xs text-zinc-500 dark:text-zinc-400"><span class="size-1.5 rounded-full bg-zinc-400"></span>{{ $stat['note'] }}</div>
                </article>
            @endforeach
        </div>

        <div class="grid gap-6 xl:grid-cols-5">
            <section class="rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-900 xl:col-span-3">
                <div class="flex items-start justify-between border-b border-zinc-100 px-5 py-4 dark:border-zinc-800">
                    <div><h2 class="font-semibold text-zinc-950 dark:text-white">Partnership Activity</h2><p class="mt-1 text-sm text-zinc-500">MoUs initiated and reviews completed in 2026</p></div>
                    <span class="rounded-lg border border-zinc-200 px-2.5 py-1.5 text-xs text-zinc-500 dark:border-zinc-700">Jan – Sep 2026</span>
                </div>
                <div class="p-3 sm:p-5"><div id="partnership-activity-chart" class="min-h-[285px]"></div></div>
            </section>

            <section class="rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-900 xl:col-span-2">
                <div class="border-b border-zinc-100 px-5 py-4 dark:border-zinc-800"><h2 class="font-semibold text-zinc-950 dark:text-white">Agreement Portfolio</h2><p class="mt-1 text-sm text-zinc-500">Distribution by current lifecycle status</p></div>
                <div class="p-3"><div id="agreement-status-chart" class="min-h-[285px]"></div></div>
            </section>
        </div>

        <div class="grid gap-6 xl:grid-cols-5">
            <section class="rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-900 xl:col-span-3">
                <div class="border-b border-zinc-100 px-5 py-4 dark:border-zinc-800"><h2 class="font-semibold text-zinc-950 dark:text-white">Agreements by Department</h2><p class="mt-1 text-sm text-zinc-500">Current agreement portfolio across major units</p></div>
                <div class="p-3 sm:p-5"><div id="department-chart" class="min-h-[270px]"></div></div>
            </section>

            <section class="rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-900 xl:col-span-2">
                <div class="flex items-center justify-between border-b border-zinc-100 px-5 py-4 dark:border-zinc-800"><div><h2 class="font-semibold text-zinc-950 dark:text-white">Upcoming Deadlines</h2><p class="mt-1 text-sm text-zinc-500">Next actions requiring attention</p></div><flux:icon name="calendar-days" class="size-5 text-zinc-400" /></div>
                <div class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    @foreach ($deadlines as $deadline)
                        <div class="flex gap-4 p-4 sm:px-5">
                            <div class="flex size-12 shrink-0 flex-col items-center justify-center rounded-lg bg-zinc-100 dark:bg-zinc-800"><span class="font-semibold leading-none text-zinc-950 dark:text-white">{{ $deadline['date'] }}</span><span class="mt-1 text-[10px] font-medium text-zinc-500">{{ $deadline['month'] }}</span></div>
                            <div class="min-w-0"><p class="text-sm font-medium text-zinc-900 dark:text-white">{{ $deadline['title'] }}</p><p class="mt-1 truncate text-xs text-zinc-500">{{ $deadline['meta'] }}</p><span class="mt-2 inline-flex rounded-full bg-zinc-100 px-2 py-1 text-[11px] font-medium text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">{{ $deadline['status'] }}</span></div>
                        </div>
                    @endforeach
                </div>
            </section>
        </div>

        <section class="overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <div class="flex items-center justify-between border-b border-zinc-100 px-5 py-4 dark:border-zinc-800"><div><h2 class="font-semibold text-zinc-950 dark:text-white">Recent Agreements</h2><p class="mt-1 text-sm text-zinc-500">Recently created or updated institutional agreements</p></div><flux:button size="sm" variant="ghost">View all</flux:button></div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-zinc-50 text-xs uppercase tracking-wide text-zinc-500 dark:bg-zinc-800/60"><tr><th class="px-5 py-3 font-medium">Agreement</th><th class="px-5 py-3 font-medium">Partner</th><th class="px-5 py-3 font-medium">Department</th><th class="px-5 py-3 font-medium">Status</th><th class="px-5 py-3 font-medium">Expiry</th><th class="px-5 py-3"></th></tr></thead>
                    <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                        @foreach ($agreements as $agreement)
                            <tr class="transition hover:bg-zinc-50/70 dark:hover:bg-zinc-800/40"><td class="px-5 py-4"><p class="font-medium text-zinc-900 dark:text-white">{{ $agreement['title'] }}</p><p class="mt-0.5 text-xs text-zinc-500">{{ $agreement['ref'] }}</p></td><td class="px-5 py-4 text-zinc-600 dark:text-zinc-300">{{ $agreement['partner'] }}</td><td class="px-5 py-4 text-zinc-600 dark:text-zinc-300">{{ $agreement['department'] }}</td><td class="px-5 py-4"><span class="inline-flex rounded-full bg-zinc-100 px-2.5 py-1 text-xs font-medium text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">{{ $agreement['status'] }}</span></td><td class="px-5 py-4 text-zinc-600 dark:text-zinc-300">{{ $agreement['expiry'] }}</td><td class="px-5 py-4"><flux:button size="sm" variant="ghost" icon="ellipsis-horizontal" /></td></tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>

        <div class="grid gap-6 lg:grid-cols-3">
            <section class="rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-900 lg:col-span-2">
                <div class="border-b border-zinc-100 px-5 py-4 dark:border-zinc-800"><h2 class="font-semibold text-zinc-950 dark:text-white">Recent Activity</h2></div>
                <div class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    @foreach ($activities as $activity)
                        <div class="flex gap-3 px-5 py-4"><div class="mt-0.5 flex size-8 shrink-0 items-center justify-center rounded-full bg-zinc-100 dark:bg-zinc-800"><flux:icon :name="$activity['icon']" class="size-4 text-zinc-500" /></div><div><p class="text-sm font-medium text-zinc-800 dark:text-zinc-100">{{ $activity['title'] }}</p><p class="mt-1 text-xs text-zinc-500">{{ $activity['meta'] }}</p></div></div>
                    @endforeach
                </div>
            </section>
            <section class="rounded-xl border border-zinc-200 bg-zinc-950 p-5 text-white shadow-sm dark:border-zinc-700 dark:bg-zinc-100 dark:text-zinc-950">
                <div class="flex items-center justify-between"><div><p class="text-sm font-medium text-zinc-300 dark:text-zinc-600">Attention required</p><p class="mt-2 text-3xl font-semibold">5</p></div><div class="flex size-10 items-center justify-center rounded-lg bg-white/10 dark:bg-black/10"><flux:icon name="exclamation-triangle" class="size-5" /></div></div>
                <div class="mt-6 space-y-3 border-t border-white/10 pt-4 text-sm dark:border-black/10"><div class="flex justify-between"><span class="text-zinc-300 dark:text-zinc-600">Overdue obligations</span><strong>3</strong></div><div class="flex justify-between"><span class="text-zinc-300 dark:text-zinc-600">Approvals over 7 days</span><strong>2</strong></div></div>
                <flux:button class="mt-5 w-full" variant="filled">Review attention items</flux:button>
            </section>
        </div>

        <p class="text-center text-xs text-zinc-400">Dashboard values are prototype demonstration data and will become database-driven as functional modules are completed.</p>
    </div>
</x-layouts::app>
