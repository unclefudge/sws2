<?php

namespace App\Traits;

use DB;
use Auth;
use Session;
use App\User;
use App\Models\Company\Company;
use App\Models\Company\CompanyDoc;
use App\Models\Company\CompanyDocCategory;
use App\Models\Misc\ContractorLicence;
use App\Models\Misc\ContractorLicenceSupervisor;
use App\Models\Misc\ComplianceOverride;
use App\Http\Utilities\UserDocTypes;
use App\Http\Utilities\CompanyDocTypes;
use Carbon\Carbon;


trait CompanyDocs {

    /**
     * A Company may have many CompanyDocs
     *
     * @return Collection
     */
    public function companyDocs($category_id = '', $status = '')
    {
        if ($category_id)
            return ($status == '') ? CompanyDoc::where('category_id', $category_id)->where('for_company_id', $this->id)->get() :
                CompanyDoc::where('status', $status)->where('category_id', $category_id)->where('for_company_id', $this->id)->get();
        else
            return ($status == '') ? CompanyDoc::where('for_company_id', $this->id)->get() : CompanyDoc::where('status', $status)->where('for_company_id', $this->id)->get();
    }

    /**
     * First active CompanyDoc of a specific type
     *
     * @return CompanyDoc record
     */
    public function activeCompanyDoc($category_id)
    {
        return CompanyDoc::where('category_id', $category_id)->where('for_company_id', $this->id)->where('status', '>', '0')->first();
    }

    /**
     *  Active CompanyDocs of a specific type for certain date
     *
     * @return CompanyDoc record
     */
    public function activeCompanyDocDate($category_id, $from, $to)
    {
        if ($from && $to) {
            $from_date = Carbon::createFromFormat('Y-m-d', $from)->startOfDay();
            $to_date = Carbon::createFromFormat('Y-m-d', $to)->endOfDay();
            $expiry = CompanyDoc::where('category_id', $category_id)->where('for_company_id', $this->id)->where('approved_by', '>', 0)->whereBetween('expiry', [$from_date, $to_date])->pluck('id')->toArray();
            $approved = CompanyDoc::where('category_id', $category_id)->where('for_company_id', $this->id)->where('approved_by', '>', 0)->whereBetween('approved_at', [$from_date, $to_date])->pluck('id')->toArray();
            $doc_ids = array_merge($expiry, $approved);

            return CompanyDoc::find($doc_ids)->sortBy('expiry');
        }

        return null;
    }

    /**
     *  Active CompanyDocs of a specific type for certain date
     *
     * @return CompanyDoc record
     */
    public function activeCompanyDocDateData($category_id, $from, $to)
    {
        if ($from && $to) {
            $docs = $this->activeCompanyDocDate($category_id, $from, $to);
            $refs = [];
            foreach ($docs as $doc) {
                if (!in_array($doc->ref_no, $refs))
                    $refs[] = $doc->ref_no;
            }

            $ref_no = $ref_type = $date_range = '';
            foreach ($refs as $ref) {
                $ref_no .= "$ref, ";
                $ref_type = $docs->where('ref_no', $ref)->last()->ref_type;
                $approved_date = ($docs->where('ref_no', $ref)->first()->approved_at) ? $docs->where('ref_no', $ref)->first()->approved_at->format('d/m/Y') : '';
                $expiry_date = ($docs->where('ref_no', $ref)->last()->expiry) ? $docs->where('ref_no', $ref)->last()->expiry->format('d/m/Y') : '';
                $date_range .= "$approved_date-$expiry_date, ";
            }

            $ref_no = rtrim($ref_no, ', ');
            $date_range = rtrim($date_range, ', ');
            $array = [
                'ref_no'     => $ref_no,
                'date_range' => $date_range,
                'ref_type'   => $ref_type,
            ];

            return $array;
        }

        return [];
    }

    /**
     * Expired CompanyDoc of a specific type
     *
     * @return CompanyDoc record
     */
    public function expiredCompanyDoc($category_id)
    {
        $doc = CompanyDoc::where('category_id', $category_id)->where('for_company_id', $this->id)->where('status', '0')->orderBy('expiry', 'DESC')->first();
        if ($doc)
            return $doc; // ($doc->expiry->lt(Carbon::today())) ? 'Expired ' . $doc->expiry->format('d/m/Y') : null;
        else
            return 'N/A';
    }

