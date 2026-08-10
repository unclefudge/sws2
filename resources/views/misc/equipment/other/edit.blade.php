@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        <li><a href="/equipment">Equipment Allocation</a><i class="fa fa-circle"></i></li>
        @if (Auth::user()->allowed2('add.equipment'))
            <li><a href="/equipment/other-location">Other Locations</a><i class="fa fa-circle"></i></li>
        @endif
        <li><span>Edit</span></li>
    </ul>
@stop

@section('content')
    <div class="page-content-inner">
        <div class="row">
            <div class="col-md-12">
                <div class="portlet light bordered">
                    <div class="portlet-title">
                        <div class="caption">
                            <span class="caption-subject font-green-haze bold uppercase">Edit Other Location </span>
                            <span class="caption-helper"> - ID: {{ $other->id }}</span>
                        </div>
                    </div>
                    <div class="portlet-body form">
                        <form method="POST" action="{{ action([App\Http\Controllers\Misc\EquipmentLocationOtherController::class, 'update'], $other->id) }}" class="horizontal-form">
                            @csrf
                            @method('PATCH')
                            @include('form-error')

                            <div class="form-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <x-form.input name="name" label="Location Name" :value="$other->name"/>
                                    </div>
                                </div>

                                <div class="form-actions right">
                                    <a href="/equipment/other-location" class="btn default"> Back</a>
                                    @if (Auth::user()->allowed2('del.equipment', $other))
                                        <button class="btn red" id="btn-delete">Delete</button>
                                    @endif
                                    <button type="submit" name="save" value="save" class="btn green">Save</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div>
            <div class="pull-right" style="font-size: 12px; font-weight: 200; padding: 10px 10px 0 0">
                {{--}}
                {!! $other->displayUpdatedBy() !!}
                --}}
            </div>
        </div>
    </div>
@stop

@section('page-level-plugins-head')
    <link href="/assets/pages/css/profile-2.min.css" rel="stylesheet" type="text/css"/>
@stop

@section('page-level-plugins')
@stop

@section('page-level-scripts')
    {{-- Metronic + custom Page Scripts --}}
    <script>
        $(document).ready(function () {
            $("#btn-delete").click(function (e) {
                e.preventDefault();
                swal({
                    title: "Are you sure?",
                    text: "This action can't be undone and all records of it will be <b>DELETED</b>!<br><b>" + name + "</b>",
                    showCancelButton: true,
                    cancelButtonColor: "#555555",
                    confirmButtonColor: "#E7505A",
                    confirmButtonText: "Yes, delete it!",
                    allowOutsideClick: true,
                    html: true,
                }, function () {
                    window.location.href = "/equipment/other-location/{{ $other->id }}/delete";
                });
            });
        });
    </script>
@stop