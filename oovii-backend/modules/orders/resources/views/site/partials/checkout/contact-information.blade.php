@php
/**
 * @var $userInfo array
 */
@endphp
<div>
    <b>1. @lang('cms-orders::site.Контактная информация')</b>
    <div>
        <x-form-group name="user.name" :label="__('cms-orders::site.Имя')">
            <x-form-input wire:model.lazy="user.name"/>
        </x-form-group>

        <x-form-group name="user.surname" :label="__('cms-orders::site.Фамилия')">
            <x-form-input wire:model.lazy="user.surname"/>
        </x-form-group>

        <x-form-group name="user.patronymic" :label="__('cms-orders::site.Отчество')">
            <x-form-input wire:model.lazy="user.patronymic"/>
        </x-form-group>

        <x-form-group name="user.phone" :label="__('cms-orders::site.Телефон')">
            <x-form-phone wire:model="user.phone"/>
        </x-form-group>

        <x-form-group name="user.email" :label="__('cms-orders::site.E-mail')">
            <x-form-email wire:model.lazy="user.email"/>
        </x-form-group>

        @guest
            <x-form-group name="user.registerMe">
                <x-form-checkbox wire:model="user.registerMe" value="1">@lang('cms-orders::site.Зарегистрировать меня')</x-form-checkbox>
            </x-form-group>
        @endguest
    </div>

    @guest
        <div>
            <div>@lang('cms-orders::site.Я постоянный клиент')</div>
            <div x-data="openModal('users.login', {{ json_encode(['redirect' => route('checkout')]) }})"
                 x-on:click="forceOpen">
                @lang('cms-orders::site.Войти в аккаунт')
            </div>
            @widget('cabinet-auth-socials', ['redirect' => route('checkout')])
        </div>
    @endguest
</div>
