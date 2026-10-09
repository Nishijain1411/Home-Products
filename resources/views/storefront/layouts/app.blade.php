<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>{{ $title ?? 'Custom E-Commerce Store' }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('styles')
</head>

<body class="min-h-screen bg-white text-gray-900 antialiased">

    @include('storefront.components.header')

   <main>
    {{ $slot ?? '' }}

    @yield('content')
    </main>
    
    @include('storefront.components.footer')

    @stack('scripts')

</body>
</html>