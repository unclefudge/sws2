<?php

namespace App\Models\Misc;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class DesignerPostcode extends Model
{
    protected $table = 'designer_postcodes';
    protected $fillable = ['postcode', 'suburb', 'state', 'active',];
    protected $casts = ['active' => 'boolean',];

    public function setPostcodeAttribute($value): void
    {
        $this->attributes['postcode'] = preg_replace('/\D+/', '', (string)$value);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('active', true);
    }
}