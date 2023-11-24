@inject('CompanyDocCategory', 'App\Models\Company\CompanyDocCategory')
@extends('layout')
@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        <li><a href="/company/doc/standard/review">Standard Details Review</a><i class="fa fa-circle"></i></li>
        <li><span>Edit</span></li>
    </ul>
@stop

@section('content')
    <div class="page-content-inner">
        <div class="row">
            <div class="col-md-12">
                <div class="portlet light bordered">
                    <div class="portlet-title">
                        <div class="caption">
                            <span class="caption-subject font-dark bold uppercase"> Edit Standard Details</span>
                            <span class="caption-helper"> ID: {{ $doc->id }}</span>
                        </div>
                    </div>
                    <div class="portlet-body form">
                        {!! Form::model($doc, ['method' => 'PATCH', 'action' => ['Company\CompanyDocReviewController@update',$doc->id], 'class' => 'horizontal-form', 'files' => true]) !!}
                        <input v-model="xx.table_id" type="hidden" id="table_id" value="{{ $doc->id }}">
                        <input v-model="xx.record_status" type="hidden" id="record_status" value="{{ $doc->status }}">
                        <input v-model="xx.stage" type="hidden" id="stage" value="{{ $doc->stage }}">
                        @include('form-error')

                        <div class="form-body">
                            <div class="row">
                                <div class="col-md-10">
                                    <h4 style="margin-bottom: 0px">{{ $doc->name }}</h4>
                                </div>
                                <div class="col-md-2">
                                    @if(!$doc->status)
                                        <h3 class="font-red uppercase pull-right" style="margin:0 0 10px;">Completed</h3>
                                    @endif
                                </div>
                            </div>
                            <hr class="field-hr">

                            <div class="row">
                                <div class="col-md-7">
                                    {{-- Stage --}}
                                    {{--}}<h4 class="font-green-haze">Status</h4>
                                    <hr class="field-hr">--}}
                                    <div class="row" style="line-height: 2">
                                        <div class="col-md-3"><b>Stage:</b></div>
                                        <div class="col-md-9">{{ $doc->stage_text }}</div>
                                        <div class="col-md-3"><b>Assigned To:</b></div>
                                        <div class="col-md-9">{{ $doc->assignedToSBC() }}</div>
                                        @if ($doc->approved_con)
                                            <div class="col-md-12">Approved by Construction Manager ({{ $doc->approved_con->format('d/m/Y') }})</div>
                                        @endif
                                    </div>
                                    <br>

                                    {{-- Review Process --}}
                                    <h4 class="font-green-haze">Review Process</h4>
                                    <hr class="field-hr">
                                    <div class="row">
                                        <div class="col-md-3"><b>Current version:</b></div>
                                        <div class="col-md-9">
                                            @if (!$doc->current_doc)
                                                <a href="{{  $doc->original_doc_url }}" target="_blank"> Original Standard Details </a>
                                            @else
                                                <a href="{{  $doc->current_doc_url }}" target="_blank"> {{ $doc->current_doc }} </a>
                                            @endif
                                        </div>
                                    </div>
                                    <br>
                                    @if ($doc->status)
                                        {{-- Assign Draftsperson --}}
                                        @if ($doc->stage == '3')
                                            <div class="row">
                                                <div class="form-group {!! fieldHasError('assign_user', $errors) !!}">
                                                    {!! Form::label('assign_user', "Assign to user:", ['class' => 'col-md-3 control-label']) !!}
                                                    <div class="col-md-9">
                                                        {!! Form::select('assign_user', Auth::user()->company->staffSelect('prompt'), null, ['class' => 'form-control select2', 'id' => 'assign_user']) !!}
                                                        {!! fieldErrorMessage('assign_user', $errors) !!}
                                                    </div>
                                                </div>
                                            </div>
                                            <br>
                                            <div class="row">
                                                <div class="form-group {!! fieldHasError('due_at', $errors) !!}">
                                                    {!! Form::label('due_at', "Task due date:", ['class' => 'col-md-3 control-label']) !!}
                                                    <div class="col-md-9">
                                                        <div class="input-group" style="width=80%">
                                                            <datepicker :value.sync="xx.due_at" format="dd/MM/yyyy" :placeholder="choose date"></datepicker>
                                                        </div>
                                                        <input v-model="xx.due_at" type="hidden" name="due_at" id="due_at" value="">
                                                        {!! fieldErrorMessage('due_at', $errors) !!}
                                                    </div>
                                                </div>
                                            </div>
                                        @elseif ($doc->stage == '3')
                                            {{-- Draftsperson to review --}}
                                            <div class="row note note-warning">
                                                <div class="col-md-12">Please review the <a href="{{ $doc->current_doc_url }}" target="_blank">current version</a> and make the requested changes.</div>
                                            </div>
                                        @elseif ($doc->stage == '9')
                                            {{-- Completed - set renew date --}}
                                            <div class="row">
                                                <div class="form-group {!! fieldHasError('next_review_date', $errors) !!}">
                                                    {!! Form::label('next_review_date', "Next review date:", ['class' => 'col-md-3 control-label']) !!}
                                                    <div class="col-md-9">
                                                        <div class="input-group" style="width=80%">
                                                            <datepicker :value.sync="xx.next_review_date" format="dd/MM/yyyy" :placeholder="choose date"></datepicker>
                                                        </div>
                                                        <input v-model="xx.next_review_date" type="hidden" name="next_review_date" id="next_review_date" value="">
                                                        {!! fieldErrorMessage('next_review_date', $errors) !!}
                                                    </div>
                                                </div>
                                            </div>
                                        @else
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group {!! fieldHasError('approve_version', $errors) !!}">
                                                        {!! Form::label('approve_version', 'Do you approve the current version', ['class' => 'control-label']) !!}
                                                        {!! Form::select('approve_version', ['' => 'Select option', '0' => 'No', '1' => 'Yes'], null, ['class' => 'form-control bs-select', 'id' => 'approve_version']) !!}
                                                        {!! fieldErrorMessage('approve_version', $errors) !!}
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    @endif
                                </div>
                                <div class="col-md-5">
                                    <h4 class="font-green-haze">Files</h4>
                                    <hr class="field-hr">
                                    1. &nbsp; <i class="fa fa-file-text-o"></i> &nbsp; <a href="{{ $doc->original_doc_url }}" target="_blank"> Original Standard Details</a><br>
                                    @if (count($doc->files))
                                        <?php $counter = 2; ?>
                                        @foreach($doc->files as $file)
                                            {{ $counter++ }}. &nbsp; <i class="fa fa-file-text-o"></i> &nbsp; <a href="{{ $file->attachment_url }}" target="_blank"> {{ $file->attachment }}</a> <i>({{ $file->updatedBy->initials }})</i><br>
                                        @endforeach
                                    @endif
                                </div>
                            </div>

                            <div id="file-upload">
                                {{-- SingleFile Upload --}}
                                <div class="row">
                                    <div class="col-md-5">
                                        <div class="form-group {!! fieldHasError('singlefile', $errors) !!}">
                                            <label class="control-label">Uploaded a document with the required changes</label>
                                            <input id="singlefile" name="singlefile" type="file" class="file-loading">
                                            {!! fieldErrorMessage('singlefile', $errors) !!}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Notes --}}
                            <div class="row">
                                <div class="col-md-12">
                                    <app-actions :table_id="{{ $doc->id }}"></app-actions>
                                </div>
                            </div>

                            <div class="form-actions right">
                                <a href="/company/doc/standard/review" class="btn default"> Back</a>
                                @if ($doc->status)
                                    <button id="approve_button" type="submit" name="approve" class="btn green" value="1"> Approve</button>
                                    <button id="save_button" type="submit" name="save" class="btn green" value="1"> Save</button>
                                    <button id="renew_button" type="submit" name="renew" class="btn green" value="1"> Save</button>
                                @endif
                            </div>
                        </div>
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
        </div>

        <div>
            <div class="pull-right" style="font-size: 12px; font-weight: 200; padding: 10px 10px 0 0">
                {!! $doc->displayUpdatedBy() !!}
            </div>
        </div>
        <!-- END PAGE CONTENT INNER -->
    </div>

    <template id="actions-template">
        <action-modal></action-modal>
        <input v-model="xx.table_id" type="hidden" id="table_id" value="{{ $doc->id }}">
        <input v-model="xx.created_by" type="hidden" id="created_by" value="{{ Auth::user()->id }}">
        <input v-model="xx.created_by_fullname" type="hidden" id="fullname" value="{{ Auth::user()->fullname }}">

        <div class="page-content-inner">
            <div class="row">
                <div class="col-md-12">
                    <h3 class="font-green-haze">Notes
                        <button v-show="xx.record_status == '1'" v-on:click.stop.prevent="$root.$broadcast('add-action-modal')" class="btn btn-circle green btn-outline btn-sm pull-right" data-original-title="Add">Add</button>
                    </h3>
                    <table v-show="actionList.length" class="table table-striped table-bordered table-nohover order-column">
                        <thead>
                        <tr class="mytable-header">
                            <th width="10%">Date</th>
                            <th> Action</th>
                            <th width="20%"> Name</th>
                            {{--}}<th width="5%"></th>--}}
                        </tr>
                        </thead>
                        <tbody>
                        <template v-for="action in actionList">
                            <tr>
                                <td>@{{ action.niceDate }}</td>
                                <td>@{{ action.action }}</td>
                                <td>@{{ action.fullname }}</td>
                                {{--}}
                                <td>
                                    <!--<button v-show="xx.record_status != 0" class=" btn blue btn-xs btn-outline sbold uppercase margin-bottom">
                                        <i class="fa fa-plus"></i> <span class="hidden-xs hidden-sm>"> Assign Task</span>
                                    </button>-->
                                    <!--
                                    <button v-show="action.created_by == xx.created_by" v-on:click.prevent="$root.$broadcast('edit-action-modal', action)"
                                            class=" btn blue btn-xs btn-outline sbold uppercase margin-bottom">
                                        <i class="fa fa-pencil"></i> <span class="hidden-xs hidden-sm>">Edit</span>
                                    </button>
                                    -->
                                </td>--}}
                            </tr>
                        </template>
                        </tbody>
                    </table>

                    <!--<pre v-if="xx.dev">@{{ $data | json }}</pre>
                    -->

                </div>
            </div>
        </div>
    </template>

    @include('misc/actions-modal')
