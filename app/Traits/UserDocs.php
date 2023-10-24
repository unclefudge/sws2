<?php
namespace App\Traits;

use DB;
use Auth;
use Session;
use App\User;
use App\Models\User\UserDocCategory;
use App\Models\User\UserDoc;
use App\Models\Misc\ContractorLicence;
use App\Models\Misc\ContractorLicenceSupervisor;
use App\Models\Misc\ComplianceOverride;
use App\Http\Utilities\UserDocTypes;
use App\Http\Utilities\CompanyDocTypes;
use Carbon\Carbon;


trait UserDocs {


    /**
     * A User may have many UserDocs
     *
     * @return Collection
     */
    public function userDocs($category_id = '', $status = '')
    {
        if ($category_id)
            return ($status == '') ? UserDoc::where('category_id', $category_id)->where('for_company_id', $this->id)->get() :
                UserDoc::where('status', $status)->where('category_id', $category_id)->where('for_company_id', $this->id)->get();
        else
            return ($status == '') ? UserDoc::where('user_id', $this->id)->get() : UserDoc::where('status', $status)->where('user_id', $this->id)->get();
    }

    /**
     * First active UserDoc of a specific type
     *
     * @return UserDoc record
     */
    public function activeUserDoc($category_id)
    {
        return UserDoc::where('category_id', $category_id)->where('user_id', $this->id)->where('status', '>', '0')->first();
    }

    /**
     * Expired UserDoc of a specific type
     *
     * @return UserDoc record
     */
    public function expiredUserDoc($category_id)
    {
        $doc = UserDoc::where('category_id', $category_id)->where('user_id', $this->id)->where('status', '>', '0')->first();
        if ($doc)
            return ($doc->expiry->lt(Carbon::today())) ? 'Expired ' . $doc->expiry->format('d/m/Y') : null;
        else
            return 'N/A';
    }

    /**
     * A dropdown list of types of Company Document Types user can access
     *
     * @return array
     */
    public function userDocTypeSelect($action, $user, $prompt = '')
    {
        $array = [];
        $single = DB::table('user_docs_categories')->whereIn('company_id', ['1', '3'])->where('multiple', 0)->pluck('id')->toArray();
        //$single = DB::table('user_docs_categories')->whereIn('company_id', ['1', Auth::user()->company_id])->where('multiple', 0)->pluck('id')->toArray();
        foreach (UserDocTypes::all() as $doc_type => $doc_name) {

            //******
            //  Have suspended the need to have user doc public/private permissions to allow users to upload their own documents.
            //******

            // Public Docs
            //if ($this->hasPermission2("$action.docs.$doc_type.pub") || $this->hasPermission2("$action.docs.$doc_type.pri")) {
            foreach (UserDocTypes::docs($doc_type, 0)->pluck('name', 'id')->toArray() as $id => $name) {
                if (!($action == 'add' && in_array($id, $single) && $user->activeUserDoc($id)))
                    $array[$id] = $name;
            }
            //}
            // Private Docs
            //if ($this->hasPermission2("$action.docs.$doc_type.pri")) {
            foreach (UserDocTypes::docs($doc_type, 1)->pluck('name', 'id')->toArray() as $id => $name) {
                if (!($action == 'add' && in_array($id, $single) && $user->activeUserDoc($id)))
                    $array[$id] = $name;
            }
            //}
        }

        asort($array);

        if ($prompt == 'all')
            return ($prompt && count($array) > 1) ? $array = array('ALL' => 'All categories') + $array : $array;

        return ($prompt && count($array) > 1) ? $array = array('' => 'Select Type') + $array : $array;
    }

    /**
     * Dropdown Option for Contractor Licence
     *
     * @return string
     */
    public function contractorLicenceOptions()
    {
        $doc = UserDoc::where('category_id', 3)->where('user_id', $this->id)->where('status', '>', '0')->first();
        if ($doc)
            return ContractorLicence::find(1)->classOptions(explode(',', $doc->ref_type));

        return ContractorLicence::find(1)->classOptions();
    }

