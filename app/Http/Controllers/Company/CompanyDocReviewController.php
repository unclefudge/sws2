<?php

namespace App\Http\Controllers\Company;

use Illuminate\Http\Request;
use Validator;

use DB;
use Session;
use App\Models\Company\Company;
use App\Models\Company\CompanyDoc;
use App\Models\Company\CompanyDocReview;
use App\Models\Company\CompanyDocReviewFile;
use App\Models\Company\CompanyDocCategory;
use App\Models\Misc\ContractorLicenceSupervisor;
use App\Http\Utilities\CompanyDocTypes;
use App\Http\Requests;
use App\Http\Requests\Company\CompanyDocRequest;
use App\Http\Requests\Company\CompanyProfileDocRequest;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Yajra\Datatables\Datatables;
use nilsenj\Toastr\Facades\Toastr;
use Carbon\Carbon;

/**
 * Class CompanyDocReviewController
 * @package App\Http\Controllers
 */
class CompanyDocReviewController extends Controller {

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Check authorisation and throw 404 if not
        if (!Auth::user()->hasPermission2('view.company.doc.review'))
            return view('errors/404');

        return view('company/doc/review/list');
    }

    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $doc = CompanyDocReview::findOrFail($id);

        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2("view.company.doc.review", $doc))
            return view('errors/404');

        return view('company/doc/review/show', compact('doc'));
    }


    /**
     * Edit the specified resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $doc = CompanyDocReview::findOrFail($id);

        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2("edit.company.doc.review", $doc))
            return view('errors/404');

        return view('company/doc/review/edit', compact('doc'));
    }


    /**
     * Update the specified resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function update($id)
    {
        $doc = CompanyDocReview::findOrFail($id);

        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2("edit.company.doc.review", $doc))
            return view('errors/404');

        $doc_request = request()->all();
        $doc_request['expiry'] = (request('expiry')) ? Carbon::createFromFormat('d/m/Y H:i', request('expiry') . '00:00')->toDateTimeString() : null;


        //dd($doc_request);
        $doc->update($doc_request);

        // Handle attached file
        if (request()->hasFile('singlefile')) {
            $file = request()->file('singlefile');
            $path = "filebank/company/" . $doc->company_doc->company_id . '/docs/review';
            $name = sanitizeFilename(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . strtolower($file->getClientOriginalExtension());
            // Ensure filename is unique by adding counter to similiar filenames
            $count = 1;
            while (file_exists(public_path("$path/$name")))
                $name = sanitizeFilename(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '-' . $count ++ . '.' . strtolower($file->getClientOriginalExtension());
            $file->move($path, $name);
            $doc->attachment = $name;
            $doc->save();
        }

        // Close any ToDoo and create new one
        $doc->closeToDo();
        // Create approval ToDoo
        //if ($doc->status == 2 && ($doc->category->type == 'acc' || $doc->category->type == 'whs'))
        //    $doc->createApprovalToDo($doc->owned_by->notificationsUsersTypeArray('doc.' . $doc->category->type . '.approval'));
        //}

        Toastr::success("Updated document");

        return redirect("company/doc/standard/review/doc/$doc->id/edit");
    }

    /**
     * Delete the specified resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $doc = CompanyDoc::findOrFail($id);

        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2("del.company.doc", $doc))
            return json_encode("failed");

        // Delete attached file
        if ($doc->attachment && file_exists(public_path('/filebank/company/' . $doc->company_id . '/docs/' . $doc->attachment)))
            unlink(public_path('/filebank/company/' . $doc->company_id . '/docs/' . $doc->attachment));

        // Delete any assigned Supervisors for Contractor Licences
        if ($doc->category_id == '7')
            ContractorLicenceSupervisor::where('company_id', $doc->for_company_id)->delete();

        $doc->closeToDo();
        $doc->delete();

        return json_encode('success');
    }


    /**
     * Reject the specified company document in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function reject($cid, $id)
    {
        $company = Company::find($cid);
        $doc = CompanyDoc::findOrFail($id);

        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2("sig.company.doc", $doc))
            return view('errors/404');

        //dd(request()->all());
        $doc->status = 3;
        $doc->reject = request('reject');
        $doc->closeToDo();
        $doc->emailReject();
        $doc->save();

        Toastr::success("Updated document");

        return redirect("company/$company->id/doc/$doc->id/edit");
    }

    /**
     * Approve / Unarchive the specified company document.
     *
     * @return \Illuminate\Http\Response
     */
    public function archive($cid, $id)
    {
        $company = Company::find($cid);
        $doc = CompanyDoc::findOrFail($id);

        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2("del.company.doc", $doc))
            return view('errors/404');

        //dd(request()->all());
        ($doc->status == 1) ? $doc->status = 0 : $doc->status = 1;
        $doc->closeToDo();
        $doc->save();

        // Delete any assigned Supervisors for Contractor Licences
        if ($doc->category_id == '7')
            ContractorLicenceSupervisor::where('company_id', $doc->for_company_id)->delete();

        if ($doc->status == 1)
            Toastr::success("Document restored");
        else {
            //$doc->emailArchived();
            Toastr::success("Document achived");
        }

        return redirect("company/$company->id/doc/$doc->id/edit");
    }

    /**
     * Upload File + Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function upload($id)
    {
        // Check authorisation and throw 404 if not
        if (!(Auth::user()->allowed2('add.company.doc.gen') || Auth::user()->allowed2('add.company.doc.lic') ||
            Auth::user()->allowed2('add.company.doc.whs') || Auth::user()->allowed2('add.company.doc.ics'))
        )
            return json_encode("failed");

        // Handle file upload
        if (request()->hasFile('multifile')) {
            $files = request()->file('multifile');
            foreach ($files as $file) {
                $path = "filebank/company/" . Auth::user()->company_id . '/docs';
                $name = sanitizeFilename(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . strtolower($file->getClientOriginalExtension());

                // Ensure filename is unique by adding counter to similiar filenames
                $count = 1;
                while (file_exists(public_path("$path/$name")))
                    $name = sanitizeFilename(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '-' . $count ++ . '.' . strtolower($file->getClientOriginalExtension());
                $file->move($path, $name);

                $doc_request['category_id'] = $request->get('category_id');
                $doc_request['name'] = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $doc_request['company_id'] = Auth::user()->company_id;
                $doc_request['for_company_id'] = Auth::user()->company_id;
                $doc_request['expiry'] = null;

                // Set Type
                if ($doc_request['category_id'] > 6 && $doc_request['category_id'] < 10)
                    $doc_request['type'] = 'lic';
                elseif ($doc_request['category_id'] > 20)
                    $doc_request['type'] = 'gen';

                // Create Site Doc
                $doc = CompanyDoc::create($doc_request);
                $doc->attachment = $name;
                $doc->save();
            }
        }

        return json_encode("success");
    }


    /**
     * Get CC Standard Details
     */
    public function getStandard()
    {
        $records = CompanyDocReview::where('status', '1')->orderBy('updated_at');

        $dt = Datatables::of($records)
            ->editColumn('id', '<div class="text-center"><a href="/company/doc/standard/review/{{$id}}"><i class="fa fa-search"></i></a></div>')
            ->editColumn('assigned_to', function ($doc) {
                //$todo = $doc->todos('1')->first();
                //if ($todo)
                return ($doc->assignedToSBC()) ? $doc->assignedToSBC() : '-';
                //return "-";
            })
            ->editColumn('updated_at', function ($doc) {
                return $doc->updated_at->format('d/m/Y');
            })
            ->rawColumns(['id', 'name'])
            ->make(true);

        return $dt;
    }
}
