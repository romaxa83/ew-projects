<html>
<head>
    <style>
        table {
            border: 2px solid #AAA;
            color: #171D2D;
            width: 100%;
            padding: 0;
            margin:0;
            font-size: 10px;
            vertical-align: top;
            border-collapse: collapse;
        }
        td {
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
<body>

@include('pdf.logo')

<h2 style="font-family: Arial, sans-serif; color: #00B67B; font-weight: 700; font-size: 17.5px; margin: 10px 0 3px; text-transform: uppercase;">
    {{ $profile['name'] }}
</h2>
<div
    style="font-family: Arial, sans-serif; font-size: 10px; color: #171D2D; margin-bottom: 5px;">{{ $profile['address'] }}</div>
<div style="font-family: Arial, sans-serif; font-size: 10px; color: #171D2D; margin-bottom: 5px;">{{ $profile['location'] }}</div>
<div style="font-family: Arial, sans-serif; font-size: 10px; color: #171D2D; margin-bottom: 5px;"><b>MC Number:</b> {{ $profile['mc_number'] }}</div>
<div style="font-family: Arial, sans-serif; font-size: 10px; color: #171D2D; margin-bottom: 5px;">
    <b>Email:</b> {{ $profile['email'] }}</div>
@if ($profile['phone'])
    <div style="font-family: Arial, sans-serif; font-size: 10px; color: #171D2D; margin-bottom: 5px;">
        <b>Phone:</b> {{ $profile['phone'] }}
    </div>
@endif
@forelse($profile['phones'] as $phone)

    @continue(empty($phone['number']))

    <div style="font-family: Arial, sans-serif; font-size: 10px; color: #171D2D; margin-bottom: 5px;">
        <b>Phone:</b> {{ $phone['number'] }}
    </div>
@empty
@endforelse
<h2 style="font-family: Arial, sans-serif; margin-top: 4px; margin-bottom: 4px; color: #00B67B; font-weight: 700; font-size: 17.5px;">INVOICE for Load ID: {{ $load_id }}</h2>
<table
    style="border: 2px solid #AAA; color: #171D2D; font-family: Arial, sans-serif; width: 100%; border-collapse: collapse;">
    <tr>
        <td style="border: 2px solid #AAA; color: #171D2D; padding: 10px; font-size: 10px; width: 50%; vertical-align: top;">
            <div style="margin-bottom: 5px;"><b>Invoice #: </b>{{ $invoice_id }}
            </div>
            <div style="margin-bottom: 5px;"><b>Invoice Date: </b>{{ $invoice_date }}</div>
            <div style="margin-bottom: 5px;"><b>Payment terms: </b>{{ $terms }}</div>
        </td>
        <td style="border: 2px solid #AAA; color: #171D2D; padding: 10px; font-size: 10px; width: 50%; vertical-align: top;">
            <h3 style="text-transform: uppercase; color: #00B67B; font-size: 10px; font-weight: 700; margin: 0 0 8px;">
                bill to
            </h3>
            @include('pdf.contact', ['contact' => $bill_to])
        </td>
    </tr>
    <tr>
        <td style="border: 2px solid #AAA; color: #171D2D; padding: 10px; font-size: 10px; width: 50%; vertical-align: top;">
            <h3 style="text-transform: uppercase; color: #00B67B; font-size: 10px; font-weight: 700; margin: 0 0 8px;">
                ORIGIN
            </h3>
            @include('pdf.contact', ['contact' => $origin])
        </td>
        <td style="border: 2px solid #AAA; color: #171D2D; padding: 10px; font-size: 10px; width: 50%; vertical-align: top;">
            <h3 style="text-transform: uppercase; color: #00B67B; font-size: 10px; font-weight: 700; margin: 0 0 8px;">
                Destination
            </h3>
            @include('pdf.contact', ['contact' => $destination])
        </td>
    </tr>
</table>
<table
    style="border: 2px solid #AAA; color: #171D2D; font-family: Arial, sans-serif;width: 100%; border-collapse: collapse; margin-top: 15px;"
    class="table-lg">
    <tr>
        <th style="width: 20%; text-transform: uppercase; border: 2px solid #AAA; color: #171D2D; text-align: center; vertical-align: middle; font-size: 10px; padding:15px 0">
            <div>QTY</div>
        </th>
        <th style="width: 40%; text-transform: uppercase; border: 2px solid #AAA; color: #171D2D; text-align: left; vertical-align: middle; font-size: 10px; padding: 15px 0 15px 10px;">
            <div>description</div>
        </th>
        <th style="width: 40%; text-transform: uppercase; border: 2px solid #AAA; color: #171D2D; text-align: left; vertical-align: middle; font-size: 10px; padding: 15px 0 15px 20px;">
            <div>line total</div>
        </th>
    </tr>
    @foreach($vehicles as $vehicle)
        <tr>
            <td style="vertical-align: middle; border: 2px solid #AAA; color: #171D2D; width: auto; padding: 15px; font-size: 10px; vertical-align: top;">
                <div>1</div>
            </td>
            <td style="vertical-align: middle; border: 2px solid #AAA; color: #171D2D; width: auto; padding: 15px; font-size: 10px; vertical-align: top;">
                <div>{{ $vehicle['year'] }} {{ $vehicle['make'] }} {{ $vehicle['model'] }}</div>
                <div>Vin: {{ $vehicle['vin'] }}, Type: {{ $vehicle['type'] }}, Color: {{ $vehicle['color'] }}</div>
            </td>
            <td style="vertical-align: middle; border: 2px solid #AAA; color: #171D2D; width: auto; padding: 15px; font-size: 10px; vertical-align: top;">

            </td>
        </tr>
    @endforeach
    @foreach($expenses as $expense)
        <tr>
            <td style="vertical-align: middle; border: 2px solid #AAA; color: #171D2D; width: auto; padding: 15px; font-size: 10px; vertical-align: top;">
                <div>1</div>
            </td>
            <td style="vertical-align: middle; border: 2px solid #AAA; color: #171D2D; width: auto; padding: 15px 10px; font-size: 10px; vertical-align: top;">
                <div><b>{{ $expense['type'] }}</b></div>
                {{ $expense['date'] }}
            </td>
            <td style="vertical-align: middle; border: 2px solid #AAA; color: #171D2D; width: auto; padding: 15px; font-size: 10px; vertical-align: top;">
                ${{ $expense['price'] }}
            </td>
        </tr>
    @endforeach
    @foreach($bonuses as $bonus)
        <tr>
            <td style="vertical-align: middle; border: 2px solid #AAA; color: #171D2D; width: auto; padding: 15px; font-size: 10px; vertical-align: top;">
                <div>1</div>
            </td>
            <td style="vertical-align: middle; border: 2px solid #AAA; color: #171D2D; width: auto; padding: 15px 10px; font-size: 10px; vertical-align: top;">
                {{ $bonus['type'] }}
            </td>
            <td style="vertical-align: middle; border: 2px solid #AAA; color: #171D2D; width: auto; padding: 15px; font-size: 10px; vertical-align: top;">
                ${{ $bonus['price'] }}
            </td>
        </tr>
    @endforeach
    <tr>
        <td style="text-align: center; border: 2px solid #AAA; color: #171D2D; width: auto; padding: 15px 10px 10px; font-size: 10px; vertical-align: top;">

        </td>
        <td style="border: 2px solid #AAA; color: #171D2D; width: auto; padding: 15px 10px; font-size: 10px; vertical-align: top;">
            <b>Total due:</b>
        </td>
        <td style="position:relative; border: 2px solid #AAA; color: #171D2D; width: auto; padding:  15px 20px; font-size: 10px; vertical-align: top;">
            <b>${{ $total }}</b>
            @if($is_paid)
                <div style="position: absolute; width:65px; height:65px; right:50px; top: -12px">
                    <img src="{{ asset('images/paid.svg') }}" width="65" height="65" />
                </div>
            @endif
        </td>
    </tr>
</table>
<div
    style="font-family: Arial, sans-serif; color: #171D2D; font-size: 10px; margin-top: 25px;">


    @if($billing_details)
        <div style="font-family: 'DejaVu Sans', sans-serif; color: #171D2D; font-size: 10px;">
            <h2 style="font-family: Arial, sans-serif; margin: 20px 0 20px 0; color: #00B67B; font-weight: 700; font-size: 17.5px; text-transform: uppercase;">
                Payment options
            </h2>
            <span>
                If you have any questions concerning this invoice,
                contact {{ $billing_details['phone'] }}
                {{ $billing_details['phones'] }} or email us at
            </span>
            <a style="font-size: 10px; color: #3639A6;" href="mailto:{{ $billing_details['email'] }}" target="_blank">
                {{ $billing_details['email'] }}
            </a>
            {!! $billing_details['details'] !!}
        </div>
    @endif
</div>

@if($terms_conditions)
    <div style="page-break-inside: avoid; font-family: Arial, sans-serif; color: #171D2D; font-size: 10px;">
        <h2 style="font-family: Arial, sans-serif; margin: 0 0 20px 0; color: #00B67B; font-weight: 700; font-size: 17.5px; text-transform: uppercase;">
            Terms and Conditions</h2>
        {!! nl2br($terms_conditions) !!}
    </div>
@endif
</body>
</html>
