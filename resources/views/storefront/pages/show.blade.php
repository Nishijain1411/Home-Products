@extends('storefront.layouts.app')

@section('title', $title)
@section('meta_description', $metaDescription)
@section('canonical_url', $canonicalUrl)

@section('content')
<div class="mx-auto px-6 py-12 lg:px-8" style="max-width: var(--store-page-width);">

    {{-- Draft Mode Banner --}}
    @if ($isDraftPreview)
        <div class="mb-8 rounded-xl border border-amber-300 bg-amber-50 p-4 text-sm text-amber-900 shadow-sm flex items-center justify-between">
            <div class="flex items-center gap-2">
                <svg class="h-5 w-5 text-amber-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <span><strong>Preview Mode:</strong> This page is currently a <strong>Draft</strong> and is only visible to store administrators.</span>
            </div>
            <a href="{{ route('admin.pages.edit', $page) }}" class="rounded-lg bg-amber-600 px-3 py-1 text-xs font-semibold text-white hover:bg-amber-700 transition">
                Edit in Admin
            </a>
        </div>
    @endif

    {{-- Breadcrumb --}}
    <nav class="mb-6 flex items-center gap-2 text-xs font-medium text-gray-500">
        <a href="{{ route('storefront.home') }}" class="hover:text-blue-600 transition">Home</a>
        <span>/</span>
        <span class="text-gray-800">{{ $page->title }}</span>
    </nav>

    {{-- Header --}}
    <div class="border-b border-gray-200 pb-8">
        <h1 class="text-3xl font-extrabold tracking-tight text-gray-900 sm:text-4xl">
            {{ $page->title }}
        </h1>
        @if ($page->published_at)
            <p class="mt-2 text-xs text-gray-400">
                Published on {{ format_date($page->published_at) }}
            </p>
        @endif
    </div>

    {{-- Page Body --}}
    <div class="mt-8">
        @if ($page->template === 'contact')
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-10">
                <div class="lg:col-span-7 prose prose-blue max-w-none text-gray-700 leading-relaxed">
                    {!! $page->content !!}
                </div>

                <div class="lg:col-span-5">
                    <div class="rounded-2xl border border-gray-200 bg-gray-50/70 p-6 shadow-sm space-y-5">
                        <h3 class="text-base font-bold text-gray-900 border-b border-gray-200 pb-3">
                            Direct Contact Information
                        </h3>

                        @php
                            $email = store_setting('contact_email');
                            $phone = store_setting('contact_phone');
                            $address = business_address();
                        @endphp

                        @if ($email)
                            <div class="flex items-start gap-3">
                                <div class="rounded-lg bg-blue-100 p-2 text-blue-600 shrink-0">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <div>
                                    <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Email Us</div>
                                    <a href="mailto:{{ $email }}" class="text-sm font-medium text-gray-900 hover:text-blue-600 transition">
                                        {{ $email }}
                                    </a>
                                </div>
                            </div>
                        @endif

                        @if ($phone)
                            <div class="flex items-start gap-3">
                                <div class="rounded-lg bg-emerald-100 p-2 text-emerald-600 shrink-0">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                    </svg>
                                </div>
                                <div>
                                    <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Call or WhatsApp</div>
                                    <a href="tel:{{ $phone }}" class="text-sm font-medium text-gray-900 hover:text-blue-600 transition">
                                        {{ $phone }}
                                    </a>
                                </div>
                            </div>
                        @endif

                        @if ($address)
                            <div class="flex items-start gap-3">
                                <div class="rounded-lg bg-purple-100 p-2 text-purple-600 shrink-0">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                </div>
                                <div>
                                    <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Office Address</div>
                                    <p class="text-sm text-gray-700 whitespace-pre-line mt-0.5">
                                        {{ $address }}
                                    </p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @else
            <div class="max-w-4xl mx-auto rounded-2xl bg-white p-6 sm:p-10 border border-gray-100 shadow-sm">
                <div class="prose prose-blue max-w-none text-gray-700 leading-relaxed space-y-4">
                    {!! $page->content !!}
                </div>
            </div>
        @endif
    </div>

</div>
@endsection
