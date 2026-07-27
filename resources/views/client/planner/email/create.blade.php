@inject('action_items', 'App\Http\Utilities\ClientPlannerActionItems')
@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        @if (Auth::user()->hasAnyPermissionType('client.planner.email'))
            <li><a href="/client/planner/email">Client Planner Emails</a><i class="fa fa-circle"></i></li>
        @endif
        <li><span>Create Email</span></li>
    </ul>
@stop

@section('content')
    <div class="page-content-inner">
        <div class="row">
            <div class="col-md-12">
                <div class="portlet light bordered">
                    <div class="portlet-title">
                        <div class="caption">
                            <span class="caption-subject font-green-haze bold uppercase">Create Client Planner Email</span>
                        </div>
                    </div>
                    <div class="portlet-body form">
                        <form method="POST" action="{{ action([App\Http\Controllers\Client\ClientPlannerEmailController::class, 'store']) }}" class="horizontal-form">
                            @csrf
                        @include('form-error')

                        <div class="form-body">
                            <h4 class="font-green-haze">Site details</h4>
                            <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                            <div class="row">
                                {{-- Site --}}
                                <div class="col-md-6">
                                    <x-form.select name="site_id" label="Site" plugin="select2" style="width:100%">
                                        {!! Auth::user()->authSitesSelect2Options('view.site.planner', old('site_id')) !!}
                                    </x-form.select>
                                </div>
                            </div>

                            <h4 class="font-green-haze">Client details</h4>
                            <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                            <div class="row">
                                <div class="col-md-6">
                                    <x-form.input name="email1" label="Email 1"/>
                                </div>
                                <div class="col-md-6">
                                    <x-form.input name="email2" label="Email 2"/>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <x-form.input name="email3" label="Additional Emails (separated by semi-colon)"/>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <x-form.input name="intro" label="Letter introdution" placeholder="Name of client to address email to"/>
                                </div>
                                <div class="col-md-3">
                                    <x-form.select name="type" label="Email type" :options="['' => 'Select type', 'Blank' => 'Blank', 'Action' => 'Action items']" value="Blank" title="Select type"/>
                                </div>
                                <div class="col-md-3">
                                    <x-form.select name="weeks" label="No, of weeks for Client Planner" :options="['1' => '1', '2' => '2', '3' => '3', '4' => '4', '5' => '5', '6' => '6', '7' => '7', '8' => '8']" value="2" title="Select type"/>
                                </div>
                            </div>

                            <div id="action_template" style="display: none">
                                <h4 class="font-green-haze">Action Template</h4>
                                <hr style="padding: 0px; margin: 0px 0px 10px 0px">
                                <div class="row">
                                    <div class="col-md-12">As discussed in our Pre Construction Meeting, I need you to start thinking about or to finalise for me, the following items:<br><br></div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">Choose which actions items you'd like to include:</div>
                                </div>

                                {{-- Action items --}}
                                <table class="table table-striped table-bordered table-hover order-column">
                                    <thead>
                                    <tr class="mytable-header">
                                        <th style="width:10%"></th>
                                        <th> Action item</th>
                                        <th style="width:15%"> Date Required</th>
                                    </tr>
                                    </thead>
                                    <tbody>

                                    @foreach ($action_items::all() as $action_id => $action_name)
                                            <?php
                                            $old_include = (old('include')) ? old('include') : [];
                                            $checked = '';
                                            $rowcolour = 'font-grey-cascade';
                                            $datefield = 'style="display: none"';
                                            if (in_array($action_id, $old_include)) {
                                                $checked = 'checked';
                                                $rowcolour = '';
                                                $datefield = '';
                                            }
                                            ?>
                                        <tr class="itemrow- {{ $rowcolour }}" id="itemrow-{{ $action_id }}">
                                            <td class="excludeitems">
                                                <div class="text-center">
                                                    <label class="mt-checkbox mt-checkbox-outline">
                                                        <input type="checkbox" value="{{ $action_id }}" name="include[]" id="itemcheck-{{ $action_id }}" class="stockitem" {{ $checked }}>
                                                        <span></span>
                                                    </label>
                                                </div>
                                            </td>
                                            <td>{{ $action_name }} {{ $checked }}</td>
                                            <td>
                                                <div class="form-group {{ $errors->has("itemdate-$action_id") ? 'has-error' : '' }}">
                                                    <div class="input-group date date-picker" id="itemdatediv-{{$action_id}}" @if (!$checked) style="display: none" @endif>
                                                        <input type="text" value="{{ old("itemdate-$action_id") }}" class="form-control form-control-inline" style="background:#FFF" data-date-format="dd-mm-yyyy" name="itemdate-{{ $action_id }}" id="itemdate-{{ $action_id }}">
                                                        <span class="input-group-btn">
                                                            <button class="btn default date-set" type="button"><i class="fa fa-calendar"></i></button>
                                                        </span>
                                                    </div>
                                                    <x-form.error :name="'itemdate-' . $action_id"/>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>

                                <div class="row">
                                    <div class="col-md-12">
                                        <x-form.textarea name="further_notes" label="Further Notes as discussed" rows="5" placeholder="Details"/>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <h5>Attachments</h5>
                                    <x-form.filepond/>
                                </div>
                            </div>

                            <div class="form-actions right">
                                <a href="/client/planner/email" class="btn default"> Back</a>
                                <button id="save_button" type="submit" name="save" class="btn green"> Save</button>
                            </div>
                        </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop


