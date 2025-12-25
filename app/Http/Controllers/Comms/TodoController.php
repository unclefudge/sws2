<?php

namespace App\Http\Controllers\Comms;

use App\Http\Controllers\Controller;
use App\Models\Comms\Todo;
use App\Models\Comms\TodoUser;
use App\Models\Company\Company;
use App\Models\Misc\Action;
use App\Models\Misc\Attachment;
use App\Models\Misc\Form\Form;
use App\Models\Misc\Form\FormQuestion;
use App\Models\Site\Incident\SiteIncident;
use App\Models\Site\SiteAccident;
use App\Models\Site\SiteHazard;
use App\Models\Site\SiteMaintenance;
use App\Services\FileBank;
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
 * Class TodoController
 * @package App\Http\Controllers
 */
class TodoController extends Controller
{

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
        $type_id2 = null;

        return view('comms/todo/create', compact('type', 'type_id', 'type_id2'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function createType($type, $type_id)
    {
        $type_id2 = null;
        if ($type == 'inspection')
            list($type_id, $type_id2) = explode('-', $type_id, 2);

        return view('comms/todo/create', compact('type', 'type_id', 'type_id2'));
    }


    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store()
    {
        // -------------------------------------------------
        // Authorisation
        // -------------------------------------------------
        if (!Auth::user()->allowed2('add.todo'))
            return view('errors/404');

        // -------------------------------------------------
        // Base request data
        // -------------------------------------------------
        $todoData = request()->all();
        $todoData['due_at'] = request('due_at') ? Carbon::createFromFormat('d/m/Y H:i', request('due_at') . '00:00')->toDateTimeString() : null;

        $assignTo = request('assign_to');
        $assignList = [];
        $todo = null;

        // -------------------------------------------------
        // USER assignment
        // -------------------------------------------------
        if ($assignTo === 'user') {
            foreach ((array)request('user_list') as $id) {
                if ($id === 'all') {
                    $assignList = Auth::user()->company->users(1)->pluck('id')->toArray();
                    break;
                }
                $assignList[] = $id;
            }

            if (request('assign_multi')) {
                foreach ($assignList as $userId) {
                    $todo = Todo::create($todoData);
                    $todo->assignUsers($userId);
                }
            } else {
                $todo = Todo::create($todoData);
                $todo->assignUsers($assignList);
            }
        }

        // -------------------------------------------------
        // COMPANY assignment
        // -------------------------------------------------
        if ($assignTo === 'company') {
            foreach ((array)request('company_list') as $id) {
                if ($id === 'all') {
                    $assignList = Auth::user()->company->companies(1)->pluck('id')->toArray();
                    break;
                }
                $assignList[] = $id;
            }

            if (request('assign_multi')) {
                foreach ($assignList as $companyId) {
                    $company = Company::findOrFail($companyId);
                    foreach ($company->staffStatus(1) as $staff) {
                        $todo = Todo::create($todoData);
                        $todo->assignUsers($staff->id);
                    }
                }
            } else {
                foreach ($assignList as $companyId) {
                    $company = Company::findOrFail($companyId);
                    $todo = Todo::create($todoData);
                    $todo->assignUsers($company->staffStatus(1)->pluck('id')->toArray());
                }
            }
        }

        // -------------------------------------------------
        // ROLE assignment
        // -------------------------------------------------
        if ($assignTo === 'role') {
            $roleIds = (array)request('role_list');
            if (request('assign_multi')) {

                $userIds = DB::table('role_user')->whereIn('role_id', $roleIds)->pluck('user_id')->unique()
                    ->filter(fn($id) => Auth::user()->company->users(1)->pluck('id')->contains($id))
                    ->toArray();

                foreach ($userIds as $userId) {
                    $todo = Todo::create($todoData);
                    $todo->assignUsers($userId);
                }

            } else {
                foreach ($roleIds as $roleId) {
                    $userIds = DB::table('role_user')->where('role_id', $roleId)->pluck('user_id')->unique()
                        ->filter(fn($id) => Auth::user()->company->users(1)->pluck('id')->contains($id))
                        ->toArray();

                    $todo = Todo::create($todoData);
                    $todo->assignUsers($userIds);
                }
            }
        }

        //dd(request('filepond'));
        // -------------------------------------------------
        // Attachments (FilePond → Spaces)
        // -------------------------------------------------
        foreach (request('filepond') as $tmp_filename) {
            ray('filepond', $tmp_filename);
            $attachment = Attachment::create(['table' => 'todo', 'table_id' => $todo->id, 'directory' => "todo/{$todo->id}",]);
            $attachment->saveAttachment($tmp_filename);
        }

        Toastr::success('Created ToDo');

        // -------------------------------------------------
        // Post-create actions + redirects
        // -------------------------------------------------
        if (!$todo)
            return redirect('/todo');

        switch ($todo->type) {

            case 'hazard':
                $hazard = SiteHazard::find($todo->type_id);
                Action::create(['action' => "Created task: {$todo->info}", 'table' => 'site_hazards', 'table_id' => $todo->type_id,]);
                $hazard->touch();
                $todo->emailToDo();
                return redirect("/site/hazard/{$todo->type_id}");

            case 'accident':
                $accident = SiteAccident::find($todo->type_id);
                Action::create(['action' => "Created task: {$todo->info}", 'table' => 'site_accidents', 'table_id' => $todo->type_id,]);
                $accident->touch();
                $todo->emailToDo();
                return redirect("/site/accident/{$todo->type_id}");

            case 'incident':
                $incident = SiteIncident::find($todo->type_id);
                Action::create(['action' => "Created task: {$todo->info}", 'table' => 'site_incidents', 'table_id' => $todo->type_id,]);
                $incident->touch();
                $todo->emailToDo();
                return redirect("/site/incident/{$todo->type_id}");

            case 'inspection':
                $form = Form::find($todo->type_id);
                $form->touch();
                $todo->emailToDo();

                $question = FormQuestion::find($todo->type_id2);
                return redirect("/site/inspection/{$todo->type_id}/{$question->section->page->order}");

            case 'maintenance_task':
                $maintenance = SiteMaintenance::find($todo->type_id);
                Action::create(['action' => "Created task: {$todo->info}", 'table' => 'site_maintenance', 'table_id' => $todo->type_id,]);
                $maintenance->touch();
                $todo->emailToDo('ASSIGNED', ['kirstie@capecod.com.au']);
                return redirect("/site/maintenance/{$todo->type_id}");
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
     * Edit the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $todo = Todo::findorFail($id);

        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('edit.todo', $todo))
            return view('errors/404');

        return view('comms/todo/edit', compact('todo'));

    }

    /**
     * Update the specified resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $todo = Todo::findorFail($id);

        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('edit.todo', $todo))
            return view('errors/404');

        // Delete ToDoo Task - if status = delete
        if (request('status') == 'delete') {
            $type_id = $todo->type_id;
            $type_id2 = $todo->type_id2;
            $todo->delete();
            Toastr::error("Task Deleted");
            if ($todo->type == 'inspection') {
                $FomQuestion = FormQuestion::find($type_id2);
                return redirect("/site/inspection/$type_id/" . $FomQuestion->section->page->order);
            }
            if ($todo->type == 'hazard')
                return redirect("/site/hazard/$type_id");
            if ($todo->type == 'incident')
                return redirect("/site/incident/$type_id");
            if ($todo->type == 'incident prevent')
                return redirect("/site/incident/$type_id/analysis");
            if ($todo->type == 'maintenance_task')
                return redirect("/site/maintenance/$type_id");
        }

        $old_status = $todo->status;
        //dd(request()->all());
        $todo_request = request()->all();
        $todo_request['due_at'] = (request('due_at')) ? Carbon::createFromFormat('d/m/Y H:i', request('due_at') . '00:00')->toDateTimeString() : null;
        (request('completed_at')) ? Carbon::createFromFormat('d/m/Y H:i', request('completed_at') . '00:00')->toDateTimeString() : null;

        // Recently closed ToDoo
        if ($todo->status && request()->has('status') && request('status') == 0) {  // required extra check has'status' variable existed because not present == 0
            $todo_request['done_by'] = Auth::user()->id;
            $todo_request['done_at'] = (request('completed_at')) ? Carbon::createFromFormat('d/m/Y H:i', request('completed_at') . '00:00')->toDateTimeString() : Carbon::now()->toDateTimeString();
        }
        // Recently re-opened ToDoo
        if (!$todo->status && request('status') == 1) {
            $todo_request['done_by'] = 0;
            $todo_request['done_at'] = null;
        }
        //dd($todo_request);
        $todo->update($todo_request);


        // Update Assigned Users
        $current_users = $user_list = $todo->users->pluck('user_id')->toArray();
        $assign_list = [];
        $newly_assigned = [];
        if (request('user_list')) {
            foreach (request('user_list') as $id) {
                if ($id == 'all') {
                    $assign_list = Auth::user()->company->users('1')->pluck('id')->toArray();
                    break;
                } else
                    $assign_list[] = $id;
            }

            foreach ($assign_list as $user_id) {
                if (!in_array($user_id, $current_users)) {
                    TodoUser::create(['todo_id' => $todo->id, 'user_id' => $user_id]); // Assign new user
                    $newly_assigned[] = $user_id;
                }
            }

            // Delete existing Assigned Users if not current
            $deleted = TodoUser::where('todo_id', $todo->id)->whereNotIn('user_id', $assign_list)->delete();

            // Email newly assigned users
            if ($newly_assigned)
                $todo->emailToDo($newly_assigned);
        }

        $table = '';
        if ($todo->type == 'hazard') $table = 'site_hazards';
        if ($todo->type == 'accident') $table = 'site_accidents';
        if ($todo->type == 'incident') $table = 'site_incidents';
        if ($todo->type == 'maintenance_task') $table = 'site_maintenance';

        // Recently closed Hazard ToDoo
        if (in_array($todo->type, ['hazard']) && $old_status && !$todo->status) {
            $action = Action::create(['action' => "Completed task: $todo->info", 'table' => $table, 'table_id' => $todo->type_id]);
            $todo->emailToDoCompleted();
        }
        // Re-opened Hazard ToDoo
        if (in_array($todo->type, ['hazard',]) && !$old_status && $todo->status) {
            $action = Action::create(['action' => "Re-opened task: $todo->info", 'table' => $table, 'table_id' => $todo->type_id]);
            $todo->emailToDo();
        }

        if (request('delete_attachment') && $todo->attachment) {
            FileBank::delete("todo/$todo->attachment");
            $todo->attachment = '';
            $todo->save();
        }

        // Handle single attached file
        if (request()->hasFile('singlefile')) {
            $basePath = 'todo';
            $forcedFilename = $todo->id . '-' . sanitizeFilename(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . strtolower($file->getClientOriginalExtension());
            $todo->attachment = FileBank::storeUploadedFile(request()->file('singlefile'), $basePath, $forcedFilename);
            $todo->save();
        }

        // Handle attachments
        $attachments = request("filepond");
        if ($attachments) {
            foreach ($attachments as $tmp_filename) {
                $attachment = Attachment::create(['table' => 'todo', 'table_id' => $todo->id, 'directory' => "todo/$todo->id"]);
                $attachment->saveAttachment($tmp_filename);
            }
        }

        Toastr::success("Saved ToDo");

        if ($todo->type == 'incident')
            return redirect("/site/incident/$todo->type_id");
        if ($todo->type == 'incident prevent')
            return redirect("/site/incident/$todo->type_id/analysis");

        return redirect('todo/' . $todo->id);
    }

    /**
     * Delete the specified resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $todo = Todo::findorFail($id);

        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('edit.todo', $todo))
            return view('errors/404');

        $todo->delete();

        return json_encode('success');
    }

    /**
     * Get Todoo list current user is authorised to manage + Process datatables ajax request.
     */
    public function getTodo()
    {
        $status = (request('status')) ? [1, 2] : [0];
        $records = TodoUser::select([
            'todo_user.todo_id', 'todo_user.user_id', 'todo_user.opened',
            'todo.id', 'todo.name', 'todo.info', 'todo.type', 'todo.type_id', 'todo.due_at', 'todo.created_by',
            DB::raw('CONCAT(todo.name, "<br>", todo.info) AS task'),
            DB::raw('DATE_FORMAT(todo.due_at, "%d/%m/%y") AS duedate'),
            //DB::raw('CONCAT(users.firstname, " ", users.lastname) AS fullname'),
        ])
            ->join('todo', 'todo_user.todo_id', '=', 'todo.id')
            //->join('users', 'todo.created_by', '=', 'users.id')
            ->where(function ($q) {
                $q->where('todo_user.user_id', Auth::user()->id);
                //$q->orWhere('todo.created_by', Auth::user()->id);
            })
            ->whereIn('todo.status', $status)
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
            ->editColumn('task', function ($todo) {
                return $todo->todo->name . "<br>Assigned to: " . $todo->todo->assignedToBySBC();
            })
            ->editColumn('created_by', function ($todo) {
                $user = User::find($todo->created_by);
                return $user ? $user->name : 'SafeWorksite';
            })
            ->rawColumns(['view', 'task'])
            ->make(true);

        return $dt;
    }
}
