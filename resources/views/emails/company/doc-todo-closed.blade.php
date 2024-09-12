{{-- @formatter:off --}}
@component('mail::message')
# Company Document ToDo Closed

Document: {{ $doc->name }}
Company: {{ $doc->company->name }}
Todo: {{ $todo->id }}

@component('mail::button', ['url' => config('app.url').'/company/'.$doc->for_company_id.'/doc'])
View Documents
@endcomponent


Regards,
{{ config('app.name') }}
@endcomponent