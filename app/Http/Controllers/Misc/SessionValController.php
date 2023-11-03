<?php

namespace App\Http\Controllers\Misc;

use Illuminate\Http\Request;
use Validator;

use DB;
use Session;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use nilsenj\Toastr\Facades\Toastr;
use Carbon\Carbon;

/**
 * Class SessionValController
 * @package App\Http\Controllers
 */
class SessionValController extends Controller {

    public function updateVal($key, $val)
    {
        //echo "N:$key V:$val<br>";
        //Session::put($key, $val);
        session([$key => $val]);
    }

    public function update()
    {
        //echo "N:$key V:$val<br>";
        //Session::put($key, $val);
        if (request()->ajax()) {
            if (request()->has('key') && request()->has('val')) {
                session([request('key') => request('val')]);
                //app('log')->debug("k:".request('key')." v:".request('val'));
                return response()->json(['status' => 'ok'], 200);
            }
        }
        return response()->json(['error' => 'None Ajax']);
    }

    /**
     * Delete the specified resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {

    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store()
    {

    }
}
