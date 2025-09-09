<x-layouts.investmentForum2025.app title="User Registration Verification">
    <section class="bg-white dark:bg-gray-900 space-y-10">
        <div class="py-8 px-4 mx-auto max-w-7xl lg:py-8 text-center">

            <h2 class="mb-6 text-2xl font-bold text-gray-900 dark:text-white">
                <strong>National Agri-Fishery Investment Forum User Registration Verification</strong>
            </h2>

            @if ($user)
            <p>
                This is to certify that <b>{{ $user->name }}</b> is registered in our system.
            </p>

            <div class="my-6 flex justify-center">
                <x-user-id :user="$user" :logo-src="$logoSrc" :user-image-src="$userImageSrc" />
            </div>

            <div class="flex justify-center">
                <div class="text-left">
                    <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-white">
                        User Information
                    </h3>
                    <p><b>Name:</b> {{ $user->name }}</p>
                    <p><b>Email:</b> {{ $user->email }}</p>
                    <p><b>Contact:</b> {{ $user->contact_number }}</p>
                    <p><b>Office:</b> {{ $user->office }}</p>
                    <p><b>Designation:</b> {{ $user->designation }}</p>
                </div>
            </div>

            @else
            <h2 class="mb-6 text-2xl font-bold text-gray-900 dark:text-white">
                User Not Found in our database.
            </h2>
            @endif
        </div>
    </section>
</x-layouts.investmentForum2025.app>