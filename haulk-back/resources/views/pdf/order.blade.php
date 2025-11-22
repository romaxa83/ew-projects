<html>
<head>
<style>
body{font-family:DejaVu Sans; font-size:10px;}
table{width:100%;border-collapse:collapse;}
table,td{border:none;}
th,td{padding:10px;}
</style>
</head>
<body>

<table>
<tr>
    <td>Load ID: {{ $order->load_id }}</td>
    <td>{{ $order->status }}</td>
    <td>Price: {{ $order->payment->price }} ({{ $order->payment->terms }})</td>
</tr>
</table>

<h3>Vehicles</h3>

<table>
<tr>
    <td>#</td>
    <td>VIN</td>
    <td>Year</td>
    <td>Make</td>
    <td>Model</td>
    <td>Type</td>
    <td>Color</td>
    <td>Lot Number</td>
    <td>Price</td>
</tr>
@foreach($order->vehicles as $vehicle)
<tr>
    <td>{{ $loop->iteration }}</td>
    <td>{{ $vehicle->vin }}</td>
    <td>{{ $vehicle->year }}</td>
    <td>{{ $vehicle->make }}</td>
    <td>{{ $vehicle->model }}</td>
    <td>{{ $vehicle->type }}</td>
    <td>{{ $vehicle->color }}</td>
    <td></td>
    <td>${{ $vehicle->price }}</td>
</tr>
@endforeach
</table>

<table>
<tr>
    <td>
        <h3>ORIGIN</h3>
        @include('pdf.contact', ['contact' => $order->postProcessContact($order->pickup_contact)])
        <br />
        {{ $order->instructions }}
    </td>
    <td>
        <h3>DESTINATION</h3>
        @include('pdf.contact', ['contact' => $order->postProcessContact($order->delivery_contact)])
    </td>
</tr>
<tr>
    <td>
        <h3>Payment Information</h3>
        Price: {{ $order->payment->price }}<br />
        Terms: {{ $order->payment->terms }}<br />
        Invoice ID: {{ $order->payment->invoice_id }}<br />
        Invoice notes: {{ $order->payment->invoice_notes }}
    </td>
    <td>
        <h3>Shipper/Customer</h3>
        @include('pdf.contact', ['contact' => $order->postProcessContact($order->shipper_contact)])
    </td>
</tr>
</table>

<h3>Expenses</h3>

<table>
<tr>
    <td>#</td>
    <td>Date</td>
    <td>Type</td>
    <td>Price</td>
</tr>
@foreach($order->expenses as $expense)
<tr>
    <td>{{ $loop->iteration }}</td>
    <td>{{ $expense->date }}</td>
    <td>{{ $expense->type }}</td>
    <td>${{ $expense->price }}</td>
</tr>
@endforeach
</table>

</body>
</html>