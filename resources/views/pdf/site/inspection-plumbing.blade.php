<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Plumbing Inspection Report</title>
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

        .table-striped>tbody>tr:nth-of-type(odd) {
            background-color: #ffffff;
        }

        .table-striped>tbody>tr:nth-of-type(even) {
            background-color: #fbfbfb;
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
<div class="container">
    <?php $pagecount = 1; ?>
    <div class="page22">
        <div class="row" style="padding: 5px">
            <div class="col-xs-3"><img src="{!! URL::to('/') !!}/img/logo-capecod3-large.png" height="40"></div>
            <div class="col-xs-9"><h3 style="margin: 0px">PLUMBING INSPECTION REPORT</h3></div>
        </div>
        {{-- Job Details --}}
        <div class="row">
            <div class="col-xs-12" style="background-color: #F6F6F6; font-weight: bold;"><h5 style="margin: 0px; padding: 5px 2px 5px 2px">JOB DETAILS &nbsp; : &nbsp; {{ $report->site->name }}</h5></div>
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


        {{-- Inspection DETAILS --}}
        <div class="row">
            <div class="col-xs-12" style="background-color: #F6F6F6; font-weight: bold;"><h5 style="margin: 0px; padding: 5px 2px 5px 2px">INSPECTION DETAILS</h5></div>
        </div>
        {{--Water Pressure / Hammer--}}
        <div class="row" style="padding: 0px">
            <div class="col-xs-2">Water Pressure</div>
            <div class="col-xs-3">{{ $report->pressure }} kpa</div>
            <div class="col-xs-5" style="text-align: right">500kpa Water Pressure Reduction Value Recommend</div>
            <div class="col-xs-2">{{ ($report->pressure_reduction) ? 'Yes' : 'No' }}</div>
        </div>
        <div class="row" style="padding: 0px">
            <div class="col-xs-2">Water Hammer</div>
            <div class="col-xs-10">{{ $report->hammer }} &nbsp; &nbsp; &nbsp; (Refer to Water Hammer comments below)</div>
        </div>
        <div class="row" style="padding: 0px">
            <div class="col-xs-2">Existing Hot Water Type</div>
            <div class="col-xs-3">{{ $report->hotwater_type }}</div>
            <div class="col-xs-5" style="text-align: right">Will pipes in roof hot water need to be lowerd?</div>
            <div class="col-xs-2">{{ ($report->hotwater_lowered) ? 'Yes' : 'No' }}</div>
        </div>
        <div class="row" style="padding: 0px">
            <div class="col-xs-2">Fuel Type</div>
            <div class="col-xs-10">{{ $report->fuel_type }}</div>
        </div>


        {{--  Gas  Meter / Pipes --}}
        <div class="row">
            <div class="col-xs-12" style="background-color: #F6F6F6; font-weight: bold;"><h5 style="margin: 0px; padding: 5px 2px 5px 2px">GAS</h5></div>
        </div>
        <div class="row" style="padding: 0px">
            <div class="col-xs-2">Gas Meter Position OK?</div>
            <div class="col-xs-3">{{ $report->gas_position }}</div>
            <div class="col-xs-5" style="text-align: right">Are gas pipes able to be tapped into?</div>
            <div class="col-xs-2">{{ ($report->gas_lines) ? 'Yes' : 'No' }}</div>
        </div>
        <div class="row" style="padding: 0px">
            <div class="col-xs-2">Gas Pipe</div>
            <div class="col-xs-10">{{ $report->gas_pipes }}</div>
        </div>
        {{-- Gas Notes --}}
        <div class="row" style="padding: 0px">
            <div class="col-xs-12">Gas Notes:<br>{!! nl2br($report->gas_notes) !!}<br><br></div>
        </div>


        {{-- Existing Plumbing --}}
        <div class="row">
            <div class="col-xs-12" style="background-color: #F6F6F6; font-weight: bold;"><h5 style="margin: 0px; padding: 5px 2px 5px 2px">CONDITION OF EXISTING PLUMBING</h5></div>
        </div>
        <div class="row">
            <div class="col-xs-12">The existing plumbing was found to be:<br>{!! nl2br($report->existing) !!}<br><br></div>
        </div>

        <!-- Comments -->
        <div class="row">
            <div class="col-xs-12" style="background-color: #F6F6F6; font-weight: bold;"><h5 style="margin: 0px; padding: 5px 2px 5px 2px">ADDITIONAL NOTES</h5></div>
        </div>
        <div class="row">
            <div class="col-xs-12">{!! nl2br($report->notes) !!}<br><br></div>
        </div>

        {{-- PAGE 2 --}}
        <div class="page"></div>

        {{-- Water Pressure --}}
        <div class="row">
            <div class="col-xs-12" style="background-color: #F6F6F6; font-weight: bold;"><h5 style="margin: 0px; padding: 5px 2px 5px 2px">WATER PRESSURE</h5></div>
        </div>
        <div class="row" style="padding: 0px;">
            <div class="col-xs-12">Water pressure higher than 500KPA will void the warranty on all mixer sets; it is our recommendation that you have fitted a pressure limiting valve at the metre to avoid possible problems:
                <br>{!! nl2br($report->pressure_notes) !!}
                @if ($report->pressure_cost)
                    <br>
                    <hr style="margin: 0px"><span style="float: right;"> <b> at a cost of ${{ $report->pressure_cost }} Incl GST</b></span>
                @endif
            </div>
        </div>

        {{-- Water Hammer --}}
        <div class="row">
            <div class="col-xs-12" style="background-color: #F6F6F6; font-weight: bold;"><h5 style="margin: 0px; padding: 5px 2px 5px 2px">WATER HAMMER</h5></div>
        </div>
        <div class="row" style="padding: 0px;">
            <div class="col-xs-12">{!! nl2br($report->hammer_notes) !!}<br><br>
            </div>
        </div>


        {{-- Sewer --}}
        <div class="row">
            <div class="col-xs-12" style="background-color: #F6F6F6; font-weight: bold;"><h5 style="margin: 0px; padding: 5px 2px 5px 2px">SEWER</h5></div>
        </div>
        <div class="row" style="padding: 0px;">
            <div class="col-xs-12">Upon closer inspection of the sewer diagram that we have obtained from the Water Board:<br>
                {!! nl2br($report->sewer_notes) !!}
            </div>
        </div>

        @if ($report->sewer_cost)
        <div class="row" style="text-align: right; padding: 0px;">
            <div class="col-xs-12" style="padding: 0px">
                <hr style="margin: 0px">
                Cost estimate <b>${{ $report->sewer_cost }}</b> (incl GST)<br>
                Allowance in your tender document is <b>${{ $report->sewer_allowance }}</b> (incl GST)<br>
                Meaning you may incur extra costs of <b>${{ $report->sewer_extra }}</b> (incl GST)
            </div>
        </div>
        @endif
        <div class="row">
            <div class="col-xs-12" style="text-align: center; padding: 0px">PRICE TO BE CONFIRMED AT TIME OF CONSTRUCTION AND DOES NOT INCLUDE BUILDERS MARGIN<br></div>
        </div>


        {{-- Stormwater --}}
        <div class="row">
            <div class="col-xs-12" style="background-color: #F6F6F6; font-weight: bold;"><h5 style="margin: 0px; padding: 5px 2px 5px 2px">STORMWATER</h5></div>
        </div>
        <div class="row" style="padding: 0px;">
            <div class="col-xs-12">Upon closer examination of your current stormwater system:<br>
                {!! nl2br($report->stormwater_notes) !!}
            </div>
        </div>

        @if ($report->stormwater_cost)
            <div class="row" style="text-align: right; padding: 0px;">
                <div class="col-xs-12" style="padding: 0px">
                    <hr style="margin: 0px">
                    Cost estimate <b>${{ $report->stormwater_cost }}</b> (incl GST)<br>
                    Allowance in your tender document is <b>${{ $report->stormwater_allowance }}</b> (incl GST)<br>
                    Meaning you may incur extra costs of <b>${{ $report->stormwater_extra }}</b> (incl GST)
                </div>
            </div>
        @endif

        {{-- Stormwater Detention --}}
        @if ($report->stormwater_detention_type)
            <div class="row">
                <div class="col-xs-12" style="background-color: #F6F6F6; font-weight: bold;"><h5 style="margin: 0px; padding: 5px 2px 5px 2px">ONSITE STORMWATER DETENTION</h5></div>
            </div>
            <div class="row" style="padding: 0px;">
                <div class="col-xs-12">{!! nl2br($report->stormwater_detention_type) !!}:<br>
                    {!! nl2br($report->stormwater_detention_notes) !!}
                </div>
            </div>
        @endif

        {{-- Note --}}
        <br>
        <hr style="padding: 0px; margin: 0px 0px 10px 0px">
        <div class="row">
            <div class="col-md-12">
                <b>Please note that these remain best estimate until the final position and depth of services are located. Final estimates will be relayed to you at that time for your approval. <br><br>Thank you for your acknowledgment of the above and we will do our best to
                    keep all costs to a minimum.</b>
            </div>
        </div>
    </div>
</div>
</body>
</html>