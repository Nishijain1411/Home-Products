<div class="p-4 md:p-6 max-w-7xl mx-auto space-y-6">

    {{-- Breadcrumb & Header --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between border-b border-gray-200 pb-5">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.products.index') }}"
                class="flex h-9 w-9 items-center justify-center rounded-lg border border-gray-300 bg-white text-gray-600 shadow-sm transition hover:bg-gray-50 hover:text-gray-900"
                title="Back to Products">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
            <div>
                <div class="flex items-center gap-2 text-xs font-medium text-gray-500">
                    <a href="{{ route('admin.products.index') }}" class="hover:text-blue-600 transition">Products</a>
                    <span>/</span>
                    <span class="text-gray-800">{{ $isEditing ? 'Edit Product' : 'Add Product' }}</span>
                </div>
                <h1 class="text-2xl font-bold tracking-tight text-gray-900 mt-1">
                    {{ $isEditing ? ($name ?: 'Edit Product') : 'Add Product' }}
                </h1>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('admin.products.index') }}"
                class="rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 transition">
                Cancel
            </a>

            <button type="button" wire:click="save" wire:loading.attr="disabled"
                class="inline-flex items-center justify-center gap-2 rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-medium text-white shadow-sm hover:bg-blue-700 transition disabled:opacity-50">
                <span wire:loading.remove wire:target="save">
                    {{ $isEditing ? 'Update Product' : 'Save Product' }}
                </span>
                <span wire:loading wire:target="save" class="inline-flex items-center gap-2">
                    <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z" />
                    </svg>
                    Saving...
                </span>
            </button>
        </div>
    </div>

    {{-- Form Body: 2 Columns on desktop --}}
    <form wire:submit="save" class="grid grid-cols-1 gap-6 lg:grid-cols-12">

        {{-- Main Column (8 cols) --}}
        <div class="space-y-6 lg:col-span-8">

            {{-- Basic Information --}}
            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                <div class="mb-5 border-b border-gray-100 pb-3">
                    <h3 class="text-base font-semibold text-gray-900">
                        Basic Information
                    </h3>
                    <p class="mt-0.5 text-xs text-gray-500">
                        Primary details, title, slug, and description for your product.
                    </p>
                </div>

                <div class="space-y-4">
                    {{-- Product Name --}}
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700">
                            Product Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" wire:model.live="name" placeholder="e.g. Wireless Noise-Cancelling Headphones"
                            class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 placeholder:text-gray-400 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10">
                        @error('name')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        {{-- SKU --}}
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700">
                                SKU <span class="text-red-500">*</span>
                            </label>
                            <input type="text" wire:model="sku" placeholder="e.g. PROD-001"
                                class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 placeholder:text-gray-400 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10">
                            @error('sku')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Slug --}}
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700">
                                Slug <span class="text-red-500">*</span>
                            </label>
                            <input type="text" wire:model="slug" placeholder="e.g. wireless-headphones"
                                class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 placeholder:text-gray-400 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10">
                            @error('slug')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Description --}}
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700">
                            Description
                        </label>
                        <textarea wire:model="description" rows="5" placeholder="Detailed product description..."
                            class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 placeholder:text-gray-400 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10"></textarea>
                        @error('description')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- Media / Product Images --}}
            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                <div class="mb-5 border-b border-gray-100 pb-3">
                    <h3 class="text-base font-semibold text-gray-900">
                        Product Images
                    </h3>
                    <p class="mt-0.5 text-xs text-gray-500">
                        Upload high quality product images. The first image or starred image is the primary photo.
                    </p>
                </div>

                {{-- Existing Images (for edit mode) --}}
                @if (count($existingProductImages))
                <div class="mb-5">
                    <h4 class="mb-3 text-xs font-semibold uppercase tracking-wider text-gray-500">
                        Current Images ({{ count($existingProductImages) }})
                    </h4>
                    <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                        @foreach ($existingProductImages as $image)
                        <div wire:key="existing-img-{{ $image['id'] }}"
                            class="group relative overflow-hidden rounded-xl border border-gray-200 bg-gray-50 shadow-sm">
                            <img src="{{ Storage::disk('public')->url($image['path']) }}"
                                class="aspect-square w-full object-cover"
                                alt="{{ $image['alt_text'] ?? 'Product image' }}">

                            @if ($image['is_primary'])
                            <span class="absolute left-2 top-2 rounded-md bg-blue-600 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-white shadow-sm">
                                Primary
                            </span>
                            @endif

                            <div class="absolute bottom-2 left-2 right-2 flex items-center justify-between">
                                <div class="flex items-center gap-1">
                                    <button type="button" wire:click="moveImageUp({{ $image['id'] }})"
                                        title="Move earlier"
                                        class="flex h-7 w-7 items-center justify-center rounded-full bg-white/95 text-gray-600 shadow transition hover:bg-gray-100">
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                                        </svg>
                                    </button>
                                    <button type="button" wire:click="moveImageDown({{ $image['id'] }})"
                                        title="Move later"
                                        class="flex h-7 w-7 items-center justify-center rounded-full bg-white/95 text-gray-600 shadow transition hover:bg-gray-100">
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                        </svg>
                                    </button>
                                </div>

                                <div class="flex items-center gap-1">
                                    <button type="button" wire:click="setPrimaryImage({{ $image['id'] }})"
                                        title="{{ $image['is_primary'] ? 'Primary Image' : 'Set as Primary' }}"
                                        class="flex h-7 w-7 items-center justify-center rounded-full bg-white/95 shadow transition hover:bg-blue-50">
                                        <svg class="h-3.5 w-3.5 {{ $image['is_primary'] ? 'text-amber-500 fill-amber-500' : 'text-gray-400' }}"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                                d="M12 3l2.78 5.63 6.22.9-4.5 4.39 1.06 6.2L12 17.2l-5.56 2.92 1.06-6.2L3 9.53l6.22-.9L12 3z" />
                                        </svg>
                                    </button>
                                    <button type="button" wire:click="removeExistingProductImage({{ $image['id'] }})"
                                        title="Delete image"
                                        class="flex h-7 w-7 items-center justify-center rounded-full bg-white/95 text-red-500 shadow transition hover:bg-red-50 hover:text-red-700">
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- Upload Dropzone --}}
                <div class="rounded-xl border-2 border-dashed border-gray-300 bg-gray-50/70 p-6 text-center transition hover:border-blue-400 hover:bg-blue-50/20">
                    <input type="file" wire:model="newProductImages" multiple accept="image/jpeg,image/png,image/webp"
                        class="hidden" id="product-page-images">
                    <label for="product-page-images" class="cursor-pointer block">
                        <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-white text-gray-400 shadow-sm">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <span class="text-sm font-semibold text-blue-600 hover:text-blue-800">Click to upload photos</span>
                        <span class="text-sm text-gray-500"> or drag and drop</span>
                        <p class="mt-1 text-xs text-gray-400">JPG, PNG, or WebP up to 5MB each</p>
                    </label>
                </div>

                <div wire:loading wire:target="newProductImages" class="mt-3 text-xs text-blue-600 flex items-center gap-1.5">
                    <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z" />
                    </svg>
                    Uploading images, please wait...
                </div>

                @error('newProductImages.*')
                <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
                @enderror

                {{-- New Images Preview --}}
                @if (count($productImages))
                <div class="mt-5">
                    <div class="mb-3 flex items-center justify-between">
                        <h4 class="text-xs font-semibold uppercase tracking-wider text-gray-500">
                            Newly Selected Images ({{ count($productImages) }})
                        </h4>
                    </div>
                    <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                        @foreach ($productImages as $index => $image)
                        <div wire:key="new-img-preview-{{ $index }}"
                            class="group relative overflow-hidden rounded-xl border border-gray-200 bg-gray-100 shadow-sm">
                            <img src="{{ $image->temporaryUrl() }}" class="aspect-square w-full object-cover" alt="Preview">
                            <button type="button" wire:click="removeProductImage({{ $index }})"
                                class="absolute right-2 top-2 flex h-7 w-7 items-center justify-center rounded-full bg-white/90 text-gray-500 shadow-sm transition hover:bg-red-50 hover:text-red-600">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>

            {{-- Options & Variants --}}
            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                <div class="mb-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-b border-gray-100 pb-3">
                    <div>
                        <h3 class="text-base font-semibold text-gray-900">
                            Options & Variants
                        </h3>
                        <p class="mt-0.5 text-xs text-gray-500">
                            Add attributes such as Size, Color, or Material to generate variants.
                        </p>
                    </div>

                    <button type="button" wire:click="addOption"
                        class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 bg-white px-3.5 py-2 text-xs font-semibold text-gray-700 shadow-sm hover:bg-gray-50 transition">
                        <svg class="h-4 w-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Add Option
                    </button>
                </div>

                @if (count($productOptions))
                <div class="space-y-4">
                    @foreach ($productOptions as $optionIndex => $option)
                    <div wire:key="opt-card-{{ $optionIndex }}" class="rounded-xl border border-gray-200 bg-gray-50 p-4">
                        <div class="mb-3 flex items-center justify-between gap-3">
                            <div class="flex-1">
                                <label class="mb-1 block text-xs font-semibold text-gray-600">
                                    Option Name
                                </label>
                                <input type="text" wire:model="productOptions.{{ $optionIndex }}.name"
                                    placeholder="e.g. Size, Color, Material"
                                    class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10">
                            </div>

                            <button type="button" wire:click="removeOption({{ $optionIndex }})"
                                class="mt-5 rounded-lg p-2 text-red-500 hover:bg-red-50 hover:text-red-700 transition" title="Delete Option">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3m-7 0h10" />
                                </svg>
                            </button>
                        </div>

                        <div>
                            <label class="mb-1.5 block text-xs font-semibold text-gray-600">
                                Option Values
                            </label>
                            <div class="space-y-2">
                                @foreach ($option['values'] as $valueIndex => $value)
                                <div wire:key="opt-val-{{ $optionIndex }}-{{ $valueIndex }}" class="flex items-center gap-2">
                                    <input type="text" wire:model="productOptions.{{ $optionIndex }}.values.{{ $valueIndex }}"
                                        placeholder="e.g. Small, Medium, Red, Blue"
                                        class="flex-1 rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10">
                                    @if (count($option['values']) > 1)
                                    <button type="button" wire:click="removeOptionValue({{ $optionIndex }}, {{ $valueIndex }})"
                                        class="rounded-lg p-2 text-gray-400 hover:bg-gray-200 hover:text-red-600 transition">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                    @endif
                                </div>
                                @endforeach
                            </div>

                            <button type="button" wire:click="addOptionValue({{ $optionIndex }})"
                                class="mt-2.5 inline-flex items-center gap-1.5 text-xs font-semibold text-blue-600 hover:text-blue-800">
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                                Add another value
                            </button>
                        </div>
                    </div>
                    @endforeach

                    <div class="flex justify-end pt-2">
                        <button type="button" wire:click="generateVariants"
                            class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-700 transition">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            Generate Variants Table
                        </button>
                    </div>
                </div>
                @else
                <div class="rounded-xl border border-dashed border-gray-300 bg-gray-50/70 p-6 text-center text-sm text-gray-500">
                    <p class="font-medium text-gray-700">No variant options configured</p>
                    <p class="text-xs text-gray-400 mt-1">This product will be saved with a single Default Variant.</p>
                </div>
                @endif

                {{-- Generated Variants Table --}}
                @if (count($generatedVariants))
                <div class="mt-6 border-t border-gray-100 pt-5">
                    <div class="mb-3 flex items-center justify-between">
                        <h4 class="text-xs font-semibold uppercase tracking-wider text-gray-500">
                            Generated Variants ({{ count($generatedVariants) }})
                        </h4>
                    </div>

                    <div class="overflow-x-auto rounded-xl border border-gray-200">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-3.5 py-2.5 text-left text-xs font-semibold uppercase text-gray-500">Variant</th>
                                    <th class="px-3.5 py-2.5 text-left text-xs font-semibold uppercase text-gray-500">SKU</th>
                                    <th class="px-3.5 py-2.5 text-left text-xs font-semibold uppercase text-gray-500">Price ({{ currency_symbol() }})</th>
                                    <th class="px-3.5 py-2.5 text-left text-xs font-semibold uppercase text-gray-500">Compare Price</th>
                                    <th class="px-3.5 py-2.5 text-left text-xs font-semibold uppercase text-gray-500">Stock Qty</th>
                                    <th class="px-3.5 py-2.5 text-center text-xs font-semibold uppercase text-gray-500">Active</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                @foreach ($generatedVariants as $index => $variant)
                                <tr wire:key="variant-row-{{ $index }}">
                                    <td class="whitespace-nowrap px-3.5 py-2.5 font-medium text-gray-900">
                                        {{ $variant['title'] }}
                                    </td>
                                    <td class="px-3.5 py-2.5">
                                        <input type="text" wire:model="generatedVariants.{{ $index }}.sku" placeholder="SKU"
                                            class="w-32 rounded-lg border border-gray-300 px-2.5 py-1.5 text-xs outline-none focus:border-blue-500">
                                    </td>
                                    <td class="px-3.5 py-2.5">
                                        <input type="number" step="0.01" min="0" wire:model="generatedVariants.{{ $index }}.price"
                                            class="w-24 rounded-lg border border-gray-300 px-2.5 py-1.5 text-xs outline-none focus:border-blue-500">
                                    </td>
                                    <td class="px-3.5 py-2.5">
                                        <input type="number" step="0.01" min="0" wire:model="generatedVariants.{{ $index }}.compare_at_price"
                                            class="w-24 rounded-lg border border-gray-300 px-2.5 py-1.5 text-xs outline-none focus:border-blue-500">
                                    </td>
                                    <td class="px-3.5 py-2.5">
                                        <input type="number" min="0" wire:model="generatedVariants.{{ $index }}.quantity"
                                            class="w-20 rounded-lg border border-gray-300 px-2.5 py-1.5 text-xs outline-none focus:border-blue-500">
                                    </td>
                                    <td class="px-3.5 py-2.5 text-center">
                                        <input type="checkbox" wire:model="generatedVariants.{{ $index }}.is_active"
                                            class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif
            </div>

            {{-- Search Engine Optimization (SEO) --}}
            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                <div class="mb-5 border-b border-gray-100 pb-3">
                    <h3 class="text-base font-semibold text-gray-900">
                        Search Engine Optimization
                    </h3>
                    <p class="mt-0.5 text-xs text-gray-500">
                        Customize how this product appears in Google and search engines.
                    </p>
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700">
                            SEO Title
                        </label>
                        <input type="text" wire:model="seo_title" placeholder="Meta title"
                            class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10">
                        @error('seo_title')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700">
                            SEO Description
                        </label>
                        <textarea wire:model="seo_description" rows="3" placeholder="Brief summary for search engine snippet..."
                            class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10"></textarea>
                        @error('seo_description')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

        </div>

        {{-- Sidebar Column (4 cols) --}}
        <div class="space-y-6 lg:col-span-4">

            {{-- Status & Visibility --}}
            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                <div class="mb-4 border-b border-gray-100 pb-2">
                    <h3 class="text-sm font-semibold text-gray-900">
                        Status & Visibility
                    </h3>
                </div>

                <div class="space-y-4">
                    <label class="flex cursor-pointer items-start justify-between gap-3 rounded-lg border border-gray-200 p-3 hover:bg-gray-50 transition">
                        <div>
                            <p class="text-sm font-semibold text-gray-900">Active Status</p>
                            <p class="text-xs text-gray-500 mt-0.5">Show product on storefront</p>
                        </div>
                        <input type="checkbox" wire:model="is_active"
                            class="mt-0.5 h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    </label>

                    <label class="flex cursor-pointer items-start justify-between gap-3 rounded-lg border border-gray-200 p-3 hover:bg-gray-50 transition">
                        <div>
                            <p class="text-sm font-semibold text-gray-900">Featured Product</p>
                            <p class="text-xs text-gray-500 mt-0.5">Highlight on homepage collections</p>
                        </div>
                        <input type="checkbox" wire:model="is_featured"
                            class="mt-0.5 h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    </label>

                    <div>
                        <label class="mb-1 block text-xs font-semibold text-gray-700">Sort Order</label>
                        <input type="number" min="0" wire:model="sort_order" placeholder="0"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-900 outline-none focus:border-blue-500">
                    </div>
                </div>
            </div>

            {{-- Pricing --}}
            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                <div class="mb-4 border-b border-gray-100 pb-2">
                    <h3 class="text-sm font-semibold text-gray-900">
                        Pricing
                    </h3>
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="mb-1 block text-xs font-semibold text-gray-700">
                            Base Price <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm text-gray-400">
                                {{ currency_symbol() }}
                            </span>
                            <input type="number" step="0.01" min="0" wire:model="price" placeholder="0.00"
                                class="w-full rounded-lg border border-gray-300 py-2 pl-8 pr-3 text-sm text-gray-900 outline-none focus:border-blue-500">
                        </div>
                        @error('price')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-semibold text-gray-700">
                            Compare-at Price
                        </label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm text-gray-400">
                                {{ currency_symbol() }}
                            </span>
                            <input type="number" step="0.01" min="0" wire:model="compare_at_price" placeholder="0.00"
                                class="w-full rounded-lg border border-gray-300 py-2 pl-8 pr-3 text-sm text-gray-900 outline-none focus:border-blue-500">
                        </div>
                        @error('compare_at_price')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- Organization & Categorization --}}
            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                <div class="mb-4 border-b border-gray-100 pb-2">
                    <h3 class="text-sm font-semibold text-gray-900">
                        Organization
                    </h3>
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="mb-1 block text-xs font-semibold text-gray-700">Product Type</label>
                        <input type="text" wire:model="product_type" placeholder="e.g. Apparel, Electronics"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-900 outline-none focus:border-blue-500">
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-semibold text-gray-700">Vendor / Brand</label>
                        <input type="text" wire:model="vendor" placeholder="e.g. Nike, Apple"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-900 outline-none focus:border-blue-500">
                    </div>

                    {{-- Categories Tree --}}
                    <div>
                        <div class="mb-1.5 flex items-center justify-between">
                            <label class="text-xs font-semibold text-gray-700">Categories</label>
                            @if (count($selectedCategories))
                            <button type="button" wire:click="$set('selectedCategories', [])"
                                class="text-[11px] font-semibold text-blue-600 hover:text-blue-800">
                                Clear
                            </button>
                            @endif
                        </div>

                        <div class="max-h-60 overflow-y-auto rounded-lg border border-gray-200 divide-y divide-gray-100 bg-gray-50/50 p-2">
                            @forelse ($categories as $cat)
                            <div wire:key="sidebar-cat-{{ $cat->id }}" class="py-1">
                                <label class="flex items-center gap-2 cursor-pointer text-xs font-medium text-gray-800">
                                    <input type="checkbox" value="{{ $cat->id }}" wire:model="selectedCategories"
                                        class="h-3.5 w-3.5 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                    <span>{{ $cat->name }}</span>
                                </label>

                                @foreach ($cat->children as $child)
                                <div wire:key="sidebar-child-{{ $child->id }}" class="pl-5 pt-1">
                                    <label class="flex items-center gap-2 cursor-pointer text-xs text-gray-600">
                                        <input type="checkbox" value="{{ $child->id }}" wire:model="selectedCategories"
                                            class="h-3.5 w-3.5 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                        <span>{{ $child->name }}</span>
                                    </label>
                                </div>
                                @endforeach
                            </div>
                            @empty
                            <p class="text-xs text-gray-400 py-2 text-center">No categories available</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            {{-- Bottom Sticky / Floating Save Button --}}
            <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm flex items-center justify-between gap-3">
                <a href="{{ route('admin.products.index') }}"
                    class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
                    Discard
                </a>

                <button type="button" wire:click="save" wire:loading.attr="disabled"
                    class="rounded-lg bg-blue-600 px-5 py-2 text-sm font-semibold text-white shadow hover:bg-blue-700 transition disabled:opacity-50">
                    <span wire:loading.remove wire:target="save">
                        {{ $isEditing ? 'Update Product' : 'Save Product' }}
                    </span>
                    <span wire:loading wire:target="save">Saving...</span>
                </button>
            </div>

        </div>

    </form>
</div>
