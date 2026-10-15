<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Models\ThemeSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PageController extends Controller
{
    public function show(string $slug, Request $request)
    {
        $page = Page::where('slug', $slug)->firstOrFail();

        $isAdmin = Auth::check() && (Auth::user()->hasRole('Admin') || Auth::user()->hasRole('Super Admin'));

        if (! $page->is_published && ! $isAdmin) {
            abort(404);
        }

        $theme = ThemeSetting::query()
            ->whereNotNull('published_settings')
            ->latest('id')
            ->first();

        $storefrontTheme = $theme?->published_settings ?? [];

        return view('storefront.pages.show', [
            'page' => $page,
            'isDraftPreview' => ! $page->is_published,
            'storefrontTheme' => $storefrontTheme,
            'title' => $page->getSeoTitle(),
            'metaDescription' => $page->getSeoDescription(),
            'canonicalUrl' => $page->getCanonicalUrl(),
            'isIndexed' => (bool) $page->is_indexed,
        ]);
    }
}
