@component('mail::message')
# Old Users

The following {{ $users->count() }} users have not logged into {{ config('app.name') }} and match the following criteria:
 * Belong to a Company Type 'On Site Trade' as either a) Subcontractor or b) Service Provider
 * Belong to a User Role of a) ext-leading-hand b) tradie c) labourers
 * It has been 3 months since last login but the company itself has been on the Planner in that time



<table style="border: 1px solid; border-collapse: collapse">
    <tr style="border: 1px solid; background-color: #F6F6F6; font-weight: bold;">
        <td width="150" style="border: 1px solid"> &nbsp; Name</td>
        <td width="250" style="border: 1px solid"> &nbsp; Comany</td>
        <td width="110" style="border: 1px solid"> &nbsp; Company On <br> &nbsp; Planner Last</td>
        <td width="110" style="border: 1px solid"> &nbsp; Last Login Date</td>
    </tr>
    @foreach ($users as $user)
        <tr>
            <td style="border: 1px solid"> &nbsp; {{ $user->fullname }}</td>
            <td style="border: 1px solid"> &nbsp; {{ $user->company->name }}</td>
            <td style="border: 1px solid"> &nbsp; {{ ($user->company->lastDateOnPlanner()) ? $user->company->lastDateOnPlanner()->format('d/m/Y') : 'Never' }}</td>
            <td style="border: 1px solid"> &nbsp; {{ ($user->last_login && $user->last_login->format('d/m/Y') != '30/11/-0001') ? $user->last_login->format('d/m/Y') : 'Never' }}</td>
        </tr>
    @endforeach
</table>
@component('mail::button', ['url' => config('app.url').'manage/report/users_lastlogin'])
View Report
@endcomponent


Regards,<br>
{{ config('app.name') }}
@endcomponent
