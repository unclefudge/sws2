<?php

namespace App\Models\Misc;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class TemporaryFile extends Model {

    protected $table = 'temporary_files';
    protected $fillable = [
        'folder', 'filename', 'company_id', 'created_by'];


    /**
     * The "booting" method of the model.
     *
     * Overrides parent function
     *
     * @return void
     */
    public static function boot() {
        parent::boot();

        if(Auth::check()) {
            // create a event to happen on creating
            static::creating(function ($table) {
                $table->created_by = Auth::user()->id;
            });
        }
    }

}

