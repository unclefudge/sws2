{{-- Add Notes --}}
<div class="portlet light" style="display: none;" id="add_notes">
    <div class="portlet-title">
        <div class="caption">
            <span class="caption-subject font-dark bold uppercase">Services Overview (Notes)</span>
            <span class="caption-helper"> &nbsp; private to Cape Cod</span>
        </div>
    </div>
    <div class="portlet-body form">
        <form method="POST" action="{{ action([App\Http\Controllers\Company\CompanyController::class, 'addNote'], $company->id) }}" class="horizontal-form">
            @csrf
            <div class="row">
                <div class="col-md-12">
                    <x-form.textarea name="action" label="Description:" rows="3" placeholder="enter note description"/>
                </div>
            </div>
            <br>
            <div class="form-actions right">
                <button class="btn default" onclick="cancelForm(event, 'notes')">Cancel</button>
                <button type="submit" class="btn green"> Save</button>
            </div>
        </form>
    </div>
</div>
