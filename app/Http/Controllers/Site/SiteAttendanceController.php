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

class SiteAttendanceController extends Controller {

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Check authorisation and throw 404 if not
        if (!Auth::user()->hasAnyPermissionType('site.attendance'))
            return view('errors/404');

        return view('site/attendance/list');
    }

    /**
     * Get Site Attendance user is authorise to view
     *
     * @return \Illuminate\Http\JsonResponse
     */

    public function getAttendance()
    {
        $site_ids = (request('site_id')) ? [request('site_id')] : Auth::user()->authSites('view.site.list')->pluck('id')->toArray();
        $company_ids = Auth::user()->company->companies()->pluck('id')->toArray();
        $user_ids = Auth::user()->authUsers('view.site.attendance')->pluck('id')->toArray();
        $date_from = (request('from')) ? Carbon::createFromFormat('d/m/Y H:i:s', request('from') . ' 00:00:00')->format('Y-m-d') : '2000-01-01';
        $date_to = (request('to')) ? Carbon::createFromFormat('d/m/Y H:i:s', request('to') . ' 00:00:00')->format('Y-m-d') : Carbon::tomorrow()->format('Y-m-d');

        $attendance_records = SiteAttendance::select([
            'site_attendance.site_id', 'site_attendance.user_id', 'site_attendance.date', 'sites.name',
            'users.id', 'users.username', 'users.firstname', 'users.lastname', 'users.company_id', 'companys.id', 'companys.name',
            DB::raw('CONCAT(users.firstname, " ", users.lastname) AS full_name')
        ])
            ->join('sites', 'sites.id', '=', 'site_attendance.site_id')
            ->join('users', 'users.id', '=', 'site_attendance.user_id')
            ->join('companys', 'users.company_id', '=', 'companys.id')
            ->whereIn('site_attendance.site_id', $site_ids)
            ->whereIn('site_attendance.user_id', $user_ids)
            ->whereIn('companys.id', $company_ids)
            ->whereDate('site_attendance.date', '>=', $date_from)
            ->whereDate('site_attendance.date', '<=', $date_to);

        //dd($attendance_records);
        $dt = Datatables::of($attendance_records)
            ->editColumn('date', function ($attendance) {
                if ($attendance->date->isToday())
                    return '<span class="font-blue">'.$attendance->date->format('d/m/Y H:m a').'</span>';
                return $attendance->date->format('d/m/Y H:m a');
            })
            ->editColumn('sites.name', function ($attendance) {
                if ($attendance->date->isToday())
                    return '<span class="font-blue">'.$attendance->site->name.'</span>';
                return $attendance->site->name;
            })
            ->editColumn('full_name', function ($attendance) {
                if ($attendance->date->isToday())
                    return '<span class="font-blue">'.$attendance->user->full_name.'</span>';
                return $attendance->user->full_name;
            })
            ->editColumn('companys.name', function ($attendance) {
                if ($attendance->date->isToday())
                    return '<span class="font-blue">'.$attendance->user->company->name.'</span>';
                return $attendance->user->company->name;
            })
            ->rawColumns(['id', 'date', 'full_name', 'companys.name', 'sites.name'])
            ->make(true);

        return $dt;
    }


}
