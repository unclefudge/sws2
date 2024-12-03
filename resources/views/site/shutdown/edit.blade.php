@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        @if (Auth::user()->hasAnyPermissionType('site'))
            <li><a href="/site">Sites</a><i class="fa fa-circle"></i></li>
        @endif
        <li><a href="/site/shutdown">Site Shutdown</a><i class="fa fa-circle"></i></li>
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
                            <span class="caption-subject font-green-haze bold uppercase">Site Shutdown</span>
                            <span class="caption-helper">ID: {{ $shutdown->id }}</span>
                        </div>
                        <div class="actions">
                        </div>
                    </div>
                    <div class="portlet-body form">
                        {!! Form::model($shutdown, ['method' => 'PATCH', 'action' => ['Site\SiteShutdownController@update', $shutdown->id], 'class' => 'horizontal-form']) !!}
                        @include('form-error')
                        <div class="form-body">
                            {{-- Site --}}
                            <div class="row">
                                <div class="col-md-7">
                                    <h2 style="margin-top: 0px">{{ $shutdown->site->name }}</h2>
                                    {{ $shutdown->site->fulladdress }}
                                </div>
                                <div class="col-md-5">
                                    @if (!$shutdown->status)
                                        <h2 class="font-red pull-right" style="margin-top: 0px">COMPLETED</h2>
                                    @endif
                                    <b>Job #:</b> {{ $shutdown->site->code }}<br>
                                    <b>Supervisor:</b> {{ $shutdown->site->supervisorName }}<br>
                                </div>
                            </div>
                            <hr style="padding: 0px; margin: 0px 0px 30px 0px">
                            {{-- Products --}}
                            <?php
                            $category = '';
                            $subcategory = '';
                            ?>
                            @foreach ($shutdown->items as $item)
                                {{-- Category Header --}}
                                @if ($item->category != $category)
                                        <?php $category = $item->category ?>
                                    <h2>{{$category}}</h2>
                                    <hr style="padding: 0px; margin: 0px 0px 10px 0px;">
                                @endif
                                {{-- Sub-Category Header --}}
                                @if ($item->sub_category != $subcategory)
                                        <?php $subcategory = $item->sub_category ?>
                                    <div class="row" style="background: #f0f6fa; padding: 5px">
                                        <div class="col-md-12"><b>{{$subcategory}}</b></div>
                                    </div>
                                @endif
                                {{-- Questions --}}
                                <div class="row" style="margin-top: 10px; margin-bottom: 10px;">
                                    <div class="col-sm-2">
                                        @if ($item->type == 'yn')
                                            {!! Form::select("resp-$item->order", ['' => 'No', 'Yes' => 'Yes', 'N/A' => 'N/A'], $item->response, ['class' => 'form-control bs-select']) !!}
                                        @else
                                            {!! Form::select("sel-$item->order", ['No' => 'No', 'Yes' => 'Yes'], ($item->response) ? 'Yes' : 'No', ['class' => 'form-control bs-select', 'disabled', 'id' => "sel-$item->order"]) !!}
                                        @endif
                                    </div>
                                    <div class="col-sm-10">
                                        <div>{{ $item->name }}</div>
                                        @if ($item->type != 'yn')
                                            <div style="margin-top: 10px">
                                                {!! Form::textarea("resp-$item->order", $item->response, ['rows' => '5', 'class' => 'form-control', 'placeholder' => 'Provide details', 'id' => "resp-$item->order"]) !!}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                <hr style="padding: 0px; margin: 0px 0px 10px 0px;">
                            @endforeach
                            <br>
                            <hr>
                            <div class="row">
                                <div class="col-md-12">
                                    <h5><b>SITE SHUTDOWN ELECTRONIC SIGN-OFF</b></h5>
                                    <p>The above items have been verified by the site construction supervisor.</p>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-3 text-right">Site Supervisor:</div>
                                <div class="col-sm-9">
                                    @if ($shutdown->supervisor_sign_by)
                                        {!! \App\User::find($shutdown->supervisor_sign_by)->full_name !!}, &nbsp;{{ $shutdown->supervisor_sign_at->format('d/m/Y') }}
                                    @elseif ($shutdown->items->count() != $shutdown->itemsCompleted()->count())
                                        <span class="font-grey-silver">Waiting for ({{ ($shutdown->items->count()  - $shutdown->itemsCompleted()->count()) }}) items to be completed</span>
                                    @elseif (Auth::user()->isSupervisor() || Auth::user()->hasAnyRole2('web-admin|mgt-general-manager'))
                                        <button class="btn blue btn-xs btn-outline sbold uppercase margin-bottom signoff">Sign Off</button>
                                    @else
                                        <span class="font-red">Pending</span>
                                    @endif
                                </div>
                                <div class="col-sm-3 text-right">Site Manager:</div>
                                <div class="col-sm-9">
                                    @if ($shutdown->manager_sign_by)
                                        {!! \App\User::find($shutdown->manager_sign_by)->full_name !!}, &nbsp;{{ $shutdown->manager_sign_at->format('d/m/Y') }}
                                    @elseif ($shutdown->items->count() != $shutdown->itemsCompleted()->count())
                                        <span class="font-grey-silver">Waiting for ({{ ($shutdown->items->count()  - $shutdown->itemsCompleted()->count()) }}) items to be completed</span>
                                    @elseif (!$shutdown->supervisor_sign_by)
                                        <span class="font-red">Waiting for Site Supervisor Sign Off</span>
                                    @elseif (Auth::user()->hasAnyRole2('con-construction-manager|web-admin|mgt-general-manager'))
                                        <button class="btn blue btn-xs btn-outline sbold uppercase margin-bottom signoff">Sign Off</button>
                                    @endif
                                </div>
                            </div>
                            <br><br>
                            <div class="form-actions right">
                                <a href="/site/shutdown" class="btn default"> Back</a>
                                <button type="submit" class="btn green"> Save</button>
                            </div>

                        </div>
                        {!! Form::close() !!}
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

@section('page-level-scripts')
    {{-- Metronic + custom Page Scripts --}}
    <script>
        $(document).ready(function () {
            $(".signoff").click(function (e) {
                e.preventDefault();
                window.location.href = "/site/shutdown/{{$shutdown->id}}/signoff";
            });

            // text response question
            $("#resp-2").change(function (e) {
                e.preventDefault();
                if ($("#resp-2").val() != '') {
                    $("#sel-2").val('Yes').change();
                } else {
                    $("#sel-2").val('No').change();
                }
            });

        });
    </script>
@stop

