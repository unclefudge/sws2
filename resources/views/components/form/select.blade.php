@props([
    'name',
    'label' => null,
    'options' => null,
    'value' => old($name),
])

<div class="form-group {{ $errors->has($name) ? 'has-error' : '' }}">
    @if($label)
        <label for="{{ $name }}" class="control-label">
            {{ $label }}
        </label>
    @endif

    <select
            name="{{ $name }}"
            id="{{ $name }}"
            {{ $attributes->merge(['class' => 'form-control']) }}
    >
        {{-- Slot-based options (legacy / Select2 helpers) --}}
        @if(trim($slot) !== '')
            {{ $slot }}

            {{-- Array-based options --}}
        @elseif(is_array($options))
            @foreach($options as $key => $text)
                <option value="{{ $key }}" {{ (string)$value === (string)$key ? 'selected' : '' }}>
                    {{ $text }}
                </option>
            @endforeach
        @endif
    </select>

    <x-form.error :name="$name"/>
</div>
