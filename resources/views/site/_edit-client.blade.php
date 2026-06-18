{{-- Edit Company Details --}}
<div class="portlet light" style="display: none;" id="edit_client">
    <div class="portlet-title">
        <div class="caption">
            <span class="caption-subject font-dark bold uppercase">Client Details</span> &nbsp; <span class="font-yellow small">*Fields Imported from Zoho</span>
        </div>
    </div>
    <div class="portlet-body form">
        <form method="POST" action="{{ action([App\Http\Controllers\Site\SiteController::class, 'updateClient'], $site->id) }}">
            @csrf
            {{-- Primary Details --}}
            <div class="row">
                <div class="col-md-12"><b>Primary Contact</b></div>
            </div>
            <hr class="field-hr">
            {{-- Primary Title --}}
            <div class="row">
                <div class="form-group {{ $errors->has('client1_title') ? 'has-error' : '' }}">
                    <label for="client1_title" class="col-md-3 control-label font-yellow">Title:</label>
                    <div class="col-md-9">
                        <x-form.input name="client1_title" :value="$site->client1_title"/>
                    </div>
                </div>
            </div>
            <hr class="field-hr">
            {{-- Primary Firstname --}}
            <div class="row">
                <div class="form-group {{ $errors->has('client1_firstname') ? 'has-error' : '' }}">
                    <label for="client1_firstname" class="col-md-3 control-label font-yellow">First Name:</label>
                    <div class="col-md-9">
                        <x-form.input name="client1_firstname" :value="$site->client1_firstname"/>
                    </div>
                </div>
            </div>
            <hr class="field-hr">
            {{-- Primary Lastname --}}
            <div class="row">
                <div class="form-group {{ $errors->has('client1_lastname') ? 'has-error' : '' }}">
                    <label for="client1_lastname" class="col-md-3 control-label font-yellow">Last Name:</label>
                    <div class="col-md-9">
                        <x-form.input name="client1_lastname" :value="$site->client1_lastname"/>
                    </div>
                </div>
            </div>
            <hr class="field-hr">
            {{-- Primary Phone --}}
            <div class="row">
                <div class="form-group {{ $errors->has('client1_mobile') ? 'has-error' : '' }}">
                    <label for="client1_mobile" class="col-md-3 control-label font-yellow">Mobile:</label>
                    <div class="col-md-9">
                        <x-form.input name="client1_mobile" :value="$site->client1_mobile"/>
                    </div>
                </div>
            </div>
            <hr class="field-hr">
            {{-- Primary Email --}}
            <div class="row">
                <div class="form-group {{ $errors->has('client1_email') ? 'has-error' : '' }}">
                    <label for="client1_email" class="col-md-3 control-label font-yellow">Email:</label>
                    <div class="col-md-9">
                        <x-form.input name="client1_email" :value="$site->client1_email"/>
                    </div>
                </div>
            </div>
            <hr class="field-hr">

            {{-- Secondary Details --}}
            <div class="row">
                <div class="col-md-12"><br><b>Secondary Contact</b></div>
            </div>
            <hr class="field-hr">
            {{-- Primary Title --}}
            <div class="row">
                <div class="form-group {{ $errors->has('client2_title') ? 'has-error' : '' }}">
                    <label for="client1_title" class="col-md-3 control-label font-yellow">Title:</label>
                    <div class="col-md-9">
                        <x-form.input name="client2_title" :value="$site->client2_title"/>
                    </div>
                </div>
            </div>
            <hr class="field-hr">
            {{-- Primary Firstname --}}
            <div class="row">
                <div class="form-group {{ $errors->has('client2_firstname') ? 'has-error' : '' }}">
                    <label for="client2_firstname" class="col-md-3 control-label font-yellow">First Name:</label>
                    <div class="col-md-9">
                        <x-form.input name="client2_firstname" :value="$site->client2_firstname"/>
                    </div>
                </div>
            </div>
            <hr class="field-hr">
            {{-- Primary Lastname --}}
            <div class="row">
                <div class="form-group {{ $errors->has('client2_lastname') ? 'has-error' : '' }}">
                    <label for="client2_lastname" class="col-md-3 control-label font-yellow">Last Name:</label>
                    <div class="col-md-9">
                        <x-form.input name="client2_lastname" :value="$site->client2_lastname"/>
                    </div>
                </div>
            </div>
            <hr class="field-hr">
            {{-- Primary Phone --}}
            <div class="row">
                <div class="form-group {{ $errors->has('client2_mobile') ? 'has-error' : '' }}">
                    <label for="client2_mobile" class="col-md-3 control-label font-yellow">Mobile:</label>
                    <div class="col-md-9">
                        <x-form.input name="client2_mobile" :value="$site->client2_mobile"/>
                    </div>
                </div>
            </div>
            <hr class="field-hr">
            {{-- Primary Email --}}
            <div class="row">
                <div class="form-group {{ $errors->has('client2_email') ? 'has-error' : '' }}">
                    <label for="client2_email" class="col-md-3 control-label font-yellow">Email:</label>
                    <div class="col-md-9">
                        <x-form.input name="client2_email" :value="$site->client2_email"/>
                    </div>
                </div>
            </div>
            <hr class="field-hr">
            <br>
            {{-- Client Intro--}}
            <div class="row">
                <div class="form-group {{ $errors->has('client_intro') ? 'has-error' : '' }}">
                    <label for="client_intro" class="col-md-3 control-label font-yellow">Letter intro:</label>
                    <div class="col-md-9">
                        <x-form.input name="client_intro" :value="$site->client_intro"/>
                    </div>
                </div>
            </div>
            <br>
            <div class="form-actions right">
                <button class="btn default" onclick="cancelForm(event, 'client')">Cancel</button>
                <button type="submit" class="btn green"> Save</button>
            </div>
        </form>
    </div>
</div>