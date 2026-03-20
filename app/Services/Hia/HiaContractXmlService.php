<?php

namespace App\Services\Hia;

use RuntimeException;
use SimpleXMLElement;

class HiaContractXmlService
{
    public function load(string $xmlString): SimpleXMLElement
    {
        libxml_use_internal_errors(true);

        $xml = simplexml_load_string($xmlString);

        if ($xml === false) {
            $errors = collect(libxml_get_errors())
                ->map(fn($error) => trim($error->message))
                ->implode('; ');

            libxml_clear_errors();

            throw new RuntimeException('Invalid HIA XML: ' . $errors);
        }

        libxml_clear_errors();

        $namespaces = $xml->getNamespaces(true);

        if (isset($namespaces['hia'])) {
            $xml->registerXPathNamespace('hia', $namespaces['hia']);
        }

        if (isset($namespaces['ns1'])) {
            $xml->registerXPathNamespace('ns1', $namespaces['ns1']);
        }

        return $xml;
    }

    public function toString(SimpleXMLElement $xml): string
    {
        $xmlString = $xml->asXML();

        if ($xmlString === false) {
            throw new RuntimeException('Failed converting XML back to string.');
        }

        return $xmlString;
    }

    public function apply(SimpleXMLElement $xml, array $data): SimpleXMLElement
    {
        if (isset($data['job_number'])) {
            $this->setValue($xml, '//hia:form/job_code', $data['job_number']);
            $this->setValue($xml, '//hia:form/contractid', (string)($data['contract_id'] ?? ''));
            $this->setValue($xml, '//hia:form/templateid', (string)($data['template_id'] ?? ''));
            $this->setValue($xml, '//hia:form/status', (string)($data['status'] ?? '1'));
        }

        if (isset($data['contract_date'])) {
            $this->setValue($xml, '//contract_date', $data['contract_date']);
        }

        if (isset($data['period_type'])) {
            $this->setValue($xml, '//period_type', $data['period_type']);
        }

        if (isset($data['owner'])) {
            $owner = $data['owner'];

            $this->setValue($xml, '//owners/hia:owner/name/type', $owner['type'] ?? 'individual');
            $this->setValue($xml, '//owners/hia:owner/name/organisation', $owner['organisation'] ?? null);
            $this->setValue($xml, '//owners/hia:owner/name/title', $owner['title'] ?? null);
            $this->setValue($xml, '//owners/hia:owner/name/firstname', $owner['firstname'] ?? null);
            $this->setValue($xml, '//owners/hia:owner/name/givennames', $owner['givennames'] ?? ($owner['firstname'] ?? null));
            $this->setValue($xml, '//owners/hia:owner/name/lastname', $owner['lastname'] ?? null);
            $this->setValue($xml, '//owners/hia:owner/name/fullname', $owner['fullname'] ?? null);

            $this->setValue($xml, '//owners/hia:owner/address/dpid', $owner['dpid'] ?? null);
            $this->setValue($xml, '//owners/hia:owner/address/building', $owner['building'] ?? null);
            $this->setValue($xml, '//owners/hia:owner/address/floor', $owner['floor'] ?? null);
            $this->setValue($xml, '//owners/hia:owner/address/unit', $owner['unit'] ?? null);
            $this->setValue($xml, '//owners/hia:owner/address/number', $owner['number'] ?? null);
            $this->setValue($xml, '//owners/hia:owner/address/line1', $owner['address_line1'] ?? null);
            $this->setValue($xml, '//owners/hia:owner/address/line2', $owner['address_line2'] ?? null);
            $this->setValue($xml, '//owners/hia:owner/address/suburb', $owner['suburb'] ?? null);
            $this->setValue($xml, '//owners/hia:owner/address/state', $owner['state'] ?? null);
            $this->setValue($xml, '//owners/hia:owner/address/pobox', $owner['pobox'] ?? null);
            $this->setValue($xml, '//owners/hia:owner/address/postcode', $owner['postcode'] ?? null);
            $this->setValue($xml, '//owners/hia:owner/address/country', $owner['country'] ?? 'Australia');
            $this->setValue($xml, '//owners/hia:owner/address/block', $owner['block'] ?? null);
            $this->setValue($xml, '//owners/hia:owner/address/lot', $owner['lot'] ?? null);
            $this->setValue($xml, '//owners/hia:owner/address/section', $owner['section'] ?? null);
            $this->setValue($xml, '//owners/hia:owner/address/volume', $owner['volume'] ?? null);
            $this->setValue($xml, '//owners/hia:owner/address/folio', $owner['folio'] ?? null);
            $this->setValue($xml, '//owners/hia:owner/address/certificate_of_title', $owner['certificate_of_title'] ?? null);

            $this->setValue($xml, '//owners/hia:owner/contact/workphone', $owner['workphone'] ?? null);
            $this->setValue($xml, '//owners/hia:owner/contact/homephone', $owner['homephone'] ?? null);
            $this->setValue($xml, '//owners/hia:owner/contact/fax', $owner['fax'] ?? null);
            $this->setValue($xml, '//owners/hia:owner/contact/mobile', $owner['mobile'] ?? null);
            $this->setValue($xml, '//owners/hia:owner/contact/email', $owner['email'] ?? null);

            $this->setValue($xml, '//owners/hia:owner/contact/mail/mail/dpid', $owner['mail_dpid'] ?? null);
            $this->setValue($xml, '//owners/hia:owner/contact/mail/mail/building', $owner['mail_building'] ?? null);
            $this->setValue($xml, '//owners/hia:owner/contact/mail/mail/floor', $owner['mail_floor'] ?? null);
            $this->setValue($xml, '//owners/hia:owner/contact/mail/mail/unit', $owner['mail_unit'] ?? null);
            $this->setValue($xml, '//owners/hia:owner/contact/mail/mail/number', $owner['mail_number'] ?? null);
            $this->setValue($xml, '//owners/hia:owner/contact/mail/mail/line1', $owner['mail_line1'] ?? null);
            $this->setValue($xml, '//owners/hia:owner/contact/mail/mail/line2', $owner['mail_line2'] ?? null);
            $this->setValue($xml, '//owners/hia:owner/contact/mail/mail/suburb', $owner['mail_suburb'] ?? null);
            $this->setValue($xml, '//owners/hia:owner/contact/mail/mail/state', $owner['mail_state'] ?? null);
            $this->setValue($xml, '//owners/hia:owner/contact/mail/mail/pobox', $owner['mail_pobox'] ?? null);
            $this->setValue($xml, '//owners/hia:owner/contact/mail/mail/postcode', $owner['mail_postcode'] ?? null);
            $this->setValue($xml, '//owners/hia:owner/contact/mail/mail/country', $owner['mail_country'] ?? 'Australia');
            $this->setValue($xml, '//owners/hia:owner/contact/mail/mail/block', $owner['mail_block'] ?? null);
            $this->setValue($xml, '//owners/hia:owner/contact/mail/mail/lot', $owner['mail_lot'] ?? null);
            $this->setValue($xml, '//owners/hia:owner/contact/mail/mail/section', $owner['mail_section'] ?? null);
            $this->setValue($xml, '//owners/hia:owner/contact/mail/mail/volume', $owner['mail_volume'] ?? null);
            $this->setValue($xml, '//owners/hia:owner/contact/mail/mail/folio', $owner['mail_folio'] ?? null);
            $this->setValue($xml, '//owners/hia:owner/contact/mail/mail/certificate_of_title', $owner['mail_certificate_of_title'] ?? null);

            $this->setValue($xml, '//owners/hia:owner/occupation', $owner['occupation'] ?? null);
            $this->setValue($xml, '//owners/hia:owner/abn', $owner['abn'] ?? null);
            $this->setValue($xml, '//owners/hia:owner/acn', $owner['acn'] ?? null);
            $this->setValue($xml, '//owners/hia:owner/resident', $this->nullableBoolString($owner['resident'] ?? null));
        }

        if (isset($data['owner'])) {
            $this->applyOwners($xml, $data['owner'], $data['owner2'] ?? null);
        }

        if (isset($data['builder'])) {
            $builder = $data['builder'];

            $this->setValue($xml, '//hia:builder/name/type', $builder['type'] ?? 'entity');
            $this->setValue($xml, '//hia:builder/name/organisation', $builder['organisation'] ?? null);
            $this->setValue($xml, '//hia:builder/name/title', $builder['title'] ?? null);
            $this->setValue($xml, '//hia:builder/name/firstname', $builder['firstname'] ?? null);
            $this->setValue($xml, '//hia:builder/name/givennames', $builder['givennames'] ?? null);
            $this->setValue($xml, '//hia:builder/name/lastname', $builder['lastname'] ?? null);
            $this->setValue($xml, '//hia:builder/name/fullname', $builder['fullname'] ?? ($builder['organisation'] ?? null));

            $this->setValue($xml, '//hia:builder/address/dpid', $builder['dpid'] ?? null);
            $this->setValue($xml, '//hia:builder/address/building', $builder['building'] ?? null);
            $this->setValue($xml, '//hia:builder/address/floor', $builder['floor'] ?? null);
            $this->setValue($xml, '//hia:builder/address/unit', $builder['unit'] ?? null);
            $this->setValue($xml, '//hia:builder/address/number', $builder['number'] ?? null);
            $this->setValue($xml, '//hia:builder/address/line1', $builder['address_line1'] ?? null);
            $this->setValue($xml, '//hia:builder/address/line2', $builder['address_line2'] ?? null);
            $this->setValue($xml, '//hia:builder/address/suburb', $builder['suburb'] ?? null);
            $this->setValue($xml, '//hia:builder/address/state', $builder['state'] ?? null);
            $this->setValue($xml, '//hia:builder/address/pobox', $builder['pobox'] ?? null);
            $this->setValue($xml, '//hia:builder/address/postcode', $builder['postcode'] ?? null);
            $this->setValue($xml, '//hia:builder/address/country', $builder['country'] ?? 'Australia');
            $this->setValue($xml, '//hia:builder/address/block', $builder['block'] ?? null);
            $this->setValue($xml, '//hia:builder/address/lot', $builder['lot'] ?? null);
            $this->setValue($xml, '//hia:builder/address/section', $builder['section'] ?? null);
            $this->setValue($xml, '//hia:builder/address/volume', $builder['volume'] ?? null);
            $this->setValue($xml, '//hia:builder/address/folio', $builder['folio'] ?? null);
            $this->setValue($xml, '//hia:builder/address/certificate_of_title', $builder['certificate_of_title'] ?? null);

            $this->setValue($xml, '//hia:builder/contact/workphone', $builder['workphone'] ?? null);
            $this->setValue($xml, '//hia:builder/contact/homephone', $builder['homephone'] ?? null);
            $this->setValue($xml, '//hia:builder/contact/fax', $builder['fax'] ?? null);
            $this->setValue($xml, '//hia:builder/contact/mobile', $builder['mobile'] ?? null);
            $this->setValue($xml, '//hia:builder/contact/email', $builder['email'] ?? null);

            $this->setValue($xml, '//hia:builder/contact/mail/mail/dpid', $builder['mail_dpid'] ?? null);
            $this->setValue($xml, '//hia:builder/contact/mail/mail/building', $builder['mail_building'] ?? null);
            $this->setValue($xml, '//hia:builder/contact/mail/mail/floor', $builder['mail_floor'] ?? null);
            $this->setValue($xml, '//hia:builder/contact/mail/mail/unit', $builder['mail_unit'] ?? null);
            $this->setValue($xml, '//hia:builder/contact/mail/mail/number', $builder['mail_number'] ?? null);
            $this->setValue($xml, '//hia:builder/contact/mail/mail/line1', $builder['mail_line1'] ?? null);
            $this->setValue($xml, '//hia:builder/contact/mail/mail/line2', $builder['mail_line2'] ?? null);
            $this->setValue($xml, '//hia:builder/contact/mail/mail/suburb', $builder['mail_suburb'] ?? null);
            $this->setValue($xml, '//hia:builder/contact/mail/mail/state', $builder['mail_state'] ?? null);
            $this->setValue($xml, '//hia:builder/contact/mail/mail/pobox', $builder['mail_pobox'] ?? null);
            $this->setValue($xml, '//hia:builder/contact/mail/mail/postcode', $builder['mail_postcode'] ?? null);
            $this->setValue($xml, '//hia:builder/contact/mail/mail/country', $builder['mail_country'] ?? 'Australia');
            $this->setValue($xml, '//hia:builder/contact/mail/mail/block', $builder['mail_block'] ?? null);
            $this->setValue($xml, '//hia:builder/contact/mail/mail/lot', $builder['mail_lot'] ?? null);
            $this->setValue($xml, '//hia:builder/contact/mail/mail/section', $builder['mail_section'] ?? null);
            $this->setValue($xml, '//hia:builder/contact/mail/mail/volume', $builder['mail_volume'] ?? null);
            $this->setValue($xml, '//hia:builder/contact/mail/mail/folio', $builder['mail_folio'] ?? null);
            $this->setValue($xml, '//hia:builder/contact/mail/mail/certificate_of_title', $builder['mail_certificate_of_title'] ?? null);

            $this->setValue($xml, '//hia:builder/licensenumber', $builder['licence_number'] ?? null);
            $this->setValue($xml, '//hia:builder/licenseworkcategory', $builder['licence_category'] ?? null);
            $this->setValue($xml, '//hia:builder/hiamembernumber', $builder['hia_member_number'] ?? null);
            $this->setValue($xml, '//hia:builder/hiamembershipexpiry', $builder['hia_membership_expiry'] ?? null);
            $this->setValue($xml, '//hia:builder/abn', $builder['abn'] ?? null);
            $this->setValue($xml, '//hia:builder/acn', $builder['acn'] ?? null);
            $this->setValue($xml, '//hia:builder/registeredbuildingpracticioner', $builder['registered_building_practitioner'] ?? null);
        }
        /*if (isset($data['builder_execution'])) {
            $exec = $data['builder_execution'];
            $this->setValue($xml, '//builder_execution/signed_by', $exec['signed_by'] ?? null);
            $this->setValue($xml, '//builder_execution/witness_name', $exec['witness_name'] ?? null);
            $this->setValue($xml, '//builder_execution/witness_address', $exec['witness_address'] ?? null);
        }*/

        if (isset($data['site'])) {
            $site = $data['site'];

            $this->setValue($xml, '//hia:site/dpid', $site['dpid'] ?? null);
            $this->setValue($xml, '//hia:site/building', $site['building'] ?? null);
            $this->setValue($xml, '//hia:site/floor', $site['floor'] ?? null);
            $this->setValue($xml, '//hia:site/unit', $site['unit'] ?? null);
            $this->setValue($xml, '//hia:site/number', $site['number'] ?? null);
            $this->setValue($xml, '//hia:site/line1', $site['line1'] ?? null);
            $this->setValue($xml, '//hia:site/line2', $site['line2'] ?? null);
            $this->setValue($xml, '//hia:site/suburb', $site['suburb'] ?? null);
            $this->setValue($xml, '//hia:site/state', $site['state'] ?? null);
            $this->setValue($xml, '//hia:site/pobox', $site['pobox'] ?? null);
            $this->setValue($xml, '//hia:site/postcode', $site['postcode'] ?? null);
            $this->setValue($xml, '//hia:site/country', $site['country'] ?? 'Australia');
            $this->setValue($xml, '//hia:site/block', $site['block'] ?? null);
            $this->setValue($xml, '//hia:site/lot', $site['lot'] ?? null);
            $this->setValue($xml, '//hia:site/section', $site['section'] ?? null);
            $this->setValue($xml, '//hia:site/volume', $site['volume'] ?? null);
            $this->setValue($xml, '//hia:site/folio', $site['folio'] ?? null);
            $this->setValue($xml, '//hia:site/division', $site['division'] ?? null);
            $this->setValue($xml, '//hia:site/certificate_of_title', $site['certificate_of_title'] ?? null);
        }

        if (isset($data['payment'])) {
            $payment = $data['payment'];

            $this->setValue($xml, '//hia:payment_price/deposit', $this->money($payment['deposit'] ?? null));
            $this->setValue($xml, '//hia:payment_price/total_ex_gst', $this->money($payment['total_ex_gst'] ?? null));
            $this->setValue($xml, '//hia:payment_price/gst', $this->money($payment['gst'] ?? null));
            $this->setValue($xml, '//hia:payment_price/total_inc_gst', $this->money($payment['total_inc_gst'] ?? null));
            $this->setValue($xml, '//hia:payment_price/rounded', $this->boolString($payment['rounded'] ?? false));

            if (isset($payment['progress_payment'])) {
                $this->applyProgressPayment($xml, $payment['progress_payment']);
            }
        }

        if (isset($data['mortgage'])) {
            $mortgage = $data['mortgage'];

            $this->setValue($xml, '//hia:mortgages/mortgage/lending_body', $mortgage['lending_body'] ?? null);
            $this->setValue($xml, '//hia:mortgages/mortgage/amount', $this->money($mortgage['amount'] ?? null));
            $this->setValue($xml, '//hia:mortgages/mortgage/branch', $mortgage['branch'] ?? null);
            $this->setValue($xml, '//hia:mortgages/mortgage/contact', $mortgage['contact'] ?? null);
            $this->setValue($xml, '//hia:mortgages/mortgage/supply_date', $mortgage['supply_date'] ?? null);
            $this->setValue($xml, '//hia:mortgages/mortgage/interest', $mortgage['interest'] ?? null);
            $this->setValue($xml, '//hia:mortgages/mortgage/term_length', $mortgage['term_length'] ?? null);
        }

        if (isset($data['funding'])) {
            $funding = $data['funding'];

            $this->setValue($xml, '//hia:funding_list/total', $this->money($funding['total'] ?? null));

            if (isset($funding['item'])) {
                $item = $funding['item'];
                $this->setValue($xml, '//hia:funding_list/item/description', $item['description'] ?? null);
                $this->setValue($xml, '//hia:funding_list/item/amount', $this->money($item['amount'] ?? null));
                $this->setValue($xml, '//hia:funding_list/item/approval_period', $item['approval_period'] ?? null);
            }
        }

        if (isset($data['lending']['funds'])) {
            $funds = $data['lending']['funds'];

            $this->setValue($xml, '//lending/funds/lending_body', $funds['lending_body'] ?? null);
            $this->setValue($xml, '//lending/funds/branch', $funds['branch'] ?? null);
            $this->setValue($xml, '//lending/funds/contact', $funds['contact'] ?? null);
        }

        if (isset($data['timeframe'])) {
            $timeframe = $data['timeframe'];

            $this->setValue($xml, '//hia:timeframe/start', $timeframe['start'] ?? null);
            $this->setValue($xml, '//hia:timeframe/price_review', $timeframe['price_review'] ?? null);
            $this->setValue($xml, '//hia:timeframe/start_price_review_string', $timeframe['start_price_review_string'] ?? null);
            $this->setValue($xml, '//hia:timeframe/end', $timeframe['end'] ?? null);

            if (isset($timeframe['days'])) {
                $days = $timeframe['days'];
                $this->setValue($xml, '//hia:timeframe/days/commencement', $days['commencement'] ?? null);
                $this->setValue($xml, '//hia:timeframe/days/completion', $days['completion'] ?? null);
                $this->setValue($xml, '//hia:timeframe/days/weather', $days['weather'] ?? null);
                $this->setValue($xml, '//hia:timeframe/days/weekend', $days['weekend'] ?? null);
                $this->setValue($xml, '//hia:timeframe/days/other', $days['other'] ?? null);
                $this->setValue($xml, '//hia:timeframe/days/defect_liability', $days['defect_liability'] ?? null);
                $this->setValue($xml, '//hia:timeframe/days/defect_rectification', $days['defect_rectification'] ?? null);
                $this->setValue($xml, '//hia:timeframe/days/progress_payment', $days['progress_payment'] ?? null);
            }
        }

        if (isset($data['responsibilities'])) {
            $this->applyResponsibilities($xml, $data['responsibilities']);
        }

        if (array_key_exists('builders_margin', $data)) {
            $this->setValue($xml, '//hia:builders_margin', $data['builders_margin'] !== null ? $this->numberString($data['builders_margin']) : null);
        }

        if (isset($data['damages'])) {
            $damages = $data['damages'];

            $this->setValue($xml, '//hia:damages/late_completion_daily', $this->money($damages['late_completion_daily'] ?? null));
            $this->setValue($xml, '//hia:damages/liquidated_daily', $this->money($damages['liquidated_daily'] ?? null));
            $this->setValue($xml, '//hia:damages/late_completion_percentage', $this->numberString($damages['late_completion_percentage'] ?? null));
            $this->setValue($xml, '//hia:damages/late_payment_percentage', $this->numberString($damages['late_payment_percentage'] ?? null));
            $this->setValue($xml, '//hia:damages/delay_daily', $this->money($damages['delay_daily'] ?? null));
        }

        if (array_key_exists('covenants', $data)) {
            $this->setValue($xml, '//hia:covenants', $data['covenants']);
        }

        if (array_key_exists('exclusions', $data)) {
            $this->setValue($xml, '//hia:exclusions', $data['exclusions']);
        }

        if (array_key_exists('special_conditions', $data)) {
            $this->setValue($xml, '//hia:special_conditions', $data['special_conditions']);
        }

        if (array_key_exists('works_description', $data)) {
            $this->setValue($xml, '//hia:works_description', $data['works_description']);
        }

        if (array_key_exists('statutory_obligations', $data)) {
            $this->setValue($xml, '//statutory_obligations', $data['statutory_obligations']);
        }

        if (isset($data['documents'])) {
            $this->setValue($xml, '//hia:documents/document', $data['documents']['document'] ?? null);
        }

        if (isset($data['guarantor'])) {
            $this->applyGuarantor($xml, $data['guarantor']);
        }

        if (isset($data['insurer'])) {
            $this->applyInsurer($xml, $data['insurer']);
        }

        if (isset($data['prime_cost'])) {
            $this->applyPrimeCost($xml, $data['prime_cost']);
        }

        if (isset($data['provisional_sum'])) {
            $this->applyProvisionalSum($xml, $data['provisional_sum']);
        }

        return $xml;
    }

