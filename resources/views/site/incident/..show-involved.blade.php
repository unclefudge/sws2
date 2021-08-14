@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        @if (Auth::user()->hasAnyPermissionType('site'))
            <li><a href="/site">Sites</a><i class="fa fa-circle"></i></li>
        @endif
        @if (Auth::user()->hasAnyPermissionType('site.incident'))
            <li><a href="/site/incident">Site Incidents</a><i class="fa fa-circle"></i></li>
        @endif
        <li><span>Incident Report</span></li>
    </ul>
@stop

@section('content')
    <div class="page-content-inner">
        <div class="row">
            <div class="col-md-12">
                <div class="portlet light bordered">
                    <div class="portlet-title">
                        <div class="caption">
                            <span class="caption-subject font-green-haze bold uppercase">Incident Report</span>
                            <span class="caption-helper"> ID: {{ $incident->id }}</span>
                        </div>
                    </div>
                    <div class="portlet-body form">
                        {!! Form::model($incident, ['method' => 'PATCH', 'action' => ['Site\Incident\SiteIncidentController@update', $incident->id], 'class' => 'horizontal-form']) !!}
                        @include('form-error')

                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group {!! fieldHasError('site_id', $errors) !!}">
                                    {!! Form::label('site_id', 'Site', ['class' => 'control-label']) !!}
                                    @if (Auth::user()->allowed2('edit.site.incident', $incident) && false)
                                        {!! Form::select('site_id', Auth::user()->company->sitesSelect('prompt'), $incident->site_id, ['class' => 'form-control bs-select']) !!}
                                        {!! fieldErrorMessage('site_id', $errors) !!}
                                    @else
                                        {!! Form::text('site_name', $incident->site->name, ['class' => 'form-control', 'disabled']) !!}
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-5">
                                <div class="form-group">
                                    {!! Form::label('site_address', 'Address', ['class' => 'control-label']) !!}
                                    {!! Form::text('site_address', $incident->site->full_address, ['class' => 'form-control', 'disabled']) !!}
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group {!! fieldHasError('supervisor', $errors) !!}">
                                    {!! Form::label('supervisor', 'Supervisor', ['class' => 'control-label']) !!}
                                    {!! Form::text('supervisor', $incident->supervisor, ['class' => 'form-control',
                                    (Auth::user()->allowed2('del.site.incident', $incident)) ? '' : 'readonly']) !!}
                                    {!! fieldErrorMessage('supervisor', $errors) !!}
                                </div>
                            </div>
                        </div>

                        {{-- Persons Involved --}}
                        <h4>Person(s) Involved <span class="pull-right" style="margin-top: -10px;"><a class="btn btn-circle green btn-outline btn-sm" href="/site/incident/{{ $incident->id }}/people/create" data-original-title="Add">Add</a></span></h4>
                        <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                        <table class="table table-striped table-bordered table-hover order-column" id="table_people">
                            <thead>
                            <tr class="mytable-header">
                                <th width="5%"> #</th>
                                <th width="20%"> Involvement Type</th>
                                <th> Name</th>
                                <th> Contact</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach ($incident->people->sortBy('name') as $person)
                                <tr>
                                    <td>
                                        <div class="text-center"><a href="/site/incident/{{ $incident->id }}/people/{{ $person->id  }}"><i class="fa fa-search"></i></a></div>
                                    </td>
                                    <td>{{ $person->typeName }}</td>
                                    <td>{{ $person->name }}</td>
                                    <td>{{ $person->contact }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                        {!! Form::close() !!} <!-- END FORM-->
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
    <link href="/assets/global/plugins/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/select2/css/select2.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/select2/css/select2-bootstrap.min.css" rel="stylesheet" type="text/css"/>
@stop

@section('page-level-plugins')
    <script src="/assets/global/plugins/bootstrap-select/js/bootstrap-select.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/bootstrap-select/js/bootstrap-select.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/select2/js/select2.full.min.js" type="text/javascript"></script>
@stop

@section('page-level-scripts') {{-- Metronic + custom Page Scripts --}}
<script src="/assets/pages/scripts/components-date-time-pickers.min.js" type="text/javascript"></script>

<script type="text/javascript">
    $(document).ready(function () {
        /* Select2 */
        $("#site_id").select2({placeholder: "Select Site"});
        $("#type").select2({placeholder: "Check all applicable"});
        $("#treatment").select2({placeholder: "Check all applicable"});
        $("#injured_part").select2({placeholder: "Check all applicable"});
        $("#injured_nature").select2({placeholder: "Check all applicable"});
        $("#injured_mechanism").select2({placeholder: "Check all applicable"});
        $("#injured_agency").select2({placeholder: "Check all applicable"});
        updateFields();

        // On Change Site ID
        $("#site_id").change(function () {
            updateFields();
        });

        // On Change Type
        $("#type").change(function () {
            updateFields();
        });

        // On Change Treatment
        $("#treatment").change(function () {
            updateFields();
        });

        // On Change Injured Part
        $("#injured_part").change(function () {
            updateFields();
        });

        function updateFields() {
            var site_id = $("#site_id").select2("val");
            var treatment = $("#treatment").select2("val");
            var injured_part = $("#injured_part").select2("val");

            // Type
            if ($("#type_text").val())
                var types = $("#type_text").val().split(', ');
            else
                var types = $("#type").select2("val");

            $("#field_type_other").hide();
            $("#injury_details").hide();
            $("#damage_details").hide();
            $("#field_treatment_other").hide();
            $("#field_injured_part_other").hide();

            // Show relevant fields
            if (types != null && (types.includes('2') || types.includes('Injury / illness'))) $("#injury_details").show();
            if (types != null && (types.includes('3') || types.includes('Damage'))) $("#damage_details").show();
            if (treatment != null && treatment.includes('20')) $("#field_treatment_other").show(); // Other treatment
            if (injured_part != null && injured_part.includes('49')) $("#field_injured_part_other").show(); // Other part
        }

    });

    // Force datepicker to not be able to select dates after today
    $('.bs-datetime').datetimepicker({
        endDate: new Date(),
        format: 'dd/mm/yyyy hh:ii',
    });
</script>
@stop

