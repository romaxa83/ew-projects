@php
    /**
     * @var $recipientIsMe bool
     * @var $showCommentField bool
     * @var $recipient array
     */
@endphp
<div>
    <div>@lang('cms-orders::site.Получатель')</div>

    <x-form-group name="recipientIsMe" :label="__('cms-orders::site.Я')">
        <x-form-radio wire:model="recipientIsMe" value="1"/>
    </x-form-group>
    <x-form-group name="recipientIsMe" :label="__('cms-orders::site.Другой человек')">
        <x-form-radio wire:model="recipientIsMe" value="0"/>
    </x-form-group>

    <div>
        @includeWhen(!$recipientIsMe, 'cms-orders::site.partials.checkout.other-recipient-fields')

        @if($showCommentField)
            <button wire:click.prevent="$set('showCommentField', false)">@lang('cms-orders::site.Скрыть комментарий')</button>
            <x-form-group name="recipient.comment" :label="__('cms-orders::site.checkout.Comment')">
                <x-form-textarea wire:model="recipient.comment"/>
            </x-form-group>
        @else
            <button type="button"
                    wire:click.prevent="$set('showCommentField', true)">@lang('cms-orders::site.Добавить комментарий к заказу')</button>
        @endif
    </div>
</div>
