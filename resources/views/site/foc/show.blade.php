@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        @if (Auth::user()->hasAnyPermissionType('site'))
            <li><a href="/site">Sites</a><i class="fa fa-circle"></i></li>
        @endif
        <li><a href="/site/foc">FOC Requirements</a><i class="fa fa-circle"></i></li>
        <li><span>View items</span></li>
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
                            <span class="caption-subject bold uppercase font-green-haze"> FOC Requirements</span>
                            <span class="caption-helper">ID: {{ $foc->id }}</span>
                        </div>
                    </div>
                    <div class="portlet-body">
                        <div class="page-content-inner">
                            <form method="POST" action="{{ action([\App\Http\Controllers\Site\SiteFocController::class, 'update'], $foc->id) }}" class="horizontal-form">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" id="site_id" value="{{ $foc->site_id }}">

                                @include('form-error')
                                <div class="row">
                                    {{-- Site Details --}}
                                    <div class="col-md-6">
                                        <h4>Site Details</h4>
                                    </div>
                                    <div class="col-md-6">
                                        {{-- FOC Stage --}}
                                        @php
                                            $focStage = $foc->stage ?: $foc->calculateStage();
                                        @endphp

                                        <h2 style="margin: 0; padding-right: 20px">
                                            <span class="pull-right font-red hidden-sm hidden-xs">{{ strtoupper($focStage) }}</span>
                                            <span class="text-center font-red visible-sm visible-xs">{{ strtoupper($focStage) }}</span>
                                        </h2>
                                    </div>
                                </div>
                                <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                                <div class="row">
                                    {{-- Site Details --}}
                                    <div class="col-md-8">
                                        @if ($foc->site)
                                            <b>{{ $foc->site->name }}</b><br>
                                            {{ $foc->site->full_address }}<br>
                                            <b>Supervisor:</b> {{ ($foc->site->supervisor_id) ? $foc->site->supervisor->name : 'none'}}<br>
                                        @endif
                                    </div>
                                    <div class="col-md-4">
                                        @if ($foc->site)
                                            <div class="row">
                                                <div class="col-md-6" style="text-align: right">
                                                    Prac Completion<br>
                                                    Damage Deposit<br>
                                                    Completion Pack Sent<br>
                                                </div>
                                                <div class="col-md-6">
                                                    {!! ($foc->site->completion_signed) ? $foc->site->completion_signed->format('d/m/Y') : '-' !!}<br>
                                                    {{ ($foc->site->damage_deposit) ? "$" . number_format($foc->site->damage_deposit, 2) : '-'}}<br>
                                                    {{ ($foc->site->cp_sent_client) ? $foc->site->cp_sent_client->format('d/m/Y') : '-'}}<br>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                <br>
                                <h4>Attachments</h4>
                                <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                                <div class="row">
                                    <div class="col-md-9">
                                        {{-- Attachments --}}
                                        @php
                                            $attachments = $foc->attachments;
                                            $images = $attachments->where('type', 'image');
                                            $files  = $attachments->where('type', 'file');
                                        @endphp
                                        @if ($attachments->isNotEmpty())
                                            {{-- Image attachments --}}
                                            @if ($images->isNotEmpty())
                                                <div class="row" style="margin: 0">
                                                    @foreach ($images as $attachment)
                                                        <div style="width: 60px; float: left; padding-right: 5px">
                                                            @if(Auth::user()->allowed2('del.site.foc', $foc))
                                                                <i class="fa fa-times font-red deleteFile" style="cursor:pointer" data-name="{{ $attachment->name }}" data-did="{{$attachment->id}}"></i>
                                                            @endif
                                                            <a href="{{ $attachment->url }}" target="_blank" data-lity>
                                                                <img src="{{ $attachment->url }}" class="thumbnail img-responsive img-thumbnail">
                                                            </a>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endif

                                            {{-- File attachments --}}
                                            @if ($files->isNotEmpty())
                                                <div class="row" style="margin: 0">
                                                    @foreach ($files as $attachment)
                                                        <i class="fa fa-file-text-o"></i> &nbsp; <a href="{{ $attachment->url }}" target="_blank"> {{ $attachment->name }}</a>
                                                        @if(Auth::user()->allowed2('del.site.foc', $foc))
                                                            <i class="fa fa-times font-red deleteFile" style="cursor:pointer" data-name="{{ $attachment->name }}" data-did="{{$attachment->id}}"></i>
                                                        @endif
                                                        <br>
                                                    @endforeach
                                                </div>
                                            @endif
                                        @endif
                                    </div>
                                    {{-- Add Attachments --}}
                                    <div class="col-md-3" style="background: #f1f0ef;">
                                        <x-form.filepond/>
                                        <br><br>
                                    </div>
                                </div>


                                {{-- Under Review + assign to super --}}
                                <h4>FOC Details</h4>
                                <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                                <div class="row">
                                    {{-- Assigned Supervisor --}}
                                    <div class="col-md-4">
                                        @if ($foc->status && Auth::user()->allowed2('sig.site.foc', $foc))
                                            <x-form.select name="super_id" label="FOC Supervisor" plugin="select2" style="width:100%">
                                                <option value=""></option>
                                                @foreach (Auth::user()->company->supervisors()->sortBy('name') as $super)
                                                    <option value="{{ $super->id }}" {{ ($super->id == $foc->super_id) ? 'selected' : '' }}>{{ $super->name }}</option>
                                                @endforeach
                                            </x-form.select>
                                        @else
                                            <x-form.input name="assigned_super_text" label="FOC Supervisor" :value="$foc->super_id ? $foc->supervisor->name : '-'" readonly/>
                                        @endif
                                    </div>
                                    <div class="col-md-2">
                                        <x-form.select name="portal_fee_paid" id="portal_fee_paid" label="Portal fee paid" :options="['' => '', '1' => 'Yes', '0' => 'No']" :value="$foc->portal_fee_paid"/>
                                    </div>
                                    <div class="col-md-2">
                                        <x-form.select name="wbo_waiting" id="wbo_waiting" label="Waiting on WBO" :options="['' => '', '1' => 'Yes', '0' => 'No']" :value="$foc->wbo_waiting"/>
                                    </div>
                                    {{-- FOC Requested --}}
                                    <div class="col-md-2">
                                        <x-form.datepicker name="foc_requested" label="FOC Requested" :value="$foc->foc_requested?->format('d/m/Y')" format="dd/mm/yyyy" clear-button/>
                                    </div>
                                    <div class="col-md-2">
                                        {{-- FOC Received --}}
                                        <x-form.input name="foc_recieved" label="FOC Received" :value="($foc->site->oc_rcvd_date) ? $foc->site->oc_rcvd_date->format('d/m/Y') : ''" readonly/>
                                    </div>
                                </div>
                        </div>
                        <div class="row">
                            <div class="col-md-10">
                                <x-form.textarea name="notes" id="notes" label="Notes" :value="$foc->notes"/>
                            </div>

                            @if (Auth::user()->allowed2('edit.site.foc', $foc))
                                <div class="col-md-1 pull-right">
                                    <button id="submit" type="submit" name="save" class="btn blue" style="margin-top: 25px">Save</button>
                                </div>
                            @endif

                        </div>
                        <div class="row">
                            {{-- Assigned Supervisor --}}
                            <div class="col-md-5"></div>
                        </div>
                        <br>


                        {{-- FOC Items --}}
                        <div class="row">
                            <div class="col-md-12">
                                <livewire:site.foc.items :foc-id="$foc->id"/>
                            </div>
                        </div>

                        {{-- Notes --}}
                        <div class="row">
                            <div class="col-md-12">
                                <livewire:misc.actions table="site_foc" :table-id="$foc->id"/>
                            </div>
                        </div>

                        {{-- ToDos--}}
                        <div class="row">
                            <div class="col-md-12">
                                <h3>Assigned Tasks
                                    {{-- Show add if user has permission to edit foc --}}
                                    @if ($foc->status && Auth::user()->hasAnyRole2('con-construction-manager|con-administrator|web-admin|mgt-general-manager'))
                                        <a href="/todo/create/site_foc_task/{{ $foc->id}}"
                                           class="btn btn-circle green btn-outline btn-sm pull-right"
                                           data-original-title="Add">Add</a>
                                    @endif
                                </h3>
                                @if ($foc->todos()->count())
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
                                        @foreach($foc->todos() as $todo)
                                            <tr>
                                                <td>
                                                    <div class="text-center"><a href="/todo/{{ $todo->id }}"><i class="fa fa-search"></i></a></div>
                                                </td>
                                                <td>
                                                    {{ $todo->info }}<br><br><i>Assigned to: {{ $todo->assignedToBySBC() }}</i>
                                                    @if ($todo->comments)
                                                        <br><b>Comments:</b> {{ $todo->comments }}
                                                    @endif
                                                    @php
                                                        $attachments = $todo->attachments;
                                                        $images = $attachments->where('type', 'image');
                                                        $files  = $attachments->where('type', 'file');
                                                    @endphp
                                                    @if ($attachments->isNotEmpty())
                                                        <hr style="margin: 10px 0px; padding: 0px;">
                                                        {{-- Image attachments --}}
                                                        @if ($images->isNotEmpty())
                                                            <div class="row" style="margin: 0">
                                                                @foreach ($images as $attachment)
                                                                    <div style="width: 60px; float: left; padding-right: 5px">
                                                                        <a href="{{ $attachment->url }}" target="_blank" data-lity>
                                                                            <img src="{{ $attachment->url }}" class="thumbnail img-responsive img-thumbnail">
                                                                        </a>
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                        @endif

                                                        {{-- File attachments --}}
                                                        @if ($files->isNotEmpty())
                                                            <div class="row" style="margin: 0">
                                                                @foreach ($files as $attachment)
                                                                    <i class="fa fa-file-text-o"></i> &nbsp; <a href="{{ $attachment->url }}" target="_blank"> {{ $attachment->name }}</a><br>
                                                                @endforeach
                                                            </div>
                                                        @endif
                                                    @endif
                                                    <br>
                                                    {{-- Old Image attachments --}}
                                                    @if ($todo->attachment)
                                                        <a href="{{ $todo->attachmentUrl }}" data-lity class="btn btn-xs blue"><i class="fa fa-picture-o"></i></a>
                                                    @endif
                                                </td>
                                                <td>{!! App\User::findOrFail($todo->created_by)->full_name  !!}<br>{{ $todo->created_at->format('d/m/Y')}}</td>
                                                    <?php
                                                    $done_by = App\User::find($todo->done_by);
                                                    $done_at = ($done_by) ? $todo->done_at->format('d/m/Y') : '';
                                                    $done_by = ($done_by) ? $done_by->full_name : 'unknown';
                                                    ?>
                                                <td>@if ($todo->status && !$todo->done_by)
                                                        <span class="font-red">Outstanding</span>
                                                    @else
                                                        {!! $done_by  !!}<br>{{ $done_at }}
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                @endif
                            </div>
                        </div>

                        </form>

                        <hr>
                        <div class="pull-right" style="min-height: 50px">
                            <a href="/site/foc" class="btn default">Back</a>

                            @if (!$foc->master && Auth::user()->allowed2('edit.site.foc', $foc) && in_array((int) $foc->status, [2, -1], true))
                                <button type="button" class="btn green" onclick="updateFocStatus(1)">Make Active</button>
                            @endif

                            @if (!$foc->master && Auth::user()->allowed2('del.site.foc', $foc) && in_array((int) $foc->status, [1, 2], true))
                                <button type="button" class="btn red" onclick="updateFocStatus(-1)">Disable</button>
                            @endif
                        </div>
                        <br><br>
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
    <link href="/assets/global/plugins/bootstrap-datepicker/css/bootstrap-datepicker3.min.css" rel="stylesheet" type="text/css"/>
    <script type="text/javascript">var html5lightbox_options = {watermark: "", watermarklink: ""};</script>
