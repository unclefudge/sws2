@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        <li><a href="/support/ticket">Support Tickets</a><i class="fa fa-circle"></i></li>
        <li><a href="/support/hours">Support Hours</a><i class="fa fa-circle"></i></li>
        <li><span>Update</span></li>
    </ul>
@stop

@section('content')
    <div class="page-content-inner">
        <div class="row">
            <div class="col-md-12">
                <div class="portlet light">
                    <div class="portlet-title">
                        <div class="caption font-dark">
                            <i class="icon-layers"></i>
                            <span class="caption-subject bold uppercase font-green-haze">Support Hours</span>
                        </div>
                        <div class="actions">
                            <a class="btn btn-circle green btn-outline btn-sm" href="/support/hours">Support Hours</a>
                        </div>
                    </div>

                    <div class="portlet-body">
                        <livewire:support.support-hours-edit/>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