    /**
     * Contractor Licence Class SBC
     *
     * @return string
     */
    public function contractorLicenceSBC()
    {
        $str = '';
        $doc = UserDoc::where('category_id', 3)->where('user_id', $this->id)->where('status', '>', '0')->first();
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
     * Supervisor Licence Class SBC
     *
     * @return string
     */
    public function supervisorLicenceSBC()
    {
        $str = '';
        $doc = UserDoc::where('category_id', 4)->where('user_id', $this->id)->where('status', '>', '0')->first();
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
     * A dropdown of Driver Class Options.
     *
     * @return string
     */
    public function driversLicenceOptions($selected = [])
    {
        $str = '';
        $classes = [
            'C'  => 'Car', 'C-A' => 'Car (automatic only)', 'R' => 'Rider', 'RE' => 'Restricted Rider', 'LR' => 'Light Rigid',
            'MR' => 'Medium Rigid', 'HR' => 'Heavy Rigid', 'HC' => 'Heavy Combination', 'MC' => 'Multi-Combination'
        ];
        foreach ($classes as $class => $name) {
            $sel = (in_array($class, $selected)) ? 'selected' : '';
            $str .= "<option value='$class' $sel>$name</option>";
        }

        return $str;
    }

    /**
     * Determine if a certain document type is Required
     *
     * @return boolean
     */
    public function requiresUserDoc($type, $system = false)
    {
        // Doc types
        // 1  WC - White Card
        // 3  CL - Contractors Licence
        // 4  SL - Supervisors Licence
        // 8  AP - Aprentice Document
        // 9  AS - Asbestos Removal Training
        // 10 HI - High Risk Work Licence
        //
        // Categories                            | WC  | CL  | SL  | AP  |
        // 0  Unallocated / Offsite              |_____|_____|_____|_____|
        // 1  On Site Non-Trade                  |__X__|_____|_____|_____|
        // 2  On Site Trade (Sole Trader)        |__X__|__X__|_____|_____|
        // 3  On Site Trade (Partner/Company)    |__X__|_____|_____|_____|
        // 4  On Site Apprentice                 |__X__|_____|_____|_____|

        // If System == False then check for any Compliance Overrides
        if (!$system) {
            $override = ComplianceOverride::where('type', "ud$type")->where('user_id', $this->id)->where('status', 1)->first();
            if ($override)
                return ($override->required) ? true : false;
        }

        // White Card
        if ($type == 1 && $this->onsite) return true;  // If you Onsite MUST have a WC (White Card)

        // Contractor Licence
        if (in_array($this->company->business_entity, ['3', 'Sole Trader'])) {
            if ($type == 3) return true; // Contractor Licence Required for ALL Sole Traders
        } else {
            // Contractor or Supervisor Licence Required if user is nominated by own company as licence holder/supervisor of their CL
            // - current users not making users non-compliant if they don't have CL / Super because company nominated them - just a notification
            //if (in_array($type, [3, 4]) && $this->company->activeCompanyDoc(7) && $this->company->activeCompanyDoc(7)->status == 1
            //    && $this->requiredContractorLicences()
            //) return true;
        }

        return false;
    }

    /**
     * Determine if user is nominated as Supervisor for Company Contractor Licence
     *
     * @return boolean
     */
    public function requiredContractorLicences()
    {
        $required_cl = ContractorLicenceSupervisor::where('user_id', $this->id)->pluck('licence_id')->toArray();
        if ($required_cl) {
            $array = [];
            foreach ($required_cl as $cl)
                $array[$cl] = ContractorLicence::find($cl)->name;

            return $array;
        }

        return [];
    }

    /**
     * Determine if user is nominated as Supervisor for Company Contractor Licence
     *
     * @return boolean
     */
    public function requiredContractorLicencesSBC()
    {
        $required_cl = ContractorLicenceSupervisor::where('user_id', $this->id)->pluck('licence_id')->toArray();
        if ($required_cl) {
            $string = '';
            foreach ($required_cl as $cl)
                $string .= ContractorLicence::find($cl)->name . ', ';

            return rtrim($string, ', ');
        }

        return '';
    }


    /**
     * Determine if a certain document type is Required
     *
     * @return string
     */
    public function requiresUserDocText($type)
    {
        return ($this->requiresUserDoc($type) ? '<span class="font-red">Required</span>' : '');
    }

    /**
     * Determine if User is compliant ie. has required docs.
     *
     * @return booleen
     */
    public function isCompliant()
    {
        $doc_types = [1, 3, 4];
        foreach ($doc_types as $type) {
            if ($this->requiresUserDoc($type) && (!$this->activeUserDoc($type) || $this->activeUserDoc($type)->status != 1))
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
        $doc_types = [1 => 'White Card', 3 => 'Contractor Licence', 4 => 'Supervisor Licence'];
        $compliant_docs = [];
        $compliant_html = '';

        foreach ($doc_types as $type => $name) {
            if ($this->requiresUserDoc($type)) {
                $compliant_docs[$type] = $name;
                $compliant_html .= "$name, ";
            }
        }

        $compliant_html = rtrim($compliant_html, ', ');

        return ($format == 'csv') ? $compliant_html : $compliant_docs;

    }

    /**
     * Documents user has but aren't required to be compliant
     *
     * @return Text or Array
     */
    public function nonCompliantDocs($format = 'array', $status = '')
    {
        $compliant_docs = $this->compliantDocs();
        $non_compliant_docs = [];
        $non_compliant_html = '';

        foreach ($this->userDocs() as $doc) {
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
     * Missing Company Documents to be compliant
     *
     * @return Text or Array
     */
    public function missingDocs($format = 'array')
    {
        $doc_types = [1 => 'White Card', 3 => 'Contractor Licence', 4 => 'Supervisor Licence'];
        $missing_docs = [];
        $missing_html = '';

        foreach ($doc_types as $type => $name) {
            if ($this->requiresUserDoc($type) && (!$this->activeUserDoc($type) || $this->activeUserDoc($type)->status != 1)) {
                $missing_docs[$type] = $name;
                if ($this->activeUserDoc($type)) {
                    $missing_status = ($this->activeUserDoc($type)->status == 3) ? 'label-warning' : 'label-danger';
                    $missing_html .= "<span class='label label-sm $missing_status'>$name</span>, ";
                } else
                    $missing_html .= "$name, ";
            }

        }

        $missing_html = rtrim($missing_html, ', ');

        return ($format == 'csv') ? $missing_html : $missing_docs;
    }

    /**
     * List of Compliance overrides set for this user
     */
    public function complianceOverrides()
    {
        return ComplianceOverride::where('user_id', $this->id)->where('status', 1)->get();
    }



    /****************************
     *
     * Company Docs
     *
     ***************************/

    /**
     * A dropdown list of types of Company Document Types user can access
     *
     * @return array
     */
    public function companyDocTypeAllowed($action)
    {
        $array = [];
        foreach (CompanyDocTypes::all() as $doc_type => $doc_name) {
            // Public Docs
            if ($this->hasPermission2("$action.docs.$doc_type.pub") || $this->hasPermission2("$action.docs.$doc_type.pri")) {
                foreach (CompanyDocTypes::docsAll($doc_type, 0)->pluck('name', 'id')->toArray() as $id => $name)
                    $array[] = $id;
            }
            // Private Docs
            if ($this->hasPermission2("$action.docs.$doc_type.pri")) {
                foreach (CompanyDocTypes::docsAll($doc_type, 1)->pluck('name', 'id')->toArray() as $id => $name)
                    $array[] = $id;
            }
        }
        return $array;
    }


    /**
     * A dropdown list of types of Company Document Departments user can access
     *
     * @return array
     */
    public function companyDocDeptSelect($action, $company, $prompt = '')
    {
        $array = [];
        foreach (CompanyDocTypes::all() as $doc_type => $doc_name) {
            // Public Docs
            if ($this->hasPermission2("$action.docs.$doc_type.pub"))
                $array[$doc_type] = $doc_name;
            // Private Docs
            if ($this->hasPermission2("$action.docs.$doc_type.pri"))
                $array[$doc_type] = $doc_name;
        }

        asort($array);

        if ($prompt == 'all')
            return ($prompt && count($array) > 1) ? $array = array('all' => 'All departments') + $array : $array;

        return ($prompt && count($array) > 1) ? $array = array('' => 'Select Type') + $array : $array;
    }

    /**
     * A dropdown list of types of Company Document Types user can access
     *
     * @return array
     */
    public function companyDocTypeSelect($action, $company, $prompt = '')
    {
        $array = [];
        $array_ss_ptc = [];
        $single = DB::table('company_docs_categories')->whereIn('company_id', ['1', Auth::user()->company_id])->where('multiple', 0)->pluck('id')->toArray();
        foreach (CompanyDocTypes::all() as $doc_type => $doc_name) {
            // Public Docs
            if ($this->hasPermission2("$action.docs.$doc_type.pub") || $this->hasPermission2("$action.docs.$doc_type.pri")) {
                foreach (CompanyDocTypes::docs($doc_type, 0)->pluck('name', 'id')->toArray() as $id => $name) {
                    if (!($action == 'add' && in_array($id, $single) && $company->activeCompanyDoc($id)))
                        $array[$id] = $name;
                    if (!($action == 'add' && in_array($id, $single) && $company->activeCompanyDoc($id)) && !in_array($id, [4, 5]))
                        $array_ss_ptc[$id] = $name;
                }
            }
            // Private Docs
            if ($this->hasPermission2("$action.docs.$doc_type.pri")) {
                foreach (CompanyDocTypes::docs($doc_type, 1)->pluck('name', 'id')->toArray() as $id => $name) {
                    if (!($action == 'add' && in_array($id, $single) && $company->activeCompanyDoc($id)))
                        $array[$id] = $name;
                    if (!($action == 'add' && in_array($id, $single) && $company->activeCompanyDoc($id)) && !in_array($id, [4, 5]))
                        $array_ss_ptc[$id] = $name;
                }
            }
        }

        asort($array);
        asort($array_ss_ptc);

        if ($prompt == 'all')
            return (count($array) > 1) ? $array = array('ALL' => 'All categories') + $array : $array;
        if ($prompt == '-SS-PTC')
            return (count($array_ss_ptc) > 1) ? $array_ss_ptc = array('' => 'Select Type') + $array_ss_ptc : $array_ss_ptc;

        return ($prompt && count($array) > 1) ? $array = array('' => 'Select Type') + $array : $array;
    }

}