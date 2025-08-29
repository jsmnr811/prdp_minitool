@props([
    'link' => '#',
    'icon' => '',
    'title' => '',
    'active' => false,
    'wireNavigate' => false,
])

<li>
    <a href="{{ $link }}" @if ($wireNavigate) wire:navigate @endif
        class="flex items-center p-2 text-base font-medium text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group"
        {{ $active ? 'class=bg-gray-100 text-gray-900 dark:bg-gray-700 dark:text-white' : '' }}>
        {{ $icon }}
        <span class="ml-3">{{ $title }}</span>
    </a>
</li>
