<?php

namespace App\Livewire;

use App\Models\Bid;
use App\Models\Product;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Auth;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;

class MarketPlace extends Component
{
    use WithPagination;

    #[Layout('layouts.app')]
    protected $paginationTheme = 'tailwind';

    public $manualBid;
    public $currentBid;
    public $productId;

    public  $selectedProduct;
    public bool $showModal = false;
    public bool $showHistoryModal = false;
    public $highest_bid_price;
    public array $auction = [
        'starting_bid_price'    => '',
        'lot_size' => '',
        'start_bid_date'      => '',
        'end_bid_date'  => '',
    ];

    public array $bid = [
        'amount' => '',
    ];
    protected $rules = [
        'manualBid' => 'required|numeric|min:0',
    ];

    public function mount($productId = null) {}

    public function openBidHistoryModal(Product $product)
    {
        $this->selectedProduct = $product->load('bids');

        $this->showHistoryModal = true;
    }
    public function openBidModal(Product $product)
    {
        // $this->selectedProductId = $productId;
        $this->selectedProduct = $product->load('auction', 'bids');
        // $product = Product::with('auction', 'bids')->find($this->selectedProductId);

        if ($this->selectedProduct) {
            $this->auction = [
                'starting_bid_price' => $this->selectedProduct->auction->starting_bid_price ?? 0,
                'lot_size' => $this->selectedProduct->auction->lot_size ?? 0,
                'start_bid_date' => $this->selectedProduct->auction->start_bid_date ?? null,
                'end_bid_date' => $this->selectedProduct->auction->end_bid_date ?? null,
            ];

            $this->highest_bid_price = 0;
            $this->manualBid = $this->selectedProduct->auction->starting_bid_price;
            $this->currentBid = $this->selectedProduct->auction->starting_bid_price;
            if ($this->selectedProduct->bids->count() > 0) {
                $highestBid = $this->selectedProduct->highest_bid_price;
                $this->highest_bid_price = $highestBid;
                $this->manualBid =  $highestBid + 10;
                $this->currentBid =  $highestBid + 10;
            }

            // $this->bid = ['amount' => $highestBid];
            // Calculate the highest bid price or fall back to starting bid

            // Set current bid and manual bid
        }
        // Show the modal
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
    }

    public function confirmQuickSave()
    {
        LivewireAlert::title('Confirm Submission')
            ->text('Are you sure you want to submit this bid? This action cannot be undone.')
            ->question()
            ->timer(0)
            ->withConfirmButton('Yes, Submit')
            ->withCancelButton('No, Cancel')
            ->onConfirm('confirmQuickBid')
            ->show();
    }

    // Quick Bid - Adds 10 to the current highest bid
    public function confirmQuickBid()
    {
        $this->validateBid();
        // Save the bid in the database
        Bid::create([
            'product_id' => $this->selectedProduct->id,
            'user_id' => Auth::id(),
            'amount' => $this->manualBid,
        ]);

        $this->reset();
        LivewireAlert::title('Success!')
            ->text('Your bid has been successfully submitted.')
            ->success()
            ->toast()
            ->position('top-end')
            ->show();
    }

    public function confirmSave()
    {
        $this->validateBid();

        LivewireAlert::title('Confirm Submission')
            ->text('Are you sure you want to submit this bid? This action cannot be undone.')
            ->question()
            ->timer(0)
            ->withConfirmButton('Yes, Submit')
            ->withCancelButton('No, Cancel')
            ->onConfirm('confirmBid', ['manualBid' => $this->manualBid])
            ->show();
    }

    private function validateBid()
    {
        $rule = ['manualBid' => 'required|numeric|min:' . ($this->selectedProduct->auction->starting_bid_price)];
        $message = ['manualBid.min' => 'Your bid must be at least ₱ ' . number_format($this->selectedProduct->auction->starting_bid_price, 2) . '.',];

        if ($this->selectedProduct->bids->count() > 0) {
            $rule['manualBid'] = 'required|numeric|min:' . ($this->selectedProduct->highest_bid_price + 10);
            $message['manualBid.min'] = 'Your bid must be at least ₱ 10.00 higher than the current bid which is ₱ ' . number_format($this->selectedProduct->highest_bid_price + 10, 2) . '.';
        }

        $this->validate($rule, $message);
    }
    // Confirm and save the bid
    public function confirmBid()
    {
        // Save the bid in the database
        Bid::create([
            'product_id' => $this->selectedProduct->id,
            'user_id' => Auth::id(),
            'amount' => $this->manualBid,
        ]);

        $this->reset();
        LivewireAlert::title('Success!')
            ->text('Your bid has been successfully submitted.')
            ->success()
            ->toast()
            ->position('top-end')
            ->show();
    }

    // Render the component view
    public function render()
    {
        return view('livewire.market-place', [
            'products' => Product::with('auction', 'bids')->withCount('bids')->get(),
        ]);
    }
}
