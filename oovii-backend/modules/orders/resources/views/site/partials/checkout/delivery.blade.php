@php
/**
 * @var $deliveryId int|null
 * @var $deliveries \Illuminate\Database\Eloquent\Collection|\WezomCms\Orders\Models\Delivery[]
 * @var $selectedDelivery \WezomCms\Orders\Models\Delivery|null
 */

$selectedDelivery = $deliveryId ? $deliveries->where('id', $deliveryId)->first() : null;
@endphp
<div>
    <b>2. @lang('cms-orders::site.Доставка')</b>
    <x-form-group name="paymentId">
        @foreach($deliveries as $delivery)
            <div>
                <x-form-radio wire:model="deliveryId" value="{{ $delivery->id }}">{{ $delivery->name }}</x-form-radio>
            </div>
        @endforeach
    </x-form-group>
    <div>
        @if($selectedDelivery && $selectedDeliveryDriver = $selectedDelivery->makeDriver(compact('deliveryData')))
            {!! $selectedDeliveryDriver->renderFormInputs($deliveryData) !!}
        @endif
    </div>
</div>
