<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| SITE DOCS (namespaced)
|--------------------------------------------------------------------------
*/
Route::prefix('site')->as('site.')->group(function () {
    Route::get('doc/type/{type}', '\App\Http\Controllers\Site\SiteDocController@listDocs');
    Route::get('doc/type/dt/{type}', '\App\Http\Controllers\Site\SiteDocController@getDocsType');
    Route::get('doc/dt/docs', '\App\Http\Controllers\Site\SiteDocController@getDocs');
    Route::any('doc/create', '\App\Http\Controllers\Site\SiteDocController@create');
    Route::any('doc/upload', '\App\Http\Controllers\Site\SiteDocController@upload');
    Route::resource('doc', '\App\Http\Controllers\Site\SiteDocController');
});

// Site Project Supply
Route::get('site/supply/dt/list', '\App\Http\Controllers\Site\SiteProjectSupplyController@getReports');
Route::get('site/supply/settings', '\App\Http\Controllers\Site\SiteProjectSupplyController@settings');
Route::post('site/supply/settings', '\App\Http\Controllers\Site\SiteProjectSupplyController@updateSettings');
Route::get('site/supply/{id}/create', '\App\Http\Controllers\Site\SiteProjectSupplyController@createItem');
Route::get('site/supply/delete/{id}', '\App\Http\Controllers\Site\SiteProjectSupplyController@deleteItem');
Route::get('site/supply/{id}/createpdf', '\App\Http\Controllers\Site\SiteProjectSupplyController@createPDF');
Route::get('site/supply/{id}/signoff', '\App\Http\Controllers\Site\SiteProjectSupplyController@signoff');
Route::get('site/supply/{id}/reset', '\App\Http\Controllers\Site\SiteProjectSupplyController@resetItems');
Route::resource('site/supply', '\App\Http\Controllers\Site\SiteProjectSupplyController');

// Site Shutdown
Route::get('site/shutdown/dt/list', '\App\Http\Controllers\Site\SiteShutdownController@getReports');
Route::get('site/shutdown/initialise', '\App\Http\Controllers\Site\SiteShutdownController@initialise');
Route::get('site/shutdown/reminder', '\App\Http\Controllers\Site\SiteShutdownController@reminder');
Route::get('site/shutdown/{id}/signoff', '\App\Http\Controllers\Site\SiteShutdownController@signoff');
Route::resource('site/shutdown', '\App\Http\Controllers\Site\SiteShutdownController');

// Upcoming Compliance
Route::post('site/upcoming/compliance/update_job', '\App\Http\Controllers\Site\SiteUpcomingComplianceController@updateJob');
Route::get('site/upcoming/compliance/settings/stages', '\App\Http\Controllers\Site\SiteUpcomingComplianceController@settingsStages');
Route::get('site/upcoming/compliance/settings/steel', '\App\Http\Controllers\Site\SiteUpcomingComplianceController@settingsSteel');
Route::get('site/upcoming/compliance/settings/sites', '\App\Http\Controllers\Site\SiteUpcomingComplianceController@settingsSites');
Route::post('site/upcoming/compliance/settings', '\App\Http\Controllers\Site\SiteUpcomingComplianceController@updateSettings');
Route::get('site/upcoming/compliance/settings/del/{id}', '\App\Http\Controllers\Site\SiteUpcomingComplianceController@deleteStage');
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
Route::get('site/note/{id}/delattachment/{attach_id}', '\App\Http\Controllers\Site\SiteNoteController@delAttachment');
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
//Route::any('site/incident/upload', '\App\Http\Controllers\Site\Incident\SiteIncidentController@uploadAttachment');
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
//Route::resource('site/compliance', '\App\Http\Controllers\Site\Planner\SiteComplianceController');
Route::prefix('site')->as('site.')->group(function () {
    Route::resource('compliance', \App\Http\Controllers\Site\SiteComplianceController::class);
});

// Site Docs
//Route::get('site/doc/type/{type}', '\App\Http\Controllers\Site\SiteDocController@listDocs');
Route::any('site/doc/plan/create', '\App\Http\Controllers\Site\SiteDocController@createPlan');

// Site QA Categories
Route::get('site/categories/qa/dt/qa_cats', '\App\Http\Controllers\Site\SiteQaCategoryController@getQaCategories');
Route::resource('site/qa/category', '\App\Http\Controllers\Site\SiteQaCategoryController');

