@php
    /**
     * @var $regions \Illuminate\Support\Collection|\AntistressStore\CdekSDK2\Entity\Responses\RegionsResponse[]
     * @var $cities \Illuminate\Database\Eloquent\Collection
     * @var $storage \WezomCms\Orders\Models\OrderDeliveryInformation
     */
@endphp

<div class="form-group">
    {!! Form::label('deliveryInformation[region_code]', __('cms-orders::admin.courier.Region')) !!}
    {!! Form::select(
        'deliveryInformation[region_code]',
        $regions,
        old('deliveryInformation.region_code', $storage->region_code),
        [ 'id' => 'sdek-region-select', 'class' => 'js-select2' ]
    ) !!}
</div>
<div class="form-group">
    {!! Form::label('deliveryInformation[city_code]', __('cms-orders::admin.pickup.Locality')) !!}
    {!! Form::select(
        'deliveryInformation[city_code]',
        $cities,
        null,
        [
            'id' => 'sdek-city-select',
            'class' => 'js-ajax-select2',
            'data-url' => route('admin.sdek.search-cities', [ 'region' => $storage->region_code ]),
            'data-minimum-input-length' => 3
        ]
    ) !!}
</div>
<div class="form-group">
    {!! Form::label('deliveryInformation[postal_code]', __('cms-orders::admin.courier.Postal code')) !!}
    {!! Form::text('deliveryInformation[postal_code]', old('deliveryInformation.postal_code', $storage->postal_code)) !!}
</div>
<div class="form-group">
    {!! Form::label('deliveryInformation[address]', __('cms-orders::admin.courier.Address')) !!}
    {!! Form::text('deliveryInformation[address]', old('deliveryInformation.address', $storage->address)) !!}
</div>
<div class="form-group">
    {!! Form::label('deliveryInformation[tariff_code]', __('cms-orders::admin.courier.Tariff')) !!}
    {!! Form::select(
        'deliveryInformation[tariff_code]',
        $tariffs,
        old('deliveryInformation.tariff_code', $storage->tariff_code),
        [ 'id' => 'sdek-tariff-select' ]
    ) !!}
</div>
<div class="form-group">
    {!! Form::label('deliveryInformation[ttn]', __('cms-orders::admin.orders.TTN')) !!}
    {!! Form::text('deliveryInformation[ttn]', $storage->ttn, [ 'disabled' => true ]) !!}
</div>
@if (!empty($deliveryStatuses))
    <div>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>@lang('cms-orders::admin.delivery_statuses.Code')</th>
                    <th>@lang('cms-orders::admin.delivery_statuses.Name')</th>
                    <th>@lang('cms-orders::admin.delivery_statuses.Status date')</th>
                </tr>
            </thead>
            <tbody>
                @foreach($deliveryStatuses as $status)
                    @php($statusDate = data_get($status, 'status_date_time'))
                    <tr>
                        <td>{{ data_get($status, 'code') }}</td>
                        <td>{{ data_get($status, 'name') }}</td>
                        <td>{{ $statusDate ? \Illuminate\Support\Carbon::make($statusDate)->format('d.m.Y H:i:s') : null }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif
