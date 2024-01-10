<?php

namespace App\Models\Site\Planner;

use File;
use App\User;
use App\Models\Company\Company;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class SiteRoster extends Model
{

    protected $table = 'site_roster';
    protected $fillable = ['site_id', 'user_id', 'date', 'created_by', 'updated_by', 'created_at', 'updated_at'];
    protected $casts = ['date' => 'datetime'];


    /**
     * A SiteRoster belongs to a site
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function site()
    {
        return $this->belongsTo('App\Models\Site\Site');
    }

    /**
     * A SiteRoster belongs to a user
     *
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     */
    public function user()
    {
        return $this->belongsTo('App\User', 'user_id');
    }

    /**
     * Get the owner of record   (getter)
     *
     * @return string;
     */
    public function getOwnedByAttribute()
    {
        return $this->site->company;
    }

    static public function dailyUpdate()
    {
        $log = '';
        //echo "<h1>Adding New Entities to Roster</h1>";
        $log .= "Adding New Entities to Roster\n\n";

        $capecod = '3';
        $company = Company::findOrFail($capecod);
        $allowedSites = $company->sites('1')->pluck('id')->toArray();
        $allowedSites = Site::all()->pluck('id')->toArray();

        $date = Carbon::now()->format('Y-m-d');
        //$date = '2016-08-17';
        $planner = SitePlanner::select(['id', 'site_id', 'entity_type', 'entity_id', 'task_id', 'from', 'to', 'days'])
            ->where('from', '<=', $date)
            ->where('to', '>=', $date)
            ->whereIn('site_id', $allowedSites)
            ->orderBy('site_id')
            ->get();

        foreach ($planner as $plan) {
            if ($plan->entity_type == 'c') {
                $site = Site::find($plan->site_id);
                $company = Company::findOrFail($plan->entity_id);
                $staff = $company->staffStatus(1)->pluck('id')->toArray();
                $log .= "\nf:" . $plan->from->format('Y-m-d') . ' t:' . $plan->to->format('Y-m-d') . ' [' . $plan->id . '] (' . $company->name_alias . ") Task:$plan->task_id Site: $site->name ($plan->site_id)\n";
                foreach ($staff as $staff_id) {
                    $user = User::findOrFail($staff_id);
                    if (!$site->isUserOnRoster($staff_id, $date)) {
                        //echo 'adding '.$user->fullname.' ('.$user->username.') to roster<br>';
                        $log .= 'adding ' . $user->fullname . ' (' . $user->username . ") to roster\n";
                        $newRoster = SiteRoster::create(array(
                            'site_id' => $site->id,
                            'user_id' => $staff_id,
                            'date' => $date . ' 00:00:00',
                            'created_by' => '1',
                            'updated_by' => '1',
                        ));
                    }
                }
            }
        }

        //echo "<h1>Completed</h1>";
        $log .= "\nCompleted";

        $now = Carbon::now()->format('Y-m-d-G-i-s');
        $bytes_written = File::put(public_path('filebank/log/nightly/' . $now . '.txt'), $log);
        if ($bytes_written === false)
            die("Error writing to file");
        else
            echo 'Logfile filebank/log/nightly/' . $now . '.txt';

        echo $log;
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