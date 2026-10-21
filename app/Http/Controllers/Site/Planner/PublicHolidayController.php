<?php

namespace App\Http\Controllers\Site\Planner;

use App\Http\Controllers\Controller;
use App\Models\Site\Planner\PublicHoliday;
use App\Models\Site\Planner\PlannerClosure;
use App\Services\Planner\PlannerCalendarChangeService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use nilsenj\Toastr\Facades\Toastr;
use Yajra\DataTables\DataTables;

class PublicHolidayController extends Controller
{
    public function index()
    {
        abort_unless(Auth::user()->company->subscription && Auth::user()->hasAnyPermissionType('settings'), 403);

        return view('planner/publicholidays');
    }

    // Keep the established URLs; editing uses the existing create route.
    public function create(Request $request)
    {
        abort_unless(Auth::user()->hasPermission2('edit.settings'), 403);
        $request->validate(['calendar_source' => ['nullable', Rule::in(['public', 'closure'])]]);
        $calendarSource = $request->input('calendar_source', 'public');
        $model = $calendarSource === 'closure' ? PlannerClosure::class : PublicHoliday::class;
        $holiday = $request->filled('holiday_id') ? $model::findOrFail($request->integer('holiday_id')) : null;
        $types = $holiday
            ? array_intersect_key(PublicHoliday::TYPES, array_flip($calendarSource === 'public' ? ['public'] : ['shutdown', 'other']))
            : PublicHoliday::TYPES;

        return view('planner/publicholidays-create', compact('holiday', 'types', 'calendarSource'));
    }

