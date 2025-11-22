@component('mail::message')
    {{ $line1 }}
    @component('mail::button', ['url' => $action1['url'], 'color' => 'primary'])
        {{ $action1['text'] }}
    @endcomponent
    {{ $line2 }}
    @component('mail::button', ['url' => $action2['url'], 'color' => 'primary'])
        {{ $action2['text'] }}
    @endcomponent
@endcomponent
