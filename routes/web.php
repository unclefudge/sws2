<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', 'HomeController@index');

// Site Login
Route::get('/login/site/{site_code}', function ($site_code) {
    Auth::logout();

    return redirect('/checkin');
    /*
    $worksite = \App\Models\Site\Site::where(['code' => $site_code])->first();
		Session::put('siteID', $worksite->id);

    return view('auth/login-site', compact('worksite'));
    */
});

// Authentication routes...
Route::get('/login', '\App\Http\Controllers\Auth\SessionController@create')->name('login');
Route::post('/login', '\App\Http\Controllers\Auth\SessionController@store');
Route::get('/logout', '\App\Http\Controllers\Auth\SessionController@destroy')->name('logout');

// Signup routes.. Pre Login
Route::get('/signup', '\App\Http\Controllers\Auth\RegistrationController@create')->name('register');
Route::get('/signup/ref/{key}', '\App\Http\Controllers\Auth\RegistrationController@refCreate');
Route::get('/signup/primary/{key}', '\App\Http\Controllers\Auth\RegistrationController@primaryCreate');
Route::post('/signup/primary', '\App\Http\Controllers\Auth\RegistrationController@primaryStore');

// Password Reset Routes...
Route::get('/password/reset', '\App\Http\Controllers\Auth\PasswordResetController@forgotForm');
Route::post('/password/email', '\App\Http\Controllers\Auth\PasswordResetController@resetEmail');
Route::get('/password/reset/{token}', '\App\Http\Controllers\Auth\PasswordResetController@resetForm');
Route::post('/password/reset', '\App\Http\Controllers\Auth\PasswordResetController@reset');

//Route::get('password/reset', '\App\Http\Controllers\Auth\ForgotPasswordController@showLinkRequestForm')->name('password.reset');
//Route::post('password/email', '\App\Http\Controllers\Auth\ForgotPasswordController@sendResetLinkEmail')->name('password.email');
//Route::get('password/reset/{token}', '\App\Http\Controllers\Auth\ResetPasswordController@showResetForm')->name('password.reset.token');
//Route::post('password/reset', '\App\Http\Controllers\Auth\ResetPasswordController@reset');

