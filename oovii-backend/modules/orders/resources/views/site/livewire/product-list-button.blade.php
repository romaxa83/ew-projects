@php
    /**
     * @var $availableforPurchase bool
     * @var $inCart bool
     */
@endphp
<div>
    @if($availableForPurchase)
        <button class="{{ $inCart ? 'in-cart' : '' }}"
                wire:click="addToCartOrOpenModal"
                wire:key="enabled-cart-product-list-button-{{ $productId }}"
                title="{{ $inCart ? __('cms-orders::site.Открыть корзину') : __('cms-orders::site.Добавить в корзину') }}">
            &dollar;
        </button>
    @else
        <button disabled
                wire:key="disabled-cart-product-list-button-{{ $productId }}">
        </button>
    @endif
</div>
