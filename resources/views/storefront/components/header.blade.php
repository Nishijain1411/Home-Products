<header class="sticky top-0 z-50 border-b border-gray-200 bg-white/95 backdrop-blur">

    <div class="mx-auto max-w-7xl px-6 lg:px-8">

        <div class="flex h-20 items-center justify-between">

            {{-- Logo --}}
            <a href="{{ route('storefront.home') }}" class="flex items-center gap-2">
                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-blue-600 text-lg font-bold text-white">
                    C
                </div>

                <span class="text-xl font-bold tracking-tight text-gray-900">
                    CustomCommerce
                </span>
            </a>


            {{-- Desktop Navigation --}}
            <nav class="hidden items-center gap-8 md:flex">

                <a
                    href="{{ route('storefront.home') }}"
                    class="text-sm font-medium text-gray-700 transition hover:text-blue-600"
                >
                    Home
                </a>

                <a
                    href="{{ route('storefront.shop') }}"
                    class="text-sm font-medium text-gray-700 transition hover:text-blue-600"
                >
                    Shop
                </a>

                <a
                    href="#categories"
                    class="text-sm font-medium text-gray-700 transition hover:text-blue-600"
                >
                    Categories
                </a>

                <a
                    href="#"
                    class="text-sm font-medium text-gray-700 transition hover:text-blue-600"
                >
                    About
                </a>

                <a
                    href="#"
                    class="text-sm font-medium text-gray-700 transition hover:text-blue-600"
                >
                    Contact
                </a>

            </nav>


            {{-- Actions --}}
            <div class="flex items-center gap-4">

                {{-- Search --}}
                <button
                    type="button"
                    class="hidden text-gray-600 transition hover:text-blue-600 sm:block"
                    aria-label="Search"
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
                            d="m21 21-4.35-4.35m1.35-5.65a7 7 0 1 1-14 0 7 7 0 0 1 14 0Z"
                        />
                    </svg>
                </button>


                {{-- Account --}}
                <a
                    href="#"
                    class="hidden text-gray-600 transition hover:text-blue-600 sm:block"
                    aria-label="Account"
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
                            d="M15.75 6.75a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.5 20.25a7.5 7.5 0 0 1 15 0"
                        />
                    </svg>
                </a>


                {{-- Cart --}}
                <a
                    href="#"
                    class="relative text-gray-600 transition hover:text-blue-600"
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

                    <span class="absolute -right-2 -top-2 flex h-4 min-w-4 items-center justify-center rounded-full bg-blue-600 px-1 text-[10px] font-semibold text-white">
                        0
                    </span>
                </a>


                {{-- Mobile Menu Button --}}
                <button
                    type="button"
                    class="text-gray-700 md:hidden"
                    aria-label="Open menu"
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

    </div>

</header>