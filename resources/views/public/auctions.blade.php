<x-public-layout>
    <x-layouts.public.header />
<div class="mx-auto max-w-screen-2xl px-8 lg:px-8 mb-8">
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
    <x-layouts.public.footer/>
</x-public-layout>