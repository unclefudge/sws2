<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>WHS Mananagement Plan</title>
    <link href="{{ asset('/') }}/assets/global/plugins/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css"/>
    <link href="{{ asset('/') }}/assets/global/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css"/>
    <!--<link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=PT+Sans&display=swap" rel="stylesheet" type='text/css'>-->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@300&display=swap" rel="stylesheet">

    <style>
        /*@import url('https://fonts.googleapis.com/css2?family=PT+Sans&display=swap');*/

        body, h1, h2, h3, h4, h5, h6 {
            font-family: 'Open Sans', sans-serif;
        }

        @page {
            margin: .7cm .7cm
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

        .roundcorners {
            border-radius: 50px;
            /*background: #A8BCBC;*/
            padding: 20px;
            margin: auto;
            width: 90%;
            height: 950px;
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

        td.pad5 {
            padding: 5px !important;
            line-height: 1em !important;
        }
    </style>
</head>

<body>
<div class="container">
    <!-- Cover Page -->
    <div class="roundcorners">
        <p style="padding-top: 50px"></p>
        <p style="text-align: center"><img src="{!! URL::to('/') !!}/img/logo-capecod3-large.png"></p>
        <p style="margin-top: 100px"></p>
        <p style="text-align: center; font-size: 30px; font-weight: 200">WHS Management Plan</p>
        <p style="padding-top: 100px"></p>

        {{-- Principle Contractor --}}
        <table class="table" style="padding: 0px; margin: 0px">
            <tr style="color:#FFF; background-color: #851750; font-weight: bold;">
                <td style="border: 1px solid #111">PRINCIPAL CONTRACTOR & KEY CONTACTS</td>
            </tr>
        </table>
        <table class="table" style="padding: 0px; margin: 0px">
            <tr>
                <td class="pad5" width="30%" style="border-top: 1px solid #111; border-left: 1px solid #111"><b>Cape Cod Australia Pty Ltd</b></td>
                <td class="pad5" width="30%" style="border-top: 1px solid #111;"><b>ABN</b> &nbsp; 54000 605 407</td>
                <td class="pad5" style="border-top: 1px solid #111; border-right: 1px solid #111"><b>BUILDERS LICENCE:</b> &nbsp; 5519</td>
            </tr>
        </table>
        <table class="table" style="padding: 0px; margin: 0px">
            <tr>
                <td class="pad5" width="20%" style="border-top: 1px solid #111; border-left: 1px solid #111"><b>Address:</b></td>
                <td class="pad5" style="border-top: 1px solid #111; border-right: 1px solid #111">4/426 Church Street PARAMATTA NSW 2151</td>
            </tr>
        </table>
        <table class="table" style="padding: 0px; margin: 0px">
            <tr>
                <td class="pad5" width="20%" style="border-top: 1px solid #111; border-left: 1px solid #111"><b>Phone:</b></td>
                <td class="pad5" style="border-top: 1px solid #111; border-right: 1px solid #111">9849 4444</td>
            </tr>
        </table>
        <table class="table" style="padding: 0px; margin: 0px;">
            <tr>
                <td class="pad5" style="border-top: 1px solid #111; border-left: 1px solid #111;  border-right: 1px solid #111;" colspan="3"><b>KEY CONTACTS:</b></td>
            </tr>
            <tr>
                <td class="pad5" width="40%" style="border-top: none; border-left: 1px solid #111">CONSTRUCTION SITE SUPERVISOR</td>
                <td class="pad5" width="20%" style="border-top: none;">{!! ($site->supervisors()->first()) ? $site->supervisors()->first()->name : '' !!}</td>
                <td class="pad5" style="border-top: none; border-right: 1px solid #111">PH: {!! ($site->supervisors()->first()) ? $site->supervisors()->first()->phone : '' !!}</td>
            </tr>
            <tr>
                <td class="pad5" width="40%" style="border-top: none; border-left: 1px solid #111">CONSTRUCTION MANAGER</td>
                <td class="pad5" width="20%" style="border-top: none;">{{ \App\User::find(7)->name }}</td>
                <td class="pad5" style="border-top: none; border-right: 1px solid #111">PH: {{ \App\User::find(7)->phone }}</td>
            </tr>
            <tr>
                <td class="pad5" width="40%" style="border-top: none; border-left: 1px solid #111">WHS & HR MANAGER</td>
                <td class="pad5" width="20%" style="border-top: none;">{{ \App\User::find(351)->name }}</td>
                <td class="pad5" style="border-top: none; border-right: 1px solid #111">PH: {{ \App\User::find(351)->phone }}</td>
            </tr>
            <tr>
                <td class="pad5" style="border-top: none; border-left: 1px solid #111;  border-right: 1px solid #111;" colspan="3"><h5 style="text-align: center;">IN THE EVENT OF AN EMERGENCY CALL 000</h5></td>
            </tr>
        </table>
        {{-- Project Details --}}
        <table class="table" style="padding: 0px; margin: 0px">
            <tr style="color:#FFF; background-color: #851750; font-weight: bold;">
                <td style="border: 1px solid #111">PROJECT DETAILS</td>
            </tr>
        </table>
        <table class="table" style="padding: 0px; margin: 0px">
            <tr>
                <td class="pad5" width="20%" style="border-top: 1px solid #111; border-left: 1px solid #111"><b>I.D</b></td>
                <td class="pad5" style="border-top: 1px solid #111; border-right: 1px solid #111">{{ $site->name }}</td>
            </tr>
        </table>
        <table class="table" style="padding: 0px; margin: 0px">
            <tr>
                <td class="pad5" width="20%" style="border-top: 1px solid #111; border-left: 1px solid #111"><b>ADDRESS</b></td>
                <td class="pad5" style="border-top: 1px solid #111; border-right: 1px solid #111">{{ $site->address }}, {{ strtoupper($site->suburb) }}</td>
            </tr>
        </table>
        {{-- Document History --}}
        <table class="table" style="padding: 0px; margin: 0px">
            <tr style="color:#FFF; background-color: #851750; font-weight: bold;">
                <td style="border: 1px solid #111">DOCUMENT HISTORY</td>
            </tr>
        </table>
        <table class="table" style="padding: 0px; margin: 0px; border: 1px solid #111">
            <tr>
                <td class="pad5" width="20%" style="border: 1px solid #111; background-color: #DDD;"><b>REVISION DATE</b></td>
                <td class="pad5" width="20%" style="border: 1px solid #111; background-color: #DDD;"><b>AUTHOR</b></td>
                <td class="pad5" style="border: 1px solid #111; background-color: #DDD;"><b>DETAILS</b></td>
            </tr>
            <tr>
                <td class="pad5" width="20%" style="border: 1px solid #111">16/06/2018</td>
                <td class="pad5" width="20%" style="border: 1px solid #111:">Tara Antoniolli</td>
                <td class="pad5" style="border: 1px solid #111">New template</td>
            </tr>
            <tr>
                <td class="pad5" width="20%" style="border: 1px solid #111">29/09/2019</td>
                <td class="pad5" width="20%" style="border: 1px solid #111">Tara Antoniolli</td>
                <td class="pad5" style="border: 1px solid #111">Code of Practice updates</td>
            </tr>
            <tr>
                <td class="pad5" width="20%" style="border: 1px solid #111">17/03/2020</td>
                <td class="pad5" width="20%" style="border: 1px solid #111">Tara Antoniolli</td>
                <td class="pad5" style="border: 1px solid #111">Include Covid-19 Risk Management Principles</td>
            </tr>
            <tr>
                <td class="pad5" width="20%" style="border: 1px solid #111">30/06/2021</td>
                <td class="pad5" width="20%" style="border: 1px solid #111">Tara Antoniolli</td>
                <td class="pad5" style="border: 1px solid #111">Covid-19 QR Code requirements</td>
            </tr>
            <tr>
                <td class="pad5" width="20%" style="border: 1px solid #111">12/05/2022</td>
                <td class="pad5" width="20%" style="border: 1px solid #111">Tara Antoniolli</td>
                <td class="pad5" style="border: 1px solid #111">Revision of responsibilities (WHS Officer, Maintenance & Construction)</td>
            </tr>
        </table>
    </div>
</div>
</body>
</html>