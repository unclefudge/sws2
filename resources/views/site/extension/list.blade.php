@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        @if (Auth::user()->hasAnyPermissionType('site'))
            <li><a href="/site">Sites</a><i class="fa fa-circle"></i></li>
        @endif
        <li><span>Contract Time Extensions</span></li>
    </ul>
@stop

@section('content')
    <div class="page-content-inner">
        <div class="row">
            <div class="col-md-12">
                <div class="portlet light ">
                    <div class="portlet-title">
                        <div class="caption font-dark">
                            <i class="icon-layers"></i>
                            <span class="caption-subject bold uppercase font-green-haze">Contract Time Extensions</span>
                        </div>
                        <div class="actions">
                            <a class="btn btn-circle green btn-outline btn-sm" href="{{ $extension->attachmentUrl }}" target="_blank" data-original-title="PDF"><i class="fa fa-file-pdf-o"></i> PDF</a>

                            @if(Auth::user()->hasPermission2('del.site.extension'))
                                <a class="btn btn-circle green btn-outline btn-sm" href="/site/extension/settings" data-original-title="Setting">Settings</a>
                            @endif
                        </div>
                    </div>
                    <div class="portlet-body">
                        <h3>Week of {{ $extension->date->format('d/m/Y') }}</h3>
                        <table class="table table-striped table-bordered table-hover order-column" id="table1">
                            <thead>
                            <tr class="mytable-header">
                                <th width="25%">Site</th>
                                <th width="5%">Supervisor</th>
                                <th width="8%">Forecast Completion</th>
                                <th width="25%">Extend Reasons</th>
                                <th>Extend Notes</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach ($data as $row)
                                <tr>
                                    <td id="sitename-{{$row['id']}}">{{ $row['name'] }}</td>
                                    <td>{{ $row['super_initials'] }}</td>
                                    <td>{{ $row['completion_date'] }}</td>
                                    <td class="hoverDiv editField" id="reason-{{$row['id']}}-td">
                                        {{ $row['extend_reasons_text'] }}
                                        <input type="hidden" id="reason-{{$row['id']}}-array" name="reason-{{$row['id']}}-array[]" value="{{ $row['extend_reasons'] }}">
                                    </td>
                                    <td class="hoverDiv editField" id="note-{{$row['id']}}-td">
                                        <div id="note-{{$row['id']}}">{!! nl2br($row['notes']) !!}</div>
                                        <input type="hidden" id="note-{{$row['id']}}-s" value="{!! $row['notes'] !!}">
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>

                        <div class="row">
                            <div class="col-md-12">
                                <h5><b>CONTRACT TIME EXTENSIONS ELECTRONIC SIGN-OFF</b></h5>
                                <p>The above supply items have been verified by the site construction supervisor.</p>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-3 text-right">Site Manager:</div>
                            <div class="col-sm-9">
                                @if ($extension->approved_by)
                                    {!! \App\User::find($extension->approved_by)->full_name !!}, &nbsp;{{ $extension->approved_at->format('d/m/Y') }}
                                @elseif ($extension->sites->count() != $extension->sitesCompleted()->count())
                                    <span class="font-grey-silver">Waiting for ({{ ($extension->sites->count()  - $extension->sitesCompleted()->count()) }}) sites to be completed</span>
                                @elseif (Auth::user()->hasAnyRole2('con-construction-manager|web-admin|mgt-general-manager'))
                                    <button class="btn blue btn-xs btn-outline sbold uppercase margin-bottom signoff">Sign Off</button>
                                @else
                                    <span class="font-red">Pending</span>
                                @endif
                            </div>
                        </div>
                        <br>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END PAGE CONTENT INNER -->

    <!--  Edit Modal -->
    <div id="modal_edit" class="modal fade bs-modal-lg" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                    <h4 class="modal-title" id="site_name"></h4>
                </div>
                <div class="modal-body">
                    {!! Form::model('extension', ['method' => 'POST', 'action' => ['Site\SiteExtensionController@updateJob'], 'class' => 'horizontal-form', 'files' => true, 'id'=>'talk_form']) !!}
                    <input type="hidden" name="site_id" id="site_id" value="">
                    <input type="text" name="approved_by" id="approved_by" value="{{ $extension->approved_by }}">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                {!! Form::label('reasons', 'Extend Reasons', ['class' => 'control-label']) !!}
                                {!! Form::select('reasons', $extend_reasons, null, ['class' => 'form-control select2', 'id' => 'reasons', 'name' => 'reasons[]', 'multiple', 'width' => '100%']) !!}
                            </div>
                        </div>
                    </div>
                    kkkkk
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                {!! Form::label('extension_notes', 'Extend notes', ['class' => 'control-label']) !!}
                                {!! Form::textarea('extension_notes', null, ['class' => 'form-control', 'rows' => 5, 'id' => 'extension_notes']) !!}
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
@stop

@section('page-level-plugins-head')
@stop

@section('page-level-styles-head')
    <link href="/assets/global/plugins/select2/css/select2.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/select2/css/select2-bootstrap.min.css" rel="stylesheet" type="text/css"/>
@stop

@section('page-level-plugins')
    <script src="/assets/global/plugins/select2/js/select2.full.min.js" type="text/javascript"></script>
@stop

@section('page-level-scripts') {{-- Metronic + custom Page Scripts --}}
<script type="text/javascript">
    $(document).ready(function () {
        /* Select2 */
        $("#reasons").select2({placeholder: "Select one or more", width: '100%'});

        $(".editField").click(function (e) {
            console.log('hhhh');
            if (!$("#approved_by").val()) {
                var event_id = e.target.id.split('-');
                var site_id = event_id[1];
                $("#site_id").val(site_id);
                $("#site_name").text($("#sitename-" + site_id).text());

                // Extension reason + notes
                $("#extension_notes").val($("#note-" + site_id).text());
                var reason_array_str = $("#reason-" + site_id + "-array").val();
                var reason_array = reason_array_str.split(',');

                $("#reasons").val(reason_array);
                $("#reasons").trigger('change');

                $("#modal_edit").modal('show');
            }
        });

        $(".signoff").click(function (e) {
            e.preventDefault();
            window.location.href = "/site/extension/{{$extension->id}}/signoff";
        });

    });


</script>
@stop