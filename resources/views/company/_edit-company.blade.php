{{-- Edit Company Details --}}
<div class="portlet light" style="display: none;" id="edit_company">
    <div class="portlet-title">
        <div class="caption">
            <span class="caption-subject font-dark bold uppercase">Company Details</span>
            @if(!$company->approved_by && $company->reportsTo()->id == Auth::user()->company_id)
                <span class="label label-warning">Pending Approval</span>
            @endif
        </div>
    </div>
    <div class="portlet-body form">
        <form method="POST" action="{{ action([App\Http\Controllers\Company\CompanyController::class, 'update'], $company->id) }}">
            @csrf
            @method('PATCH')
            {{-- Status --}}
            <div class="row">
                @if(Auth::user()->allowed2('del.company', $company))
                    <label for="status" class="col-md-3 control-label">Status:</label>
                    <div class="col-md-9">
                        <x-form.select name="status" :options="['1' => 'Active', '0' => 'Inactive']" :value="$company->status"/>
                        <span class="help-block"> Only editable by parent company</span>
                    </div>
                @else
                    <div class="col-md-3">Status:</div>
                    <div class="col-xs-9">{!! $company->status_text !!}</div>
                @endif
            </div>
            <hr class="field-hr">
            @if ($company->status)
                {{-- Name --}}
                <div class="row">
                    <label for="name" class="col-md-3 control-label">Name:</label>
                    <div class="col-md-9">
                        <x-form.input name="name" :value="$company->name" required/>
                    </div>
                </div>
                <hr class="field-hr">
                {{-- Phone --}}
                <div class="row">
                    <label for="phone" class="col-md-3 control-label">Phone:</label>
                    <div class="col-md-9">
                        <x-form.input name="phone" :value="$company->phone"/>
                    </div>
                </div>
                <hr class="field-hr">
                {{-- Email --}}
                <div class="row">
                    <label for="email" class="col-md-3 control-label">Email:</label>
                    <div class="col-md-9">
                        <x-form.input name="email" :value="$company->email" required/>
                    </div>
                </div>
                <hr class="field-hr">
                {{-- Adddress --}}
                <div class="row">
                    <label for="address" class="col-md-3 control-label">Address:</label>
                    <div class="col-md-9">
                        <x-form.input name="address" :value="$company->address" required/>
                    </div>
                </div>
                <hr class="field-hr">
                {{-- Suburb --}}
                <div class="row">
                    <label for="suburb" class="col-md-3 control-label">Suburb:</label>
                    <div class="col-md-9">
                        <x-form.input name="suburb" :value="$company->suburb" required/>
                    </div>
                </div>
                <hr class="field-hr">
                {{-- State --}}
                <div class="row">
                    <label for="state" class="col-md-3 control-label">State:</label>
                    <div class="col-md-9">
                        <x-form.select name="state" :options="$ozstates::all()" :value="$company->state ?: 'NSW'" required/>
                    </div>
                </div>
                <hr class="field-hr">
                {{-- Postcode --}}
                <div class="row">
                    <label for="postcode" class="col-md-3 control-label">Postcode:</label>
                    <div class="col-md-9">
                        <x-form.input name="postcode" :value="$company->postcode" required/>
                    </div>
                </div>
                <hr class="field-hr">
                {{-- Primary Contact --}}
                <div class="row">
                    <label for="primary_user" class="col-md-3 control-label">Primary Contact:</label>
                    <div class="col-md-9">
                        <x-form.select name="primary_user" :options="$company->usersSelect('prompt')" :value="$company->primary_user"/>
                    </div>
                </div>
                <hr class="field-hr">
                {{-- Seconday Contact --}}
                <div class="row">
                    <label for="secondary_user" class="col-md-3 control-label">Secondary Contact:</label>
                    <div class="col-md-9">
                        <x-form.select name="secondary_user" :options="['0' => 'None'] + $company->usersSelect()" :value="$company->secondary_user" required/>
                    </div>
                </div>
            @else
                {{-- Pass Required Fields as hidden --}}
                <x-form.hidden name="name" :value="$company->name"/>
                <x-form.hidden name="phone" :value="$company->phone"/>
                <x-form.hidden name="email" :value="$company->email"/>
                <x-form.hidden name="address" :value="$company->address"/>
                <x-form.hidden name="suburb" :value="$company->suburb"/>
                <x-form.hidden name="state" :value="$company->state"/>
                <x-form.hidden name="postcode" :value="$company->postcode"/>
                <x-form.hidden name="primary_user" :value="$company->primary_user"/>
            @endif
            {{-- Notes --}}
            @if (Auth::user()->isCompany($company->reportsTo()))
                <hr class="field-hr">
                {{-- Planner Abbr --}}
                <div class="row">
                    <label for="nickname" class="col-md-3 control-label">Planner Abbreviation:</label>
                    <div class="col-md-9">
                        <x-form.input name="nickname" :value="$company->nickname"/>
                    </div>
                </div>
                <hr class="field-hr">
                {{-- Reports Abbr --}}
                <div class="row">
                    <label for="abbr" class="col-md-3 control-label">Report Abbreviation:</label>
                    <div class="col-md-9">
                        <x-form.input name="abbr" :value="$company->abbr"/>
                    </div>
                </div>
                <hr class="field-hr">
                <div class="row">
                    <label for="notes" class="col-md-3 control-label">Private Notes:</label>
                    <div class="col-md-9">
                        <x-form.textarea name="notes" :value="$company->notes" rows="3"/>
                        <span class="help-block"> Only viewable by parent company</span>
                    </div>
                </div>
            @endif

            <br>
            <div class="form-actions right">
                @if ($company->status == 2)
                    <button type="submit" class="btn green"> Continue</button>
                @else
                    <button class="btn default" onclick="cancelForm(event, 'company')">Cancel</button>
                    <button type="submit" class="btn green"> Save</button>
                @endif
            </div>
        </form>
    </div>
</div>
