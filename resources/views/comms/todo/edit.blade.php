@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        <li><a href="/todo/">Todo</a><i class="fa fa-circle"></i></li>
        <li><span>Edit Todo</span></li>
    </ul>
@stop

@section('content')
    <div class="page-content-inner">
        <div class="row">
            <div class="col-md-12">
                <div class="portlet light bordered">
                    <div class="portlet-title">
                        <div class="caption">
                            <span class="caption-subject font-green-haze bold uppercase"> Todo item</span>
                            <span class="caption-helper"> - ID: {{ $todo->id }}</span>
                        </div>
                    </div>
                    <div class="portlet-body form">
                        <form method="POST" action="{{ action([App\Http\Controllers\Comms\TodoController::class, 'update'], $todo->id) }}" enctype="multipart/form-data">
                            @csrf
                            @method('PATCH')
                            @include('form-error')

                            <x-form.hidden name="company_id" :value="Auth::user()->company_id"/>
                            <x-form.hidden name="type_id" :value="$todo->type_id"/>

                            <div class="form-body">
                                {{-- Display question name for Incidents Prevents --}}
                                @if ($todo->type && $todo->type_id2 && $todo->type == 'incident prevent')
                                        <?php
                                        $question = \App\Models\Misc\FormQuestion::find($todo->type_id2);
                                        $qtext = $question->name;
                                        if ($question->parent)
                                            $qtext = $question->question->name . " - $qtext";
                                        ?>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <x-form.input name="question" label="Incident Root Cause / Contributing Factor" :value="$qtext" readonly/>
                                        </div>
                                    </div>
                                @endif

                                <div class="row">
                                    <div class="col-md-5">
                                        @if (in_array($todo->type, ['incident prevent']) && Auth::user()->hasAnyRole2('whs-manager|mgt-general-manager|web-admin'))
                                            <x-form.input name="name" label="Name" :value="$todo->name"/>
                                        @else
                                            <x-form.input name="name" label="Name" :value="$todo->name" readonly/>
                                        @endif
                                    </div>
                                    <div class="col-md-3 ">
                                        <x-form.datepicker name="due_at" label="Due Date" :value="$todo->due_at ? $todo->due_at->format('d/m/Y') : null" format="dd/mm/yyyy" start-date="+0d" clear-button wrapper-class="input-medium" readonly/>
                                    </div>
                                    <div class="col-md-2">
                                    </div>
                                    <div class="col-md-2">
                                        <x-form.input name="type" label="Type" :value="$todo->type" readonly/>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-12">
                                        <x-form.textarea name="info" label="Description of what to do" :value="$todo->info" rows="4"/>
                                    </div>
                                </div>

                                {{-- Assigned Users --}}
                                <div class="row">
                                    <div class="col-md-12">
                                        <x-form.select name="user_list[]" label="Assigned to" :options="Auth::user()->company->usersSelect('ALL', 1)" :value="($todo->assignedTo()) ? $todo->assignedTo()->pluck('id')->toArray() : null" plugin="select2" multiple style="width:100%"/>
                                    </div>
                                </div>

                                <div class="form-actions right">
                                    @if ($todo->type == 'incident')
                                        <a href="/site/incident/{{ $todo->type_id }}" class="btn default"> Back</a>
                                    @elseif ($todo->type == 'incident prevent')
                                        <a href="/site/incident/{{ $todo->type_id }}/analysis" class="btn default"> Back</a>
                                    @else
                                        <a href="/todo" class="btn default"> Back</a>
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

@section('page-level-scripts')
    {{-- Metronic + custom Page Scripts --}}
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
            $("#user_list").select2({
                placeholder: "Select",
                width: '100%',
            });
        });
    </script>
@stop

