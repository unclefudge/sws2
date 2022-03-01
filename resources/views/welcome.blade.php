@extends('layout-guest')

@section('content')
    <div class="page-content-inner" style="background: #ffffff; max-width: 940px">
        <div class="row">
            <div class="col-md-12">
                <img src="/img/logo-sws-med.png" class="img-responsive center-block" align="middle" style="margin: 0 auto;">
            </div>
            {{--
            <div class="col-md-8 col-md-offset-2">
                <div class="text-center" style="border: 5px solid #e7505a; background-color: #eee; padding: 10px; margin: 30px 0px">
                    <h2 class="font-red">COMPULSORY SITE CHECK-IN</h2>
                    <h4>Every worker MUST check in and acknowledge the work health and safety requirements before entering any worksite</h4>
                    <a href="/checkin" class="btn btn-lg dark">Check in</a>
                    <br><br>
                </div>
            </div>
            --}}
            <div class="col-md-12">
                <div class="embed-responsive embed-responsive-16by9">
                    <iframe class="embed-responsive-item" src="https://www.youtube.com/embed/-seurriz9Xc"></iframe>
                </div>
            </div>
        </div>

        <div class="row">
            @if (\App::environment('dev')) DEV @endif
            @if (\App::environment('prod')) .. @endif

        </div>
    </div>
@stop