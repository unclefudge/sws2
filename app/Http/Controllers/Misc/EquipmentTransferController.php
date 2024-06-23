<?php

namespace App\Http\Controllers\Misc;

use Alert;
use App\Http\Controllers\Controller;
use App\Models\Comms\Todo;
use App\Models\Misc\Equipment\Equipment;
use App\Models\Misc\Equipment\EquipmentLocation;
use App\Models\Misc\Equipment\EquipmentLocationItem;
use App\Models\Misc\Equipment\EquipmentLocationOther;
use App\Models\Misc\Equipment\EquipmentLog;
use App\Models\Misc\Equipment\EquipmentLost;
use App\Models\Site\Site;
use Carbon\Carbon;
use DB;
use Illuminate\Support\Facades\Auth;
use Mail;
use nilsenj\Toastr\Facades\Toastr;
use Session;
use Validator;
use Yajra\Datatables\Datatables;

class EquipmentTransferController extends Controller
{


    /**
     * Transfer the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function transfer($id)
    {
        $item = EquipmentLocationItem::findOrFail($id);

        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('edit.equipment', $item))
            return view('errors/404');

        return view('misc/equipment/transfer', compact('item'));
    }

    /**
     * Transfer the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function transferBulk($id)
    {
        $location = EquipmentLocation::find($id);

        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('edit.equipment.stocktake', $location))
            return view('errors/404');

        foreach (EquipmentLocation::where('status', 1)->where('notes', null)->where('site_id', '<>', '25')->get() as $loc) {
            if ($loc->items->count())
                $sites[$loc->name] = $loc->id;
        }
        //$sites[$loc->id] = $loc->name;

        ksort($sites);
        $sites = ['CAPE COD STORE' => '1'] + $sites;
        $supers = [];
        $users = [];
        $others = [];
        $misc = [];


        $all_supers = Auth::user()->company->reportsTo()->supervisors()->pluck('name')->toArray();
        $all_users = Auth::user()->company->reportsTo()->onsiteUsers('1')->pluck('name')->toArray();
        $all_others = EquipmentLocationOther::where('status', 1)->pluck('name')->toArray();


        foreach (EquipmentLocation::where('status', 1)->where('notes', null)->where('site_id', null)->get() as $loc) {
            if ($loc->items->count()) {
                if (in_array($loc->name, $all_supers))
                    $supers[$loc->name] = $loc->id;
                elseif (in_array($loc->name, $all_users))
                    $users[$loc->name] = $loc->id;
                elseif (in_array($loc->name, $all_others))
                    $others[$loc->name] = $loc->id;
                else
                    $misc[$loc->name] = $loc->id;
            }
            $others2[$loc->id] = $loc->name;
        }
        ksort($supers);
        ksort($users);
        ksort($others);
        ksort($misc);

        //var_dump($supers);
        //var_dump($users);
        //var_dump($others);
        //var_dump($misc);


        $items = [];
        if ($location) {
            // Get items then filter out 'deleted'
            $all_items = EquipmentLocationItem::where('location_id', $location->id)->get();
            $items = $all_items->filter(function ($item) {
                if ($item->equipment->status) return $item;
            });
        }

        return view('misc/equipment/transfer-bulk', compact('location', 'sites', 'supers', 'users', 'others', 'others2', 'misc', 'items'));
    }

    /**
     * Task the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function verifyTransfer($id)
    {
        $location = EquipmentLocation::findOrFail($id);
        list ($trans, $todo_id,) = explode(':', $location->other);
        $todo = Todo::findOrFail($todo_id);

        // Check authorisation and throw 404 if not
        // User allowed to edit the equipment or has a ToDoo assigned to them for transfer
        if (!(Auth::user()->allowed2('edit.equipment', $location) || in_array(Auth::user()->id, $todo->assignedTo()->pluck('id')->toArray())))
            return view('errors/404');

        list ($from_id, $type, $details, $user) = explode(':', $location->notes);
        $from_location = EquipmentLocation::find($from_id);
        $from = ($from_location->site_id) ? $from_location->site->address . ', ' . $from_location->site->suburb . ' (' . $from_location->site->name . ')' : $from_location->other;
        if ($type == 'site') {
            $site = Site::find($details);
            $to = "$site->address, $site->suburb ($site->name)";
        } else
            $to = $details;

        return view('misc/equipment/transfer-verify', compact('location', 'from', 'to'));
    }


    /**
     * Transfer the item to new location.
     *
     * @return \Illuminate\Http\Response
     */
    public function transferItem($id)
    {
        $item = EquipmentLocationItem::findOrFail($id);

        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('edit.equipment', $item))
            return view('errors/404');

