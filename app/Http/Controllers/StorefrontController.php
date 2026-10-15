<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\ThemeSetting;

class StorefrontController extends Controller
{
    public function home()
    {
        $theme = ThemeSetting::query()
            ->whereNotNull('published_settings')
            ->latest('id')
            ->first();

        $storefrontTheme = $theme?->published_settings ?? [];
        $sections = $storefrontTheme['sections'] ?? [];

        // Categories selection
        $selectedCategoryIds = array_filter($sections['categories']['category_ids'] ?? []);
        if (! empty($selectedCategoryIds)) {
            $categories = Category::whereIn('id', $selectedCategoryIds)->get();
        } else {
            $categories = Category::all();
        }

        // Products selection
        $productsCount = (int) ($sections['products']['count'] ?? 8);
        $productsLimit = $productsCount > 0 ? $productsCount : 8;

        $selectedProductIds = array_filter($sections['products']['product_ids'] ?? []);
        if (! empty($selectedProductIds)) {
            $products = Product::with('images')
                ->whereIn('id', $selectedProductIds)
                ->take($productsLimit)
                ->get();
        } else {
            $products = Product::with('images')
                ->latest()
                ->take($productsLimit)
                ->get();
        }

        return view('storefront.home', [
            'categories' => $categories,
            'products' => $products,
            'storefrontTheme' => $storefrontTheme,
        ]);
    }
}
