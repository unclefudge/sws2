@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        @if (Auth::user()->company->subscription > 1 && Auth::user()->hasAnyPermissionType('company'))
            <li><a href="/company">Companies</a><i class="fa fa-circle"></i></li>
        @endif
        <li><a href="/company/{{ $company->id }}/doc">Documents</a><i class="fa fa-circle"></i></li>
        <li><span>Privacy Policy</span></li>
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
                            <span class="caption-subject font-dark bold uppercase"> Privacy Policy </span>
                        </div>
                        @if ($policy->status == 1)
                            <div class="actions">
                                <a class="btn btn-circle green btn-outline btn-sm" href="{{ $policy->attachment_url }}" data-original-title="Upload">View PDF</a>
                            </div>
                        @endif
                    </div>
                    <div class="portlet-body form">

                        <div class="form-body">

                            {{-- 1. Purpose --}}
                            <table class="table" style="padding: 0px; margin: 0px">
                                <tr>
                                    <td style="width:5%; margin:5px 0 0 0; padding: 5px 0px; border: 0px"><h5 style="margin: 0px">1.</h5></td>
                                    <td class="pad5" style="border: 0px">
                                        <h4 style="margin: 0px">Purpose</h4>
                                        <p>The protection of personal information in the private sector is required by the Privacy Act 1988 as amended by the Privacy Amendment (Private Sector) Act 2000 (“Act”). All employees, officers and agents of Cape Cod Australia Pty Ltd (“Cape Cod”) are
                                            expected
                                            to comply with the Act and Cape Cod ’s policy concerning the protection of personal information.</p>
                                        Cape Cod is committed to protecting the privacy of individuals. We will only use your personal information for the purpose for which it was provided, in accordance with this policy or in accordance with the Act.
                                    </td>
                                </tr>
                            </table>

                            {{-- 2. Scope --}}
                            <table class="table" style="padding: 0px; margin: 0px">
                                <tr>
                                    <td style="width:5%; margin:5px 0 0 0; padding: 5px 0px; border: 0px"><h5 style="margin: 0px">2.</h5></td>
                                    <td class="pad5" style="border: 0px">
                                        <h4 style="margin: 0px">Scope</h4>
                                        This Privacy Policy is governed by the Australian Privacy Principles under the Privacy Act 1988. This Policy applies to the collection, storage, maintenance and disposal of personal information gathered from participants as part of the delivery of services to
                                        Cape Cod customers.
                                    </td>
                                </tr>
                            </table>

                            {{-- 3. Policy --}}
                            <table class="table" style="padding: 0px; margin: 0px">
                                <tr>
                                    <td style="width:5%; margin:5px 0px 0px 0px; padding: 5px 0px; border: 0px"><h5 style="margin: 0px">3.</h5></td>
                                    <td class="pad0" style="border: 0px;">
                                        <h4 style="margin: 0px">Policy</h4>
                                    </td>
                                </tr>
                            </table>

                            {{-- 3.1 Collection of data --}}
                            <table class="table" style="padding: 0px; margin: 0px">
                                <tr>
                                    <td style="width:5%; margin:5px 0 0 0; padding: 5px 0px; border: 0px"><h5 style="margin: 0px">3.1.</h5></td>
                                    <td class="pad0" style="border: 0px">
                                        <h4 style="margin: 0px">Collection of Personal Information</h4>
                                        We collect personal information when persons:<br>
                                        <ol type="a" style="padding-left:25px; margin-bottom: 5px">
                                            <li>interact with Cape Cod through the phone, in person, via email or through our website where personal details are provided;</li>
                                            <li>apply for positions at or contract services to Cape Cod.</li>
                                        </ol>
                                        We collect and store personal information to:<br>
                                        <ol type="a" style="padding-left:25px; margin-bottom: 5px">
                                            <li>carry on a business of providing services that you have requested;</li>
                                            <li>keep a record of business transactions for necessary purposes such as orders, accounts, audits, taxation and Government agencies;</li>
                                            <li>analyze our business transactions in order to improve our products and services;</li>
                                            <li>communicate with existing customers to offer products or promotions which may meet the needs of the customer;</li>
                                            <li>comply with the law or to use information as permitted under the law.</li>
                                        </ol>
                                        We collect and hold following types of personal information:<br>
                                        <ol type="a" style="padding-left:25px; margin-bottom: 5px">
                                            <li>contact details that may include names, business names, addresses, email addresses, fax numbers, phone numbers, signatures and social media profile names;</li>
                                            <li>optional personal information that has been provided by customers, including, but not limited to occupation, gender, birth date or age; and</li>
                                            <li>optional surveys that provide personal information.</li>
                                        </ol>
                                    </td>
                                </tr>
                            </table>

                            {{--  3.2 Security --}}
                            <table class="table" style="padding: 0px; margin: 0px">
                                <tr>
                                    <td style="width:5%; margin:5px 0 0 0; padding: 5px 0px; border: 0px"><h5 style="margin: 0px">3.2</h5></td>
                                    <td class="pad5" style="border: 0px">
                                        <h4 style="margin: 0px">Security</h4>
                                        <p>Information that we collect may be stored, processed in or transferred between parties located within and outside of Australia.</p>
                                        <p>Although we don’t send personal information overseas you should be aware your personal information may be loaded to the cloud for storage or access and it is possible that suppliers we deal with may outsource functions using overseas contractors or
                                            companies that process these services using offshore resources.</p>
                                        <p>Cape Cod is committed to ensuring that the information you provide to us is kept securely. In order to prevent unauthorised access or disclosure, we have put in place suitable physical, electronic and managerial procedures to safeguard and secure
                                            information and protect it from misuse, interference and loss.</p>
                                        The transmission and exchange of information is carried out at your own risk. We cannot guarantee the security of any information that you transmit to or receive from us.
                                    </td>
                                </tr>
                            </table>

                            {{-- 3.3 Disclosure --}}
                            <table class="table" style="padding: 0px; margin: 0px">
                                <tr>
                                    <td style="width:5%; margin:5px 0 0 0; padding: 5px 0px; border: 0px"><h5 style="margin: 0px">3.3</h5></td>
                                    <td class="pad5" style="border: 0px">
                                        <h4 style="margin: 0px">Disclosure of Personal Information</h4>
                                        <p>Except as required by law, Cape Cod discloses personal information only for purposes that are reasonably related to Cape Cod’s Business and Association activities, and for which we have your actual consent or where you would reasonably expect Cape Cod to do
                                            so.</p>
                                        <p>Cape Cod will not disclose your personal information to third parties for payment, profit or advantage without your consent.</p>
                                        Cape Cod may disclose personal information to third parties, from time to time, to assist in conducting business, including:
                                        <ol type="a" style="padding-left:25px; margin-bottom: 5px">
                                            <li>contractors that perform functions on our behalf such as Design Consultants and Trades that are engaged to provide the service you have requested;</li>
                                            <li>technology service providers including internet service providers or cloud service providers;</li>
                                            <li>couriers such as Australia Post;</li>
                                            <li>data processors that analyse our website traffic or usage;</li>
                                            <li>agents that perform functions on our behalf, such as mailouts, debt collection, marketing or advertising;</li>
                                            <li>where a person consents to the disclosure in writing or where that person would reasonably expect Cape Cod to do so; and</li>
                                            <li>to persons, entities or courts as required under the law.</li>
                                        </ol>
                                    </td>
                                </tr>
                            </table>

                            {{-- 3.4 Direct Marketing --}}
                            <table class="table" style="padding: 0px; margin: 0px">
                                <tr>
                                    <td style="width:5%; margin:5px 0 0 0; padding: 5px 0px; border: 0px"><h5 style="margin: 0px">3.4</h5></td>
                                    <td class="pad5" style="border: 0px">
                                        <h4 style="margin: 0px">Direct Marketing to You</h4>
                                        <p>We will not send you unsolicited commercial electronic messages in contravention of the Spam Act 2003.</p>
                                        We may use the non-sensitive information you gave us for the purpose of promoting and marketing our Business to you if we:
                                        <ol type="a" style="padding-left:25px; margin-bottom: 5px">
                                            <li>use the information that you reasonably expected us to use for promoting and marketing our Business to you; and</li>
                                            <li>provide you a simple method to unsubscribe.</li>
                                        </ol>
                                        We will not contact you to promote or market our Business if you requested us not to.
                                    </td>
                                </tr>
                            </table>

                            {{-- 3.5 Accessing and Correcting --}}
                            <table class="table" style="padding: 0px; margin: 0px">
                                <tr>
                                    <td style="width:5%; margin:5px 0 0 0; padding: 5px 0px; border: 0px"><h5 style="margin: 0px">3.5</h5></td>
                                    <td class="pad5" style="border: 0px">
                                        <h4 style="margin: 0px">Accessing and Correcting Your Personal Information</h4>
                                        You may request access to your personal information that we hold and we will:
                                        <ol type="a" style="padding-left:25px; margin-bottom: 5px">
                                            <li>verify your identity;</li>
                                            <li>inform you of, and if you agree, charge you the reasonable cost of meeting your request, if any, but not for the request itself; and</li>
                                            <li>on receipt of payment, if any, and within a reasonable period, comply with your request.</li>
                                        </ol>
                                        You may request to correct your personal information that we hold and we will update your personal information so that it is up-to-date, accurate, complete, relevant and not misleading.
                                    </td>
                                </tr>
                            </table>

                            {{-- 3.6 How to Contact Us --}}
                            <table class="table" style="padding: 0px; margin: 0px">
                                <tr>
                                    <td style="width:5%; margin:5px 0 0 0; padding: 5px 0px; border: 0px"><h5 style="margin: 0px">3.6</h5></td>
                                    <td class="pad5" style="border: 0px">
                                        <h4 style="margin: 0px">How to Contact Us</h4>
                                        If you would like to access or correct your personal information, please contact our Privacy Officer by:
                                        <ol type="a" style="padding-left:25px; margin-bottom: 5px">
                                            <li>email: inform@capecod.com.au</li>
                                            <li>writing to: Privacy Officer, Cape Cod Australia, PO Box 2002, North Parramatta NSW 1750; or</li>
                                            <li>phone: 02 9849 4444.</li>
                                        </ol>
                                    </td>
                                </tr>
                            </table>

                            {{-- 3.7 Complaints --}}
                            <table class="table" style="padding: 0px; margin: 0px">
                                <tr>
                                    <td style="width:5%; margin:5px 0 0 0; padding: 5px 0px; border: 0px"><h5 style="margin: 0px">3.7</h5></td>
                                    <td class="pad5" style="border: 0px">
                                        <h4 style="margin: 0px">Complaints</h4>
                                        If you believe we have breached the Australian Privacy Principles under the Privacy Act 1988 or a registered Australian Privacy Principles Code, you may lodge a complaint as follows:
                                        <ol type="a" style="padding-left:25px; margin-bottom: 5px">
                                            <li>firstly, contact us in writing to the email or postal address in clause 3.6 and include the following in your complaint:
                                                <ul style="padding-left:25px; margin-bottom: 5px">
                                                    <li>your contact details;</li>
                                                    <li>section or provision of the Australian Privacy Principles or Code that you believe we breached; and</li>
                                                    <li>• our practice or policy that you believe breaches the relevant Australian Privacy Principle or Code,</li>
                                                </ul>
                                            </li>
                                            you must allow us a reasonable time, about 30 days, to reply to your complaint; and
                                            <li>secondly, you may complain to the Office of the Australian Information Commissioner if:</li>
                                            <ul style="padding-left:25px; margin-bottom: 5px">
                                                <li>you are not satisfied with our response; or</li>
                                                <li>we do not respond to you within a reasonable time without sufficient explanation.</li>
                                            </ul>
                                        </ol>
                                    </td>
                                </tr>
                            </table>

                            {{-- 4 Notifiable Data Breaches --}}
                            <table class="table" style="padding: 0px; margin: 0px">
                                <tr>
                                    <td style="width:5%; margin:5px 0 0 0; padding: 5px 0px; border: 0px"><h5 style="margin: 0px">4.</h5></td>
                                    <td class="pad5" style="border: 0px">
                                        <h4 style="margin: 0px">Notifiable Data Breaches</h4>
                                        Cape Cod will notify the Office of the Australian Information Commissioner (OAIC) and affected individuals if Cape Cod has a data breach within the meaning of the Act.
                                    </td>
                                </tr>
                            </table>

                            {{-- Signature --}}
                            <br><br><br>
                            <div class="row">
                                <div class="form-group {{ $errors->has('contractor_signed_name') ? 'has-error' : '' }}">
                                    <label for="contractor_signed_name" class="col-md-3 control-label">Trade Contractor's Signature</label>
                                    <div class="col-md-6">
                                        <x-form.textarea name="contractor_signed_name" :value="$policy->contractor_signed_name" rows="3" disabled/>
                                        <x-form.error name="contractor_signed_name"/>
                                    </div>
                                </div>
                            </div>
                            <br>


                            <br><br>
                            <div class="form-actions right">
                                <a href="/company/{{ $company->id }}/doc" class="btn default"> Back</a>
                                @if (($company->activeCompanyDoc('12') && $company->activeCompanyDoc('12')->status == 1))
                                    <a href="#modal_archive" class="btn green" data-toggle="modal" id="sign_archive" style="display: none;">Submit</a>
                                @else
                                    <button type="submit" name="save" value="save" class="btn green" id="submit" style="display: none;">Submit</button>
                                @endif
                            </div>
                        </div>
                    </div>
                    </form>
                </div>
            </div>
        </div>
    </div>




    {{-- Archive Modal --}}
    {{--}}
    @if (($company->activeCompanyDoc('12')))
        <div id="modal_archive" class="modal fade bs-modal-sm" id="basic" tabindex="-1" role="modal_archive" aria-hidden="true">
            <div class="modal-dialog modal-sm">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                        <h4 class="modal-title">Replace Existing Contract</h4>
                    </div>
                    <div class="modal-body">
                        <form method="POST" action="{{ action([App\Http\Controllers\Company\CompanyPrivacyPolicyController::class, 'update'], ['cid' => $company->id, 'privacy_policy' => $policy->id]) }}" class="horizontal-form">
                        @csrf
                        @method('PATCH')
                        <x-form.hidden name="archive" :value="$company->activeCompanyDoc('12')->id"/>

                        <textarea name="principle_signed_name2" id="principle_signed_name2" rows="3" class="form-control" readonly="" style="display:none"></textarea>

                        <div class="text-center">
                            <b>{{ $company->name }}</b> currently has the following valid contract<br><br>
                            <a href="{!! $company->activeCompanyDoc('12')->attachment_url !!}" target="_blank">Period Trade Contract<br>
                                expiry {!! ($company->activeCompanyDoc('12')) ? $company->activeCompanyDoc('12')->expiry->format('d/m/Y') : '' !!}</a><br><br>
                            <span class="font-red"><b>By signing & accepting this contract it will archive the old one.</b></span><br><br>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn dark btn-outline" data-dismiss="modal">Close</button>
                            <button type="submit" class="btn green" name="archive_doc" value="archive">Accept</button>
                        </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif --}}
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