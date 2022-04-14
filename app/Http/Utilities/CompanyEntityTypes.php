<?php

namespace App\Http\Utilities;

class CompanyEntityTypes {

    protected static $companyEntityTypes = [
        //''  => 'Select entity',
        '1' => 'Company',
        '2' => 'Partnership',
        '3' => 'Sole Trader',
        '5' => 'Sole Trader - with employees',
        '4' => 'Trading Trust',
    ];


    /**
     * @return array
     */
    public static function all()
    {
        return static::$companyEntityTypes;
    }

    /**
     * @return string
     */
    public static function name($id)
    {
        if (preg_match('/[0-9]/', $id))
            return static::$companyEntityTypes[$id];

        return $id;
    }
}