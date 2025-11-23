@extends('cms-ui::layouts.base')

@section('main')
    <div class="content">
        <div class="content-head">
            <div class="container">
                @widget('ui:breadcrumbs')
            </div>
        </div>
        <div class="content-body">
            @yield('content', '')
        </div>
    </div>
    @yield('after_content', '')
@endsection
