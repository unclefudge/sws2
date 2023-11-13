<?php

namespace App\Http\Controllers\Company;

use Illuminate\Http\Request;
use Validator;

use DB;
use PDF;
use Mail;
use Session;
use App\User;
use App\Models\Company\Company;
use App\Models\Company\CompanyDoc;
use App\Models\Company\CompanyDocPeriodTrade;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use nilsenj\Toastr\Facades\Toastr;
use Carbon\Carbon;

/**
 * Class CompanyPeriodTradeContractController
 * @package App\Http\Controllers
 */
class CompanyPeriodTradeController extends Controller {

    public function index()
    {
        // Check authorisation and throw 404 if not
        if (!Auth::user()->hasAnyPermissionType('site.export'))
            return view('errors/404');

        return view('site/export/list');
    }

    /**
     * Display the specified resource.
     */
    public function show($cid, $id)
    {
        //dd('here');
        $company = Company::findOrFail($cid);
        $ptc = CompanyDocPeriodTrade::find($id);

        if ($ptc)
            return view('company/doc/ptc-show', compact('company', 'ptc'));

        return view('errors/404');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($cid)
    {
        $company = Company::findOrFail($cid);

        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('add.company.doc'))
            return view('errors/404');

        return view('company/doc/ptc-create', compact('company'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store($cid)
    {
        $company = Company::findOrFail($cid);

        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('add.company.doc'))
            return view('errors/404');

        $ptc_request = removeNullValues(request()->all());
        $ptc_request = request()->all();

        $ptc_request['date'] = Carbon::now();
        $ptc_request['period'] = '12';
        $ptc_request['expiry'] = Carbon::today()->addMonths($ptc_request['period']);
        $ptc_request['principle_id'] = $company->reportsTo()->id;
        $ptc_request['principle_name'] = $company->reportsTo()->name;
        $ptc_request['principle_address'] = $company->reportsTo()->address_formatted;
        $ptc_request['principle_phone'] = $company->reportsTo()->phone;
        $ptc_request['principle_email'] = ($company->reportsTo()->id == 3) ? 'accounts1@capecod.com.au' : $company->reportsTo()->email;
        $ptc_request['principle_abn'] = $company->reportsTo()->abn;
        $ptc_request['principle_acn'] = ($company->reportsTo()->id == 3) ? '000 605 407' : '';
        $ptc_request['contractor_id'] = $company->id;
        $ptc_request['contractor_name'] = $company->name;
        $ptc_request['contractor_address'] = $company->address_formatted;
        $ptc_request['contractor_phone'] = $company->phone;
        $ptc_request['contractor_email'] = $company->email;
        $ptc_request['contractor_abn'] = $company->abn;
        $ptc_request['contractor_acn'] = $company->acn;
        $ptc_request['contractor_gst'] = $company->gst;
        $ptc_request['contractor_licence'] = ($company->activeCompanyDoc('7') && $company->activeCompanyDoc('7')->status == 1) ? $company->activeCompanyDoc('7')->ref_no : null;
        $ptc_request['contractor_pl_name'] = ($company->activeCompanyDoc('1') && $company->activeCompanyDoc('1')->status == 1) ? $company->activeCompanyDoc('1')->ref_name : null;
        $ptc_request['contractor_pl_ref'] = ($company->activeCompanyDoc('1') && $company->activeCompanyDoc('1')->status == 1) ? $company->activeCompanyDoc('1')->ref_no : null;
        $ptc_request['contractor_pl_expiry'] = ($company->activeCompanyDoc('1') && $company->activeCompanyDoc('1')->status == 1) ? $company->activeCompanyDoc('1')->expiry : null;
        $ptc_request['contractor_wc_name'] = ($company->activeCompanyDoc('2') && $company->activeCompanyDoc('2')->status == 1) ? $company->activeCompanyDoc('2')->ref_name : null;
        $ptc_request['contractor_wc_ref'] = ($company->activeCompanyDoc('2') && $company->activeCompanyDoc('2')->status == 1) ? $company->activeCompanyDoc('2')->ref_no : null;
        $ptc_request['contractor_wc_expiry'] = ($company->activeCompanyDoc('2') && $company->activeCompanyDoc('2')->status == 1) ? $company->activeCompanyDoc('2')->expiry : null;
        $ptc_request['contractor_sa_name'] = ($company->activeCompanyDoc('3') && $company->activeCompanyDoc('3')->status == 1) ? $company->activeCompanyDoc('3')->ref_name : null;
        $ptc_request['contractor_sa_ref'] = ($company->activeCompanyDoc('3') && $company->activeCompanyDoc('3')->status == 1) ? $company->activeCompanyDoc('3')->ref_no : null;
        $ptc_request['contractor_sa_expiry'] = ($company->activeCompanyDoc('3') && $company->activeCompanyDoc('3')->status == 1) ? $company->activeCompanyDoc('3')->expiry : null;
        $ptc_request['contractor_signed_id'] = Auth::user()->id;
        $ptc_request['contractor_signed_at'] = Carbon::now();
        $ptc_request['for_company_id'] = $company->id;
        $ptc_request['company_id'] = $company->reportsTo()->id;
        $ptc_request['status'] = 3;  // Pending approval
        //dd($ptc_request);

        // Create PTC
        $ptc = CompanyDocPeriodTrade::create($ptc_request);

        // Create Company Doc
        $doc = CompanyDoc::create([
            'category_id'    => 5,
            'name'           => 'Period Trade Contract',
            'expiry'         => $ptc->expiry,
            'ref_no'         => $ptc->id,
            'status'         => 2,
            'for_company_id' => $ptc->for_company_id,
            'company_id'     => $ptc->company_id,
        ]);

        // Create approval ToDoo
        $ptc->createApprovalToDo($ptc->owned_by->notificationsUsersTypeArray('doc.acc.approval'));

        // Delete any rejected docs
        $deleted = CompanyDocPeriodTrade::where('for_company_id', $company->id)->where('status', 3)->delete();

        Toastr::success("Signed contract");

        return redirect("/company/$company->id/doc/period-trade-contract/$ptc->id");
    }

    /**
     * Update the specified resource.
     */
    public function update($cid, $id)
    {
        $company = Company::findOrFail($cid);
        $ptc = CompanyDocPeriodTrade::findOrFail($id);

        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('add.company.doc'))
            return view('errors/404');

        $ptc_request = request()->all();
        // Archive old contract if required
        if (request('archive')) {
            $ptc_request['principle_signed_name'] = request('principle_signed_name2');
            $old_ptc = CompanyDoc::findOrFail(request('archive'));
            $old_ptc->status = 0;
            $old_ptc->save();
        }
        $ptc_request['principle_signed_id'] = Auth::user()->id;
        $ptc_request['principle_signed_at'] = Carbon::now();
        $ptc_request['status'] = 1;

        // Set + create create directory if required
        $path = "filebank/company/$company->id/docs";
        if (!file_exists($path))
            mkdir($path, 0777, true);

        // Ensure filename is unique by adding counter to similiar filenames
        $filename = sanitizeFilename($company->name) . '-PTC-' . $ptc->date->format('d-m-Y') . '.pdf';
        $count = 1;
        while (file_exists(public_path("$path/$filename")))
            $filename = sanitizeFilename($company->name) . '-PTC-' . $ptc->date->format('d-m-Y') . '-' . $count ++ . '.pdf';

        $ptc_request['attachment'] = $filename;

        //dd($ptc_request);
        $ptc->update($ptc_request);
        $ptc->closeToDo();

        //
        // Generate PDF
        //
        //return view('pdf/company-tradecontract', compact('ptc', 'company'));
        $pdf = PDF::loadView('pdf/company-tradecontract', compact('ptc', 'company'));
        $pdf->setPaper('a4');
        $pdf->save(public_path("$path/$filename"));
        //return $pdf->stream();

        // Update Company Doc
        $doc = CompanyDoc::where('category_id', 5)->where('ref_no', $ptc->id)->where('company_id', $ptc->company_id)->where('for_company_id', $ptc->for_company_id)->first();
        if ($doc) {
            $doc->attachment = $filename;
            $doc->status = 1;
            $doc->approved_by = $ptc->principle_signed_id;
            $doc->approved_at = $ptc->principle_signed_at;
            $doc->save();
        }


        Toastr::success("Signed contract");

        return redirect("/company/$company->id/doc/period-trade-contract/$ptc->id");
    }

    /**
     * Reject the specified company document in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function reject($cid, $id)
    {
        $company = Company::findOrFail($cid);
        $ptc = CompanyDocPeriodTrade::findOrFail($id);

        // Check authorisation and throw 404 if not
        //if (!Auth::user()->allowed2("sig.company.doc", $doc))
        //    return view('errors/404');

        $ptc->status = 2;  // reject
        $ptc->reject = request('reject');
        $ptc->closeToDo();
        $ptc->emailReject();
        $ptc->save();
        Toastr::success("Contract rejected");

        return redirect("/company/$company->id/doc/period-trade-contract/$ptc->id");
    }


    /**
     * Create PDF
     */
    public function tradecontractPDF($cid)
    {
        $company = Company::findOrFail($cid);

        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('view.company', $company))
            return view('errors/404');

        //$data[] = ['company_name' => 'cname', 'status' => 'status',];
        //return view('pdf/company-tradecontract', compact('data', 'company'));
        //$pdf = PDF::loadView('pdf/company-tradecontract', compact('data', 'company'));
        $pdf = PDF::loadView('pdf/company-tradecontract', compact('company'));
        $pdf->setPaper('a4');
        //$pdf->setOption('page-width', 200)->setOption('page-height', 287);
        //$pdf->setOption('margin-bottom', 10);
        //->setOption('footer-font-size', '7')
        //->setOption('footer-left', utf8_decode('Document created ' . date('\ d/m/Y\ ')))
        //->setOption('footer-center', utf8_decode('Page [page] / [topage]'))
        //->setOption('footer-right', utf8_decode("Initials:     "))
        //->setOrientation('portrait');

        //if ($request->has('view_pdf'))
        return $pdf->stream();
    }

    /**
     * Create PDF
     */
    public function blankPtcPDF($cid)
    {
        $company = Company::findOrFail($cid);
        //$data[] = ['company_name' => 'cname', 'status' => 'status',];
        //return view('pdf/company-tradecontract-blank', compact('company'));
        $pdf = PDF::loadView('pdf/company-tradecontract-blank', compact('company'));
        $pdf->setPaper('a4');
        //$pdf->setOption('page-width', 200)->setOption('page-height', 287);
        //$pdf->setOption('margin-bottom', 10);
        //->setOption('footer-font-size', '7')
        //->setOption('footer-left', utf8_decode('Document created ' . date('\ d/m/Y\ ')))
        //->setOption('footer-center', utf8_decode('Page [page] / [topage]'))
        //->setOption('footer-right', utf8_decode("Initials:     "))
        //->setOrientation('portrait');

        //if ($request->has('view_pdf'))
        return $pdf->stream();
    }


}
