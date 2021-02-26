@component('mail::message')
# Outstanding Privacy Polices

The following {{ $companies->count() }} companies don't have a current Privacy Policy:


@foreach ($companies as $company)
 * {{ $company->name }}
@endforeach

@component('mail::button', ['url' => config('app.url').'/manage/report/company_privacy'])
View Report
@endcomponent


Regards,<br>
{{ config('app.name') }}
@endcomponent
