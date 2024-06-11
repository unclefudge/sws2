<?php

namespace App\Http\Controllers\Support;

use App\Http\Controllers\Controller;
use App\Models\Support\SupportHour;
use DB;
use Mail;
use Session;
use Validator;

/**
 * Class SupportTicketController
 * @package App\Http\Controllers
 */
class SupportHourController extends Controller
{

    public function index()
    {
        $hours = SupportHour::all()->sortBy('order');

        return view('support/hour/list', compact('hours'));
    }

    public function updateHours()
    {
        $hours = SupportHour::all()->sortBy('order');

        return view('support/hour/edit', compact('hours'));
    }

    public function show($id)
    {
        //
    }

    public function update($id)
    {
        $hour = SupportHour::find($id);
        $hour->update(request()->all());

        return $hour;
    }


    public function getHours()
    {
        $hours = SupportHour::all()->sortBy('order');
        $items = [];
        foreach ($hours as $hour) {
            $array = [];
            $array['id'] = $hour->id;
            $array['day'] = $hour->day;
            $array['h9'] = $hour->h9_11;
            $array['h11'] = $hour->h11_1;
            $array['h1'] = $hour->h1_3;
            $array['h3'] = $hour->h3_5;
            $array['order'] = $hour->order;
            $array['notes'] = $hour->notes;
            $items[] = $array;
        };
        $json = [];
        $json[] = $items;

        return $json;
    }
}
