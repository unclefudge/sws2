<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Request a Designer Visit</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <style>
        :root {
            --rdv-green: #6f8983;
            --rdv-heading: #355c57;
            --rdv-text: #111;
            --rdv-muted: #666;
            --rdv-border: #111;
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

        .rdv-page {
            min-height: 100vh;
            padding: 52px 40px 38px;
            background: linear-gradient(rgba(255, 255, 255, .86), rgba(255, 255, 255, .86)),
            url('/images/designer-visit-bg.jpg') center center / cover no-repeat;
        }

        .rdv-wrap {
            width: 100%;
            max-width: 435px;
        }

        .rdv-title {
            margin: 0 0 28px;
            color: var(--rdv-heading);
            font-size: 28px;
            line-height: 1.15;
            font-weight: 700;
            letter-spacing: .2px;
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
        .rdv-textarea {
            width: 100%;
            border: 1px solid var(--rdv-border);
            border-radius: 0;
            background: #fff;
            color: #111;
            font-size: 14px;
            outline: none;
        }

        .rdv-input {
            height: 34px;
            padding: 6px 36px 6px 10px;
        }

        .rdv-textarea {
            min-height: 92px;
            padding: 10px;
            resize: vertical;
        }

        .rdv-place-icon {
            position: absolute;
            top: 50%;
            right: 8px;
            width: 12px;
            height: 18px;
            transform: translateY(-50%);
            border-left: 3px solid #1d6f76;
            border-right: 3px solid #1d6f76;
        }

        .rdv-place-icon::before {
            content: "";
            position: absolute;
            top: 4px;
            left: 4px;
            width: 3px;
            height: 10px;
            background: #1d6f76;
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
            content: "";
            display: block;
            width: 12px;
            height: 12px;
            margin: 4px;
            background: #111;
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
            color: #7a1111;
            font-size: 13px;
            line-height: 1.5;
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
            max-width: 460px;
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

        .rdv-honeypot {
            position: absolute;
            left: -9999px;
            opacity: 0;
        }

        @media (max-width: 600px) {
            .rdv-page {
                padding: 36px 24px;
            }

            .rdv-title {
                font-size: 26px;
            }
        }
    </style>
</head>
<body>
<div class="rdv-page">
    <div class="rdv-wrap">

        <h1 class="rdv-title">Request a Designer Visit</h1>

        @if (session('success'))
            <div class="rdv-success">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="rdv-error">
                Please check the required fields and try again.
            </div>
        @endif

        @if (session('reject_message'))
            <div class="rdv-error">
                {!! session('reject_message') !!}
            </div>
        @endif

        <form method="POST" action="wp/request-designer" id="rdvForm">
            @csrf

            <input type="text" name="website" class="rdv-honeypot" tabindex="-1" autocomplete="off">

            <div class="rdv-step active" data-step="1">
                <p class="rdv-intro">
                    To request your obligation-free designer visit, please provide details below:
                </p>

                <div class="rdv-field">
                    <label class="rdv-label" for="email">Email *</label>
                    <div class="rdv-input-wrap">
                        <input
                                class="rdv-input"
                                id="email"
                                type="email"
                                name="email"
                                value="{{ old('email') }}"
                                required
                        >
                    </div>

                    <a class="rdv-privacy" href="/privacy-policy" target="_blank">
                        To view our privacy policy, please click here
                    </a>
                </div>

                <div class="rdv-field">
                    <label class="rdv-label" for="suburb">Suburb of the property to be renovated *</label>
                    <div class="rdv-input-wrap">
                        <input
                                class="rdv-input"
                                id="suburb"
                                type="text"
                                name="suburb"
                                value="{{ old('suburb') }}"
                                placeholder="Enter a location"
                                required
                        >
                    </div>
                </div>

                <div class="rdv-group-title">
                    Type of renovation work you require * <small>(select all that apply)</small>
                </div>

                @php
                    $oldWork = old('work_type', []);
                @endphp

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

                <div class="rdv-group-title">
                    Do you currently own the house you are enquiring about? *
                </div>

                <label class="rdv-option">
                    <input type="radio" name="ownership" value="yes" @checked(old('ownership') === 'yes') required>
                    <span>Yes</span>
                </label>

                <label class="rdv-option">
                    <input type="radio" name="ownership" value="pre_purchase" @checked(old('ownership') === 'pre_purchase') required>
                    <span>No, but I am wondering about likely costs before purchasing</span>
                </label>

                <div class="rdv-actions">
                    <button type="button" class="rdv-button" id="rdvNext">Next</button>
                </div>

                <div class="rdv-footer">
                    <span>Part 1 of 2</span>
                </div>
            </div>

            <div class="rdv-step" data-step="2">
                <p class="rdv-intro">
                    Please provide your contact details and we will be in touch.
                </p>

                <div class="rdv-field">
                    <label class="rdv-label" for="first_name">First name *</label>
                    <input class="rdv-input" id="first_name" type="text" name="first_name" value="{{ old('first_name') }}" required>
                </div>

                <div class="rdv-field">
                    <label class="rdv-label" for="last_name">Last name *</label>
                    <input class="rdv-input" id="last_name" type="text" name="last_name" value="{{ old('last_name') }}" required>
                </div>

                <div class="rdv-field">
                    <label class="rdv-label" for="phone">Phone *</label>
                    <input class="rdv-input" id="phone" type="tel" name="phone" value="{{ old('phone') }}" required>
                </div>

                <div class="rdv-field">
                    <label class="rdv-label" for="message">Any extra details?</label>
                    <textarea class="rdv-textarea" id="message" name="message">{{ old('message') }}</textarea>
                </div>

                <div class="rdv-actions">
                    <button type="button" class="rdv-button secondary" id="rdvBack">Back</button>
                    <button type="submit" class="rdv-button">Submit</button>
                </div>

                <div class="rdv-footer">
                    <span>Part 2 of 2</span>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="rdv-modal" id="rdvModal">
    <div class="rdv-modal-box">
        <button type="button" class="rdv-modal-close" id="rdvModalClose">×</button>
        <h4 class="rdv-modal-title">Notification</h4>
        <p class="rdv-modal-text" id="rdvModalText"></p>
    </div>
</div>

<script>
    const rdvForm = document.getElementById('rdvForm');
    const rdvNext = document.getElementById('rdvNext');
    const rdvBack = document.getElementById('rdvBack');
    const rdvModal = document.getElementById('rdvModal');
    const rdvModalText = document.getElementById('rdvModalText');
    const rdvModalClose = document.getElementById('rdvModalClose');

    function showModal(message) {
        rdvModalText.innerHTML = message;
        rdvModal.classList.add('active');
    }

    function closeModal() {
        rdvModal.classList.remove('active');
    }

    function showStep(step) {
        document.querySelectorAll('.rdv-step').forEach(el => {
            el.classList.toggle('active', el.dataset.step === String(step));
        });

        window.parent?.postMessage({
            type: 'request-designer-height',
            height: document.body.scrollHeight
        }, '*');
    }

    rdvModalClose.addEventListener('click', closeModal);

    rdvModal.addEventListener('click', function (event) {
        if (event.target === rdvModal) {
            closeModal();
        }
    });

    rdvNext.addEventListener('click', function () {
        const email = document.getElementById('email');
        const suburb = document.getElementById('suburb');
        const workTypes = [...document.querySelectorAll('input[name="work_type[]"]:checked')].map(input => input.value);
        const ownership = document.querySelector('input[name="ownership"]:checked');

        if (!email.checkValidity()) {
            email.reportValidity();
            return;
        }

        if (!suburb.checkValidity()) {
            suburb.reportValidity();
            return;
        }

        if (workTypes.length === 0) {
            showModal('Please select at least one type of renovation work.');
            return;
        }

        if (!ownership) {
            showModal('Please tell us whether you currently own the house you are enquiring about.');
            return;
        }

        if (ownership.value === 'pre_purchase') {
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

    window.addEventListener('load', function () {
        window.parent?.postMessage({
            type: 'request-designer-height',
            height: document.body.scrollHeight
        }, '*');
    });
</script>
</body>
</html>