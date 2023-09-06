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
                        <!-- BEGIN FORM-->
                        {!! Form::model('todo', ['action' => ['Comms\TodoController@store'], 'files' => true]) !!}
                        @include('form-error')

                        {!! Form::hidden('company_id', Auth::user()->company_id, ['class' => 'form-control', 'id' => 'company_id']) !!}
                        {!! Form::hidden('type_id', $type_id, ['class' => 'form-control', 'id' => 'type_id']) !!}
                        {!! Form::hidden('type_id2', $type_id2, ['class' => 'form-control', 'id' => 'type_id2']) !!}

                        <div class="form-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group {!! fieldHasError('name', $errors) !!}">
                                        {!! Form::label('name', 'Name', ['class' => 'control-label']) !!}
                                        @if ($type)
                                            @if ($type == 'hazard')
                                                <input type="text" name="name" class="form-control" readonly value="Site Hazard Task @ {!! \App\Models\Site\SiteHazard::find($type_id)->site->name !!}">
                                            @endif
                                            @if ($type == 'accident')
                                                <input type="text" name="name" class="form-control" readonly value="Site Accident Task @ {!! \App\Models\Site\SiteAccident::find($type_id)->site->name !!}">
                                            @endif
                                            @if ($type == 'incident')
                                                <input type="text" name="name" class="form-control" readonly value="Site Incident Task @ {!! \App\Models\Site\Incident\SiteIncident::find($type_id)->site_name !!}">
                                            @endif
                                            @if ($type == 'inspection')
                                                <input type="text" name="name" class="form-control" readonly value="{!! \App\Models\Misc\Form\Form::find($type_id)->template->name !!}">
                                            @endif
                                            @if ($type == 'maintenance_task')
                                                <input type="text" name="name" class="form-control" readonly value="Site Maintenance Task @ {!! \App\Models\Site\SiteMaintenance::find($type_id)->site->name !!}">
                                            @endif
                                        @else
                                            {!! Form::text('name', null, ['class' => 'form-control']) !!}
                                        @endif
                                        {!! fieldErrorMessage('name', $errors) !!}
                                    </div>
                                </div>
                                <div class="col-md-3 ">
                                    <div class="form-group {!! fieldHasError('due_at', $errors) !!}">
                                        {!! Form::label('due_at', 'Due Date', ['class' => 'control-label']) !!}
                                        <div class="input-group input-medium date date-picker" data-date-format="dd/mm/yyyy" data-date-start-date="+0d" data-date-reset>
                                            <input type="text" class="form-control" readonly style="background:#FFF" id="due_at" name="due_at">
                                            <span class="input-group-btn">
                                                <button class="btn default date-reset" type="button" id="date-reset">
                                                    <i class="fa fa-times"></i>
                                                </button>
                                                <button class="btn default" type="button">
                                                    <i class="fa fa-calendar"></i>
                                                </button>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-1">
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group {!! fieldHasError('type', $errors) !!}">
                                        @if ($type)
                                            {!! Form::hidden('type', $type, ['class' => 'form-control', 'readonly']) !!}
                                        @else
                                            {!! Form::label('type', 'Type', ['class' => 'control-label']) !!}
                                            {!! Form::select('type', ['general' => 'General'], null, ['class' => 'form-control select2']) !!}
                                        @endif
                                    </div>
                                    {!! fieldErrorMessage('type', $errors) !!}
                                </div>
                            </div>
                            @if ($type && $type == 'inspection')
                                    <?php $question = \App\Models\Misc\Form\FormQuestion::find($type_id2) ?>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            {!! Form::label('question', 'Question', ['class' => 'control-label']) !!}
                                            {!! Form::text('question', $question->name, ['class' => 'form-control', 'readonly']) !!}
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group {!! fieldHasError('info', $errors) !!}">
                                        {!! Form::label('info', 'Description of what to do', ['class' => 'control-label']) !!}
                                        {!! Form::textarea('info', null, ['rows' => '4', 'class' => 'form-control']) !!}
                                        {!! fieldErrorMessage('info', $errors) !!}
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-2">
                                    <div class="form-group {!! fieldHasError('assign_to', $errors) !!}">
                                        {!! Form::label('assign_to', 'Send To', ['class' => 'control-label']) !!}
                                        @if (Auth::user()->company->subscription)
                                            {!! Form::select('assign_to', ['' => 'Select type', 'user' => 'User', 'company' => 'Company', 'role' => 'Role'],
                                             null, ['class' => 'form-control bs-select']) !!}
                                        @else
                                            {!! Form::select('assign_to', ['' => 'Select type', 'user' => 'User'],
                                             null, ['class' => 'form-control bs-select']) !!}
                                        @endif
                                        {!! fieldErrorMessage('assign_to', $errors) !!}
                                    </div>
                                </div>

                                @if ($type)
                                    {!! Form::hidden('assign_multi', 0, ['class' => 'form-control', 'id' => 'assign_multi']) !!}
                                @else
                                    <div class="col-md-2">
                                        <div class="form-group {!! fieldHasError('assign_multi', $errors) !!}">
                                            {!! Form::label('assign_multi', 'Individual / Shared', ['class' => 'control-label']) !!}
                                            <a href="javascript:;" class="popovers" data-container="body" data-trigger="hover"
                                               data-content="Individual will create a separate ToDo item for every user that they must complete themselves.
                                           Shared will create a single ToDo item and any of the selected users may complete on behalf of the whole group"
                                               data-original-title="Individual vs Shared"> <i class="fa fa-question-circle font-grey-silver"></i>
                                            </a>
                                            {!! Form::select('assign_multi', ['1' => 'Individual', '0' => 'Shared'],null, ['class' => 'form-control bs-select']) !!}
                                            {!! fieldErrorMessage('assign_multi', $errors) !!}
                                        </div>
                                    </div>
                                @endif
                                <div class="col-md-8">
                                    <div class="note note-warning" id="help_text" style="margin-top: 10px; display:none"></div>
                                </div>
                            </div>
                            <div class="row" id="user_div" style="display: none">
                                <div class="col-md-12">
                                    <div class="form-group {!! fieldHasError('user_list', $errors) !!}">
                                        {!! Form::label('user_list', 'User(s)', ['class' => 'control-label']) !!}
                                        {!! Form::select('user_list', Auth::user()->company->usersSelect('ALL'),
                                             null, ['class' => 'form-control select2', 'name' => 'user_list[]', 'multiple' => 'multiple', 'width' => '100%']) !!}
                                        {!! fieldErrorMessage('user_list', $errors) !!}
                                    </div>
                                </div>
                            </div>
                            <div class="row" id="company_div" style="display: none">
                                <div class="col-md-12">
                                    <div class="form-group {!! fieldHasError('company_list', $errors) !!}">
                                        {!! Form::label('company_list', 'Company(s)', ['class' => 'control-label']) !!}
                                        {!! Form::select('company_list', Auth::user()->company->companiesSelect('ALL'),
                                             null, ['class' => 'form-control select2', 'name' => 'company_list[]', 'multiple' => 'multiple']) !!}
                                        {!! fieldErrorMessage('company_list', $errors) !!}
                                    </div>
                                </div>
                            </div>
                            <div class="row" id="role_div" style="display: none">
                                <div class="col-md-12">
                                    <div class="form-group {!! fieldHasError('role_list', $errors) !!}">
                                        {!! Form::label('role_list', 'Roles(s)', ['class' => 'control-label']) !!}
                                        {!! Form::select('role_list', App\Models\Misc\Role2::where('company_id', Auth::user()->company_id)->orderBy('name')->pluck('name', 'id')->toArray(),
                                             null, ['class' => 'form-control select2', 'name' => 'role_list[]', 'multiple' => 'multiple']) !!}
                                        {!! fieldErrorMessage('role_list', $errors) !!}
                                    </div>
                                </div>
                            </div>


                            <div class="form-actions right">
                                @if ($type == 'incident')
                                    <a href="/site/incident/{{ $type_id }}" class="btn default"> Back</a>
                                @else
                                    <a href="/todo" class="btn default"> Back</a>
                                @endif
                                <button type="submit" class="btn green">Submit</button>
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
    <link href="/assets/global/plugins/bootstrap-fileinput/bootstrap-fileinput.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/bootstrap-select/css/bootstrap-select.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/select2/css/select2.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/select2/css/select2-bootstrap.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/bootstrap-datepicker/css/bootstrap-datepicker3.min.css" rel="stylesheet" type="text/css"/>
@stop

@section('page-level-plugins')
    <script src="/assets/global/plugins/bootstrap-select/js/bootstrap-select.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/select2/js/select2.full.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js" type="text/javascript"></script>
@stop

@section('page-level-scripts') {{-- Metronic + custom Page Scripts --}}
<script src="/assets/global/plugins/bootstrap-fileinput/bootstrap-fileinput.js" type="text/javascript"></script>
<script src="/assets/pages/scripts/components-bootstrap-select.min.js" type="text/javascript"></script>
<script src="/assets/pages/scripts/components-select2.min.js" type="text/javascript"></script>
<script src="/assets/pages/scripts/components-date-time-pickers.min.js" type="text/javascript"></script>
<script>
    $.ajaxSetup({
        headers: {'X-CSRF-Token': $('meta[name=token]').attr('value')}
    });

    $(document).ready(function () {
        /* Select2 */
        $("#user_list").select2({placeholder: "Select", width: '100%',});
        $("#company_list").select2({placeholder: "Select", width: '100%'});
        $("#role_list").select2({placeholder: "Select", width: '100%'});
        $("#type").select2({width: '100%'});

        $("#date-reset").click(function () {
            $('#due_at').val('');
        })

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

