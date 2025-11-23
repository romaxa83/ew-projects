@php
    /**
     * @var $redirect string
     * @var $login string
     * @var $password string
     * @var $remember string
     */
@endphp
<div class="modal-content">
    <div>@lang('cms-users::site.Вход') / @lang('cms-users::site.Регистрация')</div>

    <x-form-form wire:submit.prevent="submit">
        <x-form-group name="login" :label="__('cms-users::site.Ваш E-mail или телефон')">
            <x-form-input wire:model="login"/>
        </x-form-group>

        <x-form-group name="password" :label="__('cms-users::site.Ваш пароль')">
            <x-form-password wire:model="password" />
        </x-form-group>

        <x-form-group name="remember">
            <x-form-checkbox wire:model="remember" value="1">@lang('cms-users::site.Запомнить меня')</x-form-checkbox>
        </x-form-group>

        <button type="button"
                x-data="openModal('users.forgot-password', {{ json_encode(compact('redirect')) }})"
                x-on:click="open"
            >@lang('cms-users::site.Забыли пароль?')</button>

        <button type="submit">@lang('cms-users::site.Войти')</button>
    </x-form-form>

    @widget('cabinet-auth-socials', compact('redirect'))

    <div>
        <div>@lang('cms-users::site.Зарегистрируйся')</div>
        <button x-data="openModal('users.register', {{ json_encode(compact('redirect')) }})"
                x-on:click="open"
        >@lang('cms-users::site.Создать аккаунт')</button>
    </div>
</div>
