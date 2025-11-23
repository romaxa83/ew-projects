@extends('cms-ui::layouts.main')

@php
    /**
      * @var $user \WezomCms\Users\Models\User
      */
@endphp

@section('content')
    <div class="container">
        <h1>{{ SEO::getH1() }}</h1>
        @if($user->registered_through === \WezomCms\Users\Models\User::EMAIL)
            <p>@lang('cms-users::site.Проверьте свою электронную почту')</p>
            <p>@lang('cms-users::site.Прежде чем продолжить, пожалуйста, проверьте ссылку подтверждения в своей электронной почте.')</p>
        @else
            <p>@lang('cms-users::site.Подтвердите свой телефон')</p>
            <p>@lang('cms-users::site.Прежде чем продолжить, пожалуйста, проверьте ваши смс на наличие актуального кода подтверждения.')</p>
            <livewire:users.verify-phone :user="$user"/>
        @endif
        <livewire:users.resend-verification />
        @include('cms-users::site.auth.verification.partials.logout')
    </div>
@endsection
