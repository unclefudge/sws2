<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Company\CompanyDocReview;
use App\Models\Company\CompanyDocReviewFile;
use App\Models\Misc\Action;
use App\Services\FileBank;
use App\User;
use Carbon\Carbon;
use DB;
use Illuminate\Support\Facades\Auth;
use nilsenj\Toastr\Facades\Toastr;
use Session;
use Validator;
use Yajra\Datatables\Datatables;

/**
 * Class CompanyDocReviewController
 * @package App\Http\Controllers
 */
class CompanyDocReviewController extends Controller
{

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

        // Check authorisation
        if (!Auth::user()->allowed2("edit.company.doc.review", $doc))
            return view('errors/404');

        $assigned_user = null;

        // -------------------------------------------------
        // RENEW FLOW
        // -------------------------------------------------
        if (request('renew')) {

            if (!request('next_review_date'))
                return back()->withErrors(['next_review_date' => "The next review date field is required."]);

            // Close review
            $doc->status = 0;
            $doc->stage = 10;
            $doc->approved_adm = now();
            $doc->save();

            Action::create(['action' => 'Standard Details review completed - renewal date set ' . request('next_review_date'), 'table' => 'company_docs_review', 'table_id' => $doc->id,]);

            // -------------------------------------------------
            // COPY FILE: review → official company docs
            // -------------------------------------------------
            if ($doc->current_doc && $doc->company_doc) {

                $companyId = $doc->company_doc->company_id;
                $sourcePath = "company/{$companyId}/docs/review/{$doc->current_doc}";
                $destPath = "company/{$companyId}/docs/{$doc->current_doc}";

                // Remove old attachment if exists
                if ($doc->company_doc->attachment)
                    FileBank::delete("company/{$companyId}/docs/{$doc->company_doc->attachment}");

                // Copy contents safely (Spaces-safe)
                if (FileBank::exists($sourcePath)) {
                    FileBank::putContents($destPath, FileBank::get($sourcePath));
                }

                // Update CompanyDoc record
                $doc->company_doc->attachment = $doc->current_doc;
                $doc->company_doc->expiry = Carbon::createFromFormat('d/m/Y H:i', request('next_review_date') . ' 00:00')->toDateTimeString();
                $doc->company_doc->save();
            }

        }

        // -------------------------------------------------
        // ASSIGN USER
        // -------------------------------------------------
        elseif (request('assign_user')) {

            $doc->stage = 2;
            $doc->save();
            $assigned_user = User::findOrFail(request('assign_user'));
            Action::create(['action' => "Assigned to {$assigned_user->fullname} to update", 'table' => 'company_docs_review', 'table_id' => $doc->id,]);

        }

        // -------------------------------------------------
        // UPLOAD NEW REVIEW FILE
        // -------------------------------------------------
        elseif (request()->hasFile('singlefile')) {

            $file = request()->file('singlefile');
            $basePath = "company/{$doc->company_doc->company_id}/docs/review";

            // Replace existing review file
            $filename = FileBank::storeUploadedFile($file, $basePath, $doc->current_doc);

            $doc->current_doc = $filename;
            $doc->save();

            CompanyDocReviewFile::create(['review_id' => $doc->id, 'attachment' => $filename,]);

            // If drafted → send back to Drafting Manager
            if ($doc->stage == 2) {
                $doc->stage = 1;
                $doc->save();

                Action::create(['action' => "Standard Details updated by Draftsperson", 'table' => 'company_docs_review', 'table_id' => $doc->id,]);
                $assigned_user = User::find(465); // Nadia
            }
        }

        // -------------------------------------------------
        // TODoos
        // -------------------------------------------------
        $doc->closeToDo();

        if ($assigned_user && request('due_at'))
            $doc->createAssignToDo($assigned_user->id, request('due_at'));
        elseif ($assigned_user)
            $doc->createAssignToDo($assigned_user->id);

        Toastr::success("Updated document");

        return redirect("company/doc/standard/review/{$doc->id}/edit");
    }


    /**
     * Get CC Standard Details
     */
    public function getStandard()
    {
        $records = CompanyDocReview::where('status', '1')->orderBy('updated_at');

        $dt = Datatables::of($records)
            ->editColumn('id', '<div class="text-center"><a href="/company/doc/standard/review/{{$id}}/edit"><i class="fa fa-search"></i></a></div>')
            ->editColumn('assigned_to', function ($doc) {
                return ($doc->assignedToSBC()) ? $doc->assignedToSBC() : '-';
            })
            ->addColumn('stage_text', function ($doc) {
                return $doc->stage_text;
            })
            ->editColumn('updated_at', function ($doc) {
                return $doc->updated_at->format('d/m/Y');
            })
            ->rawColumns(['id', 'name'])
            ->make(true);

        return $dt;
    }
}
