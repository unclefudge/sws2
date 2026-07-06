@extends('layout-guest')

@section('content')
    <div class="page-content-inner">
        <div class="row">
            <div class="col-md-6 col-md-offset-3">
                <div class="portlet light bordered">
                    <div class="portlet-body form">
                        <form method="POST" action="/password/reset">
                            @csrf
                            <x-form.hidden name="token" :value="$token"/>

                            <div class="form-body">
                                <h3 class="font-green form-section" style="margin: 0px 0px 10px 0px">Reset Password</h3>

                                @include('form-error')

                                <div class="row">
                                    <div class="col-md-12">
                                        <x-form.input name="email" label="Email" required/>
                                    </div>
                                    <div class="col-md-12">
                                        <x-form.input name="password" label="Password" type="password" required/>
                                    </div>
                                    <div class="col-md-12">
                                        <x-form.input name="password_confirmation" label="Re-type Password" type="password" required/>
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-success" style="margin-left: 15px">Reset Password</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
