@component('mail::layout')
{{-- Header --}}
@slot('header')
@component('mail::header', ['url' => config('app.url')])
{{ config('app.name') }}
@endcomponent
@endslot

{{-- Body --}}
{{ $slot }}

@isset($subcopy)
@component('mail::clicking-trouble', ['url' => null])
{{ $subcopy }}
@endcomponent
@endisset

@endcomponent
