@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        @if (Auth::user()->hasAnyPermissionType('site'))
            <li><a href="/site">Sites</a><i class="fa fa-circle"></i></li>
        @endif
        <li><a href="/site/inspection/custom">Safety in Design</a><i class="fa fa-circle"></i></li>
        <li><span>View Report</span></li>
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
                                <span class="caption-subject font-green-haze bold uppercase">Safety in Design </span>
                                <span class="caption-helper"> - ID: @{{ custom_form.id }}</span>
                            </div>
                        </div>
                        <div class="portlet-body form">
                            <div class="form-body">
                                {{-- Template name + description--}}
                                <div class="row">
                                    <div class="col-md-12">
                                        <h3 style="margin-top: 0px"> @{{ custom_form.name }}</h3>
                                        @{{ custom_form.description }}<br><br>
                                    </div>
                                </div>
                                <hr class="field-hr">

                                {{-- Page Icons --}}
                                <div class="row">
                                    <div class="col-md-12">
                                        <h3 v-if="activePage" class="font-green-haze" style="display: inline-block; margin: 0px">@{{ activePage.name }}</h3>
                                    <span class="pull-right">
                                        <custom-page-buttons :pages="custom_form.pages" :active_page="active_page" v-on:showpage="showpage"></custom-page-buttons>
                                    </span>
                                    </div>

                                </div>
                                <hr class="field-hr">

                                {{-- Current Page --}}
                                <custom-page :page="activePage"></custom-page>
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
        <button v-for="page in pages" :class="{'btn btn-default' : true, 'dark' : active_page == page.order}" v-on:click="showpage(page.order)" style="margin-right: 15px;">
            @{{ page.order }}
        </button>
    </template>

    {{-- Current Page Template --}}
    <template id="custom-page-template">
        <div v-if="page">
            {{-- Sections --}}
            <template v-for="section in page.sections">
                <custom-section :section="section" :sections_count="page.sections.length"></custom-section>
            </template>
            <pre v-if="debug9">PageData<br>@{{ $data }}<br>PageProps<br>@{{ $props }}</pre>
        </div>
    </template>

    {{-- Sections Template --}}
    <template id="custom-section-template">
        {{-- Section Title --}}
        <div v-if="sections_count > 1" class="row" style="background: #f0f6fa; margin: 10px 0px 5px 0px; padding: 5px 0px;">
            <div class="col-md-12">
                <table>
                    <tr>
                        <td width="5%"><i class="fa fa-plus font-dark" style="margin-right: 10px" v-on:click="toggleSection('section-'+section.id)"></i></td>
                        <td width="95%">
                            <h4 v-else class="font-dark">@{{ section.name }}</h4>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        {{-- Questions --}}
        <div v-show="section.questions.length"  style="margin-bottom: 0px">
            <template v-for="question in section.questions">
                <custom-question :question="question"></custom-question>
            </template>
        <pre v-if="debug9">SectionData<br>@{{ $data }}<br>SectionProps<br>@{{ $props }}</pre>
    </template>

    {{-- Questions Template --}}
    <template id="custom-question-template">

        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="name" class="control-label">@{{ question.name }} - T:@{{ question.type }} TS: @{{ question.type_special }} V:@{{ question.response_value }}</label>

                    {{-- Text --}}
                    <div v-if="question.type == 'text'">
                        <input v-model="question.name" type="text" name="name" class="form-control">
                    </div>

                    {{-- Textbox --}}

                    {{-- Datetime --}}

                    {{-- Select --}}
                    <div v-if="question.type == 'select' && question.type_special == 'site'">
                        <select v-model="question.type" class="form-control bs-select" v-on:change="updateType" id="question-@{{ question.id }}">
                            <option value='' selected>Select Site</option>
                            @foreach (['1' => 'One', '2' => 'Two', '3' => 'Three'] as $id => $name)
                                <option value="{{ $id }}"
                                        @if($id == 1) selected @endif>{{ $name }}</option>
                            @endforeach
                        </select>

                    </div>
                </div>
            </div>
        </div>
        <hr class="field-hr">

        {{--}}

                <select v-model="question.type" class="form-control bs-select" v-on:change="updateType" id="question_type">
                    <option value='' selected>Select Type</option>
                    @foreach (['1' => 'One', '2' => 'Two', '3' => 'Three'] as $id => $name)
                        <option value="{{ $id }}"
                                @if($id == 1) selected @endif>{{ $name }}</option>
                    @endforeach
                </select>--}}
    </template>
@stop

@section('page-level-plugins-head')
    <link href="/assets/global/plugins/bootstrap-fileinput/bootstrap-fileinput.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/bootstrap-select/css/bootstrap-select.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css" rel="stylesheet" type="text/css"/>
@stop

@section('page-level-plugins')
    <script src="/assets/global/plugins/bootstrap-select/js/bootstrap-select.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js" type="text/javascript"></script>
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
    import App from '/js/vue/custom-form/custom-form.js';

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