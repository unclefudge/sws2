@props([
    'name' => 'filepond[]',
    'multiple' => true,
    'label' => null,
])

@if($label)
    <label for="{{ $attributes->get('id', $name) }}" class="control-label">{{ $label }}</label>
@endif

<input
        type="file"
        name="{{ $name }}"
        {{ $multiple ? 'multiple' : '' }}
        {{ $attributes->merge(['class' => 'filepond']) }}
>
