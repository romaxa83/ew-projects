@php
    /**
     * @var $reviews \Illuminate\Database\Eloquent\Collection|\WezomCms\ProductReviews\Models\ProductReview[]
     */
@endphp
<div>
    <div>@lang('cms-product-reviews::site.Последние отзывы о товарах')</div>
    <div>
        @foreach($reviews as $review)
            <div>
                <div>
                    <a href="{{ $review->product->getFrontUrl() . '#reviews' }}">
                        <img src="{{ asset('images/empty.gif') }}"
                             data-src="{{ $review->product->getImageUrl() }}"
                             alt="{{ $review->product->name }}">
                    </a>
                    <div>
                        <a href="{{ $review->product->getFrontUrl() . '#reviews' }}">
                            {{ $review->product->name }}
                        </a>
                        <div>
                            <div>
                                <span>{{ $review->rating }}/5</span>
                                <span>
                                    @foreach($ratings as $rating)
                                        @if($review->rating >= $rating)
                                            &starf;
                                        @else
                                            &star;
                                        @endif
                                    @endforeach
                                </span>
                            </div>
                            <div>
                                <span>{{ $review->name }}</span>
                                <time datetime="{{ $review->created_at }}">{{ $review->formatted_date }}</time>
                                @if($review->already_bought)
                                    <span>@lang('cms-product-reviews::site.Уже купил')</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                <div>{{ str_limit(strip_tags($review->text), 250, '...') }}</div>
                <div>
                    &Wedge;
                    <span>{{ $review->likes }}</span>
                </div>
                <div>
                    &Vee;
                    <span>{{ $review->dislikes }}</span>
                </div>
            </div>
        @endforeach
    </div>
</div>
