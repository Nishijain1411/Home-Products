<div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">

    <div class="mb-10">
        <h1 class="text-3xl font-bold tracking-tight text-gray-900">
            Checkout
        </h1>

        <p class="mt-2 text-sm text-gray-500">
            Enter your details to complete your order.
        </p>
    </div>

    <div class="grid gap-8 lg:grid-cols-3">

        {{-- Checkout Form --}}
        <div class="lg:col-span-2 space-y-8">

            {{-- Contact Information --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-6">

                <h2 class="text-lg font-semibold text-gray-900">
                    Contact Information
                </h2>

                <div class="mt-6 grid gap-5 sm:grid-cols-2">

                    <div>
                        <label class="text-sm font-medium text-gray-700">
                            First Name
                        </label>

                        <input
                            type="text"
                            wire:model="firstName"
                            class="mt-2 w-full rounded-lg border border-gray-300 px-4 py-3 text-sm focus:border-blue-500 focus:ring-blue-500"
                        >

                        @error('firstName')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="text-sm font-medium text-gray-700">
                            Last Name
                        </label>

                        <input
                            type="text"
                            wire:model="lastName"
                            class="mt-2 w-full rounded-lg border border-gray-300 px-4 py-3 text-sm focus:border-blue-500 focus:ring-blue-500"
                        >

                        @error('lastName')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="text-sm font-medium text-gray-700">
                            Email Address
                        </label>

                        <input
                            type="email"
                            wire:model="email"
                            class="mt-2 w-full rounded-lg border border-gray-300 px-4 py-3 text-sm focus:border-blue-500 focus:ring-blue-500"
                        >

                        @error('email')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="text-sm font-medium text-gray-700">
                            Phone
                        </label>

                        <input
                            type="text"
                            wire:model="phone"
                            class="mt-2 w-full rounded-lg border border-gray-300 px-4 py-3 text-sm focus:border-blue-500 focus:ring-blue-500"
                        >

                        @error('phone')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                </div>

            </div>

            {{-- Shipping Address --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-6">

                <h2 class="text-lg font-semibold text-gray-900">
                    Shipping Address
                </h2>

                <div class="mt-6 space-y-5">

                    <div>
                        <label class="text-sm font-medium text-gray-700">
                            Address
                        </label>

                        <textarea
                            wire:model="address"
                            rows="3"
                            class="mt-2 w-full rounded-lg border border-gray-300 px-4 py-3 text-sm focus:border-blue-500 focus:ring-blue-500"
                        ></textarea>
                    </div>

                    <div class="grid gap-5 sm:grid-cols-2">

                        <div>
                            <label class="text-sm font-medium text-gray-700">
                                City
                            </label>

                            <input
                                type="text"
                                wire:model="city"
                                class="mt-2 w-full rounded-lg border border-gray-300 px-4 py-3 text-sm"
                            >
                        </div>

                        <div>
                            <label class="text-sm font-medium text-gray-700">
                                State
                            </label>

                            <input
                                type="text"
                                wire:model="state"
                                class="mt-2 w-full rounded-lg border border-gray-300 px-4 py-3 text-sm"
                            >
                        </div>

                        <div>
                            <label class="text-sm font-medium text-gray-700">
                                Postal Code
                            </label>

                            <input
                                type="text"
                                wire:model="postalCode"
                                class="mt-2 w-full rounded-lg border border-gray-300 px-4 py-3 text-sm"
                            >
                        </div>

                        <div>
                            <label class="text-sm font-medium text-gray-700">
                                Country
                            </label>

                            <input
                                type="text"
                                wire:model="country"
                                class="mt-2 w-full rounded-lg border border-gray-300 px-4 py-3 text-sm"
                            >
                        </div>

                    </div>

                </div>

            </div>

            {{-- Billing Address --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-6">

                <label class="flex items-center gap-3">

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

        </div>

        {{-- Order Summary --}}
        <div>

            <div class="sticky top-6 rounded-2xl border border-gray-200 bg-white p-6">

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
                                ₹{{ number_format($item['price'] * $item['quantity'], 2) }}
                            </span>

                        </div>

                    @endforeach

                </div>

                <div class="mt-6 border-t border-gray-200 pt-6">

                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">
                            Subtotal
                        </span>

                        <span class="font-medium text-gray-900">
                            ₹{{ number_format($subtotal, 2) }}
                        </span>
                    </div>

                    <div class="mt-3 flex justify-between text-sm">
                        <span class="text-gray-600">
                            Shipping
                        </span>

                        <span class="font-medium text-gray-900">
                            Free
                        </span>
                    </div>

                    <div class="mt-4 flex justify-between border-t border-gray-200 pt-4">
                        <span class="text-base font-semibold text-gray-900">
                            Total
                        </span>

                        <span class="text-xl font-bold text-gray-900">
                            ₹{{ number_format($subtotal, 2) }}
                        </span>
                    </div>

                </div>

                <button
                    type="button"
                    wire:click="placeOrder"
                    wire:loading.attr="disabled"
                    class="mt-6 w-full rounded-xl bg-gray-900 px-6 py-4 text-sm font-semibold text-white transition hover:bg-blue-600 disabled:cursor-not-allowed disabled:opacity-60"
                >
                    <span wire:loading.remove wire:target="placeOrder">
                        Place Order
                    </span>

                    <span wire:loading wire:target="placeOrder">
                        Processing Order...
                    </span>
                </button>

                @error('checkout')
                    <div class="mt-4 rounded-lg bg-red-50 px-4 py-3 text-sm text-red-700">
                        {{ $message }}
                    </div>
                @enderror

            </div>

        </div>

    </div>

</div>