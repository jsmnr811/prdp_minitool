@props([
    'id',
    'name',
    'wireModel',
    'modal' => null,
    'placeholder' => 'Select an item',
    'multi' => false,
    'enable' => true,
])

<div wire:ignore x-data="{
    init() {
            const selectElement = document.getElementById('{{ $id }}');
            if (selectElement) {
                // Initialize Select2 on the element
                $(selectElement).select2({
                    placeholder: '{{ $placeholder }}',
                    width: '100%',
                    theme: 'bootstrap-5',
                    allowClear: true,
                    @if ($multi) multiple: true, @endif
                    @if ($modal) dropdownParent: $('#{{ $modal }}'), @endif
                }).on('change', (event) => {
                    const selectedValue = event.target.value;
                    @this.set('{{ $wireModel }}', selectedValue); // Update Livewire property
                });

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
                    @if($multi)
                    multiple: true,
                    @endif
                    @if($modal)
                    dropdownParent: $('#{{ $modal }}'),
                    @endif
                }).on('change', (event) => {
                    const selectedValue = event.target.value;
                    @this.set('{{ $wireModel }}', selectedValue);
                });
            }
        }
}" x-init="init()" x-on:livewire:update="destroyAndReinitialize()">

    <!-- The Select2 Dropdown -->
    <select id="{{ $id }}" name="{{ $name }}" @if (!$enable) disabled @endif
        wire:model="{{ $wireModel }}" {{ $multi ? 'multiple' : '' }}
        {{ $attributes->merge(['class' => 'form-select search-input ' . ($errors->has($name) ? 'is-invalid' : '')]) }}
        data-control="select2" data-placeholder="{{ $placeholder }}">
        <option></option>
        {{ $slot }}
    </select>


</div>
