@extends('layouts.single')

@section('content')

<div class="page-inner bg-brand-gradient">
    <div class="page-content-wrapper bg-transparent m-0">
        <div class="height-10 w-100 shadow-lg px-4 bg-brand-gradient">
            <div class="d-flex align-items-center container p-0">
                <div class="page-logo width-mobile-auto m-0 align-items-center justify-content-center p-0 bg-transparent bg-img-none shadow-0 height-9">
                    <a href="javascript:void(0)" class="page-logo-link press-scale-down d-flex align-items-center">
                        <img src="{{ asset('/smartadmin/img/logo.png') }}" alt="{{ config('app.name') }}" aria-roledescription="logo">
                        <span class="page-logo-text mr-1">{{ config('app.name') }}</span>
                    </a>
                </div>
                @if (Route::has('register'))
                <a href="{{ route('register') }}" class="btn-link text-white ml-auto">
                    Create Account
                </a>
                @endif

                @if (Route::has('password.request'))
                <a class="btn-link text-white ml-auto" href="{{ route('password.request') }}">
                    {{ __('Forgot Your Password?') }}
                </a>
                @endif
            </div>
        </div>
        <div class="flex-1" style="background: url({{ asset('/smartadmin/img/svg/pattern-1.svg') }}) no-repeat center bottom fixed; background-size: cover;">
            <div class="container py-4 py-lg-5 my-lg-5 px-4 px-sm-0">
                <div class="row">
                    <div class="col col-md-6 col-lg-7 hidden-sm-down">
                        <h2 class="fs-xxl fw-500 mt-4 text-white">
                            Allymovers environment v2
                            <small class="h3 fw-300 mt-3 mb-5 text-white opacity-60">
                                Hello text is here
                            </small>
                        </h2>
{{--                        <a href="#" class="fs-lg fw-500 text-white opacity-70">Learn more &gt;&gt;</a>--}}
{{--                        <div class="d-sm-flex flex-column align-items-center justify-content-center d-md-block">--}}
{{--                            <div class="px-0 py-1 mt-5 text-white fs-nano opacity-50">--}}
{{--                                Find us on social media--}}
{{--                            </div>--}}
{{--                            <div class="d-flex flex-row opacity-70">--}}
{{--                                <a href="#" class="mr-2 fs-xxl text-white">--}}
{{--                                    <i class="fab fa-facebook-square"></i>--}}
{{--                                </a>--}}
{{--                                <a href="#" class="mr-2 fs-xxl text-white">--}}
{{--                                    <i class="fab fa-twitter-square"></i>--}}
{{--                                </a>--}}
{{--                                <a href="#" class="mr-2 fs-xxl text-white">--}}
{{--                                    <i class="fab fa-google-plus-square"></i>--}}
{{--                                </a>--}}
{{--                                <a href="#" class="mr-2 fs-xxl text-white">--}}
{{--                                    <i class="fab fa-linkedin"></i>--}}
{{--                                </a>--}}
{{--                            </div>--}}
{{--                        </div>--}}
                    </div>
                    <div class="col-sm-12 col-md-6 col-lg-5 col-xl-4 ml-auto">
                        <h1 class="text-white fw-300 mb-3 d-sm-block d-md-none">
                            {{ __('Login') }}
                        </h1>
                        <div class="card p-4 rounded-plus bg-faded">
                            <form method="POST" action="{{ route('login') }}">
                                @csrf

                                <div class="form-group">

                                    <label class="form-label" for="email">Email</label>
                                    <input id="email" type="email" class="form-control form-control-lg @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" placeholder="{{ __('E-Mail Address') }}" autofocus>

                                    @error('email')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                    <div class="help-block">Your unique email in app</div>
                                </div>


                                <div class="form-group">
                                    <label class="form-label" for="password">Password</label>
                                    <input id="password" type="password" class="form-control form-control-lg @error('password') is-invalid @enderror" name="password" placeholder="{{ __('Password') }}" required autocomplete="current-password">

                                    @error('password')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                    <div class="help-block">Your password</div>
                                </div>

                                <div class="form-group text-left">
                                    <div class="custom-control custom-checkbox">
                                        <input class="custom-control-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="remember"> {{ __('Remember Me') }}</label>
                                    </div>
                                </div>

                                <div class="row no-gutters">
                                    {{--
                                    <div class="col-lg-6 pr-lg-1 my-2">
                                        <button type="submit" class="btn btn-info btn-block btn-lg">Sign in with <i class="fab fa-google"></i></button>
                                    </div>
                                    <div class="col-lg-6 pl-lg-1 my-2">
                                        <button id="js-login-btn" type="submit" class="btn btn-danger btn-block btn-lg">Secure login</button>
                                    </div>
                                    --}}
                                    <button type="submit" class="btn btn-danger btn-block btn-lg">
                                        {{ __('Login') }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="position-absolute pos-bottom pos-left pos-right p-3 text-center text-white">
                    {{ date('Y') }} © {{ config('app.name') }} by&nbsp;<a href='https://developers.com' rel="noreferrer" class='text-white opacity-40 fw-500' title='gotbootstrap.com' target='_blank'>developers</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
