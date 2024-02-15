{{-- @formatter:off --}}
@component('mail::message')
# Toolbox Talk Sign Off Request Rejected

The Toolbox Talk **{{ $talk->name }}** was rejected by {!! (Auth::check()) ? Auth::user()->fullname : 'NAME' !!} ({!! (Auth::check()) ? Auth::user()->company->name : 'COMPANY' !!}) and returned to draft mode.

Please review the Toolbox Talk and correct it.

If you have any questions in regards to the Toolbox Talk you may contact {{ $talk->owned_by->name }} on {{ $talk->owned_by->phone }}

@component('mail::button', ['url' => config('app.url').'/safety/doc/toolbox2/'.$talk->id])
View Talk
@endcomponent


Regards,
{{ config('app.name') }}
@endcomponent
