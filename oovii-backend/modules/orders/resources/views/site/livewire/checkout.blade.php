@php
    /**
     * @var $backUrl string
     * @var $cart array
     * @var $delivery \WezomCms\Orders\Models\Delivery|null
     * @var $hasUnavailableProducts bool
     */
@endphp
<div class="container">
    <h1> {{ SEO::getH1() }} </h1>
    <a href="{{ $backUrl }}">&larr;@lang('cms-orders::site.Вернуться к покупкам')</a>

    <div>
        <h2> @lang('cms-orders::site.Ваш заказ') </h2>
        <span>@lang('cms-orders::site.на сумму'): @money($cart['sub_total'], true)</span>
    </div>

    <div>
        @foreach($cart['items'] as $item)
            <a href="{{ $item['url'] }}" title="{{ $item['name'] }}">
                <img src="{{ $item['src'] }}" alt="{{ $item['name'] }}">
            </a>
        @endforeach
    </div>

    <button type="button"
            x-data="openModal('orders.cart-popup', {{ json_encode(['checkoutPage' => true]) }})"
            x-on:click="open"
            x-on:mouseenter="open">
        <span>@lang('cms-orders::site.Редактировать заказ')</span>
    </button>

    <x-form-form wire:submit.prevent="submit">
        <hr>
        @include('cms-orders::site.partials.checkout.contact-information')
        <hr>

        <div>
            <h2>@lang('cms-orders::site.Выбор способа доставки и оплаты')</h2>
            <br>
            @include('cms-orders::site.partials.checkout.delivery')
            <br>
            @include('cms-orders::site.partials.checkout.payment')
            <br>
            @include('cms-orders::site.partials.checkout.recipient')
        </div>

        <div>@lang('cms-orders::site.Состав заказа')</div>

        {{--<livewire:promo-codes.form />--}}

        <ul>
            <li>
                <span>{{ $cart['items_quantity'] }} {{ trans_choice('cms-orders::site.товар|товара|товаров', $cart['items_quantity']) }} @lang('cms-orders::site.на суму')</span>
                <span>@money($cart['sub_total'], true)</span>
            </li>

            @if($cart['discounted_by_promo_code'])
                <li>
                    <span>@lang('cms-orders::site.Скидка по промо-коду')</span>
                    <span>- @money($cart['discounted_by_promo_code'], true)</span>
                </li>
            @endif
        </ul>

        <div>@lang('cms-orders::site.Итого') @money($cart['total'], true)</div>

        <x-form-checkbox wire:model="dontCallBack" value="1">@lang('cms-orders::site.Не перезванивать мне, я уверен в корректности указанных данных')</x-form-checkbox>

        <button type="submit" @if($hasUnavailableProducts) disabled @endif>@lang('cms-orders::site.Подтвердить заказ')</button>

        @if($hasUnavailableProducts)
            <div>
                <div>@lang('cms-orders::site.В корзине есть товар, которого нет в наличии')</div>
                <button type="button"
                        x-data="openModal('orders.cart-popup', {{ json_encode(['checkoutPage' => true]) }})"
                        x-on:click="open"
                        x-on:mouseenter="open"
                >
                    <span>@lang('cms-orders::site.Редактировать заказ')</span>
                </button>
            </div>
        @endif

        @widget('ui:agree-text', ['actionText' => __('cms-orders::site.Подтверждая заказ,')])
    </x-form-form>
</div>
