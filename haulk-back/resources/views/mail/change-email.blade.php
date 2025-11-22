@component('mail::message')

# Hello,

@isset($line1)
{{ $line1 }}
@endisset

@isset($line2)
{{ $line2 }}
@endisset

@isset($action1)
@component('mail::button', ['url' => $action1['url'], 'color' => 'primary'])
{{ $action1['text'] }}
@endcomponent
@endisset

@isset($line3)
{{ $line3 }}
@endisset

@isset($action2)
@component('mail::button', ['url' => $action2['url'], 'color' => 'primary'])
{{ $action2['text'] }}
@endcomponent
@endisset

@endcomponent