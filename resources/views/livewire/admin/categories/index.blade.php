<div class="p-6">

    {{-- Header --}}
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

        <div>
            <h1 class="text-2xl font-semibold text-gray-900">
                Categories
            </h1>

            <p class="mt-1 text-sm text-gray-600">
                Manage your product categories.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            <button type="button" wire:click="openImportModal"
                class="inline-flex items-center justify-center gap-1.5 rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500/20">
                <svg class="h-4 w-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                </svg>
                Import
            </button>

            <button type="button" wire:click="export" wire:loading.attr="disabled"
                class="inline-flex items-center justify-center gap-1.5 rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500/20 disabled:opacity-50">
                <span wire:loading.remove wire:target="export" class="inline-flex items-center gap-1.5">
                    <svg class="h-4 w-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    Export
                </span>
                <span wire:loading wire:target="export" class="inline-flex items-center gap-1.5">
                    <svg class="h-4 w-4 animate-spin text-gray-500" viewBox="0 0 24 24" fill="none">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z" />
                    </svg>
                    Exporting...
                </span>
            </button>

            <a href="{{ route('admin.categories.create') }}"
                class="inline-flex items-center justify-center gap-1.5 rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-medium text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500/20">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Add Category
            </a>
        </div>

    </div>


    {{-- Alerts & Notifications --}}
    @php
        $displaySuccess = $successMessage ?? session('success');
        $displayError = session('error');
    @endphp

    {{-- Floating Toast Notification --}}
    @if ($displaySuccess)
    <div x-data="{ show: true }"
         x-show="show"
         x-init="setTimeout(() => show = false, 6000)"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-[-10px] scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="fixed top-6 right-6 z-50 flex max-w-md items-start gap-3 rounded-xl border border-green-200 bg-white p-4 shadow-xl ring-1 ring-black/5">
        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-green-100 text-green-600">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
        </div>
        <div class="flex-1 pt-0.5">
            <p class="text-sm font-semibold text-gray-900">Success</p>
            <p class="mt-0.5 text-xs text-gray-600 leading-relaxed">{{ $displaySuccess }}</p>
        </div>
        <button type="button" @click="show = false" wire:click="dismissAlert" class="rounded-md p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>
    @endif

    {{-- Inline Banner Alert --}}
    @if ($displaySuccess)
    <div x-data="{ show: true }" x-show="show" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
        class="mb-6 flex items-start justify-between rounded-xl border border-green-200 bg-gradient-to-r from-green-50 via-green-50/70 to-emerald-50/40 p-4 shadow-sm">
        <div class="flex items-center gap-3">
            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-green-100 text-green-600">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div>
                <h4 class="text-sm font-semibold text-green-900">
                    Operation Successful
                </h4>
                <p class="text-sm text-green-700 mt-0.5">
                    {{ $displaySuccess }}
                </p>
            </div>
        </div>
        <button type="button" @click="show = false" wire:click="dismissAlert"
            class="rounded-lg p-1.5 text-green-600 hover:bg-green-100 hover:text-green-800 transition">
            <span class="sr-only">Dismiss</span>
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>
    @endif

    @if ($displayError)
    <div x-data="{ show: true }" x-show="show"
        class="mb-6 flex items-start justify-between rounded-xl border border-red-200 bg-red-50 p-4 shadow-sm">
        <div class="flex items-center gap-3">
            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-red-100 text-red-600">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div>
                <h4 class="text-sm font-semibold text-red-900">
                    Error
                </h4>
                <p class="text-sm text-red-700 mt-0.5">
                    {{ $displayError }}
                </p>
            </div>
        </div>
        <button type="button" @click="show = false"
            class="rounded-lg p-1.5 text-red-600 hover:bg-red-100 hover:text-red-800 transition">
            <span class="sr-only">Dismiss</span>
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>
    @endif


    {{-- Search --}}
    <div class="mb-5 rounded-lg bg-white p-4 shadow-sm">

        <div class="relative max-w-md">

            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search categories..."
                class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-blue-500 focus:ring-blue-500">

        </div>

    </div>


    {{-- Categories Table --}}
    <div class="overflow-hidden rounded-lg bg-white shadow-sm">

        <div class="overflow-x-auto">

            <table class="min-w-full divide-y divide-gray-200">

                <thead class="bg-gray-50">

                    <tr>

                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                            Category
                        </th>

                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                            Slug
                        </th>

                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                            Parent
                        </th>

                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                            Status
                        </th>

                        <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">
                            Actions
                        </th>

                    </tr>

                </thead>


                <tbody class="divide-y divide-gray-200 bg-white">

                    @forelse ($categories as $category)

                    <tr wire:key="category-{{ $category->id }}">

                        {{-- Name --}}
                        <td class="whitespace-nowrap px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="h-10 w-10 shrink-0 overflow-hidden rounded-lg border border-gray-200 bg-gray-50 flex items-center justify-center">
                                    @if ($category->image && Storage::disk('public')->exists($category->image))
                                        <img src="{{ asset('storage/' . $category->image) }}" alt="{{ $category->name }}" class="h-full w-full object-cover">
                                    @else
                                        <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                    @endif
                                </div>
                                <div>
                                    <div class="font-medium text-gray-900">
                                        {{ $category->name }}
                                    </div>

                                    @if ($category->description)
                                    <div class="mt-0.5 max-w-sm truncate text-xs text-gray-500">
                                        {{ $category->description }}
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </td>


                        {{-- Slug --}}
                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-600">
                            {{ $category->slug }}
                        </td>


                        {{-- Parent --}}
                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-600">

                            @if ($category->parent)
                            {{ $category->parent->name }}
                            @else
                            <span class="text-gray-400">
                                —
                            </span>
                            @endif

                        </td>


                        {{-- Status --}}
                        <td class="whitespace-nowrap px-6 py-4">

                            @if ($category->is_active)

                            <button wire:click="toggleStatus({{ $category->id }})"
                                class="rounded-full bg-green-100 px-3 py-1 text-xs font-medium text-green-700 hover:bg-green-200">
                                Active
                            </button>

                            @else

                            <button wire:click="toggleStatus({{ $category->id }})"
                                class="rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-600 hover:bg-gray-200">
                                Inactive
                            </button>

                            @endif

                        </td>


                        {{-- Actions --}}
                        <td class="whitespace-nowrap px-6 py-4 text-right">

                            <a href="{{ route('admin.categories.edit', $category->id) }}"
                                class="mr-3 text-sm font-medium text-blue-600 hover:text-blue-800">
                                Edit
                            </a>

                            <button wire:click="confirmDelete({{ $category->id }})"
                                class="text-sm font-medium text-red-600 hover:text-red-800">
                                Delete
                            </button>

                        </td>

                    </tr>

                    @empty

                    <tr>

                        <td colspan="5" class="px-6 py-12 text-center text-sm text-gray-500">
                            No categories found.
                        </td>

                    </tr>

                    @endforelse

                </tbody>

            </table>

        </div>


        {{-- Pagination --}}
        @if ($categories->hasPages())

        <div class="border-t border-gray-200 px-6 py-4">
            {{ $categories->links() }}
        </div>

        @endif

    </div>



    {{-- Create / Edit Modal --}}
    @if ($showModal)
    <div class="fixed inset-0 z-[9999] flex items-start justify-center bg-black/50 px-4 pt-[80px] pb-4">
        <div class="flex w-full max-w-3xl max-h-[calc(100vh-96px)] flex-col overflow-hidden rounded-2xl bg-white shadow-2xl"
            wire:click.outside="closeModal">

            <div class="border-b border-gray-200 bg-white px-6 py-5">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-xl font-semibold text-gray-900">
                            {{ $editingId ? 'Edit Category' : 'Create Category' }}
                        </h2>

                        <p class="mt-1 text-sm text-gray-500">
                            {{ $editingId ? 'Update the details of this category.' : 'Add a new category to organize your products.'}}
                        </p>
                    </div>

                    <button type="button" wire:click="closeModal"
                        class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-gray-400 transition hover:bg-gray-100 hover:text-gray-600">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            <form wire:submit="save" class="flex min-h-0 flex-1 flex-col">

                <div class="min-h-0 flex-1 overflow-y-auto bg-gray-50/50 px-6 py-6">

                    <div class="space-y-5">
                        {{-- Basic Information --}}
                        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                            <div class="mb-5">
                                <h3 class="text-sm font-semibold text-gray-900">
                                    Basic Information
                                </h3>

                                <p class="mt-1 text-xs text-gray-500">
                                    Add the basic details for this category.
                                </p>
                            </div>

                            <div class="grid grid-cols-1 gap-x-5 gap-y-4 md:grid-cols-2">

                                {{-- Category Name --}}
                                <div>
                                    <label class="mb-1.5 block text-sm font-medium text-gray-700">
                                        Category Name <span class="text-red-500">*</span>
                                    </label>

                                    <input wire:model="name"
                                        class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 placeholder:text-gray-400 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10">

                                    @error('name')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Slug --}}
                                <div>
                                    <label class="mb-1.5 block text-sm font-medium text-gray-700">
                                        Slug <span class="text-red-500">*</span>
                                    </label>

                                    <input wire:model="slug"
                                        class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 placeholder:text-gray-400 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10">

                                    @error('slug')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Parent Category --}}
                                <div>
                                    <label class="mb-1.5 block text-sm font-medium text-gray-700">
                                        Parent Category
                                    </label>

                                    <select
                                        wire:model="parent_id"
                                        class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10"
                                    >
                                        <option value="">
                                            No Parent
                                        </option>

                                        @foreach ($parentCategories as $parent)
                                            <option value="{{ $parent->id }}">
                                                {{ $parent->name }}
                                            </option>
                                        @endforeach
                                    </select>

                                    @error('parent_id')
                                        <p class="mt-1 text-xs text-red-600">
                                            {{ $message }}
                                        </p>
                                    @enderror
                                </div>

                                {{-- Collection Type --}}
                                <div>
                                    <label class="mb-1.5 block text-sm font-medium text-gray-700">
                                        Collection Type
                                    </label>

                                    <select wire:model="collection_type"
                                        class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 placeholder:text-gray-400 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10">
                                        <option value="manual">Manual</option>
                                        <option value="automated">Automated</option>
                                    </select>
                                </div>

                                {{-- Sort Order --}}
                                <div>
                                    <label class="mb-1.5 block text-sm font-medium text-gray-700">
                                        Sort Order
                                    </label>

                                    <input type="number" min="0" wire:model="sort_order" placeholder="0"
                                        class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 placeholder:text-gray-400 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10">

                                    @error('sort_order')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                            </div>


                            {{-- Description --}}
                            <div>
                                <label class="m-1.5 block text-sm font-medium text-gray-700">
                                    Description
                                </label>

                                <textarea wire:model="description" rows="3" placeholder="Enter category description..."
                                    class="w-full resize-none rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 placeholder:text-gray-400 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10"></textarea>

                                @error('description')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>


                            {{-- Image --}}
                            <div>
                                <label class="mb-2 block text-sm font-medium text-gray-700">
                                    Category Image
                                </label>

                                {{-- Case 1: Newly uploaded temporary image --}}
                                @if ($image)
                                    <div class="relative overflow-hidden rounded-xl border border-gray-200 bg-gray-50 p-4">
                                        <div class="flex items-center gap-4">
                                            <div class="relative h-20 w-20 shrink-0 overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
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

                                                <div class="mt-2.5 flex items-center gap-3">
                                                    <label for="category-image-upload" class="cursor-pointer text-xs font-semibold text-blue-600 hover:text-blue-800 underline">
                                                        Change image
                                                    </label>
                                                    <span class="text-xs text-gray-300">|</span>
                                                    <button type="button" wire:click="removeImage" class="text-xs font-semibold text-red-600 hover:text-red-800">
                                                        Remove
                                                    </button>
                                                </div>
                                            </div>
                                        </div>

                                        <input type="file" id="category-image-upload" wire:model="image" accept="image/png,image/jpeg,image/webp" class="hidden">
                                    </div>

                                {{-- Case 2: Existing saved image from database --}}
                                @elseif ($existingImage)
                                    <div class="relative overflow-hidden rounded-xl border border-gray-200 bg-gray-50 p-4">
                                        <div class="flex items-center gap-4">
                                            <div class="relative h-20 w-20 shrink-0 overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
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

                                                <div class="mt-2.5 flex items-center gap-3">
                                                    <label for="category-image-upload" class="cursor-pointer text-xs font-semibold text-blue-600 hover:text-blue-800 underline">
                                                        Replace image
                                                    </label>
                                                    <span class="text-xs text-gray-300">|</span>
                                                    <button type="button" wire:click="removeExistingImage" class="text-xs font-semibold text-red-600 hover:text-red-800">
                                                        Remove
                                                    </button>
                                                </div>
                                            </div>
                                        </div>

                                        <input type="file" id="category-image-upload" wire:model="image" accept="image/png,image/jpeg,image/webp" class="hidden">
                                    </div>

                                {{-- Case 3: No image uploaded yet --}}
                                @else
                                    <label for="category-image-upload"
                                        class="flex cursor-pointer flex-col items-center justify-center rounded-xl border-2 border-dashed border-gray-300 bg-white px-6 py-6 transition hover:border-blue-400 hover:bg-blue-50/30">
                                        <svg class="mb-3 h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>

                                        <span class="text-sm font-medium text-gray-700">
                                            Click to upload category image
                                        </span>

                                        <span class="mt-1 text-xs text-gray-400">
                                            PNG, JPG or WEBP · Maximum 2MB
                                        </span>

                                        <input type="file" id="category-image-upload" wire:model="image" accept="image/png,image/jpeg,image/webp"
                                            class="hidden">
                                    </label>
                                @endif

                                {{-- Upload Progress / Loading Indicator --}}
                                <div wire:loading wire:target="image" class="mt-2.5 flex items-center gap-2 text-xs font-medium text-blue-600">
                                    <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                                    </svg>
                                    Uploading image, please wait...
                                </div>

                                @error('image')
                                <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Status --}}
                            <div class="rounded-xl border border-gray-200 bg-white px-4 py-4 my-3">
                                <label class="flex cursor-pointer items-center justify-between gap-4">

                                    <div>
                                        <p class="text-sm font-medium text-gray-900">
                                            Category Status
                                        </p>

                                        <p class="mt-0.5 text-xs text-gray-500">
                                            Active categories can be assigned to products.
                                        </p>
                                    </div>

                                    <input type="checkbox" wire:model="is_active"
                                        class="h-5 w-5 rounded border-gray-300 text-blue-600 focus:ring-blue-500">

                                </label>
                            </div>


                            {{-- SEO --}}
                            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">

                                <div class="mb-5">
                                    <h3 class="text-sm font-semibold text-gray-900">
                                        SEO Settings
                                    </h3>

                                    <p class="mt-1 text-xs text-gray-500">
                                        Configure how this category appears in search engines.
                                    </p>
                                </div>

                                <div class="space-y-4">

                                    {{-- SEO Title --}}
                                    <div>
                                        <label class="mb-1.5 block text-sm font-medium text-gray-700">
                                            SEO Title
                                        </label>

                                        <input type="text" wire:model="seo_title" maxlength="255"
                                            placeholder="Enter SEO title"
                                            class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 placeholder:text-gray-400 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10">

                                        <p class="mt-1 text-xs text-gray-400">
                                            Recommended: 50–60 characters.
                                        </p>

                                        @error('seo_title')
                                        <p class="mt-1 text-xs text-red-600">
                                            {{ $message }}
                                        </p>
                                        @enderror
                                    </div>

                                    {{-- SEO Description --}}
                                    <div>
                                        <label class="mb-1.5 block text-sm font-medium text-gray-700">
                                            SEO Description
                                        </label>

                                        <textarea wire:model="seo_description" rows="3" maxlength="500"
                                            placeholder="Enter SEO description"
                                            class="w-full resize-none rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 placeholder:text-gray-400 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10"></textarea>

                                        <p class="mt-1 text-xs text-gray-400">
                                            Recommended: 150–160 characters.
                                        </p>

                                        @error('seo_description')
                                        <p class="mt-1 text-xs text-red-600">
                                            {{ $message }}
                                        </p>
                                        @enderror
                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>


                    {{-- Footer --}}
                    <div class="flex shrink-0 items-center justify-end gap-3 border-t border-gray-200 bg-white px-6 py-4">

                        <button type="button" wire:click="closeModal"
                            class="rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50">
                            Cancel
                        </button>

                        <button type="submit" wire:loading.attr="disabled"
                            class="inline-flex items-center rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-medium text-white transition hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-500/20 disabled:cursor-not-allowed disabled:opacity-60">
                            <span wire:loading.remove wire:target="save">
                                {{ $editingId ? 'Update Category' : 'Create Category' }}
                            </span>

                            <span wire:loading wire:target="save">
                                Saving...
                            </span>
                        </button>

                    </div>

            </form>
        </div>
    </div>
    @endif



    {{-- Delete Confirmation Modal --}}
    @if ($showDeleteModal)

    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">

        <div class="w-full max-w-md rounded-xl bg-white shadow-xl">

            <div class="p-6">

                <h2 class="text-lg font-semibold text-gray-900">
                    Delete Category
                </h2>

                <p class="mt-2 text-sm text-gray-600">
                    Are you sure you want to delete this category?
                    This action cannot be undone.
                </p>

            </div>

            <div class="flex justify-end gap-3 border-t bg-gray-50 px-6 py-4">

                <button type="button" wire:click="closeDeleteModal"
                    class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                    Cancel
                </button>

                <button type="button" wire:click="delete"
                    class="rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700">
                    Delete
                </button>

            </div>

        </div>

    </div>

    @endif


    {{-- Import Modal --}}
    @if ($showImportModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 backdrop-blur-sm">
        <div class="w-full max-w-lg rounded-xl bg-white shadow-xl">
            <div class="flex items-center justify-between border-b px-6 py-4">
                <div class="flex items-center gap-2.5">
                    <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-blue-50 text-blue-600">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                        </svg>
                    </div>
                    <h2 class="text-lg font-semibold text-gray-900">
                        Import Categories
                    </h2>
                </div>

                <button type="button" wire:click="closeImportModal" class="rounded-lg p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="space-y-4 p-6">
                <p class="text-sm text-gray-600">
                    Upload a CSV file with your categories. Categories matching existing slugs or names will be updated automatically, and new categories will be created. Parent categories can also be specified to build hierarchy.
                </p>

                <div class="flex items-center justify-between rounded-lg border border-blue-100 bg-blue-50/70 p-3 text-xs text-blue-800">
                    <div class="flex items-center gap-2">
                        <svg class="h-4 w-4 shrink-0 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>Need a pre-formatted template?</span>
                    </div>

                    <button type="button" wire:click="exportSample" class="font-semibold text-blue-700 underline hover:text-blue-900">
                        Download Sample CSV
                    </button>
                </div>

                {{-- File Input --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">
                        Select CSV File
                    </label>

                    <input type="file" wire:model="importFile" accept=".csv,text/csv,text/plain"
                        class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 border border-gray-300 rounded-lg cursor-pointer focus:outline-none" />

                    <div wire:loading wire:target="importFile" class="mt-2 text-xs text-blue-600 flex items-center gap-1.5">
                        <svg class="h-3.5 w-3.5 animate-spin" viewBox="0 0 24 24" fill="none">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z" />
                        </svg>
                        Uploading file...
                    </div>

                    @error('importFile')
                    <p class="mt-1.5 text-xs text-red-600 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Parsing Errors --}}
                @if (!empty($importErrors))
                <div class="rounded-lg border border-red-200 bg-red-50 p-3 max-h-48 overflow-y-auto">
                    <h3 class="text-xs font-semibold text-red-800 mb-1.5 flex items-center gap-1.5">
                        <svg class="h-4 w-4 shrink-0 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        Errors found in CSV (no categories imported):
                    </h3>
                    <ul class="list-disc pl-5 text-xs text-red-700 space-y-0.5">
                        @foreach ($importErrors as $err)
                        <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif
            </div>

            <div class="flex items-center justify-end gap-3 border-t bg-gray-50 px-6 py-4 rounded-b-xl">
                <button type="button" wire:click="closeImportModal"
                    class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 shadow-sm">
                    Cancel
                </button>

                <button type="button" wire:click="import" wire:loading.attr="disabled"
                    class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 shadow-sm disabled:opacity-50">
                    <span wire:loading.remove wire:target="import">
                        Import Categories
                    </span>
                    <span wire:loading wire:target="import" class="inline-flex items-center gap-1.5">
                        <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z" />
                        </svg>
                        Importing...
                    </span>
                </button>
            </div>
        </div>
    </div>
    @endif

</div>