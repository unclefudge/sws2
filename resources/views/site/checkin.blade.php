@extends('layout-basic')

@section('pagetitle')
    @if (Session::has('siteID') && $worksite->isUserOnsite(Auth::user()->id))
        <a href="/"><img src="/img/logo2-sws.png" alt="logo" class="logo-default" style="margin-top:15px"></a>
    @else
        <img src="/img/logo2-sws.png" alt="logo" class="logo-default" style="margin-top:15px">
    @endif
    <div class="pull-right" style="padding: 20px;"><a href="/logout">logout</a></div>
@stop

@section('breadcrumbs')
    @if (Session::has('siteID') && $worksite->isUserOnsite(Auth::user()->id))
        <ul class="page-breadcrumb breadcrumb">
            <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
            <li><span>Check-in</span></li>
        </ul>
    @endif
@stop

@section('content')
    <div class="page-content-inner">
        <div class="row">
            <div class="col-md-12">
                <div class="portlet light bordered">
                    <div class="portlet-title">
                        <div class="caption">
                            <i class="fa fa-sign-in"></i>
                            <span class="caption-subject font-green-haze bold uppercase">Site Checkin</span><br>
                            <span class="caption-helper">You must check into all sites you attend.</span>
                        </div>
                    </div>
                    <div class="portlet-body form">
                        <h2>{{ $worksite->name }}
                            <small>(Site: {{ $worksite->code }})</small>
                        </h2>
                        <p>{{ $worksite->address }}, {{ $worksite->suburb }}</p>
                        <hr>

                        <!-- BEGIN FORM-->
                        {!! Form::model('site_attenance', ['action' => ['Site\SiteCheckinController@processCheckin', $worksite->id], 'files' => true]) !!}
                        @include('form-error')

                        <p>Please answer the following questions.</p>
                        <div class="form-body">
                            {{--}}
                            <div class="note note-success">
                                <h4><b>COVID - Safety Requirements</b></h4>
                                <hr style="color: #000">
                                <div class="row">
                                    <div class="col-sm-2 col-xs-4 text-center">
                                        <div class="form-group">
                                            {!! Form::checkbox('question20', '1', false,
                                             ['class' => 'make-switch', 'data-size' => 'small',
                                             'data-on-text'=>'Yes', 'data-on-color'=>'success',
                                             'data-off-text'=>'No', 'data-off-color'=>'danger']) !!}
                                        </div>
                                    </div>
                                    <div class="col-sm-10 col-xs-8">
                                        I have <b>signed in</b> to the NSW service Covid safe check in
                                    </div>
                                </div>
                                <div class="row visible-xs">&nbsp;</div>
                                <div class="row">
                                    <div class="col-sm-2 col-xs-4 text-center">
                                        <div class="form-group">
                                            {!! Form::checkbox('question21', '1', false,
                                             ['class' => 'make-switch', 'data-size' => 'small',
                                             'data-on-text'=>'Yes', 'data-on-color'=>'success',
                                             'data-off-text'=>'No', 'data-off-color'=>'danger']) !!}
                                        </div>
                                    </div>
                                    <div class="col-sm-10 col-xs-8">
                                        I will <b>wear a mask</b> as required and <b>observe all other Gov directives</b>.
                                    </div>
                                </div>
                                <div class="row visible-xs">&nbsp;</div>
                                <div class="row">
                                    <div class="col-sm-2 col-xs-4 text-center">
                                        <div class="form-group">
                                            {!! Form::checkbox('question22', '1', false,
                                             ['class' => 'make-switch', 'data-size' => 'small',
                                             'data-on-text'=>'Yes', 'data-on-color'=>'success',
                                             'data-off-text'=>'No', 'data-off-color'=>'danger']) !!}
                                        </div>
                                    </div>
                                    <div class="col-sm-10 col-xs-8">
                                        I understand the current NSW Health orders and <b>comply with its requirements</b> in relation to vaccinations and or covid testing.
                                    </div>
                                </div>
                                <!--
                                <div class="row visible-xs">&nbsp;</div>
                                <div class="row">
                                    <div class="col-sm-2 col-xs-4 text-center">
                                        <div class="form-group">
                                            {!! Form::checkbox('question18', '1', false,
                                             ['class' => 'make-switch', 'data-size' => 'small',
                                             'data-on-text'=>'Yes', 'data-on-color'=>'success',
                                             'data-off-text'=>'No', 'data-off-color'=>'danger']) !!}
                                        </div>
                                    </div>
                                    <div class="col-sm-10 col-xs-8">
                                        <div>I have read, understood and will adhere to the principles required to manage the risk of COVID-19 &nbsp; &nbsp; <a
                                                    class="btn dark" href="/filebank/doc/COVID-19 construction Site Risk Management Principles.pdf">view</a></div>
                                    </div>
                                </div>-->
                            </div> --}}

                            <br>
                            <div class="row">
                                <div class="col-sm-2 col-xs-4 text-center">
                                    <div class="form-group {!! fieldHasError('transient', $errors) !!}">
                                        {!! Form::checkbox('question4', '1', false,
                                         ['class' => 'make-switch', 'data-size' => 'small',
                                         'data-on-text'=>'Yes', 'data-on-color'=>'success',
                                         'data-off-text'=>'No', 'data-off-color'=>'danger']) !!}
                                    </div>
                                </div>
                                <div class="col-sm-10 col-xs-8">
                                    <div style="background-color: #f9e491; padding: 5px 10px"><b>I acknowledge that I am physically present on the above Worksite</b></div>
                                </div>
                            </div>
                            <div class="row visible-xs">&nbsp;</div>
                            <div class="row">
                                <div class="col-sm-2 col-xs-4 text-center">
                                    <div class="form-group {!! fieldHasError('transient', $errors) !!}">
                                        {!! Form::checkbox('question1', '1', false,
                                         ['class' => 'make-switch', 'data-size' => 'small',
                                         'data-on-text'=>'Yes', 'data-on-color'=>'success',
                                         'data-off-text'=>'No', 'data-off-color'=>'danger']) !!}
                                    </div>
                                </div>
                                <div class="col-sm-10 col-xs-8">
                                    I have <b>read</b>, <b>understood</b> and will <b>adhere</b> to the <b>Site Health & Safety Rules</b>
                                    <small><a href="/Site Safety Rules.pdf" target="_blank">(site rules)</a></small>
                                </div>
                            </div>
                            <div class="row visible-xs">&nbsp;</div>
                            <div class="row">
                                <div class="col-sm-2 col-xs-4 text-center">
                                    <div class="form-group">
                                        {!! Form::checkbox('question2', '1', false,
                                         ['class' => 'make-switch', 'data-size' => 'small',
                                         'data-on-text'=>'Yes', 'data-on-color'=>'success',
                                         'data-off-text'=>'No', 'data-off-color'=>'danger']) !!}
                                    </div>
                                </div>
                                <div class="col-sm-10 col-xs-8">
                                    I declare I am <b>fit for work</b> and am <b>not under the influence of alcohol, drugs or prescription medication</b> that may affect my capacity to work
                                </div>
                            </div>
                            <div class="row visible-xs">&nbsp;</div>
                            <div class="row">
                                <div class="col-sm-2 col-xs-4 text-center">
                                    <div class="form-group">
                                        {!! Form::checkbox('question3', '1', false,
                                         ['class' => 'make-switch', 'data-size' => 'small',
                                         'data-on-text'=>'Yes', 'data-on-color'=>'success',
                                         'data-off-text'=>'No', 'data-off-color'=>'danger']) !!}
                                    </div>
                                </div>
                                <div class="col-sm-10 col-xs-8">
                                    I declare I am <b>not affected by any pre-existing medical condition</b> that may be aggravated by my work duties <b>OR</b> I have <b>declared any pre-existing
                                        medical conditions to my employer</b> and I will work in accordance with the arranged suitable duties
                                </div>
                            </div>
                            <div class="row visible-xs">&nbsp;</div>
                            <div class="row">
                                <div class="col-sm-2 col-xs-4 text-center">
                                    <div class="form-group">
                                        {!! Form::checkbox('question5', '1', false,
                                         ['class' => 'make-switch', 'data-size' => 'small',
                                         'data-on-text'=>'Yes', 'data-on-color'=>'success',
                                         'data-off-text'=>'No', 'data-off-color'=>'danger']) !!}
                                    </div>
                                </div>
                                <div class="col-sm-10 col-xs-8">
                                    I have <b>reviewed</b> and am <b>familiar with the site specific Risk Assessment</b> for the project and will apply the specified controls stated <a class="btn default" id="open_docs">view</a>
                                </div>
                            </div>
                            <div class="row visible-xs">&nbsp;</div>

                            <div class="row" style="display: none" id="docs">
                                <div class="col-xs-12">
                                    <div class="portlet box blue-hoki">
                                        <div class="portlet-title">
                                            <div class="caption">
                                                <i class="fa fa-file-text-o"></i>
                                                <span class="caption-subject">Site Safety Documents</span>
                                            </div>
                                            <div class="tools">
                                                <a class="font-white" id="close_docs"> X </a>
                                            </div>
                                        </div>
                                        <div class="portlet-body">
                                            <div class="panel-group accordion" id="accordion3">
                                                @if (Session::has('siteID'))
                                                    <div class="panel panel-default">
                                                        <div class="panel-heading">
                                                            <h4 class="panel-title">
                                                                <a class="accordion-toggle accordion-toggle-styled collapsed" data-toggle="collapse" data-parent="#accordion3" href="#collapse_3_1">
                                                                    Risk Assessments </a>
                                                            </h4>
                                                        </div>
                                                        <div id="collapse_3_1" class="panel-collapse collapse">
                                                            <div class="panel-body" style="height:200px; overflow-y:auto;">
                                                                <div class="mt-element-list">
                                                                    <div class="mt-list-container list-simple" style="border: none; margin: 0px; padding: 0px">
                                                                        <ul>
                                                                            @if ($worksite->docsOfType('RISK')->first())
                                                                                @foreach($worksite->docsOfType('RISK') as $doc)
                                                                                    <li class="mt-list-item" style="padding: 10px 0px">
                                                                                        <div class="list-icon-container"><a href="/filebank/site/{{$worksite->id}}/docs/{{ $doc->attachment }}"><i class="fa fa-file-text-o"></i></a></div>
                                                                                        <div class="list-item-content">{{ $doc->name }}</div>
                                                                                    </li>
                                                                                @endforeach
                                                                            @else
                                                                                <li class="mt-list-item" style="padding: 10px 0px">
                                                                                    <div class="list-icon-container"></div>
                                                                                    <div class="list-item-content">No current risk assessments for this site</div>
                                                                                </li>
                                                                            @endif
                                                                        </ul>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="panel panel-default">
                                                        <div class="panel-heading">
                                                            <h4 class="panel-title">
                                                                <a class="accordion-toggle accordion-toggle-styled collapsed" data-toggle="collapse" data-parent="#accordion3" href="#collapse_3_2">
                                                                    Hazardous Materials </a>
                                                            </h4>
                                                        </div>
                                                        <div id="collapse_3_2" class="panel-collapse collapse">
                                                            <div class="panel-body">
                                                                <div class="mt-element-list">
                                                                    <div class="mt-list-container list-simple" style="border: none;  margin: 0px; padding: 0px">
                                                                        <ul>
                                                                            @if ($worksite->docsOfType('HAZ')->first())
                                                                                @foreach($worksite->docsOfType('HAZ') as $doc)
                                                                                    <li class="mt-list-item" style="padding: 10px 0px">
                                                                                        <div class="list-icon-container"><a href="/filebank/site/{{$worksite->id}}/docs/{{ $doc->attachment }}"><i
                                                                                                        class="fa fa-file-text-o"></i></a></div>
                                                                                        <div class="list-item-content">{{ $doc->name }}</div>
                                                                                    </li>
                                                                                @endforeach
                                                                            @else
                                                                                <li class="mt-list-item" style="padding: 10px 0px">
                                                                                    <div class="list-icon-container"></div>
                                                                                    <div class="list-item-content">No current hazardous materials report for this site</div>
                                                                                </li>
                                                                            @endif
                                                                        </ul>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="panel panel-default">
                                                        <div class="panel-heading">
                                                            <h4 class="panel-title">
                                                                <a class="accordion-toggle accordion-toggle-styled collapsed" data-toggle="collapse" data-parent="#accordion3" href="#collapse_3_3">
                                                                    Safe Work Method Statements </a>
                                                            </h4>
                                                        </div>
                                                        <div id="collapse_3_3" class="panel-collapse collapse">
                                                            <div class="panel-body">
                                                                <div class="mt-element-list">
                                                                    <div class="mt-list-container list-simple" style="border: none;  margin: 0px; padding: 0px">
                                                                        <ul>
                                                                            @if (Auth::user()->company->wmsdocs->first())
                                                                                @foreach(Auth::user()->company->wmsdocs as $doc)
                                                                                    @if ($doc->status == 1)
                                                                                        <li class="mt-list-item" style="padding: 10px 0px">
                                                                                            <div class="list-icon-container">
                                                                                                @if($doc->attachment)
                                                                                                    <a href="/filebank/company/{{ Auth::user()->company_id }}/wms/{{ $doc->attachment }}"><i
                                                                                                                class="fa fa-file-text-o"></i></a>
                                                                                                @endif
                                                                                            </div>
                                                                                            <div class="list-item-content">{{ $doc->name }}</div>
                                                                                        </li>
                                                                                    @endif
                                                                                @endforeach
                                                                            @else
                                                                                <li class="mt-list-item" style="padding: 10px 0px">
                                                                                    <div class="list-icon-container"></div>
                                                                                    <div class="list-item-content">No Safe Work Method Statements</div>
                                                                                </li>
                                                                            @endif
                                                                        </ul>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>


                            <div class="row">
                                <div class="col-sm-2 col-xs-4 text-center">
                                    <div class="form-group">
                                        {!! Form::checkbox('question19', '1', false,
                                         ['class' => 'make-switch', 'data-size' => 'small',
                                         'data-on-text'=>'Yes', 'data-on-color'=>'success',
                                         'data-off-text'=>'No', 'data-off-color'=>'danger']) !!}
                                    </div>
                                </div>
                                <div class="col-sm-10 col-xs-8">
                                    I will <b>not make changes to any scaffold or Fall Protection</b> without express permission from the site supervisor
                                </div>
                            </div>
                            <div class="row visible-xs">&nbsp;</div>
                            <div class="row">
                                <div class="col-sm-2 col-xs-4 text-center">
                                    <div class="form-group">
                                        {!! Form::checkbox('question6', '1', false,
                                         ['class' => 'make-switch', 'data-size' => 'small', 'data-on-text'=>'Yes', 'data-on-color'=>'success','data-off-text'=>'No', 'data-off-color'=>'danger']) !!}
                                    </div>
                                </div>
                                <div class="col-sm-10 col-xs-8">
                                    I will <b>take action</b> to <b>eliminate or control any hazards</b> arising from my task and/or those that I identify
                                </div>
                            </div>
                            <div class="row visible-xs">&nbsp;</div>
                            <div class="row">
                                <div class="col-sm-2 col-xs-4 text-center">
                                    <div class="form-group">
                                        {!! Form::checkbox('question7', '1', false,
                                         ['class' => 'make-switch', 'data-size' => 'small',
                                         'data-on-text'=>'Yes', 'data-on-color'=>'success',
                                         'data-off-text'=>'No', 'data-off-color'=>'danger']) !!}
                                    </div>
                                </div>
                                <div class="col-sm-10 col-xs-8">
                                    I will <b>report all incidents, near misses, unsafe work practices and conditions</b> that I am involved with or that come to my attention
                                </div>
                            </div>
                            <div class="row visible-xs">&nbsp;</div>
                            <div class="row">
                                <div class="col-sm-2 col-xs-4 text-center">
                                    <div class="form-group">
                                        {!! Form::checkbox('question8', '1', false,
                                         ['class' => 'make-switch', 'data-size' => 'small',
                                         'data-on-text'=>'Yes', 'data-on-color'=>'success',
                                         'data-off-text'=>'No', 'data-off-color'=>'danger']) !!}
                                    </div>
                                </div>
                                <div class="col-sm-10 col-xs-8">
                                    I will <b>leave the site secure</b> and <b>safe</b> for others
                                </div>
                            </div>
                            <div class="row visible-xs">&nbsp;</div>
                            <div class="row">
                                <div class="col-sm-2 col-xs-4 text-center">
                                    <div class="form-group">
                                        {!! Form::checkbox('safe_site', '1', false,
                                         ['class' => 'make-switch', 'data-size' => 'small',
                                         'data-on-text'=>'Yes', 'data-on-color'=>'success',
                                         'data-off-text'=>'No', 'data-off-color'=>'danger', 'id'=>'safe_site']) !!}
                                    </div>
                                </div>
                                <div class="col-sm-10 col-xs-8">
                                    I have <b>conducted my own assessment</b> of the site and believe it to be <b>safe to work</b>
                                </div>
                            </div>

                            <!-- Unsafe Site Fields -->
                            @include('site/_checkin_hazard')

                            <div class="form-actions">
                                <button type="submit" class="btn green" name="checkin" value="true" id="submit">Submit</button>
                            </div>
                        </div> <!--/form-body-->
                        {!! Form::close() !!}
                        <!-- END FORM-->
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop <!-- END Content -->


