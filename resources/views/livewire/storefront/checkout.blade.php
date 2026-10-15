<div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">

    <div class="mb-10">
        <h1 class="text-3xl font-bold tracking-tight text-gray-900">
            Checkout
        </h1>

        <p class="mt-2 text-sm text-gray-500">
            Enter your details to complete your order.
        </p>
    </div>

    @if ($errors->any())
        <div class="mb-8 rounded-2xl border border-red-200 bg-red-50 p-5 text-sm text-red-700 shadow-sm">
            <div class="flex items-center gap-2 font-semibold">
                <svg class="h-5 w-5 shrink-0 text-red-600" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd" />
                </svg>
                <span>Please complete all required fields before placing your order:</span>
            </div>
            <ul class="mt-3 list-inside list-disc space-y-1 text-xs pl-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid gap-8 lg:grid-cols-3">

        {{-- Checkout Form --}}
        <div class="lg:col-span-2 space-y-8">

            {{-- Contact Information --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">

                <h2 class="text-lg font-semibold text-gray-900">
                    Contact Information
                </h2>

                <div class="mt-6 grid gap-5 sm:grid-cols-2">

                    <div>
                        <label class="text-sm font-medium text-gray-700">
                            First Name <span class="text-red-500">*</span>
                        </label>

                        <input
                            type="text"
                            wire:model.blur="firstName"
                            class="mt-2 w-full rounded-lg border px-4 py-3 text-sm outline-none transition focus:ring-2 focus:ring-blue-100
                                {{ $errors->has('firstName') ? 'border-red-500 bg-red-50/20 text-red-900 focus:border-red-500' : 'border-gray-300 focus:border-blue-500' }}"
                            placeholder="John"
                        >

                        @error('firstName')
                            <p class="mt-1 text-xs text-red-600 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="text-sm font-medium text-gray-700">
                            Last Name <span class="text-red-500">*</span>
                        </label>

                        <input
                            type="text"
                            wire:model.blur="lastName"
                            class="mt-2 w-full rounded-lg border px-4 py-3 text-sm outline-none transition focus:ring-2 focus:ring-blue-100
                                {{ $errors->has('lastName') ? 'border-red-500 bg-red-50/20 text-red-900 focus:border-red-500' : 'border-gray-300 focus:border-blue-500' }}"
                            placeholder="Doe"
                        >

                        @error('lastName')
                            <p class="mt-1 text-xs text-red-600 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="text-sm font-medium text-gray-700">
                            Email Address <span class="text-red-500">*</span>
                        </label>

                        <input
                            type="email"
                            wire:model.blur="email"
                            class="mt-2 w-full rounded-lg border px-4 py-3 text-sm outline-none transition focus:ring-2 focus:ring-blue-100
                                {{ $errors->has('email') ? 'border-red-500 bg-red-50/20 text-red-900 focus:border-red-500' : 'border-gray-300 focus:border-blue-500' }}"
                            placeholder="john@example.com"
                        >

                        @error('email')
                            <p class="mt-1 text-xs text-red-600 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="text-sm font-medium text-gray-700">
                            Phone <span class="text-red-500">*</span>
                        </label>

                        <input
                            type="tel"
                            wire:model.blur="phone"
                            class="mt-2 w-full rounded-lg border px-4 py-3 text-sm outline-none transition focus:ring-2 focus:ring-blue-100
                                {{ $errors->has('phone') ? 'border-red-500 bg-red-50/20 text-red-900 focus:border-red-500' : 'border-gray-300 focus:border-blue-500' }}"
                            placeholder="+91 9876543210"
                        >

                        @error('phone')
                            <p class="mt-1 text-xs text-red-600 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                </div>

            </div>

            {{-- Shipping Address --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">

                <h2 class="text-lg font-semibold text-gray-900">
                    Shipping Address
                </h2>

                <div class="mt-6 space-y-5">

                    <div>
                        <label class="text-sm font-medium text-gray-700">
                            Street Address <span class="text-red-500">*</span>
                        </label>

                        <textarea
                            wire:model.blur="address"
                            rows="3"
                            class="mt-2 w-full rounded-lg border px-4 py-3 text-sm outline-none transition focus:ring-2 focus:ring-blue-100
                                {{ $errors->has('address') ? 'border-red-500 bg-red-50/20 text-red-900 focus:border-red-500' : 'border-gray-300 focus:border-blue-500' }}"
                            placeholder="House / Flat No., Street, Area"
                        ></textarea>

                        @error('address')
                            <p class="mt-1 text-xs text-red-600 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid gap-5 sm:grid-cols-2">

                        <div>
                            <label class="text-sm font-medium text-gray-700">
                                City <span class="text-red-500">*</span>
                            </label>

                            <input
                                type="text"
                                wire:model.blur="city"
                                class="mt-2 w-full rounded-lg border px-4 py-3 text-sm outline-none transition focus:ring-2 focus:ring-blue-100
                                    {{ $errors->has('city') ? 'border-red-500 bg-red-50/20 text-red-900 focus:border-red-500' : 'border-gray-300 focus:border-blue-500' }}"
                                placeholder="City"
                            >

                            @error('city')
                                <p class="mt-1 text-xs text-red-600 font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="text-sm font-medium text-gray-700">
                                State <span class="text-red-500">*</span>
                            </label>

                            <input
                                type="text"
                                wire:model.blur="state"
                                class="mt-2 w-full rounded-lg border px-4 py-3 text-sm outline-none transition focus:ring-2 focus:ring-blue-100
                                    {{ $errors->has('state') ? 'border-red-500 bg-red-50/20 text-red-900 focus:border-red-500' : 'border-gray-300 focus:border-blue-500' }}"
                                placeholder="State"
                            >

                            @error('state')
                                <p class="mt-1 text-xs text-red-600 font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="text-sm font-medium text-gray-700">
                                Postal Code / PIN <span class="text-red-500">*</span>
                            </label>

                            <input
                                type="text"
                                wire:model.blur="postalCode"
                                class="mt-2 w-full rounded-lg border px-4 py-3 text-sm outline-none transition focus:ring-2 focus:ring-blue-100
                                    {{ $errors->has('postalCode') ? 'border-red-500 bg-red-50/20 text-red-900 focus:border-red-500' : 'border-gray-300 focus:border-blue-500' }}"
                                placeholder="Postal code"
                            >

                            @error('postalCode')
                                <p class="mt-1 text-xs text-red-600 font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="text-sm font-medium text-gray-700">
                                Country <span class="text-red-500">*</span>
                            </label>

                            <input
                                type="text"
                                wire:model.blur="country"
                                class="mt-2 w-full rounded-lg border px-4 py-3 text-sm outline-none transition focus:ring-2 focus:ring-blue-100
                                    {{ $errors->has('country') ? 'border-red-500 bg-red-50/20 text-red-900 focus:border-red-500' : 'border-gray-300 focus:border-blue-500' }}"
                                placeholder="Country"
                            >

                            @error('country')
                                <p class="mt-1 text-xs text-red-600 font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                    </div>

                </div>

            </div>

            {{-- Billing Address --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">

                <label class="flex items-center gap-3 cursor-pointer">

                    <input
                        type="checkbox"
                        wire:model="billingSameAsShipping"
                        class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                    >

                    <span class="text-sm font-medium text-gray-700">
                        Billing address is the same as shipping address
                    </span>

                </label>

            </div>

            {{-- Payment Method Selection --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">

                <h2 class="text-lg font-semibold text-gray-900">
                    Payment Method
                </h2>

                <p class="mt-1 text-sm text-gray-500">
                    Select how you would like to pay for your order.
                </p>

                <div class="mt-6 space-y-4">

                    {{-- COD Option --}}
                    @if ($enableCod)
                        <label class="relative flex cursor-pointer rounded-xl border p-4 transition {{ $paymentMethod === 'cod' ? 'border-blue-600 bg-blue-50/20 ring-1 ring-blue-600' : 'border-gray-200 hover:border-gray-300' }}">
                            <input
                                type="radio"
                                name="paymentMethod"
                                value="cod"
                                wire:model.live="paymentMethod"
                                class="mt-1 text-blue-600 focus:ring-blue-500"
                            >
                            <div class="ml-3 flex flex-col">
                                <span class="text-sm font-semibold text-gray-900">Cash on Delivery (COD)</span>
                                <span class="mt-0.5 text-xs text-gray-500">{{ $codInstructions ?: 'Pay in cash when your order arrives at your delivery address.' }}</span>
                            </div>
                        </label>
                    @endif

                    {{-- Credit / Debit Card Option --}}
                    @if ($enableCard)
                        <label class="relative flex cursor-pointer rounded-xl border p-4 transition {{ $paymentMethod === 'card' ? 'border-blue-600 bg-blue-50/20 ring-1 ring-blue-600' : 'border-gray-200 hover:border-gray-300' }}">
                            <input
                                type="radio"
                                name="paymentMethod"
                                value="card"
                                wire:model.live="paymentMethod"
                                class="mt-1 text-blue-600 focus:ring-blue-500"
                            >
                            <div class="ml-3 flex flex-col">
                                <div class="flex items-center gap-2">
                                    <span class="text-sm font-semibold text-gray-900">Credit / Debit Card</span>
                                    <span class="rounded bg-gray-100 px-1.5 py-0.5 text-[10px] font-semibold text-gray-600 uppercase">Visa / MasterCard / RuPay</span>
                                </div>
                                <span class="mt-0.5 text-xs text-gray-500">Secure card payment with instant order processing.</span>
                            </div>
                        </label>

                        {{-- Card Details Input Fields --}}
                        @if ($paymentMethod === 'card')
                            <div class="mt-2 space-y-4 rounded-xl border border-gray-200 bg-gray-50/50 p-4">
                                <div>
                                    <label class="text-xs font-semibold uppercase tracking-wider text-gray-600">
                                        Cardholder Name <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        type="text"
                                        wire:model.blur="cardName"
                                        class="mt-1.5 w-full rounded-lg border px-3.5 py-2.5 text-sm outline-none transition focus:ring-2 focus:ring-blue-100 {{ $errors->has('cardName') ? 'border-red-500 bg-red-50/20 text-red-900' : 'border-gray-300 bg-white focus:border-blue-500' }}"
                                        placeholder="Name on card"
                                    >
                                    @error('cardName')
                                        <p class="mt-1 text-xs text-red-600 font-medium">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label class="text-xs font-semibold uppercase tracking-wider text-gray-600">
                                        Card Number <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        type="text"
                                        wire:model.blur="cardNumber"
                                        maxlength="19"
                                        class="mt-1.5 w-full rounded-lg border px-3.5 py-2.5 text-sm outline-none transition focus:ring-2 focus:ring-blue-100 {{ $errors->has('cardNumber') ? 'border-red-500 bg-red-50/20 text-red-900' : 'border-gray-300 bg-white focus:border-blue-500' }}"
                                        placeholder="1234 5678 9012 3456"
                                    >
                                    @error('cardNumber')
                                        <p class="mt-1 text-xs text-red-600 font-medium">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="text-xs font-semibold uppercase tracking-wider text-gray-600">
                                            Expiry (MM/YY) <span class="text-red-500">*</span>
                                        </label>
                                        <input
                                            type="text"
                                            wire:model.blur="cardExpiry"
                                            placeholder="12/28"
                                            maxlength="5"
                                            class="mt-1.5 w-full rounded-lg border px-3.5 py-2.5 text-sm outline-none transition focus:ring-2 focus:ring-blue-100 {{ $errors->has('cardExpiry') ? 'border-red-500 bg-red-50/20 text-red-900' : 'border-gray-300 bg-white focus:border-blue-500' }}"
                                        >
                                        @error('cardExpiry')
                                            <p class="mt-1 text-xs text-red-600 font-medium">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div>
                                        <label class="text-xs font-semibold uppercase tracking-wider text-gray-600">
                                            CVV <span class="text-red-500">*</span>
                                        </label>
                                        <input
                                            type="password"
                                            wire:model.blur="cardCvv"
                                            placeholder="123"
                                            maxlength="4"
                                            class="mt-1.5 w-full rounded-lg border px-3.5 py-2.5 text-sm outline-none transition focus:ring-2 focus:ring-blue-100 {{ $errors->has('cardCvv') ? 'border-red-500 bg-red-50/20 text-red-900' : 'border-gray-300 bg-white focus:border-blue-500' }}"
                                        >
                                        @error('cardCvv')
                                            <p class="mt-1 text-xs text-red-600 font-medium">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endif

                    {{-- UPI Option --}}
                    @if ($enableUpi)
                        <label class="relative flex cursor-pointer rounded-xl border p-4 transition {{ $paymentMethod === 'upi' ? 'border-blue-600 bg-blue-50/20 ring-1 ring-blue-600' : 'border-gray-200 hover:border-gray-300' }}">
                            <input
                                type="radio"
                                name="paymentMethod"
                                value="upi"
                                wire:model.live="paymentMethod"
                                class="mt-1 text-blue-600 focus:ring-blue-500"
                            >
                            <div class="ml-3 flex flex-col">
                                <div class="flex items-center gap-2">
                                    <span class="text-sm font-semibold text-gray-900">UPI Instant Payment</span>
                                    <span class="rounded bg-green-100 px-1.5 py-0.5 text-[10px] font-semibold text-green-700 uppercase">GPay / PhonePe / Paytm</span>
                                </div>
                                <span class="mt-0.5 text-xs text-gray-500">{{ $upiInstructions ?: 'Pay directly from your bank app using your UPI Virtual ID.' }}</span>
                            </div>
                        </label>

                        {{-- UPI Details Input Field --}}
                        @if ($paymentMethod === 'upi')
                            <div class="mt-2 space-y-2 rounded-xl border border-gray-200 bg-gray-50/50 p-4">
                                <label class="text-xs font-semibold uppercase tracking-wider text-gray-600">
                                    Virtual Payment Address (UPI ID) <span class="text-red-500">*</span>
                                </label>
                                <input
                                    type="text"
                                    wire:model.blur="upiId"
                                    class="mt-1.5 w-full rounded-lg border px-3.5 py-2.5 text-sm outline-none transition focus:ring-2 focus:ring-blue-100 {{ $errors->has('upiId') ? 'border-red-500 bg-red-50/20 text-red-900' : 'border-gray-300 bg-white focus:border-blue-500' }}"
                                    placeholder="username@okhdfcbank"
                                >
                                @if ($upiVpa)
                                    <p class="text-xs text-gray-500 font-medium">Store UPI VPA: <span class="font-mono text-gray-700">{{ $upiVpa }}</span></p>
                                @endif
                                <p class="text-xs text-gray-500">Format: yourname@bank, username@upi, mobile@paytm</p>
                                @error('upiId')
                                    <p class="mt-1 text-xs text-red-600 font-medium">{{ $message }}</p>
                                @enderror
                            </div>
                        @endif
                    @endif

                    @if (! $enableCod && ! $enableCard && ! $enableUpi)
                        <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-xs text-amber-800">
                            No payment gateways are currently active. Please contact store support.
                        </div>
                    @endif

                </div>

            </div>

        </div>

        {{-- Order Summary --}}
        <div>

            <div class="sticky top-24 rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">

                <h2 class="text-lg font-semibold text-gray-900">
                    Order Summary
                </h2>

                <div class="mt-6 space-y-4">

                    @foreach ($cart as $item)

                        <div class="flex justify-between gap-4 text-sm">

                            <div>
                                <p class="font-medium text-gray-900">
                                    {{ $item['product_name'] ?? $item['title'] }}
                                </p>

                                <p class="text-gray-500">
                                    Qty: {{ $item['quantity'] }}
                                </p>
                            </div>

                            <span class="font-medium text-gray-900">
                                {{ format_price($item['price'] * $item['quantity']) }}
                            </span>

                        </div>

                    @endforeach

                </div>

                {{-- Promo Code Section --}}
                <div class="mt-6 border-t border-gray-200 pt-4">
                    @if ($appliedCoupon)
                        <div class="flex items-center justify-between rounded-xl bg-emerald-50 border border-emerald-200 px-3.5 py-2.5">
                            <div class="flex items-center gap-2 min-w-0">
                                <svg class="h-4 w-4 shrink-0 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                </svg>
                                <div class="truncate text-xs">
                                    <span class="font-bold uppercase tracking-wider text-emerald-800">{{ $appliedCoupon['code'] }}</span>
                                    <span class="text-emerald-700">({{ $appliedCoupon['type'] === 'percentage' ? $appliedCoupon['value'].'%' : format_price($appliedCoupon['value']) }} off)</span>
                                </div>
                            </div>
                            <button
                                type="button"
                                wire:click="removeCoupon"
                                class="shrink-0 text-xs font-semibold text-red-600 hover:text-red-800 transition"
                            >
                                Remove
                            </button>
                        </div>
                    @else
                        <form wire:submit.prevent="applyCoupon" class="space-y-1.5">
                            <label for="checkoutCouponCode" class="block text-xs font-medium text-gray-700">Have a coupon code?</label>
                            <div class="flex gap-2">
                                <input
                                    type="text"
                                    id="checkoutCouponCode"
                                    wire:model="couponCode"
                                    placeholder="Enter code"
                                    class="block w-full uppercase rounded-lg border-gray-300 text-xs shadow-sm focus:border-blue-500 focus:ring-blue-500 py-2 px-3"
                                >
                                <button
                                    type="submit"
                                    wire:loading.attr="disabled"
                                    class="rounded-lg bg-gray-900 px-3.5 py-2 text-xs font-semibold text-white hover:bg-gray-800 transition shrink-0 disabled:opacity-50"
                                >
                                    Apply
                                </button>
                            </div>
                            @error('couponCode')
                                <p class="text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </form>
                    @endif
                </div>

                <div class="mt-4 border-t border-gray-200 pt-4">

                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">
                            Subtotal
                        </span>

                        <span class="font-medium text-gray-900">
                            {{ format_price($subtotal) }}
                        </span>
                    </div>

                    @if ($discountAmount > 0)
                        <div class="mt-3 flex justify-between text-sm text-emerald-600 font-medium">
                            <span class="flex items-center gap-1">
                                <span>Discount ({{ $appliedCoupon['code'] ?? 'Coupon' }})</span>
                            </span>
                            <span class="font-semibold">-{{ format_price($discountAmount) }}</span>
                        </div>
                    @endif

                    <div class="mt-3 flex justify-between text-sm">
                        <span class="text-gray-600">
                            Shipping
                        </span>

                        @if ($shippingFee <= 0)
                            <span class="font-medium text-green-600">
                                Free
                            </span>
                        @else
                            <span class="font-medium text-gray-900">
                                {{ format_price($shippingFee) }}
                            </span>
                        @endif
                    </div>

                    @if ($taxAmount > 0)
                        @if ($taxInclusive)
                            <div class="mt-3 flex justify-between text-xs text-gray-500">
                                <span>
                                    Includes {{ $taxName }} ({{ $taxRate }}%)
                                </span>

                                <span>
                                    {{ format_price($taxAmount) }}
                                </span>
                            </div>
                        @else
                            <div class="mt-3 flex justify-between text-sm">
                                <span class="text-gray-600">
                                    {{ $taxName }} ({{ $taxRate }}%)
                                </span>

                                <span class="font-medium text-gray-900">
                                    {{ format_price($taxAmount) }}
                                </span>
                            </div>
                        @endif
                    @endif

                    <div class="mt-4 flex justify-between border-t border-gray-200 pt-4">
                        <span class="text-base font-semibold text-gray-900">
                            Total
                        </span>

                        <span class="text-xl font-bold text-gray-900">
                            {{ format_price($total) }}
                        </span>
                    </div>

                </div>

                <button
                    type="button"
                    wire:click="placeOrder"
                    wire:loading.attr="disabled"
                    class="mt-6 w-full rounded-xl bg-gray-900 px-6 py-4 text-sm font-semibold text-white transition hover:bg-blue-600 shadow-sm disabled:cursor-not-allowed disabled:opacity-60"
                >
                    <span wire:loading.remove wire:target="placeOrder">
                        @if ($paymentMethod === 'cod')
                            Place Order (Cash on Delivery)
                        @elseif ($paymentMethod === 'card')
                            Pay {{ format_price($total) }} with Card
                        @elseif ($paymentMethod === 'upi')
                            Pay {{ format_price($total) }} with UPI
                        @else
                            Place Order — {{ format_price($total) }}
                        @endif
                    </span>

                    <span wire:loading wire:target="placeOrder" class="inline-flex items-center gap-2">
                        <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Processing Order...
                    </span>
                </button>

                @if ($errors->any())
                    <p class="mt-3 text-center text-xs font-medium text-red-600">
                        Please fill in all required fields to place your order.
                    </p>
                @endif

                @error('checkout')
                    <div class="mt-4 rounded-lg bg-red-50 px-4 py-3 text-sm text-red-700 border border-red-200">
                        {{ $message }}
                    </div>
                @enderror

            </div>

        </div>

    </div>

</div>