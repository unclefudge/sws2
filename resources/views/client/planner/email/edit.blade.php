@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        @if (Auth::user()->hasAnyPermissionType('client.planner.email'))
            <li><a href="/client/planner/email">Client Planner Emails</a><i class="fa fa-circle"></i></li>
        @endif
        <li><span>Edit Email</span></li>
    </ul>
@stop

<style>
    a.mytable-header-link {
        font-size: 14px;
        font-weight: 600;
        color: #333 !important;
    }
</style>

@section('content')
    <div class="page-content-inner">
        <div class="row">
            <div class="col-md-12">
                <div class="portlet light bordered">
                    <div class="portlet-title">
                        <div class="caption">
                            <i class="icon-layers"></i>
                            <span class="caption-subject bold uppercase font-green-haze">Create Client Planner Email</span>
                            <span class="caption-helper">ID: {{ $email->id }}</span>
                        </div>
                    </div>
                    <div class="portlet-body form">
                        <div class="page-content-inner">
                            {!! Form::model($email, ['method' => 'PATCH', 'action' => ['Client\ClientPlannerEmailController@update', $email->id], 'class' => 'horizontal-form', 'files' => true, 'id' => 'email_form']) !!}
                            <input v-model="xx.email_id" type="hidden" name="email_id" id="email_id" value="{{ $email->id }}">
                            <input v-model="xx.site_id" type="hidden" name="site_id" id="site_id" value="{{ $email->site_id }}">
                            <input v-model="xx.email_body" type="hidden" name="email_body" id='email_body' value="{{ $email->body }}">

                            @include('form-error')

                            <h4>Email Draft</h4>
                            <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                            {{-- To --}}
                            <div class="row">
                                <div class="form-group {!! fieldHasError('sent_to', $errors) !!}">
                                    {!! Form::label('sent_to', 'To:', ['class' => 'col-md-1 control-label']) !!}
                                    <div class="col-md-11">
                                        {!! Form::text('sent_to', null, ['class' => 'form-control', 'readonly']) !!}
                                        {!! fieldErrorMessage('sent_to', $errors) !!}
                                    </div>
                                </div>
                            </div>
                            <br>
                            {{-- Subject --}}
                            <div class="row">
                                <div class="form-group">
                                    {!! Form::label('subject', 'Subject:', ['class' => 'col-md-1 control-label']) !!}
                                    <div class="col-md-11">
                                        {!! Form::text('subject', null, ['class' => 'form-control', 'readonly']) !!}
                                    </div>
                                </div>
                            </div>
                            <br>
                            {{-- Attachments --}}
                            <div v-if="xx.attachments">
                                <div class="row">
                                    <div class="col-md-1">Attachments:</div>
                                    <div class="col-md-11">
                                        <span v-for="doc in xx.attachments">
                                        <span v-if="doc.status == 1"><i class="fa fa-file-pdf-o"></i> <a href="@{{ doc.url }}" target="_blank" title="@{{ doc.name }}">@{{ doc.name }}</a>,</span>
                                        <span v-if="doc.status == 2"><span class="font-red"><i class="fa fa-spin fa-spinner"> </i> @{{ doc.name }}</span>,</span>
                                        <span v-if="doc.status == 0"><span class="font-red"><i class="fa fa-triangle-exclamation"></i> @{{ doc.name }}</span>,</span>
                                    </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <hr class="field-hr">
                        {{-- Body --}}
                        <div class="row">
                            <div class="col-md-12">
                                <div><textarea name="ck_body" id="ck_body" rows="25" cols="80">{{ nl2br($email->body) }}</textarea></div>
                            </div>
                        </div>

                        <hr>
                        <div class="pull-right" style="min-height: 50px">
                            <a href="/client/planner/email" class="btn default"> Back</a>
                            <button id="preview" name="preview" class="btn dark"> Preview</button>
                            @if(Auth::user()->allowed2('edit.client.planner.email', $email))
                                <button id="signoff_button" type="submit" name="save" class="btn green"> Send</button>
                            @endif
                        </div>
                        <br><br>
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
        </div>

        <!--<pre v-if="xx.dev">@{{ $data | json }}</pre>-->
    </div>

    <!-- loading Spinner -->
    <div v-show="xx.loading" style="background-color: #FFF; padding: 20px;">
        <div class="loadSpinnerOverlay">
            <div class="loadSpinner"><i class="fa fa-spinner fa-pulse fa-2x fa-fw margin-bottom"></i> Loading...</div>
        </div>
    </div>

    {{-- Preview Modal --}}
    <div id="modal_preview" class="modal fade bs-modal-lg" tabindex="-1" role="basic" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content form">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                    <h4 class="modal-title text-center"><b>Email Preview</b></h4>
                </div>
                <div class="modal-body">
                    <div class="form-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div id="email_body_preview"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn green" data-dismiss="modal" id="send_preview">Send</button>
                </div>
            </div>
        </div>
    </div>
