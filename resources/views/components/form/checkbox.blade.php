@props([
    'name',
    'label' => null,
    'value' => 1,
    'checked' => false,
])

<div class="form-group">
    <label>
        <input
                type="checkbox"
                name="{{ $name }}"
                value="{{ $value }}"
                {{ old($name, $checked) ? 'checked' : '' }}
                {{ $attributes }}
        >
        {{ $label }}
    </label>
</div>
