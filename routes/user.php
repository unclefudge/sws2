<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| USER
|--------------------------------------------------------------------------
*/
Route::prefix('user/{uid}')->as('user.')->group(function () {
    Route::get('doc/dt/docs', '\App\Http\Controllers\User\UserDocController@getDocs');
    Route::get('doc/upload', '\App\Http\Controllers\User\UserDocController@create');
    Route::post('doc/reject/{id}', '\App\Http\Controllers\User\UserDocController@reject');
    Route::get('doc/archive/{id}', '\App\Http\Controllers\User\UserDocController@archive');
    Route::resource('doc', '\App\Http\Controllers\User\UserDocController');
});

// User Routes
Route::get('user/dt/users', '\App\Http\Controllers\UserController@getUsers');
Route::get('user/dt/contractors', '\App\Http\Controllers\UserController@getContractors');
Route::get('user/data/details/{id}', '\App\Http\Controllers\UserController@getUserDetails');
Route::post('user/{id}/login', '\App\Http\Controllers\UserController@updateLogin');
Route::get('user/{id}/security', '\App\Http\Controllers\UserController@showSecurity');
Route::post('user/{id}/security', '\App\Http\Controllers\UserController@updateSecurity');
Route::get('user/{id}/resetpassword', '\App\Http\Controllers\UserController@showResetPassword');
Route::post('user/{id}/resetpassword', '\App\Http\Controllers\UserController@updatePassword');
Route::post('user/{id}/construction', '\App\Http\Controllers\UserController@updateConstruction');
Route::get('user/{id}/resetpermissions', '\App\Http\Controllers\UserController@resetPermissions');
Route::post('user/{id}/compliance', '\App\Http\Controllers\UserController@storeCompliance');
Route::post('user/{id}/compliance/update', '\App\Http\Controllers\UserController@updateCompliance');
Route::get('user/{id}/token/create', '\App\Http\Controllers\UserController@createApiToken');
Route::get('contractor', '\App\Http\Controllers\UserController@contractorList');
//Route::resource('user', '\App\Http\Controllers\UserController');
Route::as('user.')->resource('user', '\App\Http\Controllers\UserController');