<?php

namespace App\Models\Ccc;

use DB;
use Mail;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;

class Youth extends Model {

    protected $table = 'zccc_youth';
    protected $fillable = ['name', 'dob', 'address', 'parent','phone', 'email', 'pickup', 'medical',
        'consent_photo', 'consent_movie', 'consent_medicine', 'leave_unsupervised', 'notes'];

    protected $dates = ['dob'];

    /**
     * A Youth attends many Programs.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function programs()
    {
        return $this->belongsToMany('App\Models\Ccc\Program', 'zccc_programs_youth', 'program_id', 'youth_id');
    }

    /**
     *  Add Youth to a Program
     */
    public function add2program($program, $status) {
        DB::table('zccc_programs_youth')->insert(['youth_id' => $this->id, 'program_id' => $program->id, 'status' => $status]);
    }

    /**
     *  Youth sorted by Last Name
     */
    public function sortedBylast() {

    }

    /**
     * Set the name + create slug attributes  (mutator)
     */
    public function setNameAttribute($value)
    {
        $this->attributes['name'] = ucwords(strtolower($value));
        $this->attributes['name'] = preg_replace_callback('/([.!?])\s*(\w)/', function ($matches) {
            return strtoupper($matches[1] . $matches[2]);
        }, ucfirst($this->attributes['name']));
    }

    /**
     * Set the phone number to AU format  (mutator)
     */
    public function setPhoneAttribute($value)
    {
        $this->attributes['phone'] = format_phone('au', $value);
    }

    /**
     * Set the address to set format (mutator)
     */
    public function setAddressAttribute($value)
    {
        $this->attributes['address'] = trim(ucwords(strtolower($value)));
        $this->attributes['address'] = preg_replace('/Po Box/', 'PO BOX', $this->attributes['address']);
    }
}

