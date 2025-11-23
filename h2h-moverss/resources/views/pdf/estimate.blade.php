<html>
<style>
    body {
        font-family: 'sans-serif';
        font-size: 14px;
    }

    .page-break {
        page-break-after: always;
    }

    .row {
        width: 100%;
    }

    .clear,
    .row:after {
        clear: both
    }

    .column,
    .columns {
        width: 100%;
        float: left;
        margin-bottom: 10px;
    }

    .medium-1 {
        width: 8.3333333333%;
    }

    .medium-2 {
        width: 16.6666666667%;
    }

    .medium-3 {
        width: 25%;
    }

    .medium-4 {
        width: 33.3333333333%;
    }

    .medium-5 {
        width: 41.6666666667%;
    }

    .medium-6 {
        width: 50%;
    }

    .medium-7 {
        width: 58.3333333333%;
    }

    .medium-8 {
        width: 66.6666666667%;
    }

    .medium-9 {
        width: 75%;
    }

    .medium-10 {
        width: 83.3333333333%;
    }

    .medium-11 {
        width: 91.6666666667%;
    }

    .medium-12 {
        width: 100%;
    }

    .mb-1 {
        margin-bottom: 10px;
    }

    .mt-1 {
        margin-top: 10px;
    }

    .mt-2 {
        margin-top: 20px;
    }

    .mt-4 {
        margin-top: 40px;
    }

    .ml-4 {
        margin-left: 40px;
    }

    .t-center {
        text-align: center;
    }

    .t-left {
        text-align: left !important;
    }

    .t-right {
        text-align: right;
    }

    .fr {
        float: right;
    }

    b, strong {
        font-weight: bold;
    }

    p {
        margin: 0;
    }

    h1,
    h2,
    h3,
    h4 {
        margin: 0;
    }

    .notice {
        background: #EDEDED;
        border: solid 1px #CCC;
        padding: 10px;
        margin: 10px 0;
    }

    .table td, .table th {
        border: 1px solid;
    }

    table.table {
        width: 100%;
        border-collapse: collapse;
        border: 1px solid;
        page-break-inside: auto;
    }

    table.table tr {
        page-break-inside:avoid;
        page-break-after:avoid;
    }

    table.table tr:nth-child(3) ~ tr {
        page-break-after:auto;
    }

    table.table-no-border {
        border: none !important;
    }


    .header .contact {
        line-height: 20px;
        margin-top: 40px;
    }

    .header .center span {
        display: block;
        padding-top: 10px;
        font-size: 16px;
        font-weight: bold;
        line-height: 20px;
    }

    .header .dates {
        margin-top: 40px;
        line-height: 25px;
        font-size: 15px;
    }

    .waypoints {
        width: 98%;
        border: solid 1px #CCC;
        padding: 10px 5px;
        margin: 10px 0;
        word-wrap: break-word;
        font-size: 12px;
    }

    .waypoints .key {
        font-weight: bold;
    }

    .waypoints .src,
    .waypoints .dst {
        margin: 5px 5px 10px;
    }

    .waypoints table {
        width: 100%;
    }

    .waypoints .src {
        border-right: 4px solid #ccc;
        padding-right: 8px;
        width: 48%;
        float: left;
    }

    .waypoints .dst {
        float: left;
        width: 48%;
    }

    .field {
        border-bottom: 1px solid #333;
    }

    .value {
        color: #555;
        text-align: center;
    }

    .value-color {
        color: #555;
    }

    .sign {
        margin-top: 50px;
        margin-left: 45px;
    }

    .copy {
        position: absolute;
        bottom: 0;
        font-size: 12px;
        text-align: center;
        width: 100%;
    }

    .border-bottom-0 {
        border-bottom: none !important;
    }

    .border-right-0 {
        border-right: none !important;;
    }

    .border-left-0 {
        border-left: none !important;;
    }

    /*.d-flex {*/
    /*    display: -webkit-box !important;*/
    /*    display: -ms-flexbox !important;*/
    /*    display: flex !important;*/
    /*}*/

    /*.flex-fill {*/
    /*    -webkit-box-flex: 1 !important;*/
    /*    -ms-flex: 1 1 auto !important;*/
    /*    flex: 1 1 auto !important;*/
    /*}*/

    /*.signature-date {*/
    /*    display: inline-block;*/
    /*    align-self: flex-end;*/
    /*    align-items: end;*/
    /*}*/
    .signature-table {
        table-layout: fixed;

    }

    .signature-table td {
        border: none !important;
    }

    .signature-date {
        width: 100px;
        white-space: nowrap;
        vertical-align: bottom;
    }

    .signature-image {
        width: 80%;
    }
