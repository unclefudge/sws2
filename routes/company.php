<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| COMPANY DOCS (namespaced)
|--------------------------------------------------------------------------
*/
Route::prefix('company/{cid}')->as('company.')->group(function () {
    Route::get('doc/dt/docs', '\App\Http\Controllers\Company\CompanyDocController@getDocs');
    Route::get('doc/upload', '\App\Http\Controllers\Company\CompanyDocController@create');
    Route::post('doc/reject/{id}', '\App\Http\Controllers\Company\CompanyDocController@reject');
    Route::get('doc/archive/{id}', '\App\Http\Controllers\Company\CompanyDocController@archive');
    Route::get('doc/cats/{department}', '\App\Http\Controllers\Company\CompanyDocController@getCategories');

    Route::resource('doc', '\App\Http\Controllers\Company\CompanyDocController')
        ->except(['destroy']);
});

/*
|--------------------------------------------------------------------------
| COMPANY DOCS – DELETE (no company context)
|--------------------------------------------------------------------------
*/
Route::delete('company/doc/{doc}', [\App\Http\Controllers\Company\CompanyDocController::class, 'destroy'])->name('company.doc.destroy');

// Company Leave Routes
Route::get('/company/leave/dt/leave', '\App\Http\Controllers\Company\CompanyLeaveController@getCompanyLeave');
Route::resource('/company/leave', '\App\Http\Controllers\Company\CompanyLeaveController');

// Company Standard Documents Review
Route::get('company/doc/standard/review/dt/docs', '\App\Http\Controllers\Company\CompanyDocReviewController@getStandard');
Route::resource('company/doc/standard/review', '\App\Http\Controllers\Company\CompanyDocReviewController');

// Company Standard Documents
Route::get('company/doc/standard', '\App\Http\Controllers\Company\CompanyDocController@showStandard');
Route::get('company/doc/standard/dt/docs', '\App\Http\Controllers\Company\CompanyDocController@getStandard');

// Company Period Trade Contract
Route::post('company/{cid}/doc/period-trade-contract/reject/{id}', '\App\Http\Controllers\Company\CompanyPeriodTradeController@reject');
Route::resource('company/{cid}/doc/period-trade-contract', '\App\Http\Controllers\Company\CompanyPeriodTradeController');

// Company Subcontractors Statement
Route::post('company/{cid}/doc/subcontractor-statement/reject/{id}', '\App\Http\Controllers\Company\CompanySubcontractorStatementController@reject');
Route::resource('company/{cid}/doc/subcontractor-statement', '\App\Http\Controllers\Company\CompanySubcontractorStatementController');

// Privacy Policy
Route::post('company/{cid}/doc/privacy-policy/reject/{id}', '\App\Http\Controllers\Company\CompanyPrivacyPolicyController@reject');
Route::resource('company/{cid}/doc/privacy-policy', '\App\Http\Controllers\Company\CompanyPrivacyPolicyController');

// Supervisors
// Site Supervisors
Route::get('site/supervisor/data/supers', '\App\Http\Controllers\Company\CompanySupervisorController@getSupers');
Route::resource('site/supervisor', '\App\Http\Controllers\Company\CompanySupervisorController');

// Company Docs
Route::any('company/doc/export', '\App\Http\Controllers\Company\CompanyExportController@exportDocs');
Route::post('company/doc/export/pdf', '\App\Http\Controllers\Company\CompanyExportController@docsPDF');
Route::get('company/doc/create/tradecontract/{id}/{version}', '\App\Http\Controllers\Company\CompanyExportController@tradecontractPDF');
Route::get('company/doc/create/subcontractorstatement/{id}/{version}', '\App\Http\Controllers\Company\CompanyExportController@subcontractorstatementPDF');


// Company Routes
Route::get('company/dt/companies', '\App\Http\Controllers\Company\CompanyController@getCompanies');
Route::get('company/dt/users', '\App\Http\Controllers\Company\CompanyController@getUsers');
Route::get('company/data/details/{id}', '\App\Http\Controllers\Company\CompanyController@getCompanyDetails');
Route::get('company/{id}/name', '\App\Http\Controllers\Company\CompanyController@getCompanyName');
Route::get('company/{id}/approve/{type}', '\App\Http\Controllers\Company\CompanyController@approveCompany');
Route::post('company/{id}/business', '\App\Http\Controllers\Company\CompanyController@updateBusiness');
Route::post('company/{id}/construction', '\App\Http\Controllers\Company\CompanyController@updateConstruction');
Route::post('company/{id}/whs', '\App\Http\Controllers\Company\CompanyController@updateWHS');
Route::post('company/{id}/leave', '\App\Http\Controllers\Company\CompanyController@storeLeave');
Route::post('company/{id}/leave/update', '\App\Http\Controllers\Company\CompanyController@updateLeave');
Route::get('company/{id}/leave/{lid}', '\App\Http\Controllers\Company\CompanyController@destroyLeave');
Route::post('company/{id}/compliance', '\App\Http\Controllers\Company\CompanyController@storeCompliance');
Route::post('company/{id}/compliance/update', '\App\Http\Controllers\Company\CompanyController@updateCompliance');
Route::post('company/{id}/add_note', '\App\Http\Controllers\Company\CompanyController@addNote');
Route::get('company/{id}/user', '\App\Http\Controllers\Company\CompanyController@users');
//Route::get('company/{id}/edit/trade', '\App\Http\Controllers\Company\CompanyController@editTrade');
Route::get('company/{id}/demo1', '\App\Http\Controllers\Company\CompanyController@demo1');
Route::get('company/{id}/demo2', '\App\Http\Controllers\Company\CompanyController@demo2');
Route::get('company/{id}/demo3', '\App\Http\Controllers\Company\CompanyController@demo3');
Route::get('company/{id}/demo4', '\App\Http\Controllers\Company\CompanyController@demo4');
Route::as('company.')->resource('company', '\App\Http\Controllers\Company\CompanyController');