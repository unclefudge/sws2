<div>
    {{-- Fullscreen devices --}}
    @if ($main->status && $main->items->count() == $main->itemsChecked()->count())
        <div class="col-md-12 note note-warning">
            <p>All items have been completed and request requires
                <button class="btn btn-xs btn-outline dark disabled">Sign Off</button>
                at the bottom
            </p>
        </div>
    @endif
    <form wire:submit.prevent="saveForm">
        <div class="row">
            {{-- Site Details --}}
            <div class="col-md-5" x-data="{editSite: false}">
                <div class="row">
                    <div class="col-md-12">
                        <h4>Site Details
                            @if ($main->status > 0 && Auth::user()->allowed2('edit.site.maintenance', $main))
                                <button class="btn dark btn-outline btn-sm pull-right" style="margin-top: -10px; border: 0px" x-on:click="editSite = !editSite" x-text="editSite ? 'View' : 'Edit'"></button>
                            @endif
                        </h4>
                    </div>
                </div>
                <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                @if ($main->site)
                    <b>{{ $main->site->name }}</b><br>
                    {{ $main->site->full_address }}<br>
                @endif
                <br>
                <div x-show="!editSite">
                    @if ($main->completed)
                        <b>Prac Completion:</b> {{ $main->completed->format('d/m/Y') }}<br>
                    @endif
                    @if ($main->supervisor)
                        <b>Supervisor:</b> {{ $main->supervisor }}
                    @endif
                </div>
                <div x-show="editSite">
                    <div class="form-group {!! fieldHasError('completed', $errors) !!}">
                        <label for="completed" class="control-label">Prac Completed</label>
                        <input wire:model.live="m_completed" type="text" name="completed" class="form-control" placeholder="dd/mm/yyyy">
                    </div>
                    <div class="form-group {!! fieldHasError('supervisor', $errors) !!}">
                        <label for="m_supervisor" class="control-label">Supervisor</label>
                        <input wire:model.live="m_supervisor" type="text" name="m_supervisor" class="form-control">
                        {!! fieldErrorMessage('supervisor', $errors) !!}
                    </div>
                </div>

            </div>
            <div class="col-md-1"></div>
            {{-- Client Details --}}
            <div class="col-md-6" x-data="{editClient: false}">
                <div class="row">
                    <div class="col-md-5">
                        <h4>Client Details
                            @if ($main->status > 0 && Auth::user()->allowed2('edit.site.maintenance', $main))
                                <button class="btn dark btn-outline btn-sm pull-right" style="margin: -10px 0 0 50px; border: 0" x-on:click="editClient = !editClient" x-text="editClient ? 'View' : 'Edit'"></button>
                            @endif
                        </h4>
                    </div>
                    <div class="col-md-7">
                        <h2 style="margin: 0px; padding-right: 20px">
                            @if($main->status == '-1')
                                <span class="pull-right font-red hidden-sm hidden-xs">DECLINED</span>
                                <span class="text-center font-red visible-sm visible-xs">DECLINED</span>
                            @endif
                            @if($main->status == '0')
                                <span class="pull-right font-red hidden-sm hidden-xs"><small
                                            class="font-red">COMPLETED {{ $main->updated_at->format('d/m/Y') }}</small></span>
                                <span class="text-center font-red visible-sm visible-xs">COMPLETED {{ $main->updated_at->format('d/m/Y') }}</span>
                            @endif
                            @if($main->status == '1')
                                <span class="pull-right font-red hidden-sm hidden-xs">ACTIVE</span>
                                <span class="text-center font-red visible-sm visible-xs">ACTIVE</span>
                            @endif
                            @if($main->status == '2')
                                <span class="pull-right font-red hidden-sm hidden-xs">UNDER REVIEW</span>
                                <span class="text-center font-red visible-sm visible-xs">UNDER REVIEW</span>
                            @endif
                            @if($main->status == '4')
                                <span class="pull-right font-red hidden-sm hidden-xs">ON HOLD</span>
                                <span class="text-center font-red visible-sm visible-xs">ON HOLD</span>
                            @endif
                        </h2>
                    </div>
                </div>
                <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                <div x-show="!editClient">
                    @if ($main->contact_name)
                        <b>{{ $main->contact_name }}</b>
                    @endif<br>
                    @if ($main->contact_phone)
                        {{ $main->contact_phone }}<br>
                    @endif
                    @if ($main->contact_email)
                        {{ $main->contact_email }}<br>
                    @endif
                    @if($main->nextClientVisit())
                        <br><b>Scheduled Visit:</b> {{ ($main->nextClientVisit()->entity_type == 'c' && $main->nextClientVisit()->company ) ? $main->nextClientVisit()->company->name : 'Unassigned Company'}}
                        &nbsp; ({{ $main->nextClientVisit()->from->format('d/m/Y') }})<br>
                    @endif
                </div>
                <div x-show="editClient">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group {!! fieldHasError('m_contact_name', $errors) !!}">
                                <label for="m_contact_name" class="control-label">Name</label>
                                <input wire:model.live="m_contact_name" type="text" name="m_contact_name" class="form-control">
                                {!! fieldErrorMessage('m_contact_name', $errors) !!}
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group {!! fieldHasError('m_contact_phone', $errors) !!}">
                                <label for="m_contact_phone" class="control-label">Phone</label>
                                <input wire:model.live="m_contact_phone" type="text" name="m_contact_phone" class="form-control">
                                {!! fieldErrorMessage('m_contact_phone', $errors) !!}
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="form-group {!! fieldHasError('m_contact_email', $errors) !!}">
                                <label for="m_contact_email" class="control-label">Email</label>
                                <input wire:model.live="m_contact_email" type="text" name="m_contact_email" class="form-control">
                                {!! fieldErrorMessage('m_contact_email', $errors) !!}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Gallery --}}
        <br>
        <div x-data="{editGallery: false}">
            <div x-show="!editGallery" class="row">
                <div class="col-md-7">
                    <h4>Photos
                        @if(Auth::user()->allowed2('add.site.maintenance') || Auth::user()->allowed2('edit.site.maintenance', $main))
                            <button class="btn dark btn-outline btn-sm pull-right" style="margin-top: -10px; border: 0px" x-on:click="editGallery = !editGallery" x-text="editGallery ? 'View' : 'Edit'"></button>
                        @endif</h4>
                    <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                    @include('site/maintenance/_gallery')
                </div>
                <div class="col-md-1"></div>
                <div class="col-md-4" id="docs-show">
                    <h4>Documents
                        @if(Auth::user()->allowed2('add.site.maintenance') || Auth::user()->allowed2('edit.site.maintenance', $main))
                            <button class="btn dark btn-outline btn-sm pull-right" style="margin-top: -10px; border: 0px" x-on:click="editGallery = !editGallery" x-text="editGallery ? 'View' : 'Edit'"></button>
                        @endif
                    </h4>
                    <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                    @include('site/maintenance/_docs')
                </div>
            </div>
            <div x-show="editGallery">
                <h4>Photos / Documents
                    @if(Auth::user()->allowed2('add.site.maintenance') || Auth::user()->allowed2('edit.site.maintenance', $main))
                        <button class="btn dark btn-outline btn-sm pull-right" style="margin-top: -10px; border: 0px" x-on:click="editGallery = !editGallery" x-text="editGallery ? 'View' : 'Edit'"></button>
                    @endif</h4>
                <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                <div class="row">
                    <div class="col-md-6" style="background: #f1f0ef">
                        {{--}}<input type="file" class="filepond" name="filepond[]" multiple/><br><br>--}}
                        {{--}}<x-livewire-filepond wire:model="filepond"/>--}}
                    </div>
                </div>
                <br>
            </div>
        </div>

        {{-- Under Review - asign to super --}}
        <h4>Maintenance Details</h4>
        <hr style="padding: 0px; margin: 0px 0px 10px 0px">
        <div class="row">
            {{-- Category --}}
            <div class="col-md-3 ">
                <div class="form-group">
                    {!! Form::label('category_id', 'Category', ['class' => 'control-label']) !!}
                    @if ($main->status && Auth::user()->allowed2('sig.site.maintenance', $main))
                        {!! Form::select('category_id', (['' => 'Select category'] + \App\Models\Site\SiteMaintenanceCategory::all()->sortBy('name')->pluck('name' ,'id')->toArray()), null, ['class' => 'form-control select2', 'title' => 'Select category', 'id' => 'category_id']) !!}
                    @else
                        {!! Form::text('category_text', ($main->category_id) ? \App\Models\Site\SiteMaintenanceCategory::find($main->category_id)->name : 'Select Category', ['class' => 'form-control', 'readonly']) !!}
                    @endif
                </div>
            </div>

            {{-- Warranty --}}
            @inject('maintenanceWarranty', 'App\Http\Utilities\MaintenanceWarranty')
            <div class="col-md-2 ">
                <div class="form-group">
                    {!! Form::label('warranty', 'Warranty', ['class' => 'control-label']) !!}
                    @if ($main->status && Auth::user()->allowed2('sig.site.maintenance', $main))
                        {!! Form::select('warranty', $maintenanceWarranty::all(), $main->warranty, ['class' => 'form-control bs-select', 'id' => 'warranty']) !!}
                    @else
                        {!! Form::text('warranty_text', $maintenanceWarranty::name($main->warranty), ['class' => 'form-control', 'readonly']) !!}
                    @endif
                </div>
            </div>

            {{-- Client Contacted --}}
            <div class="col-md-2">
                {!! Form::label('client_contacted', 'Client Contacted', ['class' => 'control-label']) !!}
                @if ($main->status && Auth::user()->allowed2('edit.site.maintenance', $main) || Auth::user()->allowed2('sig.site.maintenance', $main))
                    <div wire:ignore class="input-group date date-picker">
                        <input wire:model.live="m_client_contacted" type="text" name="m_contact_phone" class="form-control form-control-inline" style="background:#FFF" data-date-format="dd-mm-yyyy">
                        <span class="input-group-btn">
                            <button class="btn default date-set" type="button"><i class="fa fa-calendar"></i></button>
                        </span>
                        {!! fieldErrorMessage('m_client_contacted', $errors) !!}
                    </div>
                @else
                    {!! Form::text('client_contacted', ($main->client_contacted) ? $main->client_contacted->format('d/m/Y') : '', ['class' => 'form-control', 'readonly']) !!}
                @endif
            </div>

            {{-- Client Appointment --}}
            <div class="col-md-2">
                {!! Form::label('client_appointment', 'Client Appointment', ['class' => 'control-label']) !!}
                @if ($main->status && Auth::user()->allowed2('edit.site.maintenance', $main) || Auth::user()->allowed2('sig.site.maintenance', $main) )
                    <div class="input-group">
                        <datepicker :value.sync="xx.client_appointment" format="dd/MM/yyyy" :placeholder="choose date"></datepicker>
                    </div>
                    <input v-model="xx.client_appointment" type="hidden" name="client_appointment"
                           value="{{  ($main->client_appointment) ? $main->client_appointment->format('d/m/Y') : ''}}">
                @else
                    {!! Form::text('client_appointment', ($main->client_appointment) ? $main->client_appointment->format('d/m/Y') : '', ['class' => 'form-control', 'readonly']) !!}
                @endif
            </div>

            {{-- Status --}}
            <div class="col-md-2 pull-right">
                <div class="form-group">
                    {!! Form::label('status', 'Status', ['class' => 'control-label']) !!}
                    @if ($main->status && Auth::user()->allowed2('sig.site.maintenance', $main))
                        {!! Form::select('status', ['1' => 'Active', '-1' => 'Decline',  '4' => 'On Hold'], $main->status, ['class' => 'form-control bs-select', 'id' => 'status']) !!}
                    @elseif ($main->status && Auth::user()->allowed2('edit.site.maintenance', $main))
                        {!! Form::select('status', ['1' => 'Active', '4' => 'On Hold'], $main->status, ['class' => 'form-control bs-select', 'id' => 'status']) !!}
                    @elseif ($main->status == 0 && Auth::user()->allowed2('edit.site.maintenance', $main))
                        {!! Form::select('status', ['0' => 'Completed', '1' => 'Re-Activate'], $main->status, ['class' => 'form-control bs-select', 'id' => 'status']) !!}
                    @else
                        {!! Form::text('status_text', ($main->status == 0) ? 'Completed' : 'Declined', ['class' => 'form-control', 'readonly']) !!}
                    @endif
                </div>
            </div>
        </div>

        <div class="row note note-warning" id="onhold-div"
             style="{{ fieldHasError('onhold_reason', $errors) ? 'display:show' : 'display:none' }}">
            {{-- On Hold Reason --}}
            <div class="col-md-12">
                <div class="form-group {!! fieldHasError('onhold_reason', $errors) !!}"
                     style="{{ fieldHasError('onhold_reason', $errors) ? '' : 'display:show' }}"
                     id="onhold_reason-div">
                    {!! Form::label('onhold_reason', 'Please specify the reason for placing request ON HOLD', ['class' => 'control-label']) !!}
                    {!! Form::text('onhold_reason', null, ['class' => 'form-control', 'id' => 'onhold_reason']) !!}
                    {!! fieldErrorMessage('onhold_reason', $errors) !!}
                </div>
            </div>
        </div>

        <button type="submit" class="btn mt-3 btn-primary">Save</button>
    </form>

    @section('page-level-plugins-head')
        <link href="https://unpkg.com/filepond/dist/filepond.css" rel="stylesheet" type="text/css"/>   {{-- Filepond --}}
        <link href="/assets/global/plugins/select2/css/select2.min.css" rel="stylesheet" type="text/css"/>
        <link href="/assets/global/plugins/select2/css/select2-bootstrap.min.css" rel="stylesheet" type="text/css"/>
        <link href="/assets/global/plugins/bootstrap-datepicker/css/bootstrap-datepicker3.min.css" rel="stylesheet" type="text/css"/>
        <script type="text/javascript">var html5lightbox_options = {watermark: "", watermarklink: ""};</script>
    @stop

    @section('page-level-plugins')
        <script src="/assets/global/plugins/select2/js/select2.full.min.js" type="text/javascript"></script>
        <script src="/assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js" type="text/javascript"></script>
        <script src="/js/moment.min.js" type="text/javascript"></script>
        <script src="https://unpkg.com/filepond/dist/filepond.min.js"></script> {{-- FilePond --}}
        <script src="/js/libs/html5lightbox/html5lightbox.js" type="text/javascript"></script>
    @stop

    <script src="/assets/pages/scripts/components-date-time-pickers.min.js" type="text/javascript"></script>
    <script>
        $.ajaxSetup({headers: {'X-CSRF-Token': $('meta[name=token]').attr('value')}});

        $(document).ready(function () {
            /* Select2 */
            $("#super_id").select2({placeholder: "Select supervisor", width: '100%'});
            $("#assigned_to").select2({placeholder: "Select company", width: '100%'});
            $("#category_id").select2({placeholder: "Select category", width: "100%"});
        });

        ('#datepicker').datepicker({
            dateFormat: 'dd-mm-yy',
            onSelect: function (date) {@this.set('m_client_contacted', date)
                ;
            }
        });
    </script>
</div>