</style>

<body>

<div class="row header">
    <div class="column medium-3 contact">
        Contact Mover<br/>
        at this address<br/>
        and phone<br/><br/>

        IL C.C. License Number
    </div>
    <div class="column medium-5 center">
        <h2>ESTIMATE OF CHARGES
            H2H MOVERS, INC.</h2>

        <span>
            4250 N Marine Dr<br/>
            CHICAGO, IL 60641<br/>
            Phone (773) 236-8797<br/>
            ILCC 184599 MC
        </span>
    </div>
    <div class="column medium-4 dates">
        <b>Pack Date:</b>
        <span
            class="value">{{ $data['dates']['packDate'] ? Carbon\Carbon::parse($data['dates']['packDate'])->format('D, M j, Y') : '' }}</span>
        <br/>
        <b>Pickup Date:</b>
        <span
            class="value">{{ $data['dates']['pickupDate'] ? Carbon\Carbon::parse($data['dates']['pickupDate'])->format('D, M j, Y') : '' }}</span>
        <br/>
        <b>Delivery Date:</b>
        <span
            class="value">{{ $data['dates']['deliveryDate'] ? Carbon\Carbon::parse($data['dates']['deliveryDate'])->format('D, M j, Y') : '' }}</span>
    </div>
    <div class="clear"></div>
</div>

<div class="waypoints">
    <div class="src">
        <h4>Origin:</h4>
        <table>
            <tr>
                <td class="key">Name</td>
                <td class="field value t-left" colspan="6">{{ $data['origin']['name'] }}</td>
            </tr>
            <tr>
                <td class="key">Address</td>
                <td class="field value t-left" colspan="6">{{ $data['origin']['address'] }}</td>
            </tr>
            <tr>
                <td class="key">City</td>
                <td class="field value t-left">{{ $data['origin']['city'] }}</td>
                <td class="key">St</td>
                <td class="field value t-left">{{ $data['origin']['state'] }}</td>
                <td class="key">Zip</td>
                <td class="field value t-left">{{ $data['origin']['zip'] }}</td>
            </tr>
            <tr>
                <td class="key">Phone</td>
                <td class="field value t-left" colspan="6">{{ !empty($data['origin']['phones']) ? implode(', ', $data['origin']['phones']) : '' }}</td>
            </tr>
            <tr>
                <td class="key">Email</td>
                <td class="field value t-left" colspan="6">{{ $data['origin']['email'] }}</td>
            </tr>

        </table>
    </div>
    <div class="dst">
        <h4>Destination:</h4>
        <table>
            <tr>
                <td class="key">Name</td>
                <td class="field value t-left" colspan="6">{{ $data['destination']['name'] }}</td>
            </tr>
            <tr>
                <td class="key">Address</td>
                <td class="field value t-left" colspan="6">{{ $data['destination']['address'] }}</td>
            </tr>
            <tr>
                <td class="key">City</td>
                <td class="field value t-left">{{ $data['destination']['city'] }}</td>
                <td class="key">St</td>
                <td class="field value t-left">{{ $data['destination']['state'] }}</td>
                <td class="key">Zip</td>
                <td class="field value t-left">{{ $data['destination']['zip'] }}</td>
            </tr>
            <tr>
                <td class="key">Phone</td>
                <td class="field value t-left" colspan="6">{{ $data['destination']['phone'] }}</td>
            </tr>
            <tr>
                <td class="key">Email</td>
                <td class="field value t-left" colspan="6">{{ $data['destination']['email'] }}</td>
            </tr>

        </table>
    </div>
    <div class="clear"></div>
</div>


