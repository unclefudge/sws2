<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Request a Designer Visit</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <style>
        /*
         * Request Designer Visit embedded form
         * ------------------------------------------------------------
         * Self-contained two-step form designed to be embedded in WordPress via iframe.
         *
         * Part 1 screens the enquiry:
         * - email
         * - Google suburb autocomplete
         * - NSW/service-area postcode validation
         * - renovation type / ownership filters
         *
         * Part 2 collects the full Zoho lead details:
         * - name, address, phone/contact preference
         * - how they heard about Cape Cod
         * - rooms required and additional project information
         */
        :root {
            --rdv-green: #6f8983;
            --rdv-heading: #355c57;
            --rdv-text: #111;
            --rdv-muted: #666;
            --rdv-border: #111;
            --rdv-error: #7a1111;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, Helvetica, sans-serif;
            color: var(--rdv-text);
            background: transparent;
        }

        /*
         * Main background and spacing.
         * The image should exist at public/images/designer-visit-bg.jpg.
         */
        .rdv-page {
            min-height: 100vh;
            padding: 52px 40px 38px;
            background: transparent;
        }

        /*.rdv-page {
            min-height: 100vh;
            padding: 52px 40px 38px;
            background: linear-gradient(rgba(255, 255, 255, .86), rgba(255, 255, 255, .86)),
            url('https://safeworksite.com.au/images/designer-visit-bg.jpg') center center / cover no-repeat;
        }*/

        /*
         * Step 1 uses the narrow width.
         * Step 2 adds rdv-wrap-wide via JavaScript to match the wider Cape Cod layout.
         */
        .rdv-wrap {
            width: 100%;
            /*max-width: 435px;*/
            max-width: 100%;
        }

        .rdv-wrap.rdv-wrap-wide {
            /*max-width: 920px;*/
            max-width: 100%;
        }

        .rdv-title {
            margin: 0 0 28px;
            color: var(--rdv-heading);
            font-size: 28px;
            line-height: 1.15;
            font-weight: 700;
            letter-spacing: .2px;
        }

        .rdv-title.rdv-title-step2 {
            color: #111;
            font-size: 26px;
        }

        .rdv-intro {
            margin: 0 0 18px;
            color: #555;
            font-size: 14px;
            line-height: 1.8;
        }

        .rdv-field {
            margin-bottom: 22px;
        }

        .rdv-label {
            display: block;
            margin-bottom: 10px;
            font-size: 14px;
            font-weight: 700;
        }

        .rdv-input-wrap {
            position: relative;
        }

        .rdv-input,
        .rdv-textarea,
        .rdv-select {
            width: 100%;
            border: 1px solid var(--rdv-border);
            border-radius: 0;
            background: #fff;
            color: #111;
            font-size: 14px;
            outline: none;
        }

        .rdv-input,
        .rdv-select {
            height: 34px;
            padding: 6px 36px 6px 10px;
        }

        .rdv-input[readonly] {
            background: #f4f4f4;
        }

        .rdv-textarea {
            min-height: 34px;
            padding: 8px 10px;
            resize: vertical;
        }

        /*
         * Error styling used by both Laravel validation and JavaScript validation.
         */
        .rdv-input.has-error,
        .rdv-textarea.has-error,
        .rdv-select.has-error {
            border-color: var(--rdv-error);
        }

        .rdv-field-error {
            display: none;
            margin-top: 10px;
            color: var(--rdv-error);
            font-size: 14px;
            line-height: 1.4;
            font-weight: 700;
        }

        .rdv-field-error.active {
            display: block;
        }

        .rdv-privacy {
            display: inline-block;
            margin-top: 3px;
            color: #111;
            font-size: 11px;
            font-weight: 700;
            text-decoration: none;
        }

        .rdv-group-title {
            margin: 34px 0 8px;
            font-size: 14px;
            font-weight: 700;
            line-height: 1.5;
        }

        .rdv-group-title small {
            font-weight: 400;
        }

        /*
         * Custom square checkbox/radio style.
         * Checkboxes show a tick. Radios show a dot.
         */
        .rdv-option {
            display: flex;
            align-items: flex-start;
            gap: 17px;
            margin: 12px 0;
            font-size: 14px;
            line-height: 1.45;
        }

        .rdv-option input {
            appearance: none;
            -webkit-appearance: none;
            width: 22px;
            min-width: 22px;
            height: 22px;
            margin: 0;
            border: 1px solid #111;
            background: #fff;
            cursor: pointer;
        }

        .rdv-option input[type="checkbox"]:checked::after {
            content: "✓";
            display: block;
            width: 100%;
            height: 100%;
            color: #111;
            font-size: 18px;
            line-height: 20px;
            text-align: center;
            font-weight: 700;
        }

        .rdv-option input[type="radio"]:checked::after {
            content: "";
            display: block;
            width: 10px;
            height: 10px;
            margin: 5px;
            border-radius: 50%;
            background: #111;
        }

        .rdv-actions {
            margin-top: 48px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .rdv-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 93px;
            height: 38px;
            padding: 0 20px;
            border: 0;
            background: var(--rdv-green);
            color: #fff;
            font-size: 19px;
            font-weight: 700;
            text-transform: uppercase;
            cursor: pointer;
        }

        .rdv-button.secondary {
            background: #999;
        }

        .rdv-footer {
            display: flex;
            align-items: center;
            gap: 20px;
            margin-top: 38px;
            color: #777;
            font-size: 14px;
        }

        .rdv-footer::before,
        .rdv-footer::after {
            content: "";
            height: 1px;
            background: #111;
            flex: 1;
        }

        /*
         * Only the active step is visible.
         * showStep() toggles this class.
         */
        .rdv-step {
            display: none;
        }

        .rdv-step.active {
            display: block;
        }

        .rdv-error {
            margin: 0 0 18px;
            padding: 12px 14px;
            background: #fff3f3;
            border: 1px solid #d66;
            color: var(--rdv-error);
            font-size: 13px;
            line-height: 1.5;
            font-weight: 700;
        }

        .rdv-success {
            margin: 0 0 18px;
            padding: 12px 14px;
            background: #eef8f3;
            border: 1px solid #7fad92;
            color: #214f3c;
            font-size: 13px;
            line-height: 1.5;
        }

        body.rdv-is-submitted .rdv-page {
            min-height: 0;
            padding: 14px 40px 18px;
        }

        /*body.rdv-is-submitted .rdv-title {
            display: block;
            margin: 0 0 14px;
            font-size: 24px;
            line-height: 1.15;
        }*/

        body.rdv-is-submitted .rdv-success {
            margin: 0;
        }

        /*
         * Modal used for non-field business rule messages and help text.
         */
        .rdv-modal {
            position: fixed;
            inset: 0;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 24px;
            background: rgba(0, 0, 0, .35);
            z-index: 50;
        }

        .rdv-modal.active {
            display: flex;
        }

        .rdv-modal-box {
            position: relative;
            max-width: 520px;
            width: 100%;
            padding: 26px 28px;
            background: #fff;
            box-shadow: 0 12px 40px rgba(0, 0, 0, .25);
        }

        .rdv-modal-close {
            position: absolute;
            top: 8px;
            right: 12px;
            border: 0;
            background: transparent;
            font-size: 22px;
            cursor: pointer;
        }

        .rdv-modal-title {
            margin: 0 0 12px;
            font-size: 18px;
            font-weight: 700;
        }

        .rdv-modal-text {
            margin: 0;
            font-size: 14px;
            line-height: 1.6;
        }

        /*
         * Honeypot field for spam protection.
         */
        .rdv-honeypot {
            position: absolute;
            left: -9999px;
            opacity: 0;
        }

        /*
         * Google Places dropdown must sit above iframe/page content.
         */
        .pac-container {
            z-index: 99999;
            font-family: Arial, Helvetica, sans-serif;
        }

        /*
         * Part 2 layout helpers.
         */
        .rdv-grid-2 {
            display: grid;
            grid-template-columns: 1.25fr .95fr;
            gap: 28px;
            align-items: start;
        }

        .rdv-room-layout {
            display: grid;
            grid-template-columns: 95px auto;
            gap: 12px;
            align-items: center;
            max-width: 240px;
        }

        .rdv-room-layout .rdv-select {
            width: 95px;
        }

        .rdv-room-label {
            font-weight: 700;
            white-space: nowrap;
        }

        .rdv-contact-options,
        .rdv-room-checks {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            column-gap: 32px;
            row-gap: 4px;
        }

        .rdv-contact-options,
        .rdv-room-checks {
            row-gap: 0;
        }

        .rdv-contact-options .rdv-option,
        .rdv-room-checks .rdv-option {
            margin: 4px 0;
            gap: 12px;
            align-items: center;
        }

        /* medium screens */
        @media (max-width: 900px) {
            .rdv-room-checks {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        /* small screens */
        @media (max-width: 560px) {
            .rdv-contact-options,
            .rdv-room-checks {
                grid-template-columns: 1fr;
            }
        }

        .rdv-help-row {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 40px;
            gap: 20px;
            align-items: center;
        }

        .rdv-help-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 22px;
            height: 22px;
            border: 2px solid #111;
            border-radius: 50%;
            background: transparent;
            color: #111;
            font-size: 15px;
            font-weight: 700;
            line-height: 1;
            cursor: pointer;
        }

        .rdv-postal-extra {
            display: none;
            margin-top: 12px;
        }

        .rdv-postal-extra.active {
            display: block;
        }

        .rdv-contact-time-extra {
            display: none;
            margin-top: 22px;
        }

        .rdv-contact-time-extra.active {
            display: block;
        }

        @media (max-width: 760px) {
            .rdv-grid-2 {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

@php
    $isSubmitted = request()->boolean('submitted') || session('success');

    $rejectMessage = session('reject_message');

    if (!$rejectMessage && request('rejected') === 'pre_purchase') {
        $rejectMessage = 'Sorry but at this time we do not offer pre-purchase advice.<br><br>What does a house extension cost? Our House Extension Cost Page provides full details.';
    }

    if (!$rejectMessage && request('rejected') === 'not_first_floor') {
        $rejectMessage = 'Thank you for your enquiry. While internal renovation, ground floor extensions and other associated work will often form part of our projects, we are primarily designers and builders of first floor additions and for that reason will not be taking on the project. We are sorry for any inconvenience caused.';
    }
@endphp
<body class="{{ $isSubmitted ? 'rdv-is-submitted' : '' }}">

<div class="rdv-page">
    <div class="rdv-wrap" id="rdvWrap">
        <h1 class="rdv-title" id="rdvTitle">Request a Designer Visit</h1>

        {{-- Success after form submission. Uses query string as iframe-safe fallback. --}}
        @if ($isSubmitted)
            <div class="rdv-success">
                {{ session('success') ?: 'Thank you for your enquiry. We will be in touch shortly.' }}
            </div>
        @endif

        {{-- Business-rule rejection messages. Uses query string as iframe-safe fallback. --}}
        @if (!$isSubmitted && $rejectMessage)
            <div class="rdv-error">{!! $rejectMessage !!}</div>
        @endif

        {{-- Zoho/API errors only. Field-level errors appear under fields. --}}
        @if ($errors->has('zoho'))
            <div class="rdv-error">{{ $errors->first('zoho') }}</div>
        @endif

        @if (!$isSubmitted)
            <form method="POST" action="/wp/request-designer" id="rdvForm" novalidate>
                @csrf

                {{-- Tracks the saved SafeWorksite enquiry attempt between Step 1 and final submit. --}}
                <input
                        type="hidden"
                        name="website_form_submission_uuid"
                        id="website_form_submission_uuid"
                        value="{{ old('website_form_submission_uuid') }}"
                >

                {{-- Hidden honeypot field for spam bots. --}}
                <input type="text" name="website" class="rdv-honeypot" tabindex="-1" autocomplete="off">

                {{-- ============================================================
                     PART 1: Basic enquiry / service area screening
                     ============================================================ --}}
                <div class="rdv-step active" data-step="1">
                    <p class="rdv-intro">
                        To request your obligation-free designer visit, please provide details below:
                    </p>

                    {{-- Email --}}
                    <div class="rdv-field">
                        <label class="rdv-label" for="email">Email *</label>

                        {{--}}<div class="rdv-input-wrap">--}}
                        <input class="rdv-input @error('email') has-error @enderror" id="email" type="email" name="email" value="{{ old('email') }}" required>
                        {{--}}</div>--}}

                        <div class="rdv-field-error @error('email') active @enderror" id="email_error">
                            @error('email')
                            {{ $message }}
                            @enderror
                        </div>

                        <a class="rdv-privacy" href="https://www.capecod.com.au/privacy-policy/" target="_blank">
                            To view our privacy policy, please click here
                        </a>
                    </div>

                    {{-- Suburb lookup. Google fills the hidden fields below. --}}
                    <div class="rdv-field">
                        <label class="rdv-label" for="suburb">
                            Suburb of the property to be renovated *
                        </label>

                        <div class="rdv-input-wrap">
                            <input class="rdv-input @error('suburb') has-error @enderror @error('suburb_place_id') has-error @enderror @error('suburb_postcode') has-error @enderror @error('suburb_state') has-error @enderror"
                                   id="suburb" type="text" name="suburb" value="{{ old('suburb') }}" placeholder="Enter a location" autocomplete="off" required>
                        </div>

                        {{-- Google Places details used for server-side validation. --}}
                        <input type="hidden" name="suburb_place_id" id="suburb_place_id" value="{{ old('suburb_place_id') }}">
                        <input type="hidden" name="suburb_state" id="suburb_state" value="{{ old('suburb_state') }}">
                        <input type="hidden" name="suburb_postcode" id="suburb_postcode" value="{{ old('suburb_postcode') }}">
                        <input type="hidden" name="suburb_country" id="suburb_country" value="{{ old('suburb_country') }}">
                        <input type="hidden" name="suburb_lat" id="suburb_lat" value="{{ old('suburb_lat') }}">
                        <input type="hidden" name="suburb_lng" id="suburb_lng" value="{{ old('suburb_lng') }}">
                        <input type="hidden" name="suburb_formatted_address" id="suburb_formatted_address" value="{{ old('suburb_formatted_address') }}">

                        <div class="rdv-field-error @error('suburb') active @enderror" id="suburb_error">
                            @error('suburb')
                            {{ $message }}
                            @enderror
                        </div>

                        <div class="rdv-field-error @error('suburb_place_id') active @enderror @error('suburb_postcode') active @enderror @error('suburb_state') active @enderror" id="suburb_google_error">
                            @error('suburb_place_id')
                            {{ $message }}
                            @enderror

                            @error('suburb_state')
                            {{ $message }}
                            @enderror

                            @error('suburb_postcode')
                            {{ $message }}
                            @enderror
                        </div>
                    </div>

                    <div class="rdv-group-title">
                        Type of renovation work you require * <small>(select all that apply)</small>
                    </div>

                    @php
                        $oldWork = old('work_type', []);
                        $oldRooms = old('new_rooms', []);
                    @endphp

                    {{-- Renovation work checkboxes. --}}
                    <label class="rdv-option">
                        <input type="checkbox" name="work_type[]" value="first_floor" @checked(in_array('first_floor', $oldWork))>
                        <span>First Floor Addition (second storey)</span>
                    </label>

                    <label class="rdv-option">
                        <input type="checkbox" name="work_type[]" value="ground_floor" @checked(in_array('ground_floor', $oldWork))>
                        <span>Ground Floor Extension (above 50m²)</span>
                    </label>

                    <label class="rdv-option">
                        <input type="checkbox" name="work_type[]" value="major_internal" @checked(in_array('major_internal', $oldWork))>
                        <span>Major Internal Renovation</span>
                    </label>

                    <label class="rdv-option">
                        <input type="checkbox" name="work_type[]" value="other_unsure" @checked(in_array('other_unsure', $oldWork))>
                        <span>Other/Unsure</span>
                    </label>

                    <div class="rdv-field-error @error('work_type') active @enderror" id="work_type_error">
                        @error('work_type')
                        {{ $message }}
                        @enderror
                    </div>

                    <div class="rdv-group-title">
                        Do you currently own the house you are enquiring about? *
                    </div>

                    {{-- Ownership selection is used to reject pre-purchase advice enquiries.
                         - Zoho field is Pre_Purchase.
                           Existing Zoho logic stores:
                             - "No"  = owns the house / not pre-purchase
                             - "Yes" = pre-purchase enquiry
                           So the values are intentionally opposite to the visible question wording. --}}
                    <label class="rdv-option">
                        <input type="radio" name="pre_purchase" value="No" @checked(old('pre_purchase') === 'No') required>
                        <span>Yes</span>
                    </label>

                    <label class="rdv-option">
                        <input type="radio" name="pre_purchase" value="Yes" @checked(old('pre_purchase') === 'Yes') required>
                        <span>No, but I am wondering about likely costs before purchasing</span>
                    </label>

                    <div class="rdv-field-error @error('pre_purchase') active @enderror" id="pre_purchase_error">
                        @error('pre_purchase')
                        {{ $message }}
                        @enderror
                    </div>

                    <div class="rdv-actions">
                        <button type="button" class="rdv-button" id="rdvNext">Next</button>
                    </div>

                    <div class="rdv-footer">
                        <span>Part 1 of 2</span>
                    </div>
                </div>

                {{-- ============================================================
                     PART 2: Full lead details
                     ============================================================ --}}
                <div class="rdv-step" data-step="2">
                    <p class="rdv-intro">
                        Thank you for completing the first step towards meeting with one of our expert designers. Please complete the below details so we can progress your enquiry.
                    </p>

                    {{-- Full Name. This is split into First_Name / Last_Name for Zoho in the controller. --}}
                    <div class="rdv-field">
                        <label class="rdv-label" for="full_name">Full Name*</label>
                        <input class="rdv-input @error('full_name') has-error @enderror" id="full_name" type="text" name="full_name" value="{{ old('full_name') }}" required>

                        <div class="rdv-field-error @error('full_name') active @enderror" id="full_name_error">
                            @error('full_name')
                            {{ $message }}
                            @enderror
                        </div>
                    </div>

                    {{-- Street address.
                         This is intentionally a normal text field, not Google autocomplete.
                         The suburb/postcode selected in Part 1 is shown beside it for context. --}}
                    <div class="rdv-field">
                        <label class="rdv-label" for="street_address">Street address of the property to be renovated*</label>

                        <div class="rdv-grid-2">
                            <input class="rdv-input @error('street_address') has-error @enderror" id="street_address" type="text" name="street_address" value="{{ old('street_address') }}" required>

                            {{-- Displays the Part 1 selected suburb/postcode beside the street address. --}}
                            <input class="rdv-input" id="part2_suburb_display" type="text" value="{{ old('suburb') }}" readonly>
                        </div>

                        <div class="rdv-field-error @error('street_address') active @enderror" id="street_address_error">
                            @error('street_address')
                            {{ $message }}
                            @enderror
                        </div>

                        {{-- Reveals the postal address input when ticked. --}}
                        <label class="rdv-option" style="margin-top: 16px;">
                            <input type="checkbox" id="postal_address_different" name="postal_address_different" value="1"@checked(old('postal_address_different'))>
                            <span>My postal address is different to my street address</span>
                        </label>

                        <div class="rdv-postal-extra" id="postalAddressExtra">
                            <input class="rdv-input @error('postal_address') has-error @enderror" id="postal_address" type="text" name="postal_address" value="{{ old('postal_address') }}" placeholder="Postal address">

                            <div class="rdv-field-error @error('postal_address') active @enderror" id="postal_address_error">
                                @error('postal_address')
                                {{ $message }}
                                @enderror
                            </div>
                        </div>
                    </div>

                    {{-- Phone/contact numbers. --}}
                    <div class="rdv-field">
                        <label class="rdv-label" for="contact_numbers">Contact Numbers*</label>
                        <input class="rdv-input @error('contact_numbers') has-error @enderror" id="contact_numbers" type="text" name="contact_numbers" value="{{ old('contact_numbers') }}" required>

                        <div class="rdv-field-error @error('contact_numbers') active @enderror" id="contact_numbers_error">
                            @error('contact_numbers')
                            {{ $message }}
                            @enderror
                        </div>
                    </div>

                    {{-- Preferred contact method. --}}
                    <div class="rdv-group-title">
                        Preferred method for our Design Consultant to make initial contact*
                    </div>

                    <div class="rdv-contact-options">
                        <label class="rdv-option">
                            <input type="radio" name="preferred_contact_method" value="phone" @checked(old('preferred_contact_method') === 'phone')>
                            <span>Phone</span>
                        </label>

                        <label class="rdv-option">
                            <input type="radio" name="preferred_contact_method" value="email" @checked(old('preferred_contact_method') === 'email')>
                            <span>Email</span>
                        </label>

                        <label class="rdv-option">
                            <input type="radio" name="preferred_contact_method" value="either" @checked(old('preferred_contact_method') === 'either')>
                            <span>Either</span>
                        </label>
                    </div>

                    <div class="rdv-field-error @error('preferred_contact_method') active @enderror" id="preferred_contact_method_error">
                        @error('preferred_contact_method')
                        {{ $message }}
                        @enderror
                    </div>

                    {{-- Only displayed when "Phone/Either" is selected above. --}}
                    <div class="rdv-contact-time-extra" id="bestContactTimeWrap">
                        <div class="rdv-group-title" style="margin-top: 0;">
                            Best time for our Design Consultant to contact you*
                        </div>

                        <div class="rdv-contact-options" style="max-width: 620px; grid-template-columns: repeat(2, 1fr);">
                            <label class="rdv-option">
                                <input type="radio" name="best_contact_time" value="business_hours" @checked(old('best_contact_time') === 'business_hours') disabled>
                                <span>Business Hours</span>
                            </label>

                            <label class="rdv-option">
                                <input type="radio" name="best_contact_time" value="mornings_only" @checked(old('best_contact_time') === 'mornings_only') disabled>
                                <span>Mornings only</span>
                            </label>

                            <label class="rdv-option">
                                <input type="radio" name="best_contact_time" value="anytime_9_8" @checked(old('best_contact_time') === 'anytime_9_8') disabled>
                                <span>Anytime (9am-8pm)</span>
                            </label>

                            <label class="rdv-option">
                                <input type="radio" name="best_contact_time" value="evenings_only" @checked(old('best_contact_time') === 'evenings_only') disabled>
                                <span>Evenings only</span>
                            </label>
                        </div>

                        <div class="rdv-field-error @error('best_contact_time') active @enderror" id="best_contact_time_error">
                            @error('best_contact_time')
                            {{ $message }}
                            @enderror
                        </div>
                    </div>

                    {{-- Marketing source dropdown. Update the options here if the client wants different values. --}}
                    <div class="rdv-field" style="margin-top: 20px">
                        <label class="rdv-label" for="heard_about">How did you hear about us?</label>
                        <select class="rdv-select @error('heard_about') has-error @enderror" id="heard_about" name="heard_about">
                            <option value=""></option>
                            <option value="Referral" @selected(old('heard_about') === 'Referral')>Referral/word of mouth</option>
                            <option value="Well-known Name" @selected(old('heard_about') === 'Well-known Name')>Well-known name</option>
                            <option value="Job Sign" @selected(old('heard_about') === 'Job Sign')>Job Sign</option>
                            <option value="Internet Search" @selected(old('heard_about') === 'Internet Search')>Internet Search</option>
                            <option value="Online Directory" @selected(old('heard_about') === 'Online Directory')>Online Directory</option>
                            <option value="Social Media" @selected(old('heard_about') === 'Social Media')>Social Media</option>
                            <option value="Other" @selected(old('heard_about') === 'Other')>Other</option>
                        </select>

                        <div class="rdv-field-error @error('heard_about') active @enderror" id="heard_about_error">
                            @error('heard_about')
                            {{ $message }}
                            @enderror
                        </div>
                    </div>

                    {{-- New rooms required. Controller requires bedrooms OR at least one checkbox. --}}
                    <div class="rdv-group-title">
                        NEW rooms required in your home addition <small>(select all that apply)</small>
                    </div>

                    <div class="rdv-room-layout">
                        <div>
                            <select class="rdv-select @error('bedrooms') has-error @enderror @error('rooms_required') has-error @enderror" id="bedrooms" name="bedrooms">
                                <option value=""></option>
                                <option value="0" @selected((string) old('bedrooms') === (string) '0')>0</option>
                                <option value="1" @selected((string) old('bedrooms') === (string) '1')>1</option>
                                <option value="2" @selected((string) old('bedrooms') === (string) '2')>2</option>
                                <option value="2-3" @selected((string) old('bedrooms') === (string) '2-3')>2-3</option>
                                <option value="3" @selected((string) old('bedrooms') === (string) '3')>3</option>
                                <option value="4" @selected((string) old('bedrooms') === (string) '4')>4</option>
                                <option value="5+" @selected((string) old('bedrooms') === (string) '5+')>5+</option>
                            </select>
                        </div>

                        <div class="rdv-room-label"># Bedrooms</div>
                    </div>

                    <div class="rdv-room-checks" style="margin-top: 10px">
                        <label class="rdv-option"><input type="checkbox" name="new_rooms[]" value="walk_in_robe" @checked(in_array('walk_in_robe', $oldRooms))><span>Walk-in Robe</span></label>
                        <label class="rdv-option"><input type="checkbox" name="new_rooms[]" value="ensuite" @checked(in_array('ensuite', $oldRooms))><span>Ensuite</span></label>
                        <label class="rdv-option"><input type="checkbox" name="new_rooms[]" value="bathroom" @checked(in_array('bathroom', $oldRooms))><span>Bathroom</span></label>
                        <label class="rdv-option"><input type="checkbox" name="new_rooms[]" value="living" @checked(in_array('living', $oldRooms))><span>Living</span></label>
                        <label class="rdv-option"><input type="checkbox" name="new_rooms[]" value="sitting" @checked(in_array('sitting', $oldRooms))><span>Sitting</span></label>
                        <label class="rdv-option"><input type="checkbox" name="new_rooms[]" value="study" @checked(in_array('study', $oldRooms))><span>Study</span></label>
                        <label class="rdv-option"><input type="checkbox" name="new_rooms[]" value="dining" @checked(in_array('dining', $oldRooms))><span>Dining</span></label>
                        <label class="rdv-option"><input type="checkbox" name="new_rooms[]" value="kitchen" @checked(in_array('kitchen', $oldRooms))><span>Kitchen</span></label>
                        <label class="rdv-option"><input type="checkbox" name="new_rooms[]" value="laundry" @checked(in_array('laundry', $oldRooms))><span>Laundry</span></label>
                        <label class="rdv-option"><input type="checkbox" name="new_rooms[]" value="other" @checked(in_array('other', $oldRooms))><span>Other</span></label>
                        <label class="rdv-option"><input type="checkbox" name="new_rooms[]" value="balcony" @checked(in_array('balcony', $oldRooms))><span>Balcony</span></label>
                        <label class="rdv-option"><input type="checkbox" name="new_rooms[]" value="deck" @checked(in_array('deck', $oldRooms))><span>Deck</span></label>
                        <label class="rdv-option"><input type="checkbox" name="new_rooms[]" value="garage" @checked(in_array('garage', $oldRooms))><span>Garage</span></label>
                        <label class="rdv-option"><input type="checkbox" name="new_rooms[]" value="carport" @checked(in_array('carport', $oldRooms))><span>Carport</span></label>
                    </div>

                    <div class="rdv-field-error @error('rooms_required') active @enderror @error('bedrooms') active @enderror @error('new_rooms') active @enderror" id="rooms_required_error">
                        @error('rooms_required')
                        {{ $message }}
                        @enderror
                        @error('bedrooms')
                        {{ $message }}
                        @enderror
                        @error('new_rooms')
                        {{ $message }}
                        @enderror
                    </div>

                    {{-- Renovation works details and help modal. --}}
                    <div class="rdv-field" style="margin-top: 20px">
                        <label class="rdv-label" for="renovation_works">Renovation works required</label>

                        <div class="rdv-help-row">
                            <textarea class="rdv-textarea @error('renovation_works') has-error @enderror" id="renovation_works" name="renovation_works">{{ old('renovation_works') }}</textarea>

                            <button type="button" class="rdv-help-button" data-help="renovation">?</button>
                        </div>

                        <div class="rdv-field-error @error('renovation_works') active @enderror" id="renovation_works_error">
                            @error('renovation_works')
                            {{ $message }}
                            @enderror
                        </div>
                    </div>

                    {{-- Commencement timing. --}}
                    <div class="rdv-group-title">
                        When would you like building to commence?*
                    </div>

                    <div class="rdv-contact-options">
                        <label class="rdv-option">
                            <input type="radio" name="commence_time" value="6_12_months" @checked(old('commence_time') === '6_12_months')>
                            <span>6-12 months</span>
                        </label>

                        <label class="rdv-option">
                            <input type="radio" name="commence_time" value="over_12_months" @checked(old('commence_time') === 'over_12_months')>
                            <span>Over 12 months</span>
                        </label>
                    </div>

                    <div class="rdv-field-error @error('commence_time') active @enderror" id="commence_time_error">
                        @error('commence_time')
                        {{ $message }}
                        @enderror
                    </div>

                    {{-- House Style --}}
                    <div class="rdv-field" style="margin-top: 20px">
                        <label class="rdv-label" for="house_style">Is your existing house a particular style?</label>
                        <select class="rdv-select @error('house_style') has-error @enderror" id="house_style" name="house_style">
                            <option value=""></option>
                            <option value="Californian" @selected(old('house_style') === 'Californian')>Californian</option>
                            <option value="Contemporary" @selected(old('house_style') === 'Contemporary')>Contemporary</option>
                            <option value="Federation" @selected(old('house_style') === 'Federation')>Federation</option>
                            <option value="Semi" @selected(old('house_style') === 'Semi')>Semi</option>
                            <option value="OTHER" @selected(old('house_style') === 'OTHER')>Other</option>
                        </select>

                        <div class="rdv-field-error @error('house_style') active @enderror" id="house_style">
                            @error('house_style')
                            {{ $message }}
                            @enderror
                        </div>
                    </div>

                    {{-- Materials --}}
                    <div class="rdv-field">
                        <label class="rdv-label" for="materials">And what is the house made of?</label>
                        <select class="rdv-select @error('materials') has-error @enderror" id="materials" name="materials">
                            <option value=""></option>
                            <option value="Brick" @selected(old('materials') === 'Brick')>Brick</option>
                            <option value="Brick Veneer" @selected(old('materials') === 'Brick Veneer')>Brick Veneer</option>
                            <option value="Clad" @selected(old('materials') === 'Clad')>Clad</option>
                            <option value="Fibro" @selected(old('materials') === 'Fibro')>Fibro</option>
                            <option value="Weatherboard" @selected(old('materials') === 'Weatherboard')>Weatherboard</option>
                        </select>

                        <div class="rdv-field-error @error('materials') active @enderror" id="materials">
                            @error('materials')
                            {{ $message }}
                            @enderror
                        </div>
                    </div>

                    {{-- House Build Year --}}
                    <div class="rdv-field">
                        <label class="rdv-label" for="build_year">What year was the house built?</label>
                        <input class="rdv-input @error('build_year') has-error @enderror" id="build_year" type="text" name="build_year" value="{{ old('build_year') }}">

                        <div class="rdv-field-error @error('build_year') active @enderror" id="build_year">
                            @error('build_year')
                            {{ $message }}
                            @enderror
                        </div>
                    </div>

                    {{-- Budget --}}
                    <div class="rdv-field">
                        <label class="rdv-label" for="budget">What is the Budget that you are thinking of for your project?</label>
                        <input class="rdv-input @error('budget') has-error @enderror" id="budget" type="text" name="budget" value="{{ old('budget') }}">

                        <div class="rdv-field-error @error('budget') active @enderror" id="budget">
                            @error('budget')
                            {{ $message }}
                            @enderror
                        </div>
                    </div>

                    {{-- Additional info and help modal. --}}
                    <div class="rdv-field" style="margin-top: 20px">
                        <label class="rdv-label" for="additional_information">Additional information</label>

                        <div class="rdv-help-row">
                            <textarea class="rdv-textarea @error('additional_information') has-error @enderror" id="additional_information" name="additional_information">{{ old('additional_information') }}</textarea>

                            <button type="button" class="rdv-help-button" data-help="additional">?</button>
                        </div>

                        <div class="rdv-field-error @error('additional_information') active @enderror" id="additional_information_error">
                            @error('additional_information')
                            {{ $message }}
                            @enderror
                        </div>
                    </div>

                    <div class="rdv-actions" style="margin-top: 28px;">
                        <button type="button" class="rdv-button secondary" id="rdvBack">
                            Back
                        </button>

                        <button type="submit" class="rdv-button" id="rdvSubmit">
                            Submit
                        </button>
                    </div>
                </div>
            </form>
        @endif
    </div>
