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
                        {!! Form::model('SiteIncidentPeople', ['action' => ['Site\Incident\SiteIncidentPeopleController@store', $incident->id], 'class' => 'horizontal-form', 'files' => true]) !!}
                        @include('form-error')

                        {!! Form::hidden('incident_id', $incident->id, ['class' => 'form-control', 'readonly']) !!}
                        {!! Form::hidden('type', 9, ['class' => 'form-control', 'readonly']) !!}

                        {{-- Progress Steps --}}
                        <div class="mt-element-step hidden-sm hidden-xs">
                            <div class="row step-thin" id="steps">
                                <div class="col-md-4 mt-step-col first done">
                                    <div class="mt-step-number bg-white font-grey">1</div>
                                    <div class="mt-step-title uppercase font-grey-cascade">Lodge</div>
                                    <div class="mt-step-content font-grey-cascade">Lodge notification</div>
                                </div>
                                <div class="col-md-4 mt-step-col active">
                                    <div class="mt-step-number bg-white font-grey">2</div>
                                    <div class="mt-step-title uppercase font-grey-cascade">People</div>
                                    <div class="mt-step-content font-grey-cascade">Add people involved</div>
                                </div>
                                <div class="col-md-4 mt-step-col last">
                                    <div class="mt-step-number bg-white font-grey">3</div>
                                    <div class="mt-step-title uppercase font-grey-cascade">Documents</div>
                                    <div class="mt-step-content font-grey-cascade">Add Photos/Documents</div>
                                </div>
                            </div>
                        </div>

                        <?php $qType = App\Models\Misc\FormQuestion::find(1); ?>

                        <div class="form-body">
                            @if ($incident->people->count())
                                <div class="note note-warning">
                                    Once you've finished adding all the people invloved continue onto <a href="/site/incident/{{ $incident->id }}/docs" class="btn green btn-outline btn-xs"> Next Step</a> to add Photos / Documents of the incident.
                                </div>
                                <br>
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
                                <br>
                                Remember to add the details of everyone involved in the incident including:
                                <ul>
                                    <li>Injured person</li>
                                    <li>Witnesses</li>
                                    <li>Any person(s) involved in the incident</li>
                                </ul>

                            @else
                                <div class="note note-warning">
                                    You need to add the details of everyone involved in the incident including:
                                    <ul>
                                        <li>Injured person</li>
                                        <li>Witnesses</li>
                                        <li>Any person(s) involved in the incident</li>
                                    </ul>
                                </div>
                                <h4>Person Injured</h4>
                                <hr style="padding: 0px; margin: 0px 0px 10px 0px">

                                {{-- Involvement Type --}}
                                @if (false)
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="form-group {!! fieldHasError('type', $errors) !!}">
                                                <?php $qType = App\Models\Misc\FormQuestion::find(8) ?>
                                                {!! Form::label('type', $qType->name, ['class' => 'control-label']) !!}
                                                {!! Form::select('type', ['' => 'Select type'] + $qType->optionsArray(), null, ['class' => 'form-control bs-select ', 'id' => 'type']) !!}
                                            </div>
                                        </div>
                                        <div class="col-md-3" id="field_type_other">
                                            <div class="form-group {!! fieldHasError('type_other', $errors) !!}">
                                                {!! Form::label('type_other', 'Other Type', ['class' => 'control-label']) !!}
                                                {!! Form::text('type_other', null, ['class' => 'form-control']) !!}
                                                {!! fieldErrorMessage('type_other', $errors) !!}
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                {{-- User + DOB --}}
                                <div class="row">
                                    {{-- User Id --}}
                                    <div class="col-md-6">
                                        <div class="form-group {!! fieldHasError('user_id', $errors) !!}">
                                            {!! Form::label('user_id', 'Injured Person', ['class' => 'control-label']) !!}
                                            {!! Form::select('user_id', ['' => 'Select user'] + Auth::user()->company->usersSelect('prompt', '1'),
                                                 null, ['class' => 'form-control select2', 'name' => 'user_id', 'id'  => 'user_id',]) !!}
                                            {!! fieldErrorMessage('user_id', $errors) !!}
                                        </div>
                                    </div>
                                    <div class="col-md-3"></div>
                                    @if (Auth::user()->allowed2('del.site.incident', $incident))
                                        {{-- DOB --}}
                                        <div class="col-md-3">
                                            <div class="form-group {!! fieldHasError('dob', $errors) !!}">
                                                {!! Form::label('dob', 'Date of Birth', ['class' => 'control-label']) !!}
                                                <div class="input-group date date-picker">
                                                    {!! Form::text('dob', ($incident->dob) ? $incident->dob->format('d/m/Y') : '', ['class' => 'form-control form-control-inline', 'style' => 'background:#FFF', 'data-date-format' => "dd-mm-yyyy"]) !!}
                                                    <span class="input-group-btn"><button class="btn default date-set" type="button"><i class="fa fa-calendar"></i></button></span>
                                                </div>
                                                {!! fieldErrorMessage('dob', $errors) !!}
                                            </div>
                                        </div>
                                    @endif
                                </div>


                                {{-- Name + Contact --}}
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group {!! fieldHasError('name', $errors) !!}">
                                            {!! Form::label('name', 'Full name', ['class' => 'control-label']) !!}
                                            {!! Form::text('name', null, ['class' => 'form-control']) !!}
                                            {!! fieldErrorMessage('name', $errors) !!}
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group {!! fieldHasError('contact', $errors) !!}">
                                            {!! Form::label('contact', 'Contact', ['class' => 'control-label']) !!}
                                            {!! Form::text('contact', null, ['class' => 'form-control']) !!}
                                            {!! fieldErrorMessage('contact', $errors) !!}
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group {!! fieldHasError('address', $errors) !!}">
                                            {!! Form::label('address', 'Address', ['class' => 'control-label']) !!}
                                            {!! Form::text('address', null, ['class' => 'form-control']) !!}
                                            {!! fieldErrorMessage('address', $errors) !!}
                                        </div>
                                    </div>
                                </div>

                                {{-- Employment info --}}
                                <div class="row">
                                    {{-- Supervisor --}}
                                    <div class="col-md-3">
                                        <div class="form-group {!! fieldHasError('supervisor', $errors) !!}">
                                            {!! Form::label('supervisor', 'Supervisor/PCBU', ['class' => 'control-label']) !!}
                                            {!! Form::text('supervisor', null, ['class' => 'form-control']) !!}
                                            {!! fieldErrorMessage('supervisor', $errors) !!}
                                        </div>
                                    </div>
                                    @if (Auth::user()->allowed2('del.site.incident', $incident))
                                        {{-- Employer --}}
                                        <div class="col-md-3">
                                            <div class="form-group {!! fieldHasError('employer', $errors) !!}">
                                                {!! Form::label('employer', 'Employer', ['class' => 'control-label']) !!}
                                                {!! Form::text('employer', null, ['class' => 'form-control']) !!}
                                                {!! fieldErrorMessage('employer', $errors) !!}
                                            </div>
                                        </div>

                                        {{-- Engagement --}}
                                        <div class="col-md-3">
                                            <div class="form-group {!! fieldHasError('engagement', $errors) !!}">
                                                {!! Form::label('engagement', 'Engagement Type', ['class' => 'control-label']) !!}
                                                {!! Form::select('engagement', ['' => 'Select type', 'Sub-contractor' => 'Sub-contractor', 'Employee' => 'Employee', 'Visitor' => 'Visitor', 'Public' => 'Public'], null, ['class' => 'form-control bs-select', 'id'  => 'engagement',]) !!}
                                                {!! fieldErrorMessage('engagement', $errors) !!}
                                            </div>
                                        </div>

                                        {{-- Occupation --}}
                                        <div class="col-md-3">
                                            <div class="form-group {!! fieldHasError('occupation', $errors) !!}">
                                                {!! Form::label('occupation', 'Occupation', ['class' => 'control-label']) !!}
                                                {!! Form::text('occupation', null, ['class' => 'form-control']) !!}
                                                {!! fieldErrorMessage('occupation', $errors) !!}
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            @endif
                            <br><br>
                            <div class="form-actions right">
                                <a href="/site/incident" class="btn default"> Back</a>
                                @if ($incident->people->count())
                                    <a href="/site/incident/{{ $incident->id }}/docs" class="btn green"> Next Step</a>
                                @else
                                    <button type="submit" class="btn green"> Save</button>
                                @endif
                            </div>
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
    <link href="/assets/global/plugins/select2/css/select2.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/select2/css/select2-bootstrap.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/bootstrap-select/css/bootstrap-select.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/bootstrap-datepicker/css/bootstrap-datepicker.min.css" rel="stylesheet" type="text/css"/>
