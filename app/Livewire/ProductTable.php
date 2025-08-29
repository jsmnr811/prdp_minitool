<?php

namespace App\Livewire;

use App\Models\Product;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Illuminate\Validation\Rule;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;
use Livewire\WithPagination;

class ProductTable extends Component
{
    use WithPagination;

    #[Layout('layouts.app')]
    protected $paginationTheme = 'tailwind';

    public ?int $productId = null;
    public bool $showModal = false;
    public bool $confirmDelete = false;
    public string $search = '';
    public bool $showCreateModal = false;

    public array $product = [
        'lot_number'   => '',
        'rank'         => '',
        'variety'      => '',
        'green_grade'  => '',
        'origin'       => '',
        'process'      => '',
        'elevation'    => '',
        'cup_score'    => '',
        'cup_profile'  => '',
        'status'  => '',
    ];

    public array $auction = [
        'starting_bid_price'    => '',
        'lot_size' => '',
        'start_bid_date'      => '',
        'end_bid_date'  => '',
    ];


    public function mount(): void {}
    public function openCreateModal()
    {
        $this->showCreateModal = true;
    }
    public function closeCreateModal()
    {
        $this->showCreateModal = false;
    }
    public function createAlert()
    {
        $this->validate([
            'product.lot_number'     => [
                'required',
                'string',
                Rule::unique('products', 'lot_number')->ignore($this->productId),
            ],
            'product.rank'           => 'nullable|numeric',
            'product.variety'        => 'required|string',
            'product.green_grade'    => 'nullable|string',
            'product.origin'         => 'nullable|string',
            'product.process'        => 'nullable|string',
            'product.elevation'      => 'nullable|numeric',
            'product.cup_score'      => 'nullable|numeric',
            'product.cup_profile'    => 'nullable|string',

            // Auction validation
            'auction.starting_bid_price' => 'required|numeric',
            'auction.lot_size' => 'required|numeric|min:0.01',
            'auction.start_bid_date'     => 'required|date',
            'auction.end_bid_date'       => 'required|date|after:start_bid_date',
        ]);

        LivewireAlert::title('Are you sure?')
            ->text('This will create a new product.')
            ->warning()
            ->timer(0)
            ->withConfirmButton('Create Product')
            ->withCancelButton('Cancel')
            ->onConfirm('createProduct')
            ->show();
    }

    public function createProduct()
    {

        $product = Product::create([
            'lot_number' => $this->product['lot_number'],
            'rank' => $this->product['rank'],
            'variety' => $this->product['variety'],
            'green_grade' => $this->product['green_grade'],
            'origin' => $this->product['origin'],
            'process' => $this->product['process'],
            'elevation' => $this->product['elevation'],
            'cup_score' => $this->product['cup_score'],
            'cup_profile' => $this->product['cup_profile'],
            'status' => 1,
        ]);
        $auction = $product->auction()->create([
            'starting_bid_price' => $this->auction['starting_bid_price'],
            'lot_size' => $this->auction['lot_size'],
            'start_bid_date' => $this->auction['start_bid_date'],
            'end_bid_date' => $this->auction['end_bid_date'],
        ]);

        $this->showCreateModal = false;

        LivewireAlert::title('Product Created!')
            ->text('The product has been created successfully.')
            ->success()
            ->toast()
            ->position('top-end')
            ->show();
    }
    public function showEditModal(Product $product)
    {
        $this->product = $product->only(array_keys($this->product));
        $this->productId = $product->id;

        $auction = $product->auction;
        $this->auction = [
            'starting_bid_price' => $auction->starting_bid_price ?? null,
            'lot_size' => $auction->lot_size ?? null,
            'start_bid_date' => $auction->start_bid_date ?? null,
            'end_bid_date' => $auction->end_bid_date ?? null
        ];

        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->resetModal();
    }

    public function confirmSave()
    {
        LivewireAlert::title('Are you sure?')
            ->text('Update this product?')
            ->question()
            ->timer(0)
            ->withConfirmButton('Update')
            ->withCancelButton('Cancel')
            // ->withDenyButton('Delete')
            ->onConfirm('updateProduct')
            // ->onDeny('deleteFile', ['id' => $productId])
            // ->onDismiss('cancelAction', ['id' => $this->fileId])
            ->show();
    }

    public function updateProduct()
    {

        $this->validate([
            'product.lot_number'     => [
                'required',
                'string',
                Rule::unique('products', 'lot_number')->ignore($this->productId),
            ],
            'product.rank'           => 'nullable|numeric',
            'product.variety'        => 'required|string',
            'product.green_grade'    => 'nullable|string',
            'product.origin'         => 'nullable|string',
            'product.process'        => 'nullable|string',
            'product.elevation'      => 'nullable|numeric',
            'product.cup_score'      => 'nullable|numeric',
            'product.cup_profile'    => 'nullable|string',
            'product.status'         => 'required|in:0,1',

            // Auction validation
            'auction.starting_bid_price' => 'required|numeric',
            'auction.lot_size' => 'required|numeric|min:0.01',
            'auction.start_bid_date'     => 'required|date',
            'auction.end_bid_date'       => 'required|date|after:start_bid_date',
        ]);

        $this->product['status'] = $this->product['status'] ?? 1;

        $product = Product::findOrFail($this->productId);
        $product->update($this->product);
        if ($this->auction) {
            $product->auction()->updateOrCreate(
                ['product_id' => $product->id],
                [
                    'starting_bid_price' => $this->auction['starting_bid_price'],
                    'lot_size'           => $this->auction['lot_size'],
                    'start_bid_date'     => $this->auction['start_bid_date'],
                    'end_bid_date'       => $this->auction['end_bid_date'],
                ]
            );
        }

        $this->reset();
        LivewireAlert::title('Success!')
            ->text('You have updated the product.')
            ->success()
            ->toast()
            ->position('top-end')
            ->show();
    }
    public function resetModal()
    {
        $this->reset(['showModal', 'product']);
    }

    public function deleteAlert(int $productId)
    {
        $this->productId = $productId;
        LivewireAlert::title('Are you sure?')
            ->text('You won\'t be able to revert this?')
            ->warning()
            ->timer(0)
            ->withConfirmButton('Delete')
            ->withCancelButton('Cancel')
            // ->withDenyButton('Delete')
            ->onConfirm('deleteProduct')
            // ->onDeny('deleteFile', ['id' => $productId])
            // ->onDismiss('cancelAction', ['id' => $this->fileId])
            ->show();
    }

    public function deleteProduct()
    {
        if ($this->productId) {
            $product = Product::findOrFail($this->productId);

            if ($product->auction) {
                $product->auction->delete();
            }

            $product->delete();

            $this->reset();

            LivewireAlert::title('Success!')
                ->text('Product and associated auction have been deleted.')
                ->success()
                ->toast()
                ->position('top-end')
                ->show();
        }
    }


    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function render(): View
    {
        return view(
            'livewire.product-table',
            ['products' => Product::search($this->search)->with('auction')
                ->paginate(10)]
        );
    }
}
