{{-- Edit Company Details --}}
<div class="portlet light" style="display: none;" id="edit_contact">
    <div class="portlet-title">
        <div class="caption">
            <span class="caption-subject font-dark bold uppercase">Contact Details</span>
            @if(!$user->approved_by && Auth::user()->allowed2('sig.user', $user))
                <span class="label label-warning">Pending Approval</span>
            @endif
        </div>
    </div>
    <div class="portlet-body form">
        <form method="POST" action="{{ action([App\Http\Controllers\UserController::class, 'update'], $user->id) }}">
            @csrf
            @method('PATCH')

        @if ($user->status)
            {{-- First Name --}}
            <div class="row">
                <div class="form-group">
                    <label for="firstname" class="col-md-3 control-label">First Name:</label>
                    <div class="col-md-9">
                        <x-form.input name="firstname" :value="$user->firstname" required/>
                    </div>
                </div>
            </div>
            <hr class="field-hr">
            {{-- Last Name --}}
            <div class="row">
                <div class="form-group">
                    <label for="lastname" class="col-md-3 control-label">Last Name:</label>
                    <div class="col-md-9">
                        <x-form.input name="lastname" :value="$user->lastname" required/>
                    </div>
                </div>
            </div>
            <hr class="field-hr">
            {{-- Phone --}}
            <div class="row">
                <div class="form-group">
                    <label for="phone" class="col-md-3 control-label">Phone:</label>
                    <div class="col-md-9">
                        <x-form.input name="phone" :value="$user->phone"/>
                    </div>
                </div>
            </div>
            <hr class="field-hr">
            {{-- Email --}}
            <div class="row">
                <div class="form-group">
                    <label for="email" class="col-md-3 control-label">Email:</label>
                    <div class="col-md-9">
                        <x-form.input name="email" :value="$user->email" required/>
                    </div>
                </div>
            </div>
            <hr class="field-hr">
            {{-- Adddress --}}
            <div class="row">
                <div class="form-group">
                    <label for="address" class="col-md-3 control-label">Address:</label>
                    <div class="col-md-9">
                        <x-form.input name="address" :value="$user->address"/>
                    </div>
                </div>
            </div>
            <hr class="field-hr">
            {{-- Suburb --}}
            <div class="row">
                <div class="form-group">
                    <label for="suburb" class="col-md-3 control-label">Suburb:</label>
                    <div class="col-md-9">
                        <x-form.input name="suburb" :value="$user->suburb"/>
                    </div>
                </div>
            </div>
            <hr class="field-hr">
            {{-- State --}}
            <div class="row">
                <div class="form-group">
                    <label for="state" class="col-md-3 control-label">State:</label>
                    <div class="col-md-9">
                        <x-form.select name="state" :options="$ozstates::all()" value="NSW"/>
                    </div>
                </div>
            </div>
            <hr class="field-hr">
            {{-- Postcode --}}
            <div class="row">
                <div class="form-group">
                    <label for="postcode" class="col-md-3 control-label">Postcode:</label>
                    <div class="col-md-9">
                        <x-form.input name="postcode" :value="$user->postcode"/>
                    </div>
                </div>
            </div>
            @if ($user->company_id == 3)
                <hr class="field-hr">
                {{-- Job Title --}}
                <div class="row">
                    <div class="form-group">
                        <label for="jobtitle" class="col-md-3 control-label">Job Title:</label>
                        <div class="col-md-9">
                            <x-form.input name="jobtitle" :value="$user->jobtitle"/>
                        </div>
                    </div>
                </div>
            @endif
        @else
            {{-- Pass Required Fields as hidden --}}
            <x-form.hidden name="firstname" :value="$user->firstname"/>
            <x-form.hidden name="lastname" :value="$user->lastname"/>
            <x-form.hidden name="phone" :value="$user->phone"/>
            <x-form.hidden name="email" :value="$user->email"/>
            <x-form.hidden name="address" :value="$user->address"/>
            <x-form.hidden name="suburb" :value="$user->suburb"/>
            <x-form.hidden name="state" :value="$user->state"/>
            <x-form.hidden name="postcode" :value="$user->postcode"/>
            <x-form.hidden name="jobtitle" :value="$user->jobtitle"/>
        @endif
        {{-- Notes --}}
        @if ((Auth::user()->isCompany($user->company_id) && Auth::user()->hasPermission2('view.user.security')) || ($user->company->parent_company && Auth::user()->isCompany($user->company->reportsTo()->id)))
            <hr class="field-hr">
            <div class="row">
                <div class="form-group">
                    <label for="notes" class="col-md-3 control-label">Private Notes:</label>
                    <div class="col-md-9">
                        <x-form.textarea name="notes" :value="$user->notes" rows="3"/>
                        <span class="help-block"> Viewable by parent company or user with security access</span>
                    </div>
                </div>
            </div>
        @endif

        <br>
        <div class="form-actions right">
            @if ($user->status == 2)
                <button type="submit" class="btn green"> Continue</button>
            @else
                <button class="btn default" onclick="cancelForm(event, 'contact')">Cancel</button>
                <button type="submit" class="btn green"> Save</button>
            @endif
        </div>
        </form>
    </div>
</div>