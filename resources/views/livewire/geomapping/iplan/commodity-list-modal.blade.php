<?php

use App\Models\Commodity;
use Livewire\Attributes\On;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;

new class extends Component {
    use WithFileUploads;

    public ?Commodity $commodity = null;

    public $name, $abbr;
    public bool $editModal = false;
    public bool $addModal = false;

    public array $validatedUserData = [];

    public function mount(): void {}

    // Open edit modal and populate fields
    #[On('editGeomappingCommodity')]
    public function edit(Commodity $commodity)
    {
        $this->commodity = $commodity;
        $this->name = $commodity->name;
        $this->abbr = $commodity->abbr;

        $this->editModal = true;
    }

    // Confirm update modal alert
    public function confirmUpdate()
    {
        $this->validatedUserData = $this->validate([
            'name' => 'required|string|max:255',
            'abbr' => 'nullable|string|max:255',
        ]);

        LivewireAlert::title('Are you sure?')
            ->question()
            ->timer(0)
            ->withConfirmButton('Update')
            ->withCancelButton('Cancel')
            ->onConfirm('updateCommodity')
            ->show();
    }

    // Update commodity action
    public function updateCommodity()
    {
        $this->commodity->update($this->validatedUserData);

        $this->editModal = false;
        LivewireAlert::title('Success')
            ->success()
            ->toast()
            ->position('top-end')
            ->show();

        $this->dispatch('reloadDataTable');
    }

    // Confirm block/unblock alert
    #[On('confirmUpdateBlockStatus')]
    public function confirmUpdateBlockStatus(Commodity $commodity)
    {
        $this->commodity = $commodity;

        LivewireAlert::title('Are you sure?')
            ->question()
            ->text('Are you sure you want to update the status of this commodity?')
            ->timer(0)
            ->withConfirmButton('Update')
            ->withCancelButton('Cancel')
            ->onConfirm('updateBlockStatus')
            ->show();
    }

    public function updateBlockStatus()
    {
        $this->commodity->is_blocked = !$this->commodity->is_blocked;
        $this->commodity->save();

        LivewireAlert::title('Success')
            ->success()
            ->text('Commodity status has been updated successfully')
            ->toast()
            ->position('top-end')
            ->show();

        $this->dispatch('reloadDataTable');
    }

    // Show add modal and reset fields
    #[On('openAddCommodityModal')]
    public function add()
    {
        logger('add method called');
        $this->resetValidation();
        $this->reset(['name', 'abbr']);
        $this->dispatchBrowserEvent('show-add-modal');
    }

    // Confirm adding new commodity
    public function confirmAdd()
    {
        $this->validatedUserData = $this->validate([
            'name' => 'required|string|max:255',
            'abbr' => 'nullable|string|max:255',
        ]);

        LivewireAlert::title('Add new commodity?')
            ->question()
            ->timer(0)
            ->withConfirmButton('Add')
            ->withCancelButton('Cancel')
            ->onConfirm('storeCommodity')
            ->show();
    }

    // Save new commodity
    public function storeCommodity()
    {
        Commodity::create($this->validatedUserData);

        $this->addModal = false;
        LivewireAlert::title('Success')
            ->success()
            ->toast()
            ->position('top-end')
            ->show();

        $this->dispatch('reloadDataTable');
    }
};
?>

<div>
    {{-- Edit Modal --}}
    @if ($editModal)
    <div class="modal fade show d-block" id="editUserModal" tabindex="-1" role="dialog" aria-labelledby="editUserModalLabel" aria-modal="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content rounded-2xl shadow-lg">

                <div class="modal-header border-b d-flex justify-content-between align-items-center">
                    <h5 class="modal-title font-semibold text-lg" id="editUserModalLabel">Edit Information</h5>
                    <button type="button" class="btn-close" wire:click='$set("editModal", false)' aria-label="Close"></button>
                </div>

                <form wire:submit.prevent="confirmUpdate">
                    <div class="modal-body space-y-6">
                        @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                        @endif
                        <div>
                            <h6 class="text-gray-700 font-semibold mb-2">Commodity Info</h6>
                            <div class="grid grid-cols-3 gap-4">
                                <div>
                                    <label class="text-sm font-medium">Name<span class="text-red-500">*</span></label>
                                    <input type="text" wire:model="name" placeholder="Enter Name"
                                        class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                    @error('name')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label class="text-sm font-medium">Abbreviation</label>
                                    <input type="text" wire:model="abbr" placeholder="Enter Abbreviation"
                                        class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-t mt-6">
                        <button type="button" class="btn btn-secondary" wire:click='$set("editModal", false)'>Close</button>
                        <button type="submit" class="btn btn-primary">Save changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal-backdrop fade show"></div>
    @endif

    {{-- Add Modal --}}
    @if ($addModal)
<div wire:ignore.self class="modal fade" id="addUserModal" tabindex="-1" role="dialog" aria-labelledby="addUserModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content rounded-2xl shadow-lg">

                <div class="modal-header border-b d-flex justify-content-between align-items-center">
                    <h5 class="modal-title font-semibold text-lg" id="addUserModalLabel">Add New Commodity</h5>
                    <button type="button" class="btn-close" wire:click='$set("addModal", false)' aria-label="Close"></button>
                </div>

                <form wire:submit.prevent="confirmAdd">
                    <div class="modal-body space-y-6">
                        @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                        @endif
                        <div>
                            <h6 class="text-gray-700 font-semibold mb-2">Commodity Info</h6>
                            <div class="grid grid-cols-3 gap-4">
                                <div>
                                    <label class="text-sm font-medium">Name<span class="text-red-500">*</span></label>
                                    <input type="text" wire:model="name" placeholder="Enter Name"
                                        class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                    @error('name')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label class="text-sm font-medium">Abbreviation</label>
                                    <input type="text" wire:model="abbr" placeholder="Enter Abbreviation"
                                        class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-t mt-6">
                        <button type="button" class="btn btn-secondary" wire:click='$set("addModal", false)'>Close</button>
                        <button type="submit" class="btn btn-primary">Add Commodity</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal-backdrop fade show"></div>
    @endif
</div>