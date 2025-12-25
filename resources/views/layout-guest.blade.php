<!DOCTYPE html>
<!--[if IE 8]>
<html lang="en" class="ie8 no-js"> <![endif]-->
<!--[if IE 9]>
<html lang="en" class="ie9 no-js"> <![endif]-->
<!--[if !IE]><!-->
<html lang="en" xmlns="http://www.w3.org/1999/html">
<!--<![endif]-->
<!-- BEGIN HEAD -->

<head>
    <meta charset="utf-8"/>
    <title>Cape Cod WHS</title>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta content="width=device-width, initial-scale=1" name="viewport"/>
    <meta content="" name="description"/>
    <meta content="" name="author"/>
    <meta id="token" name="token" value="E7hzfjZOmYPWTh6EzlcftPw0SXOczZBiz6N9Cwaq"/>
    <!-- BEGIN GLOBAL MANDATORY STYLES -->
    <!--<link href="http://fonts.googleapis.com/css?family=Open+Sans:400,300,600,700&subset=all" rel="stylesheet" type="text/css"/>-->
    <link href="/assets/global/plugins/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/simple-line-icons/simple-line-icons.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css"/>
    <!--<link href="/assets/global/plugins/uniform/css/uniform.default.css" rel="stylesheet" type="text/css"/>-->
    <link href="/assets/global/plugins/bootstrap-switch/css/bootstrap-switch.min.css" rel="stylesheet" type="text/css"/>
    <!-- END GLOBAL MANDATORY STYLES -->

    <!-- BEGIN PAGE LEVEL PLUGINS -->
    <link href="/assets/global/plugins/bootstrap-select/css/bootstrap-select.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/bootstrap-toastr/toastr.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/datatables/datatables.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.css" rel="stylesheet" type="text/css"/>
    <!-- END PAGE LEVEL PLUGINS -->

    <!-- BEGIN THEME GLOBAL STYLES -->
    <link href="/assets/global/css/components.min.css" rel="stylesheet" id="style_components" type="text/css"/>
    <link href="/assets/global/css/plugins.min.css" rel="stylesheet" type="text/css"/>
    <!-- END THEME GLOBAL STYLES -->

    <!-- BEGIN PAGE LEVEL STYLES -->
    <link href="/css/custom.css" rel="stylesheet" type="text/css"/>
    <link href="/css/app.css" rel="stylesheet" type="text/css"/>
    <link href="/css/libs.css" rel="stylesheet" type="text/css"/>
    <link href="/css/vue-modal.css" rel="stylesheet" type="text/css"/>
    <!-- END PAGE LEVEL STYLES -->

    <!-- BEGIN THEME LAYOUT STYLES -->
    <link href="/assets/layouts/layout3/css/layout.min.css" rel="stylesheet" type="text/css"/>
    <link href="/assets/layouts/layout3/css/themes/default.min.css" rel="stylesheet" type="text/css" id="style_color"/>
    <link href="/assets/layouts/layout3/css/custom.min.css" rel="stylesheet" type="text/css"/>
    <!-- END THEME LAYOUT STYLES -->
    <link rel="shortcut icon" href="/favicon.ico"/>
</head>
<!-- END HEAD -->

<body id="app" class="page-container-bg-solid page-boxed page-header-menu-fixed ">

<!-- BEGIN HEADER -->
<div class="page-header">
    <!-- BEGIN HEADER TOP -->
    <div class="page-header-top">
        <div class="container">
            <!-- BEGIN LOGO -->
            <div class="page-logo">
                @if (!Request::is('/'))
                    <a href="/"><img src="/img/logo2-sws.png" alt="logo" class="logo-default" style="margin-top:15px"></a>
                @endif
            </div>
            <!-- END LOGO -->
            <!-- BEGIN RESPONSIVE MENU TOGGLER -->
            <a href="javascript:;" class="menu-toggler"></a>
            <!-- END RESPONSIVE MENU TOGGLER -->

            <!-- BEGIN TOP NAVIGATION MENU -->
            {{--
            <div class="top-menu">
                <ul class="nav navbar-nav pull-right">
                    <!-- END TODO DROPDOWN -->
                    <li class="droddown dropdown-separator">
                        <span class="separator"></span>
                    </li>
                    <li class="dropdown dropdown-user dropdown-dark">
                        <a href="/login">
                            <span class="username">Login</span>
                        </a>
                    </li>

                    <li class="dropdown dropdown-user dropdown-dark">
                        <a href="/signup">
                            <span class="username username-hide-mobile">Sign Up</span>
                        </a>
                    </li>

                </ul>
            </div>--}}
            <!-- END TOP NAVIGATION MENU -->

        </div>
    </div>
    <!-- END HEADER TOP -->
    <!-- BEGIN HEADER MENU -->
    <div class="page-header-menu">
        <div class="container">
            <div class="hor-menu  ">
                <ul class="nav navbar-nav">
                    <li class="menu-dropdown classic-menu-dropdown ">
                        <a href="/"><i class="fa fa-home"></i> Home </a>
                    </li>
                    @if (!Auth::check())
                        <li class="menu-dropdown classic-menu-dropdown">
                            <a href="/login"><i class="fa fa-lock"></i> Login </a>
                        </li>
                    @endif
                </ul>
            </div>
            <!-- END MEGA MENU -->
        </div>
    </div>    <!-- END HEADER MENU -->
