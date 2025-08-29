<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Investment Forum 2025' }}</title>
    @stack('styles')
    {{-- @vite('resources/css/app.css') --}}
    {{-- @vite('resources/js/app.js') --}}


    @vite(['resources/css/app.css', 'resources/js/app.js']) 


    @livewireStyles

    {{-- ✅ Flowbite --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.js"></script>
</head>


<body class="bg-gray-50 ">
    <div class="min-h-screen flex flex-col">
        <header>
            <nav class="bg-white border-gray-200 px-4 lg:px-6 py-2.5 dark:bg-gray-800">
                <div class="flex flex-wrap justify-between items-center mx-auto max-w-screen-xl">
                    <!-- Logo -->
                    <a href="#" class="flex items-center">
                        <img src="{{ asset('media/prdp-logo.png') }}" class="h-10 sm:h-12 mr-3" alt="PRDP Logo" />
                        <span class="text-lg sm:text-xl md:text-2xl font-semibold text-gray-900 dark:text-white">
                            National Agri-Fishery Investment Forum
                        </span>
                    </a>
                    <div class="hidden lg:flex lg:items-center lg:order-2">

                        @if (!Auth::guard('geomapping')->check())
                            <!-- Desktop Log in button -->
                            <a href="{{ route('geomapping.iplan.login') }}"
                                class="text-gray-800 dark:text-white hover:bg-gray-50 focus:ring-4 focus:ring-gray-300 font-medium rounded-lg text-sm px-4 lg:px-5 py-2 lg:py-2.5 dark:hover:bg-gray-700 focus:outline-none dark:focus:ring-gray-800">
                                Log in
                            </a>
                        @else
                            @auth('geomapping')
                                <livewire:geomapping.iplan.logout-2 />
                            @endauth
                        @endif
                    </div>


                    <!-- Mobile menu button -->
                    <div class="flex items-center lg:hidden">
                        <button data-collapse-toggle="mobile-menu-2" type="button"
                            class="inline-flex items-center p-2 ml-1 text-sm text-gray-500 rounded-lg hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-200 dark:text-gray-400 dark:hover:bg-gray-700 dark:focus:ring-gray-600"
                            aria-controls="mobile-menu-2" aria-expanded="false">
                            <span class="sr-only">Open main menu</span>
                            <!-- Hamburger icon -->
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M3 5h14v2H3V5zm0 4h14v2H3V9zm0 4h14v2H3v-2z"
                                    clip-rule="evenodd"></path>
                            </svg>
                        </button>
                    </div>

                    <!-- Mobile Menu -->
                    <div class="hidden w-full lg:flex lg:w-auto lg:order-1" id="mobile-menu-2">
                        <ul class="flex flex-col mt-4 font-medium lg:flex-row lg:space-x-8 lg:mt-0">
                            <li class="lg:hidden">

                                <!-- Log in inside mobile menu -->
                                @if (!Auth::guard('geomapping')->check())
                                    <a href="#"
                                        class="block w-full text-center py-2 px-4 text-gray-800 dark:text-white bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600">
                                        Log in
                                    </a>
                                @else
                                    @auth('geomapping')
                                        <livewire:geomapping.iplan.logout-3 />
                                    @endauth
                                @endif
                            </li>

                        </ul>
                    </div>
                </div>
            </nav>
        </header>

        <main class="flex-grow w-full px-6 py-6">
            {{ $slot }}
        </main>

        {{-- ✅ Footer --}}
        <footer class="bg-white dark:bg-gray-900 border-t border-gray-200 dark:border-gray-700 py-8">
            <div class="max-w-screen-xl mx-auto px-4 text-center">
                <!-- Logo -->
                <div class="flex justify-center mb-4">
                    <a href="#">
                        <img src="{{ asset('media/Scale-Up.png') }}" alt="Scale-Up Logo" class="h-20 w-auto">
                    </a>
                </div>

                <!-- Copyright -->
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    © {{ date('Y') }}
                    <a href="#" class="font-medium text-gray-700 dark:text-white hover:underline">
                        Philippine Rural Development Project
                    </a>.
                    All Rights Reserved.
                </p>

                <!-- Optional social icons or links (you can add here if needed) -->
                <!--
        <div class="mt-4 flex justify-center space-x-6">
            <a href="#" class="text-gray-400 hover:text-gray-900 dark:hover:text-white">Facebook</a>
            <a href="#" class="text-gray-400 hover:text-gray-900 dark:hover:text-white">Twitter</a>
        </div>
        -->
            </div>
        </footer>
    </div>

    @stack('modals')


    @livewireScripts
<script src="https://code.jquery.com/jquery-3.7.1.min.js" crossorigin="anonymous"></script>

    @stack('scripts')
</body>

</html>
