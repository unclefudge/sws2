@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        <li><a href="/form">Forms</a><i class="fa fa-circle"></i></li>
        <li><a href="/form/template">Form Templates</a><i class="fa fa-circle"></i></li>
        <li><span>Edit</span></li>
    </ul>
@stop

<style>
    .hoverFinger:hover {
        cursor: pointer;
    }
</style>

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
                                {{-- Template name --}}
                                <div v-if="active_field == 'template-name'" v-bind:id="'template-name'" class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            {{--}}<label for="name" class="control-label">Template Name</label>--}}
                                            <input v-model="custom_form.name" type="text" name="name" class="form-control input-lg">
                                        </div>
                                    </div>
                                </div>
                                <h3 v-else v-on:click="editfield('template-name')" class="hoverFinger">@{{ custom_form.name }} &nbsp;
                                    <small><i class="fa fa-pencil font-grey-silver"></i></small>
                                </h3>

                                {{-- Template description --}}
                                <div v-if="active_field == 'template-description'" v-bind:id="'template-description'" class="row">
                                    <div class="col-md-6"><input v-model="custom_form.description" type="text" class="form-control"></div>
                                </div>
                                <div v-else v-on:click="editfield('template-description')" class="hoverFinger">@{{ custom_form.description }} &nbsp;<i class="fa fa-pencil font-grey-silver"></i></div>
                                <hr>

                                {{-- Page Icons --}}
                                <div class="flex">
                                    <h3 class="font-green-haze" style="display: inline-block; margin-right: 25px">Pages</h3>
                                    <custom-page-buttons :pages="custom_form.pages" :active_page="active_page" v-on:showpage="showpage"></custom-page-buttons>
                                </div>
                                <hr class="field-hr">

                                {{-- Current Page --}}
                                <custom-page :page="activePage" :active_field="active_field" v-on:editfield="editfield"></custom-page>
                                {{--}}<pre>@{{ page }}</pre> --}}  {{-- :sections="custom_form.page.sections" --}}

                                <br><br>
                                <div class="form-actions right">
                                    <a href="/form/template" class="btn default" style="margin-right: 10px"> Back</a>
                                    <button class="btn green" v-on:click="saveData()">Save</button>
                                </div>
                            </div>
                            {!! Form::close() !!}

                            <pre v-if="debug">ALL DATA<br>@{{ $data }}</pre>
                        </div>
                    </div>
                </div>
            </div>
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

    {{-- Current Page Template --}}
    <template id="custom-page-template">
        <div v-if="page">
            {{-- Page Title --}}
            <div class="row">
                <div class="col-md-4">
                    <div v-if="activeField('page-name-'+page.id)" v-bind:id="'page-name-'+page.id" class="row"> {{-- v-click-outside="nameOfCustomEventToCall" --}}
                        <input v-model="page.name" type="text" name="pagename" class="form-control">
                        {{--}}<div class="col-md-1">
                            <button v-on:click="editfield('')" class="btn btn-primary">Save</button>
                        </div>--}}
                    </div>
                    <h4 v-else v-on:click="editfield('page-name-'+page.id)" class="hoverFinger">@{{ page.name }} &nbsp;<i class="fa fa-pencil font-grey-silver"></i></h4>
                </div>
            </div>
            <br>

            {{-- Sections --}}
            <template v-for="section in page.sections">
                <custom-section :section="section" :active_field="active_field" v-on:editfield="editfield"></custom-section>
            </template>
            <pre v-if="debug9">PageData<br>@{{ $data }}<br>PageProps<br>@{{ $props }}</pre>
        </div>
    </template>

    {{-- Sections Template --}}
    <template id="custom-section-template">
        {{-- Section Title --}}
        <div class="row" style="background: #000; margin: 10px 0px 5px 0px; padding: 5px 0px;">
            <div class="col-md-12">
                <table>
                    <tr>
                        <td width="5%"><i class="fa fa-plus font-white" style="margin-right: 10px" v-on:click="toggleSection('section-'+section.id)"></i></td>
                        <td width="95%">
                            <div class="col-md-12">
                                <div v-if="activeField('section-name-'+section.id)" v-bind:id="'section-name-'+section.id" class="row">
                                    <input v-model="section.name" type="text" name="pagename" class="form-control">
                                </div>
                                <h4 v-else class="font-white"><span v-on:click="editfield('section-name-'+section.id)" class="hoverFinger">@{{ section.name }} &nbsp;<i class="fa fa-pencil font-grey-silver"></i></span></h4>
                            </div>
                        </td>
                    </tr>
                </table>
            </div>
        </div>


        {{-- Questions --}}
        <table v-show="section.questions.length" class="table table-bordered table-hover order-column" style="margin-bottom: 0px">
            <thead>
            <tr style="background: #eee">
                <th width="5%"></th>
                <th> Question</th>
                <th width="30%"> Response</th>
                <th width="25%"></th>
            </tr>
            </thead>
            <tbody>

            <template v-for="question in section.questions">
                <custom-question :question="question" :active_field="active_field" v-on:editfield="editfield"></custom-question>
            </template>

            </tbody>
        </table>
        <pre v-if="debug9">SectionData<br>@{{ $data }}<br>SectionProps<br>@{{ $props }}</pre>
    </template>

    {{-- Questions Template --}}
    <template id="custom-question-template">
        <tr>
            <td style="text-align: center">@{{ question.order }}.</td>
            <td>@{{ question.name }}</td>
            <td>@{{ question.type }}</td>
            <td>
                <select v-model="question.type" class="form-control bs-select" v-on:change="updateType" id="question_type">
                    <option value='' selected>Select Type</option>
                    @foreach (['1' => 'One', '2' => 'Two', '3' => 'Three'] as $id => $name)
                        <option value="{{ $id }}"
                                @if($id == 1) selected @endif>{{ $name }}</option>
                    @endforeach
                </select>
            </td>
            {{-- <select-picker :name.sync="question.type" :options.sync="select_array" :function="updateType"></select-picker> --}}
        </tr>
    </template>
@stop

@section('page-level-plugins-head')
@stop

@section('page-level-plugins')
@stop

@section('page-level-scripts') {{-- Metronic + custom Page Scripts --}}
<script src="https://unpkg.com/vue@3"></script>
{{--}}<script src="/js/vue-app-basic-functions.js"></script>--}}
<script>
    //Vue.http.headers.common['X-CSRF-TOKEN'] = document.querySelector('#token').getAttribute('value');
    $.ajaxSetup({
        headers: {'X-CSRF-TOKEN': $('meta[name=token]').attr('value')}
    });

    // Search through array of object with given 'key' and 'value'
    function objectFindByKey(array, key, value) {
        for (var i = 0; i < array.length; i++) {
            if (array[i][key] == value) {
                return array[i];
            }
        }
        return null;
    }
</script>
<script type="module">
    import App from '/js/vue/custom-form-template/custom-form.js';

    const clickOutside = {
        beforeMount: (el, binding) => {
            el.clickOutsideEvent = event => {
                // here I check that click was outside the el and his children
                if (!(el == event.target || el.contains(event.target))) {
                    // and if it did, call method provided in attribute value
                    binding.value();
                }
            };
            document.addEventListener("click", el.clickOutsideEvent);
        },
        unmounted: el => {
            document.removeEventListener("click", el.clickOutsideEvent);
        },
    };

    Vue.createApp(App)
            .directive("click-outside", clickOutside)
            .mount('#vueApp');
</script>
@stop