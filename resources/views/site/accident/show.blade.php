@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        @if (Auth::user()->hasAnyPermissionType('site'))
            <li><a href="/site">Sites</a><i class="fa fa-circle"></i></li>
        @endif
        @if (Auth::user()->hasAnyPermissionType('site.accident'))
            <li><a href="/site/accident">Site Accidents</a><i class="fa fa-circle"></i></li>
        @endif
        <li><span>Accident Report</span></li>
    </ul>
@stop

@section('content')
    <div class="page-content-inner">
        <div class="m-heading-1 border-green m-bordered" style="margin: 0 0 20px;">
            <h3>{{ $accident->site->name }}
                <small>(Site: {{ $accident->site->code }})</small>
            </h3>
            <p>{{ $accident->site->address }}, {{ $accident->site->suburb }}</p>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="portlet light bordered">
                    <div class="portlet-title">
                        <div class="caption">
                            <i class="fa fa-file-text-o "></i>
                            <span class="caption-subject font-green-haze bold uppercase">Accident Report</span>
                            <span class="caption-helper"> ID: {{ $accident->id }}</span>
                        </div>
                    </div>
                    <div class="portlet-body form">
                        @include('form-error')

                        <div class="form-body">
                            <form method="POST" action="{{ action([\App\Http\Controllers\Site\SiteAccidentController::class, 'update'], $accident->id) }}" class="horizontal-form">
                                @csrf
                                @method('PATCH')
                                <div class="row">
                                    <div class="col-md-8">
                                        <table class="table col2-table">
                                            <tr>
                                                <th style="width:150px">Completed by:</th>
                                                <td>{{ $accident->createdBy->fullname }}</td>
                                            </tr>
                                            <tr>
                                                <th>Date:</th>
                                                <td>{{ $accident->created_at->format('d/m/y g:i a') }}</td>
                                            </tr>
                                            @if(($accident->created_by == $accident->updated_by) && ($accident->created_at != $accident->updated_at))
                                                <tr>
                                                    <th>Updated:</th>
                                                    <td>{{ $accident->updated_at->format('d/m/y g:i a') }}</td>
                                                </tr>
                                            @endif
                                        </table>
                                    </div>
                                    <div class="col-md-1"></div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="status" class="control-label">&nbsp;</label>
                                            <input type="checkbox" name="status" id="status" value="1" class="make-switch"
                                                   @if($accident->status) checked @endif
                                                   data-on-text="Open" data-on-color="success"
                                                   data-off-text="Closed" data-off-color="danger"
                                                   @if(!Auth::user()->allowed2('del.site.accident', $accident)) readonly @endif>
                                            <p class="myswitch-label" style="font-size: 14px;">&nbsp; Status </p>
                                        </div>
                                    </div>
                                </div>
                                @if(!$accident->status)
                                    <div class="row">
                                        <div class="col-md-12">
                                            <h4 class="font-red uppercase" style="margin:0 0 10px 15px;">
                                                <span>Accident Closed {{ $accident->resolved_at->format('d/m/Y') }}</span>
                                            </h4>
                                        </div>
                                    </div>
                            @endif
                        </div>

                        <h3 class="form-section" style="margin-top: 0px">Report</h3>

                        <div class="row">
                            <div class="col-md-6">
                                @if ($accident->status && Auth::user()->id == $accident->created_by && 1 == 2)
                                    <x-form.select name="site_id" label="Site" :options="Auth::user()->company->sitesSelect('prompt')" :value="$accident->site_id"/>
                                @else
                                    <x-form.hidden name="site_id" :value="$accident->site_id"/>
                                    <x-form.input name="site_name" label="Site" :value="$accident->site->name" disabled/>
                                @endif
                            </div>
                            <div class="col-md-2">
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="date" class="control-label">Date / Time of Incident</label>
                                    @if ($accident->status && Auth::user()->id == $accident->created_by && 1 == 2)
                                        <div class="input-group date form_datetime">
                                            <input type="text" name="date" id="date" class="form-control" value="{{ old('date', $accident->date->format('d F Y - H:i')) }}" readonly style="background:#FFF">
                                            <span class="input-group-btn">
                                                <button class="btn default date-set" type="button"><i class="fa fa-calendar"></i></button>
                                            </span>
                                        </div>
                                    @else
                                        <input type="text" name="date" id="date" class="form-control" value="{{ $accident->date->format('d F Y - H:i') }}" readonly>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <x-form.input name="supervisor" label="Supervisor" :value="$accident->supervisor" readonly/>
                            </div>
                        </div>


                        <h4 class="font-green-haze">Workers details</h4>
                        <!-- Name / Age / Occupation -->
                        <div class="row">
                            <div class="col-md-3">
                                <x-form.input name="name" label="Name" :value="$accident->name" readonly/>
                            </div>
                            <div class="col-md-3">
                                <x-form.input name="company" label="Company" :value="$accident->company" readonly/>
                            </div>
                            <div class="col-md-2">
                                <x-form.input name="age" label="Age" :value="$accident->age" readonly/>
                            </div>
                            <div class="col-md-4">
                                <x-form.input name="occupation" label="Occupation" :value="$accident->occupation" readonly/>
                            </div>
                        </div>

                        <h4 class="font-green-haze">Incident details</h4>
                        <!-- Location + Nature -->
                        <div class="row">
                            <div class="col-md-6">
                                <x-form.textarea name="location" label="Location of Incident (be specific)" rows="2" :value="$accident->location" readonly/>
                            </div>
                            <div class="col-md-6">
                                <x-form.textarea name="nature" label="Nature of Injury / Illness" rows="2" :value="$accident->nature" readonly/>
                            </div>
                        </div>
                        <!-- Description -->
                        <div class="row">
                            <div class="col-md-12">
                                <x-form.textarea name="info" label="Description of Incident (describe in detail)" rows="3" :value="$accident->info" readonly/>
                            </div>
                        </div>
                        <!-- Damage / Referred -->
                        <div class="row">
                            <div class="col-md-8">
                                <x-form.input name="damage" label="Damage to Equipment / Property" :value="$accident->damage" readonly/>
                            </div>
                            <div class="col-md-4">
                                @if ($accident->status && Auth::user()->id == $accident->created_by && 1 == 2)
                                    <x-form.select name="referred" label="Referred / Transferred to" :options="['' => 'Select option', 'Hospital' => 'Hospital', 'Doctors' => 'Doctors', 'Home' => 'Home', 'Continued Work' => 'Continued Work', 'Other' => 'Other']" :value="$accident->referred"/>
                                @else
                                    <x-form.input name="referred" label="Referred / Transferred to" :value="$accident->referred" readonly/>
                                @endif
                            </div>
                        </div>
                        <!-- Preventative Action -->
                        <div class="row">
                            <div class="col-md-12">
                                <x-form.textarea name="action" label="Recommended Preventative Action" rows="3" :value="$accident->action" readonly/>
                            </div>
                        </div>

                        @if(Auth::user()->allowed2('edit.site.accident', $accident))
                            <hr>

                            {{-- Notes --}}
                            <div class="row">
                                <div class="col-md-12">
                                    <livewire:misc.actions table="site_accidents" :table-id="$accident->id" :allow-add="(int) $accident->status === 1"/>
                                </div>
                            </div>

                            {{-- Assigned Tasks --}}
                            <livewire:misc.assigned-tasks context="accident" :context-id="$accident->id"/>
                            <hr>

                            {{-- Additional Information block removed/unused --}}
                            @if (Auth::user()->isCompany($accident->site->company_id))
                                <div class="row">
                                    <div class="col-md-12">
                                        <x-form.textarea name="notes" label="Notes" rows="3" :value="$accident->notes" @if(!$accident->status) readonly @endif/>
                                        <span class="help-block"> Only viewable by parent company</span>
                                    </div>
                                </div>
                            @endif

                            <div class="form-actions right">
                                <a href="/site/accident" class="btn default"> Back</a>
                                @if(Auth::user()->allowed2('edit.site.accident', $accident))
                                    @if($accident->status || Auth::user()->allowed2('del.site.accident', $accident))
                                        <button type="submit" class="btn green"> Save</button>
                                    @endif
                                @endif
                            </div>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div>
        <div class="pull-right" style="font-size: 12px; font-weight: 200; padding: 10px 10px 0 0">
            {!! $accident->displayUpdatedBy() !!}
        </div>
    </div>

@stop


@section('page-level-plugins-head')
    <link href="https://unpkg.com/filepond/dist/filepond.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/bootstrap-fileinput/bootstrap-fileinput.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/select2/css/select2.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/select2/css/select2-bootstrap.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/bootstrap-select/css/bootstrap-select.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/bootstrap-datepicker/css/bootstrap-datepicker3.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css" rel="stylesheet" type="text/css"/>
@stop

@section('page-level-plugins')
    <script src="/assets/global/plugins/select2/js/select2.full.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/bootstrap-select/js/bootstrap-select.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js" type="text/javascript"></script>
    <script src="https://unpkg.com/filepond/dist/filepond.min.js"></script>
@stop

@section('page-level-scripts')
    {{-- Metronic + custom Page Scripts --}}
    <script src="/assets/global/plugins/bootstrap-fileinput/bootstrap-fileinput.js" type="text/javascript"></script>
    <script src="/assets/pages/scripts/components-bootstrap-select.min.js" type="text/javascript"></script>
@stop

