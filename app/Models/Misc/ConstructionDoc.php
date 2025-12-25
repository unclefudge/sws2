<?php

namespace App\Models\Misc;

use App\Services\FileBank;
use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class ConstructionDoc extends Model
{
    protected $table = 'construction_docs';
    protected $fillable = [
        'type', 'category_id', 'name', 'attachment', 'expiry',
        'reference', 'version', 'approved_by', 'approved_at',
        'notes', 'status', 'created_by', 'updated_by'];
    protected $casts = ['expiry' => 'datetime', 'approved_at' => 'datetime'];


    /**
     * A Report belongs to a Category.  (sometimes - Some Reports do)
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function category()
    {
        return $this->belongsTo('App\Models\Misc\Category', 'category_id');
    }

    public function getAttachmentUrlAttribute(): string
    {
        if (!$this->attachment) return '';
        $path = "construction/doc/standards/{$this->attachment}";

        return FileBank::exists($path) ? FileBank::url($path) : '';
    }


    /**
     * Display records last update_by + date
     *
     * @return string
     */
    public function displayUpdatedBy()
    {
        $user = User::findOrFail($this->updated_by);
        return '<span style="font-weight: 400">Last modified: </span>' . $this->updated_at->diffForHumans() . ' &nbsp; ' .
            '<span style="font-weight: 400">By:</span> ' . $user->fullname;
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

