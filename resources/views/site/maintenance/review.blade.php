@inject('maintenanceWarranty', 'App\Http\Utilities\MaintenanceWarranty')
@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        @if (Auth::user()->hasAnyPermissionType('site'))
            <li><a href="/site">Sites</a><i class="fa fa-circle"></i></li>
        @endif
        <li><a href="/site/maintenance">Maintenance</a><i class="fa fa-circle"></i></li>
        <li><span>View Request</span></li>
    </ul>
@stop

<style>
    a.mytable-header-link {
        font-size: 14px;
        font-weight: 600;
        color: #333 !important;
    }

    .file-preview {
        height: 250px !important;
    }

    ..file-drop-zone {
        height: 250px !important;
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
                            <span class="caption-subject bold uppercase font-green-haze"> Site Maintenance Request</span>
                            <span class="caption-helper">ID: {{ $main->code }}</span>
                        </div>
                    </div>
                    <div class="portlet-body form">
                        <div class="page-content-inner">
                            <form method="POST" action="{{ action([App\Http\Controllers\Site\SiteMaintenanceController::class, 'review'], $main->id) }}" class="horizontal-form" enctype="multipart/form-data">
                                @csrf
                                <x-form.hidden name="main_id" id="main_id" :value="$main->id"/>
                                <x-form.hidden name="site_id" id="site_id" :value="$main->site_id"/>
                                @include('form-error')

                                {{-- Progress Steps --}}
                                <div class="mt-element-step hidden-sm hidden-xs">
                                    <div class="row step-thin" id="steps">
                                        <div class="col-md-6 mt-step-col first done">
                                            <div class="mt-step-number bg-white font-grey">1</div>
                                            <div class="mt-step-title uppercase font-grey-cascade">Create</div>
                                            <div class="mt-step-content font-grey-cascade">Create request</div>
                                        </div>
                                        <div class="col-md-6 mt-step-col last active">
                                            <div class="mt-step-number bg-white font-grey">2</div>
                                            <div class="mt-step-title uppercase font-grey-cascade">Assign</div>
                                            <div class="mt-step-content font-grey-cascade">Assign supervisor</div>
                                        </div>

                                    </div>
                                </div>
                                <hr>
                                <div class="row">
                                    <div class="col-md-5">
                                        <h4>Site Details
                                            @if(Auth::user()->allowed2('add.site.maintenance') || Auth::user()->allowed2('edit.site.maintenance', $main))
                                                <button class="btn dark btn-outline btn-sm pull-right" style="margin-top: -10px; border: 0px" id="edit-site">Edit</button>
                                            @endif
                                        </h4>
                                        <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                                        @if ($main->site)
                                            <b>{{ $main->site->name }} (#{{ $main->site->code }})</b>
                                        @endif<br>
                                        @if ($main->site)
                                            {{ $main->site->full_address }}<br>
                                        @endif
                                        {{--@if ($main->site && $main->site->client_phone) {{ $main->site->client_phone }} ({{ $main->site->client_phone_desc }})  @endif --}}
                                        <br>
                                        <div id="site-show">
                                            @if ($main->reported)
                                                <b>Reported:</b> {{ $main->reported->format('d/m/Y') }}<br>
                                            @endif
                                            @if ($main->completed)
                                                <b>Prac Completion:</b> {{ $main->completed->format('d/m/Y') }}<br>
                                            @endif
                                            @if ($main->supervisor)
                                                <b>Supervisor:</b> {{ $main->supervisor }}
                                            @endif
                                        </div>
                                        <div id="site-edit">
                                            <x-form.input name="reported" label="Reported" :value="($main->reported) ? $main->reported->format('d/m/Y') : null" placeholder="dd/mm/yyyy"/>
                                            <x-form.input name="completed" label="Prac Completed" :value="($main->completed) ? $main->completed->format('d/m/Y') : null" placeholder="dd/mm/yyyy"/>
                                            <x-form.input name="supervisor" label="Supervisor" :value="$main->supervisor"/>
                                        </div>
                                    </div>
                                    <div class="col-md-1"></div>

                                    {{-- Client Contact --}}
                                    <div class="col-md-6">
                                        <h4>Client Details
                                            @if(Auth::user()->allowed2('add.site.maintenance') || Auth::user()->allowed2('edit.site.maintenance', $main))
                                                <button class="btn dark btn-outline btn-sm pull-right" style="margin-top: -10px; border: 0px" id="edit-client">Edit</button>
                                            @endif
                                        </h4>
                                        <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                                        <div id="client-show">
                                            @if ($main->contact_name)
                                                <b>{{ $main->contact_name }}</b>
                                            @endif<br>
                                            @if ($main->contact_phone)
                                                {{ $main->contact_phone }}<br>
                                            @endif
                                            @if ($main->contact_email)
                                                {{ $main->contact_email }}<br>
                                            @endif
                                            @if($main->nextClientVisit())
                                                <br><b>Scheduled Visit:</b> {{ $main->nextClientVisit()->company->name }} &nbsp; ({{ $main->nextClientVisit()->from->format('d/m/Y') }})<br>
                                            @endif
                                        </div>
                                        <div id="client-edit">
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <x-form.input name="contact_name" label="Name" :value="$main->contact_name"/>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <x-form.input name="contact_phone" label="Phone" :value="$main->contact_phone"/>
                                                </div>
                                                <div class="col-md-8">
                                                    <x-form.input name="contact_email" label="Email" :value="$main->contact_email"/>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Gallery --}}
                                <br>
                                <div class="row" id="photos-show">
                                    <div class="col-md-7">
                                        <h4>Photos
                                            @if(Auth::user()->allowed2('add.site.maintenance') || Auth::user()->allowed2('edit.site.maintenance', $main))
                                                <button class="btn dark btn-outline btn-sm pull-right" style="margin-top: -10px; border: 0px" id="edit-photos">Edit</button>
                                            @endif</h4>
                                        <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                                        @include('site/maintenance/_gallery')
                                    </div>
                                    <div class="col-md-1"></div>
                                    <div class="col-md-4" id="docs-show">
                                        <h4>Documents
                                            @if(Auth::user()->allowed2('add.site.maintenance') || Auth::user()->allowed2('edit.site.maintenance', $main))
                                                <button class="btn dark btn-outline btn-sm pull-right" style="margin-top: -10px; border: 0px" id="edit-docs">Edit</button>
                                            @endif
                                        </h4>
                                        <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                                        @include('site/maintenance/_docs')
                                    </div>
                                </div>

                                <div id="photos-edit">
                                    <h4>Photos / Documents
                                        @if(Auth::user()->allowed2('add.site.maintenance') || Auth::user()->allowed2('edit.site.maintenance', $main))
                                            <button class="btn dark btn-outline btn-sm pull-right" style="margin-top: -10px; border: 0px" id="view-photos">View</button>
                                        @endif</h4>
                                    <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                                    <div class="row">
                                        <div class="col-md-6" style="background: #f1f0ef">
                                            <x-form.filepond/>
                                            <br><br>
                                        </div>
                                    </div>
                                    <br>
                                </div>

                                {{-- Maintenance details --}}
                                <h4>Maintenance Details</h4>
                                <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                                <div class="row">
                                    {{-- Category --}}
                                    <div class="col-md-3 ">
                                        @if ($main->status && Auth::user()->allowed2('edit.site.maintenance', $main))
                                            <x-form.select name="category_id" id="category_id" label="Category" :options="['' => 'Select category'] + \App\Models\Site\SiteMaintenanceCategory::all()->sortBy('name')->pluck('name', 'id')->toArray()" :value="$main->category_id" plugin="select2"
                                                           title="Select category"/>
                                        @else
                                            <x-form.input name="category_text" label="Category" :value="$main->category->name" readonly/>
                                        @endif
                                    </div>

                                    {{-- Warranty --}}
                                    <div class="col-md-2 ">
                                        @if ($main->status && Auth::user()->allowed2('edit.site.maintenance', $main))
                                            <x-form.select name="warranty" id="warranty" label="Warranty" :options="$maintenanceWarranty::all()" :value="$main->warranty"/>
                                        @else
                                            <x-form.input name="warranty_text" label="Warranty" :value="$maintenanceWarranty::name($main->warranty)" readonly/>
                                        @endif
                                    </div>
                                </div>

                                @if(!$main->super_id)
                                    {{-- Under Review - asign to super --}}
                                    <div class="note note-warning">
                                        <h4>Assign Request to Maintenance Supervisor</h4>
                                        <hr style="padding: 0px; margin: 0px 0px 10px 0px; border-color: #000000">
                                        <x-form.hidden name="visited" value="0"/>

                                        @if(Auth::user()->allowed2('sig.site.maintenance', $main))
                                            <div class="row">
                                                <div class="col-md-5">
                                                    {{-- Supervisor --}}
                                                    <div class="form-group {{ $errors->has('super_id') ? 'has-error' : '' }}" style="{{ $errors->has('company_id') ? '' : 'display:show' }}" id="company-div">
                                                        <label for="super_id" class="control-label">Assign to</label>
                                                        <select id="super_id" name="super_id" class="form-control select2" style="width:100%">
                                                            <option value=""></option>
                                                            <optgroup label="Cape Code Supervisors"></optgroup>
                                                            @foreach (Auth::user()->company->supervisors()->sortBy('name') as $super)
                                                                <option value="{{ $super->id }}">{{ $super->name }}</option>
                                                            @endforeach
                                                            {{--}}<optgroup label="External Users"></optgroup>
                                                            <option value="2023" {{ ('75' == $main->super_id) ? 'selected' : '' }}>Jason Habib (Prolific Projects)</option>--}}
                                                            <optgroup label="Not in Warranty"></optgroup>
                                                            <option value="declined">Decline request (not in warranty)</option>
                                                        </select>
                                                        <x-form.error name="super_id"/>
                                                    </div>
                                                </div>

                                                {{-- Planner Date --}}
                                                {{--}}
                                                <div class="col-md-3 ">
                                                    <div class="form-group {{ $errors->has('visit_date') ? 'has-error' : '' }}">
                                                        <label for="visit_date" class="control-label">Visit Date</label>
                                                        <div class="input-group input-medium date date-picker" data-date-format="dd/mm/yyyy" data-date-start-date="+0d" data-date-reset>
                                                            <input type="text" class="form-control" value="{!! nextWorkDate(\Carbon\Carbon::today(), '+', 3)->format('d/m/Y') !!}" readonly style="background:#FFF" id="visit_date" name="visit_date">
                                                <span class="input-group-btn">
                                                    <button class="btn default" type="button">
                                                        <i class="fa fa-calendar"></i>
                                                    </button>
                                                </span>
                                                        </div>
                                                    </div>
                                                </div> --}}
                                            </div>
                                        @else
                                            <div class="row">
                                                <div class="col-md-7">
                                                    Waiting to be assigned by authorised supervisor.
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                            @else
                                {{-- Under Review - client appointment set --}}
                                {{--}}
                                    <input type="hidden" name="company_id" value="{{ $main->nextClientVisit()->company->id }}">
                                    <input type="hidden" name="visit_date" value="{{ $main->nextClientVisit()->from->format('d/m/Y') }}">
                                    <input type="hidden" name="visited" value="1"> --}}
                            @endif
                        </div>


                        {{-- Items --}}
                        <h4>Maintenance Item</h4>
                        <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                        @foreach ($main->items as $item)
                            <div class="row">
                                <div class="col-xs-1 ">Item {{$item->order}}</div>
                                <div class="col-xs-11 ">
                                    <x-form.textarea :name="'item'.$item->order" rows="3" :value="$item->name" :placeholder="'Specific details of maintenance request '.$item->order.'.'"/>
                                </div>
                            </div>
                        @endforeach

                        {{-- Planner --}}
                        <h4>Future Planner Tasks</h4>
                        <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                        <div class="row">
                            <div class="col-md-12">
                                @if ($main->site->futureTasks()->count())
                                    @foreach ($main->site->futureTasks() as $plan)
                                        <div class="row">
                                            <div class="col-xs-1">{!! $plan->from->format('d/m/y') !!}</div>
                                            <div class="col-xs-11">{{$plan->task->name}}</div>
                                        </div>
                                    @endforeach
                                @else
                                    No future tasks on planner
                                @endif
                            </div>
                        </div>
                        <br>


                        {{-- Notes --}}
                        <livewire:misc.actions table="site_maintenance" :table-id="$main->id" :allow-add="Auth::user()->allowed2('edit.site.maintenance', $main)"/>

                        <hr>
                        <div class="pull-right" style="min-height: 50px">
                            <a href="/site/maintenance" class="btn default"> Back</a>
                            @if(Auth::user()->allowed2('edit.site.maintenance', $main))
                                @if ($main->step == 3 && Auth::user()->allowed2('sig.site.maintenance', $main))
                                    <button type="submit" name="save" class="btn blue" id="submit"> Assign Request</button>
                                @elseif (Auth::user()->allowed2('edit.site.maintenance', $main))
                                    <button type="submit" name="save" class="btn blue" id="submit"> Save</button>
                                @endif
                            @endif
                        </div>
                        <br><br>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div>
    </div>
    </div>