    /**
     * Dropdown Option for Contractor Licence
     *
     * @return string
     */
    public function contractorLicenceOptions($selected = [])
    {
        $doc = CompanyDoc::where('category_id', 7)->where('for_company_id', $this->id)->where('status', '>', '0')->first();
        if ($doc && empty($selected))
            return ContractorLicence::find(1)->classOptions(explode(',', $doc->ref_type));

        return ContractorLicence::find(1)->classOptions($selected);
    }

    /**
     * Contractor Licence No. (if active)
     *
     * @return string
     */
    public function contractorLicence()
    {
        $doc = CompanyDoc::where('category_id', 7)->where('for_company_id', $this->id)->where('status', '1')->first();

        return ($doc && $doc->ref_no) ? $doc->ref_no : '';
    }

    /**
     * Contractor Licence Class SBC
     *
     * @return string
     */
    public function contractorLicenceSBC()
    {
        $str = '';
        $doc = CompanyDoc::where('category_id', 7)->where('for_company_id', $this->id)->where('status', '>', '0')->first();
        if ($doc) {
            foreach (explode(',', $doc->ref_type) as $class_id) {
                $lic = ContractorLicence::find($class_id);
                if ($lic)
                    $str .= $lic->name . ', ';
            }
        }

        return rtrim($str, ', ');
    }

    /**
     * Determine if a certain document type is Required
     *
     * @return boolean
     */
    public function requiresCompanyDoc($type, $system = false)
    {
        // Doc types
        // 1  PL - Public Liabilty
        // 2  WC - Workers Comp
        // 3  SA - Sickness & Accident
        // 4  Sub - Subcontractors Statement
        // 5  PTC - Period Trade Contract
        // 6  TT - Test & Tag
        // 7  CL - Contractors Licence
        // 12 PP - Privacy Policy
        //
        // Categories                          | PL  |WC/SA| SUB | PTC | CL  | PP  | TT  |
        //                                     |__1__|_2/3_|__4__|__5__|__7__|_12__|__6__|
        //                                     |=========================================|
        // 0  Unallocated                      |_____|_____|_____|_____|_____|_____|_____|
        // 1  Subcontractor (On Site Trade)    |__X__|__X__|__X__|__X__|__X__|__X__|__X__|
        // 2  Service Provider (On Site trade  |__X__|__X__|__X__|_____|__X__|__X__|__X__|
        // 3  Service Provider (Off Site)      |_____|__X__|__X__|_____|_____|__X__|_____|
        // 4  Supply & Fit                     |__X__|__X__|_____|_____|__X__|__X__|_____|
        // 5  Supply Only                      |__X__|_____|_____|_____|_____|__X__|_____|
        // 6  Consultant                       |__X__|__X__|__X__|_____|_____|__X__|_____|
        // 7  Builder                          |__X__|__X__|_____|_____|_____|__X__|_____|

        // Ignore docs for some split companys (NRW Carpentry 2 (202)
        if (in_array($this->id, [202]))
            return false;


        // If System == False then check for any Compliance Overrides
        if (!$system) {
            $override = ComplianceOverride::where('type', "cd$type")->where('for_company_id', $this->id)->where('status', 1)->first();
            if ($override)
                return ($override->required) ? true : false;
        }

        // Determine WC or SA
        if (in_array($this->category, [1, 2, 3, 4, 6, 7])) {  // All but 'Supply Only'
            if ($type == 2 && in_array($this->business_entity, ['1', 'Company', '4', 'Trading Trust', '5', 'Sole Trader - with employees'])) return true;  // WC required
            if ($type == 3 && in_array($this->business_entity, ['2', 'Partnership', '3', 'Sole Trader', '5', 'Sole Trader - with employees'])) return true; // SA required
        }

        // Subcontractor (On Site Trade)
        if ($this->category == 1 && (in_array($type, [1, 4]) || ($this->parent_company == 3 && in_array($type, [5, 6])))) return true; // Requires PL, Sub, + PTC, TT (CC Only)
        if ($this->category == 1 && $type == 7 && $this->tradeRequiresContractorsLicence()) return true;

        // Service Provider (On Site Trades)
        if ($this->category == 2 && in_array($type, [1, 4])) return true; // Requires PL + Sub
        if ($this->category == 2 && $type == 7 && $this->tradeRequiresContractorsLicence()) return true;

        // Service Provider (Off Site)
        if ($this->category == 3 && $type == 4) return true; // Requires Sub

        // Supply & Fit
        if ($this->category == 4 && $type == 1) return true; // Requires PL
        if ($this->category == 4 && $type == 7 && $this->tradeRequiresContractorsLicence()) return true;

        // Supply Only
        if ($this->category == 5 && $type == 1) return true; // Requires PL

        // Consultant
        if ($this->category == 6 && in_array($type, [1, 4])) return true; // Requires PL + Sub

        // Builder
        if ($this->category == 7 && in_array($type, [1, 7])) return true; // Requires PL + BL

        // Privacy Policy
        if ($type == 12) {
            $cc_companies = Company::find(3)->companies()->pluck('id')->toArray();
            if (in_array($this->id, $cc_companies)) return true; // Requires Privacy Policy for CC companies ONLY
        }

        return false;
    }