    protected function setValue(SimpleXMLElement $xml, string $xpath, ?string $value): void
    {
        $nodes = $xml->xpath($xpath);

        if (!$nodes || !isset($nodes[0])) {
            return;
        }

        $nodes[0][0] = $value ?? '';
    }

    protected function applyProgressPayment(\SimpleXMLElement $xml, array $progressPayment): void
    {
        $this->setValue($xml, '//hia:payment_price/progress_payment/method', $progressPayment['method'] ?? null);
        $this->setValue($xml, '//hia:payment_price/progress_payment/custom_reason', $progressPayment['custom_reason'] ?? null);
        $this->setValue($xml, '//hia:payment_price/progress_payment/base_stage_type', $progressPayment['base_stage_type'] ?? null);

        // If using customised stages, populate that section
        if (($progressPayment['method'] ?? null) === 'customised') {
            $this->replaceCustomisedStages($xml, $progressPayment);
        }
    }

    protected function applyResponsibilities(\SimpleXMLElement $xml, array $responsibilities): void
    {
        foreach (['design', 'planning_approval', 'building_permit', 'work_specification', 'engineering_design'] as $section) {
            if (!isset($responsibilities[$section])) {
                continue;
            }

            $item = $responsibilities[$section];
            $base = "//hia:responsibilities/{$section}";

            if (isset($item['supplier'])) {
                $supplier = $item['supplier'];
                $this->setValue($xml, "{$base}/supplier/type", $supplier['type'] ?? null);
                $this->setValue($xml, "{$base}/supplier/organisation", $supplier['organisation'] ?? null);
                $this->setValue($xml, "{$base}/supplier/title", $supplier['title'] ?? null);
                $this->setValue($xml, "{$base}/supplier/firstname", $supplier['firstname'] ?? null);
                $this->setValue($xml, "{$base}/supplier/givennames", $supplier['givennames'] ?? null);
                $this->setValue($xml, "{$base}/supplier/lastname", $supplier['lastname'] ?? null);
                $this->setValue($xml, "{$base}/supplier/fullname", $supplier['fullname'] ?? null);
            }

            $this->setValue($xml, "{$base}/pages", $item['pages'] ?? null);
            $this->setValue($xml, "{$base}/days_to_obtain", $item['days_to_obtain'] ?? null);
        }
    }

