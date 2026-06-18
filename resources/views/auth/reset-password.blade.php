@extends('layout-guest')

@section('content')
    <div class="page-content-inner">
        <div class="row">
            <div class="col-md-6 col-md-offset-3">
                <div class="portlet light bordered">
                    <div class="portlet-body form">
                        <form method="POST" action="/password/reset">
                            {!! Form::hidden('token', $token) !!}
                            {{ csrf_field() }}

                            <div class="form-body">
                                <h3 class="font-green form-section" style="margin: 0px 0px 10px 0px">Reset Password</h3>

                                @include('form-error')

                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group {!! fieldHasError('email', $errors) !!}">
                                            {!! Form::label('email', 'Email', ['class' => 'control-label']) !!}
                                            {!! Form::text('email', null, ['class' => 'form-control', 'required' => 'required']) !!}
                                            {!! fieldErrorMessage('email', $errors) !!}
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group {!! fieldHasError('password', $errors) !!}">
                                            {!! Form::label('password', 'Password', ['class' => 'control-label', 'required']) !!}
                                            <input type="password" name="password" value="{{ old('password') }}" id="password" class="form-control">
                                            {!! fieldErrorMessage('password', $errors) !!}
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group {!! fieldHasError('password_confirmation', $errors) !!}">
                                            {!! Form::label('password_confirmation', 'Re-type Password', ['class' => 'control-label', 'required']) !!}
                                            <input type="password" name="password_confirmation" value="{{ old('password_confirmation') }}" id="password_confirmation" class="form-control">
                                            {!! fieldErrorMessage('password_confirmation', $errors) !!}
                                        </div>
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
