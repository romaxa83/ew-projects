@php
    /**
     * @var $showEditForm bool
     * @var $showCreateForm bool
     * @var $address \WezomCms\Orders\Models\UserAddress
     * @var $editedRow array
     */
@endphp
<div>
    <div>
        @if($showEditForm)
            @lang('cms-orders::site.Редактирование адреса')
        @else
            @lang('cms-orders::site.Добавление адреса')
        @endif
    </div>

    <x-form-form wire:submit.prevent="save">
        <x-form-group name="editedRow.city" :label="__('cms-orders::site.Город или населенный пункт')">
            <x-form-input wire:model="editedRow.city"/>
        </x-form-group>

        <x-form-group name="editedRow.street" :label="__('cms-orders::site.Улица')">
            <x-form-input wire:model="editedRow.street"/>
        </x-form-group>

        <x-form-group name="editedRow.house" :label="__('cms-orders::site.Номер дома')">
            <x-form-input wire:model="editedRow.house"/>
        </x-form-group>

        <x-form-group name="editedRow.room" :label="__('cms-orders::site.Квартира')">
            <x-form-input wire:model="editedRow.room" type="number"/>
        </x-form-group>

        <button wire:click="cancel">&Cross;@lang('cms-orders::site.Отменить')</button>
        <button wire:click="save">&Vee;@lang('cms-orders::site.Сохранить адрес')</button>
    </x-form-form>
</div>
