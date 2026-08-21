<div>
    @once
        <style>
            .wms-unsaved {
                margin-left:10px;
                font-size:12px;
                font-weight:600;
                text-transform:uppercase;
                color:#e7505a;
            }

            .wms-header {
                border-bottom:1px solid #dfe4e8;
                padding-bottom:12px;
                margin-bottom:14px;
            }

            .wms-company {
                margin:0 0 5px;
                color:#424c55;
                font-weight:700;
            }

            .wms-principal {
                text-align:right;
                padding-top:8px;
            }

            .wms-doc-fields {
                margin-bottom:18px;
            }

            .wms-builder-header {
                margin-left:0;
                margin-right:0;
                border:1px solid #dfe4e8;
                background:#f5f7f9;
                padding:9px 0;
                font-weight:600;
                color:#4b555f;
            }

            @media (min-width:992px) {
                .wms-col-step {
                    width:22%;
                }

                .wms-col-hazard {
                    width:25%;
                }

                .wms-col-control {
                    width:53%;
                }
            }

            .wms-step-row {
                border:1px solid #e4e8eb;
                border-top:0;
                background:#fff;
                margin:0;
            }

            .wms-step-cell,
            .wms-hazard-cell,
            .wms-control-cell {
                min-height:105px;
                padding:12px 14px;
            }

            .wms-step-cell,
            .wms-hazard-cell {
                border-right:1px solid #e8ecef;
            }

            .wms-step-number {
                display:inline-block;
                min-width:26px;
                font-weight:700;
                color:#5e6973;
            }

            .wms-item {
                position:relative;
                padding:7px 0;
                border-bottom:1px solid #edf0f2;
                color:#5c6872;
                white-space:normal;
            }

            .wms-item-text {
                white-space:pre-line;
            }

            .wms-item:after {
                content:"";
                display:table;
                clear:both;
            }

            .wms-item:last-child {
                border-bottom:0;
            }

            .wms-item-diff {
                color:#e7505a;
            }

            .wms-item-actions {
                float:right;
                position:relative;
                margin:0 0 4px 10px;
                white-space:nowrap;
            }

            .wms-item-menu-toggle {
                width:28px;
                height:28px;
                min-width:28px;
                padding:2px 6px !important;
                border:1px solid #d9dfe4 !important;
                border-radius:3px !important;
                background:#f1f3f5 !important;
                color:#5f6972 !important;
                opacity:1;
                box-shadow:none !important;
            }

            .wms-item-menu-toggle:hover,
            .wms-item-menu-toggle:focus,
            .wms-item-actions.open .wms-item-menu-toggle {
                color:#36414a !important;
                background:#e2e7eb !important;
                border-color:#c8d0d6 !important;
                outline:0;
            }

            .wms-item-actions .dropdown-menu {
                min-width:145px;
                margin-top:2px;
                padding:5px 0;
                text-align:left;
                z-index:10050;
            }

            .wms-item-actions .dropdown-menu > li > button {
                display:block;
                width:100%;
                padding:6px 14px;
                border:0;
                background:transparent;
                color:#4f5b65;
                text-align:left;
                white-space:nowrap;
            }

            .wms-item-actions .dropdown-menu > li > button:hover,
            .wms-item-actions .dropdown-menu > li > button:focus {
                background:#f4f6f8;
                outline:0;
            }

            .wms-item-actions .dropdown-menu > li > button i {
                width:18px;
                margin-right:4px;
                text-align:center;
            }

            .wms-item-actions .dropdown-menu > li > button.wms-delete-action {
                color:#e7505a;
            }


            .wms-control-responsible {
                display:block;
                margin-top:3px;
                font-size:12px;
                font-weight:600;
                color:#3598dc;
            }

            .wms-add-row {
                margin-top:8px;
            }

            .wms-responsible {
                margin-top:18px;
                padding:14px;
                border:1px solid #e4e8eb;
                background:#fafbfc;
            }

            .wms-required-label {
                padding-top:7px;
                text-align:right;
                font-weight:600;
            }

            .wms-file-box {
                margin:20px 0;
                padding:20px;
                border:1px solid #e4e8eb;
                background:#fafbfc;
            }

            .wms-file-icon {
                display:inline-block;
                width:75px;
                text-align:center;
                vertical-align:middle;
                font-size:48px;
            }

            .wms-file-actions {
                display:inline-block;
                vertical-align:middle;
            }

            .wms-footer-actions {
                min-height:45px;
                margin-top:18px;
            }

            .wms-modal-responsibility label {
                margin-right:18px;
            }

            @media (max-width:991px) {
                .wms-principal {
                    text-align:left;
                }

                .wms-step-cell,
                .wms-hazard-cell {
                    border-right:0;
                    border-bottom:1px solid #e8ecef;
                }

                .wms-required-label {
                    text-align:left;
                }
            }
        </style>
    @endonce

    @if ($message)
        <div class="alert alert-success" style="padding:8px 12px">{{ $message }}</div>
    @endif

    @include('form-error')

    <div class="mt-element-step">
        <div class="row step-line" id="steps">
            <div class="col-md-3 mt-step-col first done">
                <div class="mt-step-number bg-white font-grey"><i class="fa fa-check"></i></div>
                <div class="mt-step-title uppercase font-grey-cascade">Create</div>
                <div class="mt-step-content font-grey-cascade">Create SWMS</div>
            </div>
            <div class="col-md-3 mt-step-col active">
                <div class="mt-step-number bg-white font-grey">2</div>
                <div class="mt-step-title uppercase font-grey-cascade">Draft</div>
                <div class="mt-step-content font-grey-cascade">Add content</div>
            </div>
            <div class="col-md-3 mt-step-col">
                <div class="mt-step-number bg-white font-grey">3</div>
                <div class="mt-step-title uppercase font-grey">Sign Off</div>
                <div class="mt-step-content font-grey-cascade">Request Sign Off</div>
            </div>
            <div class="col-md-3 mt-step-col last">
                <div class="mt-step-number bg-white font-grey">4</div>
                <div class="mt-step-title uppercase font-grey-cascade">Approved</div>
                <div class="mt-step-content font-grey-cascade">SWMS accepted</div>
            </div>
        </div>
    </div>

    <div class="portlet light bordered">
        <div class="portlet-title">
            <div class="caption">
                <i class="icon-layers"></i>
                <span class="caption-subject bold uppercase font-green-haze">Safe Work Method Statement</span>
                @if ($modified)
                    <span class="wms-unsaved">Unsaved</span>
                @endif
            </div>

            <div class="actions">
                @if ($modified)
                    <button type="button" class="btn btn-circle green btn-outline btn-sm" wire:click="saveDraft" wire:loading.attr="disabled" wire:target="saveDraft">
                        <i class="fa fa-save"></i> Save
                    </button>
                @elseif (!$master && $complete && $signoffMode === 'request')
                    <button type="button" class="btn btn-circle green btn-outline btn-sm" wire:click="prepareSignoff">
                        <i class="fa fa-pencil-square-o"></i> Request Sign Off
                    </button>
                @elseif (!$master && $complete && $signoffMode === 'manual')
                    <a href="/safety/doc/wms/{{ $docId }}/signoff" class="btn btn-circle green btn-outline btn-sm">
                        <i class="fa fa-pencil-square-o"></i> Manual Sign Off
                    </a>
                @endif

                <a href="javascript:;" class="btn btn-circle btn-icon-only btn-default fullscreen"></a>
            </div>
        </div>

        <div class="portlet-body">
            <div class="wms-header">
                <div class="row">
                    <div class="col-md-7">
                        <h2 class="wms-company">{{ $companyName }}</h2>
                        <h4 style="margin:0">Safe Work Method Statement <small class="font-grey-salsa">version {{ $version }}</small></h4>
                    </div>

                    <div class="col-md-5 wms-principal">
                        @if ($master)
                            <h3 class="font-red" style="margin:0">TEMPLATE</h3>
                        @else
                            <strong>Principal Contractor:</strong> {{ $principle ?: '—' }}
                            <button type="button" class="btn blue btn-xs btn-outline" style="margin-left:8px" wire:click="openPrincipal">
                                <i class="fa fa-pencil"></i> Edit
                            </button>
                        @endif
                    </div>
                </div>
            </div>

            <div class="row wms-doc-fields">
                <div class="col-md-7">
                    <label class="control-label">Name of Work Activity / Task</label>
                    <input type="text" class="form-control" wire:model.blur="name" wire:change="markModified">
                    @error('name')<span class="help-block font-red">{{ $message }}</span>@enderror
                </div>

                <div class="col-md-5">
                    <label class="control-label">Project / Location</label>
                    <input type="text" class="form-control" wire:model.blur="project" wire:change="markModified">
                </div>
            </div>

            @if ($builder)
                <div class="row wms-builder-header hidden-sm hidden-xs">
                    <div class="col-md-2 wms-col-step">Step</div>
                    <div class="col-md-3 wms-col-hazard">Potential Hazard</div>
                    <div class="col-md-7 wms-col-control">Controls / Responsible Person(s)</div>
                </div>

                @forelse ($steps as $stepIndex => $step)
                    <div class="row wms-step-row" wire:key="wms-step-{{ $step['key'] }}">
                        <div class="col-md-2 wms-col-step wms-step-cell">
                            <div class="wms-item {{ $this->itemDifferent($step) ? 'wms-item-diff' : '' }}">
                                <span class="wms-item-actions dropdown">
                                    <button type="button" class="btn btn-xs dropdown-toggle wms-item-menu-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" title="Actions">
                                        <i class="fa fa-ellipsis-v"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-right">
                                        @if ($stepIndex > 0)
                                            <li><button type="button" wire:click="moveStep({{ $stepIndex }}, -1)"><i class="fa fa-chevron-up"></i> Move up</button></li>
                                        @endif
                                        @if ($stepIndex < count($steps) - 1)
                                            <li><button type="button" wire:click="moveStep({{ $stepIndex }}, 1)"><i class="fa fa-chevron-down"></i> Move down</button></li>
                                        @endif
                                        @if ($stepIndex > 0 || $stepIndex < count($steps) - 1)
                                            <li class="divider"></li>
                                        @endif
                                        <li><button type="button" wire:click="openEditStep({{ $stepIndex }})"><i class="fa fa-pencil"></i> Edit</button></li>
                                        <li><button type="button" class="wms-delete-action" wire:click="deleteStep({{ $stepIndex }})"><i class="fa fa-trash"></i> Delete</button></li>
                                    </ul>
                                </span>
                                <span class="wms-item-text"><span class="wms-step-number">{{ $stepIndex + 1 }}.</span> {{ $step['name'] }}</span>


                            </div>

                            <div class="wms-add-row">
                                <button type="button" class="btn btn-xs default" wire:click="openAddStep({{ $stepIndex }})"><i class="fa fa-plus"></i> Step</button>
                            </div>
                        </div>

                        <div class="col-md-3 wms-col-hazard wms-hazard-cell">
                            <span class="visible-sm visible-xs"><strong>Hazards</strong></span>

                            @forelse ($step['hazards'] as $hazardIndex => $hazard)
                                <div class="wms-item {{ $this->itemDifferent($hazard) ? 'wms-item-diff' : '' }}" wire:key="wms-hazard-{{ $hazard['key'] }}">
                                    <span class="wms-item-actions dropdown">
                                        <button type="button" class="btn btn-xs dropdown-toggle wms-item-menu-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" title="Actions">
                                            <i class="fa fa-ellipsis-v"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-right">
                                            @if ($hazardIndex > 0)
                                                <li><button type="button" wire:click="moveHazard({{ $stepIndex }}, {{ $hazardIndex }}, -1)"><i class="fa fa-chevron-up"></i> Move up</button></li>
                                            @endif
                                            @if ($hazardIndex < count($step['hazards']) - 1)
                                                <li><button type="button" wire:click="moveHazard({{ $stepIndex }}, {{ $hazardIndex }}, 1)"><i class="fa fa-chevron-down"></i> Move down</button></li>
                                            @endif
                                            @if ($hazardIndex > 0 || $hazardIndex < count($step['hazards']) - 1)
                                                <li class="divider"></li>
                                            @endif
                                            <li><button type="button" wire:click="openEditHazard({{ $stepIndex }}, {{ $hazardIndex }})"><i class="fa fa-pencil"></i> Edit</button></li>
                                            <li><button type="button" class="wms-delete-action" wire:click="deleteHazard({{ $stepIndex }}, {{ $hazardIndex }})"><i class="fa fa-trash"></i> Delete</button></li>
                                        </ul>
                                    </span>
                                    <span class="wms-item-text">{{ $hazard['name'] }}</span>


                                </div>
                            @empty
                                <div class="font-grey-silver">No hazards.</div>
                            @endforelse

                            <div class="wms-add-row">
                                <button type="button" class="btn btn-xs default" wire:click="openAddHazard({{ $stepIndex }})"><i class="fa fa-plus"></i> Hazard</button>
                            </div>
                        </div>

                        <div class="col-md-7 wms-col-control wms-control-cell">
                            <span class="visible-sm visible-xs"><strong>Controls</strong></span>

                            @forelse ($step['controls'] as $controlIndex => $control)
                                <div class="wms-item {{ $this->itemDifferent($control) ? 'wms-item-diff' : '' }}" wire:key="wms-control-{{ $control['key'] }}">
                                    <span class="wms-item-actions dropdown">
                                        <button type="button" class="btn btn-xs dropdown-toggle wms-item-menu-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" title="Actions">
                                            <i class="fa fa-ellipsis-v"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-right">
                                            @if ($controlIndex > 0)
                                                <li><button type="button" wire:click="moveControl({{ $stepIndex }}, {{ $controlIndex }}, -1)"><i class="fa fa-chevron-up"></i> Move up</button></li>
                                            @endif
                                            @if ($controlIndex < count($step['controls']) - 1)
                                                <li><button type="button" wire:click="moveControl({{ $stepIndex }}, {{ $controlIndex }}, 1)"><i class="fa fa-chevron-down"></i> Move down</button></li>
                                            @endif
                                            @if ($controlIndex > 0 || $controlIndex < count($step['controls']) - 1)
                                                <li class="divider"></li>
                                            @endif
                                            <li><button type="button" wire:click="openEditControl({{ $stepIndex }}, {{ $controlIndex }})"><i class="fa fa-pencil"></i> Edit</button></li>
                                            <li><button type="button" class="wms-delete-action" wire:click="deleteControl({{ $stepIndex }}, {{ $controlIndex }})"><i class="fa fa-trash"></i> Delete</button></li>
                                        </ul>
                                    </span>
                                    <span class="wms-item-text">{{ $control['name'] }}</span>

                                    @if ($this->responsibleName($control))
                                        <span class="wms-control-responsible">By: {{ $this->responsibleName($control) }}</span>
                                    @endif


                                </div>
                            @empty
                                <div class="font-grey-silver">No controls.</div>
                            @endforelse

                            <div class="wms-add-row">
                                <button type="button" class="btn btn-xs default" wire:click="openAddControl({{ $stepIndex }})"><i class="fa fa-plus"></i> Control</button>
                            </div>
                        </div>
                    </div>
                @empty
                    <div style="padding:25px 0">
                        <button type="button" class="btn btn-sm blue btn-outline" wire:click="openAddStep(-1)"><i class="fa fa-plus"></i> Step</button>
                        <span class="font-grey-silver" style="margin-left:15px">No steps found.</span>
                    </div>
                @endforelse
            @else
                <div class="wms-file-box">
                    <div class="wms-file-icon">
                        @if ($attachmentUrl)
                            <a href="{{ $attachmentUrl }}" target="_blank"><i class="fa fa-file-pdf-o"></i></a>
                        @else
                            <i class="fa fa-chain-broken font-grey-silver"></i>
                        @endif
                    </div>

                    <div class="wms-file-actions">
                        @if (!$showFileUpload)
                            <button type="button" class="btn blue" wire:click="startFileChange">Change File</button>
                        @else
                            <div class="form-group">
                                <input type="file" wire:model="replacementFile" accept="application/pdf">
                                @error('replacementFile')<span class="help-block font-red">{{ $message }}</span>@enderror
                            </div>
                            <button type="button" class="btn default" wire:click="cancelFileChange">Cancel</button>
                            <button type="button" class="btn green" wire:click="uploadReplacement" wire:loading.attr="disabled" wire:target="replacementFile,uploadReplacement">
                                <span wire:loading.remove wire:target="uploadReplacement">Save PDF</span>
                                <span wire:loading wire:target="uploadReplacement"><i class="fa fa-spinner fa-pulse"></i> Uploading...</span>
                            </button>
                        @endif
                    </div>
                </div>
            @endif

            @if (!$master)
                <div class="wms-responsible">
                    <div class="row" style="margin-bottom:10px">
                        <div class="col-md-6 wms-required-label {{ trim($resCompliance) === '' ? 'font-red' : '' }}">
                            Person responsible for ensuring compliance with SWMS:
                            @if (trim($resCompliance) === '')
                                <span>** REQUIRED **</span>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <input type="text" class="form-control" wire:model.blur="resCompliance" wire:change="markModified">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 wms-required-label {{ trim($resReview) === '' ? 'font-red' : '' }}">
                            Person responsible for reviewing SWMS control measures:
                            @if (trim($resReview) === '')
                                <span>** REQUIRED **</span>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <input type="text" class="form-control" wire:model.blur="resReview" wire:change="markModified">
                        </div>
                    </div>
                </div>
            @endif

            <div class="clearfix wms-footer-actions">
                <div class="pull-right">
                    <a href="/safety/doc/wms" class="btn default">Back</a>

                    <button type="button" class="btn dark" wire:click="saveDraft" wire:loading.attr="disabled" wire:target="saveDraft">
                        <span wire:loading.remove wire:target="saveDraft">Save Draft</span>
                        <span wire:loading wire:target="saveDraft"><i class="fa fa-spinner fa-pulse"></i> Saving...</span>
                    </button>

                    @if ($canMakeActive)
                        <button type="button" class="btn green" wire:click="makeActive" wire:loading.attr="disabled" wire:target="makeActive">Make Active</button>
                    @endif

                    @if (!$master && $complete)
                        @if ($signoffMode === 'request')
                            <button type="button" class="btn green" wire:click="prepareSignoff">Request Sign Off</button>
                        @elseif ($signoffMode === 'manual' && !$modified)
                            <a href="/safety/doc/wms/{{ $docId }}/signoff" class="btn green">Manual Sign Off</a>
                        @elseif ($signoffMode === 'principle' && !$modified)
                            @if ($canDelete)
                                <a href="/safety/doc/wms/{{ $docId }}/archive" class="btn red">Archive</a>
                            @endif
                            <a href="/safety/doc/wms/{{ $docId }}/signoff" class="btn green">Sign Off</a>
                        @endif
                    @elseif (!$master)
                        <button type="button" class="btn green" wire:click="$set('showIncompleteModal', true)">Continue</button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="pull-right" style="font-size:12px; font-weight:200; padding:10px 10px 0 0">
        {!! $updatedBy !!}
    </div>

    <x-ui.modal :show="$showItemModal" :title="$itemType === 'step' ? ($itemStepIndex !== null ? 'Edit Step' : 'Add Step') : ($itemType === 'hazard' ? ($itemIndex !== null ? 'Edit Hazard' : 'Add Hazard') : ($itemIndex !== null ? 'Edit Control' : 'Add Control'))" close-action="closeItemModal" max-width="650px">
        <div class="form-group {{ $errors->has('itemName') ? 'has-error' : '' }}">
            <label class="control-label">{{ $itemType === 'step' ? 'Step' : ($itemType === 'hazard' ? 'Hazard' : 'Control') }}</label>
            <textarea class="form-control" wire:model="itemName" rows="{{ $itemType === 'control' ? 6 : 4 }}" style="padding:10px 12px"></textarea>
            @error('itemName')<span class="help-block">{{ $message }}</span>@enderror
        </div>

        @if ($itemType === 'control')
            <div class="wms-modal-responsibility">
                <strong>Responsible person(s)</strong><br><br>
                <label class="mt-checkbox mt-checkbox-outline">
                    <input type="checkbox" wire:model="itemResPrinciple"> Principal
                    <span></span>
                </label>
                <label class="mt-checkbox mt-checkbox-outline">
                    <input type="checkbox" wire:model="itemResCompany"> Company
                    <span></span>
                </label>
                <label class="mt-checkbox mt-checkbox-outline">
                    <input type="checkbox" wire:model="itemResWorker"> Worker
                    <span></span>
                </label>
            </div>
        @endif

        <x-slot name="footer">
            <button type="button" class="sws-modal-btn sws-modal-btn-secondary" wire:click="closeItemModal">Cancel</button>
            <button type="button" class="sws-modal-btn sws-modal-btn-primary" wire:click="saveItem" wire:loading.attr="disabled" wire:target="saveItem">Save</button>
        </x-slot>
    </x-ui.modal>

    <x-ui.modal :show="$showPrincipalModal" title="Edit Principal Contractor" close-action="closePrincipalModal" max-width="560px">
        <div class="form-group {{ $errors->has('principalDraft') ? 'has-error' : '' }}">
            <label class="control-label">Principal Contractor</label>
            <input type="text" class="form-control" wire:model="principalDraft">
            @error('principalDraft')<span class="help-block">{{ $message }}</span>@enderror
        </div>

        <x-slot name="footer">
            <button type="button" class="sws-modal-btn sws-modal-btn-secondary" wire:click="closePrincipalModal">Cancel</button>
            <button type="button" class="sws-modal-btn sws-modal-btn-primary" wire:click="savePrincipal">Save</button>
        </x-slot>
    </x-ui.modal>

    <x-ui.confirm-modal :show="$showPrincipalConfirmModal" title="Confirm Principal Contractor" close-action="closePrincipalModal" confirm-action="confirmOtherPrincipal" confirm-label="Confirm">
        <div>
            As the Principal Contractor is not <strong>{{ $parentName }}</strong>, you will need to manually get the Principal Contractor to sign off on your Work Method Statement.
            <div class="sws-confirm-item">{{ $principalDraft }}</div>
        </div>
    </x-ui.confirm-modal>

    <x-ui.modal :show="$showSignoffModal" title="Confirm Sign Off Request" close-action="closeSignoffModal" max-width="500px" footer-align="center">
        <div class="text-center">
            You are about to leave DRAFT mode and request<br><strong>{{ $principle }}</strong><br>to sign off.
            <p class="font-red" style="margin-top:12px"><i class="fa fa-exclamation-triangle"></i> You will no longer be able to modify this SWMS.</p>
        </div>

        <x-slot name="footer">
            <button type="button" class="sws-modal-btn sws-modal-btn-secondary" wire:click="closeSignoffModal">Cancel</button>
            <a href="/safety/doc/wms/{{ $docId }}/signoff" class="sws-modal-btn sws-modal-btn-primary">Confirm</a>
        </x-slot>
    </x-ui.modal>

    <x-ui.modal :show="$showIncompleteModal" title="Incomplete SWMS" close-action="closeSignoffModal" max-width="480px">
        <p class="text-center">The following fields are required:</p>

        @if (trim($resCompliance) === '')
            <p class="text-center font-red">Person responsible for ensuring compliance with SWMS</p>
        @endif

        @if (trim($resReview) === '')
            <p class="text-center font-red">Person responsible for reviewing SWMS control measures</p>
        @endif

        <x-slot name="footer">
            <button type="button" class="sws-modal-btn sws-modal-btn-secondary" wire:click="closeSignoffModal">OK</button>
        </x-slot>
    </x-ui.modal>
</div>
