<?php

namespace App\Models\Misc\Equipment;

use App\Services\FileBank;
use App\User;
use DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Equipment extends Model
{

    protected $table = 'equipment';
    protected $fillable = ['category_id', 'name', 'length', 'min_stock', 'purchased_last', 'purchased', 'disposed', 'attachment', 'status', 'company_id', 'created_by', 'created_at', 'updated_at', 'updated_by'];
    protected $casts = ['purchased_last' => 'datetime'];

    /**
     * A Equipment belongs to a category.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongTo
     */

    public function category()
    {
        return $this->belongsTo('App\Models\Misc\Equipment\EquipmentCategory');
    }

    /**
     * A Equipment has many LocationItems.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function locationItems()
    {
        return $this->hasMany('App\Models\Misc\Equipment\EquipmentLocationItem');
    }

    /**
     * A Equipment has many Locations.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function locations()
    {
        $ids = EquipmentLocationItem::where('equipment_id', $this->id)->pluck('location_id')->toArray();

        return EquipmentLocation::whereIn('id', $ids)->where('status', 1)->get();
    }

    /**
     * A Equipment has many logs.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */

    public function log()
    {
        return $this->hasMany('App\Models\Misc\Equipment\EquipmentLog');
    }

    /**
     * A Equipment has many logs.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */

    public function lost()
    {
        return $this->hasMany('App\Models\Misc\Equipment\EquipmentLost');
    }

    /**
     * A list of location of the items separated by ,
     *
     * @return string
     */
    public function locationsSBC()
    {
        $string = '';
        foreach ($this->locationItems as $item) {
            $string .= ($item->location->site_id) ? $item->location->site->name . ', ' : $item->location->other;
        }

        return rtrim($string, ', ');
    }

    /**
     * Get Parent Category  (getter)
     */
    public function getParentCategoryAttribute()
    {
        return (!$this->category->parent) ? $this->category_id : $this->category->parent;
    }

    /**
     * Get the Item Total  (getter)
     */
    public function getTotalAttribute()
    {
        return DB::table('equipment_location_items')->join('equipment_location', 'equipment_location.id', '=', 'equipment_location_items.location_id')
            ->where('equipment_id', $this->id)->where('equipment_location.status', 1)
            ->where(function ($query) {
                $query->where('equipment_location.other', null);
                $query->orWhere('equipment_location.other', 'NOT LIKE', '%Transfer in progress:%');
            })->sum('qty');
    }

    /**
     * Get the # Missing  (getter)
     */
    public function getTotalLostAttribute()
    {
        return DB::table('equipment_lost')->where('equipment_id', $this->id)->sum('qty');
    }

    /**
     * Get the # Missing  (getter)
     */
    public function getTotalExcessAttribute()
    {
        return $this->total - $this->purchased + $this->disposed + $this->total_lost;
    }
    
    public function getAttachmentUrlAttribute(): string
    {
        if (!$this->attachment) return '';
        $path = "equipment/{$this->attachment}";

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
                $table->company_id = 3; //Auth::user()->company_id;
            });

            // create a event to happen on updating
            static::updating(function ($table) {
                $table->updated_by = Auth::user()->id;
            });
        }
    }
}