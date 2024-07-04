<?php

namespace App\Models\Misc;

use Illuminate\Database\Eloquent\Model;

class ZohoSiteLog extends Model
{

    protected $table = 'zoho_sitelog';
    protected $fillable = ['site_id', 'user_id', 'user_name', 'action', 'qty', 'fields', 'old', 'new', 'log'];

    /**
     * A ZohoSiteLog belongs to a Site.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function site()
    {
        return $this->belongsTo('App\Models\Site\Site', 'site_id');
    }

    /**
     * A ZohoSiteLog belongs to a User.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('App\User', 'user_id');
    }
}

