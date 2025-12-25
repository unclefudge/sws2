<?php

namespace App\Http\Controllers\Misc;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


/**
 * Class FileUploadController
 * @package App\Http\Controllers
 */
class FileController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if (!(Auth::user()->hasAnyPermission2('add.site.doc|edit.site.doc|del.site.doc|add.safety.doc|edit.safety.doc|del.safety.doc')))
            return view('errors/404');

        $site_id = $type = '';

        return view('manage/file/index', compact('site_id', 'type'));
    }
}