</div><!-- END HEADER -->

<!-- BEGIN CONTAINER -->
<div class="page-container">
    <!-- BEGIN CONTENT -->
    <div class="page-content-wrapper">
        <!-- BEGIN CONTENT BODY -->
        <!-- BEGIN PAGE HEAD-->
        <div class="page-head">
            <div class="container">
                <!-- BEGIN PAGE TITLE -->
                @yield('pagetitle')
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
    <!-- END QUICK SIDEBAR -->
</div><!-- END CONTAINER -->

<!-- BEGIN FOOTER -->
<!-- BEGIN PRE-FOOTER -->
<div class="page-prefooter">
    <div class="container">
        <div class="row">
            <div class="col-md-4 col-sm-6 col-xs-12 footer-block">
                <h2>Contact</h2>
                <address class="margin-bottom-40"> Phone: (02) 9849 4444
                    <br> Email:
                    <a href="mailto:company@capecod.com.au">company@capecod.com.au</a>
                </address>
            </div>
            <div class="col-md-4 col-sm-6 col-xs-12 footer-block">
                <h2>Follow Us On</h2>
                <ul class="social-icons">
                    <li>
                        <a href="https://www.facebook.com/capecodaust" data-original-title="facebook" class="facebook" target="_blank"></a>
                    </li>
                    <li>
                        <a href="https://www.youtube.com/user/capecodaust" data-original-title="youtube" class="youtube" target="_blank"></a>
                    </li>
                    <li>
                        <a href="https://www.linkedin.com/company/cape-cod-australia" data-original-title="linkedin" class="linkedin" target="_blank"></a>
                    </li>
                    <li>
                        <a href="https://plus.google.com/+CapecodAu/posts" data-original-title="googleplus" class="googleplus" target="_blank"></a>
                    </li>
                </ul>
            </div>
            <div class="col-md-4 col-sm-6 col-xs-12 footer-block">
                <h2>Cape Cod Australia Pty Ltd</h2>
                <p>ABN 54 000 605 407<br>
                    Builders Lic. No 5519 </p>
            </div>
        </div>
    </div>
</div>
<!-- END PRE-FOOTER -->
<!-- BEGIN INNER FOOTER -->
<div class="page-footer">
    <div class="container">
        Licensed to Cape Cod
    </div>
</div>
<div class="scroll-to-top">
    <i class="icon-arrow-up"></i>
</div>
<!-- END INNER FOOTER --><!-- END FOOTER -->

<!--[if lt IE 9]>
<script src="/assets/global/plugins/respond.min.js"></script>
<script src="/assets/global/plugins/excanvas.min.js"></script>
<![endif]-->
<!-- BEGIN CORE PLUGINS -->
<script src="/assets/global/plugins/jquery.min.js" type="text/javascript"></script>
<script src="/assets/global/plugins/bootstrap/js/bootstrap.min.js" type="text/javascript"></script>
<script src="/assets/global/plugins/js.cookie.min.js" type="text/javascript"></script>
<script src="/assets/global/plugins/bootstrap-hover-dropdown/bootstrap-hover-dropdown.min.js" type="text/javascript"></script>
<script src="/assets/global/plugins/jquery-slimscroll/jquery.slimscroll.min.js" type="text/javascript"></script>
<script src="/assets/global/plugins/jquery.blockui.min.js" type="text/javascript"></script>
<!--<script src="/assets/global/plugins/uniform/jquery.uniform.min.js" type="text/javascript"></script>-->
<script src="/assets/global/plugins/bootstrap-switch/js/bootstrap-switch.min.js" type="text/javascript"></script>
<!-- END CORE PLUGINS -->

<!-- BEGIN PAGE LEVEL PLUGINS -->
<script src="/assets/global/plugins/bootstrap-select/js/bootstrap-select.min.js" type="text/javascript"></script>
<script src="/assets/global/plugins/bootstrap-toastr/toastr.min.js" type="text/javascript"></script>
<script src="/assets/global/scripts/datatable.js" type="text/javascript"></script>
<script src="/assets/global/plugins/datatables/datatables.min.js" type="text/javascript"></script>
<script src="/assets/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.js" type="text/javascript"></script>
@yield('page-level-plugins')

<!-- END PAGE LEVEL PLUGINS -->

<!-- BEGIN THEME GLOBAL SCRIPTS -->
<script src="/assets/global/scripts/app.min.js" type="text/javascript"></script>
<!-- END THEME GLOBAL SCRIPTS -->
<!-- BEGIN PAGE LEVEL SCRIPTS -->
<script src="/assets/pages/scripts/components-bootstrap-select.min.js" type="text/javascript"></script>
<script src="/assets/pages/scripts/ui-toastr.min.js" type="text/javascript"></script>
<script src="/js/libs.js" type="text/javascript"></script>
@yield('page-level-scripts')
<!-- END PAGE LEVEL SCRIPTS -->

<!-- BEGIN THEME LAYOUT SCRIPTS -->
<script src="/assets/layouts/layout3/scripts/layout.min.js" type="text/javascript"></script>
<script src="/assets/layouts/layout3/scripts/demo.min.js" type="text/javascript"></script>
<script src="/assets/layouts/global/scripts/quick-sidebar.min.js" type="text/javascript"></script>
<!-- END THEME LAYOUT SCRIPTS -->

@yield('custom-page-scripts')


<script type="text/javascript"></script>

</body>

</html>