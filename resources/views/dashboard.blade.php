<x-layouts::app :title="__('Dashboard')">
    <div class="flex w-full flex-1 flex-col gap-6">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Institutional Partnership Management</p>
                <h1 class="mt-1 text-2xl font-semibold tracking-tight text-zinc-900 dark:text-white">MoU & Partnership Tracking System</h1>
                <p class="mt-1 max-w-2xl text-sm text-zinc-500 dark:text-zinc-400">Manage institutional partners, agreements, approvals, obligations, documents, deadlines and renewals from one workspace.</p>
            </div>
            <div class="flex gap-2">
                <flux:button variant="ghost" icon="magnifying-glass">Search</flux:button>
                <flux:button variant="primary" icon="plus">New MoU</flux:button>
            </div>
        </div>

        <div class="rounded-xl border border-dashed border-zinc-300 bg-zinc-50 p-6 dark:border-zinc-700 dark:bg-zinc-900/50">
            <div class="flex items-start gap-4">
                <div class="flex size-10 shrink-0 items-center justify-center rounded-lg bg-zinc-900 text-white dark:bg-white dark:text-zinc-900">
                    <flux:icon name="building-office-2" class="size-5" />
                </div>
                <div>
                    <h2 class="font-semibold text-zinc-900 dark:text-white">Prototype foundation is ready</h2>
                    <p class="mt-1 text-sm leading-6 text-zinc-600 dark:text-zinc-400">The application shell and module navigation have been established. Dashboard statistics and operational data will be implemented in the next phase.</p>
                </div>
            </div>
        </div>

        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            @foreach ([
                ['label' => 'Partners', 'description' => 'Institutional partner registry', 'icon' => 'building-office'],
                ['label' => 'MoUs & Agreements', 'description' => 'Agreement lifecycle management', 'icon' => 'document-text'],
                ['label' => 'Approvals', 'description' => 'Review and approval workflow', 'icon' => 'check-circle'],
                ['label' => 'Deadlines', 'description' => 'Renewals and upcoming actions', 'icon' => 'calendar-days'],
            ] as $module)
                <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                    <flux:icon :name="$module['icon']" class="size-5 text-zinc-500" />
                    <h3 class="mt-4 font-medium text-zinc-900 dark:text-white">{{ $module['label'] }}</h3>
                    <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">{{ $module['description'] }}</p>
                </div>
            @endforeach
        </div>
    </div>
</x-layouts::app>
