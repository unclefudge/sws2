@props([
    'name',
    'label' => null,
    'help' => null,
    'options' => null,
    'value' => null,
    'plugin' => 'bs-select',
    'placeholder' => null,
])

@php
    $isMultiple = $attributes->has('multiple');

    // For old input, Laravel stores multi-select old values under the base name.
    // So email_list[] should read old('email_list'), not old('email_list[]').
    $oldName = str_replace('[]', '', $name);

    $value = old($oldName, $value);

    $selectedValues = $isMultiple
        ? array_map('strval', (array) $value)
        : [(string) $value];

    $id = $attributes->get('id', str_replace('[]', '', $name));
    $defaultClass = trim('form-control ' . $plugin);

    $hasPlaceholder = $placeholder !== null && $placeholder !== '';
@endphp

<div class="form-group {{ $errors->has($oldName) ? 'has-error' : '' }}">
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

    <select
            name="{{ $name }}"
            id="{{ $id }}"

            @if($hasPlaceholder)
                data-placeholder="{{ $placeholder }}"
            @endif

            @if($hasPlaceholder && !$isMultiple)
                data-allow-clear="true"
            @endif

            {{ $attributes->except('id')->merge(['class' => $defaultClass]) }}
    >
        @if($hasPlaceholder && !$isMultiple)
            <option value="" {{ in_array('', $selectedValues, true) ? 'selected' : '' }}>
                {{ $placeholder }}
            </option>
        @endif

        @if(trim((string) $slot) !== '')
            {{ $slot }}
        @elseif(is_iterable($options))
            @foreach($options as $key => $text)
                {{-- Avoid duplicate blank option when placeholder is already being used --}}
                @if(!($hasPlaceholder && !$isMultiple && (string) $key === ''))
                    <option value="{{ $key }}" {{ in_array((string) $key, $selectedValues, true) ? 'selected' : '' }}>
                        {{ $text }}
                    </option>
                @endif
            @endforeach
        @endif
    </select>

    <x-form.error :name="$oldName"/>
</div>