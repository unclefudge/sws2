<?php

use Illuminate\Support\Facades\Route;

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

/*
|--------------------------------------------------------------------------
| REPORTS
|--------------------------------------------------------------------------
*/
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
Route::get('/manage/report/company_swms/email-all', '\App\Http\Controllers\Misc\ReportUserCompanyController@companySWMSEmailAll');
Route::get('/manage/report/company_swms/email-outofdate', '\App\Http\Controllers\Misc\ReportUserCompanyController@companySWMSEmailOutOfDate');
Route::get('/manage/report/company_swms/settings', '\App\Http\Controllers\Misc\ReportUserCompanyController@companySWMSSettings');
Route::post('/manage/report/company_swms/settings', '\App\Http\Controllers\Misc\ReportUserCompanyController@companySWMSSettingsUpdate');
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
Route::get('/manage/report/prac_completion_no_action', '\App\Http\Controllers\Misc\ReportController@pracCompletionNoAction');
Route::get('/manage/report/site_inspections', '\App\Http\Controllers\Misc\ReportController@siteInspections');
Route::get('/manage/report/site_inspections/dt/list', '\App\Http\Controllers\Misc\ReportController@getSiteInspections');
// Report - Tasks
Route::get('/manage/report/todo', '\App\Http\Controllers\Misc\ReportTasksController@todo');
Route::get('/manage/report/todo/tasks', '\App\Http\Controllers\Misc\ReportTasksController@todoTasks');
Route::get('/manage/report/todo_inactive', '\App\Http\Controllers\Misc\ReportTasksController@todoInactive');
Route::get('/manage/report/todo_inactive/tasks', '\App\Http\Controllers\Misc\ReportTasksController@todoTasksInactive');
Route::post('/manage/report/todo_inactive/delete', '\App\Http\Controllers\Misc\ReportTasksController@todoTasksDelete');
Route::post('/manage/report/todo_inactive/reassign', '\App\Http\Controllers\Misc\ReportTasksController@todoTasksReassign');
// Report Actions
Route::get('report/actions/{type}/{id}', '\App\Http\Controllers\Misc\ReportActionController@index');
Route::post('report/actions/{type}/{id}', '\App\Http\Controllers\Misc\ReportActionController@store');
Route::patch('report/actions/{type}/{id}', '\App\Http\Controllers\Misc\ReportActionController@update');

/*
|--------------------------------------------------------------------------
| IMPORT + OTHER
|--------------------------------------------------------------------------
*/
// Import Data Routes
Route::get('manage/import-payroll', '\App\Http\Controllers\Misc\PagesImportController@importPayroll');
Route::get('manage/import-maintenance', '\App\Http\Controllers\Misc\PagesImportController@importMaintenance');
Route::get('manage/import-supers', '\App\Http\Controllers\Misc\PagesImportController@importSiteSupervivors');
Route::get('manage/import-questions', '\App\Http\Controllers\Misc\PagesImportController@importQuestions');
Route::get('manage/import-time-extensions', '\App\Http\Controllers\Misc\PagesController@importTimeExtensions');
Route::get('manage/initchecklist', '\App\Http\Controllers\Misc\PagesImportController@initSuperChecklist');
Route::get('manage/newchecklist', '\App\Http\Controllers\Misc\PagesImportController@newSuperChecklist');
Route::get('manage/convert-tasks', '\App\Http\Controllers\Misc\PagesImportController@convertTasks');
Route::get('manage/convert-emails', '\App\Http\Controllers\Misc\PagesImportController@convertEmails');
Route::get('manage/updateroles', '\App\Http\Controllers\Misc\PagesController@updateRoles');
Route::get('manage/triggerQA', '\App\Http\Controllers\Misc\PagesController@triggerQA');
// Session variables
Route::get('/session/{name}/{val}', '\App\Http\Controllers\Misc\SessionValController@updateVal');
Route::post('/session/update', '\App\Http\Controllers\Misc\SessionValController@update');
// Filebank Routes
Route::get('/filebank/{path}', \App\Http\Controllers\Misc\FileBankProxyController::class)->where('path', '.*');
// Form Template Setup
Route::get('manage/resetform', '\App\Http\Controllers\Misc\Form\FormSetupController@resetFormTemplate');
Route::get('manage/initform1', '\App\Http\Controllers\Misc\Form\FormSetupController@createFormTemplate1');
Route::get('manage/initform2', '\App\Http\Controllers\Misc\Form\FormSetupController@createFormTemplate2');
Route::get('manage/template/actions/{form_id}', '\App\Http\Controllers\Misc\Form\FormController@completedActionsConstructionWHS');
Route::get('manage/template/{id}', '\App\Http\Controllers\Misc\Form\FormSetupController@showTemplate');
// File Manager
Route::get('/manage/file', '\App\Http\Controllers\Misc\FileController@index');
Route::get('/manage/file/directory', '\App\Http\Controllers\Misc\FileController@fileDirectory');
Route::get('manage/file/directory/dt/docs', '\App\Http\Controllers\Misc\FileController@getDocs');
// CCC Routes
Route::get('manage/import-ccc-program', '\App\Http\Controllers\Misc\CccController@importCCCprogram');
Route::get('manage/import-ccc-youth', '\App\Http\Controllers\Misc\CccController@importCCCyouth');
Route::get('manage/output-ccc-program', '\App\Http\Controllers\Misc\CccController@outputCCCprogram');