// Site Quality Assurance
Route::get('site/qa/sigonoff', '\App\Http\Controllers\Site\SiteQaController@listSignoff');
Route::get('site/qa/templates/order', '\App\Http\Controllers\Site\SiteQaController@reportOrder');
Route::any('site/qa/templates/order/update', '\App\Http\Controllers\Site\SiteQaController@reportOrderUpdate2');
Route::get('site/qa/templates/order/{dir}/{id}', '\App\Http\Controllers\Site\SiteQaController@reportOrderUpdate');
Route::get('site/qa/{id}/items', '\App\Http\Controllers\Site\SiteQaController@getItems');
Route::any('site/qa/{id}/update', '\App\Http\Controllers\Site\SiteQaController@updateReport');
Route::any('site/qa/{id}/resetsign', '\App\Http\Controllers\Site\SiteQaController@resetSignature');
Route::any('site/qa/item/{id}', '\App\Http\Controllers\Site\SiteQaController@updateItem');
Route::get('site/qa/company/{task_id}', '\App\Http\Controllers\Site\SiteQaController@getCompaniesForTask');
Route::get('site/qa/upcoming/{super_id}', '\App\Http\Controllers\Site\SiteQaController@upcoming');
Route::get('site/qa/trigger/{master_id}/{site_id}', '\App\Http\Controllers\Site\SiteQaController@triggerQa');
Route::get('site/qa/dt/qa_reports', '\App\Http\Controllers\Site\SiteQaController@getQaReports');
Route::get('site/qa/dt/qa_templates', '\App\Http\Controllers\Site\SiteQaController@getQaTemplates');
//Route::get('site/qa/dt/qa_upcoming', '\App\Http\Controllers\Site\SiteQaController@getQaUpcoming');
Route::get('site/qa/templates', '\App\Http\Controllers\Site\SiteQaController@templates');
Route::prefix('site')->as('site.')->group(function () {
    Route::resource('qa', \App\Http\Controllers\Site\SiteQaController::class);
});

// Site Maintenance Categories
Route::get('site/categories/maintenance/dt/main_cats', '\App\Http\Controllers\Site\SiteMaintenanceCategoryController@getMainCategories');
Route::prefix('site/maintenance')->as('site.maintenance.')->group(function () {
    Route::resource(
        'category',
        '\App\Http\Controllers\Site\SiteMaintenanceCategoryController'
    );
});

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
Route::prefix('site')->as('site.')->group(function () {
    Route::resource('maintenance', \App\Http\Controllers\Site\SiteMaintenanceController::class);
});

// Site Prac Completion
Route::get('site/prac-completion/{id}/items', '\App\Http\Controllers\Site\SitePracCompletionController@getItems');
Route::any('site/prac-completion/{id}/additem', '\App\Http\Controllers\Site\SitePracCompletionController@addItem');
Route::any('site/prac-completion/{id}/delitem', '\App\Http\Controllers\Site\SitePracCompletionController@delItem');
Route::any('site/prac-completion/{id}/update', '\App\Http\Controllers\Site\SitePracCompletionController@updateReport');
Route::any('site/prac-completion/{id}/clearsignoff', '\App\Http\Controllers\Site\SitePracCompletionController@clearSignoff');
Route::get('site/prac-completion/{id}/delfile/{doc_id}', '\App\Http\Controllers\Site\SitePracCompletionController@deleteAttachment');
Route::any('site/prac-completion/item/{id}', '\App\Http\Controllers\Site\SitePracCompletionController@updateItem');
Route::get('site/prac-completion/dt/prac', '\App\Http\Controllers\Site\SitePracCompletionController@getPrac');
Route::any('site/prac-completion/upload', '\App\Http\Controllers\Site\SitePracCompletionController@uploadAttachment');
Route::get('site/prac-completion/data/prac_completion/{site_id}', '\App\Http\Controllers\Site\SitePracCompletionController@getPracCompletion');
Route::get('site/prac-completion/data/site_super/{site_id}', '\App\Http\Controllers\Site\SitePracCompletionController@getSiteSupervisor');
Route::any('site/prac-completion/{id}/review', '\App\Http\Controllers\Site\SitePracCompletionController@review');
Route::prefix('site')->as('site.')->group(function () {
    Route::resource('prac-completion', \App\Http\Controllers\Site\SitePracCompletionController::class);
});

