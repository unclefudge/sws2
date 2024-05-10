{{-- @formatter:off --}}
@component('mail::message')
<style>
    table:nth-of-type(1) th:nth-of-type(1) {
        width:20%;
    }
</style>

# Maintenance Request Note

A note has been added to the Maintenance request (M{{$main->code}}) for {{ $main->site->name }}.

Maintenance Request assigned to: {{ ($main->super_id) ? $main->taskOwner->name : 'unassigned' }}

**Note**
{!! nl2br2($action->action); !!}


@component('mail::button', ['url' => config('app.url').'/site/maintenance/'.$main->id])
View Request
@endcomponent


Regards,<br>
{{ config('app.name') }}
@endcomponent
