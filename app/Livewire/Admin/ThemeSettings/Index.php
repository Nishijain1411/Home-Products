<?php

namespace App\Livewire\Admin\ThemeSettings;

use App\Models\Category;
use App\Models\Product;
use App\Models\ThemeSetting;
use Livewire\Component;

class Index extends Component
{
    public ?int $themeId = null;

    public string $themeName = 'Default Theme';

    public string $primaryColor = '#2563eb';

    public string $secondaryColor = '#111827';

    public string $backgroundColor = '#ffffff';

    public string $textColor = '#111827';

    public string $pageWidth = '1280px';

    // Promotional Banner / Announcement Bar
    public bool $showAnnouncement = false;

    public string $announcementText = 'Special Offer: Free shipping on all orders over ₹999!';

    public string $announcementLink = '';

    public string $announcementBg = '#2563eb';

    public string $announcementTextColor = '#ffffff';

    // Section Ordering
    public array $sectionsOrder = ['hero', 'categories', 'products', 'cta'];

    // Hero Section
    public bool $showHero = true;

    public string $heroEyebrow = 'New Collection';

    public string $heroHeading = 'Everything you need,';

    public string $heroHighlight = 'all in one place.';

    public string $heroDescription = 'Discover our latest products, collections and exclusive offers.';

    public string $heroPrimaryButtonText = 'Shop Now';

    public string $heroPrimaryButtonLink = '#products';

    public string $heroSecondaryButtonText = 'Explore Categories';

    public string $heroSecondaryButtonLink = '#categories';

    // Categories Section
    public bool $showCategories = true;

    public string $categoryHeading = 'Shop by Category';

    public string $categoryDescription = 'Explore our product categories.';

    public int $categoryColumns = 4;

    public array $selectedCategoryIds = [];

    // Featured Products Section
    public bool $showProducts = true;

    public string $productsHeading = 'Featured Products';

    public string $productsDescription = 'Discover some of our latest products.';

    public string $productsViewAllText = 'View All';

    public string $productsViewAllLink = '/storefront/shop';

    public int $productsColumns = 4;

    public int $productsCount = 8;

    public array $selectedProductIds = [];

    // Call to Action (CTA) Section
    public bool $showCta = true;

    public string $ctaHeading = "Discover something you'll love.";

    public string $ctaDescription = 'Browse our collection and find the perfect products for you.';

    public string $ctaButtonText = 'Start Shopping';

    public string $ctaButtonLink = '#products';

