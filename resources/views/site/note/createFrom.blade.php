@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        @if (Auth::user()->hasAnyPermissionType('site'))
            <li><a href="/site">Sites</a><i class="fa fa-circle"></i></li>
        @endif
        <li><a href="/site/{{$site_id}}/notes">Site Notes</a><i class="fa fa-circle"></i></li>
        <li><span>Create</span></li>
    </ul>
@stop

@section('content')
    <div class="page-content-inner">
        <div class="row">
            <div class="col-md-12">
                <div class="portlet light bordered">
                    <div class="portlet-title">
                        <div class="caption">
                            <span class="caption-subject font-green-haze bold uppercase">Create Site Note</span>
                            <span class="caption-helper"></span>
                        </div>
                    </div>
                    <div class="portlet-body form">
                        <form method="POST" action="{{ action([App\Http\Controllers\Site\SiteNoteController::class, 'store']) }}" class="horizontal-form" enctype="multipart/form-data">
                            @csrf
                            <x-form.hidden name="previous_url" :value="url()->previous()"/>
                            @include('form-error')

                            <div class="form-body">
                                <div class="row">
                                    {{-- Site --}}
                                    <div class="col-md-6">
                                        <x-form.select name="site_id" label="Site" :options="$site_list" :value="$site_id" id="site_id" plugin="select2"/>
                                    </div>
                                    {{-- Category --}}
                                    <div class="col-md-4">
                                        <x-form.select name="category_id" label="Category" :options="['' => 'Select category'] + $categories" :value="16" id="category_id"/>
                                    </div>
                                </div>

                                {{-- Variation Fields --}}
                                <div id="variation_fields" style="display: none">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <x-form.input name="variation_name" label="Variation Name"/>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <x-form.textarea name="variation_info" label="Variation Description" rows="5"/>
                                        </div>
                                    </div>
                                </div>
                                <div id="variation_cost_fields" style="display: none">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <x-form.input name="variation_net" label="Net Cost <span class='font-grey-silver'>(Admin use only)</span>" onkeydown="return isNumber(event)"/>
                                        </div>
                                        <div class="col-md-3">
                                            <x-form.input name="variation_cost" label="Gross Cost (incl GST + 20% margin)" :value="$existing->variation_cost"/>
                                        </div>
                                        <div id="extracredit_div">
                                            <div class="col-md-3">
                                                <x-form.select name="variation_extra_credit" label="Credit / Extra" :options="['' => 'Select option', 'Extra' => 'Extra', 'Credit' => 'Credit']" id="variation_extra_credit"/>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <x-form.input name="variation_days" label="Total Extension Days (discussed with Client) Description <span class='font-grey-silver'>(Admin use only)</span>" onkeydown="return isNumber(event)"/>
                                        </div>
                                    </div>
                                    {{-- Variation items --}}
                                    <div class="row">
                                        <div class="col-md-12">Variation items <span class="font-grey-silver">(Admin use only)</span></div>
                                    </div>
                                    @php ($i = 0)
                                    @foreach ($existing->costs as $cost)
                                        @php ($i++)
                                        <div class="row">
                                            <div class="col-md-1 text-center">{{ $i }}.</div>
                                            <div class="col-md-3">
                                                <x-form.select name="cc-{{ $i }}" :options="['' => 'Select cost centre'] + $cost_centres" :value="$cost->cost_id"/>
                                            </div>
                                            <div class="col-md-8">
                                                <x-form.input name="cinfo-{{ $i }}" :value="$cost->details" placeholder="Details & Cost of item {{ $cost->order }}."/>
                                            </div>
                                        </div>
                                    @endforeach

                                    {{-- Extra Items --}}
                                    <button class="btn blue" id="more">More Items</button>
                                    <div id="more_items" style="display: none">
                                        @for ($i = $existing->costs->count() + 1; $i <= 20; $i++)
                                            <div class="row">
                                                <div class="col-md-1 text-center">{{ $i }}.</div>
                                                <div class="col-md-3">
                                                    <x-form.select name="cc-{{ $i }}" :options="['' => 'Select cost centre'] + $cost_centres"/>
                                                </div>
                                                <div class="col-md-7">
                                                    <x-form.input name="cinfo-{{ $i }}" placeholder="Details & Cost of item {{ $i }}."/>
                                                </div>
                                            </div>
                                        @endfor
                                    </div>
                                    <br><br>
                                </div>

                                {{-- Costing Fields --}}
                                <div id="costing_fields" style="display: none">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <x-form.select name="costing_extra_credit" label="Credit / Extra" :options="['' => 'Select option', 'Extra' => 'Extra', 'Credit' => 'Credit',  'Wet Call' => 'Wet Call']" id="costing_extra_credit"/>
                                        </div>
                                        <div class="col-md-3">
                                            <x-form.select name="costing_item" label="New item / In Lieu of" :options="['' => 'Select option', 'New item' => 'New item', 'In Lieu of' => 'In Lieu of']" id="costing_item"/>
                                        </div>
                                        <div class="col-md-3">
                                            <x-form.select name="costing_priority" label="Priority" :options="['' => 'Select option', '1-2 days' => '1-2 days', '3-5 days' => '3-5 days', '5+ days' => '5+ days']" id="costing_priority"/>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-3">
                                            <x-form.input name="costing_room" label="Room"/>
                                        </div>
                                        <div class="col-md-7">
                                            <x-form.input name="costing_location" label="Location"/>
                                        </div>
                                    </div>
                                </div>

                                {{-- Response Required --}}
                                <div id="response_req_field" style="display: none">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <x-form.select name="response_req" label="Response Required" :options="['0' => 'No - FYI only', '1' => 'Yes']" id="response_req"/>
                                        </div>
                                    </div>
                                </div>

                                {{-- Notes --}}
                                <div class="row">
                                    <div class="col-md-12">
                                        <x-form.textarea name="notes" label="Note (Admin use only)" :value="$existing->notes" rows="5"/>
                                    </div>
                                </div>

                                {{-- Attachments --}}
                                <div class="row">
                                    <div class="col-md-6">
                                        <h5>Upload Attachments</h5>
                                        <x-form.filepond/>
                                        <br><br>
                                    </div>
                                </div>

                                <br><br>
                                <div class="form-actions right">
                                    <a href="{!! url()->previous() !!}" class="btn default"> Back</a>
                                    <button type="submit" class="btn green" id="submit"> Save</button>
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
    <link href="/assets/global/plugins/select2/css/select2.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/select2/css/select2-bootstrap.min.css" rel="stylesheet" type="text/css"/>
