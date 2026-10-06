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

        <button type="button" wire:click="openCreate"
            class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-blue-700">
            Add Category
        </button>

    </div>


    {{-- Flash Messages --}}
    @if (session()->has('success'))
    <div class="mb-5 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
        {{ session('success') }}
    </div>
    @endif

    @if (session()->has('error'))
    <div class="mb-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
        {{ session('error') }}
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

                            <div class="font-medium text-gray-900">
                                {{ $category->name }}
                            </div>

                            @if ($category->description)
                            <div class="mt-1 max-w-sm truncate text-xs text-gray-500">
                                {{ $category->description }}
                            </div>
                            @endif

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

                            <button wire:click="openEdit({{ $category->id }})"
                                class="mr-3 text-sm font-medium text-blue-600 hover:text-blue-800">
                                Edit
                            </button>

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

                                    <input
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

                                <label
                                    class="flex cursor-pointer flex-col items-center justify-center rounded-xl border-2 border-dashed border-gray-300 bg-white px-6 py-6 transition hover:border-blue-400 hover:bg-blue-50/30">
                                    <svg class="mb-3 h-8 w-8 text-gray-400" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                            d="M12 16V4m0 0L8 8m4-4l4 4M4 16.5V19a1 1 0 001 1h14a1 1 0 001-1v-2.5" />
                                    </svg>

                                    <span class="text-sm font-medium text-gray-700">
                                        Click to upload category image
                                    </span>

                                    <span class="mt-1 text-xs text-gray-400">
                                        PNG, JPG or WEBP · Maximum 2MB
                                    </span>

                                    <input type="file" wire:model="image" accept="image/png,image/jpeg,image/webp"
                                        class="hidden">
                                </label>

                                @error('image')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror

                                <div wire:loading wire:target="image" class="mt-2 text-xs text-blue-600">
                                    Uploading image...
                                </div>
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

</div>