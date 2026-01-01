@props([
    'name',
    'label' => null,
    'checked' => false,

    // Customisation (optional)
    'onText' => 'Yes',
    'offText' => 'No',
    'onColor' => '#26a69a',   // green
    'offColor' => '#e7505a',  // red
])

@php
    $isChecked = old($name, $checked);
    $id = $attributes->get('id', $name);
@endphp

<div class="form-group">
    <div class="checkbox2-toggle-switch-wrapper">
        <input
                type="checkbox"
                id="{{ $id }}"
                name="{{ $name }}"
                value="1"
                class="checkbox2-toggle-switch-input"
                {{ $isChecked ? 'checked' : '' }}
        >

        <label
                class="checkbox2-toggle-switch"
                for="{{ $id }}"
                style="
                --toggle-on-color: {{ $onColor }};
                --toggle-off-color: {{ $offColor }};
            "
        >
            <span class="checkbox2-toggle-option toggle-on">{{ $onText }}</span>
            <span class="checkbox2-toggle-option toggle-off">{{ $offText }}</span>
            <span class="checkbox2-toggle-handle"></span>
        </label>
    </div>

    @if($label)
        <label class="control-label" for="{{ $id }}">{{ $label }}</label>
    @endif

    @error($name)
    <span class="help-block text-danger">{{ $message }}</span>
    @enderror
</div>
