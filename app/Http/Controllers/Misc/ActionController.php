<?php

namespace App\Http\Controllers\Misc;

use App\Http\Controllers\Controller;
use App\Models\Company\Company;
use App\Models\Company\CompanyDocReview;
use App\Models\Misc\Action;
use App\Models\Misc\Supervisor\SuperChecklist;
use App\Models\Site\SiteAccident;
use App\Models\Site\SiteAsbestos;
use App\Models\Site\SiteHazard;
use App\Models\Site\SiteInspectionElectrical;
use App\Models\Site\SiteInspectionPlumbing;
use App\Models\Site\SiteMaintenance;
use App\Models\Site\SiteNote;
use App\Models\Site\SitePracCompletion;
use App\Models\Site\SiteQa;
use DB;
use Illuminate\Http\Request;
use Mail;
use Session;


/**
 * Class ActionController
 * @package App\Http\Controllers
 */
class ActionController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, $table, $table_id)
    {
        // Only Allow Ajax requests
        //if ($request->ajax()) {
        $actions = DB::table("actions AS a")->select([
            'a.id', 'a.created_by', 'a.action',
            DB::raw('DATE_FORMAT(a.created_at,\'%d/%m/%y\') AS niceDate '),
            DB::raw('CONCAT(users.firstname, " ", users.lastname) AS fullname')])
            ->join('users', 'a.created_by', '=', 'users.id')
            ->where('table', $table)
            ->where('table_id', $table_id)
            ->orderBy('a.created_at', 'desc')->get();

        return response()->json($actions);

        //}

        return view('errors/404');
    }

    /**
     * Store a newly created resource in storage via ajax.
     */
    public function store(Request $request)
    {
        if ($request->ajax()) {
            $action = Action::create($request->all());
            switch (request('table')) {
                case 'site_accidents':
                    $record = SiteAccident::find(request('table_id'));
                    break;
                case 'site_hazards':
                    $record = SiteHazard::find(request('table_id'));
                    break;
                case 'site_asbestos':
                    $record = SiteAsbestos::find(request('table_id'));
                    break;
                case 'site_qa':
                    $record = SiteQa::find(request('table_id'));
                    break;
                case 'site_notes':
                    $record = SiteNote::find(request('table_id'));
                    break;
                case 'site_prac_completion':
                    $record = SitePracCompletion::find(request('table_id'));
                    break;
                case 'site_maintenance':
                    $record = SiteMaintenance::find(request('table_id'));
                    break;
                case 'site_inspection_plumbing':
                    $record = SiteInspectionPlumbing::find(request('table_id'));
                    break;
                case 'site_inspection_electrical':
                    $record = SiteInspectionElectrical::find(request('table_id'));
                    break;
                case 'company_docs_review':
                    $record = CompanyDocReview::find(request('table_id'));
                    break;
                case 'companys':
                    $record = Company::find(request('table_id'));
                    break;
                case 'supervisor_checklist':
                    $record = SuperChecklist::find(request('table_id'));
                    break;
            }
            $record->touch();
            $record->emailAction($action);

            $email_to = [env('EMAIL_DEV')];
            // Email note
            if (request('table') == 'site_maintenance') {
                if ($record->super_id) {
                    if (\App::environment('prod'))
                        $email_to = ['kirstie@capecod.com.au'];
                    Mail::to($email_to)->send(new \App\Mail\Site\SiteMaintenanceNote($record, $action));
                }
            }

            return response()->json($action);
        }

        return view('errors/404');
    }

    /**
     * Update the specified resource in storage via ajax.
     */
    public function update(Request $request)
    {
        if ($request->ajax()) {
            $action = Action::findOrFail($request->get('id'));
            $action->update($request->all());

            return response()->json($action);
        }

        return view('errors/404');
    }
}