    protected function applyGuarantor(\SimpleXMLElement $xml, array $guarantor): void
    {
        if (isset($guarantor['name'])) {
            $name = $guarantor['name'];
            $this->setValue($xml, '//guarantors/hia:guarantor/name/type', $name['type'] ?? null);
            $this->setValue($xml, '//guarantors/hia:guarantor/name/organisation', $name['organisation'] ?? null);
            $this->setValue($xml, '//guarantors/hia:guarantor/name/title', $name['title'] ?? null);
            $this->setValue($xml, '//guarantors/hia:guarantor/name/firstname', $name['firstname'] ?? null);
            $this->setValue($xml, '//guarantors/hia:guarantor/name/givennames', $name['givennames'] ?? null);
            $this->setValue($xml, '//guarantors/hia:guarantor/name/lastname', $name['lastname'] ?? null);
            $this->setValue($xml, '//guarantors/hia:guarantor/name/fullname', $name['fullname'] ?? null);
        }

        if (isset($guarantor['address'])) {
            $address = $guarantor['address'];
            $base = '//guarantors/hia:guarantor/address';
            $this->setValue($xml, "{$base}/dpid", $address['dpid'] ?? null);
            $this->setValue($xml, "{$base}/building", $address['building'] ?? null);
            $this->setValue($xml, "{$base}/floor", $address['floor'] ?? null);
            $this->setValue($xml, "{$base}/unit", $address['unit'] ?? null);
            $this->setValue($xml, "{$base}/number", $address['number'] ?? null);
            $this->setValue($xml, "{$base}/line1", $address['line1'] ?? null);
            $this->setValue($xml, "{$base}/line2", $address['line2'] ?? null);
            $this->setValue($xml, "{$base}/suburb", $address['suburb'] ?? null);
            $this->setValue($xml, "{$base}/state", $address['state'] ?? null);
            $this->setValue($xml, "{$base}/pobox", $address['pobox'] ?? null);
            $this->setValue($xml, "{$base}/postcode", $address['postcode'] ?? null);
            $this->setValue($xml, "{$base}/country", $address['country'] ?? null);
            $this->setValue($xml, "{$base}/block", $address['block'] ?? null);
            $this->setValue($xml, "{$base}/lot", $address['lot'] ?? null);
            $this->setValue($xml, "{$base}/section", $address['section'] ?? null);
            $this->setValue($xml, "{$base}/volume", $address['volume'] ?? null);
            $this->setValue($xml, "{$base}/folio", $address['folio'] ?? null);
            $this->setValue($xml, "{$base}/certificate_of_title", $address['certificate_of_title'] ?? null);
        }

        if (isset($guarantor['contact'])) {
            $contact = $guarantor['contact'];
            $base = '//guarantors/hia:guarantor/contact';
            $this->setValue($xml, "{$base}/workphone", $contact['workphone'] ?? null);
            $this->setValue($xml, "{$base}/homephone", $contact['homephone'] ?? null);
            $this->setValue($xml, "{$base}/fax", $contact['fax'] ?? null);
            $this->setValue($xml, "{$base}/mobile", $contact['mobile'] ?? null);
            $this->setValue($xml, "{$base}/email", $contact['email'] ?? null);
        }

        $this->setValue($xml, '//guarantors/hia:guarantor/abn', $guarantor['abn'] ?? null);
    }

