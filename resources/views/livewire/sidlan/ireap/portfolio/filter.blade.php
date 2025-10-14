<?php

use Livewire\Volt\Component;

new class extends Component {
    public $filterCluster = 'All';
    public $filterType = 'All';

    public function updatedFilterCluster()
    {
        $this->dispatch('filter-updated', 
            cluster: $this->filterCluster, 
            type: $this->filterType
        );
    }

    public function updatedFilterType()
    {
        $this->dispatch('filter-updated', 
            cluster: $this->filterCluster, 
            type: $this->filterType
        );
    }
};
?>

<div class="row mt-5">
    <div class="col-12">
        <div class="row row-container bg-white row-cols-1 row-cols-lg-4 row-gap-3 rounded py-3 mx-1 mx-lg-0">
            <div class="col">
                <div class="filter-label">Filter by Cluster</div>
                <select wire:model.live="filterCluster" class="filter-dropdown form-select">
                    <option value="All">All</option>
                    <option value="Luzon A">Luzon A</option>
                    <option value="Luzon B">Luzon B</option>
                    <option value="Visayas">Visayas</option>
                    <option value="Mindanao">Mindanao</option>
                </select>
            </div>

            <!-- <div class="col">
                <div class="filter-label">Filter by SP Type</div>
                <select wire:model.live="filterType" class="filter-dropdown form-select">
                    <option value="All">All</option>
                    <option value="Start-up">Start-up</option>
                    <option value="Upgrading/Expansion">Upgrading/Expansion</option>
                </select>
            </div> -->
        </div>
    </div>
</div>