// Site FOC
Route::get('site/foc/{id}/items', '\App\Http\Controllers\Site\SiteFocController@getItems');
Route::get('site/foc/{id}/additems', '\App\Http\Controllers\Site\SiteFocController@addItems');
Route::any('site/foc/{id}/additems/save', '\App\Http\Controllers\Site\SiteFocController@addItemsSave');
Route::any('site/foc/{id}/additem', '\App\Http\Controllers\Site\SiteFocController@addItem');
Route::any('site/foc/{id}/delitem', '\App\Http\Controllers\Site\SiteFocController@delItem');
Route::any('site/foc/{id}/update', '\App\Http\Controllers\Site\SiteFocController@updateReport');
Route::any('site/foc/{id}/clearsignoff', '\App\Http\Controllers\Site\SiteFocController@clearSignoff');
Route::get('site/foc/{id}/delfile/{doc_id}', '\App\Http\Controllers\Site\SiteFocController@deleteAttachment');
Route::any('site/foc/item/{id}', '\App\Http\Controllers\Site\SiteFocController@updateItem');
Route::get('site/foc/dt/foc', '\App\Http\Controllers\Site\SiteFocController@getFoc');
Route::get('site/foc/settings', '\App\Http\Controllers\Site\SiteFocController@settings');
Route::post('site/foc/settings', '\App\Http\Controllers\Site\SiteFocController@updateSettings');
Route::get('site/foc/data/foc/{site_id}', '\App\Http\Controllers\Site\SiteFocController@getFoc');
Route::get('site/foc/data/site_super/{site_id}', '\App\Http\Controllers\Site\SiteFocController@getSiteSupervisor');
Route::any('site/foc/{id}/review', '\App\Http\Controllers\Site\SiteFocController@review');
Route::prefix('site')->as('site.')->group(function () {
    Route::resource('foc', \App\Http\Controllers\Site\SiteFocController::class);
});


// Site Asbestos Register
Route::get('site/asbestos/register/dt/list', '\App\Http\Controllers\Site\SiteAsbestosRegisterController@getReports');
Route::get('site/asbestos/register/{id}/create', '\App\Http\Controllers\Site\SiteAsbestosRegisterController@createItem');
Route::get('site/asbestos/register/delete/{id}', '\App\Http\Controllers\Site\SiteAsbestosRegisterController@deleteItem');
Route::get('site/asbestos/register/{id}/createpdf', '\App\Http\Controllers\Site\SiteAsbestosRegisterController@createPDF');
Route::get('site/asbestos/register/{id}/destroy', '\App\Http\Controllers\Site\SiteAsbestosRegisterController@destroy');
Route::prefix('site/asbestos')->as('site.asbestos.')->group(function () {
    Route::resource('register', \App\Http\Controllers\Site\SiteAsbestosRegisterController::class);
});

// Site Asbestos Notification
Route::get('site/asbestos/notification/dt/list', '\App\Http\Controllers\Site\SiteAsbestosController@getReports');
Route::get('site/asbestos/notification/{id}/status/{status}', '\App\Http\Controllers\Site\SiteAsbestosController@updateStatus');
Route::any('site/asbestos/notification/{id}/extra', '\App\Http\Controllers\Site\SiteAsbestosController@updateExtra');
Route::prefix('site/asbestos')->as('site.asbestos.')->group(function () {
    Route::resource('notification', \App\Http\Controllers\Site\SiteAsbestosController::class);
});

// Site Inspection Electrical Register
Route::get('site/inspection/electrical/dt/list', '\App\Http\Controllers\Site\SiteInspectionElectricalController@getInspections');
Route::any('site/inspection/electrical/{id}/docs', '\App\Http\Controllers\Site\SiteInspectionElectricalController@documents');
Route::get('site/inspection/electrical/{id}/report', '\App\Http\Controllers\Site\SiteInspectionElectricalController@reportPDF');
Route::get('site/inspection/electrical/{id}/status/{status}', '\App\Http\Controllers\Site\SiteInspectionElectricalController@updateStatus');
Route::get('site/inspection/electrical/{id}/delfile/{doc_id}', '\App\Http\Controllers\Site\SiteInspectionElectricalController@deleteAttachment');
Route::post('site/inspection/electrical/{id}/signoff', '\App\Http\Controllers\Site\SiteInspectionElectricalController@signoff');

