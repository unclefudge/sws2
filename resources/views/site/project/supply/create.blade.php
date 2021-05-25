@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        @if (Auth::user()->hasAnyPermissionType('site'))
            <li><a href="/site">Sites</a><i class="fa fa-circle"></i></li>
        @endif
        <li><a href="/site/supply">Project Supply Info</a><i class="fa fa-circle"></i></li>
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
                            <i class="fa fa-pencil "></i>
                            <span class="caption-subject font-green-haze bold uppercase">Create Project Supply Infomation</span>
                            <span class="caption-helper"></span>
                        </div>
                    </div>
                    <div class="portlet-body form">
                        <!-- BEGIN FORM-->
                        {!! Form::model('SiteProjectSupply', ['action' => 'Site\SiteProjectSupplyController@store', 'class' => 'horizontal-form']) !!}
                        @include('form-error')

                        <div class="form-body">
                            {{-- Site --}}
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group {!! fieldHasError('site_id', $errors) !!}">
                                        {!! Form::label('site_id', 'Site', ['class' => 'control-label']) !!}
                                        {!! Form::select('site_id', $sitelist, null, ['class' => 'form-control select2']) !!}
                                        {!! fieldErrorMessage('site_id', $errors) !!}
                                    </div>
                                </div>
                            </div>

                            <h3>Project Supply Information</h3>
                            <hr style="padding: 0px; margin: 0px 0px 30px 0px">

                            <div class="row bold hidden-sm hidden-xs">
                                <div class="col-md-2">Product</div>
                                <div class="col-md-3">Supplier</div>
                                <div class="col-md-3">Type</div>
                                <div class="col-md-2">Colour</div>
                                {{--}}<div class="col-md-2">Notes</div>--}}
                            </div>
                            <hr class="hidden-sm hidden-xs" style="padding: 0px; margin: 5px 0px 20px 0px;">

                            {{-- Products --}}
                            @foreach ($products as $product)
                                {!! Form::hidden("supplier-$product->id", null, ['class' => 'form-control', 'id' => "supplier-$product->id"]) !!}
                                {!! Form::hidden("type-$product->id", null, ['class' => 'form-control', 'id' => "type-$product->id"]) !!}

                                <?php $supplierOpts = $product->supplyOptions('supplier'); ?>
                                <?php $typeOpts = $product->supplyOptions('type'); ?>
                                <div class="row">
                                    <div class="col-md-2">
                                        <div class="hidden-sm hidden-xs">
                                            {{ $product->name }}
                                        </div>
                                        <div class="visible-sm visible-xs">
                                            <br><b>{{ $product->name }}</b>
                                            <hr class="visible-sm visible-xs" style="padding: 0px; margin: 5px 0px 20px 0px;">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        {{-- Supplier --}}
                                        <div class="visible-sm visible-xs">Supplier</div>
                                        @if ($supplierOpts)
                                            <div id="div-supplier-opt-{{$product->id}}">
                                                <div class="form-group">
                                                    {!! Form::select("supplier_opt_$product->id", $supplierOpts, null, ['class' => 'form-control bs-select supplyOption']) !!}
                                                </div>
                                            </div>
                                        @endif
                                        <div id="div-supplier-txt-{{$product->id}}" @if ($supplierOpts) style="display: none" @endif>
                                            <div class="form-group">
                                                {!! Form::text("supplier_txt_$product->id", null, ['class' => 'form-control supplyText', 'placeholder' => 'Enter supplier']) !!}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        {{-- Type --}}
                                        <div class="visible-sm visible-xs">Type</div>
                                        @if ($typeOpts)
                                            <div id="div-type-opt-{{$product->id}}">
                                                <div class="form-group">
                                                    {!! Form::select("type_opt_$product->id", $typeOpts, null, ['class' => 'form-control bs-select typeOption']) !!}
                                                </div>
                                            </div>
                                        @endif
                                        <div id="div-type-txt-{{$product->id}}" @if ($typeOpts) style="display: none" @endif>
                                            <div class="form-group">
                                                {!! Form::text("type_txt_$product->id", null, ['class' => 'form-control typeText', 'placeholder' => 'Enter type']) !!}
                                            </div>
                                        </div>
                                    </div>
                                    {{-- Colour --}}
                                    <div class="col-md-2">
                                        <div class="visible-sm visible-xs">Colour</div>{!! Form::text("colour-$product->id", $product->colour, ['class' => 'form-control']) !!}</div>
                                    {{-- Notes --}}
                                    {{--}}
                                    <div class="col-md-2">
                                        <div class="visible-sm visible-xs"><br>Notes</div>
                                        {!! Form::text("notes_$product->id", null, ['class' => 'form-control',]) !!}
                                    </div>--}}
                                </div>
                                <hr class="hidden-sm hidden-xs" style="padding: 0px; margin: 0px 0px 10px 0px;">
                                <div class="visible-sm visible-xs"><br></div>
                            @endforeach

                            <br><br>
                            <div class="form-actions right">
                                <a href="/site/supply" class="btn default"> Back</a>
                                <button type="submit" class="btn green"> Save</button>
                            </div>

                        </div> <!-- /Form body -->
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop


@section('page-level-plugins-head')
    <link href="/assets/global/plugins/select2/css/select2.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/select2/css/select2-bootstrap.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/bootstrap-datepicker/css/bootstrap-datepicker3.min.css" rel="stylesheet" type="text/css"/>
    <link href="/css/libs/fileinput.min.css" media="all" rel="stylesheet" type="text/css"/>
@stop

@section('page-level-plugins')
    <script src="/assets/global/plugins/select2/js/select2.full.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js" type="text/javascript"></script>
    <script src="/js/libs/fileinput.min.js"></script>
@stop

@section('page-level-scripts') {{-- Metronic + custom Page Scripts --}}
<script src="/assets/pages/scripts/components-date-time-pickers.min.js" type="text/javascript"></script>
<script>
    $(document).ready(function () {
        /* Select2 */
        $("#site_id").select2({placeholder: "Select Site",});

        function updateField(field, id, val) {
            if (val == 'other') {
                $("#" + field + "-" + id).val('');
                $("#div-" + field + "-txt-" + id).show();
            } else {
                $("#" + field + "-" + id).val(val);
                $("#div-" + field + "-txt-" + id).hide();
            }
        }

        //
        // On Change Dropdown option
        //

        // Supply option
        $(".supplyOption").change(function () {
            var name = $(this).attr('name');
            if (name) updateField('supplier', name.substr(13), $(this).val());
        });

        // Type option
        $(".typeOption").change(function () {
            var name = $(this).attr('name');
            if (name) updateField('type', name.substr(9), $(this).val());
        });

        // Colour option
        $(".colourOption").change(function () {
            var name = $(this).attr('name');
            if (name) updateField('colour', name.substr(11), $(this).val());
        });


        //
        // Text field updated
        //

        // Supply text
        $(".supplyText").change(function () {
            var name = $(this).attr('name');
            if (name) {
                var id = name.substr(13);
                $("#supplier-" + id).val($(this).val());
            }
        });

        // Type text
        $(".typeText").change(function () {
            var name = $(this).attr('name');
            if (name) {
                var id = name.substr(9);
                $("#type-" + id).val($(this).val());
            }
        });

        // Colour text
        $(".colourText").change(function () {
            var name = $(this).attr('name');
            if (name) {
                var id = name.substr(11);
                $("#colour-" + id).val($(this).val());
            }
        });
    });
</script>
@stop

