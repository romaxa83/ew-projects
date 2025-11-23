@php
    /**
     * @var $cities array
     * @var $deliveryData array
     * @var $warehouses array
     */
@endphp
<div>
    <input placeholder="@lang('cms-orders::site.Искать город')"
           type="text"
           wire:model.debounce.500ms="deliveryData.cityQuery">

    <select class="@if($errors->has('deliveryData.city')) error @elseif($deliveryData['city'] ?? false) valid @endif"
            data-route="{{ route('nova-poshta.get-cities') }}"
            required="required"
            wire:loading.attr="disabled"
            wire:model="deliveryData.city"
    >
        @if($cities)
            <option>@lang('cms-orders::site.Выберите город')</option>
        @endif
        @forelse($cities as $ref => $city)
            <option value="{{ $ref }}">{{ $city }}</option>
        @empty
            <option disabled>@lang('cms-orders::site.Ничего не найдено')</option>
        @endforelse
    </select>
</div>
<div>
    <select class="@if($errors->has('deliveryData.warehouse')) error @elseif($deliveryData['warehouse'] ?? false) valid @endif"
            data-np-select
            required="required"
            wire:loading.attr="disabled"
            wire:model="deliveryData.warehouse">
        @if($cities && !$warehouses)
            <option>@lang('cms-orders::site.Сначала выберите город')</option>
        @elseif($warehouses)
            <option>@lang('cms-orders::site.Выберите номер отделения')</option>
        @endif
        @foreach($warehouses as $ref => $warehouse)
            <option value="{{ $ref }}">{{ $warehouse }}</option>
        @endforeach
    </select>
</div>
