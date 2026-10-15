<header class="sticky top-0 z-50 border-b border-gray-200 bg-white/95 backdrop-blur" x-data="{ mobileOpen: false }">

    <div class="mx-auto px-6 lg:px-8" style="max-width: var(--store-page-width);">

        <div class="flex h-20 items-center justify-between gap-4">

            {{-- Logo --}}
            <a href="{{ route('storefront.home') }}" class="flex items-center gap-2.5 shrink-0">
                @php
                    $storeLogo = store_logo();
                    $storeName = store_name();
                @endphp
                @if ($storeLogo && Storage::disk('public')->exists($storeLogo))
                    <img src="{{ Storage::disk('public')->url($storeLogo) }}" alt="{{ $storeName }}" class="h-10 w-auto max-w-[180px] object-contain">
                @else
                    <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-blue-600 text-lg font-bold text-white shadow-sm" style="background-color: var(--store-primary, #2563eb);">
                        {{ strtoupper(substr($storeName, 0, 1)) ?: 'C' }}
                    </div>

                    <span class="text-xl font-bold tracking-tight text-gray-900">
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
                                    class="flex items-center gap-1 text-sm font-medium text-gray-700 transition hover:text-blue-600"
                                >
                                    <span>{{ $item->title }}</span>
                                    <svg class="h-3.5 w-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>
                                <div
                                    x-show="open"
                                    x-transition
                                    class="absolute left-0 mt-2 w-48 rounded-xl border border-gray-200 bg-white p-2 shadow-lg z-50"
                                    style="display: none;"
                                >
                                    @foreach ($item->children as $child)
                                        <a
                                            href="{{ $child->url }}"
                                            target="{{ $child->target }}"
                                            class="block rounded-lg px-3 py-2 text-xs font-medium text-gray-700 hover:bg-gray-50 hover:text-blue-600 transition"
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
                                class="text-sm font-medium text-gray-700 transition hover:text-blue-600"
                            >
                                {{ $item->title }}
                            </a>
                        @endif
                    @endforeach
                @else
                    <a href="{{ route('storefront.home') }}" class="text-sm font-medium text-gray-700 transition hover:text-blue-600">Home</a>
                    <a href="{{ route('storefront.products.index') }}" class="text-sm font-medium text-gray-700 transition hover:text-blue-600">Products</a>
                    <a href="{{ route('storefront.home') }}#categories" class="text-sm font-medium text-gray-700 transition hover:text-blue-600">Categories</a>
                @endif

                @auth
                    <a
                        href="{{ route('storefront.orders.index') }}"
                        class="text-sm font-medium text-gray-700 hover:text-blue-600"
                    >
                        My Orders
                    </a>
                @endauth
            </nav>


            {{-- Actions --}}
            <div class="flex items-center gap-4">

                {{-- Database-backed Search Form --}}
                <form action="{{ route('storefront.products.index') }}" method="GET" class="relative hidden sm:block">
                    <input
                        type="text"
                        name="q"
                        placeholder="Search products..."
                        value="{{ request('q', request('search')) }}"
                        class="w-48 lg:w-64 rounded-full border border-gray-300 bg-gray-50/70 py-1.5 pl-9 pr-3 text-xs outline-none transition focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-100"
                    >
                    <button type="submit" class="absolute left-2.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600" aria-label="Submit search">
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

                {{-- Cart --}}
                <a
                    href="{{ route('storefront.cart') }}"
                    class="relative flex items-center justify-center p-2 text-gray-600 transition hover:text-blue-600"
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
                    class="text-gray-700 md:hidden p-2"
                    aria-label="Toggle menu"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-6 w-6"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                        stroke-width="1.8"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M4 6h16M4 12h16M4 18h16"
                        />
                    </svg>
                </button>

            </div>

        </div>

        {{-- Mobile Drawer Menu --}}
        <div x-show="mobileOpen" x-collapse class="border-t border-gray-200 py-4 md:hidden">
            <form action="{{ route('storefront.products.index') }}" method="GET" class="mb-4">
                <input
                    type="text"
                    name="q"
                    placeholder="Search products..."
                    value="{{ request('q', request('search')) }}"
                    class="w-full rounded-xl border border-gray-300 bg-gray-50 px-4 py-2 text-xs outline-none focus:border-blue-500 focus:bg-white"
                >
            </form>

            <nav class="flex flex-col gap-2 text-sm font-medium text-gray-700">
                @if ($headerItems->isNotEmpty())
                    @foreach ($headerItems as $item)
                        @if ($item->children->isNotEmpty())
                            <div x-data="{ expanded: false }" class="py-1">
                                <button
                                    type="button"
                                    @click="expanded = !expanded"
                                    class="flex w-full items-center justify-between py-1 text-left hover:text-blue-600"
                                >
                                    <span>{{ $item->title }}</span>
                                    <svg class="h-4 w-4 transform transition-transform" :class="{ 'rotate-180': expanded }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>
                                <div x-show="expanded" x-collapse class="pl-3 pt-1 space-y-1 border-l-2 border-gray-100 mt-1">
                                    @foreach ($item->children as $child)
                                        <a
                                            href="{{ $child->url }}"
                                            target="{{ $child->target }}"
                                            class="block py-1 text-xs text-gray-600 hover:text-blue-600"
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
                                class="py-1 hover:text-blue-600"
                            >
                                {{ $item->title }}
                            </a>
                        @endif
                    @endforeach
                @else
                    <a href="{{ route('storefront.home') }}" class="py-1 hover:text-blue-600">Home</a>
                    <a href="{{ route('storefront.products.index') }}" class="py-1 hover:text-blue-600">Products</a>
                    <a href="{{ route('storefront.home') }}#categories" class="py-1 hover:text-blue-600">Categories</a>
                @endif

                @auth
                    <a href="{{ route('storefront.orders.index') }}" class="py-1 hover:text-blue-600 border-t border-gray-100 pt-2">My Orders</a>
                @endauth
            </nav>
        </div>

    </div>

</header>