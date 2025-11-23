@extends('cms-core::admin.layouts.login')

@section('content')
    <div class="unix-login">
        <div class="container-fluid">
            <div class="row justify-content-center">
                <div class="col-lg-4">
                    <div class="login-content card">
                        <div class="login-form">
                            <h4>@lang('cms-core::admin.auth.Reset password')</h4>
                            @include('cms-core::admin.partials.errors')
                            {!! Form::open(['url' => route('admin.password.reset')]) !!}
                            <input type="hidden" name="token" value="{{ $token }}">
                            <div class="form-group">
                                {!! Form::label('email', __('cms-core::admin.auth.E-Mail address')) !!}
                                {!! Form::email('email', $email ?? old('email'), ['placeholder' => __('cms-core::admin.auth.E-Mail')]) !!}
                            </div>
                            <div class="form-group">
                                {!! Form::label('password', __('cms-core::admin.auth.New password')) !!}
                                {!! Form::password('password', ['placeholder' => __('cms-core::admin.auth.New password')]) !!}
                            </div>
                            <div class="form-group">
                                {!! Form::label('password_confirmation', __('cms-core::admin.auth.Confirm password')) !!}
                                {!! Form::password('password_confirmation', ['placeholder' => __('cms-core::admin.auth.Confirm password')]) !!}
                            </div>
                            <button type="submit" class="btn btn-primary btn-flat m-b-30 m-t-30">@lang('cms-core::admin.auth.Reset password')</button>
                            {!! Form::close() !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
