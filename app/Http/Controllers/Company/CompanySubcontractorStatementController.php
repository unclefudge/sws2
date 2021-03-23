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
use App\Models\Company\CompanyDocSubcontractorStatement;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use nilsenj\Toastr\Facades\Toastr;
use Carbon\Carbon;

/**
 * Class CompanySubcontractorStatementController
 * @package App\Http\Controllers
 */
class CompanySubcontractorStatementController extends Controller {

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
        $company = Company::findOrFail($cid);
        $ss = CompanyDocSubcontractorStatement::find($id);

        if ($ss)
            return view('company/doc/ss-show', compact('company', 'ptc'));

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

        $year = Carbon::now()->format('Y');
        $month = Carbon::now()->format('n');
        $last_year = $year - 1;
        $next_year = $year + 1;
        $from_now = ($month > 6) ? Carbon::parse("July 1 $year") : Carbon::parse("July 1 $last_year");
        $from_next = ($month > 6) ? Carbon::parse("July 1 $year")->addYear() : Carbon::parse("July 1 $last_year")->addYear();
        $to_now = ($month > 6) ? Carbon::parse("June 30 $next_year") : Carbon::parse("June 30 $year");
        $to_next = ($month > 6) ? Carbon::parse("June 30 $next_year")->addYear() : Carbon::parse("June 30 $year")->addYear();

        $dates_from = ['current' => $from_now->format('d/m/Y'), 'next' => $from_next->format('d/m/Y')];
        $dates_to = ['current' => $to_now->format('d/m/Y'), 'next' => $to_next->format('d/m/Y')];

