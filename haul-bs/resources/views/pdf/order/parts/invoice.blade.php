<!DOCTYPE html><!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Invoice</title>

    <style>
        body > * {
            font-family: Arial, sans-serif;
            font-weight: 400;
            font-size: 8px;
            color: #171D2D;
        }

        table {
            width: 100%;
        }

        table, td {
            border: 1px solid #E8E8E8;
            vertical-align: top;
            border-collapse: collapse;
        }


        .logo {
            text-align: center;
            margin-bottom: 19px;
        }


        .title {
            font-size: 13px;
            font-weight: 700;
            line-height: 15px;
            color: #00B67B;
        }

        .subtitle {
            font-size: 8px;
            font-weight: 700;
            line-height: 9px;
            color: #00B67B;
        }

        .pagenum:before {
            content: counter(page);
        }

        /*.pagenum:before {*/
        /*    content: "Page " counter(page) " from " counter(pages);*/
        /*}*/
    </style>
</head>
<body style="position: relative">
<div class="logo">
    @if($logo)
        <img style="width: 140px; height: 45px;"
\             src="{{ $logo }}"
             alt="">
    @endif
</div>

<div class="title">
    {{ $settings['company_name'] }}
</div>
<br>
<div>
    {{$settings['address'] }}
</div>
<div>
    {{ $settings['city'] ?? '' }} {{ $state }} {{ $settings['zip'] ?? '' }}
</div>
<div>
    <b>Email:</b> {{ $settings['email'] }}
</div>
@if ($settings['phone'])
    <div>
        <b>Phone:</b> {{ $settings['phone'] }}
    </div>
@endif
<br>

<table>
    <tbody>
    <tr>
        <td style="width: 50%; padding: 8px;">
            <div>
                <b>Invoice #:</b> {{ $order['number']}}
            </div>
            <div>
                <b>Invoice date:</b> {{ \Illuminate\Support\Carbon::now()->format(App\Foundations\Enums\DateTimeEnum::DateForDocs->value) }}
            </div>
        </td>
        <td style="width: 50%; padding: 8px;">
            <div>
                <div class="subtitle">BILL TO</div>
                <div>
                    <b>
                        {{
                        $order['billing_address']?->company
                        ? $order['customer']['name'] .', '. $order['billing_address']?->company
                        : $order['customer']['name']
                        }}
                    </b>
                </div>
                <div>
                    @if($order['customer']['phone'])
                        <b>Phone:</b> {{ $order['customer']['phone'] }}
                    @endif
                </div>
                <div>
                    @if($order['customer']['email'])
                        <b>Email:</b> {{ $order['customer']['email'] }}
                    @endif
                </div>
                <div>
                    <b>Billing address:</b> {{ $order['billing_address']?->getFullAddress() }}
                </div>
                <br>
                <div class="subtitle">Delivery info:</div>
                <div>
                    <b>{{ $order['delivery_address']?->first_name. ' '.$order['delivery_address']?->last_name}}</b>
                </div>
                <div>
                    @if($order['is_pickup'])
                        <b>Address:</b> Pickup
                    @else
                        <b>Address:</b> {{ $order['delivery_address']?->getFullAddress() }}
                    @endif

                </div>
                <div>
                    <b>Phone:</b> {{ $order['billing_address']?->phone->getValue() }}
                </div>
            </div>
        </td>
    </tr>
    </tbody>
</table>

<h2 class="title">Parts</h2>

