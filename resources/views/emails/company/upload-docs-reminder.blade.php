@component('mail::message')
# SafeWorksite Documents Required

{{ $company->name }} has outstanding documents that are required to be uploaded to SafeWorksite.

**Before you can perform any work you must upload all relevant WHS documents in order to comply with SafeWorksite policy and procedures.**

The following documents are required within 24hrs:
@foreach ($company->missingDocs() as $doc)
- {{ $doc }}
@endforeach

@component('mail::button', ['url' => config('app.url').'/company/'.$company->id.'/doc'])
    Upload Documents
@endcomponent

If you have any questions in regards to your sign up on the SafeWorksite, please contact Courtney using the email courtney@capecod.com.au or call on (02) 9849 4444

Regards,<br>
{{ config('app.name') }}
@endcomponent
