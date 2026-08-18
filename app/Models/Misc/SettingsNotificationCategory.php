<?php

namespace App\Models\Misc;


use DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class SettingsNotificationCategory extends Model
{

    protected $table = 'settings_notifications_categories';
    protected $fillable = ['type', 'sort_order', 'system', 'slug', 'name', 'title', 'body', 'brief', 'notes',
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
    public function notificationSelect(bool $manageReport = false, bool $canMoveUp = false, bool $canMoveDown = false)
    {
        $opacity = ($this->status) ? '' : 'style="opacity: 0.5"';
        $str = '<input type="hidden" name="notification_present[]" value="' . $this->id . '">';
        $str .= '<div class="row"><div class="col-md-11" ' . $opacity . '><div class="form-group"><div class="col-md-3">';
        $str .= '<label for="type1" class="control-label">' . $this->name;
        if ($this->title) {
            $str .= ' <a href="javascript:;" class="popovers" data-container="body" data-trigger="hover" data-original-title="' . $this->title . '" data-content="' . $this->body . '"><i class="fa fa-question-circle font-grey-silver"></i></a>';
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
        $str .= '</div></div></div>';

        // Status / report management actions
        $newStatus = ($this->status) ? 0 : 1;
        $newStatusName = ($this->status) ? '<i class="fa fa-bell font-green"></i>' : '<i class="fa fa-bell-slash font-red"></i>';

        $str .= '<div class="col-md-1 text-center" style="margin-top:5px; white-space:nowrap">';
        $str .= '<a href="/settings/notifications/' . $this->id . '/status/' . $newStatus . '" title="' . ($this->status ? 'Disable' : 'Enable') . '">' . $newStatusName . '</a>';

        if ($manageReport) {
            if ($canMoveUp)
                $str .= ' <a href="javascript:;" class="report-move" data-id="' . $this->id . '" data-direction="up" title="Move up"><i class="fa fa-arrow-up font-grey-silver"></i></a>';

            if ($canMoveDown)
                $str .= ' <a href="javascript:;" class="report-move" data-id="' . $this->id . '" data-direction="down" title="Move down"><i class="fa fa-arrow-down font-grey-silver"></i></a>';

            if ($this->system)
                $str .= ' <span title="Used by SafeWorkSite code - disable rather than delete"><i class="fa fa-lock font-grey-silver"></i></span>';
            else
                $str .= ' <a href="javascript:;" class="report-delete" data-id="' . $this->id . '" data-name="' . htmlspecialchars($this->name, ENT_QUOTES, 'UTF-8') . '" title="Delete"><i class="fa fa-trash font-red"></i></a>';
        }

        $str .= '</div></div><br>';

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

