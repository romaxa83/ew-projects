@component('mail::message')

# @lang('cms-orders::admin.email.Order has been paid')

### @lang('cms-orders::admin.email.Form data')
@component('mail::table')
| | |
|-|-|
| @lang('cms-orders::admin.email.Full name'):  | {{ $order->client->full_name }} |
@if($order->client->email)
| @lang('cms-orders::admin.email.Email'): | [{{ $order->client->email }}](mailto:{{ $order->client->email }}) |
@endif
| @lang('cms-orders::admin.email.Phone'): | [{{ $order->client->phone }}](tel:{{ preg_replace('/[^\d\+]/', '', $order->client->phone) }})|
| @lang('cms-orders::admin.email.Payment method'):  | {{ $order->payment->name }} |
| @lang('cms-orders::admin.email.Paid at'): | {{ $order->payed_at ? $order->payed_at->format('d.m.Y H:i:s') : '----' }} |
@endcomponent


@component('mail::button', ['url' => $urlToAdmin, 'color' => 'green'])
    @lang('cms-orders::admin.email.Go to admin panel')
@endcomponent

@endcomponent
