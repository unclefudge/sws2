{{-- Edit Company Leave --}}
<div class="portlet light" style="display: none;" id="edit_leave">
    <div class="portlet-title">
        <div class="caption">
            <span class="caption-subject font-dark bold uppercase">Company Leave</span>
        </div>
        <div class="actions">
            @if (Auth::user()->allowed2('edit.company.leave', $company) && $company->status)
                <button class="btn btn-circle green btn-outline btn-sm" onclick="addForm('leave')">Add</button>
            @endif
        </div>
    </div>
    <div class="portlet-body form">
        {{-- Leave --}}
        @if ($company->leave()->whereDate('to', '>', date('Y-m-d'))->first())
            <form method="POST" action="{{ action([App\Http\Controllers\Company\CompanyController::class, 'updateLeave'], $company->id) }}">
                @csrf
                @foreach($company->leave()->whereDate('to', '>', date('Y-m-d'))->get() as $leave)
                    {{-- Dates --}}
                    <div class="row">
                        <label for="from-{{ $leave->id }}" class="col-md-3 control-label">Leave From:</label>
                        <div class="col-md-9">
                            <x-form.date-range :from="'from-' . $leave->id" :to="'to-' . $leave->id" :from-value="$leave->from->format('d/m/Y')" :to-value="$leave->to->format('d/m/Y')" :disabled="$leave->from->lt(Carbon\Carbon::now())"/>
                            <x-form.error :name="'start_date-' . $leave->id"/>
                        </div>
                    </div>
                    <br>
                    {{-- Note --}}
                    <div class="row">
                        <label for="notes-{{ $leave->id }}" class="col-md-3 control-label">Notes:</label>
                        <div class="col-md-9">
                            <x-form.textarea :name="'notes-' . $leave->id" :value="$leave->notes" rows="2" required/>
                        </div>
                    </div>
                    {{-- Delete --}}
                    <div class="row">
                        <div class="form-group">
                            <div class="col-md-12">
                                <div class="mt-checkbox-list">
                                    <label class="mt-checkbox mt-checkbox-outline pull-right"> Mark to be Deleted
                                        <input type="checkbox" value="{{ $leave->id }}" name="leave_del[]">
                                        <span></span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    @if(!$loop->last)
                        <hr class="field-hr">
                    @endif
                @endforeach
                <br>
                <div class="form-actions right">
                    <button class="btn default" onclick="cancelForm(event, 'leave')">Cancel</button>
                    <button type="submit" class="btn green"> Save</button>
                </div>
            </form>
        @else
            <div class="row">
                <div class="col-md-12">Currenty no scheduled leave. Use
                    <button class="btn btn-circle green btn-outline btn-sm" onclick="addForm('leave')">Add</button>
                    button to create.
                </div>
            </div>
        @endif
    </div>
</div>
