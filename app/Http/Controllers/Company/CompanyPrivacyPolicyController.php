<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Company\Company;
use App\Models\Company\CompanyDoc;
use App\Models\Company\CompanyDocPrivacyPolicy;
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
 * Class CompanyPrivacyPolicyController
 * @package App\Http\Controllers
 */
class CompanyPrivacyPolicyController extends Controller
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
        //dd('here');
        $company = Company::findOrFail($cid);
        $policy = CompanyDocPrivacyPolicy::find($id);

        if ($policy)
            return view('company/doc/privacy-show', compact('company', 'policy'));

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

        return view('company/doc/privacy-create', compact('company'));
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

        $policy_request = removeNullValues(request()->all());
        $policy_request = request()->all();


        $policy_request['date'] = Carbon::now();
        $policy_request['contractor_signed_id'] = Auth::user()->id;
        $policy_request['contractor_signed_at'] = Carbon::now();
        $policy_request['for_company_id'] = $company->id;
        $policy_request['company_id'] = $company->reportsTo()->id;
        $policy_request['status'] = 1;
        //dd($policy_request);

        /* ------------------------------------------------------------
        | FileBank path + unique filename
        |------------------------------------------------------------ */
        $basePath = "company/{$company->id}/docs";
        $baseFilename = sanitizeFilename($company->name) . '-PRIVACY-' . now()->format('d-m-Y') . '.pdf';
        $filename = $baseFilename;
        $counter = 1;

        while (FileBank::exists("{$basePath}/{$filename}")) {
            $filename = sanitizeFilename($company->name) . '-PRIVACY-' . now()->format('d-m-Y') . "-{$counter}.pdf";
            $counter++;
        }
        $policy_request['attachment'] = $filename;

        // Create Privacy Policy
        $policy = CompanyDocPrivacyPolicy::create($policy_request);

        /* ------------------------------------------------------------
        | Generate PDF → memory → FileBank
        |------------------------------------------------------------ */
        $pdf = PDF::loadView('pdf/company-privacy', compact('policy', 'company'))->setPaper('a4');
        FileBank::putContents("{$basePath}/{$filename}", $pdf->output());


        // Create Company Doc
        $doc = CompanyDoc::create([
            'category_id' => 12,
            'name' => 'Privacy Policy',
            'attachment' => $filename,
            'ref_no' => $policy->id,
            'status' => 1,
            'for_company_id' => $policy->for_company_id,
            'company_id' => $policy->company_id,
        ]);

        $policy->closeToDo();

        Toastr::success("Signed policy");

        return redirect("/company/$company->id/doc/privacy-policy/$policy->id");
    }

    /**
     * Update the specified resource.
     */
    public function update($cid, $id)
    {

    }

}
