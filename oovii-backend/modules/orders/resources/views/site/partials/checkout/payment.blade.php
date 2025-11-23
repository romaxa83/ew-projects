@php
    /**
     * @var $payments \WezomCms\Orders\Models\Payment[]|\Illuminate\Database\Eloquent\Collection
     * @var $paymentId int|null
     * @var $selectedPayment \WezomCms\Orders\Models\Payment|null
     * @var $groupedDeliveryPayments \Illuminate\Database\Eloquent\Collection
     */
    $selectedPayment = $paymentId ? $payments->firstWhere('id', $paymentId) : null;

    $selectedPaymentDriver = $selectedPayment ? $selectedPayment->makeDriver() : null;
@endphp
<div>
    <b>3. @lang('cms-orders::site.Оплата')</b>
    <x-form-group name="paymentId">
        @foreach($payments as $payment)
            <div>
                <x-form-radio wire:model="paymentId"
                              value="{{ $payment->id }}"
                              :disabled="!$deliveryId || !$groupedDeliveryPayments->get($payment->id)->contains($deliveryId)">{{ $payment->name }}</x-form-radio>
            </div>
        @endforeach
    </x-form-group>
</div>
