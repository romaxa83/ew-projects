<html style="font-size: 8px;
            margin: 0;
            overflow: visible;
            padding: 0;">
    <head>
        <meta charset="UTF-8">
        <style>
            @page  {
                margin: 0;
            }
        </style>
    </head>
    <body style="font-size: 8px; margin: 13mm; overflow: visible; padding: 0;">
        <table style="width: 100%;">
            <thead>
                <tr>
                    <td>
                        <div style=" width: 100%;color: #B9B9B9;height: 50px;">&nbsp;</div>
                    </td>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <div >
                            <div style="font-family: Arial, sans-serif;font-size: 8px;line-height: 1.2;font-weight: 400;color: #022B3D;">
                                @if ($logo)
                                    <div style="text-align:center; margin-bottom: 16px;">
                                        <img alt=""
                                             src="{{ $logo }}"
                                             style="width: 100px;height: auto;"
                                             width="100px"
                                        >
                                    </div>
                                @endif

                                <h2 style="font-weight: 700;margin: 0;margin-bottom: 4px; color: #00B67B; font-size: 13px;">
                                    {{ $settings['company_name']->value ?? '' }}
                                </h2>
                                <div style="margin-bottom: 4px;">{{ $settings['address']->value ?? '' }}4</div>
                                <div style="margin-bottom: 4px;">{{ $settings['city']->value ?? '' }} {{ $state }} {{ $settings['zip']->value ?? '' }}</div>
                                <div style="margin-bottom: 4px;"><b style="font-weight: 700;">Email:</b> {{ $settings['email']->value ?? '' }}</div>
                                <div style="margin-bottom: 4px;"><b style="font-weight: 700;">Phone:</b> {{ $settings['phone']->value ?? '' }}</div>
                                <div style="margin-bottom: 16px;">
                                    <table style="font-size: inherit;border: 1px solid #E8E8E8;width: 100%;padding: 0;margin: 0;vertical-align: top;border-collapse: collapse;">
                                        <tbody>
                                            <tr>
                                                <td style="padding: 8px; border: 1px solid #E8E8E8;color: #171D2D;width: auto;padding: 4px 8px;vertical-align: top;border-collapse: collapse;">
                                                    <div style="margin-bottom: 4px;"><b style="font-weight: 700;">Invoice #: </b>{{ $transaction->invoice_number }}</div>
                                                    <div><b style="font-weight: 700;">Invoice date: </b>{{ $invoiceDate }}</div>
                                                </td>
                                                <td style="padding: 8px;border: 1px solid #E8E8E8;color: #171D2D;width: auto;padding: 4px 8px;vertical-align: top;border-collapse: collapse;">
                                                    <h3 style=" font-weight: 700;margin: 0;font-size: 8px;margin-bottom: 4px;color: #00B67B;">
                                                        BILL TO
                                                    </h3>
                                                    <div style="margin-bottom: 4px;"><b style="font-weight: 700;">{{ $customerName }}</b></div>
                                                    <div style="margin-bottom: 4px;"><b style="font-weight: 700;">Phone: </b>{{ $transaction->phone }}</div>
                                                    <div><b style="font-weight: 700;">Email: </b>{{ $transaction->email }}</div>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <h2 style="font-weight: 700;margin: 0;margin-bottom: 8px;color: #00B67B;">Parts</h2>
                                <div style="margin-bottom: 16px;">
                                    <table style="font-size: inherit;border: 1px solid #E8E8E8;width: 100%;padding: 0;margin: 0;vertical-align: top;border-collapse: collapse;">
                                        <thead>
                                            <tr>
                                                <th style=" text-align: left;border: 1px solid #E8E8E8;color: #171D2D;width: auto;padding: 7px 8px 7px;vertical-align: top;border-collapse: collapse;">
                                                    <b style="font-weight: 700;">#</b>
                                                </th>
                                                <th style="border: 1px solid #E8E8E8;color: #171D2D;width: auto;padding: 7px 8px 7px;vertical-align: top;border-collapse: collapse;">
                                                    <b style="font-weight: 700;">Stock number</b>
                                                </th>
                                                <th style="border: 1px solid #E8E8E8;color: #171D2D;width: auto;padding: 7px 8px 7px;vertical-align: top;border-collapse: collapse;">
                                                    <b style="font-weight: 700;">Part name</b>
                                                </th>
                                                <th style="border: 1px solid #E8E8E8;color: #171D2D;width: auto;padding: 7px 8px 7px;vertical-align: top;border-collapse: collapse;">
                                                    <b style="font-weight: 700;">Price per unit</b>
                                                </th>
                                                <th style="border: 1px solid #E8E8E8;color: #171D2D;width: auto;padding: 7px 8px 7px;vertical-align: top;border-collapse: collapse;">
                                                    <b style="font-weight: 700;">Q-ty</b>
                                                </th>
                                                <th style="border: 1px solid #E8E8E8;color: #171D2D;width: auto;padding: 7px 8px 7px;vertical-align: top;border-collapse: collapse;">
                                                    <b style="font-weight: 700;">Price</b>
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td style="border: 1px solid #E8E8E8;color: #171D2D;width: auto;padding: 4px 8px;vertical-align: top;border-collapse: collapse;">
                                                    <div>1</div>
                                                </td>
                                                <td style="border: 1px solid #E8E8E8;color: #171D2D;width: auto;padding: 4px 8px;vertical-align: top;border-collapse: collapse;">
                                                    <div>{{ $transaction->inventory->stock_number }}</div>
                                                </td>
                                                <td style="border: 1px solid #E8E8E8;color: #171D2D;width: auto;padding: 4px 8px;vertical-align: top;border-collapse: collapse;">
                                                    <div>{{ $transaction->inventory->name }}</div>
                                                </td>
                                                <td style="border: 1px solid #E8E8E8;color: #171D2D;width: auto;padding: 4px 8px;vertical-align: top;border-collapse: collapse;">
                                                    <div>$ {{ $transaction->price }}</div>
                                                </td>
                                                <td style="border: 1px solid #E8E8E8;color: #171D2D;width: auto;padding: 4px 8px;vertical-align: top;border-collapse: collapse;">
                                                    <div>{{ $transaction->quantity }}</div>
                                                </td>
                                                <td style="border: 1px solid #E8E8E8;color: #171D2D;width: auto;padding: 4px 8px;vertical-align: top;border-collapse: collapse;">
                                                    <div>$ {{ $partsTotal }}</div>
                                                </td>
                                            </tr>
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <td colspan="6" style="border: none; border-top: 1px solid #E8E8E8;color: #171D2D;width: auto;vertical-align: top;border-collapse: collapse;background-color: #FAFAFA;font-size: 10px;font-weight: 700;text-align: right;padding: 0;">
                                                    <table style="font-size: inherit;border: none #E8E8E8;width: 100%;padding: 0;margin: 0;vertical-align: top;border-collapse: collapse;;">
                                                        <tbody>
                                                        <tr>
                                                            <td style="width: 100%;vertical-align: middle;border-collapse: collapse;background-color: #FAFAFA;font-size: 10px;font-weight: 700;text-align: right;padding: 8px;">
                                                                <img src="{{ asset('images/paid.svg') }}" style="width:48px; height:48px;" width="48" height="48" />
                                                            </td>
                                                            <td style="border: none; color: #171D2D;width: auto;vertical-align: top;border-collapse: collapse;background-color: #FAFAFA;font-size: 10px;font-weight: 700;text-align: right;padding: 8px;">
                                                                <div style="position: relative; width: 100%; white-space: nowrap;">
                                                                    <div style="margin-bottom: 4px;"><b style="font-weight: 700;">Parts total: ${{ $partsTotal }}</b></div>
                                                                    <div style="margin-bottom: 4px;"><b style="font-weight: 700;">Discount parts: {{ $transaction->discount }}%, ${{ $discountPart}}</b></div>
                                                                    <div style="margin-bottom: 4px;"><b style="font-weight: 700;">Tax on Parts Only: {{ $transaction->tax }}%, ${{$taxAmount}}</b></div>
                                                                    <div><b style="font-weight: 700;">Parts Total after Discount and Tax: ${{ $totalAmount }}</b></div>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                        </tbody>
                                                    </table>
                                                </td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                                <div style="margin-bottom: 8px;">
                                    <div><b style="font-weight: 700; font-size: 10px">Payment date: </b>{{ $paymentDate }}</div>
                                    <div><b style="font-weight: 700; font-size: 10px">Payment method: </b>{{ $paymentMethodName }}</div>
                                </div>
                                @if($settings['billing_payment_details']->value ?? '')
                                    <h2 style="font-weight: 700;margin: 0;font-size: 13px;margin-bottom: 8px;color: #00B67B;">
                                        Payment options
                                    </h2>
                                    <div style="color: #8C8C8C; margin-bottom: 8px;">
                                        If you have any questions concerning this invoice,
                                        contact {{ $settings['billing_phone']->value }} or email us at
                                    </div>
                                    <a href="mailto:{{ $settings['billing_email']->value }}" style="text-decoration: underline;color: #00b67b;display: block;"
                                       target="_blank">
                                        {{ $settings['billing_email']->value }}
                                    </a>
                                    <div style="color: #8C8C8C;">
                                        {!! $settings['billing_payment_details']->value !!}
                                    </div>
                                @endif
                            </div>
                        </div>
                    </td>
                </tr>
            </tbody>
            <tfoot>
                <tr>
                    <td>
                        <div style=" width: 100%;color: #B9B9B9;height: 50px;">&nbsp;</div>
                    </td>
                </tr>
            </tfoot>
        </table>

        <div style="position: fixed; top: 10mm; left: 12mm; width: 100%;color: #B9B9B9;height: 50px;">
            <div style="margin-bottom: 4px; color: #8C8C8C;">Invoice #: {{ $transaction->invoice_number }}</div>
            <div style=" color: #8C8C8C;">Invoice Date: {{ $invoiceDate }}</div>
        </div>
    </body>
</html>
