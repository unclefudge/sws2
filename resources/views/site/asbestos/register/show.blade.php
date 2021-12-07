@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        @if (Auth::user()->hasAnyPermissionType('site'))
            <li><a href="/site">Sites</a><i class="fa fa-circle"></i></li>
        @endif
        <li><a href="/site/asbestos/register">Asbestos Register</a><i class="fa fa-circle"></i></li>
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
                            <i class="fa fa-file-text-o "></i>
                            <span class="caption-subject font-green-haze bold uppercase">Asbestos Register</span>
                            <span class="caption-helper"> ID: {{ $asb->id }}</span>
                        </div>
                        <div class="actions">
                            @if ($asb->attachment_url)
                                <a href="{{ $asb->attachment_url }}" class="btn btn-circle btn-outline btn-sm green" id="view_pdf"> Download PDF</a>
                            @endif
                        </div>
                    </div>
                    <div class="portlet-body form">
                        <div class="form-body">
                            <div class="row">
                                <div class="col-md-7">
                                    <h2 style="margin-top: 0px">{{ $asb->site->name }}</h2>
                                    {{ $asb->site->fulladdress }}
                                </div>
                                <div class="col-md-5">
                                    @if (!$asb->status)
                                        <h2 class="font-red pull-right" style="margin-top: 0px">CLOSED</h2>
                                    @endif
                                    <b>Site No:</b> {{ $asb->site->code }}<br>
                                    <b>Supervisor(s):</b> {{ $asb->site->supervisorsSBC() }}<br>
                                    <b>Last Updated:</b> {{ $asb->updated_at->format('d/m/Y') }}<br>
                                </div>
                            </div>
                            <hr>

                            <a class="btn btn-circle green btn-outline btn-sm pull-right" href="/site/asbestos/register/{{ $asb->id }}/create" data-original-title="Add">Add item</a>
                            <br><br>
                            <table class="table table-striped table-bordered table-nohover order-column">
                                <thead>
                                <tr class="mytable-header">
                                    <th width="10%">Date Identified</th>
                                    <th> Location of ACM</th>
                                    <th> Type</th>
                                    <th> Friable / Non-Friable</th>
                                    <th> Quantity</th>
                                    <th> Condition</th>
                                    <th> Assessment / Action</th>
                                    <th width="10%"></th>
                                </tr>
                                </thead>

                                <tbody>
                                @if ($asb->items->count())
                                    @foreach ($asb->items as $item)
                                        <tr>
                                            <td>{{ $item->date->format('d/m/Y') }}</td>
                                            <td>{{ $item->location }}</td>
                                            <td>{{ $item->type }}</td>
                                            <td>{{ ($item->friable) ? 'Friable' : 'Non-friable' }}</td>
                                            <td>{{ $item->amount }}</td>
                                            <td>{{ $item->condition }}</td>
                                            <td>{{ $item->assessment }}</td>
                                            <td><a href="/site/asbestos/register/{{ $item->id }}/edit" class="btn blue btn-xs btn-outline sbold uppercase margin-bottom"><i class="fa fa-pencil"></i> Edit</a></td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="8">
                                            <br>There are no listed Asbestos Items in the database currently
                                            @if ($asb->attachment)
                                                but a PDF version of the Asbestos Register for this site can be viewed here <a href="{{ $asb->attachment_url }}" class="btn btn-circle btn-outline btn-sm green" id="view_pdf"> Report</a>
                                            @endif
                                            <br><br>
                                        </td>
                                    </tr>
                                @endif
                                </tbody>
                            </table>
                        </div>

                        {!! Form::close() !!}

                        <div class="form-actions right">
                            <a href="/site/asbestos/register" class="btn default"> Back</a>
                            @if (Auth::user()->allowed2('del.site.asbestos', $asb))
                                <a id="delete" class="btn red">Delete</a></li>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>



    @stop <!-- END Content -->


@section('page-level-plugins-head')
@stop

@section('page-level-plugins')
@stop

@section('page-level-scripts') {{-- Metronic + custom Page Scripts --}}
<script>

    $(document).ready(function () {
        $('#delete').on('click', function () {
            var id = "{{ $asb->id }}";
            var name = "{{ $asb->site->name }}";
            swal({
                title: "Are you sure?",
                text: "You will not be able to restore this asbestos record!<br><b>" + name + "</b>",
                showCancelButton: true,
                cancelButtonColor: "#555555",
                confirmButtonColor: "#E7505A",
                confirmButtonText: "Yes, delete it!",
                allowOutsideClick: true,
                html: true,
            }, function () {
                window.location = "/site/asbestos/register/" + id + '/destroy';
            });
        });
    });
</script>
@stop

