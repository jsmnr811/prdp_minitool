@props([
'productId' => '',
'icon' => '',
'title' => '',
'rank' => '-',
'grade' => '-',
'origin' => '-',
'process' => '-',
'elevation' => '-',
'cup_score' => '-',
'cup_profile' => '-',
'start_bid' => '-',
'current_bid' => '-',
'active' => false,
'wireNavigate' => false,
'start_time' => '-',
'remaining_time' => '-',
'bid_count' => 0,
'lot_size'
])

<div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
    <div class="h-56 w-full">
        <a href="#">
            <img class="mx-auto h-full dark:hidden" src="{{ asset('media/'.$icon.'.png') }}" alt="" />
            <img class="mx-auto hidden h-full dark:block" src="{{ asset('media/'.$icon.'.png') }}" alt="" />
        </a>
    </div>
    <div class="pt-6">
        <div x-data="{ open: false }" class="w-full">
            <!-- Accordion Header (Click the Title to Open) -->
            <h6 @click="open = !open"
                class="cursor-pointer flex justify-between items-center text-lg font-semibold leading-snug text-gray-900 hover:underline dark:text-white">

                <!-- Left section: Icon + Title + Rank -->
                <span class="flex items-center gap-1">
                    <i class="fa-solid fa-arrow-up-right-from-square text-xs"></i>
                    {{ $title }}
                    <span class="text-xs text-gray-500 dark:text-gray-400 ml-1">Rank #{{ $rank }} | Lot Size: {{ $lot_size }}</span>
                </span>

                <!-- Right section: Bid Count with wire:click and stop propagation -->
                <span wire:click="openBidHistoryModal({{ $productId }})" @click.stop class="text-xs text-gray-600 dark:text-gray-300 font-medium cursor-pointer">
                    Bids: {{ $bid_count }}
                </span>
            </h6>

            <!-- Accordion Content -->
            <div x-show="open" x-transition class="mt-2">
                <!-- Grid for Coffee Details -->
                <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- First Column -->
                    <ul class="flex flex-col gap-2">
                        <li class="flex items-center gap-2">
                            <p class="text-xs font-medium text-gray-500 dark:text-gray-400">
                                Green Grade: <br><span
                                    class="text-gray-800 dark:text-gray-300 font-bold text-md">{{ $grade }}</span>
                            </p>
                        </li>
                        <li class="flex items-center gap-2">
                            <p class="text-xs font-medium text-gray-500 dark:text-gray-400">
                                Origin/Source: <br><span
                                    class="text-gray-800 dark:text-gray-300 font-bold text-md">{{ $origin }}</span>
                            </p>
                        </li>
                        <li class="flex items-center gap-2">
                            <p class="text-xs font-medium text-gray-500 dark:text-gray-400">
                                Process: <br><span
                                    class="text-gray-800 dark:text-gray-300 font-bold text-md">{{ $process }}</span>
                            </p>
                        </li>
                    </ul>

                    <!-- Second Column -->
                    <ul class="flex flex-col gap-2">
                        <li class="flex items-center gap-2">
                            <p class="text-xs font-medium text-gray-500 dark:text-gray-400">
                                Elevation: <br><span
                                    class="text-gray-800 dark:text-gray-300 font-bold text-md">{{ $elevation }}</span>
                            </p>
                        </li>

                        <li class="flex items-center gap-2">
                            <p class="text-xs font-medium text-gray-500 dark:text-gray-400">
                                Cupping Score: <br><span
                                    class="text-gray-800 dark:text-gray-300 font-bold text-md">{{ $cup_score }}</span>
                            </p>
                        </li>
                    </ul>
                </div>

                <!-- Cup Profile Section -->
                <div class="mt-2">
                    <ul class="w-full space-y-2">
                        <li class="flex items-start gap-2 w-full">
                            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 w-full">
                                Cup Profile: <br>
                                @foreach (explode(',', $cup_profile) as $profile)
                                <span
                                    class="inline-block bg-gray-200 text-gray-800 text-xs px-2 py-1 rounded mr-1 mb-1">
                                    {{ trim($profile) }}
                                </span>
                                @endforeach
                            </p>
                        </li>
                    </ul>
                </div>
            </div>
        </div>


        <div class="mt-2 grid grid-cols-1 md:grid-cols-2 gap-4 w-full">
            <!-- First Column: Starting Bid Price -->
            <ul class="w-full space-y-2">
                <li class="flex flex-col gap-2">
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400">
                        Starting Bid Price:
                    </p>
                    <span class="text-gray-800 dark:text-gray-300 font-bold text-xs">₱{{ $start_bid }}</span>
                </li>
            </ul>


            <!-- Second Column: Remaining Time -->
            <ul class="w-full space-y-2">
                <li class="flex flex-col gap-2" x-data="{
                        startDate: new Date('{{ $start_time }}').getTime(),
                        endDate: new Date('{{ $remaining_time }}').getTime(),
                        countdown: '',
                        timeLeft() {
                            const now = new Date().getTime();

                            if (now < this.startDate) {
                                this.countdown = 'Not started';
                                return;
                            }

                            const distance = this.endDate - now;

                            if (distance <= 0) {
                                this.countdown = 'EXPIRED';
                                return;
                            }

                            const days = Math.floor(distance / (1000 * 60 * 60 * 24));
                            const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                            const seconds = Math.floor((distance % (1000 * 60)) / 1000);

                            this.countdown = `${days}d ${hours}h ${minutes}m ${seconds}s`;
                        }
                    }" x-init="timeLeft(); setInterval(() => timeLeft(), 1000)">
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400">
                        Remaining Time:
                    </p>
                    <span
                        x-text="countdown"
                        :class="{
                                'text-red-500 dark:text-red-400': countdown === 'Not started' || countdown === 'EXPIRED',
                                'text-green-500 dark:text-green-400': countdown !== 'Not started' && countdown !== 'EXPIRED'
                            }"
                        class="ms-2 text-xs font-bold">
                    </span>

                </li>

            </ul>
        </div>

        <div class="mt-4 flex items-center justify-between gap-4">
            <div>
                <p class="text-xs text-gray-500 dark:text-gray-400">Current Highest Bid:</p>
                <p class="text-2xl font-extrabold leading-tight text-gray-900 dark:text-white">₱{{ $current_bid }}</p>
            </div>

            @auth
            @if ($start_time !== '-' && $remaining_time !== '-' && now()->between(Carbon\Carbon::parse($start_time), Carbon\Carbon::parse($remaining_time),))
            <div class="flex items-center">
                <!-- "Place Bid" button -->
                <button type="button" wire:click="openBidModal({{ $productId }})"
                    class="inline-flex items-center rounded-lg bg-green-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-4 focus:ring-green-300 dark:bg-green-500 dark:hover:bg-green-600 dark:focus:ring-green-700">
                    <i class="fa-solid fa-square-plus me-2"></i> Bid
                </button>
            </div>
            @endif
            @endauth
        </div>
    </div>


</div>