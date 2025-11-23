@php
    /**
     * @var $redirect string
     * @var $name string
     * @var $surname string
     * @var $login string
     * @var $password string
     * @var $passwordConfirmation string
     * @var $subscribe bool
     */
@endphp
<div class="modal-content">
    <button type="button"
            x-data="openModal('users.login', {{ json_encode(compact('redirect')) }})"
            x-on:click="open">
        @lang('cms-users::site.Войти')
    </button>

    <x-form-form wire:submit.prevent="submit">
        <x-form-group name="name" :label="__('cms-users::site.Имя')">
            <x-form-input wire:model="name"/>
        </x-form-group>

        <x-form-group name="surname" :label="__('cms-users::site.Фамилия')">
            <x-form-input wire:model="surname"/>
        </x-form-group>

        <x-form-group name="login" :label="__('cms-users::site.E-mail или телефон')">
            <x-form-input wire:model="login"/>
        </x-form-group>

        <x-form-group name="password" :label="__('cms-users::site.Придумайте пароль')">
            <x-form-password wire:model="password"/>
        </x-form-group>

        <x-form-group name="passwordConfirmation" :label="__('cms-users::site.Повторите пароль')">
            <x-form-password wire:model="passwordConfirmation"/>
        </x-form-group>

        @if($this->newsletterLoaded)
            <x-form-group name="subscribe" :label="__('cms-users::site.Подписаться на рассылку')">
                <x-form-checkbox wire:model="subscribe" value="1"/>
            </x-form-group>
        @endif

        <button type="submit">@lang('cms-users::site.Зарегистрироваться')</button>

        @widget('ui:agree-text')
    </x-form-form>
</div>
