<html>
<head>
    <style>
        table, td {
            border: 2px solid #AAA;
            color: #171D2D;
            width: auto;
            padding: 15px;
            font-size: 10px;
            vertical-align: top;
            border-collapse: collapse;
        }
    </style>
</head>
<body style="max-width: 878px; font-family: Arial, sans-serif; color: #171D2D; font-size: 10px;">

    @include('pdf.logo')

    <br />

    <div style="float:right;">
        <br />
        <div style="margin-bottom: 5px;"><b>Report #:</b> {{ $payroll->id }}</div>
        <div style="margin-bottom: 5px;"><b>Date:</b> {{ now()->format(config('formats.pdf_date')) }}</div>
        <div style="margin-bottom: 5px;"><b>Report total for:</b><br />{{ $payroll->start->format(config('formats.pdf_date')) }} - {{ $payroll->end->format(config('formats.pdf_date')) }}</div>
        <div style="margin-bottom: 5px;"><b>Driver:</b><br />{{ $payroll->driver->full_name }}</div>
    </div>

    <h2 style="color: #00B67B; font-weight: 700; line-height: 18px; font-size: 18px; text-transform: uppercase;">{{ $profile->name }}</h2>
    <div style="margin-bottom: 5px;">{{ $profile->address }}</div>
    <div style="margin-bottom: 5px;">{{ $profile->city }}, @if(isset($profile->state)) {{ $profile->state->name }} @endif {{ $profile->zip }}</div>
    <div style="margin-bottom: 5px;"><b>MC Number:</b> {{ $profile->mc_number }}</div>
    @if(isset($driver_salary_contact_info['email']))
        <div style="margin-bottom: 5px;"><b>Email:</b> {{ $driver_salary_contact_info['email'] ?? null }}</div>
    @endif
{{--    @if ($profile->phone)--}}
{{--        <div style="margin-bottom: 5px;"><b>Phone:</b> {{ $profile->phone }}</div>--}}
{{--    @endif--}}

        @foreach ($driver_salary_contact_info['phones'] ?? [] as $phone)
            @if(isset($phone['number']))
                <div style="margin-bottom: 5px;"><b>Phone:</b> {{ $phone['number'] }}</div>
            @endif
        @endforeach


    <br />

    <h2 style="margin-top: 4px; margin-bottom: 10px; color: #00B67B; font-weight: 700; font-size: 18px;">Report</h2>

    <table style="border: 2px solid #AAA; width: 100%; margin:0; padding:0; border-collapse: collapse;">
        <tr>
            <th style="border: 2px solid #AAA; text-align: center; vertical-align: middle; padding:15px 0">
                <div>Load ID</div>
            </th>
            <th style="border: 2px solid #AAA; text-align: center; vertical-align: middle; padding:15px 0">
                <div>Origin / Destination</div>
            </th>
{{--            <th style="border: 2px solid #AAA; text-align: center; vertical-align: middle; padding:15px 0">--}}
{{--                <div>Company name</div>--}}
{{--            </th>--}}
            <th style="border: 2px solid #AAA; text-align: center; vertical-align: middle; padding:15px 0">
                <div>Vehicles</div>
            </th>
            <th style="border: 2px solid #AAA; text-align: center; vertical-align: middle; padding:15px 0">
                <div>Payments</div>
            </th>
            <th style="border: 2px solid #AAA; text-align: center; vertical-align: middle; padding:15px 0">
                <div>Amount, $</div>
            </th>
        </tr>
        @foreach($payroll->orders as $order)
{{--            @dd($order->paymentStages)--}}
            <tr>
                <td style="border: 2px solid #AAA; padding: 10px; vertical-align: top;">
                    <div>{{ $order->load_id }}</div>
                </td>
                <td style="border: 2px solid #AAA; padding: 10px; vertical-align: top;">
                    <div>
                        {{ $stateNames[$order->pickup_contact['state_id']] ?? '' }} -
                        {{ $order->pickup_contact['zip'] }}<br />
                        {{ $stateNames[$order->delivery_contact['state_id']] ?? '' }} -
                        {{ $order->delivery_contact['zip'] }}
                    </div>
                </td>
{{--                <td style="border: 2px solid #AAA; padding: 10px; vertical-align: top;">--}}
{{--                    <div>{{ $order->shipper_full_name }}</div>--}}
{{--                </td>--}}
                <td style="border: 2px solid #AAA; padding: 10px; vertical-align: top;">
                    <div>
                        @foreach($order->vehicles as $vehicle)
                            {{ $vehicle->year }} {{ $vehicle->make }} {{ $vehicle->model }}<br/>
                        @endforeach
                    </div>
                </td>
                <td style="border: 2px solid #AAA; padding: 10px; vertical-align: top;">
                    <div>
                        @foreach($order->paymentStages as $stage)
                            {{ $stage->renderForPdf() }}<br/>
                        @endforeach
{{--                        @if($order->payment->customer_payment_amount)--}}
{{--                            ${{ $order->payment->customer_payment_amount }} via {{App\Models\Orders\Payment::CUSTOMER_METHODS[$order->payment->customer_payment_method_id]}} on {{$order->payment->customer_payment_location}}.--}}
{{--                            <br>--}}
{{--                        @endif--}}
{{--                        @if($order->payment->broker_payment_amount)--}}
{{--                            ${{ $order->payment->broker_payment_amount }} via {{App\Models\Orders\Payment::BROKER_METHODS[$order->payment->broker_payment_method_id]}} {{ $order->payment->broker_payment_days == 0 ? 'Immediately' : "within {$order->payment->broker_payment_days} days" }} {{App\Models\Orders\Payment::PAYMENT_TERMS_BEGINS_ON[$order->payment->broker_payment_begins]}}.--}}
{{--                            <br>--}}
{{--                        @endif--}}
{{--                        @if($order->payment->broker_fee_amount)--}}
{{--                            ${{ $order->payment->broker_fee_amount }} via {{App\Models\Orders\Payment::BROKER_METHODS[$order->payment->broker_fee_method_id]}} {{ $order->payment->broker_fee_days == 0 ? 'Immediately' : "within {$order->payment->broker_fee_days} days" }} {{App\Models\Orders\Payment::PAYMENT_TERMS_BEGINS_ON[$order->payment->broker_fee_begins]}}.--}}
{{--                            <br>--}}
{{--                        @endif--}}
{{--                        {{ $order->payment->terms }}--}}
                    </div>
                </td>
                <td style="border: 2px solid #AAA; padding: 10px; vertical-align: top;">
                    <div>{{ number_format($order->payment->total_carrier_amount ?? 0, 2) }}</div>
                </td>
            </tr>
        @endforeach
    </table>

    <div style="text-align:right;  font-size: 18px; padding:10px 0;">Total: <span style="font-weight: 700;">${{ $payroll->total }}</span></div>

    @if($payroll->expenses_before)
        <br/>

        <h2 style="margin-top: 4px; margin-bottom: 10px; color: #00B67B; font-weight: 700; font-size: 18px;">Expenses from gross</h2>

        <table style="border: 2px solid #AAA; width: 100%; margin:0; padding:0; border-collapse: collapse;">
            <tr>
                <th style="border: 2px solid #AAA; text-align: left; vertical-align: middle; padding:10px">
                    <div>Type</div>
                </th>
                <th style="width:20%; border: 2px solid #AAA; text-align: center; vertical-align: middle; padding:10px">
                    <div>Amount, $</div>
                </th>
            </tr>
            @foreach($payroll->expenses_before as $expense)
                <tr>
                    <td style="border: 2px solid #AAA; padding: 10px; vertical-align: top;">
                        <div>{{ $expense['type'] }}</div>
                    </td>
                    <td style="border: 2px solid #AAA; padding: 10px; vertical-align: top;">
                        <div>-{{ $expense['price'] }}</div>
                    </td>
                </tr>
            @endforeach
        </table>
    @endif

    <div style="text-align:right; font-size: 18px; padding:10px 0;">Subtotal: <span style="font-weight: 700;">${{ $payroll->subtotal }}</span></div>
    <div style="text-align:right;  font-weight: 700; font-size: 18px; padding:10px 0;">{{ $payroll->driver_rate }}%: ${{ $payroll->commission }}</div>

    @if($payroll->expenses_after)
        <br />

        <h2 style="margin-top: 4px; margin-bottom: 10px; color: #00B67B; font-weight: 700; font-size: 18px;">Charges</h2>

        <table style="border: 2px solid #AAA; width: 100%; margin:0; padding:0; border-collapse: collapse;">
            <tr>
                <th style="border: 2px solid #AAA; text-align: left; vertical-align: middle; padding:10px">
                    <div>Type</div>
                </th>
                <th style="border: 2px solid #AAA; text-align: center; vertical-align: middle; padding:10px">
                    <div>Note</div>
                </th>
                <th style="width:20%; border: 2px solid #AAA; text-align: center; vertical-align: middle; padding:10px">
                    <div>Amount, $</div>
                </th>
            </tr>
            @foreach($payroll->expenses_after as $expense)
                <tr>
                    <td style="border: 2px solid #AAA; padding: 10px; vertical-align: top;">
                        <div>{{ $expense['type'] }}</div>
                    </td>
                    <td style="border: 2px solid #AAA; padding: 10px; vertical-align: top;">
                        <div>{{ data_get($expense, 'note', '') }}</div>
                    </td>
                    <td style="border: 2px solid #AAA; padding: 10px; vertical-align: top;">
                        <div>-{{ $expense['price'] }}</div>
                    </td>
                </tr>
            @endforeach
        </table>
    @endif

    @if($payroll->bonuses)
        <br />

        <h2 style="margin-top: 4px; margin-bottom: 10px; color: #00B67B; font-weight: 700; font-size: 18px;">Bonus</h2>

        <table style="border: 2px solid #AAA; width: 100%; margin:0; padding:0; border-collapse: collapse;">
            <tr>
                <th style="border: 2px solid #AAA; text-align: left; vertical-align: middle; padding:10px">
                    <div>Type</div>
                </th>
                <th style="width:20%; border: 2px solid #AAA; text-align: center; vertical-align: middle; padding:10px">
                    <div>Amount, $</div>
                </th>
            </tr>
            @foreach($payroll->bonuses as $expense)
                <tr>
                    <td style="border: 2px solid #AAA; padding: 10px; vertical-align: top;">
                        <div>{{ $expense['type'] }}</div>
                    </td>
                    <td style="border: 2px solid #AAA; padding: 10px; vertical-align: top;">
                        <div>{{ $expense['price'] }}</div>
                    </td>
                </tr>
            @endforeach
        </table>
    @endif


    <div style="text-align:right; font-size: 18px; padding:10px 0;">Salary: <span style="color: #00b67b; font-weight: 700;">${{ $payroll->salary }}</span></div>
    <div
        style="font-family: Arial, sans-serif; color: #171D2D; font-size: 10px; margin-top: 25px;">

{{--        @if($billingContacts || $profile->billing_email)--}}
{{--            <div style="font-family: 'DejaVu Sans', sans-serif; color: #171D2D; font-size: 10px;">--}}
{{--                <span>--}}
{{--                    If you have any questions concerning this statement,--}}
{{--                    contact {{ $profile->billing_phone_name ?? ''}} {{ $profile->billing_phone ?? ''}}--}}
{{--                    @if($billingContacts) ({{ $billingContacts }})  @endif or email us at--}}
{{--                </span>--}}
{{--                <a style="font-size: 10px; color: #3639A6;" href="mailto:{{ $profile->billing_email }}" target="_blank">--}}
{{--                    {{ $profile->billing_email }}--}}
{{--                </a>--}}
{{--            </div>--}}
{{--        @endif--}}
    </div>
</body>
</html>
