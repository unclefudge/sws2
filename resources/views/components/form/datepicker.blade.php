@props([
    'name',
    'label' => null,
    'help' => null,
    'value' => null,
    'format' => 'dd-mm-yyyy',
    'placeholder' => 'dd/mm/yyyy',
    'readonly' => false,
    'background' => '#FFF',
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

    <div class="input-group date date-picker">
        <input
                type="text"
                name="{{ $name }}"
                id="{{ $id }}"
                value="{{ $value }}"
                placeholder="{{ $placeholder }}"
                data-date-format="{{ $format }}"
                {{ $readonly ? 'readonly' : '' }}
                {{ $attributes->except('id')->merge([
                    'class' => 'form-control form-control-inline',
                    'style' => 'background:' . $background,
                ]) }}
        >
        <span class="input-group-btn">
            <button class="btn default date-set" type="button">
                <i class="fa fa-calendar"></i>
            </button>
        </span>
    </div>

    <x-form.error :name="$name"/>
</div>