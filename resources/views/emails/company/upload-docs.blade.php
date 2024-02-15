@component('mail::message')
    # SafeWorksite Account Created

    {{ $company->name }} has been created and made active on SafeWorksite.

    Before you can perform any work you must upload all relevant WHS documents in order to comply with SafeWorksite policy and procedures.

    The following documents are required within 48hrs:
    @foreach ($company->compliantDocs() as $doc)
        - {{ $doc }}
    @endforeach

    @component('mail::button', ['url' => config('app.url').'/company/'.$company->id.'/doc'])
        Upload Documents
    @endcomponent

    If you have any questions in regards to your sign up on the SafeWorksite, please email accounts1@capecod.com.au or call (02) 9849 4444 and ask to speak to our accounts team.

    Regards,<br>
    {{ config('app.name') }}
@endcomponent
