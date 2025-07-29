<?php

namespace App\Http\Controllers\Safety;

use App\Http\Controllers\Controller;
use App\Http\Requests\Safety\ToolboxRequest;
use App\Http\Utilities\Diff2;
use App\Models\Comms\Todo;
use App\Models\Comms\TodoUser;
use App\Models\Company\Company;
use App\Models\Safety\ToolboxTalk;
use App\User;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Mail;
use nilsenj\Toastr\Facades\Toastr;
use PDF;
use Session;
use Validator;
use Yajra\Datatables\Datatables;

/**
 * Class ToolboxTalkController
 * @package App\Http\Controllers\Safety
 */
class ToolboxTalk3Controller extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Check authorisation and throw 404 if not
        if (!Auth::user()->hasAnyPermissionType('toolbox'))
            return view('errors/404');

        return view('safety/doc/toolbox3/list');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        /// Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('add.toolbox'))
            return view('errors/404');

        return view('safety/doc/toolbox3/create');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function createFromTemplate($id)
    {
        $talk = ToolboxTalk::findOrFail($id);

        /// Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('add.toolbox'))
            return view('errors/404');

        return view('safety/doc/toolbox3/create_template', compact('talk'));
    }

    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $talk = ToolboxTalk::findOrFail($id);

        /// Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('view.toolbox', $talk))
            return view('errors/404');

        if ($talk->status == 1)
            $talk->markOpened(Auth::user());  // Mark as opened for current user

        // Active or Archived
        if (in_array($talk->status, [0, 1, -1]))
            return view('safety/doc/toolbox3/show', compact('talk'));

        // Draft or Pending - default to edit mode
        if (Auth::user()->allowed2('edit.toolbox', $talk))
            return redirect('/safety/doc/toolbox3/' . $talk->id . '/edit');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store(ToolboxRequest $request)
    {
        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('add.toolbox'))
            return view('errors/404');

        $tool_request = request()->all();
        //dd($tool_request);

        if (request('toolbox_type') == 'scratch') {
            $tool_request['master_id'] = null;
            $tool_request['version'] = '1.0';
        } elseif (request('toolbox_type') == 'previous')
            $tool_request['master_id'] = request('previous_id');
        else
            $tool_request['master_id'] = request('master_id');

        $tool_request['company_id'] = ($request->has('parent_switch')) ? Auth::user()->company->reportsTo()->id : Auth::user()->company_id;
        $tool_request['for_company_id'] = Auth::user()->company_id;
        $tool_request['status'] = '2';  // Draft

        // Create Toolbox
        $newTalk = ToolboxTalk::create($tool_request);

        // Copy Steps / Hazards / Controls from Master Template
        if (request('toolbox_type') != 'scratch')
            $this->copyTemplate($tool_request['master_id'], $newTalk->id);

        Toastr::success("Created new talk");

        return redirect('/safety/doc/toolbox3/' . $newTalk->id . '/edit');
    }

    /**
     * Edit the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $talk = ToolboxTalk::findOrFail($id);

        /// Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('edit.toolbox', $talk))
            return view('errors/404');

        // Draft / Pending mode
        if (in_array($talk->status, [2, 3]))
            return view('safety/doc/toolbox3/edit', compact('talk'));

        return redirect('/safety/doc/toolbox3/' . $talk->id);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ToolboxRequest $request, $id)
    {
        $talk = ToolboxTalk::findOrFail($id);
        $tool_request = request()->all();
        //dd($tool_request);

        //
        // Editing when in Draft Mode - Ajax
        //
        if (request('draft') == 'save') {
            // Editing Talk Name / Info


            // Calculate if any differences in previous version of talk
            $diff_overview = Diff2::toTable(Diff2::compare($talk->overview, request('overview') . "\n"));
            $diff_hazards = Diff2::toTable(Diff2::compare($talk->hazards, request('hazards') . "\n"));
            $diff_controls = Diff2::toTable(Diff2::compare($talk->controls, request('controls') . "\n"));
            $diff_further = Diff2::toTable(Diff2::compare($talk->further, request('further') . "\n"));
            $mod_overview = preg_match('/diffDeleted|diffInserted|diffBlank/', $diff_overview);
            $mod_hazards = preg_match('/diffDeleted|diffInserted|diffBlank/', $diff_hazards);
            $mod_controls = preg_match('/diffDeleted|diffInserted|diffBlank/', $diff_controls);
            $mod_further = preg_match('/diffDeleted|diffInserted|diffBlank/', $diff_further);

            // Increment minor version if has been modified
            if ($talk->name != request('name') || $talk->status != request('status') || $mod_overview || $mod_hazards || $mod_controls || $mod_further) {
                // Talk modified so increment version
                if ($talk->name != request('name') || $mod_overview || $mod_hazards || $mod_controls || $mod_further) {
                    list($major, $minor) = explode('.', $talk->version);
                    $minor++;
                    $tool_request['version'] = $major . '.' . $minor;
                }

                // Force Cape Cod Staff to get Sign Off if talk isn't exact copy of a master template
                $master_version = 0;
                if ($talk->master_id) {
                    $master = ToolboxTalk::find($talk->master_id);
                    if ($master)
                        $master_version = $master->version;
                }
                /*
                 * Comments out requiring CC Supervisors to get signoff before they give a talk
                 *
                if (request('status') == 1 && Auth::user()->isCC() && !Auth::user()->hasPermission2('sig.toolbox') && (!$talk->master_id || $master_version != $tool_request['version'])) {
                    $tool_request['status'] = 3;
                    // Mail notification talk owner
                    if ($talk->owned_by->notificationsUsersType('doc.whs.approval'))
                        Mail::to($talk->owned_by->notificationsUsersType('doc.whs.approval'))->send(new \App\Mail\Safety\ToolboxTalkSignoff($talk));

                    Toastr::warning("Requesting Sign Off");
                }*/
                $talk->update($tool_request);

                // If regular talk is made active + copy of template determine if template was modified
                if (request('status') == 1 && !$talk->master && $talk->master_id && $master_version != $tool_request['version']) {
                    $diffs = '';
                    if ($mod_overview) $diffs .= "OVERVIEW<br>$diff_overview<br>";
                    if ($mod_hazards) $diffs .= "HAZARDS<br>$diff_hazards<br>";
                    if ($mod_controls) $diffs .= "CONTROLS<br>$diff_controls<br>";
                    if ($mod_further) $diffs .= "FURTHER INFOMATION<br>$diff_further<br>";
                    // Mail notification talk owner
                    //$talk->emailModifiedTemplate($diffs);
                    //if ($talk->owned_by->notificationsUsersType('doc.whs.approval'))
                    //    Mail::to($talk->owned_by->notificationsUsersType('doc.whs.approval'))->send(new \App\Mail\Safety\ToolboxTalkModifiedTemplate($talk, $diffs));
                }

                // If toolbox template is made Active email activeTemplate
                if (request('status') == 1 && $talk->master)
                    $talk->emailActiveTemplate();


                Toastr::success("Saved changes");
            } else
                Toastr::warning("Nothing was changed");

            //return response()->json(['success' => true, 'message' => 'Your AJAX processed correctly']);
            return redirect("safety/doc/toolbox3/$talk->id/edit");
        } else {
            //
            // Edit Active Toolbox with Users / Status
            //
            $todo_request = [
                'type' => 'toolbox',
                'type_id' => $id,
                'name' => 'Toolbox Talk - ' . request('name'),
                'info' => 'Please acknowledge you have read and understood the toolbox talk.',
                'due_at' => nextWorkDate(Carbon::today(), '+', 5)->toDateTimeString(),
                'company_id' => request('for_company_id')
            ];

            $assign_list = [];
            // Users
            $user_list = (request('user_list')) ? request('user_list') : [];
            foreach ($user_list as $id) {
                if ($id == 'all') {
                    $assign_list = Auth::user()->company->users('1')->pluck('id')->toArray();
                    break;
                } else
                    $assign_list[] = $id;
            }
            // Companies
            $company_list = (request('company_list')) ? request('company_list') : [];
            $company_list = (in_array('all', $company_list)) ? Auth::user()->company->companies(1)->pluck('id')->toArray() : $company_list;
            foreach ($company_list as $id) {
                $company = Company::findOrFail($id);
                $assign_list = array_merge($assign_list, $company->staffStatus(1)->pluck('id')->toArray());
            }
            // Roles
            $role_list = (request('role_list')) ? request('role_list') : [];
            $users = DB::table('role_user')->select('user_id')->whereIn('role_id', $role_list)->distinct('user_id')->orderBy('user_id')->get();
            $company_users = Auth::user()->company->users(1)->pluck('id')->toArray();
            foreach ($users as $u) {
                if (in_array($u->user_id, $company_users))
                    $assign_list[] = $u->user_id;
            }
            // Specials
            $special_list = (request('special_list')) ? request('special_list') : [];
            foreach ($special_list as $special) {
                if ($special == 'primary_contact') {
                    $company_list = Company::where('status', 1)->pluck('id')->toArray();
                    foreach ($company_list as $id) {
                        $company = Company::findOrFail($id);
                        $assign_list = array_merge($assign_list, [$company->primary_contact]);
                    }
                }
                if ($special == 'supply_fit') {
                    $company_list = Company::where('status', 1)->where('business_entity', 4)->pluck('id')->toArray();
                    foreach ($company_list as $id) {
                        $company = Company::findOrFail($id);
                        $assign_list = array_merge($assign_list, $company->staffStatus(1)->pluck('id')->toArray());
                    }
                }
                if ($special == 'supply') {
                    $company_list = Company::where('status', 1)->where('business_entity', 5)->pluck('id')->toArray();
                    foreach ($company_list as $id) {
                        $company = Company::findOrFail($id);
                        $assign_list = array_merge($assign_list, $company->staffStatus(1)->pluck('id')->toArray());
                    }
                }
            }
            //dd($assign_list);

            // Create ToDoo for user if they haven't got one
            $current_users = ($talk->assignedTo()) ? $talk->assignedTo()->pluck('id')->toArray() : [];
            $new_users = [];
            foreach ($assign_list as $user_id) {
                if (!in_array($user_id, $current_users)) {
                    $new_users[] = $user_id;
                    $todo = Todo::create($todo_request);
                    $todo->assignUsers($user_id);
                    $todo->emailToDo();
                }
            }

            // Delete user ToDoo task for Toolbox talk if they haven't already completed
            /*$del_users = [];
            foreach ($current_users as $user_id) {
                if (!in_array($user_id, $assign_list)) {
                    $todo_toolboxs = Todo::where('type', 'toolbox')->where('type_id', $talk->id)->get();
                    foreach ($todo_toolboxs as $todo) {
                        if ($todo->status) {
                            $todo_user = TodoUser::where('todo_id', $todo->id)->where('user_id', $user_id)->first();
                            if ($todo_user) {
                                $del_users[] = $user_id;
                                $todo_user->delete();
                                $todo->delete();
                                $user = User::find($user_id);
                                Toastr::error("Removed $user->fullname");
                            }
                        }
                    }
                }
            }*/
            Toastr::success("Assigned users");

            // Send Email of Added / Deleted Users
            $added_users = [];
            foreach ($new_users as $user_id) {
                $user = User::find($user_id);
                if ($user)
                    $added_users[$user->fullname . " (" . $user->company->name . ")"] = $user->company->name;
            }
            asort($added_users);

            $deleted_users = [];
            /*foreach ($del_users as $user_id) {
                $user = User::find($user_id);
                if ($user)
                    $deleted_users[$user->fullname . " (" . $user->company->name . ")"] = $user->company->name;
            }
            asort($deleted_users);
            */

            Mail::to($talk->createdBy)->send(new \App\Mail\Safety\ToolboxTalkUsers($talk, $added_users, $deleted_users));


            return redirect('safety/doc/toolbox3/' . $talk->id);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function updateUsers(ToolboxRequest $request, $id)
    {
        if ($request->ajax()) {
            $talk = ToolboxTalk::findOrFail($id);
            $tool_request = ($request->all());

            // Increment minor version
            list($major, $minor) = explode('.', $talk->version);
            $minor++;
            $tool_request['version'] = $major . '.' . $minor;

            $talk->update($request->all());

            Toastr::success("Saved changes");

            return response()->json(['success' => true, 'message' => 'Your AJAX processed correctly']);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function uploadMedia(Request $request, $id)
    {
        $talk = ToolboxTalk::findOrFail($id);
        $tool_request = ($request->all());

        // Handle attached file
        if ($request->hasFile('singlefile')) {
            $file = $request->file('singlefile');

            $path = "filebank/whs/toolbox/" . $talk->id;
            $name = sanitizeFilename(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . strtolower($file->getClientOriginalExtension());
            // Ensure filename is unique by adding counter to similiar filenames
            $count = 1;
            while (file_exists(public_path("$path/$name")))
                $name = sanitizeFilename(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '-' . $count++ . '.' . strtolower($file->getClientOriginalExtension());
            $file->move($path, $name);
        }
        Toastr::success("Saved changes");

        return redirect('/safety/doc/toolbox3/' . $talk->id . '/edit');
    }


    /**
     * Accept talk as read for given users .
     */
    public function accept($id)
    {
        $talk = ToolboxTalk::findOrFail($id);

        /// Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('view.toolbox', $talk))
            return view('errors/404');

        if ($talk->status == 1) {
            $talk->markAccepted(Auth::user());
            Toastr::success("Toolbox accepted");
        }

        return redirect('/safety/doc/toolbox3/' . $talk->id);
    }

    /**
     * Archive or Unarchive the given talk.
     */
    public function archive($id)
    {
        $talk = ToolboxTalk::findOrFail($id);
        if (!Auth::user()->allowed2('del.toolbox', $talk))
            return view('errors/404');

        $talk->status = ($talk->status == 1) ? '0' : '1';
        $talk->save();

        if ($talk->status == 1) {
            Toastr::success("Toolbox restored");

            // Re-open user ToDoo task for Toolbox talk if they haven't already completed
            $undone = Todo::where('type', 'toolbox')->where('type_id', $talk->id)->where('done_by', 0)->get();
            foreach ($undone as $todo) {
                $todo->status = 1;
                $todo->save();
            }
        } else {
            //$talk->emailArchived();
            Toastr::success("Toolbox archived");

            // Close user ToDoo task for Toolbox talk if they haven't already completed
            $undone = Todo::where('type', 'toolbox')->where('type_id', $talk->id)->where('status', 1)->get();
            foreach ($undone as $todo) {
                $todo->status = 0;
                $todo->save();
            }
        }

        return redirect('/safety/doc/toolbox3/' . $talk->id);
    }

    public function deluser($id, $uid)
    {
        $talk = ToolboxTalk::findOrFail($id);
        if (!Auth::user()->allowed2('del.toolbox', $talk))
            return view('errors/404');

        $todo_toolboxs = Todo::where('type', 'toolbox')->where('type_id', $talk->id)->get();
        foreach ($todo_toolboxs as $todo) {
            if ($todo->status) {
                $todo_user = TodoUser::where('todo_id', $todo->id)->where('user_id', $uid)->first();
                if ($todo_user) {
                    $todo_user->delete();
                    $todo->delete();
                    $user = User::find($uid);
                    Toastr::error("Removed $user->fullname");
                }
            }
        }
        return redirect("/safety/doc/toolbox3/$id");
    }

    public function reminder($id)
    {
        $talk = ToolboxTalk::findOrFail($id);
        if (!Auth::user()->allowed2('del.toolbox', $talk))
            return view('errors/404');

        $todo_toolboxs = Todo::where('type', 'toolbox')->where('type_id', $talk->id)->get();
        foreach ($todo_toolboxs as $todo) {
            if ($todo->status)
                $todo->emailToDoReminder();
        }
        Toastr::error("Emailed outstanding");

        return redirect("/safety/doc/toolbox3/$id");
    }

    /**
     * Delete the specified resource in storage.
     */
    public function destroy(Request $request, $id)
    {
        $talk = ToolboxTalk::findOrFail($id);
        if (!Auth::user()->allowed2('del.toolbox', $talk))
            return view('errors/404');

        $talk->delete();
        Toastr::error("Toolbox deleted");

        if ($request->ajax())
            return json_encode('success');
        else
            return redirect('/safety/doc/toolbox3');
    }

    /**
     * Sign off on the given talk.
     */
    public function signoff($id)
    {
        $talk = ToolboxTalk::findOrFail($id);
        if (!Auth::user()->allowed2('sig.toolbox', $talk))
            return view('errors/404');

        $talk->authorised_by = Auth::user()->id;
        $talk->authorised_at = Carbon::now();
        $talk->status = '1';
        $talk->save();
        if (validEmail($talk->createdBy->email))
            Mail::to($talk->createdBy)->send(new \App\Mail\Safety\ToolboxTalkApproved($talk));
        Toastr::success("Talk signed off");

        return redirect('/safety/doc/toolbox3/' . $talk->id);
    }

    /**
     * Reject the given talk and return it to draft.
     */
    public function reject($id)
    {
        $talk = ToolboxTalk::findOrFail($id);
        if (!Auth::user()->allowed2('sig.toolbox', $talk))
            return view('errors/404');
        $talk->status = '2';
        $talk->save();
        // Mail notification talk creator + cc: talk owner
        if (validEmail($talk->createdBy->email) && $talk->owned_by->notificationsUsersType('doc.whs.approval'))
            Mail::to($talk->createdBy)->cc($talk->owned_by->notificationsUsersType('doc.whs.approval'))->send(new \App\Mail\Safety\ToolboxTalkRejected($talk));
        elseif (validEmail($talk->createdBy->email))
            Mail::to($talk->createdBy)->send(new \App\Mail\Safety\ToolboxTalkRejected($talk));
        Toastr::error("Rejected sign off");

        return redirect('/safety/doc/toolbox3/' . $talk->id);
    }

    public function createPDF($id)
    {
        $talk = ToolboxTalk::findOrFail($id);

        // Set + create directory if required
        $path = "filebank/whs/toolbox/$talk->id";
        if (!file_exists($path)) mkdir($path, 0777, true);
        $filename = "$talk->name.pdf";

        //
        // Generate PDF
        //
        //return view('pdf/toolboxtalk', compact('talk'));
        return PDF::loadView('pdf/toolboxtalk', compact('talk'))->setPaper('a4')->stream();
        $pdf = PDF::loadView('pdf/toolboxtalk', compact('talk'));
        $pdf->setPaper('A4');
        $pdf->save(public_path("$path/$filename"));

        return $filename;
    }


    /**
     * Get Talks current user is authorised to manage + Process datatables ajax request.
     */
    public function getToolbox(Request $request)
    {
        // Toolboxs assigned to user
        $toolbox_user = Auth::user()->toolboxs()->pluck('id')->toArray();

        // Company IDs of Toolboxs user is allowed to view
        // ie. User can view Toolboxs owned by their company or parent company if they have access to view 'All'
        $company_ids = [];
        if (Auth::user()->permissionLevel('view.toolbox', Auth::user()->company_id) == 99)
            $company_ids[] = Auth::user()->company_id;
        if (Auth::user()->permissionLevel('view.toolbox', Auth::user()->company->reportsTo()->id) == 99)
            $company_ids[] = Auth::user()->company->reportsTo()->id;

        // For Company IDs of Toolboxs user is allowed to view
        // ie. User can view Toolboxs owned by their Parent but they have access to only view 'Own Company'
        //     unless Child company has subscription then child company permission overides
        $for_company_ids = [];
        if (!Auth::user()->company->subscription && Auth::user()->permissionLevel('view.toolbox', Auth::user()->company->reportsTo()->id) == 20)
            $for_company_ids[] = Auth::user()->company_id;

        $records = DB::table('toolbox_talks AS t')
            ->select(['t.id', 't.name', 't.version', 't.for_company_id', 't.company_id', 't.status', 't.updated_at', 'c.name AS company_name'])
            ->join('companys AS c', 't.company_id', '=', 'c.id')
            ->where(function ($q) use ($toolbox_user, $for_company_ids, $company_ids) {
                $q->whereIn('t.id', $toolbox_user);
                $q->orWhereIn('company_id', $company_ids);
                $q->orWhereIn('for_company_id', $for_company_ids);
            })
            ->where('t.master', '0')
            ->where('t.status', $request->get('status'));

        $dt = Datatables::of($records)
            ->editColumn('name', function ($doc) {
                $talk = ToolboxTalk::find($doc->id);
                if ($talk->userRequiredToRead(Auth::user()))
                    return '<span class="font-red">' . $doc->name . ' (v' . $doc->version . ')</span>';

                return $doc->name . ' (v' . $doc->version . ')';
            })
            ->editColumn('company_name', function ($doc) {
                $company = Company::find($doc->for_company_id);

                return $company->name_alias;
            })
            ->editColumn('updated_at', function ($doc) {
                return (new Carbon($doc->updated_at))->format('d/m/Y');
            })
            ->addColumn('completed', function ($doc) {
                $talk = ToolboxTalk::find($doc->id);
                if (!in_array($talk->status, [2, 3])) { // Exclude Draft+Pending as Only Active or Archived talks have completed items
                    if (Auth::user()->allowed2('edit.toolbox', $talk)) {
                        $assigned_count = ($talk->assignedTo()) ? $talk->assignedTo()->count() : 0;
                        $completed_count = ($talk->completedBy()) ? $talk->completedBy()->count() : 0;
                        $label_type = ($assigned_count == $completed_count && $assigned_count != 0) ? 'label-success' : 'label-danger';

                        return '<span class="label pull-right ' . $label_type . '">' . $completed_count . ' / ' . $assigned_count . '</span>';
                    } else {
                        if ($talk->userRequiredToRead(Auth::user()))
                            return '<span class="label pull-right label-danger"> Outstanding</span>';
                        if ($talk->userCompleted(Auth::user()))
                            return $talk->userCompleted(Auth::user())->format('d/m/Y');
                    }
                }

                return '';
            })
            ->addColumn('action', function ($doc) {
                $actions = '';
                if ($doc->status == '2' && Auth::user()->allowed2('edit.toolbox', $doc))  // Draft mode
                    $actions .= '<a href="/safety/doc/toolbox3/' . $doc->id . '/edit" class="btn blue btn-xs btn-outline sbold uppercase margin-bottom"><i class="fa fa-pencil"></i> Edit</a>';
                else
                    $actions .= '<a href="/safety/doc/toolbox3/' . $doc->id . '" class="btn blue btn-xs btn-outline sbold uppercase margin-bottom"><i class="fa fa-search"></i> View</a>';

                if (in_array($doc->status, [2, 3]) && Auth::user()->allowed2('del.toolbox', $doc))  // Draft or Pending mode
                    $actions .= '<button class="btn dark btn-xs sbold uppercase margin-bottom btn-delete " data-remote="/safety/doc/toolbox3/' . $doc->id . '" data-name="' . $doc->name . '"><i class="fa fa-trash"></i></button>';

                return $actions;
            })
            ->rawColumns(['id', 'name', 'completed', 'action'])
            ->make(true);

        return $dt;
    }

    /**
     * Get Talks current user is authorised to manage + Process datatables ajax request.
     */
    public function getToolboxTemplates(Request $request)
    {
        $records = DB::table('toolbox_talks AS t')
            ->select(['t.id', 't.name', 't.version', 't.for_company_id', 't.company_id', 't.status', 't.updated_at', 'c.name AS company_name'])
            ->join('companys AS c', 't.company_id', '=', 'c.id')
            /*->where(function ($q) {
                $q->where('t.for_company_id', 3);
                $q->orWhere('t.company_id', Auth::user()->company_id);
                $q->orWhere('t.company_id', Auth::user()->company->reportsTo()->id);
            })*/
            ->where('t.company_id', 3)
            ->where('t.master', '1')
            ->where('t.status', $request->get('status'));

        $dt = Datatables::of($records)
            ->editColumn('name', function ($doc) {
                return $doc->name . ' (v' . $doc->version . ')';
            })
            ->editColumn('company_name', function ($doc) {
                $company = Company::find($doc->for_company_id);

                return $company->name_alias;
            })
            ->editColumn('updated_at', function ($doc) {
                return (new Carbon($doc->updated_at))->format('d/m/Y');
            })
            ->addColumn('action', function ($doc) {
                $actions = '';
                if ($doc->status == '2' && Auth::user()->allowed2('edit.toolbox', $doc)) // Draft mode
                    $actions .= '<a href="/safety/doc/toolbox3/' . $doc->id . '/edit" class="btn blue btn-xs btn-outline sbold uppercase margin-bottom"><i class="fa fa-pencil"></i> Edit</a>';
                else
                    $actions .= '<a href="/safety/doc/toolbox3/' . $doc->id . '" class="btn blue btn-xs btn-outline sbold uppercase margin-bottom"><i class="fa fa-search"></i> View</a>';

                if (in_array($doc->status, [2, 3]) && Auth::user()->allowed2('del.toolbox', $doc)) // Draft or Pending mode
                    $actions .= '<button class="btn dark btn-xs sbold uppercase margin-bottom btn-delete " data-remote="/safety/doc/toolbox3/' . $doc->id . '" data-name="' . $doc->name . '"><i class="fa fa-trash"></i></button>';

                return $actions;

            })
            ->rawColumns(['id', 'name', 'completed', 'action'])
            ->make(true);

        return $dt;
    }


    /**
     * Copy Template from Master
     */
    private function copyTemplate($master_id, $talk_id)
    {
        $master = ToolboxTalk::find($master_id);
        $talk = ToolboxTalk::find($talk_id);

        // Increment major version if copying from previous Talk or new talk is a Master Template
        if (!$master->master || $talk->master) {
            list($major, $minor) = explode('.', $master->version);
            $major++;
            $talk->version = $major . '.0';
        } else
            $talk->version = $master->version;

        $talk->overview = $master->overview;
        $talk->hazards = $master->hazards;
        $talk->controls = $master->controls;
        $talk->further = $master->further;
        $talk->save();
    }

    public function diffArray($old, $new)
    {
        $matrix = array();
        $maxlen = 0;
        foreach ($old as $oindex => $ovalue) {
            $nkeys = array_keys($new, $ovalue);
            foreach ($nkeys as $nindex) {
                $matrix[$oindex][$nindex] = isset($matrix[$oindex - 1][$nindex - 1]) ? $matrix[$oindex - 1][$nindex - 1] + 1 : 1;
                if ($matrix[$oindex][$nindex] > $maxlen) {
                    $maxlen = $matrix[$oindex][$nindex];
                    $omax = $oindex + 1 - $maxlen;
                    $nmax = $nindex + 1 - $maxlen;
                }
            }
        }
        if ($maxlen == 0) return array(array('d' => $old, 'i' => $new));

        return array_merge(
            $this->diffArray(array_slice($old, 0, $omax), array_slice($new, 0, $nmax)),
            array_slice($new, $nmax, $maxlen),
            $this->diffArray(array_slice($old, $omax + $maxlen), array_slice($new, $nmax + $maxlen)));
    }

    public function htmlDiff($old, $new)
    {
        $ret = '';
        $diff = $this->diffArray(explode(' ', $old), explode(' ', $new));
        foreach ($diff as $k) {
            if (is_array($k)) {
                $ret .= (!empty($k['d']) ? '<del>' . implode(' ', $k['d']) . '</del> ' : '') . (!empty($k['i']) ? '<ins>' . implode(' ', $k['i']) . '</ins> ' : '');
            } else {
                $ret .= $k . ' ';
            }
        }

        return $ret;
    }
}
