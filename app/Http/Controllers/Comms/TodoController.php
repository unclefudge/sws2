<?php

namespace App\Http\Controllers\Comms;

use Illuminate\Http\Request;
use Validator;

use DB;
use PDF;
use Mail;
use Session;
use App\User;
use App\Models\Comms\Todo;
use App\Models\Comms\TodoUser;
use App\Models\Misc\Action;
use App\Models\Company\Company;
use App\Models\Site\Site;
use App\Models\Site\SiteHazard;
use App\Models\Site\SiteAccident;
use App\Models\Site\Incident\SiteIncident;
use App\Models\Misc\Equipment\EquipmentLocation;
use App\Models\Misc\Equipment\EquipmentLocationItem;
use App\Models\Misc\Equipment\EquipmentLog;
use App\Http\Requests;
use App\Http\Requests\Comms\TodoRequest;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Intervention\Image\Facades\Image;
use Yajra\Datatables\Datatables;
use nilsenj\Toastr\Facades\Toastr;
use Carbon\Carbon;

/**
 * Class TodoController
 * @package App\Http\Controllers
 */
class TodoController extends Controller {

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('comms/todo/list');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('add.todo'))
            return view('errors/404');

        $type = '';
        $type_id = 0;

        return view('comms/todo/create', compact('type', 'type_id'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function createType($type, $type_id)
    {
        return view('comms/todo/create', compact('type', 'type_id'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store(TodoRequest $request)
    {
        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('add.todo'))
            return view('errors/404');

        $todo_request = $request->all();
        $todo_request['due_at'] = ($request->get('due_at')) ? Carbon::createFromFormat('d/m/Y H:i', $request->get('due_at') . '00:00')->toDateTimeString() : null;

        $assign_to = $request->get('assign_to');
        $assign_list = [];

        // Users
        if ($assign_to == 'user') {
            foreach ($request->get('user_list') as $id) {
                if ($id == 'all') {
                    $assign_list = Auth::user()->company->users('1')->pluck('id')->toArray();
                    break;
                } else
                    $assign_list[] = $id;
            }

            if ($request->get('assign_multi')) {
                foreach ($assign_list as $id) {
                    $todo = Todo::create($todo_request);
                    $todo->assignUsers($id);
                }
            } else {
                $todo = Todo::create($todo_request);
                $todo->assignUsers($assign_list);
            }
        }

        // Companies
        if ($assign_to == 'company') {
            foreach ($request->get('company_list') as $id) {
                if ($id == 'all') {
                    $assign_list = Auth::user()->company->companies(1)->pluck('id')->toArray();
                    break;
                } else
                    $assign_list[] = $id;
            }

            if ($request->get('assign_multi')) {
                foreach ($assign_list as $id) {
                    $company = Company::findOrFail($id);
                    foreach ($company->staffStatus(1) as $staff) {
                        $todo = Todo::create($todo_request);
                        $todo->assignUsers($staff->id);
                    }
                }
            } else {
                foreach ($assign_list as $id) {
                    $company = Company::findOrFail($id);
                    $todo = Todo::create($todo_request);
                    $todo->assignUsers($company->staffStatus(1)->pluck('id')->toArray());
                }

            }
        }

        // Roles
        if ($assign_to == 'role') {
            $assign_list = $request->get('role_list');

            if ($request->get('assign_multi')) {
                $user_list = [];
                $users = DB::table('role_user')->select('user_id')->whereIn('role_id', $assign_list)->distinct('user_id')->orderBy('user_id')->get();
                foreach ($users as $u) {
                    if (in_array($u->user_id, Auth::user()->company->users(1)->pluck('id')->toArray()))
                        $user_list[] = $u->user_id;
                }
                foreach ($user_list as $id) {
                    $todo = Todo::create($todo_request);
                    $todo->assignUsers($id);
                }
            } else {
                foreach ($assign_list as $id) {
                    $user_list = [];
                    $users = DB::table('role_user')->select('user_id')->where('role_id', $id)->distinct('user_id')->orderBy('user_id')->get();
                    foreach ($users as $u) {
                        if (in_array($u->user_id, Auth::user()->company->users(1)->pluck('id')->toArray()))
                            $user_list[] = $u->user_id;
                    }
                    $todo = Todo::create($todo_request);
                    $todo->assignUsers($user_list);
                }

            }
        }

        //dd($todo_request);

        Toastr::success("Created ToDo");

        if ($todo->type == 'hazard') {
            $hazard = SiteHazard::find($todo->type_id);
            $action = Action::create(['action' => "Created task: $todo->info", 'table' => 'site_hazards', 'table_id' => $todo->type_id]);
            $hazard->touch(); // update timestamp
            $todo->emailToDo();

            return redirect('/site/hazard/' . $todo->type_id);
        }
        if ($todo->type == 'accident') {
            $accident = SiteAccident::find($todo->type_id);
            $action = Action::create(['action' => "Created task: $todo->info", 'table' => 'site_accidents', 'table_id' => $todo->type_id]);
            $accident->touch(); // update timestamp
            $todo->emailToDo();

            return redirect('/site/accident/' . $todo->type_id);
        }

        if ($todo->type == 'incident') {
            $incident = SiteIncident::find($todo->type_id);
            $action = Action::create(['action' => "Created task: $todo->info", 'table' => 'site_incidents', 'table_id' => $todo->type_id]);
            $incident->touch(); // update timestamp
            $todo->emailToDo();

            return redirect('/site/incident/' . $todo->type_id);
        }

        return redirect('/todo');
    }

    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $todo = Todo::findorFail($id);

        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('view.todo', $todo))
           return view('errors/404');

        if (!$todo->isOpenedBy(Auth::user()))
            $todo->markOpenedBy(Auth::user());

        return view('comms/todo/show', compact('todo'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $todo = Todo::findorFail($id);
        $old_status = $todo->status;
        $todo_request = $request->all();

        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('edit.todo', $todo))
            return view('errors/404');

        //dd($todo_request);
        $todo->update($todo_request);

        $table = '';
        if ($todo->type == 'hazard') $table = 'site_hazards';
        if ($todo->type == 'accident') $table = 'site_accidents';
        if ($todo->type == 'incident') $table = 'site_incidents';

        // Recently closed Hazard/Incident ToDoo
        if (in_array($todo->type, ['hazard', 'accident', 'incident']) && $old_status && !$todo->status) {
            $action = Action::create(['action' => "Completed task: $todo->info", 'table' => $table, 'table_id' => $todo->type_id]);
            $todo->emailToDoCompleted();
            $todo->done_by = Auth::user()->id;
            $todo->done_at = Carbon::now()->toDateTimeString();
            $todo->save();
        }
        // Re-opened Hazard/Incident ToDoo
        if (in_array($todo->type, ['hazard', 'accident', 'incident']) && !$old_status && $todo->status) {
            $action = Action::create(['action' => "Re-opened task: $todo->info", 'table' => $table, 'table_id' => $todo->type_id]);
            $todo->emailToDo();
            $todo->done_by = 0;
            $todo->done_at = null;
            $todo->save();
        }

        if ($request->get('delete_attachment') && $todo->attachment) {
            if (file_exists(public_path($todo->attachment_url)))
                unlink(public_path($todo->attachment_url));
            $todo->attachment = '';
            $todo->save();
        }

        // Handle attached file
        if ($request->hasFile('singlefile')) {
            $file = $request->file('singlefile');

            $path = "filebank/todo";
            $name = $todo->id . '-' . sanitizeFilename(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . strtolower($file->getClientOriginalExtension());
            // Ensure filename is unique by adding counter to similiar filenames
            $count = 1;
            while (file_exists(public_path("$path/$name")))
                $name = $todo->id . '-' . sanitizeFilename(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '-' . $count ++ . '.' . strtolower($file->getClientOriginalExtension());
            $file->move($path, $name);
            $todo->attachment = $name;
            $todo->save();
        }

        Toastr::success("Saved ToDo");

        if ($todo->type == 'incident')
            return redirect('/site/incident/' . $todo->type_id);

        return redirect('todo/' . $todo->id);
    }

    /**
     * Get Todoo list current user is authorised to manage + Process datatables ajax request.
     */
    public function getTodo()
    {

        $records = TodoUser::select([
            'todo_user.todo_id', 'todo_user.user_id', 'todo_user.opened',
            'todo.id', 'todo.name', 'todo.info', 'todo.type', 'todo.type_id', 'todo.due_at',
            DB::raw('CONCAT(todo.name, "<br>", todo.info) AS task'),
            DB::raw('DATE_FORMAT(todo.due_at, "%d/%m/%y") AS duedate'),
            DB::raw('CONCAT(users.firstname, " ", users.lastname) AS fullname'),
        ])
            ->join('todo', 'todo_user.todo_id', '=', 'todo.id')
            ->join('users', 'todo.created_by', '=', 'users.id')
            ->where(function ($q) {
                $q->where('todo_user.user_id', Auth::user()->id);
                /*$q->orWhere('todo.created_by', Auth::user()->id);*/
            })
            ->where('todo.status', request('status'))
            ->orderBy('todo.due_at');


        $dt = Datatables::of($records)
            ->addColumn('view', function ($todo) {
                return ('<div class="text-center"><a href="/todo/' . $todo->id . '"><i class="fa fa-search"></i></a></div>');
            })
            ->editColumn('duedate', function ($todo) {
                if (!$todo->duedate)
                    return 'N/A';

                return $todo->duedate;
            })
            ->rawColumns(['view', 'task'])
            ->make(true);

        return $dt;
    }
}
