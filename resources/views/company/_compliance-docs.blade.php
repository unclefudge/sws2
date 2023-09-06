{{-- Compliance Docs --}}
<?php

$isCompliant = $company->isCompliant();
$compliantDocs = $company->compliantDocs();
?>

<div class="col-lg-6 col-xs-12 col-sm-12 pull-right">
    @if (Auth::user()->allowed2('view.company.acc', $company))
        <div class="portlet light">
            <div class="portlet-title">
                <div class="caption">
                    <span class="caption-subject font-dark bold uppercase">Compliance Documents</span>
                </div>
                <div class="actions">
                    @if(count($company->missingDocs()) && (Auth::user()->isCompany($company->id) && Auth::user()->allowed2('add.company.doc')) ||
                            (Auth::user()->isCompany($company->reportsTo()->id) && Auth::user()->allowed2('add.company.doc') && $company->parentUpload()))
                        <a href="/company/{{ $company->id }}/doc/upload" class="btn btn-circle green btn-outline btn-sm">Upload</a>
                    @endif
                </div>
            </div>
            <div class="portlet-body">
                @if (count($compliantDocs))
                    <div class="row">
                        <div class="col-md-12">
                            @if ($isCompliant)
                                <b>All compliance documents have been submited and approved:</b>
                            @else
                                <b>The following {!! count($compliantDocs) !!} documents are required to be compliant:</b>
                            @endif
                        </div>

                        @foreach ($compliantDocs as $type => $name)
                            {{-- Accepted --}}
                            @if ($company->activeCompanyDoc($type) && $company->activeCompanyDoc($type)->status == 1)
                                <div class="col-xs-8"><i class="fa fa-check" style="width:35px; padding: 4px 15px; {!! ($isCompliant) ? 'color: #26C281' : '' !!}"></i>
                                    <a href="{!! $company->activeCompanyDoc($type)->attachment_url !!}" class="linkDark" target="_blank">{{ $name }}</a>
                                </div>
                                <div class="col-xs-4">
                                    @if (!$isCompliant)
                                        <span class="label label-success label-sm">Accepted</span>
                                    @endif
                                </div>
                            @endif
                            {{-- Pending --}}
                            @if ($company->activeCompanyDoc($type) && $company->activeCompanyDoc($type)->status == 2)
                                <div class="col-xs-8"><i class="fa fa-question" style="width:35px; padding: 4px 15px"></i>
                                    <a href="{!! $company->activeCompanyDoc($type)->attachment_url !!}" class="linkDark" target="_blank">{{ $name }}</a>
                                </div>
                                <div class="col-xs-4">
                                    @if (!$isCompliant)
                                        <span class="label label-warning label-sm">Pending Approval</span>
                                    @endif
                                </div>
                            @endif
                            {{-- Rejected --}}
                            @if ($company->activeCompanyDoc($type) && $company->activeCompanyDoc($type)->status == 3)
                                <div class="col-xs-8"><i class="fa fa-question" style="width:35px; padding: 4px 15px"></i>
                                    <a href="{!! $company->activeCompanyDoc($type)->attachment_url !!}" class="linkDark" target="_blank">{{ $name }}</a>
                                    @if(Auth::user()->isCompany($company->id) && Auth::user()->allowed2('add.company.doc'))
                                        @if ($type == 4)
                                            <a href="/company/{{ $company->id  }}/doc/subcontractor-statement/create"><i class="fa fa-pencil-square-o" style="padding-left: 10px"></i> Create</a>
                                        @elseif ($type == 5)
                                            <a href="/company/{{ $company->id  }}/doc/period-trade-contract/create"><i class="fa fa-pencil-square-o" style="padding-left: 10px"></i> Create</a>
                                        @endif
                                    @endif
                                </div>
                                <div class="col-xs-4">
                                    @if (!$isCompliant)
                                        <span class="label label-danger label-sm">Rejected</span>
                                    @endif
                                </div>
                            @endif
                            {{-- Missing --}}
                            @if (!$company->activeCompanyDoc($type))
                                <div class="col-xs-8">
                                    <i class="fa fa-times" style="width:35px; padding: 4px 15px"></i> {{ $name }}
                                    @if(Auth::user()->isCompany($company->id) && Auth::user()->allowed2('add.company.doc'))
                                        @if ($type == 4)
                                            <a href="/company/{{ $company->id  }}/doc/subcontractor-statement/create"><i class="fa fa-pencil-square-o" style="padding-left: 10px"></i> Create</a>
                                        @elseif ($type == 5)
                                            <a href="/company/{{ $company->id  }}/doc/period-trade-contract/create"><i class="fa fa-pencil-square-o" style="padding-left: 10px"></i> Create</a>
                                        @elseif ($type == 12 && $company->parent_company == 3)
                                            <a href="/company/{{ $company->id }}/doc/privacy-policy/create"><i class="fa fa-pencil-square-o" style="padding-left: 10px"></i> Create</a>
                                        @else
                                            <a href="/company/{{ $company->id  }}/doc/create"><i class="fa fa-upload" style="padding-left: 10px"></i> Upload</a>
                                        @endif
                                    @endif
                                </div>
                                <div class="col-xs-4 font-red">{!! (!$isCompliant) ? 'Not submitted' : '' !!}</div>
                            @endif
                        @endforeach
                    </div>
                    {{-- Pre-filled forms --}}
                    @if (false && ($company->requiresCompanyDoc(4) || $company->requiresCompanyDoc(5)))
                        <div class="row">
                            <div class="col-md-12"><br>Pre-filled forms:
                                @if ($company->requiresCompanyDoc(4))
                                    <a href="/company/doc/create/subcontractorstatement/{{ $company->id  }}/{!! ($company->activeCompanyDoc(4) && $company->activeCompanyDoc(4)->status == 1) ? 'next' : 'current'!!}" target="_blank"><i class="fa fa-download" style="padding-left: 10px"></i>
                                        Subcontractors Statement</a>
                                @endif

                                @if ($company->requiresCompanyDoc(5))
                                    <a href="/company/doc/create/tradecontract/{{ $company->id  }}/next" target="_blank"><i class="fa fa-download" style="padding-left: 10px"></i> Period Trade Contract</a>
                                @endif
                            </div>
                        </div>
                    @endif
                @else
                    <div class="row">
                        <div class="col-md-12">No documents are required to be compliant.</div>
                    </div>
                @endif
                @if (in_array($company->category, [1,2]))
                    {{-- Verify Company CL has supervisor for each Class --}}
                        <?php
                        $non_cl = $company->nonCompliantContractorsLicence();
                        $non_super = $company->nonCompliantSupervisorsLicence();
                        ?>
                    <hr>
                    @if ($non_cl)
                        <b>The following users require Contractor Licence with class(s)</b><br>
                        @foreach ($non_cl as $name => $classes)
                            {{ $name }} : {{ $classes }}<br>
                        @endforeach
                    @endif
                    <br>
                    @if ($non_cl)
                        <b>The following users require Supervisor Licence with class(s)</b><br>
                        @foreach ($non_super as $name => $classes)
                            {{ $name }} : {{ $classes }}<br>
                        @endforeach
                    @endif
                @endif
                {{-- Privacy Policy --}}
                {{--}}
            <div class="row">
                @if ($company->activeCompanyDoc(12))
                    <div class="col-xs-8">
                        <i class="fa fa-check" style="width:35px; padding: 4px 15px; color: #26C281"></i> <a href="{!! $company->activeCompanyDoc(12)->attachment_url !!}" class="linkDark">Privacy Policy</a>
                    </div>
                @else
                    <div class="col-xs-8">
                        <i class="fa fa-times" style="width:35px; padding: 4px 15px;"></i>Privacy Policy</a>
                    </div>
                @endif
            </div> --}}


                {{-- Currently Disabled Test & Tag as additional as it was made required --}}
                @if (false && in_array($company->category, [1,2]))
                    <hr>
                    <b>Additional documents:</b>
                    {{--- Test & Tag --}}
                        <?php $tag_doc = $company->activeCompanyDoc(6) ?>
                    <div class="row">
                        @if ($tag_doc && $tag_doc->status == 1)
                            <div class="col-xs-8">
                                <i class="fa fa-check" style="width:35px; padding: 4px 15px; color: #26C281"></i> <a href="{!! $tag_doc->attachment_url !!}" class="linkDark">Electrical Test & Tagging</a>
                            </div>
                        @endif
                        @if ($tag_doc && $tag_doc->status == 2)
                            <div class="col-xs-8">
                                <i class="fa fa-question" style="width:35px; padding: 4px 15px;"></i> <a href="{!! $tag_doc->attachment_url !!}" class="linkDark">Electrical Test & Tagging</a>
                            </div>
                            <div class="col-xs-4"><span class="label label-warning label-sm">Pending Approval</span></div>
                        @endif
                        @if ($tag_doc && $tag_doc->status == 3)
                            <div class="col-xs-8">
                                <i class="fa fa-question" style="width:35px; padding: 4px 15px;"></i> <a href="{!! $tag_doc->attachment_url !!}" class="linkDark">Electrical Test & Tagging</a>
                            </div>
                            <div class="col-xs-4"><span class="label label-danger label-sm">Rejected</span></div>
                        @endif
                        @if (!$tag_doc)
                            <div class="col-xs-8"><i class="fa fa-times" style="width:35px; padding: 4px 15px;"></i> Electrical Test & Tagging</div>
                        @endif
                    </div>
                @endif


                {{-- Non-compliant docs needing Approval --}}
                @if (Auth::user()->companyDocTypeSelect('view', $company, 'all') && count($company->nonCompliantDocs('array', 2)))
                    <br>
                    <a href="/company/{{ $company->id }}/doc" class="btn btn-warning">Some documents require approval</a>
                @endif
            </div>
        </div>
    @endif
</div>