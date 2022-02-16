<?php

namespace App\Http\Controllers\Client;


use Illuminate\Http\Request;

use DB;
use App\Models\Misc\Client;
use App\Http\Requests;
use App\Http\Requests\Misc\ClientRequest;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Yajra\Datatables\Datatables;
use nilsenj\Toastr\Facades\Toastr;

class ClientController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //if (!Auth::user()->company->subscription && Auth::user()->hasAnyPermissionType('client'))
            return view('client/list');

        return view('errors/404');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (!Auth::user()->allowed2('add.client'))
            return view('client/create');

        return view('errors/404');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store(ClientRequest $request)
    {
        // Check authorisation and throw 404 if not
        //if (!Auth::user()->allowed2('add.client'))
        //    return view('errors/404');

        $client_request = $request->except('tabs');

        // Create Client
        Client::create($client_request);
        Toastr::success("Created new client");

        return redirect('client');
    }

    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function show($slug)
    {
        $client = Client::where(compact('slug'))->firstorFail();

        // Check authorisation and throw 404 if not
        //if (!Auth::user()->allowed2('view.client', $client))
        //    return view('errors/404');

        $tabs = ['profile', 'info'];

        return view('client/show', compact('client', 'tabs'));
    }

    /**
     * Display the settings for the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function showSettings($slug, $tab = 'info')
    {
        $client = Client::where(compact('slug'))->firstOrFail();

        // Check authorisation and throw 404 if not
        //if (!Auth::user()->allowed2('edit.client', $client))
        //    return view('errors/404');

        $tabs = ['settings', $tab];

        return view('client/show', compact('client', 'tabs'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function update(ClientRequest $request, $slug)
    {
        $client = Client::where(compact('slug'))->firstOrFail();

        // Check authorisation and throw 404 if not
        //if (!Auth::user()->allowed2('edit.client', $client))
        //    return view('errors/404');

        $client_request = $request->except('tabs');

        $client->update($client_request);
        Toastr::success("Saved changes");
        $tabs = explode(':', $request->get('tabs'));

        return redirect('/client/' . $client->slug . '/' . $tabs[0] . '/' . $tabs[1]);
    }

    /**
     * Get Clients current user is authorised to manage + Process datatables ajax request.
     */
    public function getClients(Request $request)
    {
        $clients = Auth::user()->company->clients()->where('status', $request->get('status'))->get();
        $dt = Datatables::of($clients)
            ->editColumn('id', '<div class="text-center"><a href="/client/{{$slug}}"><i class="fa fa-search"></i></a></div>')
            ->editColumn('phone', function ($user) {
                return '<a href="tel:' . preg_replace("/[^0-9]/", "", $user->phone) . '">' . $user->phone . '</a>';
            })
            ->editColumn('email', function ($user) {
                return '<a href="mailto:' . $user->email . '">' . $user->email . '</a>';
            })
            ->removeColumn('slug')
            ->addColumn('action', function ($client) {
                return '<a href="/client/'.$client->slug.'/settings" class="btn blue btn-xs btn-outline sbold uppercase margin-bottom"><i class="fa fa-pencil"></i> Edit</a>';
            })
            ->make(true);

        return $dt;
    }
}
