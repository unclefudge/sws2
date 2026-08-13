@extends('layout')
<?php $hotwater_types = ['' => 'Select option', 'In Roof' => 'In Roof', 'External' => 'External', 'Internal' => 'Internal', 'Gas' => 'Gas', 'Electric' => 'Electric', 'Solar' => 'Solar', 'Other' => 'Other']; ?>
@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        @if (Auth::user()->hasAnyPermissionType('site.inspection'))
            <li><a href="/site/inspection/plumbing">Plumbing Inspection Reports</a><i class="fa fa-circle"></i></li>
        @endif
        <li><span>Edit Report</span></li>
    </ul>
@stop

<style>
    a.mytable-header-link {
        font-size: 14px;
        font-weight: 600;
        color: #333 !important;
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
                            <span class="caption-subject font-green-haze bold uppercase">Plumbing Inspection Report</span>
                            <span class="caption-helper"> ID: {{ $report->id }}</span>
                        </div>
                    </div>
                    <div class="portlet-body form">
                        <form method="POST" action="{{ action([\App\Http\Controllers\Site\SiteInspectionPlumbingController::class, 'update'], $report->id) }}" class="horizontal-form" enctype="multipart/form-data">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="report_id" id="report_id" value="{{ $report->id }}">
                            <input type="hidden" name="site_id" id="site_id" value="{{ $report->site_id }}">

                            @include('form-error')

                            @if (!$report->assigned_to)
                                {{-- Progress Steps --}}
                                <div class="mt-element-step hidden-sm hidden-xs">
                                    <div class="row step-thin" id="steps">
                                        <div class="col-md-6 mt-step-col first done">
                                            <div class="mt-step-number bg-white font-grey">1</div>
                                            <div class="mt-step-title uppercase font-grey-cascade">Create</div>
                                            <div class="mt-step-content font-grey-cascade">Create report</div>
                                        </div>
                                        <div class="col-md-6 mt-step-col last active">
                                            <div class="mt-step-number bg-white font-grey">2</div>
                                            <div class="mt-step-title uppercase font-grey-cascade">Assign</div>
                                            <div class="mt-step-content font-grey-cascade">Assign company</div>
                                        </div>
                                    </div>
                                </div>
                                <hr>
                            @endif

                            <div class="form-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">

                                            <x-form.input name="site_name" label="Site" :value="$report->site->name" readonly/>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">

                                            <x-form.input name="site_code" label="Job #" :value="$report->site->code" readonly/>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <h2 style="margin: 0px; padding-right: 20px">
                                            @if($report->status == '0')
                                                <span class="pull-right font-red hidden-sm hidden-xs"><small class="font-red">COMPLETED {{ $report->updated_at->format('d/m/Y') }}</small></span>
                                                <span class="text-center font-red visible-sm visible-xs">COMPLETED {{ $report->updated_at->format('d/m/Y') }}</span>
                                            @endif
                                            @if($report->status == '1' && $report->assigned_to)
                                                <span class="pull-right font-red hidden-sm hidden-xs">ACTIVE</span>
                                                <span class="text-center font-red visible-sm visible-xs">ACTIVE</span>
                                            @endif
                                        </h2>
                                    </div>
                                </div>

                                <h4 class="font-green-haze">Client details</h4>
                                <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                                <div class="row">
                                    <div class="col-md-3">
                                        @if(Auth::user()->allowed2('add.site.inspection'))
                                            <x-form.input name="client_name" label="Name" :value="$report->client_name"/>
                                        @else
                                            <x-form.input name="client_name" label="Name" :value="$report->client_name" readonly/>
                                        @endif
                                    </div>
                                    <div class="col-md-7">
                                        @if(Auth::user()->allowed2('add.site.inspection'))
                                            <x-form.input name="client_address" label="Address" :value="$report->client_address"/>
                                        @else
                                            <x-form.input name="client_address" label="Address" :value="$report->client_address" readonly/>
                                        @endif
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <b>Client Primary Contact</b><br>
                                        {!! $report->site->client1_name ? $report->site->client1_name . "<br>" : '' !!}
                                        {!! ($report->site->client1_mobile) ? $report->site->client1_mobile . "<br>" : "" !!}
                                        {!! ($report->site->client1_email) ? "<a href='mailto:". $report->site->client1_email."'> " . $report->site->client1_email ."</a>" : "" !!}
                                    </div>
                                    <div class="col-md-6">
                                        <b>Secondary Contact</b><br>
                                        {!! $report->site->client2_name ? $report->site->client2_name . "<br>" : '' !!}
                                        {!! ($report->site->client2_mobile) ? $report->site->client2_mobile . "<br>" : "" !!}
                                        {!! ($report->site->client2_email) ? "<a href='mailto:". $report->site->client2_email."'> " . $report->site->client2_email ."</a>" : "" !!}
                                    </div>
                                </div>

                                <h4 class="font-green-haze">Admin Notes</h4>
                                <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                                <div class="row">
                                    <div class="col-md-12 ">
                                        @if(Auth::user()->allowed2('add.site.inspection'))
                                            <x-form.textarea name="info" rows="5" placeholder="Details" :value="$report->info"/>
                                        @else
                                            <x-form.textarea name="info" rows="5" placeholder="Details" :value="$report->info" readonly/>
                                        @endif
                                    </div>
                                </div>

                                {{-- Gallery --}}
                                <br>
                                <div class="row" id="photos-show">
                                    <div class="col-md-7">
                                        <h4>Photos
                                            @if($report->status == 1 && (Auth::user()->allowed2('add.site.inspection') || Auth::user()->allowed2('edit.site.inspection', $report)))
                                                <button class="btn dark btn-outline btn-sm pull-right" style="margin-top: -10px; border: 0px" id="edit-photos">Edit</button>
                                            @endif</h4>
                                        <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                                        @include('site/inspection/_gallery')
                                    </div>
                                    <div class="col-md-1"></div>
                                    <div class="col-md-4" id="docs-show">
                                        <h4>Documents
                                            @if($report->status == 1 && (Auth::user()->allowed2('add.site.inspection') || Auth::user()->allowed2('edit.site.inspection', $report)))
                                                <button class="btn dark btn-outline btn-sm pull-right" style="margin-top: -10px; border: 0px" id="edit-docs">Edit</button>
                                            @endif
                                        </h4>
                                        <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                                        @include('site/inspection/_docs')
                                    </div>
                                </div>

                                {{-- Photos / Docs --}}
                                <div id="photos-edit">
                                    <h4 class="font-green-haze">Photos / Documents
                                        @if(Auth::user()->allowed2('add.site.maintenance') || Auth::user()->allowed2('edit.site.maintenance', $report))
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

                                <h4 class="font-green-haze">Inspection details</h4>
                                <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                                <div class="row">
                                    {{-- Assigned To Company --}}
                                    <div class="col-md-4">
                                        @if(Auth::user()->allowed2('sig.site.inspection'))
                                            <x-form.select name="assigned_to" label="Assigned to company">
                                                @if (!$report->assigned_to)
                                                    <option value="">Select company</option>
                                                @endif
                                                @foreach (Auth::user()->company->reportsTo()->companies('1')->sortBy('name') as $company)
                                                    @if (in_array('8', $company->tradesSkilledIn->pluck('id')->toArray()))
                                                        <option value="{{ $company->id }}" {{ ($report->assigned_to && $report->assigned_to == $company->id) ? 'selected' : '' }}>{{ $company->name }}</option>
                                                    @endif
                                                @endforeach
                                            </x-form.select>
                                        @else
                                            <x-form.input name="assigned_name" label="Assigned to company" :value="$report->assignedTo ? $report->assignedTo->name : ''" readonly/>
                                        @endif
                                    </div>
                                    {{-- Inspection Date/Time --}}
                                    <div class="col-md-4">
                                        <div class="form-group" style="{{ (!$report->assigned_to) ? 'display:none' : '' }}" id="inspected_at-div">
                                            <label for="inspected_at" class="control-label">Date / Time of Inspection</label>
                                            <div class="input-group date form_datetime form_datetime bs-datetime" data-date-end-date="0d">
                                                <input type="text" name="inspected_at" id="inspected_at" class="form-control" value="{{ old('inspected_at', $report->inspected_at ? $report->inspected_at->format('d F Y - H:i') : '') }}" readonly style="background:#FFF">
                                                <span class="input-group-addon">
                                                <button class="btn default date-set" type="button"><i class="fa fa-calendar"></i></button>
                                            </span>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Client contacted --}}
                                    <div class="col-md-2" style="{{ (!$report->assigned_to) ? 'display:none' : '' }}">


                                        <label for="client_contacted" class="control-label">Client contacted</label>
                                        <div class="input-group" style="width:80%">
                                            <datepicker :value.sync="xx.client_contacted" format="dd/MM/yyyy" :placeholder="choose date"></datepicker>
                                        </div>
                                        <input v-model="xx.client_contacted" type="hidden" name="client_contacted" id="client_contacted" value="{{  ($report->client_contacted) ? $report->client_contacted->format('d/m/Y') : ''}}">


                                    </div>

                                    {{-- Status --}}
                                    <div class="col-md-2 pull-right">
                                        <div class="form-group">

                                            <?php $complated_status = ($report->status == 3) ? 3 : 0 ?>
                                            @if ($report->status && Auth::user()->allowed2('edit.site.inspection', $report) || ($report->status == 0 && Auth::user()->allowed2('sig.site.inspection', $report)))
                                                @if (Auth::user()->allowed2('sig.site.inspection', $report))
                                                    <x-form.select name="status" label="Status" :options="['1' => 'Active', $complated_status => 'Completed', '4' => 'On Hold']" :value="$report->status"/>
                                                @else
                                                    <x-form.select name="status" label="Status" :options="['1' => 'Active', $complated_status => 'Completed']" :value="$report->status"/>
                                                @endif
                                            @else
                                                <x-form.input name="status_text" label="Status" :value="$report->status == 0 ? 'Completed' : 'Active'" readonly/>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                {{-- Inspectors Name + Lic--}}
                                <div class="row note note-warning" id="inspector-div" style="{{ ($errors->has('inspected_name') || $errors->has('inspected_lic')) ? 'display:block' : 'display:none' }}">
                                    <div class="col-md-4">
                                        <x-form.input name="inspected_name" label="Inspection carried out by" :value="Auth::user()->name"/>
                                    </div>
                                    <div class="col-md-2">
                                        <x-form.input name="inspected_lic" label="Licence No." :value="Auth::user()->company->contractorLicence()"/>
                                    </div>
                                </div>

                                <div id="report-div" style="{{ (!$report->assigned_to) ? 'display:none' : '' }}">
                                    <h4 class="font-green-haze">Hot / Cold Water</h4>
                                    <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                                    {{--Water Pressure / Hammer--}}
                                    <div class="row">
                                        <div class="col-md-3">
                                            <x-form.input name="pressure" label="Water Pressure (kpa)" :value="$report->pressure"/>
                                        </div>
                                        <div class="col-md-5">
                                            <x-form.select name="pressure_reduction" label="500kpa Water Pressure Reduction Value Recommend" :options="['' => 'Select option', '1' => 'Yes', '0' => 'No']" :value="$report->pressure_reduction"/>
                                        </div>
                                        <div class="col-md-2">
                                            <x-form.select name="hammer" label="Water Hammer" :options="['' => 'Select option', 'Yes' => 'Yes', 'No' => 'No', 'N/A' => 'N/A']" :value="$report->hammer"/>
                                        </div>
                                    </div>

                                    {{-- Hotwater / Pipes / Gas --}}
                                    <div class="row">
                                        <div class="col-md-3">
                                            <x-form.select name="hotwater_type" label="Existing Hot Water Type" :options="$hotwater_types" :value="$report->hotwater_type"/>
                                        </div>
                                        <div class="col-md-5">
                                            <x-form.select name="hotwater_lowered" label="Will pipes in roof hot water need to be lowerd?" :options="['' => 'Select option', '1' => 'Yes', '0' => 'No']" :value="$report->hotwater_lowered"/>
                                        </div>
                                        <div class="col-md-2">
                                            <x-form.select name="fuel_type" label="Fuel Type" :options="['' => 'Select option', 'Gas' => 'Gas', 'Electric' => 'Electric', 'Other' => 'Other']" :value="$report->fuel_type"/>
                                        </div>
                                    </div>

                                    {{--  Gas  Meter / Pipes--}}
                                    <h4 class="font-green-haze">Gas</h4>
                                    <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <x-form.select name="gas_position" label="Gas Meter Position OK?" :options="['' => 'Select option', 'Yes' => 'Yes', 'No' => 'No', 'N/A' => 'N/A']" :value="$report->gas_position"/>
                                        </div>
                                        <div class="col-md-5">
                                            <x-form.select name="gas_lines" label="Are gas pipes able to be tapped into?" :options="['' => 'Select option', '1' => 'Yes - refer to comments below', '0' => 'No - refer to comments below ']" :value="$report->gas_lines"/>
                                        </div>
                                        <div class="col-md-2">
                                            <x-form.select name="gas_pipes" label="Gas Pipes" :options="['' => 'Select option', 'GAL Steel' => 'GAL Steel', 'Copper' => 'Copper', 'Gas Pex' => 'Gas Pex', 'Other' => 'Other']" :value="$report->gas_pipes"/>
                                        </div>
                                    </div>

                                    {{-- Gas Notes --}}
                                    <div class="row">
                                        <div class="col-md-12">
                                            <x-form.textarea name="gas_notes" label="Gas Notes" rows="5" :value="$report->gas_notes"/>
                                        </div>
                                    </div>


                                    {{-- Existing Plumbing --}}
                                    <h4 class="font-green-haze">Condition of existing plumbing</h4>
                                    <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <x-form.textarea name="existing" label="The existing plumbing was found to be" rows="5" :value="$report->existing"/>
                                        </div>
                                    </div>

                                    {{-- Comments --}}
                                    <h4 class="font-green-haze">Additional Notes for Client</h4>
                                    <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <x-form.textarea name="notes" label="Client notes" rows="10" :value="$report->notes"/>
                                        </div>
                                    </div>

                                    {{-- Water Pressure --}}
                                    <h4 class="font-green-haze">Water Pressure</h4>
                                    <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <x-form.textarea name="pressure_notes" label="Water pressure higher than 500KPA will void the warranty on all mixer sets; it is our recommendation that you have fitted a pressure limiting valve at the metre to avoid possible problems" rows="3"
                                                             :value="$report->pressure_notes"/>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="pressure_cost" class="control-label">Cost (incl GST)</label>
                                                <div class="input-group">
                                                    <span class="input-group-addon"><i class="fa fa-usd"></i></span>
                                                    <input type="text" name="pressure_cost" id="pressure_cost" class="form-control" value="{{ old('pressure_cost', $report->pressure_cost) }}">
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Water Hammer --}}
                                    <h4 class="font-green-haze">Water Hammer</h4>
                                    <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <x-form.textarea name="hammer_notes" label="Water hammer comments" rows="3" :value="$report->hammer_notes"/>
                                        </div>
                                    </div>

                                    {{-- Sewer --}}
                                    <h4 class="font-green-haze">Sewer</h4>
                                    <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <x-form.textarea name="sewer_notes" label="Upon closer inspection of the sewer diagram that we have obtained from the Water Board" rows="3" :value="$report->sewer_notes"/>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="sewer_cost" class="control-label">Cost estimate (incl GST)</label>
                                                <div class="input-group">
                                                    <span class="input-group-addon"><i class="fa fa-usd"></i></span>
                                                    <input type="text" name="sewer_cost" id="sewer_cost" class="form-control" value="{{ old('sewer_cost', $report->sewer_cost) }}">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="sewer_allowance" class="control-label">Allowance in your tender document is (incl GST)</label>
                                                <div class="input-group">
                                                    <span class="input-group-addon"><i class="fa fa-usd"></i></span>
                                                    <input type="text" name="sewer_allowance" id="sewer_allowance" class="form-control" value="{{ old('sewer_allowance', $report->sewer_allowance) }}">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="sewer_extra" class="control-label">Meaning you may incur extra costs of (incl GST)</label>
                                                <div class="input-group">
                                                    <span class="input-group-addon"><i class="fa fa-usd"></i></span>
                                                    <input type="text" name="sewer_extra" id="sewer_extra" class="form-control" value="{{ old('sewer_extra', $report->sewer_extra) }}">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12"><h6>PRICE TO BE CONFIRMED AT TIME OF CONSTRUCTION AND DOES NOT INCLUDE BUILDERS MARGIN</h6><br></div>
                                    </div>


                                    {{-- Stormwater --}}
                                    <h4 class="font-green-haze">Stormwater</h4>
                                    <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <x-form.textarea name="stormwater_notes" label="Upon closer examination of your current stormwater system" rows="3" :value="$report->stormwater_notes"/>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="stormwater_cost" class="control-label">Cost estimate (incl GST)</label>
                                                <div class="input-group">
                                                    <span class="input-group-addon"><i class="fa fa-usd"></i></span>
                                                    <input type="text" name="stormwater_cost" id="stormwater_cost" class="form-control" value="{{ old('stormwater_cost', $report->stormwater_cost) }}">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="stormwater_allowance" class="control-label">Allowance in your tender document is (incl GST)</label>
                                                <div class="input-group">
                                                    <span class="input-group-addon"><i class="fa fa-usd"></i></span>
                                                    <input type="text" name="stormwater_allowance" id="stormwater_allowance" class="form-control" value="{{ old('stormwater_allowance', $report->stormwater_allowance) }}">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="stormwater_extra" class="control-label">Meaning you may incur extra costs of (incl GST)</label>
                                                <div class="input-group">
                                                    <span class="input-group-addon"><i class="fa fa-usd"></i></span>
                                                    <input type="text" name="stormwater_extra" id="stormwater_extra" class="form-control" value="{{ old('stormwater_extra', $report->stormwater_extra) }}">
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Stormwater Detention --}}
                                    <h4 class="font-green-haze">Onsite Stormwater Detention</h4>
                                    <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <x-form.select name="stormwater_detention_type" :options="['' => 'Select option', 'Refer to comments below' => 'Refer to comments below', 'Refer to quote' => 'Refer to quote', 'N/A' => 'N/A']" :value="$report->stormwater_detention_type"/>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <x-form.textarea name="stormwater_detention_notes" label="Onsite Stormwater Detention Comments" rows="3" :value="$report->stormwater_detention_notes"/>
                                        </div>
                                    </div>

                                    {{-- Note --}}
                                    <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <h6>Please note that these remain best estimate until the final position and depth of services are located. Final estimates will be relayed to you at that time for your approval. <br><br>Thank you for your acknowledgment of the above and we will do our
                                                best to
                                                keep all costs to a minimum.</h6><br></div>
                                    </div>

                                    {{-- Additional --}}
                                    {{--}}
                                    <h4 class="font-green-haze">Additional Notes for Cape Cod</h4>
                                    <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <x-form.textarea name="trade_notes" label="Cape Cod Notes (private)" rows="10" :value="$report->trade_notes"/>
                                        </div>
                                    </div>--}}

                                    {{-- Notes --}}
                                    <div class="row">
                                        <div class="col-md-12">
                                            <app-actions :table_id="{{ $report->id }}"></app-actions>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-actions right">
                                <a href="/site/inspection/plumbing" class="btn default"> Back</a>
                                <button type="submit" class="btn green" id="submit"> Save</button>
                            </div>
                    </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <template id="actions-template">
        <action-modal></action-modal>
        <input v-model="xx.table_id" type="hidden" id="table_id" value="{{ $report->id }}">
        <input v-model="xx.created_by" type="hidden" id="created_by" value="{{ Auth::user()->id }}">
        <input v-model="xx.created_by_fullname" type="hidden" id="fullname" value="{{ Auth::user()->fullname }}">

        <div class="page-content-inner">
            <div class="row">
                <div class="col-md-12">
                    <h4 class="font-green-haze">Additional Notes for {{ ($report->ownedBy->nickname) ? $report->ownedBy->nickname :  $report->ownedBy->name }}
                        <button v-on:click.stop.prevent="$root.$broadcast('add-action-modal')" class="btn btn-circle green btn-outline btn-sm pull-right" data-original-title="Add">Add</button>
                    </h4>
                    <hr>
                    <table v-show="actionList.length" class="table table-striped table-bordered table-nohover order-column">
                        <thead>
                        <tr class="mytable-header">
                            <th style="width:10%">Date</th>
                            <th> Details</th>
                            <th style="width:20%"> Name</th>
                        </tr>
                        </thead>
                        <tbody>
                        <template v-for="action in actionList">
                            <tr>
                                <td>@{{ action.niceDate }}</td>
                                <td>@{{ action.action }}</td>
                                <td>@{{ action.fullname }}</td>
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
    <link href="https://unpkg.com/filepond/dist/filepond.css" rel="stylesheet" type="text/css"/>   {{-- Filepond --}}
    <link href="/assets/global/plugins/select2/css/select2.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/select2/css/select2-bootstrap.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/bootstrap-select/css/bootstrap-select.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css" rel="stylesheet" type="text/css"/>
    <script type="text/javascript">var html5lightbox_options = {watermark: "", watermarklink: ""};</script>
