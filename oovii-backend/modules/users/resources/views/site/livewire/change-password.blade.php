@php
    /**
     * @var $oldPassword null|string
     * @var $password null|string
     * @var $passwordConfirmation null|string
     */
@endphp
<div>
    <x-form-form wire:submit.prevent="submit">
        <strong>@lang('cms-users::site.Изменение пароля')</strong>

        <x-form-group name="oldPassword" :label="__('cms-users::site.Текущий Пароль')">
            <x-form-password wire:model="oldPassword" />
        </x-form-group>

        <x-form-group name="password" :label="__('cms-users::site.Новый пароль')">
            <x-form-password wire:model="password" />
        </x-form-group>

        <x-form-group name="passwordConfirmation" :label="__('cms-users::site.Повторите новый пароль')">
            <x-form-password wire:model="passwordConfirmation" />
        </x-form-group>

        <button type="submit">@lang('cms-users::site.Изменить пароль')</button>
    </x-form-form>
</div>
