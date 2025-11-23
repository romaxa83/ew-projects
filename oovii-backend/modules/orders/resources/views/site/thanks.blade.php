@extends('cms-orders::site.layouts.checkout')

@php
    /**
     * @var $order \WezomCms\Orders\Models\Order
     */
@endphp

@section('content')
    <div class="container">
        <div>
            <div>
                <h1>{{ SEO::getH1() }}</h1>
                <p>
                    @lang('cms-orders::site.Вы успешно оформили заказ на номер телефона:') {{ $order->client->phone }}
                </p>
                @auth
                    <p>
                        @lang('cms-orders::site.Статус заказа вы можете отслеживать в') <a href="{{ route('cabinet.orders') }}">@lang('cms-orders::site.личном кабинете')</a>
                    </p>
                @endauth

                <div style="background-color: white">
                    @lang('cms-orders::site.Номер заказа') <span style="color: orange">{{ $order->id }}</span>

                    @include('cms-orders::site.partials.ordered-items-list', ['items' => $order->items])
                </div>
            </div>
            <div>
                <div style="background-color: white">
                    @lang('cms-orders::site.Детали заказа')
                    <div style="background-color: #ccc">
                        <p>@lang('cms-orders::site.Доставка'): {{ $order->delivery->name }} <strong>{{ $order->delivery_address }}</strong></p>
                        @if($payment = $order->payment)
                            <p>@lang('cms-orders::site.Способ оплаты'): <strong>{{ $payment->name }}</strong></p>
                        @endif
                        <hr>
                        <p>@lang('cms-orders::site.Получатель'): <strong>{{ $order->recipient->recipient_is_me ? $order->client->full_name : $order->recipient->full_name }}</strong></p>
                    </div>
                    <div>
                        <div>@lang('cms-orders::site.Итого')</div>
                        <div>
                            <div>
                                @if($order->discount > 0)
                                    <div>
                                        <span>@money($order->whole_purchase_price + $order->discount, true)</span>
                                    </div>
                                @endif
                                <div>@money($order->whole_purchase_price, true)</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @widget('catalog:viewed')
@endsection
