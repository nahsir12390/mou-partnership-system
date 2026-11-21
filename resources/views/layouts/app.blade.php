<x-layouts::app.sidebar :title="$title ?? null">
    <flux:main class="nsuk-main min-w-0">
        {{ $slot }}
    </flux:main>
</x-layouts::app.sidebar>
