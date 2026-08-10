@extends('layout-basic')
@section('content')
    <div class="page-content-inner">
        <div class="row">
            <div class="col-md-6 col-md-offset-3">
                <div class="note note-warning">Your password has been reset and you are required to change it.</div>
                {{-- Login Details --}}
                @if (Auth::user()->allowed2('edit.user', $user))
                    {{-- Edit Company Details --}}
                    <div class="portlet light" id="edit_login">
                        <div class="portlet-title">
                            <div class="caption">
                                <span class="caption-subject font-dark bold uppercase">Password Reset</span>
                            </div>
                        </div>
                        <div class="portlet-body form">
                            <form method="POST" action="{{ action([App\Http\Controllers\UserController::class, 'updatePassword'], $user->id) }}">
                                @csrf
                            @include('form-error')

                            @if ($user->status)
                                {{-- Password --}}
                                <div class="row">
                                    <div class="form-group">
                                        <label for="password" class="col-md-3 control-label">Password:</label>
                                        <div class="col-md-9">
                                            <x-form.input name="password" type="password" placeholder="Enter new password"/>
                                        </div>
                                    </div>
                                </div>
                                {{-- Confirm Password --}}
                                <hr class="field-hr">
                                <div class="row">
                                    <div class="form-group">
                                        <label for="password_confirmation" class="col-md-3 control-label">Re-type Password:</label>
                                        <div class="col-md-9">
                                            <x-form.input name="password_confirmation" type="password" placeholder="Re-type password"/>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <br>
                            <div class="form-actions right">
                                <button type="submit" class="btn green"> Save</button>
                            </div>
                            </form>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div>
        <div class="pull-right" style="font-size: 12px; font-weight: 200; padding: 10px 10px 0 0">
            {!! $user->displayUpdatedBy() !!}
        </div>
    </div>

@stop

@section('page-level-plugins-head')
@stop

@section('page-level-styles-head')
@stop

@section('page-level-plugins')
@stop

@section('page-level-scripts') {{-- Metronic + custom Page Scripts --}}
@stop