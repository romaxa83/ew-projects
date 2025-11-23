<html>
<style>
    body {
        font-family: 'sans-serif';
        font-size: 14px;
    }

    footer {
        position: fixed;
        bottom: -0.5cm;
        left: 0cm;
        right: 0cm;
        height: 0.5cm;

        /** Extra personal styles **/
        /*background-color: #03a9f4;*/
        /*color: white;*/
        text-align: center;
        /*line-height: 1.5cm;*/
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

    .notice-bg {
        background: #EDEDED;
        border: solid 1px #CCC;
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

    .table-gray td, .table th {
        border-color: #CCC;
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

    table.table-no-border, table.table-no-cells-border {
        border: none !important;
    }

    .table-no-cells-border td {
        border: none;
    }

    .table-no-cells-border tr {
        border: none;
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

    .border-top-0 {
        border-top: none !important;;
    }
    .border-0 {
        border: none !important;
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

    .text-center {
        text-align: center;
    }

    .header-black {
        background-color: #000000;
        color: #FFFFFF;
        font-weight: bold;
    }

    h2, h5, h3, h4 {
        margin-bottom: 0px;
    }

    .signature-field-bottom-text {
        font-size: 10px;
    }

    .vertical-align-bottom {
        vertical-align: bottom;
    }

    .vertical-align-top {
        vertical-align: top;
    }

</style>

<body>

<div class="text-center">
    <h5>UNIFORM HOUSEHOLD GOODS BILL OF LADING & FREIGHT BILL</h5>
    <h2>H2H MOVERS, INC.</h2>
    <div>4250 N Marine Dr, Chicago, IL 60613</div>
    <div>PHONE: (773) 236-8797</div>
</div>

<div style="position: absolute; top: 40px; right:15px ">
    <table class="table table-gray notice-bg" style="width: 180px;">
        <tr>
            <td>Vehicle No</td>
            <td>{{ $data['numbers']['vehicle_no'] ?? '' }}</td>
        </tr>
        <tr>
            <td>ILCC</td>
            <td>184599 MC</td>
        </tr>
        <tr>
            <td>IL TARIFF No</td>
            <td>1-U</td>
        </tr>
    </table>
</div>

<div class="notice mt-1">
    <p>RECEIVED, subject to classifications, tariffs, rules and regulations including all terms printed or stamped hereon or on the reverse side hereof in
        effect
        on the date of issue of this bill of lading.</p>
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
                <td class="field value t-left" colspan="6">{{ $data['origin']['phone'] }}</td>
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

<table class="table mt-2">
    <tr>
        <td class="notice-bg t-center">
            AGREED PICKUP DATE
        </td>
        <td class="notice-bg t-center">
            ACTUAL PICKUP DATE
        </td>
        <td class="notice-bg t-center">
            AGREED DELIVERY DATE
        </td>
        <td class="notice-bg t-center">
            AGREED PACK DATE
        </td>
    </tr>
    <tr>
        <td class="value">
            &nbsp;{{ $data['dates']['agreedPickupDate'] ? Carbon\Carbon::parse($data['dates']['agreedPickupDate'])->format('D, M j, Y') : '' }}
        </td>
        <td class="value">
            &nbsp;{{ $data['dates']['actualPickupDate'] ? Carbon\Carbon::parse($data['dates']['actualPickupDate'])->format('D, M j, Y') : '' }}
        </td>
        <td class="value">
            &nbsp;{{ $data['dates']['agreedDeliveryDate'] ? Carbon\Carbon::parse($data['dates']['agreedDeliveryDate'])->format('D, M j, Y') : '' }}
        </td>
        <td class="value">
            &nbsp;{{ $data['dates']['agreedPackDate'] ? Carbon\Carbon::parse($data['dates']['agreedPackDate'])->format('D, M j, Y') : '' }}
        </td>
    </tr>
</table>

@if($model->bolHasOldTeams())
    <table class="table mt-2 table-no-border t-center">
        <thead class="notice-bg">
        <tr>
            <td colspan="8" class="header-black">LOCAL MOVING</td>
        </tr>
        <tr>
            <td rowspan="2" class="t-left" width="25%"># Chargeable Hours</td>
            <td rowspan="2">Qty</td>
            <td colspan="5">Straight Time</td>
            <td rowspan="2">Charges<br>$$</td>
        </tr>
        <tr>
            <td>Start time</td>
            <td>End time</td>
            <td># Hrs</td>
            <td>Rate</td>
            <td>Ext.</td>
        </tr>
        </thead>
        <tbody>
        @foreach($model->bolTeams() as $team)
        <tr>
            <td class="t-left">Team {{ $loop->iteration }}</td>
            <td>{{ $team['qty'] }}</td>
            <td>{{ implode(':', $team['startTime']) }}</td>
            <td>{{ implode(':', $team['endTime']) }}</td>
            <td>{{ $team['hours'] }}</td>
            <td>{{ $model->formatPrice($team['rate']) }}</td>
            <td>{{ $model->formatPrice($team['hours'] * $team['rate']) }}</td>
            <td>{{ $model->formatPrice($team['hours'] * $team['rate']) }}</td>
        </tr>
        @endforeach
        </tbody>

        <tbody>
        <tr>
            <td class="t-left"># Trucks</td>
            <td class="t-center">{{ $data['local_move']['trucks']['qty'] }}</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td>{{ $model->formatPrice($model->rateGroupTotalBol('trucks')) }}</td>
        </tr>
        <tr>
            <td colspan="2" class="t-left">Travel Time:</td>
            <td colspan="2" class="t-left"># Hours: {{ $data['local_move']['travel_time']['hours'] }}</td>
            <td colspan="3" class="t-left">Rate: {{ $data['local_move']['travel_time']['rate'] }}</td>
            <td>{{ $model->formatPrice($model->travelTimeTotalBol()) }}</td>
        </tr>
        <tr>
            <td colspan="2" class="t-left">Mileage Charge:</td>
            <td colspan="2" class="t-left"># Miles: {{ $data['local_move']['mileage_charge']['miles'] }}</td>
            <td colspan="3" class="t-left">$ per mile: {{ $data['local_move']['mileage_charge']['rate'] }}</td>
            <td>{{ $model->formatPrice($model->mileageTotalBol()) }}</td>
        </tr>
        <tr>
            <td colspan="7" class="t-left">Valuation Charge:</td>
            <td>{{ $model->formatPrice($data['local_move']['valuation_charge']) }}</td>
        </tr>
        <tr>
            <td colspan="7" class="t-left">Other Charge:</td>
            <td>{{ $model->formatPrice($data['local_move']['other_charge']) }}</td>
        </tr>
        </tbody>
        <tfoot>
        <tr class="border-bottom-0 border-left-0 border-right-0">
            <td colspan="7" class="border-right-0 border-left-0 border-bottom-0" style="text-align: right;">
                <h3 class="mt-1" style="margin-right: 10px;">TOTAL LOCAL CHARGES:</h3>
            </td>

            <td class="border-right-0 border-left-0 border-bottom-0 value">
                <h3 class="mt-1">{{ $model->formatPrice(
                    ($model->totalBolTeams()) +
                    ($model->rateGroupTotalBol('trucks') ?: 0) +
                    ($model->travelTimeTotalBol() ?: 0) +
                    ($model->mileageTotalBol() ?: 0) +
                    ($data['local_move']['valuation_charge'] ?: 0) +
                    ($data['local_move']['other_charge'] ?: 0)
                ) }}</h3>
            </td>
        </tr>
        </tfoot>
    </table>
@elseif($model->bolHasNewTeams())
    <table class="table mt-2 table-no-border t-center">
        <thead class="notice-bg">
        <tr>
            <td colspan="10" class="header-black">LOCAL MOVING</td>
        </tr>
        <tr>
            <td rowspan="2" class="t-left" width="25%"># Chargeable Hours</td>
            <td rowspan="2">Qty</td>
            <td colspan="2">Straight Time</td>
            <td colspan="2">Off clock</td>
            <td rowspan="2"># Working Hrs</td>
            <td rowspan="2">Rate</td>
            <td rowspan="2">Ext.</td>
            <td rowspan="2">Charges<br>$$</td>
        </tr>
        <tr>
            <td>Start time</td>
            <td>End time</td>
            <td>Start time</td>
            <td>End time</td>
        </tr>
        </thead>
        <tbody>
        @foreach($model->bolTeams() as $record)
            <tr>
                <td class="t-left">Team #{{ $loop->iteration }}</td>
                <td>{{ $record['qty'] }}</td>
                <td>{{ implode(':', $record['payTime']['start']) }}</td>
                <td>{{ implode(':', $record['payTime']['end']) }}</td>
                <td>{{ implode(':', $record['freeTime']['start']) }}</td>
                <td>{{ implode(':', $record['freeTime']['end']) }}</td>
                <td>{{ $model->teamWorkingHrs($record) }}</td>
                <td>{{ $model->formatPrice($record['rate']) }}</td>
                <td>{{ $model->formatPrice($model->teamWorkingCharge($record)) }}</td>
                <td>{{ $model->formatPrice($model->teamWorkingCharge($record)) }}</td>
            </tr>
        @endforeach
        @foreach($model->bolPackingTimes() as $record)
            <tr>
                <td class="t-left" colspan="2">Packing Time #{{ $loop->iteration }}</td>
                <td>{{ implode(':', $record['time']['start']) }}</td>
                <td>{{ implode(':', $record['time']['end']) }}</td>
                <td colspan="2">&nbsp;</td>
                <td>{{ $record['hours'] }}</td>
                <td>{{ $model->formatPrice($record['rate'], true) }}</td>
                <td>{{ $model->formatPrice($record['total'], true) }}</td>
                <td>{{ $model->formatPrice($record['total'], true) }}</td>
            </tr>
        @endforeach
        </tbody>

        <tbody>
        <tr>
            <td class="t-left"># Trucks</td>
            <td class="t-center">{{ $data['local_move']['trucks']['qty'] }}</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td>{{ $model->formatPrice($model->rateGroupTotalBol('trucks')) }}</td>
        </tr>
        <tr>
            <td colspan="2" class="t-left">Travel Time:</td>
            <td colspan="4" class="t-left"># Hours: {{ $data['local_move']['travel_time']['hours'] }}</td>
            <td colspan="3" class="t-left">Rate: {{ $data['local_move']['travel_time']['rate'] }}</td>
            <td>{{ $model->formatPrice($model->travelTimeTotalBol()) }}</td>
        </tr>
        <tr>
            <td colspan="2" class="t-left">Mileage Charge:</td>
            <td colspan="4" class="t-left"># Miles: {{ $data['local_move']['mileage_charge']['miles'] }}</td>
            <td colspan="3" class="t-left">$ per mile: {{ $data['local_move']['mileage_charge']['rate'] }}</td>
            <td>{{ $model->formatPrice($model->mileageTotalBol()) }}</td>
        </tr>
        <tr>
            <td colspan="9" class="t-left">Valuation Charge:</td>
            <td>{{ $model->formatPrice($data['local_move']['valuation_charge']) }}</td>
        </tr>
        <tr>
            <td colspan="9" class="t-left">Other Charge:</td>
            <td>{{ $model->formatPrice($data['local_move']['other_charge']) }}</td>
        </tr>
        </tbody>
        <tfoot>
        <tr class="border-bottom-0 border-left-0 border-right-0">
            <td colspan="9" class="border-right-0 border-left-0 border-bottom-0" style="text-align: right;">
                <h3 class="mt-1" style="margin-right: 10px;">TOTAL LOCAL CHARGES:</h3>
            </td>

            <td class="border-right-0 border-left-0 border-bottom-0 value">
                <h3 class="mt-1">{{ $model->formatPrice(
                    ($model->totalBolTeams()) +
                    ($model->totalBolPackingTimes()) +
                    ($model->rateGroupTotalBol('trucks') ?: 0) +
                    ($model->travelTimeTotalBol() ?: 0) +
                    ($model->mileageTotalBol() ?: 0) +
                    ($data['local_move']['valuation_charge'] ?: 0) +
                    ($data['local_move']['other_charge'] ?: 0)
                ) }}</h3>
            </td>
        </tr>
        </tfoot>
    </table>
@else

    @if($model->bolHasStraightTimeRange())
    <table class="mt-1">
        <tbody>
            <tr>
                <td>Start time: {{ $model->bolStraightTimeStart() }}</td>
                <td>&nbsp;&nbsp;&nbsp;&nbsp;</td>
                <td>End time: {{ $model->bolStraightEndTime() }}</td>
            </tr>
        </tbody>
    </table>
    @endif

    <table class="table mt-2 table-no-border">
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
            <td colspan="12" class="header-black t-center">LOCAL MOVING</td>
        </tr>
        <tr>
            <td rowspan="2" class="notice-bg t-center"># Chargeable Hours</td>
            <td rowspan="2" class="notice-bg t-center">Qty</td>
            <td colspan="3" class="notice-bg t-center">Straight Time</td>
            <td colspan="3" class="notice-bg t-center">Overtime</td>
            <td colspan="3" class="notice-bg t-center">Sunday / Holiday</td>
            <td rowspan="2" class="notice-bg t-center">Charges<br>$$</td>
        </tr>
        <tr>
            <td class="notice-bg t-center"># Hrs</td>
            <td class="notice-bg t-center">Rate</td>
            <td class="notice-bg t-center">Ext.</td>
            <td class="notice-bg t-center"># Hrs</td>
            <td class="notice-bg t-center">Rate</td>
            <td class="notice-bg t-center">Ext.</td>
            <td class="notice-bg t-center"># Hrs</td>
            <td class="notice-bg t-center">Rate</td>
            <td class="notice-bg t-center">Ext.</td>
        </tr>
        <tr>
            <td># Men</td>
            <td class="t-center value">{{ $data['local_move']['men']['qty'] }}</td>
            <td class="value">{{ $data['local_move']['men']['straight']['hours'] }}</td>
            <td class="value">{{ $model->formatPrice($data['local_move']['men']['straight']['rate']) }}</td>
            <td class="value">{{ $model->formatPrice($model->rateGroupBol('men', 'straight')) }}</td>
            <td class="value">{{ $data['local_move']['men']['overtime']['hours'] }}</td>
            <td class="value">{{ $model->formatPrice($data['local_move']['men']['overtime']['rate']) }}</td>
            <td class="value">{{ $model->formatPrice($model->rateGroupBol('men', 'overtime')) }}</td>
            <td class="value">{{ $data['local_move']['men']['holiday']['hours'] }}</td>
            <td class="value">{{ $model->formatPrice($data['local_move']['men']['holiday']['rate']) }}</td>
            <td class="value">{{ $model->formatPrice($model->rateGroupBol('men', 'holiday')) }}</td>
            <td class="value">{{ $model->formatPrice($model->rateGroupTotalBol('men')) }}</td>
        </tr>
        <tr>
            <td># Trucks</td>
            <td class="t-center value">{{ $data['local_move']['trucks']['qty'] }}</td>
            <td class="value">{{ $data['local_move']['trucks']['straight']['hours'] }}</td>
            <td class="value">{{ $model->formatPrice($data['local_move']['trucks']['straight']['rate']) }}</td>
            <td class="value">{{ $model->formatPrice($model->rateGroupBol('trucks', 'straight')) }}</td>
            <td class="value">{{ $data['local_move']['trucks']['overtime']['hours'] }}</td>
            <td class="value">{{ $model->formatPrice($data['local_move']['trucks']['overtime']['rate']) }}</td>
            <td class="value">{{ $model->formatPrice($model->rateGroupBol('trucks', 'overtime')) }}</td>
            <td class="value">{{ $data['local_move']['trucks']['holiday']['hours'] }}</td>
            <td class="value">{{ $model->formatPrice($data['local_move']['trucks']['holiday']['rate']) }}</td>
            <td class="value">{{ $model->formatPrice($model->rateGroupBol('trucks', 'holiday')) }}</td>
            <td class="value">{{ $model->formatPrice($model->rateGroupTotalBol('trucks')) }}</td>
        </tr>
        <tr>
            <td colspan="2">Travel Time:</td>
            <td colspan="4" class="value t-left"># Hours: {{ $data['local_move']['travel_time']['hours'] }}</td>
            <td colspan="5" class="value t-left">Rate: {{ $data['local_move']['travel_time']['rate'] }}</td>
            <td class="value">{{ $model->formatPrice($model->travelTimeTotalBol()) }}</td>
        </tr>
        <tr>
            <td colspan="2">Mileage Charge:</td>
            <td colspan="4" class="value t-left"># Miles: {{ $data['local_move']['mileage_charge']['miles'] }}</td>
            <td colspan="5" class="value t-left">$ per mile: {{ $data['local_move']['mileage_charge']['rate'] }}</td>
            <td class="value">{{ $model->formatPrice($model->mileageTotalBol()) }}</td>
        </tr>
        <tr>
            <td colspan="11">Valuation Charge:</td>
            <td class="value">{{ $model->formatPrice($data['local_move']['valuation_charge']) }}</td>
        </tr>
        <tr>
            <td colspan="11">Other Charge:</td>
            <td class="value">{{ $model->formatPrice($data['local_move']['other_charge']) }}</td>
        </tr>
        <tr class="border-bottom-0 border-left-0 border-right-0">
            <td colspan="11" class="border-right-0 border-left-0 border-bottom-0" style="text-align: right;">
                <h3 class="mt-1" style="margin-right: 10px;">TOTAL LOCAL CHARGES:</h3>
            </td>

            <td class="border-right-0 border-left-0 border-bottom-0 value">
                <h3 class="mt-1">{{ $model->formatPrice(
                    ($model->rateGroupTotalBol('men') ?: 0) +
                    ($model->rateGroupTotalBol('trucks') ?: 0) +
                    ($model->travelTimeTotalBol() ?: 0) +
                    ($model->mileageTotalBol() ?: 0) +
                    ($data['local_move']['valuation_charge'] ?: 0) +
                    ($data['local_move']['other_charge'] ?: 0)
                ) }}</h3>
            </td>
        </tr>
        </tbody>
    </table>
@endif

{{--
<table class="table mt-2 table-no-border">
    <tbody>
    <tr>
        <td colspan="8" class="header-black t-center">LOCAL MOVING</td>
    </tr>

    <tr>
        <td class="notice-bg t-center">Vans</td>
        <td class="notice-bg t-center">Crew</td>
        <td class="notice-bg t-center">Start time</td>
        <td class="notice-bg t-center">End time</td>
        <td class="notice-bg t-center">Off-clock</td>
        <td class="notice-bg t-center">Hours</td>
        <td class="notice-bg t-center">Rate</td>
        <td class="notice-bg t-center">Charge</td>
    </tr>
    @foreach($model->localMovesBol()['records'] as $v)
        <td class="value">{{$v['trucks']}}</td>
        <td class="value">{{$v['crew']}}</td>
        <td class="value">{{$v['start_time']}}</td>
        <td class="value">{{$v['end_time']}}</td>
        <td class="value">{{$v['off_clock']}}</td>
        <td class="value">{{$v['hours']}}</td>
        <td class="value">${{$v['rate']}}</td>
        <td class="value">${{$v['charge']}}</td>
    @endforeach
    <tr>
        <td colspan="5">Travel Time:</td>
        <td colspan="1" class="value t-left"># Hours: {{ $data['local_move']['travel_time']['hours'] }}</td>
        <td colspan="1" class="value t-left">Rate: {{ $data['local_move']['travel_time']['rate'] }}</td>
        <td class="value">{{ $model->travelTimeTotalBol() ? '$'.$model->travelTimeTotalBol():'' }}</td>
    </tr>
    <tr>
        <td colspan="5">Mileage Charge:</td>
        <td colspan="1" class="value t-left"># Miles: {{ $data['local_move']['mileage_charge']['miles'] }}</td>
        <td colspan="1" class="value t-left">$ per mile: {{ $data['local_move']['mileage_charge']['rate'] }}</td>
        <td class="value">{{ $model->mileageTotalBol() ? '$'.$model->mileageTotalBol():'' }}</td>
    </tr>
    <tr>
        <td colspan="7">Valuation Charge:</td>
        <td class="value">{{ $data['local_move']['valuation_charge'] ? '$'.$data['local_move']['valuation_charge']:'' }}</td>
    </tr>
    <tr>
        <td colspan="7">Other Charge:</td>
        <td class="value">{{ $data['local_move']['other_charge'] ? '$'.$data['local_move']['other_charge']:'' }}</td>
    </tr>


    <tr class="border-bottom-0 border-left-0 border-right-0">
        <td colspan="7" class="border-right-0 border-left-0 border-bottom-0" style="text-align: right;">
            <h3 class="mt-1" style="margin-right: 10px;">TOTAL LOCAL CHARGES:</h3>
        </td>
        <td class="border-right-0 border-left-0 border-bottom-0 value">
            <h3 class="mt-1">{{ $model->localMovesBol()['total'] ? '$'.$model->localMovesBol()['total']: '' }}</h3></td>
    </tr>
    </tbody>
</table>
--}}

<table class="table table-no-border mt-2">
    <colgroup>
        <col style="width: 25%;">
        <col style="width: 25%;">
        <col style="width: 25%;">
        <col style="width: 25%;">
    </colgroup>
    <tbody>
    <tr>
        <td colspan="4" class="header-black t-center">INTER-CITY MOVING</td>
    </tr>
    {{--    <tr>--}}
    {{--        <td colspan="3">Description</td>--}}
    {{--        <td class="t-center">Charges $$</td>--}}
    {{--    </tr>--}}
    <tr>
        <td colspan="3">Extra pickup or delivery charge:</td>
        <td class="value">{{ $model->orderChargesBol()['extra_charge'] ? '$'.$model->orderChargesBol()['extra_charge']:'' }}</td>
    </tr>
    <tr>
        <td colspan="3">Hoisting or Piano Charge:</td>
        <td class="value">{{ $model->orderChargesBol()['hoisting'] ? '$'.$model->orderChargesBol()['hoisting']:'' }}</td>
    </tr>
    <tr>
        <td colspan="4">Stair Carry, Elevator, Long Carry:</td>
    </tr>
    <tr>
        <td class="value-color">Origin: ${{ $model->orderChargesBol()['stair_carry']['origin'] }}</td>
        <td colspan="2" class="value-color">Destination:
            ${{ $model->orderChargesBol()['stair_carry']['destination'] }}</td>
        <td class="value">{{ $model->orderChargesBol()['stair_carry']['sum'] ? '$'.$model->orderChargesBol()['stair_carry']['sum']:'' }}</td>
    </tr>
    <tr>
        <td colspan="4">Extra Labor:</td>
    </tr>
    <tr>
        <td># Personnel: <span class="value-color">{{ $model->orderChargesBol()['extra_labor']['personnel'] }}</span></td>
        <td># Hours: <span class="value-color">{{ $model->orderChargesBol()['extra_labor']['hours'] }}</span></td>
        <td>Rate: <span class="value-color">${{ $model->orderChargesBol()['extra_labor']['rate'] }}</span></td>
        <td class="value">{{ $model->orderChargesBol()['extra_labor']['sum'] ? '$'.$model->orderChargesBol()['extra_labor']['sum']:'' }}</td>
    </tr>
    <tr>
        <td colspan="3">Heavy Item Charge:</td>
        <td class="value">{{ $model->orderChargesBol()['bulky_item_charge'] ? '$'.$model->orderChargesBol()['bulky_item_charge']:'' }}</td>
    </tr>
    <tr>
        <td colspan="3">Trip Transit Insurance Policy Charge:</td>
        <td class="value">{{ $model->orderChargesBol()['trip_transit_insurance'] ? '$'.$model->orderChargesBol()['trip_transit_insurance']:'' }}</td>
    </tr>
    <tr>
        <td>Lowering</td>
        <td>Qty: {{$model->orderChargesBol()['lowering']['hours']}}</td>
        <td>Rate: ${{$model->orderChargesBol()['lowering']['rate']}}</td>
        <td class="value">{{ $model->orderChargesBol()['lowering']['sum'] ? '$'.$model->orderChargesBol()['lowering']['sum']:'' }}</td>
    </tr>

    <tr class="border-bottom-0 border-right-0 border-left-0">
        <td colspan="3" class="border-bottom-0 border-right-0 border-left-0">
            <h3 class="mt-1 t-right">TOTAL INTER-CITY CHARGES:</h3>
        </td>
        <td class="value border-bottom-0 border-right-0 border-left-0">
            <h3 class="mt-1">${{ $model->orderChargesBol()['total'] }}</h3>
        </td>
    </tr>
    </tbody>
</table>

<div class="page_break"></div>

{{--TODO if packing rows > 5 add <div class="page-break"></div> + after ? --}}
{{--<h3 class="mt-2 t-center">PACKING CHARGES</h3>--}}
<table class="table mt-2 table-no-border">
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
        <td colspan="11" class="header-black t-center">PACKING CHARGES</td>
    </tr>

    <tr>
        <td rowspan="2" class="notice-bg">Container<br>Description</td>
        <td colspan="3" class="notice-bg">Container Charge</td>
        <td colspan="3" class="notice-bg">Packing</td>
        <td colspan="3" class="notice-bg">Unpacking</td>
        <td rowspan="2" class="notice-bg t-center">Charges<br>$$</td>
    </tr>
    <tr>
        <td class="notice-bg t-center">Qty</td>
        <td class="notice-bg t-center">Rate</td>
        <td class="notice-bg t-center">Amount</td>
        <td class="notice-bg t-center">Qty</td>
        <td class="notice-bg t-center">Rate</td>
        <td class="notice-bg t-center">Amount</td>
        <td class="notice-bg t-center">Qty</td>
        <td class="notice-bg t-center">Rate</td>
        <td class="notice-bg t-center">Amount</td>
    </tr>
    @foreach($model->packingChargesBol()['records'] as $v)
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
        <td colspan="3" class="t-right border-right-0 border-bottom-0 border-left-0">
            <b>Total:</b>
        </td>
        <td class="t-center border-right-0 border-bottom-0 border-left-0">${{ $model->packingChargesBol()['containers'] }}</td>
        <td colspan="2" class="t-right border-right-0 border-bottom-0 border-left-0">
            <b>Total:</b>
        </td>
        <td class="t-center border-right-0 border-bottom-0 border-left-0">${{ $model->packingChargesBol()['packing'] }}</td>
        <td colspan="2" class="t-right border-right-0 border-bottom-0 border-left-0">
            <b>Total:</b>
        </td>
        <td class="t-center border-right-0 border-bottom-0 border-left-0 border-top-0">${{ $model->packingChargesBol()['unpacking'] }}</td>

        <td class="border-right-0 border-bottom-0 border-left-0"></td>

    </tr>
    <tr>
        <td colspan="10" class="t-right border-0">
            <h3 class="mt-1 border-right-0 border-bottom-0 border-left-0">TOTAL PACKING CHARGES:</h3>
        </td>
        <td class="value mt-1 border-0">
            <h3 class="mt-1">${{ $model->packingChargesBol()['total'] }}</h3>
        </td>
    </tr>
    </tbody>
</table>


@if($model->orderChargesBol()['other']['total'])
<table class="table mt-2 table-no-border">
    <colgroup>
        <col style="width: 98%">
        <col style="width: 2%">
    </colgroup>
    <tbody>
    <tr>
        <td colspan="2" class="header-black t-center">ADDITIONAL SERVICES</td>
    </tr>
    <td class="notice-bg">Title</td>
    <td class="notice-bg t-center">Rate</td>
    @foreach($model->orderChargesBol()['other']['records'] as $v)
        <tr>
            <td class="value-color">{{ $v['title'] }}</td>
            <td class="value">${{ $v['rate'] }}</td>
        </tr>
    @endforeach
    <tr>
        <td class="t-right border-0">
            <h3 class="mt-1 border-right-0 border-bottom-0 border-left-0">TOTAL ADDITIONAL SERVICES:</h3>
        </td>
        <td class="value mt-1 border-0">
            <h3 class="mt-1">${{ $model->orderChargesBol()['other']['total'] }}</h3>
        </td>
    </tr>
</table>
@endif

<table class="table table-no-cells-border mt-4" style="margin-right: 20px;">
    <tr>
        <td style="width: 50%;">
            <div class="" style="margin-left: 0px; margin-right: 20px;">
                {{--    <img src="{{ $signature_customer }}" width="200px"/>--}}
                @isset($signature_customer_30cents_base64)
                    <img src="data:image/png;base64,{{ $signature_customer_30cents_base64 }}" width="300" height="160"/>
                @endisset
                <div class="field"></div>
                <div class="signature-field-bottom-text">
                    (TO BE COMPLETED BY PERSON SIGNING BELOW)
                </div>
                {{--                <div style="float: left">(TO BE COMPLETED BY PERSON SIGNING BELOW)</div>--}}
            </div>
        </td>
        <td style="width: 50%;">
{{--            <div class="" style="margin-left: 20px; margin-right: 0px;">--}}

{{--                <table class="table table-no-cells-border vertical-align-bottom">--}}
{{--                    <tr>--}}
{{--                        <td class="vertical-align-bottom" style="width: 99%;">--}}
{{--                            @isset($signature_shipper_base64)--}}
{{--                                <img src="data:image/png;base64,{{ $signature_shipper_base64 }}" width="300" height="160">--}}
{{--                            @endisset--}}
{{--                        </td>--}}
{{--                        <td class="vertical-align-bottom" style="white-space: nowrap;">--}}
{{--                            {{ $model->bol_signed_at ? Carbon\Carbon::parse($model->bol_signed_at)->format('m/d/Y') : '' }}--}}
{{--                        </td>--}}
{{--                    </tr>--}}
{{--                </table>--}}
{{--                <div class="field"></div>--}}
{{--                <div class="signature-field-bottom-text">--}}
{{--                    <table class="table table-no-cells-border">--}}
{{--                        <tr>--}}
{{--                            <td style="width: 99%;">--}}
{{--                                SHIPPER--}}
{{--                            </td>--}}
{{--                            <td style=" white-space: nowrap;">--}}
{{--                                DATE--}}
{{--                            </td>--}}
{{--                        </tr>--}}
{{--                    </table>--}}
{{--                </div>--}}
{{--            </div>--}}
        </td>
    </tr>
</table>
<div class="notice" style="margin-top: 25px; width: 100%;">
    <p>NOTICE: THE SHIPPER SIGNING THIS
        CONTRACT MUST INSERT IN THE SPACE
        ABOVE IN HIS OWN HANDWRITING, EITHER
        HIS DECLARATION OF THE ACTUAL VALUE
        OF THE SHIPMENT, OR THE WORDS "60
        cents per pound per article", OTHERWISE
        THE SHIPMENT WILL BE DEEMED RELEASED
        TO A MAXIMUM VALUE EQUAL TO $2.00
        TIMES THE WEIGHT OF THE SHIPMENT IN
        POUNDS.</p>
</div>
<table class="mt-2" style="width: 100%;">
    <tr>
        <td style="width: 250px;" class="vertical-align-top">
            <table class="table">
                <tr>
                    <td class="header-black">Payment type</td>
                    <td class="header-black">Paid</td>
                </tr>
                <tr>
                    <td>Cash</td>
                    <td>$ {{$model->getBolPaid('cash')}}</td>
                </tr>
                <tr>
                    <td>Zelle</td>
                    <td>$ {{$model->getBolPaid('zelle')}}</td>
                </tr>
                <tr>
                    <td>Credit Card</td>
                    <td>$ {{$model->getBolPaid('credit_card')}}</td>
                </tr>
                <tr>
                    <td>Check</td>
                    <td>$ {{$model->getBolPaid('check')}}</td>
                </tr>
                <tr>
                    <td>Tips</td>
                    <td>$ {{$model->getBolPaid('tips')}}</td>
                </tr>
            </table>
        </td>
        <td style="border: 1px solid #000000; padding: 5px;" class="vertical-align-top">
            VALUATION CHARGE {{ $data['finance']['valuation_charge'] ? '$'.$data['finance']['valuation_charge']: '_______' }} @ $0.50 PER $100.00
            or fraction thereof {{ $data['finance']['fraction_thereof'] ?? '_______' }}
            <br>TRIP TRANSIT {{$data['finance']['trip_transit_rate'] ? '$'.$data['finance']['trip_transit_rate'] : '_______'}} rate per $100.0
            <h3 class="mt-2" style="text-align: right;">TOTAL CHARGES FOR ABOVE SERVICES: $ {{$model->bolTotalChargesAboveServices()}}</h3>
            <div class="mt-2">Maximum amount to be paid at time of delivery to obtain delivery of an estimated
                {{$data['finance']['max_amount'] ? '$'.$data['finance']['max_amount'] : '_______'}} C.O.D. shipment
            </div>
            <div class="mt-2"><b>Prepayment collected by ${{$model->bolPrepaid()}}</b></div>
            <div class="mt-2">YOU ARE OBLIGATED TO PAY THE BALANCE IN 30 DAY</div>
            <h3 class="mt-2" style="text-align: right;">BALANCE DUE: ${{$model->bolBalanceDue()}}</h3>
            <div class="mt-2">SHIPMENT WAS RECEIVED IN APPARENT GOOD CONDITION EXCEPT AS NOTED IN INVENTORY
                AND SERVICES ORDERED WERE PERFORMED.
            </div>
            <div class="">

                <table class="table table-no-cells-border vertical-align-bottom">
                    <tr>
                        <td class="vertical-align-bottom" style="width: 99%;">
                            @isset($signature_customer_base64)
                                <img src="data:image/png;base64,{{ $signature_customer_base64 }}" width="300" height="160">
                            @endisset
                        </td>
                        <td class="vertical-align-bottom" style="white-space: nowrap;">
                            {{ $model->bol_signed_at ? Carbon\Carbon::parse($model->bol_signed_at)->format('m/d/Y') : '' }}
                        </td>
                    </tr>
                </table>
                <div class="field"></div>
                <div class="signature-field-bottom-text">
                    <table class="table table-no-cells-border">
                        <tr>
                            <td style="width: 99%;">
                                CONSIGNEE SIGNATURE
                            </td>
                            <td style=" white-space: nowrap;">
                                DATE
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </td>
    </tr>

</table>


{{--<h3 class="mt-1 t-right">TOTAL ESTIMATED CHARGES: ${{ $model->totalSumEstimate() }}</h3>--}}



{{--<div class="sign">--}}
{{--    --}}{{--    <img src="{{ $signature_customer }}" width="200px"/>--}}
{{--    @isset($signature_customer_base64)--}}
{{--        <img src="data:image/png;base64,{{ $signature_customer_base64 }}" width="200px"/>--}}
{{--    @endisset--}}
{{--    <div class="field"></div>--}}
{{--    <div style="float: left">Customer's Signature</div>--}}
{{--    <div style="width: 50%;float: right">--}}
{{--        <input type="checkbox" checked style="position: relative;top: 5px"> Customer has been provided with IL C.C.--}}
{{--        required consumer brochure--}}
{{--    </div>--}}
{{--</div>--}}


{{--<div class="copy">--}}
{{--    Copyright 1998 Illinois Mover's and Warehousemen's Association Rev 10/06 Form # 2208-EST LTR--}}
{{--</div>--}}

<div class="page-break"></div>

<h3 class="t-center">TERMS AND CONDITIONS</h3>
<div style="font-size: 12px;">
    This contract is subject to all the rules, regulations, rates, and charges in mover's currently effective applicable tariff on file with the
    Illinois
    Commerce Commission, including, but not limited to, the following terms and conditions:<br>
    <b>1. OWNERSHIP OF PROPERTY:</b><br> Shipper has represented and warranted to the Mover to be the legal owner or in lawful possession of the
    property, and has the legal right and authority to contract for services for all of the property tendered, upon provisions, limitations, terms and
    conditions herein set forth. If there is any litigation or threatened litigation as a result of the breach of this clause, shipper and/or customer
    agrees
    to pay all charges that may be due together with such costs and expenses, including attorneys fees, which Mover may reasonably incur or become
    liable to pay in connection therewith. Shipper and/or customer shall indemnify and hold harmless Mover from any liability for loss, cost, expenses,
    and damages for which Mover may be liable or incur as a result of any breach of this clause. Mover shall have a lien on said property for all
    charges
    that may be due. as well as for such costs and expenses.<br>
    <b>2. MOVER'S LIABILITY</b><br>
    a) Mover shall not be liable for documents, currency, money, jewelry, precious stones, or articles of extraordinary value which are not specifically
    listed on the bill of lading.
    <br>b) Explosives or dangerous goods will not be accepted for shipment. Every party, whether principal or agents shipping such goods, shall
    indemnify
    the Mover against all loss or damage caused by such goods, and Mover will not be liable for safe delivery of the shipment.
    <br>c) Except in cases of negligence of the Mover or party in possession, Mover shall not be liable for mechanical or electrical functioning of any
    article,
    such as but not limited to, pianos, radios, phonographs, television sets, computers, clocks, barometer, mechanical refrigerators or air
    conditioners,
    or other instruments or appliances, whether or not such articles are packed or unpacked by the company.
    <br>d) Except in cases of negligence of the Mover or party in possession, Mover shall not be liable for any fragile articles injured or broken,
    unless
    packed by its employees and unpacked by them at the time of delivery.
    <br>e) Except in cases of negligence of the Mover or party in possession, Mover shall not be liable for damage to or loss of contents of pieces of
    furniture, crates, cartons, boxes, or other containers unless such contents are open for the Mover's inspection and then only for such articles as
    are
    specifically listed by the shipper and receipted for by the Mover or its agent.
    <br>f) Except in cases of negligence of the Mover or party in possession, Mover shall not be liable for loss or damage resulting from insects, moth,
    vermin, ordinary wear and tear, rust, fire, water, mold or mildew, changes Of temperature, fumigation or deterioration.
    <br>g) Mover shall not be liable for delay caused by highway obstruction or faulty or impassable highway, or lack of capacity of any highway bridge,
    or
    ferry, or caused by breakdown or mechanical defect of vehicles or equipment. Mover shall not be bound to transport by any particular schedule,
    means, vehicle, or otherwise than with reasonable dispatch. Mover shall have the right in case of physical necessity to forward said property by
    any Mover or route between the point of shipment and the point of destination.
    <br>h) Mover shall not be liable for any loss or damage or delay caused by an act of God, the public enemy, the acts of public authority,
    quarantine,
    riots, strikes, perils of navigation, the act or default or the shipper or owner, the nature of the property or defect, or inherent vice therein.
    <br>i) Mover shall not be liable for any loss or damage or delay caused by terrorist activity, including action in hindering or defending against an
    actual
    or expected terrorist activity. Such loss or damage is excluded regardless of any other cause or event that contributes concurrently or in any
    sequence to the loss. The term "terrorist activity" means any activity that is unlawful under the laws of the United States or any State and which
    involves any of the following: (1) the hijacking or sabotage of any conveyance (including an aircraft, vessel, cab, truck, van, trailer, container,
    or
    vehicle) or warehouse or other building; (2) the seizing or detaining, and threatening to kill, injure, or continue to detain another Individual in
    order
    to compel a third person (including a governmental organization) to do or abstain from doing any act as an explicit or implicit condition for the
    release of the individual seized or detained; (3) an assassination; (4) the use of any (A) biological agent, chemical agent, or nuclear weapon or
    device, or (B) explosive, or other weapon or dangerous device (other than for mere personal monetary gain), with intent to endanger, directly or
    indirectly, the safety of one or more individuals or to cause substantial damage to property; or (5) a threat, attempt, or conspiracy to do any of
    the
    foregoing.
    <br><b>3. PAYMENT OF CHARGES</b><br>
    a) Shipper shall be liable for any and all charges applicable under tariffs.
    <br>b) Except in those instances where it may be lawfully authorized to do so, Mover shall not deliver or relinquish possession at destination of
    the
    property covered by this bill of lading until all tariff rates and charges thereon shall have been paid.
    Nothing herein shall limit the right of the Mover to require at the time of shipment the prepayment of the charges.
    <br><b>4. CLAIMS</b><br>
    a) All claims must be filed in writing to the Mover within ninety (90) days after delivery of the property, or, in case of failure to make delivery,
    then
    within ninety days after a reasonable time for delivery has elapsed; and all suits shall be instituted against Mover only within two years from the
    day when notice in writing is given by the Mover to the claimant that the Mover has disallowed the claim or any part or parts thereof specified in
    the notice. Where claims are not filed or suits are not instituted thereon in accordance with the forgoing provisions, the Mover hereunder shall not
    be liable and such claims will not be paid.
    <br>b) Any and all charges applicable in Mover's tariff must be paid in full befqre claims will be settled.
    <br>c) Mover shall have the right to inspect and repair alleged damaged articles. Damage will be adjusted on the depreciated value of the item based
    upon the cost to repair or replace with like kind and quality not to exceed the lump sum value declared, whichever is less.
    <br>d) Valuation provisions as declared by Shipper in writing on face hereof shall be Mover's maximum liability. In all cases not prohibited by
    laws,
    where a lower value than actual value has been represented in writing by the Shipper or has been agreed upon in writing as the released value of
    the property as determined by the classification or tariffs upon which the rate is based, such lower value shall be the maximum amount to be
    recovered, whether or not such loss or damage occuis from negligence.
    <br>e) Mover or party liable on account of loss or damage to any of said property shall have the full benefit of any insurance that may have been
    effected upon or account of said property so far as this shall not avoid the policies or contracts of insurance, provided that the Mover reimburse
    the
    claimant for the premium paid thereon.
    <br><b>5. MOVER'S LIEN</b>
    <br>a) If for any reason other than the fault of the Mover, delivery cannot be made at the address shown on the face hereof, or any changed address
    of
    which Mover has been notified, Mover, at its option, may cause articles contained in shipment to be stored in a warehouse selected by it at the
    point of delivery or at other available points, and there held cost of the owner, and subject to a lien for all accrued tariff, storage, and other
    lawful
    charges.
    <br>b) If a shipment is refused by consignee at destination, or if shipper, consignee, or owner of property fails to receive or claim it within
    fifteen (15)
    days after written notice by United States mail addressed to shipper and consignee at post office addresses shown on face hereof, or if shipper
    refused to pay lawfully applicable charges in accordance with Mover's applicable tariff, Mover may sell the property at its option either (a) upon
    the
    notice and in the manner authorized by law, or (b) at public auction to the highest bidder for cash at a public sale to be held at a time and place
    named by Mover, thirty (30) days notice of which sale shall have been published at least once a week for two consecutive weeks in a newspaper of
    general circulation at or near the place of sale, a notice thereof containing a description of the property as described in the bill of lading, and
    the
    names of consignor and consignee. The proceeds of any sale shall be applied toward payment of lawful charges applicable to shipment and toward
    expenses of notice, advertising, and sale, and of storing, caring for, and maintaining property prior to sale. Any balance shall be paid to the
    owner
    of the property sold hereunder.
    <br>c) Any perishable articles contained in said shipment may be sold at public or private sale without such notice, if, in the opinion of the
    Mover, such
    action is necessary to prevent deterioration or further deterioration.
    <br><b>6. OTHER PROVISIONS:</b>
    <br>If this bill of lading is issued on the order of the Shipper, or its agent, in exchange or in substitution for another bill of lading, the
    Shipper's
    signature to the prior bill of lading as to the statement of value of otherwise, or election for common laws or bill of lading liability, or in
    connection
    with such prior bill of lading, shall be considered a part of this bill of lading as fully as if the same were written or made in connection with
    this bill f
    lading. Any alteration, addition, or erasure in this bill of lading which shall be made without special notation hereon of the agent of the Mover
    issuing this bill of lading shall be without effect, and this bill of lading shall be enforceable according to its original tenor
</div>
<footer>
    Copyright 2007 Illinois Mover's and Warehousemen's Association FORM 2209-BOL LTR
</footer>
</body>

</html>
