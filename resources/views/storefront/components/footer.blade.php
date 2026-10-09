<footer class="border-t border-gray-200 bg-gray-950 text-gray-300">

    <div class="mx-auto max-w-7xl px-6 py-14 lg:px-8">

        <div class="grid gap-10 md:grid-cols-4">

            {{-- Brand --}}
            <div class="md:col-span-2">

                <a
                    href="{{ route('storefront.home') }}"
                    class="flex items-center gap-2"
                >
                    <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-blue-600 text-lg font-bold text-white">
                        C
                    </div>

                    <span class="text-xl font-bold text-white">
                        CustomCommerce
                    </span>
                </a>

                <p class="mt-4 max-w-md text-sm leading-6 text-gray-400">
                    A modern e-commerce platform built to provide a simple,
                    secure and seamless shopping experience.
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


            {{-- Support --}}
            <div>

                <h3 class="text-sm font-semibold uppercase tracking-wider text-white">
                    Support
                </h3>

                <ul class="mt-4 space-y-3 text-sm">

                    <li>
                        <a href="#" class="transition hover:text-white">
                            Contact Us
                        </a>
                    </li>

                    <li>
                        <a href="#" class="transition hover:text-white">
                            Privacy Policy
                        </a>
                    </li>

                    <li>
                        <a href="#" class="transition hover:text-white">
                            Terms & Conditions
                        </a>
                    </li>

                </ul>

            </div>

        </div>


        <div class="mt-12 border-t border-gray-800 pt-6">

            <p class="text-sm text-gray-500">
                © {{ date('Y') }} CustomCommerce. All rights reserved.
            </p>

        </div>

    </div>

</footer>