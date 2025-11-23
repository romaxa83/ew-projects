@php
    /**
     * @var $city string|null
     * @var $branch string|null
     * @var $ttn string|null
     */
@endphp
<dl>
    <dt>{{ __('cms-orders::admin.pickup.Locality') }}</dt>
    <dd>{{ $city ?? __('cms-core::admin.layout.Not set') }}</dd>
</dl>
<dl>
    <dt>{{ __('cms-orders::admin.pickup.Branch') }}</dt>
    <dd>{{ $branch ?? __('cms-core::admin.layout.Not set') }}</dd>
</dl>
<dl>
    <dt>{{ __('cms-orders::admin.orders.TTN') }}</dt>
    <dd>{{ $ttn ?? __('cms-core::admin.layout.Not set') }}</dd>
</dl>
