@csrf
@if($errors->any())<div class="mb-6 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700 dark:border-red-900 dark:bg-red-950/30 dark:text-red-300"><div class="flex gap-3"><flux:icon name="exclamation-triangle" class="mt-0.5 size-5 shrink-0"/><div><p class="font-semibold">We couldn't save this partner yet.</p><p class="mt-1 text-xs opacity-80">Review the information below and correct the highlighted fields.</p><ul class="mt-2 list-disc space-y-1 pl-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div></div></div>@endif
<div class="grid gap-6 lg:grid-cols-2">
    <div class="lg:col-span-2"><h2 class="font-semibold text-zinc-950 dark:text-white">Organization Information</h2><p class="mt-1 text-sm text-zinc-500">Create the institutional profile used across agreements, reporting and partnership records.</p></div>
    <flux:input name="name" label="Organization name" value="{{ old('name', $partner->name ?? '') }}" placeholder="e.g. Nasarawa State University" required />
    <flux:select name="category" label="Partner category"><option value="">Select category</option>@foreach(['Academic Institution','Government Agency','Private Sector','NGO / Foundation','International Organization','Research Institution','Professional Body','Other'] as $category)<option value="{{ $category }}" @selected(old('category', $partner->category ?? '') === $category)>{{ $category }}</option>@endforeach</flux:select>
    <flux:input name="website" label="Official website" type="url" value="{{ old('website', $partner->website ?? '') }}" placeholder="https://example.org" />
    <flux:select name="status" label="Relationship status" required>@foreach(['active'=>'Active','prospective'=>'Prospective','inactive'=>'Inactive'] as $value=>$label)<option value="{{ $value }}" @selected(old('status', $partner->status ?? 'active') === $value)>{{ $label }}</option>@endforeach</flux:select>

    <div class="lg:col-span-2 border-t border-zinc-100 pt-6 dark:border-zinc-800"><h2 class="font-semibold text-zinc-950 dark:text-white">Location</h2><p class="mt-1 text-sm text-zinc-500">Record the partner's principal institutional location.</p></div>
    <flux:input name="country" label="Country" value="{{ old('country', $partner->country ?? '') }}" placeholder="e.g. Nigeria" />
    <flux:input name="state" label="State / Region" value="{{ old('state', $partner->state ?? '') }}" />
    <flux:input name="city" label="City" value="{{ old('city', $partner->city ?? '') }}" />
    <flux:input name="address" label="Office address" value="{{ old('address', $partner->address ?? '') }}" />

    <div class="lg:col-span-2 border-t border-zinc-100 pt-6 dark:border-zinc-800"><h2 class="font-semibold text-zinc-950 dark:text-white">Primary Contact</h2><p class="mt-1 text-sm text-zinc-500">Designate the principal contact for partnership correspondence.</p></div>
    <flux:input name="contact_name" label="Contact person" value="{{ old('contact_name', $partner->contact_name ?? '') }}" placeholder="Full name" />
    <flux:input name="contact_email" type="email" label="Email address" value="{{ old('contact_email', $partner->contact_email ?? '') }}" placeholder="name@organization.org" />
    <flux:input name="contact_phone" label="Phone number" value="{{ old('contact_phone', $partner->contact_phone ?? '') }}" placeholder="+234 ..." />
    <div class="lg:col-span-2"><flux:textarea name="notes" label="Internal notes" rows="4" placeholder="Optional context about the relationship, engagement history or next steps.">{{ old('notes', $partner->notes ?? '') }}</flux:textarea><p class="mt-1.5 text-xs text-zinc-400">Internal notes are visible only to authorized system users.</p></div>
</div>
<div class="mt-8 flex flex-col-reverse gap-3 border-t border-zinc-100 pt-5 sm:flex-row sm:items-center sm:justify-between dark:border-zinc-800"><p class="text-xs text-zinc-400">Review the organization details before saving.</p><div class="flex justify-end gap-3"><flux:button :href="route('partners.index')" variant="ghost">Cancel</flux:button><flux:button type="submit" variant="primary" icon="check">{{ $submitLabel }}</flux:button></div></div>