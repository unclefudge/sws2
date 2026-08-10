@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        <li><a href="/equipment">Equipment Allocation</a><i class="fa fa-circle"></i></li>
        @if (Auth::user()->allowed2('add.equipment'))
            <li><a href="/equipment/other-location">Other Locations</a><i class="fa fa-circle"></i></li>
        @endif
        <li><span>Create</span></li>
    </ul>
@stop

@section('content')
    <div class="page-content-inner">
        <div class="row">
            <div class="col-md-12">
                <div class="portlet light bordered">
                    <div class="portlet-title">
                        <div class="caption">
                            <span class="caption-subject font-green-haze bold uppercase">Create Other Location </span>
                        </div>
                    </div>
                    <div class="portlet-body form">
                        <form method="POST" action="{{ action([App\Http\Controllers\Misc\EquipmentLocationOtherController::class, 'store']) }}" class="horizontal-form" enctype="multipart/form-data">
                            @csrf
                            @include('form-error')

                            <div class="form-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <x-form.input name="name" label="Location Name"/>
                                    </div>
                                </div>

                                <div class="form-actions right">
                                    <a href="/equipment/other-location" class="btn default"> Back</a>
                                    <button type="submit" name="save" class="btn green">Save</button>
                                </div>
                            </div>
                        </form>

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
    <script>
        $(document).ready(function () {
        });
    </script>
@stop