@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        <li><a href="/form">Forms</a><i class="fa fa-circle"></i></li>
        <li><a href="/form/template">Form Templates</a><i class="fa fa-circle"></i></li>
        <li><span>Show</span></li>
    </ul>
@stop

@section('content')
    <div class="page-content-inner">
        <div class="row">
            <div class="col-md-12">
                <div class="portlet light bordered">
                    <div class="portlet-title">
                        <div class="caption">
                            <span class="caption-subject font-green-haze bold uppercase">Form Template </span>
                            <span class="caption-helper"> - ID: {{ $template->id }}</span>
                        </div>
                    </div>
                    <div class="portlet-body form">
                        <form method="POST" action="{{ action([\App\Http\Controllers\Misc\Form\FormTemplateController::class, 'update'], $template->id) }}" class="horizontal-form" enctype="multipart/form-data">
                        @csrf
                        @method('PATCH')
                        @include('form-error')

                        <div class="form-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <x-form.input name="name" label="Name" :value="$template->name"/>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <x-form.textarea name="description" label="Description" rows="3" :value="$template->description"/>
                                </div>
                            </div>
                            <div class="form-actions right">
                                <a href="/form/template" class="btn default"> Back</a>
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
                {!! $template->displayUpdatedBy() !!}
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


        });

    </script>
@stop