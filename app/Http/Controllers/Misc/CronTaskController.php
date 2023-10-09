<?php

namespace App\Http\Controllers\Misc;

use Illuminate\Http\Request;

use DB;
use PDF;
use Mail;
use File;
use Carbon\Carbon;
use App\User;
use App\Models\Company\Company;
use App\Models\Company\CompanyDoc;
use App\Models\Company\CompanyDocCategory;
use App\Models\Company\CompanyDocReview;
use App\Models\Site\Planner\Trade;
use App\Models\Site\Planner\Task;
use App\Models\Site\Site;
use App\Models\Site\SiteMaintenance;
use App\Models\Site\SiteExtension;
use App\Models\Site\SiteExtensionSite;
use App\Models\Site\Planner\SiteAttendance;
use App\Models\Site\Planner\SiteCompliance;
use App\Models\Site\Planner\SitePlanner;
use App\Models\Site\Planner\SiteRoster;
use App\Models\Site\SiteQa;
use App\Models\Site\SiteQaItem;
use App\Models\Site\SiteQaAction;
use App\Models\Site\SiteScaffoldHandover;
use App\Models\Safety\ToolboxTalk;
use App\Models\Safety\WmsDoc;
use App\Models\Misc\Action;
use App\Models\Misc\Equipment\Equipment;
use App\Models\Misc\Equipment\EquipmentLocation;
use App\Models\Misc\Equipment\EquipmentStocktake;
use App\Models\Misc\Equipment\EquipmentStocktakeItem;
use App\Models\Misc\Equipment\EquipmentLog;
use App\Models\Misc\Supervisor\SuperChecklist;
use App\Models\Misc\Supervisor\SuperChecklistResponse;
use App\Models\Misc\Supervisor\SuperChecklistSettings;
use App\Models\Comms\Todo;
use App\Models\Comms\TodoUser;
use App\Models\Comms\SafetyTip;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class CronTaskController extends Controller {

    static public function hourly()
    {
        //echo "<h1> Nightly Update - " . Carbon::now()->format('d/m/Y g:i a') . "</h1>";
        //$log = "Nightly Update - " . Carbon::now()->format('d/m/Y g:i a') . "\n-------------------------------------------------------------------------\n\n";
        //$bytes_written = File::put(public_path('filebank/log/nightly/' . Carbon::now()->format('Ymd') . '.txt'), $log);
        //if ($bytes_written === false) die("Error writing to file");
        
        if (Carbon::today()->isWeekday()) {
            $hour = Carbon::now()->format('G'); // 24hr
            $minute = Carbon::now()->format('i');

            // 2pm
            if ($hour == '14') {
                $text = "=== Hourly Tasks @ 2pm " . Carbon::now()->format('d/m/Y G:i') . " ===\n";
                app('log')->debug($text);
                CronTaskController::superChecklistsReminder();
            }
        }


        //echo "<h1>ALL DONE - NIGHTLY COMPLETE</h1>";
        //$log = "\nALL DONE - NIGHTLY COMPLETE\n\n\n";

        //$bytes_written = File::append(public_path('filebank/log/nightly/' . Carbon::now()->format('Ymd') . '.txt'), $log);
        //if ($bytes_written === false) die("Error writing to file");
    }


    /*
    * OutStandinf Supervisor Checklist @ 2pm
    */
    static public function superChecklistsReminder()
    {
        $todos = Todo::where('type', 'super checklist')->where('status', '1')->get();
        foreach ($todos as $todo) {
            $checklist = SuperChecklist::find($todo->type_id);
            if ($checklist) {

            }
        }
    }

}