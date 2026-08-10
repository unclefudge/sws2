@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        <li><a href="/safety/doc/wms">SWMS</a><i class="fa fa-circle"></i></li>
        <li><span>Create Statement</span></li>
    </ul>
@stop

@section('content')
    <div class="page-content-inner">
        {{-- Progress Steps --}}
        <div class="mt-element-step">
            <div class="row step-line" id="steps">
                <div class="col-md-3 mt-step-col first active">
                    <div class="mt-step-number bg-white font-grey">1</div>
                    <div class="mt-step-title uppercase font-grey-cascade">Create</div>
                    <div class="mt-step-content font-grey-cascade">Create SWMS</div>
                </div>
                <div class="col-md-3 mt-step-col">
                    <div class="mt-step-number bg-white font-grey">2</div>
                    <div class="mt-step-title uppercase font-grey-cascade">Draft</div>
                    <div class="mt-step-content font-grey-cascade">Add content</div>
                </div>
                <div class="col-md-3 mt-step-col">
                    <div class="mt-step-number bg-white font-grey">3</div>
                    <div class="mt-step-title uppercase font-grey-cascade">Sign Off</div>
                    <div class="mt-step-content font-grey-cascade">Request Sign Off</div>
                </div>
                <div class="col-md-3 mt-step-col last">
                    <div class="mt-step-number bg-white font-grey">4</div>
                    <div class="mt-step-title uppercase font-grey-cascade">Approved</div>
                    <div class="mt-step-content font-grey-cascade">SWMS accepted</div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="portlet light bordered">
                    <div class="portlet-title">
                        <div class="caption">
                            <span class="caption-subject font-green-haze bold uppercase">Create New Statement From Template</span>
                            <span class="caption-helper">{{ $doc->name }}</span>
                        </div>
                        <div class="actions">
                            <a href="" class="btn btn-circle btn-icon-only btn-default collapse"> </a>
                            <a href="javascript:;" class="btn btn-circle btn-icon-only btn-default fullscreen"> </a>
                        </div>
                    </div>
                    <div class="portlet-body form">
                        <form method="POST" action="{{ action([\App\Http\Controllers\Safety\WmsController::class, 'store']) }}" class="horizontal-form" enctype="multipart/form-data">
                            @csrf
                            @include('form-error')

                            <x-form.hidden name="version" value="1.0"/>
                            <div class="form-body">
                                <x-form.hidden name="swms_type" value="library"/>
                                <x-form.hidden name="master_id" :value="$doc->id"/>


                            <!-- Name -->
                            <div class="row">
                                <div class="col-md-6">
                                    <x-form.input name="name" label="Name of Work Activity / Task" :value="$doc->name"/>
                                </div>
                            </div>
                            <!-- Principal Contractor -->
                            @if(Auth::user()->company->subscription)
                                    <?php
                                    $principle_array = ['other' => 'Other'];
                                    if (Auth::user()->permissionLevel('add.wms', Auth::user()->company->id))
                                        $principle_array = [Auth::user()->company->id => Auth::user()->company->name] + $principle_array;
                                    if (Auth::user()->permissionLevel('add.wms', Auth::user()->company->parent_company))
                                        $principle_array = [Auth::user()->company->parent_company => Auth::user()->company->reportsTo()->name] + $principle_array;
                                    ?>
                                <div class="row">
                                    <div class="col-md-6">
                                        <x-form.select name="principle_id" label="Principal Contractor" :options="$principle_array"/>
                                    </div>
                                    <div class="col-md-6">
                                        <div style="display: none" id="principle-div">
                                            <x-form.input name="principle" label="Principal Contractor Name"/>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            {{-- Replacing Expired SWMS --}}
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <p class="myswitch-label">&nbsp; </p>
                                        <span style="padding-right: 30px">Is this replacing an existing or expired SWMS?</span>
                                        <label for="replace_switch" class="control-label">&nbsp;</label>
                                        <input type="checkbox" name="replace_switch" id="replace_switch" value="1" class="make-switch"
                                               data-on-text="Yes" data-on-color="success"
                                               data-off-text="No" data-off-color="danger" @checked(old('replace_switch', isset($data['replace_id'])))>
                                    </div>
                                </div>
                                <?php $replace_value = ''; if (old("replace_id")) $replace_value = old("replace_id"); elseif (isset($data['replace_id'])) $replace_value = $data['replace_id']; ?>
                                <div class="col-md-6">
                                    <div style="display: none" id="replace-div">
                                        <x-form.select name="replace_id" label="SWMS to Replace" plugin="select2" style="width:100%">
                                            <option></option>
                                            <optgroup label="Existing Statements">
                                                @foreach(Auth::user()->company->wmsDocSelect() as $value => $name)
                                                    <option value="{{ $value }}" {{ ($replace_value == $value ? 'selected':'') }}>{{ $name }}</option>
                                                @endforeach
                                            </optgroup>
                                        </x-form.select>
                                        <div class="note note-warning">NOTE: The chosen SWMS will be archived</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Save as Template -->
                            {{-- Only allowed Fudge/Kirstie/Ross access to add to library --}}
                            @if(in_array(Auth::user()->id, [3, 108, 1155]))
                                <div class="row" id="master_div">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <p class="myswitch-label">&nbsp;</p>
                                            <span style="padding-right: 30px">Save as a master template for others to access?</span>
                                            <label for="master" class="control-label">&nbsp;</label>
                                            <input type="checkbox" name="master" id="master" value="1" class="make-switch"
                                                   data-on-text="Yes" data-on-color="success"
                                                   data-off-text="No" data-off-color="danger" @checked(old('master', false))>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <x-form.hidden name="master" value="0"/>
                            @endif
                        </div>
                        <div class="form-actions right">
                            <a href="/safety/doc/wms" class="btn default"> Back</a>
                            <button type="submit" class="btn green"> Begin</button>
                        </div>
                    </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@stop


@section('page-level-plugins-head')
    <link href="/assets/global/plugins/select2/css/select2.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/select2/css/select2-bootstrap.min.css" rel="stylesheet" type="text/css"/>
    <link href="/css/libs/fileinput.min.css" media="all" rel="stylesheet" type="text/css"/>
@stop

@section('page-level-plugins')
    <script src="/assets/global/plugins/select2/js/select2.full.min.js" type="text/javascript"></script>
    <script src="/js/libs/fileinput.min.js"></script>
@stop

@section('page-level-scripts')
    {{-- Metronic + custom Page Scripts --}}
    <script>
        $(document).ready(function () {
            /* Select2 */
            $("#replace_id").select2({placeholder: "Select previous SWMS",});
            $("#for_company_id").select2({placeholder: "Select Company",});


            $('#principle_id').change(function () {
                principle_name();
            });

            function principle_name() {
                if ($('#principle_id').val() == 'other')
                    $('#principle-div').show();
                else
                    $('#principle-div').hide();
            }

            principle_name();

            /* toggle Principle + set in on page load */
            if ($('#principle_switch').bootstrapSwitch('state') == false) {
                $('#principle-div').show();
            }

            $('#principle_switch').on('switchChange.bootstrapSwitch', function (event, state) {
                $('#principle-div').toggle();
            });

            /* toggle Replace + set in on page load */
            if ($('#replace_switch').bootstrapSwitch('state') == true) {
                $('#replace-div').show();
            }

            $('#replace_switch').on('switchChange.bootstrapSwitch', function (event, state) {
                $('#replace-div').toggle();
            });
        });
    </script>
@stop

