<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="root-text-lg">
<head>
    <meta charset="utf-8">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Styles -->
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no, user-scalable=no, minimal-ui">
    <!-- Call App Mode on ios devices -->
    <meta name="mobile-web-app-capable" content="yes"/>
    <!-- Remove Tap Highlight on Windows Phone IE -->
    <meta name="msapplication-tap-highlight" content="no">
    <!-- base css -->
    <link id="vendorsbundle" rel="stylesheet" media="screen, print"
          href="{{ asset('/smartadmin/css/vendors.bundle.css') }}">
    <link rel="stylesheet" media="screen, print" href="{{ asset('/smartadmin/css/fa-light.css') }}">
    <link rel="stylesheet" media="screen, print" href="{{ asset('/smartadmin/css/fa-brands.css') }}">
    <link rel="stylesheet" media="screen, print" href="{{ asset('/smartadmin/css/fa-solid.css') }}">
    <link rel="stylesheet" media="screen, print" href="{{ asset('/smartadmin/css/fa-duotone.css') }}">
    <link id="appbundle" rel="stylesheet" media="screen, print" href="{{ asset('/smartadmin/css/app.bundle.css') }}">
    <link id="myskin" rel="stylesheet" media="screen, print"
          href="{{ asset('/smartadmin/css/skins/skin-master.css') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('/smartadmin/img/favicon/apple-touch-icon.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('/smartadmin/img/favicon/favicon-32x32.png') }}">
    <link rel="mask-icon" href="{{ asset('/smartadmin/img/favicon/safari-pinned-tab.svg') }}" color="#5bbad5">
    <link rel="stylesheet" media="screen, print" href="{{ asset('/smartadmin/css/theme-demo.css') }}">
    <link id="mytheme" rel="stylesheet" href="/smartadmin/css/themes/cust-theme-3.css">
    <link href="{{ mix('css/app.css') }}" rel="stylesheet">
    @stack('extendHeader')
</head>
<body class="mod-bg-1 mod-nav-link mod-clean-page-bg nav-function-fixed mod-hide-info-card header-function-fixed mod-skin-light">
<!-- DOC: script to save and load page settings -->
<script>
    /**
     *    This script should be placed right after the body tag for fast execution
     *    Note: the script is written in pure javascript and does not depend on thirdparty library
     **/
    'use strict';

    var classHolder = document.getElementsByTagName("BODY")[0],
        /**
         * Load from localstorage
         **/
        themeSettings = (localStorage.getItem('themeSettings')) ? JSON.parse(localStorage.getItem('themeSettings')) :
            {},
        themeURL = themeSettings.themeURL || '',
        themeOptions = themeSettings.themeOptions || '';
    /**
     * Load theme options
     **/
    if (themeSettings.themeOptions) {
        classHolder.className = themeSettings.themeOptions;
        console.log("%c✔ Theme settings loaded", "color: #148f32");
    } else {
        console.log("Heads up! Theme settings is empty or does not exist, loading default settings...");
    }
    if (themeSettings.themeURL && !document.getElementById('mytheme')) {
        var cssfile = document.createElement('link');
        cssfile.id = 'mytheme';
        cssfile.rel = 'stylesheet';
        cssfile.href = themeURL;
        document.getElementsByTagName('head')[0].appendChild(cssfile);
    }
    /**
     * Save to localstorage
     **/
    var saveSettings = function () {
        themeSettings.themeOptions = String(classHolder.className).split(/[^\w-]+/).filter(function (item) {
            return /^(nav|header|mod|display)-/i.test(item);
        }).join(' ');
        if (document.getElementById('mytheme')) {
            themeSettings.themeURL = document.getElementById('mytheme').getAttribute("href");
        }
        localStorage.setItem('themeSettings', JSON.stringify(themeSettings));
    }
    /**
     * Reset settings
     **/
    var resetSettings = function () {
        localStorage.setItem("themeSettings", "");
    }
</script>

<!-- BEGIN Page Wrapper -->
<div class="page-wrapper" id="app">
    <div id="page-spinner" class="frame-wrap position-fixed w-100 h-100 opacity-50 d-none">
        <div class="d-flex justify-content-center">
            <div class="spinner-border text-info position-absolute" style="top:50%;" role="status" >
                <span class="sr-only">Loading...</span>
            </div>
        </div>
    </div>

    <div class="page-inner">
        @if(auth_user()->isCanView())
            @include('layouts.app.sidebar')
        @endif

        <div class="page-content-wrapper">

        @include('layouts.app.header')

            @if(auth_user()->isCanView())

                <main id="js-page-content" role="main" class="page-content">
                    @if(session()->has('messages'))
                        <div class="row">
                            <div class="col-md-12">
                                @foreach (session('messages') as $message)
                                    <div class="alert alert-{{ $message['level'] }}">{!! $message['message'] !!}</div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @yield('content')

                </main>
                @if($is_zadarma_enabled)
                    <call-widget/>
                @endif
                @include('layouts.app.footer')

                @include('layouts.app.nav.shortcuts')

                @include('layouts.app.color_profile')

            @endif

        <!-- BEGIN Page Content -->

        </div>
    </div>

    <vue-portals></vue-portals>
</div>

{{--@include('layouts.app.nav.shortcut_menu')--}}

@include('layouts.app.nav.messenger')

@include('layouts.app.nav.page_settings')

<!-- Scripts -->
@production
<!--<script src="{{ asset('js/sentry.js') }}"></script>-->
@endproduction
<script src="{{ asset('/smartadmin/js/vendors.bundle.js') }}"></script>
<script src="{{ asset('/smartadmin/js/app.bundle.js') }}"></script>
<script src="{{ mix('js/lib-bundle.js') }}"></script>
<script src="{{ mix('js/app.js') }}"></script>

<script src="{{ mix('js/pbx.js') }}"></script>
@stack('extendFooter')

</body>
</html>
