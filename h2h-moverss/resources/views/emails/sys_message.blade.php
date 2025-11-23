@component('mail::message')
# System message:
{!! nl2br($data['msg']) !!}


@if(isset($data['button']))
@component('mail::button', ['url' => $data['button']['url']])
{{ $data['button']['title'] }}
@endcomponent
@endif


Thanks,<br>
{{ email_app_name() }}
@endcomponent
