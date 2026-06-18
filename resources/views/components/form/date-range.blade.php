@props([
    'from',
    'to',
    'label' => null,
    'help' => null,
    'fromValue' => null,
    'toValue' => null,
    'format' => 'dd/mm/yyyy',
    'readonly' => true,
    'background' => '#FFF',
])

@php
    $fromValue = old($from, $fromValue);
    $toValue = old($to, $toValue);

    $fromId = $attributes->get('from-id', $from);
    $toId = $attributes->get('to-id', $to);
@endphp

<div class="form-group {{ $errors->has($from) || $errors->has($to) ? 'has-error' : '' }}">
    @if($label)
        <label for="{{ $fromId }}" class="control-label">
            {!! $label !!}

            @if($help)
                <a href="javascript:;" class="popovers" data-container="body" data-trigger="hover" data-content="{{ $help }}">
                    <i class="fa fa-question-circle font-grey-silver"></i>
                </a>
            @endif
        </label>

    @endif

    <div class="input-group date date-picker input-daterange" data-date-format="{{ $format }}" data-date-reset>
        <input
                type="text"
                name="{{ $from }}"
                id="{{ $fromId }}"
                value="{{ $fromValue }}"
                class="form-control"
                style="background:{{ $background }}"
                {{ $readonly ? 'readonly' : '' }}
        >

        <span class="input-group-addon"> to </span>

        <input
                type="text"
                name="{{ $to }}"
                id="{{ $toId }}"
                value="{{ $toValue }}"
                class="form-control"
                style="background:{{ $background }}"
                {{ $readonly ? 'readonly' : '' }}
        >
    </div>

    <x-form.error :name="$from"/>
    <x-form.error :name="$to"/>
</div>