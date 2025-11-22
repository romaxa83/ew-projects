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
        .title {
            font-size: 13px;
            font-weight: 700;
            line-height: 15px;
            color: #00B67B;
        }
        .pagenum:before {
            content: counter(page);
        }
    </style>
</head>
<body style="position: relative">

<div style="padding-bottom: 4px">Invoice #: {{ $order['number']}}</div>
<div style="padding-bottom: 16px">Invoice Date: {{ \Illuminate\Support\Carbon::now()->format(App\Foundations\Enums\DateTimeEnum::DateForDocs->value) }}</div>
<div class="title" style="text-transform: uppercase">PAYMENT DETAILS</div>
<!-- Ниже вставь разметку -->
@php echo $settings['payment_details'] @endphp
<div class="title" style="text-transform: uppercase">TERMS and CONDITIONS</div>
<!-- Ниже вставь разметку -->
@php echo $settings['terms_and_conditions'] @endphp

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
