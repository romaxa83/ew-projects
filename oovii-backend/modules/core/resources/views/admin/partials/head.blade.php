<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<!-- Tell the browser to be responsive to screen width -->
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="author" content="{{ config('cms.core.main.vendor.name') }}">
<!-- Favicon icon -->
<link rel="icon" href="{{ asset('vendor/cms/core/static/favicon.ico') }}">

<meta name="robots" content="noindex, nofollow" />
<meta name="csrf-token" content="{{ csrf_token() }}">

<title>{{ $title }}</title>

<!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
<!--[if lt IE 9]>
<script src="{{ asset('vendor/cms/core/js/lib/html5shiv/html5shiv.js') }}"></script>
<script src="{{ asset('vendor/cms/core/js/lib/respond/respond.min.js') }}"></script>
<![endif]-->