// Site Inspection Plumbing Register
Route::get('site/inspection/plumbing/dt/list', '\App\Http\Controllers\Site\SiteInspectionPlumbingController@getInspections');
Route::any('site/inspection/plumbing/{id}/docs', '\App\Http\Controllers\Site\SiteInspectionPlumbingController@documents');
Route::get('site/inspection/plumbing/{id}/report', '\App\Http\Controllers\Site\SiteInspectionPlumbingController@reportPDF');
Route::get('site/inspection/plumbing/{id}/status/{status}', '\App\Http\Controllers\Site\SiteInspectionPlumbingController@updateStatus');
Route::get('site/inspection/plumbing/{id}/delfile/{doc_id}', '\App\Http\Controllers\Site\SiteInspectionPlumbingController@deleteAttachment');
Route::post('site/inspection/plumbing/{id}/signoff', '\App\Http\Controllers\Site\SiteInspectionPlumbingController@signoff');
Route::prefix('site/inspection')->as('site.inspection.')->group(function () {
    Route::resource('electrical', \App\Http\Controllers\Site\SiteInspectionElectricalController::class);
    Route::resource('plumbing', \App\Http\Controllers\Site\SiteInspectionPlumbingController::class);
});


Route::get('site/inspection/dt/forms', '\App\Http\Controllers\Misc\Form\FormController@getForms');
////Route::get('site/inspection/dt/{template_id}', '\App\Http\Controllers\Misc\Form\FormController@getTemplateForms');
Route::get('site/inspection/list/{template_id}', '\App\Http\Controllers\Misc\Form\FormController@listForms');
Route::get('site/inspection/create/{template_id}', '\App\Http\Controllers\Misc\Form\FormController@createForm');
Route::get('site/inspection/{form_id}/media/{view}', '\App\Http\Controllers\Misc\Form\FormController@showMedia');
Route::get('site/inspection/{form_id}/{pagenumber}', '\App\Http\Controllers\Misc\Form\FormController@showPage');
Route::resource('site/inspection', '\App\Http\Controllers\Misc\Form\FormController');
Route::delete('form/media', [\App\Http\Controllers\Misc\Form\FormController::class, 'deleteMedia'])->name('form.media.delete');


// Site Scaffold Handover
Route::get('site/scaffold/handover/dt/list', '\App\Http\Controllers\Site\SiteScaffoldHandoverController@getCertificates');
Route::get('site/scaffold/handover/create/{site_id}', '\App\Http\Controllers\Site\SiteScaffoldHandoverController@create');
Route::any('site/scaffold/handover/deltask/{task_id}', '\App\Http\Controllers\Site\SiteScaffoldHandoverController@delTask');
Route::any('site/scaffold/handover/{id}/docs', '\App\Http\Controllers\Site\SiteScaffoldHandoverController@documents');
Route::get('site/scaffold/handover/{id}/report', '\App\Http\Controllers\Site\SiteScaffoldHandoverController@reportPDF');
Route::post('site/scaffold/handover/{id}/report', '\App\Http\Controllers\Site\SiteScaffoldHandoverController@emailPDF');
Route::prefix('site/scaffold')->as('site.scaffold.')->group(function () {
    Route::resource('handover', \App\Http\Controllers\Site\SiteScaffoldHandoverController::class);
});

// Site Attendance
Route::get('/site/attendance/dt/attendance', '\App\Http\Controllers\Site\SiteAttendanceController@getAttendance');
Route::resource('/site/attendance', '\App\Http\Controllers\Site\SiteAttendanceController');

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
//Route::resource('site', '\App\Http\Controllers\Site\SiteController');
Route::as('site.')->resource('site', '\App\Http\Controllers\Site\SiteController');

// Trade + Task Routes
Route::resource('trade', '\App\Http\Controllers\Site\Planner\TradeController');
Route::resource('task', '\App\Http\Controllers\Site\Planner\TaskController');

// Public Holidays
Route::get('planner/publicholidays/dt/dates', '\App\Http\Controllers\Site\Planner\PublicHolidayController@getDates');
Route::as('planner.')->resource('planner/publicholidays', '\App\Http\Controllers\Site\Planner\PublicHolidayController');


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
Route::any('planner/data/publicholidays', '\App\Http\Controllers\Site\Planner\SitePlannerController@getPublicholidays');
Route::as('planner.')->resource('planner', \App\Http\Controllers\Site\Planner\SitePlannerController::class);