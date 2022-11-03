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
                            <h4 class="font-dark">@{{ section.name }}</h4>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        {{-- Questions --}}
        <div v-show="section.questions.length" style="margin-bottom: 0px">
            <template v-for="question in section.questions">
                <custom-question :question="question"></custom-question>
            </template>
            <pre v-if="debug9">SectionData<br>@{{ $data }}<br>SectionProps<br>@{{ $props }}</pre>
        </div>
    </template>

    {{-- Questions Template --}}
    <template id="custom-question-template">

        <div class="row" v-if="question.type == 'select' && question.type_special == 'site'">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="name" class="control-label">@{{ question.name }} - T:@{{ question.type }} TS: @{{ question.type_special }} V:@{{ question.response_value }}</label>

                    {{-- Text --}}
                    {{--}}
                    <div v-if="question.type == 'text'">
                        <input v-model="question.name" type="text" name="name" class="form-control">
                    </div>--}}

                    {{-- Textbox --}}

                    {{-- Datetime --}}

                    {{-- Select --}}
                    <div v-if="question.type == 'select' && question.type_special == 'site'">
                        Select

                        {{--}}<select-picker :name.sync="xx.assign_cid" :options.sync="xx.sel_company" :function="selfunction"></select-picker>--}}
                        {{--}}<custom-select :selected="{ id: 1, name: 'one' }" v-model:id="1" v-model:name="one" urldata="url/to/get/options"></custom-select>--}}
                        {{--}}<custom-select2></custom-select2>--}}


                        {{--}}
                        <select v-model="question.type" class="form-control bs-select" v-on:change="updateType" id="question-@{{ question.id }}">
                            <option value='' selected>Select Site</option>
                            @foreach (['1' => 'One', '2' => 'Two', '3' => 'Three'] as $id => $name)
                                <option value="{{ $id }}"
                                        @if($id == 1) selected @endif>{{ $name }}</option>
                            @endforeach
                        </select>--}}

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

    {{-- Select Template --}}
    <template id="custom-select-template">
        <div>
            <select v-model="selected" class="form-control bs-select" @change="change">
            <option v-for="option in options" :value="option">
                @{{ option.name }}
            </option>
            </select>
        </div>
    </template>

    <style scoped lang="scss">
        .mydropdown-wrapper {
            max-width: 350px;
            position: relative;
            margin: 0 auto;
        }

        .myselected-item {
            height: 40px;
            border: 2px solid lightgray;
            border-radius: 5px;
            padding: 5px 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 18px;
            font-weight: 500;
        }

        .myselected-item.dropdown.mydropdown-icon {
            transform: rotate(180deg);
            transition: all 0.5s ease;

        }

        .mydropdown-popover {
            position: absolute;
            border: 2px solid lightgray;
            top: 38px;
            left: 0;
            right: 0;
            background-color: #fff;
            max-width: 100%;
            padding: 10px;
        }

        .mydropdown-popover > input {
            width: 90%;
            height: 30px;
            border: 2px solid lightgray;
            font-size: 16px;
            padding-left: 8px;
            margin-left: 10px;
            margin-bottom: 5px;
        }

        .mydropdown-popover.myoptions {
            width: 100%;
        }

        .myoptions ul {
            list-style: none;
            text-align: left;
            padding-left: 8px;
            max-height: 180px;
            overflow-y: scroll;
            overflow-x: hidden;
        }

        .myoptions li {
            widows: 100%;
            border-bottom: 1px solid lightgray;
            padding: 10px;
            background-color: #f1f1f1;
            cursor: pointer;
            font-size: 16px;
        }

        .myoptions li:hover {
            background: #70878a;
            colour: #fff;
            font-weight: bold;
        }


    </style>
    {{-- Select2 Template --}}
    <template id="custom-select2-template">
        <section class="mydropdown-wrapper">
            <div @click="isVisible = !isVisible" class="myselected-item">
            <span v-if="selectedItem">@{{ selectedItem.name }}</span>
            <span v-else>Select option</span>
            <svg :class="isVisible ? 'dropdown' : ''" class="mydropdown-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24">
                <path fill="none" d="M0 0h24v24H0z"/>
                <path d="M12 10.828l-4.95 4.95-1.414-1.414L12 8l6.364 6.364-1.414 1.414z"/>
            </svg>
            </div>
            <div v-if="isVisible" class="mydropdown-popover">
                <input v-model="searchQuery" type="text" placeholder="Search">
                <div v-if="filteredItems.length === 0">No data found</div>
                <div class="myoptions">
                    <ul>
                        <li v-for="(item, index) in filteredItems" :key="index" @click="selectItem(item)">@{{ item.name }}</li>
                    </ul>
                </div>
            </div>
        </section>

        <pre v-if="debug">SelectData<br>@{{ $data }}<br></pre>
    </template>



@stop

@section('page-level-plugins-head')
    {{--}}<link href="/assets/global/plugins/bootstrap-fileinput/bootstrap-fileinput.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/bootstrap-select/css/bootstrap-select.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css" rel="stylesheet" type="text/css"/>--}}
@stop

@section('page-level-plugins')
    {{--}}<script src="/assets/global/plugins/bootstrap-select/js/bootstrap-select.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js" type="text/javascript"></script>--}}
@stop

@section('page-level-scripts') {{-- Metronic + custom Page Scripts --}}
<script src="https://unpkg.com/vue@3"></script>


{{--}}<script src="https://unpkg.com/vue-select@beta"></script>--}}
<link rel="stylesheet" href="https://unpkg.com/vue-select@beta/dist/vue-select.css">

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

    //Vue.component("v-select", vSelect);
    //Vue.component('v-select', VueSelect.VueSelect);

</script>
<script type="module">
    import App from '/js/vue/custom-form/custom-form.js';

    //import vSelect from 'https://unpkg.com/vue-select@beta';

    //import Vue from 'vue';
    //import vSelect from "vue-select";

    //Vue.component("v-select", vSelect);
    //Vue.component('v-select', VueSelect.VueSelect);

    /*
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
     };*/



    const app = Vue.createApp(App)
            //.directive("click-outside", clickOutside)
            //.component("v-select", VueSelect.VueSelect)
            .mount('#vueApp');

    //var vSelect = require('vue-select/dist/vue-select').VueSelect;
    //app.component( 'v-select', vSelect );
    //app.component('v-select', VueSelect.VueSelect);
</script>
<script>
    //import vSelect from "vue-select";

    //app.component('v-select',vSelect);
</script>


@stop