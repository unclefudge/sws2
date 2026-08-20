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

    .topmodal {
        z-index: 9996 !important;
    }

    @media screen and (min-width: 992px) {
        .datepicker-input {
            width: 130px !important;
        }
    }

    @media screen and (min-width: 1200px) {
        .datepicker-input {
            width: 160px !important;
        }
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
                    <div class="portlet-body">
                        <div class="page-content-inner">
                            <form method="POST" action="{{ action([App\Http\Controllers\Site\SiteMaintenanceController::class, 'update'], $main->id) }}" class="horizontal-form">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" id="site_id" value="{{ $main->site_id }}">

                                @include('form-error')

                                <div class="row">
                                    <div class="col-md-5">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <h4>Site Details
                                                    @if ($main->status > 0 && Auth::user()->allowed2('edit.site.maintenance', $main))
                                                        <button class="btn dark btn-outline btn-sm pull-right" style="margin-top: -10px; border: 0px" id="edit-site">Edit</button>
                                                    @endif
                                                </h4>
                                            </div>
                                        </div>
                                        <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                                        @if ($main->site)
                                            <b>{{ $main->site->name }}</b>
                                        @endif<br>
                                        @if ($main->site)
                                            {{ $main->site->full_address }}<br>
                                        @endif
                                        <br>
                                        @if ($main->completed)
                                            <b>Prac Completion:</b> {{ $main->completed->format('d/m/Y') }}<br>
                                        @endif
                                        <div id="site-show">
                                            @if ($main->supervisor)
                                                <b>Supervisor:</b> {{ $main->supervisor }}
                                            @endif
                                        </div>
                                        <div id="site-edit">
                                            <x-form.datepicker name="completed" label="Prac Completed" :value="($main->completed) ? $main->completed->format('d/m/Y') : null" format="dd/mm/yyyy" readonly/>
                                            <x-form.input name="supervisor" label="Supervisor" :value="$main->supervisor"/>
                                        </div>
                                    </div>
                                    <div class="col-md-1"></div>

                                    <div class="col-md-6">
                                        <div class="row">
                                            <div class="col-md-5">
                                                <h4>Client Details
                                                    @if ($main->status > 0 && Auth::user()->allowed2('edit.site.maintenance', $main))
                                                        <button class="btn dark btn-outline btn-sm pull-right" style="margin: -10px 0px 0px 50px; border: 0px" id="edit-client">Edit</button>
                                                    @endif
                                                </h4>
                                            </div>
                                            <div class="col-md-7">
                                                <h2 style="margin: 0px; padding-right: 20px">
                                                    @if($main->status == '-1')
                                                        <span class="pull-right font-red hidden-sm hidden-xs">DECLINED</span>
                                                        <span class="text-center font-red visible-sm visible-xs">DECLINED</span>
                                                    @endif
                                                    @if($main->status == '0')
                                                        <span class="pull-right font-red hidden-sm hidden-xs"><small class="font-red">COMPLETED {{ $main->updated_at->format('d/m/Y') }}</small></span>
                                                        <span class="text-center font-red visible-sm visible-xs">COMPLETED {{ $main->updated_at->format('d/m/Y') }}</span>
                                                    @endif
                                                    @if($main->status == '1')
                                                        <span class="pull-right font-red hidden-sm hidden-xs">ACTIVE</span>
                                                        <span class="text-center font-red visible-sm visible-xs">ACTIVE</span>
                                                    @endif
                                                    @if($main->status == '2')
                                                        <span class="pull-right font-red hidden-sm hidden-xs">UNDER REVIEW</span>
                                                        <span class="text-center font-red visible-sm visible-xs">UNDER REVIEW</span>
                                                    @endif
                                                    @if($main->status == '4')
                                                        <span class="pull-right font-red hidden-sm hidden-xs">ON HOLD</span>
                                                        <span class="text-center font-red visible-sm visible-xs">ON HOLD</span>
                                                    @endif
                                                </h2>
                                            </div>
                                        </div>
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
                                                <br><b>Scheduled
                                                    Visit:</b> {{ ($main->nextClientVisit()->entity_type == 'c' && $main->nextClientVisit()->company ) ? $main->nextClientVisit()->company->name : 'Unassigned Company'}}
                                                &nbsp; ({{ $main->nextClientVisit()->from->format('d/m/Y') }})<br>
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
                                <br>

                                {{-- Other Maintence Requests --}}
                                <h4>Other Mainenance Requests</h4>
                                <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                                <div class="row">
                                    <div class="col-md-12">
                                        @if ($main->otherMaintenance->isNotEmpty())
                                            <div class="row" style="font-weight: bold">
                                                <div class="col-xs-1">#</div>
                                                <div class="col-xs-1">Updated</div>
                                                <div class="col-xs-2">Category</div>
                                                <div class="col-xs-2">Supervisor</div>
                                            </div>
                                            @foreach ($main->otherMaintenance->sortByDesc('updated_at') as $m)
                                                <div class="row">
                                                    <div class="col-xs-1"><a href="/site/maintenance/{{ $m->id }}">M{!! $m->code !!}</a></div>
                                                    <div class="col-xs-1">{{ $m->updated_at->format('d/m/y') }}</div>
                                                    <div class="col-xs-2">{{ $m->category?->name ?? '-' }}</div>
                                                    <div class="col-xs-2">{{ $m->taskOwner?->name ?? '-' }}</div>
                                                </div>
                                            @endforeach
                                        @else
                                            No other Maintenance Requests
                                        @endif
                                    </div>
                                </div>
                                <br>


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

                                {{-- Under Review - asign to super --}}
                                <h4>Maintenance Details</h4>
                                <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                                <div class="row">
                                    {{-- Goodwill --}}
                                    {{--}}
                                    <div class="col-md-2 ">
                                        <div class="form-group">
                                            <label for="goodwill" class="control-label">Goodwill</label>
                                            @if ($main->status && Auth::user()->allowed2('sig.site.maintenance', $main))
                                                <x-form.select name="goodwill" id="goodwill" :options="['1' => 'Yes', '0' => 'No']" :value="$main->goodwill"/>
                                            @else
                                                <x-form.input name="goodwill_text" :value="($main->goodwill) ? 'Yes' : 'No'" readonly/>
                                            @endif
                                        </div>
                                    </div>--}}

                                    {{-- Category --}}
                                    <div class="col-md-3 ">
                                        @if ($main->status && Auth::user()->allowed2('sig.site.maintenance', $main))
                                            <x-form.select name="category_id" id="category_id" label="Category" :options="['' => 'Select category'] + \App\Models\Site\SiteMaintenanceCategory::all()->sortBy('name')->pluck('name', 'id')->toArray()"
                                                           :value="$main->category_id" plugin="select2" title="Select category"/>
                                        @else
                                            <x-form.input name="category_text" label="Category" :value="($main->category_id) ? \App\Models\Site\SiteMaintenanceCategory::find($main->category_id)->name : 'Select Category'" readonly/>
                                        @endif
                                    </div>

                                    {{-- Warranty --}}
                                    <div class="col-md-2 ">
                                        @if ($main->status && Auth::user()->allowed2('sig.site.maintenance', $main))
                                            <x-form.select name="warranty" id="warranty" label="Warranty" :options="$maintenanceWarranty::all()" :value="$main->warranty"/>
                                        @else
                                            <x-form.input name="warranty_text" label="Warranty" :value="$maintenanceWarranty::name($main->warranty)" readonly/>
                                        @endif
                                    </div>

                                    {{-- Client Contacted --}}
                                    <div class="col-md-2">
                                        @if ($main->status && Auth::user()->allowed2('edit.site.maintenance', $main) || Auth::user()->allowed2('sig.site.maintenance', $main))
                                            <x-form.datepicker name="client_contacted" label="Client Contacted" :value="$main->client_contacted?->format('d/m/Y')" format="dd/mm/yyyy" clear-button readonly/>
                                        @else
                                            <x-form.input name="client_contacted" label="Client Contacted" :value="$main->client_contacted?->format('d/m/Y')" readonly/>
                                        @endif
                                    </div>

                                    {{-- Client Appointment --}}
                                    <div class="col-md-2">
                                        @if ($main->status && Auth::user()->allowed2('edit.site.maintenance', $main) || Auth::user()->allowed2('sig.site.maintenance', $main))
                                            <x-form.datepicker name="client_appointment" label="Client Appointment" :value="$main->client_appointment?->format('d/m/Y')" format="dd/mm/yyyy" clear-button readonly/>
                                        @else
                                            <x-form.input name="client_appointment" label="Client Appointment" :value="$main->client_appointment?->format('d/m/Y')" readonly/>
                                        @endif
                                    </div>

                                    {{-- Status --}}
                                    <div class="col-md-2 pull-right">
                                        @if ($main->status && Auth::user()->allowed2('sig.site.maintenance', $main))
                                            <x-form.select name="status" id="status" label="Status" :options="['1' => 'Active', '-1' => 'Decline', '4' => 'On Hold']" :value="$main->status"/>
                                        @elseif ($main->status && Auth::user()->allowed2('edit.site.maintenance', $main))
                                            <x-form.select name="status" id="status" label="Status" :options="['1' => 'Active', '4' => 'On Hold']" :value="$main->status"/>
                                        @elseif ($main->status == 0 && Auth::user()->allowed2('edit.site.maintenance', $main))
                                            <x-form.select name="status" id="status" label="Status" :options="['0' => 'Completed', '1' => 'Re-Activate']" :value="$main->status"/>
                                        @else
                                            <x-form.input name="status_text" label="Status" :value="($main->status == 0) ? 'Completed' : 'Declined'" readonly/>
                                        @endif
                                    </div>
                                </div>

                                <div class="row note note-warning" id="onhold-div"
                                     style="{{ $errors->has('onhold_reason') ? 'display:show' : 'display:none' }}">
                                    {{-- On Hold Reason --}}
                                    <div class="col-md-12">
                                        <div id="onhold_reason-div" style="{{ $errors->has('onhold_reason') ? '' : 'display:show' }}">
                                            <x-form.input name="onhold_reason" label="Please specify the reason for placing request ON HOLD"/>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    {{-- Assigned Supervisor --}}
                                    <div class="col-md-5">
                                        <div class="form-group {{ $errors->has('super_id') ? 'has-error' : '' }}" style="{{ $errors->has('super_id') ? '' : 'display:show' }}" id="company-div">
                                            <label for="super_id" class="control-label">Maintenance Supervisor</label>
                                            @if ($main->status && Auth::user()->allowed2('sig.site.maintenance', $main))
                                                {{-- Supervisor --}}
                                                <select id="super_id" name="super_id" class="form-control select2"
                                                        style="width:100%">
                                                    <option value=""></option>
                                                    {{--}}<optgroup label="Cape Code Supervisors"></optgroup>--}}
                                                    @foreach (Auth::user()->company->supervisors()->sortBy('name') as $super)
                                                        <option value="{{ $super->id }}" {{ ($super->id == $main->super_id) ? 'selected' : '' }}>{{ $super->name }}</option>
                                                    @endforeach
                                                    {{--}}
                                                    <optgroup label="External Users"></optgroup>
                                                    <option value="2023" {{ ('2023' == $main->super_id) ? 'selected' : '' }}>
                                                        Jason Habib (Prolific Projects)
                                                    </option>--}}
                                                </select>
                                                <x-form.error name="super_id"/>
                                            @else
                                                <x-form.input name="assigned_super_text" :value="($main->super_id) ? $main->taskOwner->name : '-'" readonly/>
                                            @endif
                                            <x-form.error name="super_id"/>
                                        </div>
                                    </div>

                                    {{-- AC Form --}}
                                    @if ($main->status && Auth::user()->allowed2('sig.site.maintenance', $main))
                                        <div class="col-md-2 pull-right">
                                            @if ($main->site->aftercare == "No")
                                                <span class="font-red"><br>AC Not Requested</span>
                                                <x-form.hidden name="ac_form_required" value="0"/>
                                            @else
                                                <x-form.select name="ac_form_required" id="ac_form_required" label="AC Form Required" :options="['0' => 'No', '1' => 'Yes']" value="0"/>
                                            @endif
                                        </div>
                                    @endif

                                    @if (!$main->status)
                                        <div class="col-md-2 pull-right">
                                            @if ($main->site->aftercare == "No")
                                                <span class="font-red"><br>AC Not Requested</span>
                                                <x-form.hidden name="ac_form_required" value="0"/>
                                            @else
                                                @php
                                                    $acFormSentValue = ($main->ac_form_sent && (int) $main->ac_form_sent->format('Y') === 1)
                                                        ? 'N/A'
                                                        : $main->ac_form_sent?->format('d/m/Y');
                                                @endphp

                                                @if (Auth::user()->allowed2('add.site.maintenance'))
                                                    <x-form.datepicker name="ac_form_sent" label="AC Form Sent" :value="$acFormSentValue" format="dd/mm/yyyy" clear-button readonly/>
                                                @else
                                                    <x-form.input name="ac_form_sent" label="AC Form Sent" :value="$acFormSentValue" readonly/>
                                                @endif
                                                <div style="text-align:right"><a href="#" id="ac_form_mark_na">Mark as N/A</a></div>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                                <div class="row">
                                    @if (Auth::user()->allowed2('edit.site.maintenance', $main))
                                        <div class="col-md-1 pull-right">
                                            <button id="submit" type="submit" name="save" class="btn blue" style="margin-top: 25px">Save</button>
                                        </div>
                                    @endif
                                </div>
                        </div>
                        <br>

                        {{-- Maintenance Items --}}
                        <div class="row">
                            <div class="col-md-12">
                                <livewire:site.maintenance.items :maintenance-id="$main->id"/>
                            </div>
                        </div>

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
                        <livewire:misc.actions table="site_maintenance" :table-id="$main->id"/>

                        {{-- Assigned Tasks --}}
                        <livewire:misc.assigned-tasks context="maintenance" :context-id="$main->id"/>

                        </form>

                        {{-- Sign Off + Maintenance workflow --}}
                        <livewire:site.maintenance.workflow :maintenance-id="$main->id"/>

                    </div>
                </div>
            </div>
        </div>
    </div>
