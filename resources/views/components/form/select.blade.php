@props([
    'name',
    'label' => null,
    'options' => null,
    'value' => null,
    'plugin' => 'bs-select',
])

@php
    $value = old($name, $value);
    $defaultClass = trim('form-control ' . $plugin);
@endphp

<div class="form-group {{ $errors->has($name) ? 'has-error' : '' }}">
    @if($label)
        <label for="{{ $name }}" class="control-label">
            {{ $label }}
        </label>
    @endif

    <select
            name="{{ $name }}"
            id="{{ $name }}"
            {{ $attributes->merge(['class' => $defaultClass]) }}
    >
        @if(trim($slot) !== '')
            {{ $slot }}
        @elseif(is_array($options))
            @foreach($options as $key => $text)
                <option value="{{ $key }}" {{ (string) $value === (string) $key ? 'selected' : '' }}>
                    {{ $text }}
                </option>
            @endforeach
        @endif
    </select>

    <x-form.error :name="$name"/>
</div>