        $rules = ['type' => 'required', 'site_id' => 'required_if:type,site', 'other' => 'required_if:type,other', 'reason' => 'required_if:type,dispose'];
        $mesg = [
            'type.required' => 'The transfer to field is required',
            'site.required_if' => 'The site field is required',
            'other.required_if' => 'The other location field is required',
            'reason.required_if' => 'The reason field is required',
        ];
        request()->validate($rules, $mesg); // Validate

        //dd(request()->all());
        $old_location = $item->location->name;
        $qty = request('qty');
        $site_id = (request('type') == "store" || request('type') == "site") ? request('site_id') : null;
        $other = null;
        if (in_array(request('type'), ['other', 'super', 'user'])) {
            if (request('type') == "other")
                $other = request('type');
            if (request('type') == "super")
                $other = request('super');
            if (request('type') == "user")
                $other = request('user');
        }

        // Move items to New location
        if (request('type') == "dispose") { // Dispose
            $item->equipment->disposed = $item->equipment->disposed + $qty;
            $item->equipment->save();
            $log = new EquipmentLog(['equipment_id' => $item->equipment_id, 'qty' => $qty, 'action' => 'D', 'notes' => "Disposed $qty items from $old_location - " . request('reason')]);
            $log->save();
            $this->subtractItems($item, $qty);
        } else {
            // Verify not transfer to/from aren't same location
            if (($item->location->site_id && $item->location->site_id == $site_id) || ($item->location->other && $item->location->other == $other))
                return back()->withErrors(['same' => "The destination location can't be the same as the originating."]);

            if (request('assign'))
                $this->assignTransfer($item, $qty, $site_id, $other, request('assign'), request('due_at')); // Assign transfer to user
            else
                $this->performTransfer($item, $qty, $site_id, $other);  // Transfer items now
        }

        Toastr::success("Saved changes");

