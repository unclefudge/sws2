@include('emails/_email-begin')

<table class="v1inner-body" align="center" width="90%" cellpadding="0" cellspacing="0" style="background-color: #ffffff; margin: 0 auto; padding: 0; width: 90%;">
    <tr>
        <td class="v1content-cell" style="padding: 35px">
            <h1>Missing Company Information</h1>

            <h4>Contractors Licence, Workers Compensation, Sickness & Accident, Public Liability, Privacy Policy</h4>
            <table style="border: 1px solid; border-collapse: collapse">
                <tr style="border: 1px solid; background-color: #F6F6F6; font-weight: bold;">
                    <th style="border: 1px solid"> Name</th>
                    <th style="width: 20%; border: 1px solid"> Missing Info / Document</th>
                    <th style="width: 10%; border: 1px solid"> Expiry / Last Updated</th>
                </tr>
                @foreach($expired_docs1 as $row)
                    <tr>
                        <td style="border: 1px solid">{{ $row['company_name'] }} {!! $row['company_nickname'] !!}</td>
                        <td style="border: 1px solid">{!! $row['data'] !!}</td>
                        <td style="border: 1px solid">{!! $row['date']!!}</td>
                    </tr>
                @endforeach
            </table>


            <h4>Subcontractors Statement, Period Trade Contract</h4>
            <table style="border: 1px solid; border-collapse: collapse">
                <tr style="border: 1px solid; background-color: #F6F6F6; font-weight: bold;">
                    <th style="border: 1px solid"> Name</th>
                    <th style="width: 20%; border: 1px solid"> Missing Info / Document</th>
                    <th style="width: 10%; border: 1px solid"> Expiry / Last Updated</th>
                </tr>
                @foreach($expired_docs2 as $row)
                    <tr>
                        <td style="border: 1px solid">{{ $row['company_name'] }} {!! $row['company_nickname'] !!}</td>
                        <td style="border: 1px solid">{!! $row['data'] !!}</td>
                        <td style="border: 1px solid">{!! $row['date']!!}</td>
                    </tr>
                @endforeach
            </table>

            <h4>Electrical Test & Tagging</h4>
            <table style="border: 1px solid; border-collapse: collapse">
                <tr style="border: 1px solid; background-color: #F6F6F6; font-weight: bold;">
                    <th style="border: 1px solid"> Name</th>
                    <th style="width: 20%; border: 1px solid"> Missing Info / Document</th>
                    <th style="width: 10%; border: 1px solid"> Expiry / Last Updated</th>
                </tr>
                @foreach($expired_docs3 as $row)
                    <tr>
                        <td style="border: 1px solid">{{ $row['company_name'] }} {!! $row['company_nickname'] !!}</td>
                        <td style="border: 1px solid">{!! $row['data'] !!}</td>
                        <td style="border: 1px solid">{!! $row['date']!!}</td>
                    </tr>
                @endforeach
            </table>

            <h4>Missing Company Info (Phone, Address, Email, ABN, etc)</h4>
            <table style="border: 1px solid; border-collapse: collapse">
                <tr style="border: 1px solid; background-color: #F6F6F6; font-weight: bold;">
                    <th style="border: 1px solid"> Name</th>
                    <th style="width: 20%; border: 1px solid"> Missing Info / Document</th>
                    <th style="width: 10%; border: 1px solid"> Expiry / Last Updated</th>
                </tr>
                @foreach($missing_info as $row)
                    <tr>
                        <td style="border: 1px solid">{{ $row['company_name'] }} {!! $row['company_nickname'] !!}</td>
                        <td style="border: 1px solid">{!! $row['data'] !!}</td>
                        <td style="border: 1px solid">{!! $row['date']!!}</td>
                    </tr>
                @endforeach
            </table>
            <a href="config('app.url').'/manage/report/missing_company_info'" class="btn"></a>
            <hr>
            <p>Regards,<br/>SafeWorksite</p>
        </td>
    </tr>
</table>

@include('emails/_email-end')
