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

//use App\Http\Requests\Site\SiteRequest;
//use App\Http\Requests\Site\SiteCheckinRequest;
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

        // Check if user is a Supervisor or requires their login qustions
        $supers = Auth::user()->company->reportsTo()->supervisors()->pluck('id')->toArray();
        $special_users = [3, 108]; // Fudge, Kirstie
        $super_login = array_merge($supers, $special_users);

        if (in_array(Auth::user()->id, $super_login))
            return view('site/checkinSupervisor', compact('worksite'));


        // Check is Site is special (Truck/Store)
        if ($worksite->id == 254) // Truck
            return view('site/checkinTruck', compact('worksite'));
        if ($worksite->id == 25) // Store
            return view('site/checkinStore', compact('worksite'));

        // Standard tradie checkin questions
        return view('site/checkin', compact('worksite'));

    }

    /**
     * Process Site Check-in.
     *
     * @return \Illuminate\Http\Response
     */

    public function processCheckin($site_id)
    {
        $site = Site::findOrFail($site_id);

        $mesg = [
            'question1.required'  => 'Please acknowledge you have read the Site Specific Health & Safety Rules.',
            'question2.required'  => 'Please acknowledge you are fit for work.',
            'question3.required'  => 'Please acknowledge you not affected by any pre-existing medical condition.',
            'question4.required'  => 'Please acknowledge you are physically present on the above Worksite',
            'question5.required'  => 'Please acknowledge you familiar with the site specific Risk Assessment.',
            'question6.required'  => 'Please acknowledge you will take action to eliminate or control any hazards.',
            'question7.required'  => 'Please acknowledge you will report all incidents, near misses, unsafe work practices and conditions.',
            'question8.required'  => 'Please acknowledge you will leave the site secure and safe for others.',
            'question9.required'  => 'Please acknowledge you will store all materials safely.',  // Store checkin
            'question10.required' => 'Please acknowledge you will assess your tasks and implement controls as necessary.',  // Store checkin
            'question11.required' => 'Please acknowledge you will ensure all safety devices such as handrails are in place.',  // Store checkin
            'question12.required' => 'Please acknowledge you will practice good housekeeping.',  // Store checkin
            'question13.required' => 'Please acknowledge you will ensure the site is left secure, is safe for others.',  // Store checkin
            'question14.required' => 'Please acknowledge you hold a current license.', // Truck checkin
            'question15.required' => 'Please acknowledge you will abide by the road rules and be a courteous & responsible driver.',
            'question16.required' => 'Please acknowledge you report any damage and defects to the vehicle.',
            'question17.required' => 'Please acknowledge you any maintenance requirements (including servicing requirements)',
            //'question18.required' => 'Please acknowledge you will adhere to the principles required to manage the risk of COVID-19',  // Covid
            'question19.required' => 'Please acknowledge you will not make changes to any scaffold or Fall Protection',
            //'question20.required' => 'Please acknowledge you have signed in to the NSW service Covid safe checkin', // Covid
            //'question21.required' => 'Please acknowledge you wear a mask and observe Gov directives', // Covid
            //'question22.required' => 'Please acknowledge you understand the current NSW Health orders and will comply with its requirements in relation covid', // Covid
            'location.required'   => 'Please provide the location of hazard.',
            'rating.required'     => 'Please provide the risk rating of hazard.',
            'reason.required'     => 'Please provide the reason for unsafe worksite.',
            'action.required'     => 'Please provide the actions to have taken to make the site safe.',
        ];

        $rules = ['media' => 'mimes:jpg,jpeg,png,gif,bmp,m4v,avi,flv,mp4,mov'];
        $questions = [];

        //
        // Add the required questions for various Checkin types (normal/store/truck/supervisor)
        //
        if (request('checkin'))  // Regular tradie questions
            $questions = [1, 2, 3, 4, 5, 6, 7, 8, 19];
        elseif (request('checkinTruck'))   // Truck Checkin questions
            $questions = [2, 3, 14];
        elseif (request('checkinStore'))   // Store Checkin questions
            $questions = [2, 7, 9, 10, 11, 12, 13];
        elseif (request('checkinSupervisor'))   // Store Checkin questions
            $questions = [];

        // create validation rules
        foreach ($questions as $q)
            $rules = $rules + ["question{$q}" => 'required'];

        // User has marked site as Unsafe is lodging a hazard report
        if (!request('safe_site'))
            $rules = $rules + ['location' => 'required', 'rating' => 'required', 'reason' => 'required', 'action' => 'required'];

        request()->validate($rules, $mesg); // Validate
        //dd(request()->all());


        if (request()->has('safe_site'))
            $site->attendance()->save(new SiteAttendance(['safe_site' => '1']));
        else {
            /*if ($request->has('checkinTrade')) {
                $worksite = Site::find($site->id);

                return view('site/checkinTradeFail', compact(['worksite']));
            }*/

            $site->attendance()->save(new SiteAttendance(['safe_site' => '0']));

            // Create Hazard + attach to site
            if (request()->has('action_required'))
                $hazard = $site->hazards()->save(new SiteHazard(request()->only('action_required', 'reason', 'location', 'rating')));
            else
                $hazard = $site->hazards()->save(new SiteHazard(request()->only('reason', 'location', 'rating')));

            // Create action taken + attach to hazard
            if ($hazard) {
                $action = Action::create(['action' => request('action'), 'table' => 'site_hazards', 'table_id' => $hazard->id]);
                $hazard->touch(); // update timestamp

                // Handle attached Photo or Video
                if (request()->hasFile('media'))
                    $hazard->saveAttachedMedia(request()->file('media'));

                // Email hazard
                $hazard->emailHazard($action);
            }
        }

        // Store Checkin questions
        if (request('checkinSupervisor')) {
            // Determine if any ressponses to questions require further actions
            $super_questions = [
                '100' => 'Is the site adequately secured against entry by unauthorised persons.',
                '101' => 'Are Public areas unobstructed and/or adequately protected.',
                '102' => 'Principal Contractor and Building Certifier signage and emergency contact details displayed and clearly visible from outside the workplace.',
                '103' => 'Have all workers completed site sign in.',
                '104' => 'Is the layout of the site maintained to allow persons to enter, exit, and move within it safely - under both normal working conditions and in an emergency situation.',
                '105' => 'Has adequate housekeeping and hygiene of the overrall site been maintained.',
                '106' => 'Are adequate facilities for workers provided - including toilets, drinking water, washing facilities and appropriate eating areas, and have they been suitably maintained.',
                '107' => 'Have you reviewed the site Asbestos Register/Hazardous Materials report for the site and are aware of the presence of identified asbestos.',
                '108' => 'Are on site workers aware of the presence of any hazardous materials as applicable to their tasks.',
                '109' => 'Has electricity been appropriately terminated/isolated in reference to the work taking place (including demolition).',
                '110' => 'Are approach distances for work near low voltage overhead service lines and/or overhead powerlines adhered to.',
                '111' => 'Are tiger tails installed as applicable to provide visual indicator as to presence of overhead powerlines nearby work activities/areas.',
                '112' => 'Are tooling & equipment safety guards in place as applicable and in suitable condition.',
                '113' => 'Has portable electrical equipment tested and tagged within 3 months and evidence of testing affixed to the equipment by physical tag.',
                '114' => 'Are portable Residual Current Devices (RCD) used in conjunction with all portable electrical equipment that has power supplied by a plug and lead.',
                '115' => 'Are ladders being used on site (as a means of access or for tasks).',
                '116' => 'Are ladder(s) in good condition, appropriate for the task and set up correctly.',
                '117' => 'Is there any elevated work areas? (including but not limited to scaffolds, mezzanines, work on roofs etc).',
                '118' => 'Have suitable material/containment screens been affixed by a competent person to scaffold/roof rail/elevated work areas to arrest the free fall of objects to area below as applicable?',
                '119' => 'Have appropriate exclusion zones been established as applicable to address the risk of workers and others below being struck by any objects that may fall/be dropped/thrown from elevated work areas',
                '120' => 'Is adequate edge protection installed to perimeters',
                '121' => 'Are penetrations and openings in floors/work surfaces suitably protected',
                '122' => 'Are fragile roof materials/floor surfaces (such as skylights, plastic roof sheets etc) suitably protected',
                '123' => 'Guardrailing incorporates a top-rail between 900 mm and 1100 mm above the working surface, a mid-rail and toeboards (except where it may be impractical to do so and alternative control measures, such as ‘no go’ zones, to ensure no persons are at risk of being hit by falling objects from the work above)',
                '124' => 'Are scaffolds erected on the site',
                '125' => 'Has scaffold exceeding a deck height of 4m erected by a licensed scaffolder & handover certificate available',
                '126' => 'Safe Work Load (SWL) of scaffold bays not exceeded? (including weight of persons, tooling, materials etc)',
                '127' => 'Is the scaffold complete (platform full width, handrail, toeboards and access to platforms compliant)',
                '128' => 'Gaps between the face of the building or structure and the erected scaffold do not exceed 225mm',
                '129' => 'Is edge protection provided at every open edge of the work platform',
                '130' => '4 metre approach distance from overhead powerlines has been maintained in any direction where metallic scaffold is erected',
                '131' => 'Electrical wires or apparatus that pass through a scaffold have been de-energised or fully enclosed to the requirements of the network operator',

            ];
            $require_action = [100, 101, 102, 103, 104, 105, 106, 107, 108, 109, 110, 111, 112, 113, 114];
            if (request('question115')) // Ladders being used
                $require_action = array_merge($require_action, [116]);

            if (request('question117')) // Falling Objects + Heights
                $require_action = array_merge($require_action, [118, 119, 120, 121, 122, 123]);

            if (request('question124')) // Falling Objects + Heights
                $require_action = array_merge($require_action, [125, 126, 127, 128, 129, 130, 131]);

            //dd($require_action);

        } else {
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
        }

        Toastr::success("Checked in");

        //$worksite = $site;
        //dd($site);

        // Display Site Specific Alerts
        if ($site->notify()->count()) {
            $intended_url = '/dashboard';

            return view('comms/notify/alertsite', compact('intended_url', 'site'));
        }


        return redirect('/dashboard');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store()
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
     * Update the specified resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function update($id)
    {
        //
    }
}
