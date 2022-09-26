@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        <li><a href="/form">Forms</a><i class="fa fa-circle"></i></li>
        <li><a href="/form/template">Form Templates</a><i class="fa fa-circle"></i></li>
        <li><span>Edit</span></li>
    </ul>
@stop


@section('content')

    <div id="vueApp">
        <div class="page-content-inner">
            <div class="row">
                <div class="col-md-12">
                    <div class="portlet light bordered">
                        <div class="portlet-title">
                            <div class="caption">
                                <span class="caption-subject font-green-haze bold uppercase">Edit Form Template </span>
                                <span class="caption-helper"> - ID: @{{ templateForm.id }}</span>
                            </div>
                        </div>
                        <div class="portlet-body form">
                            {{--}}
                            {!! Form::model($template, ['method' => 'PATCH', 'action' => ['Misc\Form\FormTemplateController@update', $template->id], 'class' => 'horizontal-form', 'files' => true]) !!}

                            --}}@include('form-error')

                            <div class="form-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group {!! fieldHasError('name', $errors) !!}">
                                            {!! Form::label('name', 'Name', ['class' => 'control-label']) !!}
                                            {!! Form::text('name', null, ['class' => 'form-control']) !!}
                                            {!! fieldErrorMessage('name', $errors) !!}
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group {!! fieldHasError('Description', $errors) !!}">
                                            {!! Form::label('description', 'Description', ['class' => 'control-label']) !!}
                                            {!! Form::textarea('description', null, ['class' => 'form-control', 'rows' => 3]) !!}
                                            {!! fieldErrorMessage('description', $errors) !!}
                                        </div>
                                    </div>
                                </div>
                                <div class="form-actions right">
                                    <a href="/form/template" class="btn default"> Back</a>
                                    <button type="submit" name="save" value="save" class="btn green">Save</button>
                                </div>
                            </div>
                            {!! Form::close() !!}

                            TemplateForm

                            <div>@{{ templateForm.name }}</div>
                            <div>@{{ templateForm.pages }}</div>
                            <pre>@{{ templateForm }}</pre>
                        </div>
                    </div>
                </div>
            </div>
            <div>
                <div class="pull-right" style="font-size: 12px; font-weight: 200; padding: 10px 10px 0 0">
                    {{--}}{!! $template->displayUpdatedBy() !!} --}}
                </div>
            </div>
            <!-- END PAGE CONTENT INNER -->
        </div>

        <!-- loading Spinner -->
        <div v-show="loading" style="background-color: #FFF; padding: 20px;">
            <div class="loadSpinnerOverlay">
                <div class="loadSpinner"><i class="fa fa-spinner fa-pulse fa-2x fa-fw margin-bottom"></i> Loading...</div>
            </div>
        </div>
    </div>
@stop

@section('page-level-plugins-head')
@stop

@section('page-level-plugins')
@stop

@section('page-level-scripts') {{-- Metronic + custom Page Scripts --}}
<script src="https://unpkg.com/vue@3"></script>
<script type="module">
    import App from '/js/vue/form-template.js';

    Vue.createApp(App).mount('#vueApp');
</script>

{{--}}<script type="module" src="/js/vue/custom-form.js"></script>;
--}}

@stop