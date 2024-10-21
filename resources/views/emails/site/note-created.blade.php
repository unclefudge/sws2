{{-- @formatter:off --}}
@component('mail::message')
<style>
    table:nth-of-type(1) th:nth-of-type(1) {
        width:20%;
    }
</style>

# Site Note

A note has been added for {{ $note->site->name }}. {!! ($note->response_req) ? "**Response Required**" : '' !!}

{{-- 15. Costing fields --}}
@if ($note->category_id == '15')
Credit/Extra: {{ $note->costing_extra_credit }}<br>
New item/In Lie of: {{ $note->costing_item }}<br>
Priority: {{ $note->costing_priority }}<br>
Room: {{ $note->costing_room }}<br>
Location: {{ $note->costing_location }}<br>
Description:<br>
@endif
{{-- 16. Approved Variation, 19. For Issue to Client,  20. TBA Site Variations  --}}
@if (in_array($note->category_id, [16, 19, 20]))
Name: {{ $note->variation_name }}<br>
Description: {{ $note->variation_info }}<br>
@endif
{{-- 16. + 19. additional fields --}}
@if (in_array($note->category_id, [16, 19]))
Net Cost:  {{ $note->variation_net }}<br>
Gross  Cost:  {{ $note->variation_cost }}<br>
Credit/Extra: {{ $note->costing_extra_credit }}<br>
Total Extension Days: {{ $note->variation_days }}<br>
<br>Cost Centres & Item Details:<br>
@foreach ($note->costs as $cost)
    {{$cost->category->name}}: {{$cost->details}}<br>
@endforeach
@endif
{{-- 89. Prac Completion --}}
@if ($note->category_id == '89')
Prac Notified: {{ ($note->prac_notified) ? $note->prac_notified->format('d/m/Y') : '' }}<br>
Prac Meeting Date: {{ ($note->prac_meeting) ? $note->prac_meeting->format('d/m/Y') : '' }}<br>
Prac Meeting Time: {{ ($note->prac_meeting) ? $note->prac_meeting->format('h:i A') : '' }}<br>
@endif
{{-- Note for all categories except 15. --}}
@if ($note->category_id != '15')
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
