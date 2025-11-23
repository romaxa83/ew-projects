@php
    /**
     * @var $id int
     * @var $code null|int
     * @var $password null|string
     * @var $passwordConfirmation null|string
     */
@endphp
<div class="modal-content">
    <div>@lang('cms-users::site.Сброс пароля')</div>
    <x-form-form wire:submit.prevent="submit">
        <x-form-group name="code" :label="__('cms-users::site.Код')">
            <x-form-input wire:model.lazy="code"/>
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
