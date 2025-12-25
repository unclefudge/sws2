<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Http\Requests\Site\SiteDocRequest;
use App\Models\Site\SiteDoc;
use App\Services\FileBank;
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
            return response()->json('failed1');
        else if ($doc->type != 'PLAN' && !Auth::user()->allowed2('del.safety.doc', $doc))
            return response()->json('failed2');


        // Log the delete action
        $logDir = storage_path('app/logs');
        $logFile = $logDir . '/sitedocs.txt';
        if (!is_dir($logDir)) mkdir($logDir, 0755, true);

        $log = now()->format('d/m/Y G:i:s') . " - " . Auth::user()->username . " DELETED ID:$doc->id Site:$doc->site_id ({$doc->site->name}) Name:$doc->name\n";
        File::append($logFile, $log);

        // Delete file
        if ($doc->attachment)
            FileBank::delete("construction/doc/standards/{$doc->attachment}");
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
        if (request()->has('back'))
            return view('/site/doc/list', compact('site_id', 'type'));

        $doc_request = request()->all();

        // Create Site Doc
        $doc = SiteDoc::create($doc_request);

        // Handle attached file
        if (request()->hasFile('singlefile')) {
            $basePath = "site/$doc->site_id/docs";
            $doc->attachment = FileBank::storeUploadedFile(request()->file('singlefile'), $basePath);
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
            return response()->json('failed', 403);

        if (!request()->hasFile('multifile'))
            return response()->json('success');


        // Handle file upload
        $siteId = request('site_id');
        $basePath = "site/{$siteId}/docs";

        foreach (request()->file('multifile') as $file) {
            // Build base filename: {site_id}-original-name.ext
            $originalName = sanitizeFilename(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
            $forcedFilename = "{$originalName}." . strtolower($file->getClientOriginalExtension());

            // Store file (Spaces-safe, unique handled internally)
            $filename = FileBank::storeUploadedFile($file, $basePath, $forcedFilename);

            // Create SiteDoc record
            $doc = SiteDoc::create([
                'type' => request('type'),
                'site_id' => $siteId,
                'name' => pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
                'company_id' => Auth::user()->company_id,
                'attachment' => $filename,
            ]);
        }

        return response()->json('success');
    }

    /**
     * Update the specified resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function update(SiteDocRequest $request, $id)
    {
        $site_id = request('site_id');
        $type = request('type');

        // Redirect on 'back' button
        if (request()->has('back'))
            return view('/site/doc/list', compact('site_id', 'type'));

        $doc = SiteDoc::findOrFail($id);

        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('edit.site.doc', $doc))
            return view('errors/404');

        // Get Original report filename path
        $origSiteId = $doc->site_id;
        $origFilename = $doc->attachment;

        $doc->update(request()->only('name', 'type', 'site_id', 'notes'));

        /*
         |--------------------------------------------------------------------------
         | Handle SITE CHANGE (rename = copy + delete)
         |--------------------------------------------------------------------------
        */
        if ($origFilename && $doc->site_id != $origSiteId) {
            $oldPath = "site/{$origSiteId}/docs/{$origFilename}";
            $newPath = "site/{$doc->site_id}/docs/{$origFilename}";
            FileBank::move($oldPath, $newPath);
        }


        // Handle new attachment + delete old file
        if (request()->hasFile('uploadfile')) {
            $basePath = "site/$doc->site_id/docs";
            $doc->attachment = FileBank::replaceUploadedFile(request()->file('uploadfile'), $basePath, $doc->attachment);
            $doc->save();

            // Dial Before Dig
            if (stripos(strtolower($doc->name)), 'dial before you dig') !== false) {
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
        $status = request('status');
        $type = request('type');

        $site_id_active = request('site_id_active') === 'all' ? null : request('site_id_active');
        $site_id_completed = request('site_id_completed') === 'all' ? null : request('site_id_completed');
        $site_id_upcoming = request('site_id_upcoming') === 'all' ? null : request('site_id_upcoming');

        if ($status == 1) {
            $allowedSites = $site_id_active ? [$site_id_active] : Auth::user()->company->sites(1)->pluck('id');
        } elseif ($status == 0) {
            $allowedSites = $site_id_completed ? [$site_id_completed] : Auth::user()->company->sites(0)->pluck('id');
        } else {
            $allowedSites = $site_id_upcoming ? [$site_id_upcoming] : Auth::user()->company->sites(-1)->pluck('id');
        }

        $records = SiteDoc::with('site')->whereIn('site_id', $allowedSites)->where('status', 1);
        if ($type !== 'ALL')
            $records->where('type', $type);

        $user = Auth::user();
        return DataTables::of($records)
            ->addColumn('id', function ($doc) {
                return "<div class='text-center'> <a href='{$doc->attachment_url}' target='_blank'><i class='fa fa-file-text-o'></i></a></div>";
            })
            ->addColumn('updatedDate', fn($doc) => $doc->updated_at?->format('d/m/Y') ?? '')
            ->addColumn('action', function ($doc) use ($user) {
                $actions = '';

                if ($doc->type === 'PLAN') {
                    if ($user->allowed2('edit.site.doc', $doc))
                        $actions .= "<a href='/site/doc/{$doc->id}' class='btn blue btn-xs'>Edit</a>";

                    if ($user->allowed2('del.site.doc', $doc))
                        $actions .= "<button class='btn dark btn-xs btn-delete' data-remote='/site/doc/{$doc->id}' data-name='{$doc->name}'><i class='fa fa-trash'></i></button>";
                } else {
                    if ($user->allowed2('edit.safety.doc', $doc))
                        $actions .= "<a href='/site/doc/{$doc->id}' class='btn blue btn-xs'>Edit</a>";

                    if ($user->allowed2('del.safety.doc', $doc))
                        $actions .= "<button class='btn dark btn-xs btn-delete' data-remote='/site/doc/{$doc->id}' data-name='{$doc->name}'><i class='fa fa-trash'></i> </button>";
                }

                return $actions;
            })
            ->rawColumns(['id', 'action'])
            ->make(true);
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
        $records = SiteDoc::select(['id', 'type', 'site_id', 'attachment', 'name',])->where('type', $type)->where('site_id', '=', $site_id)->where('status', 1);

        $dt = Datatables::of($records)
            ->editColumn('id', function ($doc) {
                return "<div class='text-center'> <a href='{$doc->attachment_url}' target='_blank'><i class='fa fa-file-text-o'></i></a></div>";
            })
            ->rawColumns(['id', 'action'])
            ->make(true);

        return $dt;
    }
}
