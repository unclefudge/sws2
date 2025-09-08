<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Http\Requests\Site\SiteDocRequest;
use App\Models\Site\SiteDoc;
use Carbon\Carbon;
use DB;
use File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use nilsenj\Toastr\Facades\Toastr;
use Session;
use Validator;
use Yajra\Datatables\Datatables;

/**
 * Class SitePlanController
 * @package App\Http\Controllers
 */
class SiteDocController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if (!Auth::user()->hasAnyPermissionType('site.doc'))
            return view('errors/404');

        $site_id = $type = '';

        return view('site/doc/list', compact('site_id', 'type'));
    }

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
    public function create()
    {
        // Check authorisation and throw 404 if not
        if (!(Auth::user()->allowed2('add.safety.doc') || Auth::user()->allowed2('add.site.doc')))
            return view('errors/404');

        $site_id = request('site_id');
        $type = request('type');

        return view('site/doc/create', compact('site_id', 'type'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function createPlan()
    {
        // Check authorisation and throw 404 if not
        if (!(Auth::user()->allowed2('add.safety.doc') || Auth::user()->allowed2('add.site.doc')))
            return view('errors/404');

        $site_id = request('site_id');
        $type = 'plan';

        return view('site/doc/plan/create', compact('site_id', 'type'));
    }

    /**
     * Delete the specified resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        $doc = SiteDoc::findOrFail($id);

        // Check authorisation and throw 404 if not
        if ($doc->type == 'PLAN' && !Auth::user()->allowed2('del.site.doc', $doc))
            return json_encode('failed1');
        else if ($doc->type != 'PLAN' && !Auth::user()->allowed2('del.safety.doc', $doc))
            return json_encode('failed2');


        // Log the delete action
        if (!file_exists(public_path("/filebank/log"))) {
            mkdir(public_path("/filebank/log"));
            File::put(public_path('/filebank/log/sitedocs.txt'), "Site Docs\n---------\n\n");
        }
        $log = Carbon::now()->format('d/m/Y G:i:s') . " - " . Auth::user()->username . " DELETED ID:$doc->id Site:$doc->site_id (" . $doc->site->name . ") Name:$doc->name\n";
        File::append(public_path('filebank/log/sitedocs.txt'), $log);

        // Delete file
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

        $site_id = request('site_id');
        $type = request('type');

        // Redirect on 'back' button
        if ($request->has('back'))
            return view('/site/doc/list', compact('site_id', 'type'));

        $doc_request = $request->all();

        // Create Site Doc
        $doc = SiteDoc::create($doc_request);

        // Handle attached file
        if ($request->hasFile('singlefile')) {
            $file = $request->file('singlefile');

            $path = "filebank/site/" . $doc->site_id . '/docs';
            $name = sanitizeFilename(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . strtolower($file->getClientOriginalExtension());
            // Ensure filename is unique by adding counter to similiar filenames
            $count = 1;
            while (file_exists(public_path("$path/$name")))
                $name = $doc->site_id . '-' . sanitizeFilename(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '-' . $count++ . '.' . strtolower($file->getClientOriginalExtension());
            $file->move($path, $name);
            $doc->attachment = $name;
            $doc->save();

            // Dial Before Dig
            if (stripos(strtolower(request('name')), 'dial before you dig') !== false) {
                $doc->closeToDoTask('dial_before_dig');
            }
        }
        Toastr::success("Created document");

        $previous_url = parse_url(url()->previous());
        if ($previous_url['path'] == '/site/doc/plan/create')
            return view('site/doc/plan/list', compact('site_id', 'type'));

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

        // Handle file upload
        if ($request->hasFile('multifile')) {
            $files = $request->file('multifile');
            foreach ($files as $file) {
                $path = "filebank/site/" . $request->get('site_id') . '/docs';
                $name = $request->get('site_id') . '-' . sanitizeFilename(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . strtolower($file->getClientOriginalExtension());

                // Ensure filename is unique by adding counter to similiar filenames
                $count = 1;
                while (file_exists(public_path("$path/$name")))
                    $name = $request->get('site_id') . '-' . sanitizeFilename(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '-' . $count++ . '.' . strtolower($file->getClientOriginalExtension());
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
        $orig_attachment = $doc->attachmentUrl;

        //dd($request->all());
        $doc_request = $request->only('name', 'type', 'site_id', 'notes');
        $doc->update($doc_request);

        // if doc has altered 'site_id' move the file to the new file location
        if ($doc->site_id != $orig_site) {
            // Make directory if non-existant
            if (!file_exists(public_path(pathinfo($doc->attachmentUrl, PATHINFO_DIRNAME))))
                mkdir(public_path(pathinfo($doc->attachmentUrl, PATHINFO_DIRNAME), 0755));
            rename(public_path($orig_attachment), public_path($doc->attachmentUrl));
            $orig_attachment = $doc->attachmentUrl;
        }

        // Handle attached file
        if ($request->hasFile('uploadfile')) {
            $file = $request->file('uploadfile');

            $path = "filebank/site/" . $doc->site_id . '/docs';
            $name = sanitizeFilename(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . strtolower($file->getClientOriginalExtension());

            // Ensure filename is unique by adding counter to similiar filenames
            $count = 1;
            while (file_exists(public_path("$path/$name")))
                $name = $doc->site_id . '-' . sanitizeFilename(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '-' . $count++ . '.' . strtolower($file->getClientOriginalExtension());

            $file->move($path, $name);
            $doc->attachment = $name;
            $doc->save();

            // Delete previous file
            if (file_exists(public_path($orig_attachment)))
                unlink(public_path($orig_attachment));

            // Dial Before Dig
            if (stripos(strtolower(request('name')), 'dial before you dig') !== false) {
                $doc->closeToDoTask('dial_before_dig');
            }
        }
        Toastr::success("Updated document");

        return view('site/doc/edit', compact('doc', 'site_id', 'type'));
    }

    /**
     * Get Site Docs current user is authorised to manage + Process datatables ajax request.
     */
    public function getDocs()
    {
        //dd(request()->all());
        $site_id_active = (request('site_id_active') == 'all') ? '' : request('site_id_active');
        $site_id_completed = (request('site_id_completed') == 'all') ? '' : request('site_id_completed');
        $site_id_upcoming = (request('site_id_upcoming') == 'all') ? '' : request('site_id_upcoming');

        if (request('status') == 1)
            $allowedSites = ($site_id_active) ? [$site_id_active] : Auth::user()->company->sites(1)->pluck('id')->toArray();
        elseif (request('status') == '0')
            $allowedSites = ($site_id_completed) ? [$site_id_completed] : Auth::user()->company->sites(0)->pluck('id')->toArray();
        else
            $allowedSites = ($site_id_upcoming) ? [$site_id_upcoming] : Auth::user()->company->sites(-1)->pluck('id')->toArray();

        //dd($allowedSites);
        $type = request('type');
        if ($type == 'ALL')
            $records = DB::table('site_docs as d')
                ->select(['d.id', 'd.type', 'd.site_id', 'd.attachment', 'd.name', 'd.updated_at', 's.id as sid', 's.name as site_name'])
                ->join('sites as s', 'd.site_id', '=', 's.id')
                ->whereIn('site_id', $allowedSites)
                ->where('d.status', '1');
        else
            $records = DB::table('site_docs as d')
                ->select(['d.id', 'd.type', 'd.site_id', 'd.attachment', 'd.name', 'd.updated_at', 's.id as sid', 's.name as site_name'])
                ->join('sites as s', 'd.site_id', '=', 's.id')
                ->where('d.type', $type)
                ->whereIn('site_id', $allowedSites)
                ->where('d.status', '1');

        //dd($records);

        $dt = Datatables::of($records)
            ->editColumn('id', '<div class="text-center"><a href="/filebank/site/{{$site_id}}/docs/{{$attachment}}"><i class="fa fa-file-text-o"></i></a></div>')
            ->addColumn('updatedDate', function ($doc) {
                $record = SiteDoc::find($doc->id);
                if (record)
                    return $record->updated_at->format('d/m/Y');
                return "";
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
            ->rawColumns(['id', 'action'])
            ->make(true);

        return $dt;
    }


    /**
     * Display a listing of docs.
     *
     * @return \Illuminate\Http\Response
     */
    public function listDocs($type)
    {
        if (!Auth::user()->hasPermission2('view.safety.doc') && !Auth::user()->hasPermission2('view.site.doc'))
            return view('errors/404');

        $site_id = (Session::has('siteID')) ? Session::get('siteID') : '';

        return view("site/doc/$type/list", compact('site_id'));
    }

    /**
     * Get Risks current user is authorised to manage + Process datatables ajax request.
     */
    public function getDocsType($type)
    {
        //dd(request()->all());
        $status = (request('status')) ? request('status') : 1;
        $site_id = ($status == 1) ? request('site_id') : request('site_id2');
        $records = SiteDoc::select(['id', 'type', 'site_id', 'attachment', 'name',])
            ->where('type', $type)
            ->where('site_id', '=', $site_id)
            ->where('status', 1);

        $dt = Datatables::of($records)
            ->editColumn('id', '<div class="text-center"><a href="/filebank/site/{{$site_id}}/docs/{{$attachment}}" target="_blank"><i class="fa fa-file-text-o"></i></a></div>')
            ->rawColumns(['id', 'action'])
            ->make(true);

        return $dt;
    }
}
