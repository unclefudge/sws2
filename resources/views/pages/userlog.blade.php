@extends('layout')

@section('content')
    <div class="row">
        <div class="col-md-12 col-sm-12">
            <div class="portlet light ">
                <div class="portlet-title">
                    <div class="caption">
                        <i class="fa fa-user font-dark"></i>
                        <span class="caption-subject font-dark bold uppercase">Login as User</span>
                        <span class="caption-helper"></span>
                    </div>
                </div>
                <div class="portlet-body">
                    {!! Form::model('userlog', ['action' => ['Misc\PagesController@userlogAuth'], 'class' => 'horizontal-form']) !!}

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group {!! fieldHasError('assign', $errors) !!}">
                                <select id="user" name="user" class="form-control select2" style="width:100%">
                                    @foreach (\App\User::where('status', 1)->get() as $user)
                                        <option value="{{ $user->id }}">{{ $user->full_name }} ({{$user->company->name_alias}})</option>
                                    @endforeach
                                </select>
                                {{--}}
                                {!! Form::select('user', Auth::user()->company->usersSelect('prompt', 1), null, ['class' => 'form-control select2', 'id' => 'user', 'width' => '100%']) !!}
                                --}}
                                {!! fieldErrorMessage('user', $errors) !!}
                            </div>
                        </div>
                    </div>
                    <div class="form-actions right">
                        <button type="submit" name="save" class="btn green">Login</button>
                    </div>
                    {!! Form::close() !!}

                    <div>
                        {{ env('APP_ENV') }}

                    </div>
                </div>
            </div>
        </div>
    </div>

@stop

@section('page-level-plugins-head')
    <link href="/assets/global/plugins/select2/css/select2.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/select2/css/select2-bootstrap.min.css" rel="stylesheet" type="text/css"/>
@stop

@section('page-level-plugins')
    <script src="/assets/global/plugins/select2/js/select2.full.min.js" type="text/javascript"></script>
@stop

@section('page-level-scripts') {{-- Metronic + custom Page Scripts --}}
<script type="text/javascript">
    $("#user").select2({placeholder: "Select User", width: '100%'});
</script>
@stop