    protected function applyInsurer(\SimpleXMLElement $xml, array $insurer): void
    {
        if (isset($insurer['name'])) {
            $name = $insurer['name'];
            $this->setValue($xml, '//hia:insurer/name/type', $name['type'] ?? null);
            $this->setValue($xml, '//hia:insurer/name/organisation', $name['organisation'] ?? null);
            $this->setValue($xml, '//hia:insurer/name/title', $name['title'] ?? null);
            $this->setValue($xml, '//hia:insurer/name/firstname', $name['firstname'] ?? null);
            $this->setValue($xml, '//hia:insurer/name/givennames', $name['givennames'] ?? null);
            $this->setValue($xml, '//hia:insurer/name/lastname', $name['lastname'] ?? null);
            $this->setValue($xml, '//hia:insurer/name/fullname', $name['fullname'] ?? null);
        }

        if (isset($insurer['address'])) {
            $address = $insurer['address'];
            $base = '//hia:insurer/address';
            $this->setValue($xml, "{$base}/dpid", $address['dpid'] ?? null);
            $this->setValue($xml, "{$base}/building", $address['building'] ?? null);
            $this->setValue($xml, "{$base}/floor", $address['floor'] ?? null);
            $this->setValue($xml, "{$base}/unit", $address['unit'] ?? null);
            $this->setValue($xml, "{$base}/number", $address['number'] ?? null);
            $this->setValue($xml, "{$base}/line1", $address['line1'] ?? null);
            $this->setValue($xml, "{$base}/line2", $address['line2'] ?? null);
            $this->setValue($xml, "{$base}/suburb", $address['suburb'] ?? null);
            $this->setValue($xml, "{$base}/state", $address['state'] ?? null);
            $this->setValue($xml, "{$base}/pobox", $address['pobox'] ?? null);
            $this->setValue($xml, "{$base}/postcode", $address['postcode'] ?? null);
            $this->setValue($xml, "{$base}/country", $address['country'] ?? null);
            $this->setValue($xml, "{$base}/block", $address['block'] ?? null);
            $this->setValue($xml, "{$base}/lot", $address['lot'] ?? null);
            $this->setValue($xml, "{$base}/section", $address['section'] ?? null);
            $this->setValue($xml, "{$base}/volume", $address['volume'] ?? null);
            $this->setValue($xml, "{$base}/folio", $address['folio'] ?? null);
            $this->setValue($xml, "{$base}/certificate_of_title", $address['certificate_of_title'] ?? null);
        }

        if (isset($insurer['contact'])) {
            $contact = $insurer['contact'];
            $base = '//hia:insurer/contact';
            $this->setValue($xml, "{$base}/workphone", $contact['workphone'] ?? null);
            $this->setValue($xml, "{$base}/homephone", $contact['homephone'] ?? null);
            $this->setValue($xml, "{$base}/fax", $contact['fax'] ?? null);
            $this->setValue($xml, "{$base}/mobile", $contact['mobile'] ?? null);
            $this->setValue($xml, "{$base}/email", $contact['email'] ?? null);
        }

        $this->setValue($xml, '//hia:insurer/insured_name', $insurer['insured_name'] ?? null);
        $this->setValue($xml, '//hia:insurer/premium', $this->money($insurer['premium'] ?? null));
    }

