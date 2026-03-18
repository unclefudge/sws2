<?php

namespace App\Services\Hia;

use App\Models\Site\Site;
use Carbon\Carbon;

class HiaContractMapper
{
    public function fromSite(Site $site): array
    {
        $clientName = $this->fullName(
            $site->client1_firstname,
            $site->client1_lastname
        );

        $client2Name = $this->fullName(
            $site->client2_firstname,
            $site->client2_lastname
        );
        $siteContract = $site->contract;

        return [
            /*
            |--------------------------------------------------------------------------
            | Contract meta
            |--------------------------------------------------------------------------
            */
            'job_number' => $site->code ?? null,
            'client' => $clientName,
            // 'contract_date' => optional($site->contract_sent)->format('Y-m-d'),
            // 'contract_date' => $this->formatDate($site->contract_signed ?? $site->contract_sent ?? $site->created_at),
            'contract_date' => '11/03/2026',
            'status' => 1,
            'period_type' => 'Working Days',

            /*
            |--------------------------------------------------------------------------
            | 2. Contract Price / Progress Payments
            |--------------------------------------------------------------------------
           */
            'payment' => [
                'deposit' => $siteContract->deposit ?? null,
                'total_ex_gst' => $siteContract->contract_net ?? null,
                'gst' => $siteContract->contract_gst ?? null,
                'total_inc_gst' => $siteContract->contract_price ?? null,
                'rounded' => false,

                'progress_payment' => [
                    'method' => 'customised',
                    'custom_reason' => 'Testing custom progress stages via API',
                    'base_stage_type' => null,

                    'rounding_message' => null,
                    'total_calculated_percent' => 100,
                    'total_calculated_amount' => '626438', //$siteContract->contract_price ?? null,
                    'adjustment' => 0,

                    //'stages' => $this->buildCustomProgressStages((float)($siteContract->contract_price ?? 0)),
                    'stages' => $this->buildCustomProgressStages((float)(626438)),
                ],
            ],

            /*
            |--------------------------------------------------------------------------
            | 3. Owner(s)
            |--------------------------------------------------------------------------
            */
            'owner' => [
                // name
                'type' => 'individual',
                'organisation' => null,
                'title' => $siteContract->owner1_title ?? null,
                'firstname' => null,
                'givennames' => null,
                'lastname' => null,
                'fullname' => $siteContract->owner1_name ?? null,

                // address
                'dpid' => null,
                'building' => null,
                'floor' => null,
                'unit' => null,
                'number' => null,
                'address_line1' => $siteContract->owner_address ?? null,
                'address_line2' => null,
                'suburb' => $siteContract->owner_suburb ?? null,
                'state' => $siteContract->owner_state ?? null,
                'pobox' => null,
                'postcode' => $siteContract->owner_postcode ?? null,
                'country' => 'Australia',
                'block' => null,
                'lot' => null,
                'section' => null,
                'volume' => null,
                'folio' => null,
                'certificate_of_title' => null,

                // contact
                'workphone' => null,
                'homephone' => null,
                'fax' => null,
                'mobile' => $siteContract->owner1_mobile ?? null,
                'email' => $siteContract->owner1_email ?? null,

                // mailing address
                'mail_dpid' => null,
                'mail_building' => null,
                'mail_floor' => null,
                'mail_unit' => null,
                'mail_number' => null,
                'mail_line1' => null,
                'mail_line2' => null,
                'mail_suburb' => null,
                'mail_state' => null,
                'mail_pobox' => null,
                'mail_postcode' => null,
                'mail_country' => 'Australia',
                'mail_block' => null,
                'mail_lot' => null,
                'mail_section' => null,
                'mail_volume' => null,
                'mail_folio' => null,
                'mail_certificate_of_title' => null,

                // misc
                'resident_contact' => null,
                'occupation' => null,
                'abn' => null,
                'acn' => null,
                'resident' => true,
            ],

            /*
            |--------------------------------------------------------------------------
            | Optional second owner / secondary client
            |--------------------------------------------------------------------------
            | Only useful if you later expand your XML service to support multiple
            | <hia:owner> nodes.
            */
            'owner2' => [
                'type' => 'individual',
                'organisation' => null,
                'title' => $siteContract->owner2_title ?? null,
                'firstname' => null,
                'givennames' => null,
                'lastname' => null,
                'fullname' => $siteContract->owner2_name ?? null,

                // address
                'dpid' => null,
                'building' => null,
                'floor' => null,
                'unit' => null,
                'number' => null,
                'address_line1' => $siteContract->owner_address ?? null,
                'address_line2' => null,
                'suburb' => $siteContract->owner_suburb ?? null,
                'state' => $siteContract->owner_state ?? null,
                'pobox' => null,
                'postcode' => $siteContract->owner_postcode ?? null,
                'country' => 'Australia',
                'block' => null,
                'lot' => null,
                'section' => null,
                'volume' => null,
                'folio' => null,
                'certificate_of_title' => null,

                // contact
                'workphone' => null,
                'homephone' => null,
                'fax' => null,
                'mobile' => $siteContract->owner2_mobile ?? null,
                'email' => $siteContract->owner2_email ?? null,

                'mail_dpid' => null,
                'mail_building' => null,
                'mail_floor' => null,
                'mail_unit' => null,
                'mail_number' => null,
                'mail_line1' => null,
                'mail_line2' => null,
                'mail_suburb' => null,
                'mail_state' => null,
                'mail_pobox' => null,
                'mail_postcode' => null,
                'mail_country' => 'Australia',
                'mail_block' => null,
                'mail_lot' => null,
                'mail_section' => null,
                'mail_volume' => null,
                'mail_folio' => null,
                'mail_certificate_of_title' => null,

                'occupation' => null,
                'abn' => null,
                'acn' => null,
                'resident' => true,
            ],

            /*
            |--------------------------------------------------------------------------
            | 4. Builder
            |--------------------------------------------------------------------------
            | Replace these with real company/builder values later
            */
            'builder' => [
                // name
                'type' => 'entity',
                'organisation' => 'CAPE COD AUSTRALIA PTY LTD',
                'title' => null,
                'firstname' => null,
                'givennames' => null,
                'lastname' => null,
                'fullname' => 'CAPE COD AUSTRALIA PTY LTD',

                // address
                'dpid' => null,
                'building' => null,
                'floor' => null,
                'unit' => null,
                'number' => null,
                'address_line1' => 'SHOP 4, 426 CHURCH ST',
                'address_line2' => null,
                'suburb' => 'NORTH PARAMATTA',
                'state' => 'NSW',
                'pobox' => null,
                'postcode' => '2151',
                'country' => 'AUSTRALIA',
                'block' => null,
                'lot' => null,
                'section' => null,
                'volume' => null,
                'folio' => null,
                'certificate_of_title' => null,

                // contact
                'workphone' => '02 9849 4444',
                'homephone' => null,
                'fax' => null,
                'mobile' => null,
                'email' => 'inform@capecod.com.au',

                // mail address
                'mail_dpid' => null,
                'mail_building' => null,
                'mail_floor' => null,
                'mail_unit' => null,
                'mail_number' => null,
                'mail_line1' => null,
                'mail_line2' => null,
                'mail_suburb' => null,
                'mail_state' => null,
                'mail_pobox' => null,
                'mail_postcode' => null,
                'mail_country' => 'Australia',
                'mail_block' => null,
                'mail_lot' => null,
                'mail_section' => null,
                'mail_volume' => null,
                'mail_folio' => null,
                'mail_certificate_of_title' => null,

                // licence/company
                'licence_number' => '5519',
                'licence_category' => null,
                'hia_member_number' => '307160',
                'hia_membership_expiry' => null,
                'abn' => '54 000 605 407',
                'acn' => null,
                'registered_building_practitioner' => null,
            ],

            /*
            |--------------------------------------------------------------------------
            | 5. The Land
            |--------------------------------------------------------------------------
            */
            'site' => [
                'dpid' => $siteContract->land_dp ?? null,
                'building' => null,
                'floor' => null,
                'unit' => null,
                'number' => null,
                'line1' => $siteContract->land_address ?? null,
                'line2' => null,
                'suburb' => $siteContract->land_suburb ?? null,
                'state' => $siteContract->land_state ?? null,
                'pobox' => null,
                'postcode' => $siteContract->land_postcode ?? null,
                'country' => 'Australia',
                'block' => 'block',
                'lot' => $siteContract->land_lot ?? null,
                'section' => 'section',
                'volume' => null,
                'folio' => null,
                'division' => 'div', // note: your XML service may need a custom mapping for this if template supports it
                'certificate_of_title' => null,
            ],

            /*
            |--------------------------------------------------------------------------
            | Mortgage / lending / funding
            |--------------------------------------------------------------------------
            */
            'mortgage' => [
                'lending_body' => null,
                'amount' => null,
                'branch' => null,
                'contact' => null,
                'supply_date' => null,
                'interest' => null,
                'term_length' => null,
            ],

            'funding' => [
                'total' => null,
                'item' => [
                    'description' => null,
                    'amount' => null,
                    'approval_period' => null,
                ],
            ],

            'lending' => [
                'funds' => [
                    'lending_body' => null,
                    'branch' => null,
                    'contact' => null,
                ],
            ],

            /*
            |--------------------------------------------------------------------------
            | 6. Building period / 7. Initial period
            |--------------------------------------------------------------------------
            */
            'timeframe' => [
                'start' => null, //$this->formatDate($site->jobstart_estimate),
                'price_review' => null,
                'start_price_review_string' => null,
                'end' => null, //$this->formatDate($site->forecast_completion),
                'initial_period_days' => $siteContract->initial_period ?? null,
                'days' => [
                    'commencement' => null, // Initial Period
                    'completion' => $siteContract->building_period ?? null,  // Practical Completion
                    'weather' => null,
                    'weekend' => null,
                    'other' => null,
                    'defect_liability' => null,
                    'defect_rectification' => null,
                    'progress_payment' => null,
                ],
            ],

            /*
            |--------------------------------------------------------------------------
            | 10. Development application and complying development certificate
            |--------------------------------------------------------------------------
            */
            'responsibilities' => [
                'design' => [
                    'supplier' => [
                        'type' => null,
                        'organisation' => null,
                        'title' => null,
                        'firstname' => null,
                        'givennames' => null,
                        'lastname' => null,
                        'fullname' => null,
                    ],
                    'pages' => null,
                    'days_to_obtain' => null,
                ],

                'planning_approval' => [
                    'supplier' => [
                        'type' => null,
                        'organisation' => null,
                        'title' => null,
                        'firstname' => null,
                        'givennames' => null,
                        'lastname' => null,
                        'fullname' => null,
                    ],
                    'pages' => null,
                    'days_to_obtain' => null,
                ],

                'building_permit' => [
                    'supplier' => [
                        'type' => null,
                        'organisation' => null,
                        'title' => null,
                        'firstname' => null,
                        'givennames' => null,
                        'lastname' => null,
                        'fullname' => 'CAPE COD AUSTRALIA PTY LTD',  // Development application
                    ],
                    'pages' => null,
                    'days_to_obtain' => null,
                ],

                'work_specification' => [
                    'supplier' => [
                        'type' => null,
                        'organisation' => null,
                        'title' => null,
                        'firstname' => null,
                        'givennames' => null,
                        'lastname' => null,
                        'fullname' => null,
                    ],
                    'pages' => null,
                    'days_to_obtain' => null,
                ],

                'engineering_design' => [
                    'supplier' => [
                        'type' => null,
                        'organisation' => null,
                        'title' => null,
                        'firstname' => null,
                        'givennames' => null,
                        'lastname' => null,
                        'fullname' => null,
                    ],
                    'pages' => null,
                    'days_to_obtain' => null,
                ],
            ],

            /*
            |--------------------------------------------------------------------------
            | 11. Liquidated damages / 12. Interest / 13. Builder's margin
            |--------------------------------------------------------------------------
            */
            'damages' => [
                'late_completion_daily' => null,
                'liquidated_daily' => '200',
                'late_completion_percentage' => null,
                'late_payment_percentage' => '8',
                'delay_daily' => null,
            ],
            'builders_margin' => '20',


            /*
            |--------------------------------------------------------------------------
            | Covenants / exclusions / text areas
            |--------------------------------------------------------------------------
            */
            'covenants' => null,
            'statutory_obligations' => null,


            /*
            |--------------------------------------------------------------------------
            | 14. Guarantor
            |--------------------------------------------------------------------------
            */
            'guarantor' => [
                'name' => [
                    'type' => 'individual',
                    'organisation' => null,
                    'title' => null,
                    'firstname' => null,
                    'givennames' => null,
                    'lastname' => null,
                    'fullname' => null,
                ],

                'address' => [
                    'dpid' => null,
                    'building' => null,
                    'floor' => null,
                    'unit' => null,
                    'number' => null,
                    'line1' => null,
                    'line2' => null,
                    'suburb' => null,
                    'state' => null,
                    'pobox' => null,
                    'postcode' => null,
                    'country' => null,
                    'block' => null,
                    'lot' => null,
                    'section' => null,
                    'volume' => null,
                    'folio' => null,
                    'certificate_of_title' => null,
                ],

                'contact' => [
                    'workphone' => null,
                    'homephone' => null,
                    'fax' => null,
                    'mobile' => null,
                    'email' => null,
                ],

                'abn' => null,
            ],

            /*
            |--------------------------------------------------------------------------
            | Schedule 3. Excluded Items
            |--------------------------------------------------------------------------
            */
            'exclusions' => 'REFER TO SPECIFICATION CLAUSE N0 1.7 OR WORKS BY OWNER AS SHOWN ON CONTRACT PLANS',

            /*
            |--------------------------------------------------------------------------
            | Schedule 4. Description of Work
            |--------------------------------------------------------------------------
            */
            'works_description' => 'REFER TO BUILDING SPECIFCATION ISSUED CONTRACT ALONG WITH CONTRACT PLANS',

            /*
            |--------------------------------------------------------------------------
            | Schedule 5. Documents
            |--------------------------------------------------------------------------
            */
            'documents' => [
                'document' => null,
            ],

            /*
            |--------------------------------------------------------------------------
            | Schedule 6. Warranty insurance
            |--------------------------------------------------------------------------
            */
            'insurer' => [
                'name' => [
                    'type' => 'entity',
                    'organisation' => 'Insurance and Care NSW (icare)',
                    'title' => null,
                    'firstname' => null,
                    'givennames' => null,
                    'lastname' => null,
                    'fullname' => 'Insurance and Care NSW (icare)',
                ],

                'address' => [
                    'dpid' => null,
                    'building' => null,
                    'floor' => null,
                    'unit' => null,
                    'number' => null,
                    'line1' => null,
                    'line2' => null,
                    'suburb' => null,
                    'state' => null,
                    'pobox' => null,
                    'postcode' => null,
                    'country' => 'Australia',
                    'block' => null,
                    'lot' => null,
                    'section' => null,
                    'volume' => null,
                    'folio' => null,
                    'certificate_of_title' => null,
                ],

                'contact' => [
                    'workphone' => null,
                    'homephone' => null,
                    'fax' => null,
                    'mobile' => null,
                    'email' => null,
                ],

                'insured_name' => null,
                'premium' => '77',
            ],

            /*
            |--------------------------------------------------------------------------
            | Schedule 7. Prime Cost and Provisional Sum items
            |--------------------------------------------------------------------------
            */
            'prime_cost' => [
                'total' => null,
                'item' => [
                    'description' => 'REFER TO BUILDING SPECIFICATION ISSUED FOR CONTRACT',
                    'quantity' => null,
                    'rate' => null,
                    'allowance' => null,
                    'margin' => null,
                ],
            ],

            'provisional_sum' => [
                'total' => null,
                'item' => [
                    'description' => 'REFER TO BUILDING SPECIFICATION ISSUED FOR CONTRACT',
                    'quantity' => null,
                    'rate' => null,
                    'allowance' => null,
                    'margin' => null,
                ],
            ],

            /*
            |--------------------------------------------------------------------------
            | Special Conditions
            |--------------------------------------------------------------------------
            */
            'special_conditions' => 'losts of stuff here',
        ];
    }

