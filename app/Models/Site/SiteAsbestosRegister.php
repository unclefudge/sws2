<?php

namespace App\Models\Site;

use App\Services\FileBank;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Mail;
use URL;

class SiteAsbestosRegister extends Model
{

    protected $table = 'site_asbestos_register';
    protected $fillable = [
        'site_id', 'version', 'approved_by', 'approved_at', 'attachment', 'notes', 'status',
        'created_by', 'updated_by', 'created_at', 'updated_at',
    ];
    protected $casts = ['approved_at' => 'datetime'];

    /**
     * A SiteAsbestosRegister belongs to a site
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function site()
    {
        return $this->belongsTo('App\Models\Site\Site');
    }


    /**
     * A SiteAsbestosRegister has many Items
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function items()
    {
        return $this->hasMany('App\Models\Site\SiteAsbestosRegisterItem', 'register_id');
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


    public function getAttachmentUrlAttribute(): string
    {
        if (!$this->attachment)
            return '';

        return FileBank::url("site/{$this->site_id}/docs/{$this->attachment}");
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