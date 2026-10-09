<div class="space-y-6">

    {{-- Header --}}
    <div>
        <h1 class="text-2xl font-semibold text-gray-900">
            Customers
        </h1>

        <p class="mt-1 text-sm text-gray-500">
            Manage registered customers and their order history.
        </p>
    </div>


    {{-- Search --}}
    <div class="rounded-2xl border border-gray-200 bg-white p-5">

        <label class="text-sm font-medium text-gray-700">
            Search Customers
        </label>

        <input
            type="text"
            wire:model.live.debounce.300ms="search"
            placeholder="Search by name or email..."
            class="mt-2 w-full max-w-md rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-blue-500 focus:ring-blue-500"
        >

    </div>


    {{-- Customer Table --}}
    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white">

        <div class="overflow-x-auto">

            <table class="min-w-full divide-y divide-gray-200">

                <thead class="bg-gray-50">

                    <tr>

                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                            Customer
                        </th>

                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                            Email
                        </th>

                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                            Joined
                        </th>

                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                            Orders
                        </th>

                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                            Total Spent
                        </th>

                        <th class="px-6 py-4 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">
                            Action
                        </th>

                    </tr>

                </thead>

                <tbody class="divide-y divide-gray-200">

                    @forelse ($customers as $customer)

                        <tr class="hover:bg-gray-50">

                            <td class="px-6 py-4">

                                <p class="text-sm font-semibold text-gray-900">
                                    {{ $customer->name }}
                                </p>

                            </td>

                            <td class="px-6 py-4 text-sm text-gray-600">
                                {{ $customer->email }}
                            </td>

                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-600">
                                {{ $customer->created_at->format('d M Y') }}
                            </td>

                            <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900">
                                {{ $customer->orders_count }}
                            </td>

                            <td class="whitespace-nowrap px-6 py-4 text-sm font-semibold text-gray-900">
                                ₹{{ number_format($customer->orders_sum_total ?? 0, 2) }}
                            </td>

                            <td class="whitespace-nowrap px-6 py-4 text-right">

                               <a
                                    href="{{ route('admin.customers.show', ['customerId' => $customer->id]) }}"
                                    class="text-sm font-semibold text-blue-600 hover:text-blue-700"    >                    
                                    View
                                </a>

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td
                                colspan="6"
                                class="px-6 py-12 text-center text-sm text-gray-500"
                            >
                                No customers found.
                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>


        @if ($customers->hasPages())

            <div class="border-t border-gray-200 px-6 py-4">
                {{ $customers->links() }}
            </div>

        @endif

    </div>

</div>