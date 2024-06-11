<?php

namespace App\Models\Support;

use Illuminate\Database\Eloquent\Model;
use Mail;

class SupportHour extends Model
{

    protected $table = 'support_hours';
    protected $fillable = ['day', 'h9_11', 'h11_1', 'h1_3', 'h3_5', 'order', 'notes', 'created_at', 'updated_at'];


    public function dayShort($day)
    {
        if ($day == "Monday") return 'Mon';
        if ($day == "Tuesday") return 'Tue';
        if ($day == "Wednesday") return 'Wed';
        if ($day == "Thursday") return 'Thu';
        if ($day == "Friday") return 'Fri';

    }

    public function statusColour($hour)
    {
        if ($hour == 1) return '#e26a6a';  // Unavailable
        if ($hour == 2) return '#f4d03f';  // Available
        if ($hour == 3) return '#36d7b7';  // Working
        return '#e9edef';

    }

    public function statusText($hour)
    {
        if ($hour == 1) return 'Busy';
        if ($hour == 2) return 'Available';
        if ($hour == 3) return 'Working';
        return '';

    }
}