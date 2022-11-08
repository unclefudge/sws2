<?php

namespace App\Http\Controllers\Misc;

use DB;
use PDF;
use Mail;
use Session;
use App\User;
use App\Models\Company\Company;
use App\Models\Company\CompanyDoc;
use App\Models\Company\CompanyDocReview;
use App\Models\Company\CompanyDocReviewFile;
use App\Models\Site\Planner\Trade;
use App\Models\Site\Planner\Task;
use App\Models\Site\Site;
use App\Models\Site\SiteDoc;
use App\Models\Site\Planner\SiteAttendance;
use App\Models\Site\Planner\SiteCompliance;
use App\Models\Site\Planner\SitePlanner;
use App\Models\Site\Planner\SiteRoster;
use App\Models\Site\SiteQa;
use App\Models\Site\SiteQaItem;
use App\Models\Site\SiteQaCategory;
use App\Models\Site\SiteQaAction;
use App\Models\Site\SiteAsbestosRegister;
use App\Models\Site\SiteAccident;
use App\Models\Site\Incident\SiteIncident;
use App\Models\Site\Incident\SiteIncidentPeople;
use App\Models\Site\SiteProjectSupply;
use App\Models\Site\SiteProjectSupplyProduct;
use App\Models\Site\SiteProjectSupplyItem;
use App\Models\Site\SiteExtension;
use App\Models\Site\SiteExtensionSite;
use App\Models\Safety\ToolboxTalk;
use App\Models\Safety\WmsDoc;
use App\Models\Safety\SafetyDoc;
use App\Models\Safety\SafetyDataSheet;
use App\Models\Comms\Todo;
use App\Models\Comms\TodoUser;
use App\Models\Comms\SafetyTip;
use App\Models\Misc\Equipment\Equipment;
use App\Models\Misc\Equipment\EquipmentCategory;
use App\Models\Misc\Equipment\EquipmentLocation;
use App\Models\Misc\Equipment\EquipmentLocationItem;
use App\Models\Misc\Equipment\EquipmentLost;
use App\Models\Misc\Equipment\EquipmentLog;

//use App\Models\Misc\FormQuestion;
//use App\Models\Misc\FormResponse;
use App\Models\Misc\Permission2;
use App\Models\Misc\Action;
use App\Models\Misc\Form\Form;
use App\Models\Misc\Form\FormTemplate;
use App\Models\Misc\Form\FormPage;
use App\Models\Misc\Form\FormSection;
use App\Models\Misc\Form\FormQuestion;
use App\Models\Misc\Form\FormOption;
use App\Models\Misc\Form\FormLogic;
use App\Models\Support\SupportTicket;
use App\Models\Support\SupportTicketAction;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Webklex\PDFMerger\Facades\PDFMergerFacade as PDFMerger;

class PagesController extends Controller {

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return Response
     */
    public function index()
    {
        $worksite = '';

        // If Site login show check-in form
        if (Session::has('siteID')) {
            $worksite = Site::findOrFail(Session::get('siteID'));
            if ($worksite && !$worksite->isUserOnsite(Auth::user()->id)) {
                // Check if User is of a special trade  ie Certifier
                $special_trade_ids = ['19'];  // 19 - Certifier
                if (count(array_intersect(Auth::user()->company->tradesSkilledIn->pluck('id')->toArray(), $special_trade_ids)) > 0) {
                    if (Auth::user()->company->tradesSkilledIn->count() == 1) {
                        // User only has 1 trade which is classified as a 'special' trade
                        return view('site/checkinTrade', compact('worksite'));
                    } else {
                        // User has multiple trades so determine what trade they are loggin as today
                    }
                }

                if ($worksite->id == 254) // Truck
                    return view('site/checkinTruck', compact('worksite'));
                if ($worksite->id == 25) // Store
                    return view('site/checkinStore', compact('worksite'));

                return view('site/checkin', compact('worksite'));
            }
        }

        // Auto redirect to password reset if flagged
        if (Auth::user()->password_reset)
            return redirect('/user/' . Auth::user()->id . '/resetpassword');

        // If primary user and incompleted company Signup - redirect to correct step
        if (Auth::user()->company->status == 2 and Auth::user()->company->primary_user == Auth::user()->id) {
            if (Auth::user()->company->signup_step == 2) $url = '/signup/company/';
            if (Auth::user()->company->signup_step == 3) $url = '/signup/workers/';
            if (Auth::user()->company->signup_step == 4) $url = '/signup/summary/';

            return redirect($url . Auth::user()->company->id);
        }


        return view('pages/home', compact('worksite'));
    }

    public function testcal()
    {
        return view('pages/testcal');
    }

    public function testfilepond()
    {
        return view('pages/testfilepond');
    }

    public function userlog()
    {
        if (Auth::user()->id == 3)
            return view('pages/userlog');

        return view('errors/404');
    }

    public function userlogAuth()
    {
        if (Auth::user()->id == 3) {
            $userlog = User::find(request('user'));
            Auth::login($userlog);

            return redirect("/home");
        }

        return view('errors/404');
    }


    public function settings()
    {
        return view('manage/settings/list');
    }

