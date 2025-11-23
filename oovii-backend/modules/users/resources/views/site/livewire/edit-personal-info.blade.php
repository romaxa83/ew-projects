@php
    /**
     * @var $user \WezomCms\Users\Models\User
     * @var $edit bool
     * @var $name string|null
     * @var $surname string|null
     * @var $patronymic string|null
     * @var $email string|null
     * @var $phone string|null
     */
@endphp
<div>
    <x-form-form wire:submit.prevent="save">
        <strong>@lang('cms-users::site.Редактирование личных данных')</strong>

        <x-form-group name="surname" :label="__('cms-users::site.Фамилия')">
            <x-form-input wire:model="surname"/>
        </x-form-group>

         <x-form-group name="name" :label="__('cms-users::site.Имя')">
            <x-form-input wire:model="name"/>
        </x-form-group>

         <x-form-group name="patronymic" :label="__('cms-users::site.Отчество')">
            <x-form-input wire:model="patronymic"/>
        </x-form-group>

        <x-form-group name="email" :label="__('cms-users::site.E-mail')">
            <x-form-email wire:model="email"/>
        </x-form-group>

        <x-form-group name="phone" :label="__('cms-users::site.Телефон')">
            <x-form-phone wire:model="phone"/>
        </x-form-group>

        <button type="submit">@lang('cms-users::site.Обновить личные данные')</button>
    </x-form-form>
</div>
