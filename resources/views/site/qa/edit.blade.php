@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        @if (Auth::user()->hasAnyPermissionType('site'))
            <li><a href="/site">Sites</a><i class="fa fa-circle"></i></li>
        @endif
        <li><a href="/site/qa">Quality Assurance</a><i class="fa fa-circle"></i></li>
        <li><span>Edit Report</span></li>
    </ul>
@stop

@section('content')
    <div class="page-content-inner">
        <div class="row">
            <div class="col-md-12">
                <div class="portlet light bordered">
                    <div class="portlet-title">
                        <div class="caption">
                            <span class="caption-subject font-green-haze bold uppercase">Edit Report</span>
                            <span class="caption-helper">ID: {{ $qa->id }}</span>
                        </div>
                    </div>
                    <div class="portlet-body form">
                        <form method="POST" action="{{ action([App\Http\Controllers\Site\SiteQaController::class, 'update'], $qa->id) }}" class="horizontal-form" enctype="multipart/form-data" id="qa_form">
                            @csrf
                            @method('PATCH')
                        @include('form-error')

                        <x-form.hidden name="master" :value="$qa->master"/>
                        <x-form.hidden name="version" :value="$qa->version"/>
                        <x-form.hidden name="company_id" :value="$qa->company_id"/>
                        <div class="form-body">
                            @if ($qa->master)
                                <div class="row" style="padding-bottom: 10px">
                                    <div class="col-xs-12 ">
                                        <h2 style="margin: 0px">
                                            <span class="pull-right font-red hidden-sm hidden-xs">TEMPLATE</span>
                                            <span class="text-center font-red visible-sm visible-xs">TEMPLATE</span>
                                        </h2>
                                    </div>
                                </div>
                            @endif
                            <div class="row">
                                <div class="col-md-6">
                                    <x-form.input name="name" label="Name" :value="$qa->name"/>
                                </div>
                                <div class="col-md-2 pull-right">
                                    @if (Auth::user()->hasPermission2('del.site.qa.templates'))
                                        <x-form.select name="status" label="Status" :options="['1' => 'Active', '0' => 'Inactive']" :value="$qa->status"/>
                                    @else
                                        <x-form.input name="status_text" label="Status" :value="($qa->status) ? 'Active' : 'Inactive'" readonly/>
                                    @endif
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <x-form.select name="category_id" id="category_id" label="Category" :options="['' => 'Select category'] + \App\Models\Site\SiteQaCategory::all()->sortBy('name')->pluck('name', 'id')->toArray()" :value="$qa->category_id" plugin="select2" title="Select category"/>
                                    Note: If you change category this won't update any currently active/past QA's
                                </div>
                            </div>

                            <!-- Items -->
                            <br>
                            <div class="row" style="border: 1px solid #e7ecf1; padding: 10px 0px; margin: 0px; background: #f0f6fa; font-weight: bold">
                                <div class="col-md-6">INSPECTION ITEMS</div>
                                <div class="col-md-2">TASK TRIGGER</div>
                                <div class="col-md-2" style="text-align:right">SUPERVISOR<br>COMPLETES</div>
                                <div class="col-md-1" style="text-align:right">CERTIF-ICATION</div>
                                <div class="col-md-1" style="text-align:right"></div>
                            </div>
                            <br>
                            <!-- Items -->
                            @foreach ($qa->items->sortBy('order') as $item)
                                <div class="row" id="itemrow{{ $item->order }}">
                                    <div class="col-md-6">
                                        <x-form.textarea :name="'item' . $item->order" rows="2" :value="$item->name" :placeholder="'Item ' . $item->order . '.'"/>
                                    </div>
                                    <div class="col-md-3">
                                        <x-form.select :name="'task' . $item->order" :id="'task' . $item->order" :options="Auth::user()->company->taskSelect()" :value="$item->task_id" plugin="select2 task_sel"/>
                                    </div>
                                    <div class="col-md-1">
                                        <div class="form-group">
                                            <div class="mt-checkbox-list">
                                                <label class="mt-checkbox mt-checkbox-outline">
                                                    <input type="checkbox" name="super{{ $item->order }}" value="1" class="mt-checkbox" {{ old('super' . $item->order, $item->super) ? 'checked' : '' }}>
                                                    <span></span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-1">
                                        <div class="form-group">
                                            <div class="mt-checkbox-list">
                                                <label class="mt-checkbox mt-checkbox-outline">
                                                    <input type="checkbox" name="cert{{ $item->order }}" value="1" class="mt-checkbox" {{ old('cert' . $item->order, $item->certification) ? 'checked' : '' }}>
                                                    <span></span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-1">
                                        <a id="del{{$item->order}}" name="del{{$item->order}}" class="deleteItem" onClick="deleteItem(this.id)"><i class="fa fa-times font-red"></i> </a>
                                    </div>
                                </div>
                            @endforeach

                            <!-- Extra Fields -->
                            <button class="btn blue" id="more">More Items</button>
                            <div class="row" id="more_items" style="display: none">
                                @for ($i = $qa->items->count() + 1; $i <= 25; $i++)
                                    <div class="col-md-6">
                                        <x-form.input :name="'item' . $i" :placeholder="'Item ' . $i . '.'"/>
                                    </div>
                                    <div class="col-md-4">
                                        <x-form.select :name="'task' . $i" :id="'task' . $i" :options="['' => ''] + Auth::user()->company->taskSelect()" plugin="select2 task_sel" style="width:100%"/>
                                    </div>
                                    <div class="col-md-1">
                                        <div class="form-group">
                                            <div class="mt-checkbox-list">
                                                <label class="mt-checkbox mt-checkbox-outline">
                                                    <input type="checkbox" name="super{{ $i }}" value="1" class="mt-checkbox" {{ old("super$i") ? 'checked' : '' }}>
                                                    <span></span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-1">
                                        <div class="form-group">
                                            <div class="mt-checkbox-list">
                                                <label class="mt-checkbox mt-checkbox-outline">
                                                    <input type="checkbox" name="cert{{ $i }}" value="1" class="mt-checkbox" {{ old("cert$i") ? 'checked' : '' }}>
                                                    <span></span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                @endfor
                            </div>

                            <!-- Version -->
                            <div class="row">
                                <div class="col-md-3 pull-right text-right" style="margin-top: 15px; padding-right: 20px">
                                    <span class="font-grey-salsa"><span class="font-grey-salsa">version {{ $qa->version }} </span>
                                </div>
                            </div>
                        </div>
                        <div class="form-actions right">
                            <a href="{{ url()->previous() }}" class="btn default"> Back</a>
                            <button type="submit" class="btn green"> Save</button>
                        </div>
                    </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@stop


