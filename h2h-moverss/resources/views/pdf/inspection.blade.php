@php
    $logo_path = public_path('images/logo/h2hmovers-logo.png');
    $logo_type = pathinfo($logo_path, PATHINFO_EXTENSION);
    $logo_data = file_get_contents($logo_path);
    $logo_base64 = 'data:image/' . $logo_type . ';base64,' . base64_encode($logo_data);
@endphp

<html>
<style>
    body {
        font-family: 'sans-serif';
        font-size: 14px;
    }

    .header {
        text-align: center;
    }

    .logo {
        width: 95px;
        height: 99px;
    }

    .main-info {
        margin-bottom: 2em;
        line-height: 1.4;
    }

    .main-info b {
        margin-right: 0.5em;
    }

    .signature-block {
        margin-top: 2em;
    }

    .signature-block h3 {
        margin: 0;
    }

    table.table {
        border-collapse: collapse;
    }

    table.table td {
        padding: 0 4px;
        border-bottom: 1px solid #333;
    }

    table.table tr:last-child td {
        border-bottom: none;
    }

    table.table .empty-cell {
        width: 32px;
        border-bottom: none;
    }

    .signature-image {
        width: 300px;
    }

    .signature-date {
        width: 130px;
        white-space: nowrap;
        vertical-align: bottom;
    }
</style>
<body>
<header class="header">
    <img class="logo" src="{{ $logo_base64 }}" alt="H2Hmovers logo">
    <h2>PREMISES AND TRUCK INSPECTION FORM</h2>
</header>
<div class="main-info">
    <b>Customer Name:</b>
    <span>{{ $client_name }}</span>
    <br />
    <b>Job Number:</b>
    <span>#{{ $model->order_id }}</span>
    <br />
    <b>Current Date: </b>
    <span>{{ $model->inspection_origin_signed_at ? Carbon\Carbon::parse($model->inspection_origin_signed_at)->format('D, M j, Y') : '' }}</span>
</div>
<p>Dear Valued Customer,</p>
<p>In order to provide you with the best possible service, it is important that you join our crew leader for a
    walk-through at the completion of loading and unloading.</p>
<p>When loading has been completed, please do a walk-through of your pickup location, and inspect to see that nothing
    has been left behind. When unloading at the final destination is completed, please do a walk-through of the truck
    and confirm that everything has been delivered to you.</p>
<p>This will ensure the successful completion of your relocation. Thank you for your cooperation. </p>
<div class="signature-block">
    <h3>At Origin:</h3>
    <p>I have inspected my residence and the building premises and confirm that my entire shipment is loaded on the
        truck. Additionally, there are no visible damages to the walls, floors, or ceilings inside the building, nor any
        visible damages outside the building.</p>
    <div class="sign">
        <table class="table">
            <tr>
                <td class="signature-image">
                    @isset($signature_origin_at_base_64)
                        <img
                            src="data:image/png;base64,{{ $signature_origin_at_base_64 }}"
                            width="200px"
                            height="117px"
                            alt="signature_origin_at"
                        />
                    @endisset
                </td>
                <td class="empty-cell"></td>
                <td class="signature-date">
                    {{ $model->inspection_origin_signed_at ? Carbon\Carbon::parse($model->inspection_origin_signed_at)->format('D, M j, Y') : '' }}
                </td>
            </tr>
            <tr>
                <td>Customer Signature</td>
                <td class="empty-cell"></td>
                <td>Date</td>
            </tr>
        </table>
    </div>
</div>
<div class="signature-block">
    <h3>At Destination:</h3>
    <p>I have inspected my residence, truck, and the building premises and confirm that everything is unloaded and
        brought up to my apartment, and nothing is left behind. Additionally, there are no visible damages to the walls,
        floors, or ceilings inside or outside the building.
    </p>
    <div class="sign">
        <table class="table">
            <tr>
                <td class="signature-image">
                    @isset($signature_destination_at_base_64)
                        <img
                            src="data:image/png;base64,{{ $signature_destination_at_base_64 }}"
                            width="200px"
                            height="117px"
                            alt="signature_destination_at"
                        />
                    @endisset
                </td>
                <td class="empty-cell"></td>
                <td class="signature-date">
                    {{ $model->inspection_destination_signed_at ? Carbon\Carbon::parse($model->inspection_destination_signed_at)->format('D, M j, Y') : '' }}
                </td>
            </tr>
            <tr>
                <td>Customer Signature</td>
                <td class="empty-cell"></td>
                <td>Date</td>
            </tr>
        </table>
    </div>
</div>
</body>
</html>
