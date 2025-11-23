@php
    /**
     * @var $token null|string
     * @var $email null|string
     * @var $password null|string
     * @var $passwordConfirmation null|string
     */
@endphp
<div>
    <x-form-form wire:submit.prevent="submit">
        <div>@lang('cms-users::site.Восстановление пароля')</div>

        <x-form-group name="email" :label="__('cms-users::site.E-mail')">
            <x-form-email wire:model="email"/>
        </x-form-group>

        <x-form-group name="password" :label="__('cms-users::site.Пароль')">
            <x-form-password wire:model="password" />
        </x-form-group>

        <x-form-group name="passwordConfirmation" :label="__('cms-users::site.Повторите пароль')">
            <x-form-password wire:model="passwordConfirmation" />
        </x-form-group>

        <button type="submit">@lang('cms-users::site.Восстановить')</button>
    </x-form-form>
</div>
