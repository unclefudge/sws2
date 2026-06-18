@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        @if (Auth::user()->hasAnyPermissionType('site'))
            <li><a href="/site">Sites</a><i class="fa fa-circle"></i></li>
        @endif
        @if (Auth::user()->hasAnyPermissionType('site.maintenance'))
            <li><a href="/site/maintenance">Maintenance</a><i class="fa fa-circle"></i></li>
            <li><a href="/site/maintenance/{{$main->id}}">Request</a><i class="fa fa-circle"></i></li>
        @endif
        <li><span>Add item</span></li>
    </ul>
@stop

@section('content')
    <div class="page-content-inner">
        <div class="row">
            <div class="col-md-12">
                <div class="portlet light bordered">
                    <div class="portlet-title">
                        <div class="caption">
                            <span class="caption-subject font-green-haze bold uppercase">Add Item To Maintenance Request #{{ $main->code }}</span>
                            <span class="caption-helper"></span>
                        </div>
                    </div>
                    <div class="portlet-body form">
                        <form method="POST" action="{{ action([App\Http\Controllers\Site\SiteMaintenanceCategoryController::class, 'store']) }}" class="horizontal-form">
                            @csrf
                            @include('form-error')
                            <x-form.hidden name="company_id" :value="Auth::user()->company_id"/>

                            <div class="form-body">
                                <h4>Site Details</h4>
                                <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                                <div class="row">
                                    <div class="col-md-9">
                                        @if ($main->site)
                                            <b>{{ $main->site->name }}</b><br>
                                        @endif
                                        @if ($main->site)
                                            {{ $main->site->full_address }}<br><br>
                                        @endif
                                    </div>
                                    <div class="col-md-3">
                                        @if ($main->supervisor)
                                            <b>Supervisor:</b> {{ $main->supervisor }}
                                        @endif
                                    </div>
                                </div>
                                <br><br>

                                <div class="row">
                                    <div class="col-md-12">
                                        <x-form.textarea name="item" label="Maintenance Item" rows="3" placeholder="Specific details of maintenance request item"/>
                                    </div>
                                </div>
                            </div>

                            <div class="form-actions right">
                                <a href="/site/maintenance/{{$main->id}}" class="btn default"> Back</a>
                                <button type="submit" class="btn green"> Save</button>
                            </div>
                        </form>
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

        });
    </script>
@stop
