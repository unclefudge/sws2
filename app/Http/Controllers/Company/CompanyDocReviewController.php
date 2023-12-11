<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Company\CompanyDocReview;
use App\Models\Company\CompanyDocReviewFile;
use App\Models\Misc\Action;
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

        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2("edit.company.doc.review", $doc))
            return view('errors/404');

        $doc_request = request()->all();
        //dd($doc_request);

        // Updates completed - Renew
        if (request('renew')) {
            if (request('next_review_date')) {
                $doc->status = 0;
                $doc->stage = 10;
                $doc->approved_adm = Carbon::now()->toDateTimeString();
                $doc->save();
                $action = Action::create(['action' => 'Standard Details review completed - renewal date set ' . request('next_review_date'), 'table' => 'company_docs_review', 'table_id' => $doc->id]);

                // Update attachment + expiry date on Original Standard Details
                if ($doc->current_doc) {
                    // Delete old attached file
                    $company_dir = '/filebank/company/' . $doc->company_doc->company_id . '/docs';
                    if ($doc->company_doc->attachment && file_exists(public_path('/filebank/company/' . $doc->company_doc->company_id . '/docs/' . $doc->company_doc->attachment)))
                        unlink(public_path('/filebank/company/' . $doc->company_doc->company_id . '/docs/' . $doc->company_doc->attachment));
                    // Copy new file
                    copy(public_path($doc->current_doc_url), public_path("$company_dir/$doc->current_doc"));
                    $doc->company_doc->attachment = $doc->current_doc;
                }

                $doc->company_doc->expiry = Carbon::createFromFormat('d/m/Y H:i', request('next_review_date') . '00:00')->toDateTimeString();
                $doc->company_doc->save();
                $assigned_user = null;
            } else
                return back()->withErrors(['next_review_date' => "The next review date field is required."]);
        } else {
            if (request('approve_version')) {
                // Version Approved by Con Mgr
                $mesg = ($doc->stage == 1) ? 'Original Standard Details approved by Con Mgr - no changes required' : 'Updated Standard Details approved by Con Mgr';
                $action = Action::create(['action' => $mesg, 'table' => 'company_docs_review', 'table_id' => $doc->id]);

                $doc->stage = 9; // Nadia set renew date
                $doc->approved_con = Carbon::now()->toDateTimeString();
                $doc->save();
                $assigned_user = User::find(465); // Nadia
            } elseif (request('assign_user')) {
                // User assigned to update
                $doc->stage = 3;
                $doc->save();
                $assigned_user = User::findOrFail(request('assign_user'));
                $action = Action::create(['action' => "Assigned to $assigned_user->fullname to update", 'table' => 'company_docs_review', 'table_id' => $doc->id]);
            } else {
                if ($doc->stage == 1 || $doc->stage == 4) { // Updates to Standard Details by Con Mgr
                    $doc->stage = 2; // Nadia assign Eng
                    $doc->save();
                    $action = Action::create(['action' => 'Standard Details updated by Con Mgr', 'table' => 'company_docs_review', 'table_id' => $doc->id]);
                    $assigned_user = User::find(465); // Nadia
                } else if ($doc->stage == 3) { // Updates to Current Doc
                    $doc->stage = 4; // Assigned to Con Mgr to review changes
                    $doc->save();
                    $current_user = Auth::user();
                    $action = Action::create(['action' => "Standard Details updated by Draftsperson", 'table' => 'company_docs_review', 'table_id' => $doc->id]);
                    $assigned_user = User::find(108); // Kirstie
                }

                // Handle attached file
                if (request()->hasFile('singlefile')) {
                    $file = request()->file('singlefile');
                    $path = "filebank/company/" . $doc->company_doc->company_id . '/docs/review';
                    $name = sanitizeFilename(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . strtolower($file->getClientOriginalExtension());
                    // Ensure filename is unique by adding counter to similiar filenames
                    $count = 1;
                    while (file_exists(public_path("$path/$name")))
                        $name = sanitizeFilename(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '-' . $count++ . '.' . strtolower($file->getClientOriginalExtension());
                    $file->move($path, $name);
                    $doc->current_doc = $name;
                    $doc->save();

                    $doc_file = CompanyDocReviewFile::create(['review_id' => $doc->id, 'attachment' => $doc->current_doc]);
                }
            }
        }


        // Close any ToDoo and create new one if assigned user
        $doc->closeToDo();
        if ($assigned_user && request('due_at'))
            $doc->createAssignToDo($assigned_user->id, request('due_at')); // Assigned User with due date
        elseif ($assigned_user)
            $doc->createAssignToDo($assigned_user->id); // Assigned User

        Toastr::success("Updated document");

        return redirect("company/doc/standard/review/$doc->id/edit");
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
