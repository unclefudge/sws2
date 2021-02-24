@inject('ozstates', 'App\Http\Utilities\OzStates')

@extends('layout')

@section('pagetitle')
    <div class="page-title">
        <h1><i class="fa fa-building"></i> Site Management</h1>
    </div>
@stop

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        <li><a href="/site">Sites</a><i class="fa fa-circle"></i></li>
        <li><span>Create new site</span></li>
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
                            <span class="caption-subject font-green-haze bold uppercase">Create New Site</span>
                            <span class="caption-helper"></span>
                        </div>
                    </div>
                    <div class="portlet-body form">
                        <!-- BEGIN FORM-->
                        {!! Form::model('site', ['action' => 'Site\SiteController@store', 'class' => 'horizontal-form']) !!}

                        @include('form-error')

                        <div class="form-body">
                            @if (Auth::user()->permissionLevel('add.site', Auth::user()->company_id) && (Auth::user()->company->parent_company && Auth::user()->permissionLevel('add.site', Auth::user()->company->reportsTo()->id)))
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            {!! Form::label('company_id', 'Site Owner', ['class' => 'control-label']) !!}
                                            {!! Form::select('company_id', [Auth::user()->company_id => Auth::user()->company->name, Auth::user()->company->parent_company => Auth::user()->company->reportsTo()->name], null, ['class' => 'form-control bs-select', 'id' => 'site_group']) !!}
                                        </div>
                                    </div>
                                </div>
                            @elseif (Auth::user()->permissionLevel('add.site', Auth::user()->company_id))
                                {!! Form::hidden('company_id', Auth::user()->company_id) !!}
                            @elseif (Auth::user()->permissionLevel('add.site', Auth::user()->company->reportsTo()->id))
                                {!! Form::hidden('company_id', Auth::user()->company->parent_company) !!}
                            @endif
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group {!! fieldHasError('name', $errors) !!}">
                                        {!! Form::label('name', 'Name', ['class' => 'control-label']) !!}
                                        {!! Form::text('name', null, ['class' => 'form-control']) !!}
                                        {!! fieldErrorMessage('name', $errors) !!}
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group {!! fieldHasError('code', $errors) !!}">
                                        {!! Form::label('code', 'Site No.', ['class' => 'control-label']) !!}
                                        {!! Form::text('code', null, ['class' => 'form-control']) !!}
                                        {!! fieldErrorMessage('code', $errors) !!}
                                    </div>
                                </div>
                                <div class="col-md-2 pull-right">
                                    <div class="form-group {!! fieldHasError('status', $errors) !!}">
                                        {!! Form::label('staus', 'Status', ['class' => 'control-label']) !!}
                                        {!! Form::select('status', ['-1' => 'Upcoming', '1' => 'Active', '0' => 'Completed'],
                                         '-1', ['class' => 'form-control bs-select']) !!}
                                        {!! fieldErrorMessage('status', $errors) !!}
                                    </div>
                                </div>
                            </div>

                            <!-- Address -->
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group {!! fieldHasError('address', $errors) !!}">
                                        {!! Form::label('address', 'Address', ['class' => 'control-label']) !!}
                                        {!! Form::text('address', null, ['class' => 'form-control']) !!}
                                        {!! fieldErrorMessage('address', $errors) !!}
                                    </div>
                                </div>
                                <div class="col-md-8">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group {!! fieldHasError('suburb', $errors) !!}">
                                                {!! Form::label('suburb', 'Suburb', ['class' => 'control-label']) !!}
                                                {!! Form::text('suburb', null, ['class' => 'form-control']) !!}
                                                {!! fieldErrorMessage('suburb', $errors) !!}
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group {!! fieldHasError('state', $errors) !!}">
                                                {!! Form::label('state', 'State', ['class' => 'control-label']) !!}
                                                {!! Form::select('state', $ozstates::all(),
                                                 'NSW', ['class' => 'form-control bs-select']) !!}
                                                {!! fieldErrorMessage('state', $errors) !!}
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group {!! fieldHasError('postcode', $errors) !!}">
                                                {!! Form::label('postcode', 'Postcode', ['class' => 'control-label']) !!}
                                                {!! Form::text('postcode', null, ['class' => 'form-control']) !!}
                                                {!! fieldErrorMessage('postcode', $errors) !!}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <hr>

                            <!-- Client + Supervisor(s) -->
                            <div class="row">
                                <!--
                                <div class="col-md-4">
                                    <div class="form-group {!! fieldHasError('client_id', $errors) !!}">
                                        {!! Form::label('client_id', 'Client', ['class' => 'control-label']) !!}
                                {!! Form::select('client_id', Auth::user()->company->clientSelect('prompt'),
                                 '', ['class' => 'form-control bs-select']) !!}
                                {!! fieldErrorMessage('client_id', $errors) !!}
                                        </div>
                                    </div>
                                    -->
                                <div class="col-md-4">
                                    <div class="form-group {!! fieldHasError('client_phone', $errors) !!}">
                                        {!! Form::label('client_phone', 'Client Phone No.', ['class' => 'control-label']) !!}
                                        {!! Form::text('client_phone', null, ['class' => 'form-control']) !!}
                                        {!! fieldErrorMessage('client_phone', $errors) !!}
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group {!! fieldHasError('client_phone_desc', $errors) !!}">
                                        {!! Form::label('client_phone_desc', 'Phone Description', ['class' => 'control-label']) !!}
                                        {!! Form::text('client_phone_desc', null, ['class' => 'form-control']) !!}
                                        {!! fieldErrorMessage('client_phone_desc', $errors) !!}
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group {!! fieldHasError('client_phone2', $errors) !!}">
                                        {!! Form::label('client_phone2', 'Client Second Phone No.', ['class' => 'control-label']) !!}
                                        {!! Form::text('client_phone2', null, ['class' => 'form-control']) !!}
                                        {!! fieldErrorMessage('client_phone2', $errors) !!}
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group {!! fieldHasError('client_phone_desc', $errors) !!}">
                                        {!! Form::label('client_phone2_desc', 'Second Phone Description', ['class' => 'control-label']) !!}
                                        {!! Form::text('client_phone2_desc', null, ['class' => 'form-control']) !!}
                                        {!! fieldErrorMessage('client_phone2_desc', $errors) !!}
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="form-group {!! fieldHasError('supervisors', $errors) !!}" id="super-div">
                                        {!! Form::label('supervisors', 'Supervisor(s)', ['class' => 'control-label']) !!}
                                        {!! Form::select('supervisors',
                                        Auth::user()->company->supervisorsSelect(),
                                         null, ['class' => 'form-control bs-select', 'name' => 'supervisors[]', 'title' => 'Select one or more supervisors', 'multiple']) !!}
                                        {!! fieldErrorMessage('supervisors', $errors) !!}
                                    </div>
                                </div>
                            </div>

                            <h3 class="form-section"></h3>

                            <!-- Notes -->
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group {!! fieldHasError('notes', $errors) !!}">
                                        {!! Form::label('notes', 'Notes', ['class' => 'control-label']) !!}
                                        {!! Form::textarea('notes', null, ['rows' => '2', 'class' => 'form-control']) !!}
                                        {!! fieldErrorMessage('notes', $errors) !!}
                                        <span class="help-block"> For internal use only </span>
                                    </div>
                                </div>
                            </div>
                            <div class="form-actions right">
                                <a href="/site" class="btn default"> Back</a>
                                <button type="submit" class="btn green">Save</button>
                            </div>
                        </div> <!--/form-body-->
                        {!! Form::close() !!}
                                <!-- END FORM-->
                    </div>
                </div>
            </div>
        </div>
    </div>
    @stop <!-- END Content -->


@section('page-level-plugins-head')
    <link href="/assets/global/plugins/bootstrap-select/css/bootstrap-select.min.css" rel="stylesheet" type="text/css"/>
@stop

@section('page-level-plugins')
    <script src="/assets/global/plugins/bootstrap-select/js/bootstrap-select.min.js" type="text/javascript"></script>
@stop

@section('page-level-scripts') {{-- Metronic + custom Page Scripts --}}
<script src="/assets/pages/scripts/components-bootstrap-select.min.js" type="text/javascript"></script>
<script>
    $(document).ready(function () {
        //$('#transient').bootstrapSwitch('state', false);
        $('#transient').on('switchChange.bootstrapSwitch', function (event, state) {
            $('#super-div').toggle();
        });
    });
</script>
@stop

