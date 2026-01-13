<?php

namespace App\Models\Misc;

use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    protected $table = 'reports';
    protected $fillable = ['user_id', 'company_id', 'batch_id', 'name', 'type', 'status', 'disk', 'path', 'error',];

}

