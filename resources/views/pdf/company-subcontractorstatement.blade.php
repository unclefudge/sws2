<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Subcontractors Statement</title>
    <link href="{{ asset('/') }}/assets/global/plugins/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css"/>
    <link href="{{ asset('/') }}/assets/global/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css"/>
    <style>
        @import url(http://fonts.googleapis.com/css?family=PT+Sans);

        @page {
            margin: .7cm .7cm
        }

        body, h1, h2, h3, h4, h5, h6 {
            font-family: 'PT Sans', serif;
        }

        h1 {
            font-weight: 700;
        }

        body {
            font-size: 14px;
            line-height: 14px;
        }

        div.page {
            page-break-after: always;
            page-break-inside: avoid;
        }

        .table-striped>tbody>tr:nth-of-type(odd) {
            background-color: #ffffff;
        }

        .table-striped>tbody>tr:nth-of-type(even) {
            background-color: #fbfbfb;
        }

        .border-right {
            border-right: 1px solid lightgrey;
            margin-bottom: -999px;
            padding-bottom: 999px;
        }

        td.pad5, th.pad5 {
            padding: 5px !important;
            line-height: 1em !important;
        }

        td.pad0, th.pad0 {
            padding: 0px !important;
            line-height: 1em !important;
            border: 0px;
        !important;
        }
    </style>
</head>

<body>
<div class="container">
    <div class="row">
        <div class="col-xs-1">
            <img src="{{ public_path('/img/nsw_coatofarms.jpg') }}" width="100px">
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
            <p style="font-size:14px; font-weight:700">SUBCONTRACTOR’S STATEMENT (Refer to the back of this form for Notes, period of Statement retention, and Offences under various Acts.</p>
        </div>
    </div>

    <div class="row">
        <div class="col-xs-12">
            {{-- Contact Details --}}
            <table class="table" style="padding: 0px; margin: 0px;">
                <tr>
                    <td width="100px" class="pad0" style="border: 0px">Subcontractor:</td>
                    <td class="pad0" style="border-bottom: 1px dotted #555555; border-top: 0px"> {{ $ss->contractor_name }}</td>
                    <td width="20px" class="pad0" style="border: 0px">&nbsp;</td>
                    <td width="40px" class="pad0" style="border: 0px">ABN:</td>
                    <td width="100px" class="pad0" style="border-bottom: 1px dotted #555555; border-top: 0px"> {{ $ss->contractor_abn }}</td>
                </tr>
            </table>
            <div style="color:#bfbfbf; padding-left: 250px">(Business name)</div>

            <div style="width: 100%; display: table;">
                <span style="display: table-cell; width: 20px;">of: </span>
                <span style="display: table-cell; border-bottom: 1px dotted #555555;">{!! preg_replace('/<br>/', ', ', $ss->contractor_address) !!} </span>
            </div>
            <div style="color:#bfbfbf; padding-left: 300px">(Address of subcontractor)</div>

            {{-- Note 2 --}}
            <table class="table" style="padding: 0px; margin: 0px;">
                <tr>
                    <td width="210px" class="pad0" style="border: 0px">has entered into a contract with:</td>
                    <td class="pad0" style="border-bottom: 1px dotted #555555; border-top: 0px"> {{ $ss->principle_name }}</td>
                    <td width="20px" class="pad0" style="border: 0px">&nbsp;</td>
                    <td width="40px" class="pad0" style="border: 0px">ABN:</td>
                    <td width="100px" class="pad0" style="border-bottom: 1px dotted #555555; border-top: 0px"> {{ $ss->principle_abn }}</td>
                </tr>
                <tr>
                    <td width="210px" class="pad0" style="border: 0px">&nbsp;</td>
                    <td class="pad0" style="border: 0px; color:#bfbfbf; text-align: center"> (Business name of principal contractor)</td>
                    <td width="20px" class="pad0" style="border: 0px">&nbsp;</td>
                    <td width="40px" class="pad0" style="border: 0px">&nbsp;</td>
                    <td width="100px" style="border: 0px; padding: 2px 0px"><span class="pull-right"><b>(Note 2)</b></span></td>
                </tr>
            </table>

            {{-- Note 3 --}}
            <div style="width: 100%; display: table;">
                <span style="display: table-cell; width: 170px;">Contract number/identifier </span>
                <span style="display: table-cell; border-bottom: 1px dotted #555555;">{{ $ss->contract_no }}</span>
            </div>
            <div><span class="pull-right" style="padding-top: 3px"><b>(Note 3)</b></span></div>
            <br>

            {{-- Note 4 --}}
            <div style="width: 100%; display: table;">
                <span style="display: table-cell; width: 260px;">This Statement applies for work between: </span>
                <span style="display: table-cell;">{{ $ss->from->format('d / m / Y') }} &nbsp; and &nbsp; {{ $ss->to->format('d / m / Y') }} &nbsp; inclusive,</span>

                <span class="pull-right"><b>(Note 4)</b></span>
            </div>
            <br>

            {{-- Note 5 --}}
            <div style="width: 100%; display: table;">
                <span style="display: table-cell; width: 230px;">subject of the payment claim dated: </span>
                @if ($ss->claim_payment)
                    <span style="display: table-cell; width: 150px">  {!! $ss->claim_payment->format('d / m / Y') !!} </span>
                    <span style="display: table-cell;"></span>
                @else
                    <span style="display: table-cell; width: 30px; border-bottom: 1px dotted #555555;"></span>
                    <span style="display: table-cell; width: 30px"> &nbsp; &nbsp;/ </span>
                    <span style="display: table-cell; width: 30px; border-bottom: 1px dotted #555555;"></span>
                    <span style="display: table-cell; width: 30px"> &nbsp; &nbsp;/ </span>
                    <span style="display: table-cell; width: 30px; border-bottom: 1px dotted #555555;"></span>
                    <span style="display: table-cell; width: 500px;"></span>
                @endif
                <span style="display: table-cell;"><span class="pull-right"><b>(Note 5)</b></span></span>
            </div>
            <br>

            {{-- Declaration --}}
            <table class="table" style="padding: 0px; margin: 0px;">
                <tr>
                    <td width="10px" class="pad0" style="border: 0px">I</td>
                    <td class="pad0" style="border-bottom: 1px dotted #555555; border-top: 0px"> {{ $ss->contractor_full_name }}</td>
                    <td width="390px" class="pad0" style="border: 0px"> &nbsp; <span class="pull-right">a Director or a person authorised by the Subcontractor on whose</span></td>
                </tr>
            </table>
            <div class="text-justify">behalf this declaration is made, hereby declare that I am in a position to know the truth of the matters which are contained in this Subcontractor’s
                Statement and declare the following to the best of my knowledge and belief:
            </div>
            <br>

            {{-- Dot points --}}
            <ol type="a" style="padding-left:15px">
                {{-- Note 6 --}}
                <li class="text-justify">The abovementioned Subcontractor has either employed or engaged workers or subcontractors during the above period of this contract. Tick <b>[ {!! ($ss->clause_a == 1) ? 'X' : "&nbsp;" !!} ]</b>
                    if true and comply with <b>(b)</b> to <b>(g)</b>
                    below, as applicable. If it is not the case that workers or subcontractors are involved or you are an exempt employer for workers compensation purposes tick <b>[ {!! ($ss->clause_a == 2) ? 'X' : "&nbsp;" !!} ]</b>
                    and only
                    complete <b>(f)</b> and <b>(g)</b> below. You must tick one box. <span class="pull-right"><b>(Note 6)</b></span>
                </li>
                {{-- Note 7 --}}
                <li class="text-justify">All workers compensation insurance premiums payable by the Subcontractor in respect of the work done under the contract have been paid. The Certificate of
                    Currency for that insurance is attached and is dated {!! ($ss->wc_date ) ? '<b>'.$ss->wc_date->format('d/m/Y').'</b>' :  '......../......../........' !!}
                    <span class="pull-right"><b>(Note 7)</b></span>
                </li>
                {{-- Note 8 --}}
                <li class="text-justify">All remuneration payable to relevant employees for work under the contract for the above period has been
                    paid. &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; <span class="pull-right"><b>(Note 8)</b></span>
                </li>
                {{-- Note 9 --}}
                <li class="text-justify">Where the Subcontractor is required to be registered as an employer under the Payroll Tax Act 2007, the Subcontractor has paid all payroll tax due in
                    respect of employees who
                    performed work under the contract, as required at the date of this Subcontractor’s Statement. <span class="pull-right"><b>(Note 9)</b></span>
                </li>
                {{-- Note 10 --}}
                <li class="text-justify">Where the Subcontractor is also a principal contractor in connection with the work, the Subcontractor has in its capacity of principal contractor been
                    given a written
                    Subcontractor’s Statement by its subcontractor(s) in connection with that work for the period stated above. <span class="pull-right"><b>(Note 10)</b></span>
                </li>
            </ol>

            {{-- Signature --}}
            <div style="width: 100%; display: table;">
                <span style="display: table-cell; width: 120px;">f. &nbsp; Signature </span>
                <span style="display: table-cell; width: 480px; border-bottom: 1px solid #eee; border-top: 0px; background-color: #eee; padding:10px;">{!! nl2br($ss->contractor_signed_name) !!}</span>
                <span style="display: table-cell;"> &nbsp;</span>
            </div>
            <br>

            {{-- Position --}}
            <div style="width: 100%; display: table;">
                <span style="display: table-cell; width: 110px;">g. &nbsp;Position/Title </span>
                <span style="display: table-cell; border-bottom: 1px dotted #555555;">{{ $ss->contractor_signed_title }}</span>
                <span class="text-right" style="display: table-cell; width: 100px;"> &nbsp; Date: &nbsp;</span>
                <span style="display: table-cell; width: 100px; border-bottom: 1px dotted #555555;">{{ $ss->contractor_signed_at->format('d/m/Y') }}</span>
            </div>
            <br>

            {{-- Footer Note --}}
            <div class="text-justify"><b>NOTE:</b> Where required above, this Statement must be accompanied by the relevant Certificate of Currency to comply with section 175B of the Workers
                Compensation Act 1987.
            </div>

        </div>
    </div>

    <div class="page"></div>

    {{-- Page 2 --}}
    <div class="row">
        <div class="col-xs-12" style="font-size:11px">
            <h5 class="text-center" style="margin-top: 0px; padding-top: 0px"><b>Notes</b></h5>
            {{-- Dot points --}}
            <ol style="padding-left:20px">
                <li>
                    <p>This form is prepared for the purpose of section 175B of the <i>Workers Compensation Act 1987, Schedule 2 Part 5 Payroll Tax Act 2007</i> and section 127 of the <i>Industrial
                            Relation
                            Act 1996</i>. If this form is completed in accordance with these provisions, a principal contractor is relieved of liability for workers compensation premiums, payroll
                        tax and
                        remuneration payable by the subcontractor.</p>
                    <p>
                        A principal contractor can be generally defined to include any person who has entered into a contract for the carrying out of work by another person (or other legal entity
                        called <b>the subcontractor</b>) and where employees of the subcontractor are engaged in carrying out the work which is in connection with the principal contractor’s
                        business.</p>
                </li>
                <li>For the purpose of this Subcontractor’s Statement, a principal contractor is a person (or other legal entity), who has entered into a contract with another
                    person (or other legal entity) referred to as the subcontractor, and employees/workers of that subcontractor will perform the work under contract. The work must be connected to
                    the business undertaking of the principal contractor.
                </li>
                <li>Provide the unique contract number, title, or other information that identifies the contract.</li>
                <li>
                    <p>In order to meet the requirements of s127 <i>Industrial Relations Act 1996</i>, a statement in relation to remuneration must state the period to which the
                        statement relates. For sequential Statements ensure that the dates provide continuous coverage.</p>
                    <p>Section 127(6) of the <i>Industrial Relations Act 1996 defines remuneration ‘as remuneration or other amounts payable to relevant employees by legislation, or under an
                            industrial
                            instrument, in connection with work done by the employees.’</i></p>
                    <p>Section 127(11) <i>of the Industrial Relations Act 1996 states ‘to avoid doubt, this section extends to a principal contractor who is the owner or occupier of a building for
                            the
                            carrying out of work in connection with the building so long as the building is owned or occupied by the principal contractor in connection with a business undertaking
                            of the
                            principal contractor.’</i></p>
                </li>
                <li>Provide the date of the most recent payment claim.</li>
                <li>For Workers Compensation purposes an exempt employer is an employer who pays less than $7500 annually, who does not employ an apprentice or trainee and is
                    not a member of a group.
                </li>
                <li>In completing the Subcontractor’s Statement, a subcontractor declares that workers compensation insurance premiums payable up to and including the date(s)
                    on the Statement have been paid, and all premiums owing during the term of the contract will be paid.
                </li>
                <li>In completing the Subcontractor’s Statement, a subcontractor declares that all remuneration payable to relevant employees for work under the contract has
                    been paid.
                </li>
                <li>In completing the Subcontractor’s Statement, a subcontractor declares that all payroll tax payable relating to the work undertaken has been paid.</li>
                <li>It is important to note that a business could be both a subcontractor and a principal contractor, if a business ‘in turn’ engages subcontractors to carry
                    out the work. If your business engages a subcontractor you are to also obtain Subcontractor’s Statements from your subcontractors.
                </li>
            </ol>

            <h5 class="text-center"><b>Statement Retention</b></h5>
            <p>The principal contractor receiving a Subcontractor’s Statement must keep a copy of the Statement for the periods stated in the respective legislation. This is currently up to seven
                years.</p>

            <div style="border: 1px solid; background-color:#EEEEEE; padding:10px">
                <h5 class="text-center" style="margin: 0px;"><b>Offences in respect of a false Statement</b></h5>
                In terms of s127(8) of the Industrial Relations Act 1996, a person who gives the principal contractor a written statement knowing it to be false is guilty of an offence if:
                <ol type="a">
                    <li>the person is the subcontractor;</li>
                    <li>the person is authorised by the subcontractor to give the statement on behalf of the subcontractor; or</li>
                    <li>the person holds out or represents that the person is authorised by the subcontractor to give the statement on
                        behalf of the subcontractor.
                    </li>
                </ol>
                In terms of s175B of the <i>Workers Compensation Act</i> and clause 18 of Schedule 2 of the <i>Payroll Tax Act 2007</i> a person who gives the principal contractor a written
                statement knowing it
                to be false is guilty of an offence.
            </div>

            <h5 class="text-center"><b>Further Information</b></h5>
            <p>For more information, visit the WorkCover website <a>www.workcover.nsw.gov.au</a>, Office of State Revenue website <a>www.osr.nsw.gov.au</a> , or Office of Industrial Relations, Department of
                Commerce website <a>www.commerce.nsw.gov.au</a> . Copies of the Workers Compensation Act 1987, the Payroll Tax Act 2007 and the Industrial Relations Act 1996 can be found at
                www.legislation.nsw.gov.au.</p>
        </div>
    </div>
</div>
</body>
</html>