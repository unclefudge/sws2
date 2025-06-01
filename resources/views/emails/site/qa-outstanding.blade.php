{{-- @formatter:off --}}
@component('mail::message')
# Outstanding & On Hold QA Checklists

Please find the attached reports for:

{{ $outQas->count() }} Outstanding QA's for the following Supervisors.
@foreach ($outSupers as $super_name => $count)
- {{ $super_name }} ({{ $count }})
@endforeach

{{ $holdQas->count() }} On Hold QA's for the following Supervisors.
@foreach ($holdSupers as $super_name => $count)
- {{ $super_name }} ({{ $count }} )
@endforeach

<br><br>
Regards,<br>
{{ config('app.name') }}
@endcomponent