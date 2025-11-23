@php
    /**
     * @var $product WezomCms\Catalog\Models\Product
     * @var $showFavoritesCheck bool|null
     */

$showFavoritesCheck = $showFavoritesCheck ?? false;
@endphp
<div>
    @if($showFavoritesCheck)
        @widget('favorites:product-list-button', ['product' => $product, 'showFavoritesCheck' => $showFavoritesCheck])
    @endif
    <a href="{{ $product->getFrontUrl() }}">
        <img src="{{ $product->getImageUrl() }}" alt="{{ $product->image_alt }}" title="{{ $product->image_title }}">
    </a>
    @if($product->has_flag)
        @foreach($product->flags as $flag)
            <div class="{{ $flag['color'] }}">{{ $flag['text'] }}</div>
        @endforeach
    @endif

    <div>
        @if($product->sale && $product->expires_at > now())
            @lang('cms-catalog::site.До конца акции')
            {{ $product->expires_at->format('Y.m.d H:i:s') }}
        @endif
        @foreach($product->variations as $variant)
            <a href="{{ $variant->getFrontUrl() }}" class="{{ $product->id === $variant->id ? 'is-current' : '' }}"
               title="{{ $variant->color->name }}">
                <span style="width: 20px; height:20px; display:block; background-color: {{ $variant->color->color }}"></span>
            </a>
        @endforeach
    </div>
    <div>
        <a href="{{ $product->getFrontUrl() }}">{{ $product->name }}</a>

        @if($product->availableForPurchase())
            <div>@money($product->cost, true)</div>

            @if($product->sale && $product->old_cost > $product->cost)
                <div>
                    <span>@money($product->old_cost)</span>
                    <small>{{ money()->siteCurrencySymbol() }}</small>
                </div>
            @endif
        @endif

        <div>
            {{--<livewire:comparison.product-button :product="$product" />--}}

            {{--<livewire:orders.product-list-button :product="$product" :key="uniqid($product->id)" />--}}

            @if(!$showFavoritesCheck)
                @widget('favorites:product-list-button', compact('product'))
            @endif
        </div>
    </div>
</div>
