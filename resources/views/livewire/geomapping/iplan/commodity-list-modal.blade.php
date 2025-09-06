<?php

use App\Models\Commodity;
use Livewire\Attributes\On;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;

new class extends Component {
    use WithFileUploads;

    public ?Commodity $commodity = null;
    public string $name = '';
    public string $abbr = '';
    public bool $editMode = false;
    public bool $commodityModal = false;
    public array $validatedUserData = [];

    public function openCommodityModal(): void
    {
        $this->resetExcept('commodityModal');
        $this->commodity = new Commodity();
        $this->commodityModal = true;
    }

    public function confirmAdd(): void
    {
        $this->validatedUserData = $this->validate([
            'name' => 'required|string|max:255',
            'abbr' => 'nullable|string|max:255',
        ]);

        LivewireAlert::title('Add new commodity?')->question()->timer(0)->withConfirmButton('Add')->withCancelButton('Cancel')->onConfirm('updateCommodity')->show();
    }

    #[On('editGeomappingCommodity')]
    public function edit(Commodity $commodity)
    {
        $this->commodity = $commodity;
        $this->name = $commodity->name;
        $this->abbr = $commodity->abbr;
        $this->editMode = true;
        $this->commodityModal = true;
    }

    public function confirmUpdate()
    {
        $this->validatedUserData = $this->validate([
            'name' => 'required|string|max:255',
            'abbr' => 'nullable|string|max:255',
        ]);

        LivewireAlert::title('Are you sure?')->question()->timer(0)->withConfirmButton('Update')->withCancelButton('Cancel')->onConfirm('updateCommodity')->show();
    }

    //Function for saving commodities, For Adding and Updating
    public function updateCommodity(): void
    {
        $this->commodity->name = $this->name;
        $this->commodity->abbr = $this->abbr;
        $this->commodity->save();

        $this->commodityModal = false;
        $this->editMode = false;
        LivewireAlert::title('Success')->success()->toast()->position('top-end')->show();

        $this->dispatch('reloadDataTable');
    }

    #[On('confirmUpdateBlockStatus')]
    public function confirmUpdateBlockStatus(Commodity $commodity)
    {
        $this->commodity = $commodity;
        LivewireAlert::title('Are you sure?')->question()->text('Are you sure you want to update the status of this commodity?')->timer(0)->withConfirmButton('Update')->withCancelButton('Cancel')->onConfirm('updateBlockStatus')->show();
    }

    public function updateBlockStatus()
    {
        $this->commodity->is_blocked = !$this->commodity->is_blocked;
        $this->commodity->save();

        LivewireAlert::title('Success')->success()->text('Commodity status has been updated successfully')->toast()->position('top-end')->show();

        $this->dispatch('reloadDataTable');
    }
};
?>

<div>

    <button wire:click="openCommodityModal" type="button" class="btn btn-primary">
        + Add Commodity
    </button>
    {{-- Edit Modal --}}
    @if ($commodityModal)
        <div class="modal fade show d-block" id="{{ $editMode ? 'editCommodityModal' : 'addCommodityModal' }}"
            tabindex="-1" role="dialog" aria-labelledby="editUserModalLabel" aria-modal="true">
            <div class="modal-dialog modal-md" role="document">
                <div class="modal-content rounded-2xl shadow-lg">

                    <div class="modal-header border-b d-flex justify-content-between align-items-center">
                        <h5 class="modal-title font-semibold text-lg" id="editUserModalLabel">
                            {{ $editMode ? 'Edit Commodity' : 'Add New Commodity' }}</h5>
                        <button type="button" class="btn-close" wire:click='$set("commodityModal", false)'
                            aria-label="Close"></button>
                    </div>

                    <form wire:submit.prevent="{{ $editMode ? 'confirmUpdate' : 'confirmAdd' }}">
                        <div class="modal-body space-y-6">
                            {{-- @if ($errors->any())
                                <div class="alert alert-danger">
                                    <ul>
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif --}}
                                <div class="grid grid-cols-3 gap-4 px-4">
                                    <div class="row mb-3">
                                        <label class="text-sm font-medium form-label">Name<span
                                                class="text-red-500">*</span></label>
                                        <input type="text" wire:model="name" placeholder="Enter Name"
                                            class="form-control">
                                        @error('name')
                                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div class="row mb-2">
                                        <label class="text-sm font-medium form-label">Abbreviation</label>
                                        <input type="text" wire:model="abbr" placeholder="Enter Abbreviation"
                                            class="form-control">
                                        @error('abbr')
                                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                        </div>
                        <div class="modal-footer border-t mt-6">
                            <button type="button" class="btn btn-secondary"
                                wire:click='$set("commodityModal", false)'>Close</button>
                            <button type="submit" class="btn btn-primary">Save changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="modal-backdrop fade show"></div>
    @endif
</div>
