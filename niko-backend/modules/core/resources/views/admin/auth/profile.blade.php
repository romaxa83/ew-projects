@extends('cms-core::admin.layouts.main')

@section('main')
    {!! Form::model($user, ['route' => 'admin.update-profile', 'method' => 'post', 'id' => 'form']) !!}
    <div class="row">
        <div class="col-lg-7">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="py-2"><strong>@lang('cms-core::admin.profile.Main data')</strong></h5>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        {!! Form::label('name', __('cms-core::admin.profile.Name')) !!}
                        {!! Form::text('name') !!}
                    </div>
                    <div class="form-group">
                        {!! Form::label('email', __('cms-core::admin.profile.E-mail')) !!}
                        {!! Form::email('email') !!}
                    </div>
{{--                    @if($apiEnabled)--}}
{{--                        <div class="form-group">--}}
{{--                            {!! Form::label('api_token', __('cms-core::admin.profile.API access token')) !!}--}}
{{--                            {!! Form::text('api_token') !!}--}}
{{--                        </div>--}}
{{--                    @endif--}}
                    <div class="form-group">
                        {!! Form::label('notify', __('cms-core::admin.profile.Receive notifications')) !!}
                        {!! Form::status('notify') !!}
                    </div>
                    <button class="btn btn-success">@lang('cms-core::admin.layout.Save')</button>
                    <a href="{{ route('admin.dashboard') }}"
                       class="btn btn-danger">@lang('cms-core::admin.layout.Close')</a>
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="py-2"><strong>@lang('cms-core::admin.profile.Change password')</strong></h5>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        {!! Form::label('password', __('cms-core::admin.profile.Password')) !!}
                        {!! Form::password('password', ['autocomplete' => 'new-password']) !!}
                    </div>
                    <div class="form-group">
                        {!! Form::label('password_confirmation', __('cms-core::admin.profile.Confirm password')) !!}
                        {!! Form::password('password_confirmation') !!}
                    </div>
                    <button class="btn btn-success">@lang('cms-core::admin.profile.Update password')</button>
                    <a href="{{ route('admin.dashboard') }}"
                       class="btn btn-danger">@lang('cms-core::admin.layout.Close')</a>
                </div>
            </div>
        </div>
    </div>
    {!! Form::close() !!}
@endsection
