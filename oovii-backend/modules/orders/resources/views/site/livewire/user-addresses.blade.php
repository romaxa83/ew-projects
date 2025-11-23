@php
    /**
     * @var $user \WezomCms\Users\Models\User
     * @var $showEditForm bool
     * @var $showCreateForm bool
     * @var $addresses \Illuminate\Database\Eloquent\Collection|\WezomCms\Orders\Models\UserAddress[]
     */
@endphp
<div>
    <strong>@lang('cms-orders::site.Адреса доставки')</strong>
    @if($showEditForm || $showCreateForm)
        @include('cms-orders::site.partials.edit-address-form')
    @elseif($addresses->isNotEmpty())
        <div>
            @foreach($addresses as $address)
                <div>
                    <div>
                        <div>{{ $address->full_address }}</div>
                        @if($address->primary)
                            <div>@lang('cms-orders::site.Основной адрес')</div>
                        @else
                            <div wire:click="setPrimary({{ $address->id }})">
                                <input type="checkbox"
                                       value="{{ $address->id }}">
                                <span>@lang('cms-orders::site.Сделать основным')</span>
                            </div>
                        @endif
                    </div>
                    <button wire:click="startEditAddress({{ $address->id }})">@lang('cms-orders::site.Редактировать')</button>
                    <button wire:click="deleteAddress({{ $address->id }})">
                        &Cross;
                    </button>
                </div>
            @endforeach
            <div>
                <button wire:click="openCreateAddressForm">+@lang('cms-orders::site.Добавить адрес')</button>
            </div>
        </div>
    @else
        <div>
            <div>@lang('cms-orders::site.Вы пока не создали ни одного адреса')</div>
            <button wire:click="openCreateAddressForm">+@lang('cms-orders::site.Добавить адрес доставки')</button>
        </div>
    @endif
</div>
