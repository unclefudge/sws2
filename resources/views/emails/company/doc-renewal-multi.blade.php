@component('mail::message')
# Standard Detail Documents are due for renewal

{!! nl2br($docs) !!}

Please review the Standard Detail documents and arrange for them to be updated

@component('mail::button', ['url' => config('app.url').'/company/doc/standard/review'])
View Documents
@endcomponent


Regards,<br>
{{ config('app.name') }}
@endcomponent