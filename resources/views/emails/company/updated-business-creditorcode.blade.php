@inject('companyTypes', 'App\Http\Utilities\CompanyTypes')
@inject('companyEntityTypes', 'App\Http\Utilities\CompanyEntityTypes')
@component('mail::message')
    # Company Updated Business Creditor Code

    {{ $company->name }} has updated their Creditor Code.

    |        |        |
    | ------:|--------|
    | **Name**  | {{ $company->name  }} |
    | **Business Entity**  | {{ ($company->business_entity) ? $companyEntityTypes::name($company->business_entity) : '-' }} |
    | **Category**  | {{ $companyTypes::name($company->category) }} |
    | **ABN**  | {{ $company->abn  }} |
    | **Creditor Code**  | {{ $company->creditor_code }} |
    | **Updated By** | {{ $company->updatedBy->name  }} |


    @component('mail::button', ['url' => config('app.url').'/company/'.$company->id])
        View Company
    @endcomponent

    Regards,<br>
    {{ config('app.name') }}
@endcomponent
