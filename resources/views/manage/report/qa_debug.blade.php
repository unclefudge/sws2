@extends('layout')
@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        @if (Auth::user()->hasAnyPermissionType('site'))
            <li><a href="/site">Sites</a><i class="fa fa-circle"></i></li>
        @endif
        <li><a href="/site/qa">Quality Assurance</a><i class="fa fa-circle"></i></li>
        <li><span>View Report</span></li>
    </ul>
@stop

<style>
    a.mytable-header-link {
        font-size: 14px;
        font-weight: 600;
        color: #333 !important;
    }
</style>
@section('content')
    <div class="page-content-inner">
        <div class="row">
            <div class="col-md-12">
                <div class="portlet light bordered">
                    <div class="portlet-title">
                        <div class="caption">
                            <i class="icon-layers"></i>
                            <span class="caption-subject bold uppercase font-green-haze"> Quality Assurance Report</span>
                            <span class="caption-helper">ID: {{ $qa->id }}</span>
                        </div>
                    </div>
                    <div class="portlet-body">
                        <div class="page-content-inner">
                            <div class="row" style="padding-top: 10px">
                                <div class="col-xs-12 ">
                                    <br>
                                    <h2 style="margin: 0px"><b>{{ $qa->name }}</b>
                                        @if ($qa->master)
                                            <span class="pull-right font-red hidden-sm hidden-xs">TEMPLATE</span>
                                            <span class="text-center font-red visible-sm visible-xs">TEMPLATE</span>
                                        @else
                                            @if($qa->status == '-1')
                                                <span class="pull-right font-red hidden-sm hidden-xs">NOT REQUIRED</span>
                                                <span class="text-center font-red visible-sm visible-xs">NOT REQUIRED</span>
                                            @endif
                                            @if($qa->status == '0')
                                                <span class="pull-right font-red hidden-sm hidden-xs">COMPLETED {{ $qa->updated_at->format('d/m/Y') }}</span>
                                                <span class="text-center font-red visible-sm visible-xs">COMPLETED {{ $qa->updated_at->format('d/m/Y') }}</span>
                                            @endif
                                            @if($qa->status == '1' && Auth::user()->allowed2('edit.site.qa', $qa))
                                                <button v-if="xx.qa.status == 1 && xx.qa.items_done == 0" class="btn red pull-right" v-on:click.prevent="$root.$broadcast('updateReportStatus', '-1')"> Page Not
                                                    Required
                                                </button>
                                            @endif
                                            @if($qa->status == '2')
                                                <span class="pull-right font-red hidden-sm hidden-xs">ON HOLD</span>
                                                <span class="text-center font-red visible-sm visible-xs">ON HOLD</span>
                                            @endif
                                        @endif
                                    </h2>
                                </div>
                                <div class="col-xs-12 ">
                                    <p>Item Tasks: {{ $qa->tasksSBC() }}</p>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-xs-5">
                                    <p>JOB NAME: @if ($qa->site) {{ $qa->site->name }} @endif<br>
                                        ADDRESS: @if ($qa->site) {{ $qa->site->full_address }} @endif<br>
                                    </p>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-xs-5">
                                    <p>MASTER TEMPLATE: {{ $qa->master_id}}<br>
                                    </p>
                                </div>
                            </div>
                            <hr>
                            <!-- List QA -->
                            <h3>Items</h3>
                            <div class="row">
                                <div class="col-md-1"><b>Status</b></div>
                                <div class="col-md-9"><b>Name</b></div>
                                <div class="col-md-2"><b>Last Updated</b></div>
                            </div>
                            @foreach ($qa->items as $item)
                                <div class="row">
                                    <div class="col-md-1">
                                        @if ($item->status == -1 ) N/A @endif
                                        @if ($item->status == 1 ) [X] @endif
                                        @if ($item->status == 0 ) [ &nbsp; ] @endif
                                    </div>
                                    <div class="col-md-9">{{ $item->name }}</div>
                                    <div class="col-md-2">{{ $item->updated_at->format('d/m/Y') }}</div>
                                </div>
                            @endforeach
                            <hr>
                            <h3>Triggers</h3>
                            <div class="row">
                                <div class="col-md-1"><b>Status</b></div>
                                <div class="col-md-9"><b>Name</b></div>
                                <div class="col-md-2"><b>Planner Date</b></div>
                            </div>
                            @foreach ($planner as $plan)
                                <?php $task = App\Models\Site\Planner\Task::find($plan->task_id) ?>
                                <?php $company = App\Models\Company\Company::find($plan->entity_id) ?>
                                <div class="row">
                                    <div class="col-md-1">
                                        @if ($plan->to->isPast()) [X] @else  [ &nbsp; ] @endif
                                    </div>
                                    <div class="col-md-5">{{ $task->name }} &nbsp; (code: {{ $task->code }}, &nbsp; id:{{ $task->id }})</div>
                                    <div class="col-md-4">@if ($plan->entity_type == 'c') {{ $company->name }} @endif</div>
                                    <div class="col-md-2">{{ $plan->to->format('d/m/Y') }}</div>
                                </div>
                            @endforeach
                            <h3>ToDos</h3>
                            <div class="row">
                                <div class="col-md-1"><b>Status</b></div>
                                <div class="col-md-9"><b>Name</b></div>
                                <div class="col-md-2"><b>Created</b></div>
                            </div>
                            @foreach ($todos as $todo)
                                <div class="row">
                                    <div class="col-md-1">
                                        @if ($todo->status) [ &nbsp; ] @else [X]  @endif
                                    </div>
                                    <div class="col-md-9">{{ $todo->name }} &nbsp; (id:{{ $todo->id }})</div>
                                    <div class="col-md-2">{{ $todo->created_at->format('d/m/Y') }}</div>
                                </div>
                            @endforeach
                            <div class="row">
                                <div class="col-md-6 pull-right text-right" style="margin-top: 15px; padding-right: 20px">
                                    <span class="font-grey-salsa">
                                        <span class="font-grey-salsa" v-if="xx.qa.master == '0'">version {{ $qa->version }} </span>
                                        <span class="font-grey-salsa" v-if="xx.qa.master == '1'">Current version {{ $qa->version }}<br> {!! nl2br($qa->notes) !!}</span>
                                </div>
                            </div>
                            <hr>
                            <div class="pull-right" style="min-height: 50px">
                                <a href="/site/qa" class="btn default"> Back</a>
                            </div>
                            <br><br>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop


@section('page-level-plugins-head')
@stop

@section('page-level-plugins')
@stop

@section('page-level-scripts') {{-- Metronic + custom Page Scripts --}}
@stop

