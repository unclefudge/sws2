<?php

namespace App\Models\Site;

use Illuminate\Database\Eloquent\Model;
use Mail;
use URL;


class SiteNoteCost extends Model
{

    protected $table = 'site_notes_costs';
    protected $fillable = ['note_id', 'cost_id', 'details', 'notes'];

    /**
     * A SiteNoteCost belongs to a SiteNote
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function note()
    {
        return $this->belongsTo('App\Models\Site\SiteNote');
    }

    /**
     * A SiteNoteCost belongs to a Category
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function category()
    {
        return $this->belongsTo('App\Models\Misc\Category', 'cost_id');
    }

    /**
     * Get the owner of record   (getter)
     *
     * @return string;
     */
    public function getCategoryNameAttribute()
    {
        return $this->category->name;
    }
}