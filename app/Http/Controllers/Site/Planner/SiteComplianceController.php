<?php

namespace App\Http\Controllers\Site\Planner;

use Illuminate\Http\Request;
use Validator;

use DB;
use Session;
use App\User;
use App\Models\Site\Site;
use App\Models\Site\Planner\SiteCompliance;
use App\Models\Site\Planner\SiteComplianceReason;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Yajra\Datatables\Datatables;
use nilsenj\Toastr\Facades\Toastr;
use Carbon\Carbon;

/**
 * Class SiteController
 * @package App\Http\Controllers
 */
class SiteComplianceController extends Controller {

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $sites = [];
            $users = [];
            $compliance = [];
            $site_list = Auth::user()->authSites('view.compliance')->pluck('id')->toArray();
            $compliance_recs = SiteCompliance::whereIn('site_id', $site_list)->where('archive', 0)->get();
            foreach ($compliance_recs as $comply) {
                $array = [];
                $array['id'] = $comply->id;

                // Site Info - Array of unique sites (stores previous sites to speed up)
                if (isset($sites[$comply->site_id])) {
                    $site = $sites[$comply->site_id];
                    $supers = $site->supers;
                } else {
                    $site = Site::find($comply->site_id);
                    $sites[$comply->site_id] = (object) ['id' => $site->id, 'name' => $site->name, 'supers' => $site->supervisorName];
                    $supers = $site->supervisorName;
                }
                $array['site_id'] = $site->id;
                $array['site_name'] = $site->name;
                $array['site_supers'] = $supers;

                // User Info - Array of unique users (store previous users to speed up)
                if (isset($users[$comply->user_id])) {
                    $user = $users[$comply->user_id];
                    $company_name = $user->company_name;
                    $nc = $user->nc;
                    $nc_dates = $user->nc_dates;
                } else {
                    $user = User::find($comply->user_id);
                    $nc = $user->nonCompliant()->count();
                    $dates = $user->nonCompliant()->pluck('date');
                    $nc_dates = [];
                    foreach ($dates as $date) {
                        $nc_dates[] = $date->format('d/m/Y');
                    }
                    $users[$comply->user_id] = (object) ['id' => $user->id, 'full_name' => $user->full_name, 'company_name' => $user->company->name_alias, 'nc' => $nc, 'nc_dates' => $nc_dates];
                    $company_name = $user->company->name_alias;
                }
                $array['user_id'] = $user->id;
                $array['user_name'] = $user->full_name;
                $array['user_company'] = $company_name;
                $array['user_nc'] = $nc;
                $array['user_nc_dates'] = $nc_dates;

                //$array['reason_name'] = $comply->name;
                $reason = $comply->reason;
                if ($comply->reason == null)
                    $reason = '';
                $array['reason'] = $reason;
                $array['date'] = $comply->date->format('Y-m-d');
                $array['resolved_at'] = $comply->resolved_at->format('Y-m-d');
                $array['status'] = $comply->status;
                $array['notes'] = $comply->notes;
                $compliance[] = $array;
            };

            // Reasons array in specific Vuejs 'select' format.
            $reason_recs = SiteComplianceReason::where('status', '1')
                ->where('company_id', Auth::user()->company_id)
                ->orderBy('name')->get();

            $reasons = [];
            $reasons[] = ['value' => '', 'text' => 'Unassigned Reason'];
            foreach ($reason_recs as $reason) {
                $reasons[] = [
                    'value' => $reason->id,
                    'text'  => $reason->name,
                    'name'  => $reason->name,
                ];
            }

            $json = [];
            $json[] = $compliance;
            $json[] = $reasons;

            return $json;
        }

        // Check authorisation and throw 404 if not
        if (!Auth::user()->hasAnyPermissionType('compliance'))
            return view('errors/404');

        return view('site/compliance/list');
    }

    /**
     * Update the specified resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $comply = SiteCompliance::findOrFail($id);

        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('edit.compliance', $comply))
            return view('errors/404');

        $comply_request = $request->only('reason', 'status', 'notes');

        if ($comply_request['reason'] == '')
            $comply_request['reason'] = null;

        // Update resolve date if just modified
        if ($comply_request['status'] != $comply->status)
            $comply_request['resolved_at'] = ($comply_request['status']) ? Carbon::now()->toDateTimeString() : '000-00-00 00:00:00';

        // Format date from datetime picker to mysql format
        //$date = new Carbon (preg_replace('/-/', '', $request->get('resolved_at')));
        //$comply_request['resolveddate'] = $date->format('Y-m-d H:i:s');

        $comply->update($comply_request);
        Toastr::success("Updated record");

        return $comply;
    }

    public function show($id)
    {
        // required for all functions
    }
}
