<div class="p-6">

    {{-- Header --}}
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

        <div>
            <h1 class="text-2xl font-semibold text-gray-900">
                Products
            </h1>

            <p class="mt-1 text-sm text-gray-600">
                Manage your products.
            </p>
        </div>

        <button type="button" wire:click="openCreate"
            class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-blue-700">
            Add Product
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

            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search products..."
                class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-blue-500 focus:ring-blue-500">

        </div>

    </div>

    {{-- Products Table --}}
    <div class="overflow-hidden rounded-lg bg-white shadow-sm">

        <div class="overflow-x-auto">

            <table class="min-w-full divide-y divide-gray-200">

                <thead class="bg-gray-50">

                    <tr>

                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                            Product
                        </th>

                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                            SKU
                        </th>

                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                            Price
                        </th>

                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                            Categories
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

                    @forelse ($products as $product)

                    <tr wire:key="product-{{ $product->id }}">

                        {{-- Product --}}
                        <td class="px-6 py-4">

                            <div class="font-medium text-gray-900">
                                {{ $product->name }}
                            </div>

                            @if ($product->description)

                            <div class="mt-1 max-w-sm truncate text-xs text-gray-500">
                                {{ $product->description }}
                            </div>

                            @endif

                        </td>


                        {{-- SKU --}}
                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-600">

                            {{ $product->sku }}

                        </td>


                        {{-- Price --}}
                        <td class="whitespace-nowrap px-6 py-4">

                            <div class="text-sm font-medium text-gray-900">
                                {{ number_format($product->price, 2) }}
                            </div>

                            @if ($product->compare_at_price)

                            <div class="text-xs text-gray-400 line-through">
                                {{ number_format($product->compare_at_price, 2) }}
                            </div>

                            @endif

                        </td>


                        {{-- Categories --}}
                        <td class="px-6 py-4">

                            @if ($product->categories->count())

                            <div class="flex flex-wrap gap-1">

                                @foreach ($product->categories as $category)

                                <span class="rounded-full bg-blue-50 px-2.5 py-1 text-xs font-medium text-blue-700">
                                    {{ $category->name }}
                                </span>

                                @endforeach

                            </div>

                            @else

                            <span class="text-sm text-gray-400">
                                —
                            </span>

                            @endif

                        </td>


                        {{-- Status --}}
                        <td class="whitespace-nowrap px-6 py-4">

                            @if ($product->is_active)

                            <button wire:click="toggleStatus({{ $product->id }})"
                                class="rounded-full bg-green-100 px-3 py-1 text-xs font-medium text-green-700 hover:bg-green-200">
                                Active
                            </button>

                            @else

                            <button wire:click="toggleStatus({{ $product->id }})"
                                class="rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-600 hover:bg-gray-200">
                                Inactive
                            </button>

                            @endif

                        </td>


                        {{-- Actions --}}
                        <td class="whitespace-nowrap px-6 py-4 text-right">

                            <button wire:click="openEdit({{ $product->id }})"
                                class="mr-3 text-sm font-medium text-blue-600 hover:text-blue-800">
                                Edit
                            </button>

                            <button wire:click="confirmDelete({{ $product->id }})"
                                class="text-sm font-medium text-red-600 hover:text-red-800">
                                Delete
                            </button>

                        </td>

                    </tr>

                    @empty

                    <tr>

                        <td colspan="6" class="px-6 py-12 text-center">

                            <div class="flex flex-col items-center justify-center">

                                <div class="mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-gray-100">

                                    <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                            d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                    </svg>

                                </div>

                                <p class="text-sm font-medium text-gray-700">
                                    No products found
                                </p>

                                <p class="mt-1 text-xs text-gray-400">
                                    Start by adding your first product.
                                </p>

                            </div>

                        </td>

                    </tr>

                    @endforelse

                </tbody>

            </table>

        </div>


        {{-- Pagination --}}
        @if ($products->hasPages())

        <div class="border-t border-gray-200 px-6 py-4">
            {{ $products->links() }}
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

            <div class="min-h-0 flex-1 overflow-y-auto bg-gray-50/50 px-6 py-6">

                <div class="space-y-5">

                    {{-- Basic Information --}}
                    <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">

                        <div class="mb-5">
                            <h3 class="text-sm font-semibold text-gray-900">
                                Basic Information
                            </h3>

                            <p class="mt-1 text-xs text-gray-500">
                                Add the basic details for this product.
                            </p>
                        </div>

                        <div class="grid grid-cols-1 gap-x-5 gap-y-4 md:grid-cols-2">

                            {{-- Product Name --}}
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-gray-700">
                                    Product Name <span class="text-red-500">*</span>
                                </label>

                                <input type="text" wire:model.live="name" placeholder="Enter product name"
                                    class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 placeholder:text-gray-400 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10">

                                @error('name')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

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

                                <input type="text" wire:model="slug" placeholder="product-name"
                                    class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 placeholder:text-gray-400 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10">

                                @error('slug')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Product Type --}}
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-gray-700">
                                    Product Type
                                </label>

                                <input type="text" wire:model="product_type" placeholder="e.g. Clothing"
                                    class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 placeholder:text-gray-400 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10">

                                @error('product_type')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Vendor --}}
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-gray-700">
                                    Vendor
                                </label>

                                <input type="text" wire:model="vendor" placeholder="Enter vendor or brand"
                                    class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 placeholder:text-gray-400 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10">

                                @error('vendor')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
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
                        <div class="mt-4">
                            <label class="mb-1.5 block text-sm font-medium text-gray-700">
                                Description
                            </label>

                            <textarea wire:model="description" rows="4" placeholder="Enter product description..."
                                class="w-full resize-none rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 placeholder:text-gray-400 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10"></textarea>

                            @error('description')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                    </div>


                    {{-- Pricing --}}
                    <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">

                        <div class="mb-5">
                            <h3 class="text-sm font-semibold text-gray-900">
                                Pricing
                            </h3>

                            <p class="mt-1 text-xs text-gray-500">
                                Set the product selling price and comparison price.
                            </p>
                        </div>

                        <div class="grid grid-cols-1 gap-x-5 gap-y-4 md:grid-cols-2">

                            {{-- Price --}}
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-gray-700">
                                    Price <span class="text-red-500">*</span>
                                </label>

                                <div class="relative">
                                    <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-sm text-gray-400">
                                        $
                                    </span>

                                    <input type="number" step="0.01" min="0" wire:model="price" placeholder="0.00"
                                        class="w-full rounded-lg border border-gray-300 bg-white py-2.5 pl-8 pr-3.5 text-sm text-gray-900 placeholder:text-gray-400 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10">
                                </div>

                                @error('price')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Compare At Price --}}
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-gray-700">
                                    Compare-at Price
                                </label>

                                <div class="relative">
                                    <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-sm text-gray-400">
                                        $
                                    </span>

                                    <input type="number" step="0.01" min="0" wire:model="compare_at_price"
                                        placeholder="0.00"
                                        class="w-full rounded-lg border border-gray-300 bg-white py-2.5 pl-8 pr-3.5 text-sm text-gray-900 placeholder:text-gray-400 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10">
                                </div>

                                @error('compare_at_price')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                        </div>

                    </div>


                    {{-- Categories --}}
                    <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">

                        <div class="mb-5">
                            <h3 class="text-sm font-semibold text-gray-900">
                                Categories
                            </h3>

                            <p class="mt-1 text-xs text-gray-500">
                                Select the categories this product belongs to.
                            </p>
                        </div>

                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700">
                                Product Categories
                            </label>

                            <select wire:model="selectedCategories" multiple
                                class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10"
                                size="5">
                                @foreach ($categories as $category)
                                <option value="{{ $category->id }}">
                                    {{ $category->name }}
                                </option>
                                @endforeach
                            </select>

                            <p class="mt-1.5 text-xs text-gray-400">
                                Hold Ctrl (Windows) or Command (Mac) to select multiple categories.
                            </p>

                            @error('selectedCategories')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                    </div>


                    {{-- Status --}}
                    <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">

                        <div class="mb-5">
                            <h3 class="text-sm font-semibold text-gray-900">
                                Product Status
                            </h3>

                            <p class="mt-1 text-xs text-gray-500">
                                Control the visibility and featured status of this product.
                            </p>
                        </div>

                        <div class="space-y-4">

                            {{-- Active --}}
                            <label
                                class="flex cursor-pointer items-center justify-between gap-4 rounded-lg border border-gray-200 px-4 py-4">

                                <div>
                                    <p class="text-sm font-medium text-gray-900">
                                        Active
                                    </p>

                                    <p class="mt-0.5 text-xs text-gray-500">
                                        Active products are available on the storefront.
                                    </p>
                                </div>

                                <input type="checkbox" wire:model="is_active"
                                    class="h-5 w-5 rounded border-gray-300 text-blue-600 focus:ring-blue-500">

                            </label>

                            {{-- Featured --}}
                            <label
                                class="flex cursor-pointer items-center justify-between gap-4 rounded-lg border border-gray-200 px-4 py-4">

                                <div>
                                    <p class="text-sm font-medium text-gray-900">
                                        Featured Product
                                    </p>

                                    <p class="mt-0.5 text-xs text-gray-500">
                                        Mark this product as featured.
                                    </p>
                                </div>

                                <input type="checkbox" wire:model="is_featured"
                                    class="h-5 w-5 rounded border-gray-300 text-blue-600 focus:ring-blue-500">

                            </label>

                        </div>

                    </div>


                    {{-- SEO --}}
                    <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">

                        <div class="mb-5">
                            <h3 class="text-sm font-semibold text-gray-900">
                                SEO Settings
                            </h3>

                            <p class="mt-1 text-xs text-gray-500">
                                Configure how this product appears in search engines.
                            </p>
                        </div>

                        <div class="space-y-4">

                            {{-- SEO Title --}}
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-gray-700">
                                    SEO Title
                                </label>

                                <input type="text" wire:model="seo_title" maxlength="255" placeholder="Enter SEO title"
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

                    {{-- Form Actions --}}
                    <div class="flex items-center justify-end gap-3 border-t border-gray-200 bg-white px-6 py-4">
                        <button type="button" wire:click="closeModal"
                            class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50 focus:outline-none focus:ring-4 focus:ring-gray-200">
                            Cancel
                        </button>

                        <button type="button" wire:click="save" wire:loading.attr="disabled" wire:target="save"
                            class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-medium text-white transition hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-500/30 disabled:cursor-not-allowed disabled:opacity-60">
                            <span wire:loading.remove wire:target="save">
                                {{ $editingId ? 'Update Product' : 'Save Product' }}
                            </span>

                            <span wire:loading wire:target="save" class="inline-flex items-center gap-2">
                                <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                        stroke-width="4" />
                                    <path class="opacity-75" fill="currentColor"
                                        d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z" />
                                </svg>
                                Saving...
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif



    {{-- Delete Confirmation Modal --}}
    @if ($showDeleteModal)

    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">

        <div class="w-full max-w-md rounded-xl bg-white shadow-xl">

            <div class="p-6">

                <h2 class="text-lg font-semibold text-gray-900">
                    Delete Product
                </h2>

                <p class="mt-2 text-sm text-gray-600">
                    Are you sure you want to delete this Product ?
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