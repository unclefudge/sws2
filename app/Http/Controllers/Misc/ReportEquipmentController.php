<?php

namespace App\Http\Controllers\Misc;

use DB;
use PDF;
use File;
use Session;
use App\User;
use App\Models\Site\Site;
use App\Models\Site\SiteQa;
use App\Models\Site\SiteQaItem;
use App\Models\Site\SiteMaintenance;
use App\Models\Site\Planner\SitePlanner;
use App\Models\Site\Planner\SiteAttendance;
use App\Models\Site\SiteInspectionElectrical;
use App\Models\Site\SiteInspectionPlumbing;
use App\Models\Company\Company;
use App\Models\Misc\Equipment\Equipment;
use App\Models\Misc\Equipment\EquipmentLocation;
use App\Models\Misc\Equipment\EquipmentStocktake;
use App\Models\Misc\Equipment\EquipmentStocktakeItem;
use App\Models\Misc\Equipment\EquipmentLog;
use App\Models\Misc\Action;
use App\Models\Comms\Todo;
use App\Models\Comms\TodoUser;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\Datatables\Datatables;
use Carbon\Carbon;

class ReportEquipmentController extends Controller {

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /*
     * Equipment List Report
     */
    public function equipment()
    {
        $equipment = Equipment::where('status', 1)->orderBy('name')->get();

        return view('manage/report/equipment/equipment', compact('equipment'));
    }

    /**
     * Equipment List PDF
     */
    public function equipmentPDF()
    {
        $equipment = Equipment::where('status', 1)->orderBy('name')->get();

        $dir = '/filebank/tmp/report/' . Auth::user()->company_id;
        // Create directory if required
        if (!is_dir(public_path($dir)))
            mkdir(public_path($dir), 0777, true);
        $output_file = public_path($dir . "/Equipment List " . Carbon::now()->format('YmdHis') . '.pdf');
        touch($output_file);

        //return view('pdf/equipment', compact('equipment'));
        //return PDF::loadView('pdf/equipment', compact('equipment'))->setPaper('a4', 'portrait')->stream();
        \App\Jobs\EquipmentPdf::dispatch($equipment, $output_file);

        return redirect('/manage/report/recent');
    }

    /*
     * Equipment List Report
     */
    public function equipmentSite()
    {
        // Store + Other Sites
        $locations = [1 => 'other'];
        $locations_other = EquipmentLocation::where('site_id', null)->where('status', 1)->orderBy('other')->pluck('id')->toArray();
        foreach ($locations_other as $loc)
            $locations[$loc] = 'other';

        // Locations without supervisors
        $sites_without_super = [];
        $sites_without_super[] = Site::where('status', 1)->where('company_id', 3)->where('supervisor_id', null)->pluck('id')->toArray();
        //foreach ($active_sites as $site) {
        //    if (!$site->supervisor_id)
        //        $sites_without_super[] = $site->id;
        //}
        $locations_nosuper = EquipmentLocation::whereIn('site_id', $sites_without_super)->where('status', 1)->pluck('id')->toArray();
        foreach ($locations_nosuper as $loc)
            $locations[$loc] = 'no-super';

        // Locations with super
        $locations_super = [];
        $supervisors = Company::find(3)->supervisors()->sortBy('lastname');
        foreach ($supervisors as $super) {
            $sites = $super->supervisorsSites()->sortBy('code')->pluck('id')->toArray();
            foreach ($sites as $site) {
                $location = EquipmentLocation::where('site_id', $site)->where('status', 1)->where('site_id', '<>', 25)->first();
                if ($location)
                    $locations_super[$location->id] = $super->name;
            }
        }

        asort($locations_super);
        $locations = $locations + $locations_super;
        //dd($locations);

        return view('manage/report/equipment/equipment-site', compact('locations'));
    }

