@inject('ozstates', 'App\Http\Utilities\OzStates')

@extends('layout')

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
                        <form method="POST" action="{{ action([App\Http\Controllers\Site\SiteController::class, 'store']) }}" class="horizontal-form">
                            @csrf

                            @include('form-error')

                            <div class="form-body">
                                <div class="row">
                                    <div class="col-md-12">
                                        <h4>Site Details</h4>
                                        <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                                    </div>
                                </div>

                                @if (Auth::user()->permissionLevel('add.site', Auth::user()->company_id) && (Auth::user()->company->parent_company && Auth::user()->permissionLevel('add.site', Auth::user()->company->reportsTo()->id)))
                                    <div class="row">
                                        <div class="col-md-4">
                                            <x-form.select name="company_id" id="site_group" label="Site Owner" :options="[Auth::user()->company_id => Auth::user()->company->name, Auth::user()->company->parent_company => Auth::user()->company->reportsTo()->name]"/>
                                        </div>
                                    </div>
                                @elseif (Auth::user()->permissionLevel('add.site', Auth::user()->company_id))
                                    <x-form.hidden name="company_id" :value="Auth::user()->company_id"/>
                                @elseif (Auth::user()->permissionLevel('add.site', Auth::user()->company->reportsTo()->id))
                                    <x-form.hidden name="company_id" :value="Auth::user()->company->parent_company"/>
                                @endif

                                <div class="row">
                                    <div class="col-md-4">
                                        <x-form.input name="name" label="Name"/>
                                    </div>
                                    <div class="col-md-2">
                                        <x-form.input name="code" label="Job #"/>
                                    </div>
                                    <div class="col-md-2 pull-right">
                                        <x-form.select name="status" label="Status" :options="['-1' => 'Upcoming', '1' => 'Active', '0' => 'Completed']" value="-1"/>
                                    </div>
                                </div>

                                {{-- Address --}}
                                <div class="row">
                                    <div class="col-md-4">
                                        <x-form.input name="address" label="Address"/>
                                    </div>
                                    <div class="col-md-8">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <x-form.input name="suburb" label="Suburb"/>
                                            </div>
                                            <div class="col-md-3">
                                                <x-form.select name="state" label="State" :options="$ozstates::all()" value="NSW"/>
                                            </div>
                                            <div class="col-md-3">
                                                <x-form.input name="postcode" label="Postcode"/>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Supervisors --}}
                                <div class="row">
                                    <div class="col-md-8">
                                        <div id="super-div">
                                            <x-form.select name="supervisors" label="Supervisor(s)" :options="Auth::user()->company->supervisorsSelect()" title="Select a supervisor"/>
                                        </div>
                                    </div>
                                </div>

                                <!-- Client + Supervisor(s) -->
                                <br>
                                <div class="row">
                                    <div class="col-md-12">
                                        <h4>Client Details</h4>
                                        <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4">
                                        <x-form.input name="client_phone_desc" label="Primary Contact Name"/>
                                    </div>
                                    <div class="col-md-3">
                                        <x-form.input name="client_phone" label="Primary Phone No."/>
                                    </div>
                                    <div class="col-md-5">
                                        <x-form.input name="client_email" label="Primary Email"/>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4">
                                        <x-form.input name="client_phone2_desc" label="Second Contact Name"/>
                                    </div>
                                    <div class="col-md-3">
                                        <x-form.input name="client_phone2" label="Secondary Phone No."/>
                                    </div>
                                    <div class="col-md-5">
                                        <x-form.input name="client_email2" label="Secondary Email"/>
                                    </div>
                                </div>
                                <h3 class="form-section"></h3>

                                {{-- Notes --}}
                                <div class="row">
                                    <div class="col-md-12">
                                        <x-form.textarea name="notes" label="Notes" rows="2"/>
                                        <span class="help-block"> For internal use only </span>
                                    </div>
                                </div>
                                <div class="form-actions right">
                                    <a href="/site" class="btn default"> Back</a>
                                    <button type="submit" class="btn green">Save</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop


@section('page-level-plugins-head')
    <link href="/assets/global/plugins/bootstrap-select/css/bootstrap-select.min.css" rel="stylesheet" type="text/css"/>
@stop

@section('page-level-plugins')
    <script src="/assets/global/plugins/bootstrap-select/js/bootstrap-select.min.js" type="text/javascript"></script>
@stop

@section('page-level-scripts')
    {{-- Metronic + custom Page Scripts --}}
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
