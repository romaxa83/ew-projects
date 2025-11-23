@php
    /**
     * @var $product \WezomCms\Catalog\Models\Product
     * @var $specifications Illuminate\Support\Collection
     * @var $reviewsEnabled bool
     * @var $countReviews int
     */
@endphp
<div class="wstabs-btn is-active" data-wstabs-ns="details" data-wstabs-button="0">
    @lang('cms-catalog::site.Характеристики')
</div>
<div class="wstabs-btn" data-wstabs-ns="details" data-wstabs-button="1">
    @lang('cms-catalog::site.Описание')
</div>
@if($reviewsEnabled)
    <div class="wstabs-btn" {{ $reviewsEnabled ? 'is-enabled' : null }} data-wstabs-ns="details" data-wstabs-button="2">
        @lang('cms-catalog::site.Отзывы :count', ['count' => '(' . $countReviews . ')'])
    </div>
@endif
<div class="wstabs-block is-active" data-wstabs-ns="details" data-wstabs-block="0">
    @if(count($specifications))
        <ul>
            @foreach($specifications as $specification)
                <li>
                    <span>{{ $specification['name'] }}</span>
                    <span>{{ $specification['value'] }}</span>
                </li>
            @endforeach
        </ul>
    @endif
    <div>
        {!! nl2br(e(settings('product.consumer.consumer'))) !!}
    </div>
</div>
<div class="wstabs-block" data-wstabs-ns="details" data-wstabs-block="1">
    <div class="wysiwyg js-import" data-wrap-media data-draggable-table>
        @if($product->text)
            {!! $product->text !!}
        @else
            @lang('cms-catalog::site.Описание отсутствует')
        @endif
    </div>
</div>
@if($reviewsEnabled)
    <div class="wstabs-block" data-wstabs-ns="details" data-wstabs-block="2">
        <livewire:product-reviews.reviews :product="$product" />
    </div>
@endif