@stop


@section('page-level-plugins-head')
    <link href="https://unpkg.com/filepond/dist/filepond.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/select2/css/select2.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/select2/css/select2-bootstrap.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/bootstrap-select/css/bootstrap-select.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/bootstrap-datepicker/css/bootstrap-datepicker3.min.css" rel="stylesheet" type="text/css"/>
    <script type="text/javascript">var html5lightbox_options = {watermark: "", watermarklink: ""};</script>
@stop

@section('page-level-plugins')
    <script src="/assets/global/plugins/select2/js/select2.full.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/bootstrap-select/js/bootstrap-select.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js" type="text/javascript"></script>
    <script src="https://unpkg.com/filepond/dist/filepond.min.js"></script>
    <script src="/js/libs/html5lightbox/html5lightbox.js" type="text/javascript"></script>
@stop

@section('page-level-scripts')
    <script src="/js/filepond-basic.js" type="text/javascript"></script>

    <script>
        $.ajaxSetup({headers: {'X-CSRF-Token': $('meta[name=token]').attr('value')}});

        $(document).ready(function () {
            $("#super_id").select2({placeholder: "Select supervisor", width: '100%'});
            $("#category_id").select2({placeholder: "Select category", width: "100%"});

            // Initialise the site's Bootstrap Datepicker for the normal page fields.
            $('.date-picker').each(function () {
                const picker = $(this);

                if (!picker.data('datepicker')) {
                    picker.datepicker({
                        rtl: typeof App !== 'undefined' ? App.isRTL() : false,
                        orientation: 'left',
                        autoclose: true
                    });
                }
            });

            $("#status").change(function () {
                $('#onhold-div').hide();

                if ($("#status").val() == '4') {
                    $('#onhold-div').show();
                }
            });

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

            $("#ac_form_mark_na").click(function (e) {
                e.preventDefault();

                $('#ac_form_sent').val('N/A');

                const picker = $('#ac_form_sent').closest('.date-picker');
                if (picker.data('datepicker')) {
                    picker.datepicker('clearDates');
                    $('#ac_form_sent').val('N/A');
                }
            });

            $('.deleteFile').on('click', function (e) {
                e.preventDefault();

                const id = $(this).data('did');
                const name = $(this).data('name');

                swal({
                    title: "Are you sure?",
                    text: "You will not be able to restore this file!<br><b>" + name + "</b>",
                    showCancelButton: true,
                    cancelButtonColor: "#555555",
                    confirmButtonColor: "#E7505A",
                    confirmButtonText: "Yes, delete it!",
                    allowOutsideClick: true,
                    html: true,
                }, function () {
                    window.location = '/site/maintenance/' + {{ $main->id }} + '/delfile/' + id;
                });
            });
        });
    </script>
@stop
