@csrf
<div class="grid gap-6 lg:grid-cols-2">
    <div class="lg:col-span-2"><h2 class="font-semibold text-zinc-950 dark:text-white">Organization Information</h2><p class="mt-1 text-sm text-zinc-500">Basic information about the institutional partner.</p></div>
    <flux:input name="name" label="Organization name" value="{{ old('name', $partner->name ?? '') }}" required />
    <flux:select name="category" label="Partner category"><option value="">Select category</option>@foreach(['Academic Institution','Government Agency','Private Sector','NGO / Foundation','International Organization','Research Institution','Professional Body','Other'] as $category)<option value="{{ $category }}" @selected(old('category', $partner->category ?? '') === $category)>{{ $category }}</option>@endforeach</flux:select>
    <flux:input name="website" label="Website" type="url" value="{{ old('website', $partner->website ?? '') }}" placeholder="https://example.org" />
    <flux:select name="status" label="Status" required>@foreach(['active'=>'Active','prospective'=>'Prospective','inactive'=>'Inactive'] as $value=>$label)<option value="{{ $value }}" @selected(old('status', $partner->status ?? 'active') === $value)>{{ $label }}</option>@endforeach</flux:select>

    <div class="lg:col-span-2 border-t border-zinc-100 pt-6 dark:border-zinc-800"><h2 class="font-semibold text-zinc-950 dark:text-white">Location</h2></div>
    <flux:input name="country" label="Country" value="{{ old('country', $partner->country ?? '') }}" />
    <flux:input name="state" label="State / Region" value="{{ old('state', $partner->state ?? '') }}" />
    <flux:input name="city" label="City" value="{{ old('city', $partner->city ?? '') }}" />
    <flux:input name="address" label="Address" value="{{ old('address', $partner->address ?? '') }}" />

    <div class="lg:col-span-2 border-t border-zinc-100 pt-6 dark:border-zinc-800"><h2 class="font-semibold text-zinc-950 dark:text-white">Primary Contact</h2></div>
    <flux:input name="contact_name" label="Contact person" value="{{ old('contact_name', $partner->contact_name ?? '') }}" />
    <flux:input name="contact_email" type="email" label="Email address" value="{{ old('contact_email', $partner->contact_email ?? '') }}" />
    <flux:input name="contact_phone" label="Phone number" value="{{ old('contact_phone', $partner->contact_phone ?? '') }}" />
    <div class="lg:col-span-2"><flux:textarea name="notes" label="Notes" rows="4">{{ old('notes', $partner->notes ?? '') }}</flux:textarea></div>
</div>

@if($errors->any())<div class="mt-6 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-700 dark:border-red-900 dark:bg-red-950/30 dark:text-red-300"><p class="font-medium">Please correct the highlighted information.</p><ul class="mt-2 list-disc pl-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

<div class="mt-8 flex justify-end gap-3 border-t border-zinc-100 pt-5 dark:border-zinc-800"><flux:button :href="route('partners.index')" variant="ghost">Cancel</flux:button><flux:button type="submit" variant="primary">{{ $submitLabel }}</flux:button></div>