@stop

@section('page-level-plugins-head')
    <link href="/assets/global/plugins/select2/css/select2.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/select2/css/select2-bootstrap.min.css" rel="stylesheet" type="text/css"/>
    {{--}}<link href="/assets/global/plugins/bootstrap-datepicker/css/bootstrap-datepicker.min.css" rel="stylesheet" type="text/css"/>--}}
    <link href="/css/libs/fileinput.min.css" media="all" rel="stylesheet" type="text/css"/>
@stop

@section('page-level-plugins')
    <script src="/assets/global/plugins/select2/js/select2.full.min.js" type="text/javascript"></script>
    {{--}}<script src="/assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js" type="text/javascript"></script>--}}
    <script src="/js/libs/fileinput.min.js"></script>
@stop

@section('page-level-scripts') {{-- Metronic + custom Page Scripts --}}
<script src="/js/libs/moment.min.js" type="text/javascript"></script>
<script src="/js/libs/vue.1.0.24.js " type="text/javascript"></script>
<script src="/js/libs/vue-strap.min.js"></script>
<script src="/js/libs/vue-resource.0.7.0.js " type="text/javascript"></script>
<script src="/js/vue-modal-component.js"></script>
<script src="/js/vue-app-basic-functions.js"></script>
<script>
    $(document).ready(function () {
        /* Select2 */
        $("#assign_user").select2({placeholder: "Select user", width: '100%'});

        function display_fields() {
            $('#file-upload').hide();
            $('#approve_button').hide();
            $('#save_button').hide();
            $('#renew_button').hide();

            // Approved
            if ($("#approve_version").val() == '1') {
                $('#file-upload').hide();
                $('#approve_button').show();
            }

            // Not Approved
            if ($("#approve_version").val() == '0') {
                $('#file-upload').show();

                if ($("#single_file").val() != '')
                    $('#save_button').show();
            }

            // Assign user
            if ($("#stage").val() == '2' && $("#assign_user").val() != '') {
                $('#file-upload').hide();
                $('#save_button').show();
            }

            // Changes + new file requested
            if ($("#stage").val() == '3') {
                $('#file-upload').show();

                if ($("#single_file").val() != '')
                    $('#save_button').show();
            }

            // Review Complete - Save Renew Date
            if ($("#stage").val() == '9') { //} && $("#next_review_date").val() != '') {
                $('#renew_button').show();
            }
        }


        display_fields();

        $("#approve_version").change(function () {
            display_fields();
        });

        $("#assign_user").change(function () {
            display_fields();
        });


        /* Bootstrap Fileinput */
        $("#singlefile").fileinput({
            showUpload: false,
            allowedFileExtensions: ["pdf", "jpg", "jpeg", "png", "gif"],
            browseClass: "btn blue",
            browseLabel: "Browse",
            browseIcon: "<i class=\"fa fa-folder-open\"></i> ",
            //removeClass: "btn btn-danger",
            removeLabel: "",
            removeIcon: "<i class=\"fa fa-trash\"></i> ",
            uploadClass: "btn btn-info",
        });
    });

