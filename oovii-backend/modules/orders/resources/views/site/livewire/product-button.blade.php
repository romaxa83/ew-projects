@php
    /**
     * @var $product \WezomCms\Catalog\Models\Product
     * @var $inCart bool
     * @var $buttonClass string|null
     * @var $availableForPurchase bool
     */
@endphp
<div>
    @if($availableForPurchase)
        <button class="{{ $inCart ? 'in-cart' : '' }} {{ $buttonClass }}"
                wire:click="addToCart"
                wire:loading.attr="disabled"
                title="{{ $inCart ? __('cms-orders::site.Открыть корзину') : __('cms-orders::site.Добавить в корзину') }}">
            <span>{{ $inCart ? __('cms-orders::site.В корзине') : __('cms-orders::site.Купить') }}</span>
            &dollar;
        </button>
    @else
        <button class="button button_three button_fs_xl {{ $buttonClass }}"
                disabled="disabled">@lang('cms-orders::site.Нет в наличии')</button>
    @endif
</div>
