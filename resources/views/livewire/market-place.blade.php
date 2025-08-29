<div>
    <section class="bg-gray-50 py-4 antialiased dark:bg-gray-900 md:py-6">

        <div class="mx-auto max-w-screen-2xl px-8 lg:px-8">
            <!-- Heading & Filters -->
            <div class="mb-4 items-end justify-between space-y-4 sm:flex sm:space-y-0 md:mb-8">
                <div>
                    <nav class="flex" aria-label="Breadcrumb">
                        <ol class="inline-flex items-center space-x-1 md:space-x-2 rtl:space-x-reverse">
                            <li class="inline-flex items-center align-middle">
                                <a href="#"
                                    class="inline-flex align-middleitems-center text-sm font-medium text-gray-700 hover:text-primary-600 dark:text-gray-400 dark:hover:text-white">
                                    <svg class="me-3 h-4 w-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                                        fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M5.535 7.677c.313-.98.687-2.023.926-2.677H17.46c.253.63.646 1.64.977 2.61.166.487.312.953.416 1.347.11.42.148.675.148.779 0 .18-.032.355-.09.515-.06.161-.144.3-.243.412-.1.111-.21.192-.324.245a.809.809 0 0 1-.686 0 1.004 1.004 0 0 1-.324-.245c-.1-.112-.183-.25-.242-.412a1.473 1.473 0 0 1-.091-.515 1 1 0 1 0-2 0 1.4 1.4 0 0 1-.333.927.896.896 0 0 1-.667.323.896.896 0 0 1-.667-.323A1.401 1.401 0 0 1 13 9.736a1 1 0 1 0-2 0 1.4 1.4 0 0 1-.333.927.896.896 0 0 1-.667.323.896.896 0 0 1-.667-.323A1.4 1.4 0 0 1 9 9.74v-.008a1 1 0 0 0-2 .003v.008a1.504 1.504 0 0 1-.18.712 1.22 1.22 0 0 1-.146.209l-.007.007a1.01 1.01 0 0 1-.325.248.82.82 0 0 1-.316.08.973.973 0 0 1-.563-.256 1.224 1.224 0 0 1-.102-.103A1.518 1.518 0 0 1 5 9.724v-.006a2.543 2.543 0 0 1 .029-.207c.024-.132.06-.296.11-.49.098-.385.237-.85.395-1.344ZM4 12.112a3.521 3.521 0 0 1-1-2.376c0-.349.098-.8.202-1.208.112-.441.264-.95.428-1.46.327-1.024.715-2.104.958-2.767A1.985 1.985 0 0 1 6.456 3h11.01c.803 0 1.539.481 1.844 1.243.258.641.67 1.697 1.019 2.72a22.3 22.3 0 0 1 .457 1.487c.114.433.214.903.214 1.286 0 .412-.072.821-.214 1.207A3.288 3.288 0 0 1 20 12.16V19a2 2 0 0 1-2 2h-6a1 1 0 0 1-1-1v-4H8v4a1 1 0 0 1-1 1H6a2 2 0 0 1-2-2v-6.888ZM13 15a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v2a1 1 0 0 1-1 1h-2a1 1 0 0 1-1-1v-2Z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    Marketplace
                                </a>
                            </li>
                            <li>
                                <div class="flex items-center">
                                    <!-- Arrow Icon -->
                                    <svg class="h-5 w-5 text-gray-400 rtl:rotate-180" aria-hidden="true"
                                        xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none"
                                        viewBox="0 0 24 24">
                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                            stroke-width="2" d="m9 5 7 7-7 7" />
                                    </svg>
                                    <!-- Text for Products -->
                                    <a href="#"
                                        class="ms-1 text-sm font-medium text-gray-700 hover:text-primary-600 dark:text-gray-400 dark:hover:text-white md:ms-2">Products</a>
                                </div>
                            </li>
                        </ol>
                    </nav>
                    <h2 class="mt-3 text-xl font-semibold text-gray-900 dark:text-white sm:text-2xl">Products</h2>
                </div>
            </div>
            <div class="mb-4 grid gap-4 sm:grid-cols-2 md:mb-8 lg:grid-cols-3 xl:grid-cols-4">
                @forelse($products as $product)
                <x-flowbite.product-card :icon="strtolower($product->variety)" :title="$product->variety" :rank="$product->rank" :grade="$product->green_grade"
                    :origin="$product->origin" :process="$product->process" :elevation="$product->elevation" :cup_score="$product->cup_score" :cup_profile="$product->cup_profile"
                    :start_bid="$product->auction->starting_bid_price" :start_time="$product->auction->start_bid_date" :remaining_time="$product->auction->end_bid_date" :current_bid="$product->highest_bid_price" :bid_count="$product->bids->count()"
                    :productId="$product->id" :lot_size="$product->auction->lot_size" />
                @empty
                <p>No products available.</p>
                @endforelse
            </div>
        </div>


    </section>

    @if ($showModal)
    <x-modal name="bid-modal" maxWidth="2xl" :show="$showModal">
        <div class="bg-white dark:bg-gray-900 rounded-lg shadow-md p-8 space-y-6 w-full">
            <h2 class="text-2xl font-semibold text-gray-800 dark:text-gray-100">Place a Bid</h2>

            <div class="text-sm text-gray-600 dark:text-gray-300">
                Starting Bid Price:
                <strong>₱{{ number_format($auction['starting_bid_price'], 2) }}</strong><br>

                Current Highest Bid:
                <strong>
                    ₱{{ number_format($highestBid = $selectedProduct->bids->max('amount') ?: 0, 2) }}
                </strong><br>

                Minimum Next Bid:
                <strong>
                    ₱{{ number_format($highestBid ? $highestBid + 10 : $auction['starting_bid_price'], 2) }}
                </strong>
            </div>



            <form wire:submit.prevent="confirmSave" class="grid grid-cols-1 sm:grid-cols-2 gap-6 w-full">

                <!-- Manual Bid Input -->
                <div class="sm:col-span-2">
                    <label for="manualBid" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Enter Your Bid (₱)
                    </label>
                    <input type="number" step="0.01" wire:model="manualBid" id="manualBid"
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                    @error('manualBid')
                    <span class="text-red-500 text-xs">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Action Buttons -->
                <div class="sm:col-span-2 flex justify-end space-x-3">
                    <button type="button" wire:click="closeModal"
                        class="px-6 py-2 rounded-lg bg-red-500 text-white font-medium transition hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-300">
                        Close
                    </button>
                    <div class="sm:col-span-2">
                        <button type="button" wire:click="confirmQuickSave"
                            class="px-6 py-2 rounded-lg bg-green-500 text-white font-medium transition hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-green-300">
                            Quick Bid (
                            ₱{{ number_format($highestBid ? $highestBid + 10 : $auction['starting_bid_price'], 2) }})
                        </button>
                    </div>
                    <button type="submit"
                        class="px-6 py-2 rounded-lg bg-blue-500 text-white font-medium transition hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-300">
                        Submit Bid
                    </button>
                </div>
            </form>
        </div>
    </x-modal>
    @endif


    @if ($showHistoryModal)
    <x-livewire-modal name="history-modal" maxWidth="2xl" :show="$showHistoryModal" toggle="showHistoryModal">
        <div class="bg-white dark:bg-gray-900 rounded-lg shadow-md p-8 space-y-6 w-full">
            <h2 class="text-2xl font-semibold text-gray-800 dark:text-gray-100">Bid History for
                {{ $selectedProduct->variety }}
            </h2>
            <div class="mx-auto max-w-screen-xl">
                <div class="bg-white dark:bg-gray-800 relative shadow-md sm:rounded-lg overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                            <thead
                                class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                <tr>
                                    <th scope="col" class="px-4 py-3">Date</th>
                                    @if(auth()->check() && !auth()->user()->hasRole('bidder'))
                                    <th scope="col" class="px-4 py-3">Name</th>
                                    <th scope="col" class="px-4 py-3">Email</th>
                                    @endif
                                    <th scope="col" class="px-4 py-3">Bid Price</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($selectedProduct->bids as $bid)
                                <tr class="border-b dark:border-gray-700">
                                    <td class="px-4 py-3">
                                        {{ \Carbon\Carbon::parse($bid->bid_time)->format('M d Y h:i:s A') }}
                                    </td>

                                    @if(auth()->check() && !auth()->user()->hasRole('bidder'))
                                    <td class="px-4 py-3">{{ $bid->user?->name ?? 'N/A' }}</td>
                                    <td class="px-4 py-3">{{ $bid->user?->email ?? 'N/A' }}</td>
                                    @endif

                                    <td class="px-4 py-3">₱{{ number_format($bid->amount, 2) }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-2 border text-center">
                                        No bids found.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </x-livewire-modal>
    @endif

</div>