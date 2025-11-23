@php
    /**
     * @var $redirect string
     * @var $login string
     */
@endphp
<div class="modal-content">
    <button type="button"
            x-data="openModal('users.login', {{ json_encode(compact('redirect')) }})"
            x-on:click="open"
    >@lang('cms-users::site.Войти')</button>

    <x-form-form wire:submit.prevent="submit">
        <x-form-group name="login" :label="__('cms-users::site.Ваш E-mail или телефон')">
            <x-form-input wire:model="login"/>
        </x-form-group>

        <button type="submit">@lang('cms-users::site.Восстановить')</button>
    </x-form-form>
</div>
