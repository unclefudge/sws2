<?php

namespace App\Http\Controllers\Comms;

use Illuminate\Http\Request;
use Validator;

use DB;
use PDF;
use Mail;
use Session;
use App\User;
use App\Models\Comms\Notify;
use App\Models\Comms\NotifyUser;
use App\Models\Company\Company;
use App\Http\Requests;
use App\Http\Requests\Comms\NotifyRequest;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Intervention\Image\Facades\Image;
use Yajra\Datatables\Datatables;
use nilsenj\Toastr\Facades\Toastr;
use Carbon\Carbon;

/**
 * Class NotifyController
 * @package App\Http\Controllers
 */
class NotifyController extends Controller {

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('comms/notify/list');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('add.notify'))
            return view('errors/404');

        return view('comms/notify/create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store()
    {
        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('add.notify'))
            return view('errors/404');

        $notify_request = request()->all();
        $notify_request['from'] = Carbon::createFromFormat('d/m/Y H:i', request('from') . '00:00')->toDateTimeString();
        $notify_request['to'] = Carbon::createFromFormat('d/m/Y H:i', request('to') . '00:00')->toDateTimeString();

        $assign_to = request('assign_to');
        $assign_list = [];

        //dd($notify_request);

        // Users
        if ($assign_to == 'user') {
            if (in_array('all', request('user_list')))
                $assign_list = Auth::user()->company->users('1')->pluck('id')->toArray();
            else
                foreach (request('user_list') as $id)
                    $assign_list[] = $id;
            $notify = Notify::create($notify_request);
            $notify->assignUsers($assign_list);
        }

        // Companies
        if ($assign_to == 'company') {
            if (in_array('all', request('company_list')))
                $assign_list = Auth::user()->company->companies('1')->pluck('id')->toArray();
            else
                foreach (request('company_list') as $id)
                    $assign_list[] = $id;

            $user_list = [];
            $companies = '';
            foreach ($assign_list as $id) {
                $company = Company::findOrFail($id);
                $companies .= $company->name . ', ';
                foreach ($company->staffStatus(1) as $staff)
                    $user_list[] = $staff->id;
            }
            $notify = Notify::create($notify_request);
            $notify->assignUsers($user_list);
        }

        // Roles
        if ($assign_to == 'role') {
            $assign_list = request('role_list');
            $user_list = [];
            $users = DB::table('role_user')->select('user_id')->whereIn('role_id', $assign_list)->distinct('user_id')->orderBy('user_id')->get();
            $active_users = Auth::user()->company->users('1')->pluck('id')->toArray();
            foreach ($users as $u) {
                if (in_array($u->user_id, $active_users))
                    $user_list[] = $u->user_id;
            }

            $notify = Notify::create($notify_request);
            $notify->assignUsers($user_list);
        }

        // Sites
        if ($assign_to == 'site') {
            if (in_array('all', request('site_list')))
                $assign_list = Auth::user()->company->sites('1')->pluck('id')->toArray();
            else
                foreach (request('site_list') as $id)
                    $assign_list[] = $id;

            foreach ($assign_list as $site_id) {
                $notify_request['type_id'] = $site_id;
                $notify = Notify::create($notify_request);
            }
        }

        Toastr::success("Created Notify");

        return redirect('comms/notify');
    }

    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $notify = Notify::findorFail($id);

        // Check authorisation and throw 404 if not
        //if (!Auth::user()->allowed2('view.notify', $notify))
        //   return view('errors/404');

        return view('comms/notify/show', compact('notify'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $notify = Notify::findorFail($id);
        $notify_request = $request->all();


        // Check authorisation and throw 404 if not
        //if (!Auth::user()->allowed2('del.notify', $notify))
        //    return view('errors/404');
        $notify->update($notify_request);

        return redirect('comms/notify/', $notify->id);
    }

    /**
     * Delete the specified resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        $notify = Notify::findOrFail($id);
        $notify->delete();

        return json_encode('success');
    }

    /**
     * Get Notify list current user is authorised to manage + Process datatables ajax request.
     */
    public function getNotify(Request $request)
    {
        $sign = '<';
        if ($request->get('status'))
            $sign = '>=';

        $records = Notify::select([
            'notify.id', 'notify.name', 'notify.info', 'notify.type', 'notify.type_id', 'notify.from', 'notify.to',
            DB::raw('DATE_FORMAT(notify.from, "%d/%m/%y") AS datefrom'),
            DB::raw('DATE_FORMAT(notify.to, "%d/%m/%y") AS dateto'),
            DB::raw('CONCAT(users.firstname, " ", users.lastname) AS fullname'),
        ])
            ->join('users', 'notify.created_by', '=', 'users.id')
            ->where('notify.company_id', Auth::user()->company_id)
            ->where('notify.to', $sign, Carbon::today()->toDateTimeString())
            ->orderBy('notify.from', 'desc');

        $dt = Datatables::of($records)
            ->addColumn('view', function ($notify) {
                return ('<div class="text-center"><a href="/comms/notify/' . $notify->id . '"><i class="fa fa-search"></i></a></div>');
            })
            ->editColumn('datefrom', '{{$datefrom}} - {{$dateto}}')
            ->addColumn('viewed', function ($rec) {
                $notify = Notify::find($rec->id);
                $assigned_count = ($notify->assignedTo()) ? $notify->assignedTo()->count() : 0;
                $viewed_count = ($notify->viewedBy()) ? $notify->viewedBy()->count() : 0;
                $label_type = ($assigned_count == $viewed_count && $assigned_count != 0) ? 'label-success' : 'label-danger';

                return '<span class="label pull-right ' . $label_type . '">' . $viewed_count . ' / ' . $assigned_count . '</span>';

            })
            ->addColumn('action', function ($rec) {
                $record = Notify::find($rec->id);
                $actions = '';
                $actions .= '<button class="btn dark btn-xs sbold uppercase margin-bottom btn-delete " data-remote="/comms/notify/' . $rec->id . '" data-name="' . $rec->name . '"><i class="fa fa-trash"></i></button>';

                return $actions;
            })
            ->rawColumns(['view', 'viewed', 'action'])
            ->make(true);

        return $dt;
    }
}
