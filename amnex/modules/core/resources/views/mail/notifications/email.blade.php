@component('mail::message')

@component('mail::heading', [
'title' => $greeting ?? ($level === 'error' ? __('core::messages.mail.whoops') : __('core::messages.mail.hello')),
'subTitle' => $subTitle ?? ''
])
@endcomponent

@if(!empty($introLines))
@component('mail::widget')
@foreach ($introLines as $line)
@if($line)
<p align="center">{{ $line }}</p>
@else
<p>&nbsp;</p>
@endif
@endforeach
@endcomponent
@endif

@if(isset($fields))
@component('mail::widget', ['fields' => $fields])
@component('mail::data', ['fields' => $fields])
@endcomponent
@endcomponent
@endif

{{-- Action Button --}}
@isset($actionText)
@component('mail::button', ['url' => $actionUrl])
{{ $actionText }}
@endcomponent
@endisset

@foreach ($outroLines as $line)
@if($line)
<p>{{ $line }}</p>
@else
<p>&nbsp;</p>
@endif
@endforeach

{{-- Subcopy --}}
@isset($actionText)
@component('mail::clicking-trouble', ['url' => $actionUrl, 'displayableActionUrl' => $displayableActionUrl])
@endcomponent
@endisset

@endcomponent
