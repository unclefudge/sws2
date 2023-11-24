@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        <li><span>Trades</span></li>
    </ul>
@stop


@section('content')
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
        <trade-modal :show.sync="showTradeModal"></trade-modal>
        <task-modal :show.sync="showTaskModal"></task-modal>
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
                            <div class="actions">
                                @if (Auth::user()->hasPermission2('add.trade') && Auth::user()->id == 2)
                                    <a v-on:click.prevent="$root.$broadcast('add-trade-modal')" class="btn btn-circle green btn-outline btn-sm" data-original-title="Add">Add</a>
                                @endif
                            </div>
                        </div>
                        <div class="portlet-body form">
                            @if (Auth::user()->id == 2 || 1 )

                                <div class="row">
                                    <div class="col-md-4 pull-right" style="padding-bottom: 10px">
                                        <button v-on:click="store.showDisabled = ! store.showDisabled" class="btn grey pull-right">
                                            <span v-if="store.showDisabled"> Hide Disabled</span>
                                            <span v-else="store.showDisabled"> Show Disabled</span>
                                        </button>
                                    </div>
                                </div>
                            @endif

                            <table v-show="tradeList.length" class="table table-striped table-bordered table-nohover order-column">
                                <thead>
                                <tr class="mytable-header">
                                    <th width="5%"></th>
                                    <th><a href="#" v-on:click="sortBy('name')"> Name </a></th>
                                    @if (Auth::user()->id == 2)
                                        <th width="12%" class="hidden-sm hidden-xs"> Actions</th>
                                        <th width="25%" class="visible-sm visible-xs"> Actions</th>
                                    @endif
                                </tr>
                                </thead>
                                <tbody>
                                <template v-for="trade in tradeList | filterDisabled | orderBy sortKey sortOrder">
                                    <tr>
                                        <td class="text-center">
                                            <span v-show="trade.open" v-on:click="trade.open = ! trade.open" class="finger">
                                                <i class="fa fa-minus-circle" style="color: #e7505a;"></i></span>
                                            <span v-show="!trade.open" v-on:click="trade.open = ! trade.open" class="finger">
                                                <i class="fa fa-plus-circle" style="color: #32c5d2;"></i></span>
                                        </td>
                                        <td>
                                            <span :class="{ 'disabled': !trade.status }">
                                                @{{ trade.name }} <span v-if="trade.company_id != 1" class='badge badge-info badge-roundless'>Custom</span>
                                            </span>
                                        </td>
                                        @if (Auth::user()->hasPermission2('del.trade') && Auth::user()->id == 2)
                                            <td>
                                                @if (Auth::user()->hasPermission2('edit.trade'))
                                                    <button v-on:click.prevent="$root.$broadcast('edit-trade-modal', trade)" class="
                                            btn blue btn-xs btn-outline sbold uppercase margin-bottom">
                                                        <i class="fa fa-pencil"></i> <span class="hidden-xs hidden-sm>">Edit</span>
                                                    </button>
                                                @endif

                                                <span v-if="trade.status">
                                                    <button v-on:click="toggleTradeStatus(trade)" class="btn green btn-xs btn-outline sbold uppercase margin-bottom">
                                                        <i class="fa fa-eye"></i>
                                                    </button>
                                                </span>
                                                <span v-else="trade.status">
                                                    <button v-on:click="toggleTradeStatus(trade)" class="btn red btn-xs btn-outline sbold uppercase margin-bottom">
                                                        <i class="fa fa-eye-slash"></i>
                                                    </button>
                                                </span>
                                            </td>
                                        @endif
                                    </tr>
                                    <tr v-if="trade.open" style="background-color: #444D58" class="nohover">
                                        <td colspan="3">
                                            <app-tasks v-if="trade.open" :trade_id="trade.id" :trade_name="trade.name"></app-tasks>
                                        </td>
                                    </tr>
                                </template>
                                </tbody>
                            </table>

                            <p v-else>No Trades yet!</p>

                             <!--<pre v-if="store.dev">@{{ $data | json }}</pre> -->

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </template>

    <template id="tasks-template">
        <h4 class="font-white" style="margin-top: 5px"><i class="icon-layers"></i> Tasks &nbsp; - &nbsp; @{{ trade_name }}
            @if (Auth::user()->hasPermission2('add.trade'))
                <button v-on:click.prevent="$root.$broadcast('add-task-modal', trade_id)" class="btn green btn-outline btn-sm pull-right" data-original-title="Add" style="margin-top: -7px">
                    Add
                </button>
            @endif
        </h4>
        <table v-show="taskList.length" class="table table-striped table-bordered table-hover order-column"
               style="margin-bottom: 0px">
            <thead>
            <th width="10%"> Code</th>
            <th> Name</th>
            <th width="10%"><span class="hidden-xs">Upcoming</span><span class="visible-xs">Up</span></th>
            <th width="14%"> Action</th>
            </thead>
            <tbody>
            <tr v-for="task in taskList | filterDisabled | orderBy 'name'">
                <td>
                    <span v-if="task.upcoming" class="upcoming" v-bind:class="{ 'disabled': task.status == 0 }">@{{ task.code }}</span>
                    <span v-else v-bind:class="{ 'disabled': !task.status }">@{{ task.code }}</span>
                </td>
                <td>
                    <span v-if="task.upcoming" class="upcoming" v-bind:class="{ 'disabled': task.status == 0 }">@{{ task.name }}</span>
                    <span v-else v-bind:class="{ 'disabled': !task.status }">@{{ task.name }}</span>
                </td>
                <td style="text-align:center">
                    @if (Auth::user()->hasPermission2('edit.trade'))
                        <span v-show="task.upcoming" v-on:click="toggleTaskUpcoming(task)" class="finger">
                            <i class="fa fa-1.5x fa-check-square-o"></i>
                        </span>
                        <span v-show="!task.upcoming" v-on:click="toggleTaskUpcoming(task)" class="finger">
                            <i class="fa fa-1.5x fa-square-o"></i>
                        </span>
                    @endif
                </td>
                <td>
                    @if (Auth::user()->hasPermission2('edit.trade'))
                        <button v-on:click.prevent="$root.$broadcast('edit-task-modal', task)" class="btn blue btn-xs btn-outline sbold uppercase margin-bottom">
                            <i class="fa fa-pencil"></i> <span class="hidden-xs hidden-sm>">Edit</span>
                        </button>
                    @endif

                    @if (Auth::user()->hasPermission2('del.trade'))
                        <span v-if="task.status">
                            <button v-on:click="toggleTaskStatus(task)" class="btn green btn-xs btn-outline sbold uppercase margin-bottom">
                                <i class="fa fa-eye"></i>
                            </button>
                        </span>
                        <span v-else="task.status">
                            <button v-on:click="toggleTaskStatus(task)" class="btn red btn-xs btn-outline sbold uppercase margin-bottom">
                                <i class="fa fa-eye-slash"></i>
                            </button>
                        </span>
                    @endif
                </td>
            </tr>

             <!--<pre v-if='store.dev'>@{{ $data | json }}</pre> -->
            </tbody>
        </table>
        <div v-show="load_task" style="background-color: #FFF; padding: 20px;">
            <i class="fa fa-spinner fa-pulse fa-2x fa-fw margin-bottom"></i> Loading...
        </div>
        <div v-show="no_tasks" style="background-color: #FFF; padding: 20px;">
            No Tasks
        </div>
    </template>

    <!-- template for the Modal component -->
    <script type="x/template" id="modal-template">
        <div class="modal-mask" v-on:click="close" v-show="show" transition="modal">
            <div class="modal-container" v-on:click.stop>
                <slot></slot>
            </div>
        </div>
    </script>

    <!-- template for the tradeModal component -->
    <script type="x/template" id="tradeModal-template">
        <modal :show.sync="show" :on-close="close">
            <!--<pre>@{{ $data | json }}</pre>-->
            <form action="" v-on:submit.prevent="addTrade">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true" v-on:click="close()"></button>
                    <h4 class="modal-title">@{{ store.action | capitalize }} Trade</h4>
                </div>
                <div class="modal-body">
                    {{ csrf_field() }}
                    <input v-model="trade.id" type="hidden" name="id">
                    <input v-model="trade.company_id" type="hidden" name="company_id" value="{{ Auth::user()->company_id }}">

                    <div class="form-group">
                        <label class="control-label">Name</label>
                        <input v-model="trade.name" type="text" name="name" class="form-control" placeholder="new trade">
                    </div>
                </div>
                <div class="modal-footer text-right">
                    <button type="button" data-dismiss="modal" class="btn dark btn-outline" v-on:click="close()">Cancel</button>
                    <button v-if="store.action == 'add'" type="button" class="btn green" v-on:click="addTrade(trade)" :disabled="! trade.name">
                        Create
                    </button>
                    <button v-else="store.action == edit" type="button" class="btn green" v-on:click="updateTrade(trade)" :disabled="! trade.name">
                        Save
                    </button>
                </div>
            </form>
        </modal>
    </script>

    <!-- template for the taskModal component -->
    <script type="x/template" id="taskModal-template">
        <modal :show.sync="show" :on-close="close">
            <!--<pre>@{{ $data | json }}</pre>-->

            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true" v-on:click="close()"></button>
                <h4 class="modal-title">@{{ store.action | capitalize }} Task</h4>
            </div>
            <form action="" v-on:submit.prevent="addTask">
                <div class="modal-body">
                    <div class="form-body">
                        {{ csrf_field() }}
                        <input v-model="task.id" type="hidden" name="id">
                        <input v-model="task.trade_id" type="hidden" name="trade_id">
                        <input v-model="task.upcoming" type="hidden" name="upcoming">

                        <div class="form-group{{ $errors->has('name') ? ' has-error' : '' }}">
                            <label class="control-label">Name</label>
                            <input v-model="task.name" type="text" name="name" class="form-control">
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group{{ $errors->has('name') ? ' has-error' : '' }}">
                                    <label class="control-label">Code</label>
                                    <input v-model="task.code" type="text" name="code" class="form-control">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer text-right">
                    <button type="button" data-dismiss="modal" class="btn dark btn-outline" v-on:click="close()">Cancel</button>
                    <button v-if="store.action == 'add'" type="button" class="btn green" v-on:click="addTask(task)" :disabled="!task.name || !task.code">
                        Create
                    </button>
                    <button v-else="store.action == edit" type="button" class="btn green" v-on:click="updateTask(task)" :disabled="
                    !task.name || !task.code">
                        Save
                    </button>
                </div>
            </form>
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