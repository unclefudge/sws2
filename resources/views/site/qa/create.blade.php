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
                            <span class="caption-subject font-green-haze bold uppercase">Create New Template</span>
                            <span class="caption-helper"></span>
                        </div>
                    </div>
                    <div class="portlet-body form">
                        <form method="POST" action="{{ action([App\Http\Controllers\Site\SiteQaController::class, 'store']) }}" class="horizontal-form" enctype="multipart/form-data">
                            @csrf
                        @include('form-error')

                        <x-form.hidden name="master" value="1"/>
                        <x-form.hidden name="version" value="1.0"/>
                        <x-form.hidden name="company_id" :value="Auth::user()->company_id"/>
                        <div class="form-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <x-form.input name="name" label="Name"/>
                                </div>
                                <div class="col-md-2 pull-right">
                                    <x-form.select name="status" label="Status" :options="['1' => 'Active', '0' => 'Inactive']" value="0"/>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <x-form.select name="category_id" id="category_id" label="Category" :options="['' => 'Select category'] + \App\Models\Site\SiteQaCategory::all()->sortBy('name')->pluck('name', 'id')->toArray()" plugin="select2" title="Select category"/>
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
                                        <x-form.textarea :name="'item' . $i" rows="2" :value="''" :placeholder="'Item ' . $i . '.'"/>
                                    </div>
                                    <div class="col-xs-4">
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
                                </div>
                            @endfor

                            {{-- Extra Fields --}}
                            <button class="btn blue" id="more">More Items</button>
                            <div class="row" id="more_items" style="display: none">
                                @for ($i = 16; $i <= 25; $i++)
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
                        </div>
                        <div class="form-actions right">
                            <a href="/site/qa" class="btn default"> Back</a>
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

