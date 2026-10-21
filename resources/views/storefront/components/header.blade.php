<header class="sticky top-0 z-50 border-b border-gray-200/80 bg-white/90 backdrop-blur-md shadow-xs transition-all" x-data="{ mobileOpen: false }">

    <div class="mx-auto px-6 lg:px-8" style="max-width: var(--store-page-width);">

        <div class="flex h-20 items-center justify-between gap-4">

            {{-- Logo --}}
            <a href="{{ route('storefront.home') }}" class="flex items-center gap-2.5 shrink-0 transition-transform duration-200 hover:scale-[1.02]">
                @php
                    $storeLogo = store_logo();
                    $storeName = store_name();
                @endphp
                @if ($storeLogo && Storage::disk('public')->exists($storeLogo))
                    <img src="{{ Storage::disk('public')->url($storeLogo) }}" alt="{{ $storeName }}" class="h-10 w-auto max-w-[180px] object-contain">
                @else
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-600 text-lg font-black text-white shadow-sm ring-2 ring-blue-500/20" style="background-color: var(--store-primary, #2563eb);">
                        {{ strtoupper(substr($storeName, 0, 1)) ?: 'C' }}
                    </div>

                    <span class="text-xl font-extrabold tracking-tight text-gray-900">
                        {{ $storeName }}
                    </span>
                @endif
            </a>


            {{-- Desktop Navigation --}}
            <nav class="hidden items-center gap-8 md:flex">
                @php
                    $headerMenu = \App\Models\Menu::getByLocation('header');
                    $headerItems = $headerMenu?->rootItems ?? collect();
                @endphp

                @if ($headerItems->isNotEmpty())
                    @foreach ($headerItems as $item)
                        @if ($item->children->isNotEmpty())
                            <div class="relative" x-data="{ open: false }" @mouseleave="open = false">
                                <button
                                    type="button"
                                    @mouseover="open = true"
                                    @click="open = !open"
                                    class="store-nav-link flex items-center gap-1.5 text-sm font-semibold text-gray-700 transition hover:text-blue-600"
                                >
                                    <span>{{ $item->title }}</span>
                                    <svg class="h-3.5 w-3.5 text-gray-400 transition-transform duration-200" :class="{ 'rotate-180': open }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>
                                <div
                                    x-show="open"
                                    x-transition:enter="transition ease-out duration-200"
                                    x-transition:enter-start="opacity-0 translate-y-1 scale-95"
                                    x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                                    x-transition:leave="transition ease-in duration-150"
                                    x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                                    x-transition:leave-end="opacity-0 translate-y-1 scale-95"
                                    class="absolute left-0 mt-3 w-52 rounded-2xl border border-gray-100 bg-white/95 p-2 shadow-xl backdrop-blur-md z-50 ring-1 ring-black/5"
                                    style="display: none;"
                                >
                                    @foreach ($item->children as $child)
                                        <a
                                            href="{{ $child->url }}"
                                            target="{{ $child->target }}"
                                            class="block rounded-xl px-3.5 py-2.5 text-xs font-medium text-gray-700 hover:bg-gray-50 hover:text-blue-600 transition"
                                        >
                                            {{ $child->title }}
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @else
                            <a
                                href="{{ $item->url }}"
                                target="{{ $item->target }}"
                                class="store-nav-link text-sm font-semibold text-gray-700 transition hover:text-blue-600"
                            >
                                {{ $item->title }}
                            </a>
                        @endif
                    @endforeach
                @else
                    <a href="{{ route('storefront.home') }}" class="store-nav-link text-sm font-semibold text-gray-700 transition hover:text-blue-600">Home</a>
                    <a href="{{ route('storefront.products.index') }}" class="store-nav-link text-sm font-semibold text-gray-700 transition hover:text-blue-600">Products</a>
                    <a href="{{ route('storefront.home') }}#categories" class="store-nav-link text-sm font-semibold text-gray-700 transition hover:text-blue-600">Categories</a>
                @endif

                @auth
                    <a
                        href="{{ route('storefront.orders.index') }}"
                        class="store-nav-link text-sm font-semibold text-gray-700 hover:text-blue-600"
                    >
                        My Orders
                    </a>
                @endauth
            </nav>


            {{-- Actions --}}
            <div class="flex items-center gap-3">

                {{-- Database-backed Search Form --}}
                <form action="{{ route('storefront.products.index') }}" method="GET" class="relative hidden sm:block">
                    <input
                        type="text"
                        name="q"
                        placeholder="Search catalog..."
                        value="{{ request('q', request('search')) }}"
                        class="w-48 lg:w-64 rounded-full border border-gray-200 bg-gray-50/80 py-2 pl-9 pr-3 text-xs outline-none transition-all duration-200 focus:border-blue-500 focus:bg-white focus:w-72 focus:ring-2 focus:ring-blue-500/20 shadow-xs"
                    >
                    <button type="submit" class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition-colors" aria-label="Submit search">
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="h-4 w-4"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                            stroke-width="2"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="m21 21-4.35-4.35m1.35-5.65a7 7 0 1 1-14 0 7 7 0 0 1 14 0Z"
                            />
                        </svg>
                    </button>
                </form>

                {{-- Cart Button --}}
                <a
                    href="{{ route('storefront.cart') }}"
                    class="relative flex items-center justify-center p-2.5 rounded-full text-gray-700 transition-all duration-200 hover:bg-gray-100 hover:text-blue-600 active:scale-95"
                    aria-label="Cart"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-5 w-5"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                        stroke-width="1.8"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M3 3h2l2.4 11.2a2 2 0 0 0 2 1.6h7.9a2 2 0 0 0 1.95-1.55L21 7H6"
                        />
                        <circle cx="10" cy="20" r="1" />
                        <circle cx="18" cy="20" r="1" />
                    </svg>

                    <livewire:storefront.cart-counter />
                </a>

                {{-- Mobile Menu Button --}}
                <button
                    type="button"
                    @click="mobileOpen = !mobileOpen"
                    class="flex items-center justify-center rounded-xl p-2 text-gray-700 transition hover:bg-gray-100 md:hidden active:scale-95"
                    aria-label="Toggle menu"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-6 w-6"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <path
                            x-show="!mobileOpen"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M4 6h16M4 12h16M4 18h16"
                        />
                        <path
                            x-show="mobileOpen"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M6 18L18 6M6 6l12 12"
                            style="display: none;"
                        />
                    </svg>
                </button>

            </div>

        </div>

        {{-- Mobile Drawer Menu --}}
        <div
            x-show="mobileOpen"
            x-collapse
            class="border-t border-gray-100 py-4 md:hidden"
        >
            <form action="{{ route('storefront.products.index') }}" method="GET" class="mb-4">
                <input
                    type="text"
                    name="q"
                    placeholder="Search products..."
                    value="{{ request('q', request('search')) }}"
                    class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-xs outline-none focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-100"
                >
            </form>

            <nav class="flex flex-col gap-1 text-sm font-semibold text-gray-700">
                @if ($headerItems->isNotEmpty())
                    @foreach ($headerItems as $item)
                        @if ($item->children->isNotEmpty())
                            <div x-data="{ expanded: false }" class="py-1">
                                <button
                                    type="button"
                                    @click="expanded = !expanded"
                                    class="flex w-full items-center justify-between rounded-lg px-3 py-2 text-left hover:bg-gray-50 hover:text-blue-600 transition"
                                >
                                    <span>{{ $item->title }}</span>
                                    <svg class="h-4 w-4 transform transition-transform duration-200" :class="{ 'rotate-180': expanded }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>
                                <div x-show="expanded" x-collapse class="pl-4 pt-1 space-y-1 border-l-2 border-gray-100 mt-1">
                                    @foreach ($item->children as $child)
                                        <a
                                            href="{{ $child->url }}"
                                            target="{{ $child->target }}"
                                            class="block rounded-lg px-3 py-1.5 text-xs text-gray-600 hover:bg-gray-50 hover:text-blue-600 transition"
                                        >
                                            {{ $child->title }}
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @else
                            <a
                                href="{{ $item->url }}"
                                target="{{ $item->target }}"
                                class="rounded-lg px-3 py-2 hover:bg-gray-50 hover:text-blue-600 transition"
                            >
                                {{ $item->title }}
                            </a>
                        @endif
                    @endforeach
                @else
                    <a href="{{ route('storefront.home') }}" class="rounded-lg px-3 py-2 hover:bg-gray-50 hover:text-blue-600 transition">Home</a>
                    <a href="{{ route('storefront.products.index') }}" class="rounded-lg px-3 py-2 hover:bg-gray-50 hover:text-blue-600 transition">Products</a>
                    <a href="{{ route('storefront.home') }}#categories" class="rounded-lg px-3 py-2 hover:bg-gray-50 hover:text-blue-600 transition">Categories</a>
                @endif

                @auth
                    <a href="{{ route('storefront.orders.index') }}" class="rounded-lg px-3 py-2 hover:bg-gray-50 hover:text-blue-600 transition border-t border-gray-100 pt-2">My Orders</a>
                @endauth
            </nav>
        </div>

    </div>

</header>