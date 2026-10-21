<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>@yield('title', $title ?? store_name())</title>

    @hasSection('meta_description')
        <meta name="description" content="@yield('meta_description')">
    @elseif(!empty($metaDescription))
        <meta name="description" content="{{ $metaDescription }}">
    @elseif(store_setting('store_tagline'))
        <meta name="description" content="{{ store_setting('store_tagline') }}">
    @endif

    @hasSection('canonical_url')
        <link rel="canonical" href="@yield('canonical_url')">
    @elseif(!empty($canonicalUrl))
        <link rel="canonical" href="{{ $canonicalUrl }}">
    @endif

    @hasSection('robots')
        <meta name="robots" content="@yield('robots')">
    @elseif(isset($isIndexed))
        <meta name="robots" content="{{ $isIndexed ? 'index, follow' : 'noindex, nofollow' }}">
    @else
        <meta name="robots" content="index, follow">
    @endif

    @stack('meta')

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('styles')
</head>
@php
    $themeColors = $storefrontTheme['colors'] ?? [];
    $themeLayout = $storefrontTheme['layout'] ?? [];
    $announcement = $storefrontTheme['announcement'] ?? [];
    $showAnnouncement = (bool) ($announcement['visible'] ?? false);
    $announcementText = $announcement['text'] ?? '';
    $announcementLink = $announcement['link'] ?? '';
    $announcementBg = $announcement['bg_color'] ?? ($themeColors['primary'] ?? '#2563eb');
    $announcementTextColor = $announcement['text_color'] ?? '#ffffff';
    $isMaintenance = (bool) store_setting('maintenance_mode', false);
    $isAdmin = auth()->check() && (auth()->user()->hasRole('Admin') || auth()->user()->hasRole('Super Admin'));
@endphp
<body class="min-h-screen antialiased flex flex-col justify-between"
    style="
        --store-primary: {{ $themeColors['primary'] ?? '#2563eb' }};
        --store-secondary: {{ $themeColors['secondary'] ?? '#111827' }};
        --store-background: {{ $themeColors['background'] ?? '#ffffff' }};
        --store-text: {{ $themeColors['text'] ?? '#111827' }};
        --store-page-width: {{ $themeLayout['page_width'] ?? '1280px' }};
        background-color: var(--store-background);
        color: var(--store-text);
    ">

    @if ($showAnnouncement && !empty($announcementText))
        <div class="relative px-4 py-2.5 text-center text-xs font-semibold tracking-wide transition-all z-40 shadow-xs"
             style="background-color: {{ $announcementBg }}; color: {{ $announcementTextColor }};">
            <div class="inline-flex items-center justify-center gap-2">
                <span class="flex h-2 w-2 relative">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-white opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-white"></span>
                </span>
                @if ($announcementLink)
                    <a href="{{ $announcementLink }}" class="inline-flex items-center gap-1.5 hover:underline font-bold">
                        <span>{{ $announcementText }}</span>
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
                        </svg>
                    </a>
                @else
                    <span>{{ $announcementText }}</span>
                @endif
            </div>
        </div>
    @endif

    @if ($isMaintenance && $isAdmin)
        <div class="bg-amber-500 text-amber-950 px-4 py-2 text-center text-xs font-semibold shadow-sm">
            Maintenance mode is currently ACTIVE. Regular customers will see the maintenance notice.
        </div>
    @endif

    @include('storefront.components.header')

    <main class="flex-1">
        @if ($isMaintenance && ! $isAdmin)
            <div class="mx-auto max-w-xl px-6 py-20 text-center">
                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-amber-100 text-amber-600">
                    <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <h1 class="mt-6 text-2xl font-bold tracking-tight text-gray-900">Under Scheduled Maintenance</h1>
                <p class="mt-3 text-sm text-gray-600">
                    {{ store_setting('maintenance_message') ?: 'We are undergoing scheduled maintenance. Please check back shortly.' }}
                </p>
            </div>
        @else
            {{ $slot ?? '' }}

            @yield('content')
        @endif
    </main>

    @include('storefront.components.footer')

    {{-- Back to Top Floating Button --}}
    <div
        x-data="{ showTop: false }"
        @scroll.window="showTop = (window.pageYOffset > 350)"
        class="fixed bottom-6 right-6 z-40 transition-all duration-300"
        :class="showTop ? 'opacity-100 translate-y-0 pointer-events-auto' : 'opacity-0 translate-y-4 pointer-events-none'"
    >
        <button
            type="button"
            @click="window.scrollTo({ top: 0, behavior: 'smooth' })"
            class="flex h-11 w-11 items-center justify-center rounded-full bg-white/90 text-gray-700 shadow-lg border border-gray-200/80 backdrop-blur-md transition-all duration-200 hover:bg-white hover:text-blue-600 hover:-translate-y-1 hover:shadow-xl active:scale-95"
            aria-label="Scroll to top"
        >
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" />
            </svg>
        </button>
    </div>

    @stack('scripts')

</body>

</html>