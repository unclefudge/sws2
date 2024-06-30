<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SiteResource;
use App\Models\Site\Site;
use Illuminate\Http\Request;

class SiteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //return Site::all();
        ray('index');
        return SiteResource::collection(Site::all());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $site_request = request()->all();
        ray(request()->all());

        if (request('code') && request('company_id')) {
            $site = Site::where('code', request('code'))->where('company_id', request('company_id'))->first();

            if ($site) {
                
                return SiteResource::make($site);
            }
        }
        return response()->json(['status' => 'error', 'message' => 'Invalid data'], 406);

    }

    /**
     * Display the specified resource.
     */
    public function show(Site $site)
    {
        ray('show');
        return SiteResource::make($site);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        ray('update');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    protected function ok($message, $data)
    {
        return $this->success($message, $data, 200);
    }

    protected function success($message, $data, $status = 200)
    {
        return response()->json(['data' => $data, 'message' => $message, 'status' => $status], $status);
    }

    protected function error($message, $status)
    {
        return response()->json(['message' => $message, 'status' => $status], $status);
    }
}
