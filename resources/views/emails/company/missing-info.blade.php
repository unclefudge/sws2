@component('mail::message')
# Missing Company Information

<h4>Contractors Licence, Workers Compensation, Sickness & Accident, Public Liability, Privacy Policy</h4>
<table class="table table-striped table-bordered table-hover order-column" id="table_list">
    <thead>
    <tr class="mytable-header">
        <th style="width: 5%; border: 1px solid"> #</th>
        <th style="width: 20%; border: 1px solid"> Name</th>
        <th style="border: 1px solid"> Missing Info / Document</th>
        <th style="width: 10%; border: 1px solid"> Expiry / Last Updated</th>
    </tr>
    </thead>
    <tbody>
    @foreach($expired_docs1 as $row)
        <tr>
            <td  style="border: 1px solid">
                @if ($row['date'] != 'never')
                    <div class="text-center"><a href="{!! $row['link'] !!}"><i class="fa fa-file-text-o"></i></a></div>
                @else
                    <div class="text-center"><a href="{!! $row['link'] !!}"><i class="fa fa-search"></i></a></div>
                @endif
            </td>
            <td style="border: 1px solid">{{ $row['company_name'] }} {!! $row['company_nickname'] !!}</td>
            <td style="border: 1px solid">{!! $row['data'] !!}</td>
            <td style="border: 1px solid">{!! $row['date']!!}</td>
        </tr>
    @endforeach
    </tbody>
</table>


<h4>Subcontractors Statement, Period Trade Contract</h4>
<table class="table table-striped table-bordered table-hover order-column" id="table_list">
    <thead>
    <tr class="mytable-header">
        <th style="width: 5%; border: 1px solid"> #</th>
        <th style="width: 20%; border: 1px solid"> Name</th>
        <th style="border: 1px solid"> Missing Info / Document</th>
        <th style="width: 10%; border: 1px solid"> Expiry / Last Updated</th>
    </tr>
    </thead>
    <tbody>
    @foreach($expired_docs2 as $row)
        <tr>
            <td style="border: 1px solid">
                @if ($row['date'] != 'never')
                    <div class="text-center"><a href="{!! $row['link'] !!}"><i class="fa fa-file-text-o"></i></a></div>
                @else
                    <div class="text-center"><a href="{!! $row['link'] !!}"><i class="fa fa-search"></i></a></div>
                @endif
            </td>
            <td style="border: 1px solid">{{ $row['company_name'] }} {!! $row['company_nickname'] !!}</td>
            <td style="border: 1px solid">{!! $row['data'] !!}</td>
            <td style="border: 1px solid">{!! $row['date']!!}</td>
        </tr>
    @endforeach
    </tbody>
</table>

<h4>Electrical Test & Tagging</h4>
<table class="table table-striped table-bordered table-hover order-column" id="table_list">
    <thead>
    <tr class="mytable-header">
        <th style="width: 5%; border: 1px solid"> #</th>
        <th style="width: 20%; border: 1px solid"> Name</th>
        <th style="border: 1px solid"> Missing Info / Document</th>
        <th style="width: 10%; border: 1px solid"> Expiry / Last Updated</th>
    </tr>
    </thead>
    <tbody>
    @foreach($expired_docs3 as $row)
        <tr>
            <td style="border: 1px solid">
                @if ($row['date'] != 'never')
                    <div class="text-center"><a href="{!! $row['link'] !!}"><i class="fa fa-file-text-o"></i></a></div>
                @else
                    <div class="text-center"><a href="{!! $row['link'] !!}"><i class="fa fa-search"></i></a></div>
                @endif
            </td>
            <td style="border: 1px solid">{{ $row['company_name'] }} {!! $row['company_nickname'] !!}</td>
            <td style="border: 1px solid">{!! $row['data'] !!}</td>
            <td style="border: 1px solid">{!! $row['date']!!}</td>
        </tr>
    @endforeach
    </tbody>
</table>

<h4>Missing Company Info (Phone, Address, Email, ABN, etc)</h4>
<table class="table table-striped table-bordered table-hover order-column" id="table_list">
    <thead>
    <tr class="mytable-header">
        <th style="width: 5%; border: 1px solid"> #</th>
        <th style="width: 20%; border: 1px solid"> Name</th>
        <th style="border: 1px solid"> Missing Info / Document</th>
        <th style="width: 10%; border: 1px solid"> Expiry / Last Updated</th>
    </tr>
    </thead>
    <tbody>
    @foreach($missing_info as $row)
        <tr>
            <td style="border: 1px solid">
                <div class="text-center"><a href="{!! $row['link'] !!}"><i class="fa fa-search"></i></a></div>
            </td>
            <td style="border: 1px solid">{{ $row['company_name'] }} {!! $row['company_nickname'] !!}</td>
            <td style="border: 1px solid">{!! $row['data'] !!}</td>
            <td style="border: 1px solid">{!! $row['date']!!}</td>
        </tr>
    </tbody>
    @endforeach
</table>

@component('mail::button', ['url' => config('app.url').'/manage/report/missing_company_info'])
View Report
@endcomponent


Regards,<br>
{{ config('app.name') }}
@endcomponent
