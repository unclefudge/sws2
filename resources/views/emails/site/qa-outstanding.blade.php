{{-- @formatter:off --}}
@component('mail::message')
# Outstanding & On Hold QA Checklists

Please find the attached reports for:

{{ $outQas->count() }} Outstanding QA's for the following Supervisors.
@foreach ($outSupers as $super_id => $super_name)
- {{ $super_name }}
@endforeach

{{ $holdQas->count() }} On Hold QA's for the following Supervisors.
@foreach ($holdSupers as $super_id => $super_name)
- {{ $super_name }}
@endforeach

<br><br>
Regards,<br>
{{ config('app.name') }}
@endcomponent