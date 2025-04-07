{{-- @formatter:off --}}
@component('mail::message')
# Safe Work Method Statements {{ ($outofdate == 'verify') ? 'Verification' : 'Out of Date' }}

<b>{{ $company->name }}</b>

@if ($outofdate == 'verify')
Please verify your Safe Work Method Statements are up to date on the SafeWork Site.
@elseif ($outofdate == 'none')
Please note you currently have no Safe Work Method Statements on the SafeWork Site.
@else
Please note the following documents are now expired on the SafeWork Site:

@foreach ($outofdate as $doc)
- {{ $doc }}
@endforeach
@endif

All Companies need to upload their own documents into the system, from a compliance side this is not something Cape Cod can do for you, please refer to the attached guide for you to self-manage this as part of your Company obligations.

Please have these items uploaded by the {!! today()->addDays(7)->format('d/m/Y') !!}

If you have any questions and need help please feel free to contact me.

@component('mail::button', ['url' => config('app.url').'/safety/doc/wms/'])
View SWMS
@endcomponent


Regards,<br>
{!! $signature !!}
@endcomponent
