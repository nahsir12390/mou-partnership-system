<x-layouts::auth :title="__('Secure Sign In')">
    <div class="flex flex-col gap-6">
        <div>
            <div class="mb-5 flex size-11 items-center justify-center rounded-xl bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-200"><flux:icon name="lock-closed" class="size-5" /></div>
            <h2 class="text-2xl font-semibold tracking-tight text-zinc-950 dark:text-white">Welcome back</h2>
            <p class="mt-2 text-sm leading-6 text-zinc-500">Sign in to access the institutional partnership management workspace.</p>
        </div>

        <x-auth-session-status class="rounded-lg border border-emerald-200 bg-emerald-50 p-3 text-center text-sm text-emerald-700 dark:border-emerald-900 dark:bg-emerald-950/30 dark:text-emerald-300" :status="session('status')" />

        <form method="POST" action="{{ route('login.store') }}" class="flex flex-col gap-5">
            @csrf
            <flux:input name="email" :label="__('Work email')" :value="old('email')" type="email" required autofocus autocomplete="email" placeholder="name@institution.org" />
            <div class="relative">
                <flux:input name="password" :label="__('Password')" type="password" required autocomplete="current-password" :placeholder="__('Enter your password')" viewable />
                @if (Route::has('password.request'))<flux:link class="absolute top-0 text-xs end-0" :href="route('password.request')" wire:navigate>{{ __('Forgot password?') }}</flux:link>@endif
            </div>
            <div class="flex items-center justify-between"><flux:checkbox name="remember" :label="__('Keep me signed in')" :checked="old('remember')" /><span class="text-xs text-zinc-400">Secure session</span></div>
            <flux:button variant="primary" type="submit" class="w-full" icon-trailing="arrow-right" data-test="login-button">{{ __('Sign in to workspace') }}</flux:button>
        </form>

        <div class="rounded-xl border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-800 dark:bg-zinc-950/60">
            <div class="flex gap-3"><flux:icon name="information-circle" class="mt-0.5 size-4 shrink-0 text-zinc-400"/><div><p class="text-xs font-medium text-zinc-700 dark:text-zinc-300">Access is managed by your institution</p><p class="mt-1 text-xs leading-5 text-zinc-500">Contact your system administrator if you require an account or additional access privileges.</p></div></div>
        </div>
    </div>
</x-layouts::auth>