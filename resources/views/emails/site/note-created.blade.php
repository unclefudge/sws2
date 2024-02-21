{{-- @formatter:off --}}
@component('mail::message')
<style>
    table:nth-of-type(1) th:nth-of-type(1) {
        width:20%;
    }
</style>

# Site Note

A note has been added for {{ $note->site->name }}. {!! ($note->response_req) ? "**Response Required**" : '' !!}

{{-- Variation fields --}}
@if ($note->category_id == '16')
Credit/Extra: {{ $note->costing_extra_credit }}
New item/In Lie of: {{ $note->costing_item }}
Room: {{ $note->costing_room }}
Location: {{ $note->costing_location }}
Description:
@elseif
{{-- Variation fields --}}
Name: {{ $note->variation_name }}
Description: {{ $note->variation_info }}
Cost:  {{ $note->variation_cost }}
Total Extension Days: {{ $note->variation_days }}
Variation Breakup/Work Order Details:
@else
Note:
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
