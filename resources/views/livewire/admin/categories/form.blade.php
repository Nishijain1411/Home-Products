<div class="p-4 md:p-6 max-w-6xl mx-auto space-y-6">

    {{-- Breadcrumb & Header --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between border-b border-gray-200 pb-5">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.categories.index') }}"
                class="flex h-9 w-9 items-center justify-center rounded-lg border border-gray-300 bg-white text-gray-600 shadow-sm transition hover:bg-gray-50 hover:text-gray-900"
                title="Back to Categories">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
            <div>
                <div class="flex items-center gap-2 text-xs font-medium text-gray-500">
                    <a href="{{ route('admin.categories.index') }}" class="hover:text-blue-600 transition">Categories</a>
                    <span>/</span>
                    <span class="text-gray-800">{{ $isEditing ? 'Edit Category' : 'Add Category' }}</span>
                </div>
                <h1 class="text-2xl font-bold tracking-tight text-gray-900 mt-1">
                    {{ $isEditing ? ($name ?: 'Edit Category') : 'Add Category' }}
                </h1>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('admin.categories.index') }}"
                class="rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 transition">
                Cancel
            </a>

            <button type="button" wire:click="save" wire:loading.attr="disabled"
                class="inline-flex items-center justify-center gap-2 rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-medium text-white shadow-sm hover:bg-blue-700 transition disabled:opacity-50">
                <span wire:loading.remove wire:target="save">
                    {{ $isEditing ? 'Update Category' : 'Save Category' }}
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

    {{-- Form Body --}}
    <form wire:submit="save" class="grid grid-cols-1 gap-6 lg:grid-cols-12">

        {{-- Main Column (8 cols) --}}
        <div class="space-y-6 lg:col-span-8">

            {{-- Basic Information --}}
            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                <div class="mb-5 border-b border-gray-100 pb-3">
                    <h3 class="text-base font-semibold text-gray-900">
                        Category Details
                    </h3>
                    <p class="mt-0.5 text-xs text-gray-500">
                        Specify name, URL slug, and description for this product collection.
                    </p>
                </div>

                <div class="space-y-4">
                    {{-- Category Name --}}
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700">
                            Category Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" wire:model.live="name" placeholder="e.g. Footwear, Electronics, Winter Wear"
                            class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 placeholder:text-gray-400 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10">
                        @error('name')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Slug --}}
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700">
                            URL Slug <span class="text-red-500">*</span>
                        </label>
                        <input type="text" wire:model="slug" placeholder="e.g. footwear"
                            class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 placeholder:text-gray-400 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10">
                        @error('slug')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Description --}}
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700">
                            Description
                        </label>
                        <textarea wire:model="description" rows="4" placeholder="Brief description of this collection for customers..."
                            class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 placeholder:text-gray-400 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10"></textarea>
                        @error('description')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- Category Image --}}
            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                <div class="mb-5 border-b border-gray-100 pb-3">
                    <h3 class="text-base font-semibold text-gray-900">
                        Category Image
                    </h3>
                    <p class="mt-0.5 text-xs text-gray-500">
                        This image will be displayed on the storefront category banner and catalog navigation.
                    </p>
                </div>

                {{-- Case 1: Newly uploaded temporary image --}}
                @if ($image)
                    <div class="relative overflow-hidden rounded-xl border border-gray-200 bg-gray-50 p-4">
                        <div class="flex items-center gap-4">
                            <div class="relative h-24 w-24 shrink-0 overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                                <img src="{{ $image->temporaryUrl() }}" alt="Category preview" class="h-full w-full object-cover">
                            </div>

                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex items-center rounded-md bg-blue-50 px-2 py-0.5 text-xs font-medium text-blue-700 ring-1 ring-inset ring-blue-600/20">
                                        New Upload
                                    </span>
                                    <p class="truncate text-xs font-medium text-gray-700">
                                        {{ $image->getClientOriginalName() }}
                                    </p>
                                </div>

                                <p class="mt-1 text-xs text-gray-500">
                                    {{ number_format($image->getSize() / 1024, 1) }} KB · Ready to save
                                </p>

                                <div class="mt-3 flex items-center gap-3">
                                    <label for="category-form-image" class="cursor-pointer text-xs font-semibold text-blue-600 hover:text-blue-800 underline">
                                        Change image
                                    </label>
                                    <span class="text-xs text-gray-300">|</span>
                                    <button type="button" wire:click="removeImage" class="text-xs font-semibold text-red-600 hover:text-red-800">
                                        Remove
                                    </button>
                                </div>
                            </div>
                        </div>

                        <input type="file" id="category-form-image" wire:model="image" accept="image/png,image/jpeg,image/webp" class="hidden">
                    </div>

                {{-- Case 2: Existing saved image from database --}}
                @elseif ($existingImage)
                    <div class="relative overflow-hidden rounded-xl border border-gray-200 bg-gray-50 p-4">
                        <div class="flex items-center gap-4">
                            <div class="relative h-24 w-24 shrink-0 overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                                <img src="{{ asset('storage/' . $existingImage) }}" alt="{{ $name }}" class="h-full w-full object-cover">
                            </div>

                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex items-center rounded-md bg-emerald-50 px-2 py-0.5 text-xs font-medium text-emerald-700 ring-1 ring-inset ring-emerald-600/20">
                                        Current Image
                                    </span>
                                </div>

                                <p class="mt-1 truncate text-xs text-gray-500">
                                    {{ basename($existingImage) }}
                                </p>

                                <div class="mt-3 flex items-center gap-3">
                                    <label for="category-form-image" class="cursor-pointer text-xs font-semibold text-blue-600 hover:text-blue-800 underline">
                                        Replace image
                                    </label>
                                    <span class="text-xs text-gray-300">|</span>
                                    <button type="button" wire:click="removeExistingImage" class="text-xs font-semibold text-red-600 hover:text-red-800">
                                        Remove
                                    </button>
                                </div>
                            </div>
                        </div>

                        <input type="file" id="category-form-image" wire:model="image" accept="image/png,image/jpeg,image/webp" class="hidden">
                    </div>

                {{-- Case 3: No image uploaded yet --}}
                @else
                    <label for="category-form-image"
                        class="flex cursor-pointer flex-col items-center justify-center rounded-xl border-2 border-dashed border-gray-300 bg-gray-50/70 px-6 py-8 transition hover:border-blue-400 hover:bg-blue-50/20">
                        <div class="mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-white text-gray-400 shadow-sm">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>

                        <span class="text-sm font-semibold text-blue-600 hover:text-blue-800">
                            Click to upload category image
                        </span>

                        <span class="mt-1 text-xs text-gray-400">
                            PNG, JPG or WEBP · Maximum 2MB
                        </span>

                        <input type="file" id="category-form-image" wire:model="image" accept="image/png,image/jpeg,image/webp"
                            class="hidden">
                    </label>
                @endif

                {{-- Upload Progress / Loading Indicator --}}
                <div wire:loading wire:target="image" class="mt-3 flex items-center gap-2 text-xs font-medium text-blue-600">
                    <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                    </svg>
                    Uploading image, please wait...
                </div>

                @error('image')
                <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Search Engine Optimization (SEO) --}}
            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                <div class="mb-5 border-b border-gray-100 pb-3">
                    <h3 class="text-base font-semibold text-gray-900">
                        Search Engine Optimization
                    </h3>
                    <p class="mt-0.5 text-xs text-gray-500">
                        Configure search engine title and meta description.
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
                        Status & Hierarchy
                    </h3>
                </div>

                <div class="space-y-4">
                    {{-- Active Checkbox --}}
                    <label class="flex cursor-pointer items-start justify-between gap-3 rounded-lg border border-gray-200 p-3 hover:bg-gray-50 transition">
                        <div>
                            <p class="text-sm font-semibold text-gray-900">Active Category</p>
                            <p class="text-xs text-gray-500 mt-0.5">Visible and assignable to products</p>
                        </div>
                        <input type="checkbox" wire:model="is_active"
                            class="mt-0.5 h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    </label>

                    {{-- Parent Category --}}
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold text-gray-700">
                            Parent Category
                        </label>
                        <select wire:model="parent_id"
                            class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 outline-none focus:border-blue-500">
                            <option value="">None (Top-Level Category)</option>
                            @foreach ($parentCategories as $parent)
                            <option value="{{ $parent->id }}">{{ $parent->name }}</option>
                            @endforeach
                        </select>
                        @error('parent_id')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Collection Type --}}
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold text-gray-700">
                            Collection Type
                        </label>
                        <select wire:model="collection_type"
                            class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 outline-none focus:border-blue-500">
                            <option value="manual">Manual</option>
                            <option value="automated">Automated</option>
                        </select>
                    </div>

                    {{-- Sort Order --}}
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold text-gray-700">
                            Sort Order
                        </label>
                        <input type="number" min="0" wire:model="sort_order" placeholder="0"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-900 outline-none focus:border-blue-500">
                        @error('sort_order')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- Bottom Save Card --}}
            <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm flex items-center justify-between gap-3">
                <a href="{{ route('admin.categories.index') }}"
                    class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
                    Discard
                </a>

                <button type="button" wire:click="save" wire:loading.attr="disabled"
                    class="rounded-lg bg-blue-600 px-5 py-2 text-sm font-semibold text-white shadow hover:bg-blue-700 transition disabled:opacity-50">
                    <span wire:loading.remove wire:target="save">
                        {{ $isEditing ? 'Update Category' : 'Save Category' }}
                    </span>
                    <span wire:loading wire:target="save">Saving...</span>
                </button>
            </div>

        </div>

    </form>
</div>
