<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Quality Assurance</title>
    <link href="{{ asset('/') }}/assets/global/plugins/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css"/>
    <link href="{{ asset('/') }}/assets/global/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css"/>
    <style>
        body, h1, h2, h3, h4, h5, h6 {
            font-family: 'PT Sans', serif;
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
    </style>
</head>

<body>
<div class="container">
    <!-- Cover Page -->
    <div class="roundcorners">
        <p style="padding-top: 50px"></p>
        <p style="text-align: center"><img src="{!! URL::to('/') !!}/img/logo-capecod3-large.png"></p>
        <p style="padding-top: 100px"></p>
        <p style="text-align: center; font-size: 60px; font-weight: 800">QUALITY</p>
        <p style="text-align: center; font-size: 60px; font-weight: 800">ASSURANCE</p>
        <p style="text-align: center; font-size: 60px; font-weight: 800">CHECKLIST</p>
        <p style="padding-top: 100px"></p>
        <p style="text-align: center; font-size: 20px; font-weight: 400">{{ $site->address }}, {{ $site->suburb }} </p>
    </div>
</div>
</body>
</html>