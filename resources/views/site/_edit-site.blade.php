{{-- Edit Company Details --}}
<div class="portlet light" style="display: none;" id="edit_site">
    <div class="portlet-title">
        <div class="caption">
            <span class="caption-subject font-dark bold uppercase">Site Details</span> &nbsp; <span class="font-yellow small">*Fields Imported from Zoho</span>
        </div>
    </div>
    <div class="portlet-body form">
        <form method="POST" action="{{ action([App\Http\Controllers\Site\SiteController::class, 'update'], $site->id) }}">
            @csrf
            @method('PATCH')
            {{-- Status --}}
            <div class="row">
                @if(Auth::user()->allowed2('del.site', $site))
                    <div class="form-group {{ $errors->has('status') ? 'has-error' : '' }}">
                        <label for="status" class="col-md-3 control-label">Status:</label>
                        <div class="col-md-9">
                            <x-form.select name="status" :options="['-1' => 'Upcoming', '1' => 'Active', '2' => 'Maintenance', '0' => 'Completed', '-2' => 'Cancelled']" :value="$site->status"/>
                        </div>
                    </div>
                @else
                    <div class="col-md-3">Status:</div>
                    <div class="col-xs-9">{!! $site->status_text !!}</div>
                @endif
            </div>
            <hr class="field-hr">
            @if ($site->status != 0)
                @if (Auth::user()->allowed2('edit.site.zoho.fields', $site))
                    {{-- Job --}}
                    <div class="row">
                        <div class="form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                            <label for="code" class="col-md-3 control-label font-yellow">Job:</label>
                            <div class="col-md-9">
                                <x-form.input name="code" :value="$site->code" required/>
                            </div>
                        </div>
                    </div>
                    <hr class="field-hr">
                    {{-- Name --}}
                    <div class="row">
                        <div class="form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                            <label for="name" class="col-md-3 control-label font-yellow">Name:</label>
                            <div class="col-md-9">
                                <x-form.input name="name" :value="$site->name" required/>
                            </div>
                        </div>
                    </div>
                    <hr class="field-hr">
                    {{-- Adddress --}}
                    <div class="row">
                        <div class="form-group {{ $errors->has('address') ? 'has-error' : '' }}">
                            <label for="address" class="col-md-3 control-label font-yellow">Address:</label>
                            <div class="col-md-9">
                                <x-form.input name="address" :value="$site->address" required/>
                            </div>
                        </div>
                    </div>
                    <hr class="field-hr">
                    {{-- Suburb --}}
                    <div class="row">
                        <div class="form-group {{ $errors->has('suburb') ? 'has-error' : '' }}">
                            <label for="suburb" class="col-md-3 control-label font-yellow">Suburb:</label>
                            <div class="col-md-9">
                                <x-form.input name="suburb" :value="$site->suburb" required/>
                            </div>
                        </div>
                    </div>
                    <hr class="field-hr">
                    {{-- State --}}
                    <div class="row">
                        <div class="form-group {{ $errors->has('state') ? 'has-error' : '' }}">
                            <label for="state" class="col-md-3 control-label font-yellow">State:</label>
                            <div class="col-md-9">
                                <x-form.select name="state" :options="$ozstates::all()" :value="$site->state" required/>
                            </div>
                        </div>
                    </div>
                    <hr class="field-hr">
                    {{-- Postcode --}}
                    <div class="row">
                        <div class="form-group {{ $errors->has('postcode') ? 'has-error' : '' }}">
                            <label for="postcode" class="col-md-3 control-label font-yellow">Postcode:</label>
                            <div class="col-md-9">
                                <x-form.input name="postcode" :value="$site->postcode" required/>
                            </div>
                        </div>
                    </div>
                    <hr class="field-hr">
                @endif
            @else
                {{-- Pass Required Fields as hidden --}}
                <x-form.hidden name="code" :value="$site->code"/>
                <x-form.hidden name="name" :value="$site->name"/>
                <x-form.hidden name="address" :value="$site->address"/>
                <x-form.hidden name="suburb" :value="$site->suburb"/>
                <x-form.hidden name="state" :value="$site->state"/>
                <x-form.hidden name="postcode" :value="$site->postcode"/>
            @endif
            @if($site->status != 0 || Auth::user()->allowed2('del.site', $site))
                {{-- Primary Supervisor--}}
                <div class="row">
                    <div class="form-group {{ $errors->has('supervisor_id') ? 'has-error' : '' }}">
                        <label for="supervisor_id" class="col-md-3 control-label">Supervisor</label>
                        <div class="col-md-9">
                            @if($site->status != 0 || $site->company_id != 3)
                                <x-form.select name="supervisor_id" :options="Auth::user()->company->supervisorsSelect()" :value="$site->supervisor_id" placeholder="Select supervisor"/>
                            @else
                                <x-form.select name="supervisor_id" :options="Auth::user()->company->supervisorsAllSelect()" :value="$site->supervisor_id" placeholder="Select supervisor"/>
                            @endif
                            <x-form.error name="supervisor_id"/>
                        </div>
                    </div>
                </div>
                <hr class="field-hr">
                {{-- Secondary Supervisor--}}
                <div class="row">
                    <div class="form-group {{ $errors->has('supervisors') ? 'has-error' : '' }}">
                        <label for="supervisors" class="col-md-3 control-label">Secondary Supervisor(s)</label>
                        <div class="col-md-9">
                            <x-form.select name="supervisors[]" :options="Auth::user()->company->supervisorsSelect()" :value="$site->supervisors->pluck('id')->toArray()" placeholder="Select one or more supervisors" multiple/>
                        </div>
                    </div>
                </div>
                <hr class="field-hr">
            @endif
            {{-- Notes --}}
            @if (Auth::user()->company_id == $site->company_id)
                <div class="row">
                    <div class="form-group {{ $errors->has('notes') ? 'has-error' : '' }}">
                        <label for="notes" class="col-md-3 control-label">Notes:</label>
                        <div class="col-md-9">
                            <x-form.textarea name="notes" :value="$site->notes" rows="3"/>
                            <span class="help-block"> For internal use only</span>
                        </div>
                    </div>
                </div>
            @endif
            <br>
            <div class="form-actions right">
                <button class="btn default" onclick="cancelForm(event, 'site')">Cancel</button>
                <button type="submit" class="btn green"> Save</button>
            </div>
        </form>
    </div>
</div>