@section('page-level-plugins-head')
    <link href="https://unpkg.com/filepond/dist/filepond.css" rel="stylesheet" type="text/css"/>   {{-- Filepond --}}
    <link href="/assets/global/plugins/bootstrap-select/css/bootstrap-select.min.css" rel="stylesheet" type="text/css"/>
@stop

@section('page-level-plugins')
    <script src="/assets/global/plugins/bootstrap-select/js/bootstrap-select.min.js" type="text/javascript"></script>
    <script src="https://unpkg.com/filepond/dist/filepond.min.js"></script> {{-- FilePond --}}
@stop

@section('page-level-scripts')
    {{-- Metronic + custom Page Scripts --}}
    <script src="/assets/global/plugins/bootstrap-fileinput/bootstrap-fileinput.js" type="text/javascript"></script>
    <script src="/assets/pages/scripts/components-bootstrap-select.min.js" type="text/javascript"></script>
    <script src="/js/filepond-basic.js" type="text/javascript"></script>
    <script>
        $(document).ready(function () {
            //$('#safe_site').bootstrapSwitch('state', false);
            //var state = $('#safe_site').bootstrapSwitch('state');
            if ($('#safe_site').bootstrapSwitch('state'))
                $('#unsafe-site').hide();

            $('#safe_site').on('switchChange.bootstrapSwitch', function (event, state) {
                $('#unsafe-site').toggle();
            });

            $('#open_docs').click(function () {
                $('#docs').show();
            });

            $('#close_docs').click(function () {
                $('#docs').hide();
            });
        });
    </script>
@stop

