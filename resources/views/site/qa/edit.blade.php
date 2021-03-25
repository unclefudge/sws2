@extends('layout')

@section('pagetitle')
    <div class="page-title">
        <h1><i class="fa fa-file-text-o"></i> Quality Assurance Reports</h1>
    </div>
@stop
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
                            <i class="fa fa-pencil "></i>
                            <span class="caption-subject font-green-haze bold uppercase">Edit Report</span>
                            <span class="caption-helper">ID: {{ $qa->id }}</span>
                        </div>
                    </div>
                    <div class="portlet-body form">
                        <!-- BEGIN FORM-->
                        {!! Form::model($qa, ['method' => 'PATCH', 'action' => ['Site\SiteQaController@update', $qa->id], 'class' => 'horizontal-form', 'files' => true, 'id'=>'qa_form']) !!}
                        @include('form-error')

                        <input type="hidden" name="master" value="{{ $qa->master }}">
                        <input type="hidden" name="version" value="{{ $qa->version }}">
                        <input type="hidden" name="company_id" value="{{ $qa->company_id }}">
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
                                    <div class="form-group {!! fieldHasError('name', $errors) !!}">
                                        {!! Form::label('name', 'Name', ['class' => 'control-label']) !!}
                                        {!! Form::text('name', $qa->name, ['class' => 'form-control']) !!}
                                        {!! fieldErrorMessage('name', $errors) !!}
                                    </div>
                                </div>
                                <div class="col-md-2 pull-right">
                                    <div class="form-group {!! fieldHasError('status', $errors) !!}">
                                        {!! Form::label('status', 'Status', ['class' => 'control-label']) !!}
                                        @if (Auth::user()->hasPermission2('del.site.qa.templates'))
                                            {!! Form::select('status', ['1' => 'Active', '0' => 'Inactive'], $qa->status, ['class' => 'form-control bs-select']) !!}
                                        @else
                                            {!! Form::text('status_text', ($qa->status) ? 'Active' : 'Inactive', ['class' => 'form-control', 'readonly']) !!}
                                        @endif
                                            {!! fieldErrorMessage('status', $errors) !!}
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group {!! fieldHasError('category_id', $errors) !!}">
                                        {!! Form::label('category_id', 'Category', ['class' => 'control-label']) !!}
                                        {!! Form::select('category_id', (['' => 'Select category'] + \App\Models\Site\SiteQaCategory::all()->sortBy('name')->pluck('name' ,'id')->toArray()), null, ['class' => 'form-control select2', 'title' => 'Select category', 'id' => 'category_id']) !!}
                                        {!! fieldErrorMessage('category_id', $errors) !!}
                                        Note: If you change category this won't update any currently active/past QA's
                                    </div>
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
                                        <div class="form-group">
                                            {!! Form::textarea("item$item->order", $item->name, ['rows' => '2', 'class' => 'form-control', 'placeholder' => "Item $item->order.", 'id' => "item$item->order"]) !!}
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group {!! fieldHasError("task$item->order", $errors) !!}">
                                            {!! Form::select("task$item->order", Auth::user()->company->taskSelect(),$item->task_id, ['class' => 'form-control select2 task_sel']) !!}
                                            {!! fieldErrorMessage("task$item->order", $errors) !!}
                                        </div>
                                    </div>
                                    <div class="col-md-1">
                                        <div class="form-group">
                                            <div class="mt-checkbox-list">
                                                <label class="mt-checkbox mt-checkbox-outline">
                                                    {!! Form::checkbox("super$item->order", 1, $item->super, ['class' => 'mt-checkbox']) !!}
                                                    <span></span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-1">
                                        <div class="form-group">
                                            <div class="mt-checkbox-list">
                                                <label class="mt-checkbox mt-checkbox-outline">
                                                    {!! Form::checkbox("cert$item->order", 1, $item->certification, ['class' => 'mt-checkbox']) !!}
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
                                            <div class="form-group">{!! Form::text("item$i", null, ['class' => 'form-control', 'placeholder' => "Item $i."]) !!}</div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group {!! fieldHasError("task$i", $errors) !!}">
                                                <select id="task{{$i}}" name="task{{$i}}" class="form-control select2 task_sel" style="width: 100%">
                                                    <option value=""></option>
                                                    @foreach(Auth::user()->company->taskSelect() as $value => $name)
                                                        <option value="{{ $value }}" @if ($value == old("task$i")) selected @endif >{{ $name }}</option>
                                                    @endforeach
                                                </select>
                                                {!! fieldErrorMessage("task$i", $errors) !!}
                                            </div>
                                        </div>
                                        <div class="col-md-1">
                                            <div class="form-group">
                                                <div class="mt-checkbox-list">
                                                    <label class="mt-checkbox mt-checkbox-outline">
                                                        {!! Form::checkbox("super$i", 1, null, ['class' => 'mt-checkbox']) !!}
                                                        <span></span>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-1">
                                            <div class="form-group">
                                                <div class="mt-checkbox-list">
                                                    <label class="mt-checkbox mt-checkbox-outline">
                                                        {!! Form::checkbox("cert$i", 1, null, ['class' => 'mt-checkbox']) !!}
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
                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </div>
    @stop <!-- END Content -->


@section('page-level-plugins-head')
    <link href="/assets/global/plugins/select2/css/select2.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/select2/css/select2-bootstrap.min.css" rel="stylesheet" type="text/css"/>
    <link href="/css/libs/fileinput.min.css" media="all" rel="stylesheet" type="text/css"/>
@stop

@section('page-level-plugins')
    <script src="/assets/global/plugins/select2/js/select2.full.min.js" type="text/javascript"></script>
    <script src="/js/libs/fileinput.min.js"></script>
@stop

@section('page-level-scripts') {{-- Metronic + custom Page Scripts --}}
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

