<?php

namespace App\Http\Controllers\Safety;

use Illuminate\Http\Request;
use Validator;

use DB;
use Session;
use App\Models\Safety\SafetyDoc;
use App\Models\Safety\SafetyDataSheet;
use App\Models\Safety\SafetyDocCategory;
use App\Http\Requests;
use App\Http\Requests\Safety\SdsRequest;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Yajra\Datatables\Datatables;
use nilsenj\Toastr\Facades\Toastr;

/**
 * Class SdsController
 * @package App\Http\Controllers\Safety
 */
class SafetyDocController extends Controller {

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Check authorisation and throw 404 if not
        if (!Auth::user()->hasAnyPermissionType('sds'))
            return view('errors/404');

        $category_id = '';

        return view('safety/doc/sds/list', compact('category_id'));
    }

    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('add.sds'))
            return view('errors/404');

        $category_id = request('category_id');

        return view('safety/doc/sds/create', compact('category_id'));
    }

    /**
     * Edit the specified resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $doc = SafetyDoc::findOrFail($id);

        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('edit.sds', $doc))
            return view('errors/404');

        return view('safety/doc/sds/edit', compact('doc'));
    }

    /**
     * Delete the specified resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $doc = SafetyDoc::findOrFail($id);

        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('del.sds', $doc))
            return json_encode("failed");

        // Delete attached file
        if (file_exists(public_path('/filebank/whs/sds/' . $doc->attachment)))
            unlink(public_path('/filebank/whs/sds/' . $doc->attachment));
        $doc->delete();

        return json_encode('success');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store(SdsRequest $request)
    {
        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('add.sds'))
            return view('errors/404');

        $category_id = $request->get('category_id');

        // Redirect on 'back' button
        if ($request->has('back'))
            return view('/safety/doc/sds/list', compact('category_id'));

        $doc_request = $request->all();

        // Create SDS Doc
        $doc = SafetyDoc::create($doc_request);

        // Handle attached file
        if ($request->hasFile('singlefile')) {
            $file = $request->file('singlefile');

            $path = "filebank/whs/sds";
            $name = sanitizeFilename(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . strtolower($file->getClientOriginalExtension());
            // Ensure filename is unique by adding counter to similiar filenames
            $count = 1;
            while (file_exists(public_path("$path/$name")))
                $name = sanitizeFilename(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '-' . $count ++ . '.' . strtolower($file->getClientOriginalExtension());
            $file->move($path, $name);
            $doc->attachment = $name;
            $doc->save();
        }
        Toastr::success("Created document");

        return view('safety/doc/sds/list', compact('category_id'));
    }

    /**
     * Upload File + Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function upload(Request $request)
    {
        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('add.sds'))
            return json_encode("failed");

        // Handle file upload
        if ($request->hasFile('multifile')) {
            $files = $request->file('multifile');
            foreach ($files as $file) {
                $path = "filebank/whs/sds";
                $name = sanitizeFilename(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . strtolower($file->getClientOriginalExtension());

                // Ensure filename is unique by adding counter to similiar filenames
                $count = 1;
                while (file_exists(public_path("$path/$name")))
                    $name = sanitizeFilename(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '-' . $count ++ . '.' . strtolower($file->getClientOriginalExtension());
                $file->move($path, $name);

                $doc_request['type'] = 'SDS';
                $doc_request['category_id'] = $request->get('category_id');
                $doc_request['name'] = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $doc_request['company_id'] = Auth::user()->company_id;

                // Create Site Doc
                $doc = SafetyDoc::create($doc_request);
                $doc->attachment = $name;
                $doc->save();
            }
        }

        return json_encode("success");
    }

    /**
     * Update the specified resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function update(SdsRequest $request, $id)
    {
        $category_id = $request->get('category_id');

        // Redirect on 'back' button
        if ($request->has('back'))
            return view('/safety/doc/sds/list', compact('category_id'));

        $doc = SafetyDoc::findOrFail($id);

        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('edit.sds', $doc))
            return view('errors/404');

        //dd($request->all());
        $doc_request = $request->only('name', 'category_id', 'notes');
        $doc->update($doc_request);

        // Handle attached file
        if ($request->hasFile('uploadfile')) {
            $file = $request->file('uploadfile');
            $orig_attachment = "filebank/whs/sds/" . $doc->attachment;

            $path = "filebank/whs/sds";
            $name = sanitizeFilename(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . strtolower($file->getClientOriginalExtension());

            // Ensure filename is unique by adding counter to similiar filenames
            $count = 1;
            while (file_exists(public_path("$path/$name")))
                $name = $doc->site_id . '-' . sanitizeFilename(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '-' . $count ++ . '.' . strtolower($file->getClientOriginalExtension());

            $file->move($path, $name);
            $doc->attachment = $name;
            $doc->save();

            // Delete previous file
            if (file_exists(public_path($orig_attachment)))
                unlink(public_path($orig_attachment));
        }
        Toastr::success("Updated document");

        return view('safety/doc/sds/edit', compact('doc'));
    }


    /**
     * Get Docs current user is authorised to manage + Process datatables ajax request.
     */
    public function getSDS()
    {
        if (request('category_id') && request('category_id') != ' ')
            $category_list = [request('category_id')];
        else
            $category_list = SafetyDocCategory::pluck('id')->toArray();

        //$company_list = [Auth::user()->company_id, Auth::user()->company->reportsTo()->id];
        $records = DB::table('safety_docs as d')
            ->select(['d.id', 'd.type', 'd.category_id', 'd.attachment', 'd.name', 'c.id as cid', 'c.name as cat_name'])
            ->join('safety_docs_categories as c', 'd.category_id', '=', 'c.id')
            ->where('d.type', 'SDS')
            ->whereIn('d.category_id', $category_list)
            //->whereIn('d.company_id', $company_list)
            ->where('d.status', '1');

        $dt = Datatables::of($records)
            ->editColumn('id', '<div class="text-center"><a href="/filebank/whs/sds/{{$attachment}}"><i class="fa fa-file-text-o"></i></a></div>')
            ->addColumn('action', function ($doc) {
                $record = SafetyDoc::find($doc->id);
                $actions = '';

                if (Auth::user()->allowed2('edit.sds', $record))
                //if (Auth::user()->hasPermission2('edit.sds'))
                    $actions .= '<a href="/safety/doc/sds/' . $doc->id . '/edit' . '" class="btn blue btn-xs btn-outline sbold uppercase margin-bottom"><i class="fa fa-pencil"></i> Edit</a>';
                if (Auth::user()->allowed2('del.sds', $record))
                //if (Auth::user()->hasPermission2('del.sds'))
                    $actions .= '<button class="btn dark btn-xs sbold uppercase margin-bottom btn-delete " data-remote="/safety/doc/sds/' . $doc->id . '" data-name="' . $doc->name . '"><i class="fa fa-trash"></i></button>';

                return $actions;
            })
            ->rawColumns(['id', 'action'])
            ->make(true);

        return $dt;
    }
}
