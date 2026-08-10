@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        @if (Auth::user()->hasAnyPermissionType('site'))
            <li><a href="/site">Sites</a><i class="fa fa-circle"></i></li>
        @endif
        @if (Auth::user()->hasAnyPermissionType('site.incident'))
            <li><a href="/site/incident">Site Incidents</a><i class="fa fa-circle"></i></li>
            <li><a href="/site/incident/{{ $incident->id}}/">Incident</a><i class="fa fa-circle"></i></li>
        @endif
        <li><span>Witness Statements</span></li>
    </ul>
@stop

@section('content')
    <div class="page-content-inner">
        <div class="row">
            <div class="col-md-12">
                @include('site/incident/_header')

                <div class="portlet light bordered">
                    <div class="portlet-title">
                        <div class="caption">
                            <span class="caption-subject font-green-haze bold uppercase">Witness Statements</span>
                        </div>
                    </div>
                    <div class="portlet-body form">
                        <form method="POST" action="{{ action([\App\Http\Controllers\Site\Incident\SiteIncidentWitnessController::class, 'store'], $incident->id) }}" class="horizontal-form">
                        @csrf
                        @include('form-error')
                        <div class="form-body">
                            {{-- User + Name --}}
                            <div class="row">
                                {{-- User Id --}}
                                <div class="col-md-5">
                                    <x-form.select name="user_id" label="Witness" :options="['' => 'Select user'] + Auth::user()->company->usersSelect('select')" plugin="select2"/>
                                </div>
                                {{-- Name --}}
                                <div class="col-md-3">
                                    <x-form.input name="name" label="Full name"/>
                                </div>
                                <div class="col-md-4">
                                    <x-form.select name="assign_task" label="Assign task for user to complete statement" :options="['1' => 'Yes - assign to user', '0' => 'No - complete on their behalf']"/>
                                </div>
                            </div>

                            <div id="statement_fields">
                                <div class="row">
                                    <div class="col-md-12">
                                        <h4 class="font-green-haze">Witness Statement</h4>
                                        <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                                    </div>
                                </div>
                                {{-- Event Before --}}
                                <div class="row">
                                    <div class="col-md-12">
                                        <x-form.textarea name="event_before" label="In your own words describe the events leading up to the incident" rows="3"/>
                                    </div>
                                </div>

                                {{-- Event --}}
                                <div class="row">
                                    <div class="col-md-12">
                                        <x-form.textarea name="event" label="In your own words describe the incident" rows="3"/>
                                    </div>
                                </div>

                                {{-- Event After --}}
                                <div class="row">
                                    <div class="col-md-12">
                                        <x-form.textarea name="event_after" label="In your own words describe what happened after the incident" rows="3"/>
                                    </div>
                                </div>
                            </div>

                            @if (Auth::user()->allowed2('del.site.incident', $incident))
                                <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                                {{-- Notes --}}
                                <div class="row">
                                    <div class="col-md-12">
                                        <x-form.textarea name="notes" label="Notes (admin viewable only)" rows="3"/>
                                    </div>
                                </div>
                            @endif

                            <div class="form-actions right">
                                <a href="/site/incident/{{ $incident->id }}" class="btn default"> Back</a>
                                <button type="submit" class="btn green"> Save</button>
                            </div>
                            </form> <!-- END FORM-->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div>
        <div class="pull-right" style="font-size: 12px; font-weight: 200; padding: 10px 10px 0 0">
            {!! $incident->displayUpdatedBy() !!}
        </div>
    </div>

    @stop <!-- END Content -->


@section('page-level-plugins-head')
    <link href="/assets/global/plugins/bootstrap-select/css/bootstrap-select.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/select2/css/select2.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/select2/css/select2-bootstrap.min.css" rel="stylesheet" type="text/css"/>
@stop

@section('page-level-plugins')
    <script src="/assets/global/plugins/bootstrap-select/js/bootstrap-select.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/select2/js/select2.full.min.js" type="text/javascript"></script>
@stop

@section('page-level-scripts') {{-- Metronic + custom Page Scripts --}}

<script type="text/javascript">
    $(document).ready(function () {
        /* Select2 */
        $("#user_id").select2({placeholder: "Select user"});

        // On Change User_id
        $("#user_id").change(function () {
            var user_id = $("#user_id").select2("val");
            if (user_id) {
                $.ajax({
                    url: '/user/data/details/' + user_id,
                    type: 'GET',
                    dataType: 'json',
                    success: function (data) {
                        var fullname = data.firstname;

                        if (data.lastname) fullname = fullname + ' ' + data.lastname
                        $("#name").val(fullname);
                    },
                })
            }
        });

        $("#assign_task").change(function () {
            updateFields();
        });

        updateFields();

        function updateFields() {
            $("#statement_fields").hide();
            if ($("#assign_task").val() == '0') {
                $("#statement_fields").show();
            }
        }
    });
</script>
@stop

