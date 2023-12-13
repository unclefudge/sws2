@foreach ($sections as $section_array)
    @php($section = $section_array['section'])
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
            @if ($section_array['child'])
                @foreach ($section_array['child'] as $child_section)
                    <div id="sdiv-{{$child_section->id}}">
                        {{-- Child Section Title --}}
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
                            {{-- Child Questions --}}
                            <div style="margin-bottom: 0px">
                                @foreach ($child_section->questions as $question)
                                    @include('site/inspection/custom/_show_question')
                                @endforeach
                            </div>
                        </div> {{-- end child section-content div --}}
                    </div> {{-- end child section div --}}
                @endforeach
            @endif
        </div> {{-- end section-content div --}}
    </div> {{-- end section div --}}
@endforeach