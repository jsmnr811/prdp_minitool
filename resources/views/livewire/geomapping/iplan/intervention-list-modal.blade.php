<?php

use App\Models\Intervention;
use Livewire\Attributes\On;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;

new class extends Component {
    use WithFileUploads;

    public ?Intervention $intervention = null;
    public string $name = '';
    public bool $editMode = false;
    public bool $interventionModal = false;
    public array $validatedUserData = [];

    public function openInterventionModal(): void
    {
        $this->resetExcept('interventionModal');
        $this->intervention = new Intervention();
        $this->interventionModal = true;
    }

    public function confirmAdd(): void
    {
        $this->validatedUserData = $this->validate([
            'name' => 'required|string|max:255',
        ]);

        LivewireAlert::title('Add new intervention?')->question()->timer(0)->withConfirmButton('Add')->withCancelButton('Cancel')->onConfirm('updateIntervention')->show();
    }

    #[On('editGeomappingIntervention')]
    public function edit(Intervention $intervention)
    {
        $this->intervention = $intervention;
        $this->name = $intervention->name;
        $this->editMode = true;
        $this->interventionModal = true;
    }

    public function confirmUpdate()
    {
        $this->validatedUserData = $this->validate([
            'name' => 'required|string|max:255',
        ]);

        LivewireAlert::title('Are you sure?')->question()->timer(0)->withConfirmButton('Update')->withCancelButton('Cancel')->onConfirm('updateIntervention')->show();
    }

    //Function for saving commodities, For Adding and Updating
    public function updateIntervention(): void
    {
        $this->intervention->name = $this->name;
        $this->intervention->save();

        $this->interventionModal = false;
        $this->editMode = false;
        LivewireAlert::title('Success')->success()->toast()->position('top-end')->show();

        $this->dispatch('reloadDataTable');
    }

    #[On('confirmUpdateBlockStatus')]
    public function confirmUpdateBlockStatus(Intervention $intervention)
    {
        $this->intervention = $intervention;
        LivewireAlert::title('Are you sure?')->question()->text('Are you sure you want to update the status of this intervention?')->timer(0)->withConfirmButton('Update')->withCancelButton('Cancel')->onConfirm('updateBlockStatus')->show();
    }

    public function updateBlockStatus()
    {
        $this->intervention->is_blocked = !$this->intervention->is_blocked;
        $this->intervention->save();

        LivewireAlert::title('Success')->success()->text('Intervention status has been updated successfully')->toast()->position('top-end')->show();

        $this->dispatch('reloadDataTable');
    }
};
?>

<div>

    <button wire:click="openInterventionModal" type="button" class="btn btn-primary">
        + Add Intervention
    </button>
    {{-- Edit Modal --}}
    @if ($interventionModal)
        <div class="modal fade show d-block" id="{{ $editMode ? 'editInterventionModal' : 'addInterventionModal' }}"
            tabindex="-1" role="dialog" aria-labelledby="editUserModalLabel" aria-modal="true">
            <div class="modal-dialog modal-xl" role="document">
                <div class="modal-content rounded-2xl shadow-lg">

                    <div class="modal-header border-b d-flex justify-content-between align-items-center">
                        <h5 class="modal-title font-semibold text-lg" id="editUserModalLabel">
                            {{ $editMode ? 'Edit Intervention' : 'Add New Intervention' }}</h5>
                        <button type="button" class="btn-close" wire:click='$set("interventionModal", false)'
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
                            <div>
                                <h6 class="text-gray-700 font-semibold mb-2">Intervention Info</h6>
                                <div class="grid grid-cols-3 gap-4">
                                    <div>
                                        <label class="text-sm font-medium">Name<span
                                                class="text-red-500">*</span></label>
                                        <input type="text" wire:model="name" placeholder="Enter Name"
                                            class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                        @error('name')
                                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer border-t mt-6">
                            <button type="button" class="btn btn-secondary"
                                wire:click='$set("interventionModal", false)'>Close</button>
                            <button type="submit" class="btn btn-primary">Save changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="modal-backdrop fade show"></div>
    @endif
</div>
