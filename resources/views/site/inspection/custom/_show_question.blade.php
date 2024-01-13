<?php
$val = null;
$qLogic = (count($question->logic)) ? 'data-logic="true"' : '';
$response = $question->response($form->id);
if (count($response))
    $val = ($question->multiple) ? $response->pluck('value')->toArray() : $response->first()->value;

// Highlight required fields if form marked 'complete'
$highlight_required = false;
if ($showrequired && $question->required) {
    if ($question->type == 'media')
        $highlight_required = ($question->files($form->id)->count()) ? false : true; // check for media
    elseif ($question->multiple)
        $highlight_required = ($val) ? false : true;  // non empty array for multi select/button response
    else
        $highlight_required = ($val || $val == '0') ? false : true; // val not blank/null for text/textarea response
}
?>


<div id="qdiv-{{$question->id}}">
    @if ($form->status)
        {{-- Active Form - allow edit --}}
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                        <?php $required = ($question->required) ? "<small><span class='fa fa-thin fa-asterisk font-red' style='opacity: 0.7'></span></small>" : '' ?>
                    <label for="name" class="control-label {{ ($highlight_required) ? 'font-red' : ''}}" style="font-size: 18px">{!! $question->name !!} {!! $required  !!}
                        {{--}}<small>T:{{ $question->type }} TS: {{ $question->type_special }} V:{!! (is_array($val)) ? print_r(implode(',', $val)) : $val !!}</small>--}}
                    </label>
                    @if ($question->helper)
                        <br>
                        <div id="helper{{$question->id}}">{!! $question->helper !!}</div>
                        <br>
                    @endif

                    @switch($question->type)
                        @case('text')
                            {{-- Text --}}
                            @php
                                $siteAdd = ($question->type_special == 'site_address') ? 'siteAddress' : '';
                                $siteSup = ($question->type_special == 'site_super') ? 'siteSuper' : '';
                            @endphp
                            <input type="text" name="q{{$question->id}}" class="form-control {{ $siteAdd }} {{ $siteSup }}" value="{{ $val }}" {{ (!$form->status) ? "disabled" : '' }}>
                            @break

                        @case('textarea')
                            {{-- Textarea --}}
                            <textarea name="q{{$question->id}}" rows="5" class="form-control" placeholder="Details" {{ (!$form->status) ? "disabled" : '' }}>{!! $val !!}</textarea>
                            @break

                        @case('date')
                            {{-- Date --}}
                            @if ($form->status)
                                <div class="input-group date date-picker">
                                    <input type="text" name="q{{$question->id}}" id="q{{$question->id}}" class="form-control form-control-inline" style="background:#FFF" data-date-format="dd-mm-yyyy" value="{{ $val }}">
                                    <span class="input-group-btn">
                                        <button class="btn default date-set" type="button"><i class="fa fa-calendar"></i></button>
                                    </span>
                                </div>
                            @else
                                <input type="text" name="q{{$question->id}}" class="form-control" value="{{ $val }}" style="width:300px" disabled>
                            @endif
                            @break
                        @case('datetime')
                            {{-- Datetime --}}
                            @if ($form->status)
                                <div class="input-group date form_datetime form_datetime bs-datetime" data-date-end-date="0d" style="width: 300px">
                                        <?php $val = ($question->id == 2 && $val) ? $val : Carbon\Carbon::now()->format('d/m/Y G:i') ?>
                                    <input type="text" name="q{{$question->id}}" class="form-control" style="background:#FFF" value="{{ $val }}">
                                    <span class="input-group-addon"><button class="btn default date-set" type="button" style="height: 34px;"><i class="fa fa-calendar"></i></button></span>
                                </div>
                            @else
                                <input type="text" name="q{{$question->id}}" class="form-control" value="{{ $val }}" style="width:300px" disabled>
                            @endif
                            @break

                        @case('select')
                            {{-- Select --}}

                            {{-- Site --}}
                            @if ($question->type_special == 'site')
                                <select id="q{{$question->id}}" name="q{{$question->id}}" class="form-control select2 siteID" style="width:100%">
                                    {!! Auth::user()->authSitesSelect2Options('view.site.list', $val, [1,2]) !!}
                                </select>
                                <div id="siteinfo{{$question->id}}"></div>
                            @endif

                            {{-- Site --}}
                            @if ($question->type_special == 'site_upcoming')
                                <select id="q{{$question->id}}" name="q{{$question->id}}" class="form-control select2 siteID" style="width:100%">
                                    {!! Auth::user()->authSitesSelect2Options('view.site.list', $val, '-1') !!}
                                </select>
                                <div id="siteinfo{{$question->id}}"></div>
                            @endif

                            {{-- Staff --}}
                            @if ($question->type_special == 'staff')
                                {!! Form::select("q$question->id", Auth::user()->company->staffSelect(null, '1'), ($val) ? $val : Auth::user()->id, ['class' => 'form-control select2', 'name' => "q$question->id", 'id' => "q$question->id"]) !!}
                            @endif

                            {{-- Special Rest--}}
                            @if ($question->type_special && !in_array($question->type_special, ['site', 'site_upcoming', 'staff']))
                                <input type="hidden" id="q{{$question->id}}" name="q{{$question->id}}" value="{{ $val }}">
                                {!! customFormSelectButtons($question->id, $val, $form->status) !!}
                            @endif

                            {{-- Other Selects--}}
                            @if (!$question->type_special)
                                @if ($question->multiple)
                                    {!! Form::select("q$question->id", $question->optionsArray(), $val, ['class' => "form-control select2", 'name' => "q$question->id[]", 'id' => "q$question->id",  'multiple', $qLogic]) !!}
                                @else
                                    {!! Form::select("q$question->id", ['' => 'Select option'] + $question->optionsArray(), $val, ['class' => "form-control select2", 'name' => "q$question->id", 'id' => "q$question->id", $qLogic]) !!}
                                @endif
                            @endif

                            @break

                        @default
                    @endswitch

                    {{-- Required check --}}
                    @if ($highlight_required)
                        <div class="font-red">You must provide a response to this question</div>
                    @endif

                </div>
            </div>
        </div>
    @else
        {{-- Completed Form - display only text response --}}
        <div class="row gggg" style="margin-bottom: 20px">
            <div class="col-md-12">
                <span class="" style="font-size:18px"><b>{!! $question->name !!}</b></span><br>
                {!! nl2br($question->responseFormatted($form->id)) !!}
                @if (is_array($val))
                    @foreach ($val as $v)
                        <input type="hidden" id="q{{$question->id}}[]" name="q{{$question->id}}[]" value="{{ $v }}" disabled>
                    @endforeach
                @else
                    <input type="hidden" id="q{{$question->id}}" name="q{{$question->id}}" value="{{ $val }}" disabled>
                @endif
            </div>
        </div>
    @endif

    {{-- Gallery --}}
    <div id="showmedia-{{$question->id}}" style="width: 400px; {{ ($question->type != 'media' || !$form->status) ? 'display:none' : '' }}">
        <input type="file" class="my-filepond" name="q{{$question->id}}-media[]" data-qid="{{$question->id}}" multiple/>
    </div>
    <div id="q{{$question->id}}-gallery" style="margin-bottom: 20px">
        @if ($question->files($form->id)->count())
            @foreach ($question->files($form->id) as $file)
                @if ($file->type == 'image')
                    <img src="{{$file->attachment}}" class="mygallery" id="q{{$question->id}}-photo-{{$file->attachment}}" width="100" style="margin:0px 10px 10px 0px">
                @endif
            @endforeach
        @endif
    </div>
    {{-- Files --}}
    <div id="q{{$question->id}}-gallery" style="margin-bottom: 20px">
        @foreach ($question->files($form->id) as $file)
            @if ($file->type == 'file')
                <div id="q{{$question->id}}-file-{{$file->id}}">
                    <i class="fa fa-file-text-o"></i> &nbsp; <a href="{{$file->attachment}}" target="_blank">{{ $file->name }}</a> &nbsp; <a href="#"><i class="fa fa-times font-red deleteFile" data-fid="q{{$question->id}}-file-{{$file->id}}" data-file="{{$question->id}}-{{$file->name}}"></i></a>
                </div>
            @endif
        @endforeach
    </div>


    {{-- Notes - Show --}}
    <input type="hidden" id="q{{$question->id}}-notes-orig" value="{!! ($question->extraNotesForm($form->id)) ? $question->extraNotesForm($form->id)->notes : '' !!}">
    <div id="shownote-{{$question->id}}" class="row button-note" data-qid="{{$question->id}}"
         style="margin: 10px 0px; cursor: pointer; {{ ($question->extraNotesForm($form->id)) ? '' : 'display:none' }}">
        <b>Notes</b><br>
        <div class="col-md-12" id="shownote-{{$question->id}}-div" style="padding-left: 0px; margin-bottom: 10px">
            {!! ($question->extraNotesForm($form->id)) ? $question->extraNotesForm($form->id)->notes : '' !!}
        </div>
    </div>
    {{-- Notes - Edit --}}
    <div id="editnote-{{$question->id}}" style="margin-top:10px; display:none">
        <div class="row">
            <div class="col-md-12">
                <b>Notes</b><br>
                <textarea id="q{{$question->id}}-notes" name="q{{$question->id}}-notes" rows="5" class="form-control"
                          placeholder="Notes">{!! ($question->extraNotesForm($form->id)) ? $question->extraNotesForm($form->id)->notes : '' !!}</textarea>
            </div>
        </div>
        <div class="row" style="margin: 10px 0px">
            <div class="col-md-12">
                <button class="btn green button-savenote" data-qid="{{$question->id}}">Save</button>
                <button class="btn default button-cancelnote" data-qid="{{$question->id}}">Cancel</button>
                <button class="btn dark button-delnote" data-qid="{{$question->id}}">&nbsp;<i class="fa fa-trash"></i>&nbsp;</button>
            </div>
        </div>
    </div>

    {{-- Actions - Show --}}
    @if ($question->actions($form->id)->count())
        <div class="row">
            <div class="col-md-12"><b>Actions</b><br></div>
        </div>

        @foreach($question->actions($form->id) as $todo)
            <div class="row hoverDiv" style="margin: 10px 0px; padding: 10px 0px; background-color: #f9f9f9; border: #ddd 1px solid" onclick="todo({{$todo->id}})">
                <div class="col-xs-8">
                    {!! nl2br($todo->info) !!}<br><br>
                    <b>Assigned to:</b> {{ $todo->assignedToBySBC() }}
                    @if ($todo->comments)
                        <br><b>Comments:</b> {!! nl2br($todo->comments) !!}
                    @endif
                    @if ($todo->attachment)
                        <br><a href="{{ $todo->attachmentUrl }}" data-lity class="btn btn-xs blue"><i class="fa fa-picture-o"> Attachment</i></a>
                    @endif
                </div>
                <div class="col-xs-4 text-right">
                        <?php
                        $done_by = App\User::find($todo->done_by);
                        $done_at = ($done_by) ? $todo->done_at->format('d/m/Y') : '';
                        $done_by = ($done_by) ? $done_by->full_name : 'unknown';
                        ?>
                    @if ($todo->status == '1' && !$todo->done_by)
                        <span class="font-red">Outstanding</span>{!! ($todo->due_at) ? "<br>Due: " . $todo->due_at->format('d/m/Y') : '' !!}
                    @elseif ($todo->status == '2' && !$todo->done_by)
                        <span class="font-yellow">In Progress</span>{!! ($todo->due_at) ? "<br>Due: " . $todo->due_at->format('d/m/Y') : '' !!}
                    @elseif ($todo->status == '-1' && !$todo->done_by)
                        <span class="font-red">Can't do</span>{!! ($todo->due_at) ? "<br>Due: " . $todo->due_at->format('d/m/Y') : '' !!}
                    @else
                        <span class="font-green">Completed</span><br>{!! $done_by  !!} ({{ $done_at }})
                    @endif
                </div>
            </div>
        @endforeach
    @endif

    {{-- Question Extras (Notes, Media, Actions --}}
    @if ($question->type != 'static')
        <div class="row">
            <div class="col-md-12">
                <button class="btn default btn-xs pull-right button-action" data-qid="{{$question->id}}">Action <i class="fa fa-check-square-o"></i></button>
                @if ($question->type != 'media' || !$form->status)
                    <button class="btn default btn-xs pull-right button-media" style="margin-right: 10px" data-qid="{{$question->id}}">Media <i class="fa fa-picture-o"></i></button>
                @endif
                <button class="btn default btn-xs pull-right button-note" style="margin-right: 10px" data-qid="{{$question->id}}">Note <i class="fa fa-edit"></i></button>
            </div>
        </div>
    @endif
    <hr class="field-hr">
</div> {{-- end question div --}}