<?php

namespace App\Http\Controllers\Safety;

use App\Http\Controllers\Controller;
use App\Models\Safety\SafetyDataSheet;
use App\Models\Safety\SafetyDoc;
use App\Models\Safety\SafetyDocCategory;
use App\Services\FileBank;
use Carbon\Carbon;
use DB;
use Illuminate\Support\Facades\Auth;
use nilsenj\Toastr\Facades\Toastr;
use Session;
use Validator;
use Yajra\Datatables\Datatables;

/**
 * Class SdsController
 * @package App\Http\Controllers\Safety
 */
class SdsController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Check authorisation and throw 404 if not
        if (!Auth::user()->hasAnyPermissionType('sds'))
            return view('errors/404');

        $category_id = '';

        return view('safety/doc/sds/list', compact('category_id'));
    }

    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('add.sds'))
            return view('errors/404');

        $category_id = request('category_id');

        return view('safety/doc/sds/create', compact('category_id'));
    }

    /**
     * Edit the specified resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $sds = SafetyDataSheet::findOrFail($id);

        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('edit.sds', $sds))
            return view('errors/404');

        return view('safety/doc/sds/edit', compact('sds'));
    }

    /**
     * Delete the specified resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $sds = SafetyDataSheet::findOrFail($id);

        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('del.sds', $sds))
            return json_encode("failed");

        // Delete attached file
        FileBank::delete("sds/$sds->attachment");
        $sds->delete();

        return json_encode('success');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store()
    {
        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('add.sds'))
            return view('errors/404');

        $rules = ['name' => 'required', 'categories' => 'required', 'singlefile' => 'required'];
        $mesg = [
            'name.required' => 'The name field is required.',
            'categories.required' => 'The category field is required.',
            'singlefile.required' => 'The file field is required.',
        ];
        request()->validate($rules, $mesg); // Validate

        // Verify date
        if (request('date')) {
            if (preg_match("/(\d{2})\/(\d{2})\/(\d{4})$/", request('date'), $matches)) {
                list($dd, $mm, $yyyy) = explode('/', request('date'));
                if (!checkdate($mm, $dd, $yyyy))
                    return back()->withErrors(['date' => "Invalid date. Required format dd/mm/yyyy"]);
            } else
                return back()->withErrors(['date' => "Invalid date. Required format dd/mm/yyyy"]);
        }

        //dd(request()->all());
        $sds_request = request()->all();
        $sds_request['company_id'] = Auth::user()->company_id;
        $sds_request['date'] = (request('date')) ? Carbon::createFromFormat('d/m/Y H:i', request('date') . '00:00')->toDateTimeString() : null;

        //dd($sds_request);

        // Create SDS
        $sds = SafetyDataSheet::create($sds_request);

        // Add categories
        if (request('categories'))
            $sds->categories()->sync(request('categories'));

        // Handle attached file
        if (request()->hasFile('singlefile')) {
            $basePath = 'whs/sds';
            $sds->attachment = FileBank::storeUploadedFile(request()->file('singlefile'), $basePath);
            $sds->save();
        }
        Toastr::success("Created SDS");

        return view('safety/doc/sds/list');
    }


    /**
     * Update the specified resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function update($id)
    {
        $sds = SafetyDataSheet::findOrFail($id);
        //dd(request()->all());

        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('edit.sds', $sds))
            return view('errors/404');

        $rules = ['name' => 'required', 'categories' => 'required'];
        $mesg = [
            'name.required' => 'The name field is required.',
            'categories.required' => 'The category field is required.',
        ];
        request()->validate($rules, $mesg); // Validate

        // Verify date
        if (request('date')) {
            if (preg_match("/(\d{2})\/(\d{2})\/(\d{4})$/", request('date'), $matches)) {
                list($dd, $mm, $yyyy) = explode('/', request('date'));
                if (!checkdate($mm, $dd, $yyyy))
                    return back()->withErrors(['date' => "Invalid date. Required format dd/mm/yyyy"]);
            } else
                return back()->withErrors(['date' => "Invalid date. Required format dd/mm/yyyy"]);
        }

        //dd(request()->all());
        $sds_request = request()->all();
        $sds_request['date'] = (request('date')) ? Carbon::createFromFormat('d/m/Y H:i', request('date') . '00:00')->toDateTimeString() : null;
        $sds->update($sds_request);


        // Handle attached file
        if (request()->hasFile('singlefile')) {
            $basePath = 'whs/sds';
            $sds->attachment = FileBank::replaceUploadedFile(request()->file('singlefile'), $basePath, $sds->attachment);
            $sds->save();
        }
        Toastr::success("Updated SDS");

        return view('safety/doc/sds/edit', compact('sds'));
    }


    /**
     * Get Docs current user is authorised to manage + Process datatables ajax request.
     */
    public function getSDS()
    {
        if (request('category_id') && request('category_id') != ' ')
            $category_list = [request('category_id')];
        else
            $category_list = SafetyDocCategory::pluck('id')->toArray();

        $sds_list = DB::table('safety_sds_cats')->whereIn('safety_sds_cats.cat_id', $category_list)->pluck('sds_id')->toArray();
        //$company_list = [Auth::user()->company_id, Auth::user()->company->reportsTo()->id];
        $records = DB::table('safety_sds_docs as d')
            ->select(['d.id', 'd.attachment', 'd.name', 'd.application', 'd.manufacturer', 'd.hazardous', 'd.dangerous'])
            ->whereIn('d.id', $sds_list)
            ->where('d.status', '1');

        $dt = Datatables::of($records)
            ->editColumn('id', function ($doc) {
                if (!$doc->attachment) return '';
                $path = "whs/sds/{$doc->attachment}";
                return '<div class="text-center"><a href="' . FileBank::url($path) . '" target="_blank"><i class="fa fa-file-text-o"></i></a></div>';
            })
            ->addColumn('categories', function ($sds) {
                $record = SafetyDataSheet::find($sds->id);

                return $record->categoriesSBC();
            })
            ->addColumn('hazdanger', function ($doc) {
                //$record = SafetyDoc::find($doc->id);

                return ($doc->hazardous || $doc->dangerous) ? '<span class="font-red">Y</span>' : 'N';
            })
            ->addColumn('action', function ($doc) {
                $record = SafetyDoc::find($doc->id);
                $actions = '';

                if (Auth::user()->allowed2('edit.sds', $record))
                    //if (Auth::user()->hasPermission2('edit.sds'))
                    $actions .= '<a href="/safety/doc/sds/' . $doc->id . '/edit' . '" class="btn blue btn-xs btn-outline sbold uppercase margin-bottom"><i class="fa fa-pencil"></i> Edit</a>';
                if (Auth::user()->allowed2('del.sds', $record))
                    //if (Auth::user()->hasPermission2('del.sds'))
                    $actions .= '<button class="btn dark btn-xs sbold uppercase margin-bottom btn-delete " data-remote="/safety/doc/sds/' . $doc->id . '" data-name="' . $doc->name . '"><i class="fa fa-trash"></i></button>';

                return $actions;
            })
            ->rawColumns(['id', 'action', 'hazdanger'])
            ->make(true);

        return $dt;
    }
}