    public function store(Request $request, PlannerCalendarChangeService $calendar)
    {
        abort_unless(Auth::user()->hasPermission2('edit.settings'), 403);
        if ($request->has('name') && is_string($request->input('name'))) {
            $request->merge(['name' => trim($request->input('name'))]);
        }
        $data = $request->validate([
            'holiday_id' => ['nullable', 'integer'],
            'calendar_source' => ['nullable', Rule::in(['public', 'closure'])],
            'operation' => ['required', Rule::in(['save', 'remove'])],
        ]);
        $holidayId = isset($data['holiday_id']) ? (int) $data['holiday_id'] : null;
        $source = $request->input('calendar_source', 'public');
        if ($holidayId) {
            $existingModel = $source === 'closure' ? PlannerClosure::class : PublicHoliday::class;
            $existingModel::findOrFail($holidayId);
        }
        $attributes = null;
        if ($data['operation'] === 'remove') {
            abort_unless($holidayId, 422);
        } else {
            $values = $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'type' => ['required', Rule::in(array_keys(PublicHoliday::TYPES))],
                'date' => ['required', 'date_format:d/m/Y'],
                'end_date' => ['nullable', 'date_format:d/m/Y', 'after_or_equal:date'],
            ]);
            $from = Carbon::createFromFormat('!d/m/Y', $values['date']);
            $to = empty($values['end_date']) ? $from->copy() : Carbon::createFromFormat('!d/m/Y', $values['end_date']);
            if ($from->lt(Carbon::today()) || $to->gt($from->copy()->addDays(365))) {
                throw ValidationException::withMessages(['date' => 'Choose today or a future date, with a range no longer than 366 days.']);
            }
            $typeSource = $values['type'] === 'public' ? 'public' : 'closure';
            if ($holidayId && $source !== $typeSource) {
                throw ValidationException::withMessages(['type' => 'Public holidays and planner closures are separate calendars. Add a new entry instead of converting between them.']);
            }
            $source = $typeSource;
            if ($source === 'public' && !$from->isSameDay($to)) {
                throw ValidationException::withMessages(['end_date' => 'Enter public holidays one date at a time. Use Company shutdown or Other closure for a date range.']);
            }
            $attributes = [
                'name' => trim($values['name']), 'type' => $values['type'],
                'date' => $from->toDateTimeString(), 'end_date' => $to->toDateString(), 'status' => 1,
            ];
        }

        $input = array_merge(['end_date' => ''], $request->only(['holiday_id', 'operation', 'name', 'type', 'date', 'end_date']), ['calendar_source' => $source]);
        $reviewKey = 'planner.calendar_review';
        if ($request->boolean('confirm')) {
            $stored = $request->session()->get($reviewKey);
            if (!$stored || ($stored['expires'] ?? 0) < time()
                || !hash_equals((string) ($stored['token'] ?? ''), (string) $request->input('review_token', ''))
                || ($stored['source'] ?? '') !== $source || ($stored['holiday_id'] ?? null) !== $holidayId || ($stored['attributes'] ?? null) !== $attributes) {
                throw ValidationException::withMessages(['date' => 'This review has expired or changed. Select Preview again.']);
            }
            DB::transaction(function () use ($calendar, $holidayId, $attributes, $stored, $source) {
                $fresh = $calendar->preview($holidayId, $attributes, true, $source);
                if (!hash_equals($stored['fingerprint'], $fresh['fingerprint'])) {
                    throw ValidationException::withMessages(['date' => 'The planner or calendar changed since this review. Select Preview again to review the current schedule.']);
                }
                $calendar->apply($holidayId, $attributes, $fresh, $source);
            });
            $request->session()->forget($reviewKey);
            Toastr::success($attributes === null ? 'Removed non-working dates and updated affected tasks.' : 'Saved non-working dates and updated affected tasks.');

            return redirect('/planner/publicholidays');
        }

        $review = $calendar->preview($holidayId, $attributes, false, $source);
        $token = Str::random(40);
        $request->session()->put($reviewKey, [
            'token' => $token, 'source' => $source, 'expires' => time() + 900, 'holiday_id' => $holidayId,
            'attributes' => $attributes, 'fingerprint' => $review['fingerprint'],
        ]);
        $model = $source === 'closure' ? PlannerClosure::class : PublicHoliday::class;
        $holiday = $holidayId ? $model::findOrFail($holidayId) : null;

        return view('planner/publicholidays-review', compact('review', 'input', 'token', 'holiday'));
    }

    public function show($id)
    {
        abort_unless(Auth::user()->hasPermission2('edit.settings'), 403);
        PublicHoliday::findOrFail($id);

        return redirect('/planner/publicholidays/create?holiday_id=' . (int) $id);
    }

    public function destroy($id)
    {
        abort_unless(Auth::user()->hasPermission2('edit.settings'), 403);
        PublicHoliday::findOrFail($id);

        // Older open browser tabs must also go through the task review.
        return response()->json(['message' => 'Open Manage and review removal before deleting these dates.'], 422);
    }

    public function getDates()
    {
        abort_unless(Auth::user()->company->subscription && Auth::user()->hasAnyPermissionType('settings'), 403);
        $columns = ['id', 'name', 'type', 'date', 'end_date', 'status'];
        $public = DB::table('public_holidays')->select($columns)->selectRaw("'public' AS calendar_source");
        $closures = DB::table('planner_closures')->select($columns)->selectRaw("'closure' AS calendar_source");
        $records = DB::query()->fromSub($public->unionAll($closures), 'calendar_entries')
            ->where('status', 1)
            ->whereRaw('COALESCE(end_date, date) >= ?', [Carbon::today()->toDateString()]);

        return DataTables::of($records)
            ->addColumn('nicedate', fn ($entry) => Carbon::parse($entry->date)->format('d/m/Y'))
            ->addColumn('niceend', fn ($entry) => Carbon::parse($entry->end_date ?? $entry->date)->format('d/m/Y'))
            ->addColumn('type_label', fn ($entry) => PublicHoliday::TYPES[$entry->type] ?? $entry->type)
            ->addColumn('action', function ($entry) {
                if (!Auth::user()->hasPermission2('edit.settings') || Carbon::parse($entry->date)->lt(Carbon::today())) {
                    return '';
                }

                return '<a class="btn blue btn-xs" href="/planner/publicholidays/create?holiday_id=' . (int) $entry->id . '&amp;calendar_source=' . $entry->calendar_source . '">Manage</a>';
            })
            ->rawColumns(['action'])->make(true);
    }
}
