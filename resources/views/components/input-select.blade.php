@props(['name' => '', 'id' => '', 'options' => [], 'default' => ''])

<select
        @if ($id)
            id="{{$id}}"
        @endif
        @if ($name)
            name="{{$name}}"
        @if (!$id)
            id="{{$name}}"
        @endif
        @endif

        {!! $attributes->merge(['class' => 'form-control']) !!}
>
    @foreach($options as $key => $value)
        <option value="{{ $key }}" {{ ($key == $default) ? 'selected' : '' }}>{{ $value }}</option>
    @endforeach
</select>

{{--}}<select class=" select2-hidden-accessible" id="site_id" name="site_id" tabindex="-1" aria-hidden="true"></select>--}}