<div class="p-4 md:p-6 max-w-6xl mx-auto space-y-6">

    {{-- Breadcrumb & Header --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between border-b border-gray-200 pb-5">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.discounts.index') }}"
                class="flex h-9 w-9 items-center justify-center rounded-lg border border-gray-300 bg-white text-gray-600 shadow-sm transition hover:bg-gray-50 hover:text-gray-900"
                title="Back to Discounts">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
            <div>
                <div class="flex items-center gap-2 text-xs font-medium text-gray-500">
                    <a href="{{ route('admin.discounts.index') }}" class="hover:text-blue-600 transition">Discounts</a>
                    <span>/</span>
                    <span class="text-gray-800">{{ $isEditing ? 'Edit Discount' : 'Create Discount' }}</span>
                </div>
                <h1 class="text-2xl font-bold tracking-tight text-gray-900 mt-1">
                    {{ $isEditing ? ($code ? 'Edit Discount: '.$code : 'Edit Discount') : 'Create New Discount' }}
                </h1>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('admin.discounts.index') }}"
                class="rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 transition">
                Cancel
            </a>

            <button type="button" wire:click="save" wire:loading.attr="disabled"
                class="inline-flex items-center justify-center gap-2 rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-medium text-white shadow-sm hover:bg-blue-700 transition disabled:opacity-50">
                <span wire:loading.remove wire:target="save">
                    {{ $isEditing ? 'Update Discount' : 'Save Discount' }}
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
            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm space-y-4">
                <div class="border-b border-gray-100 pb-3">
                    <h3 class="text-base font-semibold text-gray-900">
                        Discount Information
                    </h3>
                    <p class="mt-0.5 text-xs text-gray-500">
                        Set the promotional code, display name, and optional description.
                    </p>
                </div>

                <div>
                    <div class="flex items-center justify-between">
                        <label for="code" class="block text-xs font-semibold text-gray-700 uppercase tracking-wider">
                            Coupon Code <span class="text-red-500">*</span>
                        </label>
                        <button
                            type="button"
                            wire:click="generateCode"
                            class="text-xs font-semibold text-blue-600 hover:text-blue-800 transition"
                        >
                            Generate Random Code
                        </button>
                    </div>
                    <input
                        type="text"
                        id="code"
                        wire:model="code"
                        placeholder="e.g. SUMMER25, SAVE50"
                        class="mt-1 block w-full uppercase font-mono font-bold tracking-wider rounded-lg border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    >
                    @error('code')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="name" class="block text-xs font-semibold text-gray-700 uppercase tracking-wider">
                        Campaign / Promotion Name <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="text"
                        id="name"
                        wire:model="name"
                        placeholder="e.g. 25% Off Summer Collection"
                        class="mt-1 block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    >
                    @error('name')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="description" class="block text-xs font-semibold text-gray-700 uppercase tracking-wider">
                        Description (Optional)
                    </label>
                    <textarea
                        id="description"
                        wire:model="description"
                        rows="2"
                        placeholder="Internal or customer notes about this promo..."
                        class="mt-1 block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    ></textarea>
                    @error('description')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Discount Rules --}}
            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm space-y-4">
                <div class="border-b border-gray-100 pb-3">
                    <h3 class="text-base font-semibold text-gray-900">
                        Discount Rules & Value
                    </h3>
                    <p class="mt-0.5 text-xs text-gray-500">
                        Choose whether this discount reduces a percentage or a fixed amount from the order subtotal.
                    </p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="type" class="block text-xs font-semibold text-gray-700 uppercase tracking-wider">
                            Discount Type <span class="text-red-500">*</span>
                        </label>
                        <select
                            id="type"
                            wire:model.live="type"
                            class="mt-1 block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        >
                            <option value="percentage">Percentage (%) Off Subtotal</option>
                            <option value="fixed">Fixed Amount Off Subtotal</option>
                        </select>
                        @error('type')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="value" class="block text-xs font-semibold text-gray-700 uppercase tracking-wider">
                            Discount Value <span class="text-red-500">*</span>
                        </label>
                        <div class="relative mt-1">
                            <input
                                type="number"
                                step="0.01"
                                min="0.01"
                                id="value"
                                wire:model="value"
                                placeholder="{{ $type === 'percentage' ? 'e.g. 15 (for 15%)' : 'e.g. 100.00' }}"
                                class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 pr-10"
                            >
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-xs text-gray-500 font-bold">
                                {{ $type === 'percentage' ? '%' : store_setting('store_currency', 'INR') }}
                            </div>
                        </div>
                        @error('value')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
                    <div>
                        <label for="min_order_amount" class="block text-xs font-semibold text-gray-700 uppercase tracking-wider">
                            Minimum Order Amount (Subtotal)
                        </label>
                        <div class="relative mt-1">
                            <input
                                type="number"
                                step="0.01"
                                min="0"
                                id="min_order_amount"
                                wire:model="min_order_amount"
                                placeholder="e.g. 500.00 (optional)"
                                class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            >
                        </div>
                        <p class="mt-1 text-[11px] text-gray-400">Cart subtotal must equal or exceed this amount to apply.</p>
                        @error('min_order_amount')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    @if ($type === 'percentage')
                        <div>
                            <label for="max_discount_amount" class="block text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                Maximum Discount Amount (Cap)
                            </label>
                            <div class="relative mt-1">
                                <input
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    id="max_discount_amount"
                                    wire:model="max_discount_amount"
                                    placeholder="e.g. 200.00 (optional)"
                                    class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                >
                            </div>
                            <p class="mt-1 text-[11px] text-gray-400">Maximum savings limit even if percentage calculates higher.</p>
                            @error('max_discount_amount')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    @endif
                </div>
            </div>

            {{-- Applies To: All Products vs Specific Categories vs Specific Products --}}
            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm space-y-4">
                <div class="border-b border-gray-100 pb-3">
                    <h3 class="text-base font-semibold text-gray-900">
                        Applies To
                    </h3>
                    <p class="mt-0.5 text-xs text-gray-500">
                        Specify whether this discount applies to all products, specific categories, or specific products.
                    </p>
                </div>

                <div class="space-y-3">
                    <label class="flex items-start gap-3 p-3 rounded-lg border cursor-pointer transition {{ $applies_to === 'all' ? 'border-blue-500 bg-blue-50/40 ring-1 ring-blue-500/20' : 'border-gray-200 hover:bg-gray-50' }}">
                        <input type="radio" wire:model.live="applies_to" value="all" class="mt-0.5 text-blue-600 focus:ring-blue-500">
                        <div>
                            <span class="block text-xs font-semibold text-gray-900">All Products</span>
                            <span class="block text-[11px] text-gray-500">Applies across the entire catalog and order subtotal.</span>
                        </div>
                    </label>

                    <label class="flex items-start gap-3 p-3 rounded-lg border cursor-pointer transition {{ $applies_to === 'categories' ? 'border-blue-500 bg-blue-50/40 ring-1 ring-blue-500/20' : 'border-gray-200 hover:bg-gray-50' }}">
                        <input type="radio" wire:model.live="applies_to" value="categories" class="mt-0.5 text-blue-600 focus:ring-blue-500">
                        <div>
                            <span class="block text-xs font-semibold text-gray-900">Specific Categories</span>
                            <span class="block text-[11px] text-gray-500">Applies only to items belonging to the selected categories.</span>
                        </div>
                    </label>

                    @if ($applies_to === 'categories')
                        <div class="ml-6 p-4 rounded-xl border border-blue-200 bg-blue-50/30 space-y-2">
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-2">
                                Select Categories <span class="text-red-500">*</span>
                            </label>
                            <div class="max-h-48 overflow-y-auto space-y-1.5 pr-2">
                                @forelse ($availableCategories as $cat)
                                    <label class="flex items-center gap-2 p-1.5 rounded hover:bg-white text-xs cursor-pointer">
                                        <input type="checkbox" wire:model="selected_categories" value="{{ $cat->id }}"
                                            class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                        <span class="text-gray-800 font-medium">{{ $cat->name }}</span>
                                    </label>
                                @empty
                                    <p class="text-xs text-gray-400">No categories found in store.</p>
                                @endforelse
                            </div>
                            @error('selected_categories')
                                <p class="text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    @endif

                    <label class="flex items-start gap-3 p-3 rounded-lg border cursor-pointer transition {{ $applies_to === 'products' ? 'border-blue-500 bg-blue-50/40 ring-1 ring-blue-500/20' : 'border-gray-200 hover:bg-gray-50' }}">
                        <input type="radio" wire:model.live="applies_to" value="products" class="mt-0.5 text-blue-600 focus:ring-blue-500">
                        <div>
                            <span class="block text-xs font-semibold text-gray-900">Specific Products</span>
                            <span class="block text-[11px] text-gray-500">Applies only to selected specific products in the cart.</span>
                        </div>
                    </label>

                    @if ($applies_to === 'products')
                        <div class="ml-6 p-4 rounded-xl border border-blue-200 bg-blue-50/30 space-y-2">
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-2">
                                Select Products <span class="text-red-500">*</span>
                            </label>
                            <div class="max-h-48 overflow-y-auto space-y-1.5 pr-2">
                                @forelse ($availableProducts as $prod)
                                    <label class="flex items-center justify-between p-1.5 rounded hover:bg-white text-xs cursor-pointer">
                                        <div class="flex items-center gap-2">
                                            <input type="checkbox" wire:model="selected_products" value="{{ $prod->id }}"
                                                class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                            <span class="text-gray-800 font-medium">{{ $prod->name }}</span>
                                        </div>
                                        <span class="text-gray-400 text-[11px]">{{ format_price($prod->price) }}</span>
                                    </label>
                                @empty
                                    <p class="text-xs text-gray-400">No active products found.</p>
                                @endforelse
                            </div>
                            @error('selected_products')
                                <p class="text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    @endif
                </div>
            </div>

            {{-- Customer Eligibility: All Customers vs Specific Customer Segments --}}
            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm space-y-4">
                <div class="border-b border-gray-100 pb-3">
                    <h3 class="text-base font-semibold text-gray-900">
                        Customer Eligibility
                    </h3>
                    <p class="mt-0.5 text-xs text-gray-500">
                        Restrict this coupon to all shoppers or exclusively to members of specific customer segments.
                    </p>
                </div>

                <div class="space-y-3">
                    <label class="flex items-start gap-3 p-3 rounded-lg border cursor-pointer transition {{ $customer_eligibility === 'all' ? 'border-indigo-500 bg-indigo-50/40 ring-1 ring-indigo-500/20' : 'border-gray-200 hover:bg-gray-50' }}">
                        <input type="radio" wire:model.live="customer_eligibility" value="all" class="mt-0.5 text-indigo-600 focus:ring-indigo-500">
                        <div>
                            <span class="block text-xs font-semibold text-gray-900">All Customers</span>
                            <span class="block text-[11px] text-gray-500">Any customer or guest can apply this discount code.</span>
                        </div>
                    </label>

                    <label class="flex items-start gap-3 p-3 rounded-lg border cursor-pointer transition {{ $customer_eligibility === 'segments' ? 'border-indigo-500 bg-indigo-50/40 ring-1 ring-indigo-500/20' : 'border-gray-200 hover:bg-gray-50' }}">
                        <input type="radio" wire:model.live="customer_eligibility" value="segments" class="mt-0.5 text-indigo-600 focus:ring-indigo-500">
                        <div>
                            <span class="block text-xs font-semibold text-gray-900">Specific Customer Segments</span>
                            <span class="block text-[11px] text-gray-500">Only shoppers belonging to chosen segments (e.g. VIP, Wholesale) can redeem this code.</span>
                        </div>
                    </label>

                    @if ($customer_eligibility === 'segments')
                        <div class="ml-6 p-4 rounded-xl border border-indigo-200 bg-indigo-50/30 space-y-2">
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-2">
                                Select Customer Segments <span class="text-red-500">*</span>
                            </label>
                            <div class="space-y-1.5">
                                @forelse ($availableSegments as $seg)
                                    <label class="flex items-center justify-between p-2 rounded-lg border border-gray-200 bg-white text-xs cursor-pointer hover:border-indigo-300">
                                        <div class="flex items-center gap-2">
                                            <input type="checkbox" wire:model="selected_segments" value="{{ $seg->id }}"
                                                class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                            <span class="text-gray-800 font-semibold">{{ $seg->name }}</span>
                                        </div>
                                        <span class="text-[11px] text-gray-400">
                                            {{ $seg->customers()->count() }} customers
                                        </span>
                                    </label>
                                @empty
                                    <div class="text-xs text-gray-500 p-2">
                                        No customer segments found.
                                        <a href="{{ route('admin.customer-segments.index') }}" target="_blank" class="text-indigo-600 underline font-medium">Create customer segments first &rarr;</a>
                                    </div>
                                @endforelse
                            </div>
                            @error('selected_segments')
                                <p class="text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    @endif
                </div>
            </div>

            {{-- Usage Limits --}}
            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm space-y-4">
                <div class="border-b border-gray-100 pb-3">
                    <h3 class="text-base font-semibold text-gray-900">
                        Usage Limits
                    </h3>
                    <p class="mt-0.5 text-xs text-gray-500">
                        Restrict total redemptions across the store or per customer.
                    </p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="usage_limit" class="block text-xs font-semibold text-gray-700 uppercase tracking-wider">
                            Total Usage Limit
                        </label>
                        <input
                            type="number"
                            min="1"
                            id="usage_limit"
                            wire:model="usage_limit"
                            placeholder="e.g. 100 (leave blank for unlimited)"
                            class="mt-1 block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        >
                        <p class="mt-1 text-[11px] text-gray-400">Max times this coupon can be redeemed by all customers.</p>
                        @error('usage_limit')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="usage_limit_per_customer" class="block text-xs font-semibold text-gray-700 uppercase tracking-wider">
                            Per-Customer Usage Limit
                        </label>
                        <input
                            type="number"
                            min="1"
                            id="usage_limit_per_customer"
                            wire:model="usage_limit_per_customer"
                            placeholder="e.g. 1 (leave blank for unlimited)"
                            class="mt-1 block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        >
                        <p class="mt-1 text-[11px] text-gray-400">Checks both account ID and email address on guest checkout.</p>
                        @error('usage_limit_per_customer')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- Validity Schedule --}}
            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm space-y-4">
                <div class="border-b border-gray-100 pb-3">
                    <h3 class="text-base font-semibold text-gray-900">
                        Validity Dates
                    </h3>
                    <p class="mt-0.5 text-xs text-gray-500">
                        Schedule start and expiration times (in {{ store_setting('timezone', 'Asia/Kolkata') }} time). Leave blank for immediate and indefinite validity.
                    </p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="starts_at" class="block text-xs font-semibold text-gray-700 uppercase tracking-wider">
                            Starts At
                        </label>
                        <input
                            type="datetime-local"
                            id="starts_at"
                            wire:model="starts_at"
                            class="mt-1 block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        >
                        <p class="mt-1 text-[11px] text-gray-400">Coupon becomes active from this date and time.</p>
                        @error('starts_at')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="expires_at" class="block text-xs font-semibold text-gray-700 uppercase tracking-wider">
                            Expires At
                        </label>
                        <input
                            type="datetime-local"
                            id="expires_at"
                            wire:model="expires_at"
                            class="mt-1 block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        >
                        <p class="mt-1 text-[11px] text-gray-400">Coupon cannot be applied after this date and time.</p>
                        @error('expires_at')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

        </div>

        {{-- Sidebar Column (4 cols) --}}
        <div class="space-y-6 lg:col-span-4">

            {{-- Status Card --}}
            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                <h3 class="text-base font-semibold text-gray-900 border-b border-gray-100 pb-3">
                    Status
                </h3>
                <div class="mt-4">
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input
                            type="checkbox"
                            wire:model="is_active"
                            class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 h-4 w-4"
                        >
                        <div>
                            <span class="text-sm font-semibold text-gray-800">Discount is Active</span>
                            <p class="text-xs text-gray-500">Uncheck to immediately deactivate and stop redemptions.</p>
                        </div>
                    </label>
                </div>
            </div>

            {{-- Live Coupon Preview --}}
            <div class="rounded-xl border border-dashed border-gray-300 bg-gray-50/70 p-5">
                <div class="text-xs font-semibold uppercase tracking-wider text-gray-500 mb-3">
                    Live Coupon Preview
                </div>

                <div class="rounded-2xl border border-blue-200 bg-gradient-to-br from-white to-blue-50 p-5 shadow-sm">
                    <div class="flex items-center justify-between">
                        <span class="inline-flex items-center rounded-lg bg-blue-600 px-2.5 py-1 font-mono text-xs font-bold text-white uppercase tracking-wider">
                            {{ $code ?: 'COUPONCODE' }}
                        </span>
                        <span class="text-xs font-semibold {{ $is_active ? 'text-emerald-600' : 'text-gray-400' }}">
                            {{ $is_active ? '● Active' : '○ Inactive' }}
                        </span>
                    </div>

                    <div class="mt-3">
                        <div class="text-2xl font-black text-gray-900">
                            @if ($type === 'percentage')
                                {{ $value ? $value.'%' : '0%' }} OFF
                            @else
                                {{ $value ? format_price($value) : format_price(0) }} OFF
                            @endif
                        </div>
                        <div class="text-sm font-medium text-gray-700 mt-1">
                            {{ $name ?: 'Promotion Name' }}
                        </div>
                    </div>

                    <div class="mt-4 border-t border-blue-100 pt-3 text-xs text-gray-600 space-y-1">
                        @if ($min_order_amount)
                            <div>• Min order: <strong>{{ format_price($min_order_amount) }}</strong></div>
                        @endif
                        @if ($type === 'percentage' && $max_discount_amount)
                            <div>• Max savings: <strong>{{ format_price($max_discount_amount) }}</strong></div>
                        @endif
                        @if ($usage_limit)
                            <div>• Limit: <strong>{{ $usage_limit }} total uses</strong></div>
                        @endif
                        @if ($usage_limit_per_customer)
                            <div>• <strong>{{ $usage_limit_per_customer }} use(s)</strong> per customer</div>
                        @endif
                        @if ($customer_eligibility === 'segments')
                            <div>• Exclusively for: <strong>Specific Customer Segments</strong></div>
                        @endif
                        @if ($applies_to === 'categories')
                            <div>• Applies to: <strong>Selected Categories</strong></div>
                        @elseif ($applies_to === 'products')
                            <div>• Applies to: <strong>Selected Products</strong></div>
                        @endif
                        @if ($expires_at)
                            <div>• Expires: <strong>{{ \Carbon\Carbon::parse($expires_at)->format('M d, Y') }}</strong></div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Summary Notes --}}
            <div class="rounded-xl border border-blue-100 bg-blue-50/50 p-4 text-xs text-blue-900 space-y-2">
                <div class="font-bold flex items-center gap-1.5 text-blue-800">
                    <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>Checkout Integration Notes</span>
                </div>
                <p>
                    All coupon validation and redemptions happen strictly on the server with pessimistic database locking (`lockForUpdate()`) to prevent race conditions during high-volume checkouts.
                </p>
                <p>
                    Discount is deducted from the order subtotal, recalculating tax and eligible free shipping thresholds accordingly.
                </p>
            </div>

        </div>

    </form>

</div>
