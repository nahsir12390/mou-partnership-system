@props([
    'sidebar' => false,
])

@if($sidebar)
    <flux:sidebar.brand :name="config('app.name', 'NSUK Partnership Management')" {{ $attributes }}>
        <x-slot name="logo" class="flex aspect-square size-9 items-center justify-center rounded-full bg-white">
            <img src="https://ug.nsuk.edu.ng/api/global/logo" alt="NSUK" class="size-9 rounded-full" />
        </x-slot>
    </flux:sidebar.brand>
@else
    <flux:brand :name="config('app.name', 'NSUK Partnership Management')" {{ $attributes }}>
        <x-slot name="logo" class="flex aspect-square size-9 items-center justify-center rounded-full bg-white">
            <img src="https://ug.nsuk.edu.ng/api/global/logo" alt="NSUK" class="size-9 rounded-full" />
        </x-slot>
    </flux:brand>
@endif
