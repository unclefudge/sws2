<?php

namespace App\Models\Site;

use URL;
use Mail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;


class SiteProjectSupplyProduct extends Model {

    protected $table = 'project_supply_products';
    protected $fillable = [
        'name', 'supplier', 'type', 'colour', 'notes', 'status',
        'created_by', 'updated_by', 'created_at', 'updated_at',
    ];


    /*
     * Supplier Options
    */
    public function supplyOptions($field)
    {
        $array = [];
        $options = [];

        if ($field == 'supplier') $options = explode("\n", $this->supplier);
        if ($field == 'type') $options = explode("\n", $this->type);
        if ($field == 'colour') $options = explode("\n", $this->colour);

        foreach ($options as $opt) {
            if ($opt)
                $array[$opt] = $opt;
        }

        if (count($array)) {
            $array = array('' => 'Select option') + $array;
            $array = $array + array('other' => 'Other'); //, 'n/a' => 'N/A');
        }

        return $array;
    }

    /**
     * The "booting" method of the model.
     *
     * Overrides parent function
     *
     * @return void
     */
    public static function boot()
    {
        parent::boot();

        if (Auth::check()) {
            // create a event to happen on creating
            static::creating(function ($table) {
                $table->created_by = Auth::user()->id;
                $table->updated_by = Auth::user()->id;
            });

            // create a event to happen on updating
            static::updating(function ($table) {
                $table->updated_by = Auth::user()->id;
            });
        }
    }
}