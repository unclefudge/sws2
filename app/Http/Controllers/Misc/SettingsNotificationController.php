<?php

namespace App\Http\Controllers\Misc;


use Illuminate\Http\Request;

use DB;
use Mail;
use App\User;
use App\Models\Company\Company;
use App\Models\Misc\SettingsNotification;
use App\Models\Misc\SettingsNotificationCategory;
use App\Http\Utilities\SettingsNotificationTypes;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Yajra\Datatables\Datatables;
use nilsenj\Toastr\Facades\Toastr;
use Carbon\Carbon;


class SettingsNotificationController extends Controller {

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //if (!Auth::user()->security)
        //    return view('errors/404');
        return view('manage/settings/notifications/edit');
    }

    /**
     * Update the specified resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function update($cid)
    {
        $cats = SettingsNotificationCategory::where('status', 1)->get();
        foreach ($cats as $cat) {
            $users = request("type$cat->id");
            $this->syncUsers($cid, $cat->id, $users);
        }
        Toastr::success('Saved notifications');

        return view('manage/settings/notifications/edit');
    }

    /**
     * Update the specified resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function updateStatus($cid, $status)
    {
        $cat = SettingsNotificationCategory::findOrFail($cid);
        $cat->status = $status;
        $cat->save();
        if ($status)
            Toastr::success('Enabled Notifcation');
        else
            Toastr::success('Disabled Notifcation');

        return view('manage/settings/notifications/edit');
    }

    public function show()
    {
        //
    }

    /**
     * Delete the specified resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        //
    }


    /**
     * Sync Users
     */
    public function syncUsers($company_id, $type, $users)
    {
        // Delete any lookup records
        $deleted_records = SettingsNotification::where('company_id', $company_id)->where('type', $type)->delete();

        // Create new lookup records
        if ($users) {
            foreach ($users as $user_id) {
                $newNotification = SettingsNotification::create(['user_id' => $user_id, 'type' => $type, 'company_id' => $company_id]);
            }
        }

    }
}
