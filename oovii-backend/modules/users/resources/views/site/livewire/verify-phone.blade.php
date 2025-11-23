@php
/**
 * @var $userId int
 * @var $code int|null
@endphp
<div>
    <x-form-form wire:submit.prevent="submit">
        <x-form-group name="code">
            <x-form-input wire:model="code"
                          type="number"
                          autocomplete="off"
                          placeholder="@lang('cms-users::site.Введите код')"/>
        </x-form-group>

        <button type="submit">@lang('cms-users::site.Отправить')</button>
    </x-form-form>
</div>
