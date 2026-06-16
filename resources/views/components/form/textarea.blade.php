@props([
    'name',
    'label' => null,
    'rows' => 3,
    'value' => null,
])

@php
    $value = old($name, $value);
@endphp

<div class="form-group {{ $errors->has($name) ? 'has-error' : '' }}">
    @if($label)
        <label for="{{ $name }}" class="control-label">
            {{ $label }}
        </label>
    @endif

    <textarea
            name="{{ $name }}"
            id="{{ $name }}"
            rows="{{ $rows }}"
        {{ $attributes->merge(['class' => 'form-control']) }}
    >{{ $value }}</textarea>

    <x-form.error :name="$name"/>
</div>