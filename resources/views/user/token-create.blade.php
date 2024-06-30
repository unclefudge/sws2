@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        @if (Auth::user()->allowed2('view.company', $user->company))
            <li><a href="/company/{{ $user->company_id }}">Company</a><i class="fa fa-circle"></i></li>
        @endif
        @if (Auth::user()->hasAnyPermissionType('user'))
            <li><a href="/company/{{ Auth::user()->company->id}}/user">Users</a><i class="fa fa-circle"></i></li>
        @endif
        <li><span>API Token</span></li>
    </ul>
@stop

@section('content')
    {{-- BEGIN PAGE CONTENT INNER --}}
    <div class="page-content-inner">

        @include('user/_header')

        <div class="portlet light ">
            <div class="portlet-title">
                <div class="caption font-dark">
                    <i class="icon-layers"></i>
                    <span class="caption-subject bold uppercase font-green-haze">Security API Token</span>
                </div>
                <div class="actions">
                    {{--}}<a class="btn btn-circle green btn-outline btn-sm" href="/supervisor/checklist" data-original-title="Current">Current Week</a>--}}
                </div>
            </div>

            <div class="portlet-body form">

                <div class="row">
                    <div class="col-md-2">API Token</div>
                    <div class="col-md-10"> {{ $token['token'] }}</div>
                </div>


                <div class="form-actions right">
                    <a href="/supervisor/checklist" class="btn default"> Back</a>
                    <button type="submit" class="btn green"> Save</button>
                </div>
            </div>
            {!! Form::close() !!}
        </div>
    </div>

    <div>
        <div class="pull-right" style="font-size: 12px; font-weight: 200; padding: 10px 10px 0 0">
            {!! $user->displayUpdatedBy() !!}
        </div>
    </div>

@stop

@section('page-level-plugins-head')
@stop

@section('page-level-styles-head')

@stop

@section('page-level-plugins')
@stop

@section('page-level-scripts')
    {{-- Metronic + custom Page Scripts --}}

    <script type="text/javascript">
        $(document).ready(function () {

        });
    </script>
@stop