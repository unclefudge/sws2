@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        <li><a href="/safety/doc/wms">SWMS</a><i class="fa fa-circle"></i></li>
        <li><span>Edit Statement</span></li>
    </ul>
@stop

@section('content')

    <app-wms :doc_id="{{ $doc->id }}"></app-wms>

    <template id="wms-template">
        <input v-model="xx.doc.id" type="hidden" id="doc_id" value="{{ $doc->id }}">
        <input v-model="xx.doc.name" type="hidden" id="doc_name" value="{{ $doc->name }}">
        <input v-model="xx.doc.file" type="hidden" id="doc_file" value="{{ $doc->attachment }}">
        <input v-model="xx.doc.builder" type="hidden" id="doc_file" value="{{ $doc->builder }}">
        <input v-model="xx.doc.master" type="hidden" id="doc_master" value="{{ $doc->master }}">
        <input v-model="xx.company.id" type="hidden" id="company_id" value="{{ $doc->for_company_id }}">
        @if ($doc->master)
            <input v-model="xx.company.name" type="hidden" value="Company">
            <input v-model="xx.company.parent_id" type="hidden" value="0">
            <input v-model="xx.company.parent_name" type="hidden" value="Parent Company">
        @else
            <input v-model="xx.company.name" type="hidden" value="{{ App\Models\Company\Company::find($doc->for_company_id)->name }}">
            <input v-model="xx.company.parent_id" type="hidden" value="{{ App\Models\Company\Company::find($doc->for_company_id)->reportsTo()->id }}">
            <input v-model="xx.company.parent_name" type="hidden" value="{{ App\Models\Company\Company::find($doc->for_company_id)->reportsTo()->name }}">
        @endif
        <input v-model="xx.user.name" type="hidden" value="{{ Auth::user()->fullname }}">
        <input v-model="xx.user.company_id" type="hidden" value="{{ Auth::user()->company_id }}">
        @if (! $doc->principle_id)
            <input v-model="xx.user.signoff" type="hidden" value="manual">
        @else
            <input v-model="xx.user.signoff" type="hidden" value="{{ Auth::user()->allowed2('sig.wms', $doc) }}">
        @endif
        <div class="page-content-inner">
            {{-- Progress Steps --}}
            <div class="mt-element-step">
                <div class="row step-line" id="steps">
                    <div class="col-md-3 mt-step-col first done">
                        <div class="mt-step-number bg-white font-grey"><i class="fa fa-check"></i></div>
                        <div class="mt-step-title uppercase font-grey-cascade">Create</div>
                        <div class="mt-step-content font-grey-cascade">Create SWMS</div>
                    </div>
                    <div class="col-md-3 mt-step-col active">
                        <div class="mt-step-number bg-white font-grey">2</div>
                        <div class="mt-step-title uppercase font-grey-cascade">Draft</div>
                        <div class="mt-step-content font-grey-cascade">Add content</div>
                    </div>
                    <div class="col-md-3 mt-step-col">
                        <div class="mt-step-number bg-white font-grey">3</div>
                        <div class="mt-step-title uppercase font-grey-cascade">Sign Off</div>
                        <div class="mt-step-content font-grey-cascade">Request Sign Off</div>
                    </div>
                    <div class="col-md-3 mt-step-col last">
                        <div class="mt-step-number bg-white font-grey">4</div>
                        <div class="mt-step-title uppercase font-grey-cascade">Approved</div>
                        <div class="mt-step-content font-grey-cascade">SWMS accepted</div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="portlet light bordered">
                        <div class="portlet-title">
                            <div class="caption">
                                <i class="icon-layers"></i>
                                <span class="caption-subject bold uppercase font-green-haze"> Safe Work Method Statement</span>
                            </div>
                            <div class="actions">
                                @if (Auth::user()->allowed2('del.wms', $doc))
                                    <a class="btn btn-circle green btn-outline btn-sm" v-on:click="xx.showConfirmSignoff = true"
                                       v-show="! xx.docModified && xx.company.id == xx.user.company_id && xx.doc.principle_id && xx.doc.principle == xx.company.parent_name && xx.doc.res_compliance && xx.doc.res_review">
                                        <i class="fa fa-pencil-square-o"></i> Request Sign Off</a>
                                    <a href="/safety/doc/wms/{{ $doc->id }}/signoff" class="btn btn-circle green btn-outline btn-sm"
                                       v-show="! xx.docModified && xx.company.id == xx.user.company_id && (!xx.doc.principle_id || xx.doc.principle_id && xx.doc.principle != xx.company.parent_name) && xx.doc.res_compliance && xx.doc.res_review">
                                        <i class="fa fa-pencil-square-o"></i> Manual Sign Off</a>
                                @endif
                                <a v-show="xx.docModified" class="btn btn-circle green btn-outline btn-sm" v-on:click="saveDocumentDB"><i class="fa fa-save"></i> Save</a>
                                <a href="javascript:;" class="btn btn-circle btn-icon-only btn-default fullscreen"> </a>
                            </div>
                        </div>
                        <div class="portlet-body">
                            <div class="page-content-inner">
                                <div class="form-body">
                                    <!-- Company + Principle header -->
                                    <div v-show="xx.edit.item == 'p'+xx.doc.id" class="row" style="border-bottom: 1px solid #ccc">
                                        <!-- Edit Info -->
                                        <div class="col-xs-7 hidden-sm hidden-xs"><h1 style="margin: 0 0 25px 0"><b>@{{ xx.company.name }}</b></h1></div>
                                        <div class="col-xs-12 visible-sm visible-xs text-center"><h1 style="margin: 0 0 25px 0"><b>@{{ xx.company.name }}</b></h1></div>
                                        <!-- Fullscreen devices -->
                                        <div class="col-xs-5 hidden-sm hidden-xs">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group">Principal Contractor</br><input v-model="xx.edit.prin" class="form-control" type="text"></div>
                                                </div>
                                                <div class="col-md-6"><br>
                                                    <button type="submit" class="btn green pull-right" style="margin-left: 10px" v-on:click="savePrinciple(xx.doc, false)"> Save</button>
                                                    <button type="button" class="btn default pull-right" v-on:click="cancelEdit"> Cancel</button>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- Mobile Device -->
                                        <div class="col-xs-12 visible-sm visible-xs">
                                            <div class="row">
                                                <div class="col-xs-12">
                                                    <div class="form-group">
                                                        <label>Principal Contractor</label>
                                                        <input v-model="xx.edit.prin" class="form-control" type="text">
                                                    </div>
                                                </div>
                                                <div class="col-xs-12">
                                                    <button type="submit" class="btn green pull-right" style="margin-left: 10px" v-on:click="savePrinciple(xx.doc, false)"> Save</button>
                                                    <button type="button" class="btn default pull-right" v-on:click="cancelEdit"> Cancel</button>
                                                    <br><br>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="hidden-sm hidden-xs">
                                            <div class="col-xs-7"><h3 style="margin: 0 0 2px 0">Safe Work Method Statement</h3></div>
                                            <div class="col-xs-5 text-right" style="margin-top: 5px"><span class="font-grey-salsa">version @{{ xx.doc.version }} </span></div>
                                        </div>
                                    </div>

                                    <!-- Show Info -->
                                    <div v-show="xx.edit.item != 'p'+xx.doc.id">
                                        <!-- Fullscreen devices -->
                                        <div class="row hidden-sm hidden-xs" style="border-bottom: 1px solid #ccc">
                                            <div class="col-xs-12">
                                                <h3 v-show="xx.docModified" class="font-red uppercase pull-right" style="margin-top: 0">unsaved</h3>
                                            </div>
                                            <div class="col-xs-7">
                                                <h1 style="margin: 0 0 25px 0"><b>@{{ xx.company.name }}</b></h1>
                                            </div>
                                            <div v-if="!xx.doc.master" class="col-xs-5 hoverDiv text-right" style="padding-right: 20px" v-on:click="editPrinciple(xx.doc)"><b>Principal
                                                    Contractor:</b> @{{ xx.doc.principle }}
                                            </div>
                                            <div v-else class="col-xs-5 text-right font-red">
                                                <h2>TEMPLATE</h2>
                                            </div>
                                            <div>
                                                <div class="col-xs-10"><h3 style="margin: 0 0 2px 0">Safe Work Method Statement</h3></div>
                                                <div class="col-xs-2 text-right" style="margin-top: 5px; padding-right: 20px"><span class="font-grey-salsa">version @{{ xx.doc.version }} </span></div>
                                            </div>
                                        </div>
                                        <!-- Mobile devices -->
                                        <div class="row visible-sm visible-xs">
                                            <div class="col-xs-12"><h3 v-show="xx.docModified" class="font-red uppercase text-center" style="margin-top: 0">unsaved</h3></div>
                                            <div class="col-xs-12 text-center">
                                                <h3 style="margin: 0 0 25px 0"><b>@{{ xx.company.name }}</b></h3>
                                            </div>
                                            <div v-if="!xx.doc.master" class="col-xs-12 hoverDiv" v-on:click="editPrinciple(xx.doc)">
                                                <div class="col-xs-6 text-right"><b>Principal Contractor:</b></div>
                                                <div class="col-xs-6">@{{ xx.doc.principle }}</div>
                                            </div>
                                            <div v-else class="col-xs-12 font-red">
                                                <h2>TEMPLATE</h2>
                                            </div>
                                        </div>
                                    </div>


                                    <!-- Doc Name + Project -->
                                    <div class="hoverDiv">
                                        <!-- Edit Info -->
                                        <div v-show="xx.edit.item == 'd'+xx.doc.id" class="row">
                                            <div class="col-md-7">
                                                <div class="form-group">
                                                    <label>Name of Work Activity / Task</label>
                                                    <input v-model="xx.edit.name" class="form-control" type="text">
                                                </div>
                                            </div>
                                            <div class="col-md-5">
                                                <div class="form-group">
                                                    <label>Project / Location</label>
                                                    <input v-model="xx.edit.prin" class="form-control" type="text">
                                                </div>
                                            </div>
                                        </div>
                                        <div v-show="xx.edit.item == 'd'+xx.doc.id" class="row">
                                            <div class="col-md-12">
                                                <button type="submit" class="btn green pull-right" style="margin-left: 10px" v-on:click="saveDoc(xx.doc, false)" :disabled="! (xx.edit.name)"> Save
                                                </button>
                                                <button type="button" class="btn default pull-right" v-on:click="cancelEdit"> Cancel</button>
                                            </div>
                                        </div>
                                        <!-- Show Info -->
                                        <div v-show="xx.edit.item != 'd'+xx.doc.id" class="row" v-on:click="editDoc(xx.doc)">
                                            <!-- Fullscreen devices -->
                                            <div class="row hidden-sm hidden-xs">
                                                <div class="col-md-7 "><span class="pull-left" style="padding: 1px 20px 0px 10px">Activity / Task:</span>
                                                    <h4 style="margin: 0px"><b>@{{ xx.doc.name }}</b></h4>
                                                </div>
                                                <div class="col-xs-5 text-right" style="margin-top: 5px; padding-right: 20px"><b>Project / Location:</b> @{{ xx.doc.project }}</div>
                                            </div>
                                            <!-- Mobile devices -->
                                            <div class="row visible-sm visible-xs">
                                                <div class="col-xs-12 text-center">
                                                    <h4 style="margin: 0 0 25px 0"><b>@{{ xx.doc.name }}</b>
                                                        <small class="font-grey-salsa">v@{{ xx.doc.version }}</small>
                                                    </h4>
                                                </div>
                                                <div class="col-xs-6 text-right"><b>Project / Location:</b></div>
                                                <div class="col-xs-6">@{{ xx.doc.project }}</div>
                                            </div>
                                        </div>
                                    </div>
                                    <br>
                                    <!-- Builder Steps / Hazards / Controls -->
                                    <div v-show="xx.doc.builder" class="row">
                                        <div class="col-md-12">
                                            <div class="row hidden-sm hidden-xs"
                                                 style="border: 1px solid #e7ecf1; padding: 10px 0px; margin: 0px; background: #f0f6fa; font-weight: bold">
                                                <div class="col-md-2">Step</div>
                                                <div class="col-md-3">Potential Hazard</div>
                                                <div class="col-md-7">Controls / Responsible Person(s)</div>
                                            </div>
                                            <div class="visible-sm visible-xs" style="border: 1px solid #e7ecf1; padding: 10px 0px; background: #f0f6fa; font-weight: bold">
                                                <div class="col-md-12">Steps / Hazards / Controls</div>
                                            </div>
                                            <!--
                                                Steps
                                            -->
                                            <div v-show="xx.steps.length">
                                                <template v-for="step in xx.steps | orderBy 'order'">
                                                    <div class="row row-striped" style="border-bottom: 1px solid lightgrey; padding: 0px; margin: 0px;">
                                                        <div class="col-md-2">
                                                            <div class="hoverDiv" style="padding: 10px 0px 10px 0px">
                                                                <div v-show="xx.edit.item == 's'+step.id" style="margin-bottom: 25px">
                                                                    <textarea v-model="xx.edit.name" class="form-control" rows="5" style="background: #fff; id=">@{{ step.name }}</textarea>
                                                                    <div>
                                                                        <button v-show="step.name == xx.edit.name" class="btn btn-xs red pull-right" v-on:click="deleteStep(step)">Delete
                                                                        </button>
                                                                        <button v-show="step.name != xx.edit.name" class="btn btn-xs green pull-right" v-on:click="saveStep(step)"
                                                                                :disabled="! (xx.edit.name)">
                                                                            Save &nbsp;
                                                                        </button>
                                                                        <button class="btn btn-xs default pull-right" v-on:click="cancelEdit">Cancel</button>
                                                                        <button class="btn btn-xs default pull-left" v-on:click="orderStep(step, '-')"><i class="fa fa-chevron-up"></i></button>
                                                                        <button class="btn btn-xs default pull-left" v-on:click="orderStep(step, '+')"><i class="fa fa-chevron-down"></i></button>
                                                                    </div>
                                                                </div>
                                                                <div v-else>
                                                                    <div class="row" v-on:click="editStep(step)">
                                                                        <div class="col-xs-2 hidden-sm hidden-xs">@{{ step.order }}.</div>
                                                                        <div class="col-xs-10 hidden-sm hidden-xs">
                                                                            <span v-show="step.diff==0 || !xx.user.signoff""> @{{{ step.name | nl2br }}}</span>
                                                                            <span v-show="step.diff==1" style="color:#FF0000"> @{{{ step.name | nl2br }}}</span>
                                                                        </div>
                                                                        <div class="col-xs-12 visible-sm visible-xs font-white text-center"
                                                                             style="background: #659be0; padding:5px"><b>Step @{{ step.order }}.</b> &nbsp;
                                                                            <span v-show="step.diff==0 || !xx.user.signoff""> @{{{ step.name | nl2br }}}</span>
                                                                            <span v-show="step.diff==1" style="color:#FF0000"> @{{{ step.name | nl2br }}}</span>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="" style="padding: 10px 20px 20px 20px">
                                                                <button type="button" class="btn btn-xs btn-default font-grey-cascade" v-on:click="addStep(step.order)"><i class="fa fa-plus"></i> Step
                                                                </button>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <wms-hazards :step_id="step.id"></wms-hazards>
                                                            <div class="col-md-12" style="padding: 10px 20px 20px 20px">
                                                                <button type="button" class="btn btn-xs btn-default font-grey-cascade" v-on:click="addHazard(step.id)"><i class="fa fa-plus"></i> Hazard
                                                                </button>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-7">
                                                            <wms-controls :step_id="step.id"></wms-controls>
                                                            <div class="col-md-12" style="padding: 10px 20px 20px 20px">
                                                                <button type="button" class="btn btn-xs btn-default font-grey-cascade" v-on:click="addControl(step.id)"><i class="fa fa-plus"></i>
                                                                    Control
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </template>
                                            </div>
                                            <div v-show="!xx.steps.length" class="row">
                                                <div class="col-md-6" style="padding: 30px;">
                                                    <button type="button" class="btn btn-sm btn-outline blue" v-on:click="addStep(0)"><i class="fa fa-plus"></i> Step</button>
                                                    <span style="padding-left: 30px">No steps found</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div v-show="!xx.doc.builder" class="row">
                                        <hr>
                                        <br><br>
                                        <!-- File upload -->
                                        <div class="col-md-2">&nbsp;</div>
                                        <div class="col-md-1">
                                            <div style="min-height: 100px">
                                                <a v-show="xx.doc.attachment" href="/filebank/company/@{{ xx.doc.for_company_id }}/wms/@{{ xx.doc.attachment }}"><i class="fa fa-bold fa-file-pdf-o"
                                                                                                                                                                    style="font-size: 5em; margin-top:20px"></i></a>
                                                <i v-else class="fa fa-bold fa-chain-broken" style="font-size: 5em; margin-top:20px"></i>
                                            </div>
                                        </div>
                                        <div v-show="xx.edit.item != 'f'+xx.doc.id" class="col-md-6">
                                            <div style="min-height: 100px">
                                                <button v-on:click="editFile(xx.doc)" class="btn btn-lg blue">Change File</button>
                                            </div>
                                        </div>
                                        <form id="fileform" name="fileform" enctype="multipart/form-data">
                                            <div class="col-md-6">
                                                <div v-show="xx.edit.item == 'f'+xx.doc.id" class="form-group">
                                                    <div class="input-group">
                                                        <input type="text" class="form-control" readonly>
                                                        <label class="input-group-btn"><span class="btn blue"><i class="fa fa-folder-open"></i> Browse</span>
                                                            <input type="file" name="attachment" style="display: none;" id="attachment"></label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <button v-show="xx.edit.item == 'f'+xx.doc.id" v-on:click="saveFile" class="btn green pull-right" style="margin-left: 10px">Save</button>
                                                <button v-show="xx.edit.item == 'f'+xx.doc.id" v-on:click.prevent="cancelEdit" class="btn default pull-right"> Cancel</button>
                                            </div>
                                        </form>
                                    </div>
                                    <!-- Person Responsible Info -->
                                    <!-- Show Info -->
                                    <div class="row" v-if="!xx.doc.master">
                                        <div class="row" style="padding: 5px">
                                            <div class="col-md-6 text-right" style="padding-top: 5px;" v-bind:class="{ 'font-red': !xx.doc.res_compliance }"><b>Person responsible for ensuring
                                                    compliance with SWMS: <span v-if="!xx.doc.res_compliance"> ** REQUIRED **</span></b></b></div>
                                            <div class="col-md-6" style="padding-right: 20px"><input v-model="xx.doc.res_compliance" class="form-control" type="text"
                                                                                                     v-on:click="xx.docModified = true"></div>
                                        </div>
                                        <div class="row" style="padding: 5px">
                                            <div class="col-md-6 text-right" style="padding-top: 5px" v-bind:class="{ 'font-red': !xx.doc.res_review }"><b>Person responsible for reviewing SWMS control
                                                    measures: <span v-if="!xx.doc.res_review"> ** REQUIRED **</span></b></div>
                                            <div class="col-md-6" style="padding-right: 20px"><input v-model="xx.doc.res_review" class="form-control" type="text" v-on:click="xx.docModified = true">
                                            </div>
                                        </div>
                                    </div>
                                    <hr>
                                    <div class="pull-right" style="min-height: 50px">
                                        <a href="/safety/doc/wms" class="btn default"> Back</a>
                                        <button type="submit" class="btn dark" v-on:click="saveDocumentDB"> Save Draft</button>
                                        @if ($doc->master && Auth::user()->hasPermission2('edit.wms'))
                                            <button type="submit" class="btn green" v-on:click="saveActiveDB"> Make Active</button>
                                        @endif
                                        @if (!$doc->master)
                                            <a v-on:click="showConfirmSignoff" type="button" class="btn green" data-dismiss="modal" id="continue" v-show="!xx.user.signoff && xx.doc.res_compliance && xx.doc.res_review">Request Sign Off</a>
                                            <a href="/safety/doc/wms/{{ $doc->id }}/signoff" class="btn green" v-show="xx.user.signoff == 'manual' && xx.doc.res_compliance && xx.doc.res_review && !xx.docModified">Manual Sign Off</a>
                                            <a href="/safety/doc/wms/{{ $doc->id }}/archive" class="btn red" v-show="xx.user.signoff == 1 && xx.doc.res_compliance && xx.doc.res_review && !xx.docModified">Archive</a>
                                            <a href="/safety/doc/wms/{{ $doc->id }}/signoff" class="btn green" v-show="xx.user.signoff == 1 && xx.doc.res_compliance && xx.doc.res_review && !xx.docModified">Sign Off</a>
                                        @endif
                                    </div>
                                    <br><br>
                                </div>
                                <!--<pre>@{{ $data | json }}</pre>
                                -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div>
                <div class="pull-right" style="font-size: 12px; font-weight: 200; padding: 10px 10px 0 0">
                    {!! $doc->displayUpdatedBy() !!}
                </div>
            </div>

            <!--
               Confirm Signoff Modal
             -->
            <confirm-signoff :show.sync="xx.showConfirmSignoff" effect="fade" :width="350">
                <div slot="modal-header" class="modal-header">
                    <h4 class="modal-title text-center"><b>Confirm Sign Off Request</b></h4>
                </div>
                <div slot="modal-body" class="modal-body">
                    <p class="text-center">You are about leave DRAFT mode and request<br><b>@{{ xx.doc.principle }}</b><br>to sign off.</p>
                    <p class="font-red text-center"><i class="fa fa-exclamation-triangle"></i> You will no longer be able to modify this SWMS anymore.</p>
                </div>
                <div class="modal-body">

                </div>
                <div slot="modal-footer" class="modal-footer">
                    <button type="button" class="btn btn-default" v-on:click="xx.showConfirmSignoff = false">Cancel</button>
                    <a href="/safety/doc/wms/{{ $doc->id }}/signoff" class="btn btn-success">Confirm</a>
                </div>
            </confirm-signoff>

            <!--
               Confirm Principle Modal
             -->
            <confirm-principle :show.sync="xx.showConfirmPrinciple" effect="fade" :width="350">
                <div slot="modal-header" class="modal-header">
                    <h4 class="modal-title text-center"><b>Confirm Principal Contractor</b></h4>
                </div>
                <div slot="modal-body" class="modal-body">
                    <p class="text-center">As the Principal Contractor is not <b>@{{ xx.company.parent_name }}</b> you will need to manually get the Principal Contractor to <u>sign off</u> on
                        your Work Method Statement.</p>
                </div>
                <div slot="modal-footer" class="modal-footer">
                    <button type="button" class="btn btn-default" v-on:click="cancelEdit">Cancel</button>
                    <button type="button" class="btn btn-success" v-on:click="savePrinciple(xx.doc, true)">Confirm</button>
                </div>
            </confirm-principle>

            <!--
               Incomplete Modal
             -->
            <incomplete-form :show.sync="xx.showIncomplete" effect="fade" :width="350">
                <div slot="modal-header" class="modal-header">
                    <h4 class="modal-title text-center"><b>Incomplete SWMS</b></h4>
                </div>
                <div slot="modal-body" class="modal-body">
                    <p class="text-center">The following fields are required:</p>
                    <p class="text-center font-red" v-show="!xx.doc.res_compliance">Person responsible for ensuring compliance with SWMS</p>
                    <p class="text-center font-red" v-show="!xx.doc.res_review">Person responsible for reviewing SWMS control measures</p>
                </div>
                <div slot="modal-footer" class="modal-footer">
                    <button type="button" class="btn" v-on:click="xx.showIncomplete = false">Ok</button>
                </div>
            </incomplete-form>
    </template>

    <!--
        Hazards
    -->
    <template id="hazard-template">
        <span class="visible-sm visible-xs"><b>Hazards:</b><br></span>
        <ul style="margin-left: -15px">
            <li v-for="hazard in xx.hazards | filterStep | orderBy 'order'" class="hoverLi">
                <div v-show="xx.edit.item == 'h'+hazard.id" style="margin-bottom: 25px;">
                    <textarea v-model="xx.edit.name" class="form-control" rows="4" style="background:#fff">@{{ hazard.name }} @{{hazard.diff}}</textarea>
                    <div>
                        <button v-show="hazard.name == xx.edit.name" class="btn btn-xs red pull-right" v-on:click="deleteHazard(hazard)">Delete</button>
                        <button v-show="hazard.name != xx.edit.name" class="btn btn-xs green pull-right" v-on:click="saveHazard(hazard)" :disabled="! (xx.edit.name)"> Save &nbsp;</button>
                        <button class="btn btn-xs default pull-right" v-on:click="cancelEdit">Cancel</button>
                        <button class="btn btn-xs default pull-left" v-on:click="orderHazard(hazard, '-')"><i class="fa fa-chevron-up"></i></button>
                        <button class="btn btn-xs default pull-left" v-on:click="orderHazard(hazard, '+')"><i class="fa fa-chevron-down"></i></button>
                    </div>
                </div>
                <div v-else>
                    <div v-on:click="editHazard(hazard)">
                        <span v-show="hazard.diff==0 || !xx.user.signoff"> @{{{ hazard.name | nl2br }}}</span>
                        <span v-show="hazard.diff==1" style="color:#FF0000"> @{{{ hazard.name | nl2br }}}</span>
                    </div>
                </div>
            </li>
        </ul>
    </template>

    <!--
        Controls
    -->
    <template id="control-template">
        <span class="visible-sm visible-xs"><b>Controls:</b><br></span>
        <ul style="margin-left: -15px">
            <li v-for="control in xx.controls | filterStep | orderBy 'order'" style="clear:both" class="hoverLi">
                <div v-show="xx.edit.item == 'c'+control.id" style="margin-bottom: 25px; background-color: #f5f5f5">
                    <div style="float:right; width:30%; padding: 0 0 0 20px">
                        <div class="mt-checkbox-list">
                            <label class="mt-checkbox mt-checkbox-outline">
                                <input v-model="xx.edit.prin" type="checkbox" v-bind:true-value="1" v-bind:false-value="0"> Principal
                                <span></span>
                            </label><br>
                            <label class="mt-checkbox mt-checkbox-outline">
                                <input v-model="xx.edit.comp" type="checkbox" v-bind:true-value="1" v-bind:false-value="0"> Company
                                <span></span>
                            </label><br>
                            <label class="mt-checkbox mt-checkbox-outline">
                                <input v-model="xx.edit.work" type="checkbox" v-bind:true-value="1" v-bind:false-value="0"> Worker
                                <span></span>
                            </label>
                        </div>
                    </div>
                    <div style="width:65%; padding: 0 20px 0 0">
                        <textarea v-model="xx.edit.name" class="form-control" rows="5" style="background:#fff">@{{ control.name }}</textarea>
                        <div>
                            <button v-show="control.name == xx.edit.name && control.res_worker == xx.edit.work && control.res_company == xx.edit.comp && control.res_principle == xx.edit.prin"
                                    class="btn btn-xs red pull-right" v-on:click="deleteControl(control)">Delete
                            </button>
                            <button v-show="control.name != xx.edit.name || control.res_worker != xx.edit.work || control.res_company != xx.edit.comp || control.res_principle != xx.edit.prin"
                                    class="btn btn-xs green pull-right" v-on:click="saveControl(control)" :disabled="! (xx.edit.name)">Save
                            </button>
                            <button class="btn btn-xs default pull-right" v-on:click="cancelEdit">Cancel</button>
                            <button class="btn btn-xs default pull-left" v-on:click="orderControl(control, '-')"><i class="fa fa-chevron-up"></i></button>
                            <button class="btn btn-xs default pull-left" v-on:click="orderControl(control, '+')"><i class="fa fa-chevron-down"></i></button>
                        </div>
                    </div>
                </div>
                <div v-else>
                    <div v-on:click="editControl(control)">
                        <span v-show="control.diff==0 || !xx.user.signoff""> @{{{ control.name | nl2br }}}</span>
                        <span v-show="control.diff==1" style="color:#FF0000"> @{{{ control.name | nl2br }}}</span>
                        &nbsp; &nbsp;<span v-show="control.res_principle || control.res_company || control.res_worker"
                                           class="font-blue"><b>By: @{{ responsibleName(control) }}</b>
                    </div>
                </div>
            </li>
        </ul>
    </template>

