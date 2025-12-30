<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Site\SiteQaController;
use App\Http\Utilities\ClientPlannerActionItems;
use App\Models\Client\ClientPlannerEmail;
use App\Models\Company\Company;
use App\Models\Misc\Attachment;
use App\Models\Site\Planner\SitePlanner;
use App\Models\Site\Planner\Task;
use App\Models\Site\Planner\Trade;
use App\Models\Site\Site;
use App\Models\Site\SiteQa;
use App\Services\FileBank;
use Carbon\Carbon;
use DB;
use Illuminate\Support\Facades\Auth;
use Mail;
use nilsenj\Toastr\Facades\Toastr;
use PDF;
use Session;
use Validator;
use Yajra\Datatables\Datatables;

/**
 * Class ClientPlannerEmailController
 * @package App\Http\Controllers
 */
class ClientPlannerEmailController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Check authorisation and throw 404 if not
        if (!Auth::user()->hasAnyPermissionType('client.planner.email'))
            return view('errors/404');

        return view('client/planner/email/list');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('add.client.planner.email'))
            return view('errors/404');

        return view('client/planner/email/create');
    }

    /**
     * Edit the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $email = ClientPlannerEmail::findOrFail($id);

        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('edit.client.planner.email', $email))
            return view('errors/404');

        return view('/client/planner/email/edit', compact('email'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store()
    {
        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('add.client.planner.email'))
            return view('errors/404');

        $rules = ['site_id' => 'required', 'intro' => 'required', 'type' => 'required', 'email1' => 'required'];

        // Add date field required rules
        if (request('include')) {
            foreach (request('include') as $action_id) {
                $rules ['itemdate-' . $action_id] = 'required';
            }
        }
        $mesg = [
            'site_id.required' => 'The site field is required.',
            'intro.required' => 'The introduction field is required.',
            'type.required' => 'The email type field is required.',
            'email1.required' => 'The email1 field is required.',
        ];
        // Add date field error messages
        for ($x = 1; $x < 20; $x++)
            $mesg["itemdate-$x.required"] = 'The date field is required.';

        request()->validate($rules, $mesg); // Validate

        /* ------------------------------------------------------------
        | Validate emails
        |------------------------------------------------------------ */
        $email1 = trim(request('email1'));
        $email2 = trim(request('email2'));
        $email3 = explode(';', request('email3'));
        if (request('email1') && !validEmail($email1))
            return back()->withErrors(['email1' => "Invalid email format for Email 1"]);

        if (request('email2') && !validEmail($email2))
            return back()->withErrors(['email2' => "Invalid email format for Email 2"]);

        if (request('email3')) {
            foreach ($email3 as $email) {
                if (!validEmail($email))
                    return back()->withErrors(['email3' => "Invalid email format for Additional emails"]);
            }
        }


        /* ------------------------------------------------------------
        | Create Email Record
        |------------------------------------------------------------ */
        $site = Site::findOrFail(request('site_id'));
        $email_user = validEmail(Auth::user()->email) ? Auth::user()->email : '';

        $sent_to = implode('; ', array_filter([$email1, $email2, ...$email3]));
        $sent_bcc = app()->environment('prod') ? 'construct@capecod.com.au' : env('EMAIL_DEV');

        if ($email_user)
            $sent_bcc .= "; {$email_user}";

        $email = ClientPlannerEmail::create([
            ...request()->all(),
            'sent_to' => $sent_to,
            'sent_bcc' => $sent_bcc,
            'subject' => "{$site->name}: Weekly Planner",
            'status' => 2, // Draft
        ]);

        /* ------------------------------------------------------------
        | Build Email Body
        |------------------------------------------------------------ */
        // Actions template
        $actions = '';
        if (request('type') == 'Action') {
            $actions = "As discussed in our Pre construction Meeting, I need you to start thinking about or to finalise for me, the following items:";
            $actions .= "<table style='border: 1px solid black;'>";
            $actions .= "<thead><tr><th>Item</th><th>&nbsp; Date Required &nbsp;</th></tr></thead>";
            foreach (request('include') as $item_id) {
                $actions .= "<tr>";
                $actions .= "<td>&nbsp; " . ClientPlannerActionItems::name($item_id) . " &nbsp;</td>";
                $actions .= "<td>&nbsp; " . request("itemdate-$item_id") . " &nbsp;</td>";
                $actions .= "</tr>";
            }
            $actions .= "</table>";

            if (request('further_notes'))
                $actions .= "<br>Further Notes as discussed:<br>" . request('further_notes') . "<br><br>";

        }

        // Generate body
        $body = "Hi " . request('intro') . ",<br><br>";
        $body .= "Please find attached this week’s construction Planner for your project and below overview of what to expect in the coming weeks.<br><br>";
        $body .= $actions;

        $body .= "Please note while it is our aim to meet the above dates in the Planner attached, forecasted dates are indicative only. I will endeavour to keep you updated with any changes throughout the week ahead. If you have a questions please as always feel free to call, text or email me.<br><br>";
        $body .= (Carbon::now()->isFriday() || Carbon::now()->isSaturday()) ? "Have a great weekend." : "Have a great afternoon.";
        $body .= "<br><br>" . Auth::user()->fullname;
        if (Auth::user()->jobtitle)
            $body .= "<br>" . strtoupper(Auth::user()->jobtitle);

        // Save body of email
        $email->update(['body' => $body]);

        /* ------------------------------------------------------------
        | Handle attachments
        |------------------------------------------------------------ */
        //dd(request()->all());
        $attachments = request("filepond");
        if ($attachments) {
            foreach ($attachments as $tmp_filename) {
                if ($tmp_filename) {
                    $attachment = Attachment::create(['table' => 'client_planner_emails', 'table_id' => $email->id, 'directory' => "site/{$email->site_id}/emails/client"]);
                    $attachment->saveAttachment($tmp_filename);
                }
            }
        }

        /* ------------------------------------------------------------
        | Generate Planner PDF → Spaces
        |------------------------------------------------------------ */
        $data = $this->clientPlanner($email->site_id, request('weeks'));
        $pdf = PDF::loadView('pdf/plan-site-client', compact('data'))->setPaper('A4', 'portrait');

        $filename = sanitizeFilename($site->name) . ' Weekly Planner.pdf';
        $basePath = "site/{$email->site_id}/emails/client";
        $path = "{$basePath}/{$filename}";

        // Store PDF directly to Spaces
        FileBank::putContents($path, $pdf->output());
        $attachment = Attachment::create(['table' => 'client_planner_emails', 'table_id' => $email->id, 'name' => $filename, 'attachment' => $filename, 'directory' => $basePath]);


        /* ------------------------------------------------------------
        |  Check for recent QAs
        |------------------------------------------------------------ */
        $lastEmail = ClientPlannerEmail::where('status', 0)->where('site_id', $site->id)->latest()->first();
        $date_from = $lastEmail ? $lastEmail->updated_at->format('Y-m-d') : max($site->created_at->format('Y-m-d'), '2022-07-30');

        if (SiteQa::where('site_id', $site->id)->where('status', 0)->whereDate('updated_at', '>=', $date_from)->exists())
            app(SiteQaController::class)->qaPDF(['email_id' => $email->id, 'date_from' => $date_from,]);

        Toastr::success("Email draft created");

        return redirect('/client/planner/email/' . $email->id . '/edit');
    }


    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $email = ClientPlannerEmail::findOrFail($id);

        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('view.client.planner.email', $email))
            return view('errors/404');

        return view('/client/planner/email/show', compact('email'));
    }


    /**
     * Update the specified resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function update($id)
    {
        $email = ClientPlannerEmail::findOrFail($id);

        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('edit.client.planner.email', $email))
            return view('errors/404');

        if (request()->ajax()) {

            $email_request = request()->all();

            //dd(request('email_body'));
            $body = request('email_body');
            //$body = preg_replace('/\\\\[t]/', '', $body);
            $body = str_replace(array("\t"), '', $body);
            $email_request['body'] = $body;
            $email_request['status'] = 0;  // Sent
            //dd(htmlspecialchars($email_request['body'], ENT_QUOTES, 'UTF-8'));
            //dd($email_request['body']);

            $email->update($email_request);

            $email->emailPlanner();
            Toastr::success("Email sent");

            return response()->json(['success' => true, 'message' => 'Your AJAX processed correctly']);
        }

        return redirect('client/planner/email/');
    }

    /**
     * Update the specified resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function updateStatus($id, $status)
    {
        /*
        $email = ClientPlannerEmail::findOrFail($id);

        $email->status = $status;
        $email->save();

        return redirect('client/planner/email/' . $email->id . '/edit');
        */
    }

    /**
     * Generate Client Planner
     */
    public function clientPlanner($site_id, $weeks = 2)
    {
        $site = Site::findOrFail($site_id);
        $obj_site = (object)[];
        $obj_site->site_id = $site->id;
        $obj_site->site_name = $site->name;
        $obj_site->weeks = [];

        // Upcoming Planner
        $date = Carbon::now()->startOfWeek()->format('Y-m-d');  // Monday of current week
        $data = [];

        $weeks = $weeks + 1;  // add an extra week to include current week in planner

        // For each week get Entities on the Planner
        $current_date = $date;
        for ($w = 1; $w <= $weeks; $w++) {
            $date_from = Carbon::createFromFormat('Y-m-d H:i:s', $current_date . ' 00:00:00');
            if ($date_from->isWeekend()) $date_from->addDays(1);
            if ($date_from->isWeekend()) $date_from->addDays(1);

            // Calculate Date to ensuring not a weekend
            $date_to = Carbon::createFromFormat('Y-m-d H:i:s', $date_from->format('Y-m-d H:i:s'));
            $dates = [$date_from->format('Y-m-d')];
            for ($i = 2; $i < 6; $i++) {
                $date_to->addDays(1);
                if ($date_to->isWeekend())
                    $date_to->addDays(2);
                $dates[] = $date_to->format('Y-m-d');
            }
            //echo "From: " . $date_from->format('d/m/Y') . " To:" . $date_to->format('d/m/Y') . "<br>";

            $planner = SitePlanner::select(['id', 'site_id', 'entity_type', 'entity_id', 'task_id', 'from', 'to', 'days'])
                // Tasks that start 'from' between mon-fri of given week
                ->where(function ($q) use ($date_from, $date_to, $site) {
                    $q->where('from', '>=', $date_from->format('Y-m-d'));
                    $q->Where('from', '<=', $date_to->format('Y-m-d'));
                    $q->where('site_id', $site->id);
                })
                // Tasks that end 'to between mon-fri of given week
                ->orWhere(function ($q) use ($date_from, $date_to, $site) {
                    $q->where('to', '>=', $date_from->format('Y-m-d'));
                    $q->Where('to', '<=', $date_to->format('Y-m-d'));
                    $q->where('site_id', $site->id);
                })
                // Tasks that start before mon but end after fri
                // ie they span the whole week but begin prior + end after given week
                ->orWhere(function ($q) use ($date_from, $date_to, $site) {
                    $q->where('from', '<', $date_from->format('Y-m-d'));
                    $q->Where('to', '>', $date_to->format('Y-m-d'));
                    $q->where('site_id', $site->id);
                })
                ->orderBy('from')->get();

            // Get Unique list of Entities for current week
            $entities = [];
            foreach ($planner as $plan) {
                $key = $plan->entity_type . '.' . $plan->entity_id;
                if (!isset($entities[$key])) {
                    // Task & Trade
                    $task = Task::find($plan->task_id);
                    $entity_task = ($task) ? $task->name : "Onsite";
                    $entity_trade = ($task) ? $task->trade->name : "-";

                    if ($plan->entity_type == 'c') {
                        $company = Company::find($plan->entity_id);
                        $entity_name = ($company) ? $company->name : "Company $plan->entity_id";
                    } else {
                        $trade = Trade::find($plan->entity_id);
                        $entity_name = ($trade) ? $trade->name : "Trade $plan->entity_id";
                    }
                    $entities[$key] = ['key' => $key, 'entity_type' => $plan->entity_type, 'entity_id' => $plan->entity_id, 'entity_name' => $entity_name, 'entity_task' => $entity_task, 'entity_trade' => $entity_trade,];
                    for ($i = 0; $i < 5; $i++)
                        $entities[$key][$dates[$i]] = '';
                }
            };
            usort($entities, 'sortEntityName');

            // Create Header Row for Current Week
            $obj_site->weeks[$w] = [];
            $obj_site->weeks[$w][0][] = 'TRADE';
            $i = 1;
            $offset = 0;
            foreach ($dates as $d)
                $obj_site->weeks[$w][0][$i++] = strtoupper(Carbon::createFromFormat('Y-m-d H:i:s', $d . ' 00:00:00')->format('l d/m'));

            // For each Entity on for current week get their Tasks for each day of the week
            $entity_count = 1;
            if ($entities) {
                foreach ($entities as $e) {
                    $obj_site->weeks[$w][$entity_count][] = $e['entity_trade'];
                    for ($i = 1; $i <= 5; $i++) {
                        $tasks = $site->entityTasksOnDate($e['entity_type'], $e['entity_id'], $dates[$i - 1]);
                        if ($tasks) {
                            $str = '';
                            foreach ($tasks as $task_id => $task_name)
                                $str .= $task_name . '<br>';
                        } else
                            $str = '&nbsp;';

                        $obj_site->weeks[$w][$entity_count][$i] = $str;
                    }
                    $entity_count++;
                }
            } else {
                $obj_site->weeks[$w][1][$offset] = 'NOTHING-ON-PLAN';
                $obj_site->weeks[$w][1][$offset + 1] = '';
            }

            $date_next = Carbon::createFromFormat('Y-m-d H:i:s', $current_date . ' 00:00:00')->addDays(7);;
            $current_date = $date_next->format('Y-m-d');
        }
        $data[] = $obj_site;

        return $data;
    }

    public function clientPlannerTable($data)
    {
        //dd($data);
        $output = '';
        foreach ($data[0]->weeks as $week_num => $week_data) {
            $output .= "\r\n<b>Week $week_num</b>";
            $output .= "<table style='border: 1px solid black;'>";
            foreach ($week_data as $row) {
                $cell_array = explode(' ', trim($row[1]));
                if ($cell_array[0] == 'MONDAY' || $cell_array[0] == 'TUESDAY' || $cell_array[0] == 'WEDNESDAY' || $cell_array[0] == 'THURSDAY' || $cell_array[0] == 'FRIDAY') {
                    $output .= "<thead>";
                    $output .= "<tr>";
                    $output .= "<th> &nbsp;" . $row[1] . " &nbsp;</th>";
                    $output .= "<th> &nbsp;" . $row[2] . " &nbsp;</th>";
                    $output .= "<th> &nbsp;" . $row[3] . " &nbsp;</th>";
                    $output .= "<th> &nbsp;" . $row[4] . " &nbsp;</th>";
                    $output .= "<th> &nbsp;" . $row[5] . " &nbsp;</th>";
                    $output .= "</tr></thead>";
                } else {
                    $output .= "<tr>";
                    if ($row[1] == 'NOTHING-ON-PLAN') {
                        $output .= "<td colspan='5'>No tasks for this week</td>";
                    } else {
                        $output .= "<td> &nbsp;" . $row[1] . " &nbsp;</td>";
                        $output .= "<td> &nbsp;" . $row[2] . " &nbsp;</td>";
                        $output .= "<td> &nbsp;" . $row[3] . " &nbsp;</td>";
                        $output .= "<td> &nbsp;" . $row[4] . " &nbsp;</td>";
                        $output .= "<td> &nbsp;" . $row[5] . " &nbsp;</td>";
                    }
                    $output .= "</tr>";
                }
            }
            $output .= "</table>";
        }

        return $output;
    }

    public function clientActionsTable($data)
    {

    }

    public function checkDocs($id)
    {
        $email = ClientPlannerEmail::findOrFail($id);
        $results = [];

        foreach ($email->attachments as $file) {
            $path = trim($file->directory, '/') . '/' . $file->attachment;
            $results[] = [
                'id' => $file->id,
                'name' => $file->name,
                'url' => FileBank::url($path),
                'status' => $file->status,
            ];
        }

        return $results;
    }

    public function checkDocs2($id)
    {
        $email = ClientPlannerEmail::findOrFail($id);

        $results = [];

        foreach ($email->attachments as $file) {

            if (!$file->directory || !$file->attachment) {
                $results[] = [
                    'id' => $file->id,
                    'name' => $file->name,
                    'url' => FileBank::url($path),
                    'status' => 0, // not started
                ];
                continue;
            }

            $path = trim($file->directory, '/') . '/' . $file->attachment;
            if (FileBank::exists($path)) {
                $results[] = [
                    'id' => $file->id,
                    'name' => $file->name,
                    'url' => FileBank::url($path),
                    'status' => 1, // complete
                ];
            } else {
                $results[] = [
                    'id' => $file->id,
                    'name' => $file->name,
                    'url' => null,
                    'status' => 2, // still processing
                ];
            }
        }

        return $results;
    }

    /**
     * Get Client Details for creating Email  ajax request.
     */
    public function getCreatefields($site_id)
    {
        $site = Site::findOrFail($site_id);

        if ($site) {
            return $site;
        }

        return null;
    }

    /**
     * Get Emails current user is authorised to manage + Process datatables ajax request.
     */
    public function getEmails()
    {
        $status = (request('status') == 0) ? [0] : [1, 2];
        $email_ids = ClientPlannerEmail::whereIn('status', $status)->pluck('id')->toArray();
        //dd($email_ids);

        $email_records = DB::table('client_planner_emails AS cpm')
            ->select(['cpm.id', 'cpm.site_id', 'cpm.type', 'cpm.updated_at', 'cpm.status',
                DB::raw('DATE_FORMAT(cpm.updated_at, "%d/%m/%y") AS updated'),
                DB::raw('sites.name AS sitename'), 'sites.code'])
            ->join('sites', 'cpm.site_id', '=', 'sites.id')
            ->whereIn('cpm.id', $email_ids);

        $dt = Datatables::of($email_records)
            ->addColumn('view', function ($record) {
                return ('<div class="text-center"><a href="/client/planner/email/' . $record->id . '"><i class="fa fa-search"></i></a></div>');
            })
            ->rawColumns(['view', 'action'])
            ->make(true);

        return $dt;
    }
}
