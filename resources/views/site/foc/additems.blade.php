@inject('maintenanceWarranty', 'App\Http\Utilities\MaintenanceWarranty')
@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        @if (Auth::user()->hasAnyPermissionType('site'))
            <li><a href="/site">Sites</a><i class="fa fa-circle"></i></li>
        @endif
        <li><a href="/site/foc">FOC Requirements</a><i class="fa fa-circle"></i></li>
        <li><a href="/site/foc/{{$foc->id}}">FOC</a><i class="fa fa-circle"></i></li>
        <li><span>Adding items</span></li>
    </ul>
@stop

@section('content')
    <div class="page-content-inner">
        <div class="row">
            <div class="col-md-12">
                <div class="portlet light bordered">
                    <div class="portlet-title">
                        <div class="caption">
                            <span class="caption-subject font-green-haze bold uppercase">FOC Requirements</span>
                            <span class="caption-helper"></span>
                        </div>
                    </div>
                    <div class="portlet-body form">
                        {!! Form::model($foc, ['method' => 'PATCH','action' => ['Site\SiteFocController@addItemsSave',  $foc->id], 'class' => 'horizontal-form']) !!}
                        <input type="hidden" id="item_count" value="5">
                        @include('form-error')

                        <div class="form-body">
                            <h4>Site Details</h4>
                            <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                            <div class="row">
                                <div class="col-md-12">
                                    @if ($foc->site)
                                        <b>{{ $foc->site->name }}</b><br>
                                        {{ $foc->site->full_address }}<br>
                                        <b>Supervisor:</b> {{ ($foc->site->supervisor_id) ? $foc->site->supervisor->name : 'none'}}<br>
                                    @endif
                                </div>
                            </div>
                            <br>

                            {{-- Items --}}
                            <div id="items-div">
                                <h4>FOC Item(s)</h4>
                                <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                                <?php $itemCount = $foc->items->count() ?>
                                @for ($i = 1; $i <= 5; $i++)
                                    <div class="row">
                                        <div class="col-xs-1 ">Item {{$i+$itemCount}}</div>
                                        <div class="col-xs-8 ">
                                            <div class="form-group {!! fieldHasError('item1', $errors) !!}">
                                                {!! Form::textarea("item$i", null, ['rows' => '3', 'class' => 'form-control', 'placeholder' => "Specific details of maintenance request item $i."]) !!}
                                                {!! fieldErrorMessage('item1', $errors) !!}
                                            </div>
                                        </div>
                                        <div class="col-xs-3">
                                            <div class="form-group {!! fieldHasError("cat$i", $errors) !!}">
                                                {!! Form::select("cat$i", $cats, null, ['class' => 'form-control bs-select', 'title' => 'Select category', 'name' => "cat$i", 'id' => "cat$i"]) !!}
                                                {!! fieldErrorMessage("cat$i", $errors) !!}
                                            </div>
                                        </div>
                                    </div>
                                @endfor

                                {{-- Extra 5 Items --}}
                                <button class="btn blue" id="more5">More Items</button>
                                <div id="more_items5" style="display: none">
                                    @for ($i = 6; $i <= 10; $i++)
                                        <div class="row">
                                            <div class="col-xs-1 ">Item {{$i+$itemCount}}</div>
                                            <div class="col-xs-8 ">
                                                <div class="form-group {!! fieldHasError('item1', $errors) !!}">
                                                    {!! Form::textarea("item$i", null, ['rows' => '3', 'class' => 'form-control', 'placeholder' => "Specific details of maintenance request item $i."]) !!}
                                                    {!! fieldErrorMessage('item1', $errors) !!}
                                                </div>
                                            </div>
                                            <div class="col-xs-3">
                                                <div class="form-group {!! fieldHasError("cat$i", $errors) !!}">
                                                    {!! Form::select("cat$i", $cats, null, ['class' => 'form-control bs-select', 'title' => 'Select category', 'name' => "cat$i", 'id' => "cat$i"]) !!}
                                                    {!! fieldErrorMessage("cat$i", $errors) !!}
                                                </div>
                                            </div>
                                        </div>
                                    @endfor
                                    <button class="btn blue" id="more10">More Items</button>
                                </div>
                                {{-- Extra 10 Items --}}
                                <div id="more_items10" style="display: none">
                                    @for ($i = 11; $i <= 15; $i++)
                                        <div class="row">
                                            <div class="col-xs-1 ">Item {{$i+$itemCount}}</div>
                                            <div class="col-xs-8 ">
                                                <div class="form-group {!! fieldHasError('item1', $errors) !!}">
                                                    {!! Form::textarea("item$i", null, ['rows' => '3', 'class' => 'form-control', 'placeholder' => "Specific details of maintenance request item $i."]) !!}
                                                    {!! fieldErrorMessage('item1', $errors) !!}
                                                </div>
                                            </div>
                                            <div class="col-xs-3">
                                                <div class="form-group {!! fieldHasError("cat$i", $errors) !!}">
                                                    {!! Form::select("cat$i", $cats, null, ['class' => 'form-control bs-select', 'title' => 'Select category', 'name' => "cat$i", 'id' => "cat$i"]) !!}
                                                    {!! fieldErrorMessage("cat$i", $errors) !!}
                                                </div>
                                            </div>
                                        </div>
                                    @endfor
                                    <button class="btn blue" id="more15">More Items</button>
                                </div>
                                {{-- Extra 15 Items --}}
                                <div id="more_items15" style="display: none">
                                    @for ($i = 16; $i <= 20; $i++)
                                        <div class="row">
                                            <div class="col-xs-1 ">Item {{$i+$itemCount}}</div>
                                            <div class="col-xs-8 ">
                                                <div class="form-group {!! fieldHasError('item1', $errors) !!}">
                                                    {!! Form::textarea("item$i", null, ['rows' => '3', 'class' => 'form-control', 'placeholder' => "Specific details of maintenance request item $i."]) !!}
                                                    {!! fieldErrorMessage('item1', $errors) !!}
                                                </div>
                                            </div>
                                            <div class="col-xs-3">
                                                <div class="form-group {!! fieldHasError("cat$i", $errors) !!}">
                                                    {!! Form::select("cat$i", $cats, null, ['class' => 'form-control bs-select', 'title' => 'Select category', 'name' => "cat$i", 'id' => "cat$i"]) !!}
                                                    {!! fieldErrorMessage("cat$i", $errors) !!}
                                                </div>
                                            </div>
                                        </div>
                                    @endfor
                                </div>
                            </div>
                        </div>

                        <div class="form-actions right">
                            <a href="/site/foc" class="btn default"> Back</a>
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

            $("#more5").click(function (e) {
                e.preventDefault();
                $('#more5').hide();
                $('#more_items5').show();
            });

            $("#more10").click(function (e) {
                e.preventDefault();
                $('#more10').hide();
                $('#more_items10').show();
            });

            $("#more15").click(function (e) {
                e.preventDefault();
                $('#more15').hide();
                $('#more_items15').show();
            });


            function updateFields() {

            }
        });
    </script>
@stop