// Logged in Routes
Route::group(['middleware' => 'auth'], function () {
    // Signup routes.. Post Login
    Route::get('/signup/user/{id}', '\App\Http\Controllers\Company\CompanySignUpController@userEdit');         // Step 1
    Route::post('/signup/user/{id}', '\App\Http\Controllers\Company\CompanySignUpController@userUpdate');
    Route::get('/signup/company/{id}', '\App\Http\Controllers\Company\CompanySignUpController@companyEdit');   // Step 2
    Route::post('/signup/company/{id}', '\App\Http\Controllers\Company\CompanySignUpController@companyUpdate');
    Route::get('/signup/workers/{id}', '\App\Http\Controllers\Company\CompanySignUpController@workersEdit');   // Step 3
    Route::post('/signup/workers/{id}', '\App\Http\Controllers\Company\CompanySignUpController@workersUpdate');
    Route::get('/signup/summary/{id}', '\App\Http\Controllers\Company\CompanySignUpController@summary');       // Step 4
    Route::get('/signup/documents/{id}', '\App\Http\Controllers\Company\CompanySignUpController@documents');   // Step 5
    Route::get('/signup/welcome/{id}', '\App\Http\Controllers\Company\CompanySignUpController@welcome');       // Resend welcome email
    Route::get('/signup/cancel/{id}', '\App\Http\Controllers\Company\CompanySignUpController@cancel');

    // Site Checkin
    Route::get('checkout', '\App\Http\Controllers\Site\SiteCheckinController@checkout');
    Route::get('checkin', '\App\Http\Controllers\Site\SiteCheckinController@checkin');
    Route::post('checkin', '\App\Http\Controllers\Site\SiteCheckinController@getQuestions');
    Route::get('checkin/whs/{site_id}', '\App\Http\Controllers\Site\SiteCheckinController@showQuestions');
    Route::post('checkin/whs/{site_id}', '\App\Http\Controllers\Site\SiteCheckinController@processCheckin');
    Route::get('checkin/supervisor/{site_id}', '\App\Http\Controllers\Site\SiteCheckinController@showQuestionsSupervisor');
    Route::post('checkin/supervisor/{site_id}', '\App\Http\Controllers\Site\SiteCheckinController@processCheckinSupervisor');

    // Session variables
    Route::get('/session/{name}/{val}', '\App\Http\Controllers\Misc\SessionValController@updateVal');
    Route::post('/session/update', '\App\Http\Controllers\Misc\SessionValController@update');


    // Pages
    //Route::get('/', '\App\Http\Controllers\Misc\PagesController@index');
    Route::get('/home', '\App\Http\Controllers\Misc\PagesController@index');
    Route::get('/dashboard', '\App\Http\Controllers\Misc\PagesController@index');
    Route::get('/manage/quick', '\App\Http\Controllers\Misc\PagesController@quick');
    Route::get('/manage/quick2', '\App\Http\Controllers\Misc\PagesController@quick2');
    Route::get('/manage/fixplanner', '\App\Http\Controllers\Misc\PagesController@fixplanner');
    Route::get('/manage/importcompany', '\App\Http\Controllers\Misc\PagesController@importCompany');
    Route::get('/manage/completedqa', '\App\Http\Controllers\Misc\PagesController@completedQA');
    Route::get('/manage/create_permission', '\App\Http\Controllers\Misc\PagesController@createPermission');
    Route::get('/manage/importmaterials', '\App\Http\Controllers\Misc\PagesController@importMaterials');
    Route::get('/manage/disabled_tasks', '\App\Http\Controllers\Misc\PagesController@disabledTasks');

    // Reports
    Route::get('/manage/report', '\App\Http\Controllers\Misc\ReportController@index');
    Route::get('/manage/report/recent', '\App\Http\Controllers\Misc\ReportController@recent');
    Route::get('/manage/report/recent/files', '\App\Http\Controllers\Misc\ReportController@recentFiles');
    // Reports - User/Company
    Route::get('/manage/report/newusers', '\App\Http\Controllers\Misc\ReportUserCompanyController@newusers');
    Route::get('/manage/report/newcompanies', '\App\Http\Controllers\Misc\ReportUserCompanyController@newcompanies');
    Route::get('/manage/report/users_noemail', '\App\Http\Controllers\Misc\ReportUserCompanyController@users_noemail');
    Route::get('/manage/report/users_nowhitecard', '\App\Http\Controllers\Misc\ReportUserCompanyController@users_nowhitecard');
    Route::get('/manage/report/users_nowhitecard_csv', '\App\Http\Controllers\Misc\ReportUserCompanyController@users_nowhitecardCSV');
    Route::get('/manage/report/users_lastlogin', '\App\Http\Controllers\Misc\ReportUserCompanyController@usersLastLogin');
    Route::get('/manage/report/users_lastlogin_csv', '\App\Http\Controllers\Misc\ReportUserCompanyController@usersLastLoginCSV');
    Route::get('/manage/report/users_contactinfo', '\App\Http\Controllers\Misc\ReportUserCompanyController@usersContactInfo');
    Route::get('/manage/report/users_contactinfo_csv', '\App\Http\Controllers\Misc\ReportUserCompanyController@usersContactInfoCSV');
    Route::get('/manage/report/roleusers', '\App\Http\Controllers\Misc\ReportUserCompanyController@roleusers');
    Route::get('/manage/report/users_extra_permissions', '\App\Http\Controllers\Misc\ReportUserCompanyController@usersExtraPermissions');
    Route::get('/manage/report/users_with_permission/{type}', '\App\Http\Controllers\Misc\ReportUserCompanyController@usersWithPermission');
    Route::get('/manage/report/missing_company_info', '\App\Http\Controllers\Misc\ReportUserCompanyController@missingCompanyInfo');
    Route::get('/manage/report/missing_company_info_csv', '\App\Http\Controllers\Misc\ReportUserCompanyController@missingCompanyInfoCSV');
    Route::get('/manage/report/missing_company_info_planner', '\App\Http\Controllers\Misc\ReportUserCompanyController@missingCompanyInfoPlanner');
    Route::get('/manage/report/missing_company_info_planner_csv', '\App\Http\Controllers\Misc\ReportUserCompanyController@missingCompanyInfoPlannerCSV');
    Route::get('/manage/report/company_users', '\App\Http\Controllers\Misc\ReportUserCompanyController@companyUsers');
    Route::get('/manage/report/company_contactinfo', '\App\Http\Controllers\Misc\ReportUserCompanyController@companyContactInfo');
    Route::get('/manage/report/company_contactinfo_csv', '\App\Http\Controllers\Misc\ReportUserCompanyController@companyContactInfoCSV');
    Route::get('/manage/report/company_privacy', '\App\Http\Controllers\Misc\ReportUserCompanyController@companyPrivacy');
    Route::get('/manage/report/company_privacy_send/{type}', '\App\Http\Controllers\Misc\ReportUserCompanyController@companyPrivacySend');
    Route::get('/manage/report/company_swms', '\App\Http\Controllers\Misc\ReportUserCompanyController@companySWMS');
    Route::get('/manage/report/expired_company_docs', '\App\Http\Controllers\Misc\ReportUserCompanyController@expiredCompanyDocs');
    Route::get('/manage/report/expired_company_docs/dt/expired_company_docs', '\App\Http\Controllers\Misc\ReportUserCompanyController@getExpiredCompanyDocs');
    Route::get('/manage/report/pending_company_docs', '\App\Http\Controllers\Misc\ReportUserCompanyController@pendingCompanyDocs');
    Route::get('/manage/report/company_planned_tasks', '\App\Http\Controllers\Misc\ReportUserCompanyController@companyPlannedTasks');
    // Reports - Equipment
    Route::get('/manage/report/equipment', '\App\Http\Controllers\Misc\ReportEquipmentController@equipment');
    Route::get('/manage/report/equipment/report', '\App\Http\Controllers\Misc\ReportEquipmentController@equipmentPDF');
    Route::get('/manage/report/equipment_site', '\App\Http\Controllers\Misc\ReportEquipmentController@equipmentSite');
    Route::get('/manage/report/equipment_site/report', '\App\Http\Controllers\Misc\ReportEquipmentController@equipmentSitePDF');
    Route::get('/manage/report/equipment_transactions', '\App\Http\Controllers\Misc\ReportEquipmentController@equipmentTransactions');
    Route::post('/manage/report/equipment_transactions/report', '\App\Http\Controllers\Misc\ReportEquipmentController@equipmentTransactionsPDF');
    Route::get('/manage/report/equipment/dt/transactions', '\App\Http\Controllers\Misc\ReportEquipmentController@getEquipmentTransactions');
    Route::get('/manage/report/equipment_stocktake', '\App\Http\Controllers\Misc\ReportEquipmentController@equipmentStocktake');
    //Route::post('/manage/report/equipment_stocktake/report', '\App\Http\Controllers\Misc\ReportEquipmentController@equipmentStocktakePDF');
    Route::get('/manage/report/equipment/dt/stocktake', '\App\Http\Controllers\Misc\ReportEquipmentController@getEquipmentStocktake');
    Route::get('/manage/report/equipment/dt/stocktake-not', '\App\Http\Controllers\Misc\ReportEquipmentController@getEquipmentStocktakeNot');
    Route::get('/manage/report/equipment_transfers', '\App\Http\Controllers\Misc\ReportEquipmentController@equipmentTransfers');
    Route::post('/manage/report/equipment_transfers/report', '\App\Http\Controllers\Misc\ReportEquipmentController@equipmentTransfersPDF');
    Route::get('/manage/report/equipment/dt/transfers', '\App\Http\Controllers\Misc\ReportEquipmentController@getEquipmentTransfers');
    Route::get('/manage/report/equipment_restock', '\App\Http\Controllers\Misc\ReportEquipmentController@equipmentRestock');
    // Reports - Other
    Route::get('/manage/report/inspection_electrical_plumbing', '\App\Http\Controllers\Misc\ReportController@inspectionReports');
    Route::get('/manage/report/licence_override', '\App\Http\Controllers\Misc\ReportController@licenceOverride');
    Route::get('/manage/report/attendance', '\App\Http\Controllers\Misc\ReportController@attendance');
    Route::get('/manage/report/attendance/dt/attendance', '\App\Http\Controllers\Misc\ReportController@getAttendance');
    //Route::get('/manage/report/payroll/{from}/{to}', '\App\Http\Controllers\Misc\ReportController@payrollDates');
    Route::get('/manage/report/payroll', '\App\Http\Controllers\Misc\ReportController@payroll');
    Route::put('/manage/report/payroll', '\App\Http\Controllers\Misc\ReportController@payrollDates');
    Route::get('/manage/report/nightly', '\App\Http\Controllers\Misc\ReportController@nightly');
    Route::get('/manage/report/zoho', '\App\Http\Controllers\Misc\ReportController@zoho');
    Route::get('/manage/report/cronjobs', '\App\Http\Controllers\Misc\ReportController@cronjobs');
    Route::get('/manage/report/cronjobs/execute/{method}', '\App\Http\Controllers\Misc\ReportController@cronjobsExecute');
    Route::get('/manage/report/qa/{id}', '\App\Http\Controllers\Misc\ReportController@QAdebug');
    Route::get('/manage/report/qa_onhold', '\App\Http\Controllers\Misc\ReportController@OnholdQA');
    Route::post('/manage/report/qa_onhold/report', '\App\Http\Controllers\Misc\ReportController@OnholdQAPDF');
    Route::get('/manage/report/qa_outstanding', '\App\Http\Controllers\Misc\ReportController@OutstandingQA');
    Route::post('/manage/report/qa_outstanding/report', '\App\Http\Controllers\Misc\ReportController@OutstandingQAPDF');
    Route::get('/manage/report/maintenance_no_action', '\App\Http\Controllers\Misc\ReportController@maintenanceNoAction');
    Route::get('/manage/report/maintenance_on_hold', '\App\Http\Controllers\Misc\ReportController@maintenanceOnHold');
    Route::get('/manage/report/maintenance_executive', '\App\Http\Controllers\Misc\ReportController@maintenanceExecutive');
    Route::post('/manage/report/maintenance_executive', '\App\Http\Controllers\Misc\ReportController@maintenanceExecutive');
    Route::get('/manage/report/maintenance_appointment', '\App\Http\Controllers\Misc\ReportController@maintenanceAppointment');
    Route::get('/manage/report/maintenance_supervisor_no_action', '\App\Http\Controllers\Misc\ReportController@maintenanceSupervisorNoAction');
    Route::get('/manage/report/maintenance_assigned_company', '\App\Http\Controllers\Misc\ReportController@maintenanceAssignedCompany');
    Route::post('/manage/report/maintenance_assigned_company/pdf', '\App\Http\Controllers\Misc\ReportController@maintenanceAssignedCompanyPDF');
    Route::get('/manage/report/maintenance_assigned_company/dt/list', '\App\Http\Controllers\Misc\ReportController@getMaintenanceAssignedCompany');
    Route::get('/manage/report/maintenance_aftercare', '\App\Http\Controllers\Misc\ReportController@maintenanceAftercare');
    Route::get('/manage/report/site_inspections', '\App\Http\Controllers\Misc\ReportController@siteInspections');
    Route::get('/manage/report/site_inspections/dt/list', '\App\Http\Controllers\Misc\ReportController@getSiteInspections');
    // Report - Tasks
    Route::get('/manage/report/todo', '\App\Http\Controllers\Misc\ReportTasksController@todo');
    Route::get('/manage/report/todo/tasks', '\App\Http\Controllers\Misc\ReportTasksController@todoTasks');
    Route::get('/manage/report/todo_inactive', '\App\Http\Controllers\Misc\ReportTasksController@todoInactive');
    Route::get('/manage/report/todo_inactive/tasks', '\App\Http\Controllers\Misc\ReportTasksController@todoTasksInactive');
    Route::post('/manage/report/todo_inactive/delete', '\App\Http\Controllers\Misc\ReportTasksController@todoTasksDelete');
    Route::post('/manage/report/todo_inactive/reassign', '\App\Http\Controllers\Misc\ReportTasksController@todoTasksReassign');


    // User Docs
    Route::get('user/{uid}/doc/dt/docs', '\App\Http\Controllers\User\UserDocController@getDocs');
    Route::get('user/{uid}/doc/upload', '\App\Http\Controllers\User\UserDocController@create');
    Route::post('user/{uid}/doc/reject/{id}', '\App\Http\Controllers\User\UserDocController@reject');
    Route::get('user/{uid}/doc/archive/{id}', '\App\Http\Controllers\User\UserDocController@archive');
    //Route::delete('user/{uid}/doc/{id}', '\App\Http\Controllers\User\UserDocController@destroy');
    //Route::get('user/{uid}/doc/cats/{department}', '\App\Http\Controllers\User\UserDocController@getCategories');
    Route::resource('user/{uid}/doc', '\App\Http\Controllers\User\UserDocController');

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
    Route::resource('user', '\App\Http\Controllers\UserController');

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

    // Company Docs
    Route::any('company/doc/export', '\App\Http\Controllers\Company\CompanyExportController@exportDocs');
    Route::post('company/doc/export/pdf', '\App\Http\Controllers\Company\CompanyExportController@docsPDF');
    Route::get('company/doc/create/tradecontract/{id}/{version}', '\App\Http\Controllers\Company\CompanyExportController@tradecontractPDF');
    Route::get('company/doc/create/subcontractorstatement/{id}/{version}', '\App\Http\Controllers\Company\CompanyExportController@subcontractorstatementPDF');
    Route::resource('company/doc', '\App\Http\Controllers\Company\CompanyDocController');

    // Company Docs
    Route::get('company/{cid}/doc/dt/docs', '\App\Http\Controllers\Company\CompanyDocController@getDocs');
    Route::get('company/{cid}/doc/upload', '\App\Http\Controllers\Company\CompanyDocController@create');
    Route::post('company/{cid}/doc/reject/{id}', '\App\Http\Controllers\Company\CompanyDocController@reject');
    Route::get('company/{cid}/doc/archive/{id}', '\App\Http\Controllers\Company\CompanyDocController@archive');
    Route::get('company/{cid}/doc/cats/{department}', '\App\Http\Controllers\Company\CompanyDocController@getCategories');
    Route::resource('company/{cid}/doc', '\App\Http\Controllers\Company\CompanyDocController');

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
    //Route::post('company/{id}/settings/logo', '\App\Http\Controllers\Company\CompanyController@updateLogo');
    //Route::post('company/{id}/edit/logo', '\App\Http\Controllers\Company\CompanyController@updateLogo');
    Route::get('company/{id}/demo1', '\App\Http\Controllers\Company\CompanyController@demo1');
    Route::get('company/{id}/demo2', '\App\Http\Controllers\Company\CompanyController@demo2');
    Route::get('company/{id}/demo3', '\App\Http\Controllers\Company\CompanyController@demo3');
    Route::get('company/{id}/demo4', '\App\Http\Controllers\Company\CompanyController@demo4');
    Route::resource('company', '\App\Http\Controllers\Company\CompanyController');


    // Client Planner Email
    Route::get('client/planner/email/dt/list', '\App\Http\Controllers\Client\ClientPlannerEmailController@getEmails');
    Route::get('client/planner/email/{id}/status/{status}', '\App\Http\Controllers\Client\ClientPlannerEmailController@updateStatus');
    Route::get('client/planner/email/{id}/check_docs', '\App\Http\Controllers\Client\ClientPlannerEmailController@checkDocs');
    Route::get('client/planner/email/createfields/{site_id}', '\App\Http\Controllers\Client\ClientPlannerEmailController@getCreatefields');
    Route::resource('client/planner/email', '\App\Http\Controllers\Client\ClientPlannerEmailController');

    // Client Routes
    Route::get('client/dt/clients', '\App\Http\Controllers\Client\ClientController@getClients');
    Route::get('client/{slug}/settings', '\App\Http\Controllers\Client\ClientController@showSettings');
    Route::get('client/{slug}/settings/{tab}', '\App\Http\Controllers\Client\ClientController@showSettings');
    Route::resource('client', '\App\Http\Controllers\Client\ClientController');

    // File Manager
    Route::get('/manage/file', '\App\Http\Controllers\Misc\FileController@index');
    Route::get('/manage/file/directory', '\App\Http\Controllers\Misc\FileController@fileDirectory');
    Route::get('manage/file/directory/dt/docs', '\App\Http\Controllers\Misc\FileController@getDocs');

    // Site Project Supply
    Route::get('site/supply/dt/list', '\App\Http\Controllers\Site\SiteProjectSupplyController@getReports');
    Route::get('site/supply/settings', '\App\Http\Controllers\Site\SiteProjectSupplyController@settings');
    Route::post('site/supply/settings', '\App\Http\Controllers\Site\SiteProjectSupplyController@updateSettings');
    Route::get('site/supply/{id}/create', '\App\Http\Controllers\Site\SiteProjectSupplyController@createItem');
    Route::get('site/supply/delete/{id}', '\App\Http\Controllers\Site\SiteProjectSupplyController@deleteItem');
    Route::get('site/supply/{id}/createpdf', '\App\Http\Controllers\Site\SiteProjectSupplyController@createPDF');
    Route::get('site/supply/{id}/signoff', '\App\Http\Controllers\Site\SiteProjectSupplyController@signoff');
    Route::resource('site/supply', '\App\Http\Controllers\Site\SiteProjectSupplyController');

    // Upcoming Compliance
    Route::post('site/upcoming/compliance/update_job', '\App\Http\Controllers\Site\SiteUpcomingComplianceController@updateJob');
    Route::get('site/upcoming/compliance/settings', '\App\Http\Controllers\Site\SiteUpcomingComplianceController@settings');
    Route::post('site/upcoming/compliance/settings', '\App\Http\Controllers\Site\SiteUpcomingComplianceController@updateSettings');
    Route::get('site/upcoming/compliance/settings/del/{id}', '\App\Http\Controllers\Site\SiteUpcomingComplianceController@deleteSetting');
    Route::get('site/upcoming/compliance/pdf', '\App\Http\Controllers\Site\SiteUpcomingComplianceController@showPDF');
    Route::post('site/upcoming/compliance/pdf', '\App\Http\Controllers\Site\SiteUpcomingComplianceController@createPDF');
    //Route::get('manage/report/upcoming_compliance/{id}/createpdf', '\App\Http\Controllers\Site\SiteUpcomingComplianceController@createPDF');
    Route::resource('site/upcoming/compliance', '\App\Http\Controllers\Site\SiteUpcomingComplianceController');

    // Site Extension
    Route::post('site/extension/update_job', '\App\Http\Controllers\Site\SiteExtensionController@updateJob');
    Route::get('site/extension/past', '\App\Http\Controllers\Site\SiteExtensionController@past');
    Route::get('site/extension/settings', '\App\Http\Controllers\Site\SiteExtensionController@settings');
    Route::post('site/extension/settings', '\App\Http\Controllers\Site\SiteExtensionController@updateSettings');
    Route::get('site/extension/site/{id}/delete', '\App\Http\Controllers\Site\SiteExtensionController@deleteSiteExtension');
    Route::get('site/extension/{id}/signoff', '\App\Http\Controllers\Site\SiteExtensionController@signoff');
    Route::get('site/extension/{id}/pdf', '\App\Http\Controllers\Site\SiteExtensionController@createPDF');
    Route::get('site/extension/{id}/{supervisor_id}', '\App\Http\Controllers\Site\SiteExtensionController@showExtensions');
    Route::resource('site/extension', '\App\Http\Controllers\Site\SiteExtensionController');

    // Site Notes
    //Route::post('site/extension/update_job', '\App\Http\Controllers\Site\SiteExtensionController@updateJob');
    Route::get('site/{site_id}/notes', '\App\Http\Controllers\Site\SiteNoteController@showSiteNotes');
    Route::get('site/{site_id}/notes/create', '\App\Http\Controllers\Site\SiteNoteController@createNote');
    Route::get('site/{id}/notes/convert', '\App\Http\Controllers\Site\SiteNoteController@createNoteFrom');
    Route::get('site/note/dt/list', '\App\Http\Controllers\Site\SiteNoteController@getNotes');
    Route::get('site/note/settings', '\App\Http\Controllers\Site\SiteNoteController@settings');
    Route::post('site/note/settings', '\App\Http\Controllers\Site\SiteNoteController@updateSettings');
    Route::get('site/note/settings/cost-centres', '\App\Http\Controllers\Site\SiteNoteController@costCentres');
    Route::post('site/note/settings/cost-centres', '\App\Http\Controllers\Site\SiteNoteController@updateCostCentres');
    //Route::get('site/note/{id}/signoff', '\App\Http\Controllers\Site\SiteNoteController@signoff');
    //Route::get('site/note/{id}/pdf', '\App\Http\Controllers\Site\SiteNoteController@createPDF');
    Route::resource('site/note', '\App\Http\Controllers\Site\SiteNoteController');

    // Site Hazards
    Route::get('site/hazard/dt/hazards', '\App\Http\Controllers\Site\SiteHazardController@getHazards');
    Route::get('site/hazard/{id}/status/{status}', '\App\Http\Controllers\Site\SiteHazardController@updateStatus');
    Route::resource('site/hazard', '\App\Http\Controllers\Site\SiteHazardController');
    //Route::resource('site/hazard-action', '\App\Http\Controllers\Site\SiteHazardActionController');

    // Site Accidents
    Route::get('site/accident/dt/accidents', '\App\Http\Controllers\Site\SiteAccidentController@getAccidents');
    Route::resource('site/accident', '\App\Http\Controllers\Site\SiteAccidentController');

    // Site Incidents - People/Witness/Conversation
    Route::resource('site/incident/{id}/people', '\App\Http\Controllers\Site\Incident\SiteIncidentPeopleController');
    Route::resource('site/incident/{id}/witness', '\App\Http\Controllers\Site\Incident\SiteIncidentWitnessController');
    Route::resource('site/incident/{id}/conversation', '\App\Http\Controllers\Site\Incident\SiteIncidentConversationController');

    // Site Incidents
    Route::get('site/incident/dt/incidents', '\App\Http\Controllers\Site\Incident\SiteIncidentController@getIncidents');
    Route::any('site/incident/upload', '\App\Http\Controllers\Site\Incident\SiteIncidentController@uploadAttachment');
    //Route::get('site/incident/{id}/createdocs', '\App\Http\Controllers\Site\Incident\SiteIncidentController@createDocs');
    Route::any('site/incident/{id}/lodge', '\App\Http\Controllers\Site\Incident\SiteIncidentController@lodge');
    //Route::get('site/incident/{id}/involved', '\App\Http\Controllers\Site\Incident\SiteIncidentController@showInvolved');
    Route::get('site/incident/{id}/admin', '\App\Http\Controllers\Site\Incident\SiteIncidentController@showAdmin');
    Route::post('site/incident/{id}/injury', '\App\Http\Controllers\Site\Incident\SiteIncidentController@updateInjury');
    Route::post('site/incident/{id}/damage', '\App\Http\Controllers\Site\Incident\SiteIncidentController@updateDamage');
    Route::post('site/incident/{id}/details', '\App\Http\Controllers\Site\Incident\SiteIncidentController@updateDetails');
    Route::post('site/incident/{id}/regulator', '\App\Http\Controllers\Site\Incident\SiteIncidentController@updateRegulator');
    Route::post('site/incident/{id}/review', '\App\Http\Controllers\Site\Incident\SiteIncidentController@updateReview');
    Route::post('site/incident/{id}/add_note', '\App\Http\Controllers\Site\Incident\SiteIncidentController@addNote');
    Route::get('site/incident/{id}/add_docs', '\App\Http\Controllers\Site\Incident\SiteIncidentController@addDocs');
    Route::post('site/incident/{id}/add_review', '\App\Http\Controllers\Site\Incident\SiteIncidentController@addReview');
    Route::post('site/incident/{id}/signoff', '\App\Http\Controllers\Site\Incident\SiteIncidentController@signoff');
    Route::post('site/incident/{id}/investigation', '\App\Http\Controllers\Site\Incident\SiteIncidentController@updateInvestigation');
    Route::get('site/incident/{id}/report', '\App\Http\Controllers\Site\Incident\SiteIncidentController@reportPDF');
    Route::get('site/incident/{id}/zip', '\App\Http\Controllers\Site\Incident\SiteIncidentController@reportZIP');
    //Route::post('site/incident/{id}/delete', '\App\Http\Controllers\Site\Incident\SiteIncidentController@delete');
    // Analysis
    Route::get('site/incident/{id}/analysis', '\App\Http\Controllers\Site\Incident\SiteIncidentAnalysisController@show');
    Route::post('site/incident/{id}/conditions', '\App\Http\Controllers\Site\Incident\SiteIncidentAnalysisController@updateConditions');
    Route::post('site/incident/{id}/confactors', '\App\Http\Controllers\Site\Incident\SiteIncidentAnalysisController@updateConfactors');
    Route::post('site/incident/{id}/rootcause', '\App\Http\Controllers\Site\Incident\SiteIncidentAnalysisController@updateRootcause');
    Route::post('site/incident/{id}/prevent', '\App\Http\Controllers\Site\Incident\SiteIncidentAnalysisController@updatePrevent');
    Route::resource('site/incident', '\App\Http\Controllers\Site\Incident\SiteIncidentController');


    // Site Compliance
    Route::resource('site/compliance', '\App\Http\Controllers\Site\Planner\SiteComplianceController');

    // Site Docs
    Route::get('site/doc/type/{type}', '\App\Http\Controllers\Site\SiteDocController@listDocs');
    Route::any('site/doc/plan/create', '\App\Http\Controllers\Site\SiteDocController@createPlan');
    Route::get('site/doc/type/dt/{type}', '\App\Http\Controllers\Site\SiteDocController@getDocsType');
    Route::get('site/doc/dt/docs', '\App\Http\Controllers\Site\SiteDocController@getDocs');
    Route::any('site/doc/create', '\App\Http\Controllers\Site\SiteDocController@create');
    Route::any('site/doc/upload', '\App\Http\Controllers\Site\SiteDocController@upload');
    Route::resource('site/doc', '\App\Http\Controllers\Site\SiteDocController');

    // Site QA Categories
    Route::get('site/categories/qa/dt/qa_cats', '\App\Http\Controllers\Site\SiteQaCategoryController@getQaCategories');
    Route::resource('site/qa/category', '\App\Http\Controllers\Site\SiteQaCategoryController');

    // Site Quality Assurance
    Route::get('site/qa/sigonoff', '\App\Http\Controllers\Site\SiteQaController@listSignoff');
    Route::get('site/qa/{id}/items', '\App\Http\Controllers\Site\SiteQaController@getItems');
    Route::any('site/qa/{id}/update', '\App\Http\Controllers\Site\SiteQaController@updateReport');
    Route::any('site/qa/item/{id}', '\App\Http\Controllers\Site\SiteQaController@updateItem');
    Route::get('site/qa/company/{task_id}', '\App\Http\Controllers\Site\SiteQaController@getCompaniesForTask');
    Route::get('site/qa/upcoming/{super_id}', '\App\Http\Controllers\Site\SiteQaController@upcoming');
    Route::get('site/qa/trigger/{master_id}/{site_id}', '\App\Http\Controllers\Site\SiteQaController@triggerQa');
    Route::get('site/qa/dt/qa_reports', '\App\Http\Controllers\Site\SiteQaController@getQaReports');
    Route::get('site/qa/dt/qa_templates', '\App\Http\Controllers\Site\SiteQaController@getQaTemplates');
    //Route::get('site/qa/dt/qa_upcoming', '\App\Http\Controllers\Site\SiteQaController@getQaUpcoming');
    Route::get('site/qa/templates', '\App\Http\Controllers\Site\SiteQaController@templates');
    Route::resource('site/qa', '\App\Http\Controllers\Site\SiteQaController');

    // Site Maintenance Categories
    Route::get('site/categories/maintenance/dt/main_cats', '\App\Http\Controllers\Site\SiteMaintenanceCategoryController@getMainCategories');
    Route::resource('site/maintenance/category', '\App\Http\Controllers\Site\SiteMaintenanceCategoryController');

    // Site Maintenance
    Route::get('site/maintenance/{id}/items', '\App\Http\Controllers\Site\SiteMaintenanceController@getItems');
    Route::any('site/maintenance/{id}/additem', '\App\Http\Controllers\Site\SiteMaintenanceController@addItem');
    Route::any('site/maintenance/{id}/delitem', '\App\Http\Controllers\Site\SiteMaintenanceController@delItem');
    Route::any('site/maintenance/{id}/update', '\App\Http\Controllers\Site\SiteMaintenanceController@updateReport');
    Route::get('site/maintenance/{id}/delfile/{doc_id}', '\App\Http\Controllers\Site\SiteMaintenanceController@deleteAttachment');
    Route::any('site/maintenance/item/{id}', '\App\Http\Controllers\Site\SiteMaintenanceController@updateItem');
    Route::get('site/maintenance/dt/maintenance', '\App\Http\Controllers\Site\SiteMaintenanceController@getMaintenance');
    Route::any('site/maintenance/upload', '\App\Http\Controllers\Site\SiteMaintenanceController@uploadAttachment');
    Route::get('site/maintenance/data/prac_completion/{site_id}', '\App\Http\Controllers\Site\SiteMaintenanceController@getPracCompletion');
    Route::get('site/maintenance/data/site_super/{site_id}', '\App\Http\Controllers\Site\SiteMaintenanceController@getSiteSupervisor');
    Route::any('site/maintenance/{id}/review', '\App\Http\Controllers\Site\SiteMaintenanceController@review');
    Route::resource('site/maintenance', '\App\Http\Controllers\Site\SiteMaintenanceController');

    // Site Prac Completion
    Route::get('site/prac-completion/{id}/items', '\App\Http\Controllers\Site\SitePracCompletionController@getItems');
    Route::any('site/prac-completion/{id}/additem', '\App\Http\Controllers\Site\SitePracCompletionController@addItem');
    Route::any('site/prac-completion/{id}/delitem', '\App\Http\Controllers\Site\SitePracCompletionController@delItem');
    Route::any('site/prac-completion/{id}/update', '\App\Http\Controllers\Site\SitePracCompletionController@updateReport');
    Route::get('site/prac-completion/{id}/delfile/{doc_id}', '\App\Http\Controllers\Site\SitePracCompletionController@deleteAttachment');
    Route::any('site/prac-completion/item/{id}', '\App\Http\Controllers\Site\SitePracCompletionController@updateItem');
    Route::get('site/prac-completion/dt/prac', '\App\Http\Controllers\Site\SitePracCompletionController@getPrac');
    Route::any('site/prac-completion/upload', '\App\Http\Controllers\Site\SitePracCompletionController@uploadAttachment');
    Route::get('site/prac-completion/data/prac_completion/{site_id}', '\App\Http\Controllers\Site\SitePracCompletionController@getPracCompletion');
    Route::get('site/prac-completion/data/site_super/{site_id}', '\App\Http\Controllers\Site\SitePracCompletionController@getSiteSupervisor');
    Route::any('site/prac-completion/{id}/review', '\App\Http\Controllers\Site\SitePracCompletionController@review');
    Route::resource('site/prac-completion', '\App\Http\Controllers\Site\SitePracCompletionController');


    // Site Asbestos Register
    Route::get('site/asbestos/register/dt/list', '\App\Http\Controllers\Site\SiteAsbestosRegisterController@getReports');
    Route::get('site/asbestos/register/{id}/create', '\App\Http\Controllers\Site\SiteAsbestosRegisterController@createItem');
    Route::get('site/asbestos/register/delete/{id}', '\App\Http\Controllers\Site\SiteAsbestosRegisterController@deleteItem');
    Route::get('site/asbestos/register/{id}/createpdf', '\App\Http\Controllers\Site\SiteAsbestosRegisterController@createPDF');
    Route::get('site/asbestos/register/{id}/destroy', '\App\Http\Controllers\Site\SiteAsbestosRegisterController@destroy');
    Route::resource('site/asbestos/register', '\App\Http\Controllers\Site\SiteAsbestosRegisterController');

    // Site Asbestos Notification
    Route::get('site/asbestos/notification/dt/list', '\App\Http\Controllers\Site\SiteAsbestosController@getReports');
    Route::get('site/asbestos/notification/{id}/status/{status}', '\App\Http\Controllers\Site\SiteAsbestosController@updateStatus');
    Route::any('site/asbestos/notification/{id}/extra', '\App\Http\Controllers\Site\SiteAsbestosController@updateExtra');
    Route::resource('site/asbestos/notification', '\App\Http\Controllers\Site\SiteAsbestosController');

    // Site Inspection Electrical Register
    Route::get('site/inspection/electrical/dt/list', '\App\Http\Controllers\Site\SiteInspectionElectricalController@getInspections');
    Route::any('site/inspection/electrical/upload', '\App\Http\Controllers\Site\SiteInspectionElectricalController@uploadAttachment');
    Route::any('site/inspection/electrical/{id}/docs', '\App\Http\Controllers\Site\SiteInspectionElectricalController@documents');
    Route::get('site/inspection/electrical/{id}/report', '\App\Http\Controllers\Site\SiteInspectionElectricalController@reportPDF');
    Route::get('site/inspection/electrical/{id}/status/{status}', '\App\Http\Controllers\Site\SiteInspectionElectricalController@updateStatus');
    Route::get('site/inspection/electrical/{id}/delfile/{doc_id}', '\App\Http\Controllers\Site\SiteInspectionElectricalController@deleteAttachment');
    Route::post('site/inspection/electrical/{id}/signoff', '\App\Http\Controllers\Site\SiteInspectionElectricalController@signoff');
    Route::resource('site/inspection/electrical', '\App\Http\Controllers\Site\SiteInspectionElectricalController');

    // Site Inspection Plumbing Register
    Route::get('site/inspection/plumbing/dt/list', '\App\Http\Controllers\Site\SiteInspectionPlumbingController@getInspections');
    Route::any('site/inspection/plumbing/upload', '\App\Http\Controllers\Site\SiteInspectionPlumbingController@uploadAttachment');
    Route::any('site/inspection/plumbing/{id}/docs', '\App\Http\Controllers\Site\SiteInspectionPlumbingController@documents');
    Route::get('site/inspection/plumbing/{id}/report', '\App\Http\Controllers\Site\SiteInspectionPlumbingController@reportPDF');
    Route::get('site/inspection/plumbing/{id}/status/{status}', '\App\Http\Controllers\Site\SiteInspectionPlumbingController@updateStatus');
    Route::get('site/inspection/plumbing/{id}/delfile/{doc_id}', '\App\Http\Controllers\Site\SiteInspectionPlumbingController@deleteAttachment');
    Route::post('site/inspection/plumbing/{id}/signoff', '\App\Http\Controllers\Site\SiteInspectionPlumbingController@signoff');
    Route::resource('site/inspection/plumbing', '\App\Http\Controllers\Site\SiteInspectionPlumbingController');

    // Site Scaffold Handover
    Route::get('site/scaffold/handover/dt/list', '\App\Http\Controllers\Site\SiteScaffoldHandoverController@getCertificates');
    Route::any('site/scaffold/handover/upload', '\App\Http\Controllers\Site\SiteScaffoldHandoverController@uploadAttachment');
    Route::get('site/scaffold/handover/create/{site_id}', '\App\Http\Controllers\Site\SiteScaffoldHandoverController@create');
    Route::any('site/scaffold/handover/{id}/docs', '\App\Http\Controllers\Site\SiteScaffoldHandoverController@documents');
    Route::get('site/scaffold/handover/{id}/report', '\App\Http\Controllers\Site\SiteScaffoldHandoverController@reportPDF');
    Route::post('site/scaffold/handover/{id}/report', '\App\Http\Controllers\Site\SiteScaffoldHandoverController@emailPDF');
    Route::resource('site/scaffold/handover', '\App\Http\Controllers\Site\SiteScaffoldHandoverController');

    // Report Actions
    Route::get('report/actions/{type}/{id}', '\App\Http\Controllers\Misc\ReportActionController@index');
    Route::post('report/actions/{type}/{id}', '\App\Http\Controllers\Misc\ReportActionController@store');
    Route::patch('report/actions/{type}/{id}', '\App\Http\Controllers\Misc\ReportActionController@update');

    Route::get('action/{table}/{table_id}', '\App\Http\Controllers\Misc\ActionController@index');
    Route::resource('action', '\App\Http\Controllers\Misc\ActionController');

    // Categories
    Route::get('category/del/{id}', '\App\Http\Controllers\Misc\CategoryController@deleteCat');
    Route::get('category/order/{direction}/{id}', '\App\Http\Controllers\Misc\CategoryController@updateOrder');
    Route::resource('category', '\App\Http\Controllers\Misc\CategoryController');

    // Site Supervisors
    Route::get('site/supervisor/data/supers', '\App\Http\Controllers\Company\CompanySupervisorController@getSupers');
    Route::resource('site/supervisor', '\App\Http\Controllers\Company\CompanySupervisorController');

    // Site Attendance
    Route::get('/site/attendance/dt/attendance', '\App\Http\Controllers\Site\SiteAttendanceController@getAttendance');
    Route::resource('/site/attendance', '\App\Http\Controllers\Site\SiteAttendanceController');

    // Form Template
    Route::get('form/template/dt/templates', '\App\Http\Controllers\Misc\Form\FormTemplateController@getTemplates');
    Route::post('form/template/data/save', '\App\Http\Controllers\Misc\Form\FormTemplateController@saveTemplate');
    Route::get('form/template/data/template/{template_id}', '\App\Http\Controllers\Misc\Form\FormTemplateController@getTemplate');
    Route::resource('form/template', '\App\Http\Controllers\Misc\Form\FormTemplateController');

    // Site Inspection Forms
    Route::get('site/inspection/dt/forms', '\App\Http\Controllers\Misc\Form\FormController@getForms');
    //Route::get('site/inspection/dt/{template_id}', '\App\Http\Controllers\Misc\Form\FormController@getTemplateForms');
    Route::get('site/inspection/list/{template_id}', '\App\Http\Controllers\Misc\Form\FormController@listForms');
    Route::get('site/inspection/create/{template_id}', '\App\Http\Controllers\Misc\Form\FormController@createForm');
    Route::get('site/inspection/{form_id}/{pagenumber}', '\App\Http\Controllers\Misc\Form\FormController@showPage');
    Route::post('site/inspection/upload', '\App\Http\Controllers\Misc\Form\FormController@upload');
    Route::delete('site/inspection/upload', '\App\Http\Controllers\Misc\Form\FormController@deleteUpload');
    Route::resource('site/inspection', '\App\Http\Controllers\Misc\Form\FormController');

    // Supervisor Checklist
    Route::get('supervisor/checklist/dt/list', '\App\Http\Controllers\Misc\SuperChecklistController@getChecklists');
    Route::get('supervisor/checklist/checklist', '\App\Http\Controllers\Misc\SuperChecklistController@checklist');
    Route::get('supervisor/checklist/settings', '\App\Http\Controllers\Misc\SuperChecklistController@settings');
    Route::post('supervisor/checklist/settings', '\App\Http\Controllers\Misc\SuperChecklistController@updateSettings');
    Route::get('supervisor/checklist/past', '\App\Http\Controllers\Misc\SuperChecklistController@pastWeeks');
    Route::get('supervisor/checklist/past/{date}', '\App\Http\Controllers\Misc\SuperChecklistController@pastWeek');
    Route::get('supervisor/checklist/{checklist_id}/weekly', '\App\Http\Controllers\Misc\SuperChecklistController@showSuperWeekly');
    Route::get('supervisor/checklist/{checklist_id}/weekly/signoff', '\App\Http\Controllers\Misc\SuperChecklistController@signoff');
    Route::get('supervisor/checklist/{checklist_id}/{day}', '\App\Http\Controllers\Misc\SuperChecklistController@showResponse');
    Route::resource('supervisor/checklist', '\App\Http\Controllers\Misc\SuperChecklistController');


    // Site Exports
    Route::get('site/export', '\App\Http\Controllers\Site\Planner\SitePlannerExportController@index');
    Route::get('site/export/plan', '\App\Http\Controllers\Site\Planner\SitePlannerExportController@exportPlanner');
    Route::post('site/export/site', '\App\Http\Controllers\Site\Planner\SitePlannerExportController@sitePDF');
    Route::get('site/export/start', '\App\Http\Controllers\Site\Planner\SitePlannerExportController@exportStart');
    Route::post('site/export/start', '\App\Http\Controllers\Site\Planner\SitePlannerExportController@jobstartPDF');
    Route::get('site/export/completion', '\App\Http\Controllers\Site\Planner\SitePlannerExportController@exportCompletion');
    Route::post('site/export/completion', '\App\Http\Controllers\Site\Planner\SitePlannerExportController@completionPDF');
    Route::get('site/export/attendance', '\App\Http\Controllers\Site\Planner\SitePlannerExportController@exportAttendance');
    Route::post('site/export/attendance', '\App\Http\Controllers\Site\Planner\SitePlannerExportController@attendancePDF');
    Route::get('site/export/qa', '\App\Http\Controllers\Site\SiteQaController@exportQA');
    Route::post('site/export/qa', '\App\Http\Controllers\Site\SiteQaController@qaPDF');

    // Site Routes
    Route::get('site/dt/sites', '\App\Http\Controllers\Site\SiteController@getSites');
    Route::get('site/dt/sitelist', '\App\Http\Controllers\Site\SiteController@getSiteList');
    Route::get('sitelist', '\App\Http\Controllers\Site\SiteController@siteList');
    Route::post('site/{id}/admin', '\App\Http\Controllers\Site\SiteController@updateAdmin');
    Route::post('site/{id}/client', '\App\Http\Controllers\Site\SiteController@updateClient');
    Route::get('site/{id}/doc', '\App\Http\Controllers\Site\SiteController@showDocs');
    Route::get('site/{site_id}/supervisor/{super_id}', '\App\Http\Controllers\Site\SiteController@updateSupervisor');
    Route::get('site/{site_id}/jobstart_estimate/{date}', '\App\Http\Controllers\Site\SiteController@updateJobstartEstimate');
    Route::get('site/{site_id}/eworks/{cid}', '\App\Http\Controllers\Site\SiteController@updateEworks');
    Route::get('site/{site_id}/pworks/{cid}', '\App\Http\Controllers\Site\SiteController@updatePworks');
    Route::get('site/{site_id}/whs-management-plan', '\App\Http\Controllers\Site\SiteController@createWhsManagementPlan');
    Route::get('site/data/doc/dt', '\App\Http\Controllers\Site\SiteController@getSiteDocs');
    Route::get('site/data/details/{id}', '\App\Http\Controllers\Site\SiteController@getSiteDetails');
    Route::get('site/data/super/{id}', '\App\Http\Controllers\Site\SiteController@getSiteSuper');
    //Route::get('site/data/owner/{id}', '\App\Http\Controllers\Site\SiteSyncController@getSiteOwner');
    Route::resource('site', '\App\Http\Controllers\Site\SiteController');

    // Trade + Task Routes
    Route::resource('trade', '\App\Http\Controllers\Site\Planner\TradeController');
    Route::resource('task', '\App\Http\Controllers\Site\Planner\TaskController');

    // SDS Safety Docs
    Route::get('safety/doc/dt/sds', '\App\Http\Controllers\Safety\SdsController@getSDS');
    Route::any('safety/doc/sds/create', '\App\Http\Controllers\Safety\SdsController@create');
    Route::any('safety/doc/sds/upload', '\App\Http\Controllers\Safety\SdsController@upload');
    Route::resource('safety/doc/sds', '\App\Http\Controllers\Safety\SdsController');

    // Contruction Docs
    Route::get('construction/doc/dt/standards', '\App\Http\Controllers\Misc\ConstructionDocController@getDocs');
    Route::any('construction/doc/standards', '\App\Http\Controllers\Misc\ConstructionDocController@index');
    Route::any('construction/doc/create', '\App\Http\Controllers\Misc\ConstructionDocController@create');
    Route::any('construction/doc/upload', '\App\Http\Controllers\Misc\ConstructionDocController@upload');
    Route::resource('construction/doc', '\App\Http\Controllers\Misc\ConstructionDocController');

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
    Route::resource('safety/doc/toolbox2', '\App\Http\Controllers\Safety\ToolboxTalkController');


    // Toolbox Talks3
    Route::get('safety/doc/toolbox3', '\App\Http\Controllers\Safety\ToolboxTalk3Controller@index');
    Route::get('safety/doc/toolbox3/{id}/accept', '\App\Http\Controllers\Safety\ToolboxTalk3Controller@accept');
    Route::get('safety/doc/toolbox3/{id}/create', '\App\Http\Controllers\Safety\ToolboxTalk3Controller@createFromTemplate');
    Route::get('safety/doc/toolbox3/{id}/reject', '\App\Http\Controllers\Safety\ToolboxTalk3Controller@reject');
    Route::get('safety/doc/toolbox3/{id}/signoff', '\App\Http\Controllers\Safety\ToolboxTalk3Controller@signoff');
    Route::get('safety/doc/toolbox3/{id}/archive', '\App\Http\Controllers\Safety\ToolboxTalk3Controller@archive');
    Route::get('safety/doc/toolbox3/{id}/destroy', '\App\Http\Controllers\Safety\ToolboxTalk3Controller@destroy');
    Route::get('safety/doc/toolbox3/{id}/pdf', '\App\Http\Controllers\Safety\ToolboxTalk3Controller@createPDF');
    Route::post('safety/doc/toolbox3/{id}/upload', '\App\Http\Controllers\Safety\ToolboxTalk3Controller@uploadMedia');
    Route::get('safety/doc/dt/toolbox3', '\App\Http\Controllers\Safety\ToolboxTalk3Controller@getToolbox');
    Route::get('safety/doc/dt/toolbox3_templates', '\App\Http\Controllers\Safety\ToolboxTalk3Controller@getToolboxTemplates');
    Route::resource('safety/doc/toolbox3', '\App\Http\Controllers\Safety\ToolboxTalk3Controller');

    // Safety Docs - WMS
    Route::get('safety/doc/wms', '\App\Http\Controllers\Safety\WmsController@index');
    Route::get('safety/doc/wms/expired', '\App\Http\Controllers\Safety\WmsController@expired');
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
    Route::resource('safety/doc/wms', '\App\Http\Controllers\Safety\WmsController');


    // Equipment Transfers
    Route::get('equipment/dt/transfers', '\App\Http\Controllers\Misc\EquipmentTransferController@getTransfers');
    Route::get('equipment/{id}/transfer', '\App\Http\Controllers\Misc\EquipmentTransferController@transfer');
    Route::post('equipment/{id}/transfer', '\App\Http\Controllers\Misc\EquipmentTransferController@transferItem');
    Route::get('equipment/{id}/transfer-bulk', '\App\Http\Controllers\Misc\EquipmentTransferController@transferBulk');
    Route::post('equipment/{id}/transfer-bulk', '\App\Http\Controllers\Misc\EquipmentTransferController@transferBulkItems');
    Route::get('equipment/{id}/transfer-verify', '\App\Http\Controllers\Misc\EquipmentTransferController@verifyTransfer');
    Route::get('equipment/{id}/transfer-cancel', '\App\Http\Controllers\Misc\EquipmentTransferController@cancelTransfer');
    Route::post('equipment/{id}/transfer-confirm', '\App\Http\Controllers\Misc\EquipmentTransferController@confirmTransfer');

    // Locations Other
    Route::get('equipment/other-location/dt/other', '\App\Http\Controllers\Misc\EquipmentLocationOtherController@getOther');
    Route::get('equipment/other-location/{id}/delete', '\App\Http\Controllers\Misc\EquipmentLocationOtherController@destroy');
    Route::resource('equipment/other-location', '\App\Http\Controllers\Misc\EquipmentLocationOtherController');

    // Equipment
    Route::get('equipment/dt/allocation', '\App\Http\Controllers\Misc\EquipmentController@getAllocation');
    Route::get('equipment/dt/inventory', '\App\Http\Controllers\Misc\EquipmentController@getInventory');
    Route::get('equipment/dt/missing', '\App\Http\Controllers\Misc\EquipmentController@getMissing');
    Route::get('equipment/dt/log', '\App\Http\Controllers\Misc\EquipmentController@getLog');
    Route::get('equipment/inventory', '\App\Http\Controllers\Misc\EquipmentController@inventory');
    Route::get('equipment/writeoff', '\App\Http\Controllers\Misc\EquipmentController@writeoff');
    Route::post('equipment/writeoff', '\App\Http\Controllers\Misc\EquipmentController@writeoffItems');
    Route::get('equipment/{id}/delete', '\App\Http\Controllers\Misc\EquipmentController@destroy');
    Route::resource('equipment', '\App\Http\Controllers\Misc\EquipmentController');

    // Stocktake
    Route::get('equipment/stocktake/dt/stocktake', '\App\Http\Controllers\Misc\EquipmentStocktakeController@getStocktake');
    Route::get('equipment/stocktake/view/{id}', '\App\Http\Controllers\Misc\EquipmentStocktakeController@showStocktake');
    Route::get('equipment/stocktake/{id}/edit/{tab}', '\App\Http\Controllers\Misc\EquipmentStocktakeController@edit');
    Route::resource('equipment/stocktake', '\App\Http\Controllers\Misc\EquipmentStocktakeController');


    // Configuration
    Route::get('settings', '\App\Http\Controllers\Misc\PagesController@settings');
    Route::get('settings/notifications/{id}/status/{status}', '\App\Http\Controllers\Misc\SettingsNotificationController@updateStatus');
    Route::resource('settings/notifications', '\App\Http\Controllers\Misc\SettingsNotificationController');

    // Roles / Permission
    Route::get('settings/role/permissions', '\App\Http\Controllers\Misc\RoleController@getPermissions');
    Route::get('settings/role/resetpermissions', '\App\Http\Controllers\Misc\PagesController@resetPermissions');
    Route::get('settings/role/child-role/{id}', '\App\Http\Controllers\Misc\RoleController@childRole');
    Route::get('settings/role/child-primary/{id}', '\App\Http\Controllers\Misc\RoleController@childPrimary');
    Route::get('settings/role/child-default/{id}', '\App\Http\Controllers\Misc\RoleController@childDefault');
    Route::get('settings/role/parent', '\App\Http\Controllers\Misc\RoleController@parent');
    Route::get('settings/role/child', '\App\Http\Controllers\Misc\RoleController@child');
    Route::resource('settings/role', '\App\Http\Controllers\Misc\RoleController');


    // Planners
    Route::any('planner/weekly', '\App\Http\Controllers\Site\Planner\SitePlannerController@showWeekly');
    Route::any('planner/site', '\App\Http\Controllers\Site\Planner\SitePlannerController@showSite');
    Route::any('planner/site/{site_id}', '\App\Http\Controllers\Site\Planner\SitePlannerController@showSite');
    Route::any('planner/trade', '\App\Http\Controllers\Site\Planner\SitePlannerController@showTrade');
    Route::any('planner/roster1', '\App\Http\Controllers\Site\Planner\SitePlannerController@showAttendance');
    Route::any('planner/roster', '\App\Http\Controllers\Site\Planner\SitePlannerController@showRoster');
    Route::any('planner/transient', '\App\Http\Controllers\Site\Planner\SitePlannerController@showTransient');
    Route::any('planner/preconstruction', '\App\Http\Controllers\Site\Planner\SitePlannerController@showPreconstruction');
    Route::any('planner/preconstruction/{site_id}', '\App\Http\Controllers\Site\Planner\SitePlannerController@showPreconstruction');
    Route::any('planner/upcoming', '\App\Http\Controllers\Site\Planner\SitePlannerController@showUpcoming');
    Route::any('planner/forecast', '\App\Http\Controllers\Site\Planner\SitePlannerController@showForecast');
    Route::any('planner/site/{site_id}/status/{status}', '\App\Http\Controllers\Site\Planner\SitePlannerController@updateSiteStatus');
    Route::get('planner/data/sites', '\App\Http\Controllers\Site\Planner\SitePlannerController@getSites');
    Route::get('planner/data/site/{site_id}', '\App\Http\Controllers\Site\Planner\SitePlannerController@getSitePlan');
    Route::get('planner/data/site/{site_id}/attendance/{date}', '\App\Http\Controllers\Site\Planner\SitePlannerController@getSiteAttendance');
    Route::get('planner/data/site/{site_id}/allocate/{user_id}', '\App\Http\Controllers\Site\Planner\SitePlannerController@allocateSiteSupervisor');
    Route::get('planner/data/roster/{date}/super/{super_id}', '\App\Http\Controllers\Site\Planner\SitePlannerController@getSiteRoster');
    Route::any('planner/data/roster/user', '\App\Http\Controllers\Site\Planner\SitePlannerController@addUserRoster');
    Route::any('planner/data/roster/user/{id}', '\App\Http\Controllers\Site\Planner\SitePlannerController@delUserRoster');
    Route::any('planner/data/roster/add-company/{cid}/site/{site_id}/date/{date}', '\App\Http\Controllers\Site\Planner\SitePlannerController@addCompanyRoster');
    Route::any('planner/data/roster/del-company/{cid}/site/{site_id}/date/{date}', '\App\Http\Controllers\Site\Planner\SitePlannerController@delCompanyRoster');
    Route::any('planner/data/weekly/{date}/{super_id}', '\App\Http\Controllers\Site\Planner\SitePlannerController@getWeeklyPlan');
    Route::get('planner/data/company/{company_id}/tasks', '\App\Http\Controllers\Site\Planner\SitePlannerController@getCompanyTasks');
    Route::get('planner/data/company/{company_id}/tasks/trade/{trade_id}', '\App\Http\Controllers\Site\Planner\SitePlannerController@getCompanyTasks');
    Route::get('planner/data/company/{company_id}/trades', '\App\Http\Controllers\Site\Planner\SitePlannerController@getCompanyTrades');
    Route::get('planner/data/company/trade/{trade_id}', '\App\Http\Controllers\Site\Planner\SitePlannerController@getCompaniesWithTrade');
    Route::get('planner/data/company/{company_id}/trade/{trade_id}/site/{site_id}', '\App\Http\Controllers\Site\Planner\SitePlannerController@getCompanies');
    Route::get('planner/data/company/{company_id}/site/{site_id}/{date}', '\App\Http\Controllers\Site\Planner\SitePlannerController@getCompanySitesOnDate');
    Route::get('planner/data/trade', '\App\Http\Controllers\Site\Planner\SitePlannerController@getTrades');
    Route::get('planner/data/trade/upcoming/{date}', '\App\Http\Controllers\Site\Planner\SitePlannerController@getUpcomingTasks');
    Route::get('planner/data/trade/{trade_id}/tasks', '\App\Http\Controllers\Site\Planner\SitePlannerController@getTradeTasks');
    Route::get('planner/data/trade/jobstarts/{exists}', '\App\Http\Controllers\Site\Planner\SitePlannerController@getJobStarts');
    Route::get('planner/data/trade/joballocate', '\App\Http\Controllers\Site\Planner\SitePlannerController@getSitesWithoutSuper');
    Route::any('planner/data/trade/email-jobstart', '\App\Http\Controllers\Site\Planner\SitePlannerController@emailJobstart');
    Route::any('planner/data/upcoming', '\App\Http\Controllers\Site\Planner\SitePlannerController@getUpcoming');
    Route::resource('planner', '\App\Http\Controllers\Site\Planner\SitePlannerController');

    // Support Tickets
    Route::get('support/ticket/dt/tickets', '\App\Http\Controllers\Support\SupportTicketController@getTickets');
    Route::get('support/ticket/dt/upgrades', '\App\Http\Controllers\Support\SupportTicketController@getUpgrades');
    Route::get('support/ticket/create', '\App\Http\Controllers\Support\SupportTicketController@create');
    Route::post('support/ticket/action', '\App\Http\Controllers\Support\SupportTicketController@addAction');
    //Route::post('support/ticket/upload', '\App\Http\Controllers\Support\SupportTicketController@upload');
    Route::get('support/ticket/{id}/eta/{date}', '\App\Http\Controllers\Support\SupportTicketController@updateETA');
    Route::get('support/ticket/{id}/status/{status}', '\App\Http\Controllers\Support\SupportTicketController@updateStatus');
    Route::get('support/ticket/{id}/hours/{hours}', '\App\Http\Controllers\Support\SupportTicketController@updateHours');
    Route::get('support/ticket/{id}/priority/{priority}', '\App\Http\Controllers\Support\SupportTicketController@updatePriority');
    Route::get('support/ticket/{id}/assigned/{assigned}', '\App\Http\Controllers\Support\SupportTicketController@updateAssigned');
    Route::resource('support/ticket', '\App\Http\Controllers\Support\SupportTicketController');

    // Support Hours
    Route::get('support/hours/update', '\App\Http\Controllers\Support\SupportHourController@updateHours');
    Route::get('support/hours/load', '\App\Http\Controllers\Support\SupportHourController@getHours');
    Route::post('support/hours/save', '\App\Http\Controllers\Support\SupportHourController@saveHours');
    Route::resource('support/hours', '\App\Http\Controllers\Support\SupportHourController');

    // Temp File Uploads
    Route::post('file/upload', '\App\Http\Controllers\Misc\FileUploadController@upload');

    // Comms
    Route::get('todo/dt/todo', '\App\Http\Controllers\Comms\TodoController@getTodo');
    Route::get('todo/create/{type}/{type_id}', '\App\Http\Controllers\Comms\TodoController@createType');
    Route::get('todo/{id}/delete', '\App\Http\Controllers\Comms\TodoController@destroy');
    Route::resource('todo', '\App\Http\Controllers\Comms\TodoController');
    Route::get('comms/notify/dt/notify', '\App\Http\Controllers\Comms\NotifyController@getNotify');
    Route::resource('comms/notify', '\App\Http\Controllers\Comms\NotifyController');
    Route::get('safety/tip/active', '\App\Http\Controllers\Comms\TipController@getActive');
    Route::resource('safety/tip', '\App\Http\Controllers\Comms\TipController');

    // Mailgun Zoho Import
    //Route::get('zoho/import/{file}', 'Api\MailgunZohoController@parseFile');

    // PDF
    Route::get('pdf/test', '\App\Http\Controllers\Misc\PdfController@test');
    Route::get('pdf/workmethod/{id}', '\App\Http\Controllers\Misc\PdfController@workmethod');
    Route::get('pdf/planner/site/{site_id}/{date}/{weeks}', '\App\Http\Controllers\Misc\PdfController@plannerSite');

    // Fudge
    Route::get('userlog', '\App\Http\Controllers\Misc\PagesController@userlog');
    Route::post('userlog', '\App\Http\Controllers\Misc\PagesController@userlogAuth');

});