<table>
    <tbody>
    <tr>
        <td style="padding: 8px;">
            <div>
                <b>#</b>
            </div>
        </td>
        <td style="padding: 8px;">
            <div>
                <b>SKU</b>
            </div>
        </td>
        <td style="padding: 8px;">
            <div>
                <b>Item name</b>
            </div>
        </td>
        <td style="padding: 8px;">
            <div>
                <b>Price per item</b>
            </div>
        </td>
        <td style="padding: 8px;">
            <div>
                <b>Q-ty</b>
            </div>
        </td>
        <td style="padding: 8px;">
            <div>
                <b>Price</b>
            </div>
        </td>
    </tr>
    @foreach($order['items'] ?? [] as $k => $item)
        <tr>
            <td style="padding: 4px 8px;">
                <div>
                    {{$k+1}}
                </div>
            </td>
            <td style="padding: 4px 8px;">
                <div>
                    {{ $item['stock_number'] }}
                </div>
            </td>
            <td style="padding: 4px 8px;">
                <div>
                    {{ $item['name'] }}
                </div>
            </td>
            <td style="padding: 4px 8px;">
                <div>
                    $ {{ $item['price'] }}
                </div>
            </td>
            <td style="padding: 4px 8px;">
                <div>
                    {{ $item['qty'] }}
                </div>
            </td>
            <td style="padding: 4px 8px;">
                <div>
                    $ {{ $item['total'] }}
                </div>
            </td>
        </tr>
    @endforeach
    </tbody>
</table>

<table style="background-color: #FAFAFA;">
    <tbody>
    <tr>
        <td style="width: 50%; border-color: #FAFAFA; padding: 8px; text-align: right;">

            @if($order['is_paid'])

                <img style="width: 48px; height: 48px;" src='data:image/png;base64,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'/>

            @endif

        </td>
        <td style="border-color: #FAFAFA; padding: 8px;">
            <table>
                <tbody>
                <tr>
                    <td style="
                    border-color: #FAFAFA;
                    font-size: 13px;
                    font-weight: 700;
                    line-height: 15px;
                    text-align: right;"
                    >
                        <b>Subtotal: ${{$order['subtotal_amount']}}</b>
                    </td>
                </tr>
                <tr>
                    <td style="
                    border-color: #FAFAFA;
                    font-size: 13px;
                    font-weight: 700;
                    line-height: 15px;
                    text-align: right;">
                        <b>
                            @if($order['delivery_amount'])
                                Shipping: ${{$order['delivery_amount']}}
                            @else
                                Shipping: Free
                            @endif
                        </b>
                    </td>
                </tr>
                <tr>
                    <td style="
                    border-color: #FAFAFA;
                    font-size: 13px;
                    font-weight: 700;
                    line-height: 15px;
                    text-align: right;">
                        <b>Additional Shipping Cost: ${{$order['custom_delivery_cost']}}</b>
                    </td>
                </tr>
                <tr>
                    <td style="
                    border-color: #FAFAFA;
                    font-size: 13px;
                    font-weight: 700;
                    line-height: 15px;
                    text-align: right;">
                        <b>Taxes: ${{$order['tax_amount']}}</b>
                    </td>
                </tr>
                <tr>
                    <td style="
                    border-color: #FAFAFA;
                    font-size: 13px;
                    font-weight: 700;
                    line-height: 15px;
                    text-align: right;">
                        <b>Saving: ${{$order['save_amount']}}</b>
                    </td>
                </tr>
                <tr>
                    <td style="
                    border-color: #FAFAFA;
                    font-size: 13px;
                    font-weight: 700;
                    line-height: 15px;
                    text-align: right;">
                        <b>Total: ${{$order['total_amount']}}</b>
                    </td>
                </tr>
                </tbody>
            </table>
        </td>
    </tr>
    </tbody>
</table>

<br>
@if($order['is_paid'])
    <div>
        <b>Payment date:</b> {{\Illuminate\Support\Carbon::parse($order['paid_at'])->format(App\Foundations\Enums\DateTimeEnum::DateForDocs->value)}}
    </div>
    <div>
        <b>Payment method:</b> {{$order['payment']['method']}}
    </div>
@endif
<br>
    <div class="title">PAYMENT OPTIONS</div>
    <br>
    <div>
        @php echo $settings['payment_options'] @endphp
    </div>


<div style="text-align: right;
    color: lightgray;
    position: absolute;
    bottom: 0;
    right: 0;">
    Page <span class="pagenum"></span>
</div>
</body>
</html>
