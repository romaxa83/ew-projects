@php
    /**
     * @var $order \Illuminate\Database\Eloquent\Collection|\WezomCms\Orders\Models\Order
     */
@endphp
<ul>
    @foreach($order->items as $item)
        <li>
            <div>
                <div>
                    @if($item->product)
                        <a href="{{ $item->getFrontUrl() }}">
                            <img class="lazy"
                                 src="{{ asset('images/empty.gif') }}"
                                 data-src="{{ $item->getImageUrl() }}" alt="{{ $item->name }}">
                        </a>
                    @else
                        <img class="lazy"
                             src="{{ asset('images/empty.gif') }}"
                             data-src="{{ $item->getImageUrl() }}" alt="{{ $item->name }}">
                    @endif
                </div>
                <div>{{ $item->quantity_with_unit }}</div>
                <div>@money($item->whole_price, true)</div>
            </div>
        </li>
    @endforeach
</ul>
