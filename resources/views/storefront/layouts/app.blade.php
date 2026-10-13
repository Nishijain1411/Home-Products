<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>{{ $title ?? 'Custom E-Commerce Store' }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('styles')
</head>
@php
    $themeColors = $storefrontTheme['colors'] ?? [];
    $themeLayout = $storefrontTheme['layout'] ?? [];
@endphp
<body class="min-h-screen antialiased"
    style="
        --store-primary: {{ $themeColors['primary'] ?? '#2563eb' }};
        --store-secondary: {{ $themeColors['secondary'] ?? '#111827' }};
        --store-background: {{ $themeColors['background'] ?? '#ffffff' }};
        --store-text: {{ $themeColors['text'] ?? '#111827' }};
        --store-page-width: {{ $themeLayout['page_width'] ?? '1280px' }};
        background-color: var(--store-background);
        color: var(--store-text);
    ">

    @include('storefront.components.header')

   <main>
    {{ $slot ?? '' }}

    @yield('content')
    </main>
    
    @include('storefront.components.footer')

    @stack('scripts')

</body>

</html>