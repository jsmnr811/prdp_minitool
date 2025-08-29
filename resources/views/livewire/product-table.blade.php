<div>
    <div>
        <h1 class="text-xl font-semibold">Product List</h1>
        <section class="bg-gray-50 dark:bg-gray-900 p-3 sm:p-5">
            <div class="mx-auto max-w-screen-2xl px-8 lg:px-8">
                <!-- Start coding here -->
                <div class="bg-white dark:bg-gray-800 relative shadow-md sm:rounded-lg overflow-hidden">
                    <div
                        class="flex flex-col md:flex-row items-center justify-between space-y-3 md:space-y-0 md:space-x-4 p-4">
                        <div class="w-full md:w-1/2">
                            <form class="flex items-center">
                                <label for="simple-search" class="sr-only">Search</label>
                                <div class="relative w-full">
                                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                        <svg aria-hidden="true" class="w-5 h-5 text-gray-500 dark:text-gray-400"
                                            fill="currentColor" viewbox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                            <path fill-rule="evenodd"
                                                d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <input type="text" id="simple-search" wire:model.live.debounce.500ms="search"
                                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full pl-10 p-2 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500"
                                        placeholder="Search" required="">
                                </div>
                            </form>
                        </div>
                        <div class="w-full md:w-auto flex flex-col md:flex-row space-y-2 md:space-y-0 items-stretch md:items-center justify-end md:space-x-3 flex-shrink-0">
                            <button type="button" wire:click="openCreateModal()"
                                class="flex items-center justify-center text-white bg-blue-500 hover:bg-blue-600 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 dark:bg-blue-600 dark:hover:bg-blue-700 focus:outline-none dark:focus:ring-blue-800">
                                <svg class="h-4 w-4 mr-2" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                    <path clip-rule="evenodd" fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" />
                                </svg>
                                Add product
                            </button>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <x-flowbite.table>
                            <x-slot:thead>
                                <th class="px-4 py-2 border">Lot #</th>
                                <th class="px-4 py-2 border">Rank</th>
                                <th class="px-4 py-2 border">Variety</th>
                                <th class="px-4 py-2 border">Green Grade</th>
                                <th class="px-4 py-2 border">Origin/Source</th>
                                <th class="px-4 py-2 border">Process</th>
                                <th class="px-4 py-2 border">Elevation</th>
                                <th class="px-4 py-2 border">Cupping Score</th>
                                <th class="px-4 py-2 border">Notes</th>
                                <th class="px-4 py-2 border">Lot Size</th>
                                <th class="px-4 py-2 border">Starting Bid Price</th>
                                <th class="px-4 py-2 border">Bidding Duration</th>
                                <th class="px-4 py-2 border">Status</th>
                                <th class="px-4 py-2 border">Actions</th>
                            </x-slot:thead>

                            @forelse ($products as $product)
                            <tr class="hover:bg-gray-50 text-center">
                                <td class="px-4 py-2 border">{{ $product->lot_number }}</td>
                                <td class="px-4 py-2 border">{{ $product->rank }}</td>
                                <td class="px-4 py-2 border">{{ $product->variety }}</td>
                                <td class="px-4 py-2 border">{{ $product->green_grade }}</td>
                                <td class="px-4 py-2 border">{{ $product->origin }}</td>
                                <td class="px-4 py-2 border">{{ $product->process }}</td>
                                <td class="px-4 py-2 border">{{ number_format($product->elevation) }}</td>
                                <td class="px-4 py-2 border">{{ $product->cup_score }}</td>
                                <td class="px-4 py-2 border">
                                    @foreach (explode(',', $product->cup_profile) as $note)
                                    <span class="inline-block bg-gray-200 text-gray-800 text-sm px-2 py-1 rounded mr-1 mb-1">
                                        {{ trim($note) }}
                                    </span>
                                    @endforeach
                                </td>
                                <td class="px-4 py-2 border">{{ optional($product->auction)->lot_size }}</td>
                                <td class="px-4 py-2 border">{{ number_format(optional($product->auction)->starting_bid_price) }}</td>
                                <td class="px-4 py-2 border">
                                    {{ optional($product->auction)->start_bid_date ? \Carbon\Carbon::parse(optional($product->auction)->start_bid_date)->format('M d, Y h:iA') : '' }}
                                    - <br>
                                    {{ optional($product->auction)->end_bid_date ? \Carbon\Carbon::parse(optional($product->auction)->end_bid_date)->format('M d, Y h:iA') : '' }}
                                </td>
                                <td class="px-4 py-2 border">
                                    <span class="inline-flex items-center px-2 py-1 text-xs font-semibold
                                    {{ $product->status == 1 ? 'text-green-800 bg-green-100' : 'text-red-800 bg-red-100' }}
                                    rounded-full">
                                        {{ $product->status == 1 ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="w-40 px-4 py-2 border space-x-1">
                                    <div class="flex justify-center items-center space-x-2">
                                        <!-- Edit Button -->
                                        <a role="button" wire:click="showEditModal({{ $product->id }})"
                                            class="bg-blue-500 text-white px-3 py-2 text-xs rounded inline-flex items-center hover:bg-blue-600">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </a>

                                        <!-- Delete Button -->
                                        <a role="button" wire:click="deleteAlert({{ $product->id }})"
                                            class="bg-red-500 text-white px-3 py-2 text-xs rounded inline-flex items-center hover:bg-red-600">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="13" class="px-4 py-2 border text-center">
                                    No products found.
                                </td>
                            </tr>
                            @endforelse
                        </x-flowbite.table>

                    </div>

                    <x-flowbite.custom-pagination :paginator="$products" />

                </div>
            </div>
        </section>
        @if ($showModal)
        <x-modal name="product-modal" maxWidth="2xl" show="true">
            <div class="bg-white dark:bg-gray-900 rounded-lg shadow-md p-8 space-y-6 w-full">
                <h2 class="text-2xl font-semibold text-gray-800 dark:text-gray-100">Edit Product</h2>

                <form wire:submit.prevent="confirmSave" class="grid grid-cols-1 sm:grid-cols-2 gap-6 w-full">
                    <!-- Product Information Section -->
                    <div class="sm:col-span-2 mt-4">
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100">Product Information</h3>
                        <hr class="my-2">
                    </div>
                    @foreach (['variety', 'lot_number', 'rank', 'green_grade', 'origin', 'process', 'elevation', 'cup_score', 'status', 'cup_profile'] as $field)
                    <div class="{{ in_array($field, ['variety', 'cup_profile']) ? 'sm:col-span-2' : '' }}">
                        <label for="{{ $field }}"
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300 capitalize">
                            {{ $field === 'cap_profile' ? 'Notes' : str_replace('_', ' ', $field) }}
                        </label>

                        @if ($field === 'cup_profile')
                        <textarea id="{{ $field }}" wire:model.defer="product.{{ $field }}"
                            class="mt-2 w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 focus:border-blue-500 focus:ring-blue-500 @error('product.' . $field) border-red-500 dark:border-red-500 @enderror"
                            rows="4" placeholder="Enter {{ str_replace('_', ' ', $field) }}"></textarea>
                        @elseif ($field === 'status')
                        <select id="{{ $field }}" wire:model.defer="product.{{ $field }}"
                            class="mt-2 w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 focus:border-blue-500 focus:ring-blue-500 @error('product.' . $field) border-red-500 dark:border-red-500 @enderror">
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                        @else
                        <input type="{{ $field === 'cup_score' ? 'number' : 'text' }}"
                            id="{{ $field }}" wire:model.defer="product.{{ $field }}"
                            class="mt-2 w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 focus:border-blue-500 focus:ring-blue-500 @error('product.' . $field) border-red-500 dark:border-red-500 @enderror"
                            placeholder="Enter {{ str_replace('_', ' ', $field) }}"
                            {{ $field === 'variety' || $field === 'lot_number' ? 'required' : '' }}
                            step="{{ $field === 'cup_score' ? '0.01' : '' }}" />
                        @endif

                        @error('product.' . $field)
                        <p class="text-red-500 text-xs mt-2">{{ $message }}</p>
                        @enderror
                    </div>
                    @endforeach


                    <!-- Auction Information Section -->
                    <div class="sm:col-span-2 mt-4">
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100">Auction Information</h3>
                        <hr class="my-2">
                    </div>

                    @foreach ([
                    'starting_bid_price' => 'Starting Bid Price',
                    'lot_size' => 'Lot Size',
                    'start_bid_date' => 'Start Bid Date',
                    'end_bid_date' => 'End Bid Date'
                    ] as $field => $label)
                    <div>
                        <label for="{{ $field }}" class="block text-sm font-medium text-gray-700 dark:text-gray-300 capitalize">
                            {{ $label }}
                        </label>

                        @if($field == 'starting_bid_price')
                        <input type="number" step="0.01" id="{{ $field }}" wire:model.defer="auction.{{ $field }}"
                            class="mt-2 w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 focus:border-blue-500 focus:ring-blue-500 @error('auction.' . $field) border-red-500 dark:border-red-500 @enderror"
                            required />
                        @elseif($field == 'lot_size')
                        <input type="number"
                            id="{{ $field }}"
                            wire:model.defer="auction.{{ $field }}"
                            step="0.01"
                            min="0.01"
                            class="mt-2 w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 focus:border-blue-500 focus:ring-blue-500 @error('auction.' . $field) border-red-500 dark:border-red-500 @enderror"
                            required />

                        @elseif($field == 'start_bid_date' || $field == 'end_bid_date')
                        <input type="datetime-local" id="{{ $field }}" wire:model.defer="auction.{{ $field }}"
                            class="mt-2 w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 focus:border-blue-500 focus:ring-blue-500 @error('auction.' . $field) border-red-500 dark:border-red-500 @enderror"
                            required />
                        @endif

                        @error('auction.' . $field)
                        <p class="text-red-500 text-xs mt-2">{{ $message }}</p>
                        @enderror
                    </div>
                    @endforeach

                    <div class="sm:col-span-2 flex justify-end space-x-3">
                        <button type="button" wire:click="closeModal"
                            class="px-6 py-2 rounded-lg bg-red-500 text-white font-medium transition hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-300">
                            Close
                        </button>
                        <button type="submit"
                            class="px-6 py-2 rounded-lg bg-blue-500 text-white font-medium transition hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-300">
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </x-modal>
        @endif

        @if ($showCreateModal)
        <x-modal name="create-product-modal" maxWidth="2xl" show="true">
            <div class="bg-white dark:bg-gray-900 rounded-lg shadow-md p-8 space-y-6 w-full">
                <h2 class="text-2xl font-semibold text-gray-800 dark:text-gray-100">Create New Product</h2>

                <form wire:submit.prevent="createAlert" class="grid grid-cols-1 sm:grid-cols-2 gap-6 w-full">
                    <!-- Product Information Section -->
                    <div class="sm:col-span-2 mt-4">
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100">Product Information</h3>
                        <hr class="my-2">
                    </div>
                    @foreach (['variety', 'lot_number', 'rank', 'green_grade', 'origin', 'process', 'elevation', 'cup_score', 'cup_profile'] as $field)
                    <div class="{{ in_array($field, ['variety', 'cup_profile']) ? 'sm:col-span-2' : '' }}">
                        <label for="{{ $field }}"
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300 capitalize">
                            {{ $field === 'cap_profile' ? 'Notes' : str_replace('_', ' ', $field) }}
                        </label>

                        @if ($field === 'cup_profile')
                        <textarea id="{{ $field }}" wire:model.defer="product.{{ $field }}"
                            class="mt-2 w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 focus:border-blue-500 focus:ring-blue-500 @error('product.' . $field) border-red-500 dark:border-red-500 @enderror"
                            rows="4" placeholder="Enter {{ str_replace('_', ' ', $field) }}"></textarea>
                        @else
                        <input type="{{ $field === 'cup_score' ? 'number' : 'text' }}"
                            id="{{ $field }}" wire:model.defer="product.{{ $field }}"
                            class="mt-2 w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 focus:border-blue-500 focus:ring-blue-500 @error('product.' . $field) border-red-500 dark:border-red-500 @enderror"
                            placeholder="Enter {{ str_replace('_', ' ', $field) }}"
                            {{ $field === 'variety' || $field === 'lot_number' ? 'required' : '' }}
                            step="{{ $field === 'cup_score' ? '0.01' : '' }}" />
                        @endif

                        @error('product.' . $field)
                        <p class="text-red-500 text-xs mt-2">{{ $message }}</p>
                        @enderror
                    </div>
                    @endforeach


                    <!-- Auction Information Section -->
                    <div class="sm:col-span-2 mt-4">
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100">Auction Information</h3>
                        <hr class="my-2">
                    </div>

                    @foreach ([
                    'starting_bid_price' => 'Starting Bid Price',
                    'lot_size' => 'Lot Size',
                    'start_bid_date' => 'Start Bid Date',
                    'end_bid_date' => 'End Bid Date'
                    ] as $field => $label)
                    <div>
                        <label for="{{ $field }}" class="block text-sm font-medium text-gray-700 dark:text-gray-300 capitalize">
                            {{ $label }}
                        </label>

                        @if($field == 'starting_bid_price')
                        <input type="number" step="0.01" id="{{ $field }}" wire:model.defer="auction.{{ $field }}"
                            class="mt-2 w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 focus:border-blue-500 focus:ring-blue-500 @error('auction.' . $field) border-red-500 dark:border-red-500 @enderror"
                            required />
                        @elseif($field == 'lot_size')
                        <input type="number"
                            id="{{ $field }}"
                            wire:model.defer="auction.{{ $field }}"
                            step="0.01"
                            min="0.01"
                            class="mt-2 w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 focus:border-blue-500 focus:ring-blue-500 @error('auction.' . $field) border-red-500 dark:border-red-500 @enderror"
                            required />

                        @elseif($field == 'start_bid_date' || $field == 'end_bid_date')
                        <input type="datetime-local" id="{{ $field }}" wire:model.defer="auction.{{ $field }}"
                            class="mt-2 w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 focus:border-blue-500 focus:ring-blue-500 @error('auction.' . $field) border-red-500 dark:border-red-500 @enderror"
                            required />
                        @endif

                        @error('auction.' . $field)
                        <p class="text-red-500 text-xs mt-2">{{ $message }}</p>
                        @enderror
                    </div>
                    @endforeach

                    <div class="sm:col-span-2 flex justify-end space-x-3">
                        <button type="button" wire:click="closeCreateModal"
                            class="px-6 py-2 rounded-lg bg-red-500 text-white font-medium transition hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-300">
                            Close
                        </button>
                        <button type="submit"
                            class="px-6 py-2 rounded-lg bg-blue-500 text-white font-medium transition hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-300">
                            Create
                        </button>
                    </div>
                </form>
            </div>
        </x-modal>
        @endif
    </div>
</div>