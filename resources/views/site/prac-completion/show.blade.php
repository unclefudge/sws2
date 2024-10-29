@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        @if (Auth::user()->hasAnyPermissionType('site'))
            <li><a href="/site">Sites</a><i class="fa fa-circle"></i></li>
        @endif
        <li><a href="/site/prac-completion">Practical Completion</a><i class="fa fa-circle"></i></li>
        <li><span>View items</span></li>
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
                            <span class="caption-subject bold uppercase font-green-haze"> Practical Completion</span>
                            <span class="caption-helper">ID: {{ $prac->id }}</span>
                        </div>
                    </div>
                    <div class="portlet-body">
                        <div class="page-content-inner">
                            {!! Form::model($prac, ['method' => 'PATCH', 'action' => ['Site\SitePracCompletionController@update', $prac->id], 'class' => 'horizontal-form']) !!}
                            <input type="hidden" id="site_id" value="{{ $prac->site_id }}">

                            @include('form-error')

                            <input v-model="xx.prac.id" type="hidden" id="prac_id" value="{{ $prac->id }}">
                            <input v-model="xx.prac.name" type="hidden" id="prac_name" value="{{ $prac->name }}">
                            <input v-model="xx.prac.site_id" type="hidden" id="prac_site_id" value="{{ $prac->site_id }}">
                            <input v-model="xx.prac.status" type="hidden" id="prac_status" value="{{ $prac->status }}">
                            <input v-model="xx.prac.signed" type="hidden" id="prac_signed" value="{{ $prac->isSigned() }}">
                            <input v-model="xx.table_id" type="hidden" id="table_id" value="{{ $prac->id }}">
                            <input v-model="xx.record_status" type="hidden" id="record_status" value="{{ $prac->status }}">
                            <input v-model="xx.user_id" type="hidden" id="user_id" value="{{ Auth::user()->id }}">
                            <input v-model="xx.user_fullname" type="hidden" id="fullname" value="{{ Auth::user()->fullname }}">
                            <input v-model="xx.company_id" type="hidden" id="company_id" value="{{ Auth::user()->company->reportsTo()->id }}">
                            <input v-model="xx.user_manager" type="hidden" id="user_manager" value="{{ Auth::user()->allowed2('sig.prac.completion', $prac) }}">
                            <input v-model="xx.user_supervisor" type="hidden" id="user_supervisor"
                                   value="{!! (in_array(Auth::user()->id, $prac->site->areaSupervisors()->pluck('id')->toArray()) || $prac->super_id == Auth::user()->id || Auth::user()->hasPermission2('sig.prac.completion')) ? 1 : 0  !!}">
                            <input v-model="xx.user_signoff" type="hidden" id="user_signoff" value="{{ Auth::user()->hasPermission2('sig.prac.completion') }}">
                            <input v-model="xx.user_edit" type="hidden" id="user_edit"
                                   value="{{ (Auth::user()->allowed2('edit.prac.completion', $prac) || $prac->super_id == Auth::user()->id) ? 1 : 0 }}">


                            <!-- Fullscreen devices -->
                            @if ($prac->status && $prac->items->count() == $prac->itemsCompleted()->count())
                                <div class="col-md-12 note note-warning">
                                    <p>All items have been completed and request requires
                                        <button class="btn btn-xs btn-outline dark disabled">Sign Off</button>
                                        at the bottom
                                    </p>
                                </div>
                            @endif

                            <div class="row">
                                {{-- Site Details --}}
                                <div class="col-md-5">
                                    <div class="row">
                                        <div class="col-md-12"><h4>Site Details</h4></div>
                                    </div>
                                    <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                                    @if ($prac->site)
                                        <b>{{ $prac->site->name }}</b><br>
                                        {{ $prac->site->full_address }}<br>
                                        <b>Supervisor:</b> {{ $prac->site->supervisor->name }}<br>
                                    @endif
                                </div>
                                <div class="col-md-1"></div>

                                <div class="col-md-6">
                                    {{-- Status --}}
                                    <div class="row">
                                        <div class="col-md-5"><h4>Client Details</h4></div>
                                        <div class="col-md-7">
                                            <h2 style="margin: 0px; padding-right: 20px">
                                                @if($prac->status == '-1')
                                                    <span class="pull-right font-red hidden-sm hidden-xs">DECLINED</span>
                                                    <span class="text-center font-red visible-sm visible-xs">DECLINED</span>
                                                @endif
                                                @if($prac->status == '0')
                                                    <span class="pull-right font-red hidden-sm hidden-xs"><small
                                                                class="font-red">COMPLETED {{ $prac->updated_at->format('d/m/Y') }}</small></span>
                                                    <span class="text-center font-red visible-sm visible-xs">COMPLETED {{ $prac->updated_at->format('d/m/Y') }}</span>
                                                @endif
                                                @if($prac->status == '1')
                                                    <span class="pull-right font-red hidden-sm hidden-xs">ACTIVE</span>
                                                    <span class="text-center font-red visible-sm visible-xs">ACTIVE</span>
                                                @endif
                                                @if($prac->status == '2')
                                                    <span class="pull-right font-red hidden-sm hidden-xs">IN PROGRESS</span>
                                                    <span class="text-center font-red visible-sm visible-xs">IN PROGRESS</span>
                                                @endif
                                                @if($prac->status == '4')
                                                    <span class="pull-right font-red hidden-sm hidden-xs">ON HOLD</span>
                                                    <span class="text-center font-red visible-sm visible-xs">ON HOLD</span>
                                                @endif
                                            </h2>
                                        </div>
                                    </div>
                                    <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                                    {{-- Client Details --}}
                                    <div class="row">
                                        <div class="col-md-6">
                                            @if ($prac->site->client1_name)
                                                <b>Primary Contact</b><br>
                                                {!! $prac->site->client1_name ? $prac->site->client1_name."<br>" : '' !!}
                                                {!! ($prac->site->client1_mobile) ? $prac->site->client1_mobile."<br>" : '' !!}
                                                {!! ($prac->site->client1_email) ? $prac->site->client1_email : '' !!}
                                            @endif
                                        </div>
                                        <div class="col-md-6">
                                            @if ($prac->site->client2_name)
                                                <b>Secondary Contact</b><br>
                                                {!! $prac->site->client2_name ? $prac->site->client2_name."<br>" : '' !!}
                                                {!! ($prac->site->client2_mobile) ? $prac->site->client2_mobile."<br>" : '' !!}
                                                {!! ($prac->site->client2_email) ? $prac->site->client2_email : '' !!}
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <br>


                            {{-- Attachments --}}
                            <h4>Attachments</h4>
                            <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                            <div class="row">
                                <div class="col-md-9">
                                    @if ($prac->attachments()->count())
                                        {{-- Image attachments --}}
                                        <div class="row" style="margin: 0">
                                            @foreach ($prac->attachments() as $attachment)
                                                @if ($attachment->type == 'image' && file_exists(public_path($attachment->url)))
                                                    <div style="width: 60px; float: left; padding-right: 5px">
                                                        @if(Auth::user()->allowed2('del.prac.completion', $prac))
                                                            <i class="fa fa-times font-red deleteFile" style="cursor:pointer" data-name="{{ $attachment->name }}" data-did="{{$attachment->id}}"></i>
                                                        @endif
                                                        <a href="{{ $attachment->url }}" target="_blank" class="html5lightbox" title="{{ $attachment->name }}" data-lity>
                                                            <img src="{{ $attachment->url }}" class="thumbnail img-responsive img-thumbnail"></a>
                                                    </div>
                                                @endif
                                            @endforeach
                                        </div>
                                        {{-- File attachments  --}}
                                        <div class="row" style="margin: 0">
                                            @foreach ($prac->attachments() as $attachment)
                                                @if ($attachment->type == 'file' && file_exists(public_path($attachment->url)))
                                                    <i class="fa fa-file-text-o"></i> &nbsp; <a href="{{ $attachment->url }}" target="_blank"> {{ $attachment->name }}</a>
                                                    @if(Auth::user()->allowed2('del.prac.completion', $prac))
                                                        <i class="fa fa-times font-red deleteFile" style="cursor:pointer" data-name="{{ $attachment->name }}" data-did="{{$attachment->id}}"></i>
                                                    @endif
                                                    <br>
                                                @endif
                                            @endforeach
                                        </div>
                                        <br>
                                    @else
                                        None
                                    @endif
                                </div>
                                {{-- Add Attachments --}}
                                <div class="col-md-3" style="background: #f1f0ef;">
                                    <input type="file" class="filepond" name="filepond[]" multiple/><br><br>
                                </div>
                            </div>


                            {{-- Under Review - asign to super --}}
                            <h4>Prac Completion Details</h4>
                            <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                            <div class="row">
                                {{-- Client Contacted --}}
                                {{--}}<div class="col-md-2">
                                    {!! Form::label('client_contacted', 'Client Contacted', ['class' => 'control-label']) !!}
                                    @if ($prac->status && Auth::user()->allowed2('edit.prac.completion', $prac) || Auth::user()->allowed2('sig.prac.completion', $prac))
                                        <div class="input-group" style="width=80%">
                                            <datepicker :value.sync="xx.client_contacted" format="dd/MM/yyyy" :placeholder="choose date" style="z-index: 888 !important"></datepicker>
                                        </div>
                                        <input v-model="xx.client_contacted" type="hidden" name="client_contacted"
                                               value="{{  ($prac->client_contacted) ? $prac->client_contacted->format('d/m/Y') : ''}}">
                                    @else
                                        {!! Form::text('client_contacted', ($prac->client_contacted) ? $prac->client_contacted->format('d/m/Y') : '', ['class' => 'form-control', 'readonly']) !!}
                                    @endif
                                </div>--}}

                                {{-- Client Appointment --}}
                                {{--}}<div class="col-md-2">
                                    {!! Form::label('client_appointment', 'Client Appointment', ['class' => 'control-label']) !!}
                                    @if ($prac->status && Auth::user()->allowed2('edit.prac.completion', $prac) || Auth::user()->allowed2('sig.prac.completion', $prac) )
                                        <div class="input-group">
                                            <datepicker :value.sync="xx.client_appointment" format="dd/MM/yyyy" :placeholder="choose date" style="z-index: 888 !important"></datepicker>
                                        </div>
                                        <input v-model="xx.client_appointment" type="hidden" name="client_appointment"
                                               value="{{  ($prac->client_appointment) ? $prac->client_appointment->format('d/m/Y') : ''}}">
                                    @else
                                        {!! Form::text('client_appointment', ($prac->client_appointment) ? $prac->client_appointment->format('d/m/Y') : '', ['class' => 'form-control', 'readonly']) !!}
                                    @endif
                                </div>--}}

                                {{-- Assigned Supervisor --}}
                                <div class="col-md-5">
                                    <div class="form-group {!! fieldHasError('super_id', $errors) !!}" style="{{ fieldHasError('super_id', $errors) ? '' : 'display:show' }}" id="company-div">
                                        {!! Form::label('super_id', 'Prac Supervisor', ['class' => 'control-label']) !!}
                                        @if ($prac->status && Auth::user()->allowed2('sig.prac.completion', $prac))
                                            {{-- Supervisor --}}
                                            <select id="super_id" name="super_id" class="form-control select2"
                                                    style="width:100%">
                                                <option value=""></option>
                                                <optgroup label="Cape Code Supervisors"></optgroup>
                                                @foreach (Auth::user()->company->supervisors()->sortBy('name') as $super)
                                                    <option value="{{ $super->id }}" {{ ($super->id == $prac->super_id) ? 'selected' : '' }}>{{ $super->name }}</option>
                                                @endforeach
                                                <optgroup label="External Users"></optgroup>
                                                <option value="2023" {{ ('2023' == $prac->super_id) ? 'selected' : '' }}>
                                                    Jason Habib (Prolific Projects)
                                                </option>
                                            </select>
                                            {!! fieldErrorMessage('super_id', $errors) !!}
                                        @else
                                            {!! Form::text('assigned_super_text', ($prac->super_id) ? $prac->supervisor->name : '-', ['class' => 'form-control', 'readonly']) !!}
                                        @endif
                                        {!! fieldErrorMessage('super_id', $errors) !!}
                                    </div>
                                </div>

                                @if (Auth::user()->allowed2('edit.prac.completion', $prac))
                                    <div class="col-md-1 pull-right">
                                        <button id="submit" type="submit" name="save" class="btn blue" style="margin-top: 25px">Save</button>
                                    </div>
                                @endif
                            </div>
                        </div>
                        <br>


                        {{-- Prac Items --}}
                        <div class="row">
                            <div class="col-md-12">
                                <app-prac></app-prac>
                            </div>
                        </div>

                        {{-- Planner --}}
                        <h4>Future Planner Tasks</h4>
                        <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                        <div class="row">
                            <div class="col-md-12">
                                @if ($prac->site->futureTasks()->count())
                                    @foreach ($prac->site->futureTasks() as $plan)
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
                        <div class="row">
                            <div class="col-md-12">
                                <app-actions :table_id="{{ $prac->id }}"></app-actions>
                            </div>
                        </div>

                        {{-- ToDos--}}
                        <div class="row">
                            <div class="col-md-12">
                                <h3>Assigned Tasks
                                    {{-- Show add if user has permission to edit prac --}}
                                    @if ($prac->status && Auth::user()->hasAnyRole2('con-construction-manager|con-administrator|web-admin|mgt-general-manager'))
                                        <a href="/todo/create/prac_completion_task/{{ $prac->id}}"
                                           class="btn btn-circle green btn-outline btn-sm pull-right"
                                           data-original-title="Add">Add</a>
                                    @endif
                                </h3>
                                @if ($prac->todos()->count())
                                    <table class="table table-striped table-bordered table-nohover order-column">
                                        <thead>
                                        <tr class="mytable-header">
                                            <th style="width:5%">#</th>
                                            <th> Action</th>
                                            <th style="width:15%">Created by</th>
                                            <th style="width:15%">Completed by</th>
                                            <th style="width:5%"></th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach($prac->todos() as $todo)
                                            <tr>
                                                <td>
                                                    <div class="text-center"><a href="/todo/{{ $todo->id }}"><i class="fa fa-search"></i></a></div>
                                                </td>
                                                <td>
                                                    {{ $todo->info }}<br><br><i>Assigned
                                                        to: {{ $todo->assignedToBySBC() }}</i>
                                                    @if ($todo->comments)
                                                        <br><b>Comments:</b> {{ $todo->comments }}
                                                    @endif
                                                </td>
                                                <td>{!! App\User::findOrFail($todo->created_by)->full_name  !!}
                                                    <br>{{ $todo->created_at->format('d/m/Y')}}</td>
                                                    <?php
                                                    $done_by = App\User::find($todo->done_by);
                                                    $done_at = ($done_by) ? $todo->done_at->format('d/m/Y') : '';
                                                    $done_by = ($done_by) ? $done_by->full_name : 'unknown';
                                                    ?>
                                                <td>@if ($todo->status && !$todo->done_by)
                                                        <span class="font-red">Outstanding</span>
                                                    @else
                                                        {!! $done_by  !!}<br>{{ $done_at }}
                                                    @endif</td>
                                                <td>
                                                    @if ($todo->attachment)
                                                        <a href="{{ $todo->attachmentUrl }}" data-lity class="btn btn-xs blue"><i class="fa fa-picture-o"></i></a>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                @endif
                            </div>
                        </div>

                        {!! Form::close() !!}

                        {{-- Sign Off --}}
                        <hr>
                        <div class="row">
                            <div class="col-md-12">
                                <h5><b>PRACTICAL COMPLETION ELECTRONIC SIGN-OFF</b></h5>
                                <p>The above items have been checked by the site construction supervisor and conform to the Cape Cod standard set.</p>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-3 text-right">Site Supervisor:</div>
                            <div class="col-sm-9">
                                @if ($prac->supervisor_sign_by)
                                    {!! \App\User::find($prac->supervisor_sign_by)->full_name !!}, &nbsp;{{ $prac->supervisor_sign_at->format('d/m/Y') }}
                                    {{--}}<button v-if="xx.user_manager == 1 || xx.user_signoff"
                                            v-on:click.prevent="$root.$broadcast('signOff', 'manager')" class="btn blue btn-xs btn-outline sbold uppercase margin-bottom">Clear Sign Off
                                    </button>--}}
                                    <a v-if="xx.user_manager == 1 || xx.user_signoff" style="margin-left: 20px" class="font-red clearSignoff"> <i class="fa fa-times"></i> Clear </a>
                                @else
                                    <button v-if="xx.prac.items_total != 0 && xx.prac.items_done == xx.prac.items_total && xx.user_supervisor == 1"
                                            v-on:click.prevent="$root.$broadcast('signOff', 'super')" class=" btn blue btn-xs btn-outline sbold uppercase margin-bottom">Sign Off
                                    </button>
                                    <span v-if="xx.prac.items_total != 0 && xx.prac.items_done == xx.prac.items_total && xx.user_supervisor == 0"
                                          class="font-red">Pending</span>
                                    <span v-if="xx.prac.items_total != 0 && xx.prac.items_done != xx.prac.items_total"
                                          class="font-grey-silver">Waiting for items to be completed</span>
                                @endif
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-3 text-right">Construction Manager:</div>
                            <div class="col-sm-9">
                                @if ($prac->manager_sign_by)
                                    {!! \App\User::find($prac->manager_sign_by)->full_name !!},
                                    &nbsp;{{ $prac->manager_sign_at->format('d/m/Y') }}
                                @else
                                    @if ($prac->supervisor_sign_by)
                                        <button v-if="xx.prac.items_total != 0 && xx.prac.items_done == xx.prac.items_total && (xx.user_manager == 1 || xx.user_signoff)"
                                                v-on:click.prevent="$root.$broadcast('signOff', 'manager')" class=" btn blue btn-xs btn-outline sbold uppercase margin-bottom">Sign Off
                                        </button>
                                        <span v-if="xx.prac.items_total != 0 && xx.prac.items_done == xx.prac.items_total && xx.user_manager == 0 && !xx.user_signoff"
                                              class="font-red">Pending</span>
                                    @else
                                        <span v-if="xx.prac.items_total != 0 && xx.prac.items_done == xx.prac.items_total" class="font-red">Waiting for Prac Supervisor Sign Off</span>
                                        <span v-if="xx.prac.items_total != 0 && xx.prac.items_done != xx.prac.items_total" class="font-grey-silver">Waiting for items to be completed</span>
                                    @endif
                                @endif
                            </div>
                        </div>

                        <hr>
                        <div class="pull-right" style="min-height: 50px">
                            <a href="/site/prac-completion" class="btn default"> Back</a>
                            @if (!$prac->master && Auth::user()->allowed2('edit.prac.completion', $prac))
                                <button v-if="xx.prac.status == 1 && xx.prac.items_total != 0 && xx.prac.items_done != xx.prac.items_total" class="btn blue"
                                        v-on:click.prevent="$root.$broadcast('updateReportStatus', 2)"> Place On Hold
                                </button>
                                <button v-if="xx.prac.status == 2 || xx.prac.status == -1 " class="btn green" v-on:click.prevent="$root.$broadcast('updateReportStatus', 1)"> Make Active
                                </button>
                            @endif
                        </div>
                        <br><br>
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

    <template id="prac-template">
        <h4 style="margin-bottom: 15px">Prac Completion Items
            {{-- Show add if user has permission to add items --}}
            @if ($prac->status && Auth::user()->allowed2('edit.prac.completion', $prac) && !$prac->supervisor_sign_by)
                <button class="btn btn-circle green btn-outline btn-sm pull-right" v-on:click.prevent="itemAdd()">Add</button>
            @endif
        </h4>
        <hr style="padding: 0px; margin: 0px 0px 10px 0px">
        <table v-show="xx.itemList.length" class="table table-striped table-bordered table-nohover order-column">
            <thead>
            <tr class="mytable-header">
                <th> Prac Item</th>
                <th style="width:30%"> Assigned Task</th>
                <th style="width:15%"> Completed</th>
                <th style="width:10%"> Action</th>
            </tr>
            </thead>
            <tbody>
            <template v-for="item in xx.itemList | orderBy item.order">
                <tr>
                    {{-- Item --}}
                    <td style="padding-top: 15px;">@{{ item.name }}</td>
                    {{-- Assigned Task --}}
                    <td style="padding-top: 15px;">
                        @{{ item.assigned_to_name }}<br>
                        <div v-if="item.planner_id && item.planner_task && item.planner_date">
                            <b>Task:</b> @{{ item.planner_task}} (@{{ item.planner_date }})
                        </div>
                    </td>
                    {{-- Completed --}}
                    <td>
                        <div v-if="item.sign_by">
                            @{{ item.sign_at | formatDate }}<br>@{{ item.sign_by_name }}
                        </div>
                        <div v-else>-</div>
                    </td>
                    <td>
                        @if (!$prac->supervisor_sign_by)
                            <button class="btn btn-xs btn-outline blue" v-on:click.prevent="itemEdit(item)"><i class="fa fa-pencil"></i> Edit</button>
                        @endif
                        @if ($prac->status && Auth::user()->hasAnyRole2('web-admin|mgt-general-manager'))
                            <button class="btn btn-xs dark" v-on:click.prevent="itemDelete(item)"><i class="fa fa-trash"></i></button>
                        @endif
                    </td>
                </tr>
            </template>
            </tbody>
        </table>

        {{--  Add Item Modal --}}
        <add-Item :show.sync="xx.addItemModal" effect="fade" class="modal fade bs-modal-lg topmodal" header="Edit Item">
            <div slot="modal-header" class="modal-header">
                <h4 class="modal-title text-center"><b>Add Item</b></h4>
            </div>
            <div slot="modal-body" class="modal-body">
                <b>Item</b>
                <div class="row" style="padding-bottom: 10px">
                    <div class="col-md-12">
                        <textarea v-model="xx.prac.newitem" name="newitem" rows="3" class="form-control" placeholder="Specific details of practical completion item" cols="50"></textarea>
                    </div>
                </div>
            </div>
            <div slot="modal-footer" class="modal-footer">
                <button type="button" class="btn dark btn-outline" v-on:click="xx.addItemModal = false">Cancel</button>
                <button v-if="xx.prac.newitem != ''" type="button" class="btn green" v-on:click="saveItem()">&nbsp; Save &nbsp;</button>
            </div>
        </add-Item>

        {{--  Edit Item Modal --}}
        <edit-Item :show.sync="xx.editItemModal" effect="fade" class="modal fade bs-modal-lg topmodal" header="Edit Item">
            <div slot="modal-header" class="modal-header">
                <h4 class="modal-title text-center"><b>Edit Item</b></h4>
            </div>
            <div slot="modal-body" class="modal-body">
                <b>Item</b><br>
                @{{ xx.item.name_brief }}<br><br>

                <div class="row" style="padding-bottom: 10px">
                    <div class="col-md-3">Assigned To</div>
                    <div class="col-md-9">
                        <select v-model="xx.item.assigned_to" class='form-control' v-on:change="updateTaskOptions(xx.item)">
                            <option v-for="option in xx.sel_company" value="@{{ option.value }}"
                                    selected="@{{option.value == item.assigned_to}}">@{{ option.text }}
                            </option>
                        </select>
                    </div>
                </div>

                <div class="row" style="padding-bottom: 10px">
                    <div class="col-md-3">Planner Task</div>
                    <div class="col-md-9">
                        <select v-model="xx.item.planner_task_id" class='form-control' v-on:change="doNothing">
                            <option v-for="option in xx.sel_task" value="@{{ option.value }}"
                                    selected="@{{option.value == item.planner_task_id}}">@{{ option.text }}
                            </option>
                        </select>
                    </div>
                </div>

                <div v-if="xx.item.planner_task_id" class="row" style="padding-bottom: 10px">
                    <div class="col-md-3">Task Date</div>
                    <div class="col-md-9">
                        <div v-if="xx.editItemModal" class="input-group">
                            <datepicker2 :value.sync="xx.item.planner_date" format="dd/MM/yyyy" :placeholder="choose date"></datepicker2>
                        </div>
                    </div>
                </div>

                <div class="row" style="padding-bottom: 10px">
                    <div class="col-md-3">Status</div>
                    <div class="col-md-9">
                        <div v-if="xx.editItemModal" class="input-group">
                            <select v-model="xx.item.status" class='form-control' v-on:change="doNothing" style="width: 160px">
                                <option v-for="option in xx.sel_checked" value="@{{ option.value }}"
                                        selected="@{{option.value == item.status}}">@{{ option.text }}
                                </option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div slot="modal-footer" class="modal-footer">
                <button type="button" class="btn dark btn-outline" v-on:click="xx.editItemModal = false">Cancel</button>
                <button v-if="!xx.item.planner_task_id || (xx.item.planner_task_id && xx.item.planner_date)" type="button" class="btn green" v-on:click="updateItem(xx.item)">&nbsp; Save &nbsp;</button>
            </div>
        </edit-Item>

    </template>




    <template id="actions-template">
        <action-modal></action-modal>
        <input v-model="xx.table_id" type="hidden" id="table_id" value="{{ $prac->id }}">
        <input v-model="xx.created_by" type="hidden" id="created_by" value="{{ Auth::user()->id }}">
        <input v-model="xx.created_by_fullname" type="hidden" id="fullname" value="{{ Auth::user()->fullname }}">

        <div class="page-content-inner">
            <div class="row">
                <div class="col-md-12">
                    <h3>Notes
                        <button v-on:click.prevent="$root.$broadcast('add-action-modal')"
                                class="btn btn-circle green btn-outline btn-sm pull-right" data-original-title="Add">Add
                        </button>
                    </h3>
                    <table v-show="actionList.length"
                           class="table table-striped table-bordered table-nohover order-column">
                        <thead>
                        <tr class="mytable-header">
                            <th style="width:10%">Date</th>
                            <th> Action</th>
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

                    <!--<pre v-if="xx.dev">@{{ $data | json }}</pre> -->

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
    <script type="text/javascript">var html5lightbox_options = {watermark: "", watermarklink: ""};</script>
