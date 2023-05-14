@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        @if (Auth::user()->hasAnyPermissionType('site'))
            <li><a href="/site">Sites</a><i class="fa fa-circle"></i></li>
        @endif
        <li><a href="/site/qa">Quality Assurance</a><i class="fa fa-circle"></i></li>
        <li><span>View Report</span></li>
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
                            <span class="caption-subject bold uppercase font-green-haze"> Quality Assurance Report</span>
                            <span class="caption-helper">ID: {{ $qa->id }}</span>
                        </div>
                    </div>
                    <div class="portlet-body">
                        <div class="page-content-inner">
                            <input v-model="xx.qa.id" type="hidden" id="qa_id" value="{{ $qa->id }}">
                            <input v-model="xx.qa.name" type="hidden" id="qa_name" value="{{ $qa->name }}">
                            <input v-model="xx.qa.site_id" type="hidden" id="qa_site_id" value="{{ $qa->site_id }}">
                            <input v-model="xx.qa.status" type="hidden" id="qa_status" value="{{ $qa->status }}">
                            <input v-model="xx.qa.master" type="hidden" id="qa_master" value="{{ $qa->master }}">
                            <input v-model="xx.qa.signoff" type="hidden" id="qa_signoff" value="{{ ($qa->supervisor_sign_by || $qa->manager_sign_by) ? '1' : '0' }}">
                            <input v-model="xx.qa.open" type="hidden" id="qa_open" value="{{ ($qa->allDocs(1)->count()) }}">
                            <input v-model="xx.table_id" type="hidden" id="table_id" value="{{ $qa->id }}">
                            <input v-model="xx.record_status" type="hidden" id="record_status" value="{{ $qa->status }}">
                            <input v-model="xx.user_id" type="hidden" id="user_id" value="{{ Auth::user()->id }}">
                            <input v-model="xx.user_fullname" type="hidden" id="fullname" value="{{ Auth::user()->fullname }}">
                            <input v-model="xx.company_id" type="hidden" id="company_id" value="{{ Auth::user()->company->reportsTo()->id }}">
                            <input v-model="xx.user_supervisor" type="hidden" id="user_supervisor" value="{{ Auth::user()->allowed2('edit.site.qa', $qa) }}">
                            <input v-model="xx.user_manager" type="hidden" id="user_manager"
                                   value="{!! (!$qa->master && in_array(Auth::user()->id, $qa->site->areaSupervisors()->pluck('id')->toArray())) ? 1 : 0  !!}">
                            <input v-model="xx.user_signoff" type="hidden" id="user_signoff" value="{{ Auth::user()->hasPermission2('del.site.qa') }}">
                            <input v-model="xx.user_edit" type="hidden" id="user_edit" value="{{ Auth::user()->allowed2('edit.site.qa', $qa) }}">

                            <!-- Fullscreen devices -->
                            @if ($qa->status && $qa->items->count() == $qa->itemsCompleted()->count())
                                <div class="col-md-12 note note-warning">
                                    <p>All items have been completed and report requires
                                        <button class="btn btn-xs btn-outline dark disabled">Sign Off</button>
                                        at the bottom
                                    </p>
                                </div>
                            @endif
                            <div class="row hidden-sm hidden-xs">
                                <div class="col-xs-7">
                                    <img src="/img/logo-capecod2-med.png">
                                </div>
                                <div class="col-xs-5">
                                    <p>JOB NAME: @if ($qa->site) {{ $qa->site->name }} @endif<br>
                                        ADDRESS: @if ($qa->site) {{ $qa->site->full_address }} @endif</p>
                                </div>
                            </div>
                            <div class="row" style="padding-top: 10px">
                                <div class="col-xs-12 ">
                                    <br>
                                    <h2 style="margin: 0px"><b>{{ $qa->name }}</b>
                                        @if ($qa->master)
                                            <span class="pull-right font-red hidden-sm hidden-xs">TEMPLATE</span>
                                            <span class="text-center font-red visible-sm visible-xs">TEMPLATE</span>
                                        @else
                                            @if($qa->status == '-1')
                                                <span class="pull-right font-red hidden-sm hidden-xs">NOT REQUIRED</span>
                                                <span class="text-center font-red visible-sm visible-xs">NOT REQUIRED</span>
                                            @endif
                                            @if($qa->status == '0')
                                                <span class="pull-right font-red hidden-sm hidden-xs">COMPLETED {{ $qa->updated_at->format('d/m/Y') }}</span>
                                                <span class="text-center font-red visible-sm visible-xs">COMPLETED {{ $qa->updated_at->format('d/m/Y') }}</span>
                                            @endif
                                            @if($qa->status == '1' && Auth::user()->allowed2('edit.site.qa', $qa))
                                                <button v-if="xx.qa.status == 1 && xx.qa.items_done == 0" class="btn red pull-right" v-on:click="$root.$broadcast('updateReportStatus', '-1')"> Page Not
                                                    Required
                                                </button>
                                            @endif
                                            @if($qa->status == '2')
                                                <span class="pull-right font-red hidden-sm hidden-xs">ON HOLD</span>
                                                <span class="text-center font-red visible-sm visible-xs">ON HOLD</span>
                                            @endif
                                        @endif
                                    </h2>
                                </div>
                                <div class="col-xs-12 ">
                                    <p>Item Tasks: {{ $qa->tasksSBC() }}</p>
                                </div>
                            </div>

                            <!-- List QA -->
                            <div class="row">
                                <div class="col-md-12">
                                    <app-qa></app-qa>
                                </div>
                            </div>

                            @if (!$qa->master)
                                <div class="row">
                                    <div class="col-md-12">
                                        <!--<app-actions :doc_id="{{ $qa->id }}"></app-actions>-->
                                        <app-actions :table_id="{{ $qa->id }}"></app-actions>
                                    </div>
                                </div>

                                <hr>

                                {{-- Handover Check to ensure previous QA are completed --}}
                                @if ($qa->master_id == 2581 && $qa->allDocs(1)->count() > 1)
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="note note-warning">
                                                <b>Handover can't be Signed Off</b><br>This Handover QA can't be signed off by Site Supervisor/Manager until all other related Quality Assurnce documents for this site have been completed.
                                                <br><br>Below are a list of outstanding QA's that haven't been signed off yet:<br>
                                                <ul>
                                                    @foreach ($qa->allDocs(1) as $q)
                                                        @if ($qa->id != $q->id)
                                                            <li><a href="/site/qa/{{$q->id}}" target="_blank">{{ $q->name }}</a></li>
                                                        @endif
                                                    @endforeach
                                                </ul>
                                            </div>
                                            <p></p><br>
                                        </div>
                                    </div>
                                @else
                                    <div class="row">
                                        <div class="col-md-12">
                                            <h5><b>QUALITY ASSURANCE ELECTRONIC SIGN-OFF</b></h5>
                                            <p>The above inspection items have been checked by the site construction supervisor and conform to the Cape Cod standard set.</p>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-3 text-right">Site Supervisor:</div>
                                        <div class="col-sm-9">
                                            @if ($qa->supervisor_sign_by)
                                                {!! \App\User::find($qa->supervisor_sign_by)->full_name !!}, &nbsp;{{ $qa->supervisor_sign_at->format('d/m/Y') }}
                                            @else
                                                <button v-if="xx.qa.items_total != 0 && xx.qa.items_done == xx.qa.items_total && xx.user_supervisor" v-on:click="$root.$broadcast('signOff', 'super')"
                                                        class=" btn blue btn-xs btn-outline sbold uppercase margin-bottom">Sign Off
                                                </button>
                                                <span v-if="xx.qa.items_total != 0 && xx.qa.items_done == xx.qa.items_total && !xx.user_supervisor" class="font-red">Pending</span>
                                                <span v-if="xx.qa.items_total != 0 && xx.qa.items_done != xx.qa.items_total" class="font-grey-silver">Waiting for items to be completed</span>
                                            @endif
                                        </div>
                                        <div class="col-sm-3 text-right">Site Manager:</div>
                                        <div class="col-sm-9">
                                            @if ($qa->manager_sign_by)
                                                {!! \App\User::find($qa->manager_sign_by)->full_name !!}, &nbsp;{{ $qa->manager_sign_at->format('d/m/Y') }}
                                            @else
                                                @if ($qa->supervisor_sign_by)
                                                    <button v-if="xx.qa.items_total != 0 && xx.qa.items_done == xx.qa.items_total && (xx.user_manager == 1 || xx.user_signoff)"
                                                            v-on:click="$root.$broadcast('signOff', 'manager')"
                                                            class=" btn blue btn-xs btn-outline sbold uppercase margin-bottom">Sign Off
                                                    </button>
                                                    <span v-if="xx.qa.items_total != 0 && xx.qa.items_done == xx.qa.items_total && xx.user_manager == 0 && !xx.user_signoff" class="font-red">Pending</span>
                                                @else
                                                    <span v-if="xx.qa.items_total != 0 && xx.qa.items_done == xx.qa.items_total" class="font-red">Waiting for Site Supervisor Sign Off</span>
                                                    <span v-if="xx.qa.items_total != 0 && xx.qa.items_done != xx.qa.items_total" class="font-grey-silver">Waiting for items to be completed</span>
                                                @endif
                                            @endif
                                        </div>
                                    </div>
                                @endif
                            @endif
                            <div class="row">
                                <div class="col-md-6 pull-right text-right" style="margin-top: 15px; padding-right: 20px">
                                    <span class="font-grey-salsa">
                                        <span class="font-grey-salsa" v-if="xx.qa.master == '0'">version {{ $qa->version }} </span>
                                        <span class="font-grey-salsa" v-if="xx.qa.master == '1'">Current version {{ $qa->version }}<br> {!! nl2br($qa->notes) !!}</span>
                                </div>
                            </div>
                            <hr>
                            <div class="pull-right" style="min-height: 50px">
                                @if ($qa->master)
                                    <a href="/site/qa/templates" class="btn default"> Back</a>
                                @else
                                    <a href="/site/qa" class="btn default"> Back</a>
                                @endif
                                @if (!$qa->master && Auth::user()->allowed2('edit.site.qa', $qa))
                                    <button v-if="xx.qa.status == 1 && xx.qa.items_total != 0 && xx.qa.items_done != xx.qa.items_total" class="btn blue"
                                            v-on:click="$root.$broadcast('updateReportStatus', 2)"> Place On Hold
                                    </button>
                                    <button v-if="xx.qa.status == 2 || xx.qa.status == -1 " class="btn green" v-on:click="$root.$broadcast('updateReportStatus', 1)"> Make Active</button>
                                @endif
                            </div>
                            <br><br>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <pre v-if="xx.dev">@{{ $data | json }}</pre>
    -->

    <!-- loading Spinner -->
    <div v-show="xx.spinner" style="background-color: #FFF; padding: 20px;">
        <div class="loadSpinnerOverlay">
            <div class="loadSpinner"><i class="fa fa-spinner fa-pulse fa-2x fa-fw margin-bottom"></i> Loading...</div>
        </div>
    </div>

    <template id="qa-template">
        <!-- QA Items -->
        <table v-show="xx.itemList.length" class="table table-striped table-bordered table-nohover order-column">
            <thead>
            <tr class="mytable-header">
                <th width="5%"></th>
                <th> Maintenance Item</th>
                <th width="15%"> Checked Date</th>
            </tr>
            </thead>
            <tbody>
            <template v-for="item in xx.itemList | orderBy item.order">
                <tr class="@{{ textColour(item)  }}">
                    {{-- checkbox --}}
                    <td class="text-center" style="padding-top: 15px">
                        <span v-if="xx.qa.master == '1'">@{{ item.order }}.</span>
                        <span v-if="xx.qa.master == '0' && item.status == '-1'">N/A</span>
                        <i v-if="xx.qa.master == '0' && item.sign_by" class="fa fa-check-square-o font-green" style="font-size: 20px; padding-top: 5px"></i>
                        <i v-if="xx.qa.master == '0' && !item.sign_by && !item.status" class="fa fa-square-o font-red" style="font-size: 20px; padding-top: 5px"></i>
                    </td>
                    {{-- Item --}}
                    <td style="padding-top: 15px;">
                        @{{ item.name }} <span class="font-grey-silver">(@{{ item.task_code }})</span>
                        <div v-if="item.done_by">
                            <small v-if="item.status == '0' || item.status == ''">
                                @if (Auth::user()->allowed2('edit.site.qa', $qa))
                                    <a v-on:click="itemCompany(item)">@{{ item.done_by_company }} (licence. @{{ item.done_by_licence }})</a>
                                @else
                                    @{{ item.done_by_company }} (licence. @{{ item.done_by_licence }})
                                @endif
                            </small>
                            <small v-if="item.status == '1' ">@{{ item.done_by_company }} (licence. @{{ item.done_by_licence }}) &nbsp;
                                <a v-if="xx.user_signoff && xx.qa.status != 0" v-on:click="itemCompany(item)"> <i class="fa fa-pencil-square-o font-blue"> Edit</i></a>
                            </small>
                        </div>
                        <div v-else>
                            <small v-if="xx.qa.master == '0' && item.super == '0' && (item.status == '0' || item.status == '')">
                                @if (Auth::user()->allowed2('edit.site.qa', $qa))
                                    <a v-on:click="itemCompany(item)">Assign company</a>
                                @endif
                            </small>

                            <small v-if="xx.qa.master == '0' && item.super == '1' && (item.status == '0' || item.status == '')">To be completed by Supervisor</small>
                            <small v-if="xx.qa.master == '0' && item.super == '1' && item.status == '1'">@{{ item.sign_by_name }}</small>
                        </div>
                    </td>
                    {{-- Sign off --}}
                    <td>
                        @if (!$qa->master)
                            <div v-if="item.sign_by">
                                @{{ item.sign_at | formatDate }}<br>@{{ item.sign_by_name }}
                                <a v-if="xx.qa.status != 0 && xx.qa.signoff != 1" v-on:click="itemStatusReset(item)"><i class="fa fa-times font-red"></i></a>
                            </div>
                            <div v-else>
                                @if (!$qa->isSigned() && Auth::user()->allowed2('edit.site.qa', $qa))
                                    <select v-if="item.done_by || item.super" v-model="item.status" class='form-control' v-on:change="itemStatus(item)">
                                        <option v-for="option in xx.sel_checked" value="@{{ option.value }}" selected="@{{option.value == item.status}}">@{{ option.text }}</option>
                                    </select>
                                    <select v-else v-model="item.status" class='form-control' v-on:change="itemStatus(item)">
                                        <option v-for="option in xx.sel_checked2" value="@{{ option.value }}" selected="@{{option.value == item.status}}">@{{ option.text }}</option>
                                    </select>
                                @endif
                            </div>
                        @else
                            <div class="text-center">
                                <i v-if="item.super" class="fa fa-check-square-o" style="font-size: 20px; padding-top: 5px"></i>
                                <i v-if="!item.super" class="fa fa-square-o" style="font-size: 20px; padding-top: 5px"></i>
                            </div>
                        @endif
                    </td>
                </tr>
            </template>
            </tbody>
        </table>
        <!--
        Confirm Item Checked Modal
        -->
        <confirm-Signoff :show.sync="xx.showSignOff" effect="fade">
            <div slot="modal-header" class="modal-header">
                <h4 class="modal-title text-center"><b>Update Item Company</b></h4>
            </div>
            <div slot="modal-body" class="modal-body">
                <p><b>@{{ xx.record.name }}</b></p>
                Completed by
                <div class="row" style="padding-bottom: 10px">
                    <div class="col-md-7">
                        <select-picker :name.sync="xx.done_by" :options.sync="xx.sel_company" :function="doNothing"></select-picker>
                    </div>
                    <div class="col-md-5">
                        Assign to all unassigned
                        <select v-model="xx.done_by_all" class='form-control bs-select'>
                            <option value="1" selected>Yes</option>
                            <option value="0">No</option>
                        </select>
                    </div>
                </div>
                <div v-show="xx.done_by == 1">

                    <div class="row" style="padding-bottom: 10px">
                        <div class="col-md-7">
                            Specify other company
                            <input v-model="xx.done_by_other" type="text" class="form-control">
                        </div>
                    </div>
                </div>
            </div>
            <div slot="modal-footer" class="modal-footer">
                <button type="button" class="btn dark btn-outline" v-on:click="xx.showSignOff = false">&nbsp; No &nbsp;</button>
                <button type="button" class="btn btn-success" v-on:click="updateItemCompany(xx.record, true)" :disabled="(!xx.done_by || (xx.done_by == 1 && !xx.done_by_other))">&nbsp; Save &nbsp;</button>
            </div>
        </confirm-Signoff>
    </template>


    <template id="actions-template">
        <action-modal></action-modal>
        <input v-model="xx.table_id" type="hidden" id="table_id" value="{{ $qa->id }}">
        <input v-model="xx.created_by" type="hidden" id="created_by" value="{{ Auth::user()->id }}">
        <input v-model="xx.created_by_fullname" type="hidden" id="fullname" value="{{ Auth::user()->fullname }}">

        <div class="page-content-inner">
            <div class="row">
                <div class="col-md-12">
                    <h3>Notes
                        {{-- Show add if user has permission to edit hazard --}}
                        @if (Auth::user()->allowed2('edit.site.qa', $qa))
                            <button v-show="xx.record_status == '1'" v-on:click="$root.$broadcast('add-action-modal')" class="btn btn-circle green btn-outline btn-sm pull-right" data-original-title="Add">Add</button>
                        @endif
                    </h3>
                    <table v-show="actionList.length" class="table table-striped table-bordered table-nohover order-column">
                        <thead>
                        <tr class="mytable-header">
                            <th width="10%">Date</th>
                            <th> Action</th>
                            <th width="20%"> Name</th>
                            <th width="5%"></th>
                        </tr>
                        </thead>
                        <tbody>
                        <template v-for="action in actionList">
                            <tr>
                                <td>@{{ action.niceDate }}</td>
                                <td>@{{ action.action }}</td>
                                <td>@{{ action.fullname }}</td>
                                <td>
                                    <!--<button v-show="xx.record_status != 0" class=" btn blue btn-xs btn-outline sbold uppercase margin-bottom">
                                        <i class="fa fa-plus"></i> <span class="hidden-xs hidden-sm>"> Assign Task</span>
                                    </button>-->
                                    <!--
                                    <button v-show="action.created_by == xx.created_by" v-on:click="$root.$broadcast('edit-action-modal', action)"
                                            class=" btn blue btn-xs btn-outline sbold uppercase margin-bottom">
                                        <i class="fa fa-pencil"></i> <span class="hidden-xs hidden-sm>">Edit</span>
                                    </button>
                                    -->
                                </td>
                            </tr>
                        </template>
                        </tbody>
                    </table>

                    <!--<pre v-if="xx.dev">@{{ $data | json }}</pre> -->

                </div>
            </div>
        </div>
    </template>

    @include('misc/actions-modal')