    public function mount(): void
    {
        $theme = ThemeSetting::query()
            ->latest('id')
            ->first();

        if (! $theme) {
            $theme = ThemeSetting::create([
                'name' => 'Default Theme',
                'settings' => [],
                'published_settings' => [],
            ]);
        }

        $this->themeId = $theme->id;
        $this->themeName = $theme->name ?? 'Default Theme';

        $settings = $theme->settings ?? [];
        $colors = $settings['colors'] ?? [];
        $layout = $settings['layout'] ?? [];
        $sections = $settings['sections'] ?? [];

        $this->primaryColor = $colors['primary'] ?? '#2563eb';
        $this->secondaryColor = $colors['secondary'] ?? '#111827';
        $this->backgroundColor = $colors['background'] ?? '#ffffff';
        $this->textColor = $colors['text'] ?? '#111827';
        $this->pageWidth = $layout['page_width'] ?? '1280px';

        // Announcement Bar
        $announcement = $settings['announcement'] ?? [];
        $this->showAnnouncement = (bool) ($announcement['visible'] ?? false);
        $this->announcementText = $announcement['text'] ?? 'Special Offer: Free shipping on all orders over ₹999!';
        $this->announcementLink = $announcement['link'] ?? '';
        $this->announcementBg = $announcement['bg_color'] ?? '#2563eb';
        $this->announcementTextColor = $announcement['text_color'] ?? '#ffffff';

        // Sections order
        $validSections = ['hero', 'categories', 'products', 'cta'];
        $rawOrder = $settings['sections_order'] ?? $validSections;
        $this->sectionsOrder = array_values(array_unique(array_merge(
            array_intersect($rawOrder, $validSections),
            $validSections
        )));

        // Hero
        $hero = $sections['hero'] ?? [];
        $this->showHero = (bool) ($hero['visible'] ?? true);
        $this->heroEyebrow = $hero['eyebrow'] ?? 'New Collection';
        $this->heroHeading = $hero['heading'] ?? 'Everything you need,';
        $this->heroHighlight = $hero['highlight'] ?? 'all in one place.';
        $this->heroDescription = $hero['description'] ?? 'Discover our latest products, collections and exclusive offers.';
        $this->heroPrimaryButtonText = $hero['primary_button_text'] ?? 'Shop Now';
        $this->heroPrimaryButtonLink = $hero['primary_button_link'] ?? '#products';
        $this->heroSecondaryButtonText = $hero['secondary_button_text'] ?? 'Explore Categories';
        $this->heroSecondaryButtonLink = $hero['secondary_button_link'] ?? '#categories';

        // Categories
        $categories = $sections['categories'] ?? [];
        $this->showCategories = (bool) ($categories['visible'] ?? true);
        $this->categoryHeading = $categories['heading'] ?? 'Shop by Category';
        $this->categoryDescription = $categories['description'] ?? 'Explore our product categories.';
        $this->categoryColumns = (int) ($categories['columns'] ?? 4);
        $this->selectedCategoryIds = array_map('intval', $categories['category_ids'] ?? []);

        // Featured Products
        $products = $sections['products'] ?? [];
        $this->showProducts = (bool) ($products['visible'] ?? true);
        $this->productsHeading = $products['heading'] ?? 'Featured Products';
        $this->productsDescription = $products['description'] ?? 'Discover some of our latest products.';
        $this->productsViewAllText = $products['view_all_text'] ?? 'View All';
        $this->productsViewAllLink = $products['view_all_link'] ?? '/storefront/shop';
        $this->productsColumns = (int) ($products['columns'] ?? 4);
        $this->productsCount = (int) ($products['count'] ?? 8);
        $this->selectedProductIds = array_map('intval', $products['product_ids'] ?? []);

        // CTA
        $cta = $sections['cta'] ?? [];
        $this->showCta = (bool) ($cta['visible'] ?? true);
        $this->ctaHeading = $cta['heading'] ?? "Discover something you'll love.";
        $this->ctaDescription = $cta['description'] ?? 'Browse our collection and find the perfect products for you.';
        $this->ctaButtonText = $cta['button_text'] ?? 'Start Shopping';
        $this->ctaButtonLink = $cta['button_link'] ?? '#products';
    }

    public function moveSectionUp(string $sectionKey): void
    {
        $index = array_search($sectionKey, $this->sectionsOrder, true);
        if ($index !== false && $index > 0) {
            $prev = $this->sectionsOrder[$index - 1];
            $this->sectionsOrder[$index - 1] = $sectionKey;
            $this->sectionsOrder[$index] = $prev;
        }
    }

    public function moveSectionDown(string $sectionKey): void
    {
        $index = array_search($sectionKey, $this->sectionsOrder, true);
        if ($index !== false && $index < count($this->sectionsOrder) - 1) {
            $next = $this->sectionsOrder[$index + 1];
            $this->sectionsOrder[$index + 1] = $sectionKey;
            $this->sectionsOrder[$index] = $next;
        }
    }

