@inject('ozstates', 'App\Http\Utilities\OzStates')

<div class="tab-pane {{ $tabs['0'] == 'settings' ? 'active' : '' }}" id="tab_settings">
    <div class="row profile-account">
        <div class="col-md-3">
            <ul class="ver-inline-menu tabbable margin-bottom-10">
                <li class="{{ $tabs['1'] == 'info' ? 'active' : '' }}">
                    <a data-toggle="tab" href="#tab_settings_info"><i class="fa fa-users"></i> Client Info </a>
                </li>
                <li class="{{ $tabs['1'] == 'security' ? 'active' : '' }}">
                    <a data-toggle="tab" href="#tab_settings_security"><i class="fa fa-eye"></i> Security Settings </a>
                </li>
            </ul>
        </div>
        <div class="col-md-9">
            <div class="tab-content">
                {{-- Info Tab --}}
                <div id="tab_settings_info" class="tab-pane {{ $tabs['1'] == 'info' ? 'active' : '' }}">
                    <form method="POST" action="{{ action([App\Http\Controllers\Misc\ClientController::class, 'update'], $client->slug) }}">
                        @csrf
                        @method('PATCH')
                        <x-form.hidden name="tabs" value="settings:info"/>
                        <x-form.hidden name="company_id" :value="Auth::User()->company_id"/>
                        <x-form.hidden name="id" :value="$client->id"/>
                        <x-form.hidden name="slug" :value="$client->slug"/>
                        <div class="form-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h3 class="font-green sbold uppercase" style="margin:0 0 10px;">{{ $client->name }}</h3>
                                </div>
                                <div class="col-md-6">
                                    {{-- Inactive --}}
                                    @if(!$client->status)
                                        <h3 class="pull-right font-red uppercase" style="margin:0 0 10px;">Inactive Client</h3>
                                    @endif
                                </div>
                            </div>

                            @include('form-error')
                            {{-- name --}}
                            <div class="row">
                                <div class="col-md-6">
                                    <x-form.input name="name" label="Name" :value="$client->name"/>
                                </div>
                                <div class="col-md-3 pull-right">
                                    <x-form.select name="status" label="Status" :options="['1' => 'Active', '0' => 'Inactive']" :value="$client->status"/>
                                </div>
                            </div>

                            {{-- address --}}
                            <div class="row">
                                <div class="col-md-12">
                                    <x-form.input name="address" label="Address" :value="$client->address"/>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <x-form.input name="suburb" label="Suburb" :value="$client->suburb"/>
                                </div>
                                <div class="col-md-3">
                                    <x-form.select name="state" label="State" :options="$ozstates::all()" value="NSW"/>
                                </div>
                                <div class="col-md-3">
                                    <x-form.input name="postcode" label="Postcode" :value="$client->postcode"/>
                                </div>
                            </div>

                            {{-- Phone + Email --}}
                            <div class="row">
                                <div class="col-md-6">
                                    <x-form.input name="phone" label="Phone" :value="$client->phone"/>
                                </div>
                                <div class="col-md-6">
                                    <x-form.input name="email" label="Email" :value="$client->email"/>
                                </div>
                            </div>
                            <hr>
                            {{-- Notes --}}
                            <div class="row">
                                <div class="col-md-12">
                                    <x-form.textarea name="notes" label="Notes" :value="$client->notes" rows="2"/>
                                    <span class="help-block"> For internal use only </span>
                                </div>
                            </div>

                            <div class="margiv-top-10">
                                <button type="submit" class="btn green"> Save Changes</button>
                                <a href="/client/{{ $client->slug }}/settings/info">
                                    <button type="button" class="btn default"> Cancel</button>
                                </a>

                            </div>
                        </div>
                    </form>
                </div>

                <div id="tab_settings_security" class="tab-pane {{ $tabs['1'] == 'security' ? 'active' : '' }}">
                    <div class="row">
                        <div class="col-md-6">
                            <h3 class="font-green sbold uppercase" style="margin:0 0 10px;">{{ $client->name }}</h3>
                        </div>
                        <div class="col-md-6">
                            {{-- Inactive --}}
                            @if(!$client->status)
                                <h3 class="pull-right font-red uppercase" style="margin:0 0 10px;">Inactive Client</h3>
                            @endif
                        </div>
                    </div>
                    <form action="#">
                        <table class="table table-bordered table-striped">
                            <tr>
                                <td> Anim pariatur cliche reprehenderit, enim eiusmod high life accusamus..</td>
                                <td>
                                    <label class="uniform-inline">
                                        <input type="radio" name="optionsRadios1" value="option1"/> Yes </label>
                                    <label class="uniform-inline">
                                        <input type="radio" name="optionsRadios1" value="option2" checked/> No </label>
                                </td>
                            </tr>
                            <tr>
                                <td> Enim eiusmod high life accusamus terry richardson ad squid wolf moon</td>
                                <td>
                                    <label class="uniform-inline">
                                        <input type="checkbox" value=""/> Yes </label>
                                </td>
                            </tr>
                            <tr>
                                <td> Enim eiusmod high life accusamus terry richardson ad squid wolf moon</td>
                                <td>
                                    <label class="uniform-inline">
                                        <input type="checkbox" value=""/> Yes </label>
                                </td>
                            </tr>
                            <tr>
                                <td> Enim eiusmod high life accusamus terry richardson ad squid wolf moon</td>
                                <td>
                                    <label class="uniform-inline">
                                        <input type="checkbox" value=""/> Yes </label>
                                </td>
                            </tr>
                        </table>
                        {{--end profile-settings--}}
                        <div class="margin-top-10">
                            <a href="javascript:;" class="btn green"> Save Changes </a>
                            <a href="javascript:;" class="btn default"> Cancel </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>