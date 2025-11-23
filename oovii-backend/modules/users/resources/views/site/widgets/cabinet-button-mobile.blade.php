@php
    /**
     * @var $user \WezomCms\Users\Models\User|null
     */
@endphp
<li>
    @auth
        <a href="{{ route('cabinet') }}">@lang('cms-users::site.Мой кабинет')</a>
    @else
        <a href="#" class="js-import" data-mfp="ajax" data-mfp-src="{{ route('auth.login-form') }}">
            @lang('cms-users::site.Войти в личный кабинет')
        </a>
    @endauth
</li>
