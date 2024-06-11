@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        <li><a href="/support/ticket">Support Tickets</a><i class="fa fa-circle"></i></li>
        <li><span>Support Hours</span></li>
    </ul>
@stop

@section('content')

    <style>
        .keybox {
            float: left;
            display: inline;
            height: 20px;
            width: 20px;
            margin: 0px 3px 5px 0px;
            cursor: pointer !important;
        }

        .state-red {
            background-color: #e26a6a;
        }

        .state-orange {
            background-color: #f4d03f;
        }

        .state-green {
            background-color: #36d7b7;
        }

        .state-grey {
            background-color: #e9edef;
        }

        .stickyKey {
            position: -webkit-sticky; /* Safari */
            position: sticky;
            top: 51px;
            z-index: 10;
            background: #ffffff;
            padding: 5px 0 5px 0;
        }
    </style>

    <!-- BEGIN PAGE CONTENT INNER -->
    <div class="page-content-inner">
        <!-- Tickets -->
        <div class="row">
            <div class="col-md-12">
                <div class="portlet light ">
                    <div class="portlet-title">
                        <div class="caption font-dark">
                            <i class="icon-layers"></i>
                            <span class="caption-subject bold uppercase font-green-haze"> Support Hours</span>
                        </div>
                        <div class="actions">
                            <a class="btn btn-circle green btn-outline btn-sm" href="/support/hours" data-original-title="Hours">Support Hours</a>
                        </div>
                    </div>
                    <div class="portlet-body">
                        <table style="width:100%">
                            <tr style="font-weight: bold;">
                                <td style="width: 120px">Day</td>
                                <td style="width: 100px"></td>
                                <td style="width: 80px; text-align: center">9 -11</td>
                                <td style="width: 80px; text-align: center">11 - 1</td>
                                <td style="width: 80px; text-align: center">1 - 3</td>
                                <td style="width: 80px; text-align: center">3 - 5</td>
                                <td> &nbsp; &nbsp; Comments</td>
                                <td style="width:20px"></td>
                            </tr>
                            <tr>
                                <td colspan="6">
                                    <hr style="margin: 5px 0 5px 0">
                                </td>
                            </tr>
                            <template v-for="hour in xx.hours | orderBy hour.order">
                                <tr style="height:40px; border-bottom: 3px solid #FFF; padding-right: 20px">
                                    <td>@{{ hour.day }}</td>
                                    <td>
                                        <span v-on:click="setDay(hour, 1)" class="keybox state-red"></span>
                                        <span v-on:click="setDay(hour, 2)" class="keybox state-orange"></span>
                                        <span v-on:click="setDay(hour, 3)" class="keybox state-green"></span>
                                    </td>
                                    <td v-on:click="setHour(hour, 'h9')" class="@{{ cellClass(hour.h9) }}" style="cursor: pointer"></td>
                                    <td v-on:click="setHour(hour, 'h11')" class="@{{ cellClass(hour.h11) }}" style="cursor: pointer; border-left: 1px solid #fff"></td>
                                    <td v-on:click="setHour(hour, 'h1')" class="@{{ cellClass(hour.h1) }}" style="cursor: pointer; border-left: 1px solid #fff"></td>
                                    <td v-on:click="setHour(hour, 'h3')" class="@{{ cellClass(hour.h3) }}" style="cursor: pointer; border-left: 1px solid #fff"></td>
                                    <td>
                                        <input v-model="hour.notes" type="text" class="form-control" name="notes" id="@{{hour.day}}-notes" style="margin: 0 20px">
                                    </td>
                                    <td>&nbsp;</td>
                                </tr>
                            </template>

                        </table>


                        <button v-on:click="clear()" type="button" class="btn default" id="save"> Clear</button>
                        <button v-on:click="normal()" type="button" class="btn dark" id="save"> Default</button>
                        <hr>
                        <div style="min-height: 50px;">
                            <button v-on:click="save()" type="button" class="btn green pull-right" id="save"> Save</button>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
    </div>

    <!--<pre v-if="xx.dev">@{{ $data | json }}</pre>
    -->
    <!-- END PAGE CONTENT INNER -->

    <!-- loading Spinner -->
    <div v-show="xx.spinner" style="background-color: #FFF; padding: 20px;">
        <div class="loadSpinnerOverlay">
            <div class="loadSpinner"><i class="fa fa-spinner fa-pulse fa-2x fa-fw margin-bottom"></i> Loading...</div>
        </div>
    </div>
