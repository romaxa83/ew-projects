@php
    /**
     * @var $orders \Illuminate\Database\Eloquent\Collection|\WezomCms\Orders\Models\Order[]|\Illuminate\Pagination\LengthAwarePaginator
     */
@endphp

@foreach($orders as $order)
    @php
        $countItems = $order->items->count();
    @endphp
    <br>
    <div x-data="{open:false}">
        <div>
            <strong>@lang('cms-orders::site.Заказ №:number', ['number' => $order->id])</strong>
            <div>
                {{ $order->created_at->format('d.m.Y') }}
                {{ $order->created_at->format('H:i') }}
            </div>
            <div>
                {{ $countItems . ' ' . trans_choice('cms-orders::site.товар|товара|товаров', $countItems) }}
            </div>
            <div>@money($order->whole_purchase_price, true)</div>
            <div>
                @if($order->status)
                    <div class="{{ $order->status->class }}">{{ $order->status->name }}</div>
                @endif
            </div>
        </div>
        <div class="user-order__more" x-on:click="open=!open">
            @lang('cms-orders::site.Полная информация о заказе')
            <span x-show="!open">
                &Vee;
            </span>
            <span x-show="open" x-cloak>
                &Wedge;
            </span>
        </div>
        <div x-show="open" x-cloak>
            <table>
                @if($order->client->name)
                    <tr>
                        <td>@lang('cms-orders::site.Получатель:')</td>
                        <td>{{ $order->client->full_name }}</td>
                    </tr>
                @endif
                @if($order->client->phone)
                    <tr>
                        <td>@lang('cms-orders::site.Номер телефона:')</td>
                        <td>{{  $order->client->phone }}</td>
                    </tr>
                @endif
                @if($order->client->email)
                    <tr>
                        <td>@lang('cms-orders::site.Эл. почта:')</td>
                        <td>{{ $order->client->email }}</td>
                    </tr>
                @endif
                @if($order->payment)
                    <tr>
                        <td>@lang('cms-orders::site.Способ оплаты:')</td>
                        <td>{{ $order->payment->name }}</td>
                    </tr>
                @endif
                @if($order->delivery)
                    <tr>
                        <td>@lang('cms-orders::site.Способ доставки:')</td>
                        <td>{{ $order->delivery->name }}</td>
                    </tr>
                @endif
                <tr>
                    <td>@lang('cms-orders::site.Адресс доставки:')</td>
                    <td>{{ $order->delivery_address }}</td>
                </tr>
                @if($order->deliveryInformation->ttn)
                    <tr>
                        <td>@lang('cms-orders::site.Номер ТТН')</td>
                        <td>{{ $order->deliveryInformation->ttn }}</td>
                    </tr>
                @endif
            </table>

            @foreach($order->items as $item)
                @if($item->product)
                    <a href="{{ $item->getFrontUrl() }}">
                        <img src="{{ $item->getImageUrl() }}" alt="{{ $item->name }}">
                    </a>
                @else
                    <img src="{{ $item->getImageUrl() }}" alt="{{ $item->name }}">
                @endif
                @if($item->product)
                    <a href="{{ $item->getFrontUrl() }}">{{ $item->name }}</a>
                @else
                    <span>{{ $item->name }}</span>
                @endif
                <div>{{ $item->quantity }} x @money($item->price, true)</div>
            @endforeach
            <div>
                @lang('cms-orders::site.Итого:')
                @money($order->whole_purchase_price, true)
            </div>
            <div>
                @lang('cms-orders::site.Статус заказа')
                <ul>
                    @foreach($order->statusHistory as $status)
                        <li title="@lang('cms-orders::site.Изменен :date', ['date' => $status->pivot->created_at->format('d.m.Y H:i:s')])">
                            {{ $status->name }}
                        </li>
                    @endforeach
                </ul>
            </div>
            <div>
                @if($order->payed)
                    <div title="{{ __('cms-orders::site.Оплачен') . ' ' . $order->payed_at->format('d.m.Y H:i:s') }}">@lang('cms-orders::site.Заказ оплачен')</div>
                @else
                    <div>@lang('cms-orders::site.Заказ не оплачен')</div>
                @endif
            </div>
        </div>
    </div>
    <hr>
@endforeach
