@php
    /**
     * @var $title string
     * @var $result \Illuminate\Support\Collection|\WezomCms\Catalog\Models\Product[]
     */
@endphp
<div>
    @foreach($result as $product)
        <div class="swiper-slide">
            <a href="{{ $product->getFrontUrl() }}">{{ $product->name }}</a>
            <a href="{{ $product->getFrontUrl() }}">
                <img src="{{ $product->getImageUrl() }}" alt="{{ $product->name }}">
            </a>
            @if($product->sale && $product->discount_percentage)
                <div>
                    <div>{{ $product->discount_percentage  }}%</div>
                    <div>@lang('cms-catalog::site.Скидка')</div>
                </div>
            @endif
            @if($product->availableForPurchase())
                <div>
                    @money($product->cost, true)
                    @if($product->sale && $product->old_cost > $product->cost)
                        <span>
                            @money($product->old_cost)
                            <small>{{ money()->siteCurrencySymbol() }}</small>
                        </span>
                    @endif
                </div>
            @endif
            @if($product->availableForPurchase())
                <a href="{{ $product->getFrontUrl() }}">
                    {{ $product->in_cart ? __('cms-catalog::site.в корзине') : __('cms-catalog::site.купить') }}
                </a>
            @endif
        </div>
    @endforeach
</div>
