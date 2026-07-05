@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        <li><span>Request a Designer Visit</span></li>
    </ul>
@stop

@section('content')
    <div class="page-content-inner">
        <div class="row">
            <div class="col-md-12">
                <div class="portlet light ">
                    <div class="portlet-title">
                        <div class="caption font-dark">
                            <span class="caption-subject bold uppercase font-green-haze"> Request a Designer Visit</span>
                        </div>
                        <div class="actions">
                            <a class="btn btn-circle green btn-outline btn-sm" href="/settings/client-enquiry-form" data-original-title="Submission">Form Submissions</a>
                        </div>
                    </div>
                    <div class="portlet-body">
                        <div class="row">
                            <div class="col-md-12">
                                <iframe
                                        id="requestDesignerStaffFrame"
                                        src="/wp/staff/request-designer"
                                        title="Staff Request a Designer Visit"
                                        style="width: 100%; border: 0; height: 900px; min-height: 500px; overflow: hidden;"
                                        scrolling="no">
                                </iframe>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('page-level-plugins-head')
@stop

@section('page-level-plugins')
@stop

@section('page-level-scripts')
    {{-- Metronic + custom Page Scripts --}}
    <script>
        window.addEventListener('message', function (event) {
            if (!event.data || event.data.type !== 'request-designer-height') {
                return;
            }

            const iframe = document.getElementById('requestDesignerStaffFrame');

            if (!iframe) {
                return;
            }

            const newHeight = Math.max(500, Number(event.data.height || 0));

            iframe.style.height = newHeight + 'px';
        });
    </script>
@stop