@stop

@section('page-level-plugins')
    <script src="/assets/global/plugins/select2/js/select2.full.min.js" type="text/javascript"></script>
    <script src="/js/moment.min.js" type="text/javascript"></script>
    <script src="https://unpkg.com/filepond/dist/filepond.min.js"></script> {{-- FilePond --}}
    <script src="/js/libs/html5lightbox/html5lightbox.js" type="text/javascript"></script>
@stop

@section('page-level-scripts')
    {{-- Metronic + custom Page Scripts --}}
    <script src="/js/filepond-basic.js" type="text/javascript"></script>
    <script src="/js/libs/vue.1.0.24.js " type="text/javascript"></script>
    <script src="/js/libs/vue-strap.min.js"></script>
    <script src="/js/libs/vue-resource.0.7.0.js " type="text/javascript"></script>
    <script src="/js/vue-modal-component.js"></script>
    <script src="/js/vue-app-basic-functions.js"></script>

    <script>
        $.ajaxSetup({headers: {'X-CSRF-Token': $('meta[name=token]').attr('value')}});

        $(document).ready(function () {
            /* Select2 */
            $("#super_id").select2({placeholder: "Select supervisor", width: '100%'});

            $("#status").change(function () {
                $('#onhold-div').hide();

                if ($("#status").val() == '4') {
                    $('#onhold-div').show();
                }
            });
            $('.clearSignoff').on('click', function (e) {
                e.preventDefault();
                alert('here');
                window.location = '/site/prac-completion/' + {{$prac->id}} + '/clearsignoff';
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
                    window.location = '/site/prac-completion/' + {{$prac->id}} + '/delfile/' + id;
                });
            });
        });
    </script>
    <script>
        var xx = {
            dev: dev,
            prac: {
                id: '', name: '', site_id: '', status: '', warranty: '', assigned_to: '', newitem: '',
                planner_id: '', planner_task_id: '', planner_task_date: '', signed: '', items_total: 0, items_done: 0
            },
            spinner: false, showSignOff: false, addItemModal: false, editItemModal: false, showAction: false,
            record: {}, item: {},
            action: '', loaded: false,
            table_name: 'site_prac_completion', table_id: '', record_status: '', record_resdate: '',
            created_by: '', created_by_fullname: '',
            done_by: '',
            itemList: [],
            actionList: [], sel_checked: [], sel_checked2: [], sel_company: [], sel_task: [],
            client_contacted: '', client_appointment: ''
        };

        //
        // Prac Items
        //
        Vue.component('app-prac', {
            template: '#prac-template',

            created: function () {
                this.getPrac();
            },
            data: function () {
                return {xx: xx};
            },
            events: {
                'updateReportStatus': function (status) {
                    this.xx.prac.status = status;
                    this.updateReportDB(this.xx.prac, true);
                },
                'signOff': function (type) {
                    this.xx.prac.signoff = type;
                    this.updateReportDB(this.xx.prac, true);
                },
            },
            components: {
                addItem: VueStrap.modal,
                editItem: VueStrap.modal,
                datepicker2: VueStrap.datepicker,
            },
            filters: {
                formatDate: function (date) {
                    return moment(date).format('DD/MM/YYYY');
                },
                max100chars: function (str) {
                    return str.substring(0, 100);
                },
            },
            methods: {
                getPrac: function () {
                    this.xx.spinner = true;
                    setTimeout(function () {
                        this.xx.load_plan = true;
                        $.getJSON('/site/prac-completion/' + this.xx.prac.id + '/items', function (data) {
                            this.xx.itemList = data[0];
                            this.xx.sel_checked = data[1];
                            this.xx.sel_checked2 = data[2];
                            this.xx.sel_company = data[3];
                            this.xx.sel_task = data[4];
                            this.xx.spinner = false;
                            this.itemsCompleted();
                        }.bind(this));
                    }.bind(this), 100);
                },
                itemsCompleted: function () {
                    this.xx.prac.items_total = 0;
                    this.xx.prac.items_done = 0;
                    for (var i = 0; i < this.xx.itemList.length; i++) {
                        if ((this.xx.itemList[i]['sign_by']))
                            this.xx.prac.items_done++;

                        this.xx.prac.items_total++;
                    }
                },
                itemAdd: function (record) {
                    this.xx.addItemModal = true;
                },
                saveItem: function (record) {
                    var record = {};
                    record.name = this.xx.prac.newitem;
                    record.order = this.xx.prac.items_total + 1

                    //console.log(record);
                    this.xx.addItemModal = false;

                    this.$http.patch('/site/prac-completion/{{$prac->id}}/additem', record)
                        .then(function (response) {
                            this.getPrac();
                            this.itemsCompleted();
                            this.xx.prac.newitem = '';
                            toastr.success('Added record');
                        }.bind(this))
                        .catch(function (response) {
                            alert('failed to add item');
                        });
                },
                itemDelete: function (record) {
                    swal({
                        title: "Are you sure?",
                        text: "You will not be able to restore this item!<br><b>" + record.name + "</b>",
                        showCancelButton: true,
                        cancelButtonColor: "#555555",
                        confirmButtonColor: "#E7505A",
                        confirmButtonText: "Yes, delete it!",
                        allowOutsideClick: true,
                        html: true,
                    }, function () {
                        window.location = '/site/prac-completion/' + record.id + '/delitem';
                    });
                },
                itemEdit: function (record) {
                    this.xx.item = record;
                    this.xx.item.name_brief = this.xx.item.name.substring(0, 150) + '....';
                    this.updateTaskOptions(record);
                    this.xx.editItemModal = true;
                },
                updateTaskOptions: function (item) {
                    if (item.assigned_to) {
                        $.getJSON('/planner/data/company/' + item.assigned_to + '/tasks/trade/all', function (tasks) {
                            this.xx.sel_task = tasks;
                        }.bind(this));
                    } else {
                        item.planner_task_id = '';
                        item.planner_date = '';
                    }
                },
                updateItem: function (record) {
                    // Get company name + licence from dropdown menu array
                    var company = objectFindByKey(this.xx.sel_company, 'value', record.assigned_to);
                    record.assigned_to_name = company.text;

                    // Get Task name from dropdown menu array
                    var task = objectFindByKey(this.xx.sel_task, 'value', record.planner_task_id);
                    record.planner_task = task.text;

                    // Item just marked Completed
                    if (record.status == '1' && !record.sign_by) {
                        // Update done by + Signed by
                        record.sign_at = moment().format('YYYY-MM-DD');
                        record.sign_by = this.xx.user_id;
                        record.sign_by_name = this.xx.user_fullname;
                        record.sign_at = moment().format('YYYY-MM-DD');
                        record.sign_by = this.xx.user_id;
                        record.sign_by_name = this.xx.user_fullname;
                    }

                    // Item just marked Incomplete
                    if (record.status == '0' && record.sign_by) {
                        record.sign_at = '';
                        record.sign_by = '';
                        record.sign_by_name = '';
                        record.sign_at = '';
                        record.sign_by = '';
                        record.sign_by_name = '';
                    }

                    //console.log(record);

                    // Get original item from list
                    //var obj = objectFindByKey(this.xx.itemList, 'id', record.id);
                    //obj = record;
                    this.updateItemDB(record);
                    this.xx.item = {};
                    this.xx.sign_by = '';
                    this.xx.editItemModal = false;
                },
                updateItemDB: function (record) {
                    //alert('update item id:'+record.id+' task:'+record.task_id+' by:'+record.sign_by);
                    this.$http.patch('/site/prac-completion/item/' + record.id, record)
                        .then(function (response) {
                            this.getPrac();
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
                    this.$http.patch('/site/prac-completion/' + record.id + '/update', record)
                        .then(function (response) {
                            this.itemsCompleted();
                            if (redirect)
                                window.location.href = '/site/prac-completion/' + record.id;
                            toastr.success('Updated record');

                        }.bind(this)).catch(function (response) {
                        alert('failed to update report');
                    });
                },
                textColour: function (record) {
                    if (record.status == '-1')
                        return 'font-grey-silver';
                    if (record.status == '0' && record.signed_by != '0')
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
            methods: {
                doNothing: function () {
                    //
                },
            },
        });
    </script>
@stop