    /**
     * Determine if a certain document type is Required
     */
    public function requiresCompanyDocText($type)
    {
        return ($this->requiresCompanyDoc($type) ? '<span class="font-red">Required</span>' : '');
    }

    /**
     * Determine if all Classes on Contractor Licence have a User with matching Supervisor qualification
     */
    public function nonCompliantContractorsLicence($user_id = '')
    {
        if ($this->requiresCompanyDoc(7)) {
            $doc = CompanyDoc::where('category_id', 7)->where('for_company_id', $this->id)->where('status', '>', '0')->first();
            if ($doc) {
                $super1_missing = '';
                $missing_docs = [];
                $number_of_supers = $doc->ref_name;
                for ($x = 1; $x <= $doc->ref_name; $x ++) {
                    $superCL = ContractorLicenceSupervisor::where('doc_id', $doc->id)->where('super', $x)->first();
                    if ($superCL) {
                        $super = User::find($superCL->user_id);
                        if ($super) {
                            $super_classes = ContractorLicenceSupervisor::where('doc_id', $doc->id)->where('super', $x)->get();
                            foreach ($super_classes as $rec) {
                                if (!($super->activeUserDoc(3) && in_array($rec->licence_id, explode(',', $super->activeUserDoc(3)->ref_type)))) {
                                    $super1_missing .= ContractorLicence::find($rec->licence_id)->name . ', ';
                                    if (isset($missing_docs[$super->name]))
                                        $missing_docs["$super->name"] .= ContractorLicence::find($rec->licence_id)->name . ', ';
                                    else
                                        $missing_docs["$super->name"] = ContractorLicence::find($rec->licence_id)->name . ', ';
                                }
                            }
                            if ($missing_docs && $missing_docs[$super->name])
                                $missing_docs[$super->name] = rtrim($missing_docs[$super->name], ', ');
                        }
                    }
                }

                return $missing_docs;
            }
        }

        return [];
    }

    /**
     * Determine if all Classes on Supervisors Licence have a User with matching Supervisor qualification
     */
    public function nonCompliantSupervisorsLicence($user_id = '')
    {
        if ($this->requiresCompanyDoc(7)) {
            $doc = CompanyDoc::where('category_id', 7)->where('for_company_id', $this->id)->where('status', '>', '0')->first();
            if ($doc) {
                $super1_missing = '';
                $missing_docs = [];
                for ($x = 1; $x <= $doc->ref_name; $x ++) {
                    $superCL = ContractorLicenceSupervisor::where('doc_id', $doc->id)->where('super', $x)->first();
                    if ($superCL) {
                        // If User given and doesn't match then skip
                        if ($user_id && $user_id != $superCL->user_id)
                            continue;

                        $super = User::find($superCL->user_id);
                        $super_classes = ContractorLicenceSupervisor::where('doc_id', $doc->id)->where('super', $x)->get();
                        foreach ($super_classes as $rec) {
                            if (!($super->activeUserDoc(4) && in_array($rec->licence_id, explode(',', $super->activeUserDoc(4)->ref_type)))) {
                                $super1_missing .= ContractorLicence::find($rec->licence_id)->name . ', ';
                                if (isset($missing_docs[$super->name]))
                                    $missing_docs[$super->name] .= ContractorLicence::find($rec->licence_id)->name . ', ';
                                else
                                    $missing_docs[$super->name] = ContractorLicence::find($rec->licence_id)->name . ', ';
                            }
                        }
                        if ($missing_docs && $missing_docs[$super->name])
                            $missing_docs[$super->name] = rtrim($missing_docs[$super->name], ', ');
                    }
                }

                return $missing_docs;
            }
        }

        return [];
    }

    /**
     * Determine if Contractors Licence is Required for any of their trades
     *
     * @return boolean
     */
    public function tradeRequiresContractorsLicence()
    {
        if ($this->category == '1' || $this->category == '2' || $this->category == '4') {
            foreach ($this->tradesSkilledIn as $trade) {
                if ($trade->licence_req)
                    return 1;
            }
        }

        return 0;
    }

