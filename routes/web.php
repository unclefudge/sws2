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

/*
|--------------------------------------------------------------------------
| PUBLIC ROUTES
|--------------------------------------------------------------------------
*/
// Authentication routes...
Route::get('/login', '\App\Http\Controllers\Auth\SessionController@create')->name('login');
Route::post('/login', '\App\Http\Controllers\Auth\SessionController@store');
Route::get('/logout', '\App\Http\Controllers\Auth\SessionController@destroy')->name('logout');

// Signup routes.. Pre Login
Route::get('/signup', '\App\Http\Controllers\Auth\RegistrationController@create')->name('register');
Route::get('/signup/ref/{key}', '\App\Http\Controllers\Auth\RegistrationController@refCreate');
Route::get('/signup/primary/{key}', '\App\Http\Controllers\Auth\RegistrationController@primaryCreate');
Route::post('/signup/primary', '\App\Http\Controllers\Auth\RegistrationController@primaryStore');
//Route::get('password/reset/{token}',  '\App\Http\Controllers\Auth\ResetPasswordController@showResetForm')->name('password.reset.token');
//Route::post('password/reset',         '\App\Http\Controllers\Auth\ResetPasswordController@reset');

// Password Reset Routes...
Route::get('/password/reset', '\App\Http\Controllers\Auth\PasswordResetController@forgotForm');
Route::post('/password/email', '\App\Http\Controllers\Auth\PasswordResetController@resetEmail');
Route::get('/password/reset/{token}', '\App\Http\Controllers\Auth\PasswordResetController@resetForm');
Route::post('/password/reset', '\App\Http\Controllers\Auth\PasswordResetController@reset');

//Route::get('password/reset',          '\App\Http\Controllers\Auth\ForgotPasswordController@showLinkRequestForm')->name('password.reset');
//Route::post('password/email',         '\App\Http\Controllers\Auth\ForgotPasswordController@sendResetLinkEmail')->name('password.email');


/*
|--------------------------------------------------------------------------
| PRIVATE ROUTES (User Authenticated)
|--------------------------------------------------------------------------
*/
Route::group(['middleware' => 'auth'], function () {

    require __DIR__ . '/user.php';
    require __DIR__ . '/company.php';
    require __DIR__ . '/site.php';
    require __DIR__ . '/safety.php';
    require __DIR__ . '/planner.php';
    //require __DIR__ . '/admin.php';
    require __DIR__ . '/misc.php';


    /*
    |--------------------------------------------------------------------------
    | CONSTRUCTION DOCS (namespaced)
    |--------------------------------------------------------------------------
    */
    Route::prefix('construction')
        ->as('construction.')
        ->group(function () {
            Route::resource('doc', '\App\Http\Controllers\Misc\ConstructionDocController');
        });


    /*
    |--------------------------------------------------------------------------
    | SIGNUP & CHECKIN (Post Login)
    |--------------------------------------------------------------------------
    */
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
    Route::as('client.')->resource('client', '\App\Http\Controllers\Client\ClientController');


    // Support Tickets
    Route::get('support/ticket/dt/tickets', '\App\Http\Controllers\Support\SupportTicketController@getTickets');
    Route::get('support/ticket/dt/upgrades', '\App\Http\Controllers\Support\SupportTicketController@getUpgrades');
    Route::get('support/ticket/create', '\App\Http\Controllers\Support\SupportTicketController@create');
    Route::post('support/ticket/action', '\App\Http\Controllers\Support\SupportTicketController@addAction');
    Route::get('support/ticket/{id}/eta/{date}', '\App\Http\Controllers\Support\SupportTicketController@updateETA');
    Route::get('support/ticket/{id}/status/{status}', '\App\Http\Controllers\Support\SupportTicketController@updateStatus');
    Route::get('support/ticket/{id}/hours/{hours}', '\App\Http\Controllers\Support\SupportTicketController@updateHours');
    Route::get('support/ticket/{id}/priority/{priority}', '\App\Http\Controllers\Support\SupportTicketController@updatePriority');
    Route::get('support/ticket/{id}/assigned/{assigned}', '\App\Http\Controllers\Support\SupportTicketController@updateAssigned');
    Route::as('support.')->resource('support/ticket', '\App\Http\Controllers\Support\SupportTicketController');


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


    // Management Routes
    Route::get('test/cal', '\App\Http\Controllers\Misc\PagesController@testcal');
    Route::get('test/filepond', '\App\Http\Controllers\Misc\PagesController@testfilepond');
    Route::get('test/blankptc/{cid}', 'CompanyPeriodTradeController@blankPtcPDF');

    Route::get('test/asbestosreg', '\App\Http\Controllers\Misc\PagesController@asbestosRegister');

    Route::get('/logs/nightly/{file}', function ($file) {
        $path = storage_path("app/log/nightly/{$file}");
        abort_unless(file_exists($path), 404);
        return response()->file($path);
    });
    Route::get('/reports/tmp/{company}/{file}', function ($company, $file) {
        abort_if(str_contains($file, '..'), 403);
        abort_unless(strtolower(pathinfo($file, PATHINFO_EXTENSION)) === 'pdf', 403);

        $path = storage_path("app/tmp/report/{$company}/{$file}");
        abort_unless(is_file($path), 404);

        return response()->stream($path);
    });
    /*Route::get('/reports/tmp/{company}/{file}', function ($company, $file) {
        // Prevent path traversal
        abort_if(str_contains($file, '..'), 403);

        $path = storage_path("app/tmp/report/{$company}/{$file}");
        abort_unless(file_exists($path), 404);

        if (request()->has('download')) {
            return response()->download($path);
        }

        return response()->file($path, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $file . '"',
        ]);
        //return response()->download($path);
    });*/
});


// PHP Info
Route::get('php-info', function () {
    //phpinfo();
});

Route::get('test/email', function () {
    //return view('emails/blank');
});