// Cron routes
Route::get('cron/nightly', '\App\Http\Controllers\Misc\CronController@nightly');
Route::get('cron/nightly-verify', '\App\Http\Controllers\Misc\CronController@verifyNightly');
Route::get('cron/nightly-report', '\App\Http\Controllers\Misc\CronReportController@nightly');
Route::get('cron/blessing', '\App\Http\Controllers\Misc\CronController@blessing');
Route::get('cron/roster', '\App\Http\Controllers\Misc\CronController@roster');
Route::get('cron/qa', '\App\Http\Controllers\Misc\CronController@qa');
Route::get('cron/overdue-todo', '\App\Http\Controllers\Misc\CronController@overdueToDo');
Route::get('cron/expired-companydoc', '\App\Http\Controllers\Misc\CronController@expiredCompanyDoc');
Route::get('cron/expired-standarddoc', '\App\Http\Controllers\Misc\CronController@expiredStandardDetailsDoc');
Route::get('cron/expired-swms', '\App\Http\Controllers\Misc\CronController@expiredSWMS');
Route::get('cron/archive-toolbox', '\App\Http\Controllers\Misc\CronController@archiveToolbox');
Route::get('cron/email-jobstart', '\App\Http\Controllers\Misc\CronReportController@emailJobstart');
Route::get('cron/email-upcomingjob', '\App\Http\Controllers\Misc\CronReportController@emailUpcomingJobCompilance');
Route::get('cron/email-fortnight', '\App\Http\Controllers\Misc\CronReportController@emailFortnightlyReports');
Route::get('cron/email-supervisor-attendance', '\App\Http\Controllers\Misc\CronReportController@emailSupervisorAttendance');
Route::get('cron/email-scaffold', '\App\Http\Controllers\Misc\CronReportController@emailScaffoldOverdue');
Route::get('cron/email-equipment-transfers', '\App\Http\Controllers\Misc\CronReportController@emailEquipmentTransfers');
Route::get('cron/email-equipment-restock', '\App\Http\Controllers\Misc\CronReportController@emailEquipmentRestock');
Route::get('cron/email-outstanding-qa', '\App\Http\Controllers\Misc\CronReportController@emailOutstandingQA');
Route::get('cron/email-onhold-qa', '\App\Http\Controllers\Misc\CronReportController@emailOnHoldQA');
Route::get('cron/email-electrical-plumbing', '\App\Http\Controllers\Misc\CronReportController@emailActiveElectricalPlumbing');
Route::get('cron/email-maintenance-executive', '\App\Http\Controllers\Misc\CronReportController@emailMaintenanceExecutive');
Route::get('cron/email-maintenance-aftercare', '\App\Http\Controllers\Misc\CronReportController@emailOutstandingAftercare');
Route::get('cron/email-maintenance-appointment', '\App\Http\Controllers\Misc\CronReportController@emailMaintenanceAppointment');
Route::get('cron/email-maintenance-under-review', '\App\Http\Controllers\Misc\CronReportController@emailMaintenanceUnderReview');
Route::get('cron/email-maintenance-supervisor-noaction', '\App\Http\Controllers\Misc\CronReportController@emailMaintenanceSupervisorNoAction');
Route::get('cron/email-outstanding-privacy', '\App\Http\Controllers\Misc\CronReportController@emailOutstandingPrivacy');
Route::get('cron/email-oldusers', '\App\Http\Controllers\Misc\CronReportController@emailOldUsers');
Route::get('cron/email-missing-company-info', '\App\Http\Controllers\Misc\CronReportController@emailMissingCompanyInfo');
Route::get('cron/email-planner-key-tasks', '\App\Http\Controllers\Misc\CronController@emailPlannerKeyTasks');
Route::get('cron/email-asbestos', '\App\Http\Controllers\Misc\CronReportController@emailActiveAsbestos');
Route::get('cron/action-planner-key-tasks', '\App\Http\Controllers\Misc\CronController@actionPlannerKeyTasks');
Route::get('cron/site-extensions', '\App\Http\Controllers\Misc\CronController@siteExtensions');
Route::get('cron/site-extensions-supervisor-task', '\App\Http\Controllers\Misc\CronController@siteExtensionsSupervisorTask');
Route::get('cron/site-extensions-supervisor-reminder', '\App\Http\Controllers\Misc\CronController@siteExtensionsSupervisorTaskReminder');
Route::get('cron/site-extensions-supervisor-final-reminder', '\App\Http\Controllers\Misc\CronController@siteExtensionsSupervisorTaskFinalReminder');
Route::get('cron/upload-companydocs', '\App\Http\Controllers\Misc\CronController@uploadCompanyDocReminder');
Route::get('cron/super-checklists', '\App\Http\Controllers\Misc\CronController@superChecklists');
Route::get('cron/rogue-todo', '\App\Http\Controllers\Misc\CronController@rogueToDo');


