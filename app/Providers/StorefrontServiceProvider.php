<?php

namespace App\Providers;

use App\Models\ThemeSetting;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class StorefrontServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        View::composer('storefront.*', function ($view) {
            $theme = ThemeSetting::query()
                ->whereNotNull('published_settings')
                ->latest('id')
                ->first();

            $view->with(
                'storefrontTheme',
                $theme?->published_settings ?? []
            );
        });
    }
}
