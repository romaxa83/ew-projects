@php
    /**
     * @var $storage \WezomCms\Orders\Models\OrderDeliveryInformation
     * @var $region string
     * @var $city string
     */
@endphp

<dl>
    <dt>@lang('cms-orders::admin.courier.Region')</dt>
    <dd>{{ $region ?? __('cms-core::admin.layout.Not set') }}</dd>
</dl>
<dl>
    <dt>@lang('cms-orders::admin.courier.Locality')</dt>
    <dd>{{ $city ?? __('cms-core::admin.layout.Not set') }}</dd>
</dl>
<dl>
    <dt>@lang('cms-orders::admin.courier.Address')</dt>
    <dd>{{ $storage->address ?? __('cms-core::admin.layout.Not set') }}</dd>
</dl>
<dl>
    <dt>@lang('cms-orders::admin.orders.TTN')</dt>
    <dd>{{ $storage->ttn ?? __('cms-core::admin.layout.Not set') }}</dd>
</dl>
@if (!empty($deliveryStatuses))
    <div>
        <table class="table table-striped">
            <thead>
            <tr>
                <th>@lang('cms-orders::admin.delivery_statuses.Status code')</th>
                <th>@lang('cms-orders::admin.delivery_statuses.Code')</th>
                <th>@lang('cms-orders::admin.delivery_statuses.Status date')</th>
            </tr>
            </thead>
            <tbody>
            @foreach($deliveryStatuses as $status)
                @php($statusDate = data_get($status, 'status_date_time'))
                <tr>
                    <td>{{ data_get($status, 'status_code') }}</td>
                    <td>{{ data_get($status, 'code') }}</td>
                    <td>{{ $statusDate ? \Illuminate\Support\Carbon::make($statusDate)->format('d.m.Y H:i:s') : null }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@endif
