@extends('layouts.master')

@section('content')

    <div class="page-content">
        <h3 class="page-title">
            Website Form Submission
            <small>{{ $formLabel }}</small>
        </h3>

        <div class="page-bar">
            <ul class="page-breadcrumb">
                <li>
                    <i class="fa fa-home"></i>
                    <a href="/">Home</a>
                    <i class="fa fa-angle-right"></i>
                </li>
                <li>
                    <a href="/website-form-submission">Website Form Submissions</a>
                    <i class="fa fa-angle-right"></i>
                </li>
                <li>
                    <span>View Submission</span>
                </li>
            </ul>
        </div>

        <div class="row">
            <div class="col-md-8">

                <div class="portlet light bordered">
                    <div class="portlet-title">
                        <div class="caption font-green-sharp">
                            <i class="fa fa-envelope font-green-sharp"></i>
                            <span class="caption-subject bold uppercase">Submission Details</span>
                        </div>

                        <div class="actions">
                            <a href="/website-form-submission" class="btn btn-circle default">
                                <i class="fa fa-arrow-left"></i> Back
                            </a>
                        </div>
                    </div>

                    <div class="portlet-body">
                        <table class="table table-bordered table-striped">
                            <tbody>
                            <tr><th style="width: 220px;">Form</th><td>{{ $formLabel }}</td></tr>
                            <tr><th>Status</th><td>{!! $statusLabel !!}</td></tr>
                            <tr><th>Step Completed</th><td>{{ $submission->step_completed }}</td></tr>
                            <tr><th>Created</th><td>{{ optional($submission->created_at)->format('d/m/Y H:i') }}</td></tr>
                            <tr><th>Last Updated</th><td>{{ optional($submission->updated_at)->format('d/m/Y H:i') }}</td></tr>
                            <tr><th>UUID</th><td>{{ $submission->uuid }}</td></tr>
                            <tr><th>Name</th><td>{{ $submission->full_name }}</td></tr>
                            <tr><th>Email</th><td>{{ $submission->email }}</td></tr>
                            <tr><th>Phone</th><td>{{ $submission->phone }}</td></tr>
                            <tr><th>Suburb</th><td>{{ $submission->suburb }}</td></tr>
                            <tr><th>Postcode</th><td>{{ $submission->postcode }}</td></tr>
                            <tr><th>State</th><td>{{ $submission->state }}</td></tr>
                            <tr><th>Rejection Reason</th><td>{{ $submission->rejection_reason }}</td></tr>
                            <tr><th>Zoho Lead ID</th><td>{{ $submission->zoho_lead_id }}</td></tr>
                            <tr><th>Zoho Status</th><td>{{ $submission->zoho_status }}</td></tr>
                            <tr><th>IP Address</th><td>{{ $submission->ip_address }}</td></tr>
                            <tr><th>User Agent</th><td style="word-break: break-word;">{{ $submission->user_agent }}</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="portlet light bordered">
                    <div class="portlet-title">
                        <div class="caption font-green-sharp">
                            <i class="fa fa-list font-green-sharp"></i>
                            <span class="caption-subject bold uppercase">Saved Form Data</span>
                        </div>
                    </div>

                    <div class="portlet-body">
                        @if($payloadJson)
                            <pre style="white-space: pre-wrap; word-break: break-word; background: #f7f7f7; border: 1px solid #ddd; padding: 15px;">{{ $payloadJson }}</pre>
                        @else
                            <p class="text-muted">No saved form data found.</p>
                        @endif
                    </div>
                </div>

                <div class="portlet light bordered">
                    <div class="portlet-title">
                        <div class="caption font-green-sharp">
                            <i class="fa fa-cloud font-green-sharp"></i>
                            <span class="caption-subject bold uppercase">Zoho Response</span>
                        </div>
                    </div>

                    <div class="portlet-body">
                        @if($zohoResponseJson)
                            <pre style="white-space: pre-wrap; word-break: break-word; background: #f7f7f7; border: 1px solid #ddd; padding: 15px;">{{ $zohoResponseJson }}</pre>
                        @else
                            <p class="text-muted">No Zoho response saved yet.</p>
                        @endif
                    </div>
                </div>

            </div>

            <div class="col-md-4">
                <div class="portlet light bordered">
                    <div class="portlet-title">
                        <div class="caption font-green-sharp">
                            <i class="fa fa-info-circle font-green-sharp"></i>
                            <span class="caption-subject bold uppercase">Quick Summary</span>
                        </div>
                    </div>

                    <div class="portlet-body">
                        <p><strong>Status:</strong><br>{!! $statusLabel !!}</p>

                        <p>
                            <strong>Contact:</strong><br>
                            {{ $submission->full_name ?: '—' }}<br>
                            {{ $submission->email ?: '—' }}<br>
                            {{ $submission->phone ?: '—' }}
                        </p>

                        <p>
                            <strong>Location:</strong><br>
                            {{ $submission->suburb ?: '—' }}
                            {{ $submission->postcode ? ' ' . $submission->postcode : '' }}
                        </p>

                        @if($submission->zoho_lead_id)
                            <p>
                                <strong>Zoho Lead ID:</strong><br>
                                {{ $submission->zoho_lead_id }}
                            </p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