    protected function applyOwners(SimpleXMLElement $xml, array $owner1, ?array $owner2 = null): void
    {
        $ownersNodes = $xml->xpath('//owners');

        if (!$ownersNodes || !isset($ownersNodes[0])) {
            return;
        }

        $owners = $ownersNodes[0];

        // Find existing owner nodes
        $existingOwnerNodes = $xml->xpath('//owners/hia:owner');

        if (!$existingOwnerNodes || !isset($existingOwnerNodes[0])) {
            return;
        }

        // First owner node already exists in template
        $this->applyOwnerNode($existingOwnerNodes[0], $owner1);

        // If no second owner provided, stop here
        if (empty($owner2['fullname']) && empty($owner2['firstname']) && empty($owner2['lastname'])) {
            return;
        }

        // Get updated owner nodes again after first write
        $existingOwnerNodes = $xml->xpath('//owners/hia:owner');

        if (isset($existingOwnerNodes[1])) {
            $secondOwnerNode = $existingOwnerNodes[1];
        } else {
            $secondOwnerNode = $this->cloneOwnerNode($owners, $existingOwnerNodes[0]);
        }

        $this->applyOwnerNode($secondOwnerNode, $owner2);
    }

    protected function cloneOwnerNode(SimpleXMLElement $ownersParent, SimpleXMLElement $sourceOwner): SimpleXMLElement
    {
        $ownersDom = dom_import_simplexml($ownersParent);
        $sourceDom = dom_import_simplexml($sourceOwner);

        if (!$ownersDom || !$sourceDom) {
            throw new RuntimeException('Unable to clone HIA owner node.');
        }

        $clonedDom = $sourceDom->cloneNode(true);

        // Clear all text values in the clone so it becomes a blank owner template
        $this->clearDomNodeValues($clonedDom);

        $ownersDom->appendChild($clonedDom);

        $newOwner = simplexml_import_dom($clonedDom);

        if (!$newOwner) {
            throw new RuntimeException('Unable to import cloned HIA owner node.');
        }

        return $newOwner;
    }

