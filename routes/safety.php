<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| SAFETY DOCS (namespaced)
|--------------------------------------------------------------------------
*/
Route::prefix('safety')->as('safety.')->group(function () {
    Route::resource('doc/sds', '\App\Http\Controllers\Safety\SdsController');
    Route::resource('doc/toolbox2', '\App\Http\Controllers\Safety\ToolboxTalkController');
    Route::resource('doc/toolbox3', '\App\Http\Controllers\Safety\ToolboxTalk3Controller');
    Route::resource('doc/wms', '\App\Http\Controllers\Safety\WmsController');
});

// SDS Safety Docs
Route::get('safety/doc/dt/sds', '\App\Http\Controllers\Safety\SdsController@getSDS');
Route::any('safety/doc/sds/create', '\App\Http\Controllers\Safety\SdsController@create');
Route::any('safety/doc/sds/upload', '\App\Http\Controllers\Safety\SdsController@upload');

// Toolbox Talks
Route::get('safety/doc/toolbox2', '\App\Http\Controllers\Safety\ToolboxTalkController@index');
Route::get('safety/doc/toolbox2/{id}/accept', '\App\Http\Controllers\Safety\ToolboxTalkController@accept');
Route::get('safety/doc/toolbox2/{id}/create', '\App\Http\Controllers\Safety\ToolboxTalkController@createFromTemplate');
Route::get('safety/doc/toolbox2/{id}/reject', '\App\Http\Controllers\Safety\ToolboxTalkController@reject');
Route::get('safety/doc/toolbox2/{id}/signoff', '\App\Http\Controllers\Safety\ToolboxTalkController@signoff');
Route::get('safety/doc/toolbox2/{id}/archive', '\App\Http\Controllers\Safety\ToolboxTalkController@archive');
Route::get('safety/doc/toolbox2/{id}/destroy', '\App\Http\Controllers\Safety\ToolboxTalkController@destroy');
Route::get('safety/doc/toolbox2/{id}/pdf', '\App\Http\Controllers\Safety\ToolboxTalkController@createPDF');
Route::post('safety/doc/toolbox2/{id}/upload', '\App\Http\Controllers\Safety\ToolboxTalkController@uploadMedia');
Route::get('safety/doc/dt/toolbox2', '\App\Http\Controllers\Safety\ToolboxTalkController@getToolbox');
Route::get('safety/doc/dt/toolbox_templates', '\App\Http\Controllers\Safety\ToolboxTalkController@getToolboxTemplates');


// Toolbox Talks3
Route::get('safety/doc/toolbox3', '\App\Http\Controllers\Safety\ToolboxTalk3Controller@index');
Route::get('safety/doc/toolbox3/{id}/accept', '\App\Http\Controllers\Safety\ToolboxTalk3Controller@accept');
Route::get('safety/doc/toolbox3/{id}/create', '\App\Http\Controllers\Safety\ToolboxTalk3Controller@createFromTemplate');
Route::get('safety/doc/toolbox3/{id}/reject', '\App\Http\Controllers\Safety\ToolboxTalk3Controller@reject');
Route::get('safety/doc/toolbox3/{id}/signoff', '\App\Http\Controllers\Safety\ToolboxTalk3Controller@signoff');
Route::get('safety/doc/toolbox3/{id}/archive', '\App\Http\Controllers\Safety\ToolboxTalk3Controller@archive');
Route::get('safety/doc/toolbox3/{id}/reminder', '\App\Http\Controllers\Safety\ToolboxTalk3Controller@reminder');
Route::get('safety/doc/toolbox3/{id}/destroy', '\App\Http\Controllers\Safety\ToolboxTalk3Controller@destroy');
Route::get('safety/doc/toolbox3/{id}/deluser/{uid}', '\App\Http\Controllers\Safety\ToolboxTalk3Controller@deluser');
Route::get('safety/doc/toolbox3/{id}/pdf', '\App\Http\Controllers\Safety\ToolboxTalk3Controller@createPDF');
Route::post('safety/doc/toolbox3/{id}/upload', '\App\Http\Controllers\Safety\ToolboxTalk3Controller@uploadMedia');
Route::get('safety/doc/dt/toolbox3', '\App\Http\Controllers\Safety\ToolboxTalk3Controller@getToolbox');
Route::get('safety/doc/dt/toolbox3_templates', '\App\Http\Controllers\Safety\ToolboxTalk3Controller@getToolboxTemplates');

// Safety Docs - WMS
Route::get('safety/doc/wms', '\App\Http\Controllers\Safety\WmsController@index');
Route::get('safety/doc/wms/expired', '\App\Http\Controllers\Safety\WmsController@expired');
Route::get('safety/doc/wms/signoff-pending', '\App\Http\Controllers\Safety\WmsController@sinoffMassPending');
Route::get('safety/doc/wms/{id}/create', '\App\Http\Controllers\Safety\WmsController@createFromTemplate');
Route::get('safety/doc/wms/{id}/steps', '\App\Http\Controllers\Safety\WmsController@getSteps');
Route::any('safety/doc/wms/{id}/update', '\App\Http\Controllers\Safety\WmsController@update');
Route::get('safety/doc/wms/{id}/reject', '\App\Http\Controllers\Safety\WmsController@reject');
Route::get('safety/doc/wms/{id}/signoff', '\App\Http\Controllers\Safety\WmsController@signoff');
Route::get('safety/doc/wms/{id}/archive', '\App\Http\Controllers\Safety\WmsController@archive');
Route::any('safety/doc/wms/{id}/pdf', '\App\Http\Controllers\Safety\WmsController@pdf');
Route::post('safety/doc/wms/{id}/email', '\App\Http\Controllers\Safety\WmsController@email');
Route::any('safety/doc/wms/{id}/upload', '\App\Http\Controllers\Safety\WmsController@upload');
Route::get('safety/doc/wms/{id}/replace', '\App\Http\Controllers\Safety\WmsController@replace');
Route::get('safety/doc/wms/{id}/renew', '\App\Http\Controllers\Safety\WmsController@createRenew');
Route::get('safety/doc/dt/wms', '\App\Http\Controllers\Safety\WmsController@getWms');
Route::get('safety/doc/dt/wms_templates', '\App\Http\Controllers\Safety\WmsController@getWmsTemplates');