    protected function rules(): array
    {
        return [
            'themeName' => ['required', 'string', 'max:100'],
            'primaryColor' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'secondaryColor' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'backgroundColor' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'textColor' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'pageWidth' => ['required', 'in:1024px,1152px,1280px,1440px,100%'],

            'showAnnouncement' => ['boolean'],
            'announcementText' => ['nullable', 'string', 'max:255'],
            'announcementLink' => ['nullable', 'string', 'max:255'],
            'announcementBg' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'announcementTextColor' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],

            'sectionsOrder' => ['required', 'array'],

            'showHero' => ['boolean'],
            'heroEyebrow' => ['required', 'string', 'max:100'],
            'heroHeading' => ['required', 'string', 'max:200'],
            'heroHighlight' => ['required', 'string', 'max:200'],
            'heroDescription' => ['required', 'string', 'max:1000'],
            'heroPrimaryButtonText' => ['required', 'string', 'max:60'],
            'heroPrimaryButtonLink' => ['required', 'string', 'max:255'],
            'heroSecondaryButtonText' => ['required', 'string', 'max:60'],
            'heroSecondaryButtonLink' => ['required', 'string', 'max:255'],

            'showCategories' => ['boolean'],
            'categoryHeading' => ['required', 'string', 'max:200'],
            'categoryDescription' => ['required', 'string', 'max:1000'],
            'categoryColumns' => ['required', 'integer', 'in:2,3,4'],
            'selectedCategoryIds' => ['array'],

            'showProducts' => ['boolean'],
            'productsHeading' => ['required', 'string', 'max:200'],
            'productsDescription' => ['required', 'string', 'max:1000'],
            'productsViewAllText' => ['required', 'string', 'max:60'],
            'productsViewAllLink' => ['required', 'string', 'max:255'],
            'productsColumns' => ['required', 'integer', 'in:2,3,4'],
            'productsCount' => ['required', 'integer', 'in:4,8,12,16'],
            'selectedProductIds' => ['array'],

            'showCta' => ['boolean'],
            'ctaHeading' => ['required', 'string', 'max:200'],
            'ctaDescription' => ['required', 'string', 'max:1000'],
            'ctaButtonText' => ['required', 'string', 'max:60'],
            'ctaButtonLink' => ['required', 'string', 'max:255'],
        ];
    }

    public function buildSettingsArray(): array
    {
        return [
            'colors' => [
                'primary' => $this->primaryColor,
                'secondary' => $this->secondaryColor,
                'background' => $this->backgroundColor,
                'text' => $this->textColor,
            ],
            'layout' => [
                'page_width' => $this->pageWidth,
            ],
            'announcement' => [
                'visible' => (bool) $this->showAnnouncement,
                'text' => $this->announcementText,
                'link' => $this->announcementLink,
                'bg_color' => $this->announcementBg,
                'text_color' => $this->announcementTextColor,
            ],
            'sections_order' => $this->sectionsOrder,
            'sections' => [
                'hero' => [
                    'visible' => (bool) $this->showHero,
                    'eyebrow' => $this->heroEyebrow,
                    'heading' => $this->heroHeading,
                    'highlight' => $this->heroHighlight,
                    'description' => $this->heroDescription,
                    'primary_button_text' => $this->heroPrimaryButtonText,
                    'primary_button_link' => $this->heroPrimaryButtonLink,
                    'secondary_button_text' => $this->heroSecondaryButtonText,
                    'secondary_button_link' => $this->heroSecondaryButtonLink,
                ],
                'categories' => [
                    'visible' => (bool) $this->showCategories,
                    'heading' => $this->categoryHeading,
                    'description' => $this->categoryDescription,
                    'columns' => (int) $this->categoryColumns,
                    'category_ids' => array_values(array_map('intval', $this->selectedCategoryIds)),
                ],
                'products' => [
                    'visible' => (bool) $this->showProducts,
                    'heading' => $this->productsHeading,
                    'description' => $this->productsDescription,
                    'view_all_text' => $this->productsViewAllText,
                    'view_all_link' => $this->productsViewAllLink,
                    'columns' => (int) $this->productsColumns,
                    'count' => (int) $this->productsCount,
                    'product_ids' => array_values(array_map('intval', $this->selectedProductIds)),
                ],
                'cta' => [
                    'visible' => (bool) $this->showCta,
                    'heading' => $this->ctaHeading,
                    'description' => $this->ctaDescription,
                    'button_text' => $this->ctaButtonText,
                    'button_link' => $this->ctaButtonLink,
                ],
            ],
        ];
    }

    public function saveDraft(): void
    {
        $this->validate();

        $theme = ThemeSetting::findOrFail($this->themeId);

        $settings = array_replace_recursive(
            $theme->settings ?? [],
            $this->buildSettingsArray()
        );

        $theme->update([
            'name' => $this->themeName,
            'settings' => $settings,
        ]);

        session()->flash('success', 'Theme draft saved successfully.');
    }

    public function publishTheme(): void
    {
        $this->validate();

        $theme = ThemeSetting::findOrFail($this->themeId);

        $settings = array_replace_recursive(
            $theme->settings ?? [],
            $this->buildSettingsArray()
        );

        $theme->update([
            'name' => $this->themeName,
            'settings' => $settings,
            'published_settings' => $settings,
            'published_at' => now(),
        ]);

        session()->flash('success', 'Theme published successfully.');
    }

    public function render()
    {
        $allCategories = Category::orderBy('name')->get();
        $allProducts = Product::select('id', 'name', 'price')->orderBy('name')->get();

        return view('livewire.admin.theme-settings.index', [
            'allCategories' => $allCategories,
            'allProducts' => $allProducts,
        ])->layout('layouts.app');
    }
}
