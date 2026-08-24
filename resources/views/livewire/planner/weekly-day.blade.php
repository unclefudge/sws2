@foreach ($dayPlan['entities'] as $entity)
    <div class="weekly-task-line {{ $entity['class'] }}">
        <small>
            {{ $entity['entity_name'] }} (
            @foreach ($entity['tasks'] as $task)
                @if ($task['highlight'])
                    <span class="label label-info" style="font-size:10px">{{ $task['code'] }}</span>
                @else
                    {{ $task['code'] }}
                @endif
                @if (!$loop->last), @endif
            @endforeach
            )
        </small>
    </div>
    @if ($entity['maintenance'])<div class="label label-info"><small>Maintenance Request</small></div>@endif
@endforeach

@foreach ($dayPlan['non_rostered'] as $user)
    <div><small class="{{ $dayPlan['past'] ? '' : 'font-grey-silver' }}">*{{ mb_substr((string)$user, 0, 15) }}</small></div>
@endforeach
