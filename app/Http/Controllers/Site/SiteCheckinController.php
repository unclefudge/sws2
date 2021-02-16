<?php

namespace App\Http\Controllers\Site;

use Illuminate\Http\Request;
use Validator;

use DB;
use Session;
use App\Models\Site\Site;
use App\Models\Site\Planner\SiteRoster;
use App\Models\Site\Planner\SiteAttendance;
use App\Models\Site\SiteHazard;
use App\Models\Misc\Action;
use App\Http\Requests;
use App\Http\Requests\Site\SiteRequest;
use App\Http\Requests\Site\SiteCheckinRequest;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Intervention\Image\Facades\Image;
use Yajra\Datatables\Datatables;
use nilsenj\Toastr\Facades\Toastr;
use Carbon\Carbon;
use Alert;

class SiteCheckinController extends Controller {

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
     * Check-in to Site.
     */
    public function checkin()
    {
        $worksite = (Session::has('siteID')) ? Site::findOrFail(Session::get('siteID')) : '';

        return view('site/checkinSelect', compact('worksite'));
    }

    /**
     * Get Check-in Questions.
     *
     * @return \Illuminate\Http\Response
     */
    public function getQuestions()
    {
        Session::put('siteID', request('site_id'));

        return redirect('checkin/whs/' . request('site_id'));
    }

    /**
     * Show Check-in Questions.
     *
     * @return \Illuminate\Http\Response
     */
    public function showQuestions($site_id)
    {
        $worksite = Site::findOrFail($site_id);

        // Check if User is of a special trade  ie Certifier
        /*
        $special_trade_ids = ['19'];  // 19 - Certifier
        if (count(array_intersect(Auth::user()->company->tradesSkilledIn->pluck('id')->toArray(), $special_trade_ids)) > 0) {
            if (Auth::user()->company->tradesSkilledIn->count() == 1) {
                // User only has 1 trade which is classified as a 'special' trade
                return view('site/checkinTrade', compact('worksite'));
            } else {
                // User has multiple trades so determine what trade they are loggin as today
            }
        }*/

        if ($worksite->id == 254) // Truck
            return view('site/checkinTruck', compact('worksite'));
        if ($worksite->id == 25) // Store
            return view('site/checkinStore', compact('worksite'));

        return view('site/checkin', compact('worksite'));

    }

    /**
     * Process Site Check-in.
     *
     * @return \Illuminate\Http\Response
     */

    public function processCheckin(SiteCheckinRequest $request, $site_id)
    {
        $site = Site::findOrFail($site_id);

        if ($request->has('safe_site'))
            $site->attendance()->save(new SiteAttendance(['safe_site' => '1']));
        else {
            if ($request->has('checkinTrade')) {
                $worksite = Site::find($site->id);

                return view('site/checkinTradeFail', compact(['worksite']));
            }

            $site->attendance()->save(new SiteAttendance(['safe_site' => '0']));

            // Create Hazard + attach to site
            if ($request->has('action_required'))
                $hazard = $site->hazards()->save(new SiteHazard($request->only('action_required', 'reason', 'location', 'rating')));
            else
                $hazard = $site->hazards()->save(new SiteHazard($request->only('reason', 'location', 'rating')));

            //Create action taken + attach to hazard
            if ($hazard) {
                $action = Action::create(['action' => $request->get('action'), 'table' => 'site_hazards', 'table_id' => $hazard->id]);
                $hazard->touch(); // update timestamp

                // Handle attached Photo or Video
                if ($request->hasFile('media'))
                    $hazard->saveAttachedMedia($request->file('media'));

                // Email hazard
                $hazard->emailHazard($action);
            }
        }

        // if Today add them to Roster if Company is on Planer but user not on Roster
        $today = Carbon::now()->format('Y-m-d');
        if ($site->isCompanyOnPlanner(Auth::user()->company_id, $today) && !$site->isUserOnRoster(Auth::user()->id, $today)) {
            $newRoster = SiteRoster::create(array(
                'site_id'    => $site->id,
                'user_id'    => Auth::user()->id,
                'date'       => $today . ' 00:00:00',
                'created_by' => '1',
                'updated_by' => '1',
            ));
        }

        Toastr::success("Checked in");

        //$worksite = $site;
        //dd($site);
        return redirect('/dashboard');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store(SiteRequest $request)
    {

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
     * Update the specified resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function update(SiteRequest $request, $id)
    {
    }


}