<div class="notice mt-2">
    <h4>IMPORTANT NOTICE
        This is not a binding estimate.</h4>
    <p><u>This estimate covers only the articles and services
            listed. It is not a warranty or representation that the
            actual charges will not exceed the amount of the
            estimate. Any additional articles and services added
            after the written estimate is executed may result in
            additional charges.</u></p>
    <p>Mover will collect charges computed on the basis of
        rates shown in their lawfully published tariffs at the
        time of the move, regardless of prior rate quotations
        or estimates made with the mover or its agents.
        Transportation charges are based upon either the
        weight of the goods transported or the time
        consumed in transporting the shipment. Special
        arrangements can be made with the mover for
        expedited services to guarantee a delivery date for
        intercity shipments, for which an additional charge
        normally will apply. As determined by the mover's
        tariff, rates may be computed by the hour for moves
        35 miles or less from origin to destination or wholly
        within the area encompassed by the counties of Cook,
        Du Page, Kane, Kendall, Lake, McHenry and Will.
        Moves over 35 miles (if outside above named counties
        must be computed on a weight-mileage basis.</p>
</div>

<table class="table table-no-border mt-2">
    <colgroup>
        <col style="width: 17.6827%;">
        <col style="width: 5.47853%;">
        <col style="width: 7.51174%;">
        <col style="width: 7.98122%;">
        <col style="width: 7.82473%;">
        <col style="width: 7.51174%;">
        <col style="width: 7.98122%;">
        <col style="width: 7.82473%;">
        <col style="width: 7.51174%;">
        <col style="width: 7.98122%;">
        <col style="width: 4.85133%;">
        <col style="width: 10.0156%;">
    </colgroup>
    <tbody>
    <tr>
        <td colspan="11" class="t-center">LOCAL MOVE<br><small>(hourly rates)</small></td>
        <td class="t-center" rowspan="2">Total<br>Charges<br>$$</td>
    </tr>
    <tr>
        <td rowspan="2"># Chargeable Hours</td>
        <td rowspan="2" class="t-center">Qty</td>
        <td colspan="3">Straight Time</td>
        <td colspan="3">Overtime</td>
        <td colspan="3">Sunday / Holiday</td>
    </tr>
    <tr>
        <td class="t-center"># Hrs</td>
        <td class="t-center">Rate</td>
        <td class="t-center">Ext.</td>
        <td class="t-center"># Hrs</td>
        <td class="t-center">Rate</td>
        <td class="t-center">Ext.</td>
        <td class="t-center"># Hrs</td>
        <td class="t-center">Rate</td>
        <td class="t-center">Ext.</td>
        <td></td>
    </tr>
    <tr>
        <td># Men</td>
        <td class="t-center value">{{ $data['local_move']['men']['qty'] }}</td>
        <td class="value">{{ $data['local_move']['men']['straight']['hours'] }}</td>
        <td class="value">{{ $data['local_move']['men']['straight']['rate'] ? '$'.$data['local_move']['men']['straight']['rate'] : '' }}</td>
        <td class="value">{{ $model->rateGroupEstimate('men', 'straight') ? '$'.$model->rateGroupEstimate('men', 'straight'):'' }}</td>
        <td class="value">{{ $data['local_move']['men']['overtime']['hours'] }}</td>
        <td class="value">{{ $data['local_move']['men']['overtime']['rate'] ? '$'.$data['local_move']['men']['overtime']['rate'] : '' }}</td>
        <td class="value">{{ $model->rateGroupEstimate('men', 'overtime') ? '$'.$model->rateGroupEstimate('men', 'overtime'):'' }}</td>
        <td class="value">{{ $data['local_move']['men']['holiday']['hours'] }}</td>
        <td class="value">{{ $data['local_move']['men']['holiday']['rate'] ? '$'.$data['local_move']['men']['holiday']['rate']: '' }}</td>
        <td class="value">{{ $model->rateGroupEstimate('men', 'holiday') ? '$'.$model->rateGroupEstimate('men', 'holiday'):'' }}</td>
        <td class="value">{{ $model->rateGroupTotalEstimate('men') ? '$'.$model->rateGroupTotalEstimate('men'):'' }}</td>
    </tr>
    <tr>
        <td># Trucks</td>
        <td class="t-center value">{{ $data['local_move']['trucks']['qty'] }}</td>
        <td class="value">{{ $data['local_move']['trucks']['straight']['hours'] }}</td>
        <td class="value">{{ $data['local_move']['trucks']['straight']['rate'] ? '$'.$data['local_move']['trucks']['straight']['rate']: '' }}</td>
        <td class="value">{{ $model->rateGroupEstimate('trucks', 'straight') ? '$'.$model->rateGroupEstimate('trucks', 'straight'):'' }}</td>
        <td class="value">{{ $data['local_move']['trucks']['overtime']['hours'] }}</td>
        <td class="value">{{ $data['local_move']['trucks']['overtime']['rate'] ? '$'.$data['local_move']['trucks']['overtime']['rate']: '' }}</td>
        <td class="value">{{ $model->rateGroupEstimate('trucks', 'overtime') ? '$'.$model->rateGroupEstimate('trucks', 'overtime'):'' }}</td>
        <td class="value">{{ $data['local_move']['trucks']['holiday']['hours'] }}</td>
        <td class="value">{{ $data['local_move']['trucks']['holiday']['rate'] ? '$'.$data['local_move']['trucks']['holiday']['rate']: '' }}</td>
        <td class="value">{{ $model->rateGroupEstimate('trucks', 'holiday') ? '$'.$model->rateGroupEstimate('trucks', 'holiday'):'' }}</td>
        <td class="value">{{ $model->rateGroupTotalEstimate('trucks') ? '$'.$model->rateGroupTotalEstimate('trucks'):'' }}</td>
    </tr>
    <tr>
        <td colspan="2">Travel Time:</td>
        <td colspan="4" class="value t-left"># Hours: {{ $data['local_move']['travel_time']['hours'] }}</td>
        <td colspan="5" class="value t-left">Rate: {{ $data['local_move']['travel_time']['rate'] }}</td>
        <td class="value">{{ $model->travelTimeTotalEstimate() ? '$'.$model->travelTimeTotalEstimate():'' }}</td>
    </tr>
    <tr>
        <td colspan="2">Mileage Charge:</td>
        <td colspan="4" class="value t-left"># Miles: {{ $data['local_move']['mileage_charge']['miles'] }}</td>
        <td colspan="5" class="value t-left">$ per mile: {{ $data['local_move']['mileage_charge']['rate'] }}</td>
        <td class="value">{{ $model->mileageTotalEstimate() ? '$'.$model->mileageTotalEstimate():'' }}</td>
    </tr>
    <tr>
        <td colspan="11">Valuation Charge:</td>
        <td class="value">{{ $data['local_move']['valuation_charge'] ? '$'.$data['local_move']['valuation_charge']:'' }}</td>
    </tr>
    <tr>
        <td colspan="11">Other Charge:</td>
        <td class="value">{{ $data['local_move']['other_charge'] ? '$'.$data['local_move']['other_charge']:'' }}</td>
    </tr>
    <tr class="border-bottom-0 border-left-0 border-right-0">
        <td colspan="11" class="border-right-0 border-left-0 border-bottom-0" style="text-align: right;">
            <h3 class="mt-1" style="margin-right: 10px;">TOTAL LOCAL CHARGES:</h3>
        </td>
        <td class="border-right-0 border-left-0 border-bottom-0 value">
            <h3 class="mt-1">{{ '$'.(
                ($model->rateGroupTotalEstimate('men') ?: 0) +
                ($model->rateGroupTotalEstimate('trucks') ?: 0) +
                ($model->travelTimeTotalEstimate() ?: 0) +
                ($model->mileageTotalEstimate() ?: 0) +
                ($data['local_move']['valuation_charge'] ?: 0) +
                ($data['local_move']['other_charge'] ?: 0)
            ) }}</h3></td>
    </tr>
    </tbody>
</table>

{{--TODO if packing rows > 5 add <div class="page-break"></div> + after ? --}}
<h3 class="mt-2 t-center">PACKING CHARGES</h3>
<table class="table table-no-border">
    <colgroup>
        <col style="width: 13.4585%;">
        <col style="width: 8.92019%;">
        <col style="width: 8.92019%;">
        <col style="width: 8.92019%;">
        <col style="width: 8.92019%;">
        <col style="width: 8.92019%;">
        <col style="width: 8.92019%;">
        <col style="width: 8.92019%;">
        <col style="width: 8.92019%;">
        <col style="width: 4.85133%;">
        <col style="width: 10.0156%;">
    </colgroup>
    <tbody>
    <tr>
        <td rowspan="2">Container<br>Description</td>
        <td colspan="3">Container Charge</td>
        <td colspan="3">Packing</td>
        <td colspan="3">Unpacking</td>
        <td rowspan="2" class="t-center">Charges<br>$$</td>
    </tr>
    <tr>
        <td class="t-center">Qty</td>
        <td class="t-center">Rate</td>
        <td class="t-center">Ext.</td>
        <td class="t-center">Qty</td>
        <td class="t-center">Rate</td>
        <td class="t-center">Ext.</td>
        <td class="t-center">Qty</td>
        <td class="t-center">Rate</td>
        <td class="t-center">Ext.</td>
    </tr>
    @foreach($model->packingChargesEstimate()['records'] as $v)
        <tr>
            <td class="value t-left">{{ $v['title'] }}</td>
            <td class="value">{{ $v['charge']['qty'] }}</td>
            <td class="value">{{ $v['charge']['rate'] }}</td>
            <td class="value">{{ $v['charge']['sum'] ? '$'.$v['charge']['sum']:'' }}</td>

            <td class="value">{{ $v['packing']['qty'] }}</td>
            <td class="value">{{ $v['packing']['rate'] }}</td>
            <td class="value">{{ $v['packing']['sum'] ? '$'.$v['packing']['sum']:'' }}</td>

            <td class="value">{{ $v['unpacking']['qty'] }}</td>
            <td class="value">{{ $v['unpacking']['rate'] }}</td>
            <td class="value">{{ $v['unpacking']['sum'] ? '$'.$v['unpacking']['sum']:'' }}</td>
            <td class="value">{{ $v['sum'] ? '$'.$v['sum']:'' }}</td>
        </tr>
    @endforeach

    <tr>
        <td colspan="10" class="t-right border-right-0 border-left-0 border-bottom-0">
            <h3 class="mt-1">TOTAL PACKING CHARGES:</h3>
            <span>(see attached packing addendum if appropriate)</span>
        </td>
        <td class="value mt-1 border-right-0 border-left-0 border-bottom-0">
            <h3 class="mt-1">${{ $model->packingChargesEstimate()['total'] }}</h3>
        </td>
    </tr>
    </tbody>
</table>

<div class="notice">
    <h4>Important Notice about Payment</h4>
    <p>In accordance with Illinois law, unless the mover has
        agreed in writing to credit arrangements, you will be
        expected to pay for the move at delivery. Payment
        must be in cash, money order or cashier's check. The
        mover is not required to accept a personal check or
        credit card.</p>
    <p>The mover has made every effort to estimate
        accurately the charges for your move, <u>based upon the
            information you have provided</u>. If the actual tariff
        charges on the day of the move exceed the charges
        contained in this written estimate, you will be
        required to estimate, you will be required to pay at
        delivery the amount of the estimated cost PLUS 10
        percent, at which time the mover will release your
        goods. You are required by law to pay within 30 days
        of delivery the balance of the total actual charges.</p>
</div>


<h3 class="mb-1 mt-2 t-center">Other Charges</h3>

<table class="table table-no-border">
    <colgroup>
        <col style="width: 25%;">
        <col style="width: 25%;">
        <col style="width: 25%;">
        <col style="width: 25%;">
    </colgroup>
    <tbody>
    <tr>
        <td colspan="3">Description</td>
        <td class="t-center">Charges $$</td>
    </tr>
    <tr>
        <td colspan="3">Extra pickup or delivery charge:</td>
        <td class="value">{{ $model->orderChargesEstimate()['extra_charge'] ? '$'.$model->orderChargesEstimate()['extra_charge']:'' }}</td>
    </tr>
    <tr>
        <td colspan="3">Hoisting or Piano Charge:</td>
        <td class="value">{{ $model->orderChargesEstimate()['hoisting'] ? '$'.$model->orderChargesEstimate()['hoisting']:'' }}</td>
    </tr>
    <tr>
        <td colspan="4">Stair Carry, Elevator, Long Carry:</td>
    </tr>
    <tr>
        <td class="value-color">Origin: ${{ $model->orderChargesEstimate()['stair_carry']['origin'] }}</td>
        <td colspan="2" class="value-color">Destination:
            ${{ $model->orderChargesEstimate()['stair_carry']['destination'] }}</td>
        <td class="value">{{ $model->orderChargesEstimate()['stair_carry']['sum'] ? '$'.$model->orderChargesEstimate()['stair_carry']['sum']:'' }}</td>
    </tr>
    <tr>
        <td colspan="4">Extra Labor:</td>
    </tr>
    <tr>
        <td># Personnel: <span class="value-color">{{ $model->orderChargesEstimate()['extra_labor']['personnel'] }}</span></td>
        <td># Hours: <span class="value-color">{{ $model->orderChargesEstimate()['extra_labor']['hours'] }}</span></td>
        <td>Rate: <span class="value-color">${{ $model->orderChargesEstimate()['extra_labor']['rate'] }}</span></td>
        <td class="value">{{ $model->orderChargesEstimate()['extra_labor']['sum'] ? '$'.$model->orderChargesEstimate()['extra_labor']['sum']:'' }}</td>
    </tr>
    <tr>
        <td colspan="3">Heavy Item Charge:</td>
        <td class="value">{{ $model->orderChargesEstimate()['bulky_item_charge'] ? '$'.$model->orderChargesEstimate()['bulky_item_charge']:'' }}</td>
    </tr>
    <tr>
        <td colspan="3">Trip Transit Insurance Policy Charge:</td>
        <td class="value">{{ $model->orderChargesEstimate()['trip_transit_insurance'] ? '$'.$model->orderChargesEstimate()['trip_transit_insurance']:'' }}</td>
    </tr>
    <tr>
        <td colspan="3">Other:</td>
        <td class="value">{{ $model->orderChargesEstimate()['other']['total'] ? '$'.$model->orderChargesEstimate()['other']['total']:'' }}</td>
    </tr>
    @if($model->orderChargesEstimate()['other']['total'])
        @foreach($model->orderChargesEstimate()['other']['records'] as $v)
            <tr>
                <td colspan="2" class="value-color">- {{ $v['title'] }}</td>
                <td class="value">${{ $v['rate'] }}</td>
                <td></td>
            </tr>
        @endforeach
    @endif
    <tr>
        <td colspan="3">Total:</td>
        <td class="value">${{ $model->orderChargesEstimate()['total'] }}</td>
    </tr>
    <tr class="border-bottom-0 border-right-0 border-left-0">
        <td colspan="3" class="border-bottom-0 border-right-0 border-left-0">
            <h3 class="mt-1 t-right">TOTAL ESTIMATED CHARGES:</h3>
        </td>
        <td class="value border-bottom-0 border-right-0 border-left-0">
            <h3 class="mt-1">${{ $model->totalSumEstimate() }}</h3>
        </td>
    </tr>
    </tbody>
</table>

{{--<h3 class="mt-1 t-right">TOTAL ESTIMATED CHARGES: ${{ $model->totalSumEstimate() }}</h3>--}}

<div class="sign">
    {{--    <img src="{{ $signature_estimator }}" width="200px"/>--}}

    <table class="table table-no-border signature-table">
        <tr>
            <td class="signature-image">@isset($signature_estimator_base64)<img src="data:image/png;base64,{{ $signature_estimator_base64 }}" width="200px"/>@endisset</td>
            <td class="signature-date">{{ $model->estimate_signed_at ? Carbon\Carbon::parse($model->estimate_signed_at)->format('D, M j, Y') : '' }}</td>
        </tr>
    </table>
    <div class="field"></div>
    Estimator's Signature Code
    <div style="float: right">#&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Date</div>
</div>

<div class="sign">
    {{--    <img src="{{ $signature_customer }}" width="200px"/>--}}
    @isset($signature_estimator_base64)<img src="data:image/png;base64,{{ $signature_customer_base64 }}" width="200px"/>@endisset
    <div class="field"></div>
    <div style="float: left">Customer's Signature</div>
    <div style="width: 50%;float: right">
        <input type="checkbox" @if($data['consumer_brochure']) checked @endif  style="position: relative;top: 5px"> Customer has been provided with IL C.C.
        required consumer brochure
    </div>
</div>


<div class="copy">
    Copyright 1998 Illinois Mover's and Warehousemen's Association Rev 10/06 Form # 2208-EST LTR
</div>


</body>

</html>