// Form Template
Route::get('form/template/dt/templates', '\App\Http\Controllers\Misc\Form\FormTemplateController@getTemplates');
Route::post('form/template/data/save', '\App\Http\Controllers\Misc\Form\FormTemplateController@saveTemplate');
Route::get('form/template/data/template/{template_id}', '\App\Http\Controllers\Misc\Form\FormTemplateController@getTemplate');
Route::get('form/media/{file_id}/rotate/{rotate}', '\App\Http\Controllers\Misc\Form\FormController@rotateImage');
Route::get('form/media/{file_id}/delete', '\App\Http\Controllers\Misc\Form\FormController@deleteFile');
Route::resource('form/template', '\App\Http\Controllers\Misc\Form\FormTemplateController');

// Site Inspection Forms
Route::get('site/inspection/dt/forms', '\App\Http\Controllers\Misc\Form\FormController@getForms');
//Route::get('site/inspection/dt/{template_id}', '\App\Http\Controllers\Misc\Form\FormController@getTemplateForms');
Route::get('site/inspection/list/{template_id}', '\App\Http\Controllers\Misc\Form\FormController@listForms');
Route::get('site/inspection/create/{template_id}', '\App\Http\Controllers\Misc\Form\FormController@createForm');
Route::get('site/inspection/{form_id}/media/{view}', '\App\Http\Controllers\Misc\Form\FormController@showMedia');
Route::get('site/inspection/{form_id}/{pagenumber}', '\App\Http\Controllers\Misc\Form\FormController@showPage');
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

// Contruction Docs
Route::get('construction/doc/dt/standards', '\App\Http\Controllers\Misc\ConstructionDocController@getDocs');
Route::any('construction/doc/standards', '\App\Http\Controllers\Misc\ConstructionDocController@index');
Route::any('construction/doc/create', '\App\Http\Controllers\Misc\ConstructionDocController@create');
Route::any('construction/doc/upload', '\App\Http\Controllers\Misc\ConstructionDocController@upload');


Route::get('action/{table}/{table_id}', '\App\Http\Controllers\Misc\ActionController@index');
Route::as('misc.')->resource('action', '\App\Http\Controllers\Misc\ActionController');


// Categories
Route::get('category/del/{id}', '\App\Http\Controllers\Misc\CategoryController@deleteCat');
Route::get('category/order/{direction}/{id}', '\App\Http\Controllers\Misc\CategoryController@updateOrder');
Route::as('misc.')->resource('category', \App\Http\Controllers\Misc\CategoryController::class);

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
Route::as('equipment.')->resource('equipment', \App\Http\Controllers\Misc\EquipmentController::class);

// Stocktake
Route::get('equipment/stocktake/dt/stocktake', '\App\Http\Controllers\Misc\EquipmentStocktakeController@getStocktake');
Route::get('equipment/stocktake/view/{id}', '\App\Http\Controllers\Misc\EquipmentStocktakeController@showStocktake');
Route::get('equipment/stocktake/{id}/edit/{tab}', '\App\Http\Controllers\Misc\EquipmentStocktakeController@edit');
Route::resource('equipment/stocktake', '\App\Http\Controllers\Misc\EquipmentStocktakeController');


// Configuration
Route::get('settings', '\App\Http\Controllers\Misc\PagesController@settings');
Route::get('settings/notifications/{id}/status/{status}', '\App\Http\Controllers\Misc\SettingsNotificationController@updateStatus');

Route::as('settings.')->resource('settings/notifications', \App\Http\Controllers\Misc\SettingsNotificationController::class);

// Roles / Permission
Route::get('settings/role/permissions', '\App\Http\Controllers\Misc\RoleController@getPermissions');
Route::get('settings/role/resetpermissions', '\App\Http\Controllers\Misc\PagesController@resetPermissions');
Route::get('settings/role/child-role/{id}', '\App\Http\Controllers\Misc\RoleController@childRole');
Route::get('settings/role/child-primary/{id}', '\App\Http\Controllers\Misc\RoleController@childPrimary');
Route::get('settings/role/child-default/{id}', '\App\Http\Controllers\Misc\RoleController@childDefault');
Route::get('settings/role/parent', '\App\Http\Controllers\Misc\RoleController@parent');
Route::get('settings/role/child', '\App\Http\Controllers\Misc\RoleController@child');
Route::as('settings.')->resource('settings/role', \App\Http\Controllers\Misc\RoleController::class);

// Fudge
Route::get('userlog', '\App\Http\Controllers\Misc\PagesController@userlog');
Route::post('userlog', '\App\Http\Controllers\Misc\PagesController@userlogAuth');