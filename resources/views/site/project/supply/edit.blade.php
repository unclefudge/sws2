@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        @if (Auth::user()->hasAnyPermissionType('site'))
            <li><a href="/site">Sites</a><i class="fa fa-circle"></i></li>
        @endif
        <li><a href="/site/supply">Project Supply Info</a><i class="fa fa-circle"></i></li>
        <li><span>Edit</span></li>
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
                            <span class="caption-subject font-green-haze bold uppercase">Project Supply Infomation</span>
                            <span class="caption-helper"></span>
                        </div>
                        <div class="actions">
                            @if ($project->attachment)
                                <a class="btn btn-circle green btn-outline btn-sm" href="{{ $project->attachment_url }}" target="_blank" data-original-title="PDF"><i class="fa fa-file-pdf-o"></i> Report </a>
                            @endif
                        </div>
                    </div>
                    <div class="portlet-body form">
                        <!-- BEGIN FORM-->
                        {!! Form::model($project, ['method' => 'PATCH', 'action' => ['Site\SiteProjectSupplyController@update', $project->id], 'class' => 'horizontal-form', 'files' => true]) !!}
                        @include('form-error')

                        <div class="form-body">
                            {{-- Site --}}
                            <div class="row">
                                <div class="col-md-7">
                                    <h2 style="margin-top: 0px">{{ $project->site->name }}</h2>
                                    {{ $project->site->fulladdress }}
                                </div>
                                <div class="col-md-5">
                                    @if (!$project->status)
                                        <h2 class="font-red pull-right" style="margin-top: 0px">COMPLETED</h2>
                                    @endif
                                    <b>Job #:</b> {{ $project->site->code }}<br>
                                    <b>Supervisor(s):</b> {{ $project->site->supervisorsSBC() }}<br>
                                </div>
                            </div>
                            <hr style="padding: 0px; margin: 0px 0px 30px 0px">

                            <h3>Project Supply Information</h3>
                            <hr style="padding: 0px; margin: 0px 0px 30px 0px">

                            <div class="row bold hidden-sm hidden-xs">
                                <div class="col-md-2">{{ $title->name }}</div>
                                <div class="col-md-3">{{ $title->supplier }}</div>
                                <div class="col-md-3">{{ $title->type }}</div>
                                <div class="col-md-2">{{ $title->colour }}</div>
                                {{--}}<div class="col-md-2">Notes</div>--}}
                            </div>
                            <hr class="hidden-sm hidden-xs" style="padding: 0px; margin: 5px 0px 20px 0px;">

                            {{-- Products --}}
                            @foreach ($project->itemsOrdered() as $item)

                                {!! Form::hidden("product-$item->id",  $item->product, ['class' => 'form-control', 'id' => "product-$item->id"]) !!}
                                {!! Form::hidden("supplier-$item->id",  $item->supplier, ['class' => 'form-control', 'id' => "supplier-$item->id"]) !!}
                                {!! Form::hidden("type-$item->id",  $item->type, ['class' => 'form-control', 'id' => "type-$item->id"]) !!}

                                <?php
                                $supplierOpts = $item->productRef->supplyOptions('supplier');
                                $typeOpts = $item->productRef->supplyOptions('type');
                                $type = $supplier = null;
                                if ($item->type)
                                    $type = (in_array($item->type, $typeOpts)) ? $item->type : 'other';
                                if ($item->supplier)
                                    $supplier = (in_array($item->supplier, $supplierOpts)) ? $item->supplier : 'other';
                                ?>
                                <div class="row" id="item-{{ $item->id }}">
                                    <div class="col-md-2">
                                        <div class="hidden-sm hidden-xs">
                                            {{ ($item->product_id == 2) ? 'Special Item' : $item->product }}
                                        </div>
                                        <div class="visible-sm visible-xs">
                                            <br><b>{{ ($item->product_id == 2) ? 'Special Item' : $item->product }}</b>
                                            <hr class="visible-sm visible-xs" style="padding: 0px; margin: 5px 0px 20px 0px;">
                                            @if ($item->product_id == 2)
                                                <div>Product</div> @endif
                                        </div>
                                        @if ($item->product_id == 2)
                                            <div class="form-group">
                                                {!! Form::text("product_txt_$item->id", $item->product, ['class' => 'form-control productText', 'placeholder' => 'Enter product']) !!}
                                            </div>
                                        @endif
                                    </div>
                                    <div class="col-md-3">
                                        {{-- Supplier --}}
                                        <div class="visible-sm visible-xs">Supplier</div>
                                        @if ($supplierOpts)
                                            <div id="div-supplier-opt-{{$item->id}}">
                                                <div class="form-group">
                                                    {!! Form::select("supplier_opt_$item->id", $supplierOpts, $supplier, ['class' => 'form-control bs-select supplyOption']) !!}
                                                </div>
                                            </div>
                                        @endif
                                        <div id="div-supplier-txt-{{$item->id}}" @if ($supplier != 'other' && $supplierOpts) style="display: none" @endif>
                                            @if ($item->product_id == 2) <br class="hidden-sm hidden-xs"> @endif
                                            <div class="form-group">
                                                {!! Form::text("supplier_txt_$item->id", $item->supplier, ['class' => 'form-control supplyText', 'placeholder' => 'Enter supplier']) !!}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        {{-- Type --}}
                                        <div class="visible-sm visible-xs">Type</div>
                                        @if ($typeOpts)
                                            <div id="div-type-opt-{{$item->id}}">
                                                <div class="form-group">
                                                    {!! Form::select("type_opt_$item->id", $typeOpts, $type, ['class' => 'form-control bs-select typeOption']) !!}
                                                </div>
                                            </div>
                                        @endif
                                        <div id="div-type-txt-{{$item->id}}" @if ($type != 'other' && $typeOpts) style="display: none" @endif>
                                            @if ($item->product_id == 2) <br class="hidden-sm hidden-xs"> @endif
                                            <div class="form-group">
                                                {!! Form::text("type_txt_$item->id", $item->type, ['class' => 'form-control typeText', 'placeholder' => 'Enter type']) !!}
                                            </div>
                                        </div>
                                    </div>
                                    {{-- Colour --}}
                                    <div class="col-md-2">
                                        <div class="visible-sm visible-xs">Colour</div>
                                        @if ($item->product_id == 2) <br class="hidden-sm hidden-xs"> @endif
                                        {!! Form::text("colour-$item->id", $item->colour, ['class' => 'form-control']) !!}
                                    </div>
                                    <div class="col-md-2">
                                        <a href="#"><i class="fa fa-times font-red delItem" id="del-{{$item->id}}" name="del-{{$item->product}}"></i></a>
                                    </div>
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

                            {{-- Special Items --}}
                            <div class="row">
                                <div class="col-md-12">
                                    <button class="btn blue" id="btn-add-item">Add Special Items</button>
                                </div>
                            </div>

                            <div id="add-items" style="display: none">
                                <h3>Special Items</h3>
                                <hr style="padding: 0px; margin: 0px 0px 10px 0px;">
                                @for ($i = 1; $i <= 5; $i++)
                                    {!! Form::hidden("product-s$i", null, ['class' => 'form-control', 'id' => "product-s$i"]) !!}
                                    {!! Form::hidden("supplier-s$i", null, ['class' => 'form-control', 'id' => "supplier-s$i"]) !!}
                                    {!! Form::hidden("type-s$i",  null, ['class' => 'form-control', 'id' => "type-s$i"]) !!}
                                    <div class="row">
                                        {{-- Product --}}
                                        <div class="col-md-2">
                                            <div class="visible-sm visible-xs">
                                                <br><b>Special Item }}</b>
                                                <hr class="visible-sm visible-xs" style="padding: 0px; margin: 5px 0px 20px 0px;">
                                                <div>Product</div>
                                            </div>
                                            <div class="form-group">
                                                {!! Form::text("product_txt_s$i", null, ['class' => 'form-control productText', 'placeholder' => 'Enter product']) !!}
                                            </div>
                                        </div>
                                        <div class="col-md-3" id="div-supplier-txt-s{{$i}}">
                                            {{-- Supplier --}}
                                            <div class="visible-sm visible-xs">Supplier</div>
                                            <div>
                                                <div class="form-group">
                                                    {!! Form::text("supplier_txt_s$i", null, ['class' => 'form-control supplyText', 'placeholder' => 'Enter supplier']) !!}
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3" id="div-type-s{{$i}}">
                                            {{-- Type --}}
                                            <div class="visible-sm visible-xs">Type</div>
                                            <div id="div-type-txt-s{{$i}}">
                                                <div class="form-group">
                                                    {!! Form::text("type_txt_s$i", null, ['class' => 'form-control typeText', 'placeholder' => 'Enter type']) !!}
                                                </div>
                                            </div>
                                        </div>
                                        {{-- Colour --}}
                                        <div class="col-md-2" id="div-colour-s{{$i}}">
                                            <div class="visible-sm visible-xs">Colour</div>{!! Form::text("colour-s$i", null, ['class' => 'form-control', 'placeholder' => 'Enter colour']) !!}</div>
                                        {{-- Notes --}}
                                        {{--}}
                                        <div class="col-md-2">
                                            <div class="visible-sm visible-xs"><br>Notes</div>
                                            {!! Form::text("notes_s$i", null, ['class' => 'form-control',]) !!}
                                        </div>--}}
                                    </div>
                                    <hr class="hidden-sm hidden-xs" style="padding: 0px; margin: 0px 0px 10px 0px;">
                                    <div class="visible-sm visible-xs"><br></div>
                                @endfor

                            </div>

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

        // Add extra items
        $("#btn-add-item").click(function (e) {
            e.preventDefault();
            $("#add-items").show();
            $(".add-item").show();
            $("#btn-add-item").hide();
        });

        function updateField(field, id, val) {
            //alert('id:'+id+' val:'+val);
            if (val == 'other') {
                $("#" + field + "-" + id).val('');
                $("#div-" + field + "-txt-" + id).show();
            } else {
                $("#" + field + "-" + id).val(val);
                $("#div-" + field + "-txt-" + id).hide();
                //alert(val);
                if (val == 'n/a') {
                    $("#div-type-s" + id).hide();
                }
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

        // Product text
        $(".productText").change(function () {
            var name = $(this).attr('name');
            if (name) {
                var id = name.substr(12);
                $("#product-" + id).val($(this).val());
            }
        });

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

        // Delete Item
        $(".delItem").click(function (e) {
            e.preventDefault();
            var id = $(this).attr('id');
            var name = $(this).attr('name');
            if (id) {
                swal({
                    title: "Are you sure?",
                    text: "You will not be able to recover this item!<br><b>" + name.substr(4) + "</b>",
                    showCancelButton: true,
                    cancelButtonColor: "#555555",
                    confirmButtonColor: "#E7505A",
                    confirmButtonText: "Yes, delete it!",
                    allowOutsideClick: true,
                    html: true,
                }, function () {
                    var item_id = id.substr(4);
                    $("#product-" + item_id).val('DELETE-ITEM');
                    $("#item-" + item_id).hide();
                });
            }

        });
    });
</script>
@stop

