<?php

namespace App\Http\Controllers;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class StorefrontController extends Controller
{
    public function home()
    {
        $categories = Category::query()
            ->whereNull('parent_id')
            ->orderBy('sort_order')
            ->get();

        $products = Product::query()
            ->where('is_active', true)
            ->where('is_featured', true)
            ->with([
                'images' => function ($query) {
                    $query->orderBy('position');
                }
            ])
            ->orderBy('sort_order')
            ->get();

        return view('storefront.home', [
            'categories' => $categories,
            'products' => $products,
        ]);
    }
    
}
