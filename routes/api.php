<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});


// Mailgun Routes
Route::group([
    'prefix' => 'mailgun',
], function () {
    Route::post('zoho', 'Api\MailgunZohoController@store');
    Route::post('sitenote', 'Api\MailgunSiteNoteController@store');
});


//Route::apiResource('site', 'Api\SiteController');
Route::middleware('auth:sanctum')->apiResource('site', 'Api\SiteController');

