@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        @if (Auth::user()->hasAnyPermissionType('site'))
            <li><a href="/site">Sites</a><i class="fa fa-circle"></i></li>
        @endif
        <li><span>Documents</span></li>
    </ul>
@stop


@section('content')
    {{-- BEGIN PAGE CONTENT INNER --}}
    <div class="page-content-inner">

        @include('site/_header')

        <div class="row">
            <div class="col-md-12">
                {{-- Staff --}}
                <div class="portlet light">
                    <div class="portlet-title tabbable-line">
                        <div class="caption">
                            <span class="caption-subject font-dark bold uppercase">Site Documents</span>
                        </div>
                        <div class="actions">
                            @if(Auth::user()->hasAnyPermission2('add.site.doc|add.safety.doc'))
                                <a class="btn btn-circle green btn-outline btn-sm" href="/site/doc/create" data-original-title="Add">Add</a>
                            @endif
                        </div>
                    </div>
                    <div class="portlet-body">
                        <input type="hidden" id="site_id" name="site_id" value="{{ $site->id }}">
                        <div class="row">
                            <div class="col-md-2">
                                <div class="form-group">
                                    {!! Form::select('type', Auth::user()->siteDocTypeSelect('view', 'all'), null, ['class' => 'form-control bs-select', 'id' => 'type']) !!}
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <table class="table table-striped table-bordered table-hover order-column" id="table1">
                                    <thead>
                                    <tr class="mytable-header">
                                        <th width="5%"> #</th>
                                        <th width="7%"> Type</th>
                                        <th> Document</th>
                                        <th width="10%"> Action</th>
                                    </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div>
        <div class="pull-right" style="font-size: 12px; font-weight: 200; padding: 10px 10px 0 0">
            {!! $site->displayUpdatedBy() !!}
        </div>
    </div>
    <!-- END PAGE CONTENT INNER -->
@stop

@section('page-level-plugins-head')
    <link href="/css/libs/fileinput.min.css" media="all" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/datatables/datatables.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.css" rel="stylesheet" tytype="text/css"/>
@stop

@section('page-level-styles-head')
    <link href="/assets/global/plugins/select2/css/select2.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/select2/css/select2-bootstrap.min.css" rel="stylesheet" type="text/css"/>
@stop

@section('page-level-plugins')
    <script src="/js/libs/fileinput.min.js"></script>
    <script src="/assets/global/plugins/bootstrap-select/js/bootstrap-select.min.js" type="text/javascript"></script>
    <script src="/assets/global/scripts/datatable.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/datatables/datatables.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/select2/js/select2.full.min.js" type="text/javascript"></script>
@stop

@section('page-level-scripts') {{-- Metronic + custom Page Scripts --}}
<script src="/assets/pages/scripts/components-bootstrap-select.min.js" type="text/javascript"></script>
<script type="text/javascript">

    $.ajaxSetup({
        headers: {'X-CSRF-Token': $('meta[name=token]').attr('value')}
    });

    var site_id = $('#site_id').val();

    var table1 = $('#table1').DataTable({
        processing: true,
        serverSide: true,
        pageLength: 100,
        ajax: {
            'url': '{!! url('site/data/doc/dt') !!}',
            'type': 'GET',
            'data': function (d) {
                d.type = $('#type').val();
                d.site_id = $('#site_id').val();
            }
        },
        columns: [
            {data: 'id', name: 'd.id', orderable: false, searchable: false},
            {data: 'type', name: 'd.type'},
            {data: 'name', name: 'd.name'},
            {data: 'action', name: 'action', orderable: false, searchable: false},
        ],
        order: [
            [2, "asc"]
        ]
    });

    $('#type').change(function () {
        table1.ajax.reload();
    });

</script>
@stop