@php
    /**
     * @var $cities \Illuminate\Database\Eloquent\Collection
     * @var $branches \Illuminate\Database\Eloquent\Collection
     * @var $storage \WezomCms\Orders\Models\OrderDeliveryInformation
     */
@endphp
<div class="row">
    <div class="col-md-9">
        {!! Form::label('deliveryInformation[city_ref]', __('cms-orders::admin.pickup.Locality')) !!}
        {!! Form::select(
            'deliveryInformation[city_ref]',
            $cities,
            null,
            [
                'id' => 'np-city-select',
                'class' => 'js-ajax-select2',
                'data-url' => route('admin.nova-poshta.search-cities'),
                'data-minimum-input-length' => 3
            ]
        ) !!}
    </div>
    <div class="col-md-3">
        {!! Form::label('deliveryInformation[ttn]', __('cms-orders::admin.orders.TTN')) !!}
        {!! Form::text('deliveryInformation[ttn]', old('deliveryInformation.ttn', $storage->ttn)) !!}
    </div>
</div>
<div class="form-group">
    {!! Form::label('deliveryInformation[branch_ref]', __('cms-orders::admin.pickup.Branch')) !!}
    {!! Form::select(
        'deliveryInformation[branch_ref]',
        $branches,
        old('deliveryInformation.branch_ref', $storage->branch_ref),
        ['id' => 'np-branch-select', 'class' => 'js-select2']
    ) !!}
</div>
