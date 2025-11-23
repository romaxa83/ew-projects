@php
    /**
     * @var $ratings array
     * @var $reviews \Illuminate\Database\Eloquent\Collection|\WezomCms\ProductReviews\Models\ProductReview[][]
     * @var $parentId int|null
     */

    $parentId = $parentId ?? null;
@endphp
@foreach($reviews[$parentId] ?? [] as $review)
    <div>
        @if($parentId)
            <div>
                &hookrightarrow;
            </div>
        @endif
        <div id="review{{ $review->id }}">
            <div>
                @if(!$review->admin_answer && $parentId === null)
                    <div>
                        <span>{{ $review->rating }}/5</span>
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
                @endif

                <div>
                    @if($review->admin_answer && !$review->name)
                        <div>@lang('cms-product-reviews::site.Администрация сайта')</div>
                    @else
                        <div>{{ $review->name }}</div>
                    @endif

                    <time
                        datetime="{{ $review->created_at }}">{{ $review->formatted_date }}</time>

                    @if($parentId === null && $review->already_bought)
                        <div>@lang('cms-product-reviews::site.Уже купил')</div>
                    @endif
                </div>
            </div>

            <div>
                @php
                    $limitNumber = 500;
                    $text = nl2br(e($review->text));
                    $additionalText = mb_substr($review->text, mb_strlen(\Illuminate\Support\Str::limit($review->text, $limitNumber, '')));
                @endphp
                <div x-data="{'open':false,showBtn:{{ json_encode(mb_strlen($additionalText) > 0) }}}"
                     x-on:expand-review.window="open=$event.detail==={{ $review->id }}"
                >
                    <div>
                        <span :class="{'expand-txt__ellipsis':!open && showBtn}">{!! \Illuminate\Support\Str::limit($review->text, $limitNumber, '') !!}</span>
                        @if($additionalText)
                            <span x-show="open" x-cloak>{!! $additionalText !!}</span>
                            <button type="button"
                                    x-on:click="open=!open"
                                    x-show="!open"
                            >
                                @lang('cms-product-reviews::site.Читать далее')
                            </button>
                        @endif
                    </div>
                </div>

                <div>
                    <button
                        type="button"
                            x-data="openModal('product-reviews.form', {'id': {{ $product->id }}, 'answerTo': {{ $review->id }}})"
                            x-on:click="open"
                            x-on:mouseenter="open"
                    >
                        ...
                        <span>@lang('cms-product-reviews::site.Ответить')</span>
                    </button>

                    <div>
                        <div>@lang('cms-product-reviews::site.Полезен ли отзыв?')</div>
                        <ul>
                            <li>
                                <button wire:click="like({{ $review->id }})"
                                        @if($this->canVote($review, 'likes') === false) disabled @endif
                                >
                                    &Wedge; @lang('cms-product-reviews::site.Да')
                                    <span>{{ $review->likes }}</span>
                                </button>
                            </li>
                            <li>
                                <button wire:click="dislike({{ $review->id }})"
                                        @if($this->canVote($review, 'dislikes') === false) disabled @endif
                                >
                                    &Vee; @lang('cms-product-reviews::site.Нет')
                                    <span>{{ $review->dislikes }}</span>
                                </button>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        @if(isset($reviews[$review->id]))
            @include('cms-product-reviews::site.partials.reviews-list', ['parentId' => $review->id])
        @endif
    </div>
@endforeach
