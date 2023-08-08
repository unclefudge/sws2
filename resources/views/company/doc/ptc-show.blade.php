@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        @if (Auth::user()->company->subscription > 1 && Auth::user()->hasAnyPermissionType('company'))
            <li><a href="/company">Companies</a><i class="fa fa-circle"></i></li>
        @endif
        <li><a href="/company/{{ $company->id }}/doc">Documents</a><i class="fa fa-circle"></i></li>
        <li><span>Period Trade Contract</span></li>
    </ul>
@stop

@section('content')
    <div class="page-content-inner">

        @include('company/_header')

        <div class="row">
            <div class="col-md-12">
                <div class="portlet light bordered">
                    <div class="portlet-title">
                        <div class="caption">
                            <span class="caption-subject font-dark bold uppercase"> Period Trade Contract </span>
                            @if ($ptc->status == 2)
                                <span class="label label-warning label">Pending Approval</span>
                            @endif
                            @if ($ptc->status == 3)
                                <span class="label label-danger label">Rejected</span>
                            @endif
                        </div>
                        @if ($ptc->status == 1)
                            <div class="actions">
                                <a class="btn btn-circle green btn-outline btn-sm" href="{{ $ptc->attachment_url }}" data-original-title="Upload">View PDF</a>
                            </div>
                        @endif
                    </div>
                    <div class="portlet-body form">
                        <!-- BEGIN FORM-->
                        {!! Form::model($ptc, ['method' => 'PATCH', 'action' => ['Company\CompanyPeriodTradeController@update', $company->id, $ptc->id], 'class' => 'horizontal-form']) !!}

                        @include('form-error')

                        <div class="form-body">
                            @if ($ptc->status == 3)
                                <div class="alert alert-danger">
                                    The document was not approved for the following reason:
                                    <ul>
                                        <li>{!! nl2br($ptc->reject) !!}</li>
                                    </ul>
                                </div>
                                <br>
                            @endif

                            <div class="row">
                                <div class="col-md-12">
                                    <h5 style="margin: -20px 0 0 0">SCHEDULE</h5><br>
                                </div>
                            </div>

                            {{-- Schedule 1. Date --}}
                            <table class="table" style="padding: 0px; margin: 0px">
                                <tr>
                                    <td width="5%" style="margin:5px 0 0 0; padding: 5px 0px; border: 0px"><h5 style="margin: 0px">1.</h5></td>
                                    <td class="pad5" style="border: 0px">
                                        <h4 style="margin: 0px">Date</h4>
                                        <hr style="margin: 5px 0px 5px 0px">
                                        AN AGREEMENT DATED &nbsp; <b>{{ $ptc->date->format('d/m/Y') }}</b>
                                    </td>
                                </tr>
                            </table>

                            {{-- Schedule 2. Principle Contractor --}}
                            <table class="table" style="padding: 0px; margin: 0px">
                                <tr>
                                    <td width="5%" style="margin:5px 0 0 0; padding: 5px 0px; border: 0px"><h5 style="margin: 0px">2.</h5></td>
                                    <td class="pad5" style="border: 0px">
                                        <h4 style="margin: 0px">Principal contractor</h4>
                                        <hr style="margin: 5px 0px 0px 0px">
                                    </td>
                                </tr>
                                <tr>
                                    <td width="5%" class="pad0" style="border: 0px"><h5 style="margin: 0px">&nbsp;</h5></td>
                                    <td class="pad5" style="border: 0px">
                                        <div style="width: 100%; display: table;">
                                            <span style="display: table-cell; width: 90px;">NAME </span>
                                            <span style="display: table-cell;">{{ $ptc->principle_name }}</span>
                                        </div>
                                        <div style="width: 100%; display: table;">
                                            <span style="display: table-cell; width: 90px;">ADDRESS </span>
                                            <span style="display: table-cell;">{!! $ptc->principle_address !!}<br></span>
                                        </div>
                                        <div style="width: 100%; display: table;">
                                            <span style="display: table-cell; width: 90px;">ABN </span>
                                            <span style="display: table-cell; width: 200px;">{{ $ptc->principle_abn }}</span>
                                            <span style="display: table-cell;">ACN  &nbsp; &nbsp; {{ $ptc->principle_acn }} </span>
                                        </div>
                                        <div style="width: 100%; display: table;">
                                            <span style="display: table-cell; width: 90px;">PHONE </span>
                                            <span style="display: table-cell">{{ $ptc->principle_phone }}</span>
                                        </div>
                                        <div style="width: 100%; display: table;">
                                            <span style="display: table-cell; width: 90px;">EMAIL </span>
                                            <span style="display: table-cell">{{ $ptc->principle_email }}</span>
                                        </div>
                                        <div style="width: 100%; display: table;">
                                            <span style="display: table-cell; width: 190px;">HIA MEMBER NUMBER </span>
                                            <span style="display: table-cell">&nbsp;</span>
                                        </div>
                                    </td>
                                </tr>
                            </table>


                            {{-- Schedule 3. Tade Contractor --}}
                            <table class="table" style="padding: 0px; margin: 0px">
                                <tr>
                                    <td width="5%" style="margin:5px 0 0 0; padding: 5px 0px; border: 0px"><h5 style="margin: 0px">3.</h5></td>
                                    <td class="pad5" style="border: 0px">
                                        <h4 style="margin: 0px">Trade contractor</h4>
                                        <hr style="margin: 5px 0px 0px 0px">
                                    </td>
                                </tr>
                                <tr>
                                    <td width="5%" class="pad0" style="border: 0px"><h5 style="margin: 0px">&nbsp;</h5></td>
                                    <td class="pad5" style="border: 0px">
                                        <div style="width: 100%; display: table;">
                                            <span style="display: table-cell; width: 90px;">NAME </span>
                                            <span style="display: table-cell">{{ $ptc->contractor_name }}</span>
                                        </div>
                                        <div style="width: 100%; display: table;">
                                            <span style="display: table-cell; width: 90px;">ADDRESS </span>
                                            <span style="display: table-cell">{!! $ptc->contractor_address !!}<br></span>
                                        </div>
                                        <div style="width: 100%; display: table;">
                                            <span style="display: table-cell; width: 90px;">ABN </span>
                                            <span style="display: table-cell; width: 200px;">{{ $ptc->contractor_abn }}</span>
                                            <span style="display: table-cell;">ACN &nbsp; &nbsp; {{ $ptc->contractor_acn }}</span>
                                        </div>
                                        <div style="width: 100%; display: table;">
                                            <span style="display: table-cell; width: 90px;">PHONE </span>
                                            <span style="display: table-cell">{{ $ptc->contractor_phone }}</span>
                                        </div>
                                        <div style="width: 100%; display: table;">
                                            <span style="display: table-cell; width: 90px;">EMAIL </span>
                                            <span style="display: table-cell">{{ $ptc->contractor_email }}</span>
                                        </div>
                                    </td>
                                </tr>
                            </table>

                            {{-- Schedule 4 --}}
                            <table class="table" style="padding: 0px; margin: 0px">
                                <tr>
                                    <td width="5%" style="margin:5px 0px 0px 0px; padding: 5px 0px; border: 0px"><h5 style="margin: 0px">4.</h5></td>
                                    <td class="pad0" style="border: 0px;">
                                        In consideration of:
                                        <ol type="a" style="padding-left:25px; margin-bottom: 5px">
                                            <li>the <b>trade contractor</b> agreeing to quote for <b>trade works</b> requested by the <b>principal contractor</b>, and</li>
                                            <li>the <b>principal contractor</b> agreeing to pay, on demand by the <b>trade contractor</b>, the invoiced amount relating and compliant to Item 6,</li>
                                        </ol>
                                        the parties agree that the period trade contract conditions overleaf are deemed to be incorporated into each <b>trade contract</b> for a period of 12 months from the date of this agreement.
                                    </td>
                                </tr>
                            </table>

                            {{-- Schedule 5 --}}
                            <table class="table" style="padding: 0px; margin: 0px">
                                <tr>
                                    <td width="5%" style="margin:5px 0 0 0; padding: 5px 0px; border: 0px"><h5 style="margin: 0px">5.</h5></td>
                                    <td class="pad0" style="border: 0px">
                                        The <b>trade contractor</b> acknowledges and agrees that:
                                        <ol type="a" style="padding-left:25px; margin-bottom: 5px">
                                            <li>the <b>principal contractor</b> has not made any representation, and</li>
                                            <li>the <b>trade contractor</b> has not relied on any representation made by the <b>principal contractor</b>,</li>
                                        </ol>
                                        as to the availability of work or the number of work orders that will be issued by the <b>principal contractor</b>.
                                    </td>
                                </tr>
                            </table>

                            {{-- Schedule 6 --}}
                            <table class="table" style="padding: 0px; margin: 0px">
                                <tr>
                                    <td width="5%" style="margin:5px 0 0 0; padding: 5px 0px; border: 0px"><h5 style="margin: 0px">6.</h5></td>
                                    <td class="pad5" style="border: 0px">
                                        The parties agree that for each <b>trade contract</b> the scope of the <b>trade works</b>, the commencement and completion dates of the <b>trade works</b> and the price of <b>trade
                                            works</b> will be set out:
                                        <ol type="a" style="padding-left:25px">
                                            <li>in a quote from the <b>trade contractor</b> that is accepted by the <b>principal contractor</b>;</li>
                                            <li>in a work order issued by the <b>principal contractor</b> that is accepted by the <b>trade contractor</b>; or</li>
                                            <li>as otherwise evidenced in writing and signed by the parties.</li>
                                        </ol>
                                    </td>
                                </tr>
                            </table>

                            {{-- Schedule 7 --}}
                            <table class="table" style="padding: 0px; margin: 0px">
                                <tr>
                                    <td width="5%" style="margin:5px 0 0 0; padding: 5px 0px; border: 0px"><h5 style="margin: 0px">7.</h5></td>
                                    <td class="pad5" style="border: 0px">
                                        The parties agree that this agreement does not form a contract to carry out work. The obligation to carry out work arises on the formation
                                        of a <b>trade contract</b>
                                        as described in paragraph 6 above.
                                    </td>
                                </tr>
                            </table>

                            {{-- Schedule 8 --}}
                            <table class="table" style="padding: 0px; margin: 0px">
                                <tr>
                                    <td width="5%" style="margin:5px 0 0 0; padding: 5px 0px; border: 0px"><h5 style="margin: 0px">8.</h5></td>
                                    <td class="pad5" style="border: 0px">
                                        <b>"Defects liability period"</b> in a <b>trade contract</b> means a period of 12 weeks from the practical completion of the work under the
                                        <b>head contract</b>.
                                    </td>
                                </tr>
                            </table>

                            {{-- Schedule 9 --}}
                            <hr style="margin: 5px 0px 5px 0px">
                            <table class="table" style="padding: 0px; margin: 0px">
                                <tr>
                                    <td width="5%" style="margin:5px 0 0 0; padding: 5px 0px; border: 0px"><h5 style="margin: 0px">9.</h5></td>
                                    <td class="pad5" style="border: 0px">
                                        <h5 style="margin: 0px">INFORMATION TO BE COMPLETED BY THE TRADE CONTRACTOR</h5>
                                    </td>
                                </tr>
                            </table>

                            {{-- Licence No. --}}
                            <br><br>
                            <div style="width: 100%; display: table;">
                                <span style="display: table-cell; width: 200px;">LICENCE NO (if required) </span>
                                <span style="display: table-cell; border-bottom: 1px solid #eee; border-top: 0px">{{ $ptc->contractor_licence }}</span>
                            </div>
                            <br>

                            {{-- Public Liabilty --}}
                            <h4 style="margin-bottom: 3px">PUBLIC LIABILITY INSURANCE</h4>
                            <div style="width: 100%; display: table;">
                                <span style="display: table-cell; width: 30px;">&nbsp;</span>
                                <span style="display: table-cell; width: 120px;">COMPANY</span>
                                <span style="display: table-cell; border-bottom: 1px solid #eee; border-top: 0px">{{ $ptc->contractor_pl_name }}</span>
                            </div>
                            <br>
                            <div style="width: 100%; display: table;">
                                <span style="display: table-cell; width: 30px;">&nbsp;</span>
                                <span style="display: table-cell; width: 120px;">POLICY NO</span>
                                <span style="display: table-cell; width: 340px; border-bottom: 1px solid #eee; border-top: 0px">{{ $ptc->contractor_pl_ref }}</span>
                                <span style="display: table-cell; width: 50px;">&nbsp;</span>
                                <span style="display: table-cell; width: 120px;">CURRENT TO</span>
                                <span style="display: table-cell; border-bottom: 1px solid #eee; border-top: 0px">{{ ($ptc->contractor_pl_expiry) ? $ptc->contractor_pl_expiry->format('d/m/Y') : '' }}</span>
                            </div>
                            <br>

                            {{-- Workers Comp --}}
                            <h4 style="margin-bottom: 3px">WORKERS COMPENSATION INSURANCE</h4>
                            <div style="width: 100%; display: table;">
                                <span style="display: table-cell; width: 30px;">&nbsp;</span>
                                <span style="display: table-cell; width: 120px;">COMPANY</span>
                                <span style="display: table-cell; border-bottom: 1px solid #eee; border-top: 0px">{{ $ptc->contractor_wc_name }}</span>
                            </div>
                            <br>
                            <div style="width: 100%; display: table;">
                                <span style="display: table-cell; width: 30px;">&nbsp;</span>
                                <span style="display: table-cell; width: 120px;">POLICY NO</span>
                                <span style="display: table-cell; width: 340px; border-bottom: 1px solid #eee; border-top: 0px">{{ $ptc->contractor_wc_ref }}</span>
                                <span style="display: table-cell; width: 50px;">&nbsp;</span>
                                <span style="display: table-cell; width: 120px;">CURRENT TO</span>
                                <span style="display: table-cell; border-bottom: 1px solid #eee; border-top: 0px">{{ ($ptc->contractor_wc_expiry) ? $ptc->contractor_wc_expiry->format('d/m/Y') : '' }}</span>
                            </div>
                            <br>

                            {{-- Sickness + Accident --}}
                            <h4 style="margin-bottom: 3px">SICKNESS & ACCIDENT INSURANCE</h4>
                            <div style="width: 100%; display: table;">
                                <span style="display: table-cell; width: 30px;">&nbsp;</span>
                                <span style="display: table-cell; width: 120px;">COMPANY</span>
                                <span style="display: table-cell; border-bottom: 1px solid #eee; border-top: 0px">{{ $ptc->contractor_sa_name }}</span>
                            </div>
                            <br>
                            <div style="width: 100%; display: table;">
                                <span style="display: table-cell; width: 30px;">&nbsp;</span>
                                <span style="display: table-cell; width: 120px;">POLICY NO</span>
                                <span style="display: table-cell; width: 340px; border-bottom: 1px solid #eee; border-top: 0px">{{ $ptc->contractor_sa_ref }}</span>
                                <span style="display: table-cell; width: 50px;">&nbsp;</span>
                                <span style="display: table-cell; width: 120px;">CURRENT TO</span>
                                <span style="display: table-cell; border-bottom: 1px solid #eee; border-top: 0px">{{ ($ptc->contractor_sa_expiry) ? $ptc->contractor_sa_expiry->format('d/m/Y') : '' }}</span>
                            </div>
                            <br>

                            {{-- ABN + GST --}}
                            <br><br>
                            <div style="width: 100%; display: table;">
                                <span style="display: table-cell; width: 60px;">ABN</span>
                                <span style="display: table-cell; width: 150px; border-bottom: 1px solid #eee; border-top: 0px">{{ $ptc->contractor_abn }}</span>
                                <span style="display: table-cell; width: 50px;">&nbsp;</span>
                                <span style="display: table-cell; width: 250px;">ARE YOU REGISTERED FOR GST?</span>
                                <span style="display: table-cell; width: 150px; border-bottom: 1px solid #eee; border-top: 0px">@if($ptc->contractor_gst)
                                        Yes
                                    @elseif($ptc->contractor_gst == '0')
                                        No
                                    @else
                                        "&nbsp;"
                                    @endif</span>
                                <span style="display: table-cell;">&nbsp;</span>
                            </div>

                            {{-- Signature --}}
                            <br><br><br>
                            <h4 style="margin-bottom: 3px">AGREEMENT</h4>
                            <br>This document serves as a formal agreement between the <b>Principal Contractor</b> and <b>Trade Contractor</b> as identified at 2 and 3 of the Schedule, hereby referred to as the 'parties,' representing their mutual understanding and acceptance of the terms and
                            conditions outlined herein as the <a href="/filebank/period_trade_contract_conditions.pdf" target="_blank">Period Trade Contract Conditions</a>. By signing below each party acknowledges their full comprehension of the Period Trade Contract contents and willingly enters a
                            legally binding contract.<br><br>

                            <div class="row">
                                <div class="form-group {!! fieldHasError('contractor_signed_name', $errors) !!}">
                                    {!! Form::label('contractor_signed_name', "Trade Contractor's Signature", ['class' => 'col-md-3 control-label']) !!}
                                    <div class="col-md-6">
                                        {!! Form::textarea('contractor_signed_name', $ptc->contractor_signed_name, ['rows' => '3', 'class' => 'form-control', 'disabled']) !!}
                                        {!! fieldErrorMessage('contractor_signed_name', $errors) !!}
                                    </div>
                                </div>
                            </div>
                            <br>

                            <div class="row">
                                <div class="form-group">
                                    {!! Form::label('principle_signed_name', "Principle Contractor's Signature", ['class' => 'col-md-3 control-label']) !!}
                                    <div class="col-md-6">
                                        @if (($ptc->principle_signed_id && $ptc->status != 3) || (Auth::user()->isCompany($ptc->company_id) && $ptc->status == 2 ))
                                            {!! Form::textarea('principle_signed_name', null, ['rows' => '3', 'class' => 'form-control', 'readonly']) !!}
                                            <span class="help-block">By signing this contract you accept the above as your digital signature.</span>
                                        @else
                                            <span class="font-red">Waiting for principle contractor's signature</span>
                                        @endif
                                    </div>
                                </div>
                                @if (Auth::user()->isCompany($ptc->company_id) && $ptc->status == 2)
                                    <div class="col-md-2">
                                        <a href="#modal_sign_contractor" class="btn green" data-toggle="modal" id="sign_contractor"> Sign Contract</a>
                                    </div>
                                @endif
                            </div>

                            @if (Auth::user()->isCompany($ptc->company_id) && $ptc->status == 2)
                                <br><br>
                                <div><b>Once you have signed this contact please click the "Submit" button to complete the submission.</b></div>
                            @endif

                            <br><br>
                            <div class="form-actions right">
                                <a href="/company/{{ $company->id }}/doc" class="btn default"> Back</a>
                                @if (Auth::user()->isCompany($ptc->company_id) && $ptc->status == 2)
                                    <a class="btn dark" data-toggle="modal" href="#modal_reject"> Reject </a>
                                @endif
                                @if (($company->activeCompanyDoc('5') && $company->activeCompanyDoc('5')->status == 1))
                                    <a href="#modal_archive" class="btn green" data-toggle="modal" id="sign_archive" style="display: none;">Submit</a>
                                @else
                                    <button type="submit" name="save" value="save" class="btn green" id="submit" style="display: none;">Submit</button>
                                @endif
                            </div>
                        </div>
                    </div>
                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </div>

    <!-- Sign Contractors Modal -->
    <div id="modal_sign_contractor" class="modal fade bs-modal-sm" tabindex="-1" role="basic" aria-hidden="true">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <form action="#" method="POST" name="form_sign_contractor" id="form_sign_contractor">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                        <h4 class="modal-title text-center"><b>Sign Contract</b></h4>
                    </div>
                    <div class="modal-body">
                        <p class="text-center">By signing this contract you accept the name entered below as your digital signature.<br></p>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    {!! Form::label('signed_name', 'Enter your Name', ['class' => 'control-label']) !!}
                                    {!! Form::text('signed_name', null, ['class' => 'form-control']) !!}
                                </div>
                            </div>
                        </div>
                        <br>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                        <button type="button" class="btn green" data-dismiss="modal" id="sign_contractor_accept">Sign</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Reject Modal --}}
    <div id="modal_reject" class="modal fade" id="basic" tabindex="-1" role="modal_reject" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                    <h4 class="modal-title">Reject Contract</h4>
                </div>
                <div class="modal-body">
                    {!! Form::model($ptc, ['method' => 'POST', 'action' => ['Company\CompanyPeriodTradeController@reject', $company->id, $ptc->id], 'class' => 'horizontal-form']) !!}
                    <div class="form-group {!! fieldHasError('reject', $errors) !!}">
                        {!! Form::label('reject', 'Reason for rejecting contract', ['class' => 'control-label']) !!}
                        {!! Form::textarea('reject', null, ['rows' => '3', 'class' => 'form-control']) !!}
                        {!! fieldErrorMessage('reject', $errors) !!}
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn dark btn-outline" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn green" name="reject_doc" value="reject">Reject</button>
                </div>
                {!! Form::close() !!}
            </div>
        </div>
    </div>

    {{-- Archive Modal --}}
    @if (($company->activeCompanyDoc('5')))
        <div id="modal_archive" class="modal fade bs-modal-sm" id="basic" tabindex="-1" role="modal_archive" aria-hidden="true">
            <div class="modal-dialog modal-sm">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                        <h4 class="modal-title">Replace Existing Contract</h4>
                    </div>
                    <div class="modal-body">
                        {!! Form::model($ptc, ['method' => 'PATCH', 'action' => ['Company\CompanyPeriodTradeController@update', $company->id, $ptc->id], 'class' => 'horizontal-form']) !!}
                        {!! Form::hidden('archive', $company->activeCompanyDoc('5')->id, ['class' => 'form-control']) !!}

                        <textarea name="principle_signed_name2" id="principle_signed_name2" rows="3" class="form-control" readonly="" style="display:none"></textarea>

                        <div class="text-center">
                            <b>{{ $company->name }}</b> currently has the following valid contract<br><br>
                            <a href="{!! $company->activeCompanyDoc('5')->attachment_url !!}" target="_blank">Period Trade Contract<br>
                                expiry {!! ($company->activeCompanyDoc('5')) ? $company->activeCompanyDoc('5')->expiry->format('d/m/Y') : '' !!}</a><br><br>
                            <span class="font-red"><b>By signing & accepting this contract it will archive the old one.</b></span><br><br>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn dark btn-outline" data-dismiss="modal">Close</button>
                            <button type="submit" class="btn green" name="archive_doc" value="archive">Accept</button>
                        </div>
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
        </div>
    @endif
@stop

@section('page-level-plugins-head')
@stop

@section('page-level-plugins')
    <script src="/js/moment.min.js" type="text/javascript"></script>
@stop

@section('page-level-scripts')
    {{-- Metronic + custom Page Scripts --}}
    <script>
        $(document).ready(function () {
            $('#sign_contractor_accept').on('click', function () {
                var name = $('#signed_name').val();
                var user = "{!! Auth::user()->fullname !!}";
                var email = "{!! (Auth::user()->email) ?  ' ('.Auth::user()->email.')' : '' !!}";
                var date = moment().format('DD/MM/YYYY, h:mm:ss a');
                var signed_string = name + "\n" + 'Digitally signed by ' + user + email + "\nDate: " + date;
                $('#principle_signed_name').val(signed_string);
                if (name != '') {
                    $('#submit').show();
                    $('#sign_archive').show();
                } else {
                    $('#principle_signed_name').val('');
                    $('#submit').hide();
                }
            });

            $('#sign_archive').on('click', function () {
                $('#principle_signed_name2').val($('#principle_signed_name').val())
            });
        });

    </script>
@stop