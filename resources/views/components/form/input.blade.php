@props([
    'name',
    'label' => null,
    'type' => 'text',
    'value' => null,
    'placeholder' => '',
    'required' => false,
    'disabled' => false,
    'readonly' => false,
    'autocomplete' => null,
    'wrapperClass' => '',
    'inputClass' => '',
    'errorClass' => '',
    'wireModel' => null,
    'wireModelLive' => false,
    'hint' => null,
    'showToggle' => false,
])

@if($type === 'checkbox')
    <div class="{{ $wrapperClass }} mb-4">
        <div class="flex items-center gap-2">
            <input
                type="checkbox"
                name="{{ $name }}"
                id="{{ $name }}"
                @if($required) required @endif
                @if($disabled) disabled @endif
                @if($readonly) readonly @endif
                @if($wireModel)
                    wire:model="{{ $wireModel }}"
                @elseif($wireModelLive)
                    wire:model.live.debounce.500ms="{{ $wireModelLive }}"
                @endif
                {{ $attributes->merge(['class' => $inputClass]) }}
            />
            @if($label)
                <label for="{{ $name }}" class="text-sm text-gray-700">
                    {{ $label }}
                    @if($required)
                        <span class="text-red-500">*</span>
                    @endif
                </label>
            @endif
        </div>

        @if ($hint)
            <p class="mt-1 text-sm text-gray-500">{{ $hint }}</p>
        @endif

        @php $errorKey = $wireModel ?: ($wireModelLive ?: $name); @endphp

        @error($errorKey)
        <p class="mt-1 text-sm text-red-600 {{ $errorClass }}">{{ $message }}</p>
        @enderror
    </div>
@elseif($type === 'textarea')
    <div class="{{ $wrapperClass }} mb-4">
        @if($label)
            <label for="{{ $name }}" class="block text-sm font-medium text-gray-700 mb-1">
                {{ $label }}
                @if($required)
                    <span class="text-red-500">*</span>
                @endif
            </label>
        @endif

        <textarea
            name="{{ $name }}"
            id="{{ $name }}"
            placeholder="{{ $placeholder }}"
            @if($required) required @endif
            @if($disabled) disabled @endif
            @if($readonly) readonly @endif
            @if($autocomplete) autocomplete="{{ $autocomplete }}" @endif
            @if($wireModel)
                wire:model="{{ $wireModel }}"
            @elseif($wireModelLive)
                wire:model.live.debounce.500ms="{{ $wireModelLive }}"
            @endif
            class="w-full px-3.5 py-2.5 border border-gray-300 rounded-md text-sm box-border focus:border-blue-500 focus:ring-1 focus:ring-blue-500 hover:border-blue-500 transition-colors {{ $inputClass }}"
            rows="4"
        >{{ old($name, $value) }}</textarea>

        @if ($hint)
            <p class="mt-1 text-sm text-gray-500">{{ $hint }}</p>
        @endif

        @php $errorKey = $wireModel ?: ($wireModelLive ?: $name); @endphp

        @error($errorKey)
        <p class="mt-1 text-sm text-red-600 {{ $errorClass }}">{{ $message }}</p>
        @enderror
    </div>
@elseif($type === 'file')
    <div class="{{ $wrapperClass }} mb-4">
        @if($label)
            <label for="{{ $name }}" class="block text-sm font-medium text-gray-700 mb-1">
                {{ $label }}
                @if($required)
                    <span class="text-red-500">*</span>
                @endif
            </label>
        @endif

        <input
            type="file"
            name="{{ $name }}"
            id="{{ $name }}"
            @if($wireModel)
                wire:model="{{ $wireModel }}"
            @elseif($wireModelLive)
                wire:model.live="{{ $wireModelLive }}"
            @endif
            @if($required) required @endif
            @if($disabled) disabled @endif
            @if($readonly) readonly @endif
            {{ $attributes->merge(['class' => 'w-full px-3.5 py-2.5 border border-gray-300 rounded-md text-sm box-border focus:border-blue-500 focus:ring-1 focus:ring-blue-500 hover:border-blue-500 transition-colors ' . $inputClass]) }}
        />

        @if ($hint)
            <p class="mt-1 text-sm text-gray-500">{{ $hint }}</p>
        @endif

        @php $errorKey = $wireModel ?: ($wireModelLive ?: $name); @endphp

        @error($errorKey)
          <p class="mt-1 text-sm text-red-600 {{ $errorClass }}">{{ $message }}</p>
        @enderror

    </div>
@else
    <div class="{{ $wrapperClass }} mb-4">
        @if($label)
            <label for="{{ $name }}" class="block text-sm font-medium text-gray-700 mb-1">
                {{ $label }}
                @if($required)
                    <span class="text-red-500">*</span>
                @endif
            </label>
        @endif

        <div x-data="{ show: false }" class="relative">
            <input
                :type="{{ $showToggle && $type === 'password' ? 'show ? \'text\' : \'password\'' : "'{$type}'" }}"
                name="{{ $name }}"
                id="{{ $name }}"
                value="{{ old($name, $value) }}"
                placeholder="{{ $placeholder }}"
                @if($required) required @endif
                @if($disabled) disabled @endif
                @if($readonly) readonly @endif
                @if($autocomplete) autocomplete="{{ $autocomplete }}" @endif
                @if($wireModel)
                    wire:model="{{ $wireModel }}"
                @elseif($wireModelLive)
                    wire:model.live.debounce.500ms="{{ $wireModelLive }}"
                @endif
                {{ $attributes->merge(['class' => 'w-full px-3.5 py-2.5 border border-gray-300 rounded-md text-sm box-border focus:border-blue-500 focus:ring-1 focus:ring-blue-500 hover:border-blue-500 transition-colors ' . $inputClass]) }}
            />

            @if($showToggle && $type === 'password')
                <button type="button"
                    class="absolute right-3 top-2.5 text-gray-600"
                    @click="show = !show"
                    tabindex="-1">
                    <svg x-show="!show" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                         viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    <svg x-show="show" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                         viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M13.875 18.825A10.05 10.05 0 0112 19c-4.477 0-8.268-2.943-9.542-7a9.956 9.956 0 011.308-2.572m1.714-2.13A9.953 9.953 0 0112 5c4.477 0 8.268 2.943 9.542 7a9.961 9.961 0 01-1.246 2.58M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M3 3l18 18" />
                    </svg>
                </button>
            @endif
        </div>

        @if ($hint)
            <p class="mt-1 text-sm text-gray-500">{{ $hint }}</p>
        @endif

        @php $errorKey = $wireModel ?: ($wireModelLive ?: $name); @endphp

        @error($errorKey)
          <p class="mt-1 text-sm text-red-600 {{ $errorClass }}">{{ $message }}</p>
        @enderror

    </div>
@endif