@stop


@section('page-level-plugins-head')
    <link href="/css/libs/fileinput.min.css" media="all" rel="stylesheet" type="text/css"/>
    <script src="https://cdn.ckeditor.com/4.7.0/standard-all/ckeditor.js"></script>
@stop

@section('page-level-plugins')
    <script src="/js/libs/fileinput.min.js"></script>
@stop

@section('page-level-scripts') {{-- Metronic + custom Page Scripts --}}
<script src="/js/libs/vue.1.0.24.js" type="text/javascript"></script>
<script src="/js/libs/vue-resource.0.7.0.js" type="text/javascript"></script>
<script src="/js/vue-app-basic-functions.js" type="text/javascript"></script>
<script>
    $.ajaxSetup({
        headers: {'X-CSRF-Token': $('meta[name=token]').attr('value')}
    });

    CKEDITOR.replace('ck_body', {
        customConfig: '/js/libs/ckeditor/customConfig.js',
    });

    // Preview
    $('#preview').click(function (e) {
        e.preventDefault(e);
        $('#email_body_preview').html(CKEDITOR.instances.ck_body.getData());
        $('#modal_preview').modal('show');
    });

    // Send Email
    $('#send_preview').click(function (e) {
        e.preventDefault(e);
        //alert('sending');
        submit_form();
    });


    $('#email_form').on('submit', function (e) {
        e.preventDefault(e);
        //alert('subbing');
        submit_form();
    });

    function submit_form() {
        $('#email_body').val(CKEDITOR.instances.ck_body.getData());

        $.ajax({
            type: "POST",
            url: '/client/planner/email/' + $('#email_id').val(),
            data: $("#email_form").serialize(),
            dataType: 'json',
            success: function (data) {
                window.location = "/client/planner/email";
                //alert('email sent');
            },
            error: function (data) {
                swal({
                    title: 'Failed to save Email Draft',
                    text: "We apologise but we were unable to save/send your Email draft (id:" + $('#email_id').val() + ")<br><br>Please try again but if the problem persists let us know.",
                    html: true
                });
            }
        })
    }

    var dev = true;
    if (window.location.hostname == 'safeworksite.com.au')
        dev = false;

    var xx = {
        dev: dev, loading: true, incomplete: true, email_id: '', site_id: '', attachments: '',
    };

    new Vue({
        el: 'body',
        data: function () {
            //items: []
            return {xx: xx};
        },
        methods: {
            loadData: function () {
                $.get('/client/planner/email/' + this.xx.email_id + '/check_docs', function (response) {
                    console.log(response);
                    this.xx.attachments = response;
                    this.reportsCompleted();
                }.bind(this));
            },
            reportsCompleted: function () {
                this.xx.incomplete = false;
                for (var i = 0; i < this.xx.attachments.length; i++) {
                    if (this.xx.attachments[i]['status'] != '1')
                        this.xx.incomplete = true;
                }
                this.xx.loading = this.xx.incomplete;
            },
        },
        ready: function () {
            this.loadData();

            setInterval(function () {
                this.loadData();
            }.bind(this), 3000);
        }
    });

</script>
@stop

