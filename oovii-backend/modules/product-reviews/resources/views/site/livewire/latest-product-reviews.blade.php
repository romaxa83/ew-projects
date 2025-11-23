@php
    /**
     * @var $reviews \Illuminate\Database\Eloquent\Collection|\WezomCms\ProductReviews\Models\ProductReview[]
     * @var $product \WezomCms\Catalog\Models\Product
     * @var $countReviews int
     * @var $hasMore bool
     */
@endphp
<div>
    <div>
        <div>
            @lang('cms-product-reviews::site.Отзывы')<sup>{{ $countReviews }}</sup>
        </div>
        <button
            x-data="openModal('product-reviews.form', {'id': {{ $product->id }}})"
                x-on:click="open"
                x-on:mouseenter="open">
            @lang('cms-product-reviews::site.Написать отзыв')
        </button>
    </div>
    <div>
        @foreach($reviews as $review)
            <div>
                <div>
                    <div>
                        <span>{{ $review->rating }}/5</span>
                        <span>
                            @for($i = 1; $i <= 5; $i++)
                                +
                            @endfor
                        </span>
                    </div>
                    <div>
                        <div>{{ $review->name }}</div>
                        <time datetime="{{ $review->created_at }}">{{ $review->formatted_date }}</time>
                        @if($review->already_bought)
                            <div>@lang('cms-product-reviews::site.Уже купил')</div>
                        @endif
                    </div>
                </div>
                <div>
                    <div>
                        @php
                            $limitNumber = 200;
                            $text = nl2br(e($review->text));
                            $additionalText = mb_substr($review->text, mb_strlen(\Illuminate\Support\Str::limit($review->text, $limitNumber, '')));
                        @endphp
                        <span>{!! \Illuminate\Support\Str::limit($review->text, $limitNumber, '') !!}</span>
                        @if($additionalText)
                            <button type="button"
                                    data-tab="reviews"
                                    data-hash="reviews"
                                    data-scroll-review="#review{{$review->id}}"
                                    x-data
                                    x-on:click="$dispatch('expand-review', {{ $review->id }})"
                            >
                                @lang('cms-product-reviews::site.Читать далее')
                            </button>
                        @endif
                    </div>
                    <ul>
                        <li>
                            <button type="button" wire:click="like({{ $review->id }})"
                                    @if($this->canVote($review, 'likes') === false) disabled @endif>
                                &Wedge;
                                <span>{{ $review->likes }}</span>
                            </button>
                        </li>
                        <li>
                            <button type="button"
                                    wire:click="dislike({{ $review->id }})"
                                    @if($this->canVote($review, 'dislikes') === false) disabled @endif>
                                &Vee;
                                <span>{{ $review->dislikes }}</span>
                            </button>
                        </li>
                    </ul>
                </div>
            </div>
        @endforeach
    </div>

    @if($hasMore)
        <button data-tab="reviews"
                data-hash="reviews"
                type="button">
            <span>@lang('cms-product-reviews::site.Все отзывы')</span>
            >>>
        </button>
    @endif
</div>
