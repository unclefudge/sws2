<?php

namespace App\Models\Site;

use URL;
use Mail;
use App\Models\Comms\Todo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;


class SiteExtensionSite extends Model {

    protected $table = 'site_extensions_sites';
    protected $fillable = ['extension_id', 'site_id', 'completion_date', 'reasons', 'days', 'notes', 'updated_by'];

    protected $dates = ['completion_date'];

    /**
     * A SiteExtensionSite belongs to a SiteExtension
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function extension()
    {
        return $this->belongsTo('App\Models\Site\SiteExtension');
    }

    /**
     * A SiteExtensionSite belongs to a Site
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function site()
    {
        return $this->belongsTo('App\Models\Site\Site');
    }

    /**
     * TotalExtensionDays
     */
    public function totalExtensionDays()
    {
        $days = 0;
        $past_extensions = SiteExtensionSite::where('site_id', $this->site_id)->where('days', '>', 0)->get();
        foreach ($past_extensions as $site_ext) {
            $days = $days + $site_ext->days;
        }
        return $days;
    }

    /**
     * PastExtensions
     */
    public function pastExtensions()
    {
        $text = '';
        $extend_reasons = SiteExtensionCategory::where('status', 1)->orderBy('order')->pluck('name', 'id')->toArray();
        $past_extensions = SiteExtensionSite::where('site_id', $this->site_id)->where('days', '>', 0)->orderBy('created_at')->get();
        foreach ($past_extensions as $site_ext) {
            $day = ($site_ext->days == 1) ? 'day' : 'days';
            $text .= $site_ext->updated_at->format('d/m/y') . " - " . $site_ext->days . " $day <b>" . $site_ext->reasonsSBC() . ":</b> $site_ext->notes<br>";
        }
        return $text;
    }

    /**
     * SiteExtensionSite Reasons Text
     */
    public function reasonsSBC()
    {
        $text = '';
        $reasons_array = explode(',', $this->reasons);
        foreach ($reasons_array as $cat_id) {
            $cat = SiteExtensionCategory::find($cat_id);
            if ($cat)
                $text .= "$cat->name, ";
        }
        return rtrim($text, ', ');
    }

    /**
     * SiteExtensionSite Reasons Array
     */
    public function reasonsArray()
    {
        return ($this->reasons) ? explode(',', $this->reasons) : [];
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
                $table->updated_by = Auth::user()->id;
            });

            // create a event to happen on updating
            static::updating(function ($table) {
                $table->updated_by = Auth::user()->id;
            });
        }
    }


}