<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Equipment Transfers</title>
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
    </style>
</head>

<body>
<div class="container">
    <div class="page22">
        <?php $row_count = 0; ?>
        <?php $page_count = 1; ?>
        @foreach ($transactions as $trans)
            @if ($row_count == 0)
                {{-- New Page - Show header --}}
                <div class="row">
                    <div class="col-xs-8"><h3 style="margin: 0px">Equipment Transfers</h3></div>
                    <div class="col-xs-4"><h6><b>{{ $from->format('d/m/Y') }} - {{ $to->format('d/m/Y') }}</b></h6></div>
                </div>
                <hr style="margin: 5px 0px">
                <div class="row">
                    <div class="col-xs-4">Item</div>
                    <div class="col-xs-2">From</div>
                    <div class="col-xs-2">To</div>
                    <div class="col-xs-2">Date</div>
                </div>
                <hr style="margin: 5px 0px">
            @endif

            <?php
            $row_count ++;
            list($from_part, $trans_to) = explode(' => ', $trans->notes);
            list($crap, $trans_from) = explode('items from ', $from_part);
            if (preg_match('/\(/', $trans_from))
                list($b1, $trans_from) = explode('(', rtrim($trans_from, ')'));
            if (preg_match('/\(/', $trans_to))
                list($b1, $trans_to) = explode('(', rtrim($trans_to, ')'));
            ?>

            <div class="row">
                <div class="col-xs-4">{{ $trans->qty }}&nbsp; x &nbsp; {{ $trans->item->name }}</div>
                <div class="col-xs-2">{{ $trans_from}}</div>
                <div class="col-xs-2">{{ $trans_to }}</div>
                <div class="col-xs-2">{{ $trans->created_at->format('d/m/Y') }}</div>
            </div>

            @if ($row_count > 40) {{-- New Page if no of lines exceed max --}}
            <div class="page"></div>
            <?php $row_count = 0; $page_count ++ ?>
            @endif
        @endforeach
        <br><br>
        <?php $row_count = $row_count + 2 ?>
    </div>
</div>
</body>
</html>