@inject('ozstates', 'App\Http\Utilities\OzStates')
@inject('companyEntity', 'App\Http\Utilities\CompanyEntityTypes')

@extends('layout-guest')

@section('content')
    <div class="page-content-inner">
        {{-- Company Signup Progress --}}
        <div class="mt-element-step">
            <div class="row step-line" id="steps">
                <div class="col-md-3 mt-step-col first active">
                    <a href="/signup/user/{{ Auth::user()->company->primary_user }}">
                        <div class="mt-step-number bg-white font-grey">1</div>
                    </a>
                    <div class="mt-step-title uppercase font-grey-cascade">Business Owner</div>
                    <div class="mt-step-content font-grey-cascade">Add primary user</div>
                </div>
                <div class="col-md-3 mt-step-col active">
                    <<a href="/signup/company/{{ Auth::user()->company_id }}">
                        <div class="mt-step-number bg-white font-grey">2</div>
                    </a>
                    <div class="mt-step-title uppercase font-grey-cascade">Company Info</div>
                    <div class="mt-step-content font-grey-cascade">Add company info</div>
                </div>
                <div class="col-md-3 mt-step-col active">
                    <a href="/signup/workers/{{ $company->id }}">
                        <div class="mt-step-number bg-white font-grey">3</div>
                    </a>
                    <div class="mt-step-title uppercase font-grey-cascade">Workers</div>
                    <div class="mt-step-content font-grey-cascade">Add workers</div>
                </div>
                <div class="col-md-3 mt-step-col last">
                    <div class="mt-step-number bg-white font-grey">4</div>
                    <div class="mt-step-title uppercase font-grey-cascade">Documents</div>
                    <div class="mt-step-content font-grey-cascade">Upload documents</div>
                </div>
            </div>
        </div>

        <div class="note note-warning">
            <h3>You have completed the first 3 steps and it's time to confirm your details.</h3>
            <b>Please review the summary to ensure it is correct. If you need to modify any information please use the edit button.</b><br><br>
            Once you are satisified please click
            <button class="btn dark btn-outline btn-xs" href="javascript:;"> Continue</button>
            and your company will be made active and you'll be able to login into SafeWorksite job sites.
            <br><br>Please be aware your company will be marked <span class="font-red">NON COMPLIANT</span> until you have completed step 4 and uploaded required documents.
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="portlet light bordered">
                    <div class="portlet-title">
                        <div class="caption">
                            <span class="caption-subject font-green-haze bold uppercase">Summary</span>
                            <span class="caption-helper"></span>
                        </div>
                    </div>
                    <div class="portlet-body form">
                        <div class="form-body">
                            {{-- Primary User --}}
                            <h3 class="font-green form-section">1. Business Owner (Primary User) <a href="/signup/user/{{ $company->primary_user }}" class="btn btn-xs default pull-right">Edit</a></h3>
                            <div class="row" style="line-height: 2">
                                <div class="col-md-6">
                                    <div class="col-xs-3" style="padding-left: 0px"><b>Name</b></div>
                                    <div class="col-xs-9">{{ $company->primary_contact()->name }}</div>
                                    <div class="col-xs-3" style="padding-left: 0px"><b>Username</b></div>
                                    <div class="col-xs-9">{{ $company->primary_contact()->username }}</div>
                                    <div class="col-sm-3" style="padding-left: 0px"><b>Employment type</b></div>
                                    <div class="col-sm-9">{{ $company->primary_contact()->employment_type_text }}</div>

                                </div>
                                <div class="col-md-6">
                                    <div class="col-xs-3" style="padding-left: 0px"><b>Phone</b></div>
                                    <div class="col-xs-9">@if ($company->primary_contact()->phone)
                                            <a
                                                    href="tel:{{ preg_replace("/[^0-9]/", "", $company->primary_contact()->phone) }}"> {{ $company->primary_contact()->phone }} </a>
                                        @else
                                            -
                                        @endif</div>
                                    <div class="col-xs-3" style="padding-left: 0px"><b>Email</b></div>
                                    <div class="col-xs-9">@if ($company->primary_contact()->email)
                                            <a
                                                    href="mailto:{{ $company->primary_contact()->email }}"> {{ $company->primary_contact()->email }} </a>
                                        @else
                                            -
                                        @endif</div>
                                    <div class="col-xs-3" style="padding-left: 0px"><b>Address</b></div>
                                    <div class="col-xs-9">
                                        @if($company->primary_contact()->address)
                                            {{ $company->primary_contact()->address }}&nbsp;
                                        @else
                                            -
                                        @endif
                                        {{ $company->primary_contact()->SuburbStatePostcode }}
                                    </div>
                                </div>
                            </div>

                            {{-- Company Info --}}
                            <h3 class="font-green form-section">2. Company Info <a href="/signup/company/{{ $company->id }}" class="btn btn-xs default pull-right">Edit</a></h3>
                            <div class="row" style="line-height: 2">
                                <div class="col-md-6">
                                    <div class="col-xs-3" style="padding-left: 0px"><b>Name</b></div>
                                    <div class="col-xs-9">{{ $company->name }}</div>
                                    <div class="col-sm-3" style="padding-left: 0px"><b>Business Entity</b></div>
                                    <div class="col-sm-9">{{ $company->business_entity }}</div>
                                    <div class="col-xs-3" style="padding-left: 0px"><b>ABN</b></div>
                                    <div class="col-xs-9">{{ $company->abn }}</div>
                                    <div class="col-xs-3" style="padding-left: 0px"><b>GST</b></div>
                                    <div class="col-xs-9">@if($company->gst)
                                            Yes
                                        @elseif($company->gst == '0')
                                            No
                                        @else
                                            -
                                        @endif</div>
                                </div>
                                <div class="col-md-6">
                                    <div class="col-xs-3" style="padding-left: 0px"><b>Phone</b></div>
                                    <div class="col-xs-9">@if ($company->phone)
                                            <a
                                                    href="tel:{{ preg_replace("/[^0-9]/", "", $company->phone) }}"> {{ $company->phone }} </a>
                                        @else
                                            -
                                        @endif</div>
                                    <div class="col-xs-3" style="padding-left: 0px"><b>Email</b></div>
                                    <div class="col-xs-9">@if ($company->email)
                                            <a
                                                    href="mailto:{{ $company->email }}"> {{ $company->email }} </a>
                                        @else
                                            -
                                        @endif</div>
                                    <div class="col-xs-3" style="padding-left: 0px"><b>Address</b></div>
                                    <div class="col-xs-9">
                                        @if($company->address)
                                            {{ $company->address }}&nbsp;
                                        @else
                                            -
                                        @endif
                                        {{ $company->SuburbStatePostcode }}
                                    </div>
                                    <div class="col-sm-3" style="padding-left: 0px"><b>Primary Contact</b></div>
                                    <div class="col-sm-9">{{ $company->primary_contact()->fullname }}</div>
                                    @if ($company->secondary_user)
                                        <div class="col-sm-3" style="padding-left: 0px"><b>Secondary Contact</b></div>
                                        <div class="col-sm-9">{{ $company->secondary_contact()->fullname }}</div>
                                    @endif
                                </div>
                            </div>

                            {{-- Workers --}}
                            <h3 class="font-green form-section">3. Workers <a href="/signup/workers/{{ $company->id }}" class="btn btn-xs default pull-right">Edit</a></h3>
                            <div class="row">
                                <div class="col-md-12">
                                    <table class="table table-striped table-bordered table-hover order-column" id="table_staff">
                                        <thead>
                                        <tr class="mytable-header">
                                            <th> Name</th>
                                            <th> Phone</th>
                                            <th> Email</th>
                                        </tr>
                                        </thead>
                                    </table>
                                </div>
                            </div>

                            <div class="form-actions right">
                                <a href="/signup/documents/{{ $company->id }}" class="btn green">Continue</a>
                            </div>
                        </div>
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop {{-- END Content --}}

@section('page-level-plugins-head')
    <link href="/assets/global/plugins/select2/css/select2.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/select2/css/select2-bootstrap.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/datatables/datatables.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.css" rel="stylesheet" tytype="text/css"/>
@stop

@section('page-level-plugins')
    <script src="/assets/global/plugins/select2/js/select2.full.min.js" type="text/javascript"></script>
    <script src="/assets/global/scripts/datatable.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/datatables/datatables.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.js" type="text/javascript"></script>
@stop

@section('page-level-scripts')
    {{-- Metronic + custom Page Scripts --}}
    <script type="text/javascript">
        $(document).ready(function () {

            var table_staff = $('#table_staff').DataTable({
                processing: true,
                serverSide: true,
                bFilter: false,
                bLengthChange: false,
                ajax: {
                    'url': '/company/dt/users',
                    'type': 'GET',
                    'data': function (d) {
                        d.company_id = {{ $company->id }};
                        d.staff = 'staff';
                        d.status = 1;
                    }
                },
                columns: [
                    {data: 'full_name', name: 'full_name'},
                    {data: 'phone', name: 'phone', orderable: false},
                    {data: 'email', name: 'email', orderable: false},
                ],
                order: [
                    [1, "asc"]
                ]
            });

        });


    </script>
@stop