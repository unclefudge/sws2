@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        <li><a href="/planner/publicholidays">Non-working Dates</a><i class="fa fa-circle"></i></li>
        <li><span>{{ $holiday ? 'Manage' : 'Add' }}</span></li>
    </ul>
@stop

@section('content')
    <div class="page-content-inner">
        <div class="portlet light bordered">
            <div class="portlet-title">
                <div class="caption"><span class="caption-subject font-green-haze bold uppercase">{{ $holiday ? 'Manage' : 'Add' }} Non-working Dates</span></div>
            </div>
            <div class="portlet-body form">
                <form method="POST" action="{{ action([App\Http\Controllers\Site\Planner\PublicHolidayController::class, 'store']) }}">
                    @csrf
                    <input type="hidden" name="calendar_source" value="{{ $calendarSource }}">
                    @if ($holiday)<input type="hidden" name="holiday_id" value="{{ $holiday->id }}">@endif
                    @include('form-error')
                    <div class="form-body">
                        <p>Planners skip these dates across all sites. Company shutdowns and other closures are stored separately from public holidays and do not become public holidays for contract-extension calculations.</p>
                        <div class="row">
                            <div class="col-md-8"><x-form.input name="name" label="Name" :value="old('name', $holiday?->name)"/></div>
                            <div class="col-md-4"><x-form.select name="type" label="Type" :options="$types" :value="old('type', $holiday?->type ?? 'public')"/></div>
                        </div>
                        <div class="row">
                            <div class="col-md-4"><x-form.datepicker name="date" label="Start Date" :value="old('date', $holiday?->date?->format('d/m/Y'))" format="dd/mm/yyyy"/></div>
                            <div class="col-md-4"><x-form.datepicker name="end_date" label="End Date (inclusive)" :value="old('end_date', ($holiday?->end_date ?? $holiday?->date)?->format('d/m/Y'))" format="dd/mm/yyyy" clear-button/></div>
                        </div>
                        <p class="help-block">Public holidays are entered as single dates. For shutdowns and other closures, leave End Date blank for one day or enter the full range. You will review affected tasks before anything is saved. Only dates starting today or later can be managed.</p>
                        <div class="form-actions right">
                            <a href="/planner/publicholidays" class="btn default">Back</a>
                            @if ($holiday)<button type="submit" name="operation" value="remove" class="btn red" formnovalidate>Review removal</button>@endif
                            <button type="submit" name="operation" value="save" class="btn green">Preview changes</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop

@section('page-level-plugins-head')
    <link href="/assets/global/plugins/bootstrap-datepicker/css/bootstrap-datepicker.min.css" rel="stylesheet" type="text/css"/>
@stop
@section('page-level-plugins')
    <script src="/assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js" type="text/javascript"></script>
@stop
@section('page-level-scripts')
    <script src="/assets/pages/scripts/components-date-time-pickers.min.js" type="text/javascript"></script>
@stop
