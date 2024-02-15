{{-- @formatter:off --}}
@component('mail::message')
# Company Document Not Approved

{{ $doc->owned_by->name }} has not approved your document **{{ $doc->name }}** for the following reason:

{!! nl2br($doc->reject) !!}


Please review the document and correct it.

If you have any questions in regards to the document you may contact {{ $doc->owned_by->name }} on {{ $doc->owned_by->phone }}

@component('mail::button', ['url' => config('app.url').'/company/'.$doc->for_company_id.'/doc/'.$doc->id.'/edit'])
View Document
@endcomponent


Regards,
{{ config('app.name') }}
@endcomponent