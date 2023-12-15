<li>{{ $project['name'] }}</li>
@if (count($project['children']) > 0)
    <ul>
        @foreach($project['children'] as $project)
            @include('site.inspection.custom..._show_section', $project)
        @endforeach
    </ul>
@endif