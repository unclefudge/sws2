<?php

namespace App\Http\Controllers\Misc;

use App\Http\Controllers\Controller;
use App\Services\ZohoCrmService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class WordPressRequestDesignerController extends Controller
{
    public function show()
    {
        return view('misc/request-designer');

    }

    public function store(Request $request, ZohoCrmService $zoho)
    {
        // Honeypot spam trap.
        if ($request->filled('website')) {
            return back()->with('success', 'Thank you for your enquiry.');
        }

        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'suburb' => ['required', 'string', 'max:120'],

            'work_type' => ['required', 'array', 'min:1'],
            'work_type.*' => [
                'required',
                Rule::in([
                    'first_floor',
                    'ground_floor',
                    'major_internal',
                    'other_unsure',
                ]),
            ],

            'ownership' => ['required', Rule::in(['yes', 'pre_purchase'])],

            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'phone' => ['required', 'string', 'max:30'],
            'message' => ['nullable', 'string', 'max:2000'],
        ]);

        if ($validated['ownership'] === 'pre_purchase') {
            return back()->withInput()->with('reject_message', 'Sorry but at this time we do not offer pre-purchase advice.');
        }

        if (!in_array('first_floor', $validated['work_type'], true)) {
            return back()->withInput()->with('reject_message', 'Thank you for your enquiry. While internal renovation, ground floor extensions and other associated work will often form part of our projects, we are primarily designers and builders of first floor additions and for that reason will not be taking on the project.');
        }

        $workLabels = [
            'first_floor' => 'First Floor Addition (second storey)',
            'ground_floor' => 'Ground Floor Extension (above 50m²)',
            'major_internal' => 'Major Internal Renovation',
            'other_unsure' => 'Other/Unsure',
        ];

        $selectedWork = collect($validated['work_type'])
            ->map(fn($key) => $workLabels[$key] ?? $key)
            ->values()
            ->all();

        try {
            $zohoLeadId = $zoho->createLead([
                'Lead_Source' => 'Website - Request a Designer Visit',

                // Standard Zoho Lead fields.
                'First_Name' => $validated['first_name'],
                'Last_Name' => $validated['last_name'],
                'Email' => $validated['email'],
                'Phone' => $validated['phone'],
                'Company' => $validated['last_name'] . ' Household',
                'City' => $validated['suburb'],

                // Use Description as a safe fallback.
                // Replace these with your actual custom field API names if you have them.
                'Description' => implode("\n", array_filter([
                    'Request a Designer Visit form submission',
                    'Suburb: ' . $validated['suburb'],
                    'Renovation work: ' . implode(', ', $selectedWork),
                    'Owns property: Yes',
                    $validated['message'] ? 'Message: ' . $validated['message'] : null,
                ])),

                /*
                 * Example custom fields, if they exist in your CRM:
                 *
                 * 'Suburb_of_Property' => $validated['suburb'],
                 * 'Renovation_Work_Required' => $selectedWork,
                 * 'Owns_Property' => 'Yes',
                 */
            ]);

            Log::info('Designer visit Zoho Lead created', [
                'zoho_lead_id' => $zohoLeadId,
                'email' => $validated['email'],
            ]);

            return redirect()
                ->route('request-designer.show')
                ->with('success', 'Thank you for your enquiry. We will be in touch shortly.');
        } catch (\Throwable $e) {
            Log::error('Designer visit Zoho Lead failed', [
                'message' => $e->getMessage(),
                'email' => $validated['email'] ?? null,
            ]);

            return back()
                ->withInput()
                ->withErrors([
                    'zoho' => 'Sorry, something went wrong while submitting the form. Please try again.',
                ]);
        }
    }
}