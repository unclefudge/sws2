@props([
    'name',
    'label' => null,
    'help' => null,
    'rows' => 3,
    'value' => null,
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

    <textarea
            name="{{ $name }}"
            id="{{ $id }}"
            rows="{{ $rows }}"
        {{ $attributes->except('id')->merge(['class' => 'form-control']) }}
    >{{ $value }}</textarea>

    <x-form.error :name="$name"/>
</div>