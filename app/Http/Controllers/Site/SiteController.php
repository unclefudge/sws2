<?php

namespace App\Http\Controllers\Site;

use Alert;
use App\Http\Controllers\Controller;
use App\Http\Requests\Site\SiteRequest;
use App\Models\Misc\Equipment\EquipmentLocation;
use App\Models\Site\Site;
use App\Services\FileBank;
use App\User;
use Carbon\Carbon;
use DB;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Auth;
use nilsenj\Toastr\Facades\Toastr;
use PDF;
use Session;
use Validator;
use Yajra\Datatables\Datatables;

class SiteController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Check authorisation and throw 404 if not
        if (!Auth::user()->hasAnyPermissionType('site'))
            return view('errors/404');

        return view('site/list');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function sitelist()
    {
        // Check authorisation and throw 404 if not
        if (!Auth::user()->hasAnyPermissionType('site.list'))
            return view('errors/404');

        return view('site/list-basic');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('add.site'))
            return view('errors/404');

        return view('site/create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store(SiteRequest $request)
    {
        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('add.site'))
            return view('errors/404');

        $site_request = $request->except('supervisors');

        // Create Site
        $newSite = Site::create($site_request);

        if (request('supervisors'))
            $newSite->supervisors()->sync(request('supervisors'));

        // Create new Equipment Location
        EquipmentLocation::create(['site_id' => $newSite->id, 'status' => 1]);

        $newSite->emailSite('new');

        Toastr::success("Created new site");

        return redirect('site');
    }

    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $site = Site::findorFail($id);

        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('view.site', $site))
            return view('errors/404');

        return view('site/show', compact('site'));
    }

    /**
     * Display the settings for the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function showDocs($id)
    {
        $site = Site::findOrFail($id);

        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('edit.site', $site))
            return view('errors/404');

        return view('site/docs', compact('site'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function update($id)
    {
        $site = Site::findorFail($id);
        $old_status = $site->status;

        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('edit.site', $site))
            return view('errors/404');

        $site_request = request()->except('supervisors');

        // Site recently marked completed
        if ($site_request['status'] == 0 && $site->status != 0) {
            $site_request['completed'] = Carbon::now();
            $location = EquipmentLocation::where('site_id', $site->id)->first();
            if ($location) {
                $location->status = 0;
                $location->save();
            }
        }
        // Site recently reactivated
        if ($site_request['status'] != 0 && $site->status == 0) {
            $location = EquipmentLocation::where('site_id', $site->id)->first();
            if ($location) {
                $location->status = 1;
                $location->save();
            } else
                $location = EquipmentLocation::create(['site_id' => $site->id, 'status' => 1]);
        }
        if ($site_request['status'] != 0)
            $site_request['completed'] = '0000-00-00 00:00:00';

        //dd($site_request);
        $site->update($site_request);

        // Update supervisors for site
        if (request('supervisors')) {
            $super_list = array_diff(request('supervisors'), [request('supervisor_id')]); // Don't include primary supervisor
            $site->supervisors()->sync($super_list);
        } else
            $site->supervisors()->detach();

        // Email Site if status change
        if ($site->status != $old_status) {
            $site->emailSite();
            if ($site->status == '-2')
                $site->cancelInspectionReports();
        }

        Toastr::success("Saved changes");

        return redirect('/site/' . $site->id);
    }

    /**
     * Update the specified resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function updateClient($id)
    {
        $site = Site::findOrFail($id);

        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('edit.site.zoho.fields', $site))
            return view('errors/404');

        $site_request = request()->all();
        $site->update($site_request);
        Toastr::success("Saved changes");

        return redirect('/site/' . $site->id);
    }

    /**
     * Update the specified resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function updateAdmin($id)
    {
        $site = Site::findOrFail($id);

        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('edit.site.admin', $site))
            return view('errors/404');

        $site_request = request()->all();
        $site_request['contract_sent'] = (request('contract_sent')) ? Carbon::createFromFormat('d/m/Y H:i', request('contract_sent') . '00:00')->toDateTimeString() : null;
        $site_request['contract_signed'] = (request('contract_signed')) ? Carbon::createFromFormat('d/m/Y H:i', request('contract_signed') . '00:00')->toDateTimeString() : null;
        $site_request['deposit_paid'] = (request('deposit_paid')) ? Carbon::createFromFormat('d/m/Y H:i', request('deposit_paid') . '00:00')->toDateTimeString() : null;
        $site_request['completion_signed'] = (request('completion_signed')) ? Carbon::createFromFormat('d/m/Y H:i', request('completion_signed') . '00:00')->toDateTimeString() : null;
        $site_request['council_approval'] = (request('council_approval')) ? Carbon::createFromFormat('d/m/Y H:i', request('council_approval') . '00:00')->toDateTimeString() : null;
        $site_request['engineering_cert'] = (request('engineering_cert')) ? Carbon::createFromFormat('d/m/Y H:i', request('engineering_cert') . '00:00')->toDateTimeString() : null;
        $site_request['construction_rcvd'] = (request('construction_rcvd')) ? Carbon::createFromFormat('d/m/Y H:i', request('construction_rcvd') . '00:00')->toDateTimeString() : null;
        $site_request['hbcf_start'] = (request('hbcf_start')) ? Carbon::createFromFormat('d/m/Y H:i', request('hbcf_start') . '00:00')->toDateTimeString() : null;
        $site_request['jobstart_estimate'] = (request('jobstart_estimate')) ? Carbon::createFromFormat('d/m/Y H:i', request('jobstart_estimate') . '00:00')->toDateTimeString() : null;
        $site_request['forecast_completion'] = (request('forecast_completion')) ? Carbon::createFromFormat('d/m/Y H:i', request('forecast_completion') . '00:00')->toDateTimeString() : null;

        // Project Coodinator
        if (request('project_mgr')) {
            $mgr = User::find(request('project_mgr'));
            $site_request['project_mgr_name'] = $mgr->fullname;
        }

        $site->update($site_request);

        Toastr::success("Saved changes");
        return redirect('/site/' . $site->id);
    }

    /**
     * Update the specified resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function updateSupervisor($site_id, $super_id)
    {
        $site = Site::findOrFail($site_id);

        // Check authorisation and throw 404 if not
        if (!(Auth::user()->allowed2('edit.site.admin', $site) || Auth::user()->hasAnyPermissionType('preconstruction.planner')))
            return view('errors/404');

        $site->supervisor_id = $super_id;
        $site->save();

        Toastr::success("Updated Supervisor");
        if (request()->ajax())
            return response()->json(['success' => '1']);
        return redirect('/site/' . $site->id);
    }

    /**
     * Update the specified resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function updateJobstartEstimate($site_id, $date)
    {
        $site = Site::findOrFail($site_id);

        // Check authorisation and throw 404 if not
        if (!(Auth::user()->allowed2('edit.site.admin', $site) || Auth::user()->hasAnyPermissionType('preconstruction.planner')))
            return view('errors/404');

        if ($date == 'clear')
            $site->jobstart_estimate = null;
        else
            $site->jobstart_estimate = Carbon::createFromFormat('Y-m-d H:i', $date . '00:00')->toDateTimeString();


        $site->save();

        Toastr::success("Updated Start Estimate");
        if (request()->ajax())
            return response()->json(['success' => '1']);
        return redirect('/site/' . $site->id);
    }

    public function updateEworks($site_id, $cid)
    {
        $site = Site::findOrFail($site_id);

        // Check authorisation and throw 404 if not
        if (!(Auth::user()->allowed2('edit.site.admin', $site) || Auth::user()->hasAnyPermissionType('preconstruction.planner')))
            return view('errors/404');

        $site->eworks = ($cid == 'null') ? null : $cid;
        $site->save();

        Toastr::success("Updated Electrical Works");
        if (request()->ajax())
            return response()->json(['success' => '1']);
        return redirect('/site/' . $site->id);
    }

    public function updatePworks($site_id, $cid)
    {
        $site = Site::findOrFail($site_id);

        // Check authorisation and throw 404 if not
        if (!(Auth::user()->allowed2('edit.site.admin', $site) || Auth::user()->hasAnyPermissionType('preconstruction.planner')))
            return view('errors/404');

        $site->pworks = ($cid == 'null') ? null : $cid;
        $site->save();

        Toastr::success("Updated Plumbing Works");
        if (request()->ajax())
            return response()->json(['success' => '1']);
        return redirect('/site/' . $site->id);
    }


    /**
     * Create WHS Management Plan
     *
     * @return \Illuminate\Http\Response
     */
    public function createWhsManagementPlan($site_id)
    {
        $site = Site::findOrFail($site_id);

        // -------------------------------------------------
        // Paths
        // -------------------------------------------------
        $tmpDir = storage_path('app/tmp/whs');
        if (!is_dir($tmpDir))
            mkdir($tmpDir, 0755, true);

        $coverTmp = "{$tmpDir}/WHS_Management_Plan_Cover_{$site_id}.pdf";
        $outputTmp = "{$tmpDir}/WHS_Management_Plan_{$site_id}.pdf";

        // Master PDF must be LOCAL for Ghostscript
        $master = public_path('WHS_Management_Plan_Master.pdf');

        // Final destination in Spaces
        $spacesPath = "site/{$site_id}/docs/WHS_Management_Plan.pdf";

        // -------------------------------------------------
        // Generate cover PDF (local tmp)
        // -------------------------------------------------
        PDF::loadView('pdf/site/whs-management-plan-cover', compact('site'))->setPaper('a4')->save($coverTmp);

        // -------------------------------------------------
        // Merge cover + master using Ghostscript
        // -------------------------------------------------
        $cmd = sprintf(
            'gs -q -sPAPERSIZE=a4 -dNOPAUSE -dBATCH -sDEVICE=pdfwrite -sOutputFile=%s %s %s',
            escapeshellarg($outputTmp),
            escapeshellarg($coverTmp),
            escapeshellarg($master)
        );

        exec($cmd, $output, $retval);

        if ($retval !== 0 || !file_exists($outputTmp))
            throw new \RuntimeException('Failed to generate WHS Management Plan PDF');


        // -------------------------------------------------
        // Upload final PDF to Spaces
        // -------------------------------------------------
        FileBank::put($spacesPath, new File($outputTmp));

        // -------------------------------------------------
        // Cleanup temp files
        // -------------------------------------------------
        @unlink($coverTmp);
        @unlink($outputTmp);

        // -------------------------------------------------
        // Redirect to Spaces-served file
        // -------------------------------------------------
        return redirect(FileBank::url($spacesPath));
    }

    /**
     * Get Sites current user is authorised to manage + Process datatables ajax request.
     */
    public function getSites()
    {

        $request_ids = (request('supervisor') == 'all') ? Auth::user()->company->sites()->pluck('id')->toArray() : Auth::user()->company->sites()->where('supervisor_id', request('supervisor'))->pluck('id')->toArray();
        $status = (request('status') == 'all') ? [-2, -1, 0, 1, 2] : request('status');
        $site_records = Auth::user()->company->sites($status)->whereIn('id', $request_ids);

        $dt = Datatables::of($site_records)
            ->editColumn('id', function ($site) {
                // Only return link to site if owner of site
                return (Auth::user()->isCompany($site->company_id)) ? '<div class="text-center"><a href="/site/' . $site->id . '"><i class="fa fa-search"></i></a></div>' : '';
            })
            ->editColumn('client_phone', function ($site) {
                $string = '';
                if ($site->client1_mobile) {
                    $string = $site->client1_mobile;
                    if ($site->client1_firstname)
                        $string = $site->client1_mobile . ' ' . $site->client1_firstname;
                }
                if ($site->client2_mobile) {
                    $string = $site->client2_mobile;
                    if ($site->client2_firstname)
                        $string = $site->client2_mobile . ' ' . $site->client2_firstname;
                }
                return $string;
            })
            ->addColumn('supervisor', function ($site) {
                return $site->supervisorName;
            })
            ->rawColumns(['id', 'client_phone'])
            ->make(true);

        return $dt;
    }

    /**
     * Get Sites current user is authorised to manage + Process datatables ajax request.
     */
    public function getSiteList()
    {
        $status = request('status');
        if (request('site_group'))
            $site_records = Auth::user()->authSites('view.site.list', $status)->where('company_id', request('site_group'));
        else {
            // If SiteGroup is All (ie 0) and Status (Upcoming or Inactive) then restrict sites to only user own company
            // Child company can't see inactive or upcoming sites for parent
            $site_records = (request('site_group') == '0' && $status != 1) ?
                Auth::user()->authSites('view.site.list', $status)->where('company_id', Auth::user()->company_id) :
                Auth::user()->authSites('view.site.list', $status);
        }

        $dt = Datatables::of($site_records)
            //->editColumn('name', function ($site) {
            //    return "$site->name ($site->address, $site->suburb)";
            //})
            ->editColumn('client_phone', function ($site) {
                $string = '';
                if ($site->client1_mobile) {
                    $string = $site->client1_mobile;
                    if ($site->client1_firstname)
                        $string = $site->client1_mobile . ' ' . $site->client1_firstname;
                }
                if ($site->client2_mobile) {
                    $string = $site->client2_mobile;
                    if ($site->client2_firstname)
                        $string = $site->client2_mobile . ' ' . $site->client2_firstname;
                }

                return $string;
            })
            ->editColumn('address', function ($site) {
                return $site->full_address;
            })
            ->addColumn('supervisor', function ($site) {
                return $site->supervisorName;
            })
            ->rawColumns(['id', 'client_phone'])
            ->make(true);

        return $dt;
    }

    /**
     * Get Site Docs current user is authorised to manage + Process datatables ajax request.
     */
    public function getSiteDocs()
    {
        $siteId = request('site_id');
        $type = request('type');

        $records = DB::table('site_docs as d')
            ->select(['d.id', 'd.type', 'd.site_id', 'd.attachment', 'd.name', 's.id as sid', 's.name as site_name',])
            ->join('sites as s', 'd.site_id', '=', 's.id')
            ->where('d.site_id', $siteId)
            ->where('d.status', 1);

        if ($type !== 'ALL')
            $records->where('d.type', $type);

        $dt = Datatables::of($records)
            ->editColumn('id', function ($doc) {
                $url = FileBank::url("site/{$doc->site_id}/docs/{$doc->attachment}");
                return '<div class="text-center"><a href="' . $url . '" target="_blank"><i class="fa fa-file-text-o"></i></a></div>';
            })
            ->addColumn('action', function ($doc) {
                return '';
            })
            ->rawColumns(['id', 'action'])
            ->make(true);

        return $dt;
    }


    /**
     * Get basic Site details.
     */
    public function getSiteDetails($id)
    {
        return Site::findOrFail($id);
    }

    /**
     * Get Site supervisor (first).
     */
    public function getSiteSuper($id)
    {
        $site = Site::findOrFail($id);
        return ($site) ? $site->supervisor : null;
    }

    /**
     * Get basic Site details.
     */
    /*
    public function getSiteOwner($id)
    {
        $site = Site::find($id);
        return $site->client->clientOfCompany;
    }*/


}
