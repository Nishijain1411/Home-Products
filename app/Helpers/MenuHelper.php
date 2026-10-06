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