@stop

@section('page-level-plugins')
    <script src="/assets/global/plugins/select2/js/select2.full.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js" type="text/javascript"></script>
    <script src="https://unpkg.com/filepond/dist/filepond.min.js"></script> {{-- FilePond --}}
    <script src="/js/libs/html5lightbox/html5lightbox.js" type="text/javascript"></script>
@stop

@section('page-level-scripts')
    {{-- Metronic + custom Page Scripts --}}
    <script src="/assets/pages/scripts/components-date-time-pickers.min.js" type="text/javascript"></script>
    <script src="/js/filepond-basic.js" type="text/javascript"></script>

    <script>
        $.ajaxSetup({headers: {'X-CSRF-Token': $('meta[name=token]').attr('value')}});

        $(document).ready(function () {
            /* Select2 */
            $("#super_id").select2({placeholder: "Select supervisor", width: '100%'});

            /* Delete attachment */
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
                    window.location = '/site/foc/' + {{ $foc->id }} + '/delfile/' + id;
                });
            });
        });

        function updateFocStatus(status) {
            $.ajax({
                url: '/site/foc/{{ $foc->id }}/update',
                type: 'PATCH',
                data: {status: status},
                success: function () {
                    window.location.href = '/site/foc/{{ $foc->id }}';
                },
                error: function () {
                    alert('Failed to update FOC status');
                }
            });
        }
    </script>
@stop
