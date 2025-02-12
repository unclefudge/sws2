<?php

namespace App\Http\Utilities;

class OverrideTypes
{

    protected static $ComplianceOverrideTypes = [
        'cdu' => 'Upload Company Documents on their behalf',
        'cd1' => 'Public Liabilty',
        'cd2' => 'Workers Compensation',
        'cd3' => 'Sickness & Accident Insurance',
        'cd6' => 'Electrical Test & Tagging',
        'cd7' => 'Contractor Licence',
        'cd8' => 'Safe Work Method Statements',
        'ud1' => 'White Card',
    ];


    /**
     * @return array
     */
    public static function all()
    {
        return static::$ComplianceOverrideTypes;
    }

    /**
     * @return array
     */
    public static function companySelect()
    {
        $array = ['' => 'Select override'];
        foreach (static::$ComplianceOverrideTypes as $key => $val) {
            if ($key[0] == 'c')
                $array[$key] = $val;
        }
        return $array;
    }

    /**
     * @return array
     */
    public static function userSelect()
    {
        $array = ['' => 'Select override'];
        foreach (static::$ComplianceOverrideTypes as $key => $val) {
            if ($key[0] == 'u')
                $array[$key] = $val;
        }
        return $array;
    }

    /**
     * @return string
     */
    public static function name($id)
    {
        return ($id != null) ? static::$ComplianceOverrideTypes[$id] : '';
    }
}