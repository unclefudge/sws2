@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        <li><a href="/site/precon">Pre Con Review</a><i class="fa fa-circle"></i></li>
        <li><span>Create</span></li>
    </ul>
@stop

@section('content')
    <div class="page-content-inner">
        <div class="portlet light bordered">
            <div class="portlet-title">
                <div class="caption">
                    <span class="caption-subject font-green-haze bold uppercase">Create Pre Con Review</span>
                </div>
            </div>
            <div class="portlet-body form">
                <form method="POST" action="{{ action([\App\Http\Controllers\Site\SitePreConController::class, 'store']) }}" class="horizontal-form">
                    @csrf
                    @include('form-error')
                    <div class="form-body">
                        <div class="row">
                            <div class="col-md-6">
                                <x-form.select name="site_id" label="Site" plugin="select2" style="width:100%" :options="['' => ''] + $siteOptions->all()"/>
                                @if ($siteOptions->isEmpty())
                                    <p class="help-block">No sites are available to add.</p>
                                @endif
                            </div>
                        </div>
                        <x-form.textarea name="notes" id="notes" label="Notes" :value="old('notes')"/>
                    </div>
                    <div class="form-actions right">
                        <a href="/site/precon" class="btn default">Back</a>
                        <button type="submit" class="btn green" @disabled($siteOptions->isEmpty())>Create</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop

@section('page-level-scripts')
    <script>
        $('#site_id').select2({placeholder: 'Select Site', width: '100%'});
    </script>
@stop
