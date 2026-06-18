@props([
    'name',
    'value' => null,
    'useOld' => false,
])

@php
    $value = $useOld ? old($name, $value) : $value;
    $id = $attributes->get('id', $name);
@endphp

<input type="hidden" name="{{ $name }}" id="{{ $id }}" value="{{ $value }}" {{ $attributes->except('id') }}>