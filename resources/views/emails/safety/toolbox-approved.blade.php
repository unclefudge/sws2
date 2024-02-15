{{-- @formatter:off --}}
@component('mail::message')
# Toolbox Talk Approved

The Toolbox Talk **{{ $talk->name }}** was approved by {!! (Auth::check()) ? Auth::user()->fullname : 'NAME' !!} ({!! (Auth::check()) ? Auth::user()->company->name : 'COMPANY' !!}).

You can now assign users and proceed with the Toolbox Talk.


@component('mail::button', ['url' => config('app.url').'/safety/doc/toolbox2/'.$talk->id])
View Talk
@endcomponent


Regards,
{{ config('app.name') }}
@endcomponent
