<div id="sdiv-{{$child_section->id}}">
    {{-- Section Title --}}
    @if ($child_section->name)
        <div class="row" style="background: #f0f6fa; margin: 10px 0px 5px 0px; padding: 5px 0px; cursor: pointer" onclick="toggleSection({{$child_section->id}})">
            <div class="col-md-12">
                <h4 class="font-dark">
                    <small><i id="sdiv-{{$child_section->id}}-arrow" class="fa fa-angle-down font-dark" style="margin-right: 10px"></i></small> {{ $child_section->name }}
                </h4>
            </div>
        </div>
    @endif

    <div id="sdiv-{{$child_section->id}}-content">
        {{-- Questions --}}
        <div style="margin-bottom: 0px">
            @foreach ($child_section->questions as $question)
                @include('site/inspection/custom/_show_question')
            @endforeach
        </div>

        {{-- Child sections --}}
        @if ($child_section->childSections)
            @foreach ($child_section->childSections as $childSection)
                @include('site/inspection/custom/_child_section', ['child_section' => $childSection])
            @endforeach
        @endif

    </div> {{-- end section-content div --}}
</div> {{-- end section div --}}