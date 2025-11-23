@php
    /**
     * @var $content array
     * @var $checkoutPage bool
     * @var $subTotal float|int
     * @var $items array
     */
@endphp
<div class="modal-content">
    <div @if(empty($items)) class="cart-empty" @endif>
        <div>@lang('cms-orders::site.Корзина')</div>

        @if(!empty($items))
            <div>
                <div>
                    @include('cms-orders::site.partials.cart-items')
                </div>
                <div>
                    <div>
                        <button x-on:click="close" >@lang('cms-orders::site.Продолжить покупки')</button>
                    </div>

                    <div>
                        <div>
                            @if($crossedOutSubTotal > $subTotal)
                                <s>@money($crossedOutSubTotal, true)</s>
                            @endif
                            <div>@money($subTotal, true)</div>
                        </div>
                        <div>
                            @if($checkoutPage)
                                <button x-on:click="close">@lang('cms-orders::site.Оформить заказ')</button>
                            @else
                                <a href="{{ route('checkout') }}">@lang('cms-orders::site.Оформить заказ')</a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div>@lang('cms-orders::site.Корзина пуста')</div>
            <div>@lang('cms-orders::site.Добавляйте товары в корзину и покупайте их быстро и удобно')</div>

            @widget('catalog:viewed')
        @endif
    </div>
</div>
