@php
    /**
     * @var $userAddresses \WezomCms\Orders\Models\UserAddress[]|\Illuminate\Database\Eloquent\Collection
     * @var $deliveryData array
     */
@endphp

@if($userAddresses->isNotEmpty())
    <x-form-group name="deliveryData.savedAddressId">
        <x-form-select wire:model="deliveryData.savedAddressId" :options="$userAddresses->pluck('full_address', 'id')->prepend(__('cms-orders::site.Другой'), '')" />
    </x-form-group>
@endif

<x-form-group name="deliveryData.city" :label="__('cms-orders::site.Город')">
    <x-form-input wire:model="deliveryData.city"/>
</x-form-group>

<x-form-group name="deliveryData.street" :label="__('cms-orders::site.Улица')">
    <x-form-input wire:model="deliveryData.street"/>
</x-form-group>

<x-form-group name="deliveryData.house" :label="__('cms-orders::site.Номер дома')">
    <x-form-input wire:model="deliveryData.house"/>
</x-form-group>

<x-form-group name="deliveryData.room" :label="__('cms-orders::site.Квартира')">
    <x-form-input wire:model="deliveryData.room"/>
</x-form-group>

@auth
    <x-form-group name="deliveryData.saveAddress" :label="__('cms-orders::site.Номер дома')">
        <x-form-checkbox wire:model="deliveryData.saveAddress">@lang('cms-vacancies::site.Сохранить адрес')</x-form-checkbox>
    </x-form-group>
@endauth
