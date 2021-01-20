<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Equipment List</title>
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
    </style>
</head>

<body>
<div class="container">
    <div class="page22">
        <div class="row">
            <div class="col-xs-8"><h3 style="margin: 0px">Equipment Transactions</h3></div>
            <div class="col-xs-4"><h6><b>{{ $from->format('d/m/Y') }} - {{ $to->format('d/m/Y') }}</b></h6></div>
        </div>
        <hr style="margin: 5px 0px">
        <div class="row">
            <div class="col-xs-2">Item</div>
            <div class="col-xs-1">Qty</div>
            <div class="col-xs-2">Who</div>
            <div class="col-xs-2">Date & Time</div>
        </div>
        <hr style="margin: 5px 0px">

        <br>
        <?php $row_count = 0; ?>
        <?php $page_count = 1; ?>
        <?php $actions = ['D' => 'Disposals', 'W' => 'Write Offs', 'N' => 'New Items', 'P' => 'Purchases'] ?>

        {{-- Loop through Actions --}}
        @foreach ($actions as $action_code => $action_name)
            @if (($row_count + $transactions->where('action', $action_code)->count() > 40) && $transactions->where('action', $action_code)->count() < 40) {{-- New Page if no of lines for current item exceed max --}}
            <?php $row_count = 0; $page_count ++ ?>
            {{-- New Page - Show header --}}
            <div class="page"></div>
            <div class="row">
                <div class="col-xs-8"><h3 style="margin: 0px">Equipment Transactions..</h3></div>
                <div class="col-xs-4"><h6><b>{{ $from->format('d/m/Y') }} - {{ $to->format('d/m/Y') }}</b></h6></div>
            </div>
            <hr style="margin: 5px 0px">
            <div class="row">
                <div class="col-xs-2">Item</div>
                <div class="col-xs-1">Qty</div>
                <div class="col-xs-2">Who</div>
                <div class="col-xs-2">Date & Time</div>
            </div>
            <hr style="margin: 5px 0px">
            @endif

            <div class="row">
                <div class="col-md-12"><b>{{ $action_name }}</b></div>
            </div>
            @foreach ($transactions->where('action', $action_code) as $trans)
                <?php $row_count ++ ?>
                <div class="row">
                    <div class="col-xs-2">&nbsp; &nbsp; {{ $trans->item->name }}</div>
                    <div class="col-xs-1">{{ $trans->qty }}</div>
                    <div class="col-xs-2">{{ $trans->user->name }}</div>
                    <div class="col-xs-2">{{ $trans->created_at->format('d/m/Y H:i a') }}</div>
                </div>
            @endforeach
            <br><br>
            <?php $row_count = $row_count + 2 ?>
        @endforeach
    </div>
</div>
</body>
</html>