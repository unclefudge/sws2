<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Upcoming Jobs</title>
    <link href="{{ asset('/') }}/assets/global/plugins/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css"/>
    <link href="{{ asset('/') }}/assets/global/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css"/>
    <style>
        @import url(http://fonts.googleapis.com/css?family=PT+Sans);

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
            font-size: 8px;
        }

        div.page {
            page-break-after: always;
            page-break-inside: avoid;
        }

        .table-striped > tbody > tr:nth-of-type(odd) {
            background-color: #ffffff;
        }

        .table-striped > tbody > tr:nth-of-type(even) {
            background-color: #fbfbfb;
        }

        .border-right {
            border-right: 1px solid lightgrey;
            margin-bottom: -999px;
            padding-bottom: 999px;
        }

        td.pad5, th.pad5 {
            padding: 5px !important;
            line-height: 1em !important;
        }

    </style>
</head>

<body>
<div class="container">
    <div class="row">
        <div class="col-xs-12">
            <h3 style="margin: 0px">Upcoming Jobs</h3>
        </div>
    </div>
    <hr style="margin: 5px 0px 15px 0px">

    <table class="table table-striped table-bordered table-hover order-column" id="table1" style="padding: 0px; margin: 0px">
        <thead>
        <tr style="background-color: #F6F6F6; font-weight: bold; overflow: hidden;">
            <th width="5%" class="pad5">Start Date</th>
            <th width="15%" class="pad5">Site</th>
            <th width="5%" class="pad5">Super</th>
            <th width="5%" class="pad5">Company</th>
            <th width="5%" class="pad5">Deposit Paid</th>
            <th width="3%" class="pad5">ENG</th>
            <th width="5%" class="pad5">HBCF</th>
            <th width="3%" class="pad5">DC</th>
            <th width="3%" class="pad5">FC-EST</th>
            <th width="15%" class="pad5">CC</th>
            <th width="15%" class="pad5">FC Plans</th>
            <th width="15%" class="pad5">FC Structural</th>
            <th width="15%" class="pad5">CF-EST</th>
            <th width="15%" class="pad5">CF-ADM</th>
        </tr>
        </thead>

        <tbody>
        @foreach($startdata as $row)
            <tr>
                <td class="pad5" style="{{ ($row['date_est']) ? 'background:#FDD7B1' : '' }}">{!! ($row['date']) ? $row['date'] : $row['date_est'] !!}</td>
                <td class="pad5">{!! $row['name'] !!}</td>
                <td class="pad5">{!! $row['supervisor'] !!}</td>
                <td class="pad5">{!! $row['company'] !!}</td>
                <td class="pad5" style="{{ ($row['deposit_paid'] == '-') ? 'background:#FDD7B1' : '' }}">{!! $row['deposit_paid'] !!}</td>
                <td class="pad5">{!! $row['eng'] !!}</td>
                <td class="pad5">{!! $row['hbcf'] !!}</td>
                <td class="pad5">{!! $row['design_con'] !!}</td>
                <td class="pad5">{!! $row['estimator_fc'] !!}</td>
                <td class="pad5" style="{{ ($row['cc_stage']) ? 'background:'.$settings_colours['opt'][$row['cc_stage']] : '' }}">{!! $row['cc'] !!}</td>
                <td class="pad5" style="{{ ($row['fc_plans_stage']) ? 'background:'.$settings_colours['opt'][$row['fc_plans_stage']] : '' }}">{!! $row['fc_plans'] !!}</td>
                <td class="pad5" style="{{ ($row['fc_struct_stage']) ? 'background:'.$settings_colours['opt'][$row['fc_struct_stage']] : '' }}">{!! $row['fc_struct'] !!}</td>
                <td class="pad5" style="{{ ($row['cf_est_stage']) ? 'background:'.$settings_colours['cfest'][$row['cf_est_stage']] : '' }}">{!! $row['cf_est'] !!}</td>
                <td class="pad5" style="{{ ($row['cf_adm_stage']) ? 'background:'.$settings_colours['cfadm'][$row['cf_adm_stage']] : '' }}">{!! $row['cf_adm'] !!}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
</body>
</html>