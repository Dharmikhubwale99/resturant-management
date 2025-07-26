@props([
    'name' => null,
    'labelTitle' => null,
    'title' => '',
    'type' => 'button',
    'class' => 'bg-blue-600 hover:bg-blue-700 text-white',
    'href' => null,
    'route' => null,
    'wireClick' => null,
    'wireTarget' => null,
])

<div class="mb-4">
    @if (!empty($labelTitle))
        <label for="{{ $name ?? '' }}" class="block text-sm font-medium text-gray-700 mb-1">
            {{ $labelTitle }}
        </label>
    @endif

    @php
         $isLink = isset($href) || isset($route);

        if (is_array($route)) {
            $link = route(...$route);
        } else {
            $link = $href ?? ($route ? route($route) : '#');
        }

        $buttonClasses =
            'inline-flex items-center justify-center gap-2 px-4 py-2 rounded-md text-sm font-medium focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors ' .
            $class;
    @endphp

    @if ($isLink)
        <a href="{{ $link }}" class="{{ $buttonClasses }}">
            {{ $title }}
            {{ $slot }}
        </a>
    @else
        <button type="{{ $type }}" @if ($wireClick) wire:click="{{ $wireClick }}" @endif
            class="{{ $buttonClasses }}" {{ $attributes }}>
            {{ $title }}

            @if ($wireTarget)
                <span wire:loading wire:target="{{ $wireTarget }}">
                    <svg class="animate-spin h-4 w-4 text-white ml-2" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                            stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor"
                            d="M4 12a8 8 0 018-8v4l3.5-3.5L12 0v4a8 8 0 00-8 8z">
                        </path>
                    </svg>
                </span>
            @endif

            {{ $slot }}
        </button>

    @endif
</div>