    protected function clearDomNodeValues(\DOMNode $node): void
    {
        if ($node->nodeType === XML_TEXT_NODE) {
            $node->nodeValue = '';
            return;
        }

        if ($node->hasChildNodes()) {
            foreach (iterator_to_array($node->childNodes) as $child) {
                $this->clearDomNodeValues($child);
            }
        }
    }

    protected function applyOwnerNode(SimpleXMLElement $ownerNode, array $owner): void
    {
        $this->setRelativeValue($ownerNode, 'name/type', $owner['type'] ?? 'individual');
        $this->setRelativeValue($ownerNode, 'name/organisation', $owner['organisation'] ?? null);
        $this->setRelativeValue($ownerNode, 'name/title', $owner['title'] ?? null);
        $this->setRelativeValue($ownerNode, 'name/firstname', $owner['firstname'] ?? null);
        $this->setRelativeValue($ownerNode, 'name/givennames', $owner['givennames'] ?? ($owner['firstname'] ?? null));
        $this->setRelativeValue($ownerNode, 'name/lastname', $owner['lastname'] ?? null);
        $this->setRelativeValue($ownerNode, 'name/fullname', $owner['fullname'] ?? null);

        $this->setRelativeValue($ownerNode, 'address/dpid', $owner['dpid'] ?? null);
        $this->setRelativeValue($ownerNode, 'address/building', $owner['building'] ?? null);
        $this->setRelativeValue($ownerNode, 'address/floor', $owner['floor'] ?? null);
        $this->setRelativeValue($ownerNode, 'address/unit', $owner['unit'] ?? null);
        $this->setRelativeValue($ownerNode, 'address/number', $owner['number'] ?? null);
        $this->setRelativeValue($ownerNode, 'address/line1', $owner['address_line1'] ?? null);
        $this->setRelativeValue($ownerNode, 'address/line2', $owner['address_line2'] ?? null);
        $this->setRelativeValue($ownerNode, 'address/suburb', $owner['suburb'] ?? null);
        $this->setRelativeValue($ownerNode, 'address/state', $owner['state'] ?? null);
        $this->setRelativeValue($ownerNode, 'address/pobox', $owner['pobox'] ?? null);
        $this->setRelativeValue($ownerNode, 'address/postcode', $owner['postcode'] ?? null);
        $this->setRelativeValue($ownerNode, 'address/country', $owner['country'] ?? 'Australia');
        $this->setRelativeValue($ownerNode, 'address/block', $owner['block'] ?? null);
        $this->setRelativeValue($ownerNode, 'address/lot', $owner['lot'] ?? null);
        $this->setRelativeValue($ownerNode, 'address/section', $owner['section'] ?? null);
        $this->setRelativeValue($ownerNode, 'address/volume', $owner['volume'] ?? null);
        $this->setRelativeValue($ownerNode, 'address/folio', $owner['folio'] ?? null);
        $this->setRelativeValue($ownerNode, 'address/certificate_of_title', $owner['certificate_of_title'] ?? null);

        $this->setRelativeValue($ownerNode, 'contact/workphone', $owner['workphone'] ?? null);
        $this->setRelativeValue($ownerNode, 'contact/homephone', $owner['homephone'] ?? null);
        $this->setRelativeValue($ownerNode, 'contact/fax', $owner['fax'] ?? null);
        $this->setRelativeValue($ownerNode, 'contact/mobile', $owner['mobile'] ?? null);
        $this->setRelativeValue($ownerNode, 'contact/email', $owner['email'] ?? null);

        $this->setRelativeValue($ownerNode, 'contact/mail/mail/dpid', $owner['mail_dpid'] ?? null);
        $this->setRelativeValue($ownerNode, 'contact/mail/mail/building', $owner['mail_building'] ?? null);
        $this->setRelativeValue($ownerNode, 'contact/mail/mail/floor', $owner['mail_floor'] ?? null);
        $this->setRelativeValue($ownerNode, 'contact/mail/mail/unit', $owner['mail_unit'] ?? null);
        $this->setRelativeValue($ownerNode, 'contact/mail/mail/number', $owner['mail_number'] ?? null);
        $this->setRelativeValue($ownerNode, 'contact/mail/mail/line1', $owner['mail_line1'] ?? null);
        $this->setRelativeValue($ownerNode, 'contact/mail/mail/line2', $owner['mail_line2'] ?? null);
        $this->setRelativeValue($ownerNode, 'contact/mail/mail/suburb', $owner['mail_suburb'] ?? null);
        $this->setRelativeValue($ownerNode, 'contact/mail/mail/state', $owner['mail_state'] ?? null);
        $this->setRelativeValue($ownerNode, 'contact/mail/mail/pobox', $owner['mail_pobox'] ?? null);
        $this->setRelativeValue($ownerNode, 'contact/mail/mail/postcode', $owner['mail_postcode'] ?? null);
        $this->setRelativeValue($ownerNode, 'contact/mail/mail/country', $owner['mail_country'] ?? 'Australia');
        $this->setRelativeValue($ownerNode, 'contact/mail/mail/block', $owner['mail_block'] ?? null);
        $this->setRelativeValue($ownerNode, 'contact/mail/mail/lot', $owner['mail_lot'] ?? null);
        $this->setRelativeValue($ownerNode, 'contact/mail/mail/section', $owner['mail_section'] ?? null);
        $this->setRelativeValue($ownerNode, 'contact/mail/mail/volume', $owner['mail_volume'] ?? null);
        $this->setRelativeValue($ownerNode, 'contact/mail/mail/folio', $owner['mail_folio'] ?? null);
        $this->setRelativeValue($ownerNode, 'contact/mail/mail/certificate_of_title', $owner['mail_certificate_of_title'] ?? null);

        $this->setRelativeValue($ownerNode, 'occupation', $owner['occupation'] ?? null);
        $this->setRelativeValue($ownerNode, 'abn', $owner['abn'] ?? null);
        $this->setRelativeValue($ownerNode, 'acn', $owner['acn'] ?? null);
        $this->setRelativeValue($ownerNode, 'resident', $this->nullableBoolString($owner['resident'] ?? null));
    }