    /**
     * Equipment List PDF
     */
    public function equipmentSitePDF()
    {
        // Store + Other Sites
        $locations = [1 => 'other'];
        $locations_other = EquipmentLocation::where('site_id', null)->where('status', 1)->orderBy('other')->pluck('id')->toArray();
        foreach ($locations_other as $loc)
            $locations[$loc] = 'other';

        // Locations without supervisors
        $sites_without_super = [];
        $active_sites = Site::where('status', 1)->where('company_id', 3)->get();
        foreach ($active_sites as $site) {
            if (!$site->supervisorName)
                $sites_without_super[] = $site->id;
        }
        $locations_nosuper = EquipmentLocation::whereIn('site_id', $sites_without_super)->where('status', 1)->pluck('id')->toArray();
        foreach ($locations_nosuper as $loc)
            $locations[$loc] = 'no-super';

        // Locations with super
        $supervisors = Company::find(3)->supervisors()->sortBy('lastname');
        foreach ($supervisors as $super) {
            $sites = $super->supervisorsSites()->sortBy('code')->pluck('id')->toArray();
            foreach ($sites as $site) {
                $location = EquipmentLocation::where('site_id', $site)->where('status', 1)->where('site_id', '<>', 25)->first();
                if ($location)
                    $locations[$location->id] = $super->name;
            }
        }

        $dir = '/filebank/tmp/report/' . Auth::user()->company_id;
        // Create directory if required
        if (!is_dir(public_path($dir)))
            mkdir(public_path($dir), 0777, true);
        $output_file = public_path($dir . "/Equipment List By Site " . Carbon::now()->format('YmdHis') . '.pdf');
        touch($output_file);

        //return view('pdf/equipment-site', compact('locations'));
        //return PDF::loadView('pdf/equipment-site', compact('locations'))->setPaper('a4', 'portrait')->stream();
        \App\Jobs\EquipmentSitePdf::dispatch($locations, $output_file);

        return redirect('/manage/report/recent');
    }

    /*
     * Equipment Transaction Report
     */
    public function equipmentTransactions()
    {
        $equipment = Equipment::where('status', 1)->orderBy('name')->get();

        return view('manage/report/equipment/equipment-transactions', compact('equipment'));
    }

    /**
     * Equipment Transaction PDF
     */
    public function equipmentTransactionsPDF()
    {

        $date_from = (request('from')) ? Carbon::createFromFormat('d/m/Y H:i:s', request('from') . ' 00:00:00')->format('Y-m-d') : '2000-01-01';
        $date_to = (request('to')) ? Carbon::createFromFormat('d/m/Y H:i:s', request('to') . ' 00:00:00')->format('Y-m-d') : Carbon::tomorrow()->format('Y-m-d');
        $transactions = EquipmentLog::whereDate('equipment_log.created_at', '>=', $date_from)->whereDate('equipment_log.created_at', '<=', $date_to)->get();

        //dd($date_from);
        $dir = '/filebank/tmp/report/' . Auth::user()->company_id;
        // Create directory if required
        if (!is_dir(public_path($dir)))
            mkdir(public_path($dir), 0777, true);
        $output_file = public_path($dir . "/Equipment Transactions " . Carbon::now()->format('YmdHis') . '.pdf');
        touch($output_file);

        $from = (request('from')) ? Carbon::createFromFormat('d/m/Y H:i:s', request('from') . ' 00:00:00') : Carbon::createFromFormat('Y-m-d H:i:s', '2000-01-01 00:00:00');
        $to = (request('to')) ? Carbon::createFromFormat('d/m/Y H:i:s', request('to') . ' 00:00:00') : Carbon::tomorrow();

        //return view('pdf/equipment-transactions', compact('transactions', 'from', 'to'));
        //return PDF::loadView('pdf/equipment-transactions', compact('transactions', 'from', 'to'))->setPaper('a4', 'portrait')->stream();
        \App\Jobs\EquipmentTransactionsPdf::dispatch($transactions, $from, $to, $output_file);

        return redirect('/manage/report/recent');
    }

    /**
     * Get Equipment Tansactions
     *
     * @return \Illuminate\Http\JsonResponse
     */

