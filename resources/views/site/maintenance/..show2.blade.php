@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        @if (Auth::user()->hasAnyPermissionType('site'))
            <li><a href="/site">Sites</a><i class="fa fa-circle"></i></li>
        @endif
        <li><a href="/site/maintenance">Maintenance</a><i class="fa fa-circle"></i></li>
        <li><span>View Request</span></li>
    </ul>
@stop

<style>
    a.mytable-header-link {
        font-size: 14px;
        font-weight: 600;
        color: #333 !important;
    }

    @media screen and (min-width: 992px) {
        .datepicker-input {
            width: 130px !important;
        }
    }

    @media screen and (min-width: 1200px) {
        .datepicker-input {
            width: 160px !important;
        }
    }
</style>

@section('content')
    <div class="page-content-inner">
        <div class="row">
            <div class="col-md-12">
                <div class="portlet light bordered">
                    <div class="portlet-title">
                        <div class="caption">
                            <i class="icon-layers"></i>
                            <span class="caption-subject bold uppercase font-green-haze"> Site Maintenance Request</span>
                            <span class="caption-helper">ID: {{ $main->code }}</span>
                        </div>
                    </div>
                    <div class="portlet-body">
                        <div class="page-content-inner">
                            <livewire:site.site-maintenance :main="$main"/>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- loading Spinner -->
    {{--}}
    <div v-show="xx.spinner" style="background-color: #FFF; padding: 20px;">
        <div class="loadSpinnerOverlay">
            <div class="loadSpinner"><i class="fa fa-spinner fa-pulse fa-2x fa-fw margin-bottom"></i> Loading...</div>
        </div>
    </div>--}}
@stop




@section('page-level-scripts')
    {{-- Metronic + custom Page Scripts --}}

    {{--}}
    <script>
        $.ajaxSetup({headers: {'X-CSRF-Token': $('meta[name=token]').attr('value')}});

        $(document).ready(function () {
            /* Select2 */
            $("#super_id").select2({placeholder: "Select supervisor", width: '100%'});
            $("#assigned_to").select2({placeholder: "Select company", width: '100%'});
            $("#category_id").select2({placeholder: "Select category", width: "100%"});
        });
    </script>
    --}}

@stop

