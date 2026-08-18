<?php

namespace App\Http\Controllers\Misc;

use App\Http\Controllers\Controller;
use App\Models\Misc\Action;
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

            $record = $action->record;

            if ($record) {
                $record->touch();
                if (method_exists($record, 'emailAction')) $record->emailAction($action);

                // Special Maintenance Note email
                if (request('table') == 'site_maintenance' && $record->super_id) {
                    $email_to = [config('mail.email_dev')];

                    if (app()->environment('prod')) $email_to = ['kirstie@capecod.com.au'];
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
