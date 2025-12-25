<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Company\Company;
use App\Models\Company\CompanyDoc;
use App\Models\Company\CompanyDocSubcontractorStatement;
use App\Services\FileBank;
use Carbon\Carbon;
use DB;
use Illuminate\Support\Facades\Auth;
use Mail;
use nilsenj\Toastr\Facades\Toastr;
use PDF;
use Session;
use Validator;

/**
 * Class CompanySubcontractorStatementController
 * @package App\Http\Controllers
 */
class CompanySubcontractorStatementController extends Controller
{

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

        $dates_from = ['' => 'Select year', 'current' => $from_now->format('d/m/Y'), 'next' => $from_next->format('d/m/Y')];
        $dates_to = ['' => 'Select year', 'current' => $to_now->format('d/m/Y'), 'next' => $to_next->format('d/m/Y')];

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
            'date_from' => 'required',
            'contractor_full_name' => 'required',
            'contractor_signed_name' => 'required',
            'contractor_signed_title' => 'required',
            'clause_a' => 'required'
        ], $messages);

        $company = Company::findOrFail($cid);

        $wc_date = ($company->activeCompanyDoc('2') && $company->activeCompanyDoc('2')->status == 1) ? $company->activeCompanyDoc('2')->expiry : null;
        //if (request('clause_a') == 1 && $wc_date == null)
        //    return back()->withErrors(['clause_a' => "You don't have an active Workers Compensation document on Safe Worksite. You need to upload one before you can sign this statement"]);

        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('add.company.doc'))
            return view('errors/404');

        $ss_request = request()->all();

        /* ------------------------------------------------------------
        | Calculate date period
        |------------------------------------------------------------ */
        $year = now()->year;
        $month = now()->month;

        if (request('date_from') === 'current') {
            $ss_request['from'] = $month > 6 ? now()->create($year, 7, 1) : now()->create($year - 1, 7, 1);
            $ss_request['to'] = $month > 6 ? now()->create($year + 1, 6, 30) : now()->create($year, 6, 30);
        } else {
            $ss_request['from'] = ($month > 6 ? now()->create($year, 7, 1) : now()->create($year - 1, 7, 1))->addYear();
            $ss_request['to'] = ($month > 6 ? now()->create($year + 1, 6, 30) : now()->create($year, 6, 30))->addYear();
        }

        $ss_request['claim_payment'] = request('claim_payment') ? Carbon::createFromFormat('d/m/Y', request('claim_payment'))->startOfDay() : null;
        $ss_request += [
            'principle_id' => $company->reportsTo()->id,
            'principle_name' => $company->reportsTo()->name,
            'principle_abn' => $company->reportsTo()->abn,
            'contractor_id' => $company->id,
            'contractor_name' => $company->name,
            'contractor_address' => $company->address_formatted,
            'contractor_abn' => $company->abn,
            'contractor_signed_id' => Auth::id(),
            'contractor_signed_at' => now(),
            'wc_date' => $wc_date,
            'for_company_id' => $company->id,
            'company_id' => $company->reportsTo()->id,
            'status' => 3, // Pending
        ];

        /* ------------------------------------------------------------
        | FileBank path + unique filename
        |------------------------------------------------------------ */
        $basePath = "company/{$company->id}/docs";
        $baseFilename = sanitizeFilename($company->name) . '-SS-' . now()->format('d-m-Y') . '.pdf';
        $filename = $baseFilename;
        $counter = 1;

        while (FileBank::exists("{$basePath}/{$filename}")) {
            $filename = sanitizeFilename($company->name) . '-SS-' . now()->format('d-m-Y') . "-{$counter}.pdf";
            $counter++;
        }

        $ss_request['attachment'] = $filename;

        // Create SS Doc
        $ss = CompanyDocSubcontractorStatement::create($ss_request);

        /* ------------------------------------------------------------
        | Generate PDF → memory → FileBank
        |------------------------------------------------------------ */
        $pdf = PDF::loadView('pdf/company-subcontractorstatement', ['ss' => $ss, 'company' => $company,])->setPaper('a4');
        
        FileBank::putContents("{$basePath}/{$filename}", $pdf->output());


        // Archive old contract if required
        if (request('archive'))
            CompanyDoc::whereKey(request('archive'))->update(['status' => 0]);


        /* ------------------------------------------------------------
        | Create CompanyDoc record
        |------------------------------------------------------------ */
        $doc = CompanyDoc::create([
            'category_id' => 4,
            'name' => 'Subcontractors Statement',
            'attachment' => $filename,
            'expiry' => $ss->to,
            'status' => 3,
            'for_company_id' => $ss->for_company_id,
            'company_id' => $ss->company_id,
        ]);

        // Delete any rejected docs
        CompanyDocSubcontractorStatement::where('for_company_id', $company->id)->where('status', 2)->delete();
        CompanyDoc::where('category_id', 4)->where('for_company_id', $company->id)->where('status', 2)->delete();

        // Create approval ToDoo
        $doc->createApprovalToDo($doc->owned_by->notificationsUsersTypeArray('doc.acc.approval'));
        Toastr::success("Signed contract");

        return redirect("/company/$company->id/doc/$doc->id/edit");
    }
}
