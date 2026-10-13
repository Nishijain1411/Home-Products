
<div class="space-y-6">

    {{-- Page Header --}}
  
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-800 dark:text-white/90">
                Inventory Management
            </h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Monitor product stock and inventory availability.
            </p>
        </div>

        <div class="flex items-center gap-3">
            <a
                href="{{ route('admin.inventory.history') }}"
                class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200"
            >
                Adjustment History
            </a>

            <a
                href="{{ route('admin.inventory.index') }}"
                class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300"
            >
                View Inventory  
            </a>
        </div>
    </div>

    @if (session()->has('inventory_success'))
        <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700 dark:border-green-800 dark:bg-green-500/10 dark:text-green-400">
            {{ session('inventory_success') }}
        </div>
    @endif

    {{-- Search and Filter --}}
    <div class="flex flex-col gap-3 rounded-2xl border border-gray-200 bg-white p-4 sm:flex-row dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="flex-1">
            <input
                type="text"
                wire:model.live.debounce.300ms="search"
                placeholder="Search by product name or SKU..."
                class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 outline-none focus:border-blue-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
            />
        </div>

        <div class="sm:w-56">
            <select
                wire:model.live="stockFilter"
                class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 outline-none focus:border-blue-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
            >
                <option value="">All Stock Levels</option>
                <option value="in">In Stock (Above 10)</option>
                <option value="low">Low Stock (1–10)</option>
                <option value="out">Out of Stock</option>
            </select>
        </div>
    </div>

    {{-- Inventory Table --}}
    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">

        <div class="overflow-x-auto">
            <table class="w-full min-w-[950px]">

                <thead class="border-b border-gray-200 bg-gray-50 dark:border-gray-800 dark:bg-white/[0.02]">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                            Product
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                            Variant
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                            SKU
                        </th>
                        <th class="px-6 py-4 text-right text-xs font-medium uppercase tracking-wider text-gray-500">
                            Available
                        </th>
                        <th class="px-6 py-4 text-right text-xs font-medium uppercase tracking-wider text-gray-500">
                            Reserved
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                            Status
                        </th>
                        <th class="px-6 py-4 text-right text-xs font-medium uppercase tracking-wider text-gray-500">
                            Actions
                        </th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">

                    @forelse ($inventory as $item)
                        @php
                            $variant = $item->variant;
                            $product = $variant?->product;
                            $image = $product?->images
                                ?->sortByDesc('is_primary')
                                ->sortBy('position')
                                ->first();

                            $quantity = (int) $item->quantity;
                            $reserved = (int) $item->reserved_quantity;
                        @endphp

                        <tr wire:key="inventory-{{ $item->id }}" class="hover:bg-gray-50 dark:hover:bg-white/[0.02]">

                            {{-- Product --}}
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    @if ($image)
                                        <img
                                            src="{{ asset('storage/' . $image->path) }}"
                                            alt="{{ $product->name }}"
                                            class="h-12 w-12 rounded-lg border border-gray-200 object-cover dark:border-gray-700"
                                        />
                                    @else
                                        <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-gray-100 text-xs text-gray-400 dark:bg-gray-800">
                                            No image
                                        </div>
                                    @endif

                                    <div>
                                        <p class="font-medium text-gray-800 dark:text-white/90">
                                            {{ $product?->name ?? 'Product unavailable' }}
                                        </p>
                                        <p class="mt-1 text-xs text-gray-500">
                                            Product ID: {{ $product?->id ?? '—' }}
                                        </p>
                                    </div>
                                </div>
                            </td>

                            {{-- Variant --}}
                            <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-300">
                                {{ $variant?->title ?? '—' }}
                            </td>

                            {{-- SKU --}}
                            <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-300">
                                {{ $variant?->sku ?? '—' }}
                            </td>

                            {{-- Available --}}
                            <td class="px-6 py-4 text-right">
                                <span class="font-semibold text-gray-800 dark:text-white/90">
                                    {{ $quantity }}
                                </span>
                            </td>

                            {{-- Reserved --}}
                            <td class="px-6 py-4 text-right text-sm text-gray-600 dark:text-gray-300">
                                {{ $reserved }}
                            </td>

                            {{-- Stock Status --}}
                            <td class="px-6 py-4">
                                @if ($quantity <= 0)
                                    <span class="inline-flex rounded-full bg-red-50 px-3 py-1 text-xs font-medium text-red-600 dark:bg-red-500/10 dark:text-red-400">
                                        Out of Stock
                                    </span>
                                @elseif ($quantity <= 10)
                                    <span class="inline-flex rounded-full bg-yellow-50 px-3 py-1 text-xs font-medium text-yellow-700 dark:bg-yellow-500/10 dark:text-yellow-400">
                                        Low Stock
                                    </span>
                                @else
                                    <span class="inline-flex rounded-full bg-green-50 px-3 py-1 text-xs font-medium text-green-600 dark:bg-green-500/10 dark:text-green-400">
                                        In Stock
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                <button
                                    type="button"
                                    wire:click="openAdjustment({{ $item->id }})"
                                    class="rounded-lg bg-blue-600 px-3 py-2 text-xs font-medium text-white hover:bg-blue-700"
                                >
                                    Adjust Stock
                                </button>
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <p class="font-medium text-gray-800 dark:text-white/90">
                                    No inventory records found
                                </p>
                                <p class="mt-1 text-sm text-gray-500">
                                    Inventory records will appear here when product variants have inventory entries.
                                </p>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <button
                                    type="button"
                                    wire:click="openAdjustment({{ $item->id }})"
                                    class="rounded-lg bg-blue-600 px-3 py-2 text-xs font-medium text-white hover:bg-blue-700"
                                >
                                    Adjust Stock
                                </button>
                            </td>
                        </tr>
                    @endforelse

                </tbody>
            </table>
        </div>

        @if ($showAdjustmentModal)
            <div
                class="fixed inset-0 z-[100] flex items-center justify-center bg-gray-900/50 p-4"
                wire:key="inventory-adjustment-modal"
            >
                <div class="w-full max-w-lg rounded-2xl bg-white p-6 shadow-xl dark:bg-gray-900">

                    <div class="mb-5 flex items-center justify-between">
                        <h2 class="text-lg font-semibold text-gray-800 dark:text-white">
                            Adjust Stock
                        </h2>

                        <button
                            type="button"
                            wire:click="closeAdjustment"
                            class="text-xl text-gray-500 hover:text-gray-800"
                        >
                            &times;
                        </button>
                    </div>

                    <form wire:submit="saveAdjustment" class="space-y-4">

                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Adjustment Type
                            </label>

                            <select
                                wire:model="adjustmentType"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2.5 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                            >
                                <option value="increase">Increase Stock</option>
                                <option value="decrease">Decrease Stock</option>
                            </select>

                            @error('adjustmentType')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Quantity
                            </label>

                            <input
                                type="number"
                                min="1"
                                wire:model="adjustmentQuantity"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2.5 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                            />

                            @error('adjustmentQuantity')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Reason
                            </label>

                            <select
                                wire:model="adjustmentReason"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2.5 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                            >
                                <option value="new_stock">New Stock</option>
                                <option value="damaged_goods">Damaged Goods</option>
                                <option value="stock_correction">Stock Correction</option>
                                <option value="return">Return</option>
                                <option value="other">Other</option>
                            </select>

                            @error('adjustmentReason')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Notes (Optional)
                            </label>

                            <textarea
                                wire:model="adjustmentNotes"
                                rows="3"
                                maxlength="1000"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2.5 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                                placeholder="Add a reason or reference..."
                            ></textarea>

                            @error('adjustmentNotes')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex justify-end gap-3 border-t border-gray-200 pt-4 dark:border-gray-800">
                            <button
                                type="button"
                                wire:click="closeAdjustment"
                                class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 dark:border-gray-700 dark:text-gray-300"
                            >
                                Cancel
                            </button>

                            <button
                                type="submit"
                                wire:loading.attr="disabled"
                                wire:target="saveAdjustment"
                                class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-50"
                            >
                                <span wire:loading.remove wire:target="saveAdjustment">
                                    Save Adjustment
                                </span>
                                <span wire:loading wire:target="saveAdjustment">
                                    Saving...
                                </span>
                            </button>
                        </div>

                    </form>
                </div>
            </div>
        @endif

        {{-- Pagination --}}
        <div class="border-t border-gray-200 px-6 py-4 dark:border-gray-800">
            {{ $inventory->links() }}
        </div>

    </div>
</div>
