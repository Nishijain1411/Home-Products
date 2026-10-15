<div class="p-4 md:p-6 max-w-7xl mx-auto space-y-6">

    {{-- Flash Notifications --}}
    @if (session()->has('success'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800 flex items-center justify-between shadow-sm">
            <div class="flex items-center gap-2">
                <svg class="h-5 w-5 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="font-medium">{{ session('success') }}</span>
            </div>
        </div>
    @endif

    {{-- Header --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between border-b border-gray-200 pb-5">
        <div>
            <div class="flex items-center gap-2 text-xs font-medium text-gray-500">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-blue-600 transition">Dashboard</a>
                <span>/</span>
                <span class="text-gray-800">Content</span>
                <span>/</span>
                <span class="text-gray-800">Pages</span>
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-gray-900 mt-1">
                CMS Pages
            </h1>
            <p class="text-sm text-gray-500 mt-0.5">
                Manage public store pages like About Us, Contact, FAQ, Privacy Policy, and Terms & Conditions.
            </p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('admin.pages.create') }}"
                class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-medium text-white shadow-sm hover:bg-blue-700 transition">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Create Page
            </a>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-3 gap-4">
        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
            <div class="text-xs font-medium uppercase tracking-wider text-gray-500">Total Pages</div>
            <div class="mt-2 text-2xl font-bold text-gray-900">{{ number_format($totalPages) }}</div>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
            <div class="text-xs font-medium uppercase tracking-wider text-gray-500">Published Pages</div>
            <div class="mt-2 text-2xl font-bold text-emerald-600">{{ number_format($publishedPages) }}</div>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
            <div class="text-xs font-medium uppercase tracking-wider text-gray-500">Drafts</div>
            <div class="mt-2 text-2xl font-bold text-amber-600">{{ number_format($draftPages) }}</div>
        </div>
    </div>

    {{-- Filter & Search Toolbar --}}
    <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div class="relative sm:col-span-2">
                <input
                    type="text"
                    wire:model.live.debounce.300ms="search"
                    placeholder="Search pages by title or slug..."
                    class="block w-full rounded-lg border-gray-300 pl-9 text-xs shadow-sm focus:border-blue-500 focus:ring-blue-500 py-2"
                >
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                    <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
            </div>

            <div>
                <select
                    wire:model.live="statusFilter"
                    class="block w-full rounded-lg border-gray-300 text-xs shadow-sm focus:border-blue-500 focus:ring-blue-500 py-2"
                >
                    <option value="all">All Statuses</option>
                    <option value="published">Published</option>
                    <option value="draft">Drafts Only</option>
                </select>
            </div>
        </div>
    </div>

    {{-- Pages Table --}}
    <div class="rounded-xl border border-gray-200 bg-white shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-left text-sm">
                <thead class="bg-gray-50 text-xs font-semibold uppercase tracking-wider text-gray-500">
                    <tr>
                        <th class="px-6 py-3.5">Page Title</th>
                        <th class="px-6 py-3.5">URL Slug</th>
                        <th class="px-6 py-3.5">Template</th>
                        <th class="px-6 py-3.5">Status</th>
                        <th class="px-6 py-3.5">Published Date</th>
                        <th class="px-6 py-3.5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse ($pages as $page)
                        <tr wire:key="page-{{ $page->id }}" class="hover:bg-gray-50/70 transition">
                            <td class="px-6 py-4">
                                <div class="font-semibold text-gray-900">{{ $page->title }}</div>
                                @if ($page->meta_description)
                                    <div class="text-xs text-gray-500 line-clamp-1 mt-0.5">{{ $page->meta_description }}</div>
                                @endif
                            </td>

                            <td class="px-6 py-4 font-mono text-xs text-blue-600">
                                /page/{{ $page->slug }}
                            </td>

                            <td class="px-6 py-4 text-xs">
                                <span class="capitalize rounded bg-gray-100 px-2 py-0.5 font-medium text-gray-700">
                                    {{ $page->template }}
                                </span>
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap">
                                @if ($page->is_published)
                                    <span class="inline-flex items-center rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-medium text-emerald-800">
                                        Published
                                    </span>
                                @else
                                    <span class="inline-flex items-center rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-medium text-amber-800">
                                        Draft
                                    </span>
                                @endif
                            </td>

                            <td class="px-6 py-4 text-xs text-gray-500 whitespace-nowrap">
                                {{ $page->published_at ? format_date($page->published_at, true) : 'Not published' }}
                            </td>

                            <td class="px-6 py-4 text-right whitespace-nowrap text-xs font-medium">
                                <div class="flex items-center justify-end gap-2">
                                    <a
                                        href="{{ route('storefront.pages.show', $page->slug) }}"
                                        target="_blank"
                                        class="rounded-lg p-1.5 text-gray-500 hover:bg-gray-100 hover:text-gray-900 transition"
                                        title="View Public Page"
                                    >
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                        </svg>
                                    </a>

                                    <button
                                        type="button"
                                        wire:click="togglePublish({{ $page->id }})"
                                        class="rounded-lg p-1.5 text-gray-500 hover:bg-gray-100 hover:text-gray-900 transition"
                                        title="{{ $page->is_published ? 'Unpublish' : 'Publish' }}"
                                    >
                                        @if ($page->is_published)
                                            <svg class="h-4 w-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        @else
                                            <svg class="h-4 w-4 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                            </svg>
                                        @endif
                                    </button>

                                    <a
                                        href="{{ route('admin.pages.edit', $page) }}"
                                        class="rounded-lg p-1.5 text-blue-600 hover:bg-blue-50 transition"
                                        title="Edit page"
                                    >
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </a>

                                    <button
                                        type="button"
                                        wire:click="confirmDelete({{ $page->id }})"
                                        class="rounded-lg p-1.5 text-red-600 hover:bg-red-50 transition"
                                        title="Delete page"
                                    >
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-gray-100 text-gray-400">
                                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                </div>
                                <h3 class="mt-3 text-sm font-semibold text-gray-900">No pages found</h3>
                                <p class="mt-1 text-xs text-gray-500">
                                    Get started by creating a new custom CMS page.
                                </p>
                                <div class="mt-4">
                                    <a href="{{ route('admin.pages.create') }}"
                                        class="inline-flex items-center gap-1.5 rounded-lg bg-blue-600 px-3.5 py-2 text-xs font-semibold text-white shadow-sm hover:bg-blue-700 transition">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                        </svg>
                                        Create Page
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($pages->hasPages())
            <div class="border-t border-gray-200 px-6 py-4">
                {{ $pages->links() }}
            </div>
        @endif
    </div>

    {{-- Delete Confirmation Modal --}}
    @if ($showDeleteModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/50 p-4">
            <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-red-100 text-red-600">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-base font-semibold text-gray-900">Delete Page</h3>
                        <p class="mt-1 text-xs text-gray-500">
                            Are you sure you want to delete this page? Visitors navigating to its URL will receive a 404 error.
                        </p>
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <button
                        type="button"
                        wire:click="$set('showDeleteModal', false)"
                        class="rounded-lg border border-gray-300 px-4 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50 transition"
                    >
                        Cancel
                    </button>

                    <button
                        type="button"
                        wire:click="delete"
                        class="rounded-lg bg-red-600 px-4 py-2 text-xs font-semibold text-white shadow-sm hover:bg-red-700 transition"
                    >
                        Yes, Delete Page
                    </button>
                </div>
            </div>
        </div>
    @endif

</div>
