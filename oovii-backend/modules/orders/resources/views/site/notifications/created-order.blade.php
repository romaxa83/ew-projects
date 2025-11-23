@php
    /**
     * @var $order \WezomCms\Orders\Models\Order
     * @var $delivery string
     * @var $payment string
     * @var $urlToCabinet null|string
     */
@endphp
@component('mail::message')

# @lang('cms-orders::site.email.Thank you for your order!') №{{ $order->id }}

### @lang('cms-orders::site.email.Contact data')
@component('mail::table')
| | |
|-|-|
| @lang('cms-orders::site.email.Full name'):  | {{ $order->client->full_name }} |
@if($order->client->email)
| @lang('cms-orders::site.email.Email'): | [{{ $order->client->email }}](mailto:{{ $order->client->email }}) |
@endif
| @lang('cms-orders::site.email.Phone'): | [{{ $order->client->phone }}](tel:{{ preg_replace('/[^\d\+]/', '', $order->client->phone) }})|
@endcomponent

@lang('cms-orders::site.email.Comment')
@component('mail::panel')
    {{ str_replace(["\r\n", "\n", "\r"], ' ', $order->recipient->comment) }}
@endcomponent

### @lang('cms-orders::site.email.Order info')
@component('mail::table')
| | |
|-|-|
@foreach($order->items as $item)
|  |  |
| @lang('cms-orders::site.email.Image'): | ![{{ $item->product->name }}]({{ $item->getImageUrl() }}) |
| @lang('cms-orders::site.email.Product name'): | {{ $item->product->name  }} |
| @lang('cms-orders::site.email.Amount'): | {{ str_replace(',', '.', $item->quantity) }} {{ $item->unit }} |
| @lang('cms-orders::site.email.Price'): |  @money($item->price, true) |
| @lang('cms-orders::site.email.Purchase price'): | @money($item->purchase_price, true) |
@endforeach
@endcomponent

@component('mail::panel')
    @lang('cms-orders::site.email.Order cost'):  @money($order->whole_purchase_price, true)<br>
    @lang('cms-orders::site.email.Count products'):  {{ $order->items->sum('quantity') }} шт
@endcomponent

### @lang('cms-orders::site.email.Delivery and Payment')
@component('mail::table')
| | |
|-|-|
| @lang('cms-orders::site.email.Delivery variant'): | {{ $order->delivery->name }} |
| @lang('cms-orders::site.email.Delivery address'): | {{ $order->delivery_address }} |
@if($order->deliveryInformation->ttn)
| @lang('cms-orders::site.email.TTN'): | {{ $order->deliveryInformation->ttn }} |
@endif
| @lang('cms-orders::site.email.Payment method'): | {{ $payment }} |
@endcomponent

@if($urlToCabinet)
@component('mail::button', ['url' => $urlToCabinet, 'color' => 'green'])
    @lang('cms-orders::site.email.Go to your personal cabinet')
@endcomponent
@endif

@endcomponent
