@props([
    'id',
    'name',
    'wireModel',
    'modal' => null,
    'placeholder' => 'Select an item',
    'multi' => false,
    'selectedValue' => [null],
])

<div wire:ignore x-data="{
    init() {
            const selectElement = document.getElementById('{{ $id }}');
            if (selectElement) {
                // Initialize Select2 on the element
                $(selectElement).select2({
                    placeholder: '{{ $placeholder }}',
                    width: '100%',
                    allowClear: true,
                    theme: 'bootstrap-5',
                    @if ($modal) dropdownParent: $('#{{ $modal }}'), @endif
                    @if ($multi) multiple: true @endif
                }).on('change', (event) => {
                    const selectedValues = $(selectElement).val(); // Get selected values
                    @this.set('{{ $wireModel }}', selectedValues); // Update Livewire property with an array of selected values
                });
                {{-- if ('{{ $selectedValue }}') {
                $(selectElement).val('{{ $selectedValue }}').trigger('change'); // Set selected value and trigger change event
            } --}}
            }
        },

        // Function to destroy and reinitialize Select2 when Livewire updates
        destroyAndReinitialize() {
            const selectElement = document.getElementById('{{ $id }}');
            if (selectElement) {
                $(selectElement).select2('destroy').select2({
                    placeholder: '{{ $placeholder }}',
                    width: '100%',
                    allowClear: true,
                    theme: 'bootstrap-5',
                    @if($modal)
                    dropdownParent: $('#{{ $modal }}'),
                    @endif
                    @if($multi)
                    multiple: true
                    @endif
                }).on('change', (event) => {
                    const selectedValues = $(selectElement).val(); // Get selected values
                    @this.set('{{ $wireModel }}', selectedValues); // Update Livewire property with an array of selected values
                });
            }
        }
}" x-init="init()" x-on:livewire:update="destroyAndReinitialize()">


    <select id="{{ $id }}" name="{{ $name }}" wire:model="{{ $wireModel }}" multiple
        {{ $attributes->merge(['class' => 'form-select form-select-solid ' . ($errors->has($name) ? 'is-invalid' : '')]) }}
        data-control="select2" data-placeholder="{{ $placeholder }}" data-kt-search="true">
        {{ $slot }}
    </select>

</div>
