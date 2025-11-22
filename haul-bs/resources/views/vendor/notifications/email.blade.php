@component('mail::message')

{{-- Greeting --}}
@if (! empty($greeting))
## {{ $greeting }}
@else
@if ($level === 'error')
## @lang('Whoops!')
@else
## @lang('Hello!')
@endif
@endif

{{-- Intro Lines --}}
@foreach ($introLines as $line)
{{ $line }}

@endforeach

{{-- Action Button --}}
@isset($actionText)
<?php
    switch ($level) {
        case 'success':
        case 'error':
            $color = $level;
            break;
        default:
            $color = 'primary';
    }
?>
@component('mail::button', ['url' => $actionUrl, 'color' => $color])
{{ $actionText }}
@endcomponent
@endisset

@if(isset($android) && isset($ios))
@component('mail::links', ['android' => $android, 'ios' => $ios])
@endcomponent
@endif

{{-- Outro Lines --}}
@foreach($outroLines as $line)
{{ $line }}

@endforeach

@component('mail::thank-you', ['companyName' => $companyName ?? null, 'companyContactString' => $companyContactString ?? null])
@endcomponent

@endcomponent