@section('page-level-plugins-head')
    <link href="/assets/global/plugins/select2/css/select2.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/select2/css/select2-bootstrap.min.css" rel="stylesheet" type="text/css"/>
    <link href="/css/libs/fileinput.min.css" media="all" rel="stylesheet" type="text/css"/>
@stop

@section('page-level-plugins')
    <script src="/assets/global/plugins/select2/js/select2.full.min.js" type="text/javascript"></script>
    <script src="/js/libs/fileinput.min.js"></script>
@stop

@section('page-level-scripts')
    {{-- Metronic + custom Page Scripts --}}
    <script>
        $(document).ready(function () {
            /* Select2 */
            $("#category_id").select2({placeholder: "Select category", width: "100%"});
            $(".task_sel").select2({placeholder: "Select task",});

            $("#more").click(function (e) {
                e.preventDefault();
                $('#more').hide();
                $('#more_items').show();
            });
        });

        function deleteItem(item_id) {
            var id = item_id.substring(3);
            var item_name = $("#item" + id).val();
            swal({
                title: "Are you sure?",
                text: item_name,
                showCancelButton: true,
                cancelButtonColor: "#555555",
                confirmButtonColor: "#E7505A",
                confirmButtonText: "Yes, delete it!",
                allowOutsideClick: true,
                html: true,
            }, function () {
                $("#item" + id).val('DELETE-ITEM');
                $("#itemrow" + id).hide();
            });
        }
        ;
    </script>
@stop

