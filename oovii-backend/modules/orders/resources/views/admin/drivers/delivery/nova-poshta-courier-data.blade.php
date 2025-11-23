@php
    /**
     * @var $storage \WezomCms\Orders\Models\OrderDeliveryInformation
     */
@endphp
<dl>
    <dt>@lang('cms-orders::admin.courier.Locality')</dt>
    <dd>{{ $storage->city ?? __('cms-core::admin.layout.Not set') }}</dd>
</dl>
<dl>
    <dt>@lang('cms-orders::admin.orders.TTN')</dt>
    <dd>{{ $storage->ttn ?? __('cms-core::admin.layout.Not set') }}</dd>
</dl>
<div class="row">
    <div class="col-md-6">
        <dl>
            <dt>@lang('cms-orders::admin.courier.Street')</dt>
            <dd>{{ $storage->street ?? __('cms-core::admin.layout.Not set') }}</dd>
        </dl>
    </div>
    <div class="col-md-4">
        <dl>
            <dt>@lang('cms-orders::admin.courier.House')</dt>
            <dd>{{ $storage->house ?? __('cms-core::admin.layout.Not set') }}</dd>
        </dl>
    </div>
    <div class="col-md-2">
        <dl>
            <dt>@lang('cms-orders::admin.courier.Room')</dt>
            <dd>{{ $storage->room ?? __('cms-core::admin.layout.Not set') }}</dd>
        </dl>
    </div>
</div>
