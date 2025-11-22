@php
    /** @var App\Models\Orders\Order  $order */
@endphp
<html>
<head>
</head>
<body>
@if(count($order->vehicles))

    @foreach($order->vehicles as $vehicle)

        <div style="page-break-inside: avoid;">

            @include('pdf.logo')

            @if($show_shipper_info)
                <div
                    style="float:right; width:200px; font-family: Arial, sans-serif; font-size: 10px; color: #171D2D; margin-bottom: 5px;">
                    <h3 style="text-transform: uppercase; color: #00B67B; font-size: 10px; font-weight: 700; margin-bottom: 4px;">
                        SHIPPER/CUSTOMER
                    </h3>
                    @include('pdf.contact', ['contact' => $order->postProcessContact($order->shipper_contact)])
                </div>
            @endif
            <h2 style="font-family: Arial, sans-serif; color: #00B67B; font-weight: 700; font-size: 17.5px; margin: 10px 0 3px; text-transform: uppercase;">
                {{ $profile->name }}
            </h2>
            <div
                style="font-family: Arial, sans-serif; font-size: 10px; color: #171D2D; margin-bottom: 3px;">{{ $profile->address }}</div>
            <div
                style="font-family: Arial, sans-serif; font-size: 10px; color: #171D2D; margin-bottom: 3px;">{{ $profile->city }}
                , @if(isset($profile->state)) {{ $profile->state->name }} @endif {{ $profile->zip }}</div>
            <div style="font-family: Arial, sans-serif; font-size: 10px; color: #171D2D; margin-bottom: 3px;"><b>MC
                    Number:</b> {{ $profile->mc_number }}</div>
            <div style="font-family: Arial, sans-serif; font-size: 10px; color: #171D2D; margin-bottom: 3px;">
                <b>Email:</b> {{ $profile->email }}</div>
            @if ($profile->phone)
                <div style="font-family: Arial, sans-serif; font-size: 10px; color: #171D2D; margin-bottom: 3px;">
                    <b>Phone:</b> {{ $profile->phone }}
                </div>
            @endif
            @if ($profile->phones)
                @foreach ($profile->phones as $phone)
                    @if(isset($phone['number']))
                        <div style="font-family: Arial, sans-serif; font-size: 10px; color: #171D2D; margin-bottom: 3px;">
                            <b>Phone:</b> {{ $phone['number'] }}
                        </div>
                    @endif
                @endforeach
            @endif
            <h2 style="font-family: Arial, sans-serif; margin-top: 4px; margin-bottom: 4px; color: #00B67B; font-weight: 700; font-size: 17.5px; text-transform: uppercase;">
                BILL OF LADING / VEHICLE INSPECTION REPORT</h2>
            <table
                style="border: 2px solid #AAA; color: #171D2D; font-family: Arial, sans-serif; width: 100%; max-width: 878px; border-collapse: collapse;">
                <tr>
                    <th style="border: 2px solid #AAA; color: #171D2D; text-align: left; font-size: 13px; padding: 4px;"
                        colspan="2">
                        Load ID: {{ $order->load_id }}
                    </th>
                </tr>
                <tr>
                    <td style="border: 2px solid #AAA; color: #171D2D; padding: 9px; font-size: 10px; width: 50%; vertical-align: top;">
                        <h3 style="text-transform: uppercase; color: #00B67B; font-size: 10px; font-weight: 700; margin: 0 0 4px;">
                            ORIGIN
                        </h3>
                        @include('pdf.contact', ['contact' => $order->postProcessContact($order->pickup_contact)])
                    </td>
                    <td style="border: 2px solid #AAA; color: #171D2D; padding: 9px; font-size: 10px; width: 50%; vertical-align: top;">
                        <h3 style="text-transform: uppercase; color: #00B67B; font-size: 10px; font-weight: 700; margin: 0 0 4px;">
                            Destination
                        </h3>
                        @include('pdf.contact', ['contact' => $order->postProcessContact($order->delivery_contact)])
                    </td>
                </tr>
            </table>
            <table
                style="border: 2px solid #AAA; color: #171D2D; font-family: Arial, sans-serif;width: 100%; border-collapse: collapse; max-width: 878px; margin-top: 5px;"
                class="table-lg">
                <tr>
                    <th style="text-transform: uppercase; border: 2px solid #AAA; color: #171D2D; width: 20%; text-align: center; vertical-align: middle; font-size: 10px; padding: 4px 0;">
                        <div>VIN</div>
                        <div style="font-size: 10px; font-weight: 400; margin-top: 3px;">{{ $vehicle->vin }}</div>
                    </th>
                    <th style="text-transform: uppercase; border: 2px solid #AAA; color: #171D2D; width: 20%; text-align: center; vertical-align: middle; font-size: 10px; padding: 4px 0;">
                        <div>YEAR / MAKE / MODEL</div>
                        <div
                            style="font-size: 10px; font-weight: 400; margin-top: 3px;">{{ $vehicle->year }} {{ $vehicle->make }} {{ $vehicle->model }}</div>
                    </th>
                    <th style="text-transform: uppercase; border: 2px solid #AAA; color: #171D2D; width: 20%; text-align: center; vertical-align: middle; font-size: 10px; padding: 4px 0;">
                        <div>TYPE</div>
                        <div style="font-size: 10px; font-weight: 400; margin-top: 3px;">{{ $vehicle->type_name }}</div>
                    </th>
                    <th style="text-transform: uppercase; border: 2px solid #AAA; color: #171D2D; width: 20%; text-align: center; vertical-align: middle; font-size: 10px; padding: 4px 0;">
                        <div>COLOR</div>
                        <div style="font-size: 10px; font-weight: 400; margin-top: 3px;">{{ $vehicle->color }}</div>
                    </th>
                    <th style="text-transform: uppercase; border: 2px solid #AAA; color: #171D2D; width: 20%; text-align: center; vertical-align: middle; font-size: 10px; padding: 4px 0;">
                        <div>ODOMETER</div>
                        <div style="font-size: 10px; font-weight: 400; margin-top: 3px;">
                            @if($vehicle->deliveryInspection && $vehicle->deliveryInspection->odometer)
                                {{ $vehicle->deliveryInspection->odometer }}
                            @elseif($vehicle->pickupInspection && $vehicle->pickupInspection->odometer)
                                {{ $vehicle->pickupInspection->odometer }}
                            @else
                                {{ $vehicle->odometer }}
                            @endif
                        </div>
                    </th>
                </tr>
                <tr>
                    <td style="border: 2px solid #AAA; color: #171D2D; font-size: 10px; width: 50%; vertical-align: top; padding: 4px 10px;"
                        colspan="5">
                        <b>Inspection notes: </b>
                    </td>
                </tr>
                <tr>
                    @if(env('HARDCODE_OLD_DAMAGE_PHOTO'))
                        <td style="border: 2px solid #AAA; color: #171D2D; width: auto; padding: 10px 10px; font-size: 9px; vertical-align: top;">
                            @foreach(config('orders.inspection.damage_labels') as $label => $title)
                                <div style="margin-bottom: 3px;"><b>{{ $label }}</b> - {{ $title }}</div>
                            @endforeach
                        </td>
                        <td style="border: 2px solid #AAA; color: #171D2D; font-size: 10px; vertical-align: top; width: auto; text-align: center; padding: 10px 10px;" colspan="4">
                    @else
                        <td style="border: 2px solid #AAA; color: #171D2D; font-size: 10px; vertical-align: top; width: auto; text-align: center; padding: 10px 10px;" colspan="5">
                            @endif
                            @if ($vehicle->getDeliveryDamagePhoto())
                                <img src="{{ $vehicle->getDeliveryDamagePhoto() }}" height="320"/>
                            @elseif ($vehicle->getPickupDamagePhoto())
                                <img src="{{ $vehicle->getPickupDamagePhoto() }}" height="320"/>
                            @else
                                <img src="{{ asset($vehicle->getTypeImagePath()) }}" height="320"/>
                            @endif
                            <div style="margin-top: 8px; width: 100%; text-align:center; padding:0 0 0 100px;">
                                <div
                                    style="display:inline-block; font-size: 10px; line-height:24px; background: #00B67B; color: #fff; border-radius: 15px; height: 30px; width: 30px;">
                                    PD
                                </div>
                                <div
                                    style="margin:0 30px 0 10px; display:inline-block; padding:8px 0; font-size: 11px; color: #171D2D;">
                                    Pickup damage
                                </div>
                                <div
                                    style="display:inline-block; font-size: 10px; line-height:24px; background: #1F9FF7; color: #fff; border-radius: 15px; height: 30px; width: 30px;">
                                    DD
                                </div>
                                <div
                                    style="margin:0 0 0 10px; display:inline-block; padding:8px 0; font-size: 11px; color: #171D2D;">
                                    Delivery damage
                                </div>
                            </div>
                        </td>
                </tr>
            </table>
            <table
                style="border: 2px solid #AAA; border-top:none; color: #171D2D;font-family: Arial, sans-serif; width: 100%; border-collapse: collapse; max-width: 878px;">
                <tr>
                    <td style="border: 2px solid #AAA; border-top:none; color: #171D2D; width: 50%; vertical-align: top; padding: 4px 10px; font-size: 10px; color:#171D2D;"
                        colspan="3">
                        <b>Notes: </b>
                        @if($vehicle->pickupInspection && $vehicle->pickupInspection->notes)
                            {{ $vehicle->pickupInspection->notes }}
                        @endif
                    </td>
                    <td style="border: 2px solid #AAA; border-top:none; color: #171D2D; width: 50%; vertical-align: top; padding: 4px 10px; font-size: 10px; color:#171D2D;">
                        <b>Notes: </b>
                        @if($vehicle->deliveryInspection && $vehicle->deliveryInspection->notes)
                            {{ $vehicle->deliveryInspection->notes }}
                        @endif
                    </td>
                </tr>
                <tr>
                    <td style="border: 2px solid #AAA; color: #171D2D; width: 50%; vertical-align: top; padding: 4px 10px; font-size: 10px; color:#171D2D;"
                        colspan="3">
                        <div style="min-height:60px">
                            @if($order->has_pickup_signature && $order->getCustomerPickupSignature())
                                <div style="float:right;">
                                    <img style="max-width: 100px; max-height: 50px;"
                                         src="{{ $order->getCustomerPickupSignature() }}" alt="">
                                </div>
                            @endif
                            <div style="margin-right: 15px;">
                                <div>
                                    <b>I agree with the Driver's assessment <br>
                                        of the condition of this vehicle.</b>
                                </div>
                                <div style="padding-top: 6px;">
                                    Origin Signature / @if($add_pickup_delivery_dates_to_bol && $order->pickup_date_actual)
                                        {{ date('M j, Y', $order->pickup_date_actual) }} /
                                    @endif
                                    {{ $order->pickup_customer_full_name }}
                                </div>
                                @if($order->pickup_customer_refused_to_sign)
                                    <div style="padding-top: 6px;">
                                        <b>Customer refused to sign</b>
                                    </div>
                                @elseif($order->pickup_customer_not_available)
                                    <div style="padding-top: 6px;">
                                        <b>Customer not available</b>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td style="border: 2px solid #AAA; color: #171D2D; width: 50%; vertical-align: top; padding: 4px 10px; font-size: 10px; color:#171D2D;">
                        <div style="min-height:60px">
                            @if($order->has_delivery_signature && $order->getCustomerDeliverySignature())
                                <div style="float:right;">
                                    <img style="max-width: 100px; max-height: 50px;"
                                         src="{{ $order->getCustomerDeliverySignature() }}" alt="">
                                </div>
                            @endif
                            <div style="margin-right: 15px;">
                                <div>
                                    <b>Vehicle received in good condition <br>
                                        except as noted above.</b>
                                </div>
                                <div style="padding-top: 6px;">
                                    Destination Signature
                                    / @if($add_pickup_delivery_dates_to_bol && $order->delivery_date_actual)
                                        {{ date('M j, Y', $order->delivery_date_actual) }} /
                                    @endif
                                    {{ $order->delivery_customer_full_name }}
                                </div>
                                @if($order->delivery_customer_refused_to_sign)
                                    <div style="padding-top: 6px;">
                                        <b>Customer refused to sign</b>
                                    </div>
                                @elseif($order->delivery_customer_not_available)
                                    <div style="padding-top: 6px;">
                                        <b>Customer not available</b>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td style="border: 2px solid #AAA; color: #171D2D; width: 50%; vertical-align: top; padding: 4px 10px; font-size: 10px; color:#171D2D;"
                        colspan="3">
                        <div style="min-height:60px">
                            @if($order->has_pickup_signature && $order->getDriverPickupSignature())
                                <div style="float:right;">
                                    <img style="max-width: 100px; max-height: 50px;"
                                         src="{{ $order->getDriverPickupSignature() }}" alt="">
                                </div>
                            @endif
                            <div>
                                Driver Signature / @if($add_pickup_delivery_dates_to_bol && $order->pickup_date_actual)
                                    {{ date('M j, Y', $order->pickup_date_actual) }} /
                                @endif
                                @if($driver_pickup) {{ $driver_pickup->full_name }} @endif
                            </div>
                        </div>
                    </td>
                    <td style="border: 2px solid #AAA; color: #171D2D; width: 50%; vertical-align: top; padding: 8px 10px; font-size: 10px; color:#171D2D;">
                        <div style="min-height:60px">
                            @if($order->has_delivery_signature && $order->getDriverDeliverySignature())
                                <div style="float:right;">
                                    <img style="max-width: 100px; max-height: 50px;"
                                         src="{{ $order->getDriverDeliverySignature() }}" alt="">
                                </div>
                            @endif
                            <div>
                                Driver Signature / @if($add_pickup_delivery_dates_to_bol && $order->delivery_date_actual)
                                    {{ date('M j, Y', $order->delivery_date_actual) }} /
                                @endif
                                @if($driver_delivery) {{ $driver_delivery->full_name }} @endif
                            </div>
                        </div>
                    </td>
                </tr>
            </table>
            <div
                style="font-family: Arial, sans-serif; color: #171D2D; font-size: 10px; margin-top: 8px;">
                <b>View online BOL & inspection photos and details:</b>
                <div>
                    <a style="font-size: 10px; color: #3639A6;"
                       href="{{ config('frontend.url') }}/online-bol/{{ $order->public_token }}" target="_blank">
                        {{ config('frontend.url') }}/online-bol/{{ $order->public_token }}
                    </a>
                </div>
            </div>

        </div>

    @endforeach

