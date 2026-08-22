@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        @if (Auth::user()->hasAnyPermissionType('site'))
            <li><a href="/site">Sites</a><i class="fa fa-circle"></i></li>
        @endif
        <li><a href="/site/prac-completion">Practical Completion</a><i class="fa fa-circle"></i></li>
        <li><span>View items</span></li>
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
                            <span class="caption-subject bold uppercase font-green-haze"> Practical Completion</span>
                            <span class="caption-helper">ID: {{ $prac->id }}</span>
                        </div>
                    </div>
                    <div class="portlet-body">
                        <div class="page-content-inner">
                            <form method="POST" action="{{ action([App\Http\Controllers\Site\SitePracCompletionController::class, 'update'], $prac->id) }}" class="horizontal-form" enctype="multipart/form-data">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" id="site_id" value="{{ $prac->site_id }}">

                                @include('form-error')

                                <div class="row">
                                    {{-- Site Details --}}
                                    <div class="col-md-5">
                                        <div class="row">
                                            <div class="col-md-12"><h4>Site Details</h4></div>
                                        </div>
                                        <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                                        @if ($prac->site)
                                            <b>{{ $prac->site->name }}</b><br>
                                            {{ $prac->site->full_address }}<br>
                                            <b>Supervisor:</b> {{ $prac->site->supervisor->name }}<br>
                                        @endif
                                    </div>
                                    <div class="col-md-1"></div>

                                    <div class="col-md-6">
                                        {{-- Status --}}
                                        <div class="row">
                                            <div class="col-md-5"><h4>Client Details</h4></div>
                                            <div class="col-md-7">
                                                <h2 style="margin: 0px; padding-right: 20px">
                                                    @if($prac->status == '-1')
                                                        <span class="pull-right font-red hidden-sm hidden-xs">DECLINED</span>
                                                        <span class="text-center font-red visible-sm visible-xs">DECLINED</span>
                                                    @endif
                                                    @if($prac->status == '0')
                                                        <span class="pull-right font-red hidden-sm hidden-xs"><small
                                                                    class="font-red">COMPLETED {{ $prac->updated_at->format('d/m/Y') }}</small></span>
                                                        <span class="text-center font-red visible-sm visible-xs">COMPLETED {{ $prac->updated_at->format('d/m/Y') }}</span>
                                                    @endif
                                                    @if($prac->status == '1')
                                                        <span class="pull-right font-red hidden-sm hidden-xs">ACTIVE</span>
                                                        <span class="text-center font-red visible-sm visible-xs">ACTIVE</span>
                                                    @endif
                                                    @if($prac->status == '2')
                                                        <span class="pull-right font-red hidden-sm hidden-xs">IN PROGRESS</span>
                                                        <span class="text-center font-red visible-sm visible-xs">IN PROGRESS</span>
                                                    @endif
                                                    @if($prac->status == '4')
                                                        <span class="pull-right font-red hidden-sm hidden-xs">ON HOLD</span>
                                                        <span class="text-center font-red visible-sm visible-xs">ON HOLD</span>
                                                    @endif
                                                </h2>
                                            </div>
                                        </div>
                                        <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                                        {{-- Client Details --}}
                                        <div class="row">
                                            <div class="col-md-6">
                                                @if ($prac->site->client1_name)
                                                    <b>Primary Contact</b><br>
                                                    {!! $prac->site->client1_name ? $prac->site->client1_name."<br>" : '' !!}
                                                    {!! ($prac->site->client1_mobile) ? $prac->site->client1_mobile."<br>" : '' !!}
                                                    {!! ($prac->site->client1_email) ? $prac->site->client1_email : '' !!}
                                                @endif
                                            </div>
                                            <div class="col-md-6">
                                                @if ($prac->site->client2_name)
                                                    <b>Secondary Contact</b><br>
                                                    {!! $prac->site->client2_name ? $prac->site->client2_name."<br>" : '' !!}
                                                    {!! ($prac->site->client2_mobile) ? $prac->site->client2_mobile."<br>" : '' !!}
                                                    {!! ($prac->site->client2_email) ? $prac->site->client2_email : '' !!}
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                {{-- Under Review - asign to super --}}
                                <h4>Prac Completion Details</h4>
                                <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                                <div class="row">
                                    {{-- Assigned Supervisor --}}
                                    <div class="col-md-5">
                                        @if ($prac->status && Auth::user()->allowed2('sig.prac.completion', $prac))
                                            <x-form.select name="super_id" label="Prac Supervisor" :options="Auth::user()->company->supervisors()->sortBy('name')->pluck('name', 'id')->toArray()" :value="$prac->super_id" plugin="select2" placeholder="Select supervisor"/>
                                        @else
                                            <x-form.input name="assigned_super_text" label="Prac Supervisor" :value="$prac->super_id ? $prac->supervisor?->name : '-'" readonly/>
                                        @endif
                                    </div>

                                    @if (Auth::user()->allowed2('edit.prac.completion', $prac))
                                        <div class="col-md-1 pull-right">
                                            <button id="submit" type="submit" name="save" class="btn blue" style="margin-top: 25px">Save</button>
                                        </div>
                                    @endif
                                </div>
                            </form>
                            <livewire:misc.attachments context="site-prac-completion" :context-id="$prac->id"/>
                        </div>
                        <br>


                        {{-- Page-specific Items component --}}
                        <div class="row">
                            <div class="col-md-12">
                                <livewire:site.prac-completion.items :prac-id="$prac->id"/>
                            </div>
                        </div>

                        {{-- Planner --}}
                        <h4>Future Planner Tasks</h4>
                        <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                        <div class="row">
                            <div class="col-md-12">
                                @if ($prac->site->futureTasks()->count())
                                    @foreach ($prac->site->futureTasks() as $plan)
                                        <div class="row">
                                            <div class="col-xs-1">{!! $plan->from->format('d/m/y') !!}</div>
                                            <div class="col-xs-11">{{$plan->task->name}}</div>
                                        </div>
                                    @endforeach
                                @else
                                    No future tasks on planner
                                @endif
                            </div>
                        </div>
                        <br>


                        {{-- Notes --}}
                        <livewire:misc.actions table="site_prac_completion" :table-id="$prac->id"/>

                        {{-- Assigned Tasks --}}
                        <livewire:misc.assigned-tasks context="prac_completion" :context-id="$prac->id"/>

                        {{-- Page-specific workflow/sign-off component --}}
                        <livewire:site.prac-completion.workflow :prac-id="$prac->id"/>

                    </div>
                </div>
            </div>
        </div>
    </div>
@stop


@section('page-level-plugins-head')
    <link href="/assets/global/plugins/select2/css/select2.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/select2/css/select2-bootstrap.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/bootstrap-select/css/bootstrap-select.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/bootstrap-datepicker/css/bootstrap-datepicker3.min.css" rel="stylesheet" type="text/css"/>
@stop

@section('page-level-plugins')
    <script src="/assets/global/plugins/select2/js/select2.full.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/bootstrap-select/js/bootstrap-select.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js" type="text/javascript"></script>
@stop

@section('page-level-scripts')
    <script>
        $(document).ready(function () {
            $("#super_id").select2({placeholder: "Select supervisor", width: '100%'});

        });
    </script>
@stop
