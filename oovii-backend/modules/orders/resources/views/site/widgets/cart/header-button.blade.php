@php
    /**
     * @var $count int
     */
@endphp
<div>
    <div title="@lang('cms-orders::site.Открыть корзину')" data-cart-target='{"action":"open"}'>
        <span data-cart-count {{ $count === 0 ? 'hidden' : '' }}>{{ $count }}</span>
    </div>
    <div data-cart-hub-popup {{ $count > 0 ? 'hidden' : '' }}>
        <span>@lang('cms-orders::site.Ваша корзина пуста')!</span>
    </div>
</div>
