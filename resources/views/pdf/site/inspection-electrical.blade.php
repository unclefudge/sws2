<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Electrical Inspection Report</title>
    <link href="{{ asset('/') }}/assets/global/plugins/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css"/>
    <link href="{{ asset('/') }}/assets/global/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css"/>
    <style>
        @import url(http://fonts.googleapis.com/css?family=PT+Sans);
        /*@import url(https://fonts.googleapis.com/css?family=Martel+Sans);*/

        @page {
            margin: .7cm .7cm
        }

        body, h1, h2, h3, h4, h5, h6 {
            font-family: 'PT Sans', serif;
        }

        h1 {
            /*font-family: 'Martel Sans', sans-serif;*/
            font-weight: 700;
        }

        body {
            font-size: 10px;
        }

        div.page {
            page-break-after: always;
            page-break-inside: avoid;
        }

        .row-striped:nth-of-type(odd) {
            background-color: #ffffff;
        }

        .row-striped:nth-of-type(even) {
            background-color: #f4f4f4;
        }

        .border-right {
            border-right: 1px solid lightgrey;
            margin-bottom: -999px;
            padding-bottom: 999px;
        }

        tr {
            border: none !important;
        }

        .table2 {
            padding: 2px;
        }

        td.pad5 {
            padding: 5px !important;
            line-height: 1em !important;
        }

        footer {
            position: fixed;
            bottom: 0px;
            left: 0px;
            right: 0px;
            height: 20px;
        }

        footer .pagenum:before {
            content: counter(page);
        }
    </style>
</head>

<body>
<footer>
    <div class="pagenum-container">
        <div class="row" style="padding: 2px 5px 0px 20px">
            <div class="col-xs-12">
                <b>If you wish to proceed with the above works, please send a reply email to construct@capecod.com.au to authorise your acceptance.</b>
            </div>
        </div>
    </div>
</footer>
<div class="container">
    <?php $pagecount = 1; ?>
    <div class="page22">
        <div class="row" style="padding: 5px">
            <div class="col-xs-3"><img src="{!! URL::to('/') !!}/img/logo-capecod2.png"></div>
            <div class="col-xs-9"><h3 style="margin: 0px">ELECTRICAL INSPECTION REPORT</h3></div>
        </div>
        {{-- Job Details --}}
        <div class="row">
            <div class="col-xs-12" style="background-color: #f0f6fa; font-weight: bold;"><h5 style="margin: 0px; padding: 5px 2px 5px 2px">JOB DETAILS</h5></div>
        </div>
        <div class="row" style="padding: 0px;">
            <div class="col-xs-2">Date</div>
            <div class="col-xs-3">{{ ($report->inspected_at) ?  $report->inspected_at->format('d/m/Y g:i a') : '' }}</div>
            <div class="col-xs-1">Client</div>
            <div class="col-xs-6">{{ $report->client_name }}</div>
        </div>
        <div class="row" style="padding: 0px">
            <div class="col-xs-2">Inspection carried out by</div>
            <div class="col-xs-3">{{ ($report->assignedTo) ? $report->assignedTo->name : '' }}</div>
            <div class="col-xs-1">&nbsp;</div>
            <div class="col-xs-6">{{ $report->client_address }}</div>
        </div>
        <div class="row" style="padding: 0px">
            <div class="col-xs-2">&nbsp;</div>
            <div class="col-xs-3">Licence No. {{ $report->inspected_lic }}</div>
            <div class="col-xs-7"></div>
        </div>
        <div class="row" style="padding: 0px;">
            <div class="col-xs-2">Signature</div>
            <div class="col-xs-3">{{ $report->inspected_name }}</div>
            <div class="col-xs-1"></div>
            <div class="col-xs-6">Client contact was made: &nbsp; {{ ($report->client_contacted) ? 'Yes' : 'No' }}</div>
        </div>

        {{--}}
        <table class="table2" style="width: 100%; padding: 0px; margin: 0px;">
            <tr>
                <td width="15%">Date</td>
                <td width="35%"  style="padding: 2px">{{ ($report->inspected_at) ?  $report->inspected_at->format('d/m/Y g:i a') : '' }}</td>
                <td width="10%">Client</td>
                <td width="40%">{{ $report->client_name }}</td>
            </tr>
            <tr>
                <td>Inspection carried</td>
                <td>{{ ($report->assignedTo) ? $report->assignedTo->name : '' }}</td>
                <td></td>
                <td>{{ $report->client_address }}</td>
            </tr>
            <tr>
                <td>out by</td>
                <td>Licence No. {{ $report->inspected_lic }}</td>
                <td>Client contact was made</td>
                <td>{{ ($report->client_contacted) ? 'Yes' : 'No' }}</td>
            </tr>
        </table> --}}

        {{-- Existing Wiring --}}
        <div class="row">
            <div class="col-xs-12" style="background-color: #f0f6fa; font-weight: bold;"><h5 style="margin: 0px; padding: 5px 2px 5px 2px">CONDITION OF EXISTING WIRING</h5></div>
        </div>
        <div class="row" style="padding: 2px;">
            <div class="col-xs-12">The existing wiring was found to be:<br>{!! nl2br($report->existing) !!}</div>
        </div>
        <br>

        {{-- Required Work --}}
        <div class="row">
            <div class="col-xs-12" style="background-color: #f0f6fa; font-weight: bold;"><h5 style="margin: 0px; padding: 5px 2px 5px 2px">REQUIRED WORK TO MEET COMPLIANCE</h5></div>
        </div>
        <div class="row" style="padding: 2px;">
            <div class="col-xs-12">
                The following work is required so that Existing Electrical Wiring will comply to the requirements of S.A.A Codes and the local Council:<br>
                {!! nl2br($report->required) !!}
                @if ($report->required_cost)
                    <br>
                    <hr style="margin: 0px"><span style="float: right;"> <b> at a cost of ${{ $report->required_cost }} Incl GST</b></span>
                @endif
            </div>
        </div>
        <br>

        {{-- Recommended Work --}}
        <div class="row">
            <div class="col-xs-12" style="background-color: #f0f6fa; font-weight: bold;"><h5 style="margin: 0px; padding: 5px 2px 5px 2px">RECOMMEND WORKS</h5></div>
        </div>
        <div class="row" style="padding: 2px;">
            <div class="col-xs-12">
                Work not essential but strongly recommended to be carried out to prevent the necessity of costly maintenance in the future when access to same:<br>
                {!! nl2br($report->recommend) !!}
                @if ($report->recommend_cost)
                    <br>
                    <hr style="margin: 0px"><span style="float: right;"> <b> at a cost of ${{ $report->recommend_cost }} Incl GST</b></span>
                @endif
            </div>
        </div>

        {{-- <div class="page"></div> --}}

        {{-- Additional Notes  --}}
        <div class="row">
            <div class="col-xs-12" style="background-color: #f0f6fa; font-weight: bold;"><h5 style="margin: 0px; padding: 5px 2px 5px 2px">ADDITIONAL NOTES</h5></div>
        </div>
        <div class="row" style="padding: 2px;">
            <div class="col-xs-12">
                {!! nl2br($report->notes) !!}</div>
        </div>
    </div>
</div>
</body>
</html>