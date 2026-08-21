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
        font-size:14px;
        font-weight:600;
        color:#333 !important;
    }

    .client-planner-email-actions {
        margin-top:15px;
        min-height:50px;
    }
</style>

@section('content')
    <div class="page-content-inner" x-data="{ attachmentsReady: {{ $attachmentsReady ? 'true' : 'false' }} }" x-on:client-planner-attachments-status.window="attachmentsReady = $event.detail.ready">
        <div class="row">
            <div class="col-md-12">
                <div class="portlet light bordered">
                    <div class="portlet-title">
                        <div class="caption">
                            <i class="icon-layers"></i>
                            <span class="caption-subject bold uppercase font-green-haze">Edit Client Planner Email</span>
                            <span class="caption-helper">ID: {{ $email->id }}</span>
                        </div>
                    </div>

                    <div class="portlet-body form">
                        <form method="POST" action="{{ action([App\Http\Controllers\Client\ClientPlannerEmailController::class, 'update'], $email->id) }}" class="horizontal-form" id="email_form">
                            @csrf
                            @method('PATCH')

                            <x-form.hidden name="email_id" :value="$email->id"/>
                            <x-form.hidden name="site_id" :value="$email->site_id"/>

                            @include('form-error')

                            @error('attachments')
                                <div class="alert alert-danger">{{ $message }}</div>
                            @enderror

                            <h4 style="margin-bottom:5px">Email Draft</h4>
                            <hr style="padding:0; margin:0 0 10px 0">

                            <div class="row">
                                <div class="col-md-12">
                                    <x-form.input name="sent_to" label="To:" :value="$email->sent_to" readonly/>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <x-form.input name="sent_cc" label="CC:" :value="$email->sent_cc" readonly/>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <x-form.input name="sent_bcc" label="Bcc:" :value="$email->sent_bcc" readonly/>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <x-form.input name="subject" label="Subject:" :value="$email->subject" readonly/>
                                </div>
                            </div>

                            <div style="margin-top:5px">
                                <livewire:client.planner.email.attachment-status :email-id="$email->id"/>
                            </div>

                            <hr class="field-hr">

                            <div class="row">
                                <div class="col-md-12">
                                    <x-form.rich-text name="email_body" :value="$email->body" :min-height="420"/>
                                </div>
                            </div>

                            <hr>

                            <div class="pull-right client-planner-email-actions">
                                <a href="/client/planner/email" class="btn default">Back</a>
                                <button id="preview" type="button" class="btn dark">Preview</button>

                                @if (Auth::user()->allowed2('edit.client.planner.email', $email))
                                    <button id="send" type="submit" name="save" class="btn green" x-bind:disabled="!attachmentsReady" x-bind:title="attachmentsReady ? '' : 'Waiting for attachments to finish generating'">Send</button>
                                @endif
                            </div>

                            <br><br>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <x-ui.bootstrap-modal id="modal_preview" title="Email Preview" max-width="900px">
            <div id="email_body_preview"></div>

            <x-slot name="footer">
                <button type="button" class="sws-modal-btn sws-modal-btn-secondary" data-dismiss="modal">Cancel</button>
                @if (Auth::user()->allowed2('edit.client.planner.email', $email))
                    <button type="button" id="send_preview" class="sws-modal-btn sws-modal-btn-primary" x-bind:disabled="!attachmentsReady">Send</button>
                @endif
            </x-slot>
        </x-ui.bootstrap-modal>
    </div>
@stop

@section('page-level-plugins-head')
@stop

@section('page-level-plugins')
    <script src="/js/tiptap-editor.js"></script>
@stop

@section('page-level-scripts')
    <script>
        $(document).ready(function () {
            $('#preview').on('click', function () {
                var html = window.SwsRichText ? window.SwsRichText.get('email_body') : $('textarea[name="email_body"]').val();

                $('#email_body_preview').html(html);
                $('#modal_preview').modal('show');
            });

            $('#send_preview').on('click', function () {
                $('#modal_preview').modal('hide');
                $('#email_form').trigger('submit');
            });

            $('#email_form').on('submit', function () {
                if (window.SwsRichText) {
                    window.SwsRichText.sync('email_body');
                }

                $('#send').prop('disabled', true).text('Sending...');
                $('#send_preview').prop('disabled', true);
            });
        });
    </script>
@stop
