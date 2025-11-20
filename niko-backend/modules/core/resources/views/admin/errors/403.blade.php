@extends('cms-core::admin.layouts.main')

@section('main')
    <div class="error-body text-center">
        <h1>403</h1>
        <h3 class="text-uppercase">@lang('cms-core::admin.auth.Access is denied!')</h3>
        <p class="text-muted m-t-30 m-b-30">@lang('cms-core::admin.auth.You are not allowed to access this section')</p>
        <a class="btn btn-info btn-rounded waves-effect waves-light m-b-40"
           href="{{ route('admin.dashboard') }}">@lang('cms-core::admin.auth.Back to home')</a></div>
@endsection
