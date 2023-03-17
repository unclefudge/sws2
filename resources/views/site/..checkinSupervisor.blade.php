@extends('layout-basic')

@section('pagetitle')
    @if (Session::has('siteID') && $worksite->isUserOnsite(Auth::user()->id))
        <a href="/"><img src="/img/logo2-sws.png" alt="logo" class="logo-default" style="margin-top:15px"></a>
    @else
        <img src="/img/logo2-sws.png" alt="logo" class="logo-default" style="margin-top:15px">
    @endif
    <div class="pull-right" style="padding: 20px;"><a href="/logout">logout</a></div>
@stop

@section('breadcrumbs')
    @if (Session::has('siteID') && $worksite->isUserOnsite(Auth::user()->id))
        <ul class="page-breadcrumb breadcrumb">
            <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
            <li><span>Check-in</span></li>
        </ul>
    @endif
@stop

@section('content')
    <div class="page-content-inner">
        <div class="row">
            <div class="col-md-12">
                <div class="portlet light bordered">
                    <div class="portlet-title">
                        <div class="caption">
                            <i class="fa fa-sign-in "></i>
                            <span class="caption-subject font-green-haze bold uppercase">Site Supervisor Checkin</span><br>
                            <span class="caption-helper">You must check into all sites you attend.</span>
                        </div>
                    </div>
                    <div class="portlet-body form">
                        <h2>{{ $worksite->name }}
                            <small>(Site: {{ $worksite->code }})</small>
                        </h2>
                        <p>{{ $worksite->address }}, {{ $worksite->suburb }}</p>
                        <hr>

                        <!-- BEGIN FORM-->
                        {!! Form::model('site_attenance', ['action' => ['Site\SiteCheckinController@processCheckin', $worksite->id], 'files' => true]) !!}

                        @include('form-error')

                        <p>Please answer the following questions.</p>
                        <div class="form-body">
                            <h4 class="font-green-haze">Site Attendance, Public Protection & Site Safety</h4>
                            <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                            {{-- Question 100 --}}
                            <div class="row">
                                <div class="col-sm-2 col-xs-4 text-center">
                                    <div class="form-group">
                                        {!! Form::checkbox('question100', '1', false, ['class' => 'make-switch', 'data-size' => 'small', 'data-on-text'=>'Yes', 'data-on-color'=>'success', 'data-off-text'=>'No', 'data-off-color'=>'danger']) !!}
                                    </div>
                                </div>
                                <div class="col-sm-10 col-xs-8">
                                    Is the site adequately secured against entry by unauthorised persons? <a id="info_100" class="btn btn-xs btn-info infofield">info</a>
                                    <div id="info_100_div" class="note note-info" style="display: none">
                                        WORKERS INCLUDING SUBCONTRACTORS MUST BE ABLE TO SECURE THE SITE OR THEIR WORK AREA AGAINST UNAUTHORISED ACCESS. WHILE CONSTRUCTION WORK IS BEING CARRIED OUT AND PEOPLE ARE ON SITE, MEANS OF SECURING THE SITE MAY BE LEFT UNLOCKED OR INCOMPLETE TO ENSURE SAFE
                                        ENTRY AND EXIT.<br>
                                        Site Security may include a combination of control methods in conjunction, and may change as the site conditions change. Guidelines for suitable types of site security include:<br>
                                        • fencing (could be existing fencing) or barricades should be at least 1.8m high,<br>
                                        • it should be difficult to gain access under or to scale the fence or barrier<br>
                                        • should be able to withstand the anticipated loads to which it may be subjected, such as wind forces, persons attempting to scale and vehicle impact loads<br>
                                        • where a fence is comprised of panels, the joints should not weaken it and should provide the same level of security as the panels<br>
                                        • gates should not represent a weak point and the closed gate should provide the same level of security<br>
                                    </div>
                                </div>
                            </div>
                            <div class="row visible-xs">&nbsp;</div>

                            {{-- Question 101 --}}
                            <div class="row">
                                <div class="col-sm-2 col-xs-4 text-center">
                                    <div class="form-group">
                                        {!! Form::checkbox('question101', '1', false, ['class' => 'make-switch', 'data-size' => 'small', 'data-on-text'=>'Yes', 'data-on-color'=>'success', 'data-off-text'=>'No', 'data-off-color'=>'danger']) !!}
                                    </div>
                                </div>
                                <div class="col-sm-10 col-xs-8">
                                    Are Public areas unobstructed and/or adequately protected? <a id="info_101" class="btn btn-xs btn-info infofield">info</a>
                                    <div id="info_101_div" class="note note-info" style="display: none">
                                        MEMBERS OF THE PUBLIC SHOULD NOT BE EXPOSED TO OR IMPACTED BY CONSTRUCTION ACTIVITIES AND WHERE IT IS NOT POSSIBLE TO ELIMINATE CONSTRUCTION RELATED ACTIVITIES FROM PUBLIC AREAS, THE WORK ZONE SHOULD BE ISOLATED FROM THE PUBLIC WITH PHYSICAL BARRIERS TO
                                        MINIMISE THE RISK.<br>
                                        • roads and footpaths should be unaffected or clearly delineated and made safe<br>
                                        • risks of falling objects and debris to public areas from construction areas should be actively controlled (e.g. Containment netting to restrict debris/materials from falling freely from scaffolds)<br>
                                    </div>
                                </div>
                            </div>
                            <div class="row visible-xs">&nbsp;</div>

                            {{-- Question 102 --}}
                            <div class="row">
                                <div class="col-sm-2 col-xs-4 text-center">
                                    <div class="form-group">
                                        {!! Form::checkbox('question102', '1', false, ['class' => 'make-switch', 'data-size' => 'small', 'data-on-text'=>'Yes', 'data-on-color'=>'success', 'data-off-text'=>'No', 'data-off-color'=>'danger']) !!}
                                    </div>
                                </div>
                                <div class="col-sm-10 col-xs-8">
                                    Principal Contractor and Building Certifier signage and emergency contact details displayed and clearly visible from outside the workplace?<br><br>
                                </div>
                            </div>
                            <div class="row visible-xs">&nbsp;</div>

                            {{-- Question 103 --}}
                            <div class="row">
                                <div class="col-sm-2 col-xs-4 text-center">
                                    <div class="form-group">
                                        {!! Form::checkbox('question103', '1', false, ['class' => 'make-switch', 'data-size' => 'small', 'data-on-text'=>'Yes', 'data-on-color'=>'success', 'data-off-text'=>'No', 'data-off-color'=>'danger']) !!}
                                    </div>
                                </div>
                                <div class="col-sm-10 col-xs-8">
                                    Have all workers completed site sign in?
                                </div>
                            </div>
                            <div class="row visible-xs">&nbsp;</div>


                            <h4 class="font-green-haze">Access / Egress</h4>
                            <hr style="padding: 0px; margin: 0px 0px 10px 0px">

                            {{-- Question 104 --}}
                            <div class="row">
                                <div class="col-sm-2 col-xs-4 text-center">
                                    <div class="form-group">
                                        {!! Form::checkbox('question104', '1', false, ['class' => 'make-switch', 'data-size' => 'small', 'data-on-text'=>'Yes', 'data-on-color'=>'success', 'data-off-text'=>'No', 'data-off-color'=>'danger']) !!}
                                    </div>
                                </div>
                                <div class="col-sm-10 col-xs-8">
                                    Is the layout of the site maintained to allow persons to enter, exit, and move within it safely - under both normal working conditions and in an emergency situation? <a id="info_104" class="btn btn-xs btn-info infofield">info</a><br><br>
                                    <div id="info_104_div" class="note note-info" style="display: none">
                                        CONSIDER ALL POTENTIAL USERS E.G. -<br>
                                        • Clients and their visitors (e.g. suitability/ease of access, risk of falling materials & tooling from scaffold near entries/pathways)<br>
                                        • Workers above and below (can workers access their work area without risk from other activities?)<br>
                                        • Emergency situations (such as when a person may be incapacitated, or emergency services and their equipment need to quickly and easily access work areas)<br>
                                    </div>
                                </div>
                            </div>
                            <div class="row visible-xs">&nbsp;</div>

                            {{-- Question 105 --}}
                            <div class="row">
                                <div class="col-sm-2 col-xs-4 text-center">
                                    <div class="form-group">
                                        {!! Form::checkbox('question105', '1', false, ['class' => 'make-switch', 'data-size' => 'small', 'data-on-text'=>'Yes', 'data-on-color'=>'success', 'data-off-text'=>'No', 'data-off-color'=>'danger']) !!}
                                    </div>
                                </div>
                                <div class="col-sm-10 col-xs-8">
                                    Has adequate housekeeping and hygiene of the overrall site been maintained? <a id="info_105" class="btn btn-xs btn-info infofield">info</a>
                                    <div id="info_105_div" class="note note-info" style="display: none">
                                        • Have designated waste disposal sites/placement points been established and maintained?<br>
                                        • Are entry, exit areas and passageways kept free of obstruction from materials, waste and debris? - Are materials/tooling/equipment appropriately stored?<br>
                                    </div>
                                </div>
                            </div>
                            <div class="row visible-xs">&nbsp;</div>

                            {{-- Question 106 --}}
                            <div class="row">
                                <div class="col-sm-2 col-xs-4 text-center">
                                    <div class="form-group">
                                        {!! Form::checkbox('question106', '1', false, ['class' => 'make-switch', 'data-size' => 'small', 'data-on-text'=>'Yes', 'data-on-color'=>'success', 'data-off-text'=>'No', 'data-off-color'=>'danger']) !!}
                                    </div>
                                </div>
                                <div class="col-sm-10 col-xs-8">
                                    Are adequate facilities for workers provided - including toilets, drinking water, washing facilities and appropriate eating areas, and have they been suitably maintained? <a id="info_106" class="btn btn-xs btn-info infofield">info</a>
                                    <div id="info_106_div" class="note note-info" style="display: none">
                                        • Are facilities maintained in good working order and are clean, safe and accessible?<br>
                                        • Are facilities adequately stocked (toilet paper, handwash etc)<br>
                                    </div>
                                </div>
                            </div>
                            <div class="row visible-xs">&nbsp;</div>


                            <h4 class="font-green-haze">Hazardous Materials</h4>
                            <hr style="padding: 0px; margin: 0px 0px 10px 0px">

                            {{-- Question 107 --}}
                            <div class="row">
                                <div class="col-sm-2 col-xs-4 text-center">
                                    <div class="form-group">
                                        {!! Form::checkbox('question107', '1', false, ['class' => 'make-switch', 'data-size' => 'small', 'data-on-text'=>'Yes', 'data-on-color'=>'success', 'data-off-text'=>'No', 'data-off-color'=>'danger']) !!}
                                    </div>
                                </div>
                                <div class="col-sm-10 col-xs-8">
                                    <?php $asb = \App\Models\Site\SiteAsbestosRegister::where('site_id', $worksite->id)->first() ?>
                                    Have you reviewed the site Asbestos Register/Hazardous Materials report for the site and are aware of the presence of identified asbestos? @if ($asb && $asb->attachment_url)<a href="{!! $asb->attachment_url !!}" id="info_107" target="_blank" class="btn btn-xs btn-info">report</a> @endif<br><br>
                                </div>
                            </div>
                            <div class="row visible-xs">&nbsp;</div>

                            {{-- Question 108 --}}
                            <div class="row">
                                <div class="col-sm-2 col-xs-4 text-center">
                                    <div class="form-group">
                                        {!! Form::checkbox('question108', '1', false, ['class' => 'make-switch', 'data-size' => 'small', 'data-on-text'=>'Yes', 'data-on-color'=>'success', 'data-off-text'=>'No', 'data-off-color'=>'danger']) !!}
                                    </div>
                                </div>
                                <div class="col-sm-10 col-xs-8">
                                    Are on site workers aware of the presence of any hazardous materials as applicable to their tasks?
                                </div>
                            </div>
                            <div class="row visible-xs">&nbsp;</div>


                            <h4 class="font-green-haze">Electrical</h4>
                            <hr style="padding: 0px; margin: 0px 0px 10px 0px">

                            {{-- Question 109 --}}
                            <div class="row">
                                <div class="col-sm-2 col-xs-4 text-center">
                                    <div class="form-group">
                                        {!! Form::checkbox('question109', '1', false, ['class' => 'make-switch', 'data-size' => 'small', 'data-on-text'=>'Yes', 'data-on-color'=>'success', 'data-off-text'=>'No', 'data-off-color'=>'danger']) !!}
                                    </div>
                                </div>
                                <div class="col-sm-10 col-xs-8">
                                    Has electricity been appropriately terminated/isolated in reference to the work taking place (including demolition)?<br><br>
                                </div>
                            </div>
                            <div class="row visible-xs">&nbsp;</div>

                            {{-- Question 110 --}}
                            <div class="row">
                                <div class="col-sm-2 col-xs-4 text-center">
                                    <div class="form-group">
                                        {!! Form::checkbox('question110', '1', false, ['class' => 'make-switch', 'data-size' => 'small', 'data-on-text'=>'Yes', 'data-on-color'=>'success', 'data-off-text'=>'No', 'data-off-color'=>'danger']) !!}
                                    </div>
                                </div>
                                <div class="col-sm-10 col-xs-8">
                                    Are approach distances for work near low voltage overhead service lines and/or overhead powerlines adhered to? <a id="info_110" class="btn btn-xs btn-info infofield">info</a><br><br>
                                    <div id="info_110_div" class="note note-info" style="display: none">
                                        <img src="/img/electrical_approach_chart.jpg">
                                    </div>
                                </div>
                            </div>
                            <div class="row visible-xs">&nbsp;</div>

                            {{-- Question 111 --}}
                            <div class="row">
                                <div class="col-sm-2 col-xs-4 text-center">
                                    <div class="form-group">
                                        {!! Form::checkbox('question111', '1', false, ['class' => 'make-switch', 'data-size' => 'small', 'data-on-text'=>'Yes', 'data-on-color'=>'success', 'data-off-text'=>'No', 'data-off-color'=>'danger']) !!}
                                    </div>
                                </div>
                                <div class="col-sm-10 col-xs-8">
                                    Are tiger tails installed as applicable to provide visual indicator as to presence of overhead powerlines nearby work activities/areas? <a id="info_111" class="btn btn-xs btn-info infofield">info</a>
                                    <div id="info_111_div" class="note note-info" style="display: none">
                                        Tiger tails are to be regarded as a visual indicator only and do not provide the level of insulation/isolation from workers and machinery to be regarded as a barrier.<br>
                                    </div>
                                </div>
                            </div>
                            <div class="row visible-xs">&nbsp;</div>


                            <h4 class="font-green-haze">Tooling & Equipment</h4>
                            <hr style="padding: 0px; margin: 0px 0px 10px 0px">

                            {{-- Question 112 --}}
                            <div class="row">
                                <div class="col-sm-2 col-xs-4 text-center">
                                    <div class="form-group">
                                        {!! Form::checkbox('question112', '1', false, ['class' => 'make-switch', 'data-size' => 'small', 'data-on-text'=>'Yes', 'data-on-color'=>'success', 'data-off-text'=>'No', 'data-off-color'=>'danger']) !!}
                                    </div>
                                </div>
                                <div class="col-sm-10 col-xs-8">
                                    Are tooling & equipment safety guards in place as applicable and in suitable condition?
                                </div>
                            </div>
                            <div class="row visible-xs">&nbsp;</div>

                            {{-- Question 113 --}}
                            <div class="row">
                                <div class="col-sm-2 col-xs-4 text-center">
                                    <div class="form-group">
                                        {!! Form::checkbox('question113', '1', false, ['class' => 'make-switch', 'data-size' => 'small', 'data-on-text'=>'Yes', 'data-on-color'=>'success', 'data-off-text'=>'No', 'data-off-color'=>'danger']) !!}
                                    </div>
                                </div>
                                <div class="col-sm-10 col-xs-8">
                                    Has portable electrical equipment tested and tagged within 3 months and evidence of testing affixed to the equipment by physical tag?<br><br>
                                </div>
                            </div>
                            <div class="row visible-xs">&nbsp;</div>

                            {{-- Question 114 --}}
                            <div class="row">
                                <div class="col-sm-2 col-xs-4 text-center">
                                    <div class="form-group">
                                        {!! Form::checkbox('question114', '1', false, ['class' => 'make-switch', 'data-size' => 'small', 'data-on-text'=>'Yes', 'data-on-color'=>'success', 'data-off-text'=>'No', 'data-off-color'=>'danger']) !!}
                                    </div>
                                </div>
                                <div class="col-sm-10 col-xs-8">
                                    Are portable Residual Current Devices (RCD) used in conjunction with all portable electrical equipment that has power supplied by a plug and lead?<br><br>
                                </div>
                            </div>
                            <div class="row visible-xs">&nbsp;</div>

                            <h4 class="font-green-haze">Ladders</h4>
                            <hr style="padding: 0px; margin: 0px 0px 10px 0px">

                            {{-- Question 115 --}}
                            <div class="row">
                                <div class="col-sm-2 col-xs-4 text-center">
                                    <div class="form-group">
                                        {!! Form::checkbox('question115', '1', false, ['class' => 'make-switch', 'data-size' => 'small', 'data-on-text'=>'Yes', 'data-on-color'=>'success', 'data-off-text'=>'No', 'data-off-color'=>'danger', 'id' => 'question115']) !!}
                                    </div>
                                </div>
                                <div class="col-sm-10 col-xs-8">
                                    Are ladders being used on site (as a means of access or for tasks)?
                                </div>
                            </div>
                            <div class="row visible-xs">&nbsp;</div>

                            {{-- Ladders --}}
                            <div id="ladders_div" style="display: none">
                                {{-- Question 116 --}}
                                <div class="row">
                                    <div class="col-sm-2 col-xs-4 text-center">
                                        <div class="form-group">
                                            {!! Form::checkbox('question116', '1', false, ['class' => 'make-switch', 'data-size' => 'small', 'data-on-text'=>'Yes', 'data-on-color'=>'success', 'data-off-text'=>'No', 'data-off-color'=>'danger']) !!}
                                        </div>
                                    </div>
                                    <div class="col-sm-10 col-xs-8">
                                        Are ladder(s) in good condition, appropriate for the task and set up correctly? <a id="info_116" class="btn btn-xs btn-info infofield">info</a>
                                        <div id="info_116_div" class="note note-info" style="display: none">
                                            Ladders must be:<br>
                                            • maintained in good condition and rated for industrial use?<br>
                                            • set up on firm, stable and level ground?<br>
                                            • extend at least one metre above the stepping off point on the working platform (where used for access purposes)<br>
                                            • secured against displacement (i.e. slipping or sliding) and/or there is another person holding the base of the ladder?<br>
                                            • set up at an appropriate distance from the support structure? (distance between the ladder base and the supporting structure should be at a ratio of 4:1 - about one metre for every four metres of working ladder height)<br>
                                            • set up with locking devices secure?<br>
                                            • used at the correct height for the task to avoid reaching or stretching?<br>
                                            • If working from the ladder, can workers maintain three points of contact and tools can be operated safely with one hand?<br>
                                        </div>
                                    </div>
                                </div>
                                <div class="row visible-xs">&nbsp;</div>
                            </div>

                            <h4 class="font-green-haze">Falling Objects & Height Safety</h4>
                            <hr style="padding: 0px; margin: 0px 0px 10px 0px">

                            {{-- Question 117 --}}
                            <div class="row">
                                <div class="col-sm-2 col-xs-4 text-center">
                                    <div class="form-group">
                                        {!! Form::checkbox('question117', '1', false, ['class' => 'make-switch', 'data-size' => 'small', 'data-on-text'=>'Yes', 'data-on-color'=>'success', 'data-off-text'=>'No', 'data-off-color'=>'danger', 'id' => 'question117']) !!}
                                    </div>
                                </div>
                                <div class="col-sm-10 col-xs-8">
                                    Is there any elevated work areas? (including but not limited to scaffolds, mezzanines, work on roofs etc)
                                </div>
                            </div>
                            <div class="row visible-xs">&nbsp;</div>

                            {{-- Falling Objects --}}
                            <div id="fallingobject_div" style="display: none">
                                {{-- Question 118 --}}
                                <div class="row">
                                    <div class="col-sm-2 col-xs-4 text-center">
                                        <div class="form-group">
                                            {!! Form::checkbox('question118', '1', false, ['class' => 'make-switch', 'data-size' => 'small', 'data-on-text'=>'Yes', 'data-on-color'=>'success', 'data-off-text'=>'No', 'data-off-color'=>'danger']) !!}
                                        </div>
                                    </div>
                                    <div class="col-sm-10 col-xs-8">
                                        Have suitable material/containment screens been affixed by a competent person to scaffold/roof rail/elevated work areas to arrest the free fall of objects to area below as applicable? <a id="info_118" class="btn btn-xs btn-info infofield">info</a><br><br>
                                        <div id="info_118_div" class="note note-info" style="display: none">
                                            (i.e. brick guard mesh, shadecloth etc)<br>
                                        </div>
                                    </div>
                                </div>
                                <div class="row visible-xs">&nbsp;</div>

                                {{-- Question 119 --}}
                                <div class="row">
                                    <div class="col-sm-2 col-xs-4 text-center">
                                        <div class="form-group">
                                            {!! Form::checkbox('question119', '1', false, ['class' => 'make-switch', 'data-size' => 'small', 'data-on-text'=>'Yes', 'data-on-color'=>'success', 'data-off-text'=>'No', 'data-off-color'=>'danger']) !!}
                                        </div>
                                    </div>
                                    <div class="col-sm-10 col-xs-8">
                                        Have appropriate exclusion zones been established as applicable to address the risk of workers and others below being struck by any objects that may fall/be dropped/thrown from elevated work areas?<br><br>
                                    </div>
                                </div>

                                <div class="row visible-xs">&nbsp;</div>

                                {{-- Question 120 --}}
                                <div class="row">
                                    <div class="col-sm-2 col-xs-4 text-center">
                                        <div class="form-group">
                                            {!! Form::checkbox('question120', '1', false, ['class' => 'make-switch', 'data-size' => 'small', 'data-on-text'=>'Yes', 'data-on-color'=>'success', 'data-off-text'=>'No', 'data-off-color'=>'danger']) !!}
                                        </div>
                                    </div>
                                    <div class="col-sm-10 col-xs-8">
                                        Is adequate edge protection installed to perimeters?
                                    </div>
                                </div>
                                <div class="row visible-xs">&nbsp;</div>

                                {{-- Question 121 --}}
                                <div class="row">
                                    <div class="col-sm-2 col-xs-4 text-center">
                                        <div class="form-group">
                                            {!! Form::checkbox('question121', '1', false, ['class' => 'make-switch', 'data-size' => 'small', 'data-on-text'=>'Yes', 'data-on-color'=>'success', 'data-off-text'=>'No', 'data-off-color'=>'danger']) !!}
                                        </div>
                                    </div>
                                    <div class="col-sm-10 col-xs-8">
                                        Are penetrations and openings in floors/work surfaces suitably protected?
                                    </div>
                                </div>
                                <div class="row visible-xs">&nbsp;</div>

                                {{-- Question 122 --}}
                                <div class="row">
                                    <div class="col-sm-2 col-xs-4 text-center">
                                        <div class="form-group">
                                            {!! Form::checkbox('question122', '1', false, ['class' => 'make-switch', 'data-size' => 'small', 'data-on-text'=>'Yes', 'data-on-color'=>'success', 'data-off-text'=>'No', 'data-off-color'=>'danger']) !!}
                                        </div>
                                    </div>
                                    <div class="col-sm-10 col-xs-8">
                                        Are fragile roof materials/floor surfaces (such as skylights, plastic roof sheets etc) suitably protected?
                                    </div>
                                </div>
                                <div class="row visible-xs">&nbsp;</div>

                                {{-- Question 123 --}}
                                <div class="row">
                                    <div class="col-sm-2 col-xs-4 text-center">
                                        <div class="form-group">
                                            {!! Form::checkbox('question123', '1', false, ['class' => 'make-switch', 'data-size' => 'small', 'data-on-text'=>'Yes', 'data-on-color'=>'success', 'data-off-text'=>'No', 'data-off-color'=>'danger']) !!}
                                        </div>
                                    </div>
                                    <div class="col-sm-10 col-xs-8">
                                        Guardrailing incorporates a top-rail between 900 mm and 1100 mm above the working surface, a mid-rail and toeboards (except where it may be impractical to do so and alternative control measures, such as ‘no go’ zones, to ensure no persons are at risk of being
                                        hit by falling objects from the work above)<br><br>
                                    </div>
                                </div>
                                <div class="row visible-xs">&nbsp;</div>

                            </div> {{-- end Falling Object div --}}


                            <h4 class="font-green-haze">Scaffolds</h4>
                            <hr style="padding: 0px; margin: 0px 0px 10px 0px">

                            {{-- Question 124 --}}
                            <div class="row">
                                <div class="col-sm-2 col-xs-4 text-center">
                                    <div class="form-group">
                                        {!! Form::checkbox('question124', '1', false, ['class' => 'make-switch', 'data-size' => 'small', 'data-on-text'=>'Yes', 'data-on-color'=>'success', 'data-off-text'=>'No', 'data-off-color'=>'danger', 'id' => 'question124']) !!}
                                    </div>
                                </div>
                                <div class="col-sm-10 col-xs-8">
                                    Are scaffolds erected on the site?
                                </div>
                            </div>
                            <div class="row visible-xs">&nbsp;</div>

                            {{-- Scaffolds --}}
                            <div id="scaffolds_div" style="display: none">
                                {{-- Question 125 --}}
                                <div class="row">
                                    <div class="col-sm-2 col-xs-4 text-center">
                                        <div class="form-group">
                                            {!! Form::checkbox('question125', '1', false, ['class' => 'make-switch', 'data-size' => 'small', 'data-on-text'=>'Yes', 'data-on-color'=>'success', 'data-off-text'=>'No', 'data-off-color'=>'danger']) !!}
                                        </div>
                                    </div>
                                    <div class="col-sm-10 col-xs-8">
                                        Has scaffold exceeding a deck height of 4m erected by a licensed scaffolder & handover certificate available?
                                    </div>
                                </div>
                                <div class="row visible-xs">&nbsp;</div>

                                {{-- Question 126 --}}
                                <div class="row">
                                    <div class="col-sm-2 col-xs-4 text-center">
                                        <div class="form-group">
                                            {!! Form::checkbox('question126', '1', false, ['class' => 'make-switch', 'data-size' => 'small', 'data-on-text'=>'Yes', 'data-on-color'=>'success', 'data-off-text'=>'No', 'data-off-color'=>'danger']) !!}
                                        </div>
                                    </div>
                                    <div class="col-sm-10 col-xs-8">
                                        Safe Work Load (SWL) of scaffold bays not exceeded? (including weight of persons, tooling, materials etc) <a id="info_126" class="btn btn-xs btn-info infofield">info</a>
                                        <div id="info_126_div" class="note note-info" style="display: none">
                                            Scaffold SWL as per SafeWork NSW Code Of Practice - Preventing Falls in Housing Construction<br>
                                            Scaffold working platforms are generally rated as light, medium or heavy duty:<br>
                                            • Light Duty - up to 225kg per bay. Examples include painting, electrical work, many carpenty tasks and other light tasks. Platforms should be at least two planks wide (approximately 450mm)<br>
                                            • Medium Duty - up to 450kg per bay. This is suitable for general trades work. Platforms should be at least four planks wide (approximately 900mm)<br>
                                            • Heavy Duty - up to 675kg per bay. This is what is needed for bricklaying, concreting, demolition work and most other work tasks involving heavy loads or heavy impact forces. Platforms should be at leasr 5 planks wide (approximately 1000mm)<br>
                                            • Special Duty - has a designated allowable load as designed<br>
                                        </div>
                                    </div>
                                </div>
                                <div class="row visible-xs">&nbsp;</div>

                                {{-- Question 127 --}}
                                <div class="row">
                                    <div class="col-sm-2 col-xs-4 text-center">
                                        <div class="form-group">
                                            {!! Form::checkbox('question127', '1', false, ['class' => 'make-switch', 'data-size' => 'small', 'data-on-text'=>'Yes', 'data-on-color'=>'success', 'data-off-text'=>'No', 'data-off-color'=>'danger']) !!}
                                        </div>
                                    </div>
                                    <div class="col-sm-10 col-xs-8">
                                        Is the scaffold complete (platform full width, handrail, toeboards and access to platforms compliant)
                                    </div>
                                </div>
                                <div class="row visible-xs">&nbsp;</div>

                                {{-- Question 128 --}}
                                <div class="row">
                                    <div class="col-sm-2 col-xs-4 text-center">
                                        <div class="form-group">
                                            {!! Form::checkbox('question128', '1', false, ['class' => 'make-switch', 'data-size' => 'small', 'data-on-text'=>'Yes', 'data-on-color'=>'success', 'data-off-text'=>'No', 'data-off-color'=>'danger']) !!}
                                        </div>
                                    </div>
                                    <div class="col-sm-10 col-xs-8">
                                        Gaps between the face of the building or structure and the erected scaffold do not exceed 225mm?
                                    </div>
                                </div>
                                <div class="row visible-xs">&nbsp;</div>

                                {{-- Question 129 --}}
                                <div class="row">
                                    <div class="col-sm-2 col-xs-4 text-center">
                                        <div class="form-group">
                                            {!! Form::checkbox('question129', '1', false, ['class' => 'make-switch', 'data-size' => 'small', 'data-on-text'=>'Yes', 'data-on-color'=>'success', 'data-off-text'=>'No', 'data-off-color'=>'danger']) !!}
                                        </div>
                                    </div>
                                    <div class="col-sm-10 col-xs-8">
                                        Is edge protection provided at every open edge of the work platform?
                                    </div>
                                </div>
                                <div class="row visible-xs">&nbsp;</div>

                                {{-- Question 130 --}}
                                <div class="row">
                                    <div class="col-sm-2 col-xs-4 text-center">
                                        <div class="form-group">
                                            {!! Form::checkbox('question130', '1', false, ['class' => 'make-switch', 'data-size' => 'small', 'data-on-text'=>'Yes', 'data-on-color'=>'success', 'data-off-text'=>'No', 'data-off-color'=>'danger']) !!}
                                        </div>
                                    </div>
                                    <div class="col-sm-10 col-xs-8">
                                        4 metre approach distance from overhead powerlines has been maintained in any direction where metallic scaffold is erected
                                    </div>
                                </div>
                                <div class="row visible-xs">&nbsp;</div>

                                {{-- Question 131 --}}
                                <div class="row">
                                    <div class="col-sm-2 col-xs-4 text-center">
                                        <div class="form-group">
                                            {!! Form::checkbox('question131', '1', false, ['class' => 'make-switch', 'data-size' => 'small', 'data-on-text'=>'Yes', 'data-on-color'=>'success', 'data-off-text'=>'No', 'data-off-color'=>'danger']) !!}
                                        </div>
                                    </div>
                                    <div class="col-sm-10 col-xs-8">
                                        Electrical wires or apparatus that pass through a scaffold have been de-energised or fully enclosed to the requirements of the network operator?
                                    </div>
                                </div>
                                <div class="row visible-xs">&nbsp;</div>
                            </div> {{-- end Scaffolds}}


                            {{-- Safe site --}}
                            <h4 class="font-green-haze">Hazards</h4>
                            <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                            <div class="row">
                                <div class="col-sm-2 col-xs-4 text-center">
                                    <div class="form-group">
                                        {!! Form::checkbox('safe_site', '1', false, ['class' => 'make-switch', 'data-size' => 'small', 'data-on-text'=>'Yes', 'data-on-color'=>'success', 'data-off-text'=>'No', 'data-off-color'=>'danger', 'id'=>'safe_site']) !!}
                                    </div>
                                </div>
                                <div class="col-sm-10 col-xs-8">
                                    I have <b>conducted my own assessment</b> of the site and believe it to be <b>safe to work</b>
                                </div>
                            </div>

                            <!-- Unsafe Site Fields -->
                            <div id="unsafe-site">
                                <h5><b>Hazard Details</b></h5>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group {!! fieldHasError('location', $errors) !!}">
                                            {!! Form::label('location', 'Location of hazard (eg. bathroom, first floor addition, kitchen, backyard)', ['class' => 'control-label']) !!}
                                            {!! Form::text('location', null, ['class' => 'form-control']) !!}
                                            {!! fieldErrorMessage('location', $errors) !!}
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group {!! fieldHasError('rating', $errors) !!}">
                                            {!! Form::label('rating', 'Risk Rating', ['class' => 'control-label']) !!}
                                            {!! Form::select('rating', ['' => 'Select rating', '1' => "Low", '2' => 'Medium', '3' => 'High', '4' => 'Extreme'], null, ['class' => 'form-control bs-select']) !!}
                                            {!! fieldErrorMessage('rating', $errors) !!}
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group {!! fieldHasError('reason', $errors) !!}">
                                            {!! Form::label('reason', 'What is the hazard / safety issue?', ['class' => 'control-label']) !!}
                                            {!! Form::textarea('reason', null, ['rows' => '3', 'class' => 'form-control']) !!}
                                            {!! fieldErrorMessage('reason', $errors) !!}
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group {!! fieldHasError('action', $errors) !!}">
                                            {!! Form::label('action', 'What action/s (if any) have you taken to resolve the issue?', ['class' => 'control-label']) !!}
                                            {!! Form::textarea('action', null, ['rows' => '3', 'class' => 'form-control']) !!}
                                            {!! fieldErrorMessage('action', $errors) !!}
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <div class="fileinput fileinput-new" data-provides="fileinput">
                                                <div class="fileinput-preview fileinput-exists thumbnail"
                                                     style="max-width: 200px; max-height: 150px;"></div>
                                                <div>
                                                        <span class="btn default btn-file">
                                                            <span class="fileinput-new"> Upload Photo/Video of issue</span>
                                                            <span class="fileinput-exists"> Change </span>
                                                            <input type="file" name="media">
                                                        </span>
                                                    <a href="javascript:;" class="btn default fileinput-exists"
                                                       data-dismiss="fileinput">Remove </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!--
                                <div class="row visible-xs">
                                    <div class="form-group">
                                        <label for="media">File input</label>
                                        <input type="file" name="media2" id="media2">
                                        <p class="help-block"> some help text here. </p>
                                    </div>
                                </div>
                                -->
                                <div class="row">
                                    <div class="col-sm-2 col-xs-4 text-center">
                                        <div class="form-group">
                                            {!! Form::checkbox('action_required', '1', null,
                                             ['class' => 'make-switch', 'data-size' => 'small',
                                             'data-on-text'=>'Yes', 'data-on-color'=>'success',
                                             'data-off-text'=>'No', 'data-off-color'=>'danger']) !!}
                                        </div>
                                    </div>
                                    <div class="col-sm-10 col-xs-8">
                                        Does {{ $worksite->company->name }} need to take any action?
                                    </div>
                                </div>
                            </div>
                            <div class="form-actions">
                                <button type="submit" class="btn green" name="checkinSupervisor" value="true">Submit</button>
                            </div>
                        </div> <!--/form-body-->
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
    @stop <!-- END Content -->


