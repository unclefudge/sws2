@props([
    'name',
    'label' => null,
    'checked' => false,

    // Customisation
    'onText' => 'Yes',
    'offText' => 'No',
    'onColor' => '#26a69a',
    'offColor' => '#e7505a',
])

@php
    $isChecked = old($name, $checked);
    $id = $attributes->get('id', $name);
@endphp

<div class="form-group">
    @if($label)
        <label class="control-label" for="{{ $id }}">{{ $label }}</label>
    @endif

    <div class="checkbox2-wrapper">
        <input
                type="checkbox"
                id="{{ $id }}"
                name="{{ $name }}"
                value="1"
                class="checkbox2-input"
                {{ $isChecked ? 'checked' : '' }}
        >

        <label
                class="checkbox2"
                for="{{ $id }}"
                style="
                --checkbox2-on-color: {{ $onColor }};
                --checkbox2-off-color: {{ $offColor }};
            "
        >
            <span class="checkbox2-option checkbox2-on">{{ $onText }}</span>
            <span class="checkbox2-option checkbox2-off">{{ $offText }}</span>
            <span class="checkbox2-handle"></span>
        </label>
    </div>

    @error($name)
    <span class="help-block text-danger">{{ $message }}</span>
    @enderror
</div>