        return view('company/doc/ss-create', compact('company', 'dates_from', 'dates_to'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store($cid)
    {
        $messages = [
            'contractor_signed_title.required' => 'The position/title field is required',
            'clause_a.required' => 'The clause a. field is required',
        ];

        $this->validate(request(), [
            'contractor_full_name'    => 'required',
            'contractor_signed_name'  => 'required',
            'contractor_signed_title' => 'required',
            'clause_a' => 'required'
        ], $messages);

        $company = Company::findOrFail($cid);

        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('add.company.doc'))
            return view('errors/404');

        $ss_request = request()->all();

        // Calculate Date Period
        $year = Carbon::now()->format('Y');
        $month = Carbon::now()->format('n');
        $last_year = $year - 1;
        $next_year = $year + 1;
        if (request('date_from') == 'current') {
            $ss_request['from'] = ($month > 6) ? Carbon::parse("July 1 $year") : Carbon::parse("July 1 $last_year");
            $ss_request['to'] = ($month > 6) ? Carbon::parse("June 30 $next_year") : Carbon::parse("June 30 $year");
        } else {
            $ss_request['from'] = ($month > 6) ? Carbon::parse("July 1 $year")->addYear() : Carbon::parse("July 1 $last_year")->addYear();
            $ss_request['to'] = ($month > 6) ? Carbon::parse("June 30 $next_year")->addYear() : Carbon::parse("June 30 $year")->addYear();
        }

        $ss_request['claim_payment'] = (request('claim_payment')) ?  Carbon::createFromFormat('d/m/Y H:i', request('claim_payment') . '00:00')->toDateTimeString() : null;
        $ss_request['principle_id'] = $company->reportsTo()->id;
        $ss_request['principle_name'] = $company->reportsTo()->name;
        $ss_request['principle_abn'] = $company->reportsTo()->abn;
        $ss_request['contractor_id'] = $company->id;
        $ss_request['contractor_name'] = $company->name;
        $ss_request['contractor_address'] = $company->address_formatted;
        $ss_request['contractor_abn'] = $company->abn;
        $ss_request['contractor_signed_id'] = Auth::user()->id;
        $ss_request['contractor_signed_at'] = Carbon::now();
        $ss_request['wc_date'] = ($company->activeCompanyDoc('2') && $company->activeCompanyDoc('2')->status == 1 ) ? $company->activeCompanyDoc('2')->expiry : null;
        $ss_request['for_company_id'] = $company->id;
        $ss_request['company_id'] = $company->reportsTo()->id;
        $ss_request['status'] = 2;

        //dd($ss_request);
        // Set + create create directory if required
        $path = "filebank/company/$company->id/docs";
        if (!file_exists($path))
            mkdir($path, 0777, true);

        // Determine filename + ensure filename is unique by adding counter to similiar filenames
        $filename = sanitizeFilename($company->name) . '-SS-' . Carbon::today()->format('d-m-Y') . '.pdf';
        $count = 1;
        while (file_exists(public_path("$path/$filename")))
            $filename = sanitizeFilename($company->name) . '-SS-' . Carbon::today()->format('d-m-Y') . '-' . $count ++ . '.pdf';

        $ss_request['attachment'] = $filename;
        //dd($ss_request);

        // Create SS Doc
        $ss = CompanyDocSubcontractorStatement::create($ss_request);

        //
        // Generate PDF
        //
        //return view('pdf/company-subcontractorstatement', compact('ss', 'company'));
        $pdf = PDF::loadView('pdf/company-subcontractorstatement', compact('ss', 'company'));
        $pdf->setPaper('a4');
        $pdf->save(public_path("$path/$filename"));
        //return $pdf->stream();

        // Archive old contract if required
        if (request('archive')) {
            $old_ss = CompanyDoc::findOrFail(request('archive'));
            $old_ss->status = 0;
            $old_ss->save();
        }

        // Create Site Doc
        $doc = CompanyDoc::create([
            'category_id'    => 4,
            'name'           => 'Subcontractors Statement',
            'attachment'     => $filename,
            'expiry'         => $ss->to,
            'status'         => 2,
            'for_company_id' => $ss->for_company_id,
            'company_id'     => $ss->company_id,
        ]);

        // Delete any rejected docs
        $deleted1 = CompanyDocSubcontractorStatement::where('for_company_id', $company->id)->where('status', 3)->delete();
        $deleted2 = CompanyDoc::where('category_id', 4)->where('for_company_id', $company->id)->where('status', 3)->delete();



        // Create approval ToDoo
        $doc->createApprovalToDo($doc->owned_by->notificationsUsersTypeArray('doc.acc.approval'));
        Toastr::success("Signed contract");

        return redirect("/company/$company->id/doc/$doc->id/edit");
    }

    /**
     * Update the specified resource.
     */
    public function update($cid, $id)
    {
        $company = Company::findOrFail($cid);
        $ss = CompanyDocSubcontractorStatement::findOrFail($id);

        // Check authorisation and throw 404 if not
        //if (!Auth::user()->allowed2('add.company.doc'))
        //    return view('errors/404');

        $ss_request = request()->all();
        // Archive old contract if required
        if (request('archive')) {
            $ss_request['principle_signed_name'] = request('principle_signed_name2');
            $old_ptc = CompanyDoc::findOrFail(request('archive'));
            $old_ptc->status = 0;
            $old_ptc->save();
        }
        $ss_request['principle_signed_id'] = Auth::user()->id;
        $ss_request['principle_signed_at'] = Carbon::now();
        $ss_request['status'] = 1;

        // Determine filename
        $path = "filebank/company/$company->id/docs";
        $filename = sanitizeFilename($company->name) . '-PTC-' . $ss->date->format('d-m-Y') . '.pdf';

        // Ensure filename is unique by adding counter to similiar filenames
        $count = 1;
        while (file_exists(public_path("$path/$filename")))
            $filename = sanitizeFilename($company->name) . '-PTC-' . $ss->date->format('d-m-Y') . '-' . $count ++ . '.pdf';

        $ss_request['attachment'] = $filename;

        //dd($ss_request);
        $ss->update($ss_request);
        $ss->closeToDo();

        //
        // Generate PDF
        //
        //return view('pdf/company-tradecontract', compact('ptc', 'company'));
        $pdf = PDF::loadView('pdf/company-tradecontract', compact('ptc', 'company'));
        $pdf->setPaper('a4');
        $pdf->save(public_path("$path/$filename"));
        //return $pdf->stream();

        // Create Site Doc
        $doc = CompanyDoc::create([
            'category_id'    => 5,
            'name'           => 'Period Trade Contract',
            'attachment'     => $filename,
            'expiry'         => $ss->expiry,
            'status'         => 1,
            'for_company_id' => $ss->for_company_id,
            'company_id'     => $ss->company_id,
            'approved_by'    => $ss->principle_signed_id,
            'approved_at'    => $ss->principle_signed_at,
        ]);

        Toastr::success("Signed contract");

        return redirect("/company/$company->id/doc/period-trade-contract/$ss->id");
    }

    /**
     * Reject the specified company document in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function reject($cid, $id)
    {
        $company = Company::findOrFail($cid);
        $ss = CompanyDocSubcontractorStatement::findOrFail($id);

        // Check authorisation and throw 404 if not
        //if (!Auth::user()->allowed2("sig.company.doc", $doc))
        //    return view('errors/404');

        $ss->status = 3;
        $ss->reject = request('reject');
        $ss->closeToDo();
        $ss->emailReject();
        $ss->save();
        Toastr::success("Contract rejected");

        return redirect("/company/$company->id/doc/period-trade-contract/$ss->id");
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


}
