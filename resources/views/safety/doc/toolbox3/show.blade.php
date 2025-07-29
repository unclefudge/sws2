@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        <li><a href="/safety/doc/toolbox3">Toolbox Talks</a><i class="fa fa-circle"></i></li>
        <li><span>View Talk</span></li>
    </ul>
@stop

@section('content')
    <div class="page-content-inner">
        {{-- Progress Steps --}}
        @if(!$talk->master && Auth::user()->allowed2('edit.toolbox', $talk) && $talk->status != 0)
            <div class="mt-element-step hidden-sm hidden-xs">
                <div class="row step-line" id="steps">
                    <div class="col-md-3 mt-step-col first done">
                        <div class="mt-step-number bg-white font-grey"><i class="fa fa-check"></i></div>
                        <div class="mt-step-title uppercase font-grey-cascade">Create</div>
                        <div class="mt-step-content font-grey-cascade">Create Talk</div>
                    </div>
                    <div class="col-md-3 mt-step-col done">
                        <div class="mt-step-number bg-white font-grey"><i class="fa fa-check"></i></div>
                        <div class="mt-step-title uppercase font-grey-cascade">Draft</div>
                        <div class="mt-step-content font-grey-cascade">Add content</div>
                    </div>

                    @if($talk->status == 1)
                        <div class="col-md-3 mt-step-col active">
                            <div class="mt-step-number bg-white font-grey">3</div>
                            <div class="mt-step-title uppercase font-grey-cascade">Users</div>
                            <div class="mt-step-content font-grey-cascade">@if(!$talk->assignedTo())
                                    Assign users
                                @else
                                    Monitor users
                                @endif</div>
                        </div>
                        <div class="col-md-3 mt-step-col last">
                            <div class="mt-step-number bg-white font-grey">4</div>
                            <div class="mt-step-title uppercase font-grey-cascade">Archive</div>
                            <div class="mt-step-content font-grey-cascade">Talk completed</div>
                        </div>
                    @else
                        <div class="col-md-3 mt-step-col active">
                            <div class="mt-step-number bg-white font-grey">3</div>
                            <div class="mt-step-title uppercase font-grey-cascade">Sign Off</div>
                            <div class="mt-step-content font-grey-cascade">Pending sign off</div>
                        </div>
                        <div class="col-md-3 mt-step-col last">
                            <div class="mt-step-number bg-white font-grey">4</div>
                            <div class="mt-step-title uppercase font-grey-cascade">Users</div>
                            <div class="mt-step-content font-grey-cascade">Assign users</div>
                        </div>
                    @endif
                </div>
            </div>
        @endif
        @if ($talk->userRequiredToRead(Auth::user()))
            <div class="col-md-12 note note-warning">
                <p>Please complete this TBT and once satisfied you understand the information acknowledge completion by clicking the
                    <button class="btn btn-xs btn-outline dark disabled">Accept as Read</button>
                </p>
                <p><br>NOTE: ONLY ACCEPT IF YOU ARE SATISFIED THAT YOU UNDERSTAND THE INFORMATION. IF NOT GO AND TALK TO YOUR LEADING HAND.</p>
            </div>
        @endif
        @if (!$talk->master && Auth::user()->allowed2('edit.toolbox', $talk))
            @if ($talk->status == 3)
                <div class="note note-warning">This toolbox talk is pending sign off.</div>
            @elseif (!$talk->assignedTo())
                <div class="note note-danger">This toolbox talk currently isn't assigned to any users.
                    <a class="btn btn-sm dark" style="margin-left: 20px" data-toggle="modal" href="#modal_users"> Assign Users </a>
                </div>
            @else
                <div class="col-md-12 note note-warning">
                    <div class="row">
                        <div class="col-md-4 pull-right">
                            <h1><span class="label pull-right {!! ($talk->completedBy()->count() == $talk->assignedTo()->count()) ? 'label-success' : 'label-danger' !!}">{{ $talk->completedBy()->count() }}
                                    / {{ $talk->assignedTo()->count() }}</span></h1>
                        </div>
                        <div class="col-md-8">
                            <p>This toolbox has been assigned to the following users:<br><br></p>
                        </div>
                        <div class="col-md-12">
                            @if ($talk->completedBySBC())
                                <p><b>Completed by:</b> {{ $talk->completedBySBC() }}</p>
                            @endif
                            @if ($talk->outstandingBySBC())
                                <p><b>Outstanding by:</b> {!! $talk->outstandingBySBCDelete() !!}</p>
                            @endif
                        </div>
                    </div>
                </div>
            @endif
        @endif
        <div class="row">
            <div class="col-md-12">
                <div class="portlet light bordered">
                    <div class="portlet-title">
                        <div class="caption">
                            <i class="fa fa-text-o "></i>
                            <span class="caption-subject font-green-haze bold ">TOOLBOX TALK (v3)</span>
                            <span class="caption-helper">ID: {{ $talk->id }}</span>
                        </div>
                        <div class="actions">
                            <a class="btn btn-circle green btn-outline btn-sm" href="/safety/doc/toolbox3/{{ $talk->id }}/pdf" data-original-title="PDF"><i class="fa fa-file-pdf-o"></i> &nbsp; PDF</a>
                            @if (Auth::user()->allowed2('edit.toolbox', $talk))
                                <div class="btn-group">
                                    <a class="btn btn-circle green btn-outline btn-sm" href="javascript:;" data-toggle="dropdown" data-over="dropdown"><i class="fa fa-cog"></i> Actions</a>
                                    <ul class="dropdown-menu pull-right">
                                        {{-- Talk Pending --}}
                                        @if ($talk->status == 3 && (Auth::user()->allowed2('del.toolbox', $talk) || Auth::user()->allowed2('sig.toolbox', $talk)))
                                            @if(Auth::user()->allowed2('sig.toolbox', $talk))
                                                <li><a href="/safety/doc/toolbox3/{{ $talk->id }}/signoff"><i class="fa fa-check"></i> Sign Off</a></li>
                                                <li><a href="/safety/doc/toolbox3/{{ $talk->id }}/reject"><i class="fa fa-ban"></i> Reject</a></li>
                                                <li class="divider"></li>
                                            @endif
                                            @if(Auth::user()->allowed2('del.toolbox', $talk))
                                                <li><a id="delete"><i class="fa fa-trash"></i> Delete</a></li>
                                            @endif
                                        @endif

                                        @if ($talk->status != 3)
                                            {{-- Not Pending --}}
                                            @if(!$talk->master && $talk->status == 1)
                                                <li><a data-original-title="Archive" data-toggle="modal" href="#modal_users"><i class="fa fa-archive"></i> Add Users</a></li>
                                                <li><a href="/safety/doc/toolbox3/{{ $talk->id }}/reminder"><i class="fa fa-envelope"></i> Email reminder to outstanding</a></li>
                                                <li class="divider"></li>
                                            @endif
                                            @if($talk->status == 1 && Auth::user()->allowed2('del.toolbox', $talk))
                                                <li><a data-original-title="Archive" data-toggle="modal" href="#modal_archive"><i class="fa fa-archive"></i> Archive</a></li>
                                            @elseif($talk->status == 0 && Auth::user()->isCompany($talk->owned_by) && Auth::user()->allowed2('del.toolbox', $talk))
                                                <li><a href="/safety/doc/toolbox3/{{ $talk->id }}/archive"><i class="fa fa-archive"></i> Unarchive</a></li>
                                            @endif
                                        @endif
                                    </ul>
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="portlet-body form">
                        {!! Form::model($talk, ['method' => 'PATCH', 'action' => ['Safety\ToolboxTalk3Controller@update', $talk->id], 'class' => 'horizontal-form', 'files' => true, 'id'=>'talk_form']) !!}
                        @include('form-error')
                        <input type="hidden" name="talk_id" id='talk_id' value="{{ $talk->id }}">
                        <div class="form-body">
                            <div class="row">
                                <div class="col-md-6 pull-right">
                                    @if($talk->master && $talk->status == '1')
                                        <h3 class="pull-right font-red uppercase" style="margin:0 0 10px;">Template</h3>
                                    @elseif($talk->master && $talk->status == '0')
                                        <h3 class="pull-right font-red uppercase" style="margin:0 0 10px;">Template Archived</h3>
                                    @elseif($talk->status == '0')
                                        <h3 class="pull-right font-red uppercase" style="margin:0 0 10px;">Archived</h3>
                                    @endif
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-9"><h1 style="margin: 0 0 2px 0">{{ $talk->name }}</h1></div>
                                <div class="col-md-3 text-right" style="margin-top: 15px; padding-right: 20px">
    <span class="font-grey-salsa"><span class="font-grey-salsa">version {{ $talk->version }} </span>
                                </div>
                            </div>
                            <hr style="margin: 2px 0 15px 0">
                            <div class="row">
                                <div class="col-md-12">Created by: {{ $talk->createdBy->full_name }}<br><br></div>
                                <div class="col-md-12">
                                    <div style="background: #f0f6fa; padding: 2px 0px 2px 20px;"><h5 style="margin: 5px; font-weight: bold">OVERVIEW</h5></div>
                                </div>
                                <div class="col-md-12"><br>{!! $talk->overview !!}</div>
                                @if ($talk->hazards )
                                    <div class="col-md-12">
                                        <div style="background: #f0f6fa; padding: 2px 0px 2px 20px;"><h5 style="margin: 5px; font-weight: bold">HAZARDS</h5></div>
                                    </div>
                                    <div class="col-md-12"><br>{!! $talk->hazards !!}</div>
                                @endif
                                @if ($talk->controls )
                                    <div class="col-md-12">
                                        <div style="background: #f0f6fa; padding: 2px 0px 2px 20px;"><h5 style="margin: 5px; font-weight: bold">CONTROLS / ACTIONS</h5></div>
                                    </div>
                                    <div class="col-md-12"><br>{!! $talk->controls !!}</div>
                                @endif
                                @if ($talk->further )
                                    <div class="col-md-12">
                                        <div style="background: #f0f6fa; padding: 2px 0px 2px 20px;"><h5 style="margin: 5px; font-weight: bold">FURTHER INFORMATION</h5></div>
                                    </div>
                                    <div class="col-md-12"><br>{!! $talk->further !!}</div>
                                @endif
                            </div>

                            <br>
                            <div class="form-actions right">
                                <a href="/safety/doc/toolbox3" class="btn default"> Back</a>
                                @if($talk->master && $talk->status == '1' && Auth::user()->hasPermission2('add.toolbox'))
                                    <a href="/safety/doc/toolbox3/{{ $talk->id}}/create" class="btn green">Create Toolbox Talk using this Template</a>
                                @endif
                                @if($talk->status == '1' && Auth::user()->allowed2('del.toolbox', $talk))
                                    <a class="btn dark" data-original-title="Archive" data-toggle="modal" href="#modal_archive"><i class="fa fa-archive"></i> Archive</a>
                                @endif
                                @if($talk->status == '3' && Auth::user()->allowed2('sig.toolbox', $talk))
                                    <a href="/safety/doc/toolbox3/{{ $talk->id }}/signoff" class="btn green"> Sign Off</a>
                                    <a href="/safety/doc/toolbox3/{{ $talk->id }}/reject" class="btn red"> Reject</a>
                                @endif
                                @if($talk->userRequiredToRead(Auth::user()))
                                    <a href="/safety/doc/toolbox3/{{ $talk->id }}/accept" class="btn green"> Accept as Read</a>
                                @endif
                            </div>
                        </div>
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- User Add Modal -->
    <div id="modal_users" class="modal fade" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                    <h4 class="modal-title">{{ $talk->name }}</h4>
                </div>
                <div class="modal-body">
                    {!! Form::model($talk, ['method' => 'PATCH', 'action' => ['Safety\ToolboxTalk3Controller@update', $talk->id], 'class' => 'horizontal-form', 'files' => true, 'id'=>'talk_form']) !!}
                    <input type="hidden" name="name" value="{{ $talk->name }}">
                    <input type="hidden" name="toolbox_type" value="none">
                    <input type="hidden" name="for_company_id" value="{{ Auth::user()->company_id }}">

                    <div class="scroller" style="height:350px" data-always-visible="1" data-rail-visible1="1">
                        <!-- Assigned to Users -->
                        <div class="col-md-12">
                            <p><b>Please select the users you'd like to assign the Toolbox talk to</b></p>
                            @if ($talk->assignedTo())
                                <input type="hidden" name="add_users" value="new">
                            @endif
                            <div class="col-md-12" id="user_div">
                                <div class="form-group {!! fieldHasError('user_list', $errors) !!}">
                                    {!! Form::label('user_list', 'User(s)', ['class' => 'control-label']) !!}
                                    {!! Form::select('user_list', Auth::user()->company->usersSelect('ALL', 1),
                                         null, ['class' => 'form-control select2', 'name' => 'user_list[]', 'multiple' => 'multiple', 'width' => '100%']) !!}
                                    {!! fieldErrorMessage('user_list', $errors) !!}
                                </div>
                            </div>
                            <div class="col-md-12" id="company_div">
                                <div class="form-group {!! fieldHasError('company_list', $errors) !!}">
                                    {!! Form::label('company_list', 'Company(s)', ['class' => 'control-label']) !!}
                                    {!! Form::select('company_list', Auth::user()->company->companiesSelect('ALL'),
                                         null, ['class' => 'form-control select2', 'name' => 'company_list[]', 'multiple' => 'multiple']) !!}
                                    {!! fieldErrorMessage('company_list', $errors) !!}
                                </div>
                            </div>
                            <div class="col-md-12" id="role_div">
                                <div class="form-group {!! fieldHasError('role_list', $errors) !!}">
                                    {!! Form::label('role_list', 'Roles(s)', ['class' => 'control-label']) !!}
                                    {!! Form::select('role_list', App\Models\Misc\Role2::where('company_id', Auth::user()->company_id)->orderBy('name')->pluck('name', 'id')->toArray(),
                                         null, ['class' => 'form-control select2', 'name' => 'role_list[]', 'multiple' => 'multiple']) !!}
                                    {!! fieldErrorMessage('role_list', $errors) !!}
                                </div>
                            </div>
                            <div class="col-md-12" id="special_div">
                                <div class="form-group {!! fieldHasError('special_list', $errors) !!}">
                                    {!! Form::label('special_list', 'Special', ['class' => 'control-label']) !!}
                                    {!! Form::select('special_list', ['primary_contact' => 'Primary Contacts', 'supply_fit' => "Supply & Fit Providers", "supply" => "Supply only"],
                                         null, ['class' => 'form-control select2', 'name' => 'special_list[]', 'multiple' => 'multiple']) !!}
                                    {!! fieldErrorMessage('special_list', $errors) !!}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" data-dismiss="modal" class="btn dark btn-outline">Close</button>
                    <button type="submit" class="btn green">Save</button>
                </div>
                {!! Form::close() !!}
            </div>
        </div>
    </div>

    <!-- Archive Modal -->
    <div id="modal_archive" class="modal fade bs-modal-sm" tabindex="-1" role="basic" aria-hidden="true">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                    <h4 class="modal-title text-center"><b>Archive Toolbox Talk</b></h4>
                </div>
                <div class="modal-body">
                    <p class="text-center">You are about to make this toolbox talk no longer <span style="text-decoration: underline">active</span> and archive it.</p>
                    <p class="font-red text-center"><i class="fa fa-exclamation-triangle"></i> Once archived only {{ $talk->owned_by->name }} can reactivite it.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <a href="/safety/doc/toolbox3/{{ $talk->id }}/archive" class="btn green">Continue</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Modal -->
    <div id="modal_archive" class="modal fade bs-modal-sm" tabindex="-1" role="basic" aria-hidden="true">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                    <h4 class="modal-title text-center"><b>Archive Toolbox Talk</b></h4>
                </div>
                <div class="modal-body">
                    <p class="text-center">You are about to make this toolbox talk no longer <span style="text-decoration: underline">active</span> and archive it.</p>
                    <p class="font-red text-center"><i class="fa fa-exclamation-triangle"></i> Once archived only {{ $talk->owned_by->name }} can reactivite it.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <a href="/safety/doc/toolbox3/{{ $talk->id }}/archive" class="btn green">Continue</a>
                    <!-- <li><a href="/safety/doc/toolbox3/{{ $talk->id }}/destroy"><i class="fa fa-trash"></i> Delete</a></li> -->
                </div>
            </div>
        </div>
    </div>
