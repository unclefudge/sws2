<?php

namespace App\Http\Controllers\Support;

use App\Http\Controllers\Controller;
use App\Http\Requests\Support\SupportTicketRequest;
use App\Models\Misc\Attachment;
use App\Models\Site\SiteHazardAction;
use App\Models\Support\SupportTicket;
use App\Models\Support\SupportTicketAction;
use App\User;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Mail;
use nilsenj\Toastr\Facades\Toastr;
use Session;
use Validator;
use Yajra\Datatables\Datatables;

/**
 * Class SupportTicketController
 * @package App\Http\Controllers
 */
class SupportTicketController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('support/ticket/list');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store(SupportTicketRequest $request)
    {
        $ticket_request = removeNullValues($request->all());
        $ticket_request['company_id'] = Auth::user()->company_id;
        $ticket_request['attachment'] = '';  // clear attachment as it's attached to Action
        $ticket_request['assigned_to'] = (request('type') == 0) ? 3 : null;  // Auto assign new standard requests to Fudge

        //dd($ticket_request);
        $ticket = SupportTicket::create($ticket_request);

        //Create action taken + attach image to issue
        if ($ticket) {
            $action_request = ['action' => $request->get('summary')];
            $action = $ticket->actions()->save(new SupportTicketAction($action_request));

            // Handle attachments
            $attachments = collect(request('filepond', []))->filter()->values();
            foreach ($attachments as $tmp_filename) {
                $attachment = Attachment::create(['table' => 'support_tickets_actions', 'table_id' => $action->id, 'directory' => "support/ticket"]);
                $attachment->saveAttachment($tmp_filename);
            }

            // Email ticket
            if (!$ticket->type)
                $ticket->emailTicket($action);

        }
        Toastr::success("Created support ticket");

        return redirect('support/ticket');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('support/ticket/create');
    }

    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $ticket = SupportTicket::findorFail($id);

        return view('support/ticket/show', compact('ticket'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Add action to existing ticket
     */
    public function addAction()
    {
        $ticket_id = request('ticket_id');
        $ticket = SupportTicket::findorFail($ticket_id);

        request()->validate(['action' => 'required']); // Validate

        //dd(request()->all());
        //Add action to ticket
        if ($ticket) {
            $action = $ticket->actions()->save(new SupportTicketAction(['action' => request('action')]));

            // Handle attachments
            $attachments = collect(request('filepond', []))->filter()->values();
            foreach ($attachments as $tmp_filename) {
                $attachment = Attachment::create(['table' => 'support_tickets_actions', 'table_id' => $action->id, 'directory' => "support/ticket"]);
                $attachment->saveAttachment($tmp_filename);
            }

            // Email action
            $action->emailAction();

            $ticket->updated_by = Auth::user()->id;
            $ticket->touch();
            $ticket->save();

            Toastr::success("Added action");
        }

        return redirect('support/ticket/' . $ticket_id);
    }


    /**
     * Update Priority of existing ticket
     */
    public function updatePriority($id, $priority)
    {
        $ticket = SupportTicket::findorFail($id);

        $old = $ticket->priority_text;
        $ticket->priority = $priority;
        $action_request = ['action' => "Changed ticket priority from $old to " . $ticket->priority_text];
        $action = $ticket->actions()->save(new SupportTicketAction($action_request));
        $action->emailAction();
        $ticket->save();
        Toastr::success("Updated priority to " . $ticket->priority_text);

        return redirect('support/ticket/' . $id);
    }

    /**
     * Update Assigned of existing ticket
     */
    public function updateAssigned($id, $assigned)
    {
        $ticket = SupportTicket::findorFail($id);
        $user = User::findorFail($assigned);

        $ticket->assigned_to = $assigned;
        $action_request = ['action' => "Assigned ticket to " . $user->fullname];
        $action = $ticket->actions()->save(new SupportTicketAction($action_request));
        $action->emailAction();
        $ticket->save();
        Toastr::success("Updated assigned to " . $user->firstname);

        return redirect('support/ticket/' . $id);
    }

    /**
     * Update ETA of existing ticket
     */
    public function updateETA(Request $request, $id, $date)
    {
        $ticket = SupportTicket::findorFail($id);
        $eta = Carbon::createFromFormat('Y-m-d H:i:s', $date . ' 00:00:00');
        $ticket->eta = $eta;
        $ticket->save();

        return redirect('support/ticket/' . $id);
    }

    /**
     * Update Hours of existing ticket
     */
    public function updateHours(Request $request, $id, $hours)
    {
        $ticket = SupportTicket::findorFail($id);
        $ticket->hours = $hours;
        $ticket->save();

        return redirect('support/ticket/' . $id);
    }

    /**
     * Update status of existing ticket
     */
    public function updateStatus(Request $request, $id, $status)
    {
        $ticket = SupportTicket::findorFail($id);
        $ticket->status = $status;

        if ($status) {
            $ticket->resolved_at = null;
            $ticket->eta = null;
            $action_request = ['action' => 'Re-opened ticket'];
            $action = $ticket->actions()->save(new SupportTicketAction($action_request));
            $action->emailAction();
            Toastr::success("Re-opened ticket");
            $ticket->save();

            return redirect('support/ticket/' . $id);
        } else {
            $ticket->resolved_at = Carbon::now();
            $action_request = ['action' => 'Resolved ticket'];
            $action = $ticket->actions()->save(new SupportTicketAction($action_request));
            $action->emailAction();
            Toastr::success("Resolved ticket");
            $ticket->save();

            return redirect('support/ticket');
        }

    }

    /**
     * Get Tickets current user is authorised to manage + Process datatables ajax request.
     */
    public function getTickets(Request $request)
    {
        if (in_array(Auth::user()->id, [3, 1359])) // Fudge, Courtney,
            $user_list = User::all()->pluck('id')->toArray();
        else if (Auth::user()->hasPermission2('edit.user.security'))
            $user_list = Auth::user()->company->users()->pluck('id')->toArray();
        else
            $user_list = [Auth::user()->id];
        $ticket_records = DB::table('support_tickets AS t')
            ->select(['t.id', 't.name', 't.created_by', 't.attachment', 't.priority', 't.status', 't.resolved_at', 't.eta', 't.assigned_to',
                DB::raw('DATE_FORMAT(t.updated_at, "%d/%m/%y") AS nicedate'),
                DB::raw('DATE_FORMAT(t.eta, "%d/%m/%y") AS niceeta'),
                DB::raw('CONCAT(users.firstname, " ", users.lastname) AS fullname'),
            ])
            ->join('users', 't.updated_by', '=', 'users.id')
            ->where('t.status', '=', $request->get('status'))
            ->where('t.type', '=', '0')
            ->whereIn('t.created_by', $user_list);
        //->orderBy('site_hazards.created_at', 'DESC');


        $dt = Datatables::of($ticket_records)
            ->addColumn('view', function ($ticket) {
                return ('<div class="text-center"><a href="/support/ticket/' . $ticket->id . '"><i class="fa fa-search"></i></a></div>');
            })
            //->editColumn('id', '<div class="text-center"><a href="/site/hazard/{{$id}}"><i class="fa fa-search"></i></a></div>')
            ->editColumn('priority', function ($ticket) {
                if ($ticket->priority == '0') return 'none';
                if ($ticket->priority == '1') return 'low';
                if ($ticket->priority == '2') return 'med';
                if ($ticket->priority == '3') return 'high';
                if ($ticket->priority == '4') return 'progress';
            })
            ->editColumn('assigned_to', function ($ticket) {
                $user = User::find($ticket->assigned_to);

                return ($user) ? $user->firstname : '-';
            })
            ->editColumn('niceeta', function ($ticket) {
                if (!$ticket->eta) return 'none';

                return $ticket->niceeta;
            })
            //->filterColumn('fullname', 'whereRaw', "CONCAT(users . firstname, ' ', users . lastname) like ? ", [" % $1 % "])
            ->rawColumns(['view'])
            ->make(true);

        return $dt;
    }

    /**
     * Get Upgrades current user is authorised to manage + Process datatables ajax request.
     */
    public function getUpgrades(Request $request)
    {
        //$company_list = Auth::user()->company->reportsTo()->sites()->pluck('id')->toArray();
        $company_list = Auth::user()->company->companies()->pluck('id')->toArray();
        //$user_list = Auth::user()->company->users($request->get('status'))->pluck('id')->toArray();
        $ticket_records = DB::table('support_tickets AS t')
            ->select(['t.id', 't.name', 't.created_by', 't.attachment', 't.priority', 't.status', 't.resolved_at', 't.eta', 't.hours', 't.assigned_to',
                DB::raw('DATE_FORMAT(t.updated_at, "%d/%m/%y") AS nicedate'),
                DB::raw('DATE_FORMAT(t.eta, "%d/%m/%y") AS niceeta'),
                DB::raw('CONCAT(users.firstname, " ", users.lastname) AS fullname'),
            ])
            ->join('users', 't.updated_by', '=', 'users.id')
            ->where('t.status', '=', $request->get('status'))
            ->where('t.type', '=', '1')
            ->whereIn('t.company_id', $company_list);

        $dt = Datatables::of($ticket_records)
            ->addColumn('view', function ($ticket) {
                return ('<div class="text-center"><a href="/support/ticket/' . $ticket->id . '"><i class="fa fa-search"></i></a></div>');
            })
            ->editColumn('priority', function ($ticket) {
                if ($ticket->priority == '0') return 'none';
                if ($ticket->priority == '1') return '1-low';
                if ($ticket->priority == '2') return '2-med';
                if ($ticket->priority == '3') return '3-high';
                if ($ticket->priority == '4') return '4-progress';
            })
            ->editColumn('niceeta', function ($ticket) {
                if (!$ticket->eta) return 'none';

                return $ticket->niceeta;
            })
            ->editColumn('hours', function ($ticket) {
                if ($ticket->hours == 0)
                    return '?';

                if ($ticket->hours >= 8)
                    return $ticket->hours / 8 . ' day';

                return $ticket->hours . ' hr';
            })
            ->editColumn('assigned_to', function ($ticket) {
                $user = User::find($ticket->assigned_to);

                return ($user) ? $user->firstname : '-';
            })
            //->filterColumn('fullname', 'whereRaw', "CONCAT(users . firstname, ' ', users . lastname) like ? ", [" % $1 % "])
            ->rawColumns(['view'])
            ->make(true);

        return $dt;
    }
}
