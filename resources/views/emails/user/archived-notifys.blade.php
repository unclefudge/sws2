{{-- @formatter:off --}}
@component('mail::message')
# Archived User

{{ $user->name }} ({{ $user->username  }}) has been archived by {{ $updated_by->name }} with the following notifications:


@foreach ($notifys as $notify)
     * {{ $notify }}
@endforeach


@component('mail::button', ['url' => config('app.url').'/user/'.$user->id])
     View User
@endcomponent


Regards,
{{ config('app.name') }}
@endcomponent
