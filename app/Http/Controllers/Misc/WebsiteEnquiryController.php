<?php

namespace App\Http\Controllers\Misc;

use App\Http\Controllers\Controller;
use App\Mail\Misc\RequestDesignerSubmitted;
use App\Models\Misc\DesignerPostcode;
use App\Models\Misc\WebsiteFormSubmission;
use App\Services\Zoho\ZohoCrmService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Throwable;


/**
 * Handles the custom two-step Cape Cod enquiry forms
 * that are embedded into the WordPress site.
 *
 * Form types currently supported:
 * - Request a Designer Visit
 * - Request a Fixed Price Quotation
 */
class WebsiteEnquiryController extends Controller
{

    /*
     * Set to true if Cape Cod only wants First Floor Addition enquiries.
     * Set to false if users can continue with Ground Floor, Internal Renovation, or Other.
     */
    private const REQUIRE_FIRST_FLOOR = false;

    /**
     * Display the public Request a Designer Visit form.
     */
    public function show()
    {
        return $this->showForm(false, 'designer');
    }

    /**
     * Display the staff Request a Designer Visit form.
     */
    public function showStaff()
    {
        return $this->showForm(true, 'designer');
    }

    /**
     * Display the public Request a Fixed Price Quotation form.
     */
    public function showFixedPrice()
    {
        return $this->showForm(false, 'fixed_price');
    }

    /**
     * Display the staff Request a Fixed Price Quotation form.
     */
    public function showStaffFixedPrice()
    {
        return $this->showForm(true, 'fixed_price');
    }

    /**
     * Central form configuration.
     *
     * Keeping the two forms in one Blade/controller makes future visual,
     * validation and Zoho mapping changes easier to maintain.
     */
    protected function getFormConfig(string $formType): array
    {
        return match ($formType) {
            'fixed_price' => [
                'form_type' => 'fixed_price',
                'form_key' => 'request_fixed_price_quotation',
                'is_fixed_price' => true,

                'title_step_1' => 'Request a Fixed Price Quotation',
                'title_step_2' => 'Request a Fixed Price Quotation - Part 2',
                'intro_step_1' => 'To request your fixed price quotation, please provide details below:',
                'intro_step_2' => 'Thank you for completing the first step towards your fixed price quotation. Please complete the below details so we can progress your enquiry.',
                'success_message' => 'Thank you for your fixed price quotation request. We will be in touch shortly.',

                'public_get_url' => '/wp/request-fixed-price',
                'public_post_url' => '/wp/request-fixed-price',
                'public_step_one_url' => '/wp/request-fixed-price/step-one',

                'staff_get_url' => '/wp/staff/request-fixed-price',
                'staff_post_url' => '/wp/staff/request-fixed-price',
                'staff_step_one_url' => '/wp/staff/request-fixed-price/step-one',
            ],

            default => [
                'form_type' => 'designer',
                'form_key' => 'request_designer_visit',
                'is_fixed_price' => false,

                'title_step_1' => 'Request a Designer Visit',
                'title_step_2' => 'Request a Designer Visit - Part 2',
                'intro_step_1' => 'To request your obligation-free designer visit, please provide details below:',
                'intro_step_2' => 'Thank you for completing the first step towards meeting with one of our expert designers. Please complete the below details so we can progress your enquiry.',
                'success_message' => 'Thank you for your enquiry. We will be in touch shortly.',

                'public_get_url' => '/wp/request-designer',
                'public_post_url' => '/wp/request-designer',
                'public_step_one_url' => '/wp/request-designer/step-one',

                'staff_get_url' => '/wp/staff/request-designer',
                'staff_post_url' => '/wp/staff/request-designer',
                'staff_step_one_url' => '/wp/staff/request-designer/step-one',
            ],
        };
    }

