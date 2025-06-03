<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Site\Site;
use App\Models\Site\SiteAsbestosRegister;
use App\Models\Site\SiteAsbestosRegisterItem;
use Carbon\Carbon;
use DB;
use Illuminate\Support\Facades\Auth;
use Input;
use Mail;
use nilsenj\Toastr\Facades\Toastr;
use PDF;
use Session;
use Validator;
use Yajra\Datatables\Datatables;

/**
 * Class SiteAsbestosRegisterController
 * @package App\Http\Controllers\Site
 */
class SiteAsbestosRegisterController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Check authorisation and throw 404 if not
        if (!Auth::user()->hasAnyPermissionType('site.asbestos'))
            return view('errors/404');

        $progress = [];
        $asb10 = SiteAsbestosRegister::where('version', "1.0")->get();
        foreach ($asb10 as $report) {
            if (!count($report->items))
                $progress[] = $report;
        }

        return view('site/asbestos/register/list', compact('progress'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        /// Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('add.site.asbestos'))
            return view('errors/404');

        $site_id = (Session::has('siteID')) ? Session::get('siteID') : '';

        $sitelist = [];
        $sites_active = Auth::user()->authSites('view.site.asbestos', '1');
        $sites_maint = Auth::user()->authSites('view.site.asbestos', '2');
        $sites_upcom = Auth::user()->authSites('view.site.asbestos', '-1');

        foreach ($sites_active as $site) {
            $reg = SiteAsbestosRegister::where('site_id', $site->id)->first();
            if (!$reg && !in_array($site->code, ['0002', '0003', '0005', '0006', '0007']))
                $sitelist[$site->id] = $site->name; //"$site->suburb - $site->address ($site->code:$site->name)";
        }

        foreach ($sites_maint as $site) {
            $reg = SiteAsbestosRegister::where('site_id', $site->id)->first();
            if (!$reg && !in_array($site->code, ['0002', '0003', '0005', '0006', '0007']))
                $sitelist[$site->id] = $site->name; //"$site->suburb - $site->address ($site->code:$site->name)";
        }

        foreach ($sites_upcom as $site) {
            $reg = SiteAsbestosRegister::where('site_id', $site->id)->first();
            if (!$reg && !in_array($site->code, ['0002', '0003', '0005', '0006', '0007']))
                $sitelist[$site->id] = $site->name; //"$site->suburb - $site->address ($site->code:$site->name)";
        }


        return view('site/asbestos/register/create', compact('sitelist', 'site_id'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function createItem($id)
    {
        /// Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('add.site.asbestos'))
            return view('errors/404');

        $asb = SiteAsbestosRegister::findOrFail($id);

        return view('site/asbestos/register/createItem', compact('asb'));
    }


    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $asb = SiteAsbestosRegister::findOrFail($id);

        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('view.site.asbestos', $asb))
            return view('errors/404');

        return view('site/asbestos/register/show', compact('asb'));
    }

    /**
     * Edit the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $asbItem = SiteAsbestosRegisterItem::findOrFail($id);
        $asb = SiteAsbestosRegister::findOrFail($asbItem->register_id);

        // Check authorisation and throw 404 if not
        if (!(Auth::user()->allowed2('edit.site.asbestos', $asb)))
            return view('errors/404');

        return view('site/asbestos/register/editItem', compact('asb', 'asbItem'));

    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store()
    {
        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('add.site.asbestos'))
            return view('errors/404');

        $rules = (request('no-asbestos')) ? ['site_id' => 'required'] : ['site_id' => 'required', 'date' => 'required', 'friable' => 'required', 'type' => 'required', 'location' => 'required', 'condition' => 'required', 'assessment' => 'required'];
        $mesg = ['site_id.required' => 'The site field is required.', 'amount.required' => 'The quantity field is required.', 'friable.required' => 'The asbestos class field is required.'];
        request()->validate($rules, $mesg); // Validate

        $item_request = request()->except('site_id');
        //dd($item_request);

        $asb = SiteAsbestosRegister::where('site_id', request('site_id'))->first();

        // Create Site Asbestos
        if ($asb) {
            // Increment major version
            list($major, $minor) = explode('.', $asb->version);
            $major++;
            $asb->version = $major . '.0';
        } else
            $asb = SiteAsbestosRegister::create(['site_id' => request('site_id'), 'version' => '1.0']);

        // Create Item
        if ($asb && !request('no-asbestos')) {
            $item_request['register_id'] = $asb->id;
            $item_request['date'] = Carbon::createFromFormat('d/m/Y H:i', request('date') . '00:00')->toDateTimeString();

            // Type Other Specific
            if (request('type') == 'other')
                $item_request['type'] = request('type_other');
            $asb->items()->save(new SiteAsbestosRegisterItem($item_request));

            // Create PDF
            $asb->attachment = $this->createPDF($asb->id);
            $asb->save();
        }

        Toastr::success("Created register");

        return redirect("/site/asbestos/register/$asb->id");
    }

    /**
     * Update a resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function update($id)
    {
        $asbItem = SiteAsbestosRegisterItem::findOrFail($id);
        $asb = SiteAsbestosRegister::findOrFail($asbItem->register_id);


        // Check authorisation and throw 404 if not
        if (!(Auth::user()->allowed2('edit.site.asbestos', $asb)))
            return view('errors/404');

        $rules = ['date' => 'required', 'friable' => 'required', 'type' => 'required', 'location' => 'required', 'condition' => 'required', 'assessment' => 'required'];
        $mesg = ['amount.required' => 'The quantity field is required.', 'friable.required' => 'The asbestos class field is required.'];
        request()->validate($rules, $mesg); // Validate

        $item_request = request()->all();
        //dd($item_request);

        $item_request['date'] = Carbon::createFromFormat('d/m/Y H:i', request('date') . '00:00')->toDateTimeString();
        if (request('type') == 'other') $item_request['type'] = request('type_other'); // Type Other Specific

        $asbItem->update($item_request);

        // Increment minor version
        list($major, $minor) = explode('.', $asb->version);
        $minor++;
        $asb->version = $major . '.' . $minor;

        // Create PDF
        $asb->attachment = $this->createPDF($asb->id);
        $asb->save();

        Toastr::success("Updated register");

        return redirect("/site/asbestos/register/$asb->id");
    }

    /**
     * Delete Item
     */
    public function deleteItem($id)
    {
        $asbItem = SiteAsbestosRegisterItem::findOrFail($id);
        $asb = SiteAsbestosRegister::findOrFail($asbItem->register_id);

        // Check authorisation and throw 404 if not
        if (!(Auth::user()->allowed2('edit.site.asbestos', $asb)))
            return view('errors/404');

        $asbItem->delete();

        // Increment major version
        list($major, $minor) = explode('.', $asb->version);
        $major++;
        $asb->version = $major . '.0';
        $asb->save();

        return redirect("/site/asbestos/register/$asb->id");
    }

    /**
     * Delete the specified resource in storage.
     */
    public function destroy($id)
    {
        $asb = SiteAsbestosRegister::findOrFail($id);
        if (!(Auth::user()->allowed2('del.site.asbestos', $asb)))
            return view('errors/404');

        // Delete attached file
        if ($asb->attachment && file_exists(public_path('/filebank/site/' . $asb->site_id . '/docs/' . $asb->attachment)))
            unlink(public_path('/filebank/site/' . $asb->site_id . '/docs/' . $asb->attachment));


        //dd('here');
        $asb->delete();
        Toastr::error("Asbestos register deleted");

        if (request()->ajax())
            return json_encode('success');
        else
            return redirect('/site/asbestos/register');
    }

    public function createPDF($id)
    {
        $asb = SiteAsbestosRegister::findOrFail($id);

        // Set + create create directory if required
        $path = "filebank/site/$asb->site_id/docs";
        if (!file_exists($path))
            mkdir($path, 0777, true);

        $filename = "Asbestos-Register-" . $asb->site->code . ".pdf";

        //
        // Generate PDF
        //
        //return view('pdf/site/asbestos-register', compact('asb'));
        //return PDF::loadView('pdf/site/asbestos-register', compact('asb'))->setPaper('a4', 'landscape')->stream();
        $pdf = PDF::loadView('pdf/site/asbestos-register', compact('asb'));
        $pdf->setPaper('A4', 'landscape');
        $pdf->save(public_path("$path/$filename"));

        return $filename;
    }

    /**
     * Get Asbestos Reports current user is authorised to manage + Process datatables ajax request.
     */
    public function getReports()
    {
        // Asbestos Registor in Progress
        $progress_ids = [];
        $asb10 = SiteAsbestosRegister::where('version', "1.0")->get();
        foreach ($asb10 as $report) {
            if (!count($report->items))
                $progress_ids[] = $report->id;
        }

        $site_list = Auth::user()->authSites('view.site.asbestos')->pluck('id')->toArray();
        if (request('status') == 0)
            $status = [0];
        elseif (request('status') == '-1')
            $status = [-1];
        else
            $status = [1, 2];
        $records = DB::table('site_asbestos_register AS a')
            ->select(['a.id', 'a.site_id', 'a.attachment', 'a.status', 'a.updated_at',
                's.name as sitename', 's.code'])
            ->join('sites AS s', 'a.site_id', '=', 's.id')
            ->whereNotIn('a.id', $progress_ids)
            ->whereIn('a.site_id', $site_list)
            ->whereIn('s.status', $status);

        $dt = Datatables::of($records)
            ->editColumn('id', function ($doc) {
                $asb = SiteAsbestosRegister::find($doc->id);

                return ($asb->attachment_url) ? '<div class="text-center"><a href="' . $asb->attachment_url . '" target="_blank"><i class="fa fa-file-text-o"></i></a></div>' : '';
                //return '<div class="text-center"><a href="' . $asb->attachment_url . '"><i class="fa fa-search"></i></a></div>';
            })
            ->editColumn('sitename', function ($doc) {
                $s = Site::find($doc->site_id);
                return "$s->name ($s->address, $s->suburb)";
            })
            ->editColumn('updated_at', function ($doc) {
                return (new Carbon($doc->updated_at))->format('d/m/Y');
            })
            ->addColumn('action', function ($doc) {
                return '<a href="/site/asbestos/register/' . $doc->id . '" class="btn blue btn-xs btn-outline sbold uppercase margin-bottom"><i class="fa fa-search"></i> View</a>';
            })
            ->rawColumns(['id', 'action'])
            ->make(true);

        return $dt;
    }
}
