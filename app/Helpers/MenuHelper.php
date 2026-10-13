<?php

namespace App\Helpers;

class MenuHelper
{
    public static function getMenuGroups(): array
    {
        return [
            [
                'title' => 'Main',
                'items' => [
                    [
                        'name' => 'Dashboard',
                        'path' => '/admin/dashboard',
                        'icon' => 'dashboard',
                    ],
                ],
            ],

            [
                'title' => 'Catalog',
                'items' => [
                    [
                        'name' => 'Categories',
                        'path' => '/admin/categories',
                        'icon' => 'category',
                    ],
                    [
                        'name' => 'Products',
                        'path' => '/admin/products',
                        'icon' => 'shopping-bag',
                    ],
                    [
                        'name' => 'Inventory',
                        'path' => '/admin/inventory',
                        'icon' => 'inventory',
                    ],
                ],
            ],

            [
                'title' => 'Sales',
                'items' => [
                    [
                        'name' => 'Orders',
                        'path' => '/admin/orders',
                        'icon' => 'shopping-cart',
                    ],
                    [
                        'name' => 'Customers',
                        'path' => '/admin/customers',
                        'icon' => 'user-group',
                    ],
                ],
            ],

            [
                'title' => 'Storefront',
                'items' => [
                    [
                        'name' => 'Storefront Home',
                        'path' => '/storefront/home',
                        'icon' => 'store',
                    ],
                    [
                        'name' => 'Theme Settings',
                        'path' => '/admin/theme-settings',
                        'icon' => 'settings',
                    ],
                ],
            ],
        ];
    }

    public static function getIconSvg(string $icon): string
    {
        return match ($icon) {

            'dashboard' => '
                <svg xmlns="http://www.w3.org/2000/svg"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke-width="1.5"
                    stroke="currentColor"
                    class="h-5 w-5">
                    <path stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M3.75 3.75h6.5v6.5h-6.5v-6.5zm10 0h6.5v6.5h-6.5v-6.5zm-10 10h6.5v6.5h-6.5v-6.5zm10 0h6.5v6.5h-6.5v-6.5z"/>
                </svg>',

            'category' => '
                <svg xmlns="http://www.w3.org/2000/svg"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke-width="1.5"
                    stroke="currentColor"
                    class="h-5 w-5">
                    <path stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M3.75 6.75h16.5M3.75 12h16.5M3.75 17.25h16.5"/>
                </svg>',

            'shopping-bag' => '
                <svg xmlns="http://www.w3.org/2000/svg"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke-width="1.5"
                    stroke="currentColor"
                    class="h-5 w-5">
                    <path stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m-3.75 0h15l-.75 9.75H5.25L4.5 10.5z"/>
                </svg>',

            'inventory' => '
                <svg xmlns="http://www.w3.org/2000/svg"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke-width="1.5"
                    stroke="currentColor"
                    class="h-5 w-5">
                    <path stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M3.75 6.75L12 2.25l8.25 4.5M3.75 6.75L12 11.25m-8.25-4.5v10.5L12 21.75m0-10.5l8.25-4.5m-8.25 4.5v10.5m8.25-15v10.5L12 21.75"/>
                </svg>',

            'shopping-cart' => '
                <svg xmlns="http://www.w3.org/2000/svg"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke-width="1.5"
                    stroke="currentColor"
                    class="h-5 w-5">
                    <path stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M2.25 3h1.386a1.5 1.5 0 011.447 1.107L5.91 6.75h13.84a1.5 1.5 0 011.474 1.79l-1.125 5.25a1.5 1.5 0 01-1.474 1.185H8.25a1.5 1.5 0 01-1.474-1.185L5.91 6.75m0 0L5.25 4.5M9 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm9.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z"/>
                </svg>',
            'user-group' => '
                    <svg xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke-width="1.5"
                        stroke="currentColor"
                        class="h-5 w-5">
                        <path stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.125-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-.113-.01-.224-.03-.333m.03.336A7.5 7.5 0 0 1 9.75 21a7.5 7.5 0 0 1-5.25-1.872m10.5 0a24.255 24.255 0 0 1-10.5 0m10.5 0v-.003c0-.113-.01-.224-.03-.333m-10.44.336a4.125 4.125 0 0 1 7.533-2.493M9.75 21a7.5 7.5 0 0 1-5.25-1.872M9.75 21a7.5 7.5 0 0 0 5.25-1.872M12 15.75a3.75 3.75 0 1 0 0-7.5 3.75 3.75 0 0 0 0 7.5Zm6.75-6a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 0 4.5 0 2.25 2.25 0 0 0-4.5 0Z"/>
                    </svg>',

            'store' => '
                    <svg xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke-width="1.5"
                        stroke="currentColor"
                        class="h-5 w-5">
                        <path stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M3 10.5V20h18v-9.5M3 10.5L5.5 4h13L21 10.5M3 10.5a3 3 0 006 0 3 3 0 006 0 3 3 0 006 0"/>
                    </svg>',
            'settings' => '
                    <svg xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke-width="1.5"
                        stroke="currentColor"
                        class="h-5 w-5">
                        <path stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z" />
                        <path stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>',
            default => '
                <svg xmlns="http://www.w3.org/2000/svg"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke-width="1.5"
                    stroke="currentColor"
                    class="h-5 w-5">
                    <circle cx="12" cy="12" r="9"/>
                </svg>',
        };
    }
}