@stop


@section('page-level-plugins-head')
@stop

@section('page-level-plugins')
    <script src="/js/moment.min.js" type="text/javascript"></script>
@stop

@section('page-level-scripts') {{-- Metronic + custom Page Scripts --}}
<script src="/js/libs/vue.1.0.24.js " type="text/javascript"></script>
<script src="/js/libs/vue-strap.min.js"></script>
<script src="/js/libs/vue-resource.0.7.0.js " type="text/javascript"></script>
<script src="/js/vue-modal-component.js"></script>
<script src="/js/vue-app-basic-functions.js"></script>
<!--<script src="/js/vue-app-qa.js"></script>-->
<script>
    var xx = {
        dev: dev,
        qa: {id: '', name: '', site_id: '', status: '', items_total: 0, items_done: 0, signoff: 0, open: 0},
        spinner: false, showSignOff: false, showAction: false,
        record: {},
        action: '', loaded: false,
        table_name: 'site_qa', table_id: '', record_status: '', record_resdate: '',
        created_by: '', created_by_fullname: '',
        done_by: '', done_by_other: '', done_by_all : '',
        itemList: [],
        actionList: [], sel_checked: [], sel_checked2: [], sel_company: [],
    };

    //
    // QA Items
    //
    Vue.component('app-qa', {
        template: '#qa-template',

        created: function () {
            this.getQA();
        },
        data: function () {
            return {xx: xx};
        },
        events: {
            'updateReportStatus': function (status) {
                this.xx.qa.status = status;
                this.updateReportDB(this.xx.qa, true);
            },
            'signOff': function (type) {
                this.xx.qa.signoff = type;
                this.updateReportDB(this.xx.qa, true);
            },
        },
        components: {
            confirmSignoff: VueStrap.modal,
        },
        filters: {
            formatDate: function (date) {
                return moment(date).format('DD/MM/YYYY');
            },
        },
        methods: {
            getQA: function () {
                this.xx.spinner = true;
                setTimeout(function () {
                    this.xx.load_plan = true;
                    $.getJSON('/site/qa/' + this.xx.qa.id + '/items', function (data) {
                        this.xx.itemList = data[0];
                        this.xx.sel_checked = data[1];
                        this.xx.sel_checked2 = data[2];
                        this.xx.spinner = false;
                        this.itemsCompleted();
                    }.bind(this));
                }.bind(this), 100);
            },
            itemsCompleted: function () {
                this.xx.qa.items_total = 0;
                this.xx.qa.items_done = 0;
                for (var i = 0; i < this.xx.itemList.length; i++) {
                    if (this.xx.itemList[i]['status'] == 1 || this.xx.itemList[i]['status'] == -1) {
                        this.xx.qa.items_done++;
                    }
                    this.xx.qa.items_total++;
                }
            },
            itemStatus: function (record) {
                if (record.status == '1') {
                    record.sign_at = moment().format('YYYY-MM-DD');
                    record.sign_by = this.xx.user_id;
                    record.sign_by_name = this.xx.user_fullname;
                }
                this.updateItemDB(record);
            },
            itemStatusReset: function (record) {
                record.status = '';
                record.sign_at = '';
                record.sign_by = '';
                record.sign_by_name = '';
                this.updateItemDB(record);
            },
            itemCompany: function (record) {
                this.xx.sel_company = [];
// Get Company list
                $.getJSON('/site/qa/company/' + record.task_id, function (companies) {
                    this.xx.sel_company = companies;
                    this.xx.done_by = record.done_by;
                    this.xx.done_by_other = record.done_by_other;
                    this.xx.showSignOff = true;
                    this.xx.record = record;

                }.bind(this));
            },
            updateItemCompany: function (record, response) {
                if (response) {
                    record.done_by = this.xx.done_by;
                    //alert('by:'+record.done_by);
                    if (this.xx.done_by != 1) {
                        // Get company name + licence from dropdown menu array
                        var company = objectFindByKey(this.xx.sel_company, 'value', record.done_by);
                        record.done_by_all = this.xx.done_by_other_all;
                        record.done_by_other = '';
                        record.done_by_company = company.text;
                        record.done_by_licence = company.licence;
                    } else {
                        //alert('other:'+this.xx.done_by_other);
                        record.done_by_all = this.xx.done_by_other_all;
                        record.done_by_other = this.xx.done_by_other;
                        record.done_by_company = this.xx.done_by_other;
                        record.done_by_licence = '??????';
                    }

                    // Get original item from list
                    var obj = objectFindByKey(this.xx.itemList, 'id', record.id);
                    obj = record;
                    this.updateItemDB(obj);
                }
                this.xx.record = {};
                this.xx.done_by = '';
                this.xx.done_by_other = '';
                this.xx.done_by_other_all = '';
                this.xx.showSignOff = false;
            },
            updateItemDB: function (record) {
//alert('update item id:'+record.id+' task:'+record.task_id+' by:'+record.done_by);
                this.$http.patch('/site/qa/item/' + record.id, record)
                        .then(function (response) {
                            this.itemsCompleted();
                            toastr.success('Updated record');
                        }.bind(this))
                        .catch(function (response) {
                            record.status = '';
                            record.sign_at = '';
                            record.sign_by = '';
                            record.sign_by_name = '';
                            alert('failed to update item');
                        });
            },
            updateReportDB: function (record, redirect) {
                this.$http.patch('/site/qa/' + record.id + '/update', record)
                        .then(function (response) {
                            this.itemsCompleted();
                            if (redirect)
                                window.location.href = '/site/qa/' + record.id;
                            toastr.success('Updated record');

                        }.bind(this)).catch(function (response) {
                    alert('failed to update report');
                });
            },
            textColour: function (record) {
                if (record.status == '-1')
                    return 'font-grey-silver';
                if (record.status == '0' && record.signed_by != '0' && !this.xx.qa.master)
                    return 'leaveBG';
                return '';
            },
            doNothing: function () {
//
            },
        },
    });


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
            'add-action-modal': function () {
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

                console.log(actiondata);
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

    //
    // QA Actions
    //
    /*
     Vue.component('app-actions', {
     template: '#actions-template',
     props: ['doc_id', 'status'],

     created: function () {
     this.getActions();
     },
     data: function () {
     return {xx: xx, showTradeModal: false};
     },
     events: {
     'addActionEvent': function (action) {
     this.xx.actionList.push(action);
     },
     },
     methods: {
     getActions: function () {
     $.getJSON('/site/qa/action/' + this.doc_id, function (actions) {
     this.xx.actionList = actions;
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
     'add-action-modal': function () {
     var newaction = {};
     this.oAction = '';
     this.action = newaction;
     this.action.doc_id = this.xx.qa.id;
     this.xx.action = 'add';
     this.xx.showAction = true;
     },
     'edit-action-modal': function (action) {
     this.oAction = action.action;
     this.action = action;
     this.xx.action = 'edit';
     this.xx.showAction = true;
     }
     },
     methods: {
     close: function () {
     this.xx.showAction = false;
     this.action.action = this.oAction;
     },
     addAction: function (action) {
     var actiondata = {
     action: action.action,
     doc_id: action.doc_id,
     niceDate: moment().format('DD/MM/YY'),
     created_by: this.xx.user_id,
     fullname: this.xx.user_fullname,
     };

     this.$http.post('/site/qa/action', actiondata)
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
     this.$http.patch('/site/qa/action/' + action.id, action)
     .then(function (response) {
     toastr.success('Saved Action');
     }.bind(this))
     .catch(function (response) {
     alert('failed to save action');
     });
     this.xx.showAction = false;
     },
     }
     });*/

    var myApp = new Vue({
        el: 'body',
        data: {xx: xx},
    });
</script>
@stop

