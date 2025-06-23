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

        @error($name)
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
            class="w-full px-3.5 py-2.5 border border-gray-300 rounded-md text-sm box-border focus:border-[oklch(76.9%_0.188_70.08)] focus:ring-1 focus:ring-[oklch(76.9%_0.188_70.08)] hover:border-[oklch(76.9%_0.188_70.08)] transition-colors {{ $inputClass }}"
            rows="4"
        >{{ old($name, $value) }}</textarea>

        @if ($hint)
            <p class="mt-1 text-sm text-gray-500">{{ $hint }}</p>
        @endif

        @error($name)
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

        <div class="relative">
            <input
                type="{{ $type }}"
                name="{{ $name }}"
                id="{{ $name }}"
                @if($type !== 'file')
                    value="{{ old($name, $value) }}"
                @endif
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

                {{ $attributes->merge(['class' => 'w-full px-3.5 py-2.5 border border-gray-300 rounded-md text-sm box-border focus:border-[oklch(76.9%_0.188_70.08)] focus:ring-1 focus:ring-[oklch(76.9%_0.188_70.08)] hover:border-[oklch(76.9%_0.188_70.08)] transition-colors ' . $inputClass]) }}
            />
        </div>

        @if ($hint)
            <p class="mt-1 text-sm text-gray-500">{{ $hint }}</p>
        @endif

        @error($name)
            <p class="mt-1 text-sm text-red-600 {{ $errorClass }}">{{ $message }}</p>
        @enderror
    </div>
@endif
