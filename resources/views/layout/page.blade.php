<div class="page-container">
    <!-- BEGIN CONTENT -->
    <div class="page-content-wrapper">
        <!-- BEGIN CONTENT BODY -->
        <!-- BEGIN PAGE HEAD-->
        <div class="page-head">
            <div class="container">
                <!-- BEGIN PAGE TITLE -->
                @yield('pagetitle')
                <!-- END PAGE TITLE -->

                <!-- BEGIN PAGE TOOLBAR -->
                {{-- @include('layout.pagetoolbar') --}}
                <!-- END PAGE TOOLBAR -->

            </div>
        </div>
        <!-- END PAGE HEAD-->
        <!-- BEGIN PAGE CONTENT BODY -->
        <div class="page-content"
             @if (\App::environment('dev')) style="background-image: url('/img/bg-development.png'); background-repeat: repeat" @endif
             @if (\App::environment('local')) style="background-image: url('/img/bg-local.png'); background-repeat: repeat" @endif
             @if (app()->environment('prod')) style="background: #eff3f8" @endif>
            <div class="container">
                <!-- BEGIN PAGE BREADCRUMBS -->
                @yield('breadcrumbs')
                <!-- END PAGE BREADCRUMBS -->

                <!-- BEGIN PAGE CONTENT INNER -->
                <div class="page-content-inner">
                    @yield('content')
                </div>
                <!-- END PAGE CONTENT INNER -->
            </div>
        </div>
        <!-- END PAGE CONTENT BODY -->
        <!-- END CONTENT BODY -->
    </div>
    <!-- END CONTENT -->
    <!-- BEGIN QUICK SIDEBAR -->
    {{-- @include('layout.sidebar') --}}
    <!-- END QUICK SIDEBAR -->
</div>