// Import Data Routes
Route::get('manage/import-payroll', '\App\Http\Controllers\Misc\PagesImportController@importPayroll');
Route::get('manage/import-maintenance', '\App\Http\Controllers\Misc\PagesImportController@importMaintenance');
Route::get('manage/import-supers', '\App\Http\Controllers\Misc\PagesImportController@importSiteSupervivors');
Route::get('manage/import-questions', '\App\Http\Controllers\Misc\PagesImportController@importQuestions');
Route::get('manage/import-time-extensions', '\App\Http\Controllers\Misc\PagesController@importTimeExtensions');
Route::get('manage/initchecklist', '\App\Http\Controllers\Misc\PagesImportController@initSuperChecklist');
Route::get('manage/newchecklist', '\App\Http\Controllers\Misc\PagesImportController@newSuperChecklist');
// CCC Routes
Route::get('manage/import-ccc-program', '\App\Http\Controllers\Misc\CccController@importCCCprogram');
Route::get('manage/import-ccc-youth', '\App\Http\Controllers\Misc\CccController@importCCCyouth');
Route::get('manage/output-ccc-program', '\App\Http\Controllers\Misc\CccController@outputCCCprogram');

// Management Routes
Route::get('test/cal', '\App\Http\Controllers\Misc\PagesController@testcal');
Route::get('test/filepond', '\App\Http\Controllers\Misc\PagesController@testfilepond');
Route::get('test/blankptc/{cid}', '\App\Http\Controllers\Company\CompanyPeriodTradeController@blankPtcPDF');
Route::get('manage/updateroles', '\App\Http\Controllers\Misc\PagesController@updateRoles');

Route::get('manage/triggerQA', '\App\Http\Controllers\Misc\PagesController@triggerQA');
Route::get('test/asbestosreg', '\App\Http\Controllers\Misc\PagesController@asbestosRegister');

// Form Template Setup
Route::get('manage/resetform', '\App\Http\Controllers\Misc\Form\FormSetupController@resetFormTemplate');
Route::get('manage/initform1', '\App\Http\Controllers\Misc\Form\FormSetupController@createFormTemplate1');
Route::get('manage/initform2', '\App\Http\Controllers\Misc\Form\FormSetupController@createFormTemplate2');
Route::get('manage/template/actions/{form_id}', '\App\Http\Controllers\Misc\Form\FormController@completedActionsConstructionWHS');
Route::get('manage/template/{id}', '\App\Http\Controllers\Misc\Form\FormSetupController@showTemplate');


// PHP Info
Route::get('php-info', function () {
    phpinfo();
});

Route::get('test/email', function () {
    return view('emails/blank');
});


