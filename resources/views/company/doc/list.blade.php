@extends('layout')
@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        @if (Auth::user()->company->subscription > 1 && Auth::user()->hasAnyPermissionType('company'))
            <li><a href="/company">Companies</a><i class="fa fa-circle"></i></li>
        @endif
        <li><span>Documents</span></li>
    </ul>
@stop

@section('content')
    <div class="page-content-inner">

        @include('company/_header')

        {{-- Non-Compliance Message for Primary User--}}
        @if (count($company->missingDocs()) && Auth::user()->id == $company->primary_user)
            <div class="row">
                <div class="col-lg-6 col-xs-12 col-sm-12 note note-warning">
                    <div style="text-align: center">You're company is <b>NON-COMPLIANT</b><br>to work on {{ $company->reportsTo()->name_alias }} work sites.
                        <br><br>
                        Please upload the documents listed within the Compliance Documents section.<br><br>
                        @if(count($company->missingDocs()) && (Auth::user()->isCompany($company->id) && Auth::user()->allowed2('add.company.doc')) ||
                   (Auth::user()->isCompany($company->reportsTo()->id) && Auth::user()->allowed2('add.company.doc') && $company->parentUpload()))
                            <a href="/company/{{ $company->id }}/doc/upload" class="btn dark">Upload</a>
                        @endif
                    </div>
                </div>

                @include('company/_compliance-docs')
            </div>
        @endif

        <div class="row">
            <div class="col-md-12">
                <div class="portlet light ">
                    <div class="portlet-title">
                        <div class="caption font-dark">
                            <span class="caption-subject font-dark bold uppercase"> Company Documents</span>
                        </div>
                        <div class="actions">
                            @if((Auth::user()->isCompany($company->id) && Auth::user()->allowed2('add.company.doc')) ||
                            (Auth::user()->isCompany($company->reportsTo()->id) && Auth::user()->allowed2('add.company.doc') && $company->parentUpload()))
                                @if ($company->parent_company && Auth::user()->isCompany($company->id))
                                    <div class="btn-group">
                                        <a class="btn green btn-outline btn-circle btn-sm" href="javascript:;" data-toggle="dropdown" data-hover="dropdown" data-close-others="true"> Generate
                                            <i class="fa fa-angle-down"></i>
                                        </a>
                                        <ul class="dropdown-menu pull-right">
                                            <li><a href="/company/{{ $company->id }}/doc/period-trade-contract/create">Period Trade Contract</a></li>
                                            <li><a href="/company/{{ $company->id }}/doc/subcontractor-statement/create">Subcontractor's Statement</a></li>
                                            @if (!$company->activeCompanyDoc('12') && Auth::user()->isCCcompany())
                                                <li><a href="/company/{{ $company->id }}/doc/privacy-policy/create">Privacy Policy</a></li>
                                            @endif
                                        </ul>
                                    </div>
                                @endif
                                <a class="btn btn-circle green btn-outline btn-sm" href="/company/{{ $company->id }}/doc/upload" data-original-title="Upload">Upload</a>
                            @endif
                        </div>
                    </div>
                    <div class="row">
                        @if (Auth::user()->companyDocTypeSelect('view', $company, 'all'))
                            <div class="col-md-3">
                                <div class="form-group">
                                    {!! Form::label('department', 'Department', ['class' => 'control-label']) !!}
                                    {!! Form::select('department', Auth::user()->companyDocDeptSelect('view', $company, 'all'), null, ['class' => 'form-control bs-select']) !!}
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="category_id" class="control-label">Category <span id="loader" style="visibility: hidden"><i class="fa fa-spinner fa-spin"></i></span></label>
                                    <select name="category_id" class="form-control select2" id="category_id">
                                        @foreach (Auth::user()->companyDocTypeSelect('view', $company, 'all') as $key => $value)
                                            <option value="{{ $key }}">{{ $value }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        @else
                            <div class="col-md-4">
                                <br>
                                <div class="alert alert-danger">You don't have permission to view any documents</div>
                            </div>
                        @endif


                        <div class="col-md-2 pull-right">
                            {!! Form::label('status', 'Status', ['class' => 'control-label']) !!}
                            {!! Form::select('status', ['1' => 'Current', '3' => 'Pending', '0' => 'Expired'], null, ['class' => 'form-control bs-select', 'id' => 'status',]) !!}
                        </div>
                    </div>
                    <div class="portlet-body">
                        <table class="table table-striped table-bordered table-hover order-column" id="table1">
                            <thead>
                            <tr class="mytable-header">
                                <th width="5%"> #</th>
                                <th> Document</th>
                                <th> Dept.</th>
                                <th> Details</th>
                                <th width="10%"> Expiry</th>
                                <th width="10%"> Action</th>
                            </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div> <!-- end portlet -->
        </div>
    </div>
    <!-- END PAGE CONTENT INNER -->
@stop

@section('page-level-plugins-head')
    <link href="/assets/global/plugins/datatables/datatables.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/select2/css/select2.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/select2/css/select2-bootstrap.min.css" rel="stylesheet" type="text/css"/>
@stop

@section('page-level-plugins')
    <script src="/assets/global/scripts/datatable.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/datatables/datatables.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/bootstrap-select/js/bootstrap-select.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/select2/js/select2.full.min.js" type="text/javascript"></script>
@stop

@section('page-level-scripts')
    {{-- Metronic + custom Page Scripts --}}
    <script src="/assets/pages/scripts/components-select2.min.js" type="text/javascript"></script>
    <script type="text/javascript">
        $.ajaxSetup({
            headers: {'X-CSRF-Token': $('meta[name=token]').attr('value')}
        });


        $(document).ready(function () {
            /* Dynamic Category dropdown from Department */
            $("#category_id").select2({width: '100%', minimumResultsForSearch: -1});
            $("#department").on('change', function () {
                var dept_id = $(this).val();
                //alert(deptId);
                if (dept_id) {
                    $.ajax({
                        url: '/company/' + {{ Auth::user()->company_id }} + '/doc/cats/' + dept_id,
                        type: "GET",
                        dataType: "json",
                        beforeSend: function () {
                            $('#loader').css("visibility", "visible");
                        },

                        success: function (data) {
                            console.log(data);
                            $("#category_id").empty();
                            $("#category_id").append('<option value="ALL">All categories</option>');
                            $.each(data, function (key, value) {
                                console.log('k:' + key + ' v:' + value);
                                $("#category_id").append('<option value="' + key + '">' + value + '</option>');
                            });
                        },
                        complete: function () {
                            $('#loader').css("visibility", "hidden");
                            table1.ajax.reload();
                        }
                    });
                } else {
                    $('select[name="state"]').empty();
                }

            });

        });
        var table1 = $('#table1').DataTable({
            processing: true,
            serverSide: true,
            pageLength: 100,
            ajax: {
                'url': '{!! url("company/$company->id/doc/dt/docs") !!}',
                'type': 'GET',
                'data': function (d) {
                    d.category_id = $('#category_id').val();
                    d.department = $('#department').val();
                    d.status = $('#status').val();
                }
            },
            columns: [
                {data: 'id', name: 'id', orderable: false, searchable: false},
                {data: 'name', name: 'name'},
                {data: 'category_id', name: 'category_id'},
                {data: 'details', name: 'details'},
                {data: 'expiry', name: 'expiry'},
                {data: 'action', name: 'action', orderable: false, searchable: false},
            ],
            order: [
                [2, "asc"]
            ]
        });

        table1.on('click', '.btn-delete[data-remote]', function (e) {
            e.preventDefault();
            var url = $(this).data('remote');
            var name = $(this).data('name');

            swal({
                title: "Are you sure?",
                text: "You will not be able to recover this file!<br><b>" + name + "</b>",
                showCancelButton: true,
                cancelButtonColor: "#555555",
                confirmButtonColor: "#E7505A",
                confirmButtonText: "Yes, delete it!",
                allowOutsideClick: true,
                html: true,
            }, function () {
                $.ajax({
                    url: url,
                    type: 'DELETE',
                    dataType: 'json',
                    data: {method: '_DELETE', submit: true},
                    success: function (data) {
                        toastr.error('Deleted document');
                    },
                }).always(function (data) {
                    $('#table1').DataTable().draw(false);
                });
            });
        });


        $('#category_id').change(function () {
            table1.ajax.reload();
        });

        $('#department').change(function () {
            table1.ajax.reload();
        });

        $('#status').change(function () {
            table1.ajax.reload();
        });
    </script>
@stop