    /**
     * Display an embedded form.
     *
     * The postcode list is passed to the Blade file so JavaScript can reject
     * suburbs that are outside the service area before the user reaches Part 2.
     */
    protected function showForm(bool $isStaffEntry, string $formType = 'designer')
    {
        $formConfig = $this->getFormConfig($formType);
        $allowedPostcodes = DesignerPostcode::active()->orderBy('postcode')->pluck('postcode')->map(fn($postcode) => (string)$postcode)->values()->all();

        return view('misc/wp-form/website-enquiry', [
            'allowedPostcodes' => $allowedPostcodes,
            'isStaffEntry' => $isStaffEntry,
            'formType' => $formConfig['form_type'],
            'formConfig' => $formConfig,
            'stepOneAction' => $isStaffEntry ? $formConfig['staff_step_one_url'] : $formConfig['public_step_one_url'],
            'requireFirstFloor' => self::REQUIRE_FIRST_FLOOR,
        ]);
    }

    /**
     * Save Step 1 for the public Request a Designer Visit form.
     */
    public function saveStepOne(Request $request)
    {
        return $this->saveStepOneForm($request, 'designer');
    }

    /**
     * Save Step 1 for the staff Request a Designer Visit form.
     */
    public function saveStaffStepOne(Request $request)
    {
        $request->merge(['staff_entry' => 1]);

        return $this->saveStepOneForm($request, 'designer');
    }

    /**
     * Save Step 1 for the public Request a Fixed Price Quotation form.
     */
    public function saveFixedPriceStepOne(Request $request)
    {
        return $this->saveStepOneForm($request, 'fixed_price');
    }

    /**
     * Save Step 1 for the staff Request a Fixed Price Quotation form.
     */
    public function saveStaffFixedPriceStepOne(Request $request)
    {
        $request->merge(['staff_entry' => 1]);

        return $this->saveStepOneForm($request, 'fixed_price');
    }

    /**
     * Save Step 1 before the visitor either moves to Step 2 or is rejected.
     *
     * This gives SafeWorksite a record of attempted enquiries even when
     * the user does not qualify for a Zoho Lead.
     */
    protected function saveStepOneForm(Request $request, string $formType = 'designer')
    {
        $formConfig = $this->getFormConfig($formType);
        $isFixedPrice = (bool)$formConfig['is_fixed_price'];

        $request->merge(['suburb_postcode' => preg_replace('/\D+/', '', (string)$request->input('suburb_postcode')),]);

        $rules = [
            'email' => ['required', 'email', 'max:255', 'regex:/^[^\s@]+@[^\s@]+\.[^\s@]+$/'],
            'suburb' => ['required', 'string', 'max:120'],
            'suburb_place_id' => ['required', 'string', 'max:255'],
            'suburb_state' => ['required', 'string', Rule::in(['NSW'])],
            'suburb_postcode' => ['required', 'string', Rule::exists((new DesignerPostcode)->getTable(), 'postcode')->where(fn($query) => $query->where('active', true)),],
            'suburb_country' => ['nullable', 'string', 'max:10'],
            'suburb_lat' => ['nullable', 'numeric'],
            'suburb_lng' => ['nullable', 'numeric'],
            'suburb_formatted_address' => ['nullable', 'string', 'max:255'],

            'work_type' => ['required', 'array', 'min:1'],
            'work_type.*' => ['required', Rule::in(['first_floor', 'ground_floor', 'major_internal', 'other_unsure'])],

            'website_form_submission_uuid' => ['nullable', 'uuid'],
        ];

        if ($isFixedPrice) {
            $rules['home_addition_plans'] = ['required', Rule::in(['Yes', 'No'])];
        } else {
            $rules['pre_purchase'] = ['required', Rule::in(['Yes', 'No'])];
        }

        $validated = $request->validate($rules, [
            'email.required' => 'This field is required.',
            'email.regex' => 'Please enter a valid email address.',
            'suburb.required' => 'Please enter your suburb',
            'suburb_place_id.required' => 'Please select your suburb from the dropdown list',
            'suburb_postcode.required' => 'Please select your suburb from the dropdown list',
            'suburb_postcode.exists' => 'Sorry, this property appears to be outside our current service area.',
            'suburb_state.required' => 'Please select a suburb in NSW.',
            'suburb_state.in' => 'Please select a suburb in NSW.',
            'work_type.required' => 'Please select at least one type of renovation work',
            'work_type.min' => 'Please select at least one type of renovation work',
            'pre_purchase.required' => 'Please select an option',
            'home_addition_plans.required' => 'Please select an option',
        ]);

        $status = 'step1 complete';
        $rejectionReason = null;

        /*
        * Business rule: enquiry must include a first floor addition.
        * Toggle REQUIRE_FIRST_FLOOR at the top of this controller to enable/disable.
        */
        if (self::REQUIRE_FIRST_FLOOR && !in_array('first_floor', $validated['work_type'], true)) {
            $status = 'rejected';
            $rejectionReason = 'No first floor addition selected';
        }

        $submission = $this->saveWebsiteFormSubmission(
            request: $request,
            validated: $validated,
            status: $status,
            step: 1,
            rejectionReason: $rejectionReason,
            payloadKey: 'step_1',
            formKey: $formConfig['form_key']
        );

        return response()->json(['success' => true, 'uuid' => $submission->uuid, 'status' => $submission->status, 'rejection_reason' => $submission->rejection_reason,]);
    }

