<?php

namespace App\Http\Controllers\Misc;

use App\Http\Controllers\Controller;
use App\Models\Misc\SettingsNotification;
use App\Models\Misc\SettingsNotificationCategory;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use nilsenj\Toastr\Facades\Toastr;

class SettingsNotificationController extends Controller
{
    public function index()
    {
        $this->authoriseSettings();

        return $this->editView();
    }

    public function update($cid)
    {
        $this->authoriseSettings();
        abort_unless((int) $cid === (int) Auth::user()->company_id, 403);

        $categoryIds = collect(request('notification_present', []))->map(fn($id) => (int)$id)->filter()->unique()->values()->all();
        $cats = SettingsNotificationCategory::whereIn('id', $categoryIds)->where('status', 1)->get();

        foreach ($cats as $cat)
            $this->syncUsers($cid, $cat->id, request("type$cat->id"));

        Toastr::success('Saved notifications');

        return redirect('/settings/notifications');
    }

    public function updateStatus($cid, $status)
    {
        $this->authoriseSettings();
        abort_unless(in_array((int) $status, [0, 1], true), 422);

        $cat = SettingsNotificationCategory::findOrFail($cid);
        $cat->status = (int)$status;
        $cat->updated_by = Auth::id();
        $cat->save();

        Toastr::success($status ? 'Enabled notification' : 'Disabled notification');

        return redirect('/settings/notifications');
    }

    /**
     * Add a new configurable report recipient list.
     */
    public function storeReportCategory(Request $request)
    {
        $this->authoriseWebAdmin();

        $data = $request->validate([
            'report_name' => ['required', 'string', 'max:100'],
            'report_slug' => ['required', 'string', 'max:100', 'regex:/^[a-z0-9._-]+$/', 'unique:settings_notifications_categories,slug'],
            'report_title' => ['nullable', 'string', 'max:150'],
            'report_body' => ['nullable', 'string', 'max:1000'],
            'report_brief' => ['nullable', 'string', 'max:255'],
        ]);

        $nextOrder = (int)SettingsNotificationCategory::where('type', 'report')->max('sort_order') + 10;

        SettingsNotificationCategory::create([
            'type' => 'report',
            'sort_order' => $nextOrder,
            'system' => 0,
            'slug' => $data['report_slug'],
            'name' => $data['report_name'],
            'title' => $data['report_title'] ?? null,
            'body' => $data['report_body'] ?? null,
            'brief' => $data['report_brief'] ?? null,
            'status' => 1,
            'company_id' => Auth::user()->company_id,
            'created_by' => Auth::id(),
            'updated_by' => Auth::id(),
        ]);

        Toastr::success('Added report email list');

        return redirect('/settings/notifications');
    }

    public function moveReportCategory($id, $direction)
    {
        $this->authoriseWebAdmin();
        abort_unless(in_array($direction, ['up', 'down'], true), 404);

        $category = SettingsNotificationCategory::where('type', 'report')->findOrFail($id);

        $other = SettingsNotificationCategory::where('type', 'report')
            ->where('id', '<>', $category->id)
            ->when($direction === 'up', fn($query) => $query->where('sort_order', '<', $category->sort_order)->orderByDesc('sort_order')->orderByDesc('id'))
            ->when($direction === 'down', fn($query) => $query->where('sort_order', '>', $category->sort_order)->orderBy('sort_order')->orderBy('id'))
            ->first();

        if ($other) {
            $currentOrder = $category->sort_order;
            $category->update(['sort_order' => $other->sort_order, 'updated_by' => Auth::id()]);
            $other->update(['sort_order' => $currentOrder, 'updated_by' => Auth::id()]);
        }

        return response()->json(['ok' => true]);
    }

    public function destroyReportCategory($id)
    {
        $this->authoriseWebAdmin();

        $category = SettingsNotificationCategory::where('type', 'report')->findOrFail($id);

        // Code-backed report lists should be disabled rather than deleted.
        abort_if($category->system, 422, 'System report email lists cannot be deleted. Disable them instead.');

        SettingsNotification::where('type', $category->id)->delete();
        $category->delete();

        return response()->json(['ok' => true]);
    }

    public function show()
    {
        //
    }

    public function destroy(Request $request, $id)
    {
        //
    }

    /**
     * Sync Users
     */
    public function syncUsers($company_id, $type, $users)
    {
        SettingsNotification::where('company_id', $company_id)->where('type', $type)->delete();

        $userIds = User::query()
            ->where('company_id', $company_id)
            ->where('status', 1)
            ->whereIn('id', collect($users ?: [])->map(fn($id) => (int) $id)->filter()->unique())
            ->pluck('id');

        foreach ($userIds as $user_id) {
            SettingsNotification::create(['user_id' => $user_id, 'type' => $type, 'company_id' => $company_id,]);
        }
    }

    protected function editView()
    {
        $this->authoriseSettings();
        $reportCategories = SettingsNotificationCategory::where('type', 'report')->orderBy('sort_order')->orderBy('name')->get();

        return view('manage/settings/notifications/edit', compact('reportCategories'));
    }

    private function authoriseSettings(): void
    {
        $user = Auth::user();
        abort_unless($user && ($user->hasRole2('web-admin') || $user->hasAnyPermissionType('settings')), 403);
    }

    private function authoriseWebAdmin(): void
    {
        abort_unless(Auth::user() && Auth::user()->hasRole2('web-admin'), 403);
    }
}
