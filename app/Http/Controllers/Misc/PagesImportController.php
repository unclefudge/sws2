<?php

namespace App\Http\Controllers\Misc;

use App\Http\Controllers\Controller;
use App\Models\Company\Company;
use App\Models\Misc\DesignerPostcode;
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
        if (($handle = fopen(storage_path("app/files/company.csv"), "r")) !== false) {
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
        if (($handle = fopen(storage_path("app/files/TimeExtensions.csv"), "r")) !== false) {
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
        if (($handle = fopen(storage_path("app/files/materials.csv"), "r")) !== false) {
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
        if (($handle = fopen(storage_path("app/files/payroll.csv"), "r")) !== false) {
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
        if (($handle = fopen(storage_path("app/files/resp.csv"), "r")) !== false) {
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

    public function convertTasks()
    {
        echo "Subject,All Modules, Due Date, Priority, Status, Assigned To, Modified On<br>";
        $row = '';

        if (($handle = fopen(storage_path("app/files/tasks.txt"), "r")) !== false) {
            while (($line = fgets($handle)) !== false) {
                $line = trim($line);
                $fields = explode("\t", $line);

                if ($fields[0] == 'user')
                    continue;
                elseif (preg_match('/\d+\/\d+\/\d+/', $fields[0])) {
                    echo $row . $fields[0] . "<br>";
                    $row = '';
                } else {
                    foreach ($fields as $field) {
                        $field = trim($field);
                        $row .= "$field,";
                    }
                    $fields_count = count($fields);
                    for ($i = 1; $i < (7 - count($fields)); $i++)
                        $row .= ",";
                }


            }
            fclose($handle);
        }
        echo "<br><br>Completed<br>-------------<br>";
    }

    public function convertEmails()
    {
        echo "Name,No. Of Receipients,All Modules,Email Templates,Modified On<br>";
        $row = '';

        if (($handle = fopen(storage_path("app/files/emails.txt"), "r")) !== false) {
            while (($line = fgets($handle)) !== false) {
                $line = trim($line);
                $fields = explode("\t", $line);

                if ($fields[0] == 'user')
                    continue;
                elseif (preg_match('/\d+\/\d+\/\d+/', $fields[0])) {
                    echo $row . $fields[0] . "<br>";
                    $row = '';
                } else {
                    foreach ($fields as $field) {
                        $field = trim($field);
                        $row .= "$field,";
                    }
                    $fields_count = count($fields);
                    for ($i = 1; $i < (5 - count($fields)); $i++)
                        $row .= ",";
                }


            }
            fclose($handle);
        }
        echo "<br><br>Completed<br>-------------<br>";
    }

    public function importMaintenance()
    {
        echo "Importing Maintenance<br>---------------------<br><br>";
        $row = 0;
        if (($handle = fopen(storage_path("app/files/maintenance.csv"), "r")) !== false) {
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
        if (($handle = fopen(storage_path("app/files/Jobs_for_Fudge.csv"), "r")) !== false) {
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

    public function postcodeSeeder(): void
    {
        $unresolvedSuburbs = [
            'EDMONDSON PARK',
            'BOWING',
            'GING',
            'JANE',
            'KRIS',
            'SIMON',
            'STEPHEN',
            'UNKNOWN',
            'VONNIE',
        ];

        $removedSuburbs = [
            'CAMPBELLTOWN',
            'GOSFORD',
            'SUTHERLAND',
        ];

        $rows = [
            ['postcode' => '2176', 'suburb' => 'ABBOTSBURY', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2046', 'suburb' => 'ABBOTSFORD', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2763', 'suburb' => 'ACACIA GARDENS', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2015', 'suburb' => 'ALEXANDRIA', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2234', 'suburb' => 'ALFORDS POINT', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2100', 'suburb' => 'ALLAMBIE', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2100', 'suburb' => 'ALLAMBIE HEIGHTS', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2560', 'suburb' => 'AMBARVALE', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2038', 'suburb' => 'ANNANDALE', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2156', 'suburb' => 'ANNANGROVE', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2560', 'suburb' => 'APPIN', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2159', 'suburb' => 'ARCADIA', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2205', 'suburb' => 'ARNCLIFFE', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2148', 'suburb' => 'ARNDELL PARK', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2064', 'suburb' => 'ARTARMON', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2193', 'suburb' => 'ASHBURY', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2168', 'suburb' => 'ASHCROFT', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2131', 'suburb' => 'ASHFIELD', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2077', 'suburb' => 'ASQUITH', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2144', 'suburb' => 'AUBURN', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2107', 'suburb' => 'AVALON', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2107', 'suburb' => 'AVALON BEACH', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2251', 'suburb' => 'AVOCA BEACH', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2093', 'suburb' => 'BALGOWLAH', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2093', 'suburb' => 'BALGOWLAH HEIGHTS', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2041', 'suburb' => 'BALMAIN', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2088', 'suburb' => 'BALMORAL BEACH', 'state' => 'NSW', 'active' => true], // manual postcode; neighbourhood in Mosman area
            ['postcode' => '2234', 'suburb' => 'BANGOR', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2216', 'suburb' => 'BANKSIA', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2019', 'suburb' => 'BANKSMEADOW', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2200', 'suburb' => 'BANKSTOWN', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2565', 'suburb' => 'BARDIA', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2207', 'suburb' => 'BARDWELL PARK', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2207', 'suburb' => 'BARDWELL VALLEY', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2197', 'suburb' => 'BASS HILL', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2153', 'suburb' => 'BAULKHAM HILLS', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2104', 'suburb' => 'BAYVIEW', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2100', 'suburb' => 'BEACON HILL', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2015', 'suburb' => 'BEACONSFIELD', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2155', 'suburb' => 'BEAUMONT HILLS', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2088', 'suburb' => 'BEAUTY POINT', 'state' => 'NSW', 'active' => true], // manual postcode; neighbourhood in Mosman area
            ['postcode' => '2119', 'suburb' => 'BEECROFT', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2191', 'suburb' => 'BELFIELD', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2153', 'suburb' => 'BELLA VISTA', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2023', 'suburb' => 'BELLEVUE HILL', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2192', 'suburb' => 'BELMORE', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2085', 'suburb' => 'BELROSE', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2141', 'suburb' => 'BERALA', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2261', 'suburb' => 'BERKLEY VALE', 'state' => 'NSW', 'active' => true], // matched to BERKELEY VALE
            ['postcode' => '2081', 'suburb' => 'BEROWRA', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2082', 'suburb' => 'BEROWRA HEIGHTS', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2209', 'suburb' => 'BEVERLEY HILLS', 'state' => 'NSW', 'active' => true], // matched to BEVERLY HILLS
            ['postcode' => '2217', 'suburb' => 'BEVERLEY PARK', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2207', 'suburb' => 'BEXLEY', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2207', 'suburb' => 'BEXLEY NORTH', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2770', 'suburb' => 'BIDWILL', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2107', 'suburb' => 'BILGOLA PLATEAU', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2041', 'suburb' => 'BIRCHGROVE', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2143', 'suburb' => 'BIRRONG', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2770', 'suburb' => 'BLACKETT', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2785', 'suburb' => 'BLACKHEATH', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2148', 'suburb' => 'BLACKTOWN', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2560', 'suburb' => 'BLAIR ATHOL', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2559', 'suburb' => 'BLAIRMOUNT', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2221', 'suburb' => 'BLAKEHURST', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2774', 'suburb' => 'BLAXLAND', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2774', 'suburb' => 'BLAXLAND EAST', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2756', 'suburb' => 'BLIGH PARK', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2026', 'suburb' => 'BONDI', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2026', 'suburb' => 'BONDI BEACH', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2022', 'suburb' => 'BONDI JUNCTION', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2177', 'suburb' => 'BONNYRIGG', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2177', 'suburb' => 'BONNYRIGG HEIGHTS', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2111', 'suburb' => 'BORONIA PARK', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2176', 'suburb' => 'BOSSLEY PARK', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2019', 'suburb' => 'BOTANY', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2566', 'suburb' => 'BOW BOWING', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2753', 'suburb' => 'BOWEN MOUNTAIN', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2765', 'suburb' => 'BOX HILL', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2560', 'suburb' => 'BRADBURY', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2216', 'suburb' => 'BRIGHTON LE-SANDS', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2556', 'suburb' => 'BRINGELLY', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2024', 'suburb' => 'BRONTE', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2083', 'suburb' => 'BROOKLYN', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2100', 'suburb' => 'BROOKVALE', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2784', 'suburb' => 'BULLABURRA', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2230', 'suburb' => 'BURRANER', 'state' => 'NSW', 'active' => true], // matched to BURRANEER
            ['postcode' => '2134', 'suburb' => 'BURWOOD', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2136', 'suburb' => 'BURWOOD HEIGHTS', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2137', 'suburb' => 'CABARITA', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2166', 'suburb' => 'CABRAMATTA', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2166', 'suburb' => 'CABRAMATTA WEST', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2747', 'suburb' => 'CAMBRIDGE GARDENS', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2747', 'suburb' => 'CAMBRIDGE PARK', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2570', 'suburb' => 'CAMDEN', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2570', 'suburb' => 'CAMDEN SOUTH', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2062', 'suburb' => 'CAMMERAY', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2050', 'suburb' => 'CAMPERDOWN', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2194', 'suburb' => 'CAMPSIE', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2046', 'suburb' => 'CANADA BAY', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2166', 'suburb' => 'CANLEY HEIGHTS', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2166', 'suburb' => 'CANLEY VALE', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2193', 'suburb' => 'CANTERBURY', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2107', 'suburb' => 'CAREEL BAY', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2229', 'suburb' => 'CARINGBAH', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2118', 'suburb' => 'CARLINGFORD', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2218', 'suburb' => 'CARLTON', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2221', 'suburb' => 'CARSS PARK', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2168', 'suburb' => 'CARTWRIGHT', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2069', 'suburb' => 'CASTLE COVE', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2154', 'suburb' => 'CASTLE HILL', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2068', 'suburb' => 'CASTLECRAG', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2749', 'suburb' => 'CASTLEREAGH', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2170', 'suburb' => 'CASULA', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2171', 'suburb' => 'CECIL HILLS', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2178', 'suburb' => 'CECIL PARK', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2021', 'suburb' => 'CENTENNIAL PARK', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2067', 'suburb' => 'CHATSWOOD', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2067', 'suburb' => 'CHATSWOOD WEST', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2119', 'suburb' => 'CHELTENHAM', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2126', 'suburb' => 'CHERRYBROOK', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2162', 'suburb' => 'CHESTER HILL', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2036', 'suburb' => 'CHIFLEY', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2008', 'suburb' => 'CHIPPENDALE', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2170', 'suburb' => 'CHIPPING NORTON', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2046', 'suburb' => 'CHISWICK', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2747', 'suburb' => 'CLAREMONT MEADOWS', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2107', 'suburb' => 'CLAREVILLE', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2206', 'suburb' => 'CLEMTON PARK', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2088', 'suburb' => 'CLIFTON GARDENS', 'state' => 'NSW', 'active' => true], // manual postcode; neighbourhood in Mosman area
            ['postcode' => '2093', 'suburb' => 'CLONTARF', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2031', 'suburb' => 'CLOVELLY', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2570', 'suburb' => 'COBBITTY', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2097', 'suburb' => 'COLLAROY', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2097', 'suburb' => 'COLLAROY PLATEAU', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2760', 'suburb' => 'COLYTON', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2226', 'suburb' => 'COMO', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2226', 'suburb' => 'COMO WEST', 'state' => 'NSW', 'active' => true], // matched to COMO
            ['postcode' => '2137', 'suburb' => 'CONCORD', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2138', 'suburb' => 'CONCORD WEST', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2200', 'suburb' => 'CONDEL PARK', 'state' => 'NSW', 'active' => true], // matched to CONDELL PARK
            ['postcode' => '2221', 'suburb' => 'CONNELLS POINT', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2145', 'suburb' => 'CONSTITUTION HILL', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2034', 'suburb' => 'COOGEE', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2571', 'suburb' => 'COURIDJAH', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2749', 'suburb' => 'CRANEBROOK', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2090', 'suburb' => 'CREMORNE', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2090', 'suburb' => 'CREMORNE POINT', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2099', 'suburb' => 'CROMER', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2230', 'suburb' => 'CRONULLA', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2065', 'suburb' => 'CROWS NEST', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2132', 'suburb' => 'CROYDON', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2133', 'suburb' => 'CROYDON PARK', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2096', 'suburb' => 'CURL CURL', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2567', 'suburb' => 'CURRANS HILL', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2032', 'suburb' => 'DACEYVILLE', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2027', 'suburb' => 'DARLING POINT', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2010', 'suburb' => 'DARLINGHURST', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2085', 'suburb' => 'DAVIDSON', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2251', 'suburb' => 'DAVISTOWN', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2761', 'suburb' => 'DEAN PARK', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2099', 'suburb' => 'DEE WHY', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2114', 'suburb' => 'DENISTONE', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2112', 'suburb' => 'DENISTONE EAST', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2114', 'suburb' => 'DENISTONE WEST', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2770', 'suburb' => 'DHARRUK', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2030', 'suburb' => 'DIAMOND BAY', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2229', 'suburb' => 'DOLANS BAY', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2767', 'suburb' => 'DOONSIDE', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2259', 'suburb' => 'DOORALONG', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2028', 'suburb' => 'DOUBLE BAY', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2030', 'suburb' => 'DOVER HEIGHTS', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2047', 'suburb' => 'DRUMMOYNE', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2084', 'suburb' => 'DUFFYS FOREST', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2203', 'suburb' => 'DULWICH HILL', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2117', 'suburb' => 'DUNDAS', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2117', 'suburb' => 'DUNDAS VALLEY', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2158', 'suburb' => 'DURAL', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2558', 'suburb' => 'EAGLEVALE', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2206', 'suburb' => 'EARLWOOD', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2250', 'suburb' => 'EAST GOSFORD', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2213', 'suburb' => 'EAST HILLS', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2071', 'suburb' => 'EAST KILLARA', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2758', 'suburb' => 'EAST KURRAJONG', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2070', 'suburb' => 'EAST LINDFIELD', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2113', 'suburb' => 'EAST RYDE', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2766', 'suburb' => 'EASTERN CREEK', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2036', 'suburb' => 'EASTGARDENS', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2018', 'suburb' => 'EASTLAKES', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2122', 'suburb' => 'EASTWOOD', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2756', 'suburb' => 'EBENEZER', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2176', 'suburb' => 'EDENSOR PARK', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2027', 'suburb' => 'EDGECLIFF', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2101', 'suburb' => 'ELANORA', 'state' => 'NSW', 'active' => true], // matched to ELANORA HEIGHTS
            ['postcode' => '2101', 'suburb' => 'ELANORA HEIGHTS', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2570', 'suburb' => 'ELDERSLIE', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2770', 'suburb' => 'EMERTON', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2750', 'suburb' => 'EMU HEIGHTS', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2750', 'suburb' => 'EMU PLAINS', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2136', 'suburb' => 'ENFIELD', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2233', 'suburb' => 'ENGADINE', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2560', 'suburb' => 'ENGLORIE PARK', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2042', 'suburb' => 'ENMORE', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2121', 'suburb' => 'EPPING', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2115', 'suburb' => 'ERMINGTON', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2759', 'suburb' => 'ERSKINE PARK', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2043', 'suburb' => 'ERSKINEVILLE', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2558', 'suburb' => 'ESCHOL PARK', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2558', 'suburb' => 'ESCOL PARK', 'state' => 'NSW', 'active' => true], // matched to ESCHOL PARK
            ['postcode' => '2257', 'suburb' => 'ETTALONG', 'state' => 'NSW', 'active' => true], // matched to ETTALONG BEACH
            ['postcode' => '2165', 'suburb' => 'FAIRFIELD', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2165', 'suburb' => 'FAIRFIELD WEST', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2094', 'suburb' => 'FAIRLIGHT', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2776', 'suburb' => 'FAULCONBRIDGE', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2046', 'suburb' => 'FIVE DOCK', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2037', 'suburb' => 'FOREST LODGE', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2087', 'suburb' => 'FORESTVILLE', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2260', 'suburb' => 'FORRESTERS BEACH', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2756', 'suburb' => 'FREEMANS REACH', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2086', 'suburb' => 'FRENCHS FOREST', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2096', 'suburb' => 'FRESHWATER', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2159', 'suburb' => 'GALSTON', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2198', 'suburb' => 'GEORGES HALL', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2145', 'suburb' => 'GIRRAWEEN', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2111', 'suburb' => 'GLADESVILLE', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2037', 'suburb' => 'GLEBE', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2560', 'suburb' => 'GLEN ALPINE', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2773', 'suburb' => 'GLENBROOK', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2761', 'suburb' => 'GLENDENNING', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2167', 'suburb' => 'GLENFIELD', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2156', 'suburb' => 'GLENHAVEN', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2745', 'suburb' => 'GLENMORE PARK', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2261', 'suburb' => 'GLENNING VALLEY', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2157', 'suburb' => 'GLENORIE', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2768', 'suburb' => 'GLENWOOD', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2756', 'suburb' => 'GLOSSODIA', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2072', 'suburb' => 'GORDON', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2142', 'suburb' => 'GRANVILLE', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2232', 'suburb' => 'GRAYS POINT', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2251', 'suburb' => 'GREEN POINT', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2168', 'suburb' => 'GREEN VALLEY', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2190', 'suburb' => 'GREENACRE', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2176', 'suburb' => 'GREENFIELD PARK', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2065', 'suburb' => 'GREENWICH', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2145', 'suburb' => 'GREYSTANES', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2753', 'suburb' => 'GROSE VALE', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2753', 'suburb' => 'GROSE WOLD', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2161', 'suburb' => 'GUILDFORD', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2161', 'suburb' => 'GUILDFORD WEST', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2227', 'suburb' => 'GYMEA BAY', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2045', 'suburb' => 'HABERFIELD', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2170', 'suburb' => 'HAMMONDVILLE', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2096', 'suburb' => 'HARBORD', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2567', 'suburb' => 'HARRINGTON PARK', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2761', 'suburb' => 'HASSALL GROVE', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2777', 'suburb' => 'HAWKESBURY HEIGHTS', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2779', 'suburb' => 'HAZELBROOK', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2233', 'suburb' => 'HEATHCOTE', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2770', 'suburb' => 'HEBERSHAM', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2168', 'suburb' => 'HECKENBERG', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2036', 'suburb' => 'HILLSDALE', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2430', 'suburb' => 'HILLVILLE', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2168', 'suburb' => 'HINCHINBROOK', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2753', 'suburb' => 'HOBARTVILLE', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2173', 'suburb' => 'HOLSWORTHY', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2140', 'suburb' => 'HOMEBUSH', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2171', 'suburb' => 'HORNINGSEA PARK', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2077', 'suburb' => 'HORNSBY', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2077', 'suburb' => 'HORNSBY HEIGHTS', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2256', 'suburb' => 'HORSFIELD BAY', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2171', 'suburb' => 'HOXTON PARK', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2110', 'suburb' => 'HUNTERS HILL', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2193', 'suburb' => 'HURLSTONE PARK', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2220', 'suburb' => 'HURSTVILLE', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2220', 'suburb' => 'HURSTVILLE GROVE', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2234', 'suburb' => 'ILLAWONG', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2565', 'suburb' => 'INGLEBURN', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2750', 'suburb' => 'JAMISONTOWN', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2226', 'suburb' => 'JANNALI', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2259', 'suburb' => 'JILLIBY', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2747', 'suburb' => 'JORDAN SPRINGS', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2232', 'suburb' => 'KAREELA', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2250', 'suburb' => 'KARIONG', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2780', 'suburb' => 'KATOOMBA', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2558', 'suburb' => 'KEARNS', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2155', 'suburb' => 'KELLYVILLE', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2155', 'suburb' => 'KELLYVILLE RIDGE', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2033', 'suburb' => 'KENSINGTON', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2156', 'suburb' => 'KENTHURST', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2071', 'suburb' => 'KILLARA', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2087', 'suburb' => 'KILLARNEY HEIGHTS', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2261', 'suburb' => 'KILLARNEY VALE', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2257', 'suburb' => 'KILLCARE HEIGHTS', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2251', 'suburb' => 'KINCUMBER', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2147', 'suburb' => 'KINGS LANGLEY', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2148', 'suburb' => 'KINGS PARK', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2032', 'suburb' => 'KINGSFORD', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2208', 'suburb' => 'KINGSGROVE', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2747', 'suburb' => 'KINGSWOOD', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2232', 'suburb' => 'KIRRAWEE', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2217', 'suburb' => 'KOGARAH', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2217', 'suburb' => 'KOGARAH BAY', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2757', 'suburb' => 'KURMOND', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2231', 'suburb' => 'KURNELL', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2758', 'suburb' => 'KURRAJONG', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2216', 'suburb' => 'KYEEMAGH', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2195', 'suburb' => 'LAKEMBA', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2147', 'suburb' => 'LALOR PARK', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2066', 'suburb' => 'LANE COVE', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2066', 'suburb' => 'LANE COVE EAST', 'state' => 'NSW', 'active' => true], // matched to LANE COVE
            ['postcode' => '2066', 'suburb' => 'LANE COVE NORTH', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2066', 'suburb' => 'LANE COVE WEST', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2166', 'suburb' => 'LANSVALE', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2773', 'suburb' => 'LAPSTONE', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2783', 'suburb' => 'LAWSON', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2040', 'suburb' => 'LEICHHARDT', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2750', 'suburb' => 'LEONAY', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2179', 'suburb' => 'LEPPINGTON', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2770', 'suburb' => 'LETHBRIDGE PARK', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2560', 'suburb' => 'LEUMEAH', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2780', 'suburb' => 'LEURA', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2049', 'suburb' => 'LEWISHAM', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2141', 'suburb' => 'LIDCOMBE', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2040', 'suburb' => 'LILYFIELD', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2778', 'suburb' => 'LINDEN', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2070', 'suburb' => 'LINDFIELD', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2066', 'suburb' => 'LINLEY POINT', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2250', 'suburb' => 'LISAROW', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2036', 'suburb' => 'LITTLE BAY', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2170', 'suburb' => 'LIVERPOOL', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2747', 'suburb' => 'LLANDILO', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2232', 'suburb' => 'LOFTUS', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2753', 'suburb' => 'LONDONDERRY', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2066', 'suburb' => 'LONGUEVILLE', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2745', 'suburb' => 'LUDDENHAM', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2210', 'suburb' => 'LUGARNO', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2170', 'suburb' => 'LURNEA', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2564', 'suburb' => 'MACFIELDS', 'state' => 'NSW', 'active' => true], // matched to MACQUARIE FIELDS
            ['postcode' => '2564', 'suburb' => 'MACQUARIE FIELDS', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2565', 'suburb' => 'MACQUARIE LINKS', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2036', 'suburb' => 'MALABAR', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2095', 'suburb' => 'MANLY', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2093', 'suburb' => 'MANLY VALE', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2765', 'suburb' => 'MARAYLYA', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2148', 'suburb' => 'MARAYONG', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2035', 'suburb' => 'MAROUBRA', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2035', 'suburb' => 'MAROUBRA JUNCTION', 'state' => 'NSW', 'active' => true], // matched to MAROUBRA
            ['postcode' => '2204', 'suburb' => 'MARRICKVILLE', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2765', 'suburb' => 'MARSDEN PARK', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2122', 'suburb' => 'MARSFIELD', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2020', 'suburb' => 'MASCOT', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2250', 'suburb' => 'MATCHAM', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2036', 'suburb' => 'MATRAVILLE', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2756', 'suburb' => 'MCGRATHS HILL', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2060', 'suburb' => 'MCMAHONS POINT', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2114', 'suburb' => 'MEADOWBANK', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2114', 'suburb' => 'MELROSE PARK', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2234', 'suburb' => 'MENAI', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2160', 'suburb' => 'MERRYLANDS', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2068', 'suburb' => 'MIDDLE COVE', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2158', 'suburb' => 'MIDDLE DURAL', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2168', 'suburb' => 'MILLER', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2214', 'suburb' => 'MILPERRA', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2770', 'suburb' => 'MINCHINBURY', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2566', 'suburb' => 'MINTO', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2228', 'suburb' => 'MIRANDA', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2575', 'suburb' => 'MITTAGONG', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2103', 'suburb' => 'MONA VALE', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2217', 'suburb' => 'MONTEREY', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2083', 'suburb' => 'MOONEY MOONEY', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2170', 'suburb' => 'MOOREBANK', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2223', 'suburb' => 'MORTDALE', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2137', 'suburb' => 'MORTLAKE', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2088', 'suburb' => 'MOSMAN', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2567', 'suburb' => 'MOUNT ANNAN', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2079', 'suburb' => 'MOUNT COLAH', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2770', 'suburb' => 'MOUNT DRUITT', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2080', 'suburb' => 'MOUNT KU RING GAI', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2170', 'suburb' => 'MOUNT PRITCHARD', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2774', 'suburb' => 'MOUNT RIVERVIEW', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2567', 'suburb' => 'MT ANNAN', 'state' => 'NSW', 'active' => true], // matched to MOUNT ANNAN
            ['postcode' => '2079', 'suburb' => 'MT COLAH', 'state' => 'NSW', 'active' => true], // matched to MOUNT COLAH
            ['postcode' => '2770', 'suburb' => 'MT DRUITT', 'state' => 'NSW', 'active' => true], // matched to MOUNT DRUITT
            ['postcode' => '2080', 'suburb' => 'MT KU-RING-GAI', 'state' => 'NSW', 'active' => true], // matched to MOUNT KURING-GAI
            ['postcode' => '2200', 'suburb' => 'MT LEWIS', 'state' => 'NSW', 'active' => true], // matched to MOUNT LEWIS
            ['postcode' => '2170', 'suburb' => 'MT PRICHARD', 'state' => 'NSW', 'active' => true], // matched to MOUNT PRITCHARD
            ['postcode' => '2170', 'suburb' => 'MT PRITCHARD', 'state' => 'NSW', 'active' => true], // matched to MOUNT PRITCHARD
            ['postcode' => '2774', 'suburb' => 'MT RIVERVIEW', 'state' => 'NSW', 'active' => true], // matched to MOUNT RIVERVIEW
            ['postcode' => '2745', 'suburb' => 'MULGOA', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2250', 'suburb' => 'NARARA', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2567', 'suburb' => 'NARELLAN', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2567', 'suburb' => 'NARELLAN VALE', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2065', 'suburb' => 'NAREMBURN', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2101', 'suburb' => 'NARRABEEN', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2099', 'suburb' => 'NARRAWEENA', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2209', 'suburb' => 'NARWEE', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2089', 'suburb' => 'NEUTRAL BAY', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2127', 'suburb' => 'NEWINGTON', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2106', 'suburb' => 'NEWPORT', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2042', 'suburb' => 'NEWTOWN', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2076', 'suburb' => 'NORMANHURST', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2093', 'suburb' => 'NORTH BALGOWLAH', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2026', 'suburb' => 'NORTH BONDI', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2099', 'suburb' => 'NORTH CURL CURL', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2121', 'suburb' => 'NORTH EPPING', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2250', 'suburb' => 'NORTH GOSFORD', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2100', 'suburb' => 'NORTH MANLY', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2101', 'suburb' => 'NORTH NARRABEEN', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2151', 'suburb' => 'NORTH PARRAMATTA', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2754', 'suburb' => 'NORTH RICHMOND', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2151', 'suburb' => 'NORTH ROCKS', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2113', 'suburb' => 'NORTH RYDE', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2137', 'suburb' => 'NORTH STRATHFIELD', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2055', 'suburb' => 'NORTH SYDNEY', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2074', 'suburb' => 'NORTH TURRAMURRA', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2076', 'suburb' => 'NORTH WAHROONGA', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2068', 'suburb' => 'NORTH WILLOUGHBY', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2063', 'suburb' => 'NORTHBRIDGE', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2152', 'suburb' => 'NORTHMEAD', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2066', 'suburb' => 'NORTHWOOD', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2761', 'suburb' => 'OAKHURST', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2765', 'suburb' => 'OAKVILLE', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2117', 'suburb' => 'OATLANDS', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2223', 'suburb' => 'OATLEY', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2770', 'suburb' => 'OLD MT DRUITT', 'state' => 'NSW', 'active' => true], // matched to MOUNT DRUITT
            ['postcode' => '2146', 'suburb' => 'OLD TOONGABBIE', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2570', 'suburb' => 'ORANGEVILLE', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2258', 'suburb' => 'OURIMBAH', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2760', 'suburb' => 'OXLEY PARK', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2225', 'suburb' => 'OYSTER BAY', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2021', 'suburb' => 'PADDINGTON', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2211', 'suburb' => 'PADSTOW', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2211', 'suburb' => 'PADSTOW HEIGHTS', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2035', 'suburb' => 'PAGEWOOD', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2108', 'suburb' => 'PALM BEACH', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2213', 'suburb' => 'PANANIA', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2768', 'suburb' => 'PARKLEA', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2150', 'suburb' => 'PARRAMATTA', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2210', 'suburb' => 'PEAKHURST', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2145', 'suburb' => 'PEMULWUY', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2145', 'suburb' => 'PENDLE HILL', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2120', 'suburb' => 'PENNANT HILLS', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2750', 'suburb' => 'PENRITH', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2222', 'suburb' => 'PENSHURST', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2049', 'suburb' => 'PETERSHAM', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2036', 'suburb' => 'PHILLIP BAY', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2213', 'suburb' => 'PICNIC POINT', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2571', 'suburb' => 'PICTON', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2756', 'suburb' => 'PITT TOWN', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2761', 'suburb' => 'PLUMPTON', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2250', 'suburb' => 'POINT CLARE', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2250', 'suburb' => 'POINT FREDERICK', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2176', 'suburb' => 'PRAIRIEWOOD', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2170', 'suburb' => 'PRESTONS', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2148', 'suburb' => 'PROSPECT', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2196', 'suburb' => 'PUNCHBOWL', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2112', 'suburb' => 'PUTNEY', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2073', 'suburb' => 'PYMBLE', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2763', 'suburb' => 'QUAKERS HILL', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2022', 'suburb' => 'QUEENS PARK', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2096', 'suburb' => 'QUEENSCLIFF', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2566', 'suburb' => 'RABY', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2217', 'suburb' => 'RAMSGATE', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2217', 'suburb' => 'RAMSGATE BEACH', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2031', 'suburb' => 'RANDWICK', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2016', 'suburb' => 'REDFERN', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2143', 'suburb' => 'REGENTS PARK', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2745', 'suburb' => 'REGENTVILLE', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2212', 'suburb' => 'REVESBY', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2212', 'suburb' => 'REVESBY HEIGHTS', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2138', 'suburb' => 'RHODES', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2753', 'suburb' => 'RICHMOND', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2765', 'suburb' => 'RIVERSTONE', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2066', 'suburb' => 'RIVERVIEW', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2210', 'suburb' => 'RIVERWOOD', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2216', 'suburb' => 'ROCKDALE', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2046', 'suburb' => 'RODD POINT', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2766', 'suburb' => 'ROOTY HILL', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2760', 'suburb' => 'ROPES CROSSING', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2029', 'suburb' => 'ROSE BAY', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2030', 'suburb' => 'ROSE BAY NORTH', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2142', 'suburb' => 'ROSE HILL', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2560', 'suburb' => 'ROSE MEADOW', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2018', 'suburb' => 'ROSEBERY', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2142', 'suburb' => 'ROSEHILL', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2196', 'suburb' => 'ROSELANDS', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2560', 'suburb' => 'ROSEMEADOW', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2069', 'suburb' => 'ROSEVILLE', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2069', 'suburb' => 'ROSEVILLE CHASE', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2158', 'suburb' => 'ROUND CORNER', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2155', 'suburb' => 'ROUSE HILL', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2039', 'suburb' => 'ROZELLE', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2560', 'suburb' => 'RUSE', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2046', 'suburb' => 'RUSSELL LEA', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2116', 'suburb' => 'RYDALMERE', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2112', 'suburb' => 'RYDE', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2168', 'suburb' => 'SADLEIR', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2262', 'suburb' => 'SAN REMO', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2219', 'suburb' => 'SANDRINGHAM', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2219', 'suburb' => 'SANS SOUCI', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2251', 'suburb' => 'SARATOGA', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2762', 'suburb' => 'SCHOFIELDS', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2092', 'suburb' => 'SEAFORTH', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2162', 'suburb' => 'SEFTON', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2147', 'suburb' => 'SEVEN HILLS', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2770', 'suburb' => 'SHALVEY', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2747', 'suburb' => 'SHANE PARK', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2752', 'suburb' => 'SILVERDALE', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2128', 'suburb' => 'SILVERWATER', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2164', 'suburb' => 'SMITHFIELD', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2034', 'suburb' => 'SOUTH COOGEE', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2221', 'suburb' => 'SOUTH HURSTVILLE', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2750', 'suburb' => 'SOUTH PENRITH', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2074', 'suburb' => 'SOUTH TURRAMURRA', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2756', 'suburb' => 'SOUTH WINDSOR', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2570', 'suburb' => 'SPRING FARM', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2777', 'suburb' => 'SPRINGWOOD', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2566', 'suburb' => 'ST ANDREWS', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2759', 'suburb' => 'ST CLAIR', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2560', 'suburb' => 'ST HELENS PARK', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2257', 'suburb' => 'ST HUBERTS ISLAND', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2075', 'suburb' => 'ST IVES', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2075', 'suburb' => 'ST IVES CHASE', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2176', 'suburb' => 'ST JOHNS PARK', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2065', 'suburb' => 'ST LEONARDS', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2760', 'suburb' => 'ST MARYS', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2044', 'suburb' => 'ST PETERS', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2768', 'suburb' => 'STANHOPE GARDENS', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2048', 'suburb' => 'STANMORE', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2135', 'suburb' => 'STRATHFIELD', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2136', 'suburb' => 'STRATHFIELD SOUTH', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2130', 'suburb' => 'SUMMER HILL', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2010', 'suburb' => 'SURRY HILLS', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2044', 'suburb' => 'SYDENHAM', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2224', 'suburb' => 'SYLVANIA', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2224', 'suburb' => 'SYLVANIA WATERS', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2026', 'suburb' => 'TAMARAMA', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2250', 'suburb' => 'TASCOTT', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2117', 'suburb' => 'TELOPEA', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2044', 'suburb' => 'TEMPE', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2754', 'suburb' => 'TENNYSON', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2111', 'suburb' => 'TENNYSON POINT', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2084', 'suburb' => 'TERREY HILLS', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2260', 'suburb' => 'TERRIGAL', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2570', 'suburb' => 'THE OAKS', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2769', 'suburb' => 'THE PONDS', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2570', 'suburb' => 'THERESA PARK', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2120', 'suburb' => 'THORNLEIGH', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2146', 'suburb' => 'TOONGABBIE', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2263', 'suburb' => 'TOUKLEY', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2770', 'suburb' => 'TREGEAR', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2261', 'suburb' => 'TUMBI UMBI', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2205', 'suburb' => 'TURELLA', 'state' => 'NSW', 'active' => true], // matched to TURRELLA
            ['postcode' => '2074', 'suburb' => 'TURRAMURRA', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2250', 'suburb' => 'TUSCOTT', 'state' => 'NSW', 'active' => true], // matched to TASCOTT
            ['postcode' => '2257', 'suburb' => 'UMINA', 'state' => 'NSW', 'active' => true], // matched to UMINA BEACH
            ['postcode' => '2257', 'suburb' => 'UMINA BEACH', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2777', 'suburb' => 'VALLEY HEIGHTS', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2030', 'suburb' => 'VAUCLUSE', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2163', 'suburb' => 'VILLAWOOD', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2765', 'suburb' => 'VINEYARD', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2076', 'suburb' => 'WAHROONGA', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2077', 'suburb' => 'WAITARA', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2176', 'suburb' => 'WAKELEY', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2745', 'suburb' => 'WALLACIA', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2260', 'suburb' => 'WAMBERAL', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2046', 'suburb' => 'WAREEMBA', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2752', 'suburb' => 'WARRAGAMBA', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2074', 'suburb' => 'WARRAWEE', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2102', 'suburb' => 'WARRIEWOOD', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2774', 'suburb' => 'WARRIMOO', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2102', 'suburb' => 'WARRIWOOD', 'state' => 'NSW', 'active' => true], // matched to WARRIEWOOD
            ['postcode' => '2170', 'suburb' => 'WARWICK FARM', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2017', 'suburb' => 'WATERLOO', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2030', 'suburb' => 'WATSONS BAY', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2173', 'suburb' => 'WATTLE GROVE', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2024', 'suburb' => 'WAVERLEY', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2060', 'suburb' => 'WAVERTON', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2560', 'suburb' => 'WEDDERBURN', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2782', 'suburb' => 'WENTWORTH FALLS', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2145', 'suburb' => 'WENTWORTHVILLE', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2570', 'suburb' => 'WEROMBI', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2747', 'suburb' => 'WERRINGTON', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2747', 'suburb' => 'WERRINGTON COUNTY', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2747', 'suburb' => 'WERRINGTON DOWNS', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2250', 'suburb' => 'WEST GOSFORD', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2171', 'suburb' => 'WEST HOXTON', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2070', 'suburb' => 'WEST LINDFIELD', 'state' => 'NSW', 'active' => true], // matched to LINDFIELD WEST
            ['postcode' => '2125', 'suburb' => 'WEST PENNANT HILLS', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2073', 'suburb' => 'WEST PYMBLE', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2114', 'suburb' => 'WEST RYDE', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2120', 'suburb' => 'WESTLEIGH', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2145', 'suburb' => 'WESTMEAD', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2164', 'suburb' => 'WETHERILL PARK', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2770', 'suburb' => 'WHALAN', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2107', 'suburb' => 'WHALE BEACH', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2097', 'suburb' => 'WHEELER HEIGHTS', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2756', 'suburb' => 'WILBERFORCE', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2195', 'suburb' => 'WILEY PARK', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2770', 'suburb' => 'WILLMOT', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2068', 'suburb' => 'WILLOUGHBY', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2068', 'suburb' => 'WILLOUGHBY EAST', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2770', 'suburb' => 'WILMOTT', 'state' => 'NSW', 'active' => true], // matched to WILLMOT
            ['postcode' => '2756', 'suburb' => 'WINDSOR', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2756', 'suburb' => 'WINDSOR DOWNS', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2777', 'suburb' => 'WINMALEE', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2153', 'suburb' => 'WINSTON HILLS', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2065', 'suburb' => 'WOLLSTONECRAFT', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2515', 'suburb' => 'WOMBARRA', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2260', 'suburb' => 'WOMBERAL', 'state' => 'NSW', 'active' => true], // matched to WAMBERAL
            ['postcode' => '2560', 'suburb' => 'WOODBINE', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2767', 'suburb' => 'WOODCROFT', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2778', 'suburb' => 'WOODFORD', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2164', 'suburb' => 'WOODPARK', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2025', 'suburb' => 'WOOLLAHRA', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2230', 'suburb' => 'WOOLOOWARE', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2110', 'suburb' => 'WOOLWICH', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2232', 'suburb' => 'WORONORA', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2233', 'suburb' => 'WORONORA HEIGHTS', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2256', 'suburb' => 'WOY WOY', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2250', 'suburb' => 'WYOMING', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2199', 'suburb' => 'YAGOONA', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2233', 'suburb' => 'YARRAWARRAH', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2161', 'suburb' => 'YENNORA', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2228', 'suburb' => 'YOWIE BAY', 'state' => 'NSW', 'active' => true],
            ['postcode' => '2017', 'suburb' => 'ZETLAND', 'state' => 'NSW', 'active' => true],
        ];

        \Illuminate\Support\Facades\DB::transaction(function () use ($rows, $removedSuburbs) {
            foreach ($removedSuburbs as $suburb) {
                DesignerPostcode::whereRaw('UPPER(suburb) = ?', [$suburb])
                    ->update(['active' => false]);
            }

            foreach ($rows as $row) {
                DesignerPostcode::updateOrCreate(
                    [
                        'suburb' => $row['suburb'],
                        'state' => $row['state'],
                    ],
                    [
                        'postcode' => $row['postcode'],
                        'active' => $row['active'],
                    ]
                );
            }
        });
    }
}
