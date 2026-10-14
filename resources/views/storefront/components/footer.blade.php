<footer class="border-t border-gray-200 bg-gray-950 text-gray-300">

    <div class="mx-auto max-w-7xl px-6 py-14 lg:px-8">

        <div class="grid gap-10 md:grid-cols-4">

            {{-- Brand --}}
            <div class="md:col-span-2">

                <a
                    href="{{ route('storefront.home') }}"
                    class="flex items-center gap-2.5"
                >
                    @php
                        $storeLogo = store_logo();
                        $storeName = store_name();
                        $storeTagline = store_setting('store_tagline');
                    @endphp
                    @if ($storeLogo && Storage::disk('public')->exists($storeLogo))
                        <img src="{{ Storage::disk('public')->url($storeLogo) }}" alt="{{ $storeName }}" class="h-9 w-auto max-w-[160px] object-contain brightness-0 invert">
                    @else
                        <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-blue-600 text-lg font-bold text-white">
                            {{ strtoupper(substr($storeName, 0, 1)) ?: 'C' }}
                        </div>

                        <span class="text-xl font-bold text-white">
                            {{ $storeName }}
                        </span>
                    @endif
                </a>

                <p class="mt-4 max-w-md text-sm leading-6 text-gray-400">
                    {{ $storeTagline ?: 'A modern e-commerce platform built to provide a simple, secure and seamless shopping experience.' }}
                </p>

            </div>


            {{-- Store --}}
            <div>

                <h3 class="text-sm font-semibold uppercase tracking-wider text-white">
                    Store
                </h3>

                <ul class="mt-4 space-y-3 text-sm">

                    <li>
                        <a href="{{ route('storefront.home') }}" class="transition hover:text-white">
                            Home
                        </a>
                    </li>

                    <li>
                        <a href="#categories" class="transition hover:text-white">
                            Categories
                        </a>
                    </li>

                    <li>
                        <a href="#products" class="transition hover:text-white">
                            Products
                        </a>
                    </li>

                </ul>

            </div>


            {{-- Support & Contact --}}
            <div>

                <h3 class="text-sm font-semibold uppercase tracking-wider text-white">
                    Support
                </h3>

                @php
                    $contactEmail = store_setting('contact_email');
                    $contactPhone = store_setting('contact_phone');
                    $businessAddress = business_address();
                @endphp

                <ul class="mt-4 space-y-3 text-sm">

                    @if ($contactEmail)
                        <li>
                            <a href="mailto:{{ $contactEmail }}" class="transition hover:text-white flex items-center gap-2">
                                <svg class="h-4 w-4 shrink-0 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                                <span class="truncate">{{ $contactEmail }}</span>
                            </a>
                        </li>
                    @endif

                    @if ($contactPhone)
                        <li>
                            <a href="tel:{{ $contactPhone }}" class="transition hover:text-white flex items-center gap-2">
                                <svg class="h-4 w-4 shrink-0 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                </svg>
                                <span>{{ $contactPhone }}</span>
                            </a>
                        </li>
                    @endif

                    @if ($businessAddress)
                        <li class="pt-1 text-xs text-gray-400 leading-relaxed flex items-start gap-2">
                            <svg class="h-4 w-4 shrink-0 mt-0.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            <span>{{ $businessAddress }}</span>
                        </li>
                    @endif

                </ul>

            </div>

        </div>


        <div class="mt-12 border-t border-gray-800 pt-6 flex flex-col sm:flex-row items-center justify-between gap-4">

            <p class="text-sm text-gray-500">
                © {{ date('Y') }} {{ store_name() }}. All rights reserved.
            </p>

            @if ($businessAddress)
                <p class="text-xs text-gray-500">
                    {{ store_name() }}
                </p>
            @endif

        </div>

    </div>

</footer>