    protected function setRelativeValue(SimpleXMLElement $contextNode, string $relativeXpath, ?string $value): void
    {
        $nodes = $contextNode->xpath($relativeXpath);

        if (!$nodes || !isset($nodes[0])) {
            return;
        }

        $nodes[0][0] = $value ?? '';
    }

    protected function applyPrimeCost(\SimpleXMLElement $xml, array $primeCost): void
    {
        $this->setValue($xml, '//hia:prime_cost/total', $this->money($primeCost['total'] ?? null));

        if (isset($primeCost['item'])) {
            $item = $primeCost['item'];
            $this->setValue($xml, '//hia:prime_cost/item/description', $item['description'] ?? null);
            $this->setValue($xml, '//hia:prime_cost/item/quantity', $this->numberString($item['quantity'] ?? null));
            $this->setValue($xml, '//hia:prime_cost/item/rate', $this->money($item['rate'] ?? null));
            $this->setValue($xml, '//hia:prime_cost/item/allowance', $this->money($item['allowance'] ?? null));
            $this->setValue($xml, '//hia:prime_cost/item/margin', $this->numberString($item['margin'] ?? null));
        }
    }

    protected function applyProvisionalSum(\SimpleXMLElement $xml, array $provisionalSum): void
    {
        $this->setValue($xml, '//hia:provisional_sum/total', $this->money($provisionalSum['total'] ?? null));

        if (isset($provisionalSum['item'])) {
            $item = $provisionalSum['item'];
            $this->setValue($xml, '//hia:provisional_sum/item/description', $item['description'] ?? null);
            $this->setValue($xml, '//hia:provisional_sum/item/quantity', $this->numberString($item['quantity'] ?? null));
            $this->setValue($xml, '//hia:provisional_sum/item/rate', $this->money($item['rate'] ?? null));
            $this->setValue($xml, '//hia:provisional_sum/item/allowance', $this->money($item['allowance'] ?? null));
            $this->setValue($xml, '//hia:provisional_sum/item/margin', $this->numberString($item['margin'] ?? null));
        }
    }

