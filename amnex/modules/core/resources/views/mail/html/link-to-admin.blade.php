@php
    /**
     * @var $url string
     * @var $showClickingTrouble bool
     * @var $slot \Illuminate\Support\HtmlString
     */
@endphp

@component('mail::button', ['url' => $url])
{{ $slot->isNotEmpty() ? $slot : __('core::messages.mail.go_to_admin') }}
@endcomponent

@if($showClickingTrouble ?? true)
@component('mail::clicking-trouble', ['url' => $url, 'fields' => $fields])
@endcomponent
@endif
