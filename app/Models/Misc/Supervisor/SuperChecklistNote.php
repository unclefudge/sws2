<?php

namespace App\Models\Misc\Supervisor;

use URL;
use Mail;
use App\User;
use App\Models\Misc\Supervisor\SuperChecklist;
use App\Models\Misc\Supervisor\SuperChecklistCategory;
use App\Models\Misc\Supervisor\SuperChecklistQuestion;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class SuperChecklistNote extends Model {

    protected $table = 'supervisor_checklist_notes';
    protected $fillable = ['checklist_id', 'info', 'attachment', 'status', 'created_by', 'created_at', 'updated_at', 'updated_by'];


    /*
     * A SuperChecklistNote belongs to a SuperChecklist
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function checklist()
    {
        return $this->belongsTo('App\Models\Misc\Supervisor\SuperChecklist', 'checklist_id');
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