@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        @if (Auth::user()->hasAnyPermissionType('client.planner.email'))
            <li><a href="/client/planner/email">Client Planner Emails</a><i class="fa fa-circle"></i></li>
        @endif
        <li><span>View Email</span></li>
    </ul>
@stop

<style>
    a.mytable-header-link {
        font-size: 14px;
        font-weight: 600;
        color: #333 !important;
    }

    p {
        padding: 0;
        margin: 0;
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
                            <span class="caption-subject bold uppercase font-green-haze">Client Planner Email</span>
                            <span class="caption-helper">ID: {{ $email->id }}</span>
                        </div>
                    </div>
                    <div class="portlet-body form">
                        <div class="page-content-inner">
                            {{-- To --}}
                            <div class="row">
                                <div class="col-md-2"><b>To:</b></div>
                                <div class="col-md-6">{{ $email->sent_to }}</div>
                                @if ($email->status == 0)
                                    <div class="col-md-1"><b>Sent by:</b></div>
                                    <div class="col-md-3">{{ $email->sent_by->name }}</div>
                                @else
                                    <div class="col-md-4"><h3 class="font-red pull-right" style="margin-top: 0px">DRAFT</h3></div>
                                @endif
                            </div>
                            {{-- Cc --}}
                            <div class="row">
                                <div class="col-md-2"><b>Cc:</b></div>
                                <div class="col-md-6">{{ $email->sent_cc }}</div>
                                @if ($email->status == 0)
                                    <div class="col-md-1"><b>Sent at:</b></div>
                                    <div class="col-md-3">{{ ($email->status == 0) ? $email->updated_at->format('d/m/Y g:i a') : '' }}</div>
                                @endif
                            </div>
                            {{-- Subject --}}
                            <div class="row">
                                <div class="col-md-2"><b>Subject:</b></div>
                                <div class="col-md-10">{{ $email->subject }}</div>
                            </div>
                            <br>

                            <div class="row">
                                <div class="col-md-2"><b>Attachments:</b></div>
                                <div class="col-md-10">
                                    @foreach ($email->attachment as $file)
                                        <i class="fa fa-file-pdf-o"></i> <a href="{{ $file->url }}" target="_blank" title="{{ $file->name }}">{{ $file->name }}</a>, &nbsp;
                                    @endforeach
                                </div>
                            </div>

                        </div>
                        <hr class="field-hr">
                        {{-- Body --}}
                        <div class="row">
                            <div class="col-md-12">
                                <div>{!! $email->body !!}</div>
                            </div>
                        </div>
                        {{--}}
                         <hr class="field-hr">
                        <div class="row">
                            <div class="col-md-12">
                                <div>{!! htmlspecialchars($email->body, ENT_QUOTES, 'UTF-8') !!}</div>
                            </div>
                        </div>
                        <hr class="field-hr">
                        <div class="row">
                            <div class="col-md-12">
                                <div>{!! $email->body !!}</div>
                            </div>
                        </div>
                        <hr class="field-hr"> --}}

                        <hr>
                        <div class="pull-right" style="min-height: 50px">
                            <a href="/client/planner/email" class="btn default"> Back</a>
                            @if ($email->status == 2)
                                <a href="/client/planner/email/{{$email->id}}/edit" class="btn green"> Edit</a>
                            @endif
                        </div>
                        <br><br>
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop


@section('page-level-plugins-head')
@stop

@section('page-level-plugins')
@stop

@section('page-level-scripts')
    {{-- Metronic + custom Page Scripts --}}
@stop

