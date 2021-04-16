@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        <li><span>Compliance</span></li>
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
                <div class="portlet light ">
                    <div class="portlet-title">
                        <div class="caption">
                            <i class="icon-layers"></i>
                            <span class="caption-subject font-green-haze bold uppercase">Compliance</span>
                        </div>
                    </div>
                    <div class="portlet-body">
                        <!-- List Tips -->
                        <app-comply></app-comply>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- loading Spinner -->
    <div v-show="xx.spinner" style="background-color: #FFF; padding: 20px;">
        <div class="loadSpinnerOverlay">
            <div class="loadSpinner"><i class="fa fa-spinner fa-pulse fa-2x fa-fw margin-bottom"></i> Loading...</div>
        </div>
    </div>

    <template id="comply-template">
        <input v-model="xx.user_id" type="hidden" id="user_id" value="{{ Auth::user()->id }}">
        <input v-model="xx.user_fullname" type="hidden" id="fullname" value="{{ Auth::user()->fullname }}">
        <input v-model="xx.company_id" type="hidden" id="company_id" value="{{ Auth::user()->company->reportsTo()->id }}">

        <div class="row">
            <div class="col-md-3">
                <select-picker :name.sync="xx.reason" :options.sync="xx.sel_reasons" :function="updateReason"></select-picker>
            </div>
            <div class="col-md-3">
                <div v-if="xx.reason == 1">
                    <label class="mt-checkbox mt-checkbox-outline" style="margin-top: 5px"> Resolved
                        <input v-model="xx.status" type="checkbox" v-bind:true-value="1" v-bind:false-value="0"/>
                        <span></span>
                    </label>
                </div>
            </div>
            <div class="col-md-6">
                <div class="col-xs-3 text-right" style="margin-top: 5px">Search</div>
                <div class="col-xs-9 "><input v-model="xx.search" type="text" class="form-control"></div>
            </div>
        </div>

        {{--}}
        <div v-show="xx.list.length">
            <br>
            <div class="row" style="background-color: #f0f6fa; font-weight: bold; min-height: 40px; display: flex; align-items: center;">
                <div class="col-xs-2">
                    <a href="#" class="mytable-header-link" v-on:click="sortBy('date')"> Date</a>
                    <i v-if="xx.sortKey == 'date' && xx.sortOrder == '1'" class="fa fa-caret-down"></i>
                    <i v-if="xx.sortKey == 'date' && xx.sortOrder == '-1'" class="fa fa-caret-up"></i>
                </div>
                <div class="col-xs-2">
                    <a href="#" class="mytable-header-link" v-on:click="sortBy('site_name')"> Site</a>
                    <i v-if="xx.sortKey == 'site_name' && xx.sortOrder == '1'" class="fa fa-caret-down"></i>
                    <i v-if="xx.sortKey == 'site_name' && xx.sortOrder == '-1'" class="fa fa-caret-up"></i>
                </div>
                <div class="col-xs-2">
                    <a href="#" class="mytable-header-link" v-on:click="sortBy('user_name')"> Name</a>
                    <i v-if="xx.sortKey == 'user_name' && xx.sortOrder == '1'" class="fa fa-caret-down"></i>
                    <i v-if="xx.sortKey == 'user_name' && xx.sortOrder == '-1'" class="fa fa-caret-up">
                </div>
                <div class="col-xs-2">
                    <a href="#" class="mytable-header-link" v-on:click="sortBy('user_company')"> Company</a>
                    <i v-if="xx.sortKey == 'user_company' && xx.sortOrder == '1'" class="fa fa-caret-down"></i>
                    <i v-if="xx.sortKey == 'user_company' && xx.sortOrder == '-1'" class="fa fa-caret-up"></i>
                </div>
                <div class="col-xs-2">
                    <a href="#" class="mytable-header-link" v-on:click="sortBy('site_supers')"> Supervisor</a>
                    <i v-if="xx.sortKey == 'site_supers' && xx.sortOrder == '1'" class="fa fa-caret-down"></i>
                    <i v-if="xx.sortKey == 'site_supers' && xx.sortOrder == '-1'" class="fa fa-caret-up"></i>
                </div>
                <div class="col-xs-2">Actions</div>
            </div>


            <h3 v-if="xx.reason == '' && !xx.status" class="font-red" style="margin-top: 0px;">Not Logged in Users</h3>

      <template v-for="comply in xx.list | filterReason xx.reason | filterStatus xx.status | filterBy xx.search | orderBy xx.sortKey xx.sortOrder">

          <div class="row @{{ textColour(comply)  }}">
              <div class="col-xs-2">@{{ comply.date | formatDate}}</div>
              <div class="col-xs-2">@{{ comply.site_name }}</div>
              <div class="col-xs-2">@{{ comply.user_name }}</div>
              <div class="col-xs-2">@{{ comply.user_company }}</div>
              <div class="col-xs-2">@{{ comply.site_supers }}</div>
              <div class="col-xs-2">
                  @if (Auth::user()->hasPermission2('edit.compliance'))
                      <button v-on:click="editRecord(comply)" class="btn blue btn-xs btn-outline sbold uppercase margin-bottom">
                          <i class="fa fa-pencil"></i> <span class="hidden-xs hidden-sm>">Edit</span>
                      </button>
                  @endif
              </div>
          </div>
            </template>

            <pre>@{{ $data | json }}</pre>
        </div>--}}


        <div class="row">
            <div class="col-md-12">
                <table v-show="xx.list.length" class="table table-striped table-bordered table-nohover order-column">
                    <thead>
                    <tr class="mytable-header">
                        <th width="10%"><a href="#" class="mytable-header-link" v-on:click="sortBy('date')"> Date</a>
                            <i v-if="xx.sortKey == 'date' && xx.sortOrder == '1'" class="fa fa-caret-down"></i>
                            <i v-if="xx.sortKey == 'date' && xx.sortOrder == '-1'" class="fa fa-caret-up"></i>
                        </th>
                        <th width="20%"><a href="#" class="mytable-header-link" v-on:click="sortBy('site_name')"> Site</a>
                            <i v-if="xx.sortKey == 'site_name' && xx.sortOrder == '1'" class="fa fa-caret-down"></i>
                            <i v-if="xx.sortKey == 'site_name' && xx.sortOrder == '-1'" class="fa fa-caret-up"></i>
                        </th>
                        <th><a href="#" class="mytable-header-link" v-on:click="sortBy('user_name')"> Name</a>
                            <i v-if="xx.sortKey == 'user_name' && xx.sortOrder == '1'" class="fa fa-caret-down"></i>
                            <i v-if="xx.sortKey == 'user_name' && xx.sortOrder == '-1'" class="fa fa-caret-up"></i>
                        </th>
                        <th><a href="#" class="mytable-header-link" v-on:click="sortBy('user_company')"> Company</a>
                            <i v-if="xx.sortKey == 'user_company' && xx.sortOrder == '1'" class="fa fa-caret-down"></i>
                            <i v-if="xx.sortKey == 'user_company' && xx.sortOrder == '-1'" class="fa fa-caret-up"></i>
                        </th>
                        <th width="20%"><a href="#" class="mytable-header-link" v-on:click="sortBy('site_supers')"> Supervisor</a>
                            <i v-if="xx.sortKey == 'site_supers' && xx.sortOrder == '1'" class="fa fa-caret-down"></i>
                            <i v-if="xx.sortKey == 'site_supers' && xx.sortOrder == '-1'" class="fa fa-caret-up"></i>
                        </th>
                        <th> Actions</th>
                    </tr>
                    <br>
                    </thead>
                    <tbody>
                    <h3 v-if="xx.reason == '' && !xx.status" class="font-red" style="margin-top: 0px;">Not Logged in Users</h3>
                    <template v-for="comply in xx.list | filterReason xx.reason | filterStatus xx.status | filterBy xx.search | orderBy xx.sortKey xx.sortOrder">
                        <tr class="@{{ textColour(comply)  }}">
                            <td>@{{ comply.date | formatDate}}</td>
                            <td>@{{ comply.site_name }}</td>
                            <td>@{{ comply.user_name }}</td>
                            <td>@{{ comply.user_company }}</td>
                            <td>@{{ comply.site_supers }}</td>
                            <td>
                                @if (Auth::user()->hasPermission2('edit.compliance'))
                                    <button v-on:click="editRecord(comply)" class="btn blue btn-xs btn-outline sbold uppercase margin-bottom">
                                        <i class="fa fa-pencil"></i> <span class="hidden-xs hidden-sm>">Edit</span>
                                    </button>
                                @endif
                            </td>
                        </tr>
                    </template>
                    </tbody>
                </table>

                <!--<pre>@{{ $data | json }}</pre>
                    -->

            </div>
        </div>

        <!--
            Sidebar for editing record
           -->
        <sidebar :show.sync="xx.showSidebar" placement="right" header="Edit Compliance" :width="300">
            <h3 style="margin: 0px">@{{  xx.record.user_name }} @{{ xx.record.id }}<br>
                <small class="font-grey-silver">@{{ xx.record.user_company }}</small>
            </h3>

            <hr style="margin: 10px 0px">
            <div class="row" style="padding: 3px;">
                <div class="col-xs-3"><b>Date:</b></div>
                <div class="col-xs-9">@{{ xx.record.date | formatDate }}</div>
                <div class="col-xs-3"><b>Site:</b></div>
                <div class="col-xs-9">@{{  xx.record.site_name }}
                    <small class="font-grey-silver">(@{{ xx.record.site_id }})</small>
                </div>
                <div class="col-xs-12">
                    <b>Reason:</b><br>
                    <select v-model="xx.record.reason_new" class='form-control' v-on:change="doNothing">
                        <option v-for="option in xx.sel_reasons" value="@{{ option.value }}" selected="@{{option.value == xx.record.reason}}">@{{ option.text }}</option>
                    </select>
                </div>
                <div v-if="xx.record.reason == 1 && xx.record.status" class="col-xs-12">
                    <span class="font-red"<b>RESOLVED @{{ xx.record.resolved_at | formatDate }}</b>
                </div>
                <div v-if="xx.record.user_nc > 2" class="col-xs-12">
                    <div class="font-red"><b>Non Compliant Dates</b></div>
                    <div class="list-group">
                        <li v-for="date in xx.record.user_nc_dates" class="list-group-item" style="padding: 0px 10px">@{{ date }}</li>
                    </div>
                </div>

                <div class="col-xs-12">
                    <br><b>Notes:</b> <span v-if="xx.record.reason_new == '1' || (!xx.record.reason_new && xx.record.reason == 1 && xx.record.status == 0)" class="font-red">*required to resolve</span><br>
                    <textarea v-model="xx.record.notes_new" rows="3" class='form-control'>@{{ xx.record.notes }}</textarea>
                </div>
            </div>

            <br>
            <button class="btn btn-default" v-on:click="xx.showSidebar = false">Cancel</button>
            <button class="btn blue" v-on:click="saveRecord(xx.record)">Save</button>
            <button v-if="(xx.record.reason_new == '1' || (!xx.record.reason_new && xx.record.reason == 1 && xx.record.status == 0)) && xx.record.notes_new != ''" class="btn green"
                    v-on:click="resolveRecord(xx.record)">Resolve
            </button>
            <br><br>
            <hr>
            <!-- <pre>@{{ xx.record | json}}</pre>
            -->

        </sidebar>

        <!--
           Confirm Multiple Company Modal
         -->
        <multi-company :show.sync="xx.showMultiCompany" effect="fade">
            <div slot="modal-header" class="modal-header">
                <h4 class="modal-title text-center"><b>Confirm Multiple Contractors</b></h4>
            </div>
            <div v-if="xx.same_company.length" slot="modal-body" class="modal-body">
                <p class="text-center">There are other contractors from the same company that also didn't log in
                    on @{{ xx.same_company[0].date | formatDate }} @ @{{ xx.same_company[0].site_name }}</p>
                <p>
                    <template v-for="(index, user) in xx.same_company | orderBy 'user_name'">
                        @{{ user.user_name }}<span v-if="index != xx.same_company.length -1">, </span>
                    </template>
                </p>
                <p>Would you like to save them all with the same reason <b>@{{ xx.same_reason }}</b> ?</p>
            </div>
            <div slot="modal-footer" class="modal-footer">
                <button type="button" class="btn btn-default" v-on:click="resolveSameCompany(false)">&nbsp; No &nbsp;</button>
                <button type="button" class="btn btn-success" v-on:click="resolveSameCompany(true)">&nbsp; Yes &nbsp;</button>
            </div>
        </multi-company>
    </template>
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
<script src="/js/vue-app-planner-functions.js"></script>
<script src="/js/vue-app-compliance.js"></script>
@stop

