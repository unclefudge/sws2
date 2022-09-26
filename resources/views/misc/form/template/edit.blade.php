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
                                <span class="caption-helper"> - ID: @{{ custom_form.id }}</span>
                            </div>
                        </div>
                        <div class="portlet-body form">
                            {{--}}{!! Form::model($template, ['method' => 'PATCH', 'action' => ['Misc\Form\FormTemplateController@update', $template->id], 'class' => 'horizontal-form', 'files' => true]) !!}--}}

                            @include('form-error')

                            <div class="form-body">
                                {{-- Template details --}}
                                <div class="row">
                                    <div class="col-md-6">
                                        {{--}}<div @focus="handleFocus" @focusout="handleFocusOut" tabindex="0">--}}
                                        <div>
                                            <div class="form-group {!! fieldHasError('name', $errors) !!}">
                                                {!! Form::label('name', 'Name', ['class' => 'control-label']) !!}
                                                <input v-model="custom_form.name" type="text" name="name" class="form-control">
                                                {!! fieldErrorMessage('name', $errors) !!}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group {!! fieldHasError('Description', $errors) !!}">
                                            {!! Form::label('description', 'Description', ['class' => 'control-label']) !!}
                                            <textarea v-model="custom_form.description" name="description" id="description" class="form-control" rows="1"></textarea>
                                            {!! fieldErrorMessage('description', $errors) !!}
                                        </div>
                                    </div>
                                </div>

                                <hr>

                                {{-- Pages --}}
                                <template-page title="In Progress"></template-page>

                                {{--}}
                                <ul>
                                    <li v-for="assignment in filteredAssignments" :key="assignment.id">
                                        <label>
                                            <input type="checkbox" v-model="assignment.complete"> {{ assignment.name }}
                                        </label>
                                    </li>
                                </ul> --}}

                                <div class="flex">
                                    <h3 class="font-green-haze" style="display: inline-block; margin-right: 25px">Pages</h3>
                                    <custom-page-buttons :pages="custom_form.pages" :active_page="active_page" v-on:showpage="showpage"></custom-page-buttons>
                                    {{--}}
                                    <button v-for="page in customForm.pages"  class="btn btn-outline btn-default" style="margin-right: 15px; margin-top:-10px">@{{ page.order }}</button>
                                    <button class="btn btn-outline btn-default" style="margin-right: 15px; margin-top:-10px">Add page</button>--}}
                                </div>
                                <hr class="field-hr">

                                <br><br>
                                <div class="form-actions right">
                                    <a href="/form/template" class="btn default"> Back</a>
                                    <button type="submit" name="save" value="save" class="btn green">Save</button>
                                </div>
                            </div>
                            {!! Form::close() !!}

                            <pre>@{{ $data }}</pre>
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

    {{-- Custom Page Button Template --}}
    <template id="custom-page-buttons-template">
        <button v-for="page in pages" :class="{'btn btn-default' : true, 'dark' : active_page == page.order}"
                v-on:click="showpage(page.order)" style="margin-right: 15px; margin-top:-10px">
            @{{ page.order }}
        </button>
        <button class="btn btn-outline btn-default" style="margin-right: 15px; margin-top:-10px">Add page</button>
    </template>
@stop

@section('page-level-plugins-head')
@stop

@section('page-level-plugins')
@stop

@section('page-level-scripts') {{-- Metronic + custom Page Scripts --}}
<script src="https://unpkg.com/vue@3"></script>
<script type="module">
    import App from '/js/vue/custom-form/custom-form.js';

    Vue.createApp(App).mount('#vueApp');
</script>

{{--}}<script type="module" src="/js/vue/custom-form.js"></script>;
--}}

@stop