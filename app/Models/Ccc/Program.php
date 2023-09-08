<?php

namespace App\Models\Ccc;

use App\Models\Ccc\Youth;
use DB;
use Mail;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;

class Program extends Model {

    protected $table = 'zccc_programs';
    protected $fillable = ['name', 'date', 'cost', 'max', 'pickups', 'brief', 'notes'];

    protected $dates = ['date'];

    /**
     * A Progam has many youth.
     */
    public function youth()
    {
        return $this->belongsToMany('App\Models\Ccc\Youth', 'zccc_programs_youth', 'program_id', 'youth_id');
    }

    /**
     * Youth Comfirmed
     */
    public function youthConfirmed()
    {
        $ids = [];
        foreach ($this->youth as $youth) {
            $confirmed = DB::table('zccc_programs_youth')->where('youth_id', $youth->id)->where('program_id', $this->id)->where('status', 0)->first();
            if ($confirmed)
                $ids[] = $youth->id;
        }

        return Youth::find($ids)->sortBy('name');
    }

    /**
     * Youth Waitlisted
     */
    public function youthWaitlist()
    {
        $ids = [];
        $waitlist = [];
        foreach ($this->youth as $youth) {
            $waiting = DB::table('zccc_programs_youth')->where('youth_id', $youth->id)->where('program_id', $this->id)->where('status', '>', 0)->first();
            if ($waiting)
                $ids[$waiting->status] = $youth->id;
        }

        ksort($ids);
        foreach ($ids as $pos => $id)
            $waitlist[] = Youth::findOrFail($id);

        return $waitlist;
    }

    /**
     * Youth Position (x)
     */
    public function youthPosition($pos)
    {
        $count = 0;
        foreach ($this->youthConfirmed() as $youth) {
            $count++;
            if ($count == $pos)
                return $youth;
        }

        foreach ($this->youthWaitlist() as $youth) {
            $count++;
            if ($count == $pos)
                return $youth;
        }

        return null;
    }

    /**
     * Pickups
     */
    public function pickups()
    {
        $locations = [];
        foreach ($this->youthConfirmed() as $youth) {
            if (!isset($locations[$youth->pickup]))
                $locations[$youth->pickup] = [$youth->name];
            else
                $locations[$youth->pickup][] = $youth->name;
        }

        return $locations;
    }
}

