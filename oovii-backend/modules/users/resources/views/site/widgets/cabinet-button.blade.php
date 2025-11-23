@php
    /**
     * @var $user \WezomCms\Users\Models\User|null
     */
@endphp
@auth
    <a href="{{ route('cabinet') }}" title="@lang('cms-users::site.Перейти в личный кабинет')">
        @lang('cms-users::site.Мой кабинет')
    </a>
    <div>
        <div>@lang('cms-users::site.Здравствуйте'),</div>
        <strong>{{ $user->full_name }}</strong>!
    </div>
@else
    <div x-data="openModal('users.login')"
         x-on:click="open"
         x-on:mouseenter="open">
        @lang('cms-users::site.Вход / Регистрация')
    </div>
@endauth
