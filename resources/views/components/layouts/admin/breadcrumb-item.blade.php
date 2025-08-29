@props([
    'link' => '#',
    'icon' => '', // Optional SVG icon as string or a component.
    'title' => '',
    'active' => false,
    'wireNavigate' => false,
])

<li class="inline-flex items-center">
    @if (!$active)
        <a
            @if ($wireNavigate)
                wire:navigate
            @endif
            href="{{ $link }}"
            class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-primary-600 dark:text-gray-400 dark:hover:text-white"
        >
            @if ($icon)
                <x-dynamic-component :component="$icon" class="me-2 h-4 w-4" />
            @endif
            {{ $title }}
        </a>
    @else
        <span class="inline-flex items-center text-sm font-medium text-gray-500 dark:text-gray-400">
            @if ($icon)
                <x-dynamic-component :component="$icon" class="me-2 h-4 w-4" />
            @endif
            {{ $title }}
        </span>
    @endif
</li>
