<?php

namespace App\Http\Controllers\Misc;

use App\Http\Controllers\Controller;
use App\Models\Company\Company;
use App\Models\Misc\Equipment\Equipment;
use App\Models\Misc\Equipment\EquipmentCategory;
use App\Models\Misc\Equipment\EquipmentLocation;
use App\Models\Misc\Equipment\EquipmentLocationItem;
use App\Models\Misc\Equipment\EquipmentLog;
use App\Models\Misc\FormQuestion;
use App\Models\Misc\Supervisor\SuperChecklist;
use App\Models\Misc\Supervisor\SuperChecklistCategory;
use App\Models\Misc\Supervisor\SuperChecklistQuestion;
use App\Models\Misc\Supervisor\SuperChecklistResponse;
use App\Models\Site\Site;
use App\Models\Site\SiteExtension;
use App\Models\Site\SiteExtensionSite;
use App\Models\Site\SiteQaAction;
use App\User;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Mail;
use Session;

class PagesImportController extends Controller
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

    }


    public function quick()
    {

        echo "Testing Email<br>";
        $users = User::all();
        foreach ($users as $user) {
            $valid = (validEmail($user->email)) ? '' : '*** ';
            if ($valid) {
                $trim = trim($user->email);
                //if ($trim != $user->email)
                echo $valid . "[$user->email]<br>";
            }
        }


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


    }

    public function importCompany(Request $request)
    {
        echo "Importing Companies<br><br>";
        $row = 0;
        if (($handle = fopen(public_path("company.csv"), "r")) !== false) {
            while (($data = fgetcsv($handle, 5000, ",")) !== false) {
                $row++;
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

    public function importTimeExtensions(Request $request)
    {
        echo "Importing Time Extensions<br><br>";
        $row = 0;
        $cutoff_date = Carbon::createFromFormat('d/m/Y H:i', '21/09/2023 00:00');
        $site_data = [];
        $extension = SiteExtension::find(4);
        if (($handle = fopen(public_path("TimeExtensions.csv"), "r")) !== false) {
            while (($data = fgetcsv($handle, 5000, ",")) !== false) {
                $row++;
                if ($row == 1) continue;
                $num = count($data);

                $site = Site::where('code', $data[1])->first();

                // Created Time
                list($date, $time) = explode(' ', $data[7]);
                list($d, $m, $y) = explode('/', $date);
                $date_with_leading_zeros = sprintf('%02d', $d) . '/' . sprintf('%02d', $m) . '/' . sprintf('%04d', $y);
                $created = Carbon::createFromFormat('d/m/Y H:i', $date_with_leading_zeros . '00:00');
                $include = ($created->lt($cutoff_date)) ? '*' : '';
                if ($site && $include) {
                    //echo $include . "[$site->id] $site->name - " . $created->format('d/m/Y') . "<br>";
                    $site_data[$site->id][] = ['days' => $data[3], 'reason' => $data[4], 'notes' => $data[5], 'created' => $created->format('d/m/Y')];
                } else {
                    echo "** NEW SITE ** <br>";
                }
            }
            fclose($handle);

            ksort($site_data);
            echo "<br><br>--------------<br>";
            foreach ($site_data as $site_id => $data) {
                $site = Site::find($site_id);
                $total = 0;
                $text = '';
                foreach ($data as $d) {
                    $total = $total + $d['days'];
                    $notes = ($d['notes']) ? $d['notes'] : '';
                    $text .= $d['created'] . " - " . $d['days'] . " days <b>" . $d['reason'] . "</b>: $notes \r\n";
                }
                $text = "Bulk Import Zoho\n\r----------------------------------------------------------------\r\n" . $text . "----------------------------------------------------------------\r\n";
                echo "[$site->id] $site->name - $total<br>" . nl2br($text) . "<br>";

                $site_extension = SiteExtensionSite::where('extension_id', 4)->where('site_id', $site->id)->first();
                if ($site_extension) {
                    $site_extension->days = $total;
                    $site_extension->reasons = 1;
                    $site_extension->notes = $text;
                    $site_extension->save();
                    echo "updated<br>";
                } else {
                    SiteExtensionSite::create(['extension_id' => '4', 'site_id' => $site->id, 'days' => $total, 'reasons' => '1', 'notes' => $text]);
                    echo "created<br>";
                }
            }
            dd($site_data);
        }
        echo "<br><br>Completed<br>-------------<br>";
    }

    public function importMaterials()
    {
        echo "Importing Materials<br><br>";
        $row = 0;
        if (($handle = fopen(public_path("materials.csv"), "r")) !== false) {
            while (($data = fgetcsv($handle, 5000, ",")) !== false) {
                $row++;
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
                        'name' => $name,
                        'length' => $length,
                        'status' => 1
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
                $row++;
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
                    $pid = substr($payroll, -2, 1);

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
        echo "Importing Incident Questions<br>---------------------<br><br>";
        $row = 0;
        if (($handle = fopen(public_path("resp.csv"), "r")) !== false) {
            while (($data = fgetcsv($handle, 5000, ",")) !== false) {
                $row++;
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
                $row++;
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

    /*
    * Initialise Supervisor Checklist
    */
    public function initSuperChecklist()
    {
        $now = Carbon::now()->format('d/m/Y g:i a');
        echo "<b>Reseting Super Checklist - $now</b></br>";
        DB::table('supervisor_checklist')->truncate();
        DB::table('supervisor_checklist_categories')->truncate();
        DB::table('supervisor_checklist_questions')->truncate();
        DB::table('supervisor_checklist_responses')->truncate();
        DB::table('supervisor_checklist_notes')->truncate();
        echo "<b>Creating Super Checklist Questions - $now</b></br>";

        //
        // Categories
        //
        $order = 1;
        $cat = SuperChecklistCategory::create(['name' => 'Daily Activities', 'description' => 'as a reminder and update of the days activities', 'parent' => null, 'order' => $order++]);
        $cat = SuperChecklistCategory::create(['name' => 'Forward Planning and Confirmation', 'description' => null, 'parent' => null, 'order' => $order++]);
        $cat = SuperChecklistCategory::create(['name' => 'Clean ups & Labour', 'description' => null, 'parent' => null, 'order' => $order++]);
        $cat = SuperChecklistCategory::create(['name' => 'New Project', 'description' => null, 'parent' => null, 'order' => $order++]);
        $cat = SuperChecklistCategory::create(['name' => 'Maintenance', 'description' => null, 'parent' => null, 'order' => $order++]);

        //
        // Questions
        //

        $order = 1;
        $question = SuperChecklistQuestion::create(['cat_id' => 1, 'name' => "Download photos taken at each project", 'type' => 'YNNA', 'order' => $order++, 'default' => null, 'multiple' => null, 'required' => 1]);
        $question = SuperChecklistQuestion::create(['cat_id' => 1, 'name' => "Check weekly planner that all trades have signed in for the day for compliance", 'type' => 'YNNA', 'order' => $order++, 'default' => null, 'multiple' => null, 'required' => 1]);
        $question = SuperChecklistQuestion::create(['cat_id' => 1, 'name' => "Review and check all QA Checklists considering what may need checking next visit", 'type' => 'YNNA', 'order' => $order++, 'default' => null, 'multiple' => null, 'required' => 1]);
        $question = SuperChecklistQuestion::create(['cat_id' => 1, 'name' => "Have any inspections been carried out today that require confirmation and documentation", 'type' => 'YNNA', 'order' => $order++, 'default' => null, 'multiple' => null, 'required' => 1]);
        $question = SuperChecklistQuestion::create(['cat_id' => 1, 'name' => "Are inspections needing to be booked & scheduled in Site Planners", 'type' => 'YNNA', 'order' => $order++, 'default' => null, 'multiple' => null, 'required' => 1]);
        $order = 1;
        $question = SuperChecklistQuestion::create(['cat_id' => 2, 'name' => "Check tomorrows planner and consider whether anyone needs a call to confirm that the project is ready for them, in most cases this has been done a week or so earlier however an extra call to
let them know things are ready breeds confidence", 'type' => 'YNNA', 'order' => $order++, 'default' => null, 'multiple' => null, 'required' => 1]);
        $question = SuperChecklistQuestion::create(['cat_id' => 2, 'name' => "While in the planner look further forward with a mindset as to what may need organising at least one, two and three weeks in advance", 'type' => 'YNNA', 'order' => $order++, 'default' => null, 'multiple' => null, 'required' => 1]);
        $question = SuperChecklistQuestion::create(['cat_id' => 2, 'name' => "Are any variations required to be raised?", 'type' => 'YNNA', 'order' => $order++, 'default' => null, 'multiple' => null, 'required' => 1]);
        $question = SuperChecklistQuestion::create(['cat_id' => 2, 'name' => "Are any materials required to be released?", 'type' => 'YNNA', 'order' => $order++, 'default' => null, 'multiple' => null, 'required' => 1]);
        $question = SuperChecklistQuestion::create(['cat_id' => 2, 'name' => "Are any Orders or contracts required to be sent?", 'type' => 'YNNA', 'order' => $order++, 'default' => null, 'multiple' => null, 'required' => 1]);
        $question = SuperChecklistQuestion::create(['cat_id' => 2, 'name' => "Are any clients required to be contacted to provide updates or confirmation either by phone or by email", 'type' => 'YNNA', 'order' => $order++, 'default' => null, 'multiple' => null, 'required' => 1]);
        $order = 1;
        $question = SuperChecklistQuestion::create(['cat_id' => 3, 'name' => "Organise all clean ups & labours for tomorrow and if possible for days in advance. Example for final cleans, strip days or large demolition. Plan with relevant supervisor", 'type' => 'YNNA', 'order' => $order++, 'default' => null, 'multiple' => null, 'required' => 1]);
        $order = 1;
        $question = SuperChecklistQuestion::create(['cat_id' => 4, 'name' => "Once allocated new project take time to study plans and specifications in full", 'type' => 'YNNA', 'order' => $order++, 'default' => null, 'multiple' => null, 'required' => 1]);
        $question = SuperChecklistQuestion::create(['cat_id' => 4, 'name' => "Consider all pre-construction requirements, book pre-construction meeting", 'type' => 'YNNA', 'order' => $order++, 'default' => null, 'multiple' => null, 'required' => 1]);
        $question = SuperChecklistQuestion::create(['cat_id' => 4, 'name' => "Plan project out as far as possible in Site planner", 'type' => 'YNNA', 'order' => $order++, 'default' => null, 'multiple' => null, 'required' => 1]);
        $question = SuperChecklistQuestion::create(['cat_id' => 4, 'name' => "Organise job set up requirements with Aaron", 'type' => 'YNNA', 'order' => $order++, 'default' => null, 'multiple' => null, 'required' => 1]);
        $question = SuperChecklistQuestion::create(['cat_id' => 4, 'name' => "Book Ashby’s scaffold", 'type' => 'YNNA', 'order' => $order++, 'default' => null, 'multiple' => null, 'required' => 1]);
        $order = 1;
        $question = SuperChecklistQuestion::create(['cat_id' => 5, 'name' => "Action Maintenance requests", 'type' => 'YNNA', 'order' => $order++, 'default' => null, 'multiple' => null, 'required' => 1]);
        $question = SuperChecklistQuestion::create(['cat_id' => 5, 'name' => "Make appointments", 'type' => 'YNNA', 'order' => $order++, 'default' => null, 'multiple' => null, 'required' => 1]);
        $question = SuperChecklistQuestion::create(['cat_id' => 5, 'name' => "Organise rectification", 'type' => 'YNNA', 'order' => $order++, 'default' => null, 'multiple' => null, 'required' => 1]);

        $mon = new Carbon('monday this week');
        foreach (Company::find(3)->supervisors() as $super) {
            if ($super->name == "TO BE ALLOCATED")
                continue;

            $mesg = "Existing";
            $checklist = SuperChecklist::where('super_id', $super->id)->whereDate('date', $mon->format('Y-m-d'))->first();
            if (!$checklist) {
                $checklist = SuperChecklist::create(['super_id' => $super->id, 'date' => $mon->toDateTimeString(), 'status' => 1]);
                $mesg = "Creating new";

                for ($day = 1; $day < 6; $day++) {
                    foreach ($checklist->questions()->sortBy('id') as $question)
                        $response = SuperChecklistResponse::create(['checklist_id' => $checklist->id, 'day' => $day, 'question_id' => $question->id, 'status' => 1, 'created_by' => 1]);
                }
            }

            echo "$mesg week: " . $mon->format('d/m/Y') . " Super:$super->name<br>";
            //$log .= "$mesg week: " . $mon->format('d/m/Y') . "Super:$super->name\n";
        }
    }

    /*
    * New Supervisor Checklist
    */
    public function newSuperChecklist()
    {
        $now = Carbon::now()->format('d/m/Y g:i a');
        echo "<b>New Super Checklists - $now</b></br>";

        $mon = new Carbon('monday this week');
        foreach (Company::find(3)->supervisors() as $super) {
            if ($super->name == "TO BE ALLOCATED")
                continue;

            $mesg = "Existing";
            $checklist = SuperChecklist::where('super_id', $super->id)->whereDate('date', $mon->format('Y-m-d'))->first();
            if (!$checklist) {
                $checklist = SuperChecklist::create(['super_id' => $super->id, 'date' => $mon->toDateTimeString(), 'status' => 1]);
                $mesg = "Creating new";

                for ($day = 1; $day < 6; $day++) {
                    foreach ($checklist->questions()->sortBy('id') as $question)
                        $response = SuperChecklistResponse::create(['checklist_id' => $checklist->id, 'day' => $day, 'question_id' => $question->id, 'status' => 1, 'created_by' => 1]);
                }
            }

            echo "$mesg week: " . $mon->format('d/m/Y') . " Super:$super->name<br>";
            //$log .= "$mesg week: " . $mon->format('d/m/Y') . "Super:$super->name\n";
        }
    }

    public function importSiteSupervivors()
    {
        echo "Importing Site Supervisors from Zoho<br>---------------------<br><br>";
        $row = 0;

        $superLookup = [
            'X-Dean B' => 'Dean Beringer',
            'X-Andrew C' => 'Andrew Cashmore',
            'X-Phil D' => 'Philip Doughty',
            'X-Todd' => 'Todd Fordham',
            'X-David W' => 'David Wahba',
            'X-Jaimie S' => 'Jaimie Smith',
            'JL' => 'Josh Lay',
        ];
        $superIds = [
            'Dean Beringer' => '5',
            'Andrew Cashmore' => '281',
            'Philip Doughty' => '9',
            'David Wahba' => '133',
            'Jaimie Smith' => '538',
            'Josh Lay' => '696',
            'Aaron Graham' => '432',
            'Damian Cook' => '2252',
            'Jason Frazer' => '2216',
            'Josh James' => '2215',
            'Robert Ristevski' => '1065',
            'Ross Thomson' => '1155',
            'TO BE ALLOCATED' => 136,
            'Gary Klomp' => '7',
        ];
        $siteStatus = ['1' => 'Active', '0' => 'Completed', '-1' => 'Upcoming', '2' => 'Maintenance', '-2' => 'Cancelled'];
        $siteStatusColour = ['1' => 'color:#26c281', '0' => 'color:#555', '-1' => '', '2' => 'color:#f7ca18', '-2' => 'color:#d91e18'];
        if (($handle = fopen(public_path("Jobs_for_Fudge.csv"), "r")) !== false) {
            while (($data = fgetcsv($handle, 5000, ",")) !== false) {
                $row++;
                if ($row == 1) continue;
                //echo $data[0];
                $job = $data[1];
                $super_initials = $data[6];
                $super_name = $data[7];
                $site = Site::where('code', $job)->first();
                if ($site && $site->status != 1 && ($super_name || $super_initials) && $super_initials != 'X-Todd') {
                    $textColour = $siteStatusColour[$site->status];

                    if ($site->supervisor) {
                        $sName = $site->supervisor->name;
                        $sInit = $site->supervisor->initials;
                    } else {
                        echo "No SUPER: $site->name<br>";
                        $sName = '';
                        $sInit = '';
                    }

                    if ($super_name) {
                        // Check for exact SuperName
                        if ($sName != $super_name) {
                            echo '<span style="' . $textColour . '">' . "$site->name: [$sName] <= [$super_name] &nbsp; (" . $siteStatus[$site->status] . ")</span><br>";
                            if (isset($superIds[$super_name])) {
                                $site->supervisor_id = $superIds[$super_name];
                                $site->save();
                            } else
                                echo "***** SuperID Not Found ***<br>";
                        }
                    } elseif ($super_initials) {
                        // Check for Same SuperInitials
                        if ($sInit != $super_initials) {
                            // Check for Deactived Zoho SuperInitials to SWS SuperName
                            if (isset($superLookup[$super_initials])) {
                                $super_name = $superLookup[$super_initials];
                                if ($sName != $super_name) {
                                    echo '<span style="' . $textColour . '">' . "$site->name: [$sName] <= [$super_name] &nbsp; (" . $siteStatus[$site->status] . ")</span><br>";
                                    if (isset($superIds[$super_name])) {
                                        $site->supervisor_id = $superIds[$super_name];
                                        $site->save();
                                    } else
                                        echo "***** SuperID Not Found ***<br>";
                                }

                            } else {
                                //echo "*** $site->name: [$super] <= [$super_initials] - [$super_name]<br>";
                            }
                        }
                    }

                }
            }
            fclose($handle);
        }
        echo "<br><br>Completed<br>-------------<br>";
    }
}