@section('page-level-plugins-head')
    <link href="https://unpkg.com/filepond/dist/filepond.css" rel="stylesheet" type="text/css"/>   {{-- Filepond --}}
    <link href="/assets/global/plugins/bootstrap-select/css/bootstrap-select.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/bootstrap-datepicker/css/bootstrap-datepicker3.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/select2/css/select2.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/select2/css/select2-bootstrap.min.css" rel="stylesheet" type="text/css"/>
@stop

@section('page-level-plugins')
    <script src="/assets/global/plugins/bootstrap-select/js/bootstrap-select.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/select2/js/select2.full.min.js" type="text/javascript"></script>
    <script src="/assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js" type="text/javascript"></script>
    <script src="https://unpkg.com/filepond/dist/filepond.min.js"></script> {{-- FilePond --}}
@stop

@section('page-level-scripts')
    {{-- Metronic + custom Page Scripts --}}
    <script src="/assets/pages/scripts/components-bootstrap-select.min.js" type="text/javascript"></script>
    <script src="/assets/pages/scripts/components-date-time-pickers.min.js" type="text/javascript"></script>
    <script src="/js/filepond-basic.js" type="text/javascript"></script>
    <script type="text/javascript">
        $(document).ready(function () {
            /* Select2 */
            $("#site_id").select2({placeholder: "Select Site"});

            updateFields();

            // On Change Site ID
            $("#site_id").change(function () {
                updateFields();
            });

            // On Change Type
            $("#type").change(function () {
                updateFields();
            });

            function updateFields() {

                if ($("#type").val() == 'Action') {
                    $("#action_template").show();
                } else {
                    $("#action_template").hide();
                }

                var site_id = $("#site_id").select2("val");

                if (site_id != '') {
                    $.ajax({
                        url: '/client/planner/email/createfields/' + site_id,
                        type: 'GET',
                        dataType: 'json',
                        success: function (data) {

                            //if (data.client1_email != '')
                            //    $("#email1").val(data.client1_email);

                            //if (data.client2_email != '')
                            //    $("#email2").val(data.client2_email);

                            if (data.client_intro != '')
                                $("#intro").val(data.client_intro);

                            //console.log(data);
                        },
                    })
                }
            }

            $(".stockitem").click(function (e) {
                if ($("#itemcheck-" + $(this).val()).prop('checked')) {
                    $("#itemrow-" + $(this).val()).removeClass("font-grey-cascade");
                    $("#itemactual-" + $(this).val()).show();
                    $("#itemdate-" + $(this).val()).show();
                    $("#itemdatediv-" + $(this).val()).show();
                } else {
                    $("#itemactual-" + $(this).val()).hide();
                    $("#itemdate-" + $(this).val()).hide();
                    $("#itemdatediv-" + $(this).val()).hide();
                    $("#itemrow-" + $(this).val()).addClass("font-grey-cascade");
                }
            });

        });

        $('.date-picker').datepicker({
            autoclose: true,
            clearBtn: true,
            format: 'dd/mm/yyyy',
        });
    </script>
@stop


