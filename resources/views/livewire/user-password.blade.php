<div x-data="{ errorMessage: '', successMessage: '' }">
    <form wire:submit.prevent="submit">
        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input wire:model="password" id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <x-primary-button>
                Login
            </x-primary-button>
        </div>
    </form>

    <!-- Flash Messages (Error or Success) -->
    <div x-show="errorMessage" x-text="errorMessage" class="alert alert-danger mt-4" x-cloak></div>
    <div x-show="successMessage" x-text="successMessage" class="alert alert-success mt-4" x-cloak></div>
</div>

<script>
    window.addEventListener('force-page-reload', () => {
        setTimeout(() => {
            window.location.reload();
        }, 3000); 
    });
</script>