    protected function replaceCustomisedStages(\SimpleXMLElement $xml, array $progressPayment): void
    {
        $customisedStagesNodes = $xml->xpath('//hia:payment_price/progress_payment/customised_stages');

        if (!$customisedStagesNodes || !isset($customisedStagesNodes[0])) {
            return;
        }

        $customisedStages = $customisedStagesNodes[0];

        // Clear existing children
        $domNode = dom_import_simplexml($customisedStages);
        if (!$domNode) {
            return;
        }

        while ($domNode->firstChild) {
            $domNode->removeChild($domNode->firstChild);
        }

        // Re-add summary fields
        $this->addChild($customisedStages, 'rounding_message', $progressPayment['rounding_message'] ?? null);
        $this->addChild($customisedStages, 'total_calculated_percent', (string)($progressPayment['total_calculated_percent'] ?? ''));
        $this->addChild($customisedStages, 'total_calculated_amount', $this->money($progressPayment['total_calculated_amount'] ?? null));
        $this->addChild($customisedStages, 'adjustment', (string)($progressPayment['adjustment'] ?? 0));

        foreach (($progressPayment['stages'] ?? []) as $stageData) {
            $stage = $customisedStages->addChild('stage');

            $stage->addAttribute('percent_adjustable', 'true');
            $stage->addAttribute('text_adjustable', 'true');

            $this->addChild($stage, 'name', $stageData['name'] ?? null);
            $this->addChild($stage, 'description', $stageData['description'] ?? null);
            $this->addChild($stage, 'percent', $this->numberString($stageData['percent'] ?? null));
            $this->addChild($stage, 'amount', $this->money($stageData['amount'] ?? null));
            $this->addChild($stage, 'adjustment', $this->numberString($stageData['adjustment'] ?? null));
            $this->addChild($stage, 'update', $stageData['update'] ?? null);
        }
    }

    protected function addChild(\SimpleXMLElement $parent, string $name, $value = null): \SimpleXMLElement
    {
        $child = $parent->addChild($name);

        if ($value !== null && $value !== '') {
            $child[0] = (string)$value;
        }

        return $child;
    }

    protected function money($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return number_format((float)$value, 2, '.', '');
    }

    protected function numberString($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (string)$value;
    }

    protected function boolString(bool $value): string
    {
        return $value ? 'true' : 'false';
    }

    protected function nullableBoolString($value): ?string
    {
        if ($value === null) {
            return null;
        }

        return $value ? 'true' : 'false';
    }
}