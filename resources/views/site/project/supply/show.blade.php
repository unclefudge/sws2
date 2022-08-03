@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        @if (Auth::user()->hasAnyPermissionType('site'))
            <li><a href="/site">Sites</a><i class="fa fa-circle"></i></li>
        @endif
        <li><a href="/site/supply">Project Supply Info</a><i class="fa fa-circle"></i></li>
        <li><span>View project</span></li>
    </ul>
@stop

@section('content')
    <div class="page-content-inner">
        <div class="row">
            <div class="col-md-12">
                <div class="portlet light bordered">
                    <div class="portlet-title">
                        <div class="caption">
                            <i class="fa fa-pencil "></i>
                            <span class="caption-subject font-green-haze bold uppercase">Project Supply Infomation</span>
                            <span class="caption-helper">ID: {{ $project->id }}</span>
                        </div>
                        <div class="actions">
                            @if($project->attachment_url)
                                <a class="btn btn-circle green btn-outline btn-sm" href="{{  $project->attachment_url }}" data-original-title="PDF">View PDF</a>
                            @endif
                        </div>
                    </div>
                    <div class="portlet-body form">
                        <div class="form-body">
                            {{-- Site --}}
                            <div class="row">
                                <div class="col-md-7">
                                    <h2 style="margin-top: 0px">{{ $project->site->name }}</h2>
                                    {{ $project->site->fulladdress }}
                                </div>
                                <div class="col-md-5">
                                    @if (!$project->status)
                                        <h2 class="font-red pull-right" style="margin-top: 0px">COMPLETED</h2>
                                    @endif
                                    <b>Job #:</b> {{ $project->site->code }}<br>
                                    <b>Supervisor(s):</b> {{ $project->site->supervisorsSBC() }}<br>
                                </div>
                            </div>
                            <hr style="padding: 0px; margin: 0px 0px 30px 0px">

                            <div class="row bold hidden-sm hidden-xs">
                                <div class="col-md-2">{{ $title->name }}</div>
                                <div class="col-md-3">{{ $title->supplier }}</div>
                                <div class="col-md-3">{{ $title->type }}</div>
                                <div class="col-md-2">{{ $title->colour }}</div>
                                {{--}}<div class="col-md-2">Notes</div>--}}
                            </div>
                            <hr class="hidden-sm hidden-xs" style="padding: 0px; margin: 5px 0px 20px 0px;">

                            {{-- Products --}}
                            @foreach ($project->itemsOrdered() as $item)
                                <div class="row">
                                    <div class="col-md-2">
                                        <div class="hidden-sm hidden-xs">
                                            {{ $item->product }}
                                        </div>
                                        <div class="visible-sm visible-xs">
                                            <br><b>{{ $item->product }}</b>
                                            <hr class="visible-sm visible-xs" style="padding: 0px; margin: 5px 0px 20px 0px;">
                                            @if ($item->product_id == 32) Product: {{  $item->product }} @endif
                                        </div>
                                    </div>
                                    {{-- Supplier --}}
                                    <div class="col-md-3">
                                        <div class="visible-sm visible-xs">Supplier: {{ ($item->supplier) ? $item->supplier : '-' }}</div>
                                        <div class="hidden-sm hidden-xs">{{ ($item->supplier) ? $item->supplier : '-' }}</div>
                                    </div>
                                    {{-- Type --}}
                                    <div class="col-md-3">
                                        <div class="visible-sm visible-xs">Type: {{ ($item->type) ? $item->type : '-' }}</div>
                                        <div class="hidden-sm hidden-xs">{{ ($item->type) ? $item->type : '-' }}</div>
                                    </div>
                                    {{-- Colour --}}
                                    <div class="col-md-2">
                                        <div class="visible-sm visible-xs">Colour: {{ ($item->colour) ? $item->colour : '-' }}</div>
                                        <div class="hidden-sm hidden-xs">{{ ($item->colour) ? $item->colour : '-' }}</div>
                                    </div>
                                    {{-- Notes --}}
                                    {{--}}
                                    <div class="col-md-2">
                                        <div class="visible-sm visible-xs"><br>Notes</div>
                                        {{ $product->notes, null }}
                                    </div>--}}
                                </div>
                                <hr class="hidden-sm hidden-xs" style="padding: 0px; margin: 0px 0px 10px 0px;">
                            @endforeach


                            <br><br>
                            <hr>
                            <div class="row">
                                <div class="col-md-12">
                                    <h5><b>PROJECT SUPPLY ELECTRONIC SIGN-OFF</b></h5>
                                    <p>The above supply items have been verified by the site construction supervisor.</p>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-3 text-right">Site Supervisor:</div>
                                <div class="col-sm-9">
                                    @if ($project->supervisor_sign_by)
                                        {!! \App\User::find($project->supervisor_sign_by)->full_name !!}, &nbsp;{{ $project->supervisor_sign_at->format('d/m/Y') }}
                                    @elseif ($project->items->count() != $project->itemsCompleted()->count())
                                        <span class="font-grey-silver">Waiting for ({{ ($project->items->count()  - $project->itemsCompleted()->count()) }}) items to be completed</span>
                                    @elseif (Auth::user()->isSupervisor() || Auth::user()->hasAnyRole2('web-admin|mgt-general-manager'))
                                        <button class="btn blue btn-xs btn-outline sbold uppercase margin-bottom signoff">Sign Off</button>
                                    @else
                                        <span class="font-red">Pending</span>
                                    @endif
                                </div>
                                <div class="col-sm-3 text-right">Site Manager:</div>
                                <div class="col-sm-9">
                                    @if ($project->manager_sign_by)
                                        {!! \App\User::find($project->manager_sign_by)->full_name !!}, &nbsp;{{ $project->manager_sign_at->format('d/m/Y') }}
                                    @elseif ($project->items->count() != $project->itemsCompleted()->count())
                                        <span class="font-grey-silver">Waiting for ({{ ($project->items->count()  - $project->itemsCompleted()->count()) }}) items to be completed</span>
                                    @elseif (!$project->supervisor_sign_by)
                                        <span class="font-red">Waiting for Site Supervisor Sign Off</span>
                                    @elseif (Auth::user()->hasAnyRole2('con-construction-manager|web-admin|mgt-general-manager'))
                                        <button class="btn blue btn-xs btn-outline sbold uppercase margin-bottom signoff">Sign Off</button>
                                    @endif
                                </div>
                            </div>
                            <br><br>
                            <div class="form-actions right">
                                <a href="/site/supply" class="btn default"> Back</a>
                                @if(Auth::user()->allowed2('edit.site.project.supply', $project))
                                    <a href="/site/supply/{{ $project->id }}/edit" class="btn green"> Edit</a>
                                @endif
                            </div>

                        </div> <!-- /Form body -->
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop


@section('page-level-plugins-head')
    <link href="/assets/global/plugins/select2/css/select2.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/select2/css/select2-bootstrap.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/bootstrap-datepicker/css/bootstrap-datepicker3.min.css" rel="stylesheet" type="text/css"/>
    <link href="/css/libs/fileinput.min.css" media="all" rel="stylesheet" type="text/css"/>
@stop

@section('page-level-plugins')
    <script src="/assets/global/plugins/select2/js/select2.full.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js" type="text/javascript"></script>
    <script src="/js/libs/fileinput.min.js"></script>
@stop

@section('page-level-scripts') {{-- Metronic + custom Page Scripts --}}
<script src="/assets/pages/scripts/components-date-time-pickers.min.js" type="text/javascript"></script>
<script>
    $(document).ready(function () {
        $(".signoff").click(function (e) {
            e.preventDefault();
            window.location.href = "/site/supply/{{$project->id}}/signoff";
        });
    });
</script>
@stop