</script>
<script>
    Vue.http.headers.common['X-CSRF-TOKEN'] = document.querySelector('#token').getAttribute('value');

    var host = window.location.hostname;
    var dev = true;
    if (host == 'safeworksite.com.au')
        dev = false;

    var xx = {
        dev: dev,
        action: '', loaded: false,
        table_name: 'company_docs_review', table_id: '', record_status: '', stage: '', next_review_date: '', due_at: '',
        created_by: '', created_by_fullname: '',
    };

    Vue.component('app-actions', {
        template: '#actions-template',
        props: ['table', 'table_id', 'status'],

        created: function () {
            this.getActions();
        },
        data: function () {
            return {xx: xx, actionList: []};
        },
        events: {
            'addActionEvent': function (action) {
                this.actionList.unshift(action);
            },
        },
        methods: {
            getActions: function () {
                $.getJSON('/action/' + this.xx.table_name + '/' + this.table_id, function (actions) {
                    this.actionList = actions;
                }.bind(this));
            },
        },
    });

    Vue.component('ActionModal', {
        template: '#actionModal-template',
        props: ['show'],
        data: function () {
            var action = {};
            return {xx: xx, action: action, oAction: ''};
        },
        events: {
            'add-action-modal': function (e) {
                var newaction = {};
                this.oAction = '';
                this.action = newaction;
                this.xx.action = 'add';
                this.show = true;
            },
            'edit-action-modal': function (action) {
                this.oAction = action.action;
                this.action = action;
                this.xx.action = 'edit';
                this.show = true;
            }
        },
        methods: {
            close: function () {
                this.show = false;
                this.action.action = this.oAction;
            },
            addAction: function (action) {
                var actiondata = {
                    action: action.action,
                    table: this.xx.table_name,
                    table_id: this.xx.table_id,
                    niceDate: moment().format('DD/MM/YY'),
                    created_by: this.xx.created_by,
                    fullname: this.xx.created_by_fullname,
                };
                //alert('add action');

                this.$http.post('/action', actiondata)
                        .then(function (response) {
                            toastr.success('Created new action ');
                            actiondata.id = response.data.id;
                            this.$dispatch('addActionEvent', actiondata);
                        }.bind(this))
                        .catch(function (response) {
                            alert('failed adding new action');
                        });

                this.close();
            },
            updateAction: function (action) {
                this.$http.patch('/action/' + action.id, action)
                        .then(function (response) {
                            toastr.success('Saved Action');
                        }.bind(this))
                        .catch(function (response) {
                            alert('failed to save action [' + action.id + ']');
                        });
                this.show = false;
            },
        }
    });

    var myApp = new Vue({
        el: 'body',
        data: {xx: xx},
        components: {
            datepicker: VueStrap.datepicker,
        },
    });

</script>
@stop