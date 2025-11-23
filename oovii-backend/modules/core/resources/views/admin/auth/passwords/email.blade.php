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
                            @if (session('status'))
                                <div class="alert alert-success" role="alert">{{ session('status') }}</div>
                            @endif
                            {!! Form::open(['url' => route('admin.password.email')]) !!}
                            <div class="form-group">
                                {!! Form::label('email', __('cms-core::admin.auth.E-Mail address')) !!}
                                {!! Form::email('email', old('email'), ['placeholder' => __('cms-core::admin.auth.E-Mail')]) !!}
                            </div>
                            <button type="submit" class="btn btn-primary btn-flat m-b-30 m-t-30">@lang('cms-core::admin.auth.Send Password Reset Link')</button>
                            {!! Form::close() !!}
                            <div><a href="{{ route('admin.login-form') }}"><i class="fa fa-reply-all"></i> @lang('cms-core::admin.auth.Come back')</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
