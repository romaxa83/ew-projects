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
        margin-bottom: 12px;
    }

    .main-info {
        margin: 1.5em 0;
    }

    .main-info div {
        display: inline-block;
        min-width: 6rem;
        border-bottom: 1px solid currentColor;
    }

    .main-info h3 {
        margin: 0;
    }

    .signature-block {
        margin-bottom: 1.5em;
        padding-bottom: 1.5em;
        border-bottom: 1px solid #333;
    }

    .signature-block:last-child {
        margin-bottom: 0;
        padding-bottom: 0;
        border-bottom: none;
    }

    table.table {
        margin: 0 auto;
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
        width: 12px;
        border-bottom: none;
    }

    .signature-name {
        min-width: 200px;
        vertical-align: bottom;
    }

    .signature-image {
        width: 200px;
    }

    .signature-date {
        width: 130px;
        white-space: nowrap;
        vertical-align: bottom;
    }

    .mb-2 {
        margin-bottom: 4px;
    }

    .px-1 {
        padding-left: 4px;
        padding-right: 4px;
    }

    .text-underline {
        text-decoration: underline;
    }

    .float-right {
        float: right;
    }
</style>
<body>
<header class="header">
    <img class="logo" src="{{ $logo_base64 }}" alt="H2Hmovers logo">
    <div class="mb-2">4250 N Marine Dr, Chicago, IL 60613</div>
    <div>(773) 236-8797 / <a href="https://h2hmovers.com/">www.h2hmovers.com</a></div>
</header>

<div class="main-info">
    <div class="px-1"><h3>Physical Damage - Liability Waiver</h3></div>
    <div class="float-right px-1"><h3>Job #{{ $model->order_id }}</h3></div>
</div>

<div>
    @if($signature_protect_property)
        <div class="signature-block">
            <p>
                I <b class="text-underline">{{ $client_name_property_block }}</b>, do hereby release and absolve the
                moving
                company of any and all damages incurred to my property and/or my
                belongings due to my refusal to properly protect my belongings as per the movers’ recommendations.
                Improper
                protection includes but is not limited to, not wrapping furniture with protective pads and not crating
                fragile
                objects such as glass, paintings, marble, crystal, etc. I accept all responsibility for anything that
                shall
                happen to my belongings during the moving process.
            </p>
            <div class="sign">
                <table class="table">
                    <tr>
                        <td class="signature-name">{{ $client_name_property_block }}</td>
                        <td class="empty-cell"></td>
                        <td class="signature-image">
                            @isset($signature_protect_property_base_64)
                                <img
                                    src="data:image/png;base64,{{ $signature_protect_property_base_64 }}"
                                    width="200px"
                                    height="117px"
                                    alt="signature_protect_property"
                                />
                            @endisset
                        </td>
                        <td class="empty-cell"></td>
                        <td class="signature-date">
                            {{ $model->waiver_failure_to_protect_property_signed_at ? Carbon\Carbon::parse($model->waiver_failure_to_protect_property_signed_at)->format('D, M j, Y') : '' }}
                        </td>
                    </tr>
                    <tr>
                        <td>Name</td>
                        <td class="empty-cell"></td>
                        <td>Signature</td>
                        <td class="empty-cell"></td>
                        <td>Date</td>
                    </tr>
                </table>
            </div>
        </div>
    @endif

    @if($signature_oversized)
        <div class="signature-block">
            <p>
                I <b class="text-underline">{{ $client_name_oversize_block }}</b>, do hereby release and absolve the
                moving
                company of any and all damages incurred to my property and/or my belongings due to my requesting the
                movers
                to
                force an oversize object through a compromisingly small entrance or hall way. I accent all
                responsibility
                for
                anything that shall happen to my doors, floors, walls, ceilings, ext.
            </p>
            <div class="sign">
                <table class="table">
                    <tr>
                        <td class="signature-name">{{ $client_name_oversize_block }}</td>
                        <td class="empty-cell"></td>
                        <td class="signature-image">
                            @isset($signature_oversized_base_64)
                                <img
                                    src="data:image/png;base64,{{ $signature_oversized_base_64 }}"
                                    width="200px"
                                    height="117px"
                                    alt="signature_oversized"
                                />
                            @endisset
                        </td>
                        <td class="empty-cell"></td>
                        <td class="signature-date">
                            {{ $model->waiver_oversized_object_handling_signed_at ? Carbon\Carbon::parse($model->waiver_oversized_object_handling_signed_at)->format('D, M j, Y') : '' }}
                        </td>
                    </tr>
                    <tr>
                        <td>Name</td>
                        <td class="empty-cell"></td>
                        <td>Signature</td>
                        <td class="empty-cell"></td>
                        <td>Date</td>
                    </tr>
                </table>
            </div>
        </div>
    @endif

    @if($signature_custom)
        <div class="signature-block">
            <p>
                I <b class="text-underline">{{ $client_name_custom_block }}</b>, do hereby release and absolve the
                moving
                company of any and all damages incurred to my property and/or my belongings due
                to <b class="text-underline">{{ $model->waiver_custom_reason ?? "" }}</b><br />
                I accept all responsibility for anything that shall happen to my property and/or belongings.
            </p>
            <div class="sign">
                <table class="table">
                    <tr>
                        <td class="signature-name">{{ $client_name_custom_block }}</td>
                        <td class="empty-cell"></td>
                        <td class="signature-image">
                            @isset($signature_custom_base_64)
                                <img
                                    src="data:image/png;base64,{{ $signature_custom_base_64 }}"
                                    width="200px"
                                    height="117px"
                                    alt="signature_custom"
                                />
                            @endisset
                        </td>
                        <td class="empty-cell"></td>
                        <td class="signature-date">
                            {{ $model->waiver_custom_reason_signed_at ? Carbon\Carbon::parse($model->waiver_custom_reason_signed_at)->format('D, M j, Y') : '' }}
                        </td>
                    </tr>
                    <tr>
                        <td>Name</td>
                        <td class="empty-cell"></td>
                        <td>Signature</td>
                        <td class="empty-cell"></td>
                        <td>Date</td>
                    </tr>
                </table>
            </div>
        </div>
    @endif
</div>
</body>
</html>