@else

    <div style="page-break-inside: avoid;">

        @include('pdf.logo')

        @if($show_shipper_info)
            <div
                style="float:right; width:200px; font-family: Arial, sans-serif; font-size: 10px; color: #171D2D; margin-bottom: 5px;">
                <h3 style="text-transform: uppercase; color: #00B67B; font-size: 10px; font-weight: 700; margin-bottom: 4px;">
                    SHIPPER/CUSTOMER
                </h3>
                @include('pdf.contact', ['contact' => $order->postProcessContact($order->shipper_contact)])
            </div>
        @endif
        <h2 style="font-family: Arial, sans-serif; color: #00B67B; font-weight: 700; font-size: 17.5px; margin: 10px 0 3px; text-transform: uppercase;">
            {{ $profile->name }}
        </h2>
        <div
            style="font-family: Arial, sans-serif; font-size: 10px; color: #171D2D; margin-bottom: 5px;">{{ $profile->address }}</div>
        <div
            style="font-family: Arial, sans-serif; font-size: 10px; color: #171D2D; margin-bottom: 5px;">{{ $profile->city }}
            , @if(isset($profile->state)) {{ $profile->state->name }} @endif {{ $profile->zip }}</div>
        <div style="font-family: Arial, sans-serif; font-size: 10px; color: #171D2D; margin-bottom: 3px;"><b>MC
                Number:</b> {{ $profile->mc_number }}</div>
        <div style="font-family: Arial, sans-serif; font-size: 10px; color: #171D2D; margin-bottom: 5px;">
            <b>Email:</b> {{ $profile->email }}</div>
        @if ($profile->phones)
            @foreach ($profile->phones as $phone)
                @if(isset($phone['number']))
                    <div style="font-family: Arial, sans-serif; font-size: 10px; color: #171D2D; margin-bottom: 5px;"><b>Phone:</b> {{ $phone['number'] }}
                    </div>
                @endif
            @endforeach
        @endif
        <br/>
        <h2 style="font-family: Arial, sans-serif; margin-top: 4px; margin-bottom: 4px; color: #00B67B; font-weight: 700; font-size: 17.5px; text-transform: uppercase;">
            BILL OF LADING / VEHICLE INSPECTION REPORT</h2>
        <table
            style="border: 2px solid #AAA; color: #171D2D; font-family: Arial, sans-serif; width: 100%; max-width: 878px; border-collapse: collapse;">
            <tr>
                <th style="border: 2px solid #AAA; color: #171D2D; text-align: left; font-size: 13px; padding: 4px;"
                    colspan="2">
                    Load ID: {{ $order->load_id }}
                </th>
            </tr>
            <tr>
                <td style="border: 2px solid #AAA; color: #171D2D; padding: 9px; font-size: 10px; width: 50%; vertical-align: top;">
                    <h3 style="text-transform: uppercase; color: #00B67B; font-size: 10px; font-weight: 700; margin: 0 0 4px;">
                        ORIGIN
                    </h3>
                    @include('pdf.contact', ['contact' => $order->postProcessContact($order->pickup_contact)])
                </td>
                <td style="border: 2px solid #AAA; color: #171D2D; padding: 9px; font-size: 10px; width: 50%; vertical-align: top;">
                    <h3 style="text-transform: uppercase; color: #00B67B; font-size: 10px; font-weight: 700; margin: 0 0 4px;">
                        Destination
                    </h3>
                    @include('pdf.contact', ['contact' => $order->postProcessContact($order->delivery_contact)])
                </td>
            </tr>
        </table>
        <table
            style="border: 2px solid #AAA; color: #171D2D; font-family: Arial, sans-serif;width: 100%; border-collapse: collapse; max-width: 878px; margin-top: 5px;"
            class="table-lg">
            <tr>
                <th style="text-transform: uppercase; border: 2px solid #AAA; color: #171D2D; width: 20%; text-align: center; vertical-align: middle; font-size: 10px; padding: 4px 0;">
                    <div>VIN</div>
                </th>
                <th style="text-transform: uppercase; border: 2px solid #AAA; color: #171D2D; width: 20%; text-align: center; vertical-align: middle; font-size: 10px; padding: 4px 0;">
                    <div>YEAR / MAKE / MODEL</div>
                </th>
                <th style="text-transform: uppercase; border: 2px solid #AAA; color: #171D2D; width: 20%; text-align: center; vertical-align: middle; font-size: 10px; padding: 4px 0;">
                    <div>TYPE</div>
                </th>
                <th style="text-transform: uppercase; border: 2px solid #AAA; color: #171D2D; width: 20%; text-align: center; vertical-align: middle; font-size: 10px; padding: 4px 0;">
                    <div>COLOR</div>
                </th>
                <th style="text-transform: uppercase; border: 2px solid #AAA; color: #171D2D; width: 20%; text-align: center; vertical-align: middle; font-size: 10px; padding: 4px 0;">
                    <div>ODOMETER</div>
                </th>
            </tr>
            <tr>
                <td style="border: 2px solid #AAA; color: #171D2D; font-size: 10px; width: 50%; vertical-align: top; padding: 4px 10px;"
                    colspan="5">
                    <b>Inspection notes: </b>
                </td>
            </tr>
            <tr>
                <td style="border: 2px solid #AAA; color: #171D2D; font-size: 10px; vertical-align: top; width: auto; text-align: center; padding: 10px 10px;"
                    colspan="5">
                    <img src="{{ asset('vehicle-schemes/20.png') }}" width="470"/>
                    <div style="margin-top: 8px; width: 100%; text-align:center; padding:0 0 0 100px;">
                        <div
                            style="display:inline-block; font-size: 10px; line-height:24px; background: #00B67B; color: #fff; border-radius: 15px; height: 30px; width: 30px;">
                            PD
                        </div>
                        <div
                            style="margin:0 30px 0 10px; display:inline-block; padding:8px 0; font-size: 11px; color: #171D2D;">
                            Pickup damage
                        </div>
                        <div
                            style="display:inline-block; font-size: 10px; line-height:24px; background: #1F9FF7; color: #fff; border-radius: 15px; height: 30px; width: 30px;">
                            DD
                        </div>
                        <div
                            style="margin:0 0 0 10px; display:inline-block; padding:8px 0; font-size: 11px; color: #171D2D;">
                            Delivery damage
                        </div>
                    </div>
                </td>
            </tr>
        </table>
        <table
            style="border: 2px solid #AAA; border-top:none; color: #171D2D;font-family: Arial, sans-serif; width: 100%; border-collapse: collapse; max-width: 878px;">
            <tr>
                <td style="border: 2px solid #AAA; border-top:none; color: #171D2D; width: 50%; vertical-align: top; padding: 4px 10px; font-size: 10px; color:#171D2D;"
                    colspan="3">
                    <b>Notes:</b>
                </td>
                <td style="border: 2px solid #AAA; border-top:none; color: #171D2D; width: 50%; vertical-align: top; padding: 4px 10px; font-size: 10px; color:#171D2D;">
                    <b>Notes:</b>
                </td>
            </tr>
            <tr>
                <td style="border: 2px solid #AAA; color: #171D2D; width: 50%; vertical-align: top; padding: 4px 10px; font-size: 10px; color:#171D2D;"
                    colspan="3">
                    <div style="min-height:60px">
                        <div style="margin-right: 15px;">
                            <div>
                                <b>I agree with the Driver's assessment <br>
                                    of thecondition of this vehicle.</b>
                            </div>
                            <div style="padding-top: 6px;">
                                Origin Signature / @if($add_pickup_delivery_dates_to_bol && $order->pickup_date_actual)
                                    {{ date('M j, Y', $order->pickup_date_actual) }} /
                                @endif
                                {{ $order->pickup_customer_full_name }}
                            </div>
                        </div>
                    </div>
                </td>
                <td style="border: 2px solid #AAA; color: #171D2D; width: 50%; vertical-align: top; padding: 4px 10px; font-size: 10px; color:#171D2D;">
                    <div style="min-height:60px">
                        <div style="margin-right: 15px;">
                            <div>
                                <b>Vehicle received in good condition <br>
                                    except as noted above.</b>
                            </div>
                            <div style="padding-top: 6px;">
                                Destination Signature
                                / @if($add_pickup_delivery_dates_to_bol && $order->delivery_date_actual)
                                    {{ date('M j, Y', $order->delivery_date_actual) }} /
                                @endif
                                {{ $order->delivery_customer_full_name }}
                            </div>
                        </div>
                    </div>
                </td>
            </tr>
            <tr>
                <td style="border: 2px solid #AAA; color: #171D2D; width: 50%; vertical-align: top; padding: 4px 10px; font-size: 10px; color:#171D2D;"
                    colspan="3">
                    <div style="min-height:60px">
                        <div>
                            Driver Signature / @if($add_pickup_delivery_dates_to_bol && $order->pickup_date_actual)
                                {{ date('M j, Y', $order->pickup_date_actual) }} /
                            @endif
                            @if($driver) {{ $driver->full_name }} @endif
                        </div>
                    </div>
                </td>
                <td style="border: 2px solid #AAA; color: #171D2D; width: 50%; vertical-align: top; padding: 8px 10px; font-size: 10px; color:#171D2D;">
                    <div style="min-height:60px">
                        <div>
                            Driver Signature / @if($add_pickup_delivery_dates_to_bol && $order->delivery_date_actual)
                                {{ date('M j, Y', $order->delivery_date_actual) }} /
                            @endif
                            @if($driver) {{ $driver->full_name }} @endif
                        </div>
                    </div>
                </td>
            </tr>
        </table>
        <div style="font-family: Arial, sans-serif; color: #171D2D; font-size: 10px; margin-top: 8px;">
            <b>View online BOL & inspection photos and details:</b>
            <div>
                <a style="font-size: 10px; color: #3639A6;"
                   href="{{ config('frontend.url') }}/online-bol/{{ $order->public_token }}" target="_blank">
                    {{ config('frontend.url') }}/online-bol/{{ $order->public_token }}
                </a>
            </div>
        </div>

    </div>

@endif

@if($terms_conditions)
    <div style="page-break-inside: avoid; font-family: Arial, sans-serif; color: #171D2D; font-size: 10px;">
        <h2 style="font-family: Arial, sans-serif; margin: 0 0 20px 0; color: #00B67B; font-weight: 700; font-size: 17.5px; text-transform: uppercase;">Terms and Conditions</h2>
        {!! nl2br($terms_conditions) !!}
    </div>
@endif

</body>
</html>
