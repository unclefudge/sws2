<?php

namespace App\Models\Misc;


use DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class SettingsNotificationCategory extends Model {

    protected $table = 'settings_notifications_categories';
    protected $fillable = ['type', 'slug', 'name', 'title', 'body', 'brief', 'notes',
        'status', 'company_id', 'created_by', 'updated_by', 'created_at', 'updated_at'];


    /**
     * A Company Notification Category belongs to a company
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function company()
    {
        return $this->belongsTo('App\Models\Company\Company');
    }

    /*
    * Create Select field for Notifications
     *
     * @return string
     */
    public function notificationSelect()
    {
        $str = '<div class="row"><div class="col-md-12"><div class="form-group"><div class="col-md-3">';
        $str .= '<label for="type1" class="control-label">' . $this->name;
        if ($this->title) {
            $str .= ' <a href="javascript:;" class="popovers" data-container="body" data-trigger="hover"
        data-original-title="' . $this->title . '" data-content="' . $this->body . '"><i class="fa fa-question-circle font-grey-silver"></i></a>';
        }
        $str .= '</label></div><div class="col-md-9">';

        // Select Options
        $options = '';
        $selected = Auth::user()->company->notificationsUsersTypeArray($this->id);
        foreach (Auth::user()->company->staffSelect() as $value => $text) {
            $options .= (in_array($value, $selected)) ? "<option value='$value' selected='selected'>$text</option>" : "<option value='$value'>$text</option>";
        }

        $str .= '<select class="form-control select2" name="type' . $this->id . '[]" width="100%" multiple>';
        $str .= $options;
        $str .= '</select>';
        if ($this->brief)
            $str .= '<span class="help-block">' . $this->brief . '</span>';
        $str .= '</div></div></div></div><br>';

        return $str;
    }

    /**
     * Get the owner of record  (getter)
     *
     * @return string;
     */
    public function getOwnedByAttribute()
    {
        return $this->company;
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
            });
        }
    }
}

