@extends('layout-guest')

@section('content')
    <div class="page-content-inner">
        <div class="row">
            <div class="col-md-6 col-md-offset-3">
                <div class="portlet light bordered">
                    <div class="portlet-body form">
                        <form method="POST" action="/login" id="login_form">
                            {{ csrf_field() }}

                            <div class="form-body">
                                {{-- Login Details --}}
                                <h3 class="font-green form-section" style="margin: 0px 0px 10px 0px">Sign In</h3>

                                <span class="help-block font-red">{!! $errors->first('message') !!}</span>

                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group {!! fieldHasError('username', $errors) !!}">
                                            {!! Form::label('username', 'Username / Email (case sensitive)', ['class' => 'control-label']) !!}
                                            {!! Form::text('username', null, ['class' => 'form-control', 'required' => 'required']) !!}
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group {!! fieldHasError('password', $errors) !!}">
                                            {!! Form::label('password', 'Password', ['class' => 'control-label']) !!}
                                            <input type="password" class="form-control" name="password" value="{{ old('password') }}" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-3">
                                        <button type="submit" class="btn green">Login</button>
                                    </div>
                                    <div class="col-md-9">
                                        <br style="font-size: 3px"><a href="/password/reset">Forgot your password?</a>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop {{-- END Content --}}