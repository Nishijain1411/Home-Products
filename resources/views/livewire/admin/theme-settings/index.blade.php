<div class="mx-auto max-w-5xl space-y-6 p-6">

    <div>
        <h1 class="text-2xl font-bold text-gray-900">Theme Settings</h1>
        <p class="mt-1 text-sm text-gray-500">
            Customize the appearance and sections of your storefront.
        </p>
    </div>

    @if (session()->has('success'))
        <div class="rounded-lg bg-green-50 p-4 text-sm text-green-700">
            {{ session('success') }}
        </div>
    @endif

    <form wire:submit="saveDraft" class="space-y-6">

        {{-- Theme General --}}
        <section class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-gray-900">General Settings</h2>

            <div class="mt-4">
                <label class="block text-sm font-medium text-gray-700">
                    Theme Name
                </label>
                <input
                    type="text"
                    wire:model.live.debounce.300ms="themeName"
                    class="mt-1 w-full rounded-lg border-gray-300"
                />
                @error('themeName')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </section>

        {{-- Colors --}}
        <section class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-gray-900">Colors</h2>
            <p class="mt-1 text-xs text-gray-500">Pick colors to match your brand across the storefront.</p>

            <div class="mt-5 grid gap-5 sm:grid-cols-2">
                @foreach ([
                    'primaryColor' => 'Primary color (Buttons, accents & highlights)',
                    'secondaryColor' => 'Secondary color (Hero background & footer)',
                    'backgroundColor' => 'Background color',
                    'textColor' => 'Text color',
                ] as $field => $label)
                    <div wire:key="theme-color-{{ $field }}">
                        <label class="block text-sm font-medium text-gray-700">
                            {{ $label }}
                        </label>

                        <div class="mt-2 flex items-center gap-3">
                            <input
                                type="color"
                                wire:model.live="{{ $field }}"
                                class="h-10 w-14 cursor-pointer rounded border border-gray-300"
                            />

                            <input
                                type="text"
                                wire:model.live.debounce.300ms="{{ $field }}"
                                maxlength="7"
                                class="w-32 rounded-lg border-gray-300 text-sm font-mono"
                            />
                        </div>

                        @error($field)
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                @endforeach
            </div>
        </section>

        {{-- Layout --}}
        <section class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-gray-900">Layout</h2>

            <div class="mt-4">
                <label class="block text-sm font-medium text-gray-700">
                    Maximum Page Width
                </label>

                <select
                    wire:model.live="pageWidth"
                    class="mt-1 w-full rounded-lg border-gray-300 sm:max-w-xs"
                >
                    <option value="1024px">1024px — Compact</option>
                    <option value="1152px">1152px — Standard</option>
                    <option value="1280px">1280px — Wide</option>
                    <option value="1440px">1440px — Extra wide</option>
                    <option value="100%">100% — Full width</option>
                </select>

                @error('pageWidth')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </section>

        {{-- Hero Section --}}
        <section class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-semibold text-gray-900">Hero Section</h2>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input
                        type="checkbox"
                        wire:model.live="showHero"
                        class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                    />
                    <span class="text-sm font-medium text-gray-700">Show Hero Section</span>
                </label>
            </div>

            <div class="mt-5 grid gap-5 sm:grid-cols-2">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Small Label / Eyebrow</label>
                    <input
                        wire:model.live.debounce.300ms="heroEyebrow"
                        type="text"
                        class="mt-1 w-full rounded-lg border-gray-300"
                    />
                    @error('heroEyebrow') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Main Heading</label>
                    <input
                        wire:model.live.debounce.300ms="heroHeading"
                        type="text"
                        class="mt-1 w-full rounded-lg border-gray-300"
                    />
                    @error('heroHeading') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-700">Highlighted Heading (Colored Accent)</label>
                    <input
                        wire:model.live.debounce.300ms="heroHighlight"
                        type="text"
                        class="mt-1 w-full rounded-lg border-gray-300"
                    />
                    @error('heroHighlight') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-700">Description</label>
                    <textarea
                        wire:model.live.debounce.300ms="heroDescription"
                        rows="3"
                        class="mt-1 w-full rounded-lg border-gray-300"
                    ></textarea>
                    @error('heroDescription') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Primary Button Text</label>
                    <input
                        wire:model.live.debounce.300ms="heroPrimaryButtonText"
                        type="text"
                        class="mt-1 w-full rounded-lg border-gray-300"
                    />
                    @error('heroPrimaryButtonText') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Primary Button Link</label>
                    <input
                        wire:model.live.debounce.300ms="heroPrimaryButtonLink"
                        type="text"
                        class="mt-1 w-full rounded-lg border-gray-300"
                    />
                    @error('heroPrimaryButtonLink') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Secondary Button Text</label>
                    <input
                        wire:model.live.debounce.300ms="heroSecondaryButtonText"
                        type="text"
                        class="mt-1 w-full rounded-lg border-gray-300"
                    />
                    @error('heroSecondaryButtonText') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Secondary Button Link</label>
                    <input
                        wire:model.live.debounce.300ms="heroSecondaryButtonLink"
                        type="text"
                        class="mt-1 w-full rounded-lg border-gray-300"
                    />
                    @error('heroSecondaryButtonLink') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>
        </section>

        {{-- Categories Section --}}
        <section class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-semibold text-gray-900">Categories Section</h2>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input
                        type="checkbox"
                        wire:model.live="showCategories"
                        class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                    />
                    <span class="text-sm font-medium text-gray-700">Show Categories Section</span>
                </label>
            </div>

            <div class="mt-5 space-y-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Section Heading</label>
                    <input
                        type="text"
                        wire:model.live.debounce.300ms="categoryHeading"
                        class="mt-1 w-full rounded-lg border-gray-300"
                    />
                    @error('categoryHeading') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Section Description</label>
                    <textarea
                        wire:model.live.debounce.300ms="categoryDescription"
                        rows="2"
                        class="mt-1 w-full rounded-lg border-gray-300"
                    ></textarea>
                    @error('categoryDescription') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Categories per Row</label>
                    <select
                        wire:model.live="categoryColumns"
                        class="mt-1 w-full rounded-lg border-gray-300 sm:max-w-xs"
                    >
                        <option value="2">2 columns</option>
                        <option value="3">3 columns</option>
                        <option value="4">4 columns</option>
                    </select>
                    @error('categoryColumns') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>
        </section>

        {{-- Featured Products Section --}}
        <section class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-semibold text-gray-900">Featured Products Section</h2>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input
                        type="checkbox"
                        wire:model.live="showProducts"
                        class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                    />
                    <span class="text-sm font-medium text-gray-700">Show Products Section</span>
                </label>
            </div>

            <div class="mt-5 space-y-5">
                <div class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Section Heading</label>
                        <input
                            type="text"
                            wire:model.live.debounce.300ms="productsHeading"
                            class="mt-1 w-full rounded-lg border-gray-300"
                        />
                        @error('productsHeading') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">"View All" Button Text</label>
                        <input
                            type="text"
                            wire:model.live.debounce.300ms="productsViewAllText"
                            class="mt-1 w-full rounded-lg border-gray-300"
                        />
                        @error('productsViewAllText') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">"View All" Link</label>
                    <input
                        type="text"
                        wire:model.live.debounce.300ms="productsViewAllLink"
                        class="mt-1 w-full rounded-lg border-gray-300"
                    />
                    @error('productsViewAllLink') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Section Description</label>
                    <textarea
                        wire:model.live.debounce.300ms="productsDescription"
                        rows="2"
                        class="mt-1 w-full rounded-lg border-gray-300"
                    ></textarea>
                    @error('productsDescription') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Products per Row</label>
                        <select
                            wire:model.live="productsColumns"
                            class="mt-1 w-full rounded-lg border-gray-300 sm:max-w-xs"
                        >
                            <option value="2">2 columns</option>
                            <option value="3">3 columns</option>
                            <option value="4">4 columns</option>
                        </select>
                        @error('productsColumns') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Number of Products to Display</label>
                        <select
                            wire:model.live="productsCount"
                            class="mt-1 w-full rounded-lg border-gray-300 sm:max-w-xs"
                        >
                            <option value="4">4 products</option>
                            <option value="8">8 products</option>
                            <option value="12">12 products</option>
                            <option value="16">16 products</option>
                        </select>
                        @error('productsCount') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>
        </section>

        {{-- Call to Action Section --}}
        <section class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-semibold text-gray-900">Call to Action (CTA) Banner</h2>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input
                        type="checkbox"
                        wire:model.live="showCta"
                        class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                    />
                    <span class="text-sm font-medium text-gray-700">Show CTA Banner</span>
                </label>
            </div>

            <div class="mt-5 space-y-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Headline</label>
                    <input
                        type="text"
                        wire:model.live.debounce.300ms="ctaHeading"
                        class="mt-1 w-full rounded-lg border-gray-300"
                    />
                    @error('ctaHeading') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Description</label>
                    <textarea
                        wire:model.live.debounce.300ms="ctaDescription"
                        rows="2"
                        class="mt-1 w-full rounded-lg border-gray-300"
                    ></textarea>
                    @error('ctaDescription') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Button Text</label>
                        <input
                            type="text"
                            wire:model.live.debounce.300ms="ctaButtonText"
                            class="mt-1 w-full rounded-lg border-gray-300"
                        />
                        @error('ctaButtonText') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Button Link</label>
                        <input
                            type="text"
                            wire:model.live.debounce.300ms="ctaButtonLink"
                            class="mt-1 w-full rounded-lg border-gray-300"
                        />
                        @error('ctaButtonLink') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>
        </section>

        {{-- Save Draft action --}}
        <div class="flex justify-end gap-3">
            <button
                type="submit"
                wire:loading.attr="disabled"
                class="rounded-lg bg-blue-600 px-6 py-3 text-sm font-semibold text-white shadow-sm hover:bg-blue-700 disabled:opacity-50 transition"
            >
                <span wire:loading.remove wire:target="saveDraft">Save Draft</span>
                <span wire:loading wire:target="saveDraft">Saving Draft...</span>
            </button>
        </div>

    </form>

    {{-- Live Storefront Preview --}}
    <section class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-200 pb-4">
            <div>
                <h2 class="text-lg font-semibold text-gray-900">
                    Live Storefront Preview
                </h2>
                <p class="mt-1 text-sm text-gray-500">
                    Preview your theme changes in real time across all sections before publishing.
                </p>
            </div>

            <div class="flex items-center gap-2">
                <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700">
                    Live Preview
                </span>
                <span class="rounded-full bg-gray-100 px-3 py-1 text-xs text-gray-600">
                    Max: {{ $pageWidth }}
                </span>
            </div>
        </div>

        @php
            $previewCategories = [
                ['name' => 'Clothing', 'icon' => '👕'],
                ['name' => 'Footwear', 'icon' => '👟'],
                ['name' => 'Accessories', 'icon' => '🎒'],
                ['name' => 'Electronics', 'icon' => '🎧'],
            ];

            $previewCategoryCols = match ((int) $categoryColumns) {
                2 => 'grid-cols-2',
                3 => 'grid-cols-2 sm:grid-cols-3',
                default => 'grid-cols-2 sm:grid-cols-3 lg:grid-cols-4',
            };

            $previewProductCols = match ((int) $productsColumns) {
                2 => 'grid-cols-2',
                3 => 'grid-cols-2 sm:grid-cols-3',
                default => 'grid-cols-2 sm:grid-cols-3 lg:grid-cols-4',
            };

            $previewProductCount = min((int) $productsCount, 8);
        @endphp

        <div
            class="mt-6 overflow-hidden rounded-xl border border-gray-300 shadow-md transition-all"
            style="background-color: {{ $backgroundColor }}; color: {{ $textColor }};"
        >
            {{-- Store Header Preview --}}
            <header
                class="flex items-center justify-between gap-3 px-6 py-4"
                style="background-color: {{ $secondaryColor }}; color: #ffffff;"
            >
                <div class="flex items-center gap-2">
                    <div
                        class="flex h-7 w-7 items-center justify-center rounded font-bold text-white text-xs"
                        style="background-color: {{ $primaryColor }};"
                    >
                        C
                    </div>
                    <span class="font-bold text-sm tracking-tight">
                        {{ $themeName ?: 'CustomCommerce' }}
                    </span>
                </div>

                <div class="flex items-center gap-4 text-xs font-medium opacity-90">
                    <span>Home</span>
                    <span>Shop</span>
                    <span>Categories</span>
                    <span>Cart</span>
                </div>
            </header>

            {{-- Hero Section Preview --}}
            @if ($showHero)
                <section
                    class="px-6 py-12 sm:px-10 sm:py-16 transition-all"
                    style="background-color: {{ $secondaryColor }}; color: #ffffff;"
                >
                    <div class="max-w-2xl">
                        <span
                            class="text-xs font-bold uppercase tracking-widest"
                            style="color: {{ $primaryColor }};"
                        >
                            {{ $heroEyebrow }}
                        </span>

                        <h3 class="mt-3 text-2xl font-extrabold sm:text-4xl tracking-tight">
                            {{ $heroHeading }}
                            <span style="color: {{ $primaryColor }};">
                                {{ $heroHighlight }}
                            </span>
                        </h3>

                        <p class="mt-4 max-w-xl text-sm leading-6 text-gray-300">
                            {{ $heroDescription }}
                        </p>

                        <div class="mt-6 flex flex-wrap gap-3">
                            <span
                                class="rounded-lg px-5 py-2.5 text-xs font-semibold shadow-sm"
                                style="background-color: {{ $primaryColor }}; color: #ffffff;"
                            >
                                {{ $heroPrimaryButtonText }}
                            </span>

                            <span
                                class="rounded-lg border border-white/30 px-5 py-2.5 text-xs font-semibold hover:bg-white/10"
                            >
                                {{ $heroSecondaryButtonText }}
                            </span>
                        </div>
                    </div>
                </section>
            @else
                <div class="border-b border-dashed border-gray-300 bg-gray-50/50 p-4 text-center text-xs text-gray-500">
                    Hero Section is hidden.
                </div>
            @endif

            {{-- Categories Section Preview --}}
            @if ($showCategories)
                <section class="border-t border-gray-100 px-6 py-10 sm:px-10">
                    <div class="mb-6">
                        <h3 class="text-xl font-bold" style="color: {{ $textColor }};">
                            {{ $categoryHeading }}
                        </h3>
                        <p class="mt-1 text-xs opacity-75">
                            {{ $categoryDescription }}
                        </p>
                    </div>

                    <div class="grid {{ $previewCategoryCols }} gap-4">
                        @foreach ($previewCategories as $cat)
                            <div
                                wire:key="preview-cat-{{ $loop->index }}"
                                class="rounded-xl border border-gray-200 bg-white p-4 text-center shadow-sm"
                            >
                                <div
                                    class="mx-auto flex h-10 w-10 items-center justify-center rounded-full text-lg"
                                    style="background-color: {{ $primaryColor }}15; color: {{ $primaryColor }};"
                                >
                                    {{ $cat['icon'] }}
                                </div>
                                <p class="mt-2 text-xs font-semibold" style="color: {{ $textColor }};">
                                    {{ $cat['name'] }}
                                </p>
                            </div>
                        @endforeach
                    </div>
                </section>
            @else
                <div class="border-t border-b border-dashed border-gray-300 bg-gray-50/50 p-4 text-center text-xs text-gray-500">
                    Categories Section is hidden.
                </div>
            @endif

            {{-- Featured Products Section Preview --}}
            @if ($showProducts)
                <section class="border-t border-gray-200 bg-gray-50/60 px-6 py-10 sm:px-10">
                    <div class="mb-6 flex items-end justify-between gap-4">
                        <div>
                            <h3 class="text-xl font-bold" style="color: {{ $textColor }};">
                                {{ $productsHeading }}
                            </h3>
                            <p class="mt-1 text-xs opacity-75">
                                {{ $productsDescription }}
                            </p>
                        </div>

                        <span
                            class="text-xs font-semibold"
                            style="color: {{ $primaryColor }};"
                        >
                            {{ $productsViewAllText }} &rarr;
                        </span>
                    </div>

                    <div class="grid {{ $previewProductCols }} gap-4">
                        @for ($i = 1; $i <= $previewProductCount; $i++)
                            <div
                                wire:key="preview-prod-{{ $i }}"
                                class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm"
                            >
                                <div
                                    class="flex aspect-square items-center justify-center text-xl font-semibold opacity-70"
                                    style="background-color: {{ $primaryColor }}10; color: {{ $primaryColor }};"
                                >
                                    Product {{ $i }}
                                </div>

                                <div class="p-3">
                                    <p class="text-xs font-semibold truncate" style="color: {{ $textColor }};">
                                        Sample Product Item {{ $i }}
                                    </p>
                                    <div class="mt-1 flex items-center gap-2">
                                        <span class="text-xs font-bold" style="color: {{ $textColor }};">
                                            ₹{{ number_format($i * 499, 2) }}
                                        </span>
                                        <span class="text-[10px] text-gray-400 line-through">
                                            ₹{{ number_format($i * 599, 2) }}
                                        </span>
                                    </div>
                                    <div
                                        class="mt-3 block w-full rounded py-1.5 text-center text-[11px] font-medium text-white"
                                        style="background-color: {{ $secondaryColor }};"
                                    >
                                        View Product
                                    </div>
                                </div>
                            </div>
                        @endfor
                    </div>
                </section>
            @else
                <div class="border-t border-b border-dashed border-gray-300 bg-gray-50/50 p-4 text-center text-xs text-gray-500">
                    Featured Products Section is hidden.
                </div>
            @endif

            {{-- Call to Action Banner Preview --}}
            @if ($showCta)
                <section class="p-6 sm:p-10">
                    <div
                        class="rounded-2xl px-6 py-10 text-center text-white shadow-md"
                        style="background-color: {{ $primaryColor }};"
                    >
                        <h3 class="text-2xl font-bold">
                            {{ $ctaHeading }}
                        </h3>

                        <p class="mx-auto mt-2 max-w-lg text-xs text-white/90">
                            {{ $ctaDescription }}
                        </p>

                        <div class="mt-5 inline-block">
                            <span
                                class="rounded-lg bg-white px-5 py-2.5 text-xs font-semibold shadow-sm"
                                style="color: {{ $primaryColor }};"
                            >
                                {{ $ctaButtonText }}
                            </span>
                        </div>
                    </div>
                </section>
            @else
                <div class="border-t border-dashed border-gray-300 bg-gray-50/50 p-4 text-center text-xs text-gray-500">
                    CTA Banner is hidden.
                </div>
            @endif

            {{-- Footer Preview --}}
            <footer
                class="px-6 py-6 text-center text-xs"
                style="background-color: {{ $secondaryColor }}; color: #ffffff;"
            >
                <p class="opacity-80">
                    © {{ date('Y') }} {{ $themeName ?: 'CustomCommerce' }}. All rights reserved.
                </p>
            </footer>
        </div>

        {{-- Publish action --}}
        <div class="mt-6 flex items-center justify-between rounded-xl bg-gray-50 p-4 border border-gray-200">
            <div class="text-sm text-gray-600">
                Ready to make these changes live on your storefront?
            </div>

            <button
                type="button"
                wire:click="publishTheme"
                wire:loading.attr="disabled"
                wire:target="publishTheme"
                class="rounded-lg bg-green-600 px-6 py-3 text-sm font-semibold text-white shadow-sm hover:bg-green-700 disabled:opacity-50 transition"
            >
                <span wire:loading.remove wire:target="publishTheme">
                    Publish Theme
                </span>
                <span wire:loading wire:target="publishTheme">
                    Publishing...
                </span>
            </button>
        </div>
    </section>

</div>
