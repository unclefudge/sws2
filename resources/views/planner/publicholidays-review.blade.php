@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        <li><a href="/planner/publicholidays">Non-working Dates</a><i class="fa fa-circle"></i></li>
        <li><span>Review</span></li>
    </ul>
@stop

@section('content')
    <div class="page-content-inner">
        <div class="portlet light bordered">
            <div class="portlet-title"><div class="caption"><span class="caption-subject bold uppercase font-green-haze">Review Non-working Dates</span></div></div>
            <div class="portlet-body">
                @include('form-error')
                <h4>{{ $input['operation'] === 'remove' ? 'Remove' : 'Save' }}: {{ $input['operation'] === 'remove' ? $holiday->name : $input['name'] }}</h4>
                @if ($input['operation'] === 'remove')
                    <p>{{ $holiday->date->format('d/m/Y') }} – {{ ($holiday->end_date ?? $holiday->date)->format('d/m/Y') }}</p>
                @else
                    <p>{{ $input['date'] }} – {{ $input['end_date'] ?: $input['date'] }}</p>
                @endif
                <p><strong>{{ count($review['changes']) }} task(s) need attention.</strong> Working-day quantities stay the same. Each affected site’s remaining schedule moves together, preserving its current working-day spacing and task order. Tasks crossing the closure retain their start and have their finish recalculated.</p>
                <p class="note note-info">Company shutdowns and other closures are saved separately from statutory public holidays. They only change the planner calendar.</p>
                <p>This includes later tasks already scheduled after the closure. Sites with no affected tasks keep their dates. Check company capacity and job milestones in the proposed schedule. Attendance and manually entered roster records are retained.</p>
                @if ($review['blockers'])
                    <div class="note note-danger">
                        <strong>Resolve these tasks before applying:</strong>
                        <ul>@foreach ($review['blockers'] as $blocker)<li>{{ $blocker }}</li>@endforeach</ul>
                        <p>No dates have been saved. Use the site and task names above to find the entries in the planner, resolve the listed issues, then refresh this preview.</p>
                    </div>
                @endif
                @if ($review['changes'])
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered">
                            <thead><tr><th>Task ID</th><th>Site</th><th>Task</th><th>Days</th><th>Current start</th><th>Current finish</th><th>Proposed start</th><th>Proposed finish</th><th>Action needed</th></tr></thead>
                            <tbody>
                            @foreach ($review['changes'] as $change)
                                <tr>
                                    <td>{{ $change['id'] }}</td><td>{{ $change['site'] }}</td><td>{{ $change['task'] }}</td><td>{{ $change['days'] }}</td>
                                    @foreach (['from', 'to', 'new_from', 'new_to'] as $key)<td>{{ \Carbon\Carbon::parse($change[$key])->format('d/m/Y') }}</td>@endforeach
                                    <td>{{ $change['reason'] }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="note note-info">No existing task dates need to change.</div>
                @endif
                <form method="POST" action="{{ action([App\Http\Controllers\Site\Planner\PublicHolidayController::class, 'store']) }}">
                    @csrf
                    @foreach ($input as $key => $value)<input type="hidden" name="{{ $key }}" value="{{ $value }}">@endforeach
                    <input type="hidden" name="review_token" value="{{ $token }}">
                    <div class="form-actions right">
                        <a class="btn default" href="/planner/publicholidays/create{{ $holiday ? '?holiday_id='.$holiday->id.'&calendar_source='.$input['calendar_source'] : '' }}">Back</a>
                        <button type="submit" name="confirm" value="0" class="btn blue">Refresh preview</button>
                        @if (!$review['blockers'])<button type="submit" name="confirm" value="1" class="btn {{ $input['operation'] === 'remove' ? 'red' : 'green' }}">{{ $input['operation'] === 'remove' ? 'Remove dates and update tasks' : 'Save dates and update tasks' }}</button>@endif
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop
