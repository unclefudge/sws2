<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Incident Report</title>
    <link href="{{ asset('/') }}/assets/global/plugins/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css"/>
    <link href="{{ asset('/') }}/assets/global/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css"/>
    <style>
        @import url(http://fonts.googleapis.com/css?family=PT+Sans);
        /*@import url(https://fonts.googleapis.com/css?family=Martel+Sans);*/

        @page {
            margin: .7cm .7cm
        }

        body, h1, h2, h3, h4, h5, h6 {
            font-family: 'PT Sans', serif;
        }

        h1 {
            /*font-family: 'Martel Sans', sans-serif;*/
            font-weight: 700;
        }

        body {
            font-size: 10px;
        }

        div.page {
            page-break-after: always;
            page-break-inside: avoid;
        }

        .table-striped>tbody>tr:nth-of-type(odd) {
            background-color: #ffffff;
        }

        .table-striped>tbody>tr:nth-of-type(even) {
            background-color: #fbfbfb;
        }

        .border-right {
            border-right: 1px solid lightgrey;
            margin-bottom: -999px;
            padding-bottom: 999px;
        }

        tr {
            border: none !important;
        }

        .table2 {
            padding: 2px;
        }

        td.pad5 {
            padding: 5px !important;
            line-height: 1em !important;
        }

        footer {
            position: fixed;
            bottom: 0px;
            left: 0px;
            right: 0px;
            height: 20px;
        }

        footer .pagenum:before {
            content: counter(page);
        }
    </style>
</head>

<body>
<div class="container">
    <?php
    $pagecount = 1;

    $qType = App\Models\Misc\FormQuestion::find(1);
    $qTreatment = App\Models\Misc\FormQuestion::find(14);
    $qInjuredPart = App\Models\Misc\FormQuestion::find(21);
    $qInjuredNature = App\Models\Misc\FormQuestion::find(50);
    $qInjuredMechanism = App\Models\Misc\FormQuestion::find(69);
    $qInjuredAgency = App\Models\Misc\FormQuestion::find(92);
    $qConditions = App\Models\Misc\FormQuestion::find(113);
    $qConFactorDefences = App\Models\Misc\FormQuestion::find(125);
    $qConFactorITactions = App\Models\Misc\FormQuestion::find(148);
    $qConFactorWorkplace = App\Models\Misc\FormQuestion::find(167);
    $qConFactorHuman = App\Models\Misc\FormQuestion::find(192);
    $qRootCause = App\Models\Misc\FormQuestion::find(219);
    $qPreventive = App\Models\Misc\FormQuestion::find(236);
    ?>
    <div class="page22">
        <div class="row" style="padding: 5px">
            <div class="col-xs-3"> <img src="{!! URL::to('/') !!}/img/logo-capecod3-large.png" height="40"></div>
            <div class="col-xs-9"><h3 style="margin: 0px">INCIDENT REPORT</h3></div>
        </div>
        {{-- Notification Details --}}
        <div class="row">
            <div class="col-xs-12" style="background-color: #F6F6F6; font-weight: bold;"><h5 style="margin: 0px; padding: 5px 2px 5px 2px">NOTIFICATION DETAILS</h5></div>
        </div>
        <div class="row" style="padding: 0px;">
            <div class="col-xs-2">Incident Date</div>
            <div class="col-xs-3">{{ ($incident->date) ?  $incident->date->format('d/m/Y g:i a') : '' }}</div>
            <div class="col-xs-1">Incident Type</div>
            <div class="col-xs-6">{!! $qType->responsesCSV('site_incidents', $incident->id) !!}</div>
        </div>
        <div class="row" style="padding: 0px">
            <div class="col-xs-2">{{ ($incident->site_id) ? 'Site:' : 'Place of incident:'}}</div>
            <div class="col-xs-3">
                @if ($incident->site)
                    <b>{!! $incident->site_name !!}</b><br>
                    {!! $incident->site->address_formatted !!}
                @else
                    {!! $incident->site_name !!}
                @endif
            </div>
            <div class="col-xs-1">Location</div>
            <div class="col-xs-6">{{ $incident->location }}</div>
        </div>
        @if ($incident->site)
            <div class="row" style="padding: 0px;">
                <div class="col-xs-2">Supervisor</div>
                <div class="col-xs-10">{!! $incident->site_supervisor !!}</div>
            </div>
        @endif
        <div class="row" style="padding: 0px;">
            <div class="col-xs-2">What occured</div>
            <div class="col-xs-10">{!! nl2br($incident->describe) !!}</div>
        </div>
        <div class="row" style="padding: 0px;">
            <div class="col-xs-2">Actions taken</div>
            <div class="col-xs-10">{!! nl2br($incident->actions_taken) !!}</div>
        </div>


        {{-- Persons Involved --}}
        <div class="row">
            <div class="col-xs-12" style="background-color: #F6F6F6; font-weight: bold;"><h5 style="margin: 0px; padding: 5px 2px 5px 2px">PERSONS INVOLVED</h5></div>
        </div>
        @foreach ($incident->people as $person)
            <div class="row" style="padding: 0px;">
                <div class="col-xs-2">Involvement type</div>
                <div class="col-xs-3">{{ $person->typeName }}</div>
                <div class="col-xs-1"></div>
                <div class="col-xs-6"></div>
            </div>
            <div class="row" style="padding: 0px;">
                <div class="col-xs-2">Full name</div>
                <div class="col-xs-3">{{ $person->name }}</div>
                <div class="col-xs-1">Date of Birth</div>
                <div class="col-xs-6">{{ ($person->dob) ? $person->dob->format('d/m/Y') : '' }}</div>
            </div>
            <div class="row" style="padding: 0px;">
                <div class="col-xs-2">Contact</div>
                <div class="col-xs-3">{{ $person->contact }}</div>
                <div class="col-xs-1">Address</div>
                <div class="col-xs-6">{{ $person->address }}</div>
            </div>
            <div class="row" style="padding: 0px;">
                <div class="col-xs-2">Engagement type</div>
                <div class="col-xs-3">{{ $person->engagement }}</div>
                <div class="col-xs-1">Employer</div>
                <div class="col-xs-6">{{ $person->employer }}</div>
            </div>
            <div class="row" style="padding: 0px;">
                <div class="col-xs-2">Occupation</div>
                <div class="col-xs-3">{{ $person->occupation }}</div>
                <div class="col-xs-1">Supervisor/PCBU</div>
                <div class="col-xs-6">{{ $person->supervisor }}</div>
            </div>
            @if (!$loop->last)
                <hr style="margin: 0px">
            @endif
        @endforeach

        {{-- Witness Statement --}}
        <div class="row">
            <div class="col-xs-12" style="background-color: #F6F6F6; font-weight: bold;"><h5 style="margin: 0px; padding: 5px 2px 5px 2px">WITNESS STATEMENTS</h5></div>
        </div>
        @foreach ($incident->witness as $witness)
            <div class="row" style="padding: 0px;">
                <div class="col-xs-2">Full name</div>
                <div class="col-xs-3">{{ $witness->name }}</div>
                <div class="col-xs-7"></div>
            </div>
            <div class="row" style="padding: 0px;">
                <div class="col-xs-12">In your own words describe the events leading up to the incident:<br>{!! nl2br($witness->event_before) !!}<br><br></div>
            </div>
            <div class="row" style="padding: 0px;">
                <div class="col-xs-12">In your own words describe the events:<br>{!! nl2br($witness->event) !!}<br><br></div>
            </div>
            <div class="row" style="padding: 0px;">
                <div class="col-xs-12">In your own words describe what happened after the incident:<br>{!! nl2br($witness->event_after) !!}<br><br></div>
            </div>
            @if ($witness->notes)
                <div class="row" style="padding: 0px;">
                    <div class="col-xs-12">Admin Notes:<br>{!! nl2br($witness->notes) !!}<br><br></div>
                </div>
            @endif
            @if (!$loop->last)
                <hr style="margin: 0px">
            @endif
        @endforeach

        {{-- Conversation --}}
        <div class="row">
            <div class="col-xs-12" style="background-color: #F6F6F6; font-weight: bold;"><h5 style="margin: 0px; padding: 5px 2px 5px 2px">RECORD OF CONVERSATION</h5></div>
        </div>
        @foreach ($incident->conversations as $conversation)
            <div class="row" style="padding: 0px;">
                <div class="col-xs-2">Conversation between</div>
                <div class="col-xs-3">{{ $conversation->name }}</div>
                <div class="col-xs-1">Started at</div>
                <div class="col-xs-5">{{ ($conversation->start) ? $conversation->start->format('d/m/Y H:i') : '' }}</div>
            </div>
            <div class="row" style="padding: 0px;">
                <div class="col-xs-2">Witness</div>
                <div class="col-xs-3">{{ $conversation->witness }}</div>
                <div class="col-xs-1">Ended at</div>
                <div class="col-xs-5">{{ ($conversation->end) ? $conversation->end->format('d/m/Y H:i') : '' }}</div>
            </div>
            <div class="row" style="padding: 0px;">
                <div class="col-xs-2">Conversation Details</div>
                <div class="col-xs-9">{!! nl2br($conversation->details) !!}<br><br></div>
            </div>
            @if (!$loop->last)
                <hr style="margin: 0px">
            @endif
        @endforeach


        {{-- Incident Details --}}
        <div class="row">
            <div class="col-xs-12" style="background-color: #F6F6F6; font-weight: bold;"><h5 style="margin: 0px; padding: 5px 2px 5px 2px">DETAILS OF INCIDENT</h5></div>
        </div>
        <div class="row" style="padding: 0px;">
            <div class="col-xs-2">Risk Potential</div>
            <div class="col-xs-3">{!! $incident->riskRatingText('risk_potential') !!}</div>
            <div class="col-xs-1">Risk Actual</div>
            <div class="col-xs-4">{!! $incident->riskRatingText('risk_actual') !!}</div>
        </div>
        <div class="row">
            <div class="col-xs-2">Incident Summary</div>
            <div class="col-xs-9">{!! nl2br($incident->exec_summary) !!}<br><br></div>
        </div>
        <div class="row">
            <div class="col-xs-2">Description</div>
            <div class="col-xs-9">{!! nl2br($incident->exec_describe) !!}<br><br></div>
        </div>
        <div class="row">
            <div class="col-xs-2">Immediate actions</div>
            <div class="col-xs-9">{!! nl2br($incident->exec_actions) !!}<br><br></div>
        </div>
        <div class="row">
            <div class="col-xs-2">Notifiable incident?</div>
            <div class="col-xs-3">@if ($incident->notifiable != null){!! ($incident->notifiable) ? 'Yes' : 'No'!!}@endif</div>
            <div class="col-xs-2">@if ($incident->notifiable) Notifiable Context @endif </div>
            <div class="col-xs-4">@if ($incident->notifiable) {!! nl2br($incident->notifiable_reason) !!} @endif </div>
        </div>

        {{-- Notifiable --}}
        @if ($incident->notifiable)
            <div class="row">
                <div class="col-xs-12" style="background-color: #F6F6F6; font-weight: bold;"><h5 style="margin: 0px; padding: 5px 2px 5px 2px">NOTIFIABLE INCIDENT & REGULATOR ACTION DETAILS</h5></div>
            </div>
            <div class="row" style="padding: 0px;">
                <div class="col-xs-2">Regulator</div>
                <div class="col-xs-3">{!! $incident->regulator !!}</div>
                <div class="col-xs-1">Regulator Ref</div>
                <div class="col-xs-5">{!! $incident->regulator_ref !!}</div>
            </div>
            <div class="row" style="padding: 0px;">
                <div class="col-xs-2">Notified Date</div>
                <div class="col-xs-3">{!! ($incident->regulator_date) ? $incident->regulator_date->format('d/m/Y') : '' !!}</div>
                <div class="col-xs-1">Inspector</div>
                <div class="col-xs-5">{!! $incident->inspector !!}</div>
            </div>
            <div class="row">
                <div class="col-xs-2">Notes</div>
                <div class="col-xs-9">{!! nl2br($incident->notes) !!}<br><br></div>
            </div>
        @endif

        {{-- Injury Details --}}
        @if ($incident->isInjury())
            <div class="row">
                <div class="col-xs-12" style="background-color: #F6F6F6; font-weight: bold;"><h5 style="margin: 0px; padding: 5px 2px 5px 2px">DETAILS OF INJURY</h5></div>
            </div>
            <div class="row" style="padding: 0px;">
                <div class="col-xs-2">Treatment</div>
                <div class="col-xs-3">{!! $qTreatment->responsesCSV('site_incidents', $incident->id) !!}</div>
                <div class="col-xs-1">Part(s) Injured</div>
                <div class="col-xs-5">{!! $qInjuredPart->responsesCSV('site_incidents', $incident->id) !!}</div>
            </div>
            <div class="row" style="padding: 0px;">
                <div class="col-xs-2">Nature of injury</div>
                <div class="col-xs-9">{!! $qInjuredNature->responsesBullet2('site_incidents', $incident->id) !!}</div>
            </div>
            <div class="row" style="padding: 0px;">
                <div class="col-xs-2">Mechanism of injury</div>
                <div class="col-xs-9">{!! $qInjuredMechanism->responsesBullet2('site_incidents', $incident->id) !!}</div>
            </div>
            <div class="row" style="padding: 0px;">
                <div class="col-xs-2">Agency of injury</div>
                <div class="col-xs-9">{!! $qInjuredAgency->responsesBullet2('site_incidents', $incident->id) !!}</div>
            </div>
        @endif

        {{-- Damage Details --}}
        @if ($incident->isDamage())
            <div class="row">
                <div class="col-xs-12" style="background-color: #F6F6F6; font-weight: bold;"><h5 style="margin: 0px; padding: 5px 2px 5px 2px">DAMAGE</h5></div>
            </div>
            <div class="row" style="padding: 0px;">
                <div class="col-xs-2">Damage Details</div>
                <div class="col-xs-3">{{  $incident->damage }}</div>
                <div class="col-xs-1">Repair Cost</div>
                <div class="col-xs-6">{!! $incident->damage_cost !!}</div>
            </div>
            <div class="row" style="padding: 0px;">
                <div class="col-xs-2">Repair Details</div>
                <div class="col-xs-9">{!! nl2br($incident->damage_repair) !!}</div>
            </div>
        @endif

        {{-- Notes --}}
        @if ($incident->actions->count())
            <div class="row">
                <div class="col-xs-12" style="background-color: #F6F6F6; font-weight: bold;"><h5 style="margin: 0px; padding: 5px 2px 5px 2px">NOTES</h5></div>
            </div>
            @foreach ($incident->actions as $action)
                <div class="row">
                    <div class="col-xs-2">{{ $action->created_at->format('d/m/Y') }}</div>
                    <div class="col-xs-9">{{ $action->action }}<br>- {{ $action->user->fullname }} </div>
                </div>
                <hr style="margin: 0px">
            @endforeach
        @endif

        {{-- Assigned Tasks --}}
        @if ($incident->todos()->count())
            <div class="row">
                <div class="col-xs-12" style="background-color: #F6F6F6; font-weight: bold;"><h5 style="margin: 0px; padding: 5px 2px 5px 2px">ASSIGNED TASKS</h5></div>
            </div>
            @foreach ($incident->todos() as $todo)
                <div class="row">
                    <div class="col-xs-2">Action</div>
                    <div class="col-xs-9">{{ $todo->info }}</div>
                </div>
                @if ($todo->comments)
                <div class="row">
                    <div class="col-xs-2">Comments</div>
                    <div class="col-xs-9">{{ $todo->comments }}</div>
                </div>
                @endif
                <div class="row">
                    <div class="col-xs-2">Completed By</div>
                    <div class="col-xs-9">
                        @if ($todo->status && !$todo->done_by)
                            Outstanding
                        @else
                            {!! $todo->doneBy->full_name  !!} {{ ($todo->done_at) ? '('.$todo->done_at->format('d/m/Y').')' : '' }}
                        @endif
                    </div>
                </div>
                <hr style="margin: 0px">
            @endforeach
        @endif

        {{-- Reviewed --}}
        <div class="row">
            <div class="col-xs-12" style="background-color: #F6F6F6; font-weight: bold;"><h5 style="margin: 0px; padding: 5px 2px 5px 2px">REVIEW</h5></div>
        </div>
        @if ($incident->reviews()->count())
            @foreach ($incident->reviews() as $review)
                <?php
                list($crap, $review_role) = explode(' : ', $review->name);
                ?>
                <div class="row" style="padding: 0px;">
                    <div class="col-xs-2">Name</div>
                    <div class="col-xs-3">{!! ($review->assignedToBySBC()) ? $review->assignedToBySBC() : '' !!}</div>
                    <div class="col-xs-1">Role</div>
                    <div class="col-xs-6">{{ $review_role }}</div>
                </div>
                <div class="row" style="padding: 0px;">
                    <div class="col-xs-2">Date Signed</div>
                    <div class="col-xs-3">{!! ($review->done_at) ? $review->done_at->format('d/m/Y') : '' !!}</div>
                    <div class="col-xs-1">{!! ($review->comments) ? 'Comments' : '' !!}</div>
                    <div class="col-xs-6">{!! ($review->comments) ? $review->comments : '' !!}</div>
                </div>
                @if (!$loop->last)
                    <hr style="margin: 0px">
                @endif
            @endforeach
        @else
            <div class="row" style="padding: 0px;">
                <div class="col-xs-12">No reviews</div>
            </div>
        @endif

        {{-- PAGE 2 --}}
        <div class="page"></div>

        {{-- Investigation & Analysis --}}
        <div class="row" style="padding: 5px">
            <div class="col-xs-3"> <img src="{!! URL::to('/') !!}/img/logo-capecod3-large.png" height="40"></div>
            <div class="col-xs-9"><h3 style="margin: 0px">INCIDENT INVESTIGATION & ANALYSIS</h3></div>
        </div>

        {{-- Incident Conditions --}}
        <div class="row">
            <div class="col-xs-12" style="background-color: #F6F6F6; font-weight: bold;"><h5 style="margin: 0px; padding: 5px 2px 5px 2px">INCIDENT CONDITIONS</h5></div>
        </div>
        @if ($qConditions->responsesCSV('site_incidents', $incident->id))
            @foreach ($qConditions->optionsArray() as $id => $label)
                @if ($qConditions->responseOther('site_incidents', $incident->id, $id))
                    <div class="row">
                        <div class="col-xs-2">{{ $label }}</div>
                        <div class="col-xs-9">{!! $qConditions->responseOther('site_incidents', $incident->id, $id) !!}</div>
                    </div>
                @endif
            @endforeach
        @else
            <div class="row">
                <div class="col-xs-12">No conditions specified</div>
            </div>
        @endif

        {{-- Contributing Factors --}}
        <div class="row">
            <div class="col-xs-12" style="background-color: #F6F6F6; font-weight: bold;"><h5 style="margin: 0px; padding: 5px 2px 5px 2px">CONTRIBUTING FACTORS</h5></div>
        </div>
        @if ($qConFactorDefences->responsesCSV('site_incidents', $incident->id))
            <div class="row">
                <div class="col-xs-2">Absent / Failed Defences</div>
                <div class="col-xs-9">{!! $qConFactorDefences->responsesBullet2('site_incidents', $incident->id) !!}</div>
            </div>
        @endif
        @if ($qConFactorITactions->responsesCSV('site_incidents', $incident->id))
            <div class="row">
                <div class="col-xs-2">Individual / Team Actions</div>
                <div class="col-xs-9">{!! $qConFactorITactions->responsesBullet2('site_incidents', $incident->id) !!}</div>
            </div>
        @endif
        @if ($qConFactorWorkplace->responsesCSV('site_incidents', $incident->id))
            <div class="row">
                <div class="col-xs-2">Workplace Conditions</div>
                <div class="col-xs-9">{!! $qConFactorWorkplace->responsesBullet2('site_incidents', $incident->id) !!}</div>
            </div>
        @endif
        @if ($qConFactorHuman->responsesCSV('site_incidents', $incident->id))
            <div class="row">
                <div class="col-xs-2">Human Factors</div>
                <div class="col-xs-9">{!! $qConFactorHuman->responsesBullet2('site_incidents', $incident->id) !!}</div>
            </div>
        @endif
        @if (!$qConFactorDefences->responsesCSV('site_incidents', $incident->id) && !$qConFactorITactions->responsesCSV('site_incidents', $incident->id) &&
         !$qConFactorWorkplace->responsesCSV('site_incidents', $incident->id) &&  !$qConFactorHuman->responsesCSV('site_incidents', $incident->id))
            <div class="row">
                <div class="col-xs-12">No factors specfied</div>
            </div>
        @endif

        {{-- Root Causes --}}
        <div class="row">
            <div class="col-xs-12" style="background-color: #F6F6F6; font-weight: bold;"><h5 style="margin: 0px; padding: 5px 2px 5px 2px">ROOT CAUSE - ORGANISATION FACTORS</h5></div>
        </div>
        @if ($qRootCause->responsesCSV('site_incidents', $incident->id))
            @foreach ($qRootCause->optionsArray() as $id => $label)
                @if ($qRootCause->responseOther('site_incidents', $incident->id, $id))
                    <div class="row">
                        <div class="col-xs-2">{{ $label }}</div>
                        <div class="col-xs-9">{!! $qRootCause->responseOther('site_incidents', $incident->id, $id) !!}</div>
                    </div>
                @endif
            @endforeach
        @else
            <div class="row">
                <div class="col-xs-12">No conditions specified</div>
            </div>
        @endif

        {{-- Actions to Prevent Reoccurence --}}
        <div class="row">
            <div class="col-xs-12" style="background-color: #F6F6F6; font-weight: bold;"><h5 style="margin: 0px; padding: 5px 2px 5px 2px">ACTIONS TO PREVENT REOCCURENCE</h5></div>
        </div>
        <div class="row">
            <div class="col-xs-2">Preventive Strategies:</div>
            @if ($qPreventive->responsesCSV('site_incidents', $incident->id))
                <div class="col-xs-9">{!! $qPreventive->responsesBullet2('site_incidents', $incident->id) !!}</div>
            @else
                <div class="col-xs-9">No actions specified</div>
            @endif
        </div>
        <hr style="margin: 0px">

        {{-- Actions --}}
        @foreach ($incident->preventActions() as $action)
            <?php list($crap, $action_name) = explode(' : ', $action->name); ?>
            <div class="row">
                <div class="col-xs-3">Contributing Factor / Root cause</div>
                <div class="col-xs-7">{{ $action_name }}</div>
            </div>
            <div class="row">
                <div class="col-xs-3">Actions Taken / Recommended</div>
                <div class="col-xs-7">{!! ($action->info) ? $action->info."<br>" : '' !!}{!! ($action->comments) ? "<b>Notes:</b> $action->comments<br>" : '' !!}</div>
            </div>
            <div class="row">
                <div class="col-xs-6">
                    By Whom: &nbsp;
                    @if ($action->doneBy)
                        {{ $action->doneBy->fullname }}
                    @else
                        {!! ($action->assignedToBySBC()) ? $action->assignedToBySBC() : 'Unassigned' !!}
                    @endif</div>
                <div class="col-xs-6">Completed: &nbsp; {{ ($action->done_at) ? $action->done_at->format('d/m/Y') : 'Incomplete' }}</div>
            </div>
            @if (!$loop->last)
                <hr style="margin: 0px">
            @endif

            @if (!$loop->last)
                <hr style="margin: 0px">
            @endif
        @endforeach
    </div>
</div>
</body>
</html>