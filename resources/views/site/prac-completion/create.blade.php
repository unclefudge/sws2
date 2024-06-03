@inject('maintenanceWarranty', 'App\Http\Utilities\MaintenanceWarranty')
@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        @if (Auth::user()->hasAnyPermissionType('site'))
            <li><a href="/site">Sites</a><i class="fa fa-circle"></i></li>
        @endif
        <li><a href="/site/prac-completion">Practical Completion</a><i class="fa fa-circle"></i></li>
        <li><span>Create</span></li>
    </ul>
@stop

@section('content')
    <div class="page-content-inner">
        <div class="row">
            <div class="col-md-12">
                <div class="portlet light bordered">
                    <div class="portlet-title">
                        <div class="caption">
                            <span class="caption-subject font-green-haze bold uppercase">Practical Completion</span>
                            <span class="caption-helper"></span>
                        </div>
                    </div>
                    <div class="portlet-body form">
                        <!-- BEGIN FORM-->
                        {!! Form::model('modal', ['action' => 'Site\SitePracCompletionController@store', 'class' => 'horizontal-form', 'files' => true]) !!}
                        @include('form-error')

                        <div class="form-body">
                            <h4>Site Details</h4>
                            <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group {!! fieldHasError('site_id', $errors) !!}">
                                        {!! Form::label('site_id', 'Site', ['class' => 'control-label']) !!}
                                        <select id="site_id" name="site_id" class="form-control select2" style="width:100%">
                                            {!! Auth::user()->authSitesSelect2Options('view.site.list', old('site_id')) !!}
                                        </select>
                                        {!! fieldErrorMessage('site_id', $errors) !!}
                                    </div>
                                </div>
                            </div>

                            {{-- Photo/Docs --}}
                            <div class="row">
                                <div class="col-md-6">
                                    <h5>Upload Photos/Documents</h5>
                                    <input type="file" class="filepond" name="filepond[]" multiple/><br><br>
                                </div>
                            </div>

                            {{-- Items --}}
                            <div id="items-div">
                                <h4>Prac Item(s)</h4>
                                <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                                @for ($i = 1; $i <= 5; $i++)
                                    <div class="row">
                                        <div class="col-xs-1 ">Item {{$i}}</div>
                                        <div class="col-xs-11 ">
                                            <div class="form-group {!! fieldHasError('item1', $errors) !!}">
                                                {!! Form::textarea("item$i", null, ['rows' => '3', 'class' => 'form-control', 'placeholder' => "Specific details of maintenance request item $i."]) !!}
                                                {!! fieldErrorMessage('item1', $errors) !!}
                                            </div>
                                        </div>
                                    </div>
                                @endfor

                                {{-- Extra Items --}}
                                <button class="btn blue" id="more">More Items</button>
                                <div id="more_items" style="display: none">
                                    @for ($i = 6; $i <= 20; $i++)
                                        <div class="row">
                                            <div class="col-xs-1 ">Item {{$i}}</div>
                                            <div class="col-xs-11 ">
                                                <div class="form-group {!! fieldHasError('item1', $errors) !!}">
                                                    {!! Form::textarea("item$i", null, ['rows' => '3', 'class' => 'form-control', 'placeholder' => "Specific details of maintenance request item $i."]) !!}
                                                    {!! fieldErrorMessage('item1', $errors) !!}
                                                </div>
                                            </div>
                                        </div>
                                    @endfor
                                </div>
                            </div>
                        </div>

                        <div class="form-actions right">
                            <a href="/site/prac-completion" class="btn default"> Back</a>
                            <button type="submit" class="btn green" id="submit"> Save</button>
                        </div>
                    </div>
                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </div>
@stop <!-- END Content -->


@section('page-level-plugins-head')
    <link href="https://unpkg.com/filepond/dist/filepond.css" rel="stylesheet" type="text/css"/>   {{-- Filepond --}}
    <link href="/assets/global/plugins/select2/css/select2.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/select2/css/select2-bootstrap.min.css" rel="stylesheet" type="text/css"/>
@stop

@section('page-level-plugins')
    <script src="/assets/global/plugins/select2/js/select2.full.min.js" type="text/javascript"></script>
    <script src="https://unpkg.com/filepond/dist/filepond.min.js"></script> {{-- FilePond --}}
@stop

@section('page-level-scripts')
    {{-- Metronic + custom Page Scripts --}}
    <script src="/js/filepond-basic.js" type="text/javascript"></script>
    <script>
        $.ajaxSetup({headers: {'X-CSRF-Token': $('meta[name=token]').attr('value')}});

        $(document).ready(function () {
            /* Select2 */
            $("#site_id").select2({placeholder: "Select Site", width: "100%"});

            updateFields();

            // On Change Site ID
            $("#site_id").change(function () {
                updateFields();
            });

            $("#more").click(function (e) {
                e.preventDefault();
                $('#more').hide();
                $('#more_items').show();
            });


            function updateFields() {
                
            }
        });

        $('.date-picker').datepicker({
            autoclose: true,
            format: 'dd/mm/yyyy',
        });
    </script>
@stop

