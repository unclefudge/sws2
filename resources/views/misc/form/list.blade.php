@extends('layout')

@section('breadcrumbs')
    <ul class="page-breadcrumb breadcrumb">
        <li><a href="/">Home</a><i class="fa fa-circle"></i></li>
        <li><span>Forms</span></li>
    </ul>
@stop

@section('content')

    <div class="page-content-inner">
        <div class="row">
            <div class="col-md-12">
                <div class="portlet light ">
                    <div class="portlet-title">
                        <div class="caption font-dark">
                            <span class="caption-subject bold uppercase font-green-haze"> Forms</span>
                        </div>
                        <div class="actions">
                            @if (true || Auth::user()->allowed2('add.equipment'))
                                <a class="btn btn-circle green btn-outline btn-sm" href="/form/template/create" data-original-title="Add">Add</a>
                            @endif
                        </div>
                    </div>

                    <div class="portlet-body">
                        <div id="vueApp"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END PAGE CONTENT INNER -->
@stop

@section('page-level-plugins-head')

@stop

@section('page-level-plugins')

@stop

@section('page-level-scripts') {{-- Metronic + custom Page Scripts --}}
{{--}}
<script src="https://unpkg.com/vue@3"></script>
<script src="{{ mix('js/app.js') }}"></script>
--}}
{{--}}
<script type="module" >
    import App from '/js/vue/testapp.js';

    Vue.createApp(App).mount('#vueApp');
</script>--}}

{{--}}<script type="module" src="/js/vue/custom-form.js"></script>;
--}}
{{--}}
<script src="https://unpkg.com/vue@3"></script>
<script src="{{ mix('js/custom-form.js') }}"></script>
--}}

<script src="{{ mix('js/custom-form.js') }}"></script>
@stop