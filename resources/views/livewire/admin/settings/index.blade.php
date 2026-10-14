<div class="space-y-6">

    {{-- Page Header --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
                Global Settings
            </h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Configure your store details, email, currency, taxes, payments, shipping, and store-wide policies.
            </p>
        </div>

        <div class="flex items-center gap-3">
            <button
                type="button"
                wire:click="save"
                wire:loading.attr="disabled"
                class="inline-flex items-center justify-center gap-2 rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-60"
            >
                <svg wire:loading.remove wire:target="save" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>

                <svg wire:loading wire:target="save" class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>

                <span wire:loading.remove wire:target="save">Save Changes</span>
                <span wire:loading wire:target="save">Saving...</span>
            </button>
        </div>
    </div>

    {{-- Notification Alert --}}
    @if (session()->has('settings_success'))
        <div class="rounded-xl border border-green-200 bg-green-50 p-4 text-sm text-green-800 dark:border-green-800 dark:bg-green-900/30 dark:text-green-300 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <svg class="h-5 w-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <span>{{ session('settings_success') }}</span>
            </div>
        </div>
    @endif

    {{-- Settings Shell --}}
    <div class="grid grid-cols-1 gap-8 lg:grid-cols-4">

        {{-- Navigation Tabs --}}
        <div class="lg:col-span-1">
            <nav class="space-y-1 rounded-2xl border border-gray-200 bg-white p-2 shadow-sm dark:border-gray-800 dark:bg-gray-800">
                @php
                    $tabs = [
                        ['id' => 'general', 'label' => 'Application & Store', 'icon' => 'store'],
                        ['id' => 'email', 'label' => 'Email (SMTP)', 'icon' => 'mail'],
                        ['id' => 'currency', 'label' => 'Currency & Pricing', 'icon' => 'currency'],
                        ['id' => 'tax', 'label' => 'Taxes', 'icon' => 'tax'],
                        ['id' => 'payment', 'label' => 'Payments', 'icon' => 'card'],
                        ['id' => 'shipping', 'label' => 'Shipping', 'icon' => 'truck'],
                        ['id' => 'other', 'label' => 'Other Settings', 'icon' => 'cog'],
                    ];
                @endphp

                @foreach ($tabs as $tab)
                    <button
                        type="button"
                        wire:click="setTab('{{ $tab['id'] }}')"
                        class="flex w-full items-center gap-3 rounded-xl px-4 py-3 text-left text-sm font-medium transition {{ $activeTab === $tab['id'] ? 'bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400 font-semibold' : 'text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-gray-700/50' }}"
                    >
                        @if ($tab['icon'] === 'store')
                            <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M3 21h18M3 7v1a3 3 0 006 0V7m0 1a3 3 0 006 0V7m0 1a3 3 0 006 0V7H3l2-4h14l2 4M5 21V10.85A4.98 4.98 0 013 7m6 14v-7a1 1 0 011-1h4a1 1 0 011 1v7" />
                            </svg>
                        @elseif ($tab['icon'] === 'mail')
                            <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                        @elseif ($tab['icon'] === 'currency')
                            <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        @elseif ($tab['icon'] === 'tax')
                            <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                            </svg>
                        @elseif ($tab['icon'] === 'card')
                            <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                            </svg>
                        @elseif ($tab['icon'] === 'truck')
                            <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M8 17a2 2 0 100-4 2 2 0 000 4zm10 0a2 2 0 100-4 2 2 0 000 4zM4 9h9m2 0h2l3 4v4H4V9z" />
                            </svg>
                        @else
                            <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        @endif

                        <span>{{ $tab['label'] }}</span>
                    </button>
                @endforeach
            </nav>
        </div>

        {{-- Content Panel --}}
        <div class="lg:col-span-3 space-y-6">

            {{-- 1. Application & Store Settings --}}
            @if ($activeTab === 'general')
                <div class="space-y-6">

                    {{-- Store Profile --}}
                    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-800">
                        <h2 class="text-base font-semibold text-gray-900 dark:text-white">Store Profile & Branding</h2>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Basic details about your store, branding logo and public contact.</p>

                        <div class="mt-6 grid grid-cols-1 gap-5 sm:grid-cols-2">
                            <div>
                                <label class="text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">
                                    Store Name <span class="text-red-500">*</span>
                                </label>
                                <input type="text" wire:model.blur="store_name" class="mt-1.5 w-full rounded-lg border border-gray-300 px-3.5 py-2.5 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                                @error('store_name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">
                                    Store Tagline
                                </label>
                                <input type="text" wire:model.blur="store_tagline" class="mt-1.5 w-full rounded-lg border border-gray-300 px-3.5 py-2.5 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                                @error('store_tagline') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">
                                    Customer Support Email <span class="text-red-500">*</span>
                                </label>
                                <input type="email" wire:model.blur="contact_email" class="mt-1.5 w-full rounded-lg border border-gray-300 px-3.5 py-2.5 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                                @error('contact_email') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">
                                    Contact Phone
                                </label>
                                <input type="text" wire:model.blur="contact_phone" class="mt-1.5 w-full rounded-lg border border-gray-300 px-3.5 py-2.5 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                                @error('contact_phone') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">
                                    Store Front URL
                                </label>
                                <input type="url" wire:model.blur="store_url" placeholder="https://yourstore.com" class="mt-1.5 w-full rounded-lg border border-gray-300 px-3.5 py-2.5 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                                @error('store_url') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        {{-- Logo Upload --}}
                        <div class="mt-6 border-t border-gray-200 pt-6 dark:border-gray-700">
                            <label class="text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">
                                Store Logo
                            </label>
                            <div class="mt-3 flex items-center gap-6">
                                <div class="h-16 w-32 shrink-0 overflow-hidden rounded-xl border border-gray-200 bg-gray-50 flex items-center justify-center dark:border-gray-700 dark:bg-gray-900">
                                    @if ($new_store_logo)
                                        <img src="{{ $new_store_logo->temporaryUrl() }}" alt="Logo preview" class="h-full w-full object-contain">
                                    @elseif ($store_logo)
                                        <img src="{{ Storage::disk('public')->url($store_logo) }}" alt="Store logo" class="h-full w-full object-contain">
                                    @else
                                        <span class="text-xs text-gray-400">No Logo</span>
                                    @endif
                                </div>

                                <div class="space-y-2">
                                    <input type="file" wire:model="new_store_logo" id="store-logo-input" accept="image/*" class="hidden">
                                    <label for="store-logo-input" class="inline-flex cursor-pointer rounded-lg border border-gray-300 bg-white px-3.5 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50 shadow-sm dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200">
                                        Upload Logo
                                    </label>

                                    @if ($store_logo || $new_store_logo)
                                        <button type="button" wire:click="removeLogo" class="inline-flex rounded-lg px-3 py-2 text-xs font-semibold text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20">
                                            Remove
                                        </button>
                                    @endif
                                    <p class="text-[11px] text-gray-500">PNG, JPG or WebP up to 2MB. Recommended 240x80px.</p>
                                    @error('new_store_logo') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Standards & Formats --}}
                    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-800">
                        <h2 class="text-base font-semibold text-gray-900 dark:text-white">Regional & Standards</h2>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Timezone and date formats used across orders, dashboards and notifications.</p>

                        <div class="mt-6 grid grid-cols-1 gap-5 sm:grid-cols-2">
                            <div>
                                <label class="text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">
                                    Timezone <span class="text-red-500">*</span>
                                </label>
                                <select wire:model.blur="timezone" class="mt-1.5 w-full rounded-lg border border-gray-300 px-3.5 py-2.5 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                                    <option value="Asia/Kolkata">Asia/Kolkata (IST - UTC+05:30)</option>
                                    <option value="UTC">UTC (Coordinated Universal Time)</option>
                                    <option value="America/New_York">America/New_York (EST/EDT)</option>
                                    <option value="America/Los_Angeles">America/Los_Angeles (PST/PDT)</option>
                                    <option value="Europe/London">Europe/London (GMT/BST)</option>
                                    <option value="Europe/Paris">Europe/Paris (CET/CEST)</option>
                                    <option value="Asia/Dubai">Asia/Dubai (GST - UTC+04:00)</option>
                                    <option value="Asia/Singapore">Asia/Singapore (SGT - UTC+08:00)</option>
                                    <option value="Asia/Tokyo">Asia/Tokyo (JST - UTC+09:00)</option>
                                    <option value="Australia/Sydney">Australia/Sydney (AEST/AEDT)</option>
                                </select>
                                @error('timezone') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">
                                    Date Format <span class="text-red-500">*</span>
                                </label>
                                <select wire:model.blur="date_format" class="mt-1.5 w-full rounded-lg border border-gray-300 px-3.5 py-2.5 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                                    <option value="d M Y">13 Oct 2026 (d M Y)</option>
                                    <option value="Y-m-d">2026-10-13 (Y-m-d)</option>
                                    <option value="d/m/Y">13/10/2026 (d/m/Y)</option>
                                    <option value="m/d/Y">10/13/2026 (m/d/Y)</option>
                                    <option value="F j, Y">October 13, 2026 (F j, Y)</option>
                                </select>
                                @error('date_format') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        {{-- Maintenance Mode --}}
                        <div class="mt-6 border-t border-gray-200 pt-6 dark:border-gray-700">
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="checkbox" wire:model="maintenance_mode" class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                <div>
                                    <span class="text-sm font-semibold text-gray-900 dark:text-white">Enable Maintenance Mode</span>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Temporarily display a maintenance notice to storefront customers while you update the store.</p>
                                </div>
                            </label>

                            @if ($maintenance_mode)
                                <div class="mt-3">
                                    <label class="text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">
                                        Maintenance Message
                                    </label>
                                    <textarea wire:model.blur="maintenance_message" rows="2" class="mt-1.5 w-full rounded-lg border border-gray-300 px-3.5 py-2.5 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100 dark:border-gray-700 dark:bg-gray-900 dark:text-white"></textarea>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Business Address --}}
                    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-800">
                        <h2 class="text-base font-semibold text-gray-900 dark:text-white">Legal Business Address</h2>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Printed on invoices, packing slips, tax returns and order confirmation emails.</p>

                        <div class="mt-6 grid grid-cols-1 gap-5 sm:grid-cols-2">
                            <div class="sm:col-span-2">
                                <label class="text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">Address Line 1</label>
                                <input type="text" wire:model.blur="address_line1" class="mt-1.5 w-full rounded-lg border border-gray-300 px-3.5 py-2.5 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                            </div>

                            <div>
                                <label class="text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">Address Line 2 (Optional)</label>
                                <input type="text" wire:model.blur="address_line2" class="mt-1.5 w-full rounded-lg border border-gray-300 px-3.5 py-2.5 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                            </div>

                            <div>
                                <label class="text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">City</label>
                                <input type="text" wire:model.blur="city" class="mt-1.5 w-full rounded-lg border border-gray-300 px-3.5 py-2.5 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                            </div>

                            <div>
                                <label class="text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">State / Province</label>
                                <input type="text" wire:model.blur="state" class="mt-1.5 w-full rounded-lg border border-gray-300 px-3.5 py-2.5 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                            </div>

                            <div>
                                <label class="text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">Postal / PIN Code</label>
                                <input type="text" wire:model.blur="postal_code" class="mt-1.5 w-full rounded-lg border border-gray-300 px-3.5 py-2.5 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                            </div>

                            <div>
                                <label class="text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">Country</label>
                                <input type="text" wire:model.blur="country" class="mt-1.5 w-full rounded-lg border border-gray-300 px-3.5 py-2.5 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                            </div>
                        </div>
                    </div>

                </div>
            @endif

            {{-- 2. Email Settings (SMTP) --}}
            @if ($activeTab === 'email')
                <div class="space-y-6">

                    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-800">
                        <h2 class="text-base font-semibold text-gray-900 dark:text-white">Outbound Email & SMTP Server</h2>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Configure SMTP credentials used to deliver order receipts, shipping notifications and password resets.</p>

                        <div class="mt-6 grid grid-cols-1 gap-5 sm:grid-cols-2">
                            <div>
                                <label class="text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">
                                    Mail Driver / Transport <span class="text-red-500">*</span>
                                </label>
                                <select wire:model.blur="mail_driver" class="mt-1.5 w-full rounded-lg border border-gray-300 px-3.5 py-2.5 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                                    <option value="smtp">SMTP (Recommended)</option>
                                    <option value="sendmail">Sendmail</option>
                                    <option value="log">Log (Local testing only)</option>
                                </select>
                                @error('mail_driver') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">
                                    SMTP Host <span class="text-red-500">*</span>
                                </label>
                                <input type="text" wire:model.blur="mail_host" placeholder="smtp.mailtrap.io" class="mt-1.5 w-full rounded-lg border border-gray-300 px-3.5 py-2.5 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                                @error('mail_host') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">
                                    SMTP Port <span class="text-red-500">*</span>
                                </label>
                                <input type="number" wire:model.blur="mail_port" placeholder="587" class="mt-1.5 w-full rounded-lg border border-gray-300 px-3.5 py-2.5 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                                @error('mail_port') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">
                                    Encryption Protocol
                                </label>
                                <select wire:model.blur="mail_encryption" class="mt-1.5 w-full rounded-lg border border-gray-300 px-3.5 py-2.5 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                                    <option value="tls">TLS (Port 587)</option>
                                    <option value="ssl">SSL (Port 465)</option>
                                    <option value="none">None (Insecure / Local)</option>
                                </select>
                                @error('mail_encryption') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">
                                    SMTP Username
                                </label>
                                <input type="text" wire:model.blur="mail_username" class="mt-1.5 w-full rounded-lg border border-gray-300 px-3.5 py-2.5 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                            </div>

                            <div>
                                <label class="text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">
                                    SMTP Password
                                </label>
                                <input type="password" wire:model.blur="mail_password" placeholder="••••••••" class="mt-1.5 w-full rounded-lg border border-gray-300 px-3.5 py-2.5 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                            </div>

                            <div>
                                <label class="text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">
                                    Sender Email Address <span class="text-red-500">*</span>
                                </label>
                                <input type="email" wire:model.blur="mail_from_address" placeholder="noreply@yourdomain.com" class="mt-1.5 w-full rounded-lg border border-gray-300 px-3.5 py-2.5 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                                @error('mail_from_address') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">
                                    Sender Display Name <span class="text-red-500">*</span>
                                </label>
                                <input type="text" wire:model.blur="mail_from_name" placeholder="Custom Commerce Support" class="mt-1.5 w-full rounded-lg border border-gray-300 px-3.5 py-2.5 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                                @error('mail_from_name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>

                    {{-- Test Email Card --}}
                    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-800">
                        <h2 class="text-base font-semibold text-gray-900 dark:text-white">Send Test Email</h2>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Verify that your SMTP configuration is successfully routing outbound emails.</p>

                        <div class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-center">
                            <input
                                type="email"
                                wire:model.blur="test_email_recipient"
                                placeholder="recipient@example.com"
                                class="flex-1 rounded-lg border border-gray-300 px-3.5 py-2.5 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                            >
                            <button
                                type="button"
                                wire:click="sendTestEmail"
                                wire:loading.attr="disabled"
                                class="inline-flex items-center justify-center rounded-lg bg-gray-900 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-gray-800 disabled:opacity-60 dark:bg-gray-100 dark:text-gray-900 dark:hover:bg-white"
                            >
                                <span wire:loading.remove wire:target="sendTestEmail">Send Test</span>
                                <span wire:loading wire:target="sendTestEmail">Sending...</span>
                            </button>
                        </div>
                        @error('test_email_recipient') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror

                        @if ($testEmailStatus === 'success')
                            <p class="mt-3 text-xs font-medium text-green-600 dark:text-green-400">{{ $testEmailMessage }}</p>
                        @elseif ($testEmailStatus === 'error')
                            <p class="mt-3 text-xs font-medium text-red-600 dark:text-red-400">{{ $testEmailMessage }}</p>
                        @endif
                    </div>

                </div>
            @endif

            {{-- 3. Currency & Pricing --}}
            @if ($activeTab === 'currency')
                <div class="space-y-6">

                    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-800">
                        <h2 class="text-base font-semibold text-gray-900 dark:text-white">Currency & Number Formatting</h2>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Control how prices, totals, currencies and decimals are displayed across your catalog and checkout.</p>

                        <div class="mt-6 grid grid-cols-1 gap-5 sm:grid-cols-2">
                            <div>
                                <label class="text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">
                                    Default Currency Code <span class="text-red-500">*</span>
                                </label>
                                <input type="text" wire:model.blur="default_currency" placeholder="INR" maxlength="3" class="mt-1.5 w-full uppercase rounded-lg border border-gray-300 px-3.5 py-2.5 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                                @error('default_currency') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">
                                    Currency Symbol <span class="text-red-500">*</span>
                                </label>
                                <input type="text" wire:model.blur="currency_symbol" placeholder="₹" class="mt-1.5 w-full rounded-lg border border-gray-300 px-3.5 py-2.5 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                                @error('currency_symbol') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">
                                    Symbol Placement <span class="text-red-500">*</span>
                                </label>
                                <select wire:model.blur="currency_symbol_position" class="mt-1.5 w-full rounded-lg border border-gray-300 px-3.5 py-2.5 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                                    <option value="before">Before amount (e.g. ₹500)</option>
                                    <option value="after">After amount (e.g. 500₹)</option>
                                </select>
                            </div>

                            <div>
                                <label class="text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">
                                    Decimal Precision <span class="text-red-500">*</span>
                                </label>
                                <select wire:model.blur="decimal_precision" class="mt-1.5 w-full rounded-lg border border-gray-300 px-3.5 py-2.5 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                                    <option value="0">0 decimals (e.g. ₹500)</option>
                                    <option value="2">2 decimals (e.g. ₹500.00)</option>
                                </select>
                            </div>

                            <div>
                                <label class="text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">
                                    Thousands Separator <span class="text-red-500">*</span>
                                </label>
                                <input type="text" wire:model.blur="thousands_separator" placeholder="," maxlength="2" class="mt-1.5 w-full rounded-lg border border-gray-300 px-3.5 py-2.5 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                            </div>

                            <div>
                                <label class="text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">
                                    Decimal Separator <span class="text-red-500">*</span>
                                </label>
                                <input type="text" wire:model.blur="decimal_separator" placeholder="." maxlength="2" class="mt-1.5 w-full rounded-lg border border-gray-300 px-3.5 py-2.5 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                            </div>

                            <div>
                                <label class="text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">
                                    Spacing Format <span class="text-red-500">*</span>
                                </label>
                                <select wire:model.blur="price_display_format" class="mt-1.5 w-full rounded-lg border border-gray-300 px-3.5 py-2.5 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                                    <option value="symbol_no_space">No Space (e.g. ₹1,299.00)</option>
                                    <option value="symbol_space">With Space (e.g. ₹ 1,299.00)</option>
                                </select>
                            </div>
                        </div>

                        {{-- Preview Box --}}
                        <div class="mt-6 rounded-xl border border-blue-200 bg-blue-50/50 p-4 dark:border-blue-900/50 dark:bg-blue-950/20">
                            <span class="text-xs font-semibold uppercase tracking-wider text-blue-900 dark:text-blue-300">Live Price Preview</span>
                            <div class="mt-1 text-2xl font-bold text-blue-900 dark:text-blue-100">
                                @php
                                    $sampleAmount = 1499.50;
                                    $formatted = number_format($sampleAmount, $decimal_precision, $decimal_separator, $thousands_separator);
                                    $spacing = $price_display_format === 'symbol_space' ? ' ' : '';
                                    echo $currency_symbol_position === 'after' ? $formatted . $spacing . $currency_symbol : $currency_symbol . $spacing . $formatted;
                                @endphp
                            </div>
                        </div>
                    </div>

                </div>
            @endif

            {{-- 4. Tax Settings --}}
            @if ($activeTab === 'tax')
                <div class="space-y-6">

                    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-800">
                        <h2 class="text-base font-semibold text-gray-900 dark:text-white">Tax Configuration & Rates</h2>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Configure sales tax, GST, inclusive or exclusive pricing rules, and shipping tax rates.</p>

                        <div class="mt-6 space-y-5">
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="checkbox" wire:model="enable_tax" class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                <div>
                                    <span class="text-sm font-semibold text-gray-900 dark:text-white">Enable Automated Tax Calculation</span>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Apply sales tax on customer cart orders and invoices.</p>
                                </div>
                            </label>

                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="checkbox" wire:model="prices_include_tax" class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                <div>
                                    <span class="text-sm font-semibold text-gray-900 dark:text-white">Catalog Prices are Tax-Inclusive</span>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">When checked, product prices already include tax (no extra tax added at checkout). Leave unchecked to add tax on top of product prices at checkout.</p>
                                </div>
                            </label>

                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="checkbox" wire:model="tax_shipping" class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                <div>
                                    <span class="text-sm font-semibold text-gray-900 dark:text-white">Apply Tax to Shipping Fees</span>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Calculate tax on courier delivery charges in addition to product items.</p>
                                </div>
                            </label>

                            <div class="grid grid-cols-1 gap-5 pt-4 border-t border-gray-200 sm:grid-cols-2 dark:border-gray-700">
                                <div>
                                    <label class="text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">
                                        Tax Label / Name <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" wire:model.blur="tax_name" placeholder="GST" class="mt-1.5 w-full rounded-lg border border-gray-300 px-3.5 py-2.5 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                                    @error('tax_name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label class="text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">
                                        Default Tax Rate (%) <span class="text-red-500">*</span>
                                    </label>
                                    <div class="relative mt-1.5">
                                        <input type="number" step="0.1" min="0" max="100" wire:model.blur="default_tax_rate" placeholder="18.0" class="w-full rounded-lg border border-gray-300 px-3.5 py-2.5 pr-10 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                                        <span class="absolute inset-y-0 right-0 flex items-center pr-3 text-sm text-gray-400">%</span>
                                    </div>
                                    @error('default_tax_rate') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            @endif

            {{-- 5. Payment Settings --}}
            @if ($activeTab === 'payment')
                <div class="space-y-6">

                    {{-- Cash on Delivery --}}
                    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-800">
                        <div class="flex items-center justify-between">
                            <div>
                                <h2 class="text-base font-semibold text-gray-900 dark:text-white">Cash on Delivery (COD)</h2>
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Accept physical cash payments upon parcel delivery.</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" wire:model="enable_cod" class="sr-only peer">
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
                            </label>
                        </div>

                        @if ($enable_cod)
                            <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                                <label class="text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">
                                    Customer COD Instructions
                                </label>
                                <textarea wire:model.blur="cod_instructions" rows="2" class="mt-1.5 w-full rounded-lg border border-gray-300 px-3.5 py-2.5 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100 dark:border-gray-700 dark:bg-gray-900 dark:text-white" placeholder="Keep exact change ready for our courier partner."></textarea>
                            </div>
                        @endif
                    </div>

                    {{-- Credit & Debit Cards --}}
                    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-800">
                        <div class="flex items-center justify-between">
                            <div>
                                <h2 class="text-base font-semibold text-gray-900 dark:text-white">Credit & Debit Card Gateway</h2>
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Accept Visa, MasterCard, RuPay and American Express cards.</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" wire:model="enable_card" class="sr-only peer">
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
                            </label>
                        </div>

                        @if ($enable_card)
                            <div class="mt-5 pt-5 border-t border-gray-200 grid grid-cols-1 gap-5 sm:grid-cols-2 dark:border-gray-700">
                                <div>
                                    <label class="text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">
                                        Gateway Environment <span class="text-red-500">*</span>
                                    </label>
                                    <select wire:model.blur="card_mode" class="mt-1.5 w-full rounded-lg border border-gray-300 px-3.5 py-2.5 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                                        <option value="test">Test (Sandbox Mode)</option>
                                        <option value="live">Live (Production Mode)</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">
                                        API Publishable Key
                                    </label>
                                    <input type="text" wire:model.blur="card_gateway_key" placeholder="pk_test_..." class="mt-1.5 w-full rounded-lg border border-gray-300 px-3.5 py-2.5 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                                </div>

                                <div class="sm:col-span-2">
                                    <label class="text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">
                                        API Secret Key
                                    </label>
                                    <input type="password" wire:model.blur="card_gateway_secret" placeholder="sk_test_..." class="mt-1.5 w-full rounded-lg border border-gray-300 px-3.5 py-2.5 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- UPI Instant Payment --}}
                    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-800">
                        <div class="flex items-center justify-between">
                            <div>
                                <h2 class="text-base font-semibold text-gray-900 dark:text-white">UPI Instant Payment (VPA / QR)</h2>
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Accept direct bank transfers via Google Pay, PhonePe, Paytm and BHIM.</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" wire:model="enable_upi" class="sr-only peer">
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
                            </label>
                        </div>

                        @if ($enable_upi)
                            <div class="mt-5 pt-5 border-t border-gray-200 grid grid-cols-1 gap-5 sm:grid-cols-2 dark:border-gray-700">
                                <div>
                                    <label class="text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">
                                        UPI Environment <span class="text-red-500">*</span>
                                    </label>
                                    <select wire:model.blur="upi_mode" class="mt-1.5 w-full rounded-lg border border-gray-300 px-3.5 py-2.5 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                                        <option value="test">Test Mode</option>
                                        <option value="live">Live Production</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">
                                        Merchant VPA / UPI ID
                                    </label>
                                    <input type="text" wire:model.blur="upi_merchant_vpa" placeholder="store@okhdfcbank" class="mt-1.5 w-full rounded-lg border border-gray-300 px-3.5 py-2.5 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                                </div>

                                <div class="sm:col-span-2">
                                    <label class="text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">
                                        UPI Customer Instructions
                                    </label>
                                    <textarea wire:model.blur="upi_instructions" rows="2" class="mt-1.5 w-full rounded-lg border border-gray-300 px-3.5 py-2.5 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100 dark:border-gray-700 dark:bg-gray-900 dark:text-white" placeholder="Approve payment prompt in your bank app to complete your order."></textarea>
                                </div>
                            </div>
                        @endif
                    </div>

                </div>
            @endif

            {{-- 6. Shipping Settings --}}
            @if ($activeTab === 'shipping')
                <div class="space-y-6">

                    {{-- Origin & Rates --}}
                    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-800">
                        <h2 class="text-base font-semibold text-gray-900 dark:text-white">Shipping Origin & Rules</h2>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Warehouse location used to calculate carrier transit times and shipping charges.</p>

                        <div class="mt-6 grid grid-cols-1 gap-5 sm:grid-cols-2">
                            <div class="sm:col-span-2">
                                <label class="text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">Shipping Origin Address</label>
                                <input type="text" wire:model.blur="origin_address" placeholder="Warehouse address" class="mt-1.5 w-full rounded-lg border border-gray-300 px-3.5 py-2.5 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                            </div>

                            <div>
                                <label class="text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">City</label>
                                <input type="text" wire:model.blur="origin_city" class="mt-1.5 w-full rounded-lg border border-gray-300 px-3.5 py-2.5 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                            </div>

                            <div>
                                <label class="text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">State</label>
                                <input type="text" wire:model.blur="origin_state" class="mt-1.5 w-full rounded-lg border border-gray-300 px-3.5 py-2.5 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                            </div>

                            <div>
                                <label class="text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">Origin Postal / PIN Code</label>
                                <input type="text" wire:model.blur="origin_postal_code" class="mt-1.5 w-full rounded-lg border border-gray-300 px-3.5 py-2.5 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                            </div>

                            <div>
                                <label class="text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">Origin Country</label>
                                <input type="text" wire:model.blur="origin_country" class="mt-1.5 w-full rounded-lg border border-gray-300 px-3.5 py-2.5 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                            </div>
                        </div>

                        <div class="mt-6 pt-6 border-t border-gray-200 grid grid-cols-1 gap-5 sm:grid-cols-2 dark:border-gray-700">
                            <div>
                                <label class="text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">
                                    Handling Time (Days) <span class="text-red-500">*</span>
                                </label>
                                <input type="number" min="0" max="30" wire:model.blur="handling_time_days" class="mt-1.5 w-full rounded-lg border border-gray-300 px-3.5 py-2.5 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                                @error('handling_time_days') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">
                                    Default Shipping Charge (₹) <span class="text-red-500">*</span>
                                </label>
                                <input type="number" step="0.01" min="0" wire:model.blur="default_shipping_fee" class="mt-1.5 w-full rounded-lg border border-gray-300 px-3.5 py-2.5 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                                @error('default_shipping_fee') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        {{-- Free Shipping Threshold --}}
                        <div class="mt-6 pt-6 border-t border-gray-200 space-y-4 dark:border-gray-700">
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="checkbox" wire:model="enable_free_shipping" class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                <div>
                                    <span class="text-sm font-semibold text-gray-900 dark:text-white">Enable Free Shipping Threshold</span>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Waive shipping fees when customer cart subtotal meets or exceeds a minimum order value.</p>
                                </div>
                            </label>

                            @if ($enable_free_shipping)
                                <div class="max-w-xs">
                                    <label class="text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">
                                        Free Shipping Minimum Cart Value (₹) <span class="text-red-500">*</span>
                                    </label>
                                    <input type="number" step="1" min="0" wire:model.blur="free_shipping_threshold" placeholder="500" class="mt-1.5 w-full rounded-lg border border-gray-300 px-3.5 py-2.5 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                                    @error('free_shipping_threshold') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                </div>
                            @endif
                        </div>
                    </div>

                </div>
            @endif

            {{-- 7. Other Settings --}}
            @if ($activeTab === 'other')
                <div class="space-y-6">

                    {{-- Customer Accounts & Orders --}}
                    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-800">
                        <h2 class="text-base font-semibold text-gray-900 dark:text-white">Accounts & Order Numbering</h2>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Policies for guest shoppers, customer accounts, and sequential order numbers.</p>

                        <div class="mt-6 space-y-4">
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="checkbox" wire:model="allow_customer_registration" class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                <div>
                                    <span class="text-sm font-semibold text-gray-900 dark:text-white">Allow Public Customer Registration</span>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Permit visitors to create customer accounts on the storefront.</p>
                                </div>
                            </label>

                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="checkbox" wire:model="guest_checkout" class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                <div>
                                    <span class="text-sm font-semibold text-gray-900 dark:text-white">Enable Guest Checkout</span>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Allow customers to complete checkout without requiring password registration.</p>
                                </div>
                            </label>

                            <div class="grid grid-cols-1 gap-5 pt-4 border-t border-gray-200 sm:grid-cols-2 dark:border-gray-700">
                                <div>
                                    <label class="text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">
                                        Order Number Prefix <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" wire:model.blur="order_number_prefix" placeholder="ORD-" class="mt-1.5 w-full rounded-lg border border-gray-300 px-3.5 py-2.5 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                                    @error('order_number_prefix') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label class="text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">
                                        Order Number Digits Length <span class="text-red-500">*</span>
                                    </label>
                                    <input type="number" min="4" max="12" wire:model.blur="order_number_digits" class="mt-1.5 w-full rounded-lg border border-gray-300 px-3.5 py-2.5 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                                    @error('order_number_digits') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Inventory Alerts & Admin Notifications --}}
                    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-800">
                        <h2 class="text-base font-semibold text-gray-900 dark:text-white">Inventory Alerts & Admin Notifications</h2>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Receive alerts when variant stocks run low or when orders are placed.</p>

                        <div class="mt-6 space-y-4">
                            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                                <div>
                                    <label class="text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">
                                        Low Stock Threshold Quantity <span class="text-red-500">*</span>
                                    </label>
                                    <input type="number" min="0" wire:model.blur="low_stock_threshold" class="mt-1.5 w-full rounded-lg border border-gray-300 px-3.5 py-2.5 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                                    @error('low_stock_threshold') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label class="text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">
                                        Admin Notification Email
                                    </label>
                                    <input type="email" wire:model.blur="admin_notification_email" placeholder="admin@yourdomain.com" class="mt-1.5 w-full rounded-lg border border-gray-300 px-3.5 py-2.5 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                                    @error('admin_notification_email') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                </div>
                            </div>

                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="checkbox" wire:model="notify_low_stock" class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Send low stock alerts when variant quantity falls below threshold</span>
                            </label>

                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="checkbox" wire:model="notify_new_order" class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Send email notification whenever a new order is completed</span>
                            </label>
                        </div>
                    </div>

                    {{-- API & Webhooks --}}
                    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-800">
                        <h2 class="text-base font-semibold text-gray-900 dark:text-white">API & Webhooks</h2>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Integrate external ERP, fulfillment services or accounting tools.</p>

                        <div class="mt-6 space-y-4">
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="checkbox" wire:model="enable_api_access" class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                <span class="text-sm font-semibold text-gray-900 dark:text-white">Enable REST API Access</span>
                            </label>

                            <div>
                                <label class="text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">
                                    Outbound Order Webhook URL
                                </label>
                                <input type="url" wire:model.blur="api_webhook_url" placeholder="https://external-service.com/webhooks/orders" class="mt-1.5 w-full rounded-lg border border-gray-300 px-3.5 py-2.5 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                                @error('api_webhook_url') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>

                </div>
            @endif

        </div>

    </div>

</div>
