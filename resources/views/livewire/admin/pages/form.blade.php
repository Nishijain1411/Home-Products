<div class="p-4 md:p-6 max-w-6xl mx-auto space-y-6">

    {{-- Breadcrumb & Header --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between border-b border-gray-200 pb-5">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.pages.index') }}"
                class="flex h-9 w-9 items-center justify-center rounded-lg border border-gray-300 bg-white text-gray-600 shadow-sm transition hover:bg-gray-50 hover:text-gray-900"
                title="Back to Pages">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
            <div>
                <div class="flex items-center gap-2 text-xs font-medium text-gray-500">
                    <a href="{{ route('admin.pages.index') }}" class="hover:text-blue-600 transition">Pages</a>
                    <span>/</span>
                    <span class="text-gray-800">{{ $isEditing ? 'Edit Page' : 'Create Page' }}</span>
                </div>
                <h1 class="text-2xl font-bold tracking-tight text-gray-900 mt-1">
                    {{ $isEditing ? ($title ?: 'Edit Page') : 'Create New Page' }}
                </h1>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('admin.pages.index') }}"
                class="rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 transition">
                Cancel
            </a>

            @if ($isEditing && $slug)
                <a href="{{ route('storefront.pages.show', $slug) }}" target="_blank"
                    class="rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 transition flex items-center gap-1.5">
                    <svg class="h-4 w-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                    </svg>
                    Preview
                </a>
            @endif

            <button type="button" wire:click="save" wire:loading.attr="disabled"
                class="inline-flex items-center justify-center gap-2 rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-medium text-white shadow-sm hover:bg-blue-700 transition disabled:opacity-50">
                <span wire:loading.remove wire:target="save">
                    {{ $isEditing ? 'Update Page' : 'Save Page' }}
                </span>
                <span wire:loading wire:target="save" class="inline-flex items-center gap-2">
                    <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z" />
                    </svg>
                    Saving...
                </span>
            </button>
        </div>
    </div>

    {{-- Form Body --}}
    <form wire:submit="save" class="grid grid-cols-1 gap-6 lg:grid-cols-12">

        {{-- Main Column (8 cols) --}}
        <div class="space-y-6 lg:col-span-8">

            {{-- Basic Information --}}
            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm space-y-4">
                <div class="border-b border-gray-100 pb-3">
                    <h3 class="text-base font-semibold text-gray-900">
                        Page Details
                    </h3>
                    <p class="mt-0.5 text-xs text-gray-500">
                        Define title, URL slug, and body content for this page.
                    </p>
                </div>

                <div>
                    <label for="title" class="block text-xs font-semibold text-gray-700 uppercase tracking-wider">
                        Page Title <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="text"
                        id="title"
                        wire:model.live.debounce.300ms="title"
                        placeholder="e.g. About Our Brand, Shipping Policy"
                        class="mt-1 block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    >
                    @error('title')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="slug" class="block text-xs font-semibold text-gray-700 uppercase tracking-wider">
                        URL Slug <span class="text-red-500">*</span>
                    </label>
                    <div class="mt-1 flex rounded-lg shadow-sm">
                        <span class="inline-flex items-center rounded-l-lg border border-r-0 border-gray-300 bg-gray-50 px-3 text-xs text-gray-500 font-mono">
                            /page/
                        </span>
                        <input
                            type="text"
                            id="slug"
                            wire:model="slug"
                            placeholder="about-us"
                            class="block w-full min-w-0 flex-1 rounded-none rounded-r-lg border-gray-300 text-xs font-mono shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        >
                    </div>
                    @error('slug')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="content" class="block text-xs font-semibold text-gray-700 uppercase tracking-wider">
                        Page Content (HTML / Text)
                    </label>
                    <textarea
                        id="content"
                        wire:model="content"
                        rows="14"
                        placeholder="Write your page content or paste HTML here..."
                        class="mt-1 block w-full font-mono text-xs rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    ></textarea>
                    <p class="mt-1 text-[11px] text-gray-400">Standard HTML tags (&lt;h2&gt;, &lt;p&gt;, &lt;ul&gt;, &lt;strong&gt;, &lt;a&gt;) are rendered cleanly on the public storefront.</p>
                    @error('content')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- SEO Fields Card --}}
            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm space-y-4">
                <div class="border-b border-gray-100 pb-3 flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-semibold text-gray-900">
                            Search Engine Optimization (SEO)
                        </h3>
                        <p class="mt-0.5 text-xs text-gray-500">
                            Optimize how this page appears in search engines like Google.
                        </p>
                    </div>
                    <span class="rounded bg-blue-50 px-2 py-1 text-xs font-semibold text-blue-700">
                        Meta Tags
                    </span>
                </div>

                <div>
                    <label for="meta_title" class="block text-xs font-semibold text-gray-700 uppercase tracking-wider">
                        SEO Meta Title
                    </label>
                    <input
                        type="text"
                        id="meta_title"
                        wire:model="meta_title"
                        placeholder="{{ $title ? $title . ' — ' . store_name() : 'Custom page title for search engines' }}"
                        class="mt-1 block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    >
                    <p class="mt-1 text-[11px] text-gray-400">Leave blank to default to page title and store name.</p>
                    @error('meta_title')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="meta_description" class="block text-xs font-semibold text-gray-700 uppercase tracking-wider">
                        Meta Description
                    </label>
                    <textarea
                        id="meta_description"
                        wire:model="meta_description"
                        rows="3"
                        placeholder="Brief summary of this page to entice search engine clicks (recommended: 140–160 characters)..."
                        class="mt-1 block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    ></textarea>
                    @error('meta_description')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
                    <div>
                        <label for="canonical_url" class="block text-xs font-semibold text-gray-700 uppercase tracking-wider">
                            Canonical URL (Optional)
                        </label>
                        <input
                            type="url"
                            id="canonical_url"
                            wire:model="canonical_url"
                            placeholder="https://example.com/page/slug"
                            class="mt-1 block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        >
                        <p class="mt-1 text-[11px] text-gray-400">Leave blank to use the default storefront page URL.</p>
                        @error('canonical_url')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider">
                            Search Engine Indexing
                        </label>
                        <div class="mt-3">
                            <label class="flex items-center gap-2.5 cursor-pointer">
                                <input
                                    type="checkbox"
                                    wire:model="is_indexed"
                                    class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 h-4 w-4"
                                >
                                <span class="text-xs font-medium text-gray-800">
                                    Allow search engines to index this page (robots: index, follow)
                                </span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        {{-- Sidebar Column (4 cols) --}}
        <div class="space-y-6 lg:col-span-4">

            {{-- Publishing Status Card --}}
            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm space-y-4">
                <h3 class="text-base font-semibold text-gray-900 border-b border-gray-100 pb-3">
                    Visibility & Publishing
                </h3>

                <div>
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input
                            type="checkbox"
                            wire:model="is_published"
                            class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 h-4 w-4"
                        >
                        <div>
                            <span class="text-sm font-semibold text-gray-800">Publish to Storefront</span>
                            <p class="text-xs text-gray-500">When unchecked, this page is saved as a private Draft and is hidden from the public.</p>
                        </div>
                    </label>
                </div>
            </div>

            {{-- Template Card --}}
            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm space-y-4">
                <h3 class="text-base font-semibold text-gray-900 border-b border-gray-100 pb-3">
                    Page Template
                </h3>

                <div>
                    <label for="template" class="block text-xs font-semibold text-gray-700 uppercase tracking-wider">
                        Layout Template
                    </label>
                    <select
                        id="template"
                        wire:model="template"
                        class="mt-1 block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    >
                        <option value="default">Default Page (Standard Layout)</option>
                        <option value="legal">Legal Policy (Terms, Privacy)</option>
                        <option value="contact">Contact Us (with Store Contact Info)</option>
                        <option value="faq">FAQ (Frequently Asked Questions)</option>
                    </select>
                </div>
            </div>

            {{-- Google Search Result Snippet Preview --}}
            <div class="rounded-xl border border-dashed border-gray-300 bg-gray-50/70 p-5">
                <div class="text-xs font-semibold uppercase tracking-wider text-gray-500 mb-3">
                    Search Engine Snippet Preview
                </div>

                <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                    <div class="text-xs text-emerald-800 truncate">
                        {{ url('/page/' . ($slug ?: 'sample-slug')) }}
                    </div>
                    <div class="mt-1 text-sm font-semibold text-blue-700 line-clamp-1 hover:underline cursor-pointer">
                        {{ $meta_title ?: ($title ? $title . ' — ' . store_name() : 'Page Title — ' . store_name()) }}
                    </div>
                    <div class="mt-1 text-xs text-gray-600 line-clamp-2">
                        {{ $meta_description ?: ($content ? \Illuminate\Support\Str::limit(strip_tags($content), 140) : 'Add a meta description to control the snippet that appears in search engine results.') }}
                    </div>
                </div>
            </div>

        </div>

    </form>

</div>
