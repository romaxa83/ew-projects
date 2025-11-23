@component('mail::message')

# @lang('cms-orders::admin.email.New order')

### @lang('cms-orders::admin.email.Form data')
@component('mail::table')
| | |
|-|-|
| @lang('cms-orders::admin.email.Full name'):  | {{ $order->client->full_name }} |
@if($order->client->email)
| @lang('cms-orders::admin.email.Email'): | [{{ $order->client->email }}](mailto:{{ $order->client->email }}) |
@endif
| @lang('cms-orders::admin.email.Phone'): | [{{ $order->client->phone }}](tel:{{ preg_replace('/[^\d\+]/', '', $order->client->phone) }})|
| @lang('cms-orders::admin.email.Created at'): | {{ $order->created_at ? $order->created_at->format('d.m.Y H:i:s') : '----' }} |
@endcomponent

### @lang('cms-orders::admin.email.Order info')
@component('mail::table')
| | |
|-|-|
@foreach($order->items as $item)
|  |  |
| @lang('cms-orders::admin.email.Image'): | ![{{ $item->product->name }}]({{ $item->getImageUrl() }}) |
| @lang('cms-orders::admin.email.Product name'): | {{ $item->product->name  }} |
| @lang('cms-orders::admin.email.Amount'): | {{ str_replace(',', '.', $item->quantity) }} {{ $item->unit }} |
| @lang('cms-orders::admin.email.Price'): | @money($item->purchase_price, true) |
@endforeach
@endcomponent

@component('mail::panel')
@lang('cms-orders::admin.email.Order cost'): @money($order->whole_purchase_price, true) <br>
@lang('cms-orders::admin.email.Count products'):  {{ $order->items->count() }} шт
@endcomponent

### @lang('cms-orders::admin.email.Delivery and Payment')
@component('mail::table')
| | |
|-|-|
| @lang('cms-orders::admin.email.Delivery variant'): | {{ $order->delivery->name }} |
| @lang('cms-orders::admin.email.Delivery address'): | {{ $order->delivery_address }} |
@if($order->deliveryInformation->ttn)
| @lang('cms-orders::admin.email.TTN'): | {{ $order->deliveryInformation->ttn }} |
@endif
| @lang('cms-orders::admin.email.Payment method'): | {{ $payment }} |
@endcomponent

@component('mail::button', ['url' => $urlToAdmin, 'color' => 'green'])
    @lang('cms-orders::admin.email.Go to admin panel')
@endcomponent

@endcomponent
