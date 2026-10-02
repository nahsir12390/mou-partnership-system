<x-layouts::app :title="__('Dashboard')">
    @php
        $stats = [
            ['label' => 'Total Partners', 'value' => '24', 'note' => '3 added this quarter', 'icon' => 'building-office'],
            ['label' => 'Active MoUs', 'value' => '18', 'note' => 'Across 9 departments', 'icon' => 'document-check'],
            ['label' => 'Pending Approvals', 'value' => '6', 'note' => '2 require attention', 'icon' => 'clock'],
            ['label' => 'Expiring Soon', 'value' => '4', 'note' => 'Within the next 90 days', 'icon' => 'calendar-days'],
        ];

        $activities = [
            ['title' => 'Research Collaboration MoU submitted for legal review', 'meta' => 'Research & Partnerships · 35 minutes ago', 'icon' => 'document-text'],
            ['title' => 'Partnership agreement approved by Management', 'meta' => 'Office of the Vice-Chancellor · 2 hours ago', 'icon' => 'check-circle'],
            ['title' => 'New partner profile added', 'meta' => 'International Relations Office · Yesterday', 'icon' => 'building-office'],
            ['title' => 'Signed agreement document uploaded', 'meta' => 'Legal Services · Yesterday', 'icon' => 'arrow-up-tray'],
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
    @endphp

    <div class="flex w-full flex-1 flex-col gap-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Institutional Partnership Management</p>
                <h1 class="mt-1 text-2xl font-semibold tracking-tight text-zinc-950 dark:text-white">Dashboard</h1>
                <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Overview of partnerships, agreements, approvals and upcoming institutional obligations.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <flux:button variant="ghost" icon="magnifying-glass">Search</flux:button>
                <flux:button variant="primary" icon="plus">New MoU</flux:button>
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ($stats as $stat)
                <div class="rounded-xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">{{ $stat['label'] }}</p>
                            <p class="mt-2 text-3xl font-semibold tracking-tight text-zinc-950 dark:text-white">{{ $stat['value'] }}</p>
                        </div>
                        <div class="flex size-10 items-center justify-center rounded-lg bg-zinc-100 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">
                            <flux:icon :name="$stat['icon']" class="size-5" />
                        </div>
                    </div>
                    <p class="mt-4 text-xs text-zinc-500 dark:text-zinc-400">{{ $stat['note'] }}</p>
                </div>
            @endforeach
        </div>

        <div class="grid gap-6 xl:grid-cols-3">
            <section class="rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-900 xl:col-span-2">
                <div class="flex items-center justify-between border-b border-zinc-200 px-5 py-4 dark:border-zinc-700">
                    <div>
                        <h2 class="font-semibold text-zinc-950 dark:text-white">Agreement Status Overview</h2>
                        <p class="mt-0.5 text-sm text-zinc-500 dark:text-zinc-400">Current distribution of institutional agreements</p>
                    </div>
                    <flux:button size="sm" variant="ghost">View reports</flux:button>
                </div>
                <div class="grid gap-6 p-5 md:grid-cols-2">
                    <div class="flex min-h-52 items-end gap-4 rounded-lg bg-zinc-50 p-5 dark:bg-zinc-800/60">
                        @foreach ([['Active', 78], ['Review', 48], ['Pending', 35], ['Renewal', 25]] as [$label, $height])
                            <div class="flex flex-1 flex-col items-center gap-2">
                                <div class="flex h-36 w-full items-end rounded-md bg-zinc-200/70 p-1 dark:bg-zinc-700/70">
                                    <div class="w-full rounded bg-zinc-700 dark:bg-zinc-300" style="height: {{ $height }}%"></div>
                                </div>
                                <span class="text-xs text-zinc-500 dark:text-zinc-400">{{ $label }}</span>
                            </div>
                        @endforeach
                    </div>
                    <div class="space-y-4">
                        @foreach ([
                            ['Active agreements', '18', '60%'],
                            ['Under review', '6', '20%'],
                            ['Pending approval', '4', '13%'],
                            ['Renewal due', '2', '7%'],
                        ] as [$label, $value, $width])
                            <div>
                                <div class="mb-1.5 flex justify-between text-sm"><span class="text-zinc-600 dark:text-zinc-300">{{ $label }}</span><span class="font-medium text-zinc-900 dark:text-white">{{ $value }}</span></div>
                                <div class="h-2 rounded-full bg-zinc-100 dark:bg-zinc-800"><div class="h-2 rounded-full bg-zinc-700 dark:bg-zinc-300" style="width: {{ $width }}"></div></div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>

            <section class="rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                <div class="border-b border-zinc-200 px-5 py-4 dark:border-zinc-700">
                    <h2 class="font-semibold text-zinc-950 dark:text-white">Upcoming Deadlines</h2>
                    <p class="mt-0.5 text-sm text-zinc-500 dark:text-zinc-400">Actions requiring attention</p>
                </div>
                <div class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    @foreach ($deadlines as $deadline)
                        <div class="flex gap-4 p-5">
                            <div class="flex size-12 shrink-0 flex-col items-center justify-center rounded-lg bg-zinc-100 dark:bg-zinc-800">
                                <span class="text-base font-semibold leading-none text-zinc-900 dark:text-white">{{ $deadline['date'] }}</span>
                                <span class="mt-1 text-[10px] font-medium text-zinc-500">{{ $deadline['month'] }}</span>
                            </div>
                            <div class="min-w-0">
                                <p class="text-sm font-medium text-zinc-900 dark:text-white">{{ $deadline['title'] }}</p>
                                <p class="mt-1 truncate text-xs text-zinc-500">{{ $deadline['meta'] }}</p>
                                <span class="mt-2 inline-flex rounded-full bg-zinc-100 px-2 py-1 text-[11px] font-medium text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">{{ $deadline['status'] }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        </div>

        <section class="overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <div class="flex items-center justify-between border-b border-zinc-200 px-5 py-4 dark:border-zinc-700">
                <div>
                    <h2 class="font-semibold text-zinc-950 dark:text-white">Recent Agreements</h2>
                    <p class="mt-0.5 text-sm text-zinc-500 dark:text-zinc-400">Recently created or updated MoUs and agreements</p>
                </div>
                <flux:button size="sm" variant="ghost">View all</flux:button>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-zinc-50 text-xs uppercase tracking-wide text-zinc-500 dark:bg-zinc-800/60 dark:text-zinc-400">
                        <tr><th class="px-5 py-3 font-medium">Reference / Agreement</th><th class="px-5 py-3 font-medium">Partner</th><th class="px-5 py-3 font-medium">Department</th><th class="px-5 py-3 font-medium">Status</th><th class="px-5 py-3 font-medium">Expiry</th></tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                        @foreach ($agreements as $agreement)
                            <tr class="hover:bg-zinc-50/70 dark:hover:bg-zinc-800/40">
                                <td class="px-5 py-4"><p class="font-medium text-zinc-900 dark:text-white">{{ $agreement['title'] }}</p><p class="mt-0.5 text-xs text-zinc-500">{{ $agreement['ref'] }}</p></td>
                                <td class="px-5 py-4 text-zinc-600 dark:text-zinc-300">{{ $agreement['partner'] }}</td>
                                <td class="px-5 py-4 text-zinc-600 dark:text-zinc-300">{{ $agreement['department'] }}</td>
                                <td class="px-5 py-4"><span class="inline-flex rounded-full bg-zinc-100 px-2.5 py-1 text-xs font-medium text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">{{ $agreement['status'] }}</span></td>
                                <td class="px-5 py-4 text-zinc-600 dark:text-zinc-300">{{ $agreement['expiry'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>

        <div class="grid gap-6 lg:grid-cols-2">
            <section class="rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                <div class="border-b border-zinc-200 px-5 py-4 dark:border-zinc-700"><h2 class="font-semibold text-zinc-950 dark:text-white">Recent Activity</h2></div>
                <div class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    @foreach ($activities as $activity)
                        <div class="flex gap-3 px-5 py-4">
                            <div class="mt-0.5 flex size-8 shrink-0 items-center justify-center rounded-full bg-zinc-100 dark:bg-zinc-800"><flux:icon :name="$activity['icon']" class="size-4 text-zinc-500" /></div>
                            <div><p class="text-sm font-medium text-zinc-800 dark:text-zinc-100">{{ $activity['title'] }}</p><p class="mt-1 text-xs text-zinc-500">{{ $activity['meta'] }}</p></div>
                        </div>
                    @endforeach
                </div>
            </section>

            <section class="rounded-xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                <h2 class="font-semibold text-zinc-950 dark:text-white">Attention Required</h2>
                <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Items that may need management action.</p>
                <div class="mt-5 grid gap-3 sm:grid-cols-2">
                    <div class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-700"><p class="text-2xl font-semibold text-zinc-950 dark:text-white">3</p><p class="mt-1 text-sm text-zinc-500">Overdue obligations</p></div>
                    <div class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-700"><p class="text-2xl font-semibold text-zinc-950 dark:text-white">2</p><p class="mt-1 text-sm text-zinc-500">Approvals waiting &gt; 7 days</p></div>
                </div>
                <p class="mt-4 text-xs leading-5 text-zinc-400">Prototype values are demonstration data and will be replaced by database-driven metrics as the functional modules are implemented.</p>
            </section>
        </div>
    </div>
</x-layouts::app>