    protected function buildCustomProgressStages(float $contractPrice): array
    {
        $definitions = [
            [
                'name' => 'BUILDING CONTRACT DEPOSIT',
                'description' => 'Deposit payable on signing of contract',
                'percent' => 10,
            ],
            [
                'name' => 'FIRST FLOOR - SCAFFOLD & ROOF DEMOLITION IN PROGRESS STAGE',
                'description' => null,
                'percent' => 5,
            ],
            [
                'name' => 'FIRST FLOOR - FLOOR FRAME IN PROGRESS STAGE',
                'description' => null,
                'percent' => 15,
            ],
            [
                'name' => 'FIRST FLOOR - WALL AND ROOF FRAME IN PROGRESS STAGE',
                'description' => null,
                'percent' => 15,
            ],
            [
                'name' => 'FIRST FLOOR - LOCK UP IN PROGRESS STAGE',
                'description' => null,
                'percent' => 15,
            ],
            [
                'name' => 'FIRST FLOOR - SERVICES ROUGH-IN IN PROGRESS STAGE',
                'description' => null,
                'percent' => 8,
            ],
            [
                'name' => 'FIRST FLOOR - PLASTERBOARD IN PROGRESS STAGE',
                'description' => null,
                'percent' => 9,
            ],
            [
                'name' => 'FIRST FLOOR - FIXOUT IN PROGRESS STAGE',
                'description' => null,
                'percent' => 8,
            ],
            [
                'name' => 'GROUND FLOOR - WORKS IN PROGRESS STAGE',
                'description' => null,
                'percent' => 3,
            ],
            [
                'name' => 'FIRST FLOOR - STAIRCASE IN PROGRESS STAGE',
                'description' => null,
                'percent' => 4,
            ],
            [
                'name' => 'FIRST FLOOR - WALL+FLOOR TILES IN PROGRESS STAGE',
                'description' => null,
                'percent' => 3,
            ],
            [
                'name' => 'PRACTICAL COMPLETION',
                'description' => null,
                'percent' => 5,
            ],
        ];

        return $this->calculateStageAmounts($definitions, $contractPrice);
    }

    protected function calculateStageAmounts(array $definitions, float $contractPrice): array
    {
        $stages = [];
        $runningTotal = 0.0;
        $lastIndex = count($definitions) - 1;

        foreach ($definitions as $index => $stage) {
            $percent = (float)($stage['percent'] ?? 0);

            if ($index === $lastIndex) {
                // Make the last stage the remainder so totals always match exactly
                $amount = round($contractPrice - $runningTotal, 2);
            } else {
                $amount = round($contractPrice * ($percent / 100), 2);
                $runningTotal += $amount;
            }

            $stages[] = [
                'name' => $stage['name'],
                'description' => $stage['description'] ?? null,
                'percent' => $percent,
                'amount' => $amount,
                'adjustment' => null,
                'update' => null,
            ];
        }

        return $stages;
    }

    protected function fullName(?string $firstName, ?string $lastName): string
    {
        return trim(collect([$firstName, $lastName])->filter()->implode(' '));
    }

    protected function formatDate($value): ?string
    {
        if (blank($value)) {
            return null;
        }

        if ($value instanceof Carbon) {
            return $value->format('Y-m-d');
        }

        try {
            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Throwable $e) {
            return null;
        }
    }
}