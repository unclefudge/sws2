<?php

namespace App\Models\Misc\Supervisor;

use URL;
use Mail;
use App\User;
use App\Models\Misc\Supervisor\SuperChecklist;
use App\Models\Misc\Supervisor\SuperChecklistQuestion;
use App\Models\Misc\Supervisor\SuperChecklistResponse;
use App\Models\Site\Site;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class SuperChecklistCategory extends Model {

    protected $table = 'supervisor_checklist_categories';
    protected $fillable = ['type', 'name', 'description', 'parent', 'order', 'status', 'created_at', 'updated_at'];


    /**
     * A SuperChecklistCategory has many questions
     *
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     */
    public function questions()
    {
        return $this->hasMany('App\Models\Misc\Supervisor\SuperChecklistQuestion', 'cat_id');
    }
}