    /**
     * Determine if Company is compliant ie. has required docs.
     *
     * @return booleen
     */
    public function isCompliant()
    {
        $doc_types = [1, 2, 3, 4, 5, 7, 12];
        foreach ($doc_types as $type) {
            if ($this->requiresCompanyDoc($type) && (!$this->activeCompanyDoc($type) || $this->activeCompanyDoc($type)->status != 1))
                return false;
        }

        return true;
    }

    /**
     * Documents required for a company to be compliant
     *
     * @return Text or Array
     */
    public function compliantDocs($format = 'array')
    {
        $doc_types = [1 => 'Public Liability', 2 => "Worker's Compensation", 3 => 'Sickness & Accident Insurance', 4 => 'Subcontractors Statement', 5 => 'Period Trade Contract', 6 => 'Electrical Test & Tagging', 7 => 'Contractor Licence', '12' => 'Privacy Policy'];
        $compliant_docs = [];
        $compliant_html = '';

        foreach ($doc_types as $type => $name) {
            if ($this->requiresCompanyDoc($type)) {
                $compliant_docs[$type] = $name;
                $compliant_html .= "$name, ";
            }
        }

        $compliant_html = rtrim($compliant_html, ', ');

        return ($format == 'csv') ? $compliant_html : $compliant_docs;

    }

    /**
     * Documents company has but aren't required to be compliant
     *
     * @return Text or Array
     */
    public function nonCompliantDocs($format = 'array', $status = '')
    {
        $compliant_docs = $this->compliantDocs();
        $non_compliant_docs = [];
        $non_compliant_html = '';

        foreach ($this->companyDocs() as $doc) {
            if ($doc->status && !isset($compliant_docs[$doc->category_id])) {
                if ($status != '' && $doc->status != $status)
                    continue;
                $non_compliant_docs[$doc->category_id] = $doc->name;
                $non_compliant_html .= "$doc->name, ";
            }
        }

        $non_compliant_html = rtrim($non_compliant_html, ', ');

        return ($format == 'csv') ? $non_compliant_html : $non_compliant_docs;
    }

    /**
     * List of Compliance overrides set for this company
     */
    public function complianceOverrides()
    {
        return ComplianceOverride::where('for_company_id', $this->id)->where('status', 1)->get();
    }

    /**
     * List of Compliance overrides set for this company
     */
    public function parentUpload()
    {
        return (ComplianceOverride::where('type', 'cdu')->where('for_company_id', $this->id)->where('status', 1)->first()) ? true : false;
    }


    /**
     * Missing Company Documents to be compliant
     *
     * @return Text or Array
     */
    public function missingDocs($format = 'array')
    {
        $doc_types = [1 => 'Public Liability', 2 => "Worker's Compensation", 3 => 'Sickness & Accident Insurance', 4 => 'Subcontractors Statement', 5 => 'Period Trade Contract', 6 => 'Electrical Test & Tagging', 7 => 'Contractor Licence', '12' => 'Privacy Policy'];
        $missing_docs = [];
        $missing_html = '';

        foreach ($doc_types as $type => $name) {
            if ($this->requiresCompanyDoc($type) && (!$this->activeCompanyDoc($type) || $this->activeCompanyDoc($type)->status != 1)) {
                $missing_docs[$type] = $name;
                if ($this->activeCompanyDoc($type)) {
                    $missing_status = ($this->activeCompanyDoc($type)->status == 3) ? 'label-warning' : 'label-danger';
                    $missing_html .= "<span class='label label-sm $missing_status'>$name</span>, ";
                } else
                    $missing_html .= "$name, ";
            }

        }

        $missing_html = rtrim($missing_html, ', ');

        return ($format == 'csv') ? $missing_html : $missing_docs;

    }

    /**
     * Missing Company Documents to be compliant
     *
     * @return boolean
     */
    public function isMissingDocs()
    {
        $doc_types = [1 => 'Public Liability', 2 => "Worker's Compensation", 3 => 'Sickness & Accident Insurance', 4 => 'Subcontractors Statement', 5 => 'Period Trade Contract', 6 => 'Electrical Test & Tagging', 7 => 'Contractor Licence', '12' => 'Privacy Policy'];
        foreach ($doc_types as $type => $name) {
            if ($this->requiresCompanyDoc($type) && (!$this->activeCompanyDoc($type) || $this->activeCompanyDoc($type)->status != 1))
                return true;
        }

        return false;

    }
}