@stop


@section('page-level-plugins-head')
    <link href="https://unpkg.com/filepond/dist/filepond.css" rel="stylesheet" type="text/css"/>   {{-- Filepond --}}
    <link href="/assets/global/plugins/select2/css/select2.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/select2/css/select2-bootstrap.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/bootstrap-datepicker/css/bootstrap-datepicker.min.css" rel="stylesheet" type="text/css"/>
    <!--<link href="/assets/global/plugins/bootstrap-datepicker/css/bootstrap-datepicker3.min.css" rel="stylesheet" type="text/css"/>-->
    <script type="text/javascript">var html5lightbox_options = {watermark: "", watermarklink: ""};</script>
@stop

@section('page-level-plugins')
    <script src="/assets/global/plugins/select2/js/select2.full.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js" type="text/javascript"></script>
    <script src="/js/moment.min.js" type="text/javascript"></script>
    <script src="/js/libs/html5lightbox/html5lightbox.js" type="text/javascript"></script>
    <script src="https://unpkg.com/filepond/dist/filepond.min.js"></script> {{-- FilePond --}}
@stop

@section('page-level-scripts')
    {{-- Metronic + custom Page Scripts --}}
    <script src="/assets/pages/scripts/components-date-time-pickers.min.js" type="text/javascript"></script>
    <script src="/assets/pages/scripts/components-select2.min.js" type="text/javascript"></script>
    <script src="/js/filepond-basic.js" type="text/javascript"></script>
    <script>
        $.ajaxSetup({headers: {'X-CSRF-Token': $('meta[name=token]').attr('value')}});

        $(document).ready(function () {
            /* Select2 */
            $("#company_id").select2({placeholder: "Select Company", width: '100%'});
            $("#category_id").select2({placeholder: "Select category", width: "100%"});
            $("#assigned_to").select2({placeholder: "Select Company", width: '100%'});
            $("#super_id").select2({placeholder: "Select Supervisor", width: "100%"});

            /*
             $("#more").click(function (e) {
             e.preventDefault();
             $('#more').hide();
             $('#more_items').show();
             });*/

            $('#site-edit').hide();
            $('#client-edit').hide();
            $('#photos-edit').hide();

            $("#edit-site").click(function (e) {
                e.preventDefault();
                $('#edit-site').hide();
                $('#site-show').hide();
                $('#site-edit').show();
            });

            $("#edit-client").click(function (e) {
                e.preventDefault();
                $('#edit-client').hide();
                $('#client-show').hide();
                $('#client-edit').show();
            });
            $("#edit-photos").click(function (e) {
                e.preventDefault();
                $('#photos-show').hide();
                $('#photos-edit').show();
            });
            $("#edit-docs").click(function (e) {
                e.preventDefault();
                $('#photos-show').hide();
                $('#photos-edit').show();
            });
            $("#view-photos").click(function (e) {
                e.preventDefault();
                $('#photos-show').show();
                $('#photos-edit').hide();
            });
        });
    </script>
@stop

