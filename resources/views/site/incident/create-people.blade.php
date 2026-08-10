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
                        <form method="POST" action="{{ action([\App\Http\Controllers\Site\Incident\SiteIncidentPeopleController::class, 'store'], $incident->id) }}" class="horizontal-form" enctype="multipart/form-data">
                            @csrf
                            @include('form-error')

                            <x-form.hidden name="incident_id" :value="$incident->id"/>
                            <x-form.hidden name="status" :value="1"/>
                            <x-form.hidden name="step" :value="0"/>
                            <input type="hidden" name="type" value="9">

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
                                            <th style="width:5%"> #</th>
                                            <th style="width:20%"> Involvement Type</th>
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
                                            <x-form.select name="person_injured" label="Was anyone injured in the incident?" :options="['' => 'Select option', 'y' => 'Yes', 'n' => 'No']"/>
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
                                                <?php $qType = App\Models\Misc\FormQuestion::find(8) ?>
                                            <x-form.select name="type" :label="$qType->name" :options="['' => 'Select type'] + $qType->optionsArray()"/>
                                        </div>
                                        <div class="col-md-3" id="field_type_other">
                                            <x-form.input name="type_other" label="Other Type"/>
                                        </div>
                                    </div>

                                    <div id="person_details_div">
                                        {{-- User + DOB --}}
                                        <div class="row">
                                            {{-- User Id --}}
                                            <div class="col-md-6">
                                                <label for="user_id" id="user_id_label" class="control-label">Person Involved</label>
                                                <x-form.select name="user_id" :options="['' => 'Select user'] + Auth::user()->company->usersSelect('prompt', '1')" plugin="select2"/>
                                            </div>
                                            <div class="col-md-3"></div>
                                            @if (Auth::user()->allowed2('del.site.incident', $incident))
                                                {{-- DOB --}}
                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <label for="dob" class="control-label">Date of Birth</label>
                                                        <div class="input-group date date-picker">
                                                            <input type="text" name="dob" id="dob" class="form-control form-control-inline" value="{{ old('dob', $incident->dob ? $incident->dob->format('d/m/Y') : '') }}" style="background:#FFF" data-date-format="dd-mm-yyyy">
                                                            <span class="input-group-btn"><button class="btn default date-set" type="button"><i class="fa fa-calendar"></i></button></span>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>


                                        {{-- Name + Contact --}}
                                        <div class="row">
                                            <div class="col-md-3">
                                                <x-form.input name="name" label="Full name"/>
                                            </div>
                                            <div class="col-md-3">
                                                <x-form.input name="contact" label="Contact"/>
                                            </div>
                                            <div class="col-md-6">
                                                <x-form.input name="address" label="Address"/>
                                            </div>
                                        </div>

                                        {{-- Employment info --}}
                                        <div class="row">
                                            {{-- Supervisor --}}
                                            <div class="col-md-3">
                                                <x-form.input name="supervisor" label="Supervisor/PCBU"/>
                                            </div>
                                            @if (Auth::user()->allowed2('del.site.incident', $incident))
                                                {{-- Employer --}}
                                                <div class="col-md-3">
                                                    <x-form.input name="employer" label="Employer"/>
                                                </div>

                                                {{-- Engagement --}}
                                                <div class="col-md-3">
                                                    <x-form.select name="engagement" label="Engagement Type" :options="['' => 'Select type', 'Sub-contractor' => 'Sub-contractor', 'Employee' => 'Employee', 'Visitor' => 'Visitor', 'Public' => 'Public']"/>
                                                </div>

                                                {{-- Occupation --}}
                                                <div class="col-md-3">
                                                    <x-form.input name="occupation" label="Occupation"/>
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
                        </form>
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

@stop


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

