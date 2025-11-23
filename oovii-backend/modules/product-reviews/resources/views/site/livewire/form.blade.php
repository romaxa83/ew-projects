@php
    /**
     * @var $ratings array
     * @var $ratingText string
     * @var $replyToReview \WezomCms\ProductReviews\Models\ProductReview|null
     * @var $commentRulesPage \WezomCms\Pages\Models\Page|null
     */
@endphp
<div class="modal-content">
    @if($replyToReview)
        <div>@lang('cms-product-reviews::site.Ответить на отзыв'):
            @if($replyToReview->admin_answer && !$replyToReview->name)
                @lang('cms-product-reviews::site.Менеджер магазина')
            @else
                <span>{{ $replyToReview->name }}</span>
                @endif
                &lpar;{{ $replyToReview->formatted_date }}&rpar;
        </div>
    @else
        <div>@lang('cms-product-reviews::site.Написать отзыв')</div>
    @endif

    <x-form-form wire:submit.prevent="submit">
        @if(!$replyToReview)
            <x-form-group name="rating" :label="__('cms-product-reviews::site.Моя оценка')">
                @foreach($ratings as $rating => $text)
                    <x-form-radio wire:model="rating">&star;</x-form-radio>
                @endforeach
            </x-form-group>

            <div>{{ $ratingText }}</div>

            <ul>
                @foreach($ratings as $text)
                    <li>{{ $text }}</li>
                @endforeach
            </ul>
        @endif

        <x-form-group name="text" :label="__('cms-product-reviews::site.Комментарий')">
            <x-form-textarea wire:model="text"/>
        </x-form-group>

        <x-form-group name="name" :label="__('cms-product-reviews::site.Имя')">
            <x-form-input wire:model="name"/>
        </x-form-group>

        <x-form-group name="email" :label="__('cms-product-reviews::site.E-mail')">
            <x-form-email wire:model="email"/>
        </x-form-group>

        @if($commentRulesPage)
            <div>
                @lang('cms-product-reviews::site.Чтобы ваш отзыв либо комментарий прошел модерацию и был опубликован, ознакомьтесь, пожалуйста, с')
                <a href="{{ $commentRulesPage->getFrontUrl() }}"
                   target="_blank"
                   rel="noopener">@lang('cms-product-reviews::site.правилами комментирования')</a>
            </div>
        @endif

        <button type="submit">@lang('cms-product-reviews::site.Оставить отзыв')</button>

        @widget('ui:agree-text')
    </x-form-form>
</div>
