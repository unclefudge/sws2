@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        <li><a href="/equipment">Equipment Allocation</a><i class="fa fa-circle"></i></li>
        @if (Auth::user()->allowed2('add.equipment'))
            <li><a href="/equipment/inventory">Inventory</a><i class="fa fa-circle"></i></li>
        @endif
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
                            <span class="caption-subject font-green-haze bold uppercase">Edit Item </span>
                            <span class="caption-helper"> - ID: {{ $item->id }}</span>
                        </div>
                    </div>
                    <div class="portlet-body form">
                        <form method="POST" action="{{ action([App\Http\Controllers\Misc\EquipmentController::class, 'update'], $item->id) }}" class="horizontal-form" enctype="multipart/form-data">
                            @csrf
                            @method('PATCH')
                        <?php $sub_cat = $item->category_id; ?>
                        @include('form-error')

                        <div class="form-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <x-form.input name="name" label="Item Name" :value="$item->name"/>
                                </div>
                                <div class="col-md-3">
                                    <x-form.select name="category_id" label="Category" :options="\App\Models\Misc\Equipment\EquipmentCategory::where('parent', 0)->orderBy('name')->pluck('name', 'id')->toArray()" :value="($item->category->parent == 0) ? $item->category_id : $item->category->parent"/>
                                </div>
                                <div class="col-md-3" id="field-subcat">
                                    <?php $subcat_array = ['' => 'Select sub-category'] + \App\Models\Misc\Equipment\EquipmentCategory::where('parent', 3)->orderBy('name')->pluck('name', 'id')->toArray(); ?>
                                    <x-form.select name="subcategory_id" label="Sub Category" :options="$subcat_array" :value="$item->category_id"/>
                                </div>
                            </div>

                            {{-- Purchase --}}
                            <div class="row" id="purchase-div">
                                <div class="col-md-2" id="field-length">
                                    <x-form.input name="length" label="Length" :value="$item->length" placeholder="N/A"/>
                                </div>
                                <div class="col-md-2" id="field-minstock">
                                    <x-form.input name="min_stock" label="Minimum Required Stock" onkeydown="return isNumber(event)"/>
                                </div>
                                <div class="col-md-2">
                                    <x-form.input name="purchase_qty" label="No. of items to purchase" onkeydown="return isNumber(event)"/>
                                    {{--}}
                                    <select id="purchase_qty" name="purchase_qty" class="form-control bs-select" style="width:100%">
                                        @for ($i = 0; $i < 100; $i++)
                                            <option value="{{ $i }}">{{ $i }}</option>
                                        @endfor
                                    </select>--}}
                                    <?php $red_font = ($item->category_id == 19 && $item->total < $item->min_stock) ? 'font-red' : '' ?>
                                    <span class="help-block {{$red_font}}">Currently in stock: {{ $item->total }}</span>
                                </div>
                                <div class="col-md-6">
                                    <br>
                                    <div class="note note-warning"><b>Note:</b> Purchased items will be initially allocated to CAPE COD STORE</div>
                                </div>
                            </div>

                            {{-- Photo --}}
                            <img src="{{ $item->attachment }}" alt=""/>
                            <div class="form-group">
                                <div class="fileinput fileinput-new" data-provides="fileinput">
                                    <div class="fileinput-new thumbnail" style="width: 150px; height: 150px;">
                                        @if($item->attachment)
                                            <img src="{{ $item->attachmentUrl }}" alt=""/>
                                        @else
                                            <img src="/img/no_image.png" alt=""/>
                                        @endif
                                    </div>
                                    <div class="fileinput-preview fileinput-exists thumbnail" style="max-width: 200px; max-height: 150px;"></div>
                                    <div>
                                    <span class="btn default btn-file">
                                        <span class="fileinput-new"> Select image </span>
                                        <span class="fileinput-exists"> Change </span>
                                        <input type="file" name="media"> </span>
                                        <a href="javascript:;" class="btn default fileinput-exists" data-dismiss="fileinput"> Remove </a>
                                    </div>
                                </div>
                            </div>

                            <div class="form-actions right">
                                <a href="/equipment/inventory" class="btn default"> Back</a>
                                @if (Auth::user()->allowed2('del.equipment', $item))
                                    <button class="btn red" id="btn-delete">Delete</button>
                                @endif
                                <button type="submit" name="save" value="save" class="btn green">Save</button>
                            </div>
                        </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div>
            <div class="pull-right" style="font-size: 12px; font-weight: 200; padding: 10px 10px 0 0">
                {!! $item->displayUpdatedBy() !!}
            </div>
        </div>
    </div>
@stop

@section('page-level-plugins-head')
    <link href="/assets/global/plugins/bootstrap-fileinput/bootstrap-fileinput.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/pages/css/profile-2.min.css" rel="stylesheet" type="text/css"/>
@stop

@section('page-level-plugins')
    <script src="/assets/global/plugins/bootstrap-fileinput/bootstrap-fileinput.js" type="text/javascript"></script>
@stop

@section('page-level-scripts')
    {{-- Metronic + custom Page Scripts --}}
    <script>
        $(document).ready(function () {
            $('#category_id').change(function () {
                displayFields();
            });

            $('#subcategory_id').change(function () {
                displayFields();
            });

            displayFields();

            function displayFields() {
                $('#field-subcat').hide()
                $('#field-length').hide()
                $('#field-minstock').hide()

                if ($('#category_id').val() == 3) {
                    $('#field-subcat').show();
                    $('#field-length').show();
                }
                if ($('#category_id').val() == 3 && $('#subcategory_id').val() == 19) {
                    $('#field-minstock').show();
                }
            }

            $("#btn-delete").click(function (e) {
                e.preventDefault();
                swal({
                    title: "Are you sure?",
                    text: "This action can't be undone and all records of it will be <b>DELETED</b>!<br><b>" + name + "</b>",
                    showCancelButton: true,
                    cancelButtonColor: "#555555",
                    confirmButtonColor: "#E7505A",
                    confirmButtonText: "Yes, delete it!",
                    allowOutsideClick: true,
                    html: true,
                }, function () {
                    window.location.href = "/equipment/{{ $item->id }}/delete";
                });
            });
        });

        function isNumber(evt) {
            evt = (evt) ? evt : window.event;
            var charCode = (evt.which) ? evt.which : evt.keyCode;
            if ((charCode > 31 && charCode < 48) || charCode > 57) {
                return false;
            }
            return true;
        }
    </script>
@stop