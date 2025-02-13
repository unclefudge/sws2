<?php

namespace App\Http\Controllers\Safety;

use App\Http\Controllers\Controller;
use App\Http\Requests\Safety\WmsRequest;
use App\Models\Comms\Todo;
use App\Models\Company\Company;
use App\Models\Safety\WmsControl;
use App\Models\Safety\WmsDoc;
use App\Models\Safety\WmsHazard;
use App\Models\Safety\WmsStep;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Mail;
use nilsenj\Toastr\Facades\Toastr;
use PDF;
use Session;
use Validator;
use Yajra\Datatables\Datatables;

/**
 * Class WmsController
 * @package App\Http\Controllers\Safety
 */
class WmsController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Check authorisation and throw 404 if not
        if (!Auth::user()->hasAnyPermissionType('wms'))
            return view('errors/404');

        $pending = WmsDoc::where('status', 3)->where('company_id', 3)->get();

        return view('safety/doc/wms/list', compact('pending'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        /// Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('add.wms'))
            return view('errors/404');

        $data = [];

        return view('safety/doc/wms/create', compact('data'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function createFromTemplate($id)
    {
        $doc = WmsDoc::findOrFail($id);
        $data = [];

        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('add.wms'))
            return view('errors/404');

        return view('safety/doc/wms/create_template', compact('data', 'doc'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function createRenew($id)
    {
        $doc = WmsDoc::findOrFail($id);
        $data = [];

        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('add.wms'))
            return view('errors/404');

        return view('safety/doc/wms/create_renew', compact('data', 'doc'));
    }

    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $doc = WmsDoc::findOrFail($id);

        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('view.wms', $doc))
            return view('errors/404');

        if (in_array($doc->status, [0, 1]))
            return view('safety/doc/wms/show', compact('doc'));

        if (Auth::user()->allowed2('edit.wms', $doc))
            return redirect('/safety/doc/wms/' . $doc->id . '/edit');

        return view('safety/doc/wms/show', compact('doc'));
    }

    /**
     * Edit the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $doc = WmsDoc::findOrFail($id);

        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('edit.wms', $doc))
            return view('errors/404');

        // Draft / Pending Mode
        if (in_array($doc->status, [2, 3]))
            return view('safety/doc/wms/edit', compact('doc'));

        return redirect('/safety/doc/wms/' . $doc->id);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store(WmsRequest $request)
    {
        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('add.wms'))
            return view('errors/404');

        $wms_request = request()->all();
        $wms_request['for_company_id'] = Auth::user()->company_id;

        // Defaults
        $wms_request['company_id'] = Auth::user()->company->reportsTo()->id;
        $wms_request['principle_id'] = Auth::user()->company->reportsTo()->id;
        $wms_request['principle'] = Auth::user()->company->reportsTo()->name;
        $wms_request['status'] = '2'; // Draft mode

        // Determine Principle contractor if subscription otherwise assign parent company
        if (Auth::user()->company->subscription) {
            // If Principle checkbox Yes then assign principle fields + document owner
            if (request('master')) {
                $wms_request['company_id'] = 3;
                $wms_request['principle'] = null;
                $wms_request['principle'] = null;
            } elseif (request('principle_id') != 'other' && !request('master')) {
                $wms_request['company_id'] = request('principle_id');
                $wms_request['principle_id'] = request('principle_id');
                $wms_request['principle'] = Company::find(request('principle_id'))->name;
            } else {
                $wms_request['company_id'] = Auth::user()->company->id;
                $wms_request['principle_id'] = null;
                $wms_request['principle'] = request('principle');
            }
        }

        if (request('swms_type') != 'upload') {
            $wms_request['builder'] = 1;
            $wms_request['project'] = 'All Jobs';
        }

        if (!request('master_id'))
            $wms_request['master_id'] = null;

        // If Replace checkbox Yes then archive selected SWMS
        if (request('replace_switch') && $request->filled('replace_id')) {
            $replace_wms = WmsDoc::findOrFail(request('replace_id'));
            $replace_wms->status = '0';
            $replace_wms->save();
            $replace_wms->closeToDo();
            if (!Auth::user()->isCompany($replace_wms->owned_by))
                $replace_wms->emailArchived();
        }

        //dd($wms_request);

        // Create WMSdoc
        $newDoc = WmsDoc::create($wms_request);

        // Copy Steps / Hazards / Controls from Master Template
        if (request('master_id'))
            $this->copyTemplate(request('master_id'), $newDoc->id);


        // Handle attached file
        if ($request->hasFile('attachment')) {
            $file = request('attachment');

            $path = "filebank/company/" . Auth::user()->company_id . '/wms';
            $name = sanitizeFilename($newDoc->name) . '-v1.0-' . $newDoc->id . '.' . strtolower($file->getClientOriginalExtension());
            $path_name = $path . '/' . $name;
            $file->move($path, $name);
            $newDoc->attachment = $name;
            $newDoc->save();
            //dd($path);
        }

        Toastr::success("Created new statement");

        return redirect('/safety/doc/wms/' . $newDoc->id . '/edit');
    }

    /**
     * Sign off on the given doc.
     */
    public function signoff(Request $request, $id)
    {
        $doc = WmsDoc::findOrFail($id);

        // Signed off by Company doc is For
        if (!$doc->user_signed_id && $doc->for_company_id == Auth::user()->company_id) {
            // Check authorisation and throw 404 if not
            if (!Auth::user()->allowed2('edit.wms', $doc))
                return view('errors/404');

            $doc->user_signed_id = Auth::user()->id;
            $doc->user_signed_at = Carbon::now();

            // Move doc to active if Principle signed or has no Principle else make pending
            if ($doc->principle_signed_id)
                $doc->status = '1';
            else
                $doc->status = ($doc->principle_id) ? 3 : 1;

            if ($doc->status == '3') // Pending Sign off
                $doc->emailSignOff();
        }

        // Signed off by Principle doc
        if (!$doc->principle_signed_id && $doc->principle_id && $doc->company_id == Auth::user()->company_id && Auth::user()->allowed2('sig.wms', $doc)) {
            $doc->principle_signed_id = Auth::user()->id;
            $doc->principle_signed_at = Carbon::now();
            // Move doc to active if user signed else make pending
            $doc->status = ($doc->user_signed_id) ? 1 : 3;
        }

        if ($doc->status == 1 && $doc->builder) {
            $file = $this->createPdf($doc);
            $doc->attachment = $file;
        }
        ($doc->status == 1) ? Toastr::success("Statement signed off") : Toastr::info("Requested signed off");

        $doc->save();

        return redirect('/safety/doc/wms/' . $doc->id);
    }

    public function sinoffMassPending()
    {
        if (Auth::user()->hasAnyRole2('mgt-general-manager|web-admin')) {
            $pending = WmsDoc::where('status', 3)->where('company_id', 3)->get();
            foreach ($pending as $doc) {
                if ($doc->templateModified() == 'No') {
                    $doc->principle_signed_id = Auth::user()->id;
                    $doc->principle_signed_at = Carbon::now();
                    $doc->status = 1;
                    if ($doc->builder) {
                        $file = $this->createPdf($doc);
                        $doc->attachment = $file;
                    }
                    $doc->save();
                }
            }
            Toastr::success("Statements signed off");
        }

        return redirect('/safety/doc/wms/');
    }

    /**
     * Reject the given doc and return it to inactive.
     */
    public function reject(Request $request, $id)
    {
        $doc = WmsDoc::findOrFail($id);

        /// Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('sig.wms', $doc))
            return view('errors/404');

        $doc->principle_signed_id = null;
        $doc->principle_signed_at = null;
        $doc->user_signed_id = null;
        $doc->user_signed_at = null;
        $doc->status = 2; // Return to Pending
        $doc->save();

        if ($doc->for_company_id == Auth::user()->company_id)
            Toastr::info("Sign off request cancelled");
        else
            Toastr::error("Rejected sign off");

        return redirect('/safety/doc/wms/' . $doc->id . '/edit');
    }

    /**
     * Archive or Unarchive the given doc.
     */
    public function archive(Request $request, $id)
    {
        $doc = WmsDoc::findOrFail($id);

        /// Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('del.wms', $doc))
            return view('errors/404');

        $doc->status = (in_array($doc->status, [1, 3])) ? 0 : 1;
        $doc->save();

        if ($doc->status == 1)
            Toastr::success("Statement restored");
        else {
            if (!Auth::user()->isCompany($doc->owned_by))
                $doc->emailArchived();
            Toastr::success("Statement archived");
        }

        return redirect('/safety/doc/wms/' . $doc->id);
    }

    /**
     * Show the form for creating/renewing a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function replace($id)
    {
        /// Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('add.wms'))
            return view('errors/404');

        $data = ['replace_id' => $id];

        return view('safety/doc/wms/create', compact('data'));
    }

    /**
     * Create PDF for Doc using 'builder'
     */
    public function pdf($id)
    {
        $doc = WmsDoc::findOrFail($id);
        if ($doc->builder && $doc->status == 1) {
            //return view('pdf/workmethod', compact('doc'));
            $file = $this->createPdf($doc);
            //dd($file);
            $doc->attachment = $file;
            Toastr::success("Statement signed off");
            $doc->save();
        }

        return redirect('/safety/doc/wms/' . $doc->id);
    }

    /**
     * Email document to someone
     */
    public function email(Request $request, $id)
    {
        $doc = WmsDoc::findOrFail($id);
        $email_list = ($request->has('email_list')) ? $request->get('email_list') : '';
        $email_user = ($request->get('email_self')) ? true : false;
        if ($email_list) {
            $doc->emailStatement($email_list, $email_user);
            Toastr::success("Sent email");
        }

        return redirect('/safety/doc/wms/' . $doc->id);
    }

    /**
     * Upload a file for the specified resource in storage.
     */
    public function upload(Request $request, $id)
    {
        $doc = WmsDoc::findOrFail($id);

        /// Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('edit.wms', $doc))
            return view('errors/404');

        // Update Doc info + increment version
        $doc_request = $request->except('attachment');
        list($major, $minor) = explode('.', $doc_request['version']);
        $minor++;
        $doc_request['version'] = $major . '.' . $minor;
        $doc->update($doc_request);

        // Delete previous file
        if ($doc->attachment && file_exists(public_path($doc->attachmentUrl)))
            unlink(public_path($doc->attachmentUrl));

        // Handle attached file
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');

            $path = "filebank/company/" . $doc->for_company_id . '/wms';
            $name = sanitizeFilename($request->get('name')) . '-v1.0-' . $doc->id . '.' . strtolower($file->getClientOriginalExtension());
            $file->move($path, $name);
            $doc->attachment = $name;
            dd('y');
        } else
            $doc->attachment = '';
        $doc->save();
        dd('c');


        return ['attachment' => $doc->attachment, 'version' => $doc->version];
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        //if ($request->ajax()) {
        $wmsDoc = WmsDoc::findOrFail($id);

        /// Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('edit.wms', $wmsDoc))
            return view('errors/404');

        $action = $request->get('action');

        // Update Doc + All Steps / Hazards / Controls
        if ($action == 'save') {
            $doc = json_decode($request->get('doc'));

            // Update Doc data
            $doc_data = [];
            $doc_data['name'] = $doc->name;
            $doc_data['project'] = $doc->project;
            $doc_data['principle'] = $doc->principle;
            $doc_data['principle_id'] = $doc->principle_id;
            $doc_data['company_id'] = $doc->company_id;
            $doc_data['res_compliance'] = $doc->res_compliance;
            $doc_data['res_review'] = $doc->res_review;
            $doc_data['status'] = $doc->status;


            // Increment minor version
            list($major, $minor) = explode('.', $doc->version);
            $minor++;
            $doc_data['version'] = $major . '.' . $minor;

            // Save Doc data
            $wmsDoc->update($doc_data);

            // Delete existing Steps / Hazards / Controls
            foreach ($wmsDoc->steps as $step) {
                WmsHazard::where('step_id', $step->id)->delete();
                WmsControl::where('step_id', $step->id)->delete();
            }
            WmsStep::where('doc_id', $id)->delete();

            // Re-create new ones
            $steps = json_decode($request->get('steps'));
            $hazards = json_decode($request->get('hazards'));
            $controls = json_decode($request->get('controls'));
            foreach ($steps as $step) {
                $newStep = WmsStep::create((array)$step);
                // Re-create hazards for current step;
                foreach ($hazards as $hazard)
                    if ($hazard->step_id == $step->id) {
                        $hazard->step_id = $newStep->id;
                        $newHaz = WmsHazard::create((array)$hazard);
                    }

                // Re-create controls for current step;
                foreach ($controls as $control)
                    if ($control->step_id == $step->id) {
                        $control->step_id = $newStep->id;
                        $newCon = WmsControl::create((array)$control);
                    }
            }

            return $doc_data['version'];
        }

        //return view('errors.404');
    }

    /**
     * Get Docs current user is authorised to manage + Process datatables ajax request.
     */
    public function getWms(Request $request)
    {
        // Company IDs of Toolboxs user is allowed to view
        // ie. User can view Toolboxs owned by their company or parent company if they have access to view 'All'
        $company_ids = [];
        if (Auth::user()->permissionLevel('view.wms', Auth::user()->company_id) == 99)
            $company_ids[] = Auth::user()->company_id;
        if (Auth::user()->permissionLevel('view.wms', Auth::user()->company->reportsTo()->id) == 99)
            $company_ids[] = Auth::user()->company->reportsTo()->id;

        // For Company IDs of Toolboxs user is allowed to view
        // ie. User can view Toolboxs owned by their Parent but they have access to only view 'Own Company'
        //     unless Child company has subscription then child company permission overides
        $for_company_ids = [];
        //if (!Auth::user()->company->subscription && Auth::user()->permissionLevel('view.wms', Auth::user()->company->reportsTo()->id) == 20)
        if (Auth::user()->permissionLevel('view.wms', Auth::user()->company->reportsTo()->id) == 20)
            $for_company_ids[] = Auth::user()->company_id;

        $records = DB::table('wms_docs AS d')
            ->select(['d.id', 'd.attachment', 'd.name', 'd.version', 'd.principle', 'd.principle_id', 'd.principle_signed_id', 'd.user_signed_id',
                'd.for_company_id', 'd.company_id', 'd.status', 'd.updated_at', 'c.name AS company_name'])
            ->join('companys AS c', 'd.for_company_id', '=', 'c.id')
            ->where(function ($q) use ($for_company_ids, $company_ids) {
                $q->WhereIn('company_id', $company_ids);
                $q->orWhereIn('for_company_id', $for_company_ids);
            })
            ->where('d.master', '0')
            ->where('d.status', $request->get('status'));


        $dt = Datatables::of($records)
            ->editColumn('id', function ($doc) {
                if ($doc->attachment && file_exists(public_path('/filebank/company/' . $doc->for_company_id . '/wms/' . $doc->attachment)))
                    return '<div class="text-center"><a href="/filebank/company/' . $doc->for_company_id . '/wms/' . $doc->attachment . '"><i class="fa fa-file-text-o"></i></a></div>';

                return '';
            })
            ->editColumn('name', function ($doc) {
                $name = $doc->name . ' v' . $doc->version;
                if ($doc->status == 1) {
                    $now = Carbon::now();
                    $twoyearago = $now->subYears(2)->toDateTimeString();
                    if ($doc->updated_at < $twoyearago)
                        $name .= ' <span class="badge badge-danger badge-roundless">Out of Date</span>';
                }

                return $name;
            })
            ->editColumn('company_name', function ($doc) {
                $company = Company::find($doc->for_company_id);

                return ($doc->user_signed_id) ? $company->name : '<span class="font-red">' . $company->name . '</span>';
            })
            ->editColumn('principle', function ($doc) {
                if ($doc->principle_id) {
                    $company = Company::find($doc->principle_id);

                    return ($doc->principle_signed_id) ? $company->name : '<span class="font-red">' . $company->name . '</span>';
                }

                return $doc->principle;
            })
            ->editColumn('updated_at', function ($doc) {
                return (new Carbon($doc->updated_at))->format('d/m/Y');
            })
            ->addColumn('action', function ($doc) {
                if ($doc->status == '1' || $doc->status == '-1')
                    return '<a href="/safety/doc/wms/' . $doc->id . '" class="btn blue btn-xs btn-outline sbold uppercase margin-bottom"><i class="fa fa-search"></i> View</a>';

                if (Auth::user()->allowed2('edit.wms', $doc))
                    return '<a href="/safety/doc/wms/' . $doc->id . '/edit" class="btn blue btn-xs btn-outline sbold uppercase margin-bottom"><i class="fa fa-pencil"></i> Edit</a>';

                return '<a href="/safety/doc/wms/' . $doc->id . '" class="btn blue btn-xs btn-outline sbold uppercase margin-bottom"><i class="fa fa-search"></i> View</a>';

            })
            ->rawColumns(['id', 'name', 'company_name', 'principle', 'action'])
            ->make(true);

        return $dt;
    }

    /**
     * Get Docs current user is authorised to manage + Process datatables ajax request.
     */
    public function getWmsTemplates(Request $request)
    {
        $records = DB::table('wms_docs AS d')
            ->select(['d.id', 'd.attachment', 'd.name', 'd.version', 'd.company_id', 'd.status', 'd.updated_at'])
            ->where('d.company_id', 3)
            ->where('d.master', '1')
            ->where('d.status', $request->get('status'));

        $dt = Datatables::of($records)
            ->editColumn('name', function ($doc) {
                $name = $doc->name . ' v' . $doc->version;
                if ($doc->status == 1) {
                    $now = Carbon::now();
                    $yearago = $now->subYear()->toDateTimeString();
                    //if ($doc->updated_at < $yearago && Auth::user()->isCC())
                    //    $name .= ' <span class="badge badge-danger badge-roundless">Out of Date</span>';
                }

                return $name;
            })
            ->editColumn('updated_at', function ($doc) {
                return (new Carbon($doc->updated_at))->format('d/m/Y');
            })
            ->addColumn('action', function ($doc) {
                if ($doc->status == '0' && Auth::user()->isCC() && Auth::user()->hasPermission2('edit.wms'))
                    return '<a href="/safety/doc/wms/' . $doc->id . '/edit" class="btn blue btn-xs btn-outline sbold uppercase margin-bottom"><i class="fa fa-pencil"></i> Edit</a>';

                return '<a href="/safety/doc/wms/' . $doc->id . '" class="btn blue btn-xs btn-outline sbold uppercase margin-bottom"><i class="fa fa-search"></i> View</a>';

            })
            ->rawColumns(['id', 'name', 'action'])
            ->make(true);

        return $dt;
    }

    /**
     * Get Steps for specific WMS doc
     */
    public function getSteps(Request $request, $id)
    {
        $wms_doc = WmsDoc::findOrFail($id);
        $master = ($wms_doc->master_id) ? WmsDoc::find($wms_doc->master_id) : null;
        $steps = WmsStep::where('doc_id', $wms_doc->id)->get();

        $wms_steps = [];
        $wms_hazards = [];
        $wms_controls = [];
        foreach ($steps as $step) {
            $array = [];
            $array['id'] = $step->id;
            $array['doc_id'] = $step->doc_id;
            $array['name'] = $step->name;
            $array['order'] = $step->order;
            $array['master'] = $step->master;
            $array['master_id'] = $step->master_id;
            $array['diff'] = ($wms_doc->status == 3) ? 1 : 0;
            if ($step->master_id && $wms_doc->status == 3) {
                $masterstep = WmsStep::find($step->master_id);
                if ($step->name == $masterstep->name)
                    $array['diff'] = 0;
            }
            $wms_steps[] = $array;

            // Hazards
            $hazards = WmsHazard::where('step_id', $step->id)->get();
            foreach ($hazards as $hazard) {
                $array = [];
                $array['id'] = $hazard->id;
                $array['step_id'] = $step->id;
                $array['name'] = $hazard->name;
                $array['order'] = $hazard->order;
                $array['master'] = $hazard->master;
                $array['master_id'] = $hazard->master_id;
                $array['diff'] = ($wms_doc->status == 3) ? 1 : 0;
                if ($hazard->master_id && $wms_doc->status == 3) {
                    $masterhazard = WmsHazard::find($hazard->master_id);
                    if ($hazard->name == $masterhazard->name)
                        $array['diff'] = 0;
                }
                $wms_hazards[] = $array;
            };

            // Controls
            $controls = WmsControl::where('step_id', $step->id)->get();
            foreach ($controls as $control) {
                $array = [];
                $array['id'] = $control->id;
                $array['step_id'] = $step->id;
                $array['name'] = $control->name;
                $array['order'] = $control->order;
                $array['master'] = $control->master;
                $array['master_id'] = $control->master_id;
                $array['res_principle'] = $control->res_principle;
                $array['res_company'] = $control->res_company;
                $array['res_worker'] = $control->res_worker;
                $array['diff'] = ($wms_doc->status == 3) ? 1 : 0;
                if ($control->master_id && $wms_doc->status == 3) {
                    $mastercontrol = WmsControl::find($control->master_id);
                    if ($control->name == $mastercontrol->name)
                        $array['diff'] = 0;
                }
                $wms_controls[] = $array;
            };

        };

        $json = [];
        $json[] = $wms_doc;
        $json[] = $wms_steps;
        $json[] = $wms_hazards;
        $json[] = $wms_controls;

        return $json;
    }

    /**
     * Get Steps for specific WMS doc
     */
    private function copyTemplate($master_id, $doc_id)
    {
        // Doc
        $master = WmsDoc::find($master_id);
        $doc = WmsDoc::find($doc_id);

        // Increment major version if copying from previous Wms Doc (normal) or if new doc is a Master
        if (!$master->master || $doc->master) {
            list($major, $minor) = explode('.', $master->version);
            $major++;
            $doc->version = $major . '.0';
        } else
            $doc->version = $master->version;
        $doc->save();

        // Steps
        $steps = WmsStep::where('doc_id', $master_id)->get();
        foreach ($steps as $step) {
            $newStep = WmsStep::create(array(
                'doc_id' => $doc_id,
                'name' => $step->name,
                'order' => $step->order,
                'master' => '0',
                'master_id' => $step->id,
            ));

            // Hazards
            $hazards = WmsHazard::where('step_id', $step->id)->get();
            foreach ($hazards as $hazard) {
                $newHazard = WmsHazard::create(array(
                    'step_id' => $newStep->id,
                    'name' => $hazard->name,
                    'order' => $hazard->order,
                    'master' => '0',
                    'master_id' => $hazard->id,
                ));
            };

            // Controls
            $controls = WmsControl::where('step_id', $step->id)->get();
            foreach ($controls as $control) {
                $newControl = WmsControl::create(array(
                    'step_id' => $newStep->id,
                    'name' => $control->name,
                    'res_principle' => $control->res_principle,
                    'res_company' => $control->res_company,
                    'res_worker' => $control->res_worker,
                    'order' => $control->order,
                    'master' => '0',
                    'master_id' => $control->id,
                ));
            };
        };
    }

    private function createPdf($doc)
    {
        $pdf = PDF::loadView('pdf.workmethod', compact('doc'));

        $file = sanitizeFilename($doc->name) . '-v' . $doc->version . '-ref-' . $doc->id . '.pdf';
        $file_path = public_path('filebank/company/' . $doc->for_company_id . '/wms/' . $file);
        if (file_exists($file_path))
            unlink($file_path);

        // Make Directory if doesn't exist
        if (!file_exists('filebank/company/' . $doc->for_company_id . '/wms'))
            if (!mkdir('filebank/company/' . $doc->for_company_id . '/wms', 0755, true))
                die('Failed to create folders...');

        $pdf->save($file_path);

        return $file;
    }

    public function expired(Request $request)
    {
        $now = Carbon::now();
        $yearago = $now->subMonth()->toDateTimeString();
        $expired = WmsDoc::where('status', '1')->where('master', '0')->whereDate('updated_at', '<', $yearago)->orderBy('name')->get();

        $count = 1;
        foreach ($expired as $exp) {
            $todo_wms = Todo::where('type', 'wms_expired')->where('type_id', $exp->id)->where('status', '1')->first();
            if (!$todo_wms) {
                $todo_request = [
                    'type' => 'wms_expired',
                    'type_id' => $exp->id,
                    'name' => 'SWMS Out of Date - ' . $exp->name,
                    'info' => 'This SWMS was last updated ' . $exp->updated_at->format('d/m/Y') . ' and is out of date.',
                    'company_id' => $exp->for_company_id
                ];

                $for_company = Company::find($exp->for_company_id);

                // Create ToDoo for users
                $todo = Todo::create($todo_request);
                $todo->assignUsers($for_company->seniorUsers());

                echo '<br>' . $exp->status . ' ' . $count++ . ' ' . $exp->updated_at->format('Y-m-d') . " - $exp->name (" . $for_company->name . ") <br>";
                //print_r($user_list);
                //dd($todo);
            } else {
                echo '<br>' . $exp->status . ' ' . $count++ . ' ' . $exp->updated_at->format('Y-m-d') . " - $exp->name EXISTING TODO<br>";
            }
        }

        //return $expired->count();
    }

}
