{{-- @formatter:off --}}
@component('mail::message')

# Asbestos Notifications Report

The following Asbestos Notifications are active.
<table style="padding:2px">
    <tr>
        <td><b>Site</b></td>
        <td><b>Proposed Removal</b></td>
        <td><b>Supervisor</b></td>
    </tr>
@foreach ($abs as $notice)
    <tr>
        <td>{{ $notice->site->name  }}</td>
        <td>{{ $notice->date_from->format('d M') }} - {{ $notice->date_to->format('d M') }}</td>
        <td>{{ $notice->site->supervisorName  }}</td>
    </tr>
    @endforeach
</table>

@component('mail::button', ['url' => config('app.url').'/site/asbestos/notification'])
View Asbestos Notifications
@endcomponent


Regards,
{{ config('app.name') }}
@endcomponent
