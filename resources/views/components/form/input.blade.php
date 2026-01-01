@props([
    'name',
    'label' => null,
    'value' => old($name),
    'type' => 'text',
    'readonly' => false,
])

<div class="form-group {{ $errors->has($name) ? 'has-error' : '' }}">
    @if($label)
        <label for="{{ $name }}" class="control-label">
            {{ $label }}
        </label>
    @endif

    <input
            type="{{ $type }}"
            name="{{ $name }}"
            id="{{ $name }}"
            value="{{ $value }}"
            {{ $readonly ? 'readonly' : '' }}
            {{ $attributes->merge(['class' => 'form-control']) }}
    >

    <x-form.error :name="$name"/>
</div>
