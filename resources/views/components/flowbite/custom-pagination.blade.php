@props(['paginator'])

@if ($paginator->hasPages())

    <nav class="flex flex-col md:flex-row justify-between items-start md:items-center space-y-3 md:space-y-0 p-4"
        aria-label="Table navigation">
        <nav role="navigation" aria-label="Pagination"
            class="flex items-center justify-between space-y-4 sm:space-y-0 sm:space-x-4 flex-wrap">
            {{-- Page Info --}}
            <span class="text-sm font-normal text-gray-500 dark:text-gray-400 order-last sm:order-first">
                Showing <span class="font-semibold text-gray-900 dark:text-white">{{ $paginator->firstItem() }}</span>
                to <span class="font-semibold text-gray-900 dark:text-white">{{ $paginator->lastItem() }}</span>
                of <span class="font-semibold text-gray-900 dark:text-white">{{ $paginator->total() }}</span>
                results
            </span>

            {{-- Page Links --}}
            <ul class="flex items-center space-x-1 order-first sm:order-last">
                {{-- Previous Button --}}
                @if ($paginator->onFirstPage())
                    <li aria-disabled="true">
                        <span
                            class="flex items-center justify-center h-full py-1.5 px-3 text-gray-300 bg-gray-200 dark:bg-gray-700 dark:text-gray-500 rounded-l-lg cursor-not-allowed">
                            <span class="sr-only">Previous</span>
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"
                                xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd"
                                    d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z"
                                    clip-rule="evenodd" />
                            </svg>
                        </span>
                    </li>
                @else
                    <li>
                        <button wire:click="previousPage" rel="prev"
                            class="flex items-center justify-center h-full py-1.5 px-3 bg-white border border-gray-300 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400 rounded-l-lg hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-gray-700 dark:hover:text-white">
                            <span class="sr-only">Previous</span>
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"
                                xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd"
                                    d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z"
                                    clip-rule="evenodd" />
                            </svg>
                        </button>
                    </li>
                @endif

                {{-- Page Numbers --}}
                @foreach ($paginator->links()->elements as $element)
                    {{-- Dots --}}
                    @if (is_string($element))
                        <li class="text-gray-500 dark:text-gray-400 px-2">...</li>
                    @endif

                    {{-- Page Links --}}
                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <li aria-current="page">
                                    <span
                                        class="flex items-center justify-center py-2 px-3 text-sm font-semibold bg-blue-500 text-white border border-blue-600 rounded">{{ $page }}</span>
                                </li>
                            @else
                                <li>
                                    <button wire:click="gotoPage({{ $page }})"
                                        class="flex items-center justify-center py-2 px-3 text-sm text-gray-500 bg-white border border-gray-300 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400 rounded hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-gray-700 dark:hover:text-white">
                                        {{ $page }}
                                    </button>
                                </li>
                            @endif
                        @endforeach
                    @endif
                @endforeach

                {{-- Next Button --}}
                @if ($paginator->hasMorePages())
                    <li>
                        <button wire:click="nextPage" rel="next"
                            class="flex items-center justify-center h-full py-1.5 px-3 bg-white border border-gray-300 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400 rounded-r-lg hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-gray-700 dark:hover:text-white">
                            <span class="sr-only">Next</span>
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"
                                xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd"
                                    d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                                    clip-rule="evenodd" />
                            </svg>
                        </button>
                    </li>
                @else
                    <li aria-disabled="true">
                        <span
                            class="flex items-center justify-center h-full py-1.5 px-3 text-gray-300 bg-gray-200 dark:bg-gray-700 dark:text-gray-500 rounded-r-lg cursor-not-allowed">
                            <span class="sr-only">Next</span>
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"
                                xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd"
                                    d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                                    clip-rule="evenodd" />
                            </svg>
                        </span>
                    </li>
                @endif
            </ul>
        </nav>
    </nav>

@endif
