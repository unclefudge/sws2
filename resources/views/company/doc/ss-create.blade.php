@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        @if (Auth::user()->company->subscription > 1 && Auth::user()->hasAnyPermissionType('company'))
            <li><a href="/company">Companies</a><i class="fa fa-circle"></i></li>
        @endif
        <li><a href="/company/{{ $company->id }}/doc">Documents</a><i class="fa fa-circle"></i></li>
        <li><span>Subcontactor's Statement</span></li>
    </ul>
@stop

@section('content')
    <div class="page-content-inner">

        @include('company/_header')

        <div class="row">
            <div class="col-md-12">
                <div class="note note-warning">
                    This contract has been created using details from your <a href="/company/{{ $company->id }}" target="_blank">Company Profile</a> as well as documents uploaded such as:
                    <ul>
                        <li>
                            {!! ($company->activeCompanyDoc('2') && $company->activeCompanyDoc('2')->status == 1) ? "<a href='".$company->activeCompanyDoc('2')->attachment_url."' target='_blank'>Workers Compensation</a>" : 'Workers Compensation' !!}
                            {!! ($company->requiresCompanyDoc(2) && !($company->activeCompanyDoc('2') && $company->activeCompanyDoc('2')->status == 1)) ? " &nbsp; <span class='font-red'> Not submitted </span> <a href='/company/$company->id/doc/create'><i class='fa fa-upload' style='padding: 0px 15px'></i> Upload</a>" : '' !!}
                        </li>
                    </ul>
                    <b>Please ensure ALL of these items are uploaded and correct prior to generating this form.</b><br><br>
                    If any information is incorrect please update your above details/documents or contact <a href="mailto:{!! ($company->reportsTo()->id == 3) ? 'accounts1@capecod.com.au' : $company->reportsTo()->email !!}">{{ $company->reportsTo()->name }}</a>
                    @if ($company->requiresCompanyDoc(2) && !($company->activeCompanyDoc(2) && $company->activeCompanyDoc(2)->status == 1))
                        <br><br><span class="font-red"><b><i class="fa fa-warning"></i> We are unble to generate your Subcontractor's Statement until you have submitted the required documents</b></span>
                    @endif
                </div>
            </div>
        </div>
        @if (!($company->requiresCompanyDoc(2) && !($company->activeCompanyDoc('2') && $company->activeCompanyDoc('2')->status == 1)))
            <div class="row">
                <div class="col-md-12">
                    <div class="portlet light bordered">
                        <div class="portlet-title">
                            <div class="caption">
                                <span class="caption-subject font-dark bold uppercase"> Subcontactor's Statement </span>
                            </div>
                        </div>
                        <div class="portlet-body form">
                            {!! Form::model('CompanyDocSubcontractorStatement', ['action' => ['Company\CompanySubcontractorStatementController@store', $company->id], 'class' => 'horizontal-form']) !!}
                            @include('form-error')

                            <div class="form-body">
                                <div class="row">
                                    <div class="col-xs-1">
                                        <img src="/img/nsw_coatofarms.jpg" width="100px">
                                    </div>
                                    <div class="col-xs-11 text-center">
                                        <h3 style="margin: 0px">SUBCONTRACTOR’S STATEMENT</h3>
                                        <h5 style="margin: 0px">REGARDING WORKER’S COMPENSATION, PAYROLL TAX AND</h5>
                                        <h5 style="margin: 0px">REMUNERATION (Note1 – see back of form)</h5>
                                    </div>
                                </div>

                                {{-- Intro --}}
                                <div class="row">
                                    <div class="col-xs-12">
                                        <p class="text-justify">For the purposes of this Statement a “subcontractor” is a person (or other legal entity) that has entered into a contract with a “principal contractor” to carry
                                            out work.</p>
                                        <p class="text-justify">This Statement must be signed by a “subcontractor” (or by a person who is authorised, or held out as being authorised, to sign the statement by the
                                            subcontractor) referred to in
                                            any of s175B Workers Compensation Act 1987, Schedule 2 Part 5 Payroll Tax Act 2007, and s127 Industrial Relations Act 1996 where the “subcontractor” has employed or engaged workers
                                            or subcontractors during the period of the contract to which the form applies under the relevant Act(s). The signed Statement is to be submitted to the relevant principal
                                            contractor.</p>
                                        <p style="font-size:14px; font-weight:700">SUBCONTRACTOR’S STATEMENT (Refer to the <a href="/filebank/subcontractor_statement_notes.pdf" target="_blank">Notes</a>, period of Statement retention, and Offences under various Acts.</p>
                                    </div>
                                </div>

                                {{-- Contact Details --}}
                                <div style="width: 100%; display: table;">
                                    <span style="display: table-cell; width: 120px;">Subcontractor:</span>
                                    <span style="display: table-cell; border-bottom: 1px solid #eee; border-top: 0px">{{ $company->name }}</span>
                                    <span style="display: table-cell; width: 30px;">&nbsp;</span>
                                    <span style="display: table-cell; width: 50px;">ABN:</span>
                                    <span style="display: table-cell; width: 130px; border-bottom: 1px solid #eee; border-top: 0px">{{ $company->abn }}</span>
                                </div>
                                <div style="color:#bfbfbf; padding-left: 250px">(Business name)</div>

                                <div style="width: 100%; display: table;">
                                    <span style="display: table-cell; width: 20px;">of: </span>
                                    <span style="display: table-cell; border-bottom: 1px solid #eee;">{{ $company->address }} {{  $company->suburb_state_postcode }} </span>
                                </div>
                                <div style="color:#bfbfbf; padding-left: 300px">(Address of subcontractor)</div>

                                {{-- Note 2 --}}
                                <div style="width: 100%; display: table;">
                                    <span style="display: table-cell; width: 220px;">has entered into a contract with:</span>
                                    <span style="display: table-cell; border-bottom: 1px solid #eee; border-top: 0px">{{ $company->reportsTo()->name }}</span>
                                    <span style="display: table-cell; width: 30px;">&nbsp;</span>
                                    <span style="display: table-cell; width: 50px;">ABN:</span>
                                    <span style="display: table-cell; width: 130px; border-bottom: 1px solid #eee; border-top: 0px">{{ $company->reportsTo()->abn }}</span>
                                </div>
                                <div style="width: 100%; display: table;">
                                    <span style="display: table-cell; width: 220px;">&nbsp;</span>
                                    <span style="display: table-cell; width: 600px; color:#bfbfbf; text-align: center">(Business name of principal contractor)</span>
                                    <span style="display: table-cell; width: 50px;">&nbsp;</span>
                                    <span style="display: table-cell; width: 60px;">&nbsp;</span>
                                    <span style="display: table-cell;"><span class="pull-right"><b>(Note 2)</b></span></span>
                                </div>
                                <br>

                                {{-- Note 3 --}}
                                <div class="row">
                                    <div class="col-md-4">{!! Form::label('contract_no', 'Contract number/identifier', ['class' => 'control-label']) !!}</div>
                                    <div class="col-md-8"> {!! Form::text('contract_no', null, ['class' => 'form-control', 'placeholder' => 'optional']) !!}</div>
                                </div>
                                <div><span class="pull-right" style="padding-top: 3px"><b>(Note 3)</b></span></div>
                                <br>

                                {{-- Note 4 --}}
                                <div class="row">
                                    <div class="col-md-4">{!! Form::label('contract_no', 'This Statement applies for work between:', ['class' => 'control-label']) !!}</div>
                                    <div class="col-md-2">
                                        {!! Form::select('date_from', $dates_from, null, ['class' => 'form-control bs-select', 'id' => 'date_from']) !!}
                                        {!! fieldErrorMessage('date_from', $errors) !!}
                                    </div>
                                    <div class="col-md-1 text-center">
                                        <div style="margin-top: 7px">and</div>
                                    </div>
                                    <div class="col-md-2"> {!! Form::select('date_to', $dates_to, null, ['class' => 'form-control bs-select', 'id' => 'date_to']) !!}</div>
                                    <div class="col-md-3">
                                        <div style="margin-top: 7px">inclusive</div>
                                    </div>
                                </div>
                                <div><span class="pull-right" style="padding-top: 3px"><b>(Note 4)</b></span></div>
                                <br>

                                {{-- Note 5 --}}
                                <div class="row">
                                    <div class="col-md-4">{!! Form::label('claim_payment', 'subject of the payment claim dated:', ['class' => 'control-label']) !!}</div>
                                    <div class="col-md-2">
                                        <div class="form-group {!! fieldHasError('claim_payment', $errors) !!}">
                                            <div class="input-group date date-picker">
                                                {!! Form::text('claim_payment', null, ['class' => 'form-control form-control-inline', 'readonly',
                                                'style' => 'background:#FFF', 'data-date-format' => "dd-mm-yyyy"]) !!}
                                                <span class="input-group-btn">
                                            <button class="btn default date-set" type="button"><i class="fa fa-calendar"></i></button>
                                        </span>
                                            </div>
                                            <!-- /input-group -->
                                            {!! fieldErrorMessage('claim_payment', $errors) !!}
                                        </div>
                                    </div>
                                    <div class="col-md-6"><span class="pull-right" style="padding-top: 3px"><b>(Note 5)</b></span></div>
                                </div>
                                <br>

                                {{-- Declaration --}}
                                <div class="row">
                                    <div class="col-md-1 text-center">
                                        <div style="margin-top: 10px">I</div>
                                    </div>
                                    <div class="col-md-6"> {!! Form::text('contractor_full_name', null, ['class' => 'form-control', 'id' => 'contractor_full_name', 'placeholder' => 'Full name']) !!}</div>
                                    <div class="col-md-5">
                                        <div style="margin-top: 10px">a Director or a person authorised by the Subcontractor</div>
                                    </div>
                                </div>
                                <br>
                                <div class="text-justify">on whose behalf this declaration is made, hereby declare that I am in a position to know the truth of the matters which are contained in this Subcontractor’s
                                    Statement and declare the following to the best of my knowledge and belief:
                                </div>
                                <br>

                                {{-- Dot points --}}
                                <ol type="a" style="padding-left:15px">
                                    {{-- Note 6 --}}
                                    <li class="text-justify">The abovementioned Subcontractor has either employed or engaged workers or subcontractors during the above period of this contract.
                                        <div class="form-group {!! fieldHasError('clause_a', $errors) !!}">
                                            <div class="col-md-12">
                                                <div class="mt-radio-list">
                                                    <label class="mt-radio">
                                                        <input type="radio" name="clause_a" id="clause_a1" value="1" @if (old('clause_a') == 1) checked @endif> if true and comply with <b>(b)</b> to <b>(g)</b> below, as applicable. If it is not the case that workers or subcontractors are involved or
                                                        you
                                                        are an exempt employer for workers compensation purposes
                                                        <span></span>
                                                    </label>
                                                    <label class="mt-radio">
                                                        <input type="radio" name="clause_a" id="clause_a2" value="2" @if (old('clause_a') == 2) checked @endif> and only complete <b>(f)</b> and <b>(g)</b> below.
                                                        <span></span>
                                                    </label>
                                                </div>
                                                {!! fieldErrorMessage('clause_a', $errors) !!}
                                            </div>
                                        </div>
                                        <div class="col-md-12">You must tick one box. <span class="pull-right"><b>(Note 6)</b></span><br><br></div>
                                    </li>
                                    {{-- Note 7 --}}
                                    <li class="text-justify">All workers compensation insurance premiums payable by the Subcontractor in respect of the work done under the contract have been paid. The Certificate of
                                        Currency for that insurance is attached and is dated {!! ($company->activeCompanyDoc('2') && $company->activeCompanyDoc('2')->status == 1 ) ? '<b>'.$company->activeCompanyDoc('2')->expiry->format('d / m / Y').'</b>' :  '......../......../........' !!}
                                        <span class="pull-right"><b>(Note 7)</b></span><br><br>
                                    </li>
                                    {{-- Note 8 --}}
                                    <li class="text-justify">All remuneration payable to relevant employees for work under the contract for the above period has been
                                        paid. &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; <span class="pull-right"><b>(Note 8)</b></span><br><br>
                                    </li>
                                    {{-- Note 9 --}}
                                    <li class="text-justify">Where the Subcontractor is required to be registered as an employer under the Payroll Tax Act 2007, the Subcontractor has paid all payroll tax due in
                                        respect of employees who
                                        performed work under the contract, as required at the date of this Subcontractor’s Statement. <span class="pull-right"><b>(Note 9)</b></span><br><br>
                                    </li>
                                    {{-- Note 10 --}}
                                    <li class="text-justify">Where the Subcontractor is also a principal contractor in connection with the work, the Subcontractor has in its capacity of principal contractor been
                                        given a written
                                        Subcontractor’s Statement by its subcontractor(s) in connection with that work for the period stated above. <span class="pull-right"><b>(Note 10)</b></span>
                                    </li>
                                </ol>


                                {{-- Signature --}}
                                <div class="row">
                                    <div class="form-group">
                                        {!! Form::label('contractor_signed_name', "f. &nbsp; Signature", ['class' => 'col-md-2 control-label']) !!}
                                        <div class="col-md-6" style="display: none" id="contractor_signed_name_field">
                                            {!! Form::textarea('contractor_signed_name', '', ['rows' => '3', 'class' => 'form-control', 'readonly']) !!}
                                            <span class="help-block">By signing this contract you accept the above as your digital signature.</span>
                                        </div>
                                    </div>
                                    <button type="button" class="btn green" id="sign_contractor"> Sign Contract</button>
                                </div>
                                <br>

                                {{-- Position --}}
                                <div class="row">
                                    <div class="form-group {!! fieldHasError('contractor_signed_title', $errors) !!}">
                                        {!! Form::label('contractor_signed_name', "g. &nbsp;Position/Title", ['class' => 'col-md-2 control-label']) !!}
                                        <div class="col-md-9">
                                            {!! Form::text('contractor_signed_title', null, ['class' => 'form-control', 'placeholder' => 'Position/Title']) !!}
                                            {!! fieldErrorMessage('contractor_signed_title', $errors) !!}
                                        </div>
                                    </div>
                                </div>
                                <br>

                                {{-- Footer Note --}}
                                <div class="text-justify"><b>NOTE:</b> Where required above, this Statement must be accompanied by the relevant Certificate of Currency to comply with section 175B of the Workers
                                    Compensation Act 1987.
                                </div>

                                <br><br>
                                <div><b>Once you have signed this contact please click the "Submit" button to complete the submission.</b></div>

                                <br><br>
                                <div class="form-actions right">
                                    <a href="/company/{{ $company->id }}/doc" class="btn default"> Back</a>
                                    <button type="submit" name="save" value="save" class="btn green" id="submit" style="display: none;">Submit</button>
                                </div>
                            </div>
                            {!! Form::close() !!}
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
@stop

@section('page-level-plugins-head')
    <link href="/assets/global/plugins/bootstrap-datepicker/css/bootstrap-datepicker.min.css" rel="stylesheet" type="text/css"/>
@stop

@section('page-level-plugins')
    <script src="/assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js" type="text/javascript"></script>
    <script src="/js/moment.min.js" type="text/javascript"></script>
@stop

@section('page-level-scripts') {{-- Metronic + custom Page Scripts --}}
<script src="/assets/pages/scripts/components-date-time-pickers.min.js" type="text/javascript"></script>
<script>
    $('.date-picker').datepicker({autoclose: true, format: 'dd/mm/yyyy'});

    $(document).ready(function () {
        // Clear Signature on page load
        $('#contractor_signed_name').val('');

        $('#sign_contractor').on('click', function () {
            var name = $('#contractor_full_name').val();
            var user = "{!! Auth::user()->fullname !!}";
            var email = "{!! (Auth::user()->email) ?  ' ('.Auth::user()->email.')' : '' !!}";
            var date = moment().format('DD/MM/YYYY, h:mm:ss a');
            var signed_string = name + "\n" + 'Digitally signed by ' + user + email + "\nDate: " + date;
            $('#contractor_signed_name').val(signed_string);

            if (name != '') {
                $('#submit').show();
                $('#contractor_signed_name_field').show();
            } else {
                swal({
                    title: 'Unable to Sign Contract',
                    text: '<b>Please enter your full name where requested</b>',
                    html: true
                });
                $('#contractor_signed_name').val('');
                $('#contractor_full_name').val('');
                $('#contractor_signed_name_field').hide();
                $('#submit').hide();
            }
        });

        $('#date_from').change(function () {
            $('#date_to').val($('#date_from').val());
            $('#date_to').change();
        });

        $('#date_to').change(function () {
            $('#date_from').val($('#date_to').val());
            $('#date_from').change();
        });

    });

</script>
@stop