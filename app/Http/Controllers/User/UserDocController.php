<?php

namespace App\Http\Controllers\User;

use Illuminate\Http\Request;
use Validator;

use DB;
use Session;
use App\User;
use App\Models\User\UserDoc;
use App\Models\User\UserDocCategory;
use App\Models\Company\Company;
use App\Http\Utilities\UserDocTypes;
use App\Http\Requests;
use App\Http\Requests\User\UserDocRequest;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Yajra\Datatables\Datatables;
use nilsenj\Toastr\Facades\Toastr;
use Carbon\Carbon;

/**
 * Class UserDocController
 * @package App\Http\Controllers
 */
class UserDocController extends Controller {

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($uid)
    {
        $user = User::findorFail($uid);

        // Check authorisation and throw 404 if not
        //if (!Auth::user()->allowed2('view.company', $company))
        //    return view('errors/404');

        $category_id = '';

        return view('user/doc/list', compact('user', 'category_id'));
    }

    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function show($uid, $id)
    {
        $user = User::findOrFail($uid);
        $doc = UserDoc::findOrFail($id);

        // Check authorisation and throw 404 if not
        //if (!Auth::user()->allowed2("view.user.doc", $doc))
        //    return view('errors/404');

        return view('user/doc/show', compact('user', 'doc'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($uid)
    {
        $user = User::findorFail($uid);
        $category_id = '';

        // Check authorisation and throw 404 if not
        //if (!(Auth::user()->allowed2('add.company.doc')))
        //    return view('errors/404');

        return view('user/doc/create', compact('user', 'category_id'));
    }

    /**
     * Edit the specified resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function edit($uid, $id)
    {
        $user = User::findOrFail($uid);
        $doc = UserDoc::findOrFail($id);

        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2("edit.user.doc", $doc)) {
            // If allowed to view then redirect to View only
            if (Auth::user()->allowed2("view.user.doc", $doc))
                return redirect("user/$user->id/doc/$doc->id");

            return view('errors/404');
        }

        return view('user/doc/edit', compact('user', 'doc'));
    }

    /**
     * Delete the specified resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy($uid, $id)
    {
        $doc = UserDoc::findOrFail($id);

        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2("del.user.doc", $doc))
            return json_encode("failed");

        // Delete attached file
        if ($doc->attachment && file_exists(public_path('/filebank/user/' . $doc->user_id . '/docs/' . $doc->attachment)))
            unlink(public_path('/filebank/user/' . $doc->user_id . '/docs/' . $doc->attachment));

        $doc->closeToDo();
        $doc->delete();

        return json_encode('success');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store(UserDocRequest $request, $uid)
    {
        $user = User::find($uid);

        // Check authorisation and throw 404 if not
        //if (!Auth::user()->allowed2("add.company.doc"))
        //    return view('errors/404');

        $doc_request = request()->all();
        $doc_request['user_id'] = $user->id;
        $doc_request['company_id'] = $user->company_id;
        $doc_request['expiry'] = (request('expiry')) ? Carbon::createFromFormat('d/m/Y H:i', request('expiry') . '00:00')->toDateTimeString() : null;
        $doc_request['issued'] = (request('issued')) ? Carbon::createFromFormat('d/m/Y H:i', request('issued') . '00:00')->toDateTimeString() : null;

        // Convert licence type into CSV - Drivers/Contractors
        if (request('category_id') == '2') {
            $doc_request['ref_no'] = request('lic_no');
            $doc_request['ref_type'] = implode(',', request('drivers_type'));
        }

        // Convert Contractor licence type into CSV
        if (request('category_id') == '3') {
            $doc_request['ref_no'] = request('lic_no');
            $doc_request['ref_type'] = implode(',', request('cl_type'));
        }

        // Convert Supervisor licence type into CSV
        if (request('category_id') == '4') {
            $doc_request['ref_no'] = request('lic_no');
            $doc_request['ref_type'] = implode(',', request('super_type'));
        }

        // Reassign Asbestos Licence to correct category
        if (request('category_id') == '9')
            $doc_request['ref_type'] = request('asb_type');


        // Create User Doc
        $doc = UserDoc::create($doc_request);

        // Handle attached file
        if ($request->hasFile('singlefile') || $request->hasFile('singleimage')) {
            $file = ($request->hasFile('singlefile')) ? $request->file('singlefile') : $request->file('singleimage');

            $path = "filebank/user/" . $user->id . '/docs';
            $name = sanitizeFilename(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . strtolower($file->getClientOriginalExtension());
            // Ensure filename is unique by adding counter to similiar filenames
            $count = 1;
            while (file_exists(public_path("$path/$name")))
                $name = sanitizeFilename(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '-' . $count ++ . '.' . strtolower($file->getClientOriginalExtension());
            $file->move($path, $name);

            //dd($doc_request);
            $doc->attachment = $name;
            $doc->save();
        }
        Toastr::success("Uploaded document");

        // Closing any outstanding todoos associated with this doc category ie. expired docs
        $doc->closeToDo();

        // If uploaded by User with 'authorise' permissions set to active other set pending
        $doc->status = 2;
        $category = UserDocCategory::find($doc->category_id);
        $pub_pri = ($category->private) ? 'pri' : 'pub';
        //dd($category->type);
        if (Auth::user()->permissionLevel("sig.docs.$category->type.$pub_pri", $user->company->reportsTo()->id)) {
            $doc->approved_by = Auth::user()->id;
            $doc->approved_at = Carbon::now()->toDateTimeString();
            $doc->status = 1;
        } else {
            // Create approval ToDoo
            if ($doc->category->type == 'acc' || $doc->category->type == 'whs') {
                $doc_owner_notify = $doc->owned_by->notificationsUsersTypeArray('doc.' . $doc->category->type . '.approval');
                if (!$doc_owner_notify) // in cases of company without a subscription
                    $doc_owner_notify = ($doc->owned_by->primary_user) ? [$doc->owned_by->primary_contact()->id] : [];

                // Allow CapeCod to also approve if this doc is owned by a child of theirs
                $cc = Company::find(3);
                $cc_notify = [];
                if (in_array($doc->owned_by->id, flatten_array($cc->subCompanies(3))))
                    $cc_notify = $cc->notificationsUsersTypeArray('doc.' . $doc->category->type . '.approval');

                $doc->createApprovalToDo(array_merge($doc_owner_notify, $cc_notify));
            }
        }
        $doc->save();

        return redirect("user/$user->id/doc/upload");
    }

    /**
     * Update the specified resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function update(UserDocRequest $request, $uid, $id)
    {
        $user = User::find($uid);
        $doc = UserDoc::findOrFail($id);

        //dd($request->all());
        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2("edit.user.doc", $doc))
            return view('errors/404');

        $doc_request = request()->all();
        $doc_request['expiry'] = (request('expiry')) ? Carbon::createFromFormat('d/m/Y H:i', request('expiry') . '00:00')->toDateTimeString() : null;
        $doc_request['issued'] = (request('issued')) ? Carbon::createFromFormat('d/m/Y H:i', request('issued') . '00:00')->toDateTimeString() : null;

        // Convert licence type into CSV - Drivers/Contractors
        if (request('category_id') == '2') {
            $doc_request['ref_no'] = request('lic_no');
            $doc_request['ref_type'] = request('drivers_type');
        }

        // Convert Contractor licence type into CSV
        if (request('category_id') == '3') {
            $doc_request['ref_no'] = request('lic_no');
            $doc_request['ref_type'] = request('cl_type');
        }

        // Convert Supervisor licence type into CSV
        if (request('category_id') == '4') {
            $doc_request['ref_no'] = request('lic_no');
            $doc_request['ref_type'] = request('super_type');
        }

        // Reassign Asbestos Licence to correct category
        if (request('category_id') == '9')
            $doc_request['ref_type'] = request('asb_type');

        // Verify if document is rejected
        $doc_request['reject'] = '';
        if (request()->has('reject_doc')) {
            $doc->status = 3;
            $doc->reject = request('reject');
            $doc->save();
            $doc->closeToDo();
            $doc->emailReject();
            Toastr::error("Document rejected");

            return redirect("user/$user->id/doc/$doc->id/edit");
        }

        if ($doc->category_id < 21) {
            // Determine Status of Doc
            // If uploaded by User with 'authorise' permissions set to active otherwise set pending
            $company = Company::findOrFail($doc->company_id);
            $category = UserDocCategory::find($doc->category_id);
            $pub_pri = ($category->private) ? 'pri' : 'pub';
            if (request()->has('status') && request('status') == 0)
                $doc_request['status'] = 0;
            else if (Auth::user()->permissionLevel("sig.docs.$category->type.$pub_pri", $company->reportsTo()->id) || $company->primary_user == Auth::user()->id) {
                $doc_request['approved_by'] = Auth::user()->id;
                $doc_request['approved_at'] = Carbon::now()->toDateTimeString();
                $doc_request['status'] = 1;
            } else {
                $doc_request['status'] = 2;
            }
        }

        //dd($doc_request);
        $doc->update($doc_request);

        // Close any ToDoo and create new one
        if ($doc->category_id < 21) {
            $doc->closeToDo();
            // Create approval ToDoo
            if ($doc->status == 2 && ($doc->category->type == 'acc' || $doc->category->type == 'whs')) {
                $doc_owner_notify = $doc->owned_by->notificationsUsersTypeArray('doc.' . $doc->category->type . '.approval');
                if (!$doc_owner_notify) // in cases of company without a subscription
                    $doc_owner_notify = ($doc->owned_by->primary_user) ? [$doc->owned_by->primary_contact()->id] : [];

                // Allow CapeCod to also approve if this doc is owned by a child of theirs
                $cc = Company::find(3);
                $cc_notify = [];
                if (in_array($doc->owned_by->id, flatten_array($cc->subCompanies(3))))
                    $cc_notify = $cc->notificationsUsersTypeArray('doc.' . $doc->category->type . '.approval');

                $doc->createApprovalToDo(array_merge($doc_owner_notify, $cc_notify));
            }
        }

        // Handle attached file
        if (request()->hasFile('singlefile')) {
            // Delete previous file
            if ($doc->attachment && file_exists(public_path('filebank/user/' . $doc->user_id . '/docs/' . $doc->attachment)))
                unlink(public_path('filebank/user/' . $doc->user_id . '/docs/' . $doc->attachment));

            $file = $request->file('singlefile');
            $path = "filebank/user/" . $doc->user_id . '/docs';
            $name = sanitizeFilename(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . strtolower($file->getClientOriginalExtension());
            // Ensure filename is unique by adding counter to similiar filenames
            $count = 1;
            while (file_exists(public_path("$path/$name")))
                $name = sanitizeFilename(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '-' . $count ++ . '.' . strtolower($file->getClientOriginalExtension());
            $file->move($path, $name);
            $doc->attachment = $name;
            $doc->save();
        }
        Toastr::success("Updated document");

        return redirect("user/$user->id/doc/$doc->id/edit");
    }

    /**
     * Reject the specified company document in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function reject($uid, $id)
    {
        $user = User::find($uid);
        $doc = UserDoc::findOrFail($id);

        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2("sig.user.doc", $doc))
            return view('errors/404');

        //dd(request()->all());
        $doc->status = 3;
        $doc->reject = request('reject');
        $doc->closeToDo();
        $doc->emailReject();
        $doc->save();

        Toastr::success("Updated document");

        return redirect("user/$user->id/doc/$doc->id/edit");
    }

    /**
     * Approve / Unarchive the specified company document.
     *
     * @return \Illuminate\Http\Response
     */
    public function archive($uid, $id)
    {
        $user = User::find($uid);
        $doc = UserDoc::findOrFail($id);

        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2("del.user.doc", $doc))
            return view('errors/404');

        //dd(request()->all());
        ($doc->status == 1) ? $doc->status = 0 : $doc->status = 1;
        $doc->closeToDo();
        $doc->save();

        if ($doc->status == 1)
            Toastr::success("Document restored");
        else {
            Toastr::success("Document achived");
        }

        return redirect("user/$user->id/doc/$doc->id/edit");
    }


    /**
     * Get Categories Users is allowed to access filtered by Department.
     */
    public function getCategories($cid, $department)
    {
        $company = Company::find($cid);
        $categories = array_keys(Auth::user()->companyDocTypeSelect('view', $company));

        if ($department != 'all') {
            $filtered = []; //['ALL' => 'All categories'];
            if ($categories) {
                foreach ($categories as $cat) {
                    $category = CompanyDocCategory::find($cat);
                    if ($category && $category->type == $department)
                        $filtered[$cat] = $category->name;
                }
                $categories = $filtered;
            }
        }

        return json_encode($categories);
    }

    /**
     * Get Docs current user is authorised to manage + Process datatables ajax request.
     */
    public function getDocs($uid)
    {
        //$user = User::find($uid);

        /*
        $categories = (request('category_id') == 'ALL') ? array_keys(Auth::user()->companyDocTypeSelect('view', $company)) : [request('category_id')];

        if (request('department') != 'all') {
            $filtered = [];
            if ($categories) {
                foreach ($categories as $cat) {
                    $category = CompanyDocCategory::find($cat);
                    if ($category && $category->type == request('department'))
                        $filtered[] = $cat;
                }
                $categories = $filtered;
            }
        }*/

        $status = (request('status') == 0) ? [0] : [1, 2, 3];
        $records = UserDoc::where('user_id', $uid)
            //->whereIn('category_id', $categories)
            ->whereIn('status', $status)->orderBy('category_id')->get();


        $dt = Datatables::of($records)
            ->editColumn('id', function ($doc) {
                return ($doc->attachment) ? '<div class="text-center"><a href="' . $doc->attachment_url . '" target="_blank"><i class="fa fa-file-text-o"></i></a></div>' : '';
            })
            ->editColumn('category_id', function ($doc) {

                return strtoupper($doc->category->type);
            })
            ->addColumn('details', function ($doc) {
                $details = '';

                if (in_array($doc->category_id, [2, 3])) // Drivers + CL
                    $details .= "Licence No: $doc->ref_no";
                if (in_array($doc->category_id, [6, 7, 10]) || $doc->category_id > 10) // FirstAid + Training + Apprentice + Other
                    $details .= "$doc->ref_name";

                return ($details == '') ? '-' : $details;
            })
            ->editColumn('name', function ($doc) {
                if ($doc->status == 2)
                    return $doc->name . " <span class='badge badge-warning badge-roundless'>Pending Approval</span>";
                if ($doc->status == 3)
                    return $doc->name . " <span class='badge badge-danger badge-roundless'>Rejected</span>";

                return $doc->name;
            })
            ->editColumn('issued', function ($doc) {
                return ($doc->issued) ? $doc->issued->format('d/m/Y') : '-';
            })
            ->editColumn('expiry', function ($doc) {
                return ($doc->expiry) ? $doc->expiry->format('d/m/Y') : '-';
            })
            ->addColumn('action', function ($doc) {
                $actions = '';
                $type = $doc->type;
                $user = User::find($doc->user_id);
                $expiry = ($doc->expiry) ? $doc->expiry->format('d/m/Y') : '';

                if (Auth::user()->allowed2("edit.user.doc", $doc))
                   $actions .= '<a href="/user/' . $user->id . '/doc/' . $doc->id . '/edit' . '" class="btn blue btn-xs btn-outline sbold uppercase margin-bottom"><i class="fa fa-pencil"></i> Edit</a>';
                //elseif (Auth::user()->allowed2("view.company.doc", $doc))
                $actions .= '<a href="/user/' . $user->id . '/doc/' . $doc->id . '" class="btn blue btn-xs btn-outline sbold uppercase margin-bottom"><i class="fa fa-search"></i> View</a>';

                if (Auth::user()->allowed2("del.user.doc", $doc))
                    $actions .= '<button class="btn dark btn-xs sbold uppercase margin-bottom btn-delete " data-remote="/user/' . $doc->user_id . '/doc/' . $doc->id . '" data-name="' . $doc->name . '"><i class="fa fa-trash"></i></button>';


                return $actions;
            })
            ->rawColumns(['id', 'name', 'details', 'action'])
            ->make(true);

        return $dt;
    }
}
