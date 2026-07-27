{{-- Edit  Compliance Manaement  --}}
<div class="portlet light" style="display: none;" id="edit_compliance">
    <div class="portlet-title">
        <div class="caption">
            <span class="caption-subject font-dark bold uppercase">Compliance Management</span>
        </div>
        <div class="actions">
            @if (Auth::user()->allowed2('edit.compliance.manage', $company) && $company->status)
                <button class="btn btn-circle green btn-outline btn-sm" onclick="addForm('compliance')">Add</button>
            @endif
        </div>
    </div>
    <div class="portlet-body form">
        {{-- Current Overrides --}}
        @if ($company->complianceOverrides()->count())
            <form method="POST" action="{{ action([App\Http\Controllers\Company\CompanyController::class, 'updateCompliance'], $company->id) }}">
                @csrf
                @foreach ($company->complianceOverrides() as $over)
                    {{-- Overtpe Type --}}
                    <div class="row">
                        <label for="compliance_text-{{ $over->id }}" class="col-md-3 control-label">Override Type:</label>
                        <div class="col-md-9">
                            <x-form.input :name="'compliance_text-' . $over->id" :value="$overrideTypes::name($over->type)" required disabled/>
                            <x-form.hidden :name="'compliance_type-' . $over->id" :value="$over->id"/>
                        </div>
                    </div><br>q


                    {{-- Required --}}
                    @if ($over->type != 'cdu')
                        <div class="row">
                            <label for="required-{{ $over->id }}" class="col-md-3 control-label">Required:</label>
                            <div class="col-md-9">
                                <x-form.select :name="'required-' . $over->id" :options="['0' => 'No', '1' => 'Yes']" :value="$over->required"/>
                                    <?php $cat = substr($over->type, 2) ?>
                                <span class="help-block"> By default this document {!! ($company->requiresCompanyDoc($cat, 'system')) ? '<b>IS</b>' : 'is <b>NOT</b>' !!} <b>REQUIRED</b> for this company to be compliant</span>
                            </div>
                        </div><br>
                    @endif

                    {{-- Reason --}}
                    <div class="row">
                        <label for="reason-{{ $over->id }}" class="col-md-3 control-label">Reason:</label>
                        <div class="col-md-9">
                            <x-form.textarea :name="'reason-' . $over->id" :value="$over->reason" rows="2" required/>
                        </div>
                    </div><br>

                    {{-- Expiry --}}
                    <div class="row">
                        <label for="expiry-{{ $over->id }}" class="col-md-3 control-label">Expiry:</label>
                        <div class="col-md-9">
                            <x-form.datepicker :name="'expiry-' . $over->id" :value="($over->expiry) ? $over->expiry->format('d/m/Y') : null" placeholder="Leave blank to never expire" format="dd/mm/yyyy" clear-button readonly/>
                        </div>
                    </div><br>

                    {{-- Delete --}}
                    <div class="row">
                        <div class="form-group">
                            <div class="col-md-12">
                                <div class="mt-checkbox-list">
                                    <label class="mt-checkbox mt-checkbox-outline pull-right"> Mark to be Deleted
                                        <input type="checkbox" value="{{ $over->id }}" name="co_del[]"/>
                                        <span></span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    @if (!$loop->last)
                        <hr class="field-hr">
                    @endif
                @endforeach

                <div class="form-actions right">
                    <button class="btn default" onclick="cancelForm(event, 'compliance')">Cancel</button>
                    <button type="submit" class="btn green"> Save</button>
                </div>
            </form>
        @else
            <div class="row">
                <div class="col-md-12">Currenty no overrides are set. Use
                    <button class="btn btn-circle green btn-outline btn-sm" onclick="addForm('compliance')">Add</button>
                    button to create one.
                </div>
            </div>
        @endif

    </div>
</div>
