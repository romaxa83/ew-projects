@php
    /**
    * @var $ratings array
    * @var $reviews \Illuminate\Database\Eloquent\Collection|\WezomCms\ProductReviews\Models\ProductReview[]
    * @var $product \WezomCms\Catalog\Models\Product
    * @var $hasMore bool
    * @var $sortVariants array
    */
    $productRating = round($product->rating);
@endphp
<div>
    <div>
        @lang('cms-product-reviews::site.Отзывы покупателей о')
        <span>{{ $product->name }}</span>
    </div>
    <div>
        <div>
            <div>
                <span>{{ $product->rating }} @lang('cms-product-reviews::site.из') 5</span>
                <span>
                    @foreach($ratings as $rating)
                        @if($productRating >= $rating)
                            &starf;
                        @else
                            &star;
                        @endif
                    @endforeach
                </span>
            </div>
            <div>
                <x-form-select wire:model="sort" :options="$sortVariants" />

                <button
                    x-data="openModal('product-reviews.form', {'id': {{ $product->id }}})"
                        x-on:click="open"
                        x-on:mouseenter="open"
                        @if(request()->has('open-review-modal'))
                            x-init="openOnLoad"
                        @endif
                >@lang('cms-product-reviews::site.Написать отзыв')</button>
            </div>
        </div>

        <div>
            @include('cms-product-reviews::site.partials.reviews-list')
        </div>

        @if($hasMore)
            <div>
                <button type="button"
                        wire:loading.attr="disabled"
                        wire:loading.class="is-loading"
                        wire:click="loadMore">
                    <span>@lang('cms-product-reviews::site.Загрузить еще')</span>
                    &orarr;
                </button>
            </div>
        @endif
    </div>
</div>
