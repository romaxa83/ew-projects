@php
    /**
     * @var $deliveryText string
     * @var $paymentText string
     */
@endphp
<div>
    @if($deliveryText)
        <div>@lang('cms-orders::site.Доставка')</div>
        <div>{!! nl2br($deliveryText) !!}</div>
    @endif
    @if($paymentText)
        <div>@lang('cms-orders::site.Оплата')</div>
        <div>{!! nl2br($paymentText) !!}</div>
    @endif
</div>
