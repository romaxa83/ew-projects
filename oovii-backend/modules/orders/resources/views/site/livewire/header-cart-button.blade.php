@php
    /**
     * @var $count int
     * @var $subTotal string
     * @var $items array
     */
@endphp
<div>
    <div @if($count) title="@lang('cms-orders::site.Открыть корзину')" @endif
         x-data="openModal('orders.cart-popup')"
         x-on:click="forceOpen"
    >
        @if($count)
            <div>{{ $count }}</div>
        @endif
        <div>
            <div>@lang('cms-orders::site.Корзина')</div>
            <div>@money($subTotal, true)</div>
        </div>
    </div>

    @if($count)
        <div>
            <div>
                <div>
                    @foreach($items as $item)
                        <div>
                            <div>
                                <a href="{{ $item['url'] }}">
                                    <img src="{{ $item['image'] }}" alt="{{ $item['name'] }}">
                                </a>
                            </div>
                            <div>
                                <div>
                                    <div>
                                        <a href="{{ $item['url'] }}">{{ $item['name'] }}</a>
                                    </div>
                                    <div>
                                        <button type="button"
                                                wire:click="deleteItemFromCart('{{ $item['row_id'] }}')"
                                                title="@lang('cms-orders::site.Удалить')"
                                        >
                                            &Cross;
                                        </button>
                                    </div>
                                </div>

                                <div>
                                    <div>
                                        <div>{{ $item['quantity'] }} @lang('cms-orders::site.шт')</div>
                                    </div>
                                    <div>
                                        <div>
                                            @if($item['crossed_out_sub_total'] > $item['sub_total'])
                                                <s>@money($item['crossed_out_sub_total'], true)</s>
                                            @endif
                                            <span>@money($item['sub_total'], true)</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <a href="{{ route('checkout') }}" >@lang('cms-orders::site.Оформить заказ')</a>

                <button x-data="openModal('orders.cart-popup')"
                        x-on:click="open"
                        x-on:mouseenter="open">@lang('cms-orders::site.Перейти в корзину')</button>
            </div>
        </div>
    @else
        <div>
            <div>@lang('cms-orders::site.Ваша корзина пуста')</div>
            <div>@lang('cms-orders::site.Добавляйте понравившиеся товары в корзину')</div>
        </div>
    @endif
</div>
