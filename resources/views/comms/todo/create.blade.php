@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        <li><a href="/todo/">Todo</a><i class="fa fa-circle"></i></li>
        <li><span>Create Todo</span></li>
    </ul>
@stop

@section('content')
    <div class="page-content-inner">
        <div class="row">
            <div class="col-md-12">
                @if ($type && $type == 'incident')
                        <?php $incident = \App\Models\Site\Incident\SiteIncident::find($type_id) ?>
                    @include('site/incident/_header')
                @endif

                <div class="portlet light bordered">
                    <div class="portlet-title">
                        <div class="caption">
                            <span class="caption-subject font-green-haze bold uppercase">Create Todo</span>
                        </div>
                    </div>
                    <div class="portlet-body form">
                        <form method="POST" action="{{ action([App\Http\Controllers\Comms\TodoController::class, 'store']) }}" enctype="multipart/form-data">
                            @csrf
                            @include('form-error')

                            <x-form.hidden name="company_id" :value="Auth::user()->company_id"/>
                            <x-form.hidden name="type_id" :value="$type_id"/>
                            <x-form.hidden name="type_id2" :value="$type_id2"/>

                            <div class="form-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        @if ($type)
                                            @if ($type == 'hazard')
                                                <x-form.input name="name" label="Name" :value="'Site Hazard Task @ ' . \App\Models\Site\SiteHazard::find($type_id)->site->name" readonly/>
                                            @endif
                                            @if ($type == 'accident')
                                                <x-form.input name="name" label="Name" :value="'Site Accident Task @ ' . \App\Models\Site\SiteAccident::find($type_id)->site->name" readonly/>
                                            @endif
                                            @if ($type == 'incident')
                                                <x-form.input name="name" label="Name" :value="'Site Incident Task @ ' . \App\Models\Site\Incident\SiteIncident::find($type_id)->site_name" readonly/>
                                            @endif
                                            @if ($type == 'inspection')
                                                <x-form.input name="name" label="Name" :value="\App\Models\Misc\Form\Form::find($type_id)->template->name" readonly/>
                                            @endif
                                            @if ($type == 'maintenance_task')
                                                <x-form.input name="name" label="Name" :value="'Site Maintenance Task @ ' . \App\Models\Site\SiteMaintenance::find($type_id)->site->name" readonly/>
                                            @endif
                                        @else
                                            <x-form.input name="name" label="Name"/>
                                        @endif
                                    </div>
                                    <div class="col-md-3 ">
                                        <x-form.datepicker name="due_at" label="Due Date" format="dd/mm/yyyy" start-date="+0d" clear-button wrapper-class="input-medium" readonly/>
                                    </div>
                                    <div class="col-md-1">
                                    </div>
                                    <div class="col-md-2">
                                        @if ($type)
                                            <x-form.hidden name="type" :value="$type"/>
                                        @else
                                            <x-form.select name="type" label="Type" :options="['general' => 'General']" style="width:100%"/>
                                        @endif
                                    </div>
                                </div>
                                @if ($type && $type == 'inspection')
                                        <?php $question = \App\Models\Misc\Form\FormQuestion::find($type_id2) ?>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <x-form.input name="question" label="Question" :value="$question->name" readonly/>
                                        </div>
                                    </div>
                                @endif

                                <div class="row">
                                    <div class="col-md-12">
                                        <x-form.textarea name="info" label="Description of what to do" rows="4"/>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-2">
                                        @if (Auth::user()->company->subscription)
                                            <x-form.select name="assign_to" label="Send To" :options="['' => 'Select type', 'user' => 'User', 'company' => 'Company', 'role' => 'Role']"/>
                                        @else
                                            <x-form.select name="assign_to" label="Send To" :options="['' => 'Select type', 'user' => 'User']"/>
                                        @endif
                                    </div>

                                    @if ($type)
                                        <x-form.hidden name="assign_multi" :value="0"/>
                                    @else
                                        <div class="col-md-2">
                                            <x-form.select name="assign_multi" label="Individual / Shared" :options="['1' => 'Individual', '0' => 'Shared']"
                                                           help="Individual will create a separate ToDo item for every user that they must complete themselves. Shared will create a single ToDo item and any of the selected users may complete on behalf of the whole group"/>
                                        </div>
                                    @endif
                                    <div class="col-md-8">
                                        <div class="note note-warning" id="help_text" style="margin-top: 10px; display:none"></div>
                                    </div>
                                </div>
                                <div class="row" id="user_div" style="display: none">
                                    <div class="col-md-12">
                                        <x-form.select name="user_list[]" label="User(s)" :options="Auth::user()->company->usersSelect('ALL')" plugin="select2" multiple style="width:100%"/>
                                    </div>
                                </div>
                                <div class="row" id="company_div" style="display: none">
                                    <div class="col-md-12">
                                        <x-form.select name="company_list[]" label="Company(s)" :options="Auth::user()->company->companiesSelect('ALL')" plugin="select2" multiple style="width:100%"/>
                                    </div>
                                </div>
                                <div class="row" id="role_div" style="display: none">
                                    <div class="col-md-12">
                                        <x-form.select name="role_list[]" label="Roles(s)" :options="App\Models\Misc\Role2::where('company_id', Auth::user()->company_id)->orderBy('name')->pluck('name', 'id')->toArray()" plugin="select2" multiple style="width:100%"/>
                                    </div>
                                </div>

                                {{-- Attachments --}}
                                <div class="row">
                                    <div class="col-md-6">
                                        <h5 id="uploads_label">Upload Attachments</h5>
                                        <x-form.filepond name="filepond[]" multiple/>
                                        <br><br>
                                    </div>
                                </div>


                                <div class="form-actions right">
                                    @if ($type == 'incident')
                                        <a href="/site/incident/{{ $type_id }}" class="btn default"> Back</a>
                                    @else
                                        <a href="{{url()->previous()}}" class="btn default"> Back</a>
                                    @endif
                                    <button type="submit" class="btn green">Submit</button>
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
    {{--<link href="/assets/global/plugins/bootstrap-fileinput/bootstrap-fileinput.css" rel="stylesheet" type="text/css"/>--}}
    <link href="https://unpkg.com/filepond/dist/filepond.css" rel="stylesheet" type="text/css"/>   {{-- Filepond --}}
    <link href="/assets/global/plugins/bootstrap-select/css/bootstrap-select.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/select2/css/select2.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/select2/css/select2-bootstrap.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/bootstrap-datepicker/css/bootstrap-datepicker3.min.css" rel="stylesheet" type="text/css"/>