@stop

@section('page-level-plugins')
    <script src="/assets/global/plugins/select2/js/select2.full.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js" type="text/javascript"></script>
    <script src="/js/moment.min.js" type="text/javascript"></script>
    <script src="/js/libs/html5lightbox/html5lightbox.js" type="text/javascript"></script>
    <script src="https://unpkg.com/filepond/dist/filepond.min.js"></script> {{-- FilePond --}}
@stop

@section('page-level-scripts')
    {{-- Metronic + custom Page Scripts --}}
    <script src="/assets/pages/scripts/components-date-time-pickers.js" type="text/javascript"></script>
    <script src="/js/libs/moment.min.js" type="text/javascript"></script>
    <script src="/js/filepond-basic.js" type="text/javascript"></script>
    <script src="/js/libs/vue.1.0.24.js " type="text/javascript"></script>
    <script src="/js/libs/vue-strap.min.js"></script>
    <script src="/js/libs/vue-resource.0.7.0.js " type="text/javascript"></script>
    <script src="/js/vue-modal-component.js"></script>
    <script src="/js/vue-app-basic-functions.js"></script>
    <script type="text/javascript">
        $.ajaxSetup({headers: {'X-CSRF-Token': $('meta[name=token]').attr('value')}});

        $(document).ready(function () {
            /* Select2 */
            $("#assigned_to").select2({placeholder: "Select Company"});

            if ($("#status").val() == '3') {
                $('#inspector-div').show();
            }

            $("#status").change(function () {
                $('#inspector-div').hide();

                if ($("#status").val() == '0') {
                    $('#inspector-div').show();
                }
            });

            $('#photos-edit').hide();
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

            $('.deleteFile').on('click', function (e) {
                e.preventDefault();
                var id = $(this).data('did');
                var name = $(this).data('name');
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
                    window.location = '/site/inspection/plumbing/' + {{$report->id}} + '/delfile/' + id;
                });
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
            table_name: 'site_inspection_plumbing', table_id: '', record_status: '', stage: '', next_review_date: '', client_contacted: '',
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