        return redirect("/equipment/");
    }

    /**
     * Transfer Bulk items to new location.
     *
     * @return \Illuminate\Http\Response
     */
    public function transferBulkItems($id)
    {
        $rules = ['location_id' => 'required', 'type' => 'required', 'site_id' => 'required_if:type,site', 'other' => 'required_if:type,other', 'reason' => 'required_if:type,dispose'];
        $mesg = [
            'type.required' => 'The transfer to field is required',
            'site_id.required_if' => 'The site field is required',
            'other.required_if' => 'The other location field is required',
            'reason.required_if' => 'The reason field is required',
        ];
        request()->validate($rules, $mesg); // Validate


        $location = EquipmentLocation::findOrFail($id);

        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('edit.equipment', $location))
            return view('errors/404');


        $site_id = (request('type') == "store" || request('type') == "site") ? request('site_id') : null;
        $other = null;
        if (in_array(request('type'), ['other', 'super', 'user'])) {
            if (request('type') == "other")
                $other = request('type');
            if (request('type') == "super")
                $other = request('super');
            if (request('type') == "user")
                $other = request('user');
        }

        // Verify not transfer to/from aren't same location
        if ($site_id && $location->site_id == $site_id)
            return back()->withErrors(['samelocation' => "The From and To locations can't be the same site"]);
        elseif ($other && $location->other == $other)
            return back()->withErrors(['samelocation' => "The From and To locations can't be the same"]);

        // Get items then filter out 'deleted'
        $all_items = EquipmentLocationItem::where('location_id', $location->id)->get();
        $items = $all_items->filter(function ($item) {
            if ($item->equipment->status) return $item;
        });

        //dd(request()->all());
        // Get Qty to transfer for each item at location
        foreach ($items as $item) {
            $qty = request($item->id . '-qty');
            if ($qty) {
                if (request('assign'))
                    $this->assignTransfer($item, $qty, $site_id, $other, request('assign'), request('due_at')); // Assign transfer to user
                else
                    $this->performTransfer($item, $qty, $site_id, $other);  // Transfer items now
            }
        }

        Toastr::success("Saved changes");

        return redirect("/equipment/");
    }

    /**
     * Transfer the item to new location.
     *
     */
    public function confirmTransfer($id)
    {
        $transLocation = EquipmentLocation::findOrFail($id);
        list ($trans, $todo_id,) = explode(':', $transLocation->other);
        $todo = Todo::findOrFail($todo_id);

        // Check authorisation and throw 404 if not
        // User allowed to edit the equipment or has a ToDoo assigned to them for transfer
        if (!(Auth::user()->allowed2('edit.equipment', $transLocation) || in_array(Auth::user()->id, $todo->assignedTo()->pluck('id')->toArray())))
            return view('errors/404');

        list($crap, $todo_id) = explode(':', $transLocation->other);
        list($location_id, $type, $details, $user_id) = explode(':', $transLocation->notes);

        $location = EquipmentLocation::findOrFail($location_id);


        $site_id = ($type == 'site') ? $details : null;
        $other = ($type == 'other') ? $details : null;

        // Check if current qty matches DB
        foreach ($transLocation->items as $transItem) {
            $qty = request($transItem->id . '-qty');
            echo "checking $location->name: $transItem->id-qty [$qty]<br>";
            $item = EquipmentLocationItem::where('location_id', $location->id)->where('equipment_id', $transItem->equipment_id)->first();
            if ($item)
                $this->performTransfer($item, $qty, $site_id, $other);

            /*
            if ($item->qty > $qty) {
                // There were less items found at location then expected so
                // check if 'extra' items are elsewhere and any none 'extra' mark them as missing
                if (($item->qty - $qty) > $item->equipment->total_excess)
                    $this->lostItem($item->location_id, $item->equipment_id, ($item->qty - $qty - $item->equipment->total_excess));
            }*/
        }
        $transLocation->status = 0;
        $transLocation->save();

        $todo = Todo::find($todo_id);
        $todo->done_by = Auth::user()->id;
        $todo->done_at = Carbon::now();
        $todo->status = 0;
        $todo->save();
        Toastr::success("Transfer completed");

        return redirect("/todo/$todo->id");
    }


    /**
     * Perform actual transfer of item to new locations
     */
    public function performTransfer($item, $qty, $site_id, $other)
    {
        $old_location = $item->location->name;
        $log = new EquipmentLog(['equipment_id' => $item->equipment_id, 'qty' => $qty, 'action' => 'T']);

        if ($site_id) { //  Site
            $site = Site::find($site_id);
            $log->notes = "Transferred $qty items from $old_location => $site->suburb ($site->name)";
            $location = EquipmentLocation::where('site_id', $site_id)->first();
        } else {  // Other
            $log->notes = "Transferred $qty items from $old_location => $other";
            $location = EquipmentLocation::where('other', $other)->where('status', 1)->first();
        }
        $log->save();

        // If Category 3 'Materials' don't transfer but simply subtract items EXCEPT when transferring to Store (site_id 25)
        if ($item->equipment->parent_category != 3 || $site_id == 25) {
            // Check if location exists
            if ($location) {
                // Check if location also has existing item to add qty to.
                $existing = EquipmentLocationItem::where('location_id', $location->id)->where('equipment_id', $item->equipment_id)->first();
                if ($existing) {
                    $existing->qty = $existing->qty + $qty;
                    $existing->save();
                } else
                    $location->items()->save(new EquipmentLocationItem(['location_id' => $location->id, 'equipment_id' => $item->equipment_id, 'qty' => $qty]));
            } else {
                // Create location + add item
                $loc_request = ['site_id' => $site_id, 'other' => $other];
                $newLocation = EquipmentLocation::create($loc_request);
                $newLocation->items()->save(new EquipmentLocationItem(['location_id' => $newLocation->id, 'equipment_id' => $item->equipment_id, 'qty' => $qty]));
            }
        }
        $this->subtractItems($item, $qty); // Subtract items from original location

        if (!($item->equipment->parent_category != 3 || $site_id == 25)) {
            // Email Notification of Material transfer
            if ($site_id) {
                $site = Site::find($site_id);
                app('log')->debug("========= Equipment Transfer ==========");
                app('log')->debug("UserID: " . Auth::user()->id);
                app('log')->debug("Site: "$site->name);
                app('log')->debug($item);
                app('log')->debug("Qty: $qty");
                Mail::to("construct@capecod.com.au")->send(new \App\Mail\Misc\EquipmentMaterialTransfer($site, $item, $qty));
            }
        }
    }

    /**
     * Assign transfer of items to user
     */
    public function assignTransfer($item, $qty, $site_id, $other, $assign, $due_at)
    {
        //
        //  Transfer is assigned to be done by a User
        //
        $old_site = ($item->location->site_id) ? Site::find($item->location->site_id) : null;
        $old_location_full = ($old_site) ? "$old_site->address, $old_site->suburb ($old_site->name)" : $item->location->name;
        $old_location = ($old_site) ? "$old_site->suburb ($old_site->name)" : $item->location->name;
        $new_site = ($site_id) ? Site::find($site_id) : null;
        $new_location_full = ($new_site) ? "$new_site->address, $new_site->suburb ($new_site->name)" : $other;
        $new_location = ($new_site) ? "$new_site->suburb ($new_site->name)" : $other;

        // Determine if user is already transferring other items from & to same locations
        $location_code = ($site_id) ? "$item->location_id:site:$site_id" : "$item->location_id:other:$other";
        //$location = EquipmentLocation::where('notes', "$location_code:$assign")->first();
        $locations = EquipmentLocation::where('notes', "$location_code:$assign")->get();
        if ($locations) {
            $location = null;
            foreach ($locations as $loc) {
                $todo = Todo::where('type', 'equipment')->where('type_id', $loc->id)->where('status', 1)->first();
                if ($todo) {
                    $location = $loc;
                    break;
                }
            }
        }
        if ($location) {
            //
            // Append new item to current user ToDoo
            //
            list ($crap, $todo_id) = explode(':', $location->other);

            // Check if location also has existing item to add qty to.
            $existing = EquipmentLocationItem::where('location_id', $location->id)->where('equipment_id', $item->equipment_id)->first();
            if ($existing) {
                $existing->qty = $existing->qty + $qty;
                $existing->save();
            } else
                $location->items()->save(new EquipmentLocationItem(['location_id' => $location->id, 'equipment_id' => $item->equipment_id, 'qty' => $qty]));
            $log = new EquipmentLog(['equipment_id' => $item->equipment_id, 'qty' => $qty, 'action' => 'X', 'notes' => "Task updated to transfer $qty items from $old_location => $new_location"]);
            $log->save();
        } else {
            //
            // Create temporary location for transfer and new ToDoo for user
            //
            $newLocation = new EquipmentLocation();
            $newLocation->save();
            $newLocation->items()->save(new EquipmentLocationItem(['location_id' => $newLocation->id, 'equipment_id' => $item->equipment_id, 'qty' => $qty]));

            // Create ToDoo and assign to user
            $todo_request = [
                'type' => 'equipment',
                'type_id' => $newLocation->id,
                'name' => "Equipment transfer - $old_location => $new_location",
                'info' => "Please transfer equipment from the locations below.\nFrom: $old_location_full\nTo: $new_location_full",
                'due_at' => Carbon::createFromFormat('d/m/Y H:i', $due_at . '00:00')->toDateTimeString(),
                'company_id' => $item->company_id,
            ];
            $todo = Todo::create($todo_request);
            $todo->assignUsers($assign);
            $log = new EquipmentLog(['equipment_id' => $item->equipment_id, 'qty' => $qty, 'action' => 'X', 'notes' => "Task created to transfer $qty items from $old_location => $new_location"]);
            $log->save();

            // Update temporary transfer location with details of ToDoo request for tracking
            $newLocation->other = "Transfer in progress:$todo->id";
            $newLocation->notes = "$location_code:$assign";
            $newLocation->save();
        }
    }

    /**
     * Cancel transfer assign to user
     *
     */
    public function cancelTransfer($id)
    {
        $location = EquipmentLocation::findOrFail($id);

        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('edit.equipment', $location))
            return view('errors/404');

        list($crap, $todo_id) = explode(':', $location->other);
        list($orig_location_id, $type, $details, $user_id) = explode(':', $location->notes);

        $site_id = ($type == 'site') ? $details : null;
        $other = ($type == 'other') ? $details : null;
        $orig_location = EquipmentLocation::findOrFail($orig_location_id);
        $old_site = ($orig_location->site_id) ? Site::find($orig_location->site_id) : null;
        $orig_location_name = ($old_site) ? "$old_site->suburb ($old_site->name)" : $orig_location->name;
        $new_site = ($site_id) ? Site::find($site_id) : null;
        $new_location = ($new_site) ? "$new_site->suburb ($new_site->name)" : $other;

        foreach ($location->items as $item) {
            $log = new EquipmentLog(['equipment_id' => $item->equipment_id, 'qty' => $item->qty, 'action' => 'X', 'notes' => "Task cancelled to transfer items from $orig_location_name => $new_location"]);
            $log->save();
        }

        // Delete ToDoo + Transfer Location
        $todo = Todo::find($todo_id);
        $todo->delete();
        $location->delete();
        Toastr::success("Transfer cancelled");

        return redirect("/equipment");
    }

    /**
     * Subtract X items from location
     */
    public function subtractItems($item, $qty)
    {
        // Subtract items from current location
        $remain_qty = $item->qty - $qty;
        if ($remain_qty < 1) {
            $extra_amount = $remain_qty * -1;
            $lost_items = EquipmentLost::where('equipment_id', $item->equipment_id)->orderBy('created_at', 'DESC')->get();
            if ($extra_amount && $lost_items) {
                // Found extra item on current site and lost ones of same type
                foreach ($lost_items as $lost) {
                    if ($extra_amount) {
                        if ($lost->qty > $extra_amount) {
                            // More lost items then found so subtract only found amount
                            $lost->decrement('qty', $extra_amount);
                            $location_name = $lost->location->name;
                            $log = new EquipmentLog(['equipment_id' => $lost->equipment_id, 'qty' => $extra_amount, 'action' => 'F', 'notes' => "Found $extra_amount items at $location_name"]);
                            $extra_amount = 0;
                            break;
                        } else {
                            // Found more items then are actually lost so delete full amount from lost item.
                            $extra_amount = $extra_amount - $lost->qty;
                            $location_name = $lost->location->name;
                            $log = new EquipmentLog(['equipment_id' => $lost->equipment_id, 'qty' => $lost->qty, 'action' => 'F', 'notes' => "Found $lost->qty items at $location_name"]);
                            $lost->delete();
                        }
                        $log->save();
                    }
                }
                if ($extra_amount) {
                    $equip = Equipment::find($item->equipment_id);
                    if (($equip->total - ($equip->purchased - $equip->disposed)) > 0 && in_array($equip->category_id, [1, 2])) // Exclude Material Category from warning mesg
                        Toastr::warning("Item: $equip->name increased above actual number of purchased items.");
                }
            }
            $item->delete();

            // Make location inactive if type Other
            if (!$item->location->site_id) {
                $item->location->status = 0;
                $item->location->save();
            }
        } else {
            $item->qty = $remain_qty;
            $item->save();
        }
    }

    /**
     * Lost item
     *
     * @return \Illuminate\Http\Response
     */
    public function lostItem($location_id, $equipment_id, $qty)
    {
        $location = EquipmentLocation::findOrFail($location_id);
        $existing = EquipmentLost::where('location_id', $location_id)->where('equipment_id', $equipment_id)->first();
        if ($existing) {
            // Update existing lost qty
            $existing->qty = $existing->qty + $qty;
            $existing->save();
        } else {
            // Create Lost item
            $newLost = new EquipmentLost(['location_id' => $location_id, 'equipment_id' => $equipment_id, 'qty' => $qty]);
            $newLost->save();
        }

        // Create New Transaction for log
        $log = new EquipmentLog(['equipment_id' => $equipment_id, 'qty' => $qty, 'action' => 'M', 'notes' => "Missing $qty items from $location->name"]);
        $log->save();
    }


    /**
     * Get Allocations + Process datatables ajax request.
     */
    public function getTransfers()
    {
        $todos = Todo::where('todo.type', 'equipment')->where('todo.status', 1)->orderBy('created_at', 'DESC');
        $dt = Datatables::of($todos)
            ->editColumn('created_at', function ($todo) {
                return $todo->created_at->format('d/m/Y');
            })
            ->addColumn('items', function ($todo) {
                $location = EquipmentLocation::find($todo->type_id);

                return $location->itemsListSBC();
            })
            ->addColumn('from', function ($todo) {
                $location = EquipmentLocation::find($todo->type_id);
                list($location_id, $site_other, $site_other_id, $user_id) = explode(':', $location->notes);
                $location_from = EquipmentLocation::find($location_id);

                return $location_from->name3;
            })
            ->addColumn('to', function ($todo) {
                $location = EquipmentLocation::find($todo->type_id);
                list($location_id, $site_other, $site_other_id, $user_id) = explode(':', $location->notes);
                if ($site_other == 'site') {
                    $site = Site::find($site_other_id);

                    return $site->name;
                }

                return $site_other_id;
            })
            ->addColumn('assigned_to', function ($todo) {
                $to = $todo->assignedToBySBC();
                $by = $todo->createdBy->name;

                return "To: $to<br>By: $by";
            })
            ->addColumn('action', function ($todo) {
                $action = '';
                $action .= "<a href='/todo/" . $todo->id . "' class='btn blue btn-xs btn-outline sbold uppercase margin-bottom'>View Task</a>";

                return $action;
            })
            ->rawColumns(['items', 'created_by', 'action', 'assigned_to'])
            ->make(true);

        return $dt;
    }
}
