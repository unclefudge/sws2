<?php

namespace App\Http\Controllers\Misc;

use Illuminate\Http\Request;
use Validator;

use DB;
use Session;
use App\Models\Site\Site;
use App\Models\Site\SiteDoc;
use App\Http\Requests;
use App\Http\Requests\Site\SiteDocRequest;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Yajra\Datatables\Datatables;
use nilsenj\Toastr\Facades\Toastr;
use Carbon\Carbon;

/**
 * Class FileUploadController
 * @package App\Http\Controllers
 */
class FileController extends Controller {

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if (!(Auth::user()->hasAnyPermission2('add.site.doc|edit.site.doc|del.site.doc|add.safety.doc|edit.safety.doc|del.safety.doc')))
            return view('errors/404');

        $site_id = $type = '';

        return view('manage/file/index', compact('site_id', 'type'));
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
		 /*
    public function fileDirectory(Request $request)
    {
        if (!(Auth::user()->company->subscription && Auth::user()->allowed2('view.upload.manager')))
            return view('errors/404');

        $site_id = $type = '';

        return view('manage/file/directory', compact('site_id', 'type'));
    }*/

    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $doc = SiteDoc::findorFail($id);

        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('edit.site.doc', $doc))
            return view('errors/404');

        $site_id = $doc->site_id;
        if ($doc->type == 'RISK') $type = 'risk';
        if ($doc->type == 'HAZ') $type = 'hazard';
        if ($doc->type == 'PLAN') $type = 'plan';

        return view('site/doc/edit', compact('doc', 'site_id', 'type'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        // Check authorisation and throw 404 if not
        if (!(Auth::user()->allowed2('add.safety.doc') || Auth::user()->allowed2('add.site.doc')))
            return view('errors/404');

        $site_id = $request->get('site_id');
        $type = $request->get('type');

        return view('site/doc/create', compact('site_id', 'type'));

        return view('errors/404');
    }

    /**
     * Delete the specified resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        $doc = SiteDoc::findOrFail($id);

        // Delete previous file
        if (file_exists(public_path($doc->attachmentUrl)))
            unlink(public_path($doc->attachmentUrl));
        $doc->delete();

        return json_encode('success');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store(SiteDocRequest $request)
    {
        // Check authorisation and throw 404 if not
        if (!(Auth::user()->allowed2('add.safety.doc') || Auth::user()->allowed2('add.site.doc')))
            return view('errors/404');

        $site_id = $request->get('site_id');
        $type = $request->get('type');

        // Redirect on 'back' button
        if ($request->has('back'))
            return view('/site/doc/list', compact('site_id', 'type'));

        $doc_request = $request->all();

        // Create Site Doc
        $doc = SiteDoc::create($doc_request);

        if ($doc->type == 'RISK') $type = 'risk';
        if ($doc->type == 'HAZ') $type = 'hazard';
        if ($doc->type == 'PLAN') $type = 'plan';

        // Handle attached file
        if ($request->hasFile('singlefile')) {
            $file = $request->file('singlefile');

            $path = "filebank/site/" . $doc->site_id . '/' . $type;
            $name = sanitizeFilename(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . strtolower($file->getClientOriginalExtension());
            // Ensure filename is unique by adding counter to similiar filenames
            $count = 1;
            while (file_exists(public_path("$path/$name")))
                $name = $doc->site_id . '-' . sanitizeFilename(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '-' . $count ++ . '.' . strtolower($file->getClientOriginalExtension());
            $file->move($path, $name);
            $doc->attachment = $name;
            $doc->save();
        }
        Toastr::success("Created document");

        return view('site/doc/list', compact('site_id', 'type'));
    }

    /**
     * Upload File + Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function upload(Request $request)
    {
        // Check authorisation and throw 404 if not
        if (!(Auth::user()->allowed2('add.safety.doc') || Auth::user()->allowed2('add.site.doc')))
            return json_encode("failed");

        if ($request->get('type') == 'RISK') $type = 'risk';
        if ($request->get('type') == 'HAZ') $type = 'hazard';
        if ($request->get('type') == 'PLAN') $type = 'plan';

        // Handle file upload
        if ($request->hasFile('multifile')) {
            $files = $request->file('multifile');
            foreach ($files as $file) {
                $path = "filebank/site/" . $request->get('site_id') . '/' . $type;
                $name = $request->get('site_id') . '-' . sanitizeFilename(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . strtolower($file->getClientOriginalExtension());

                // Ensure filename is unique by adding counter to similiar filenames
                $count = 1;
                while (file_exists(public_path("$path/$name")))
                    $name = $request->get('site_id') . '-' . sanitizeFilename(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '-' . $count ++ . '.' . strtolower($file->getClientOriginalExtension());
                $file->move($path, $name);

                $doc_request = $request->only('type', 'site_id');
                $doc_request['name'] = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $doc_request['company_id'] = Auth::user()->company_id;

                // Create Site Doc
                $doc = SiteDoc::create($doc_request);
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
    public function update(SiteDocRequest $request, $id)
    {
        $site_id = $request->get('site_id');
        $type = $request->get('type');

        // Redirect on 'back' button
        if ($request->has('back'))
            return view('/site/doc/list', compact('site_id', 'type'));

        $doc = SiteDoc::findOrFail($id);

        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('edit.site.doc', $doc))
            return view('errors/404');

        // Get Original report filename path
        $orig_site = $doc->site_id;
        $orig_type = $doc->type;
        $orig_attachment = $doc->attachmentUrl;

        //dd($request->all());
        $doc_request = $request->only('name', 'type', 'site_id', 'notes');
        $doc->update($doc_request);

        // if doc has altered 'site_id' or 'type' move the file to the new file location
        if ($doc->type != $orig_type || $doc->site_id != $orig_site) {
            // Make directory if non-existant
            if (!file_exists(public_path(pathinfo($doc->attachmentUrl, PATHINFO_DIRNAME))))
                mkdir(public_path(pathinfo($doc->attachmentUrl, PATHINFO_DIRNAME), 0755));
            rename(public_path($orig_attachment), public_path($doc->attachmentUrl));
            $orig_attachment = $doc->attachmentUrl;
        }

        if ($doc->type == 'RISK') $type = 'risk';
        if ($doc->type == 'HAZ') $type = 'hazard';
        if ($doc->type == 'PLAN') $type = 'plan';

        // Handle attached file
        if ($request->hasFile('uploadfile')) {
            $file = $request->file('uploadfile');

            $path = "filebank/site/" . $doc->site_id . '/' . $type;
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

        return view('site/doc/edit', compact('doc', 'site_id', 'type'));
    }

    /**
     * Get Site Docs current user is authorised to manage + Process datatables ajax request.
     */
    public function getDocs(Request $request)
    {

        $type = $request->get('type');
        if ($type == 'ALL')
            $records = DB::table('site_docs as d')
                ->select(['d.id', 'd.type', 'd.site_id', 'd.attachment', 'd.name', 's.id as sid', 's.name as site_name'])
                ->join('sites as s', 'd.site_id', '=', 's.id')
                ->whereIn('s_id', $allowedSites)
                ->where('d.status', '1');
        else
            $records = DB::table('site_docs as d')
                ->select(['d.id', 'd.type', 'd.site_id', 'd.attachment', 'd.name', 's.id as sid', 's.name as site_name'])
                ->join('sites as s', 'd.site_id', '=', 's.id')
                ->where('d.type', $type)
                ->whereIn('site_id', $allowedSites)
                ->where('d.status', '1');

        $dt = Datatables::of($records)
            ->editColumn('id', function ($doc) {
                $type = '';
                if ($doc->type == 'RISK') $type = 'risk';
                if ($doc->type == 'HAZ') $type = 'hazard';
                if ($doc->type == 'PLAN') $type = 'plan';

                return '<div class="text-center"><a href="/filebank/site/' . $doc->site_id . '/' . $type . '/' . $doc->attachment . '" target="_blank"><i class="fa fa-file-text-o"></i></a></div>';
            })
            ->addColumn('action', function ($doc) {
                $record = SiteDoc::find($doc->id);
                $actions = '';

                if ($doc->type == 'PLAN') {
                    if (Auth::user()->allowed2('edit.site.doc', $record))
                        $actions .= '<a href="/site/doc/' . $doc->id . '" class="btn blue btn-xs btn-outline sbold uppercase margin-bottom"><i class="fa fa-pencil"></i> Edit</a>';
                    if (Auth::user()->allowed2('del.site.doc', $record))
                        $actions .= '<button class="btn dark btn-xs sbold uppercase margin-bottom btn-delete " data-remote="/site/doc/' . $doc->id . '" data-name="' . $doc->name . '"><i class="fa fa-trash"></i></button>';
                } else {
                    if (Auth::user()->allowed2('edit.safety.doc', $record))
                        $actions .= '<a href="/site/doc/' . $doc->id . '" class="btn blue btn-xs btn-outline sbold uppercase margin-bottom"><i class="fa fa-pencil"></i> Edit</a>';
                    if (Auth::user()->allowed2('del.safety.doc', $record))
                        $actions .= '<button class="btn dark btn-xs sbold uppercase margin-bottom btn-delete " data-remote="/site/doc/' . $doc->id . '" data-name="' . $doc->name . '"><i class="fa fa-trash"></i></button>';
                }


                return $actions;
            })
            ->make(true);

        return $dt;
    }

    /**
     * Display a listing of risks.
     *
     * @return \Illuminate\Http\Response
     */
    public function listRisks()
    {
        if (!Auth::user()->hasPermission2('view.safety.doc'))
            return view('errors/404');

        $site_id = (Session::has('siteID')) ? Session::get('siteID') : '';

        return view('site/doc/risk/list', compact('site_id'));
    }

    /**
     * Get Risks current user is authorised to manage + Process datatables ajax request.
     */
    public function getRisks(Request $request)
    {
        $records = SiteDoc::select(['id', 'type', 'site_id', 'attachment', 'name',])
            ->where('type', 'RISK')
            ->where('site_id', '=', $request->get('site_id'))
            ->where('status', '1');

        $dt = Datatables::of($records)
            ->editColumn('id', '<div class="text-center"><a href="/filebank/site/{{$site_id}}/risk/{{$attachment}}"><i class="fa fa-search"></i></a></div>')
            ->editColumn('id', function ($doc) {
                ($doc->type == 'RISK') ? $type = 'risk' : $type = 'hazard';

                return '<div class="text-center"><a href="/filebank/site/' . $doc->site_id . '/' . $type . '/' . $doc->attachment . '"><i class="fa fa-file-text-o"></i></a></div>';
            })
            ->make(true);

        return $dt;
    }

    /**
     * Display a listing of hazards.
     *
     * @return \Illuminate\Http\Response
     */
    public function listHazards()
    {
        if (!Auth::user()->hasPermission2('view.safety.doc'))
            return view('errors/404');

       $site_id = (Session::has('siteID')) ? Session::get('siteID') : '';

        return view('site/doc/hazard/list', compact('site_id'));
    }


    /**
     * Get Hazards current user is authorised to manage + Process datatables ajax request.
     */
    public function getHazards(Request $request)
    {
        $records = SiteDoc::select(['id', 'type', 'site_id', 'attachment', 'name',])
            ->where('type', 'HAZ')
            ->where('site_id', '=', $request->get('site_id'))
            ->where('status', '1');

        $dt = Datatables::of($records)
            ->editColumn('id', '<div class="text-center"><a href="/filebank/site/{{$site_id}}/risk/{{$attachment}}"><i class="fa fa-search"></i></a></div>')
            ->editColumn('id', function ($doc) {
                ($doc->type == 'RISK') ? $type = 'risk' : $type = 'hazard';

                return '<div class="text-center"><a href="/filebank/site/' . $doc->site_id . '/' . $type . '/' . $doc->attachment . '"><i class="fa fa-file-text-o"></i></a></div>';
            })
            ->make(true);

        return $dt;
    }

    /**
     * Display a listing of plans.
     *
     * @return \Illuminate\Http\Response
     */
    public function listPlans()
    {
        if (!Auth::user()->hasPermission2('view.site.doc'))
            return view('errors/404');

        $site_id = (Session::has('siteID')) ? Session::get('siteID') : '';

        return view('site/doc/plan/list', compact('site_id'));
    }

    /**
     * Get Plans current user is authorised to view + Process datatables ajax request.
     */
    public function getPlans(Request $request)
    {
        $records = SiteDoc::select(['id', 'type', 'site_id', 'attachment', 'name',])
            ->where('type', 'PLAN')
            ->where('site_id', '=', $request->get('site_id'))
            ->where('status', '1');

        $dt = Datatables::of($records)
            ->editColumn('id', '<div class="text-center"><a href="/filebank/site/{{$site_id}}/risk/{{$attachment}}"><i class="fa fa-search"></i></a></div>')
            ->editColumn('id', function ($doc) {
                return '<div class="text-center"><a href="/filebank/site/' . $doc->site_id . '/plan/' . $doc->attachment . '"><i class="fa fa-file-text-o"></i></a></div>';
            })
            ->make(true);

        return $dt;
    }

    public function serveFile ($file)
    {
        $file = Storage::disk('local')->get('files/'.$file);

        //dd($contents);

        /*
        $storagePath = storage_path('app/files/'.$file->url);
        //$storagePath = '/files/'.$file;
        echo 'path:'.$storagePath."<br>";
        if (file_exists($storagePath))
            echo 'exists<br>';
        else
            echo 'not<br>';

        //dd($storagePath);
        if( ! File::exists($storagePath)){
            //return view('errors.404');
            echo 'NOT<br>';
        }
        $mimeType = mime_content_type($storagePath);
        $headers = array(
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline; filename="'.$file.'"'
        );
        return Response::make(file_get_contents($storagePath), 200, $headers);

*/


        $path = storage_path('app/files/'.$file->url);
        $mime = File::mimeType($path);

        if( ! File::exists($path)){
            //abort(404);
            return view('errorpages.404');
        }

        return response(
            File::get($path), 200, [
                'Content-Type' => $mime,
                'Content-Disposition' => 'inline; '.$file->url
            ]
        );

    }
}
