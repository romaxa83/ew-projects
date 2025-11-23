@extends('cms-core::admin.layouts.login')

@section('content')
    <div class="unix-login">
        <div class="container-fluid">
            <div class="row justify-content-center">
                <div class="col-lg-4">
                    <div class="login-content card">
                        <div class="login-form">
                            <h4>@lang('cms-core::admin.auth.Register')</h4>
                            @include('cms-core::admin.partials.errors')
                            {!! Form::open(['url' => route('admin.register')]) !!}
                            <div class="form-group">
                                {!! Form::label('name', __('cms-providers::admin.provider.Name')) !!}
                                {!! Form::text('name', null, ['required' => 'required']) !!}
                            </div>
                            <div class="form-group">
                                {!! Form::label('email', __('cms-core::admin.auth.E-Mail address')) !!}
                                {!! Form::email('email', null, ['required' => 'required']) !!}
                            </div>
                            <div class="form-group">
                                {!! Form::label('phone', __('cms-users::admin.Phone')) !!}
                                {!! Form::text('phone', null, ['required' => 'required']) !!}
                            </div>
                            <div class="form-group">
                                {!! Form::label('company', __('cms-provider::admin.company.Company')) !!}
                                {!! Form::text('company', null, ['required' => 'required']) !!}
                            </div>
                            <div class="form-group">
                                {!! Form::label('region_code', __('cms-provider::admin.Region')) !!}
                                {!! Form::select(
                                    'region_code',
                                    $regions,
                                    old('region_code'),
                                    [ 'id' => 'sdek-region-select', 'class' => 'js-select2' ]
                                ) !!}
                            </div>
                            <div class="form-group">
                                {!! Form::label('city_code', __('cms-provider::admin.City')) !!}
                                {!! Form::select(
                                    'city_code',
                                    [],
                                    old('city_code'),
                                    [
                                        'id' => 'sdek-city-select',
                                        'class' => 'js-ajax-select2',
                                        'data-url' => route('admin.sdek.search-cities', [ 'region' => $regions->keys()->first() ]),
                                        'data-minimum-input-length' => 3
                                    ]
                                ) !!}
                            </div>
                            <div class="form-group">
                                {!! Form::label('address', __('cms-provider::admin.company.Address')) !!}
                                {!! Form::text('address', null, ['required' => 'required']) !!}
                            </div>
                            <div class="form-group">
                                {!! Form::label('password', __('cms-core::admin.auth.Password')) !!}
                                {!! Form::password('password', ['required' => 'required', 'autocomplete' => 'new-password']) !!}
                            </div>
                            <div class="form-group">
                                {!! Form::label('password_confirmation', __('cms-users::admin.Confirm password')) !!}
                                {!! Form::password('password_confirmation', ['required' => 'required', 'autocomplete' => 'new-password']) !!}
                            </div>
                            <div class="d-flex justify-content-between">
                                <div>
                                    <a href="{{ route('admin.login') }}">@lang('cms-core::admin.auth.Login')</a>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary btn-flat m-b-30 m-t-30" tabindex="4">@lang('cms-core::admin.auth.Sign up')</button>
                            {!! Form::close() !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