@stop <!-- END Content -->


@section('page-level-plugins-head')
    <link href="/assets/global/plugins/bootstrap-fileinput/bootstrap-fileinput.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/bootstrap-select/css/bootstrap-select.min.css" rel="stylesheet" type="text/css"/>
    <link href="/css/libs/fileinput.min.css" media="all" rel="stylesheet" type="text/css"/>
@stop

@section('page-level-plugins')
    <script src="/assets/global/plugins/bootstrap-select/js/bootstrap-select.min.js" type="text/javascript"></script>
    <script src="/js/libs/fileinput.min.js"></script>
    <script src="/assets/global/plugins/bootstrap/js/bootstrap.min.js" type="text/javascript"></script>
@stop

@section('page-level-scripts')
    {{-- Metronic + custom Page Scripts --}}
    <script src="/assets/pages/scripts/components-bootstrap-select.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/moment.min.js" type="text/javascript"></script>

    <!-- Vue -->
    <script src="/js/libs/vue.1.0.24.js" type="text/javascript"></script>
    <script src="/js/libs/vue-strap.min.js"></script>
    <script src="/js/libs/vue-resource.0.7.0.js" type="text/javascript"></script>
    <script src="/js/vue-app-wms.js"></script>
    <script>
        /* File Upload */
        // We can attach the `fileselect` event to all file inputs on the page

        $(document).on('change', ':file', function () {
            var input = $(this), numFiles = input.get(0).files ? input.get(0).files.length : 1, label = input.val().replace(/\\/g, '/').replace(/.*\//, '');
            input.trigger('fileselect', [numFiles, label]);
        });

        $(':file').on('fileselect', function (event, numFiles, label) {
            var input = $(this).parents('.input-group').find(':text'), log = numFiles > 1 ? numFiles + ' files selected' : label;

            if (input.length) {
                input.val(log);
            } else {
                if (log) alert(log);
            }

        });
    </script>
@stop