@stop

@section('page-level-plugins')
    <script src="/assets/global/plugins/bootstrap-select/js/bootstrap-select.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/select2/js/select2.full.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js" type="text/javascript"></script>
    <script src="https://unpkg.com/filepond/dist/filepond.min.js"></script> {{-- FilePond --}}
@stop

@section('page-level-scripts')
    {{-- Metronic + custom Page Scripts --}}
    {{--}}<script src="/assets/global/plugins/bootstrap-fileinput/bootstrap-fileinput.js" type="text/javascript"></script>--}}
    <script src="/assets/pages/scripts/components-bootstrap-select.min.js" type="text/javascript"></script>
    <script src="/assets/pages/scripts/components-select2.min.js" type="text/javascript"></script>
    <script src="/assets/pages/scripts/components-date-time-pickers.min.js" type="text/javascript"></script>
    <script src="/js/filepond-basic.js" type="text/javascript"></script>
    <script>
        $.ajaxSetup({
            headers: {'X-CSRF-Token': $('meta[name=token]').attr('value')}
        });

        $(document).ready(function () {
            /* Select2 */
            $("#user_list").select2({placeholder: "Select", width: '100%',});
            $("#company_list").select2({placeholder: "Select", width: '100%'});
            $("#role_list").select2({placeholder: "Select", width: '100%'});
            // On Change Assign To
            $("#assign_to").change(function () {
                showAssignedList();
                showHelp();
            });

            // On Change Assign Multi
            $("#assign_multi").change(function () {
                showHelp();
            });

            function showAssignedList() {
                $("#user_div").hide();
                $("#company_div").hide();
                $("#role_div").hide();

                // Assign to User selected
                if ($("#assign_to").val() == 'user')
                    $("#user_div").show();
                // Assign to Company selected
                if ($("#assign_to").val() == 'company')
                    $("#company_div").show();
                // Assign to Group selected
                if ($("#assign_to").val() == 'role')
                    $("#role_div").show();
            }

            // Display Help test
            function showHelp() {
                if ($("#assign_to").val() != '')
                    $("#help_text").show();
                else
                    $("#help_text").hide();

                var help_text = document.getElementById("help_text");
                if ($("#assign_to").val() == 'user' && $("#assign_multi").val() == '0')
                    help_text.textContent = "One ToDo but any user may complete it on behalf of all of the other users";
                if ($("#assign_to").val() == 'user' && $("#assign_multi").val() == '1')
                    help_text.textContent = "One ToDo per user which they must complete themselves.";

                if ($("#assign_to").val() == 'company' && $("#assign_multi").val() == '0')
                    help_text.textContent = "One ToDo per company but any user within that company may complete it on behalf of their company.";
                if ($("#assign_to").val() == 'company' && $("#assign_multi").val() == '1')
                    help_text.textContent = "One ToDo per user within each of the selected companies";

                if ($("#assign_to").val() == 'role' && $("#assign_multi").val() == '0')
                    help_text.textContent = "One ToDo per role but any user within that role may complete it on behalf of the role.";
                if ($("#assign_to").val() == 'role' && $("#assign_multi").val() == '1')
                    help_text.textContent = "One ToDo per user within each of the selected roles";
            };

            showAssignedList();
            showHelp();
        });
    </script>
@stop