@stop <!-- END Content -->


@section('page-level-plugins-head')
    <link href="/assets/global/plugins/select2/css/select2.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/select2/css/select2-bootstrap.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/bootstrap-summernote/summernote.css" rel="stylesheet" type="text/css"/>
@stop

@section('page-level-plugins')
    <script src="/assets/global/plugins/select2/js/select2.full.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/bootstrap-summernote/summernote.min.js" type="text/javascript"></script>
@stop

@section('page-level-scripts')
    {{-- Metronic + custom Page Scripts --}}
    <script src="/assets/pages/scripts/components-select2.min.js" type="text/javascript"></script>
    <script>

        $(document).ready(function () {
            /* Select2 */
            $("#user_list").select2({placeholder: "Select", width: '100%',});
            $("#company_list").select2({placeholder: "Select", width: '100%'});
            $("#role_list").select2({placeholder: "Select", width: '100%'});
            $("#special_list").select2({placeholder: "Select", width: '100%'});
            $("#site_list").select2({placeholder: "Select", width: '100%'});

            $("#test_alert").click(function (e) {
                e.preventDefault();
                swal($("#name").val(), $("#info").val());
            })

            /*
            // On Change Assign To
            $("#assign_to").change(function () {
                showAssignedList();
            });


            function showAssignedList() {
                $("#user_div").hide();
                $("#company_div").hide();
                $("#group_div").hide();
                $("#role_div").hide();
                $("#special_div").hide();
                $("#site_div").hide();
                $("#type").val('user');

                // Assign to User selected
                if ($("#assign_to").val() == 'user')
                    $("#user_div").show();
                // Assign to Company selected
                if ($("#assign_to").val() == 'company')
                    $("#company_div").show();
                // Assign to Group selected
                if ($("#assign_to").val() == 'group')
                    $("#group_div").show();
                // Assign to Role selected
                if ($("#assign_to").val() == 'role')
                    $("#role_div").show();
                // Assign to Special selected
                if ($("#assign_to").val() == 'special')
                    $("#special_div").show();
                // Assign to Group selected
                if ($("#assign_to").val() == 'site') {
                    $("#site_div").show();
                    $("#type").val('site');
                }
            }

            showAssignedList();

             */


            $('#delete').on('click', function () {
                var id = "{{ $talk->id }}";
                var name = "{{ $talk->name }}";
                swal({
                    title: "Are you sure?",
                    text: "You will not be able to restore this talk!<br><b>" + name + "</b>",
                    showCancelButton: true,
                    cancelButtonColor: "#555555",
                    confirmButtonColor: "#E7505A",
                    confirmButtonText: "Yes, delete it!",
                    allowOutsideClick: true,
                    html: true,
                }, function () {
                    window.location = "/safety/doc/toolbox3/" + id + '/destroy';
                });
            });
        });
    </script>
@stop

