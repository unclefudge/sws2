<?php

namespace App\Http\Controllers\Misc\Form;

use App\Http\Controllers\Controller;
use App\Models\Misc\Option;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Mail;
use nilsenj\Toastr\Facades\Toastr;
use Session;
use Validator;
use Yajra\Datatables\Datatables;

/**
 * Class SiteMaintenanceCategoryController
 * @package App\Http\Controllers\Site
 */
class OptionsController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Check authorisation and throw 404 if not
        //if (!Auth::user()->allowed2('add.site.maintenance'))
        //    return view('errors/404');

        return view('options/list');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        /// Check authorisation and throw 404 if not
        //if (!Auth::user()->allowed2('add.site.maintenance'))
        //   return view('errors/404');

        return view('options/create');
    }

    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $opt = Option::findOrFail($id);

        // Check authorisation and throw 404 if not
        //if (!Auth::user()->allowed2('view.site.maintenance', $main))
        //    return view('errors/404');

        return view('options/show', compact('opt'));
    }

    /**
     * Edit the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $opt = Option::findOrFail($id);

        // Check authorisation and throw 404 if not
        //if (!Auth::user()->allowed2('add.site.maintenance'))
        //    return view('errors/404');

        return view('options/edit', compact('opt'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store()
    {
        // Check authorisation and throw 404 if not
        //if (!Auth::user()->allowed2('add.site.maintenance'))
        //   return view('errors/404');

        request()->validate(['value' => 'required']); // Validate

        // Create Category
        Option::create(request()->all());

        Toastr::success("Created new option");

        return redirect('/option');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update($id)
    {
        $opt = Option::findOrFail($id);

        // Check authorisation and throw 404 if not
        //if (!Auth::user()->allowed2('add.site.maintenance'))
        //    return view('errors/404');

        request()->validate(['value' => 'required']); // Validate

        $opt->update(request()->all());

        Toastr::success("Updated Option");

        return redirect('option');
    }

    /**
     * Delete the specified resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $opt = Option::findOrFail($id);

        // Check authorisation and throw 404 if not
        //if (!Auth::user()->allowed2('add.site.maintenance'))
        //    return view('errors/404');

        $opt->delete();

        return json_encode('success');
    }

    /**
     *  Delete Setting
     */
    public function deleteCat($id)
    {
        // Check authorisation and throw 404 if not
        if (!Auth::user()->hasAnyRole2('web-admin|mgt-general-manager'))
            return view('errors/404');

        //dd(request()->all());

        // Delete setting
        $cat = Category::findOrFail($id);
        $cat->status = 0;
        $cat->save();

        // Re-order settings
        $cats = Category::where('type', $cat->type)->where('status', 1)->orderBy('order')->get();
        $order = 1;
        foreach ($cats as $cat) {
            $cat->order = $order++;
            $cat->save();
        }

        Toastr::success("Updated categories");

        return redirect(url()->previous());
    }

    /**
     *  Category Update
     */
    static public function updateCategories($type, Request $request)
    {
        if (request('add_cat')) {
            $rules = ['add_cat_name' => 'required'];
            $mesg = ['add_cat_name.required' => 'The name field is required.'];
            request()->validate($rules, $mesg); // Validate
        }

        //dd(request()->all());
        $cats = Category::where('type', $type)->where('status', 1)->orderBy('order')->get();
        foreach ($cats as $cat) {
            if (request()->has("cat-$cat->id")) {
                if (request("cat-$cat->id")) {
                    $cat->name = request("cat-$cat->id");

                    // Notify Users
                    $cat->notify_users = null;
                    if (request("notify_users-$cat->id") && is_array(request("notify_users-$cat->id")))
                        $cat->notify_users = implode(',', request("notify_users-$cat->id"));

                    $cat->save();  // save record
                } else
                    return back()->withErrors(["cat-$cat->id" => "The name field is required."]);
            }
        }

        // Add Extra Field
        if (request('add_cat')) {
            $add_order = count($cats) + 1;
            $notify_users = (request('add_cat_notify_users')) ? implode(',', request('add_cat_notify_users')) : null;
            Category::create(['type' => $type, 'name' => request('add_cat_name'), 'order' => $add_order, 'notify_users' => $notify_users, 'company_id' => Auth::user()->company->reportsTo()->id, 'status' => 1]);
        }
    }

    /**
     *  Category Order
     */
    public function updateOrder($direction, $id)
    {
        // Check authorisation and throw 404 if not
        if (!Auth::user()->hasAnyRole2('web-admin|mgt-general-manager'))
            return view('errors/404');

        //dd(request()->all());
        $cat = Category::findOrFail($id);

        if ($direction == 'up' && $cat->order != 1) {
            $newPos = $cat->order - 1;
            $cat2 = Category::where('type', $cat->type)->where('status', 1)->where('order', $newPos)->first();
            if ($cat2) {
                $cat2->order = $cat->order;
                $cat2->save();
                $cat->order = $newPos;
                $cat->save();
            }
        }

        $last = Category::where('type', $cat->type)->where('status', 1)->orderByDesc('order')->first();
        if ($last && $direction == 'down' && $cat->order != $last->order) {
            $newPos = $cat->order + 1;
            $cat2 = Category::where('type', $cat->type)->where('status', 1)->where('order', $newPos)->first();
            if ($cat2) {
                $cat2->order = $cat->order;
                $cat2->save();
                $cat->order = $newPos;
                $cat->save();
            }
        }

        Toastr::success("Updated categories");

        return redirect(url()->previous());
    }


    /**
     * Get QA templates current user is authorised to manage + Process datatables ajax request.
     */
    public function getMainCategories()
    {
        $records = Category::where('status', 1)->orderBy('name');

        $dt = Datatables::of($records)
            ->addColumn('reports', function ($cat) {
                $reports = implode(', ', $cat->reports->where('master', 1)->where('status', 1)->pluck('name')->toArray());

                return $reports;
            })
            ->addColumn('action', function ($cat) {
                $actions = '<a href="/category/' . $cat->id . '/edit" class="btn blue btn-xs btn-outline sbold uppercase margin-bottom"><i class="fa fa-pencil"></i> Edit</a>';
                $actions .= '<button class="btn dark btn-xs sbold uppercase margin-bottom btn-delete " data-remote="/category/' . $cat->id . '" data-name="' . $cat->name . '"><i class="fa fa-trash"></i></button>';

                return $actions;
            })
            ->rawColumns(['id', 'name', 'reports', 'updated_at', 'action'])
            ->make(true);

        return $dt;
    }


}
