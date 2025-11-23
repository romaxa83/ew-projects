@php
    /**
     * @var $recipient array
     */
@endphp
<div>
    <x-form-group name="recipient.name" :label="__('cms-orders::site.checkout.Имя')">
        <x-form-input wire:model.lazy="recipient.name"/>
    </x-form-group>

    <x-form-group name="recipient.surname" :label="__('cms-orders::site.checkout.Фамилия')">
        <x-form-input wire:model.lazy="recipient.surname"/>
    </x-form-group>

    <x-form-group name="recipient.patronymic" :label="__('cms-orders::site.checkout.Отчество')">
        <x-form-input wire:model.lazy="recipient.patronymic"/>
    </x-form-group>

    <x-form-group name="recipient.phone" :label="__('cms-orders::site.Телефон')">
        <x-form-phone wire:model="recipient.phone"/>
    </x-form-group>
</div>
