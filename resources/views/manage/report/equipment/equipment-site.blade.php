@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        @if (Auth::user()->hasAnyPermissionType('manage.report'))
            <li><a href="/manage/report">Management Reports</a><i class="fa fa-circle"></i></li>
        @endif
        <li><span>Equipment List</span></li>
    </ul>
@stop

@section('content')
    <div class="page-content-inner">
        <div class="row">
            <div class="col-md-12">
                <div class="portlet light ">
                    <div class="portlet-title">
                        <div class="caption font-dark">
                            <i class="icon-layers"></i>
                            <span class="caption-subject bold uppercase font-green-haze"> Equipment List By Site</span>
                        </div>
                        <div class="actions">
                            <a href="/manage/report/equipment_site/report" class="btn btn-circle btn-outline btn-sm green" id="view_pdf"> View PDF</a>
                        </div>
                    </div>
                    <div class="portlet-body form">
                        <div class="portlet-body">
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
                                                <?php $current_name = $name ?>
                                                <h3 class="font-green">{{ $name }}</h3>
                                            @endif
                                            <?php $site = \App\Models\Site\Site::find($location->site_id); ?>
                                            <b>{{ $site->code }} {{ $site->name }}</b>
                                        @endif
                                    </div>
                                </div>
                                <hr style="margin-top: 2px">
                                @foreach ($location->items as $item)
                                    <div class="row">
                                        <div class="col-xs-1 text-right">{{ $item->qty }}</div>
                                        <div class="col-xs-11">{{ $item->item_name }}</div>
                                    </div>
                                @endforeach
                                <br><br>
                            @endforeach
                        </div>

                        <div class="form-actions right">
                            <a href="/manage/report" class="btn default"> Back</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- loading Spinner -->
    <div style="background-color: #FFF; padding: 20px; display: none" id="spinner">
        <div class="loadSpinnerOverlay">
            <div class="loadSpinner"><i class="fa fa-spinner fa-pulse fa-2x fa-fw margin-bottom"></i> Loading...</div>
        </div>
    </div>
    <!-- END PAGE CONTENT INNER -->
@stop


@section('page-level-plugins-head')
    <link href="/assets/global/plugins/select2/css/select2.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/select2/css/select2-bootstrap.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/bootstrap-datepicker/css/bootstrap-datepicker.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/datatables/datatables.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.css" rel="stylesheet" type="text/css"/>
@stop

@section('page-level-plugins')
    <script src="/assets/global/plugins/bootstrap-select/js/bootstrap-select.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/select2/js/select2.full.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js" type="text/javascript"></script>
    <script src="/assets/global/scripts/datatable.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/datatables/datatables.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.js" type="text/javascript"></script>
@stop

@section('page-level-scripts') {{-- Metronic + custom Page Scripts --}}
<script src="/assets/pages/scripts/components-bootstrap-select.min.js" type="text/javascript"></script>
<script src="/assets/pages/scripts/components-date-time-pickers.min.js" type="text/javascript"></script>
<script src="/assets/pages/scripts/components-select2.min.js" type="text/javascript"></script>

<script type="text/javascript">
</script>
@stop