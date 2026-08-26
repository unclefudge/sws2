<?php

namespace App\Models\Scheduled;

use Illuminate\Database\Eloquent\Model;

class ScheduledOperationCategory extends Model
{
    protected $fillable = ['slug', 'name', 'sort_order', 'enabled'];

    protected $casts = [
        'sort_order' => 'integer',
        'enabled' => 'boolean',
    ];
}
