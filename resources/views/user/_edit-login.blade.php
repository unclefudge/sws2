{{-- Edit Login Details --}}
<div class="portlet light" style="display: none;" id="edit_login">
    <div class="portlet-title">
        <div class="caption">
            <span class="caption-subject font-dark bold uppercase">Login Details</span>
        </div>
    </div>
    <div class="portlet-body form">
        <form method="POST" action="{{ action([App\Http\Controllers\UserController::class, 'updateLogin'], $user->id) }}">
            @csrf
        <x-form.hidden name="password_update" :value="Auth::user()->password_reset ? 1 : 0"/>
        <x-form.hidden name="user" :value="Auth::user()->id == $user->id ? 1 : 0"/>

        {{-- Status --}}
        <div class="row">
            @if(Auth::user()->allowed2('del.user', $user) && Auth::user()->id != $user->id)
                <div class="form-group">
                    <label for="status" class="col-md-3 control-label">Status:</label>
                    <div class="col-md-9">
                        <x-form.select name="status" :options="['1' => 'Active', '0' => 'Inactive']" :value="$user->status"/>
                        <span class="help-block"> Only editable by user with security access</span>
                    </div>
                </div>
            @else
                <div class="col-md-3">Status:</div>
                <div class="col-xs-9">
                    {!! $user->status_text !!}
                    @if (Auth::user()->allowed2('del.user', $user) && Auth::user()->id == $user->id)
                        <span class="help-block">Can't disable own account</span>
                    @endif
                </div>
            @endif
        </div>
        <hr class="field-hr">

        @if ($user->status)
            {{-- Username --}}
            <div class="row">
                <div class="form-group">
                    <label for="username" class="col-md-3 control-label">Username:</label>
                    <div class="col-md-9">
                        <x-form.input name="username" :value="$user->username" required/>
                    </div>
                </div>
            </div>
            <hr class="field-hr">

            {{-- Password --}}
            <div class="row">
                <div class="form-group @if (Auth::user()->password_reset) has-error @endif">
                    <label for="password" class="col-md-3 control-label">Password:</label>
                    <div class="col-md-9">
                        @if (Auth::user()->id == $user->id)
                            <x-form.input name="password" type="password" placeholder="Leave blank to keep password unchanged"/>
                        @else
                            <x-form.input name="password" placeholder="Leave blank to keep password unchanged"/>
                        @endif
                        @if (Auth::user()->id != $user->id)
                            <span class="help-block">User will be forced to choose new password upon login</span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Confirm Password --}}
            <div id="password_confirmation_div" style="@if (!Auth::user()->password_reset && !old('password')) display:none @endif">
                <hr class="field-hr">
                <div class="row">
                    <div class="form-group @if (Auth::user()->password_reset) has-error @endif">
                        <label for="password_confirmation" class="col-md-3 control-label">Re-type Password:</label>
                        <div class="col-md-9">
                            <x-form.input name="password_confirmation" type="password" placeholder="Re-type password"/>
                        </div>
                    </div>
                </div>
            </div>
        @else
            {{-- Pass Required Fields as hidden --}}
            <x-form.hidden name="username" :value="$user->username"/>
        @endif

        <br>
        <div class="form-actions right">
            @if ($user->status == 2)
                <button type="submit" class="btn green"> Continue</button>
            @else
                <button class="btn default" onclick="cancelForm(event, 'login')">Cancel</button>
                <button type="submit" class="btn green"> Save</button>
            @endif
        </div>
        </form>
    </div>
</div>