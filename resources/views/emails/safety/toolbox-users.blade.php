@component('mail::message')
# Toolbox Talk Users Updated

Users updated for Toolbox Talk **{{ $talk->name }}**.

@if (count($added))
The following users were added:
@foreach ($added as $name => $company)
- {{$name}}
@endforeach
@endif

@if (count($deleted))
The following users were removed:
@foreach ($deleted as $name => $company)
- {{$name}}
@endforeach
@endif

@component('mail::button', ['url' => config('app.url').'/safety/doc/toolbox2/'.$talk->id])
View Talk
@endcomponent


Regards,<br>
{{ config('app.name') }}
@endcomponent
