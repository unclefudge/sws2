@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        <li><a href="/settings">Settings</a><i class="fa fa-circle"></i></li>
        <li><a href="/settings/website-form-submission">Website Form Submissions</a><i class="fa fa-circle"></i></li>
        <li><span>View</span></li>
    </ul>
@stop

@section('content')

    @php
        /*
         * Friendly labels for values saved in the raw form payload.
         * These keep the page readable for staff who do not know the code values.
         */
        $workLabels = [
            'first_floor' => 'First Floor Addition (second storey)',
            'ground_floor' => 'Ground Floor Extension (above 50m²)',
            'major_internal' => 'Major Internal Renovation',
            'other_unsure' => 'Other/Unsure',
        ];

        $roomLabels = [
            'walk_in_robe' => 'Walk-in Robe',
            'ensuite' => 'Ensuite',
            'bathroom' => 'Bathroom',
            'living' => 'Living',
            'sitting' => 'Sitting',
            'study' => 'Study',
            'dining' => 'Dining',
            'kitchen' => 'Kitchen',
            'laundry' => 'Laundry',
            'other' => 'Other',
            'balcony' => 'Balcony',
            'deck' => 'Deck',
            'garage' => 'Garage',
            'carport' => 'Carport',
        ];

        $contactMethodLabels = [
            'phone' => 'Phone',
            'email' => 'Email',
            'either' => 'Either',
        ];

        $bestContactTimeLabels = [
            'business_hours' => 'Business Hours',
            'mornings_only' => 'Mornings only',
            'anytime_9_8' => 'Anytime (9am-8pm)',
            'evenings_only' => 'Evenings only',
        ];

        $commenceLabels = [
            '6_12_months' => '6-12 months',
            'over_12_months' => 'Beyond 12 months',
        ];

        $payload = $submission->payload ?? [];
        $stepOne = $payload['step_1'] ?? [];
        $finalSubmission = $payload['final_submission'] ?? [];
        $meta = $payload['meta'] ?? [];

        $zohoResponse = $submission->zoho_response ?? [];
        $zohoFirstData = $zohoResponse['data'][0] ?? null;

        $displayValue = function ($value) {
            if (is_array($value)) {
                return count($value) ? implode(', ', $value) : '—';
            }

            if ($value === null || $value === '') {
                return '—';
            }

            return $value;
        };

        $mapArrayLabels = function ($values, $labels) {
            $values = (array) $values;

            return collect($values)->map(fn ($value) => $labels[$value] ?? $value)->filter()->values()->all();
        };

        $workSelected = $mapArrayLabels($stepOne['work_type'] ?? $finalSubmission['work_type'] ?? [], $workLabels);
        $roomsSelected = $mapArrayLabels($finalSubmission['new_rooms'] ?? [], $roomLabels);

        $preferredContact = $finalSubmission['preferred_contact_method'] ?? null;
        $bestContactTime = $finalSubmission['best_contact_time'] ?? null;
        $commenceTime = $finalSubmission['commence_time'] ?? null;

        $zohoCode = $zohoFirstData['code'] ?? ($zohoResponse['code'] ?? null);
        $zohoMessage = $zohoFirstData['message'] ?? ($zohoResponse['message'] ?? null);
        $zohoStatus = $zohoFirstData['status'] ?? ($zohoResponse['status'] ?? $submission->zoho_status);
        $zohoDetails = $zohoFirstData['details'] ?? ($zohoResponse['details'] ?? []);
    @endphp

    <style>
        .submission-label {
            display: block;
            color: #6b6f76;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            margin-bottom: 2px;
        }

        .submission-value {
            color: #333;
            font-size: 14px;
            word-break: break-word;
        }

        .submission-section {
            margin-bottom: 20px;
        }

        .submission-section-title {
            margin: 0 0 15px;
            font-weight: 600;
            color: #3f444a;
            border-bottom: 1px solid #e7ecf1;
            padding-bottom: 10px;
        }

        .submission-detail-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 14px 22px;
        }

        .submission-detail-item {
            padding-bottom: 12px;
            border-bottom: 1px solid #f2f4f7;
        }

        .submission-text-box {
            background: #f7f9fb;
            border: 1px solid #e7ecf1;
            padding: 12px 14px;
            line-height: 1.6;
            white-space: pre-wrap;
            word-break: break-word;
        }

        .submission-pill-list {
            display: flex;
            flex-wrap: wrap;
            gap: 7px;
            margin-top: 4px;
        }

        .submission-pill {
            display: inline-block;
            padding: 5px 9px;
            border-radius: 12px;
            background: #eef3f7;
            color: #333;
            font-size: 12px;
            line-height: 1.2;
        }

        .submission-technical {
            margin-top: 12px;
        }

        .submission-technical summary {
            cursor: pointer;
            color: #337ab7;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .submission-technical pre {
            white-space: pre-wrap;
            word-break: break-word;
            background: #f7f7f7;
            border: 1px solid #ddd;
            padding: 15px;
            margin-top: 8px;
        }

        @media (max-width: 991px) {
            .submission-detail-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <div class="page-content-inner">
        <div class="row">
            <div class="col-lg-8 col-xs-12 col-sm-12">
                <div class="portlet light bordered submission-section">
                    <div class="portlet-title">
                        <div class="caption font-green-haze">
                            <span class="caption-subject bold uppercase">Enquiry Details</span>
                        </div>
                    </div>

                    <div class="portlet-body">
                        <h4 class="submission-section-title">Step 1 - Initial Enquiry</h4>

                        <div class="submission-detail-grid">
                            <div class="submission-detail-item">
                                <span class="submission-label">Email</span>
                                <span class="submission-value">{{ $displayValue($stepOne['email'] ?? $submission->email) }}</span>
                            </div>

                            <div class="submission-detail-item">
                                <span class="submission-label">Suburb</span>
                                <span class="submission-value">{{ $displayValue($stepOne['suburb'] ?? $submission->suburb) }}</span>
                            </div>

                            <div class="submission-detail-item">
                                <span class="submission-label">Postcode</span>
                                <span class="submission-value">{{ $displayValue($stepOne['suburb_postcode'] ?? $submission->postcode) }}</span>
                            </div>

                            <div class="submission-detail-item">
                                <span class="submission-label">State</span>
                                <span class="submission-value">{{ $displayValue($stepOne['suburb_state'] ?? $submission->state) }}</span>
                            </div>

                            <div class="submission-detail-item">
                                <span class="submission-label">Owns Property?</span>
                                <span class="submission-value">{{ $displayValue($stepOne['pre_purchase'] ?? $finalSubmission['pre_purchase'] ?? null) }}</span>
                            </div>

                            <div class="submission-detail-item">
                                <span class="submission-label">Rejection Reason</span>
                                <span class="submission-value">{{ $displayValue($submission->rejection_reason) }}</span>
                            </div>
                        </div>

                        <div style="margin-top: 16px;">
                            <span class="submission-label">Renovation Type Selected</span>

                            @if(count($workSelected))
                                <div class="submission-pill-list">
                                    @foreach($workSelected as $work)
                                        <span class="submission-pill">{{ $work }}</span>
                                    @endforeach
                                </div>
                            @else
                                <span class="submission-value">—</span>
                            @endif
                        </div>

                        @if(!empty($finalSubmission))
                            <h4 class="submission-section-title" style="margin-top: 28px;">Step 2 - Contact & Property Details</h4>

                            <div class="submission-detail-grid">
                                <div class="submission-detail-item">
                                    <span class="submission-label">Full Name</span>
                                    <span class="submission-value">{{ $displayValue($finalSubmission['full_name'] ?? null) }}</span>
                                </div>

                                <div class="submission-detail-item">
                                    <span class="submission-label">Contact Numbers</span>
                                    <span class="submission-value">{{ $displayValue($finalSubmission['contact_numbers'] ?? null) }}</span>
                                </div>

                                <div class="submission-detail-item">
                                    <span class="submission-label">Street Address</span>
                                    <span class="submission-value">{{ $displayValue($finalSubmission['street_address'] ?? null) }}</span>
                                </div>

                                <div class="submission-detail-item">
                                    <span class="submission-label">Postal Address</span>
                                    <span class="submission-value">{{ $displayValue($finalSubmission['postal_address'] ?? null) }}</span>
                                </div>

                                <div class="submission-detail-item">
                                    <span class="submission-label">Preferred Contact Method</span>
                                    <span class="submission-value">{{ $displayValue($contactMethodLabels[$preferredContact] ?? $preferredContact) }}</span>
                                </div>

                                <div class="submission-detail-item">
                                    <span class="submission-label">Best Contact Time</span>
                                    <span class="submission-value">{{ $displayValue($bestContactTimeLabels[$bestContactTime] ?? $bestContactTime) }}</span>
                                </div>

                                <div class="submission-detail-item">
                                    <span class="submission-label">How They Heard About Us</span>
                                    <span class="submission-value">{{ $displayValue($finalSubmission['heard_about'] ?? null) }}</span>
                                </div>

                                <div class="submission-detail-item">
                                    <span class="submission-label">Building Commencement</span>
                                    <span class="submission-value">{{ $displayValue($commenceLabels[$commenceTime] ?? $commenceTime) }}</span>
                                </div>

                                <div class="submission-detail-item">
                                    <span class="submission-label">Bedrooms</span>
                                    <span class="submission-value">{{ $displayValue($finalSubmission['bedrooms'] ?? null) }}</span>
                                </div>

                                <div class="submission-detail-item">
                                    <span class="submission-label">House Style</span>
                                    <span class="submission-value">{{ $displayValue($finalSubmission['house_style'] ?? null) }}</span>
                                </div>

                                <div class="submission-detail-item">
                                    <span class="submission-label">Materials</span>
                                    <span class="submission-value">{{ $displayValue($finalSubmission['materials'] ?? null) }}</span>
                                </div>

                                <div class="submission-detail-item">
                                    <span class="submission-label">Build Year</span>
                                    <span class="submission-value">{{ $displayValue($finalSubmission['build_year'] ?? null) }}</span>
                                </div>

                                <div class="submission-detail-item">
                                    <span class="submission-label">Budget</span>
                                    <span class="submission-value">{{ $displayValue($finalSubmission['budget'] ?? null) }}</span>
                                </div>
                            </div>

                            <div style="margin-top: 16px;">
                                <span class="submission-label">New Rooms Required</span>

                                @if(count($roomsSelected))
                                    <div class="submission-pill-list">
                                        @foreach($roomsSelected as $room)
                                            <span class="submission-pill">{{ $room }}</span>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="submission-value">—</span>
                                @endif
                            </div>

                            <div style="margin-top: 18px;">
                                <span class="submission-label">Renovation Works Required</span>
                                <div class="submission-text-box">{{ $displayValue($finalSubmission['renovation_works'] ?? null) }}</div>
                            </div>

                            <div style="margin-top: 18px;">
                                <span class="submission-label">Additional Information</span>
                                <div class="submission-text-box">{{ $displayValue($finalSubmission['additional_information'] ?? null) }}</div>
                            </div>
                        @else
                            <div class="alert alert-info" style="margin-top: 25px;">
                                This enquiry has Step 1 data only. The visitor either did not progress to Step 2 or was rejected before completing the full form.
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-xs-12 col-sm-12">
            </div>
        </div>
        <div class="col-md-12">
            <div class="portlet light bordered">
                <div class="portlet-title">
                    <div class="caption">
                        <span class="caption-subject font-green-haze bold uppercase">Website Form Submission</span>
                        <span class="caption-helper"> ID: {{ $submission->id }}</span>
                    </div>

                    <div class="actions">
                        <a href="/settings/website-form-submission" class="btn default">
                            <i class="fa fa-arrow-left"></i> Back
                        </a>
                    </div>
                </div>

                <div class="portlet-body">
                    {{-- Main details --}}
                    <div class="row">
                        <div class="col-md-8">

                            <div class="portlet light bordered submission-section">
                                <div class="portlet-title">
                                    <div class="caption font-green-haze">
                                        <span class="caption-subject bold uppercase">Website Form Submission</span>
                                        <span class="caption-helper"> ID: {{ $submission->id }}</span>
                                    </div>
                                </div>

                                <div class="portlet-body">
                                    <h4 class="submission-section-title">Step 1 - Initial Enquiry</h4>

                                    <div class="submission-detail-grid">
                                        <div class="submission-detail-item">
                                            <span class="submission-label">Email</span>
                                            <span class="submission-value">{{ $displayValue($stepOne['email'] ?? $submission->email) }}</span>
                                        </div>

                                        <div class="submission-detail-item">
                                            <span class="submission-label">Suburb</span>
                                            <span class="submission-value">{{ $displayValue($stepOne['suburb'] ?? $submission->suburb) }}</span>
                                        </div>

                                        <div class="submission-detail-item">
                                            <span class="submission-label">Postcode</span>
                                            <span class="submission-value">{{ $displayValue($stepOne['suburb_postcode'] ?? $submission->postcode) }}</span>
                                        </div>

                                        <div class="submission-detail-item">
                                            <span class="submission-label">State</span>
                                            <span class="submission-value">{{ $displayValue($stepOne['suburb_state'] ?? $submission->state) }}</span>
                                        </div>

                                        <div class="submission-detail-item">
                                            <span class="submission-label">Owns Property?</span>
                                            <span class="submission-value">{{ $displayValue($stepOne['pre_purchase'] ?? $finalSubmission['pre_purchase'] ?? null) }}</span>
                                        </div>

                                        <div class="submission-detail-item">
                                            <span class="submission-label">Rejection Reason</span>
                                            <span class="submission-value">{{ $displayValue($submission->rejection_reason) }}</span>
                                        </div>
                                    </div>

                                    <div style="margin-top: 16px;">
                                        <span class="submission-label">Renovation Type Selected</span>

                                        @if(count($workSelected))
                                            <div class="submission-pill-list">
                                                @foreach($workSelected as $work)
                                                    <span class="submission-pill">{{ $work }}</span>
                                                @endforeach
                                            </div>
                                        @else
                                            <span class="submission-value">—</span>
                                        @endif
                                    </div>

                                    @if(!empty($finalSubmission))
                                        <h4 class="submission-section-title" style="margin-top: 28px;">Step 2 - Contact & Property Details</h4>

                                        <div class="submission-detail-grid">
                                            <div class="submission-detail-item">
                                                <span class="submission-label">Full Name</span>
                                                <span class="submission-value">{{ $displayValue($finalSubmission['full_name'] ?? null) }}</span>
                                            </div>

                                            <div class="submission-detail-item">
                                                <span class="submission-label">Contact Numbers</span>
                                                <span class="submission-value">{{ $displayValue($finalSubmission['contact_numbers'] ?? null) }}</span>
                                            </div>

                                            <div class="submission-detail-item">
                                                <span class="submission-label">Street Address</span>
                                                <span class="submission-value">{{ $displayValue($finalSubmission['street_address'] ?? null) }}</span>
                                            </div>

                                            <div class="submission-detail-item">
                                                <span class="submission-label">Postal Address</span>
                                                <span class="submission-value">{{ $displayValue($finalSubmission['postal_address'] ?? null) }}</span>
                                            </div>

                                            <div class="submission-detail-item">
                                                <span class="submission-label">Preferred Contact Method</span>
                                                <span class="submission-value">{{ $displayValue($contactMethodLabels[$preferredContact] ?? $preferredContact) }}</span>
                                            </div>

                                            <div class="submission-detail-item">
                                                <span class="submission-label">Best Contact Time</span>
                                                <span class="submission-value">{{ $displayValue($bestContactTimeLabels[$bestContactTime] ?? $bestContactTime) }}</span>
                                            </div>

                                            <div class="submission-detail-item">
                                                <span class="submission-label">How They Heard About Us</span>
                                                <span class="submission-value">{{ $displayValue($finalSubmission['heard_about'] ?? null) }}</span>
                                            </div>

                                            <div class="submission-detail-item">
                                                <span class="submission-label">Building Commencement</span>
                                                <span class="submission-value">{{ $displayValue($commenceLabels[$commenceTime] ?? $commenceTime) }}</span>
                                            </div>

                                            <div class="submission-detail-item">
                                                <span class="submission-label">Bedrooms</span>
                                                <span class="submission-value">{{ $displayValue($finalSubmission['bedrooms'] ?? null) }}</span>
                                            </div>

                                            <div class="submission-detail-item">
                                                <span class="submission-label">House Style</span>
                                                <span class="submission-value">{{ $displayValue($finalSubmission['house_style'] ?? null) }}</span>
                                            </div>

                                            <div class="submission-detail-item">
                                                <span class="submission-label">Materials</span>
                                                <span class="submission-value">{{ $displayValue($finalSubmission['materials'] ?? null) }}</span>
                                            </div>

                                            <div class="submission-detail-item">
                                                <span class="submission-label">Build Year</span>
                                                <span class="submission-value">{{ $displayValue($finalSubmission['build_year'] ?? null) }}</span>
                                            </div>

                                            <div class="submission-detail-item">
                                                <span class="submission-label">Budget</span>
                                                <span class="submission-value">{{ $displayValue($finalSubmission['budget'] ?? null) }}</span>
                                            </div>
                                        </div>

                                        <div style="margin-top: 16px;">
                                            <span class="submission-label">New Rooms Required</span>

                                            @if(count($roomsSelected))
                                                <div class="submission-pill-list">
                                                    @foreach($roomsSelected as $room)
                                                        <span class="submission-pill">{{ $room }}</span>
                                                    @endforeach
                                                </div>
                                            @else
                                                <span class="submission-value">—</span>
                                            @endif
                                        </div>

                                        <div style="margin-top: 18px;">
                                            <span class="submission-label">Renovation Works Required</span>
                                            <div class="submission-text-box">{{ $displayValue($finalSubmission['renovation_works'] ?? null) }}</div>
                                        </div>

                                        <div style="margin-top: 18px;">
                                            <span class="submission-label">Additional Information</span>
                                            <div class="submission-text-box">{{ $displayValue($finalSubmission['additional_information'] ?? null) }}</div>
                                        </div>
                                    @else
                                        <div class="alert alert-info" style="margin-top: 25px;">
                                            This enquiry has Step 1 data only. The visitor either did not progress to Step 2 or was rejected before completing the full form.
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="portlet light bordered submission-section">
                                <div class="portlet-title">
                                    <div class="caption font-green-haze">
                                        <span class="caption-subject bold uppercase">Quick Summary</span>
                                    </div>
                                </div>

                                <div class="portlet-body">
                                    <div class="row">
                                        <div class="col-md-4"><span class="submission-label">Form</span></div>
                                        <div class="col-md-8"><span class="submission-value">{{ $formLabel }}</span></div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-4"><span class="submission-label">Status</span></div>
                                        <div class="col-md-8"><span class="submission-value">{!! $statusLabel !!}</span></div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-4"><span class="submission-label">Location</span></div>
                                        <div class="col-md-8"><span class="submission-value">
                                                    <span class="submission-value">{{ $submission->suburb ?: ($stepOne['suburb'] ?? '—') }}{{ $submission->postcode ? ' ' . $submission->postcode : '' }}</span>
                                                </span></div>
                                    </div>

                                    <p>
                                        <br>
                                        <span class="submission-label">Contact</span>
                                        <span class="submission-value">
                                                {{ $submission->full_name ?: ($finalSubmission['full_name'] ?? '—') }}<br>
                                                {{ $submission->email ?: ($stepOne['email'] ?? '—') }}<br>
                                                {{ $submission->phone ?: ($finalSubmission['contact_numbers'] ?? '—') }}
                                            </span>
                                    </p>
                                    <div class="row">
                                        <div class="col-md-4"><span class="submission-label">Updated</span></div>
                                        <div class="col-md-8"><span class="submission-value">{{ optional($submission->updated_at)->format('d/m/Y H:i') }}</span></div>
                                    </div>


                                    @if($submission->zoho_lead_id)
                                        <div class="row">
                                            <div class="col-md-4"><span class="submission-label">Zoho Lead</span></div>
                                            <div class="col-md-8"><span class="submission-value">{{ $submission->zoho_lead_id }}</span></div>
                                        </div>
                                    @endif

                                    @if($submission->rejection_reason)
                                        <div class="row">
                                            <div class="col-md-4"><span class="submission-label">Rejection Reason</span></div>
                                            <div class="col-md-8"><span class="submission-value">{{ $submission->rejection_reason }}</span></div>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <div class="portlet light bordered submission-section">
                                <div class="portlet-title">
                                    <div class="caption font-green-haze">
                                        <span class="caption-subject bold uppercase">Technical Info</span>
                                    </div>
                                </div>

                                <div class="portlet-body">
                                    <p>
                                        <span class="submission-label">IP Address</span>
                                        <span class="submission-value">{{ $submission->ip_address ?: ($meta['ip_address'] ?? '—') }}</span>
                                    </p>
                                    <p>
                                        <span class="submission-label">User Agent</span>
                                        <span class="submission-value">{{ $submission->user_agent ?: ($meta['user_agent'] ?? '—') }}</span>
                                    </p>
                                    <details class="submission-technical">
                                        <summary>Show raw saved form data</summary>
                                        @if($payloadJson)
                                            <pre>{{ $payloadJson }}</pre>
                                        @else
                                            <p class="text-muted">No saved form data found.</p>
                                        @endif
                                    </details>
                                </div>
                            </div>

                        </div>
                    </div>

                    <div class="form-actions right">
                        <a href="/settings/website-form-submission" class="btn default">Back</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>

@stop

@section('page-level-plugins-head')
    <link href="/assets/global/plugins/select2/css/select2.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/select2/css/select2-bootstrap.min.css" rel="stylesheet" type="text/css"/>
@stop

@section('page-level-plugins')
    <script src="/assets/global/plugins/select2/js/select2.full.min.js" type="text/javascript"></script>
@stop

@section('page-level-scripts')
    <script src="/assets/pages/scripts/components-select2.min.js" type="text/javascript"></script>
@stop