@stop

@section('page-level-plugins')
    <script src="/assets/global/plugins/select2/js/select2.full.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/bootstrap-select/js/bootstrap-select.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js" type="text/javascript"></script>
@stop

@section('page-level-scripts') {{-- Metronic + custom Page Scripts --}}
<script type="text/javascript">
    $(document).ready(function () {
        /* Select2 */
        $("#user_id").select2({placeholder: "Select user"});

        updateFields();

        // On Change User_id
        $("#user_id").change(function () {
            updateFields();
        });


        function updateFields() {
            var user_id = $("#user_id").select2("val");
            if (user_id) {
                $.ajax({
                    url: '/user/data/details/' + user_id,
                    type: 'GET',
                    dataType: 'json',
                    success: function (data) {
                        var fullname = data.firstname;
                        var address = data.address;

                        if (data.lastname) fullname = fullname + ' ' + data.lastname
                        if (address) address = address + ', ' + data.suburb;
                        if (address) address = address + ', ' + data.state;
                        if (address) address = address + ', ' + data.postcode;

                        $("#name").val(fullname);
                        $("#contact").val(data.phone);
                        $("#address").val(address);

                        // Company Details
                        $.ajax({
                            url: '/company/data/details/' + data.company_id,
                            type: 'GET',
                            dataType: 'json',
                            success: function (data2) {
                                $("#employer").val(data2.name);
                            },
                        })
                    },
                })
            }
        }
    });

    $('.date-picker').datepicker({
        autoclose: true,
        clearBtn: true,
        format: 'dd/mm/yyyy',
    });
</script>
@stop

