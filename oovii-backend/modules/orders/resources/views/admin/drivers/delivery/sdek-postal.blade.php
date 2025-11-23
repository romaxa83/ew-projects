@php
    /**
     * @var $regions \Illuminate\Support\Collection|\AntistressStore\CdekSDK2\Entity\Responses\RegionsResponse[]
     * @var $cities \Illuminate\Database\Eloquent\Collection
     * @var $branches \Illuminate\Database\Eloquent\Collection
     * @var $storage \WezomCms\Orders\Models\OrderDeliveryInformation
     */
@endphp

<div class="form-group">
    {!! Form::label('deliveryInformation[region_ref]', __('cms-orders::admin.pickup.Region')) !!}
    {!! Form::select(
        'deliveryInformation[region_ref]',
        $regions,
        old('deliveryInformation.region_ref', $storage->region_ref),
        [
            'id' => 'sdek-region-select',
            'class' => 'js-select2',
        ]
    ) !!}
</div>
<div class="form-group">
    {!! Form::label('deliveryInformation[city_ref]', __('cms-orders::admin.pickup.Locality')) !!}
    {!! Form::select(
        'deliveryInformation[city_ref]',
        $cities,
        null,
        [
            'id' => 'np-city-select',
            'class' => 'js-ajax-select2',
            'data-url' => route('admin.sdek.search-cities', [ 'region' => $storage->region_ref ]),
            'data-minimum-input-length' => 3
        ]
    ) !!}
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
