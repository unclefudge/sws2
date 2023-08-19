<?php

namespace App\Http\Controllers\Site;

use Illuminate\Http\Request;
use Validator;

use DB;
use PDF;
use Mail;
use Input;
use Session;
use App\User;
use App\Models\Site\Site;
use App\Models\Site\SiteAsbestos;
use App\Models\Site\SiteAsbestosAction;
use App\Models\Misc\Action;
use App\Models\Company\Company;
use App\Models\Comms\Todo;
use App\Models\Comms\TodoUser;
use App\Http\Requests;
use App\Http\Requests\Site\SiteAsbestosRequest;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Yajra\Datatables\Datatables;
use nilsenj\Toastr\Facades\Toastr;
use Carbon\Carbon;
use Webklex\PDFMerger\Facades\PDFMergerFacade as PDFMerger;


/**
 * Class SiteWhsManagementPlanController
 * @package App\Http\Controllers\Site
 */
class SiteWhsManagementPlanController extends Controller {

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
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
       //
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
     * Edit the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //

    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store()
    {


        Toastr::success("Created notification");

        return redirect('/site/asbestos/notification/');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update($id)
    {

        $asb->update($asb_request);
        Action::create(['action' => 'Notification fields updated', 'table' => 'site_asbestos', 'table_id' => $asb->id]);
        Toastr::success("Saved changes");

        return redirect("site/asbestos/notification/$asb->id");
    }

    /**
     * Get Asbestos Reports current user is authorised to manage + Process datatables ajax request.
     */
    public function getReports()
    {
        $site_list = Auth::user()->authSites('view.site.asbestos')->pluck('id')->toArray();
        $records = DB::table('site_asbestos AS a')
            ->select(['a.id', 'a.site_id', 'a.amount', 'a.friable', 'a.type', 'a.amount', 'a.date_from', 'a.date_to', 'a.status', 'a.company_id', 'a.updated_at',
                's.name as sitename', 's.code'])
            ->join('sites AS s', 'a.site_id', '=', 's.id')
            ->whereIn('a.site_id', $site_list)
            ->where('a.status', request('status'));

        $dt = Datatables::of($records)
            ->editColumn('id', '<div class="text-center"><a href="/site/asbestos/notification/{{$id}}"><i class="fa fa-search"></i></a></div>')
            ->editColumn('sitename', function ($doc) {
                $s = Site::find($doc->site_id);
                return "$s->name ($s->address, $s->suburb)";
            })
            ->editColumn('updated_at', function ($doc) {
                return (new Carbon($doc->updated_at))->format('d/m/Y');
            })
            ->addColumn('proposed_dates', function ($doc) {
                return (new Carbon($doc->date_from))->format('d M') . ' - ' . (new Carbon($doc->date_to))->format('d M');
            })
            ->addColumn('supervisor', function ($doc) {
                $s = Site::find($doc->site_id);

                return ($s->supervisorName);
            })
            ->addColumn('action', function ($doc) {
                //if ($doc->status && Auth::user()->allowed2('edit.site.asbestos', $doc))
                //    return '<a href="/site/asbestos/' . $doc->id . '/edit" class="btn blue btn-xs btn-outline sbold uppercase margin-bottom"><i class="fa fa-pencil"></i> Edit</a>';

                //return '<a href="/site/asbestos/' . $doc->id . '" class="btn blue btn-xs btn-outline sbold uppercase margin-bottom"><i class="fa fa-search"></i> View</a>';
            })
            ->rawColumns(['id', 'action'])
            ->make(true);

        return $dt;
    }
}
