@component('mail::message')
<style>
    table:nth-of-type(1) th:nth-of-type(1) {
        width:20%;
    }
</style>

# Site Note

A note has been added for {{ $note->site->name }}. {!! ($note->response_req) ? "**Response Required**" : '' !!}

---

{!! nl2br2($note->notes)  !!}

---

Created by: {{ $note->createdBy->name }}

@component('mail::button', ['url' => config('app.url').'/site/'.$note->site_id])
View Site Notes
@endcomponent


Regards,<br>
{{ config('app.name') }}
@endcomponent
