<?php

namespace App\Http\Controllers\Misc;

use App\Http\Controllers\Controller;
use App\Models\Company\Company;
use App\Models\Misc\Attachment;
use App\Models\Misc\Permission2;
use App\Models\Site\Planner\SitePlanner;
use App\Models\Site\Planner\Task;
use App\Models\Site\Planner\Trade;
use App\Models\Site\Site;
use App\Models\Site\SiteAsbestosRegister;
use App\Models\Site\SiteDoc;
use App\Models\Site\SiteQa;
use App\Models\Site\SiteQaAction;
use App\Models\Site\SiteQaItem;
use App\Models\Support\SupportTicket;
use App\User;
use Carbon\Carbon;
use DB;
use Illuminate\Support\Facades\Auth;
use Mail;
use Session;

class PagesController extends Controller
{

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
        if (Auth::user()->hasAnyRole2('web-admin|mgt-general-manager'))
            return view('pages/userlog');

        return view('errors/404');
    }

    public function userlogAuth()
    {
        if (Auth::user()->hasAnyRole2('web-admin|mgt-general-manager')) {
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

        echo "<h1>Migrate attachments</h1><br>";

        $logDir = storage_path('app/log/nightly');
        $logFile = "$logDir/" . Carbon::now()->format('Ymd') . '.txt';


        echo "<h2>Site Hazards</h2><br>";
        foreach (SiteHazardFile::all() as $file) {
            echo "$file->name<br>";
            $directory = "site/" . $file->hazard->site_id . '/hazard';
            $attach = Attachment::create(['table' => 'site_hazards', 'table_id' => $file->hazard_id, 'type' => $file->type, 'name' => $file->name, 'attachment' => $file->attachment, 'directory' => $directory, 'status' => 1]);
        }

        echo "<h2>Site Incidents</h2><br>";
        foreach (SiteIncidentDoc::all() as $file) {
            echo "$file->name<br>";
            $directory = "incident/$file->incident_id";
            $attach = Attachment::create(['table' => 'site_incidents', 'table_id' => $file->incident_id, 'type' => $file->type, 'name' => $file->name, 'attachment' => $file->attachment, 'directory' => $directory, 'status' => 1]);
        }

        echo "<h2>Site Plumbing/Electrical</h2><br>";
        foreach (SiteInspectionDoc::all() as $file) {
            echo "$file->name<br>";
            $inspection = $file->inspection();
            if ($inspection) {
                $directory = "site/" . $file->inspection()->site_id . "/inspection";
                $table = ($file->table == 'electrical') ? 'site_inspection_electrical' : 'site_inspection_plumbing';
                $attach = Attachment::create(['table' => $table, 'table_id' => $file->inspection()->id, 'type' => $file->type, 'name' => $file->name, 'attachment' => $file->attachment, 'directory' => $directory, 'status' => 1]);
            }
        }

        echo "<h2>Site Maintenance</h2><br>";
        foreach (SiteMaintenanceDoc::all() as $file) {
            echo "$file->name<br>";
            $main = SiteMaintenance::find($file->main_id);
            if ($main) {
                $directory = "site/$main->site_id/maintenance";
                $attach = Attachment::create(['table' => 'site_maintenance', 'table_id' => $file->main_id, 'type' => $file->type, 'name' => $file->name, 'attachment' => $file->attachment, 'directory' => $directory, 'status' => 1]);
            }
        }

        echo "<h2>Support Tickets</h2><br>";
        foreach (SupportTicketActionFile::all() as $file) {
            echo "$file->name<br>";
            $tix = SupportTicketAction::find($file->action_id);
            if ($tix) {
                $directory = "support/ticket";
                $attach = Attachment::create(['table' => 'support_tickets_actions', 'table_id' => $file->action_id, 'type' => $file->type, 'name' => $file->name, 'attachment' => $file->attachment, 'directory' => $directory, 'status' => 1]);
            }
        }

        echo "<h2>Scaffold</h2><br>";
        foreach (SiteScaffoldHandoverDoc::all() as $file) {
            echo "$file->name<br>";
            $directory = "site/{$file->scaffold_handover->site_id}/scaffold";
            $attach = Attachment::create(['table' => 'site_scaffold_handover', 'table_id' => $file->scaffold_id, 'type' => $file->type, 'name' => $file->name, 'attachment' => $file->attachment, 'directory' => $directory, 'status' => 1]);
        }


        echo "<h2>Client Planner</h2><br>";
        foreach (ClientPlannerEmailDoc::all() as $file) {
            echo "$file->name<br>";
            $client = ClientPlannerEmail::find($file->email_id);
            $directory = "site/{$client->site_id}/emails/client";
            $attach = Attachment::create(['table' => 'client_planner_emails', 'table_id' => $file->email_id, 'type' => 'file', 'name' => $file->name, 'attachment' => $file->attachment, 'directory' => $directory, 'status' => 1]);
        }

        echo "<h2>Update Attachments to not include filebank</h2>";
        foreach (Attachment::all() as $attachment) {
            if (str_starts_with($attachment->directory, "/filebank/")) {
                echo "$attachment->directory<br>";
                $attachment->directory = substr($attachment->directory, 10);
                $attachment->save();
            }
            $attachment->directory = str_replace("<br>", "", $attachment->directory);
            $attachment->save();
        }

        /*
        echo "Update Site Eworks + Pworks<br>";
        $sites = Site::where('company_id', '3')->whereNot('status', 0)->get();
        foreach ($sites as $site) {
            $up = '';
            if ($site->inspection_electrical->first() && !$site->eworks) {
                $site->eworks = $site->inspection_electrical->first()->assigned_to;
                $site->save();
                $up = 'E';
            }

            if ($site->inspection_plumbing->first() && !$site->pworks) {
                $site->pworks = $site->inspection_plumbing->first()->assigned_to;
                $site->save();
                $up .= 'P';
            }
            echo "$up [$site->id] " . $site->name . "<br>";
        }*/


        /*
        echo "Maintenance Items Migrate to Multi-items<br>";
        $docs = SiteMaintenance::all();
        foreach ($docs as $doc) {
            echo "[$doc->id] " . $doc->site->name . "<br>";
            if (count($doc->items) == 1) {
                $item = $doc->items->first();
                $item->assigned_to = $doc->assigned_to;
                $item->planner_id = $doc->planner_id;
                $item->save();
            }
        }*/


        /*
                echo "Scaffold certs for year<br>";
                $date = Carbon::parse('2024-01-01');
                $tasks = SitePlanner::whereDate('from', '>', $date)->whereIn('task_id', ['220', '24', '297'])->orderBy('site_id')->get();
                echo $tasks->count();
                echo "<br>";
                foreach ($tasks as $task) {
                    $scaf = SiteScaffoldHandover::where('site_id', $task->site_id)->first();
                    if ($scaf) {
                        echo "Has cert [$scaf->id]<br>";
                    } else {
                        if ($task->task_id == 220) $trade = 'Labourer';
                        if ($task->task_id == 24) $trade = 'Carpenter';
                        if ($task->task_id == 297) $trade = 'Scaffolder';
                        echo $task->from->format('d/m/Y') . " - " . $task->site->name . " - $trade" . "<br>";
                    }
                }*/


        /*echo "Migrate Incident Doc<br>";
        $docs = SiteIncidentDoc::all();
        foreach ($docs as $doc) {
            if ($doc->type == 'photo')
                $doc->type = 'image';
            if ($doc->type == 'doc')
                $doc->type = 'file';
            $doc->timestamps = false;
            $doc->save();
        }*/

        /*echo "Migrate Site Ext Category<br>";
        $cats = SiteExtensionCategory::all();
        foreach ($cats as $cat) {
            $new = Category::create(['type' => 'site_extension', 'name' => $cat->name, 'order' => $cat->order, 'company_id' => Auth::user()->company->reportsTo()->id, 'status' => $cat->status]);

            $extensions = SiteExtensionSite::all();
            foreach ($extensions as $ext) {
                $reasons = explode(',', $ext->reasons);
                $new_reasons = [];
                foreach ($reasons as $val)
                    $new_reasons[] = ($val == $cat->id) ? $new->id : $val;
                $ext->reasons = implode(',', $new_reasons);
                $ext->timestamps = false;
                $ext->save();
            }
        }*/

        /*
        echo "<b>Updating Hazard Files</b></br>";

        $hazards = SiteHazard::all();
        DB::table('site_hazards_files')->truncate();
        foreach ($hazards as $hazard) {
            if ($hazard->attachment) {
                $ext = pathinfo($hazard->attachment, PATHINFO_EXTENSION);
                $type = (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) ? 'image' : 'file';
                $file = SiteHazardFile::create(['hazard_id' => $hazard->id, 'type' => $type, 'name' => $hazard->attachment, 'attachment' => $hazard->attachment]);
                $file->timestamps = false;
                $file->created_by = $hazard->created_by;
                $file->created_at = $hazard->created_at;
                $file->updated_by = $hazard->updated_by;
                $file->updated_at = $hazard->created_at;
                $file->save();
                echo "[$hazard->id] ".$hazard->site->name."<br>";
            }
        }*/

        /*
        echo "<b>Open Project Supply ToDo</b></br>";

        $open = Todo::where('type', 'project supply')->where('status', '1')->get();
        foreach ($open as $todo) {
            $p = SiteProjectSupply::find($todo->type_id);
            echo "[$todo->id] ($p->status - $p->id) $todo->name<br>";
            if (!$p->status) {
                echo "close (".$todo->assignedToBySBC().") s:" . $p->supervisor_sign_at->format('d/m/Y') . " m:" . $p->manager_sign_at->format('d/m/Y') . "<br>";
                $p->closeToDo();
            }
        }*/


        // test
        /*
        echo "<b>Creating Merged PDF</b></br>";

        $site = Site::find(403);
        $site->createWhsManagementPlanPDF();

        $mergedPDF = PDFMerger::init();

        $cover = storage_path('app/tmp/report/3/QA 7865-IsikSantos-Bronte (403) 20220623143956.pdf');
        $master = storage_path('WHS Management Plan.pdf');
        $mergedPDF->addPDF($cover, 'all');
        $mergedPDF->addPDF($master, 'all');

        $mergedPDF->merge();
        $mergedPDF->save(storage_path('app/tmp/merged_result.pdf'));

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
        }*/
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
            $f = storage_path("/app/site/".$site->id);
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

    public function archiveOldData()
    {
        echo "<b>Archive Old Data</b><br><br>";

        $archiveDate = Carbon::now()->subYears(2);
        $archiveSites = [];
        $archiveCompanies = [];
        $archiveUsers = [];
        $archiveSizeBytes = 0;

        echo "Archive from: " . $archiveDate->format('d/m/Y') . "<br><br>";

        /*
         |--------------------------------------------------------------------------
         | Sites
         |--------------------------------------------------------------------------
         */
        $sites = Site::where('status', 0)->where('company_id', 3)->get();
        echo "Sites Count: {$sites->count()}<br><br>";

        foreach ($sites as $site) {
            $archive = false;

            $lastPlanner = SitePlanner::where('site_id', $site->id)->orderByDesc('to')->first();

            if (!$lastPlanner || $lastPlanner->to->lt($archiveDate)) {
                $archive = true;
            }

            if ($archive) {
                $archiveSites[] = $site->id;
            }
        }

        echo "<br>---------- Archived Sites ----------<br>";
        echo "Count: " . count($archiveSites) . "<br>";

        $size = 0;
        foreach ($archiveSites as $siteId) {
            $bytes = FileBank::folderSize("site/{$siteId}");
            $size += $bytes;
        }

        echo "Total size: " . round($size / 1024 / 1024 / 1024, 2) . " GB<br>";
        echo "-----------------------------------<br>";
        $archiveSizeBytes += $size;

        /*
         |--------------------------------------------------------------------------
         | Companies
         |--------------------------------------------------------------------------
         */
        $companies = Company::where('status', 0)->where('parent_company', 3)->get();
        echo "<br>Companies Count: {$companies->count()}<br><br>";

        foreach ($companies as $company) {
            if ($company->updated_at && $company->updated_at->lt($archiveDate)) {
                $archiveCompanies[] = $company->id;
            }
        }

        echo "<br>---------- Archived Companies ----------<br>";
        echo "Count: " . count($archiveCompanies) . "<br>";

        $size = 0;
        foreach ($archiveCompanies as $companyId) {
            $bytes = FileBank::folderSize("company/{$companyId}");
            $size += $bytes;
        }

        echo "Total size: " . round($size / 1024 / 1024 / 1024, 2) . " GB<br>";
        echo "--------------------------------------<br>";
        $archiveSizeBytes += $size;

        /*
         |--------------------------------------------------------------------------
         | Users
         |--------------------------------------------------------------------------
         */
        $users = User::where('status', 0)->whereIn('company_id', $archiveCompanies)->get();
        echo "<br>Users Count: {$users->count()}<br><br>";

        foreach ($users as $user) {
            if ($user->updated_at && $user->updated_at->lt($archiveDate)) {
                $archiveUsers[] = $user->id;
            }
        }

        echo "<br>---------- Archived Users ----------<br>";
        echo "Count: " . count($archiveUsers) . "<br>";

        $size = 0;
        foreach ($archiveUsers as $userId) {
            $bytes = FileBank::folderSize("users/{$userId}");
            $size += $bytes;
        }

        echo "Total size: " . round($size / 1024 / 1024 / 1024, 2) . " GB<br>";
        echo "-----------------------------------<br>";

        $archiveSizeBytes += $size;

        /*
         |--------------------------------------------------------------------------
         | Final Total
         |--------------------------------------------------------------------------
         */
        echo "<br><strong>Total Archive Size: "
            . round($archiveSizeBytes / 1024 / 1024 / 1024, 2)
            . " GB</strong><br>";
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

    public function triggerQA()
    {
        echo "Manually trigger QA creation<br><br>";
        $master_qas = ['2581']; // 2581 Handover, 2563 On Completion
        $site_ids = ['702'];

        foreach ($master_qas as $master_qa) {
            // Create new QA by copying required template
            $qa_master = SiteQa::findOrFail($master_qa);

            foreach ($site_ids as $site_id) {
                $site = Site::findOrFail($site_id);
                if (!$site->hasTemplateQa($master_qa)) {
                    echo "Creating QA [$qa_master->name] for site [$site->name]";

                    // Create new QA Report for Site
                    $newQA = SiteQa::create([
                        'name' => $qa_master->name,
                        'site_id' => $site->id,
                        'version' => $qa_master->version,
                        'master' => '0',
                        'master_id' => $qa_master->id,
                        'company_id' => $qa_master->company_id,
                        'status' => '1',
                        'created_by' => '1',
                        'updated_by' => '1',
                    ]);

                    // Copy items from template
                    foreach ($qa_master->items as $item) {
                        $newItem = SiteQaItem::create(
                            ['doc_id' => $newQA->id,
                                'task_id' => $item->task_id,
                                'name' => $item->name,
                                'order' => $item->order,
                                'super' => $item->super,
                                'master' => '0',
                                'master_id' => $item->id,
                                'created_by' => '1',
                                'updated_by' => '1',
                            ]);
                    }
                    echo "....created QA [$newQA->id]<br>";
                    $newQA->createToDo($site->supervisor_id);
                } else {
                    echo "Existing QA [$qa_master->name] for site [$site->name]<br>";
                }
            }
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
                $count++;
                if ($rec->task_id == 11)
                    $start++;

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
                $bad_end++;
                //$rec->delete(); // delete bad record
            } else {
                $workdays = $this->workDaysBetween($rec->from, $rec->to);
                if ($workdays != $rec->days) {
                    echo "$workdays/$rec->days $rec->id F:" . $rec->from->format('Y-m-d') . " T:" . $rec->to->format('Y-m-d') . " site:$site->name   task:$taskname<br>";
                    $bad_daycount++;

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
                $counter++;
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
            $counter++;

        return $counter;
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

    public function createPermission()
    {
        //
        // Creating Permission
        //
        $name = 'Site FOC';
        $slug = 'site.foc';
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
