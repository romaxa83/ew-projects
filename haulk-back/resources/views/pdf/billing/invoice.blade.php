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

        .green-title {
            font-size: 13px;
            font-weight: 700;
            line-height: 15px;
            color: #00B67B;
            margin: 4px 0 8px;
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

        .pagenum:before {
            content: counter(page);
        }
    </style>
</head>
<body style="max-width: 878px; font-family: Arial, sans-serif; color: #171D2D; font-size: 10px;">

<table style="font-size: 8px; width: 100%; margin-bottom: 16px ">
    <tbody>
    <tr>
        <td style="width: 50%; border-color: #ffffff;">
            <div style="font-size: 13px; font-weight: 700">Invoice</div>
            <div style="font-size: 8px; font-weight: 700">Invoice No {{ $invoice->id + 1000 }}</div>
        </td>
        <td style="padding-left: 8px; border-color: #ffffff;">
            <div style="text-align: start">
                @include('pdf.logo-left')
            </div>
        </td>
    </tr>
    </tbody>
</table>

<table style="font-size: 8px; width: 100%; ">
    <tbody>
    <tr>
        <td style="width: 50%; border-color: #ffffff;">
            <b>Bill to</b>
            <h2 class="green-title" style="text-transform: uppercase">
                @if($invoice->company)
                    {{ $invoice->company->name }}
                @endif
            </h2>
            <div style="font-family: Arial, sans-serif; font-size: 8px; color: #171D2D; margin-bottom: 4px;">
                @if($invoice->company)
                    {{ $invoice->company->address }}
                @endif
            </div>
            <div style="font-family: Arial, sans-serif; font-size: 8px; color: #171D2D; margin-bottom: 4px;">
                @if($invoice->company)
                    {{$invoice->company->city }}, @if(isset($invoice->company->state)) {{ $invoice->company->state->name }} @endif {{ $invoice->company->zip }}
                @endif
            </div>
            <div style="font-family: Arial, sans-serif; font-size: 8px; color: #171D2D; margin-bottom: 4px;">
                <b>MC Number:</b> @if($invoice->company) {{$invoice->company->mc_number }} @endif
            </div>
            <div style="font-family: Arial, sans-serif; font-size: 8px; color: #171D2D; margin-bottom: 4px;">
                <b>Email:</b> @if($invoice->company) {{ $invoice->company->email }} @endif
            </div>
            @if($invoice->company)
                @if ($invoice->company->phone)
                    <div style="font-family: Arial, sans-serif; font-size: 8px; color: #171D2D; margin-bottom: 4px;">
                        <b>Phone:</b> {{ $invoice->company->phone }}
                    </div>
                @endif
                @if ($invoice->company->phones)
                    @foreach ($invoice->company->phones as $phone)
                        @if(isset($phone['number']))
                            <div style="font-family: Arial, sans-serif; font-size: 8px; color: #171D2D; margin-bottom: 4px;">
                                <b>Phone:</b> {{ $phone['number'] }}
                            </div>
                        @endif
                    @endforeach
                @endif
            @endif
            <div></div>
        </td>
        <td style="padding-left: 8px; border-color: #ffffff;">
            <h2 class="green-title">
                Haulk Inc
            </h2>
            <div
                style="font-family: Arial, sans-serif; font-size: 8px; color: #171D2D; margin-bottom: 4px;">
                728 NORTHWEST HWY #259
            </div>
            <div
                style="font-family: Arial, sans-serif; font-size: 8px; color: #171D2D; margin-bottom: 4px;">
                FOX RIVER GROVE, IL 60021
            </div>
            <div
                style="font-family: Arial, sans-serif; font-size: 8px; color: #171D2D; margin-bottom: 4px;">
                billing@haulk.app
            </div>
        </td>
    </tr>
    </tbody>
</table>

<table style="font-weight: 700; font-size: 11px; width: 100%; ">
    <tbody>
    <tr>
        <td style="padding: 4px 0 4px 32px; width: 50%; border-color: #ffffff;">
            Period: {{ \Illuminate\Support\Carbon::parse($invoice->billing_start)->format('M j, Y') }} - {{ \Illuminate\Support\Carbon::parse($invoice->billing_end)->format('M j, Y') }}
        </td>
        <td style="padding: 4px 0; width: 25%; border-color: #ffffff;">
            {{\Illuminate\Support\Str::plural('Driver', collect($invoice->billing_data)->last()['driver_count']) .': '. collect($invoice->billing_data)->last()['driver_count']}}
        </td>
        <td style="padding: 4px 0; width: 25%; border-color: #ffffff;">Subtotal: {{toMoney($invoice['drivers_amount'])}}</td>
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


<h2 class="green-title">Details</h2>

<table style="width: 100%">
    <thead>
    <tr>
        <td style="width: 25%; padding: 8px;">
            <strong>Haulk subscription</strong>
        </td>
        <td style="width: 25%; padding: 8px;">
            <strong>Period</strong>
        </td>
        <td style="width: 25%; padding: 8px;">
            <strong>Quantity</strong>
        </td>
        <td style="width: 25%; padding: 8px;">
            <strong>Amount, $</strong>
        </td>
    </tr>
    </thead>
    <tbody>
    @forelse($invoice->formatDriverDataForPdf() as $item)
        <tr>
            <td style="padding: 4px 8px">
                Active drivers
            </td>
            <td style="padding: 4px 8px">
                {{\Illuminate\Support\Carbon::parse($item['start_period'])->format('M j, Y')}} - {{\Illuminate\Support\Carbon::parse($item['end_period'])->format('M j, Y')}}
            </td>
            <td style="padding: 4px 8px">
                {{$item['driver_count']}}
            </td>
            <td style="padding: 4px 8px">
                {{toMoney($item['amount'])}}
            </td>
        </tr>
    @empty
    @endforelse
    </tbody>
</table>
<div class="table-subtotal">Subtotal in USD: {{toMoney($invoice['drivers_amount'])}}</div>

@if($invoice->has_gps_subscription)
    <h3 class="table-title">GPS subscription</h3>
    <table style="width: 100%">
        <thead>
        <tr>
            <td style="padding: 4px 8px">
                <strong>Number of active devices</strong>
            </td>
            <td style="padding: 4px 8px">
                <strong>Number of days</strong>
            </td>
            <td style="padding: 4px 8px; width: 15%">
                <strong>Amount, $</strong>
            </td>
        </tr>
        </thead>
        <tbody>
        @forelse($invoice->formatGpsDeviceDataForPdf() as $item)
            <tr>
                <td style="padding: 4px 8px">
                    {{$item['count']}}
                </td>
                <td style="padding: 4px 8px">
                    {{$item['days'].' '.\Illuminate\Support\Str::plural('day', $item['days'])}}
                </td>
                <td style="padding: 4px 8px;">
                    {{toMoney($item['amount'])}}
                </td>
            </tr>
        @empty
        @endforelse
        </tbody>
    </table>

    <div class="table-subtotal">Subtotal in USD: {{toMoney($invoice->gps_device_amount)}}</div>
@endif

<div class="total">Total in USD: {{toMoney($invoice->amount)}}</div>
<div style="
    text-align: right;
    color: lightgray;
    position: absolute;
    bottom: 0;
    right: 0;
">
    Page <span class="pagenum"></span>
</div>
</body>
</html>
