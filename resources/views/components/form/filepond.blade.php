@props([
    'name' => 'filepond[]',
    'multiple' => true,
])

<input
        type="file"
        name="{{ $name }}"
        {{ $multiple ? 'multiple' : '' }}
        {{ $attributes->merge(['class' => 'filepond']) }}
>
