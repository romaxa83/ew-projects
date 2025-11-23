@php
    /**
     * @var $title null|string
     * @var \Illuminate\Database\Eloquent\Collection|\WezomCms\Catalog\Models\Product
     */
@endphp
<div class="container">
    @if($title ?? false)
        <div>{{ $title }}</div>
    @endif
    <div class="js-import" data-slider="carousel">
        <div class="swiper-container">
            <div class="swiper-wrapper">
                @foreach($result as $product)
                    @include('cms-catalog::site.partials.product-list-item')
                @endforeach
            </div>
        </div>
    </div>
</div>
