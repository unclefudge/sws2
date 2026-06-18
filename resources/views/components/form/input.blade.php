@props([
    'name',
    'label' => null,
    'help' => null,
    'value' => null,
    'type' => 'text',
    'readonly' => false,
])

@php
    $value = old($name, $value);
    $id = $attributes->get('id', $name);
@endphp

<div class="form-group {{ $errors->has($name) ? 'has-error' : '' }}">
    @if($label)
        <label for="{{ $id }}" class="control-label">
            {!! $label !!}

            @if($help)
                <a href="javascript:;" class="popovers" data-container="body" data-trigger="hover" data-content="{{ $help }}">
                    <i class="fa fa-question-circle font-grey-silver"></i>
                </a>
            @endif
        </label>
    @endif

    <input
            type="{{ $type }}"
            name="{{ $name }}"
            id="{{ $id }}"
            value="{{ $value }}"
            {{ $readonly ? 'readonly' : '' }}
            {{ $attributes->except('id')->merge(['class' => 'form-control']) }}
    >
    <x-form.error :name="$name"/>
</div>