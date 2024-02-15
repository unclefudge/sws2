{{-- @formatter:off --}}
@component('mail::message')
# Company Period Trade Contract Not Approved

{{ $ptc->owned_by->name }} has not approved your contract for the following reason:

{!! nl2br($ptc->reject) !!}

Please review the contract and correct it.

If you have any questions in regards to the contract you may contact {{ $ptc->owned_by->name }} on {{ $ptc->owned_by->phone }}

@component('mail::button', ['url' => config('app.url').'/company/'.$ptc->for_company_id.'/doc/period-trade-contract/'.$ptc->id])
    View Contract
@endcomponent


Regards,
{{ config('app.name') }}
@endcomponent