{{-- @formatter:off --}}
@component('mail::message')
<style>
    table:nth-of-type(1) th:nth-of-type(1) {
        width:20%;
    }
</style>

# Zoho Site Sync

ID: {{ $site->id  }}
Site: {{ $site->name  }}

**Zoho Data**

@foreach ($zoho as $key => $value)
{{$key}}: {{$value}}
@endforeach

**Difference**

@foreach ($diff as $key => $value)
{{$key}}: {{$value}}
@endforeach


@component('mail::button', ['url' => config('app.url').'/site/'.$site->id])
View Site
@endcomponent


Regards,<br>
{{ config('app.name') }}
@endcomponent
