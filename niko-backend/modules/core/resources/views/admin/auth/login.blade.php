@extends('cms-core::admin.layouts.login')

@section('content')
    <div class="unix-login">
        <div class="container-fluid">
            <div class="row justify-content-center">
                <div class="col-lg-4">
                    <div class="login-content card">
                        <div class="login-form">
                            <h4>@lang('cms-core::admin.auth.Login')</h4>
                            @include('cms-core::admin.partials.errors')
                            {!! Form::open(['url' => route('admin.login')]) !!}
                            <div class="form-group">
                                {!! Form::label('email', __('cms-core::admin.auth.E-Mail address')) !!}
                                {!! Form::email('email', old('email'), ['placeholder' => __('cms-core::admin.auth.E-Mail'), 'required' => 'required', 'tabindex' => '1']) !!}
                            </div>
                            <div class="form-group">
                                {!! Form::label('password', __('cms-core::admin.auth.Password')) !!}
                                {!! Form::password('password', ['placeholder' => __('cms-core::admin.auth.Password'), 'required' => 'required', 'tabindex' => '2']) !!}
                            </div>
                            <div class="d-flex justify-content-between">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" name="remember" class="form-check-input custom-control-input" id="remember" {{ old('remember') ? 'checked' : '' }} tabindex="3">
                                    <label class="form-check-label custom-control-label" for="remember">@lang('cms-core::admin.auth.Remember Me')</label>
                                </div>
                                <div>
                                    <a href="{{ route('admin.password.request') }}">@lang('cms-core::admin.auth.Forgotten Password?')</a>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary btn-flat m-b-30 m-t-30" tabindex="4">@lang('cms-core::admin.auth.Sign in')</button>
                            {!! Form::close() !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
