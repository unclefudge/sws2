<div id="sdiv-{{$section->id}}">
    {{-- Section Title --}}
    @if ($section->name)
        <div class="row" style="background: #f0f6fa; margin: 10px 0px 5px 0px; padding: 5px 0px; cursor: pointer" onclick="toggleSection({{$section->id}})">
            <div class="col-md-12">
                <h4 class="font-dark">
                    <small><i id="sdiv-{{$section->id}}-arrow" class="fa fa-angle-down font-dark" style="margin-right: 10px"></i></small> {{ $section->name }}
                </h4>
            </div>
        </div>
    @endif

    <div id="sdiv-{{$section->id}}-content">
        {{-- Questions --}}
        <div style="margin-bottom: 0px">
            @foreach ($section->questions as $question)
                @include('site/inspection/custom/_show_question')
            @endforeach
        </div>

        {{-- Child sections --}}
        @if ($section->childSections)
            @foreach ($section->childSections as $childSection)
                @include('site/inspection/custom/_child_section', ['child_section' => $childSection])
            @endforeach
        @endif
    </div> {{-- end section-content div --}}
</div> {{-- end section div --}}