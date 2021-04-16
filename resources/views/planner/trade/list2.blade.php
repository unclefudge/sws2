@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        <li><span>Trades</span></li>
    </ul>
    @stop


    @section('content')

            <!-- BEGIN PAGE CONTENT INNER -->
    <style>
        .disabled {
            color: #FF0000;
            text-decoration: line-through;
        }

        .upcoming {
            color: #3598dc;
        }
    </style>

    <!-- Vue Trades -->
    <app-trades></app-trades>

    <template id="trades-template">
        <input v-model="store.company_id" type="hidden" id="cid" value="{{ Auth::user()->company_id }}">
        <div class="page-content-inner">
            <div class="row">
                <div class="col-md-12">
                    <div class="portlet light ">
                        <div class="portlet-title">
                            <div class="caption font-dark">
                                <i class="icon-layers"></i>
                                <span class="caption-subject bold uppercase font-green-haze"> Trades List</span>
                            </div>
                        </div>
                        <div class="portlet-body form">
                            <table v-show="tradeList.length" class="table table-striped table-bordered table-nohover order-column">
                                <thead>
                                <tr class="mytable-header">
                                    <th width="5%"></th>
                                    <th><a href="#" @click="sortBy('name')"> Name </a></th>
                                </tr>
                                </thead>
                                <tbody>
                                <template v-for="trade in tradeList | filterDisabled | orderBy sortKey sortOrder">
                                    <tr>
                                        <td>
                                            <span @click="trade.open = ! trade.open">
                                            <i class="fa fa-minus-circle" style="color: #e7505a;"></i>
                                            </span>
                                        </td>
                                        <td>
                                            <span>@{{ trade.name }}</span>
                                        </td>
                                    </tr>
                                    <tr style="background-color: #444D58" class="nohover">
                                        <td colspan="3">
                                            <app-tasks v-if="trade.open" :trade_id="trade.id" :trade_name="trade.name"></app-tasks>
                                        </td>
                                    </tr>
                                </template>
                                </tbody>
                            </table>
                            <!-- <pre>@{{ $data | json }}</pre> -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </template>

    <template id="tasks-template">
        <h3 class="font-white" style="margin-top: 5px"><i class="icon-layers"></i> Tasks &nbsp; - &nbsp; @{{ trade_name }}
            <button @click="$root.$broadcast('add-task-modal', trade_id)" class="btn btn-circlek green btn-outline btn-sm pull-right" data-original-title="Add">Add</button>
            </h3>
        <table v-show="taskList.length" class="table table-striped table-bordered table-hover order-column"
               style="margin-bottom: 0px">
            <thead>
            <th width="10%"> Code</th>
            </thead>
            <tbody>
            <tr v-for="task in taskList | filterDisabled">
                <td>
                    <span v-if="task.upcoming" class="upcoming" v-bind:class="{ 'disabled': !task.status' }">@{{ task.code }}</span>
                    <span v-else v-bind:class="{ 'disabled': !task.status }">@{{ task.code }}</span>
                </td>
            </tr>
            <!-- <pre>@{{ $data | json }}</pre> -->
            </tbody>
        </table>
    </template>

    <!-- template for the Modal component -->
    <script type="x/template" id="modal-template">
        <div class="modal-mask" @click="close" v-show="show" transition="modal">
        <div class="modal-container" @click.stop>
            <slot></slot>
        </div>
        </div>
    </script>

    <!-- template for the tradeModal component -->
    <script type="x/template" id="tradeModal-template">
        <modal :show.sync="show" :on-close="close">
        </modal>
    </script>

    <!-- template for the taskModal component -->
    <script type="x/template" id="taskModal-template">
        <modal :show.sync="show" :on-close="close">
            <!--<pre>@{{ $data | json }}</pre>-->
        </modal>
    </script>

    <!-- END PAGE CONTENT INNER -->
@stop


@section('page-level-plugins-head')
@stop

@section('page-level-plugins')
    <script src="/js/libs/yajrabox-handlebars.js"></script>
    @stop

    @section('page-level-scripts') {{-- Metronic + custom Page Scripts --}}

            <!-- Vue -->
    <script src="/js/libs/vue.1.0.24.js " type="text/javascript"></script>
    <script src="/js/libs/vue-resource.0.7.0.js " type="text/javascript"></script>
    <script src="/js/vue-modal-component.js"></script>
    <script src="/js/vue-app-trades.js"></script>

@stop