    public function getEquipmentTransactions()
    {
        $date_from = (request('from')) ? Carbon::createFromFormat('d/m/Y H:i:s', request('from') . ' 00:00:00')->format('Y-m-d') : '2000-01-01';
        $date_to = (request('to')) ? Carbon::createFromFormat('d/m/Y H:i:s', request('to') . ' 00:00:00')->format('Y-m-d') : Carbon::tomorrow()->format('Y-m-d');
        $actions = ['P', 'D', 'W'];

        $transactions = EquipmentLog::select([
            'equipment_log.id', 'equipment_log.equipment_id', 'equipment_log.qty', 'equipment_log.action', 'equipment_log.notes', 'equipment_log.created_at',
            'equipment.id', 'equipment.name', 'users.id', 'users.username', 'users.firstname', 'users.lastname',
            DB::raw('CONCAT(users.firstname, " ", users.lastname) AS full_name')
        ])
            ->join('equipment', 'equipment.id', '=', 'equipment_log.equipment_id')
            ->join('users', 'users.id', '=', 'equipment_log.created_by')
            ->whereDate('equipment_log.created_at', '>=', $date_from)
            ->whereDate('equipment_log.created_at', '<=', $date_to)
            ->whereIn('equipment_log.action', $actions);


        //dd($transactions);
        $dt = Datatables::of($transactions)
            ->editColumn('created_at', function ($trans) {
                return $trans->created_at->format('d/m/Y');
            })
            ->editColumn('action', function ($trans) {
                if ($trans->action == 'P') return 'Purchase';
                if ($trans->action == 'D') return 'Disposal';
                if ($trans->action == 'W') return 'Write Off';
                if ($trans->action == 'N') return 'New Item';

                return $trans->action;
            })
            ->rawColumns(['full_name', 'created_at'])
            ->make(true);

        return $dt;
    }

    /**
     * Equipment Transfers Report
     */
    public function equipmentTransfers()
    {
        $to = Carbon::now();
        $from = Carbon::now()->subDays(14);
        $transactions = EquipmentLog::where('action', 'T')->whereDate('created_at', '>=', $from->format('Y-m-d'))->whereDate('created_at', '<=', $to->format('Y-m-d'))->get();

        //dd($transactions);

        return view('manage/report/equipment/equipment-transfers', compact('transactions', 'from', 'to'));
    }

    /**
     * Equipment Transfers Report
     */
    public function equipmentTransfersPDF()
    {
        $to = Carbon::now();
        $from = Carbon::now()->subDays(7);
        $transactions = EquipmentLog::where('action', 'T')->whereDate('created_at', '>=', $from->format('Y-m-d'))->whereDate('created_at', '<=', $to->format('Y-m-d'))->get();

        return PDF::loadView('pdf/equipment-transfers', compact('transactions', 'from', 'to'))->setPaper('a4', 'portrait')->stream();
    }

    /**
     * Get Equipment Tansactions
     *
     * @return \Illuminate\Http\JsonResponse
     */

    public function getEquipmentTransfers()
    {
        $date_from = (request('from')) ? Carbon::createFromFormat('d/m/Y H:i:s', request('from') . ' 00:00:00')->format('Y-m-d') : Carbon::now()->subDays(7)->format('Y-m-d');
        $date_to = (request('to')) ? Carbon::createFromFormat('d/m/Y H:i:s', request('to') . ' 00:00:00')->format('Y-m-d') : Carbon::now()->format('Y-m-d');

        $transactions = EquipmentLog::select([
            'equipment_log.id', 'equipment_log.equipment_id', 'equipment_log.qty', 'equipment_log.action', 'equipment_log.notes', 'equipment_log.created_at',
            'equipment.id', 'equipment.name', 'users.id', 'users.username', 'users.firstname', 'users.lastname',
            DB::raw('CONCAT(users.firstname, " ", users.lastname) AS full_name')
        ])
            ->join('equipment', 'equipment.id', '=', 'equipment_log.equipment_id')
            ->join('users', 'users.id', '=', 'equipment_log.created_by')
            ->whereDate('equipment_log.created_at', '>=', $date_from)
            ->whereDate('equipment_log.created_at', '<=', $date_to)
            ->where('equipment_log.action', 'T');


        //dd($transactions);
        $dt = Datatables::of($transactions)
            ->addColumn('trans_from', function ($trans) {
                list($from_part, $trans_to) = explode(' => ', $trans->notes);
                list($crap, $trans_from) = explode('items from ', $from_part);
                if (preg_match('/\(/', $trans_from))
                    list($b1, $trans_from) = explode('(', rtrim($trans_from, ')'));
                return $trans_from;
            })
            ->addColumn('trans_to', function ($trans) {
                list($from_part, $trans_to) = explode(' => ', $trans->notes);
                list($crap, $trans_from) = explode('items from ', $from_part);
                if (preg_match('/\(/', $trans_from))
                    list($b1, $trans_from) = explode('(', rtrim($trans_from, ')'));
                if (preg_match('/\(/', $trans_to))
                    list($b1, $trans_to) = explode('(', rtrim($trans_to, ')'));
                return $trans_to;
            })
            ->editColumn('created_at', function ($trans) {
                return $trans->created_at->format('d/m/Y');
            })
            ->rawColumns(['full_name', 'created_at'])
            ->make(true);

        return $dt;
    }


