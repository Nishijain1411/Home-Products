<?php

namespace Database\Seeders;

use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Page;
use Illuminate\Database\Seeder;

class CmsSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedPages();
        $this->seedMenus();
    }

    protected function seedPages(): void
    {
        $pages = [
            [
                'title' => 'About Us',
                'slug' => 'about-us',
                'template' => 'default',
                'is_published' => true,
                'published_at' => now(),
                'meta_title' => 'About Us — Custom Commerce',
                'meta_description' => 'Learn more about our story, craftsmanship, mission, and dedication to offering premier products.',
                'content' => '<h2>Our Story</h2>
<p>Welcome to <strong>Custom Commerce</strong>, your premier destination for high-quality lifestyle, apparel, and home essentials. Founded with a vision to redefine modern online shopping, we bridge the gap between quality craftsmanship, accessible pricing, and outstanding customer care.</p>
<h3>Our Mission</h3>
<p>We believe everyday essentials should be reliable, well-crafted, and sustainably produced. We partner directly with trusted artisans and manufacturers worldwide to ensure every product in our catalog meets the highest benchmarks of quality and longevity.</p>
<h3>Why Choose Us?</h3>
<ul>
    <li><strong>Curated Quality:</strong> Every item is meticulously inspected before it reaches your hands.</li>
    <li><strong>Transparent Value:</strong> Direct-to-consumer pricing without retail markups.</li>
    <li><strong>Seamless Shopping:</strong> Instant UPI, secure card payments, and fast door-to-door delivery.</li>
    <li><strong>Dedicated Support:</strong> Real human customer support whenever you need assistance.</li>
</ul>',
            ],
            [
                'title' => 'Contact Us',
                'slug' => 'contact-us',
                'template' => 'contact',
                'is_published' => true,
                'published_at' => now(),
                'meta_title' => 'Contact Us — Customer Support',
                'meta_description' => 'Get in touch with our team for orders, inquiries, partnerships, or support.',
                'content' => '<h2>We would love to hear from you</h2>
<p>Have a question about an order, need sizing help, or interested in wholesale partnerships? Our customer care specialists are available Monday through Saturday to help you with anything you need.</p>
<div class="contact-details">
    <h3>Customer Support</h3>
    <p><strong>Email:</strong> support@customcommerce.test</p>
    <p><strong>Phone:</strong> +91 9876543210</p>
    <p><strong>Hours:</strong> Mon – Sat, 9:00 AM – 7:00 PM IST</p>
    <h3>Headquarters & Returns Warehouse</h3>
    <p>123 Commercial Boulevard, Suite 400<br>Mumbai, Maharashtra, 400001, India</p>
</div>',
            ],
            [
                'title' => 'Frequently Asked Questions',
                'slug' => 'faq',
                'template' => 'faq',
                'is_published' => true,
                'published_at' => now(),
                'meta_title' => 'FAQ — Help & Frequently Asked Questions',
                'meta_description' => 'Find quick answers regarding shipping times, order tracking, returns, and payment methods.',
                'content' => '<h2>Frequently Asked Questions</h2>
<div class="faq-group">
    <h3>1. How long does shipping take?</h3>
    <p>Orders are dispatched within 24 to 48 hours. Standard domestic delivery typically takes between 2 to 5 business days depending on your location.</p>
    <h3>2. What payment methods do you accept?</h3>
    <p>We accept all major credit and debit cards (Visa, MasterCard, RuPay), instant UPI payments via Google Pay, PhonePe, and Paytm, as well as Cash on Delivery (COD) for eligible pincodes.</p>
    <h3>3. Can I track my order?</h3>
    <p>Yes. As soon as your order is placed, you receive an order confirmation with a unique Order Number. Registered customers can monitor their shipments live under "My Orders".</p>
    <h3>4. What is your return and exchange policy?</h3>
    <p>We offer a hassle-free 7-day return policy for unused, unwashed items in their original packaging with tags intact.</p>
    <h3>5. How do promotional discount codes work?</h3>
    <p>You can apply any active promo code during checkout or directly in your shopping cart. Discounts are calculated instantly off your subtotal.</p>
</div>',
            ],
            [
                'title' => 'Privacy Policy',
                'slug' => 'privacy-policy',
                'template' => 'legal',
                'is_published' => true,
                'published_at' => now(),
                'meta_title' => 'Privacy Policy — Custom Commerce',
                'meta_description' => 'Learn how we protect your personal information, manage security, and handle cookies.',
                'content' => '<h2>Privacy Policy</h2>
<p><em>Last updated: October 2026</em></p>
<p>At <strong>Custom Commerce</strong>, your privacy and data security are paramount. This Privacy Policy outlines how we collect, store, and protect your information when you browse our storefront or place orders.</p>
<h3>1. Information We Collect</h3>
<p>When you place an order or create an account, we collect necessary contact information (name, email address, phone number, shipping and billing addresses). Payment card details are processed directly through encrypted, PCI-DSS compliant payment gateways and are never stored on our servers.</p>
<h3>2. How We Use Your Data</h3>
<ul>
    <li>To process, fulfill, and deliver your orders.</li>
    <li>To send tracking updates and order confirmations.</li>
    <li>To provide personalized customer support and resolve inquiries.</li>
    <li>To prevent fraudulent transactions and maintain platform security.</li>
</ul>
<h3>3. Data Security</h3>
<p>We implement 256-bit SSL encryption across all store communications to protect your sensitive data against unauthorized access.</p>',
            ],
            [
                'title' => 'Terms & Conditions',
                'slug' => 'terms-conditions',
                'template' => 'legal',
                'is_published' => true,
                'published_at' => now(),
                'meta_title' => 'Terms & Conditions — Custom Commerce',
                'meta_description' => 'Read our terms of service, purchasing agreements, and warranty information.',
                'content' => '<h2>Terms & Conditions</h2>
<p><em>Last updated: October 2026</em></p>
<p>By accessing or purchasing from <strong>Custom Commerce</strong>, you agree to be bound by the following terms and conditions.</p>
<h3>1. Orders & Pricing</h3>
<p>All prices listed on our website are in the specified store currency. We reserve the right to modify pricing, promotions, and product availability without prior notice. In the event an item is listed at an incorrect price due to a typographical error, we reserve the right to cancel or refund the affected order.</p>
<h3>2. Shipping & Delivery</h3>
<p>Delivery timeframes are estimates and may vary based on carrier logistics, weather conditions, or customs clearance. Risk of loss passes to the buyer upon handoff to the courier service.</p>
<h3>3. Returns & Refunds</h3>
<p>Returned items must be unworn, undamaged, and accompanied by proof of purchase. Refunds will be issued to the original payment method within 5 to 7 business days following inspection.</p>',
            ],
        ];

        foreach ($pages as $pageData) {
            Page::updateOrCreate(['slug' => $pageData['slug']], $pageData);
        }
    }

    protected function seedMenus(): void
    {
        // 1. Header Menu
        $headerMenu = Menu::updateOrCreate(
            ['location' => 'header'],
            ['name' => 'Main Header Navigation', 'is_active' => true]
        );

        $headerItems = [
            ['title' => 'Home', 'url' => '/storefront/home', 'sort_order' => 1],
            ['title' => 'Products', 'url' => '/storefront/shop', 'sort_order' => 2],
            ['title' => 'Categories', 'url' => '/storefront/home#categories', 'sort_order' => 3],
            ['title' => 'About Us', 'url' => '/page/about-us', 'sort_order' => 4],
            ['title' => 'Contact', 'url' => '/page/contact-us', 'sort_order' => 5],
        ];

        foreach ($headerItems as $item) {
            MenuItem::updateOrCreate(
                ['menu_id' => $headerMenu->id, 'url' => $item['url']],
                [
                    'title' => $item['title'],
                    'target' => '_self',
                    'sort_order' => $item['sort_order'],
                    'is_active' => true,
                ]
            );
        }

        // 2. Footer Store Menu
        $footerStoreMenu = Menu::updateOrCreate(
            ['location' => 'footer_store'],
            ['name' => 'Footer Store Links', 'is_active' => true]
        );

        $footerStoreItems = [
            ['title' => 'Home', 'url' => '/storefront/home', 'sort_order' => 1],
            ['title' => 'All Products', 'url' => '/storefront/shop', 'sort_order' => 2],
            ['title' => 'Shop by Category', 'url' => '/storefront/home#categories', 'sort_order' => 3],
            ['title' => 'About Us', 'url' => '/page/about-us', 'sort_order' => 4],
        ];

        foreach ($footerStoreItems as $item) {
            MenuItem::updateOrCreate(
                ['menu_id' => $footerStoreMenu->id, 'url' => $item['url']],
                [
                    'title' => $item['title'],
                    'target' => '_self',
                    'sort_order' => $item['sort_order'],
                    'is_active' => true,
                ]
            );
        }

        // 3. Footer Legal & Support Menu
        $footerLegalMenu = Menu::updateOrCreate(
            ['location' => 'footer_legal'],
            ['name' => 'Footer Support & Legal Links', 'is_active' => true]
        );

        $footerLegalItems = [
            ['title' => 'Help & FAQ', 'url' => '/page/faq', 'sort_order' => 1],
            ['title' => 'Contact Us', 'url' => '/page/contact-us', 'sort_order' => 2],
            ['title' => 'Privacy Policy', 'url' => '/page/privacy-policy', 'sort_order' => 3],
            ['title' => 'Terms & Conditions', 'url' => '/page/terms-conditions', 'sort_order' => 4],
        ];

        foreach ($footerLegalItems as $item) {
            MenuItem::updateOrCreate(
                ['menu_id' => $footerLegalMenu->id, 'url' => $item['url']],
                [
                    'title' => $item['title'],
                    'target' => '_self',
                    'sort_order' => $item['sort_order'],
                    'is_active' => true,
                ]
            );
        }
    }
}
