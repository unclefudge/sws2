@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        @if (Auth::user()->hasAnyPermissionType('site'))
            <li><a href="/site">Sites</a><i class="fa fa-circle"></i></li>
        @endif
        <li><a href="/site/qa">Quality Assurance</a><i class="fa fa-circle"></i></li>
        <li><span>Create Template</span></li>
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
                            <span class="caption-subject font-green-haze bold uppercase">Create New Template</span>
                            <span class="caption-helper"></span>
                        </div>
                    </div>
                    <div class="portlet-body form">
                        <!-- BEGIN FORM-->
                        {!! Form::model('SiteQa', ['action' => 'Site\SiteQaController@store', 'class' => 'horizontal-form', 'files' => true]) !!}
                        @include('form-error')

                        <input type="hidden" name="master" value="1">
                        <input type="hidden" name="version" value="1.0">
                        <input type="hidden" name="company_id" value="{{ Auth::user()->company_id }}">
                        <div class="form-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group {!! fieldHasError('name', $errors) !!}">
                                        {!! Form::label('name', 'Name', ['class' => 'control-label']) !!}
                                        {!! Form::text('name', null, ['class' => 'form-control']) !!}
                                        {!! fieldErrorMessage('name', $errors) !!}
                                    </div>
                                </div>
                                <div class="col-md-2 pull-right">
                                    <div class="form-group {!! fieldHasError('status', $errors) !!}">
                                        {!! Form::label('status', 'Status', ['class' => 'control-label']) !!}
                                        {!! Form::select('status', ['1' => 'Active', '0' => 'Inactive'],
                                         0, ['class' => 'form-control bs-select']) !!}
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
                                    </div>
                                </div>
                            </div>

                            <!-- Items -->
                            <br>
                            <div class="row" style="border: 1px solid #e7ecf1; padding: 10px 0px; margin: 0px; background: #f0f6fa; font-weight: bold">
                                <div class="col-md-6">INSPECTION ITEMS</div>
                                <div class="col-md-3">TASK TRIGGER</div>
                                <div class="col-md-2" style="text-align:right">SUPERVISOR<br>COMPLETES</div>
                                <div class="col-md-1" style="text-align:right">CERTIF-ICATION</div>
                            </div>
                            <br>
                            @for ($i = 1; $i <= 15; $i++)
                                <div class="row">
                                    <div class="col-xs-6">
                                        <div class="form-group">{!! Form::textarea("item$i", '', ['rows' => '2', 'class' => 'form-control', 'placeholder' => "Item $i."]) !!}</div>
                                    </div>
                                    <div class="col-xs-4">
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
                                </div>
                            @endfor

                            {{-- Extra Fields --}}
                            <button class="btn blue" id="more">More Items</button>
                            <div class="row" id="more_items" style="display: none">
                                @for ($i = 16; $i <= 25; $i++)
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
                        </div>
                        <div class="form-actions right">
                            <a href="/site/qa" class="btn default"> Back</a>
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

        $("#more").click(function (e) {
            e.preventDefault();
            $('#more').hide();
            $('#more_items').show();
        });

        /* Select2 */
        $("#category_id").select2({placeholder: "Select category", width: "100%"});
        $(".task_sel").select2({placeholder: "Select task",});
    });
</script>
@stop

