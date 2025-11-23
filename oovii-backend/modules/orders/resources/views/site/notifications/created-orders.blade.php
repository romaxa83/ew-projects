@php
    /**
     * @var $orders \Illuminate\Support\Collection|\WezomCms\Orders\Models\Order[]
     * @var $delivery string
     * @var $payment string
     * @var $urlToCabinet null|string
     */
$firstOrder = $orders->first();
@endphp
@component('mail::message')

# @lang('cms-orders::site.email.Thank you for your orders!') №{{ $orders->implode('id', ', ') }}

### @lang('cms-orders::site.email.Contact data')
@component('mail::table')
| | |
|-|-|
| @lang('cms-orders::site.email.Full name'):  | {{ $firstOrder->client->full_name }} |
@if($firstOrder->client->email)
| @lang('cms-orders::site.email.Email'): | [{{ $firstOrder->client->email }}](mailto:{{ $firstOrder->client->email }}) |
@endif
| @lang('cms-orders::site.email.Phone'): | [{{ $firstOrder->client->phone }}](tel:{{ preg_replace('/[^\d\+]/', '', $firstOrder->client->phone) }})|
@endcomponent

@lang('cms-orders::site.email.Comment')
@component('mail::panel')
    {{ str_replace(["\r\n", "\n", "\r"], ' ', $firstOrder->recipient->comment) }}
@endcomponent

@foreach($orders as $order)
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
@endforeach

### @lang('cms-orders::site.email.Delivery and Payment')
@component('mail::table')
| | |
|-|-|
| @lang('cms-orders::site.email.Delivery variant'): | {{ $firstOrder->delivery->name }} |
| @lang('cms-orders::site.email.Delivery address'): | {{ $firstOrder->delivery_address }} |
| @lang('cms-orders::site.email.Payment method'): | {{ $firstOrder->payment->name ?? '' }} |
@endcomponent

@if($urlToCabinet)
@component('mail::button', ['url' => $urlToCabinet, 'color' => 'green'])
    @lang('cms-orders::site.email.Go to your personal cabinet')
@endcomponent
@endif

@endcomponent