    public function GetDirectorySize($path)
    {
        $bytestotal = 0;
        $path = realpath($path);
        if ($path !== false && $path != '' && file_exists($path)) {
            foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS)) as $object) {
                $bytestotal += $object->getSize();
            }
        }

        return $bytestotal;
    }


    public function quick()
    {
        /*
        echo "<b>Creating Merged PDF</b></br>";

        $site = Site::find(403);
        $site->createWhsManagementPlanPDF();

        $mergedPDF = PDFMerger::init();

        $cover = public_path('/filebank/tmp/report/3/QA 7865-IsikSantos-Bronte (403) 20220623143956.pdf');
        $master = public_path('WHS Management Plan.pdf');
        $mergedPDF->addPDF($cover, 'all');
        $mergedPDF->addPDF($master, 'all');

        $mergedPDF->merge();
        $mergedPDF->save(public_path('/filebank/tmp/merged_result.pdf'));

*/
        /*
                echo "<b>Creating Site Extension for Active Sites</b></br>";

                $hide_site_code = ['0000', '0001', '0002', '0003', '0004', '0005', '0006', '0007', '0008', '1234', '1235'];
                $sites = Site::where('company_id', 3)->where('status', 1)->where('special', null)->get();
                //$sites = Auth::user()->authSites('view.site.extension', '1')->whereNotIn('code', $hide_site_code);

                $today = Carbon::now();
                $mon = new Carbon('monday this week');
                echo "Today:" . $today->format('d/m/Y') . "<br>";
                echo "Mon:" . $mon->format('d/m/Y') . "<br>";

                $data = [];
                $prac_yes = $prac_no = [];
                foreach ($sites as $site) {
                    $start_job = SitePlanner::where('site_id', $site->id)->where('task_id', 11)->first();
                    // Show only site which Job Start has before today
                    if ($start_job && $start_job->from->lte($today)) {
                        $prac_completion = SitePlanner::where('site_id', $site->id)->where('task_id', 265)->first();
                        $site_data = [
                            'id'              => $site->id,
                            'name'            => $site->name,
                            'completion_date' => ($prac_completion) ? $prac_completion->from : '',
                            'completion_ymd'  => ($prac_completion) ? $prac_completion->from->format('ymd') : '',
                        ];
                        if ($prac_completion)
                            $prac_yes[] = $site_data;
                        else
                            $prac_no[] = $site_data;
                    }
                }

                usort($prac_yes, function ($a, $b) {
                    return $a['completion_ymd'] <=> $b['completion_ymd'];
                });

                usort($prac_no, function ($a, $b) {
                    return $a['name'] <=> $b['name'];
                });

                $data = $prac_yes + $prac_no;

                //dd($data);

                $ext = SiteExtension::whereDate('date', $mon->format('Y-m-d'))->first();
                if (!$ext)
                    $ext = SiteExtension::create(['date' => $mon->toDateTimeString(), 'status' => 1]);

                foreach ($data as $site) {
                    $ext_site = SiteExtensionSite::where('extension_id', $ext->id)->where('site_id', $site['id'])->first();
                    if (!$ext_site)
                        $ext_site = SiteExtensionSite::create(['extension_id' => $ext->id, 'site_id' => $site['id'], 'completion_date' => $site['completion_date']]);
                }

                $ext->createPDF();

        */
        /*
        $today = Carbon::today();
        $one_year = Carbon::today()->subMonths(10)->format('Y-m-d');

        echo "<b>Creating Standard Details for Review $one_year</b></br>";
        $cat_ids = ['22', '23', '24', '25', '26', '27', '28', '29', '30', '31', '32', '33', '34', 35];
        $doc_ids = ['77', '78', '79', '80', '81', '82', '83', '84', '85', '86'];
        $docs = CompanyDoc::whereIn('category_id', $cat_ids)->whereDate('updated_at', '<', $one_year)->get();
        $docs = CompanyDoc::whereIn('id', $doc_ids)->get();
        foreach ($docs as $doc) {
            echo "[$doc->id] $doc->name<br>";
            $review_doc = CompanyDocReview::where('doc_id', $doc->id)->first();

            if (!$review_doc) {
                $review_doc = CompanyDocReview::create(['doc_id' => $doc->id, 'name' => $doc->name, 'stage' => '1', 'original_doc' => $doc->attachment, 'status' => 1, 'created_by' => '1', 'updated_by' => 1]);
                $review_doc->createAssignToDo(7); // Gary
                $action = Action::create(['action' => 'Standard Details review initiated', 'table' => 'company_docs_review', 'table_id' => $review_doc->id]);
            }
        }
        */
        /*
        // Update Old Single Line Client Name into Title-First-Last fields

        $titles = ['MR', 'MRS', 'DR', 'MS'];
        $sites = Site::where('company_id', 3)->get();
        foreach ($sites as $site) {
            $one = $two = $rest1 = $rest =null;

            $desc = $site->client_phone_desc;
            if ($desc) {
                $str = trim($desc);
                if (strpos($str, ' ') !== false) {
                    // Multi word
                    list($one, $rest) = explode(' ', $desc, 2);
                    if (strpos($rest, ' ') !== false) {
                        list($two, $rest2) = explode(' ', $rest, 2);
                        $rest = $rest2;
                    } else
                        $two = $rest1;
                } else {
                    // Single word
                    $one = $str;
                }
                if (in_array(strtoupper($one), $titles)) {
                    $title = ucfirst(strtolower($one));;
                    $first = $two;
                    $last = $rest;
                } else {
                    $title = null;
                    $first = $one;
                    $last = $two;
                }
                echo "[$site->id] 1:$site->client_phone_desc  => [$title] [$first] [$last]<br>";
                $site->client1_title = $title;
                $site->client1_firstname = $first;
                $site->client1_lastname = $last;
                $site->client1_lastname = $last;
            }

            $desc = $site->client_phone2_desc;
            if ($desc) {
                $str = trim($desc);
                if (strpos($str, ' ') !== false) {
                    // Multi word
                    list($one, $rest) = explode(' ', $desc, 2);
                    if (strpos($rest, ' ') !== false) {
                        list($two, $rest2) = explode(' ', $rest, 2);
                        $rest = $rest2;
                    } else
                        $two = $rest1;
                } else {
                    // Single word
                    $one = $str;
                }
                if (in_array(strtoupper($one), $titles)) {
                    $title = ucfirst(strtolower($one));;
                    $first = $two;
                    $last = $rest;
                } else {
                    $title = null;
                    $first = $one;
                    $last = $two;
                }
                echo "[$site->id] 2:$site->client_phone2_desc  => [$title] [$first] [$last]<br>";
                $site->client2_title = $title;
                $site->client2_firstname = $first;
                $site->client2_lastname = $last;
            }

            $site->client1_mobile = $site->client_phone;
            $site->client2_mobile = $site->client_phone2;
            $site->client1_email = $site->client_email;
            $site->client2_email = $site->client_email2;
            $site->timestamps = false;
            $site->save();
        }
        */

        //$doc = CompanyDoc::find(113);
        //$doc->emailRenewal(['fudge@jordan.net.au']);
        /*
        echo "<b>Old Sites</b></br>";
        $sites = Site::all();
        $today = Carbon::now();
        $subyears = Carbon::now()->subYears(2);
        $site_list = [];
        foreach ($sites as $site) {
            if ($site->completed && $site->completed->lt($subyears) && $site->updated_at->lt($subyears) && $site->code > 1000) {
                $site_list[] = $site->id;
                $site->archived_files = 1;
                $site->timestamps = false;
                $site->save();
            }
        }

        $sites = Site::whereIn('id', $site_list)->orderBy('completed')->get();

        echo "<br>Count: " . $site->count() . " - " . $subyears->format('d/m/Y') . "<br>";
        $total_size = 0;
        foreach ($sites as $site) {
            //$dir_size = $this->GetDirectorySize();
            $f = public_path("/filebank/site/".$site->id);
            $io = popen ( '/usr/bin/du -sk ' . $f, 'r' );
            $size = fgets ( $io, 4096);
            $size = substr ( $size, 0, strpos ( $size, "\t" ) );
            pclose ( $io );
            //echo 'Directory: ' . $f . ' => Size: ' . $size . "<br>";
            $diff_dates = ($site->completed->format('d/m/Y') != $site->updated_at->format('d/m/Y')) ? '*' : '-';
            echo "[$site->id] [$size] $diff_dates " . $site->completed->format('d/m/Y') . ' - ' . $site->updated_at->format('d/m/Y') . "<br>";
            if ($size)
                $total_size = $total_size + $size;
        }

        echo "---------------<br>Total: $total_size kb  ".($total_size/1000)." mb ".($total_size/1000000)." gb <br>";
        */

        /*echo "<b>Converting SDS </b></br>";
        $sds_docs = SafetyDoc::where('type', 'SDS')->get();
        foreach ($sds_docs as $sds) {
            $sds_request['name'] = $sds->name;
            $sds_request['attachment'] = $sds->attachment;
            $sds_request['company_id'] = $sds->company_id;
            $sds_request['status'] = $sds->status;

            $doc = SafetyDataSheet::where('name', $sds->name)->first();

            $doubles = 1;

            if (!$doc && $doubles ) {
                $doc = SafetyDataSheet::create($sds_request);
                $doc->created_by = $sds->created_by;
                $doc->created_at = $sds->created_at;
                $doc->save();
                echo "created ";
                if ($doc->name == 'Styroboard And Expanded Polystyrene _ Foamex _ Issued March 2019')
                    $doubles = 0;
            } else
                echo "add [$sds->category_id] ";

            $doc->categories()->attach([$sds->category_id]);
            echo "[$doc->id] $sds->name <br>";
        }*/


        /*
        echo "<b>Importing Accident </b></br>";
        $accidents = SiteAccident::all();
        foreach ($accidents as $accident) {
            if ($accident->site->company_id == 3) {
                echo "id:$accident->id name:$accident->name<br>";
                $incident_request = [];
                $incident_request['site_id'] = $accident->site_id;
                $incident_request['site_name'] = $accident->site->name;
                $incident_request['site_supervisor'] = $accident->supervisor;
                $incident_request['date'] = $accident->date;
                $incident_request['location'] = $accident->location;
                $incident_request['damage'] = $accident->damage;
                $incident_request['describe'] = $accident->info;
                $incident_request['exec_actions'] = $accident->action;
                $incident_request['resolved_at'] = $accident->resolved_at;
                $incident_request['notes'] = $accident->notes;
                $incident_request['status'] = $accident->status;
                $incident_request['company_id'] = $accident->site->company_id;
                $incident_request['step'] = '0';
                $incident_request['created_by'] = $accident->created_by;
                $incident_request['created_at'] = $accident->created_at;


                $incident = SiteIncident::create($incident_request);
                $incident->created_by = $accident->created_by;
                $incident->created_at = $accident->created_at;
                $incident->updated_at = $accident->updated_at;
                $incident->updated_by = $accident->updated_by;
                $incident->timestamps = false;
                $incident->save();


                // Add Injured
                $people_request = [];
                $people_request['incident_id'] = $incident->id;
                $people_request['type'] = '9';
                $people_request['name'] = $accident->name;
                $people_request['employer'] = $accident->company;
                $people_request['occupation'] = $accident->occupation;
                $person = SiteIncidentPeople::create($people_request);

                // Add responses
                FormResponse::create(['question_id' => '14', 'option_id' => '20', 'table' => 'site_incidents', 'table_id' => $incident->id, 'info' => $accident->referred]);
                FormResponse::create(['question_id' => '21', 'option_id' => '49', 'table' => 'site_incidents', 'table_id' => $incident->id, 'info' => $accident->nature]);

                // Add notes
                $actions = Action::where('table', 'site_accidents')->where('table_id', $accident->id)->get();
                foreach ($actions as $act) {
                    $newAct = Action::create(['table' => 'site_incidents', 'table_id' => $incident->id, 'action' => $act->action]);
                    $newAct->created_by = $act->created_by;
                    $newAct->updated_by = $act->updated_by;
                    $newAct->created_at = $act->created_at;
                    $newAct->updated_at = $act->updated_at;
                    $newAct->timestamps = false;
                    $newAct->save();
                }


                // Add Todoos
                $todos = Todo::where('type', 'accident')->where('type_id', $accident->id)->get();
                foreach ($todos as $todo) {
                    $newToDo = Todo::create(['type'    => 'incident', 'type_id' => $incident->id, 'name' => "Site Incident Task @ " . $incident->site->name, 'info' => $todo->info, 'due_at' => $todo->due_at,
                                             'done_at' => $todo->done_at, 'done_by' => $todo->done_by, 'attachment' => $todo->attachment, 'comments' => $todo->comments,
                                             'status'  => $todo->status, 'company_id' => $todo->company_id]);
                    $newToDo->created_by = $todo->created_by;
                    $newToDo->updated_by = $todo->updated_by;
                    $newToDo->created_at = $todo->created_at;
                    $newToDo->updated_at = $todo->updated_at;
                    $newToDo->timestamps = false;
                    $newToDo->save();
                    $newToDoUser = ToDoUser::create(['todo_id' => $newToDo->id, 'user_id' => $todo->assignedTo()->first()->id]);
                }
            }
        }
        */


        /*
        echo "<b>Active Equipment Locations with no items </b></br>";
        $locations = EquipmentLocation::where('status', 1)->get();
        foreach ($locations as $location) {
            if ($location->items->count() == 0) {
                if (!$location->site_id)
                    echo "*O [$location->id] $location->name<br>";
                elseif ($location->site_id && $location->site->status == 0)
                    echo "*S [$location->id] $location->name<br>";
                else
                    echo "-- [$location->id] $location->name<br>";
            }

        }*/


        /*
        echo "<b>Archiving SWMS for inactive companies </b></br>";
        $sws = WmsDoc::where('status', '>', 0)->where('master', 0)->get();

        foreach ($sws as $doc) {
            if ($doc->company->status == 0) {
                echo $doc->company->name . ' - Inactive<br>';
                $doc->status = -1;
                $doc->save();
            }
        }*/


        /*
        echo "<b>Fixing broken QA items </b></br>";
        $qas = SiteQa::where('status', '>', 0)->where('master', 0)->get();

        foreach ($qas as $qa) {
            foreach ($qa->items as $item) {
                if ($item->done_by === null && $item->status == 0 && $item->sign_by) {
                    echo "<br>[$qa->id] $qa->name (" . $qa->site->name . ")<br>- $item->name doneBy[$item->done_by] signBy[$item->sign_by] status[$item->status]<br>";
                    $item->status = 1;

                    // Check Planner which company did the task
                    $planned_task = SitePlanner::where('site_id', $qa->site_id)->where('task_id', $item->task_id)->first();
                    if ($planned_task && $planned_task->entity_type == 'c' && !$item->super)
                        $item->done_by = $planned_task->entity_id;

                    $item->save();
                }
            }
        }
        */

        /*
        echo "<b>Fixing toolbox images </b></br>";
        $toolboxs = ToolboxTalk::all();

        foreach ($toolboxs as $toolbox) {
            if (preg_match('/safeworksite.net/', $toolbox->overview)) {
                $toolbox->overview = preg_replace('/safeworksite.net/', 'safeworksite.com.au', $toolbox->overview);
                echo "O[$toolbox->id] $toolbox->name<br>";
                $toolbox->save();
            }
            if (preg_match('/safeworksite.net/', $toolbox->hazards)) {
                $toolbox->hazards = preg_replace('/safeworksite.net/', 'safeworksite.com.au', $toolbox->hazards);
                echo "H[$toolbox->id] $toolbox->name<br>";
                $toolbox->save();
            }
            if (preg_match('/safeworksite.net/', $toolbox->controls)) {
                $toolbox->controls = preg_replace('/safeworksite.net/', 'safeworksite.com.au', $toolbox->controls);
                echo "C[$toolbox->id] $toolbox->name<br>";
                $toolbox->save();
            }
            if (preg_match('/safeworksite.net/', $toolbox->further)) {
                $toolbox->further = preg_replace('/safeworksite.net/', 'safeworksite.com.au', $toolbox->further);
                echo "F[$toolbox->id] $toolbox->name<br>";
                $toolbox->save();
            }
        }*/

        /*
                echo "<b>Old/New QA's</b></br>";
                // Old Templates
                $trigger_ids_old = [];
                $active_templates_old = SiteQa::where('master', '1')->where('status', '1')->where('company_id', '3')->where('id', '<', 100)->get();
                foreach ($active_templates_old as $qa) {
                    foreach ($qa->tasks() as $task) {
                        if (isset($trigger_ids_old[$task->id])) {
                            if (!in_array($qa->id, $trigger_ids_old[$task->id]))
                                $trigger_ids_old[$task->id][] = $qa->id;
                        } else
                            $trigger_ids_old[$task->id] = [$qa->id];
                    }
                }
                ksort($trigger_ids_old);

                // New Templates
                $trigger_ids_new = [];
                $active_templates_new = SiteQa::where('master', '1')->where('status', '0')->where('company_id', '3')->where('id', '>', 100)->get();
                foreach ($active_templates_new as $qa) {
                    foreach ($qa->tasks() as $task) {
                        if (isset($trigger_ids_new[$task->id])) {
                            if (!in_array($qa->id, $trigger_ids_new[$task->id]))
                                $trigger_ids_new[$task->id][] = $qa->id;
                        } else
                            $trigger_ids_new[$task->id] = [$qa->id];
                    }
                }
                ksort($trigger_ids_new);

                echo "<br>OLD<br>";
                print_r($trigger_ids_old);
                echo "<br>NEW<br>";
                print_r($trigger_ids_new);


                $qas = SiteQa::all();
                $sites = [];
                $active = 0;
                foreach ($qas as $qa) {
                    if (!$qa->master && $qa->status > 0) {
                        $sites[$qa->site->code] = $qa->site->name;
                    }
                }
                asort($sites);

                echo "<br>Total invidual reports: $active<br><br>Site<br>";
                foreach ($sites as $id => $name) {
                    echo "$id - $name<br>";
                }
        */

        /*

        echo "<b>QA cats</b></br>";
        $qas = SiteQa::all();
        $map = [1  => 1, 44 => 2, 45 => 3, 46 => 4, 47 => 5, 48 => 6, 49 => 7, 50 => 8, 51 => 9, 52 => 10, 53 => 11, 54 => 12, 55 => 13, 56 => 14, 57 => 15, 58 => 16, 59 => 17, 60 => 18,
                63 => 19, 64 => 20, 65 => 21, 66 => 22, 67 => 23, 68 => 24, 69 => 25, 70 => 26, 71 => 27, 72 => 28, 73 => 29, 74 => 30, 91 => 31];
        foreach ($qas as $qa) {
            if ($qa->master) {
                $cat = SiteQaCategory::find($map[$qa->id]);
                //echo "[$qa->id]  Name: $qa->name* - $cat->name<br>";
                echo "$qa->name*<br>$cat->name<br><br>";
                $qa->category_id = $cat->id;
                $qa->save();
            } else {
                $cat = SiteQaCategory::find($map[$qa->master_id]);
                //echo "[$qa->id]  Name: $qa->name* - $cat->name<br>";
                echo "$qa->name*<br>$cat->name<br><br>";
                $qa->category_id = $cat->id;
                $qa->save();
            }

        }*/

        /*
        $today = Carbon::today();
        echo "<b>Docs being marked as expired</b></br>";
        $docs = CompanyDoc::where('status', 1)->whereDate('expiry', '<', $today->format('Y-m-d'))->get();
        if ($docs->count()) {
            foreach ($docs as $doc) {
                $company =  Company::find($doc->for_company_id);
                echo "id[$doc->id] $company->name_alias ($doc->name) [" . $doc->expiry->format('d/m/Y') . "]<br>";
                $doc->status = 0;
                $doc->save();
            }
        }*/

        /*
        echo "Table of Tradies = Leading Hands<br><br>";
        $users = \App\Models\Company\Company::find(3)->users(1);
        echo '<table><td>Username</td><td>Name</td><td>Company</td><td>Email</td></tr>';
        foreach ($users as $user) {
            if ($user->hasAnyRole2('ext-leading-hand|tradie|labourers'))
                echo "<tr><td>$user->username</td><td>$user->fullname</td><td>" . $user->company->name . "</td><td>$user->email</td></tr>";
        }

        echo "</table>";
        echo "<br><br>Completed<br>-------------<br>";
        */

        /*
        echo "Fix QA Reports Missing Supervisor to Complete Flags<br><br>";
        $qa_items = \App\Models\Site\SiteQaItem::where('master', 0)->get();
        $bad = [];
        $sites = [];
        foreach ($qa_items as $item) {
            $master = \App\Models\Site\SiteQaItem::where('id', $item->master_id)->first();
            if ($master && $item->super != $master->super) {
                if (!$item->document->status && !$item->sign_by)
                    $item->super = $master->super;
                else {
                    $item->super = $master->super;
                    $item->done_by = 3;
                    $on = ($item->super) ? 'Y' : 'N';
                    echo $on . ':' . $item->document->name . '** ' . $item->name . '**<br>';
                    $bad[$item->document->id] = '[' . $item->document->status . '] ' . $item->document->updated_at->format('d/m/Y') . ' - ' . $item->document->name . " Site:" . $item->document->site->name;
                    $sites[$item->document->site->id] = ($item->document->site->completed) ? $item->document->site->name . ' (' . $item->document->site->completed->format('d/m/Y') . ')' : $item->document->site->name;
                }
                //$item->save();
            }

        }
        echo "<br><br>Completed<br>-------------<br>";
        echo "Total Documents" . count($bad) . '<br>';
        foreach ($bad as $id => $name)
            echo "$id: $name<br>";

        echo "<br><br>Total Sites" . count($sites) . '<br>';
        //asort($sites);
        //foreach ($sites as $id => $name)
        //    echo "$id: $name<br>";
        */


        /*
        echo "Equipment transfers TASKS<br><br>";
        $todos = \App\Models\Comms\Todo::where('type', 'equipment')->whereDate('created_at', '>', '2019-01-01')->get();
        foreach ($todos as $todo) {
            $location =  \App\Models\Misc\Equipment\EquipmentLocation::find($todo->type_id);
            echo "<br>[$todo->id] Equipment Transfer - ".$todo->created_at->format('d/m/Y')."<br>";
            echo preg_replace('/Please transfer equipment from the locations below./', '', $todo->info)."<br>";
            echo $location->itemsList();
        }
        echo "<br><br>Completed<br>-------------<br>";

        echo "<br><br>Bad Equipment Locations<br><br>";
        $locations =  \App\Models\Misc\Equipment\EquipmentLocation::where('site_id', null)->where('other', null)->get();
        foreach ($locations as $location) {
            $user = \App\User::find($location->created_by);
            echo "<br>[$location->id] Location created by $user->fullname (".$location->created_at->format('d/m/Y g:i a').")<br>";
            echo $location->itemsList();
        }
        echo "<br><br>Completed<br>-------------<br>";
        */


        /*echo "<br><br>Signed QA items with status 0<br><br>";
        $qas = \App\Models\Site\SiteQa::where('status', '>', 0)->where('master', 0)->get();
        foreach ($qas as $qa) {
            foreach ($qa->items as $item) {
                if ($item->status == 0 && $item->sign_by) {
                    echo "[$qa->id]-[$item->id] " . $qa->site->name . ": $qa->name - $item->name<br>";
                    $item->status = 1;
                    $item->save();
                }
            }
        }*/
        /*
        echo "<br><br>Export Toolbox Talk<br><br>";

        $toolbox_id = 286;
        $talk = \App\Models\Safety\ToolboxTalk::find($toolbox_id);
        $todos = \App\Models\Comms\Todo::where('type', 'toolbox')->where('type_id', $toolbox_id)->get();
        $x = 1;

        $insert_todo = "INSERT INTO `todo` (`id`, `name`, `info`, `type`, `type_id`, `due_at`, `done_at`, `done_by`, `priority`, `attachment`, `comments`, `status`, `company_id`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`)
    VALUES<br>";
        $insert_todo_user = "INSERT INTO `todo_user` (`id`,`todo_id`, `user_id`, `opened`, `opened_at`) VALUES<br>";
        foreach ($todos as $todo) {
            $todo_user = \App\Models\Comms\TodoUser::where('todo_id', $todo->id)->first();
            if ($todo_user) {
                $done_at = ($todo->done_at) ? "'$todo->done_at'" : 'NULL';
                $opened_at = ($todo_user->opened_at) ? "'$todo_user->opened_at'" : 'NULL';
                //echo "($todo->id, '$todo->name', '$todo->info', '$todo->type', $todo->type_id, '$todo->due_at', $done_at, $todo->done_by, $todo->priority, NULL, NULL, $todo->status, $todo->company_id, $todo->created_by, $todo->updated_by, '$todo->created_at', '$todo->updated_at', NULL),<br>";
                $insert_todo .= "($todo->id, '$todo->name', '$todo->info', '$todo->type', $todo->type_id, '$todo->due_at', $done_at, $todo->done_by, $todo->priority, NULL, NULL, $todo->status, $todo->company_id, $todo->created_by, $todo->updated_by, '$todo->created_at', '$todo->updated_at', NULL),<br>";
                $insert_todo_user .= "($todo_user->id, $todo_user->todo_id, $todo_user->user_id, $todo_user->opened, $opened_at ),<br>";
                //echo $x++ . " ToDo [$todo->id] - $todo->name - UserID:$todo_user->user_id <br>";
                $ids[] = $todo_user->id;
            }
        }

        $insert_todo = rtrim($insert_todo, ',<br>') . ';';
        $insert_todo_user = rtrim($insert_todo_user, ',<br>') . ';';
        echo $insert_todo;
        echo "<br><br>-----<br>";
        echo $insert_todo_user;
        */

        /*
        echo "<br><br>Move security toggle to permission<br><br>";
        $users = \App\User::all();
        foreach ($users as $user) {
            if ($user->security) {
                echo $user->name . "<br>";
                // Attach permissions required for primary user
                $user->attachPermission2(1, 99, $user->company_id);  // View all users
                $user->attachPermission2(3, 99, $user->company_id);  // Edit all users
                $user->attachPermission2(5, 1, $user->company_id);   // Add users
                $user->attachPermission2(7, 1, $user->company_id);   // Dell users
                $user->attachPermission2(241, 1, $user->company_id); // Signoff users
                $user->attachPermission2(379, 99, $user->company_id);   // View users contact
                $user->attachPermission2(380, 99, $user->company_id);   // Edit users contact
                $user->attachPermission2(384, 99, $user->company_id);   // View users security
                $user->attachPermission2(385, 99, $user->company_id);   // Edit users security
                $user->attachPermission2(9, 99, $user->company_id);  // View company details
                $user->attachPermission2(11, 99, $user->company_id); // Edit company details
                $user->attachPermission2(13, 1, $user->company_id); // Add company details
                $user->attachPermission2(15, 1, $user->company_id); // Del company details
                $user->attachPermission2(308, 99, $user->company_id); // View business details
                $user->attachPermission2(309, 99, $user->company_id); // Edit business details
                $user->attachPermission2(312, 1, $user->company_id); // Signoff business details
                $user->attachPermission2(313, 99, $user->company_id); // View contruction details
                $user->attachPermission2(314, 99, $user->company_id); // Edit contruction details
                $user->attachPermission2(317, 1, $user->company_id); // Signoff contruction details
                $user->attachPermission2(303, 99, $user->company_id); // View WHS details
                $user->attachPermission2(304, 99, $user->company_id); // Edit WHS details
                $user->attachPermission2(307, 1, $user->company_id); // Signoff WHS details
            }
        }*/

        /*
        echo "<br><br>Todo company doc completed but still active<br><br>";
        $todos = \App\Models\Comms\Todo::all();
        foreach ($todos as $todo) {
            if ($todo->status && $todo->type == 'company doc') {
                $doc = \App\Models\Company\CompanyDoc::find($todo->type_id);
                if ($doc) {
                    if ($doc->status == 1) {
                        //echo "ToDo [$todo->id] - $todo->name (".$doc->company->name.") ACTIVE DOC<br>";
                        //$todo->status = 0;
                        //$todo->done_at = Carbon::now();
                        //$todo->done_by = 1;
                        //$todo->save();
                    }
                    if ($doc->status == 0) {
                        if ($doc->company->activeCompanyDoc($doc->category_id)) {
                            echo "ToDo [$todo->id] - $todo->name (" . $doc->company->name . ") REPLACED DOC<br>";
                            $todo->status = 0;
                            $todo->done_at = Carbon::now();
                            $todo->done_by = 1;
                            $todo->save();
                        } else
                            echo "ToDo [$todo->id] - $todo->name (" . $doc->company->name . ") INACTIVE DOC<br>";

                    }

                } else {
                    echo "ToDo [$todo->id] - " . $todo->company->name . " (DELETED)<br>";
                }
            }
        }*/


        /*
        $company = \App\Models\Company\Company::find(125);
        echo "Site attendance - $company->name<br><br>";
        //print_r($company->staff->pluck('id')->toArray());
        $attendance = \App\Models\Site\Planner\SiteAttendance::whereIn('user_id', $company->staff->pluck('id')->toArray())->orderBy('date')->get();
        echo "<table>";
        foreach ($attendance as $attend) {
            echo "<tr>";
            echo "<td>".$attend->date->format('d/m/Y g:i a')."</td>";
            echo "<td>".$attend->user->fullname."</td>";
            echo "<td>".$attend->user->username."</td>";
            echo "<td>".$attend->site->name."</td>";
            echo "</tr>";
        }
        echo "</table>";*/

        /*
        echo "Todo assigned to inactive user<br><br>";
        $docs = \App\Models\Comms\Todo::all();
        foreach ($docs as $doc) {
            if ($doc->status) {
                foreach ($doc->users as $user) {
                    $u = User::find($user->user_id);
                    if (!$u->status)
                        echo "ToDo [$doc->id] - $doc->name ($u->fullname)<br>";
                }
            }
        }

        echo "<br><br>Todo company doc completed but still active<br><br>";
        $todos = \App\Models\Comms\Todo::all();
        foreach ($todos as $todo) {
            if ($todo->status && $todo->type == 'company doc') {
                $doc = \App\Models\Company\CompanyDoc::find($todo->type_id);
                if ($doc) {
                    if ($doc->status == 1)
                        echo "ToDo [$todo->id] - $todo->name ($doc->name)<br>";
                } else {
                    echo "ToDo [$todo->id] - $todo->name (DELETED)<br>";
                }
            }
        }*/


        /*echo "Child Company LH default permissions<br><br>";
        $lh =  DB::table('role_user')->where('role_id', 12)->get();
        foreach ($lh as $u) {
            $user = User::find($u->user_id);
            echo "$user->fullname<br>";
            $user->attachPermission2(1, 99, $user->company_id);
            $user->attachPermission2(3, 99, $user->company_id);
            $user->attachPermission2(5, 1, $user->company_id);
            $user->attachPermission2(7, 1, $user->company_id);
            $user->attachPermission2(241, 1, $user->company_id);
            $user->attachPermission2(9, 99, $user->company_id);
            $user->attachPermission2(11, 99, $user->company_id);
        }
        echo "Child Company CA default permissions<br><br>";
        $ca =  DB::table('role_user')->where('role_id', 13)->get();
        foreach ($ca as $u) {
            $user = User::find($u->user_id);
            echo "$user->fullname<br>";
            $user->attachPermission2(1, 99, $user->company_id);
            $user->attachPermission2(3, 99, $user->company_id);
            $user->attachPermission2(5, 1, $user->company_id);
            $user->attachPermission2(7, 1, $user->company_id);
            $user->attachPermission2(241, 1, $user->company_id);
            $user->attachPermission2(9, 99, $user->company_id);
            $user->attachPermission2(11, 99, $user->company_id);
        }
        echo "Child Company Tradie default permissions<br><br>";
        $ca =  DB::table('role_user')->where('role_id', 14)->get();
        foreach ($ca as $u) {
            $user = User::find($u->user_id);
            echo "$user->fullname<br>";
            $user->attachPermission2(9, 99, $user->company_id);
        }*/


        /*echo "Creating Primary + Secondary Users for existing Companies<br><br>";
        $companies = \App\Models\Company\Company::all();
        foreach ($companies as $company) {
            if ($company->staffStatus(1)->count() > 0) {
                echo "<br>$company->name " . count($company->staffStatus(1)) . "/" . count($company->staff) . "<br>---------------------------<br>";

                $lhs = $company->usersWithRole('leading.hand');
                if (count($lhs) > 1) {
                    echo "*********   2+ LH *************<br>";
                    foreach ($lhs as $lh) {
                        $inactive = ($lh->status) ? '' : ' *********** INACTIVE';
                        if ($company->id == 21 && $lh->id == 84) { // Dean Taylor
                            $company->primary_user = $lh->id;
                            echo $lh->fullname . "  => PRIMARY<br>";
                            $company->secondary_user = 83;
                            echo "Ian Taylor  => SECONDARY<br>";
                        } elseif ($company->id == 41 && $lh->id == 59) { // Syd Waster Jamie Ross
                            $company->primary_user = $lh->id;
                            echo $lh->fullname . "  => PRIMARY<br>";
                            $company->secondary_user = 301;
                            echo "David Clark  => SECONDARY<br>";
                        } elseif ($company->id == 61 && $lh->id == 17) { // Palace Painiting
                            $company->primary_user = $lh->id;
                            echo $lh->fullname . "  => PRIMARY<br>";
                            $company->secondary_user = 531;
                            echo "Richard Santosa  => SECONDARY<br>";
                        } elseif ($company->id == 109 && $lh->id == 272) { // Pegasus Roofing
                            $company->primary_user = $lh->id;
                            echo $lh->fullname . "  => PRIMARY<br>";
                        } elseif ($company->id == 114 && $lh->id == 298) { // Pro-gyp
                            $company->primary_user = $lh->id;
                            echo $lh->fullname . "  => PRIMARY<br>";
                        } elseif ($company->id == 104 && $lh->id == 237) { // Test Company
                            $company->primary_user = $lh->id;
                            echo $lh->fullname . "  => PRIMARY<br>";
                            $company->secondary_user = 204;
                            echo "Robert Moerman  => SECONDARY<br>";
                        } else
                            echo "$lh->fullname $inactive<br>";
                    }
                } elseif (count($lhs) == 1) {
                    echo $lhs[0]->fullname . " => PRIMARY<br>";
                    $company->primary_user = $lhs[0]->id;
                    $cas = $company->usersWithRole('contractor.admin');
                    if (count($cas) > 1) {
                        echo "*********   2+ CA *************<br>";
                    } elseif (count($cas) == 1) {
                        echo $cas[0]->fullname . "  => SECONDARY<br>";
                        $company->secondary_user = $cas[0]->id;
                    }
                }
                //$company->save();

                foreach ($company->staffStatus(1) as $staff) {
                    if ($staff->is('security')) {
                        echo $staff->fullname . " => ADMIN<br>";
                        $staff->security = 1;
                    } else
                        $staff->security = 0;
                    //$staff->save();
                }
            }
        }
        echo "<br><br>Completed<br>-------------<br>";
        */

    }

    public function quick2()
    {

    }

    public function completedQA()
    {
        echo "<br><br>Todo QA doc completed/hold but still active<br><br>";
        $todos = \App\Models\Comms\Todo::all();
        foreach ($todos as $todo) {
            if ($todo->status && $todo->type == 'qa') {
                $qa = \App\Models\Site\SiteQa::find($todo->type_id);
                if ($qa) {
                    if ($qa->status == 1) {
                        //echo "ToDo [$todo->id] - $todo->name ACTIVE QA<br>";
                    }
                    if ($qa->status == 0) {
                        echo "ToDo [$todo->id] - $todo->name COMPLETED QA<br>";
                        $todo->status = 0;
                        $todo->save();
                        // $todo->delete();
                    }
                    if ($qa->status == 2) {
                        echo "ToDo [$todo->id] - $todo->name HOLD QA<br>";
                        $todo->status = 0;
                        $todo->save();
                        // $todo->delete();
                    }

                } else {
                    echo "ToDo [$todo->id] (DELETED)<br>";
                    $todo->status = 0;
                    $todo->save();
                    // $todo->delete();
                }
            }
        }
        echo "<br><br>Completed<br>-------------<br>";
    }


    public function refreshQA()
    {
        echo "Updating Current QA Reports to match new QA template with Supervisor tick<br><br>";
        $items = SiteQaItem::all();
        foreach ($items as $item) {
            if ($item->master_id) {
                $master = SiteQaItem::find($item->master_id);
                $doc = SiteQa::find($item->doc_id);
                $site = Site::find($doc->site_id);

                // Has master + master set to super but current QA item isn'tr
                if ($master && $master->super && !$item->super) {
                    echo "[$item->id] docID:$item->doc_id $doc->name ($site->name)<br> - $item->name<br><br>";
                    $item->super = 1;
                    if ($item->done_by)
                        $item->done_by = 0;
                    $item->save();
                }

                if (!$item->super) {
                    $doc_master_item = SiteQaItem::where('doc_id', $doc->master_id)->where('task_id', $item->task_id)
                        ->where('name', $item->name)->where('super', '1')->first();
                    if ($doc_master_item) {
                        echo "*[$item->id] docID:$item->doc_id $doc->name ($site->name)<br> - $item->name<br><br>";
                        $item->super = 1;
                        if ($item->done_by)
                            $item->done_by = 0;
                        $item->save();
                    }
                }
            }
        }
        echo "<br><br>Completed<br>-------------<br>";
    }

    public function importCompany(Request $request)
    {
        echo "Importing Companies<br><br>";
        $row = 0;
        if (($handle = fopen(public_path("company.csv"), "r")) !== false) {
            while (($data = fgetcsv($handle, 5000, ",")) !== false) {
                $row ++;
                if ($row == 1) continue;
                $num = count($data);

                $company = Company::find($data[0]);
                if ($company && !($company->id == 120 || $company->id == 121)) {
                    $company->name = $data[1];
                    $company->nickname = $data[2];
                    $company->email = $data[3];
                    $company->phone = $data[4];
                    $company->address = $data[5];
                    $company->suburb = $data[6];
                    $company->state = $data[7];
                    $company->postcode = $data[8];
                    $company->abn = $data[9];
                    $company->gst = $data[10];
                    $company->payroll_tax = $data[11];
                    $company->creditor_code = $data[12];
                    $company->business_entity = $data[13];
                    $company->sub_group = $data[14];
                    $company->category = $data[15];
                    $company->lic_override = $data[16];
                    $company->maxjobs = $data[17];
                    $company->transient = $data[18];
                    $company->primary_user = $data[19];
                    $company->secondary_user = $data[20];

                    $company->status = 0;
                    //$company->approved_by = 424;
                    //$company->approved_at = Carbon::now();
                    echo "<h1>$company->name</h1>";
                    dd($company);
                    //print_r($company);
                    $company->save();

                    /*for ($c = 0; $c < $num; $c ++) {
                        echo $data[$c] . "<br>";
                    }*/
                } elseif ($data[0]) {
                    /*
                    echo "NEW $data[0]<br>";
                    $address = $suburb = $state = $postcode = '';
                    $addy = explode(',', $data[9]);
                    if ($data[9] && count($addy) == 4)
                        list($address, $suburb, $state, $postcode) = explode(',', $data[9]);
                    elseif (($data[9] && count($addy) > 1))
                        echo "<br>***" . count($addy) . '***';
                    // Create Company
                    $company_request = [
                        'name'            => $data[0],
                        'category'        => $data[1],
                        'creditor_code'   => $data[2],
                        'business_entity' => $data[6],
                        'sub_group'       => $data[7],
                        'abn'             => $data[8],
                        'address'         => $address,
                        'suburb'          => $suburb,
                        'state'           => $state,
                        'postcode'        => $postcode,
                        'email'           => $data[10],
                        'gst'             => ($data[17] == 'YES') ? 1 : 0,
                        'payroll_tax'     => $data[23][0],
                        'licence_expiry'  => null,
                        'parent_company'  => 3,

                    ];
                    $company_request['licence_no'] = ($data[33] && $data[33] != 'N/A') ? $data[33] : '';
                    if ($data[34] && preg_match('/\d+\/\d+\/\d+/', $data[34]))
                        $company_request['licence_expiry'] = Carbon::createFromFormat('d/m/Y H:i', $data[34] . '00:00')->toDateTimeString();
                    var_dump($company_request);

                    $newCompany = \App\Models\Company\Company::create($company_request);
                    */
                }

            }
            fclose($handle);
        }
        echo "<br><br>Completed<br>-------------<br>";
    }

    public function fixplanner()
    {
        set_time_limit(120);

        //
        // Sites Without Start Dates
        //
        $sites = Site::where('status', '1')->orderBy('name')->get();
        $startJobIDs = Task::where('code', 'START')->where('status', '1')->pluck('id')->toArray();
        $array = [];
        // Create array in specific Vuejs 'select' format.
        foreach ($sites as $site) {
            $planner = SitePlanner::where('site_id', $site->id)->orderBy('from')->get();

            $found = false;
            foreach ($planner as $plan) {
                if (in_array($plan->task_id, $startJobIDs)) {
                    $found = true;
                    break;
                }
            }

            if (!$found) {
                $tasks = '0';
                $planner2 = SitePlanner::where('site_id', $site->id)->get();
                if ($planner2)
                    $tasks = $planner2->count();

                $array[] = ['id' => $site->id, 'code' => $site->code, 'name' => $site->name, 'tasks' => $tasks];
            }
        }

        echo "Sites without START JOB but have other tasks on planner<br><br>";
        foreach ($array as $a) {
            if ($a['tasks'] != 0)
                echo "$a[code] $a[name] - tasks($a[tasks])<br>";
        }

        echo "<br><br>Sites without START JOB but are blank<br><br>";
        foreach ($array as $a) {
            if ($a['tasks'] == 0)
                echo "$a[code] $a[name]<br>";
        }

        echo "<br><br>Completed<br>-------------<br>";

        //
        // Tasks that end before they start
        //
        echo "<br><br>Tasks that end before they start<br><br>";

        $recs = SitePlanner::orderBy('site_id')->get();
        $count = 0;
        $start = 0;
        foreach ($recs as $rec) {
            if ($rec->to->lt($rec->from)) {
                $site = Site::find($rec->site_id);
                $task = Task::find($rec->task_id);
                echo "$rec->id F:$rec->from  T:$rec->to site:$site->name   task:$task->name<br>";
                $count ++;
                if ($rec->task_id == 11)
                    $start ++;

                $rec->delete();
            }
        }
        echo "<br><br>Completed<br>-------------<br>";
        echo "Found $count records  with $start START JOBS<br>";

        //
        // Tasks that end before they start
        //
        echo "<br><br>Task with an invaild To/From Date + Days count<br><br>";

        $recs = SitePlanner::orderBy('id')->get();
        $bad_end = 0;
        $bad_daycount = 0;
        foreach ($recs as $rec) {
            $site = Site::find($rec->site_id);
            $task = Task::find($rec->task_id);
            $taskname = 'NULL';
            if ($task)
                $taskname = $task->name;

            // Task ends before it starts
            if ($rec->to->lt($rec->from)) {
                echo "END $rec->id F:" . $rec->from->format('Y-m-d') . " T:" . $rec->to->format('Y-m-d') . " site:$site->name   task:$taskname<br>";
                $bad_end ++;
                //$rec->delete(); // delete bad record
            } else {
                $workdays = $this->workDaysBetween($rec->from, $rec->to);
                if ($workdays != $rec->days) {
                    echo "$workdays/$rec->days $rec->id F:" . $rec->from->format('Y-m-d') . " T:" . $rec->to->format('Y-m-d') . " site:$site->name   task:$taskname<br>";
                    $bad_daycount ++;

                    // Update bad record
                    $rec->days = $workdays;
                    $rec->save();
                }
            }
        }
        echo "<br><br>Completed<br>-------------<br>";
        echo "$bad_end records that end before they start  <br>";
        echo "$bad_daycount records with incorrect day count<br>";

    }

    public function workDaysBetween($from, $to, $debug = false)
    {
        if ($from == $to)
            return 1;

        $counter = 0;
        $startDate = Carbon::createFromFormat('Y-m-d H:i:s', $from);
        $endDate = Carbon::createFromFormat('Y-m-d H:i:s', $to);
        while ($startDate->format('Y-m-d') != $endDate->format('Y-m-d')) {
            if ($debug) echo "c:" . $counter . " d:" . $startDate->dayOfWeek . ' ' . $startDate->format('Y-m-d') . '<br>';
            if ($startDate->dayOfWeek > 0 && $startDate->dayOfWeek < 6) {
                $counter ++;
                $startDate->addDay();
            } else if ($startDate->dayOfWeek === 6) { // Skip Sat
                if ($debug) echo "skip sat<br>";
                $startDate->addDay();
            } else if ($startDate->dayOfWeek === 0) { // Skip Sun
                if ($debug) echo "skip sun<br>";
                $startDate->addDay();
            }
        }
        if ($endDate->dayOfWeek > 0 && $endDate->dayOfWeek < 6)
            $counter ++;

        return $counter;
    }


    public function importMaterials()
    {
        echo "Importing Materials<br><br>";
        $row = 0;
        if (($handle = fopen(public_path("materials.csv"), "r")) !== false) {
            while (($data = fgetcsv($handle, 5000, ",")) !== false) {
                $row ++;
                if ($row == 1) continue;
                $num = count($data);

                $cat = $data[0];
                $name = $data[1];
                $length = $data[2];
                $qty = $data[3];

                $category = EquipmentCategory::where('name', $cat)->first();
                if (!$category)
                    $category = EquipmentCategory::create(['name' => $cat, 'parent' => 3, 'private' => 0, 'status' => 1, 'company_id' => 3]);

                $equip = Equipment::where('category_id', $category->id)->where('name', $name)->where('length', $length)->first();

                if ($equip) {
                    // Existing
                } else {
                    // Create item
                    $equip_request = [
                        'category_id' => $category->id,
                        'name'        => $name,
                        'length'      => $length,
                        'status'      => 1
                    ];

                    var_dump($equip_request);
                    $equip = Equipment::create($equip_request);

                    $store = EquipmentLocation::where('site_id', 25)->first();
                    // Allocate New Item to Store
                    $existing = EquipmentLocationItem::where('location_id', $store->id)->where('equipment_id', $equip->id)->first();
                    if ($existing) {
                        $existing->qty = $existing->qty + $qty;
                        $existing->save();
                    } else
                        $store->items()->save(new EquipmentLocationItem(['location_id' => $store->id, 'equipment_id' => $equip->id, 'qty' => $qty]));

                    // Update Purchased Qty
                    if (is_int($qty)) {
                        $equip->purchased = $equip->purchased + $qty;
                        $equip->save();
                    }

                    // Update log
                    $log = new EquipmentLog(['equipment_id' => $equip->id, 'qty' => $qty, 'action' => 'P']);
                    $log->notes = 'Purchased ' . $qty . ' items';
                    $equip->log()->save($log);
                }


            }
            fclose($handle);
        }
        echo "<br><br>Completed<br>-------------<br>";
    }

    public function importPayroll()
    {
        echo "Importing Payroll<br>---------------------<br><br>";
        $row = 0;
        if (($handle = fopen(public_path("payroll.csv"), "r")) !== false) {
            while (($data = fgetcsv($handle, 5000, ",")) !== false) {
                $row ++;
                if ($row == 1) continue;
                $num = count($data);

                $cid = $data[0];
                $company = Company::find($cid);
                $name = $data[1];
                $entity = $data[2];
                $staff = $data[3];
                $gst = $data[4];
                $payroll = $data[5];
                if ($payroll == 'Liable')
                    $pid = 8;
                else
                    $pid = substr($payroll, - 2, 1);

                $mod = false;
                if ($company) {
                    //echo "<br>$name - $entity - $staff - $gst - $payroll<br>";
                    echo "<br>$name<br>---------------------------------------------------------<br>";
                    if ($name != $company->name) {
                        echo "- Updating Name: $company->name => $name<br>";
                        $company->name = $name;
                        $mod = true;
                    }

                    if (array_search($entity, \App\Http\Utilities\CompanyEntityTypes::all()) != $company->business_entity) {
                        echo "- Updating Business Entity: " . \App\Http\Utilities\CompanyEntityTypes::name($company->business_entity) . " => $entity<br>";
                        $company->business_entity = array_search($entity, \App\Http\Utilities\CompanyEntityTypes::all());
                        $mod = true;
                    }

                    if (($gst == "Yes" && $company->gst == 0) || ($gst == "No" && $company->gst == 1)) {
                        echo "- Updating GST: to $gst<br>";
                        $company->gst = ($gst == 'Yes') ? 1 : 0;
                        $mod = true;
                    }

                    if ($pid != $company->payroll_tax) {
                        if (!$company->payroll_tax)
                            echo "- Updating Payroll Tax: None  => $payroll<br>";
                        elseif ($company->payroll_tax == 8)
                            echo "- Updating Payroll Tax: Liable => $payroll<br>";
                        else
                            echo "- Updating Payroll Tax: Exempt ($company->payroll_tax)  => $payroll<br>";
                        $company->payroll_tax = $pid;
                        $mod = true;
                    }

                    if ($mod) {
                        //echo "NEW: $company->name - ent($company->business_entity) - gst($company->gst) - pay($company->payroll_tax)<br>";
                        $company->save();
                    }

                } else {
                    echo "*****************************<br>INVAILD COMPANY ID ($cid)   $name - $entity - $staff - $gst - $payroll<br>*****************************<br>";
                }

                echo "<br>";


            }
            fclose($handle);
        }
        echo "<br><br>Completed<br>-------------<br>";
    }

    public function importQuestions()
    {
        echo "Importing Questions<br>---------------------<br><br>";
        $row = 0;
        if (($handle = fopen(public_path("resp.csv"), "r")) !== false) {
            while (($data = fgetcsv($handle, 5000, ",")) !== false) {
                $row ++;
                $num = count($data);

                $question = $data[0];
                $option = $data[1];

                $quest = FormQuestion::where('name', $question)->where('parent', null)->first();

                if ($quest) {
                    echo " ";
                    $opt = FormQuestion::where('name', $option)->where('parent', $quest->id)->first();
                    if (!$opt)
                        $quest = FormQuestion::create(['name' => $option, 'parent' => $quest->id, 'form' => 'site_incident']);
                } else {
                    echo "*  ";
                    $quest = FormQuestion::create(['name' => $question, 'form' => 'site_incident']);
                    $option = FormQuestion::create(['name' => $option, 'parent' => $quest->id, 'form' => 'site_incident']);
                }
                echo "$question - $option<br>";

                echo "<br>";


            }
            fclose($handle);
        }
        echo "<br><br>Completed<br>-------------<br>";
    }

    public function importMaintenance()
    {
        echo "Importing Maintenance<br>---------------------<br><br>";
        $row = 0;
        if (($handle = fopen(public_path("maintenance.csv"), "r")) !== false) {
            while (($data = fgetcsv($handle, 5000, ",")) !== false) {
                $row ++;
                //if ($row == 1) continue;
                $num = count($data);

                $status = ($data[0] && $data[0] == 'OPEN') ? 1 : 0;
                $id = substr($data[1], 1);
                $job = $data[2];
                $site = Site::where('code', $job)->first();
                $job_name = $data[3];
                $job_suburb = $data[4];
                $prac_complete = $data[5];
                if ($data[5] && preg_match('/\d+\/\d+\/\d+/', $data[5]))
                    $prac_date = Carbon::createFromFormat('d/m/y H:i', $data[5] . '00:00')->toDateTimeString();
                $super = ucwords(strtolower($data[6]));;
                $created = $data[7];
                if ($data[7] && preg_match('/\d+\/\d+\/\d+/', $data[7]))
                    $created_date = Carbon::createFromFormat('d/m/y H:i', $data[7] . '00:00')->toDateTimeString();
                $client_name = $data[8];
                $client_phone = $data[9];
                $client_email = $data[10];
                $item = $data[11];
                $warranty = $data[12];
                switch ($warranty) {
                    case 'GBT':
                        $company_id = 29;
                        break;
                    case 'Scott Bartley Plumbing' :
                        $company_id = 69;
                        break;
                    case 'NEXT POINT' :
                        $company_id = 108;
                        break;
                    case 'Josh Lay' :
                        $company_id = 289;
                        break;
                    case 'Philip Dougty' :
                        $company_id = 219;
                        break;
                    case 'Andrew Cashmore' :
                        $company_id = 105;
                        break;
                    default :
                        $company_id = 3;
                }
                $cat = $data[13];
                $cat_id = \App\Models\Site\SiteMaintenanceCategory::where('name', $cat)->first();
                $company = $data[14];
                switch ($company) {
                    case 'GBT':
                        $company_id = 29;
                        break;
                    case 'Scott Bartley Plumbing' :
                        $company_id = 69;
                        break;
                    case 'NEXT POINT' :
                        $company_id = 108;
                        break;
                    case 'Josh Lay' :
                        $company_id = 289;
                        break;
                    case 'Philip Dougty' :
                        $company_id = 219;
                        break;
                    case 'Andrew Cashmore' :
                        $company_id = 105;
                        break;
                    default :
                        $company_id = 3;
                }
                $assigned = Company::find($company_id);
                $res = ($data[15] && preg_match('/\d+\/\d+\/\d+/', $data[15])) ? Carbon::createFromFormat('d/m/y H:i', $data[15] . '00:00')->toDateTimeString() : null;
                $futher = $data[16];
                if ($futher) {
                    $futher = ($futher == 'YES') ? 1 : 0;
                } else
                    $futher = null;

                $notes = $data[17];

                if ($status == 0) {
                    $site->status = 0;
                    $site->save();
                }

                /*

                if (!$site) {
                    echo "<br><br>Creating SITE $job ($job_name)<br>";
                    $site = Site::create(['name' => $job_name, 'code' => $job, 'suburb' => $job_suburb, 'client_phone' => $client_phone, 'client_phone_desc' => $client_name, 'company_id' => 3, 'status' => 2]);
                }
                echo "<br><br>$id : $job : $site->name ($job_name) : $site->suburb ($job_suburb)<br>";
                //echo "$prac_complete ($prac_date) : $super : $created_date<br>";
                //echo "$client_name : $client_phone : $client_email<br>";
                //echo "-----<br>".nl2br($item)."<br>--------<br>";
                echo "$warranty : $cat ($cat_id->name)<br>";
                echo "$company ($assigned->name)<br>";

                // Create item
                $main_request = [
                    'site_id'       => $site->id,
                    'code'          => $id,
                    'completed'     => $prac_date,
                    'warranty'      => $warranty,
                    'category_id'   => $cat_id->id,
                    'contact_name'  => $client_name,
                    'contact_email' => $client_email,
                    'contact_phone' => $client_phone,
                    'step'          => 5,
                    'assigned_to'   => $assigned->id,
                    'further_works' => $futher,
                    'supervisor'    => $super,
                    'status'        => $status,
                    'created_by'    => 3,
                    'created_at'    => $created_date,
                    'updated_by'    => 3,
                    'updated_at'    => '2020-09-18 00:00:00',

                ];

                if ($status == 0) {
                    $main_request['supervisor_sign_by'] = 7;
                    $main_request['supervisor_sign_at'] = $res;
                    $main_request['manager_sign_by'] = 7;
                    $main_request['manager_sign_at'] = $res;
                    $main_request['updated_by'] = 7;
                    $main_request['updated_at'] = $res;
                }

                var_dump($main_request);
                $main = \App\Models\Site\SiteMaintenance::create($main_request);
                $action = \App\Models\Misc\Action::create(['action' => "Maintenance Request created by " . Auth::user()->fullname, 'table' => 'site_maintenance', 'table_id' => $main->id]);

                if ($status == 1)
                    $main_item = \App\Models\Site\SiteMaintenanceItem::create(['main_id' => $main->id, 'name' => $item, 'order' => 1, 'status' => 0]);
                else
                    $main_item = \App\Models\Site\SiteMaintenanceItem::create(['main_id' => $main->id, 'name' => $item, 'order' => 1, 'sign_by' => 7, 'sign_at' => $res, 'done_by' => $assigned->id, 'done_at' => $res, 'status' => 1]);

                // Put Site into maintenance mode
                $site->status = 2;
                $site->save();
                */

                echo "<br>";


            }
            fclose($handle);
        }
        echo "<br><br>Completed<br>-------------<br>";
    }

    public function disabledTasks()
    {

        echo "List of Disabled Tasks currently still in use<br>--------------------------------------------------------<br><br>";

        $tasks = Task::where('status', 0)->get();
        $qas = SiteQa::where('status', 1)->where('master', 1)->get();


        foreach ($tasks as $task) {
            $found = 0;
            $trade = Trade::find($task->trade_id);

            // Check Active QAs
            foreach ($qas as $qa) {
                // Loop each task
                foreach ($qa->tasks() as $t) {
                    if ($t->id == $task->id) {
                        if (!$found)
                            echo "<br><br>Task (id: $task->id) $trade->name - $task->name ($task->code):<br>";
                        $found = 1;
                        echo "- QA Template (id: $qa->id) $qa->name<br>";
                    }
                }
            }

            // Check Future Planner
            $planner = SitePlanner::whereDate('from', '>', today()->format('Y-m-d'))->where('task_id', $task->id)->get();
            foreach ($planner as $plan) {
                if (!$found)
                    echo "<br><br>Task (id: $task->id) $trade->name - $task->name ($task->code):<br>";
                $found = 1;
                $site = Site::find($plan->site_id);
                echo "- Site (id: $site->id) $site->name planned for " . $plan->to->format('d/m/Y') . "<br>";
            }
        }
    }

    function exportSupportTickets()
    {
        echo "<b>Export of Dev Support Tickets </b></br>";
        $tickets = SupportTicket::where('status', 1)->where('type', 1)->get();

        echo "<table st><br>";
        echo "<tr><td width='5%'><b>ID</b></td><td width='5%'><b>NAME</b></td><td><b>ACTIONS</b></td></tr>";
        foreach ($tickets as $ticket) {
            echo "<tr style='outline: thin solid'><td>$ticket->id</td><td>$ticket->name</td><td>&nbsp;</td></tr>";
            foreach ($ticket->actions as $action) {
                echo "<tr style='outline: thin dotted'><td>&nbsp;</td><td>" . $action->created_at->format('d/m/Y') . "\n" . $action->user->firstname . "</td><td>$action->action</td></tr>";
            }
        }
        echo "</table>";
    }

    function asbestosRegister()
    {
        echo "<b>Create Asbestos Register </b></br>";
        $sites = Site::where('company_id', 3)->get();


        echo "<table><br>";
        echo "<tr><td width='5%'><b>ID</b></td><td width='15%'><b>NAME</b></td><td><b>REPORTS</b></td></tr>";
        foreach ($sites as $site) {
            echo "<tr style='outline: thin solid'><td>$site->id &nbsp; $site->status</td><td>$site->name</td><td>";
            $docs = SiteDoc::where('site_id', $site->id)->where('name', 'Asbestos Register')->get();
            foreach ($docs as $doc) {
                echo "$doc->name &nbsp; &nbsp; - &nbsp; $doc->attachment<br>";
                $reg = SiteAsbestosRegister::where('site_id', $site->id)->first();
                if (!$reg)
                    $asb = SiteAsbestosRegister::create(['site_id' => $site->id, 'attachment' => $doc->attachment, 'version' => '1.0']);
            }
            echo "</td></tr>";
        }
        echo "</table>";
    }

    /*
     * Reset Template Form
     */
    public function resetFormTemplate()
    {
        $now = Carbon::now()->format('d/m/Y g:i a');
        echo "<b>Reseting Sample Form Template - $now</b></br>";
        DB::table('forms_templates')->truncate();
        DB::table('forms_pages')->truncate();
        DB::table('forms_sections')->truncate();
        DB::table('forms_questions')->truncate();
        DB::table('forms_options')->truncate();
        DB::table('forms_logic')->truncate();
        DB::table('forms')->truncate();
        DB::table('forms_responses')->truncate();
        DB::table('forms_files')->truncate();
        DB::table('forms_actions')->truncate();
    }


    /*
     * Initilise Template Form
     */
    public function initFormTemplate()
    {
        $now = Carbon::now()->format('d/m/Y g:i a');
        echo "<b>Creating Sample Form Template - $now</b></br>";

        //
        // Creating special options
        //
        echo "Creating Special Option</br>";
        // CONN
        FormOption::create(['text' => 'Compliant', 'value' => 'Compliant', 'order' => 1, 'colour' => 'green', 'score' => 2, 'group' => 'CONN', 'master' => 1, 'status' => 1, 'created_by' => 3, 'updated_by' => 3]);
        FormOption::create(['text' => 'Observation', 'value' => 'Observation', 'order' => 2, 'colour' => 'orange', 'score' => 1, 'group' => 'CONN', 'master' => 1, 'status' => 1, 'created_by' => 3, 'updated_by' => 3]);
        FormOption::create(['text' => 'Non-conformance', 'value' => 'Non-conformance', 'order' => 3, 'colour' => 'red', 'score' => - 2, 'group' => 'CONN', 'master' => 1, 'status' => 1, 'created_by' => 3, 'updated_by' => 3]);
        FormOption::create(['text' => 'Not Applicable', 'value' => 'Not Applicable', 'order' => 4, 'colour' => null, 'score' => 0, 'group' => 'CONN', 'master' => 1, 'status' => 1, 'created_by' => 3, 'updated_by' => 3]);
        // YN
        FormOption::create(['text' => 'Yes', 'value' => 'Yes', 'order' => 1, 'score' => 1, 'colour' => null, 'group' => 'YN', 'master' => 1, 'status' => 1, 'created_by' => 3, 'updated_by' => 3]);
        FormOption::create(['text' => 'No', 'value' => 'No', 'order' => 2, 'score' => 0, 'colour' => null, 'group' => 'YN', 'master' => 1, 'status' => 1, 'created_by' => 3, 'updated_by' => 3]);
        // YrN
        FormOption::create(['text' => 'Yes', 'value' => 'Yes', 'order' => 1, 'score' => 1, 'colour' => 'red', 'group' => 'YrN', 'master' => 1, 'status' => 1, 'created_by' => 3, 'updated_by' => 3]);
        FormOption::create(['text' => 'No', 'value' => 'No', 'order' => 2, 'score' => 0, 'colour' => null, 'group' => 'YrN', 'master' => 1, 'status' => 1, 'created_by' => 3, 'updated_by' => 3]);
        // YgN
        FormOption::create(['text' => 'Yes', 'value' => 'Yes', 'order' => 1, 'score' => 1, 'colour' => 'green', 'group' => 'YgN', 'master' => 1, 'status' => 1, 'created_by' => 3, 'updated_by' => 3]);
        FormOption::create(['text' => 'No', 'value' => 'No', 'order' => 2, 'score' => 0, 'colour' => null, 'group' => 'YgN', 'master' => 1, 'status' => 1, 'created_by' => 3, 'updated_by' => 3]);
        // YNNA
        FormOption::create(['text' => 'Yes', 'value' => 'Yes', 'order' => 1, 'score' => 0, 'colour' => null, 'group' => 'YNNA', 'master' => 1, 'status' => 1, 'created_by' => 3, 'updated_by' => 3]);
        FormOption::create(['text' => 'No', 'value' => 'No', 'order' => 2, 'score' => 0, 'colour' => null, 'group' => 'YNNA', 'master' => 1, 'status' => 1, 'created_by' => 3, 'updated_by' => 3]);
        FormOption::create(['text' => 'N/A', 'value' => 'N/A', 'order' => 3, 'score' => 0, 'colour' => null, 'group' => 'YNNA', 'master' => 1, 'status' => 1, 'created_by' => 3, 'updated_by' => 3]);


        // Creating Sample Template
        $template = FormTemplate::create(['name' => 'Safety In Design Checklist', 'description' => 'The following criteria is to be established in order to prompt identification of potential hazards related to the existing conditions of a project and those arising from the associated proposed design and contract works. All identified hazards must be captured within the site-specific risk assessment. ', 'company_id' => 3, 'created_by' => 3, 'updated_by' => 3]);
        $tid = $template->id;
        $pn = 1;
        //
        // Page 1
        //
        $page = FormPage::create(['template_id' => $tid, 'name' => "Title Page", 'description' => null, 'order' => 1, 'created_by' => 3, 'updated_by' => 3]);
        $pid = $page->id;

        // Questions - Section 1
        $section = FormSection::create(['template_id' => $tid, 'page_id' => $pid, 'parent' => null, 'name' => null, 'description' => "Section 1a", 'order' => 1, 'created_by' => 3, 'updated_by' => 3]);
        $sid = $section->id;
        $question = FormQuestion::create(
            ['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
             'name'        => "Site conducted", 'type' => "select", 'type_special' => 'site', 'type_version' => 'select2',
             'order'       => $pn ++, 'default' => null, 'multiple' => null, 'required' => 1, 'created_by' => 3, 'updated_by' => 3, 'placeholder' => 'Select site']);
        $question = FormQuestion::create(
            ['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
             'name'        => "Date initiated", 'type' => "datetime", 'type_special' => null, 'type_version' => null,
             'order'       => $pn ++, 'default' => null, 'multiple' => null, 'required' => 1, 'created_by' => 3, 'updated_by' => 3]);
        $question = FormQuestion::create(
            ['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
             'name'        => "Prepared by", 'type' => "select", 'type_special' => 'staff', 'type_version' => 'select2',
             'order'       => $pn ++, 'default' => null, 'multiple' => null, 'required' => 1, 'created_by' => 3, 'updated_by' => 3]);
        $question = FormQuestion::create(
            ['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
             'name'        => "Contributions by:", 'type' => "text", 'type_special' => null, 'type_version' => null,
             'order'       => $pn ++, 'default' => null, 'multiple' => null, 'required' => 0, 'created_by' => 3, 'updated_by' => 3]);
        $question = FormQuestion::create(
            ['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
             'name'        => "Contributions by:", 'type' => "text", 'type_special' => null, 'type_version' => null,
             'order'       => $pn ++, 'default' => null, 'multiple' => null, 'required' => 0, 'created_by' => 3, 'updated_by' => 3]);
        $question = FormQuestion::create(
            ['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
             'name'        => "Contributions by:", 'type' => "text", 'type_special' => null, 'type_version' => null,
             'order'       => $pn ++, 'default' => null, 'multiple' => null, 'required' => 0, 'created_by' => 3, 'updated_by' => 3]);
        $question = FormQuestion::create(
            ['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
             'name'        => "Contributions by:", 'type' => "text", 'type_special' => null, 'type_version' => null,
             'order'       => $pn ++, 'default' => null, 'multiple' => null, 'required' => 0, 'created_by' => 3, 'updated_by' => 3]);

        //
        // Page 2
        //
        $page = FormPage::create(['template_id' => $template->id, 'name' => "Existing Site", 'description' => null, 'order' => 2, 'created_by' => 3, 'updated_by' => 3]);
        $pid = $page->id;

        // Questions - Section 1
        $section = FormSection::create(['template_id' => $tid, 'page_id' => $pid, 'parent' => null, 'name' => null, 'description' => "Section 2a", 'order' => 1, 'created_by' => 3, 'updated_by' => 3]);
        $sid = $section->id;
        $question = FormQuestion::create(
            ['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
             'name'        => "Existing structure", 'type' => "media", 'type_special' => null, 'type_version' => null,
             'order'       => $pn ++, 'default' => null, 'multiple' => 1, 'required' => 1, 'created_by' => 3, 'updated_by' => 3]);
        $question = FormQuestion::create(
            ['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
             'name'        => "Estimated age", 'type' => "text", 'type_special' => null, 'type_version' => null,
             'order'       => $pn ++, 'default' => null, 'multiple' => null, 'required' => 1, 'created_by' => 3, 'updated_by' => 3]);
        $question = FormQuestion::create(
            ['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
             'name'        => "Type of construction", 'type' => "textarea", 'type_special' => null, 'type_version' => null,
             'order'       => $pn ++, 'default' => null, 'multiple' => null, 'required' => 1, 'created_by' => 3, 'updated_by' => 3]);
        $question = FormQuestion::create(
            ['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
             'name'        => "External Cladding", 'type' => "textarea", 'type_special' => null, 'type_version' => null,
             'order'       => $pn ++, 'default' => null, 'multiple' => null, 'required' => 1, 'created_by' => 3, 'updated_by' => 3]);
        $question = FormQuestion::create(
            ['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
             'name'        => "Roof", 'type' => "textarea", 'type_special' => null, 'type_version' => null,
             'order'       => $pn ++, 'default' => null, 'multiple' => null, 'required' => 1, 'created_by' => 3, 'updated_by' => 3]);
        $question = FormQuestion::create(
            ['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
             'name'        => "Orientation", 'type' => "textarea", 'type_special' => null, 'type_version' => null,
             'order'       => $pn ++, 'default' => null, 'multiple' => null, 'required' => 1, 'created_by' => 3, 'updated_by' => 3]);


        //
        // Page 3
        //
        $page = FormPage::create(['template_id' => $template->id, 'name' => "Proposed Works", 'description' => '', 'order' => 3, 'created_by' => 3, 'updated_by' => 3]);
        $pid = $page->id;

        // Questions -  Section 1
        $section = FormSection::create(['template_id' => $tid, 'page_id' => $pid, 'parent' => null, 'name' => null, 'description' => "Section 3a", 'order' => 1, 'created_by' => 3, 'updated_by' => 3]);
        $sid = $section->id;
        $question = FormQuestion::create(
            ['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
             'name'        => "Summary of proposed works and design scope", 'type' => "textarea", 'type_special' => null, 'type_version' => null,
             'order'       => $pn ++, 'default' => null, 'multiple' => null, 'required' => 1, 'created_by' => 3, 'updated_by' => 3]);
        $question = FormQuestion::create(
            ['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
             'name'        => "Construction materials", 'type' => "textarea", 'type_special' => null, 'type_version' => null,
             'order'       => $pn ++, 'default' => null, 'multiple' => null, 'required' => 1, 'created_by' => 3, 'updated_by' => 3]);

        //
        // Page 4
        //
        $page = FormPage::create(['template_id' => $template->id, 'name' => "Proximity to Adjacent Properties and Infrastructure", 'description' => '', 'order' => 4, 'created_by' => 3, 'updated_by' => 3]);
        $pid = $page->id;

        // Questions -  Section 1
        $section = FormSection::create(['template_id' => $tid, 'page_id' => $pid, 'parent' => null, 'name' => null, 'description' => "Section 4a", 'order' => 1, 'created_by' => 3, 'updated_by' => 3]);
        $sid = $section->id;
        $question = FormQuestion::create(
            ['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
             'name'        => "Property has or is adjacent to battle axe/right of way?", 'type' => "select", 'type_special' => 'YrN', 'type_version' => null,
             'order'       => $pn ++, 'default' => null, 'multiple' => null, 'required' => 1, 'created_by' => 3, 'updated_by' => 3]);
        $question = FormQuestion::create(
            ['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
             'name'        => "Are there any recreational facilities that exist nearby, such as parks, playgrounds, sporting fields?", 'type' => "select", 'type_special' => 'YrN', 'type_version' => null,
             'order'       => $pn ++, 'default' => null, 'multiple' => null, 'required' => 1, 'created_by' => 3, 'updated_by' => 3]);
        $question = FormQuestion::create(
            ['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
             'name'        => "Are there any public or government facilities nearby, such as schools, bus stops, recreational centres or community centres?", 'type' => "select", 'type_special' => 'YrN', 'type_version' => null,
             'order'       => $pn ++, 'default' => null, 'multiple' => null, 'required' => 1, 'created_by' => 3, 'updated_by' => 3]);
        $question = FormQuestion::create(
            ['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
             'name'        => "Are pedestrian paths/walkways in proximity to the property?", 'type' => "select", 'type_special' => 'YrN', 'type_version' => null,
             'order'       => $pn ++, 'default' => null, 'multiple' => null, 'required' => 1, 'created_by' => 3, 'updated_by' => 3]);
        $question = FormQuestion::create(
            ['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
             'name'        => "Is the site address positioned on or affected by a corner?", 'type' => "select", 'type_special' => 'YrN', 'type_version' => null,
             'order'       => $pn ++, 'default' => null, 'multiple' => null, 'required' => 1, 'created_by' => 3, 'updated_by' => 3]);
        $question = FormQuestion::create(
            ['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
             'name'        => "Is the site address positioned on or affected by a busy or main road?", 'type' => "select", 'type_special' => 'YrN', 'type_version' => null,
             'order'       => $pn ++, 'default' => null, 'multiple' => null, 'required' => 1, 'created_by' => 3, 'updated_by' => 3]);


        //
        // Page 5
        //
        $page = FormPage::create(['template_id' => $template->id, 'name' => "Project Position and Conditions", 'description' => '', 'order' => 5, 'created_by' => 3, 'updated_by' => 3]);
        $pid = $page->id;

        // Questions - Section 1
        $section = FormSection::create(['template_id' => $tid, 'page_id' => $pid, 'parent' => null, 'name' => null, 'description' => 'Section 5a', 'order' => 1, 'created_by' => 3, 'updated_by' => 3]);
        $sid = $section->id;
        $question = FormQuestion::create(
            ['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
             'name'        => "Is the Client to reside in the home for the entirety of the construction, or portion of construction?", 'type' => "select", 'type_special' => 'YrN', 'type_version' => null,
             'order'       => $pn ++, 'default' => null, 'multiple' => null, 'required' => 1, 'created_by' => 3, 'updated_by' => 3]);
        $question = FormQuestion::create(
            ['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
             'name'        => "Is a power supply outlet fitted to the electrical meter board (\"Builder's power\")", 'type' => "select", 'type_special' => 'YrN', 'type_version' => null,
             'order'       => $pn ++, 'default' => null, 'multiple' => null, 'required' => 1, 'created_by' => 3, 'updated_by' => 3]);
        $question = FormQuestion::create(
            ['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
             'name'        => "Does the Client have any animals or pets residing on, or likely to be residing on the property at the time of construction?", 'type' => "select", 'type_special' => 'YrN', 'type_version' => null,
             'order'       => $pn ++, 'default' => null, 'multiple' => null, 'required' => 1, 'created_by' => 3, 'updated_by' => 3]);

        // Logic
        $logic = FormLogic::create(['template_id' => $tid, 'page_id' => $pid, 'question_id' => $question->id, 'match_operation' => '=', 'match_value' => '7', 'trigger' => 'section', 'trigger_id' => 6, 'created_by' => 3, 'updated_by' => 3]);

        // Questions - Section 2
        $section = FormSection::create(['template_id' => $tid, 'page_id' => $pid, 'parent' => null, 'name' => "Animals & Pets", 'description' => null, 'order' => 2, 'created_by' => 3, 'updated_by' => 3]);
        $sid = $section->id;
        $question = FormQuestion::create(
            ['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
             'name'        => "Indicate the animals/pets on the property", 'type' => "select", 'type_special' => null, 'type_version' => 'select2',
             'order'       => $pn ++, 'default' => null, 'multiple' => 1, 'required' => 1, 'placeholder' => 'Select one or more pet(s)', 'created_by' => 3, 'updated_by' => 3]);
        // Add Options
        FormOption::create(['question_id' => $question->id, 'text' => 'Dog(s)', 'value' => 'Dog(s)', 'order' => 1, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1, 'created_by' => 3, 'updated_by' => 3]);
        FormOption::create(['question_id' => $question->id, 'text' => 'Cat(s)', 'value' => 'Cat(s)', 'order' => 2, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1, 'created_by' => 3, 'updated_by' => 3]);
        FormOption::create(['question_id' => $question->id, 'text' => 'Pet Bird(s)', 'value' => 'Pet Bird(s)', 'order' => 3, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1, 'created_by' => 3, 'updated_by' => 3]);
        FormOption::create(['question_id' => $question->id, 'text' => 'Livestock', 'value' => 'Livestock', 'order' => 4, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1, 'created_by' => 3, 'updated_by' => 3]);
        FormOption::create(['question_id' => $question->id, 'text' => 'Horse(s)', 'value' => 'Horse(s)', 'order' => 5, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1, 'created_by' => 3, 'updated_by' => 3]);
        FormOption::create(['question_id' => $question->id, 'text' => 'Other', 'value' => 'Other', 'order' => 6, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1, 'created_by' => 3, 'updated_by' => 3]);

        $question = FormQuestion::create(
            ['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
             'name'        => "What is the confinement or restraint practices available to control the risk of loss of pets or potential injury caused by interaction?", 'type' => "text", 'type_special' => null, 'type_version' => null,
             'order'       => $pn ++, 'default' => null, 'multiple' => null, 'required' => 1, 'created_by' => 3, 'updated_by' => 3]);

        // Questions - Section 3
        $section = FormSection::create(['template_id' => $tid, 'page_id' => $pid, 'parent' => null, 'name' => null, 'description' => "Section 5c", 'order' => 3, 'created_by' => 3, 'updated_by' => 3]);
        $sid = $section->id;
        $question = FormQuestion::create(
            ['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
             'name'        => "Is the Client aware of any adverse or aggravating factors in relationships with neighbours?", 'type' => "select", 'type_special' => 'YrN', 'type_version' => null,
             'order'       => $pn ++, 'default' => null, 'multiple' => null, 'required' => 1, 'created_by' => 3, 'updated_by' => 3]);
        $question = FormQuestion::create(
            ['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
             'name'        => "Is the client aware of any adverse conditions related to the property that may affect design or construction?", 'type' => "select", 'type_special' => 'YrN', 'type_version' => null,
             'order'       => $pn ++, 'default' => null, 'multiple' => null, 'required' => 1, 'created_by' => 3, 'updated_by' => 3]);
        $question = FormQuestion::create(
            ['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
             'name'        => "What does the foundation of the structure comprise of?", 'type' => "select", 'type_special' => null, 'type_version' => 'select2',
             'order'       => $pn ++, 'default' => null, 'multiple' => 1, 'required' => 1, 'created_by' => 3, 'updated_by' => 3]);
        // Add Options
        FormOption::create(['question_id' => $question->id, 'text' => 'Concrete slab', 'value' => 'Concrete slab', 'order' => 1, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1, 'created_by' => 3, 'updated_by' => 3]);
        FormOption::create(['question_id' => $question->id, 'text' => 'Brick piers, bearers & joists', 'value' => 'Brick piers, bearers & joists', 'order' => 2, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1, 'created_by' => 3, 'updated_by' => 3]);
        FormOption::create(['question_id' => $question->id, 'text' => 'Suspended slab', 'value' => 'Suspended slab', 'order' => 3, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1, 'created_by' => 3, 'updated_by' => 3]);

        $question = FormQuestion::create(
            ['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
             'name'        => "Is the project, street or immediate vicinity of the project address subject to sloping?", 'type' => "select", 'type_special' => 'YrN', 'type_version' => null,
             'order'       => $pn ++, 'default' => null, 'multiple' => null, 'required' => 1, 'created_by' => 3, 'updated_by' => 3]);

        //
        // Page 6
        //
        $page = FormPage::create(['template_id' => $template->id, 'name' => "Access", 'description' => '', 'order' => 6, 'created_by' => 3, 'updated_by' => 3]);
        $pid = $page->id;

        // Questions - Section 1
        $section = FormSection::create(['template_id' => $tid, 'page_id' => $pid, 'parent' => null, 'name' => null, 'description' => "Section 6a", 'order' => 1, 'created_by' => 3, 'updated_by' => 3]);
        $sid = $section->id;
        $question = FormQuestion::create(
            ['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
             'name'        => "Is the project, or access to the project positioned on a narrow street?", 'type' => "select", 'type_special' => 'YrN', 'type_version' => null,
             'order'       => $pn ++, 'default' => null, 'multiple' => null, 'required' => 1, 'created_by' => 3, 'updated_by' => 3]);
        $question = FormQuestion::create(
            ['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
             'name'        => "Is access to the property itself narrow or obstructed?", 'type' => "select", 'type_special' => 'YrN', 'type_version' => null,
             'order'       => $pn ++, 'default' => null, 'multiple' => null, 'required' => 1, 'created_by' => 3, 'updated_by' => 3]);
        $question = FormQuestion::create(
            ['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
             'name'        => "Is use of adjacent streets or side access possible? (alleyways, side streets, back streets, rear access)", 'type' => "select", 'type_special' => 'YgN', 'type_version' => null,
             'order'       => $pn ++, 'default' => null, 'multiple' => null, 'required' => 1, 'created_by' => 3, 'updated_by' => 3]);
        $question = FormQuestion::create(
            ['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
             'name'        => "Is access via neighbouring properties possible (i.e. with neighbour permission)", 'type' => "select", 'type_special' => 'YgN', 'type_version' => null,
             'order'       => $pn ++, 'default' => null, 'multiple' => null, 'required' => 1, 'created_by' => 3, 'updated_by' => 3]);
        $question = FormQuestion::create(
            ['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
             'name'        => "Is access for deliveries foreseeably difficult?", 'type' => "select", 'type_special' => 'YrN', 'type_version' => null,
             'order'       => $pn ++, 'default' => null, 'multiple' => null, 'required' => 1, 'created_by' => 3, 'updated_by' => 3]);
        $question = FormQuestion::create(
            ['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
             'name'        => "Is there adequate space for the storage of materials on site?", 'type' => "select", 'type_special' => 'YgN', 'type_version' => null,
             'order'       => $pn ++, 'default' => null, 'multiple' => null, 'required' => 1, 'created_by' => 3, 'updated_by' => 3]);
        // Logic
        $logic = FormLogic::create(['template_id' => $tid, 'page_id' => $pid, 'question_id' => $question->id, 'match_operation' => '=', 'match_value' => '10', 'trigger' => 'question', 'trigger_id' => ($question->id + 1), 'created_by' => 3, 'updated_by' => 3]);
        $question = FormQuestion::create(
            ['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
             'name'        => "Will a council permit be required for material storage?", 'type' => "select", 'type_special' => 'YrN', 'type_version' => null,
             'order'       => $pn ++, 'default' => null, 'multiple' => null, 'required' => 1, 'created_by' => 3, 'updated_by' => 3]);

        //
        // Page 7
        //
        $page = FormPage::create(['template_id' => $template->id, 'name' => "Waste Management", 'description' => '', 'order' => 7, 'created_by' => 3, 'updated_by' => 3]);
        $pid = $page->id;

        // Questions - Section 1
        $section = FormSection::create(['template_id' => $tid, 'page_id' => $pid, 'parent' => null, 'name' => null, 'description' => "Section 7a", 'order' => 1, 'created_by' => 3, 'updated_by' => 3]);
        $sid = $section->id;
        $question = FormQuestion::create(
            ['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
             'name'        => "Type of waste management system advised", 'type' => "select", 'type_special' => 'button', 'type_version' => null,
             'order'       => $pn ++, 'default' => null, 'multiple' => null, 'required' => 1, 'created_by' => 3, 'updated_by' => 3]);
        // Add Options
        FormOption::create(['question_id' => $question->id, 'text' => 'Skip Bin', 'value' => 'Skip Bin', 'order' => 1, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1, 'created_by' => 3, 'updated_by' => 3]);
        FormOption::create(['question_id' => $question->id, 'text' => 'Enclosure', 'value' => 'Enclosure', 'order' => 2, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1, 'created_by' => 3, 'updated_by' => 3]);
        // Logic
        $logic = FormLogic::create(['template_id' => $tid, 'page_id' => $pid, 'question_id' => $question->id, 'match_operation' => '=', 'match_value' => '23', 'trigger' => 'section', 'trigger_id' => '10', 'created_by' => 3, 'updated_by' => 3]);
        $logic = FormLogic::create(['template_id' => $tid, 'page_id' => $pid, 'question_id' => $question->id, 'match_operation' => '=', 'match_value' => '24', 'trigger' => 'section', 'trigger_id' => '11', 'created_by' => 3, 'updated_by' => 3]);

        // Questions - Section 2
        $section = FormSection::create(['template_id' => $tid, 'page_id' => $pid, 'parent' => null, 'name' => null, 'description' => "Section 7b", 'order' => 2, 'created_by' => 3, 'updated_by' => 3]);
        $sid = $section->id;
        $question = FormQuestion::create(
            ['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
             'name'        => "Location", 'type' => "select", 'type_special' => 'button', 'type_version' => null,
             'order'       => $pn ++, 'default' => null, 'multiple' => null, 'required' => 1, 'created_by' => 3, 'updated_by' => 3]);
        // Add Options
        FormOption::create(['question_id' => $question->id, 'text' => 'Driveway', 'value' => 'Driveway', 'order' => 1, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1, 'created_by' => 3, 'updated_by' => 3]);
        FormOption::create(['question_id' => $question->id, 'text' => 'Front yard', 'value' => 'Front yard', 'order' => 2, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1, 'created_by' => 3, 'updated_by' => 3]);
        FormOption::create(['question_id' => $question->id, 'text' => 'On street', 'value' => 'On street', 'order' => 3, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1, 'created_by' => 3, 'updated_by' => 3]);

        $question = FormQuestion::create(
            ['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
             'name'        => "Size", 'type' => "select", 'type_special' => null, 'type_version' => 'select2', 'placeholder' => 'Select size',
             'order'       => $pn ++, 'default' => null, 'multiple' => null, 'required' => 1, 'created_by' => 3, 'updated_by' => 3]);
        // Add Options
        FormOption::create(['question_id' => $question->id, 'text' => '3m (L)2.4m x (W)1.6m x (H)0.9m', 'value' => '3m (L)2.4m x (W)1.6m x (H)0.9m', 'order' => 1, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1, 'created_by' => 3, 'updated_by' => 3]);
        FormOption::create(['question_id' => $question->id, 'text' => '4m (L)3.2m x (W)1.6m x (H)0.9m', 'value' => '4m (L)3.2m x (W)1.6m x (H)0.9m', 'order' => 2, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1, 'created_by' => 3, 'updated_by' => 3]);
        FormOption::create(['question_id' => $question->id, 'text' => '6m (L)3.6m x (W)1.6m x (H)1.2m', 'value' => '6m (L)3.6m x (W)1.6m x (H)1.2m', 'order' => 3, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1, 'created_by' => 3, 'updated_by' => 3]);
        FormOption::create(['question_id' => $question->id, 'text' => '10m (L)4.3m x (W)1.6m x (H)1.5m', 'value' => '10m (L)4.3m x (W)1.6m x (H)1.5m', 'order' => 4, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1, 'created_by' => 3, 'updated_by' => 3]);
        FormOption::create(['question_id' => $question->id, 'text' => '14m (L)4.8m x (W)1.6m x (H)1.6m', 'value' => '14m (L)4.8m x (W)1.6m x (H)1.6m', 'order' => 5, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1, 'created_by' => 3, 'updated_by' => 3]);

        $question = FormQuestion::create(
            ['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
             'name'        => "Is a council permit required?", 'type' => "select", 'type_special' => 'YN', 'type_version' => null,
             'order'       => $pn ++, 'default' => null, 'multiple' => null, 'required' => 1, 'created_by' => 3, 'updated_by' => 3]);

        // Questions - Section 3
        $section = FormSection::create(['template_id' => $tid, 'page_id' => $pid, 'parent' => null, 'name' => null, 'description' => "Section 7c", 'order' => 3, 'created_by' => 3, 'updated_by' => 3]);
        $sid = $section->id;
        $question = FormQuestion::create(
            ['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
             'name'        => "Size", 'type' => "select", 'type_special' => 'button', 'type_version' => null,
             'order'       => $pn ++, 'default' => null, 'multiple' => null, 'required' => 1, 'created_by' => 3, 'updated_by' => 3]);
        // Add Options
        FormOption::create(['question_id' => $question->id, 'text' => 'Large (metal)', 'value' => 'Large (metal)', 'order' => 1, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1, 'created_by' => 3, 'updated_by' => 3]);
        FormOption::create(['question_id' => $question->id, 'text' => 'Small (timber)', 'value' => 'Small (timber)', 'order' => 2, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1, 'created_by' => 3, 'updated_by' => 3]);

        //
        // Page 8
        //
        $page = FormPage::create(['template_id' => $template->id, 'name' => "Underground or Overhead Services", 'description' => '', 'order' => 8, 'created_by' => 3, 'updated_by' => 3]);
        $pid = $page->id;

        // Questions - Section 1
        $section = FormSection::create(['template_id' => $tid, 'page_id' => $pid, 'parent' => null, 'name' => null, 'description' => "Section 8a", 'order' => 1, 'created_by' => 3, 'updated_by' => 3]);
        $sid = $section->id;
        $question = FormQuestion::create(
            ['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
             'name'        => "What type of electrical infrastructure supplies the property?", 'type' => "select", 'type_special' => null, 'type_version' => 'select2',
             'order'       => $pn ++, 'default' => null, 'multiple' => 1, 'required' => 1, 'created_by' => 3, 'updated_by' => 3]);
        // Add Options
        FormOption::create(['question_id' => $question->id, 'text' => 'Overhead powerlines / point of attachment', 'value' => 'Overhead powerlines / point of attachment', 'order' => 1, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1, 'created_by' => 3, 'updated_by' => 3]);
        FormOption::create(['question_id' => $question->id, 'text' => 'Private pole', 'value' => 'Private pole', 'order' => 2, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1, 'created_by' => 3, 'updated_by' => 3]);
        FormOption::create(['question_id' => $question->id, 'text' => 'Underground supply to property', 'value' => 'Underground supply to property', 'order' => 3, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1, 'created_by' => 3, 'updated_by' => 3]);

        $question = FormQuestion::create(
            ['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
             'name'        => "Is the property affected by nearby overhead powerlines?", 'type' => "select", 'type_special' => null, 'type_version' => 'select2',
             'order'       => $pn ++, 'default' => null, 'multiple' => 1, 'required' => 1, 'created_by' => 3, 'updated_by' => 3]);
        // Add Options
        FormOption::create(['question_id' => $question->id, 'text' => 'No', 'value' => 'No', 'order' => 1, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1, 'created_by' => 3, 'updated_by' => 3]);
        FormOption::create(['question_id' => $question->id, 'text' => 'Parallel (left)', 'value' => 'Parallel (left)', 'order' => 2, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1, 'created_by' => 3, 'updated_by' => 3]);
        FormOption::create(['question_id' => $question->id, 'text' => 'Parallel (right)', 'value' => 'Parallel (right)', 'order' => 3, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1, 'created_by' => 3, 'updated_by' => 3]);
        FormOption::create(['question_id' => $question->id, 'text' => 'Front of property', 'value' => 'Front of property', 'order' => 4, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1, 'created_by' => 3, 'updated_by' => 3]);
        FormOption::create(['question_id' => $question->id, 'text' => 'Rear of property', 'value' => 'Rear of property', 'order' => 5, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1, 'created_by' => 3, 'updated_by' => 3]);
        FormOption::create(['question_id' => $question->id, 'text' => 'Intersecting property', 'value' => 'Intersecting property', 'order' => 6, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1, 'created_by' => 3, 'updated_by' => 3]);

        $question = FormQuestion::create(
            ['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
             'name'        => "Has Dial Before You Dig been actioned as part of the design process?", 'type' => "select", 'type_special' => 'YN', 'type_version' => null,
             'order'       => $pn ++, 'default' => null, 'multiple' => null, 'required' => 1, 'created_by' => 3, 'updated_by' => 3]);
        // Logic
        $logic = FormLogic::create(['template_id' => $tid, 'page_id' => $pid, 'question_id' => $question->id, 'match_operation' => '=', 'match_value' => '5', 'trigger' => 'section', 'trigger_id' => '13', 'created_by' => 3, 'updated_by' => 3]);

        // Questions - Section 2
        $section = FormSection::create(['template_id' => $tid, 'page_id' => $pid, 'parent' => null, 'name' => null, 'description' => "Section 8b", 'order' => 2, 'created_by' => 3, 'updated_by' => 3]);
        $sid = $section->id;
        $question = FormQuestion::create(
            ['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
             'name'        => 'Detail', 'type' => "textarea", 'type_special' => null, 'type_version' => null,
             'order'       => $pn ++, 'default' => null, 'multiple' => null, 'required' => 1, 'created_by' => 3, 'updated_by' => 3]);
        $question = FormQuestion::create(
            ['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
             'name'        => 'Are there any underground assets identified as affected (Detail of assets affected)', 'type' => "select", 'type_special' => 'YrN', 'type_version' => null,
             'order'       => $pn ++, 'default' => null, 'multiple' => null, 'required' => 1, 'created_by' => 3, 'updated_by' => 3]);
        // Logic
        $logic = FormLogic::create(['template_id' => $tid, 'page_id' => $pid, 'question_id' => $question->id, 'match_operation' => '=', 'match_value' => '7', 'trigger' => 'question', 'trigger_id' => '48', 'created_by' => 3, 'updated_by' => 3]);

        $question = FormQuestion::create(
            ['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
             'name'        => 'Detail of assets affected', 'type' => "textarea", 'type_special' => null, 'type_version' => null,
             'order'       => $pn ++, 'default' => null, 'multiple' => null, 'required' => 1, 'created_by' => 3, 'updated_by' => 3]);

        //
        // Page 9
        //
        $page = FormPage::create(['template_id' => $template->id, 'name' => "Environmental Conditions", 'description' => '', 'order' => 9, 'created_by' => 3, 'updated_by' => 3]);
        $pid = $page->id;

        // Questions - Section 1
        $section = FormSection::create(['template_id' => $tid, 'page_id' => $pid, 'parent' => null, 'name' => null, 'description' => "Section 9a", 'order' => 1, 'created_by' => 3, 'updated_by' => 3]);
        $sid = $section->id;
        $question = FormQuestion::create(
            ['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
             'name'        => "Is the location of the project subject to any adverse environmental conditions?", 'type' => "select", 'type_special' => null, 'type_version' => 'select2',
             'order'       => $pn ++, 'default' => null, 'multiple' => 1, 'required' => 1, 'created_by' => 3, 'updated_by' => 3]);
        // Add Options
        FormOption::create(['question_id' => $question->id, 'text' => 'Bushfire', 'value' => 'Bushfire', 'order' => 1, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1, 'created_by' => 3, 'updated_by' => 3]);
        FormOption::create(['question_id' => $question->id, 'text' => 'Flooding', 'value' => 'Flooding', 'order' => 1, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1, 'created_by' => 3, 'updated_by' => 3]);
        FormOption::create(['question_id' => $question->id, 'text' => 'Noise Pollution', 'value' => 'Noise Pollution', 'order' => 1, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1, 'created_by' => 3, 'updated_by' => 3]);
        FormOption::create(['question_id' => $question->id, 'text' => 'Heritage', 'value' => 'Heritage', 'order' => 1, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1, 'created_by' => 3, 'updated_by' => 3]);
        FormOption::create(['question_id' => $question->id, 'text' => 'Other', 'value' => 'Other', 'order' => 1, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1, 'created_by' => 3, 'updated_by' => 3]);
        FormOption::create(['question_id' => $question->id, 'text' => 'Not applicable', 'value' => 'Not applicable', 'order' => 1, 'score' => 0, 'colour' => null, 'group' => null, 'master' => 0, 'status' => 1, 'created_by' => 3, 'updated_by' => 3]);

        // Logic
        $logic = FormLogic::create(['template_id' => $tid, 'page_id' => $pid, 'question_id' => $question->id, 'match_operation' => '=*', 'match_value' => '44,45,46,47,48', 'trigger' => 'question', 'trigger_id' => '50', 'created_by' => 3, 'updated_by' => 3]);

        $question = FormQuestion::create(
            ['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
             'name'        => 'Detail', 'type' => "textarea", 'type_special' => null, 'type_version' => null,
             'order'       => $pn ++, 'default' => null, 'multiple' => null, 'required' => 1, 'created_by' => 3, 'updated_by' => 3]);

        //
        // Page 10
        //
        $page = FormPage::create(['template_id' => $template->id, 'name' => "Hazardous Materials", 'description' => '', 'order' => 10, 'created_by' => 3, 'updated_by' => 3]);
        $pid = $page->id;

        // Questions - Section 1
        $section = FormSection::create(['template_id' => $tid, 'page_id' => $pid, 'parent' => null, 'name' => null, 'description' => "Section 10a", 'order' => 1, 'created_by' => 3, 'updated_by' => 3]);
        $sid = $section->id;
        $question = FormQuestion::create(
            ['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
             'name'        => "Has a hazardous materials survey been conducted?", 'type' => "select", 'type_special' => 'YgN', 'type_version' => null,
             'order'       => $pn ++, 'default' => null, 'multiple' => null, 'required' => 1, 'created_by' => 3, 'updated_by' => 3]);
        $question = FormQuestion::create(
            ['template_id' => $tid, 'page_id' => $pid, 'section_id' => $sid,
             'name'        => "Has asbestos been identified on the property?", 'type' => "select", 'type_special' => 'YrN', 'type_version' => null,
             'order'       => $pn ++, 'default' => null, 'multiple' => null, 'required' => 1, 'created_by' => 3, 'updated_by' => 3]);

        //
        // Create User Form
        //
        $form = Form::create(['template_id' => 1, 'name' => 'MyForm', 'company_id' => 3, 'created_by' => 3, 'updated_by' => 3]);

    }

    //
    // Show FormTemplate
    public function showTemplate($id)
    {
        $template = FormTemplate::find($id);

        echo "Name: $template->name ($template->description)<br>";
        echo "P:" . $template->pages->count() . " S:" . $template->sections->count() . " Q:" . $template->questions->count() . "<br>-----------<br><br>";

        foreach ($template->pages as $page) {
            echo "<br>=====================================<br>Page $page->id : $page->name<br>=====================================<br>";
            foreach ($page->sections as $section) {
                echo "Section $section->order : $section->name (pid:" . $section->page->id . " sid:$section->id)<br>-------------------------------------<br>";
                foreach ($section->questions as $question) {
                    echo "Q $question->id - $question->name (s:" . $question->section->id . ") &nbsp; T:$question->type  &nbsp; S:$question->type_special<br>";
                    if ($question->type == 'select' && count($question->options())) {
                        foreach ($question->options() as $opt) {
                            echo " &nbsp; &nbsp; [$opt->id] T:$opt->text V:$opt->value C:$opt->colour<br>";
                        }
                        echo "<br>";
                    }
                }
                echo "<br>";
            }
        }
    }


    public function createPermission()
    {
        //
        // Creating Permission
        //
        $name = 'Site Extension';
        $slug = 'site.extension';
        echo "Creating Permission for $name ($slug)<br><br>";
        // View
        $p = Permission2::create(['name' => "View $name", 'slug' => "view.$slug"]);
        $p->model = 'c';
        $p->save();
        // Edit
        $p = Permission2::create(['name' => "Edit $name", 'slug' => "edit.$slug"]);
        $p->model = 'c';
        $p->save();
        // Add
        $p = Permission2::create(['name' => "Add $name", 'slug' => "add.$slug"]);
        $p->model = 'c';
        $p->save();
        // Delete
        $p = Permission2::create(['name' => "Delete $name", 'slug' => "del.$slug"]);
        $p->model = 'c';
        $p->save();
        // Sig
        $p = Permission2::create(['name' => "Sign Off $name", 'slug' => "sig.$slug"]);
        $p->model = 'c';
        $p->save();
        echo "<br><br>Completed<br>-------------<br>";
    }
}
