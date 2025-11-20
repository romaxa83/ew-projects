<!DOCTYPE html>
<html lang="{{ App::getLocale() }}" {!! count(app('locales')) <= 1 ? 'data-hide-lang-tabs="true"' : '' !!}>
<head>
    @include('cms-core::admin.partials.head')
    @widget('admin:assets', ['position' => \WezomCms\Core\Contracts\Assets\AssetManagerInterface::POSITION_HEAD])
</head>
<body class="fix-header fix-sidebar">
@widget('admin:assets', ['position' => \WezomCms\Core\Contracts\Assets\AssetManagerInterface::POSITION_START_BODY])
@include('cms-core::admin.partials.preloader')
<!-- Main wrapper  -->
<div id="main-wrapper">
    <!-- header header  -->
    @include('cms-core::admin.partials.header')
    <!-- End header header -->
    <!-- Left Sidebar  -->
    <div class="left-sidebar">
        <!-- Sidebar scroll-->
    @include('cms-core::admin.partials.sidebar')
    <!-- End Sidebar scroll-->
    </div>
    <!-- End Left Sidebar  -->
    <!-- Page wrapper  -->
    <div class="page-wrapper">
        @includeUnless($__env->yieldContent('hide-page-title'), 'cms-core::admin.partials.page-title')
        <!-- Container fluid  -->
        <div class="container-fluid p-3">
            <!-- Start Page Content -->
            @include('cms-core::admin.partials.errors')
            @yield('filter', '')
            @yield('main')
            <!-- End Page Content -->
        </div>
        <!-- End Container fluid  -->
        @include('cms-core::admin.partials.footer')
    </div>
    <!-- End Page wrapper  -->
</div>
<!-- End Wrapper -->
@include('cms-core::admin.partials.js.index')
@widget('admin:assets', ['position' => \WezomCms\Core\Contracts\Assets\AssetManagerInterface::POSITION_END_BODY])
@include('cms-core::admin.partials.flash')
@routes
</body>
</html>
