<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\ThemeSetting;

class StorefrontController extends Controller
{
    public function home()
    {
        $categories = Category::all();

        $theme = ThemeSetting::query()
            ->whereNotNull('published_settings')
            ->latest('id')
            ->first();

        $storefrontTheme = $theme?->published_settings ?? [];
        $productsCount = (int) ($storefrontTheme['sections']['products']['count'] ?? 8);

        $products = Product::with('images')
            ->latest()
            ->take($productsCount > 0 ? $productsCount : 8)
            ->get();

        return view('storefront.home', [
            'categories' => $categories,
            'products' => $products,
            'storefrontTheme' => $storefrontTheme,
        ]);
    }
}
