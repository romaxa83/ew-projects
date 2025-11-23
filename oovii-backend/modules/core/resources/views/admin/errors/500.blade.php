@extends('cms-core::admin.layouts.main')

@section('hide-page-title', true)

@section('main')
    <div class="error-body text-center">
        <h1>500</h1>
        <h3 class="text-uppercase">@lang('cms-core::admin.Server error')</h3>
        <p class="text-muted m-t-30 m-b-30">@lang('cms-core::admin.Oops, something went wrong on our servers')</p>
        @if(Route::currentRouteName() !== 'admin.dashboard')
            <a class="btn btn-info btn-rounded waves-effect waves-light m-b-40"
               href="{{ route('admin.dashboard') }}">@lang('cms-core::admin.auth.Back to home')</a></div>
        @endif
@endsection
