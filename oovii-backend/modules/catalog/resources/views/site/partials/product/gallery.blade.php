@php
    /**
     * @var $product \WezomCms\Catalog\Models\Product
     * @var $gallery \Illuminate\Support\Collection|\WezomCms\Catalog\Models\ProductImage[]|string[]
     */
@endphp
@if($gallery->isNotEmpty())
    <div class="js-import" data-slider="thumbs">
        <div class="swiper-container">
            <div class="swiper-wrapper">
                @foreach($gallery as $image)
                    @if($image instanceof \WezomCms\Catalog\Models\ProductImage)
                        <div class="swiper-slide">
                            <img class="swiper-lazy"
                                 src="{{ url('assets/images/empty.gif') }}"
                                 data-src="{{ $image->getImageUrl() }}"
                                 alt="{{ $image->altAttribute($product, $loop->iteration) }}"
                                 title="{{ $image->titleAttribute($product, $loop->iteration) }}">
                            <div class="swiper-lazy-preloader"></div>
                        </div>
                    @else
                        <div class="swiper-slide">
                            <img src="{{ \WezomCms\Core\Foundation\Helpers::getYoutubePoster($image, 'sddefault') }}"
                                 alt="{{ $image }}">
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
    </div>
@endif

<div class="{{ $gallery->isNotEmpty() ? 'js-import' : '' }}" data-slider="product" data-mfp="gallery">
    <div class="swiper-container">
        <div class="swiper-wrapper">
            @forelse($gallery as $image)
                @if($image instanceof \WezomCms\Catalog\Models\ProductImage)
                    <div class="swiper-slide">
                        <img class="swiper-lazy"
                             src="{{ url('assets/images/empty.gif') }}"
                             data-mfp-src="{{ $image->getImageUrl() }}"
                             data-src="{{ $image->getImageUrl() }}"
                             alt="{{ $image->altAttribute($product, $loop->iteration) }}"
                             title="{{ $image->titleAttribute($product, $loop->iteration) }}">
                        <div class="swiper-lazy-preloader"></div>
                    </div>
                @else
                    <div class="swiper-slide">
                        <div class="js-import" data-mfp-src="{{ $image }}"
                             style="background-image: url({{ \WezomCms\Core\Foundation\Helpers::getYoutubePoster($image, 'sddefault') }})">
                        </div>
                    </div>
                @endif
            @empty
                <div class="swiper-slide">
                    <img src="{{ $product->getImageUrl() }}" alt="{{ $product->name }}">
                </div>
            @endforelse
        </div>
    </div>
    @if($product->has_flag)
        @foreach($product->flags as $flag)
            <div class="{{ $flag['color'] }}">{{ $flag['text'] }}</div>
        @endforeach
    @endif
</div>
