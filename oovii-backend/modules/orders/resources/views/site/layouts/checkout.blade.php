<!doctype html>
<html lang="{{ app()->getLocale() }}" dir="ltr">
<head>
    @include('cms-ui::partials.head.head')
    @widget('seo:metrics', ['position' => 'head'])
</head>
<body>
@widget('seo:metrics', ['position' => 'body'])
<div class="page">
    <div class="page__body">
        @yield('content')
    </div>
    <div class="page__footer">
        @widget('ui:footer')
    </div>
</div>
@widget('ui:hidden-data', ['checkoutPage' => true])
</body>
</html>