    /*
    * Equipment Stocktake Report
    */
    public function equipmentStocktake()
    {
        //$equipment = EquipmentStocktake::all()->orderBy('name')->get();
        $equipment = '';

        return view('manage/report/equipment/equipment-stocktake', compact('equipment'));
    }

    /**
     * Get Equipment Stocktake
     *
     * @return \Illuminate\Http\JsonResponse
     */

    public function getEquipmentStocktake()
    {
        $date_from = (request('from')) ? Carbon::createFromFormat('d/m/Y H:i:s', request('from') . ' 00:00:00')->format('Y-m-d') : '2000-01-01';
        $date_to = (request('to')) ? Carbon::createFromFormat('d/m/Y H:i:s', request('to') . ' 00:00:00')->format('Y-m-d') : Carbon::tomorrow()->format('Y-m-d');

        $stocktake = EquipmentStocktake::select([
            'equipment_stocktake.id', 'equipment_stocktake.location_id', 'equipment_stocktake.created_at', 'equipment_location.id AS loc_id', 'equipment_location.site_id', 'equipment_location.other',
            'users.id AS uid', 'users.username', 'users.firstname', 'users.lastname',
            DB::raw('CONCAT(users.firstname, " ", users.lastname) AS full_name')
        ])
            ->join('equipment_location', 'equipment_stocktake.location_id', '=', 'equipment_location.id')
            ->join('users', 'users.id', '=', 'equipment_stocktake.created_by')
            ->whereDate('equipment_stocktake.created_at', '>=', $date_from)
            ->whereDate('equipment_stocktake.created_at', '<=', $date_to);

        //dd($stocktake);
        $dt = Datatables::of($stocktake)
            ->editColumn('created_at', function ($stock) {
                return "<a href='/equipment/stocktake/view/$stock->id' target=_blank >" . $stock->created_at->format('d/m/Y') . "</a>";
            })
            ->addColumn('location', function ($stock) {
                return $stock->name;
            })
            ->addColumn('summary', function ($stock) {
                return $stock->summary();
            })
            ->rawColumns(['full_name', 'created_at', 'summary'])
            ->make(true);

        return $dt;
    }

    /**
     * Get Equipment Stocktake Not
     *
     * @return \Illuminate\Http\JsonResponse
     */

    public function getEquipmentStocktakeNot()
    {
        $date_from = (request('from')) ? Carbon::createFromFormat('d/m/Y H:i:s', request('from') . ' 00:00:00')->format('Y-m-d') : '2000-01-01';
        $date_to = (request('to')) ? Carbon::createFromFormat('d/m/Y H:i:s', request('to') . ' 00:00:00')->format('Y-m-d') : Carbon::tomorrow()->format('Y-m-d');

        $stocktake = EquipmentStocktake::whereDate('equipment_stocktake.created_at', '>=', $date_from)->whereDate('equipment_stocktake.created_at', '<=', $date_to)
            ->pluck('location_id')->toArray();
        $locations = EquipmentLocation::where('status', 1)->whereNotIn('id', $stocktake)->get();

        $location_names = [];
        foreach ($locations as $loc)
            $location_names[$loc->id] = $loc->name5;

        asort($location_names);
        //dd($location_names);

        $objects = [];
        foreach ($location_names as $id => $name)
            $objects[] = (object) array('id' => $id, 'name' => $name);

        //dd($objects);
        //dd($transactions);
        $dt = Datatables::of($objects)
            ->editColumn('id', '<div class="text-center"><a href="/equipment/stocktake/{{$id}}"><i class="fa fa-search"></i></a></div>')
            ->editColumn('name', function ($location) {
                return $location->name;
            })
            ->rawColumns(['id', 'name'])
            ->make(true);

        return $dt;
    }

    /*
   * Equipment Re-Stockt Report
   */
    public function equipmentRestock()
    {
        $equipment = Equipment::where('min_stock', '!=', null)->orderBy('name')->get();

        return view('manage/report/equipment/equipment-restock', compact('equipment'));
    }
}
