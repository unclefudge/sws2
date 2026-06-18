@extends('layout-basic')

@section('pagetitle')
    <div class="page-title">
        <h1><i class="fa fa-sign-in"></i> Site Checkin</h1>
    </div>
    <div class="pull-right" style="padding: 20px;"><a href="/logout">logout</a></div>
@stop

@section('content')
    <div class="page-content-inner">
        <div class="m-heading-1 border-green m-bordered" style="margin: 0 0 20px;">
            <h3>{{ $worksite->name }}
                <small>(Site: {{ $worksite->code }})</small>
            </h3>
            <p>{{ $worksite->address }}, {{ $worksite->suburb }}</p>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="portlet light bordered">
                    <div class="portlet-title">
                        <div class="caption">
                            <i class="fa fa-sign-in "></i>
                            <span class="caption-subject font-green-haze bold uppercase">Site Checkin</span><br>
                            <span class="caption-helper">You must check into all sites you attend.</span>
                        </div>
                        <div class="actions">
                            <a href="" class="btn btn-circle btn-icon-only btn-default collapse"> </a>
                            <a href="javascript:;" class="btn btn-circle btn-icon-only btn-default fullscreen"> </a>
                        </div>
                    </div>
                    <div class="portlet-body form">
                        <form method="POST" action="{{ action([App\Http\Controllers\Site\SiteController::class, 'processCheckin'], $worksite->slug) }}" enctype="multipart/form-data">
                            @csrf
                            <x-form.hidden name="reason" value="Special Trade Checkin"/>
                            <x-form.hidden name="action" value="Special Trade Checkin"/>
                            <x-form.hidden name="super_name" :value="$worksite->supervisorsContactSBC()"/>

                            @include('form-error')

                            <p>Please answer the following questions.</p>
                            <div class="form-body">
                                <div class="row">
                                    <div class="col-sm-2 col-xs-4 text-center">
                                        <div class="form-group">
                                            <input type="checkbox" name="safe_site" value="1" class="make-switch" data-size="small" data-on-text="Yes" data-on-color="success" data-off-text="No" data-off-color="danger" id="safe_site">
                                        </div>
                                    </div>
                                    <div class="col-sm-10 col-xs-8">
                                        The work site is safe for me to complete the duties assigned to me
                                    </div>
                                </div>
                                <div class="form-actions">
                                    <button type="submit" class="btn green" name="checkinTrade" value="true">Submit</button>
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
@stop

@section('page-level-plugins')
    <script src="/assets/global/plugins/bootstrap-select/js/bootstrap-select.min.js" type="text/javascript"></script>
@stop

@section('page-level-scripts')
    {{-- Metronic + custom Page Scripts --}}
    <script src="/assets/global/plugins/bootstrap-fileinput/bootstrap-fileinput.js" type="text/javascript"></script>
    <script src="/assets/pages/scripts/components-bootstrap-select.min.js" type="text/javascript"></script>
    <script>
        //$('#status').val();
        swal({
            title: "Unable to Enter Site",
            text: "Please contact <b>" + $('#super_name').val() + "</b> to discuss and resolve the worksite issue.<br><br><span class='font-red'>You have <b>NOT</b> been signed in and therefore are required to stay off the site until issue is resolved and sign in is achieved</span>",
            showCancelButton: false,
            confirmButtonColor: "#3598dc",
            confirmButtonText: "Ok",
            allowOutsideClick: false,
            html: true,
        }, function () {
            window.location = "/auth/logout";
            /*
            $.ajax({
                url: '/auth/logout',
                type: 'GET',
                data: '',
                success: function (data) {
                    // success
                },
            });*/
        });
    </script>
@stop