</div>

{{-- Modal used for filter messages and help text. --}}
@if (!$isSubmitted)
    <div class="rdv-modal" id="rdvModal">
        <div class="rdv-modal-box">
            <button type="button" class="rdv-modal-close" id="rdvModalClose">×</button>
            <h4 class="rdv-modal-title">Notification</h4>
            <p class="rdv-modal-text" id="rdvModalText"></p>
        </div>
    </div>

    <script>
        /*
         * JavaScript overview
         * ------------------------------------------------------------
         * - Handles step switching
         * - Displays inline validation errors before form submission
         * - Initializes Google Places autocomplete for the suburb only
         * - Sends iframe height back to WordPress parent page
         */

        const rdvForm = document.getElementById('rdvForm');
        const rdvNext = document.getElementById('rdvNext');
        const rdvBack = document.getElementById('rdvBack');
        const rdvModal = document.getElementById('rdvModal');
        const rdvModalText = document.getElementById('rdvModalText');
        const rdvModalClose = document.getElementById('rdvModalClose');
        const rdvWrap = document.getElementById('rdvWrap');
        const rdvTitle = document.getElementById('rdvTitle');
        const rdvSubmit = document.getElementById('rdvSubmit');
        let submitButtonClicked = false;

        if (rdvSubmit) {
            rdvSubmit.addEventListener('click', function () {
                submitButtonClicked = true;
            });
        }

        /*
         * Prevent Enter key from submitting the form.
         * Textareas are allowed to keep Enter for new lines.
         */
        rdvForm.addEventListener('keydown', function (event) {
            if (event.key !== 'Enter') {
                return;
            }

            const tagName = event.target.tagName.toLowerCase();

            if (tagName === 'textarea') {
                return;
            }

            event.preventDefault();
        });


        /*
         * Active service-area postcodes passed from the controller/database.
         * This is used for instant front-end rejection. The controller still validates again.
         */
        const allowedPostcodes = @json($allowedPostcodes ?? []);

        function showModal(message) {
            rdvModalText.innerHTML = message;
            rdvModal.classList.add('active');
        }

        function closeModal() {
            rdvModal.classList.remove('active');
        }

        /*
         * Move between Part 1 and Part 2.
         * Also widens the form and updates the title to match the Part 2 design.
         */
        function showStep(step) {
            document.querySelectorAll('.rdv-step').forEach(el => {
                el.classList.toggle('active', el.dataset.step === String(step));
            });

            const isStep2 = String(step) === '2';

            rdvWrap.classList.toggle('rdv-wrap-wide', isStep2);
            rdvTitle.classList.toggle('rdv-title-step2', isStep2);
            rdvTitle.innerText = isStep2
                ? 'Request a Designer Visit - Part 2'
                : 'Request a Designer Visit';

            if (isStep2) {
                const suburb = document.getElementById('suburb').value;
                const suburbDisplay = document.getElementById('part2_suburb_display');

                if (suburbDisplay) {
                    suburbDisplay.value = suburb;
                }
            }

            sendHeightToParent();
        }

        /*
         * Tells the WordPress iframe wrapper how tall the embedded form is.
         * The parent WordPress page should listen for request-designer-height.
         */
        let lastIframeHeight = 0;
        let iframeHeightFrame = null;

        function sendHeightToParent() {
            if (iframeHeightFrame) {
                cancelAnimationFrame(iframeHeightFrame);
            }

            iframeHeightFrame = requestAnimationFrame(function () {
                const wrap = document.getElementById('rdvWrap');
                const page = document.querySelector('.rdv-page');

                if (!wrap || !page) {
                    return;
                }

                const pageStyles = window.getComputedStyle(page);
                const paddingTop = parseFloat(pageStyles.paddingTop) || 0;
                const paddingBottom = parseFloat(pageStyles.paddingBottom) || 0;

                /*
                 * Measure the actual form content, not the iframe viewport.
                 * This avoids the iframe growing forever.
                 */
                const height = Math.ceil(
                    wrap.offsetHeight + paddingTop + paddingBottom + 30
                );

                if (Math.abs(height - lastIframeHeight) < 5) {
                    return;
                }

                lastIframeHeight = height;

                window.parent?.postMessage({
                    type: 'request-designer-height',
                    height: height
                }, '*');
            });
        }

        // Re-check iframe height when responsive layout changes.
        window.addEventListener('resize', sendHeightToParent);

        // Re-check iframe height when actual form content changes.
        if ('ResizeObserver' in window) {
            const resizeObserver = new ResizeObserver(function () {
                sendHeightToParent();
            });

            resizeObserver.observe(document.getElementById('rdvWrap'));
        }

        /*
         * Error helper functions.
         * These use predictable IDs like email_error, suburb_error, etc.
         */
        function setFieldError(fieldId, message) {
            const error = document.getElementById(fieldId + '_error');
            const field = document.getElementById(fieldId);

            if (error) {
                error.innerHTML = message;
                error.classList.add('active');
            }

            if (field) {
                field.classList.add('has-error');
            }
        }

        function setCustomError(errorId, message) {
            const error = document.getElementById(errorId);

            if (error) {
                error.innerHTML = message;
                error.classList.add('active');
            }
        }

        function clearFieldError(fieldId) {
            const error = document.getElementById(fieldId + '_error');
            const field = document.getElementById(fieldId);

            if (error) {
                error.innerHTML = '';
                error.classList.remove('active');
            }

            if (field) {
                field.classList.remove('has-error');
            }
        }

        function clearCustomError(errorId) {
            const error = document.getElementById(errorId);

            if (error) {
                error.innerHTML = '';
                error.classList.remove('active');
            }
        }

        function clearStepOneErrors() {
            clearFieldError('email');
            clearFieldError('suburb');
            clearCustomError('suburb_google_error');
            clearCustomError('work_type_error');
            clearCustomError('pre_purchase_error');
        }

        function clearStepTwoErrors() {
            clearFieldError('full_name');
            clearFieldError('street_address');
            clearFieldError('postal_address');
            clearFieldError('contact_numbers');
            clearCustomError('preferred_contact_method_error');
            clearCustomError('best_contact_time_error');
            clearFieldError('heard_about');
            clearFieldError('bedrooms');
            clearCustomError('rooms_required_error');
            clearFieldError('renovation_works');
            clearCustomError('commence_time_error');
            clearFieldError('additional_information');
        }

        /*
         * Clears hidden Google suburb data when a user manually types again.
         * This forces them to select a real Google dropdown result.
         */
        function clearGoogleSuburbFields() {
            [
                'suburb_place_id',
                'suburb_state',
                'suburb_postcode',
                'suburb_country',
                'suburb_lat',
                'suburb_lng',
                'suburb_formatted_address'
            ].forEach(function (id) {
                const field = document.getElementById(id);

                if (field) {
                    field.value = '';
                }
            });
        }

        /*
         * Helper for extracting parts from a Google Places result.
         */
        function getAddressComponent(place, type, useShortName = false) {
            if (!place.address_components) {
                return '';
            }

            const component = place.address_components.find(function (item) {
                return item.types.includes(type);
            });

            if (!component) {
                return '';
            }

            return useShortName ? component.short_name : component.long_name;
        }

        /*
         * Approximate NSW bounds used to restrict Google suggestions.
         * Google does not offer a perfect "state = NSW only" filter in this older widget,
         * so we restrict to NSW bounds and then validate the selected state as NSW.
         */
        function nswBounds() {
            return new google.maps.LatLngBounds(
                new google.maps.LatLng(-37.505, 140.999),
                new google.maps.LatLng(-28.157, 153.638)
            );
        }

        /*
         * Google callback.
         * The script tag at the bottom calls this once Maps/Places has loaded.
         */
        window.initGoogleSuburbAutocomplete = function () {
            const suburbInput = document.getElementById('suburb');

            if (!window.google || !google.maps || !google.maps.places) {
                return;
            }

            /*
             * Part 1 suburb autocomplete.
             * It only accepts:
             * - Australia
             * - within approximate NSW bounds
             * - selected result has state NSW
             * - selected postcode exists in allowedPostcodes
             */
            if (suburbInput) {
                const suburbAutocomplete = new google.maps.places.Autocomplete(suburbInput, {
                    componentRestrictions: {
                        country: 'au'
                    },
                    bounds: nswBounds(),
                    strictBounds: true,
                    types: ['(regions)'],
                    fields: ['address_components', 'formatted_address', 'geometry', 'place_id', 'name']
                });

                suburbInput.addEventListener('input', function () {
                    clearGoogleSuburbFields();
                    clearFieldError('suburb');
                    clearCustomError('suburb_google_error');
                });

                suburbAutocomplete.addListener('place_changed', function () {
                    clearFieldError('suburb');
                    clearCustomError('suburb_google_error');

                    const place = suburbAutocomplete.getPlace();

                    if (!place || !place.place_id) {
                        return;
                    }

                    const suburb =
                        getAddressComponent(place, 'locality') ||
                        getAddressComponent(place, 'sublocality') ||
                        getAddressComponent(place, 'postal_town') ||
                        place.name ||
                        suburbInput.value;

                    const state = getAddressComponent(place, 'administrative_area_level_1', true);
                    const postcode = getAddressComponent(place, 'postal_code');
                    const country = getAddressComponent(place, 'country', true);

                    if (state !== 'NSW') {
                        suburbInput.value = '';
                        suburbInput.classList.add('has-error');
                        clearGoogleSuburbFields();
                        setCustomError('suburb_google_error', 'Please select a suburb in NSW.');
                        sendHeightToParent();
                        return;
                    }

                    const normalisedPostcode = String(postcode || '').replace(/\D+/g, '');

                    if (!normalisedPostcode || !allowedPostcodes.includes(normalisedPostcode)) {
                        suburbInput.value = '';
                        suburbInput.classList.add('has-error');
                        clearGoogleSuburbFields();
                        setCustomError('suburb_google_error', 'Sorry, this property appears to be outside our current service area.');
                        sendHeightToParent();
                        return;
                    }

                    document.getElementById('suburb_place_id').value = place.place_id || '';
                    document.getElementById('suburb_state').value = state || '';
                    document.getElementById('suburb_postcode').value = normalisedPostcode;
                    document.getElementById('suburb_country').value = country || '';
                    document.getElementById('suburb_formatted_address').value = place.formatted_address || '';

                    if (place.geometry && place.geometry.location) {
                        document.getElementById('suburb_lat').value = place.geometry.location.lat();
                        document.getElementById('suburb_lng').value = place.geometry.location.lng();
                    }

                    suburbInput.value = [suburb, state, normalisedPostcode]
                        .filter(Boolean)
                        .join(' ');

                    clearFieldError('suburb');
                    clearCustomError('suburb_google_error');
                    sendHeightToParent();
                });
            }
        };

        /*
         * Show best-contact-time options only when Phone is selected.
         * When Email is selected, hide the options, clear any selection,
         * and disable the inputs so they are not accidentally submitted.
         */
        function syncBestContactTimeVisibility() {
            const bestContactTimeWrap = document.getElementById('bestContactTimeWrap');
            const selectedPreferredContact = document.querySelector('input[name="preferred_contact_method"]:checked');
            const showBestTimes = selectedPreferredContact && ['phone', 'either'].includes(selectedPreferredContact.value);

            if (!bestContactTimeWrap) {
                return;
            }

            bestContactTimeWrap.classList.toggle('active', Boolean(showBestTimes));

            document.querySelectorAll('input[name="best_contact_time"]').forEach(function (timeInput) {
                timeInput.disabled = !showBestTimes;

                if (!showBestTimes) {
                    timeInput.checked = false;
                }
            });

            if (!showBestTimes) {
                clearCustomError('best_contact_time_error');
            }

            sendHeightToParent();
        }


        /*
         * Save Step 1 to SafeWorksite before moving to Step 2 or showing a rejection.
         * This gives the client a record of attempted enquiries even when they do not qualify.
         */
        async function saveStepOneAttempt() {
            const formData = new FormData(rdvForm);

            try {
                const response = await fetch('/wp/request-designer/step-one', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                    },
                    body: formData,
                    credentials: 'include'
                });

                if (!response.ok) {
                    return null;
                }

                const data = await response.json();

                if (data.uuid) {
                    document.getElementById('website_form_submission_uuid').value = data.uuid;
                }

                return data;
            } catch (error) {
                console.error('Unable to save Step 1 enquiry', error);
                return null;
            }
        }

        /*
         * Modal close handlers.
         */
        rdvModalClose.addEventListener('click', closeModal);

        rdvModal.addEventListener('click', function (event) {
            if (event.target === rdvModal) {
                closeModal();
            }
        });

        /*
         * Part 1 "Next" button validation.
         * This prevents moving to Part 2 until the first step passes.
         */
        rdvNext.addEventListener('click', async function () {
            clearStepOneErrors();

            let valid = true;

            const email = document.getElementById('email');
            const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            const suburb = document.getElementById('suburb');
            const suburbPlaceId = document.getElementById('suburb_place_id');
            const suburbState = document.getElementById('suburb_state');
            const suburbPostcode = document.getElementById('suburb_postcode');

            const workTypes = [...document.querySelectorAll('input[name="work_type[]"]:checked')]
                .map(input => input.value);

            const pre_purchase = document.querySelector('input[name="pre_purchase"]:checked');

            if (!email.value.trim()) {
                setFieldError('email', 'This field is required.');
                valid = false;
            } else if (!emailPattern.test(email.value.trim())) {
                setFieldError('email', 'Please enter a valid email address.');
                valid = false;
            }

            if (!suburb.value.trim()) {
                setFieldError('suburb', 'Please enter your suburb');
                valid = false;
            } else if (!suburbPlaceId.value) {
                setCustomError('suburb_google_error', 'Please select your suburb from the dropdown list');
                suburb.classList.add('has-error');
                valid = false;
            } else if (suburbState.value !== 'NSW') {
                setCustomError('suburb_google_error', 'Please select a suburb in NSW.');
                suburb.classList.add('has-error');
                valid = false;
            } else if (!suburbPostcode.value || !allowedPostcodes.includes(suburbPostcode.value)) {
                setCustomError('suburb_google_error', 'Sorry, this property appears to be outside our current service area.');
                suburb.classList.add('has-error');
                valid = false;
            }

            if (workTypes.length === 0) {
                setCustomError('work_type_error', 'Please select at least one type of renovation work');
                valid = false;
            }

            if (!pre_purchase) {
                setCustomError('pre_purchase_error', 'Please select an option');
                valid = false;
            }

            if (!valid) {
                sendHeightToParent();
                return;
            }

            const stepOneSave = await saveStepOneAttempt();

            if (!stepOneSave || !stepOneSave.success) {
                showModal('Sorry, something went wrong while saving your enquiry. Please try again.');
                return;
            }

            /*
             * Business rule popups.
             * These are shown as modals rather than inline validation errors.
             */
            if (pre_purchase.value === 'Yes') {
                showModal(
                    'Sorry but at this time we do not offer pre-purchase advice.<br><br>' +
                    'What does a house extension cost? Our House Extension Cost Page provides full details.'
                );
                return;
            }

            if (!workTypes.includes('first_floor')) {
                showModal(
                    'Thank you for your enquiry. While internal renovation, ground floor extensions and other associated work will often form part of our projects, ' +
                    'we are primarily designers and builders of first floor additions and for that reason will not be taking on the project. ' +
                    'We are sorry for any inconvenience caused.'
                );
                return;
            }

            showStep(2);
        });

        rdvBack.addEventListener('click', function () {
            showStep(1);
        });

        /*
         * Part 2 submit validation.
         * The server still validates again in the controller.
         */
        rdvForm.addEventListener('submit', function (event) {
            if (!submitButtonClicked) {
                event.preventDefault();
                return;
            }

            submitButtonClicked = false;

            clearStepTwoErrors();

            let valid = true;

            const fullName = document.getElementById('full_name');
            const streetAddress = document.getElementById('street_address');
            const contactNumbers = document.getElementById('contact_numbers');
            const preferredContact = document.querySelector('input[name="preferred_contact_method"]:checked');
            const bestContactTime = document.querySelector('input[name="best_contact_time"]:checked');
            const bedrooms = document.getElementById('bedrooms');
            const selectedRooms = document.querySelectorAll('input[name="new_rooms[]"]:checked');
            const commenceTime = document.querySelector('input[name="commence_time"]:checked');

            if (!fullName.value.trim()) {
                setFieldError('full_name', 'Please enter your full name');
                valid = false;
            }

            if (!streetAddress.value.trim()) {
                setFieldError('street_address', 'Please enter the street address of the property to be renovated');
                valid = false;
            }

            if (!contactNumbers.value.trim()) {
                setFieldError('contact_numbers', 'Please enter your contact number');
                valid = false;
            }

            if (!preferredContact) {
                setCustomError('preferred_contact_method_error', 'Please select your preferred contact method');
                valid = false;
            }

            if (preferredContact && ['phone', 'either'].includes(preferredContact.value) && !bestContactTime) {
                setCustomError('best_contact_time_error', 'Please select the best time for our Design Consultant to contact you');
                valid = false;
            }

            if (!bedrooms.value && selectedRooms.length === 0) {
                setCustomError('rooms_required_error', 'Please provide us with the number of new bedrooms or other rooms required in your home addition.');
                bedrooms.classList.add('has-error');
                valid = false;
            }

            if (!commenceTime) {
                setCustomError('commence_time_error', 'Please select when you would like building to commence');
                valid = false;
            }

            if (!valid) {
                event.preventDefault();
                sendHeightToParent();
            }
        });

        /*
         * Clear errors as users fix fields.
         */
        document.getElementById('email').addEventListener('input', function () {
            clearFieldError('email');
        });

        document.getElementById('full_name').addEventListener('input', function () {
            clearFieldError('full_name');
        });

        document.getElementById('street_address').addEventListener('input', function () {
            clearFieldError('street_address');
        });

        document.getElementById('contact_numbers').addEventListener('input', function () {
            clearFieldError('contact_numbers');
        });

        document.getElementById('bedrooms').addEventListener('change', function () {
            clearCustomError('rooms_required_error');
            this.classList.remove('has-error');
        });

        /*
         * Postal address reveal/hide.
         */
        document.getElementById('postal_address_different').addEventListener('change', function () {
            document.getElementById('postalAddressExtra').classList.toggle('active', this.checked);
            sendHeightToParent();
        });

        document.querySelectorAll('input[name="work_type[]"]').forEach(function (input) {
            input.addEventListener('change', function () {
                clearCustomError('work_type_error');
            });
        });

        document.querySelectorAll('input[name="pre_purchase"]').forEach(function (input) {
            input.addEventListener('change', function () {
                clearCustomError('pre_purchase_error');
            });
        });

        document.querySelectorAll('input[name="preferred_contact_method"]').forEach(function (input) {
            input.addEventListener('change', function () {
                clearCustomError('preferred_contact_method_error');
                clearCustomError('best_contact_time_error');
                syncBestContactTimeVisibility();
            });
        });

        document.querySelectorAll('input[name="best_contact_time"]').forEach(function (input) {
            input.addEventListener('change', function () {
                clearCustomError('best_contact_time_error');
            });
        });

        document.querySelectorAll('input[name="new_rooms[]"]').forEach(function (input) {
            input.addEventListener('change', function () {
                clearCustomError('rooms_required_error');
                document.getElementById('bedrooms').classList.remove('has-error');
            });
        });

        document.querySelectorAll('input[name="commence_time"]').forEach(function (input) {
            input.addEventListener('change', function () {
                clearCustomError('commence_time_error');
            });
        });

        /*
         * Help buttons used beside the larger text fields.
         */
        document.querySelectorAll('.rdv-help-button').forEach(function (button) {
            button.addEventListener('click', function () {
                if (this.dataset.help === 'renovation') {
                    showModal(
                        'Please provide us with a brief description of any other building works you would like included within your enquiry – whether it be adding further rooms to the existing level of your home, demolishing some walls to open up the space, renovating your existing Kitchen, Laundry or Bathroom, building a new Deck or Garage or Carport etc. The more information we have the better our Design Consultants can provide you with the best advice.'
                    );
                }

                if (this.dataset.help === 'additional') {
                    showModal(
                        'Please provide us with any other non-building information you think is relevant to your enquiry that will help us provide you with the best advice – Are you going to be away sometime this year? Do you have a deadline you’re trying to meet for the Construction? Have you just bought the house and won’t have access for a particular period of time?'
                    );
                }
            });
        });

        /*
         * Initial page setup.
         * If Laravel returns Part 2 validation errors, open Part 2 automatically.
         */
        window.addEventListener('load', function () {
            if (document.getElementById('postal_address_different').checked) {
                document.getElementById('postalAddressExtra').classList.add('active');
            }

            syncBestContactTimeVisibility();

            @if ($errors->has('full_name') || $errors->has('street_address') || $errors->has('postal_address') || $errors->has('contact_numbers') || $errors->has('preferred_contact_method') ||
            $errors->has('best_contact_time') || $errors->has('heard_about') || $errors->has('bedrooms') || $errors->has('new_rooms') || $errors->has('rooms_required') ||
            $errors->has('renovation_works') || $errors->has('commence_time') || $errors->has('additional_information') ||  $errors->has('zoho'))
            showStep(2);
            @endif

            sendHeightToParent();
        });
    </script>

    {{-- Google Maps Places API. Callback initializes the suburb autocomplete. --}}
    <script src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google.maps_browser_key') }}&libraries=places&callback=initGoogleSuburbAutocomplete" async defer></script>
@else
    <script>
        (function () {
            function sendSubmittedHeightToParent() {
                const wrap = document.getElementById('rdvWrap');
                const page = document.querySelector('.rdv-page');

                if (!wrap || !page) {
                    return;
                }

                const pageStyles = window.getComputedStyle(page);
                const paddingTop = parseFloat(pageStyles.paddingTop) || 0;
                const paddingBottom = parseFloat(pageStyles.paddingBottom) || 0;

                const height = Math.ceil(
                    wrap.offsetHeight + paddingTop + paddingBottom + 30
                );

                window.parent?.postMessage({
                    type: 'request-designer-height',
                    height: height,
                    submitted: true
                }, '*');
            }

            window.addEventListener('load', sendSubmittedHeightToParent);
            window.addEventListener('resize', sendSubmittedHeightToParent);

            setTimeout(sendSubmittedHeightToParent, 100);
            setTimeout(sendSubmittedHeightToParent, 500);
        })();
    </script>
@endif
</body>
</html>