@stop

@section('page-level-plugins')
    <script src="/assets/global/plugins/select2/js/select2.full.min.js" type="text/javascript"></script>
    <script src="https://unpkg.com/filepond/dist/filepond.min.js"></script> {{-- FilePond --}}
@stop

@section('page-level-scripts')
    {{-- Metronic + custom Page Scripts --}}
    <script src="/js/filepond-basic.js" type="text/javascript"></script>
    <script>
        $(document).ready(function () {
            /* Select2 */
            $("#site_id").select2({placeholder: "Select Site", width: '100%'});
            $("#site_id2").select2({placeholder: "Select Site", width: '100%'});

            $("#category_id").change(function (e) {
                e.preventDefault();
                displayFields();
            });

            $("#more").click(function (e) {
                e.preventDefault();
                $('#more').hide();
                $('#more_items').show();
            });

            displayFields();

            function displayFields() {
                var cat_id = $("#category_id").val();

                $("#variation_fields").hide();
                $("#variation_cost_fields").hide();
                $("#costing_fields").hide();
                $("#response_req_field").hide();
                $("#siteall_div").hide();
                $("#extracredit_div").show();
                $("#savenote").show();
                $("#notes_label").html('Note (Admin use only)');

                // Approved Site Variation, For Issue to Client Site Variations, TBA Site Variation, Wet Calls
                if (cat_id == '16' || cat_id == '19' || cat_id == '20' || cat_id == '93') {
                    $("#variation_fields").show();
                }

                // Approved Site Variation, For Issue to Client Site Variations, Wet Calls
                if (cat_id == '16' || cat_id == '19' || cat_id == '93') {
                    $("#variation_cost_fields").show();
                }

                // Costing Request
                if (cat_id == '15') {
                    $("#costing_fields").show();
                    $("#notes_label").html('Description');
                }

                // Wet Call Request
                if (cat_id == '93') {
                    $("#siteall_div").show();
                    $("#site_div").hide();
                    $("#extracredit_div").hide();
                } else {
                    $("#siteall_div").hide();
                    $("#site_div").show();
                }

                var response_req_cats = ['12', '13', '14']
                if (response_req_cats.includes(cat_id)) {
                    $("#response_req_field").show();
                } else {
                    $("#response_req").val('');
                    $("#response_req_field").hide();
                }
            };

        });

        function isNumber(evt) {
            evt = (evt) ? evt : window.event;
            var charCode = (evt.which) ? evt.which : evt.keyCode;
            if ((charCode > 31 && charCode < 48) || charCode > 57) {
                return false;
            }
            return true;
        }
    </script>
@stop

