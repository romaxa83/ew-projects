<!DOCTYPE html>
<html lang="{{ App::getLocale() }}">
<head>
    @include('cms-core::admin.partials.head')
    @widget('admin:assets', ['position' => \WezomCms\Core\Contracts\Assets\AssetManagerInterface::POSITION_HEAD])
</head>
<body class="fix-header fix-sidebar">
@widget('admin:assets', ['position' => \WezomCms\Core\Contracts\Assets\AssetManagerInterface::POSITION_START_BODY])
@include('cms-core::admin.partials.preloader')
<!-- Main wrapper  -->
<div id="main-wrapper">
    @yield('content')
</div>
<!-- End Wrapper -->
@include('cms-core::admin.partials.js.translations')
@widget('admin:assets', ['position' => \WezomCms\Core\Contracts\Assets\AssetManagerInterface::POSITION_END_BODY])
@include('cms-core::admin.partials.flash')
</body>
</html>
