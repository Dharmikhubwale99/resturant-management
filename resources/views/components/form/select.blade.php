@props([
    'name',
    'label' => null,
    'options' => [],
    'value' => null,
    'placeholder' => 'Select an option',
    'required' => false,
    'disabled' => false,
    'readonly' => false,
    'autocomplete' => null,
    'wrapperClass' => '',
    'inputClass' => '',
    'errorClass' => '',
    'wireModel' => null,
    'wireModelLive' => false,
])

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
        <select
            name="{{ $name }}"
            id="{{ $name }}"
            @if($required) required @endif
            @if($disabled) disabled @endif
            @if($readonly) readonly @endif
            @if($autocomplete) autocomplete="{{ $autocomplete }}" @endif

            @if($wireModel)
                wire:model="{{ $wireModel }}"
            @elseif($wireModelLive)
                wire:model.live.debounce.500ms="{{ $wireModelLive }}"
            @endif

            {{ $attributes->merge(['class' => 'w-full px-3.5 py-2.5 border border-gray-300 rounded-md text-sm focus:border-[oklch(76.9%_0.188_70.08)] focus:ring-[oklch(76.9%_0.188_70.08)] hover:border-[oklch(76.9%_0.188_70.08)] transition-colors ' . $inputClass]) }}
        >
            <option value="">{{ $placeholder }}</option>

            @foreach($options as $key => $label)
                <option value="{{ $key }}" {{ (old($name, $value) == $key) ? 'selected' : '' }}>
                    {{ $label }}
                </option>
            @endforeach
        </select>
    </div>

    @error($name)
        <p class="mt-1 text-sm text-red-600 {{ $errorClass }}">{{ $message }}</p>
    @enderror
</div>
