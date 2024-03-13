{{-- @formatter:off --}}
@component('mail::message')
<style>
    table:nth-of-type(1) th:nth-of-type(1) {
        width:20%;
    }
</style>

# Site Note

A note has been added for {{ $note->site->name }}. {!! ($note->response_req) ? "**Response Required**" : '' !!}

{{-- Costing fields --}}
@if ($note->category_id == '15')
Credit/Extra: {{ $note->costing_extra_credit }}<br>
New item/In Lie of: {{ $note->costing_item }}<br>
Priority: {{ $note->costing_priority }}<br>
Room: {{ $note->costing_room }}<br>
Location: {{ $note->costing_location }}<br>
Description:<br>
@elseif ($note->category_id == '16')
{{-- Variation fields --}}
Name: {{ $note->variation_name }}<br>
Description: {{ $note->variation_info }}<br>
Cost:  {{ $note->variation_cost }}<br>
Total Extension Days: {{ $note->variation_days }}<br>
Variation Breakup/Work Order Details:<br>
@else
Note:<br>
@endif
{!! nl2br2($note->notes)  !!}

---

Created by: {{ $note->createdBy->name }}

@component('mail::button', ['url' => config('app.url').'/site/'.$note->site_id])
    View Site Notes
@endcomponent


Regards,
{{ config('app.name') }}
@endcomponent