@section('page-level-plugins-head')
    <link href="/assets/global/plugins/bootstrap-fileinput/bootstrap-fileinput.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/bootstrap-select/css/bootstrap-select.min.css" rel="stylesheet" type="text/css"/>
@stop

@section('page-level-plugins')
    <script src="/assets/global/plugins/bootstrap-select/js/bootstrap-select.min.js" type="text/javascript"></script>
@stop

@section('page-level-scripts') {{-- Metronic + custom Page Scripts --}}
<script src="/assets/global/plugins/bootstrap-fileinput/bootstrap-fileinput.js" type="text/javascript"></script>
<script src="/assets/pages/scripts/components-bootstrap-select.min.js" type="text/javascript"></script>
<script>
    $(document).ready(function () {
        //$('#safe_site').bootstrapSwitch('state', false);
        //var state = $('#safe_site').bootstrapSwitch('state');
        if ($('#safe_site').bootstrapSwitch('state'))
            $('#unsafe-site').hide();

        $('#safe_site').on('switchChange.bootstrapSwitch', function (event, state) {
            $('#unsafe-site').toggle();
        });

        $(".infofield").click(function (e) {
            var event_id = e.target.id.split('_');
            var qid = event_id[1];
            $("#info_" + qid + "_div").toggle();
        });

        $('#question115').on('switchChange.bootstrapSwitch', function (event, state) {
            $("#ladders_div").toggle();
        });

        $('#question117').on('switchChange.bootstrapSwitch', function (event, state) {
            $("#fallingobject_div").toggle();
        });

        $('#question124').on('switchChange.bootstrapSwitch', function (event, state) {
            $("#scaffolds_div").toggle();
        });
    });
</script>
@stop

