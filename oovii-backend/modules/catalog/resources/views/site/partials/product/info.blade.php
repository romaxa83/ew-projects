@php
    /**
     * @var $product \WezomCms\Catalog\Models\Product
     * @var $variations \Illuminate\Support\Collection|\WezomCms\Catalog\Models\Product[]
     */
@endphp
<div>
    <h1>{{ SEO::getH1() }}</h1>
    <div>@lang('cms-catalog::site.Код товара'): {{ $product->id }}</div>
    @if($product->availableForPurchase())
        <div>@lang('cms-catalog::site.Есть в наличии')!</div>
    @else
        <div>@lang('cms-catalog::site.Нет в наличии')!</div>
    @endif
    <div>
        <div>@money($product->cost, true)</div>
        @if($product->old_cost)
            <span>@money($product->old_cost)</span>
            <small>{{ money()->siteCurrencySymbol() }}</small>
        @endif
    </div>
    @widget('product-labels', compact('product'))
    <div>
        @if($variations->isNotEmpty())
            <div>@lang('cms-catalog::site.Цвет'):</div>
            @foreach($variations as $variation)
                @if($product->id === $variation->id)
                    <span title="{{ $variation->color->name }}" style="width: 20px; height:20px; display:block; background-color: {{ $variation->color->color }}"></span>
                @else
                    <a href="{{ $variation->getFrontUrl() }}" title="{{ $variation->color->name }}"
                       style="width: 20px; height:20px; display:block; background-color: {{ $variation->color->color }}"></a>
                @endif
            @endforeach
        @endif
    </div>
    <div>
        {{--<livewire:comparison.product-button :product="$product" :productPage="true" />--}}

        {{--<livewire:orders.product-button :product="$product" :key="uniqid()"/>--}}

        {{--<livewire:favorites.product-button :favorable="$product" />--}}

        {{--<livewire:buy-one-click.product-button :product="$product" />--}}
    </div>
</div>
