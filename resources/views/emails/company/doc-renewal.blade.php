@component('mail::message')
# Company Document is due for renewal

**{{ $doc->name }}**

Please review the attached Standard Detail and arrange for it to be updated

@component('mail::button', ['url' => config('app.url').'/company/'.$doc->for_company_id.'/doc/'.$doc->id.'/edit'])
View Document
@endcomponent


Regards,<br>
{{ config('app.name') }}
@endcomponent