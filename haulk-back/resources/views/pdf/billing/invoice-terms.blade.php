<!DOCTYPE html>
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
<body>

<div style="padding-bottom: 4px">Invoice #: {{ $invoice->id + 1000 }}</div>
<div style="padding-bottom: 16px">Invoice Date: {{\Illuminate\Support\Carbon::parse($invoice->created_at)->format('M j, Y')}}</div>
<div class="title" style="text-transform: uppercase">TERMS and CONDITIONS</div>
<!-- Ниже вставь разметку -->

@php echo $invoice->company->getTermsAndConditions() @endphp

<div style="
    text-align: right;
    color: lightgray;
    position: absolute;
    bottom: 0;
    right: 0;
">
    Page <span class="pagenum"></span>
</body>
</html>
