<?php

namespace App\Http\Controllers\Misc;

use App\Http\Controllers\Controller;
use App\Models\Misc\DesignerPostcode;
use App\Services\ZohoCrmService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

/**
 * Handles the custom two-step "Request a Designer Visit" form
 * that is embedded into the WordPress site.
 *
 * Flow:
 * 1. show() loads active service-area postcodes from the database.
 * 2. Blade uses those postcodes for front-end Google suburb validation.
 * 3. store() validates everything server-side again.
 * 4. If valid, a Zoho CRM Lead is created.
 */
class RequestDesignerController extends Controller
{
    /**
     * Display the embedded form.
     *
     * The postcode list is passed to the Blade file so JavaScript can reject
     * suburbs that are outside the service area before the user reaches Part 2.
     */
    public function show()
    {
        $allowedPostcodes = DesignerPostcode::active()->orderBy('postcode')->pluck('postcode')->map(fn($postcode) => (string)$postcode)->values()->all();

        return view('misc/request-designer', compact('allowedPostcodes'));
    }

    /**
     * Validate the full form and create a Zoho CRM Lead.
     *
     * Important security note:
     * The browser does postcode/suburb checks for user experience, but the
     * real protection is here on the server. Hidden fields can be tampered with,
     * so never rely on JavaScript validation alone.
     */
    public function store(Request $request, ZohoCrmService $zoho)
    {
        /*
         * Simple honeypot spam trap.
         * Real users will not see/fill this field because it is hidden off-screen.
         */
        if ($request->filled('website')) {
            return back()->with('success', 'Thank you for your enquiry.');
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

        /*
         * Validation rules for both Part 1 and Part 2 of the form.
         *
         * The service-area check is done using:
         * - suburb_state must be NSW
         * - suburb_postcode must exist in designer_postcodes with active = true
         */
        $validated = $request->validate([
            // Part 1: email and suburb/service-area validation.
            'email' => ['required', 'email', 'max:255'],
            'suburb' => ['required', 'string', 'max:120'],
            'suburb_place_id' => ['required', 'string', 'max:255'],
            'suburb_state' => ['required', 'string', Rule::in(['NSW'])],

            'suburb_postcode' => ['required', 'string', Rule::exists((new DesignerPostcode)->getTable(), 'postcode')->where(fn($query) => $query->where('active', true)),
            ],

            'suburb_country' => ['nullable', 'string', 'max:10'],
            'suburb_lat' => ['nullable', 'numeric'],
            'suburb_lng' => ['nullable', 'numeric'],
            'suburb_formatted_address' => ['nullable', 'string', 'max:255'],

            /*
             * Part 1: renovation type.
             * At least one checkbox is required, and later we also require
             * first_floor to be selected before allowing the enquiry.
             */
            'work_type' => ['required', 'array', 'min:1'],
            'work_type.*' => ['required', Rule::in(['first_floor', 'ground_floor', 'major_internal', 'other_unsure',]),
            ],

            /*
             * Part 1: ownership filter.
             * Pre-purchase enquiries are rejected below after validation.
             */
            'ownership' => ['required', Rule::in(['yes', 'pre_purchase'])],

            // Part 2: contact and property details.
            'full_name' => ['required', 'string', 'max:255'],
            'street_address' => ['required', 'string', 'max:255'],
            'postal_address_different' => ['nullable', 'boolean'],
            'postal_address' => ['nullable', 'string', 'max:255'],
            'contact_numbers' => ['required', 'string', 'max:80'],
            'preferred_contact_method' => ['required', Rule::in(['phone', 'email', 'either']),
            ],

            /*
             * Only shown/required when the preferred contact method is Phone.
             * If the user chooses Email or Either, this field can stay empty.
             */
            'best_contact_time' => [
                'exclude_unless:preferred_contact_method,phone',
                Rule::requiredIf(fn() => $request->input('preferred_contact_method') === 'phone'),
                Rule::in(['business_hours', 'mornings_only', 'anytime_9_8', 'evenings_only']),
            ],

            'heard_about' => ['nullable', 'string', 'max:120'],

            /*
             * Part 2: rooms required.
             * The rule below allows no bedrooms selected here, but after validation
             * we manually require either bedrooms OR at least one room checkbox.
             */
            'bedrooms' => ['nullable', 'integer', 'min:0', 'max:10'],

            'new_rooms' => ['nullable', 'array'],
            'new_rooms.*' => ['required',
                Rule::in([
                    'walk_in_robe',
                    'ensuite',
                    'bathroom',
                    'living',
                    'sitting',
                    'study',
                    'dining',
                    'kitchen',
                    'laundry',
                    'other',
                    'balcony',
                    'deck',
                    'garage',
                    'carport',
                ]),
            ],

            'renovation_works' => ['nullable', 'string', 'max:2000'],

            'commence_time' => ['required', Rule::in(['6_12_months', 'over_12_months']),
            ],

            'additional_information' => ['nullable', 'string', 'max:2000'],
        ], [
            // Custom validation messages used by the Blade inline errors.
            'email.required' => 'This field is required.',
            'email.email' => 'Please enter a valid email address.',

            'suburb.required' => 'Please enter your suburb',
            'suburb_place_id.required' => 'Please select your suburb from the dropdown list',
            'suburb_postcode.required' => 'Please select your suburb from the dropdown list',
            'suburb_postcode.exists' => 'Sorry, this property appears to be outside our current service area.',
            'suburb_state.required' => 'Please select a suburb in NSW.',
            'suburb_state.in' => 'Please select a suburb in NSW.',

            'work_type.required' => 'Please select at least one type of renovation work',
            'work_type.min' => 'Please select at least one type of renovation work',

            'ownership.required' => 'Please select an option',

            'full_name.required' => 'Please enter your full name',
            'street_address.required' => 'Please enter the street address of the property to be renovated',
            'contact_numbers.required' => 'Please enter your contact number',
            'preferred_contact_method.required' => 'Please select your preferred contact method',
            'best_contact_time.required' => 'Please select the best time for our Design Consultant to contact you',
            'commence_time.required' => 'Please select when you would like building to commence',
        ]);

        // Business rule: Cape Cod currently does not accept pre-purchase advice enquiries.
        if ($validated['ownership'] === 'pre_purchase') {
            return back()->withInput()->with('reject_message', 'Sorry but at this time we do not offer pre-purchase advice.');
        }

        /*
         * Business rule: enquiry must include a first floor addition.
         * Other work can be included, but first floor additions are the primary service.
         */
        if (!in_array('first_floor', $validated['work_type'], true)) {
            return back()->withInput()
                ->with('reject_message', 'Thank you for your enquiry. While internal renovation, ground floor extensions and other associated work will often form part of our projects, we are primarily designers and builders of first floor additions and for that reason will not be taking on the project.');
        }

        /*
         * Business rule: require some information about rooms required.
         * The user can either choose a number of bedrooms or select one/more room checkboxes.
         */
        if (empty($validated['bedrooms']) && empty($validated['new_rooms'])) {
            return back()->withInput()
                ->withErrors(['rooms_required' => 'Please provide us with the number of new bedrooms or other rooms required in your home addition.',]);
        }

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
            'over_12_months' => 'Over 12 months',
        ];

        $selectedWork = collect($validated['work_type'])->map(fn($key) => $workLabels[$key] ?? $key)->values()->all();
        $selectedRooms = collect($validated['new_rooms'] ?? [])->map(fn($key) => $roomLabels[$key] ?? $key)->values()->all();

        /*
         * Zoho Leads require Last_Name.
         * The form has a single Full Name field, so split the first word into
         * First_Name and use the remaining words as Last_Name.
         * If there is only one word, use it for both first and last name.
         */
        $nameParts = preg_split('/\s+/', trim($validated['full_name']));
        $firstName = array_shift($nameParts) ?: $validated['full_name'];
        $lastName = count($nameParts) ? implode(' ', $nameParts) : $validated['full_name'];

        try {
            /*
             * Create the Zoho Lead.
             * For now most form details are stored in Description.
             * If Zoho custom fields exist later, map those values directly below.
             */
            $zohoLeadId = $zoho->createLead([
                'Lead_Source' => 'Website - Request a Designer Visit',

                // Standard Zoho Lead fields.
                'First_Name' => $firstName,
                'Last_Name' => $lastName,
                'Email' => $validated['email'],
                'Phone' => $validated['contact_numbers'],
                'Company' => $validated['full_name'] . ' Household',
                'City' => $validated['suburb'],

                // Full submission summary stored in Zoho's Description field.
                'Description' => implode("\n", array_filter([
                    'Request a Designer Visit form submission',

                    '',
                    'PART 1',
                    'Email: ' . $validated['email'],
                    'Suburb: ' . $validated['suburb'],
                    'Postcode: ' . ($validated['suburb_postcode'] ?? ''),
                    'State: ' . ($validated['suburb_state'] ?? ''),
                    'Google address: ' . ($validated['suburb_formatted_address'] ?? ''),
                    'Google Place ID: ' . ($validated['suburb_place_id'] ?? ''),
                    'Renovation work: ' . implode(', ', $selectedWork),
                    'Owns property: Yes',

                    '',
                    'PART 2',
                    'Full name: ' . $validated['full_name'],
                    'Street address: ' . $validated['street_address'],
                    'Postal address different: ' . (!empty($validated['postal_address_different']) ? 'Yes' : 'No'),
                    !empty($validated['postal_address'])
                        ? 'Postal address: ' . $validated['postal_address']
                        : null,
                    'Contact numbers: ' . $validated['contact_numbers'],
                    'Preferred contact method: ' . ($contactMethodLabels[$validated['preferred_contact_method']] ?? $validated['preferred_contact_method']),
                    !empty($validated['best_contact_time'])
                        ? 'Best time to contact: ' . ($bestContactTimeLabels[$validated['best_contact_time']] ?? $validated['best_contact_time'])
                        : null,
                    !empty($validated['heard_about'])
                        ? 'How did you hear about us: ' . $validated['heard_about']
                        : null,
                    !empty($validated['bedrooms'])
                        ? '# Bedrooms: ' . $validated['bedrooms']
                        : null,
                    count($selectedRooms)
                        ? 'New rooms required: ' . implode(', ', $selectedRooms)
                        : null,
                    !empty($validated['renovation_works'])
                        ? 'Renovation works required: ' . $validated['renovation_works']
                        : null,
                    'Building commencement: ' . ($commenceLabels[$validated['commence_time']] ?? $validated['commence_time']),
                    !empty($validated['additional_information'])
                        ? 'Additional information: ' . $validated['additional_information']
                        : null,
                ])),
            ]);

            //Log::info('Designer visit Zoho Lead created', ['zoho_lead_id' => $zohoLeadId, 'email' => $validated['email'],]);

            return redirect('/wp/request-designer')->with('success', 'Thank you for your enquiry. We will be in touch shortly.');
        } catch (\Throwable $e) {
            // Log the technical error privately, but show the user a generic message.
            Log::error('Designer visit Zoho Lead failed', ['message' => $e->getMessage(), 'email' => $validated['email'] ?? null,]);

            return back()->withInput()->withErrors(['zoho' => 'Sorry, something went wrong while submitting the form. Please try again.',]);
        }
    }
}