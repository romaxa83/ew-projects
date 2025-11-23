<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <!-- CSRF Token -->
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Styles -->
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no, user-scalable=no, minimal-ui">
        <!-- Call App Mode on ios devices -->
        <meta name="mobile-web-app-capable" content="yes" />
        <!-- Remove Tap Highlight on Windows Phone IE -->
        <meta name="msapplication-tap-highlight" content="no">
        <!-- base css -->
        <link rel="stylesheet" media="screen, print" href="{{ asset('/smartadmin/css/vendors.bundle.css') }}">
        <link rel="stylesheet" media="screen, print" href="{{ asset('/smartadmin/css/app.bundle.css') }}">
        <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('/smartadmin/img/favicon/apple-touch-icon.png') }}">
        <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('/smartadmin/img/favicon/favicon-32x32.png') }}">
        <link rel="mask-icon" href="{{ asset('/smartadmin/img/favicon/safari-pinned-tab.svg') }}" color="#5bbad5">
        <!-- Optional: page related CSS-->
        <link rel="stylesheet" media="screen, print" href="{{ asset('/smartadmin/css/fa-brands.css') }}">
        @stack('extendHeader')

        <meta name="robots" content="noindex" />
    </head>

    <body>

        <div class="page-wrapper" id="app">

            @yield('content')

        </div>


        <!-- Scripts -->
        <script src="{{ asset('/smartadmin/js/vendors.bundle.js') }}"></script>
        <script src="{{ asset('/smartadmin/js/app.bundle.js') }}"></script>
        <script src="{{ mix('js/lib-bundle.js') }}"></script>
        <script src="{{ mix('js/app.js') }}" defer></script>
        @stack('extendFooter')

    </body>
</html>
