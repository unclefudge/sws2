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
    </style>
</head>

<body>
<div class="container">
    <div class="page22">
        <div class="row">
            <div class="col-xs-8"><h3 style="margin: 0px">Equipment List By Site</h3></div>
            <div class="col-xs-4"><h6><b>Date: {{ \Carbon\Carbon::today()->format('d/m/Y') }}</b></h6></div>
        </div>
        <hr style="margin: 5px 0px">
        <br>
        <?php $row_count = 0; ?>
        <?php $page_count = 1; ?>
        <?php $current_name = '' ?>
        @foreach ($locations as $id => $name)
                <?php $location = \App\Models\Misc\Equipment\EquipmentLocation::find($id) ?>
            @continue($location->items->count() < 1)
            <div class="row">
                <div class="col-md-12">
                    @if ($name == 'other')
                        <b>{{ ($location->id == 1) ? 'STORE' : $location->other }}</b>
                    @elseif ($name == 'no-super')
                            <?php $site = \App\Models\Site\Site::find($location->site_id); ?>
                        <b>{{ $site->code }} {{ $site->name }} &nbsp; &nbsp; ** No Supervisor Assigned To Site **</b>
                    @else
                        @if ($name != $current_name)
                            <div class="page"></div>
                            <div class="row">
                                <div class="col-xs-8"><h3 style="margin: 0px">Equipment List By Site</h3></div>
                                <div class="col-xs-4"><h6><b>Date: {{ \Carbon\Carbon::today()->format('d/m/Y') }}</b></h6></div>
                            </div>
                            <hr style="margin: 5px 0px">
                                <?php $current_name = $name ?>
                            <h4 class="font-green">{{ $name }}</h4>
                        @endif
                            <?php $site = \App\Models\Site\Site::find($location->site_id); ?>
                        <b>{{ $site->code }} {{ $site->name }}</b>
                    @endif
                </div>
            </div>
            <hr style="margin: 2px 0px">
            @foreach ($location->itemsOrderedByName() as $item)
                <div class="row" style="padding: 0px; margin: 0px">
                    <div class="col-xs-1 text-right" style="padding: 0px 10px; margin: 0px">{{ $item->qty }}</div>
                    <div class="col-xs-11" style="padding: 0px 10px; margin: 0px">{{ $item->item_name }}</div>
                </div>
            @endforeach
            <br><br>
        @endforeach
    </div>
</div>
</body>
</html>