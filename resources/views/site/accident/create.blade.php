@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        @if (Auth::user()->company->subscription)
            <li><a href="/site/accident">Site Accidents</a><i class="fa fa-circle"></i></li>
        @endif
        <li><span>Lodge Accident Report</span></li>
    </ul>
@stop

@section('content')
    <div class="page-content-inner">
        <div class="note note-warning">
            To be completed by the Primary Contractor AND Construction Supervisor immediately after:
            <ul>
                <li>A lost time injury or</li>
                <li>A incident with the potenital cause serious injury / illness occurs</li>
            </ul>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="portlet light bordered">
                    <div class="portlet-title">
                        <div class="caption">
                            <span class="caption-subject font-green-haze bold uppercase">Lodge Accident Report</span>
                        </div>
                    </div>
                    <div class="portlet-body form">
                        <form method="POST" action="{{ action([\App\Http\Controllers\Site\SiteAccidentController::class, 'store']) }}" class="horizontal-form">
                            @csrf
                            <x-form.hidden name="status" value="1"/>

                            @include('form-error')
                            <div class="form-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <x-form.select name="site_id" label="Site" plugin="select2" style="width:100%">
                                            {!! Auth::user()->authSitesSelect2Options('view.site.list', old('site_id')) !!}
                                        </x-form.select>
                                    </div>
                                    <div class="col-md-2">
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="date" class="control-label">Date / Time of Incident</label>
                                            <div class="input-group date form_datetime form_datetime bs-datetime" data-date-end-date="0d">
                                                <input type="text" id="date" name="date" value="{{ old('date') }}" class="form-control" readonly style="background:#FFF">
                                                <span class="input-group-addon">
                                                <button class="btn default date-set" type="button"><i class="fa fa-calendar"></i></button>
                                            </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <h4 class="font-green-haze">Workers details</h4>
                                <!-- Name / Age / Occupation -->
                                <div class="row">
                                    <div class="col-md-3">
                                        <x-form.input name="name" label="Name"/>
                                    </div>
                                    <div class="col-md-3">
                                        <x-form.input name="company" label="Company"/>
                                    </div>
                                    <div class="col-md-2">
                                        <x-form.input name="age" label="Age"/>
                                    </div>
                                    <div class="col-md-4">
                                        <x-form.input name="occupation" label="Occupation"/>
                                    </div>
                                </div>

                                <h4 class="font-green-haze">Incident details</h4>
                                <!-- Location + Nature -->
                                <div class="row">
                                    <div class="col-md-6">
                                        <x-form.textarea name="location" label="Location of Incident (be specific)" rows="2"/>
                                    </div>
                                    <div class="col-md-6">
                                        <x-form.textarea name="nature" label="Nature of Injury / Illness" rows="2"/>
                                    </div>
                                </div>
                                <!-- Description -->
                                <div class="row">
                                    <div class="col-md-12">
                                        <x-form.textarea name="info" label="Description of Incident (describe in detail)" rows="3"/>
                                    </div>
                                </div>
                                <!-- Damage / Referred -->
                                <div class="row">
                                    <div class="col-md-8">
                                        <x-form.input name="damage" label="Damage to Equipment / Property"/>
                                    </div>
                                    <div class="col-md-4">
                                        <x-form.select name="referred" label="Referred / Transferred to" :options="['' => 'Select option', 'Hospital' => 'Hospital', 'Doctors' => 'Doctors', 'Home' => 'Home', 'Continued Work' => 'Continued Work', 'Other' => 'Other']"/>
                                    </div>
                                </div>
                                <!-- Preventative Action -->
                                <div class="row">
                                    <div class="col-md-12">
                                        <x-form.textarea name="action" label="Recommended Preventative Action" rows="3"/>
                                    </div>
                                </div>

                                <div class="form-actions right">
                                    <a href="/site/accident" class="btn default"> Back</a>
                                    <button type="submit" class="btn green"> Save</button>
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
    <link href="/assets/global/plugins/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css" rel="stylesheet" type="text/css"/>

    <link href="/assets/global/plugins/select2/css/select2.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/select2/css/select2-bootstrap.min.css" rel="stylesheet" type="text/css"/>
@stop

@section('page-level-plugins')
    <script src="/assets/global/plugins/bootstrap-select/js/bootstrap-select.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js" type="text/javascript"></script>

    <script src="/assets/global/plugins/bootstrap-select/js/bootstrap-select.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/select2/js/select2.full.min.js" type="text/javascript"></script>
@stop

@section('page-level-scripts')
    {{-- Metronic + custom Page Scripts --}}
    <script src="/assets/global/plugins/bootstrap-fileinput/bootstrap-fileinput.js" type="text/javascript"></script>
    <script src="/assets/pages/scripts/components-bootstrap-select.min.js" type="text/javascript"></script>
    <script src="/assets/pages/scripts/components-date-time-pickers.min.js" type="text/javascript"></script>
    <script src="/assets/pages/scripts/components-select2.min.js" type="text/javascript"></script>
    <script type="text/javascript">
        $(document).ready(function () {
            /* Select2 */
            $("#site_id").select2({placeholder: "Select Site"});
        });

        // Force datepicker to not be able to select dates after today
        $('.bs-datetime').datetimepicker({
            endDate: new Date()
        });
    </script>
@stop