@stop


@section('page-level-plugins-head')
@stop

@section('page-level-plugins')
@stop

@section('page-level-scripts')
    {{-- Metronic + custom Page Scripts --}}
    <script src="/js/libs/vue.1.0.24.js " type="text/javascript"></script>
    <script src="/js/libs/vue-strap.min.js"></script>
    <script src="/js/libs/vue-resource.0.7.0.js " type="text/javascript"></script>
    <script src="/js/vue-modal-component.js"></script>
    <script src="/js/vue-app-basic-functions.js"></script>

    <script>
        $.ajaxSetup({headers: {'X-CSRF-Token': $('meta[name=token]').attr('value')}});

        var xx = {
            dev: dev, spinner: false,
            hours: [],
        };

        var myApp = new Vue({
            el: 'body',
            data: {xx: xx},
            created: function () {
                this.getHours();
            },
            methods: {
                getHours: function () {
                    this.xx.spinner = true;
                    setTimeout(function () {
                        this.xx.load_plan = true;
                        $.getJSON('/support/hours/load', function (data) {
                            this.xx.hours = data[0];
                            this.xx.spinner = false;
                        }.bind(this));
                    }.bind(this), 100);
                },
                setDay: function (hour, state) {
                    hour.h9 = state;
                    hour.h11 = state;
                    hour.h1 = state;
                    hour.h3 = state;
                },
                setHour: function (hour, val) {
                    console.log(hour);
                    console.log(val)
                    console.log(hour[val])
                    var state = hour[val];

                    if (state == 3)
                        hour[val] = 1;
                    else
                        hour[val] = state + 1;
                },
                clear: function () {
                    this.xx.hours = [
                        {id: 1, day: "Monday", h9: 0, h11: 0, h1: 0, h3: 0, order: 1, notes: ''},
                        {id: 2, day: "Tuesday", h9: 0, h11: 0, h1: 0, h3: 0, order: 2, notes: ''},
                        {id: 3, day: "Wednesday", h9: 0, h11: 0, h1: 0, h3: 0, order: 3, notes: ''},
                        {id: 4, day: "Thursday", h9: 0, h11: 0, h1: 0, h3: 0, order: 4, notes: ''},
                        {id: 5, day: "Friday", h9: 0, h11: 0, h1: 0, h3: 0, order: 5, notes: ''},

                    ];
                },
                normal: function () {
                    this.xx.hours = [
                        {id: 1, day: "Monday", h9: 3, h11: 3, h1: 3, h3: 3, order: 1, notes: ''},
                        {id: 2, day: "Tuesday", h9: 2, h11: 2, h1: 2, h3: 2, order: 2, notes: ''},
                        {id: 3, day: "Wednesday", h9: 1, h11: 1, h1: 2, h3: 2, order: 3, notes: ''},
                        {id: 4, day: "Thursday", h9: 3, h11: 3, h1: 3, h3: 3, order: 4, notes: ''},
                        {id: 5, day: "Friday", h9: 2, h11: 2, h1: 2, h3: 2, order: 5, notes: ''},

                    ];
                },
                save: function () {
                    for (const el of this.xx.hours) {
                        var record = {_method: 'patch', day: el.day, h9_11: el.h9, h11_1: el.h11, h1_3: el.h1, h3_5: el.h3, notes: el.notes};
                        console.log(record);

                        $.ajax({
                            url: '/support/hours/' + el.id,
                            type: 'POST',
                            data: record,
                            success: function (result) {
                                console.log('DB updated hours');
                            },
                            error: function (result) {
                                //console.log('FAILED updated hours');
                                alert("failed updating hours");
                            }
                        });
                    }

                },
                cellClass: function (state) {
                    // Set class of task name for displaying on planner
                    var str = 'col-md-1 ';
                    if (state == '0')
                        str = str + ' state-grey';
                    if (state == '1')
                        str = str + ' state-red';
                    if (state == '2')
                        str = str + ' state-orange';
                    if (state == '3')
                        str = str + ' state-green';

                    return str;
                },
            },
        });
    </script>
@stop