    /**
     * Submit the public Request a Designer Visit form.
     */
    public function store(Request $request, ZohoCrmService $zoho)
    {
        return $this->storeForm($request, $zoho, 'designer');
    }

    /**
     * Submit the staff Request a Designer Visit form.
     */
    public function storeStaff(Request $request, ZohoCrmService $zoho)
    {
        $request->merge(['staff_entry' => 1]);

        return $this->storeForm($request, $zoho, 'designer');
    }

    /**
     * Submit the public Request a Fixed Price Quotation form.
     */
    public function storeFixedPrice(Request $request, ZohoCrmService $zoho)
    {
        return $this->storeForm($request, $zoho, 'fixed_price');
    }

    /**
     * Submit the staff Request a Fixed Price Quotation form.
     */
    public function storeStaffFixedPrice(Request $request, ZohoCrmService $zoho)
    {
        $request->merge(['staff_entry' => 1]);

        return $this->storeForm($request, $zoho, 'fixed_price');
    }

    /**
     * Validate the full form and create a Zoho CRM Lead.
     *
     * Important security note:
     * The browser does postcode/suburb checks for user experience, but the
     * real protection is here on the server. Hidden fields can be tampered with,
     * so never rely on JavaScript validation alone.
     */
    protected function storeForm(Request $request, ZohoCrmService $zoho, string $formType = 'designer')
    {
        $formConfig = $this->getFormConfig($formType);
        $isFixedPrice = (bool)$formConfig['is_fixed_price'];

        /*
         * Simple honeypot spam trap.
         * Real users will not see/fill this field because it is hidden off-screen.
         */
        if ($request->filled('website')) {
            return back()->with('success', $formConfig['success_message']);
        }

        /*
         * Normalise values before validation.
         * Google returns postcodes as strings, but this strips any accidental spaces
         * or non-numeric characters before checking against designer_postcodes.
         */
        $request->merge([
            'suburb_postcode' => preg_replace('/\D+/', '', (string)$request->input('suburb_postcode')),
            'postal_address_different' => $request->boolean('postal_address_different'),
        ]);

        /* Staff Entry */
        $isStaffEntry = $request->boolean('staff_entry') && Auth::check();

        /*
         * Marketing source options.
         * Staff users get an extra option that can assign the lead directly
         * to a Design Consultant in Zoho.
         */
        $heardAboutOptions = ['Referral', 'Well-known Name', 'Job Sign', 'Internet Search', 'Online Directory', 'Facebook', 'Instagram', 'LinkedIn', 'Other',];
        if ($isStaffEntry) {
            $heardAboutOptions[] = 'Direct to Consultant';
        }

        /*
         * Add the Zoho Owner user IDs beside each consultant initial.
         */
        $designConsultantOwnerIds = [
            'BZ' => '1976497000008061001',  // Barbara Szymanski
            'CH' => '1976497000006477001',  // Charles Tabone
            'DS' => '1976497000008056001',  // Darek Szymanski
            'KB' => '1976497000000133001',  // Keith Bow
            'MB' => '1976497000153849001',  // Matt Beesley
            'ME' => '1976497000002298001',  // Mark Elder
            'RR' => '1976497000000624001',  // Rocco Raso
            'SM' => '1976497000006476001',  // Scott McDougall
            'TS' => '1976497000006475001',  // Terry Smith
            'OTHER' => '1976497000002481001', // Zoho One
        ];

        $rules = [
            // Part 1: email and suburb/service-area validation.
            'email' => ['required', 'email', 'max:255', 'regex:/^[^\s@]+@[^\s@]+\.[^\s@]+$/',],
            'suburb' => ['required', 'string', 'max:120'],
            'suburb_place_id' => ['required', 'string', 'max:255'],
            'suburb_state' => ['required', 'string', Rule::in(['NSW'])],
            'suburb_postcode' => ['required', 'string', Rule::exists((new DesignerPostcode)->getTable(), 'postcode')->where(fn($query) => $query->where('active', true)),],
            'suburb_country' => ['nullable', 'string', 'max:10'],
            'suburb_lat' => ['nullable', 'numeric'],
            'suburb_lng' => ['nullable', 'numeric'],
            'suburb_formatted_address' => ['nullable', 'string', 'max:255'],
            'staff_entry' => ['nullable', 'boolean'],

            // Part 1: renovation type.
            'work_type' => ['required', 'array', 'min:1'],
            'work_type.*' => ['required', Rule::in(['first_floor', 'ground_floor', 'major_internal', 'other_unsure',]),],

            // Part 2: contact and property details.
            'full_name' => ['required', 'string', 'max:255'],
            'street_address' => ['required', 'string', 'max:255'],
            'postal_address_different' => ['nullable', 'boolean'],
            'postal_address' => ['nullable', 'string', 'max:255'],
            'contact_numbers' => ['required', 'string', 'max:80'],
            'preferred_contact_method' => ['required', Rule::in(['phone', 'email', 'either'])],

            /*
             * Only shown/required when the preferred contact method is Phone or Either.
             * If the user chooses Email this field can stay empty.
             */
            'best_contact_time' => [
                Rule::excludeIf(fn() => !in_array($request->input('preferred_contact_method'), ['phone', 'either'], true)),
                Rule::requiredIf(fn() => in_array($request->input('preferred_contact_method'), ['phone', 'either'], true)),
                Rule::in(['business_hours', 'mornings_only', 'anytime_9_8', 'evenings_only']),
            ],

            'heard_about' => ['nullable', 'string', Rule::in($heardAboutOptions)],
            'design_consultant' => [
                Rule::excludeIf(fn() => !($isStaffEntry && $request->input('heard_about') === 'Direct to Consultant')),
                Rule::requiredIf(fn() => $isStaffEntry && $request->input('heard_about') === 'Direct to Consultant'),
                Rule::in(array_keys($designConsultantOwnerIds)),
            ],

            /*
             * Bedrooms are required, but 0 is a valid selected option.
             * Other room checkboxes are optional extra detail.
             */
            'bedrooms' => ['required', 'string', Rule::in(['0', '1', '2', '2-3', '3', '4', '5+']),],
            'new_rooms' => ['nullable', 'array'],
            'new_rooms.*' => ['required', Rule::in(['walk_in_robe', 'ensuite', 'bathroom', 'living', 'sitting', 'study', 'dining', 'kitchen', 'laundry', 'other', 'balcony', 'deck', 'garage', 'carport',]),],

            'renovation_works' => ['nullable', 'string', 'max:2000'],
            'commence_time' => ['required', Rule::in(['6_12_months', 'over_12_months']),],
            'house_style' => ['nullable', 'string', 'max:120'],
            'materials' => ['nullable', 'string', 'max:120'],
            'build_year' => ['nullable', 'string', 'max:120'],
            'budget' => ['nullable', 'string', 'max:120'],
            'additional_information' => ['nullable', 'string', 'max:2000'],
        ];

        if ($isFixedPrice) {
            $hasOtherDevelopmentStatus = in_array('other', (array)$request->input('da_cdc_pending_status', []), true);

            $rules['home_addition_plans'] = ['required', Rule::in(['Yes', 'No'])];
            $rules['plans_have_da_cdc'] = [Rule::excludeIf(!$isFixedPrice), Rule::requiredIf($isFixedPrice), Rule::in(['Yes', 'No']),];

            $rules['plans_have_cc_approval'] = [
                Rule::excludeIf(fn() => $request->input('plans_have_da_cdc') !== 'Yes'),
                Rule::requiredIf(fn() => $request->input('plans_have_da_cdc') === 'Yes'),
                Rule::in(['Yes', 'No']),
            ];

            $rules['da_cdc_pending_status'] = [
                Rule::excludeIf(fn() => $request->input('plans_have_da_cdc') !== 'No'),
                Rule::requiredIf(fn() => $request->input('plans_have_da_cdc') === 'No'),
                'array', 'min:1',
            ];

            $rules['da_cdc_pending_status.*'] = [Rule::in(['council_da', 'certifier_cdc', 'other']),];

            $rules['da_cdc_other_details'] = [
                Rule::excludeIf(fn() => !($request->input('plans_have_da_cdc') === 'No' && $hasOtherDevelopmentStatus)),
                Rule::requiredIf(fn() => $request->input('plans_have_da_cdc') === 'No' && $hasOtherDevelopmentStatus),
                'string', 'max:1000',
            ];
        } else {
            $rules['pre_purchase'] = ['required', Rule::in(['Yes', 'No'])];
        }

        $validated = $request->validate($rules, [
            // Custom validation messages used by the Blade inline errors.
            'email.required' => 'This field is required.',
            'email.regex' => 'Please enter a valid email address.',

            'suburb.required' => 'Please enter your suburb',
            'suburb_place_id.required' => 'Please select your suburb from the dropdown list',
            'suburb_postcode.required' => 'Please select your suburb from the dropdown list',
            'suburb_postcode.exists' => 'Sorry, this property appears to be outside our current service area.',
            'suburb_state.required' => 'Please select a suburb in NSW.',
            'suburb_state.in' => 'Please select a suburb in NSW.',

            'work_type.required' => 'Please select at least one type of renovation work',
            'work_type.min' => 'Please select at least one type of renovation work',

            'pre_purchase.required' => 'Please select an option',
            'home_addition_plans.required' => 'Please select an option',
            'full_name.required' => 'Please enter your full name',
            'street_address.required' => 'Please enter the street address of the property to be renovated',
            'contact_numbers.required' => 'Please enter your contact number',
            'preferred_contact_method.required' => 'Please select your preferred contact method',
            'best_contact_time.required' => 'Please select the best time for our Design Consultant to contact you',
            'bedrooms.required' => 'Please select the number of bedrooms required.',
            'design_consultant.required' => 'Please select the Design Consultant.',
            'design_consultant.in' => 'Please select a valid Design Consultant.',
            'plans_have_da_cdc.required' => 'Please select the status of your Development Application.',
            'plans_have_cc_approval.required' => 'Please select whether the plans have CC approval.',
            'da_cdc_pending_status.required' => 'Please select at least one DA/CDC status option.',
            'da_cdc_pending_status.min' => 'Please select at least one DA/CDC status option.',
            'da_cdc_other_details.required' => 'Please specify the other DA/CDC status.',
            'commence_time.required' => 'Please select when you would like building to commence',
        ]);

        /*
        * Business rule: enquiry must include a first floor addition.
        * Toggle REQUIRE_FIRST_FLOOR at the top of this controller to enable/disable.
        */
        if (self::REQUIRE_FIRST_FLOOR && !in_array('first_floor', $validated['work_type'], true)) {
            return back()->withInput()->with('reject_message', 'Thank you for your enquiry. While internal renovation, ground floor extensions and other associated work will often form part of our projects, we are primarily designers and builders of first floor additions and for that reason will not be taking on the project.');
        }

        /*
         * Direct to Consultant handling.
         * When a staff member selects this marketing source, Zoho should move
         * the lead into RFQ Prep Stage and optionally assign the Zoho Owner.
         */
        $isDirectToConsultant = $isStaffEntry && (($validated['heard_about'] ?? null) === 'Direct to Consultant');
        $selectedDesignConsultant = $isDirectToConsultant ? ($validated['design_consultant'] ?? null) : null;
        $selectedOwnerId = $selectedDesignConsultant ? ($designConsultantOwnerIds[$selectedDesignConsultant] ?? null) : null;

        /*
         * Human-readable labels for storing a clear description in Zoho.
         * These can later be replaced with actual Zoho custom fields if desired.
         */
        $workLabels = [
            'first_floor' => 'First Floor Addition (second storey)',
            'ground_floor' => 'Ground Floor Extension (above 50m²)',
            'major_internal' => 'Major Internal Renovation',
            'other_unsure' => 'Other/Unsure',
        ];

        $renovationZohoValues = [
            'first_floor' => 'FFA',
            'ground_floor' => 'GFA',
            'major_internal' => 'REN',
            'other_unsure' => 'unknown',
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

        $daCdcPendingLabels = [
            'council_da' => 'Plans are in Council for DA approval',
            'certifier_cdc' => 'Plans are with Certifier for CDC approval',
            'other' => 'Other',
        ];

        $contactMethodLabels = ['phone' => 'Phone', 'email' => 'Email', 'either' => 'Either',];
        $bestContactTimeLabels = ['business_hours' => 'Business Hours', 'mornings_only' => 'Mornings only', 'anytime_9_8' => 'Anytime (9am-8pm)', 'evenings_only' => 'Evenings only',];
        $commenceLabels = ['6_12_months' => '6-12 months', 'over_12_months' => 'Beyond 12 months',];

        $selectedWork = collect($validated['work_type'])->map(fn($key) => $workLabels[$key] ?? $key)->values()->all();
        $selectedRenovations = collect($validated['work_type'])->map(fn($key) => $renovationZohoValues[$key] ?? null)->filter()->values()->all();
        $selectedRooms = collect($validated['new_rooms'] ?? [])->map(fn($key) => $roomLabels[$key] ?? $key)->values()->all();
        $selectedDaCdcStatuses = collect($validated['da_cdc_pending_status'] ?? [])->map(fn($key) => $daCdcPendingLabels[$key] ?? $key)->values()->all();

        /*
         * Zoho Leads require Last_Name.
         * The form has a single Full Name field, so split the first word into
         * First_Name and use the remaining words as Last_Name.
         * If there is only one word, use it for both first and last name.
         */
        $nameParts = preg_split('/\s+/', trim($validated['full_name']));
        $firstName = array_shift($nameParts) ?: $validated['full_name'];
        $lastName = count($nameParts) ? implode(' ', $nameParts) : $validated['full_name'];

        $suburbNameOnly = trim(preg_replace('/\s+NSW\s+\d{4}$/i', '', $validated['suburb']));

        // Determine Council Area using Postcode/Suburb.
        $designerPostcode = DesignerPostcode::active()->where('postcode', $validated['suburb_postcode'])->whereRaw('UPPER(suburb) = ?', [strtoupper($suburbNameOnly)])->first();

        if (!$designerPostcode) {
            $designerPostcode = DesignerPostcode::active()->where('postcode', $validated['suburb_postcode'])->first();
        }

        $councilArea = $designerPostcode?->council;

        $submission = $this->saveWebsiteFormSubmission(
            request: $request,
            validated: $validated,
            status: 'submitted_before_zoho',
            step: 2,
            payloadKey: 'final_submission',
            formKey: $formConfig['form_key']
        );

        /* Staff Users mapping from SafeWorksite to Zoho */
        $queryTaker = 'WEBS';

        if ($isStaffEntry && Auth::check()) {
            $queryTaker = 'Other';
            $userInitials = strtoupper(trim((string)(Auth::user()->initials ?? '')));
            $zohoQueryTakerOptions = ['AL', 'KB', 'KS', 'NL', 'MM', 'RT'];

            if (in_array($userInitials, $zohoQueryTakerOptions, true)) {
                $queryTaker = $userInitials;
            }
        }

        $clientComments = implode("\n", array_filter([
            !empty($validated['home_addition_plans']) ? 'Home Addition/Extension: ' . $validated['home_addition_plans'] : null,
            !empty($validated['da_cdc_other_details']) ? 'Other DA/CDC status: ' . $validated['da_cdc_other_details'] : null,
            !empty($validated['renovation_works']) ? 'Renovation works: ' . $validated['renovation_works'] : null,
            !empty($validated['additional_information']) ? 'Additional info: ' . $validated['additional_information'] : null,
        ]));

        try {
            /*
             * Create the Zoho Lead.
             * For now most form details are stored in Client_Comments.
             * If Zoho custom fields exist later, map those values directly below.
             */
            $zohoLeadPayload = [
                'First_Name' => $firstName,
                'Last_Name' => $lastName,
                'Enquiry_Name' => $lastName,
                'Email' => $validated['email'],
                'Mobile' => $validated['contact_numbers'],
                'Street' => $validated['street_address'],
                'Suburb' => strtoupper($suburbNameOnly),
                'PostCode' => ($validated['suburb_postcode'] ?? ''),
                'Council_Area' => $councilArea,
                'Renovations' => count($selectedRenovations) ? $selectedRenovations : null,
                'Alt_Address_1' => !empty($validated['postal_address']) ? $validated['postal_address'] : null,
                'Preferred_Contact_Method' => $contactMethodLabels[$validated['preferred_contact_method']] ?? $validated['preferred_contact_method'],
                'Call_Time' => !empty($validated['best_contact_time']) ? ($bestContactTimeLabels[$validated['best_contact_time']] ?? $validated['best_contact_time']) : null,
                'Lead_Source' => $validated['heard_about'],
                'Query_Taker' => $queryTaker,
                'Lead_Status' => '010 Initial Entry',
                'Design_Consultant' => 'unallocated',

                'Bedrooms' => array_key_exists('bedrooms', $validated) && $validated['bedrooms'] !== '' ? [(string)$validated['bedrooms']] : null,
                'Other_Rooms' => count($selectedRooms) ? $selectedRooms : null,
                'Time_Frame' => $commenceLabels[$validated['commence_time']] ?? $validated['commence_time'],
                'Existing_1' => $validated['house_style'],
                'Existing_2' => $validated['materials'],

                'Please_Note' => implode("\n", array_filter([
                    !empty($validated['build_year']) ? 'Build Year: ' . $validated['build_year'] : null,
                    !empty($validated['budget']) ? 'Budget: ' . $validated['budget'] : null,
                ])),

                'Client_Comments' => $clientComments,
            ];

            if ($isFixedPrice) {
                // Zoho checkbox field. A boolean true should tick the checkbox.
                $zohoLeadPayload['Plans_by_Others'] = true;

                // PBO Info options
                $pboInfo2Values = [];

                if (($validated['plans_have_da_cdc'] ?? null) === 'Yes') {
                    $pboInfo2Values[] = 'DA';
                    $pboInfo2Values[] = 'Approved';
                }

                if (($validated['plans_have_da_cdc'] ?? null) === 'No') {
                    $daCdcPendingStatus = $validated['da_cdc_pending_status'] ?? [];

                    if (in_array('council_da', $daCdcPendingStatus, true)) {
                        $pboInfo2Values[] = 'DA';
                        $pboInfo2Values[] = 'Not Approved';
                    }
                    if (in_array('certifier_cdc', $daCdcPendingStatus, true)) {
                        $pboInfo2Values[] = 'CDC';
                        $pboInfo2Values[] = 'Not Approved';
                    }
                }

                if (count($pboInfo2Values)) {
                    $zohoLeadPayload['PBO_info2'] = array_values(array_unique($pboInfo2Values));
                }

                if (($validated['plans_have_da_cdc'] ?? null) === 'Yes') {
                    $zohoLeadPayload['CC_in_Place'] = $validated['plans_have_cc_approval'] ?? null;
                }

                // PBO Client Budget + Preferred Start Time
                $zohoLeadPayload['Client_Budget'] = !empty($validated['budget']) ? $validated['budget'] : null;
                $zohoLeadPayload['Client_preffered_Start'] = $commenceLabels[$validated['commence_time']] ?? $validated['commence_time'];
            } else {
                $zohoLeadPayload['Pre_Purchase'] = $validated['pre_purchase'];
            }

            if ($isDirectToConsultant) {
                $zohoLeadPayload['Design_Consultant'] = $selectedDesignConsultant;
                $zohoLeadPayload['Lead_Status'] = '104 RFQ Prep Stage';

                if ($selectedOwnerId) {
                    $zohoLeadPayload['Owner'] = ['id' => $selectedOwnerId];
                }
            }

            $zohoLead = $zoho->createLead($zohoLeadPayload);
            $zohoLeadId = $zohoLead['zoho_lead_id'] ?? null;
            $submission->update(['status' => 'zoho created', 'zoho_status' => 'success', 'zoho_lead_id' => $zohoLeadId, 'zoho_response' => $zohoLead['raw'] ?? $zohoLead,]);

            try {
                Mail::to($validated['email'])->send(new RequestDesignerSubmitted([
                    'name' => $validated['full_name'],
                    'mobile' => $validated['contact_numbers'],
                    'email' => $validated['email'],
                    'street_address' => $validated['street_address'],
                    'suburb' => strtoupper($suburbNameOnly),
                    'postcode' => $validated['suburb_postcode'] ?? '',
                    'renovations' => implode(', ', $selectedWork),
                ]));
            } catch (Throwable $mailException) {
                Log::error('Cape Cod enquiry confirmation email failed', ['message' => $mailException->getMessage(), 'email' => $validated['email'] ?? null, 'website_form_submission_id' => $submission->id ?? null, 'form_key' => $formConfig['form_key'],]);
            }

            $redirectUrl = $isStaffEntry ? $formConfig['staff_get_url'] . '?submitted=1' : $formConfig['public_get_url'] . '?submitted=1';

            return redirect($redirectUrl);
        } catch (Throwable $e) {
            /*
             * Zoho failed after the form submission was saved.
             * Keep the submission record and mark it clearly so staff can find/retry it.
             */
            try {
                $submission->update(['status' => 'zoho failed', 'zoho_status' => 'failed', 'zoho_lead_id' => null, 'zoho_response' => ['message' => $e->getMessage(), 'exception' => get_class($e), 'failed_at' => now()->toDateTimeString(),],]);
            } catch (Throwable $submissionUpdateException) {
                Log::error('Cape Cod enquiry submission status update failed after Zoho error', ['message' => $submissionUpdateException->getMessage(), 'original_zoho_error' => $e->getMessage(), 'email' => $validated['email'] ?? null, 'form_key' => $formConfig['form_key'], 'website_form_submission_id' => $submission->id ?? null,]);
            }

            // Log the technical error privately, but show the user a generic message.
            Log::error('Cape Cod enquiry Zoho Lead failed', ['message' => $e->getMessage(), 'email' => $validated['email'] ?? null, 'form_key' => $formConfig['form_key'], 'website_form_submission_id' => $submission->id ?? null,]);

            return back()->withInput()->withErrors([
                'zoho' => 'Sorry, something went wrong while submitting the form. Please try again.',
            ]);
        }
    }

    /**
     * Create/update a generic website form submission record.
     *
     * This table can be used by future WordPress/contact forms as well.
     */
    protected function saveWebsiteFormSubmission(Request $request, array $validated, string $status, int $step, ?string $rejectionReason = null, string $payloadKey = 'data', string $formKey = 'request_designer_visit'): WebsiteFormSubmission
    {
        $uuid = $request->input('website_form_submission_uuid');
        $submission = $uuid ? WebsiteFormSubmission::where('uuid', $uuid)->first() : null;

        if (!$submission) {
            $submission = new WebsiteFormSubmission();
            $submission->uuid = (string)Str::uuid();
            $submission->form_key = $formKey;
        }

        // If an existing UUID is reused, make sure the form_key stays correct for this form.
        $submission->form_key = $formKey;

        $payload = $submission->payload ?? [];
        $payload[$payloadKey] = $request->except(['_token', 'website']);
        $payload['meta'] = ['ip_address' => $request->ip(), 'user_agent' => $request->userAgent(), 'saved_at' => now()->toDateTimeString(),];

        $suburbNameOnly = isset($validated['suburb']) ? trim(preg_replace('/\s+NSW\s+\d{4}$/i', '', $validated['suburb'])) : null;

        $submission->fill([
            'status' => $status,
            'step' => $step,
            'email' => $validated['email'] ?? $submission->email,
            'full_name' => $validated['full_name'] ?? $submission->full_name,
            'phone' => $validated['contact_numbers'] ?? $submission->phone,
            'suburb' => $suburbNameOnly ? strtoupper($suburbNameOnly) : $submission->suburb,
            'postcode' => $validated['suburb_postcode'] ?? $submission->postcode,
            'state' => $validated['suburb_state'] ?? $submission->state,
            'rejection_reason' => $rejectionReason,
            'payload' => $payload,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        $submission->save();

        return $submission;
    }
}
