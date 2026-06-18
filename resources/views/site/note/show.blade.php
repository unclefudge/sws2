@php
    $notes_label = 'Note';
@endphp
@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        @if (Auth::user()->hasAnyPermissionType('site'))
            <li><a href="/site">Sites</a><i class="fa fa-circle"></i></li>
        @endif
        <li><a href="/site/note">Site Notes</a><i class="fa fa-circle"></i></li>
        <li><span>View</span></li>
    </ul>
@stop

@section('content')
    <div class="page-content-inner">
        <div class="row">
            <div class="col-md-12">
                <div class="portlet light bordered">
                    <div class="portlet-title">
                        <div class="caption">
                            <span class="caption-subject font-green-haze bold uppercase">Site Note</span>
                            <span class="caption-helper">ID: {{ $note->id }}</span>
                        </div>
                    </div>
                    <div class="portlet-body form">
                        <form method="POST" action="{{ action([App\Http\Controllers\Site\SiteNoteController::class, 'uploadAttachment'], $note->id) }}" class="horizontal-form" enctype="multipart/form-data">
                            @csrf
                        <div class="form-body">
                            <div class="row">
                                {{-- Site --}}
                                <div class="col-md-6">
                                    <x-form.input name="site_name" label="Site" :value="$note->site->name" id="site_name" readonly/>
                                    <x-form.hidden name="site_id" :value="$note->site_id"/>
                                </div>
                                {{-- Category --}}
                                <div class="col-md-4">
                                    <x-form.input name="category_name" label="Category" :value="($note->category_id) ? $note->category->name : 'none'" id="category_name" readonly/>
                                </div>
                            </div>

                            {{-- Costing fields --}}
                            @if ($note->category_id == '15')
                                <div class="row">
                                    <div class="col-md-3">
                                        <x-form.input name="costing_extra_credit" label="Credit / Extra" :value="$note->costing_extra_credit" readonly/>
                                    </div>
                                    <div class="col-md-3">
                                        <x-form.input name="costing_item" label="New item / In Lieu of" :value="$note->costing_item" readonly/>
                                    </div>
                                    <div class="col-md-3">
                                        <x-form.input name="costing_priority" label="Priority" :value="$note->costing_priority" readonly/>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-3">
                                        <x-form.input name="costing_room" label="Room" :value="$note->costing_room" readonly/>
                                    </div>
                                    <div class="col-md-5">
                                        <x-form.input name="costing_location" label="Location" :value="$note->costing_location" readonly/>
                                    </div>
                                </div>
                                @php
                                    $notes_label = 'Description';
                                @endphp
                            @endif

                            {{-- 16. Approved Variation, 19. For Issue to Client,  20. TBA Site Variations, 93. Wet Calls  --}}
                            @if (in_array($note->category_id, [16, 19, 20, 93]))
                                <div class="row">
                                    <div class="col-md-6">
                                        <x-form.input name="variation_name" label="Variation Name" :value="$note->variation_name" readonly/>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <x-form.textarea name="variation_info" label="Variation Description" :value="$note->variation_info" rows="5" readonly/>
                                    </div>
                                </div>
                            @endif

                            {{-- 16. + 19. additional fields --}}
                            @if (in_array($note->category_id, [16, 19, 93]))
                                <div class="row">
                                    <div class="col-md-3">
                                        <x-form.input name="variation_net" label="Net Cost <span class='font-grey-silver'>(Admin use only)</span>" :value="$note->variation_net" readonly/>
                                    </div>

                                    <div class="col-md-3">
                                        <x-form.input name="variation_cost" label="Gross Cost (incl GST + 20% margin)" :value="$note->variation_cost" readonly/>
                                    </div>

                                    @if (in_array($note->category_id, [16, 19]))
                                        <div class="col-md-3">
                                            <x-form.input name="costing_extra_credit" label="Credit / Extra" :value="$note->costing_extra_credit" readonly/>
                                        </div>
                                    @endif
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <x-form.input name="variation_days" label="Total Extension Days (discussed with Client) Description <span class='font-grey-silver'>(Admin use only)</span>" :value="$note->variation_days" readonly/>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12"><b>Cost Centres & Item Details:</b></div>
                                </div>
                                {{-- Cost centre & Details --}}
                                @foreach ($note->costs as $cost)
                                    <div class="row">
                                        <div class="col-md-3">{{$cost->category->name}}</div>
                                        <div class="col-md-9">{{$cost->details}}</div>
                                    </div>
                                @endforeach
                                <br>
                                @php $notes_label = 'Note (Admin use only)'; @endphp
                            @endif

                            {{--  Prac Completion Fields --}}
                            @if (in_array($note->category_id, ['89']))
                                <div class="row">
                                    <div class="col-md-3">
                                        <x-form.input name="prac_notified" label="Prac Notified" help="Date you will be delivering letters. Please make sure you have given 7 working days notice" :value="($note->prac_notified) ? $note->prac_notified->format('d/m/Y') : ''" readonly/>
                                    </div>

                                    <div class="col-md-3">
                                        <x-form.input name="prac_meeting_date" label="Prac Meeting Date" help="Date you will be holding the Prac Meeting with the Client." :value="($note->prac_meeting) ? $note->prac_meeting->format('d/m/Y') : ''" readonly/>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="prac_meeting_time" class="control-label"> Prac Meeting Time
                                                <a href="javascript:;" class="popovers" data-container="body" data-trigger="hover"
                                                   data-content="Time you will be holding the Prac Meeting with the Client."> <i class="fa fa-question-circle font-grey-silver"></i>
                                                </a>
                                            </label>
                                            <input type="text" name="prac_notified" id="prac_notified" value="{{ old('prac_notified', ($note->prac_meeting) ? $note->prac_meeting->format('h:i A') : '') }}" class="form-control" readonly>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            {{-- Early Occupation Fields --}}
                            @if (in_array($note->category_id, ['94']))
                                <div class="row">
                                    <div class="col-md-3">
                                        <x-form.input name="occupation_date" label="Date of Occupancy" help="Date client took occupancy" :value="($note->occupation_date) ? $note->occupation_date->format('d/m/Y') : ''" readonly/>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-12">
                                        <x-form.textarea name="occupation_area" label="Areas Client has taken Occupation of" :value="$note->occupation_area" readonly/>
                                        <br><br>
                                    </div>
                                </div>
                            @endif

                            {{-- Response Required --}}
                            @if (in_array($note->category_id, ['12, 13, 14']))
                                <div class="row">
                                    <div class="col-md-3">
                                        <x-form.input name="response_req" label="Response Required" :value="($note->response_req) ? 'Yes' : 'No - FYI only'" readonly/>
                                    </div>
                                </div>
                            @endif

                            {{-- Notes --}}
                            <div class="row">
                                <div class="col-md-12">
                                    <b>{{ $notes_label }}:</b><br>
                                    {!! nl2br($note->notes) !!}
                                </div>
                            </div>
                        </div>

                        {{-- Attachments --}}
                        <h5><b>Attachments</b> <small><a id="edit-attachment-link">EDIT</a></small></h5>
                        <hr style="margin: 10px 0px; padding: 0px;">
                        @php
                            $attachments = $note->attachments;
                            $images = $attachments->where('type', 'image');
                            $files  = $attachments->where('type', 'file');
                        @endphp
                        @if ($attachments->isNotEmpty())
                            {{-- Image attachments --}}
                            @if ($images->isNotEmpty())
                                <div class="row" style="margin: 0">
                                    @foreach ($images as $attachment)
                                        <div style="width: 60px; float: left; padding-right: 5px">
                                            @if (Auth::user()->hasPermission2("del.site.note"))
                                                <i class="fa fa-times font-red deleteFile edit-toggle" style="cursor:pointer; display:none;" data-name="{{ $attachment->name }}" data-attachid="{{$attachment->id}}"></i>
                                            @endif
                                            <a href="{{ $attachment->url }}" target="_blank" data-lity><img src="{{ $attachment->url }}" class="thumbnail img-responsive img-thumbnail"></a>
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            {{-- File attachments --}}
                            @if ($files->isNotEmpty())
                                <div class="row" style="margin: 0">
                                    @foreach ($files as $attachment)
                                        <i class="fa fa-file-text-o"></i> &nbsp; <a href="{{ $attachment->url }}" target="_blank"> {{ $attachment->name }}</a>
                                        @if (Auth::user()->hasPermission2("del.site.note"))
                                            <i class="fa fa-times font-red deleteFile edit-toggle" style="cursor:pointer; display:none;" data-name="{{ $attachment->name }}" data-attachid="{{$attachment->id}}"></i>
                                        @endif
                                        <br>
                                    @endforeach
                                </div>
                            @endif
                        @else
                            None
                        @endif

                        <div class="row edit-toggle" style="display: none">
                            <div class="col-md-6">
                                <h5 id="uploads_label">Upload Attachments</h5>
                                <x-form.filepond/><br><br>
                            </div>
                            <div class="col-md-6">
                                <br><br>
                                <button type="submit" class="btn green" id="submit"> Save</button>
                            </div>
                        </div>

                        </form>

                        {{-- Reply --}}
                        @if ($note->extraNotes->count())
                            <h5><b>Replies:</b></h5>
                            <hr style="margin: 10px 0px; padding: 0px;">
                            <table class="table table-striped table-bordered">
                                <tr class="mytable-header text-bold">
                                    <td>Date</td>
                                    <td>Reply</td>
                                </tr>
                                @foreach ($note->extraNotes->sortByDesc('created_at') as $extra)
                                    <tr>
                                        <td>{{$extra->created_at->format('d/m/Y')}}</td>
                                        <td>{!! nl2br($extra->notes) !!}</td>
                                    </tr>
                                @endforeach
                            </table>
                        @endif

                        <br><br>
                        <div class="form-actions right">
                            <a href="/site/note" class="btn default"> Back</a>
                            @if(Auth::user()->hasAnyRole2('web-admin|mgt-general-manager'))
                                <a href="/site/note/{{$note->id}}/edit" class="btn green"> Edit </a>
                            @endif
                            @if (in_array($note->category_id, [19,20]))
                                <a href="/site/{{$note->id}}/notes/convert" class="btn green"> Copy & Create To Approved Site Variation </a>
                            @endif
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
@stop

