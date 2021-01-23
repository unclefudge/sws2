<?php

namespace App\Http\Utilities;

use DB;
use Auth;

class CompanyDocTypes {

    protected static $companyDocTypes = [
        'acc' => 'Accounting',
        'adm' => 'Administration',
        'con' => 'Construction',
        'whs' => 'WHS',
    ];


    /**
     * @return array
     */
    public static function all()
    {
        return static::$companyDocTypes;
    }

    /**
     * @return string
     */
    public static function name($id)
    {
        return static::$companyDocTypes[$id];
    }

    /**
     * @return array
     */
    public static function docs($type, $private = 0)
    {
        if (Auth::check())
            return DB::table('company_docs_categories')->whereIn('company_id', ['1', Auth::user()->company_id])->where('type', $type)->where('private', $private)->where('parent', 0)->where('status', 1)->get();

        return DB::table('company_docs_categories')->where('type', $type)->where('private', $private)->where('parent', 0)->where('status', 1)->get();
    }

    /**
     * @return array
     */
    public static function docsAll($type, $private = 0)
    {
        if (Auth::check())
            return DB::table('company_docs_categories')->whereIn('company_id', ['1', Auth::user()->company_id])->where('type', $type)->where('private', $private)->where('status', 1)->get();

        return DB::table('company_docs_categories')->where('type', $type)->where('private', $private)->where('status', 1)->get();
    }

    /**
     * @return array
     */
    public static function docCats($type, $private = 0)
    {
        $ids = [];
        if (Auth::check())
            $docs = DB::table('company_docs_categories')->whereIn('company_id', ['1', Auth::user()->company_id])->where('type', $type)->where('private', $private)->where('parent', 0)->where('status', 1)->get();
        else
            $docs = DB::table('company_docs_categories')->where('type', $type)->where('private', $private)->where('parent', 0)->where('status', 1)->get();

        foreach ($docs as $doc) {
            $ids[] = $doc->id;
        }

        return $ids;
    }

    /**
     * @return array
     */
    public static function docNames($type, $private = 0)
    {
        $names = '';
        if (Auth::check())
            $docs = DB::table('company_docs_categories')->whereIn('company_id', ['1', Auth::user()->company_id])->where('type', $type)->where('private', $private)->get();
        else
            $docs = DB::table('company_docs_categories')->where('type', $type)->where('private', $private)->get();
        foreach ($docs as $doc) {
            $names .= "$doc->name, ";
        }

        return rtrim($names, ', ');
    }
}