<html>
<head>
</head>
<body style="margin: 0;">
<table align="center" width="100%" border="0" cellspacing="0" cellpadding="0" style="font-family: Arial, sans-serif; font-size: 16px; background-color: #ffffff; color: #000000; width: 100% !important;">
    @php
        /** @var $order \App\Models\Orders\Dealer\Order */
        /** @var $settings \App\Models\Commercial\CommercialSettings */
    @endphp
    <tr>
        <td>
            <table align="center" border="0" cellspacing="0" cellpadding="0" width="536px" style="max-width: 536px;">
                <!-- header -->
                <tr>
                    <td style="padding-top: 30px; padding-bottom: 32px;">
                        <table width="100%" border="0" cellspacing="0" cellpadding="0" style="width: 100% !important;">
                            <tr>
                                <td>
                                    <img src="{{config('app.site_url') . "/images/email/logo.png"}}" alt="" width="143" height="68">
                                </td>
                                <td style="text-align: right; color: #898C94; font-size: 13px">
                                    <div style="padding-bottom: 4px">{{ $settings->quote_address_line_1 ?? null }}</div>
                                    <div style="padding-bottom: 4px">{{ $settings->quote_address_line_2 ?? null }}</div>
                                    <div style="padding-bottom: 4px">{{ $settings->quote_email ?? null }}</div>
                                    <div style="padding-bottom: 4px">{{ $settings->quote_phone ?? null }}</div>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <!-- end header -->
                <!-- text -->
                <tr>
                    <td style="padding-bottom: 32px; line-height: 28px;">
                        @if($changed )
                            Dear customer, please note that your purchase order #{{ $order->po }} has been approved with the following edits:
                        @else
                            Dear customer, please note that your purchase order #{{ $order->po }}has been approved.
                        @endif
                    </td>
                </tr>
                <!-- end text -->
                <!-- products -->
                <tr>
                    <td style="padding-bottom: 32px;">
                        <table width="100%" border="0" cellspacing="0" cellpadding="0" style="width: 100% !important; border: 1px solid #CFD8EB;">
                            <!-- products head -->
                            <tr>
                                <td style="width: 50%; background: #F2F2F2; padding: 8px 24px; text-transform: uppercase; color: #898C94; font-size: 13px;">It was</td>
                                <td style="width: 50%; background: #F2F2F2; padding: 8px 24px; text-transform: uppercase; color: #898C94; font-size: 13px; border-left: 1px solid #CFD8EB;">Became</td>
                            </tr>
                            <!-- end products head -->
                            <!-- products body -->
                            <!-- product 1 -->

                            <!-- end product 1 -->
                            <!-- product 2 -->
                            @foreach($order->items()->withTrashed()->get() as $item)
                                @php
                                /** @var $item \App\Models\Orders\Dealer\Item */
                                @endphp
                                <tr>
                                    <!-- product it was -->
                                    <td style="width: 50%; padding: 9px 24px; border-bottom: 1px solid #F0F4FD;">
                                        <table width="100%" border="0" cellspacing="0" cellpadding="0" style="width: 100% !important;">
                                            <tr>
                                                <td style="width: 50px; padding-right: 24px">
                                                    <img style="max-width: 50px; max-height: 50px;" src="{{
                                                        $item->product->getImgUrl()
                                                        ? $item->product->getImgUrl()
                                                        : config('app.site_url') . "/images/email/no-image.png"
                                                    }}">
                                                </td>
                                                <td>
                                                    <table width="100%" border="0" cellspacing="0" cellpadding="0" style="width: 100% !important;">
                                                        <tr>
                                                            <td style="padding-bottom: 8px;">
                                                                <table width="100%" border="0" cellspacing="0" cellpadding="0" style="width: 100% !important;">
                                                                    <tr>
                                                                        <td>
                                                                            <a
                                                                                href="{{$item->product->getFrontLink()}}"
                                                                                style="font-weight: 500; color: #004B91; font-size: 16px; text-transform: uppercase"
                                                                            >
                                                                                {{ $item->product->title }}
                                                                            </a>
                                                                        </td>
                                                                        <td style="width: 12px; padding-left: 5px; text-align: right;"></td>
                                                                    </tr>
                                                                </table>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td>
                                                                <table width="100%" border="0" cellspacing="0" cellpadding="0" style="width: 100% !important;">
                                                                    <tr>
                                                                        <td style="font-weight: 500; font-size: 13px;">
                                                                            {{ $item->primary?->qty }}
                                                                        </td>
                                                                        <td style="padding-left: 10px; text-align: right; font-weight: 500; font-size: 13px;">
                                                                            $ {{ $item->primary?->price }}
                                                                        </td>
                                                                    </tr>
                                                                </table>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                    <!-- end product it was -->
                                    <!-- product became -->
                                    <td style="width: 50%; padding: 9px 24px; border-bottom: 1px solid #F0F4FD; border-left: 1px solid #F0F4FD;">
                                        <table
                                            width="100%"
                                            border="0"
                                            cellspacing="0"
                                            cellpadding="0"
                                            style="width: 100% !important; ">
                                            <tr>
                                                <td style="width: 50px; padding-right: 24px">
                                                    <img style="max-width: 50px; max-height: 50px;{{$item->trashed() ? 'opacity: 0.5' : null}}" src="{{
                                                        $item->product->getImgUrl()
                                                        ? $item->product->getImgUrl()
                                                        : config('app.site_url') . "/images/email/no-image.png"
                                                    }}">
                                                </td>
                                                <td>
                                                    <table width="100%" border="0" cellspacing="0" cellpadding="0" style="width: 100% !important;">
                                                        <tr>
                                                            <td style="padding-bottom: 8px;">
                                                                <table width="100%" border="0" cellspacing="0" cellpadding="0" style="width: 100% !important;">
                                                                    <tr>
                                                                        <td>
                                                                            <a
                                                                                href="{{$item->product->getFrontLink()}}"
                                                                                style="font-weight: 500; color: #004B91; font-size: 16px; text-transform: uppercase; {{$item->trashed() ? 'color: rgba(0, 75, 145, 0.5)' : null}}"
                                                                            >
                                                                                {{ $item->product->title }}
                                                                            </a>
                                                                        </td>
                                                                        <td style="width: 12px; padding-left: 5px; text-align: right;">
                                                                            @if($item->trashed())
                                                                                <img src="{{ config('app.site_url') . "/images/email/icon-cross.png" }}" width="12" height="12" alt="icon-cross.png">
                                                                            @endif
                                                                        </td>
                                                                    </tr>
                                                                </table>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td>
                                                                <table width="100%" border="0" cellspacing="0" cellpadding="0" style="width: 100% !important;">
                                                                    <tr>
                                                                        <td style="font-weight: 500; font-size: 13px; {{$item->trashed() ? 'color: rgba(0, 0, 0, 0.5)' : null}}">
                                                                            {{ $item->qty }}
                                                                        </td>
                                                                        <td style="padding-left: 10px; text-align: right; font-weight: 500; font-size: 13px; {{$item->trashed() ? 'color: rgba(0, 0, 0, 0.5)' : null}}">
                                                                            $ {{ $item->price }}
                                                                        </td>
                                                                    </tr>
                                                                </table>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                    <!-- end product became -->
                                </tr>
                                <!-- end product 2 -->
                                <!-- end products body -->
                                <!-- products foot -->
                                <tr>
                            @endforeach

                                <td style="width: 50%; background: #F0F4FD; padding: 16px 24px; border-top: 1px solid #CFD8EB;">
                                </td>
                                <td style="width: 50%; background: #F0F4FD; padding: 16px 24px; border-top: 1px solid #CFD8EB; border-left: 1px solid #CFD8EB;">
                                    <table width="100%" border="0" cellspacing="0" cellpadding="0" style="width: 100% !important;">
                                        <tr>
                                            <td style="padding-bottom: 4px">
                                                <table width="100%" border="0" cellspacing="0" cellpadding="0" style="width: 100% !important;">
                                                    <tr>
                                                        <td style="font-size: 12px; color: #999999;">Amount</td>
                                                        <td style="font-weight: 500; font-size: 13px; text-align: right;">{{ $order->items_qty }}</td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="padding-bottom: 4px">
                                                <table width="100%" border="0" cellspacing="0" cellpadding="0" style="width: 100% !important;">
                                                    <tr>
                                                        <td style="font-size: 12px; color: #999999;">Tax</td>
                                                        <td style="font-weight: 500; font-size: 13px; text-align: right;">$ {{ $order->tax }}</td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="padding-bottom: 4px">
                                                <table width="100%" border="0" cellspacing="0" cellpadding="0" style="width: 100% !important;">
                                                    <tr>
                                                        <td style="font-size: 12px; color: #999999;">Shipping</td>
                                                        <td style="font-weight: 500; font-size: 13px; text-align: right;">$ {{ $order->shipping_price }}</td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <table width="100%" border="0" cellspacing="0" cellpadding="0" style="width: 100% !important;">
                                                    <tr>
                                                        <td style="font-size: 18px; color: #999999;">Total</td>
                                                        <td style="font-weight: 700; font-size: 18px; text-align: right;">$ {{ $order->total }}</td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                            <!-- end products foot -->
                        </table>
                    </td>
                </tr>
                <!-- end products -->
                <!-- link -->
                <tr>
                    <td style="padding-bottom: 32px; text-align: center;">
                        <a href="{{ config('app.site_url') }}/account/dealer-orders" style="background: #004B91; border-radius: 8px; padding: 11px 12px 9px; display: inline-block; text-transform: uppercase; color: #FFFFFF; font-size: 13px; text-decoration: none;">Go to your account</a>
                    </td>
                </tr>
                <!-- end link -->
                <!-- footer -->
                <tr>
                    <td style="color: #AFB3BC; font-size: 13px; padding: 8px 0 19px; border-top: 1px solid rgba(0, 0, 0, 0.15); text-align: center;">
                        &copy; {{ config('mail.from.name') }} {{ date('Y') }}
                    </td>
                </tr>
                <!-- end footer -->
            </table>
        </td>
    </tr>
</table>
</body>
</html>

