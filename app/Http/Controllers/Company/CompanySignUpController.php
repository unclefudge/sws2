<?php

namespace App\Http\Controllers\Company;

use Illuminate\Http\Request;
use Validator;

use DB;
use Mail;
use Carbon\Carbon;
use App\User;
use App\Models\Company\Company;
use App\Models\Site\Planner\SitePlanner;
use App\Models\Site\Planner\Trade;
use App\Models\Site\Planner\Task;
use App\Http\Requests;
use App\Http\Requests\Company\CompanyRequest;
use App\Http\Utilities\CompanyTypes;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Intervention\Image\Facades\Image;
use Yajra\Datatables\Datatables;
use nilsenj\Toastr\Facades\Toastr;

/**
 * Class CompanySignUpController
 * @package App\Http\Controllers
 */
class CompanySignUpController extends Controller {

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Edit Primary User
     */
    protected function userEdit($id)
    {
        $user = User::find($id);
        // Check authorisation and throw 404 if not
        if (!(Auth::user()->allowed2('edit.user', $user)))
            return view('errors/404');

        return view('company/signup/primary-edit', compact('user'));
    }

    /**
     * Update User
     */
    public function userUpdate($id)
    {
        $user = User::find($id);

        // Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('edit.user', $user))
            return view('errors/404');

        // Validate
        $rules = [
            'username'           => 'required|min:3|max:50|unique:users,username,' . $user->id,
            'firstname'          => 'required',
            'lastname'           => 'required',
            'email'              => 'required_if:status,1|email|max:255|unique:users,email,' . $user->id . ',id',
            'employment_type'    => 'required',
            'subcontractor_type' => 'required_if:employment_type,3',
        ];
        $mesgs = [
            'email.required_if'              => 'The email field is required if user active ie. Login Enabled.',
            'subcontractor_type.required_if' => 'The subcontractor entity is required',
        ];
        $this->validate(request(), $rules, $mesgs);

        if (request()->filled('password') || request()->filled('password_force')) {
            $this->validate(request(), [
                'password' => 'required:|confirmed|min:3',
            ]);
        }

        $user_request = removeNullValues(request()->all());

        // Empty State field if rest of address fields are empty
        if (!request()->filled('address') && !request()->filled('suburb') && !request()->filled('postcode'))
            $user_request['state'] = null;

        // Zero Subcontractor_type field if empty
        if (!request('subcontractor_type'))
            $user_request['subcontractor_type'] = 0;

        // Encrypt password
        if (request('password'))
            $user_request['password'] = bcrypt($user_request['password']);

        // Update User
        $user->update($user_request);
        Toastr::success("Saved changes");

        return redirect("/signup/company/" . Auth::user()->company_id);   // Adding company info

    }

    /**
     * Edit Company Info
     */
    public function companyEdit($id)
    {
        $company = Company::findorFail($id);

        // Check authorisation and throw 404 if not
        if (!(Auth::user()->allowed2('edit.company', $company)))
            return view('errors/404');

        return view('company/signup/company', compact('company'));

    }

    /**
     * Update Company
     *
     */
    public function companyUpdate($id)
    {
        $company = Company::findorFail($id);

        /// Check authorisation and throw 404 if not
        if (!Auth::user()->allowed2('edit.company', $company))
            return view('errors/404');

        // Validate
        $rules = [
            'name'     => 'required',
            'phone'    => 'required',
            'email'    => 'required|email|max:255',
            'address'  => 'required',
            'suburb'   => 'required',
            'state'    => 'required',
            'postcode' => 'required',
            'abn'      => 'required',
        ];

        $this->validate(request(), $rules);

        //dd(request()->all());
        $company->update(request()->all());
        Toastr::success("Saved changes");

        return redirect("/signup/workers/$company->id");   // Adding users
    }

    /**
     * Edit Workers
     */
    public function workersEdit($id)
    {
        $company = Company::findorFail($id);

        // Check authorisation and throw 404 if not
        if (!(Auth::user()->allowed2('edit.company', $company)))
            return view('errors/404');

        return view('company/signup/workers', compact('company'));

    }

    /**
     * Show Summary
     */
    public function summary($id)
    {
        $company = Company::findorFail($id);

        // Check authorisation and throw 404 if not
        if (!(Auth::user()->allowed2('edit.company', $company)))
            return view('errors/404');

        $company->signup_step = 4;
        $company->save();

        return view("company/signup/summary", compact('company'));
    }

    /**
     * Add Documents
     */
    public function documents($id)
    {
        $company = Company::findorFail($id);

        // Check authorisation and throw 404 if not
        if (!(Auth::user()->allowed2('edit.company', $company)))
            return view('errors/404');

        $company->signup_step = 0;
        $company->status = 1;
        if ($company->parent_company && $company->reportsTo()->notificationsUsersType('company.signup.completed'))
            Mail::to($company->reportsTo()->notificationsUsersType('company.signup.completed'))->send(new \App\Mail\Company\CompanySignup($company));

        $company->save();

        return redirect("company/$company->id/doc");
    }

    /**
     * Resend Signup Email
     */
    public function welcome($id)
    {
        $company = Company::findorFail($id);
        Mail::to($company)->send(new \App\Mail\Company\CompanyWelcome($company, Auth::user()->company, $company->nickname));

        return view('company/list');
    }

    /**
     * Cancel Signup
     */
    public function cancel($id)
    {
        $company = Company::findorFail($id);

        // Check authorisation and throw 404 if not
        if (!(Auth::user()->allowed2('edit.company', $company) && $company->status == 2))
            return view('errors/404');

        // Delete all users + uploaded user docs linked to company
        if (count($company->staff))
            foreach ($company->staff as $user) {
                foreach($user->userDocs() as $doc) {
                    // Delete any User Docs
                    if ($doc->attachment && file_exists(public_path('/filebank/user/' . $doc->user_id . '/docs/' . $doc->attachment)))
                        unlink(public_path('/filebank/user/' . $doc->user_id . '/docs/' . $doc->attachment));

                    $doc->closeToDo();
                    $doc->delete();
                }
                $user->delete();
            }

        $company->delete();

        return redirect()->back();
    }
}
