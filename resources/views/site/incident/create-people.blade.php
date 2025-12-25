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
                        {!! Form::hidden('status', 1, ['class' => 'form-control', 'readonly']) !!}
                        {!! Form::hidden('step', 0, ['class' => 'form-control', 'readonly']) !!}
                        {!! Form::hidden('type', 9, ['class' => 'form-control', 'readonly']) !!}

                        {{-- Progress Steps --}}
                        <div class="mt-element-step hidden-sm hidden-xs">
                            <div class="row step-thin" id="steps">
                                <div class="col-md-6 mt-step-col first done">
                                    <div class="mt-step-number bg-white font-grey">1</div>
                                    <div class="mt-step-title uppercase font-grey-cascade">Lodge</div>
                                    <div class="mt-step-content font-grey-cascade">Lodge notification</div>
                                </div>
                                <div class="col-md-6 mt-step-col active">
                                    <div class="mt-step-number bg-white font-grey">2</div>
                                    <div class="mt-step-title uppercase font-grey-cascade">People</div>
                                    <div class="mt-step-content font-grey-cascade">Add people involved</div>
                                </div>
                            </div>
                        </div>

                        <?php $qType = App\Models\Misc\FormQuestion::find(1); ?>

                        <div class="form-body">
                            {{-- Incident Summary Details --}}
                            <div class="row">
                                <div class="col-md-2"><b>Incident Date:</b></div>
                                <div class="col-xs-10">{{  $incident->date->format('d/m/Y G:i a') }}</div>
                            </div>
                            <div class="row">
                                <div class="col-md-2"><b>{{ ($incident->site_id) ? 'Site:' : 'Place of incident:'}}</b></div>
                                <div class="col-xs-10">
                                    @if ($incident->site)
                                        <b>{!! $incident->site_name !!}</b><br>
                                        {!! $incident->site->address_formatted !!}
                                    @else
                                        {!! $incident->site_name !!}
                                    @endif</div>
                            </div>
                            <div class="row">
                                <div class="col-md-2"><b>Location:</b></div>
                                <div class="col-xs-10">{!! $incident->location !!}</div>
                            </div>
                            <div class="row">
                                <div class="col-md-2"><b>Incident Type:</b></div>
                                <div class="col-xs-10">{!! $qType->responsesCSV('site_incidents', $incident->id) !!}</div>
                            </div>
                            <div class="row">
                                <div class="col-md-2"><b>What occured:</b></div>
                                <div class="col-xs-10">{!! nl2br($incident->describe) !!}</div>
                            </div>
                            <div class="row">
                                <div class="col-md-2"><b>Actions taken:</b></div>
                                <div class="col-xs-10">{!! nl2br($incident->actions_taken) !!}</div>
                            </div>
                            {{-- Attachments --}}
                            <h5><b>Attachments</b></h5>
                            @php
                                $attachments = $incident->attachments;
                                $images = $attachments->where('type', 'image');
                                $files  = $attachments->where('type', 'file');
                            @endphp
                            @if ($attachments->isNotEmpty())
                                <hr style="margin: 10px 0px; padding: 0px;">
                                {{-- Image attachments --}}
                                @if ($images->isNotEmpty())
                                    <div class="row" style="margin: 0">
                                        @foreach ($images as $attachment)
                                            <div style="width: 60px; float: left; padding-right: 5px">
                                                <a href="{{ $attachment->url }}" target="_blank" data-lity>
                                                    <img src="{{ $attachment->url }}" class="thumbnail img-responsive img-thumbnail">
                                                </a>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                                {{-- File attachments --}}
                                @if ($files->isNotEmpty())
                                    <div class="row" style="margin: 0">
                                        @foreach ($files as $attachment)
                                            <i class="fa fa-file-text-o"></i> &nbsp; <a href="{{ $attachment->url }}" target="_blank"> {{ $attachment->name }}</a><br>
                                        @endforeach
                                    </div>
                                @endif
                            @else
                                None
                            @endif
                            <br>

                            @if ($incident->people->count())
                                <h4>Person(s) Involved <span class="pull-right" style="margin-top: -10px;"><a class="btn btn-circle green btn-outline btn-sm" href="/site/incident/{{ $incident->id }}/people/create" data-original-title="Add">Add</a></span></h4>
                                <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                                <div class="note note-warning">
                                    Remember to add the details of everyone involved in the incident including:
                                    <ul>
                                        <li>Injured person</li>
                                        <li>Witnesses</li>
                                        <li>Any person(s) involved in the incident</li>
                                    </ul>
                                </div>
                                <br>
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
                                    @foreach ($incident->people as $person)
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
                            @else
                                <div class="note note-warning">
                                    You need to add the details of everyone involved in the incident including:
                                    <ul>
                                        <li>Injured person</li>
                                        <li>Witnesses</li>
                                        <li>Any person(s) involved in the incident</li>
                                    </ul>
                                </div>


                                {{-- Anyone injured --}}
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group {!! fieldHasError('person_injured', $errors) !!}">
                                            {!! Form::label('person_injured', 'Was anyone injured in the incident?', ['class' => 'control-label']) !!}
                                            {!! Form::select('person_injured', ['' => 'Select option', 'y' => 'Yes', 'n' => 'No'], null, ['class' => 'form-control bs-select ', 'id' => 'person_injured']) !!}
                                        </div>
                                    </div>
                                </div>


                                <div id="person_injured_div">
                                    <h4>Person Injured</h4>
                                    <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                                </div>

                                {{-- Other type of invloved person --}}
                                {{-- Involvement Type --}}
                                <div class="row" id="person_other_div">
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

                                <div id="person_details_div">
                                    {{-- User + DOB --}}
                                    <div class="row">
                                        {{-- User Id --}}
                                        <div class="col-md-6">
                                            <div class="form-group {!! fieldHasError('user_id', $errors) !!}">
                                                {!! Form::label('user_id', 'Person Involved', ['class' => 'control-label', 'id' => 'user_id_label']) !!}
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
                                </div>
                            @endif
                            <br><br>
                            <div class="form-actions right">
                                <a href="/site/incident" class="btn default"> Back</a>
                                @if ($incident->people->count())
                                    <a href="/site/incident/{{ $incident->id }}/lodge" class="btn green"> Save</a>
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

@section('page-level-scripts')
    {{-- Metronic + custom Page Scripts --}}
    <script type="text/javascript">
        $(document).ready(function () {
            /* Select2 */
            $("#user_id").select2({placeholder: "Select user"});

            updateFields();

            // On Change Person_injured
            $("#person_injured").change(function () {
                updateFields();
            });

            // On Change User_id
            $("#user_id").change(function () {
                updateFields();
            });

            // On Change Type
            $("#type").change(function () {
                updateFields();
            });


            function updateFields() {
                $("#person_injured_div").hide();
                $("#person_other_div").hide();
                $("#person_details_div").hide();
                $("#field_type_other").hide();


                // Injured person
                if ($("#person_injured").val() == 'y') {
                    $("#person_injured_div").show();
                    $("#person_details_div").show();
                    $("#user_id_label").html('Injured Person');
                    $("#type").val('9');
                }

                // No-one injured
                if ($("#person_injured").val() == 'n') {
                    $("#person_other_div").show();
                    $("#person_details_div").show();
                    $("#user_id_label").html('Person Involved');
                }

                // Type Other
                if ($("#type").val() == '13')
                    $("#field_type_other").show();


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