@section('page-level-plugins')
    <script src="/assets/global/plugins/select2/js/select2.full.min.js" type="text/javascript"></script>
    <script src="https://unpkg.com/filepond/dist/filepond.min.js"></script> {{-- FilePond --}}
@stop

@section('page-level-scripts')
    {{-- Metronic + custom Page Scripts --}}
    <script src="/js/filepond-basic.js" type="text/javascript"></script>
    <script>
        $(document).ready(function () {
            $('#edit-attachment-link').on('click', function (e) {
                e.preventDefault();
                $('.edit-toggle').toggle();

                // Toggle link text
                if ($(this).text() === 'EDIT') {
                    $(this).text('DONE');
                } else {
                    $(this).text('EDIT');
                }
            });

            // Delete from Ashbys
            $('.deleteFile').click(function (e) {
                e.preventDefault();
                var attach_id = $(this).data('attachid');
                var name = $(this).data('name');
                //alert(taskid);

                swal({
                    title: "Are you sure?",
                    text: "You will not be able to recover this attachment!<br><b>" + name + "</b>",
                    showCancelButton: true,
                    cancelButtonColor: "#555555",
                    confirmButtonColor: "#E7505A",
                    confirmButtonText: "Yes, delete it!",
                    allowOutsideClick: true,
                    html: true,
                }, function () {
                    window.location.href = "/site/note/{{$note->id}}/delattachment/" + attach_id;
                });
            });
        });
    </script>
@stop

