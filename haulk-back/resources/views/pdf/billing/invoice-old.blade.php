<html>
<head>
    <style>
        body > * {
            font-family: Arial, sans-serif;
        }

        table {
            width: 100%;
            margin-bottom: 8px;
        }

        table, td {
            border: 1px solid #dcdcdc;
            color: #171D2D;
            width: auto;
            font-size: 8px;
            vertical-align: top;
            border-collapse: collapse;
        }

        .table {
            width: 100%;
            thead > tr > td {
                width: 25%;
                padding: 8px
            }
            tbody > tr > td {
                padding: 4px 8px
            }
        }


        .green-title {
            font-size: 13px;
            font-weight: 700;
            line-height: 15px;
            color: #00B67B;
        }

        .table-title {
            font-weight: 700;
            font-size: 10px;
        }

        .table-subtotal {
            font-size: 10px;
            font-weight: 700;
            line-height: 11px;
            text-align: right;
            padding-bottom: 8px;
        }

        .total {
            font-size: 13px;
            font-weight: 700;
            line-height: 15px;
            text-align: right;
        }
    </style>
</head>
<body style="max-width: 878px; font-family: Arial, sans-serif; color: #171D2D; font-size: 10px;">
    @include('pdf.logo')
    <br>
    <h2 style="font-family: Arial, sans-serif; color: #00B67B; font-weight: 700; font-size: 17.5px; margin: 10px 0 3px; text-transform: uppercase;">
        {{ $invoice->company->name }}
    </h2>
    <div style="font-family: Arial, sans-serif; font-size: 10px; color: #171D2D; margin-bottom: 5px;">
        {{$invoice->company->address }}
    </div>
    <div style="font-family: Arial, sans-serif; font-size: 10px; color: #171D2D; margin-bottom: 5px;">
        {{$invoice->company->city }}, @if(isset($invoice->company->state)) {{ $invoice->company->state->name }} @endif {{
        $invoice->company->zip }}
    </div>
    <div style="font-family: Arial, sans-serif; font-size: 10px; color: #171D2D; margin-bottom: 5px;">
        <b>MC Number:</b>{{$invoice->company->mc_number }}
    </div>
    <div style="font-family: Arial, sans-serif; font-size: 10px; color: #171D2D; margin-bottom: 5px;">
        <b>Email:</b> {{ $invoice->company->email }}
    </div>
    @if ($invoice->company->phone)
        <div style="font-family: Arial, sans-serif; font-size: 10px; color: #171D2D; margin-bottom: 5px;">
            <b>Phone:</b> {{ $invoice->company->phone }}
        </div>
   @endif
    @if ($invoice->company->phones)
        @foreach ($invoice->company->phones as $phone)
            @if(isset($phone['number']))
                <div style="font-family: Arial, sans-serif; font-size: 10px; color: #171D2D; margin-bottom: 5px;">
                    <b>Phone:</b> {{ $phone['number'] }}
                </div>
           @endif
        @endforeach
   @endif
    <div>

    </div>

    <table style="font-weight: 700; font-size: 11px; width: 100%; ">
        <tbody>
            <tr>
                <td style="padding: 4px 0 4px 50px; width: 70%; border-color: #ffffff;">
                    Period: {{ \Illuminate\Support\Carbon::parse($invoice->billing_start)->format('M j, Y') }} - {{ \Illuminate\Support\Carbon::parse($invoice->billing_end)->format('M j, Y') }}
                </td>
                <td style="padding: 4px 0; border-color: #ffffff;">
                    {{\Illuminate\Support\Str::plural('Driver', collect($invoice->billing_data)->last()['driver_count']) .': '. collect($invoice->billing_data)->last()['driver_count']}}
                </td>
                <td style="padding: 4px 0; border-color: #ffffff;">Subtotal: {{toMoney($invoice['drivers_amount'])}}</td>
            </tr>
            @if($invoice->has_gps_subscription)
                <tr>
                    <td style="padding: 4px 0; width: 70%; border-color: #ffffff;"></td>
                    <td style="padding: 4px 0; border-color: #ffffff;">GPS {{\Illuminate\Support\Str::plural('Device', collect($invoice->gps_device_data)->count())}}: {{ collect($invoice->gps_device_data)->count()}}</td>
                    <td style="padding: 4px 0; border-color: #ffffff;">Subtotal: {{toMoney($invoice['gps_device_amount'])}}</td>
                </tr>
            @endif
        </tbody>
    </table>
    <h2 class="detail-label">Details</h2>

    <h3 class="table-title">Haulk subscription</h3>
    <table style="width: 100%">
        <thead>
            <tr>
                <td style="padding: 7px 15px">
                    <strong>Haulk subscription</strong>
                </td>
                <td style="padding: 7px 15px">
                    <strong>Period</strong>
                </td>
                <td style="padding: 7px 15px">
                    <strong>Quantity</strong>
                </td>
                <td style="padding: 7px 15px; width: 15%">
                    <strong>Amount, $</strong>
                </td>
            </tr>
        </thead>
        <tbody>
            @forelse($invoice->formatDriverDataForPdf() as $item)
                <tr>
                    <td style="padding: 4px 15px">
                        Active drivers
                    </td>
                    <td style="padding: 4px 15px">
                        {{\Illuminate\Support\Carbon::parse($item['start_period'])->format('M j, Y')}} - {{\Illuminate\Support\Carbon::parse($item['end_period'])->format('M j, Y')}}
                    </td>
                    <td style="padding: 4px 15px">
                        {{$item['driver_count']}}
                    </td>
                    <td style="padding: 4px 15px;">
                        {{toMoney($item['amount'])}}
                    </td>
                </tr>
            @empty
            @endforelse
        </tbody>
    </table>
    <div class="table-subtotal">Subtotal: {{toMoney($invoice['drivers_amount'])}}</div>

    @if($invoice->has_gps_subscription)
        <h3 class="table-title">GPS subscription</h3>
        <table style="width: 100%">
            <thead>
            <tr>
                <td style="padding: 7px 15px">
                    <strong>Number of active devices</strong>
                </td>
                <td style="padding: 7px 15px">
                    <strong>Number of days</strong>
                </td>
                <td style="padding: 7px 15px; width: 15%">
                    <strong>Amount, $</strong>
                </td>
            </tr>
            </thead>
            <tbody>
            @forelse($invoice->formatGpsDeviceDataForPdf() as $item)
                <tr>
                    <td style="padding: 4px 15px">
                        {{$item['count']}}
                    </td>
                    <td style="padding: 4px 15px">
                        {{$item['days'].' '.\Illuminate\Support\Str::plural('day', $item['days'])}}
                    </td>
                    <td style="padding: 4px 15px;">
                        {{toMoney($item['amount'])}}
                    </td>
                </tr>
            @empty
            @endforelse
            </tbody>
        </table>

        <div class="table-subtotal">Subtotal in USD: {{toMoney($invoice->gps_device_amount)}}</div>
    @endif


    <div class="total">Total in USD:{{toMoney($invoice->amount)}}</div>
    <div style="
    text-align: right;
    color: lightgray;
    position: absolute;
    bottom: 0;
    right: 0;
">
        Page 1 of 1
    </div>
</body>
</html>
