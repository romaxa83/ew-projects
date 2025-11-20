<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>{{ $title }}</title>

    <style>
        body {
            font-family: dompdf_arial;
        }
    </style>
</head>
<body style="
max-width: 878px;
margin: 5mm 10mm;
">
<!-- heading -->
<div style="padding: 1.875rem 0 1.25rem; width: 100%; display: inline-flex; justify-content: space-between; align-items: center; white-space: nowrap;">
    <img src="{{asset('storage/logo.svg')}}" style="margin-top: 0.5rem; max-width: 17.1875rem; float: left" alt="logo">

    <div style="display: inline-block; padding-top: 10px; float: right; text-align: right; width: 70%; font-size: 1.125rem; font-weight: normal; word-wrap: break-word; line-height: 1; color: #367c2b;">
        Report
    </div>
</div>
<div style="padding: 2.25rem 0 1.25rem;">
    {{ $title }}
</div>
<div style="page-break-inside: avoid; width: 100%;">
    <!-- title -->
{{--    <div style="font-size: 1rem; font-weight: bold; padding-top: 2.5rem; line-height: 1.2em; width: 100%; border-top: 5px solid #367c2b;">--}}
{{--        {{ $translates['product_specialist'] }}--}}
{{--    </div>--}}
    <!-- table -->
    <table style="page-break-inside: avoid; border-collapse: collapse; margin: 1.5625rem 0 2.5rem; width: 100%; font-size: 1rem; line-height: 1.25em;">
        <tr>
            <td style="width: 50%; padding: 0.75rem 0.4375rem; font-weight: bold; word-break: break-word;">
                {{ $translates['account_name'] }}
            </td>
            <td style="width: 50%; padding: 0.75rem 0.4375rem; font-weight: normal; word-break: break-word;">
                {{ $user_full_name }}
            </td>
        </tr>
        <tr>
            <td style="border-top: 1px solid rgba(0, 0, 0, 0.12); width: 50%; padding: 0.75rem 0.4375rem; font-weight: bold; word-break: break-word;">
                {{ $translates['country'] }}
            </td>
            <td style="border-top: 1px solid rgba(0, 0, 0, 0.12); width: 50%; padding: 0.75rem 0.4375rem; font-weight: normal; word-break: break-word;">
                {{ $user_country }}
            </td>
        </tr>
        <tr>
            <td style="border-top: 1px solid rgba(0, 0, 0, 0.12); width: 50%; padding: 0.75rem 0.4375rem; font-weight: bold; word-break: break-word;">
                {{ $translates['email'] }}
            </td>
            <td style="border-top: 1px solid rgba(0, 0, 0, 0.12); width: 50%; padding: 0.75rem 0.4375rem; font-weight: normal; word-break: break-word;">
                {{ $user_email }}
            </td>
        </tr>
        <tr>
            <td style="border-top: 1px solid rgba(0, 0, 0, 0.12); width: 50%; padding: 0.75rem 0.4375rem; font-weight: bold; word-break: break-word;">
                {{ $translates['phone'] }}
            </td>
            <td style="border-top: 1px solid rgba(0, 0, 0, 0.12); width: 50%; padding: 0.75rem 0.4375rem; font-weight: normal; word-break: break-word;">
                {{ $user_phone }}
            </td>
        </tr>
    </table>
</div>
<div style="page-break-inside: avoid; width: 100%;">
    <!-- title -->
    <div style="font-size: 1rem; color: #367c2b; font-weight: bold; line-height: 1.2em;">
        {{ $translates['dealer'] }}
    </div>
    <!-- table -->
    <table style="page-break-inside: avoid; border-collapse: collapse; margin: 1.5625rem 0 2.5rem; width: 100%; font-size: 1rem; line-height: 1.25em;">
        <tr>
            <td style="width: 50%; padding: 0.75rem 0.4375rem; font-weight: bold; word-break: break-word;">
                {{ $translates['account_name'] }}
            </td>
            <td style="width: 50%; padding: 0.75rem 0.4375rem; font-weight: normal; word-break: break-word;">
                {{ $dealer_name }}
            </td>
        </tr>
        <tr>
            <td style="border-top: 1px solid rgba(0, 0, 0, 0.12); width: 50%; padding: 0.75rem 0.4375rem; font-weight: bold; word-break: break-word;">
                {{ $translates['country'] }}
            </td>
            <td style="border-top: 1px solid rgba(0, 0, 0, 0.12); width: 50%; padding: 0.75rem 0.4375rem; font-weight: normal; word-break: break-word;">
                {{ $dealer_country }}
            </td>
        </tr>
{{--        <tr>--}}
{{--            <td style="border-top: 1px solid rgba(0, 0, 0, 0.12); width: 50%; padding: 0.75rem 0.4375rem; font-weight: bold; word-break: break-word;">--}}
{{--                {{ $translates['dealer_id'] }}--}}
{{--            </td>--}}
{{--            <td style="border-top: 1px solid rgba(0, 0, 0, 0.12); width: 50%; padding: 0.75rem 0.4375rem; font-weight: normal; word-break: break-word;">--}}
{{--                {{ $dealer_id }}--}}
{{--            </td>--}}
{{--        </tr>--}}
        <tr>
            <td style="border-top: 1px solid rgba(0, 0, 0, 0.12); width: 50%; padding: 0.75rem 0.4375rem; font-weight: bold; word-break: break-word;">
                {{ $translates['salesman_name'] }}
            </td>
            <td style="border-top: 1px solid rgba(0, 0, 0, 0.12); width: 50%; padding: 0.75rem 0.4375rem; font-weight: normal; word-break: break-word;">
                {{ $salesman_name }}
            </td>
        </tr>
    </table>
</div>
<div style="page-break-inside: avoid; width: 100%;">
    <!-- title -->
    <div style="font-size: 1rem; color: #367c2b; font-weight: bold; line-height: 1.2em;">
        {{ $translates['customer'] }}
    </div>
    <!-- table -->
    @foreach($customers ?? [] as $customer)
        <table style="page-break-inside: avoid; border-collapse: collapse; margin: 1.5625rem 0 2.5rem; width: 100%; font-size: 1rem; line-height: 1.25em;">
            <tr>
                <td style="width: 50%; padding: 0.75rem 0.4375rem; font-weight: bold; word-break: break-word;">
                    {{ $translates['company_name'] }}
                </td>
                <td style="width: 50%; padding: 0.75rem 0.4375rem; font-weight: normal; word-break: break-word;">
                    {{ $customer['company_name'] }}
                </td>
            </tr>
            <tr>
                <td style="border-top: 1px solid rgba(0, 0, 0, 0.12); width: 50%; padding: 0.75rem 0.4375rem; font-weight: bold; word-break: break-word;">
                    {{ $translates['first_name'] }}
                </td>
                <td style="border-top: 1px solid rgba(0, 0, 0, 0.12); width: 50%; padding: 0.75rem 0.4375rem; font-weight: normal; word-break: break-word;">
                    {{ $customer['first_name'] }}
                </td>
            </tr>
            <tr>
                <td style="border-top: 1px solid rgba(0, 0, 0, 0.12); width: 50%; padding: 0.75rem 0.4375rem; font-weight: bold; word-break: break-word;">
                    {{ $translates['last_name'] ?? null }}
                </td>
                <td style="border-top: 1px solid rgba(0, 0, 0, 0.12); width: 50%; padding: 0.75rem 0.4375rem; font-weight: normal; word-break: break-word;">
                    {{ $customer['last_name'] ?? null }}
                </td>
            </tr>
            <tr>
                <td style="border-top: 1px solid rgba(0, 0, 0, 0.12); width: 50%; padding: 0.75rem 0.4375rem; font-weight: bold; word-break: break-word;">
                    {{ $translates['phone'] }}
                </td>
                <td style="border-top: 1px solid rgba(0, 0, 0, 0.12); width: 50%; padding: 0.75rem 0.4375rem; font-weight: normal; word-break: break-word;">
                    {{ $customer['phone'] }}
                </td>
            </tr>
            @if(isset($customer['product_name']) && $customer['product_name'] != null && $customer['product_name'] != '')
                <tr>
                    <td style="border-top: 1px solid rgba(0, 0, 0, 0.12); width: 50%; padding: 0.75rem 0.4375rem; font-weight: bold; word-break: break-word;">
                        {{ $translates['product_name'] }}
                    </td>
                    <td style="border-top: 1px solid rgba(0, 0, 0, 0.12); width: 50%; padding: 0.75rem 0.4375rem; font-weight: normal; word-break: break-word;">
                        {{ $customer['product_name'] }}
                    </td>
                </tr>
            @endif
            @if(isset($customer['quantity_machine']) && $customer['quantity_machine'] != null && $customer['quantity_machine'] != '')
                <tr>
                    <td style="border-top: 1px solid rgba(0, 0, 0, 0.12); width: 50%; padding: 0.75rem 0.4375rem; font-weight: bold; word-break: break-word;">
                        {{ $translates['quantity_machine'] }}
                    </td>
                    <td style="border-top: 1px solid rgba(0, 0, 0, 0.12); width: 50%; padding: 0.75rem 0.4375rem; font-weight: normal; word-break: break-word;">
                        {{ $customer['quantity_machine'] }}
                    </td>
                </tr>
            @endif
            <tr>
                <td style="border-top: 1px solid rgba(0, 0, 0, 0.12); width: 50%; padding: 0.75rem 0.4375rem; font-weight: bold; word-break: break-word;">
                    {{ $translates['customer_type'] }}
                </td>
                <td style="border-top: 1px solid rgba(0, 0, 0, 0.12); width: 50%; padding: 0.75rem 0.4375rem; font-weight: normal; word-break: break-word;">
                    {{ $customer['type'] }}
                </td>
            </tr>
        </table>
    @endforeach


</div>
<div style="page-break-inside: avoid; width: 100%;">
    <!-- table -->
    @foreach($machines ?? [] as $machine)
        <!-- title -->
        <div style="font-size: 1rem; color: #367c2b; font-weight: bold; line-height: 1.2em;">
            {{ $translates['product'] }}
        </div>
        <table style="page-break-inside: avoid; border-collapse: collapse; margin: 1.5625rem 0 2.5rem; width: 100%; font-size: 1rem; line-height: 1.25em;">
            <tr>
                <td style="width: 50%; padding: 0.75rem 0.4375rem; font-weight: bold; word-break: break-word;">
                    {{ $translates['manufacturer'] }}
                </td>
                <td style="width: 50%; padding: 0.75rem 0.4375rem; font-weight: normal; word-break: break-word;">
                    {{ $machine['manufacturer'] }}
                </td>
            </tr>
            <tr>
                <td style="border-top: 1px solid rgba(0, 0, 0, 0.12); width: 50%; padding: 0.75rem 0.4375rem; font-weight: bold; word-break: break-word;">
                    {{ $translates['equipment_group'] }}
                </td>
                <td style="border-top: 1px solid rgba(0, 0, 0, 0.12); width: 50%; padding: 0.75rem 0.4375rem; font-weight: normal; word-break: break-word;">
                    {{ $machine['equipment_group'] }}
                </td>
            </tr>
            <tr>
                <td style="border-top: 1px solid rgba(0, 0, 0, 0.12); width: 50%; padding: 0.75rem 0.4375rem; font-weight: bold; word-break: break-word;">
                    {{ $translates['model_description'] }}
                </td>
                <td style="border-top: 1px solid rgba(0, 0, 0, 0.12); width: 50%; padding: 0.75rem 0.4375rem; font-weight: normal; word-break: break-word;">
                    {{ $machine['model_description'] }}
                </td>
            </tr>
            @if(isset($machine['model_description.size']) && $machine['model_description.size'] != null && $machine['model_description.size'] != '')
                <tr>
                    <td style="border-top: 1px solid rgba(0, 0, 0, 0.12); width: 50%; padding: 0.75rem 0.4375rem; font-weight: bold; word-break: break-word;">
                        {{ $translates['model_description.size'] }}
                    </td>
                    <td style="border-top: 1px solid rgba(0, 0, 0, 0.12); width: 50%; padding: 0.75rem 0.4375rem; font-weight: normal; word-break: break-word;">
                        {{ $machine['model_description.size'] }} {{ $machine['model_description.size_parameter'] }}
                    </td>
                </tr>
            @endif
            @if(isset($machine['model_description.type']) && $machine['model_description.type'] != null && $machine['model_description.type'] != '')
                <tr>
                    <td style="border-top: 1px solid rgba(0, 0, 0, 0.12); width: 50%; padding: 0.75rem 0.4375rem; font-weight: bold; word-break: break-word;">
                        {{ $translates['model_description.type'] }}
                    </td>
                    <td style="border-top: 1px solid rgba(0, 0, 0, 0.12); width: 50%; padding: 0.75rem 0.4375rem; font-weight: normal; word-break: break-word;">
                        {{ $machine['model_description.type'] }}
                    </td>
                </tr>
            @endif
            <tr>
                <td style="border-top: 1px solid rgba(0, 0, 0, 0.12); width: 50%; padding: 0.75rem 0.4375rem; font-weight: bold; word-break: break-word;">
                    {{ $translates['machine_serial_number'] }}
                </td>
                <td style="border-top: 1px solid rgba(0, 0, 0, 0.12); width: 50%; padding: 0.75rem 0.4375rem; font-weight: normal; word-break: break-word;">
                    {{ $machine['machine_serial_number'] }}
                </td>
            </tr>
            @if(isset($machine['header_brand']) && $machine['header_brand'] != null && $machine['header_brand'] != '')
                <tr>
                    <td style="border-top: 1px solid rgba(0, 0, 0, 0.12); width: 50%; padding: 0.75rem 0.4375rem; font-weight: bold; word-break: break-word;">
                        {{ $translates['header_brand'] }}
                    </td>
                    <td style="border-top: 1px solid rgba(0, 0, 0, 0.12); width: 50%; padding: 0.75rem 0.4375rem; font-weight: normal; word-break: break-word;">
                        {{ $machine['header_brand'] }}
                    </td>
                </tr>
            @endif
            @if(isset($machine['header_model']) && $machine['header_model'] != null && $machine['header_model'] != '')
                <tr>
                    <td style="border-top: 1px solid rgba(0, 0, 0, 0.12); width: 50%; padding: 0.75rem 0.4375rem; font-weight: bold; word-break: break-word;">
                        {{ $translates['header_model'] }}
                    </td>
                    <td style="border-top: 1px solid rgba(0, 0, 0, 0.12); width: 50%; padding: 0.75rem 0.4375rem; font-weight: normal; word-break: break-word;">
                        {{ $machine['header_model'] }}
                    </td>
                </tr>
            @endif
            @if(isset($machine['serial_number_header']) && $machine['serial_number_header'] != null && $machine['serial_number_header'] != '')
                <tr>
                    <td style="border-top: 1px solid rgba(0, 0, 0, 0.12); width: 50%; padding: 0.75rem 0.4375rem; font-weight: bold; word-break: break-word;">
                        {{ $translates['serial_number_header'] }}
                    </td>
                    <td style="border-top: 1px solid rgba(0, 0, 0, 0.12); width: 50%; padding: 0.75rem 0.4375rem; font-weight: normal; word-break: break-word;">
                        {{ $machine['serial_number_header'] }}
                    </td>
                </tr>
            @endif
            <tr>
                <td style="border-top: 1px solid rgba(0, 0, 0, 0.12); width: 50%; padding: 0.75rem 0.4375rem; font-weight: bold; word-break: break-word;">
                    {{ $translates['for machine'] }}
                </td>
                <td style="border-top: 1px solid rgba(0, 0, 0, 0.12); width: 50%; padding: 0.75rem 0.4375rem; font-weight: normal; word-break: break-word;">
                    {{ $machine['type'] }}
                </td>
            </tr>
            @if(isset($machine['trailer_model']) && $machine['trailer_model'] != null && $machine['trailer_model'] != '')
                <tr>
                    <td style="border-top: 1px solid rgba(0, 0, 0, 0.12); width: 50%; padding: 0.75rem 0.4375rem; font-weight: bold; word-break: break-word;">
                        {{ $translates['trailer_model'] }}
                    </td>
                    <td style="border-top: 1px solid rgba(0, 0, 0, 0.12); width: 50%; padding: 0.75rem 0.4375rem; font-weight: normal; word-break: break-word;">
                        {{ $machine['trailer_model'] }}
                    </td>
                </tr>
            @endif
        </table>
        <!-- title for sub tech -->
        {{-- @todo вывести инфу по прицепной технике если она есть, формат ниже --}}
        {{-- @todo в таблице просто дублировать tr для новой записи с данными --}}
        @if(null != $machine['sub_manufacturer'])
                <div style="font-size: 1rem; color: #367c2b; font-weight: bold; line-height: 1.2em;">
                    {{ $translates['trailed_equipment_type'] }}
                </div>
                <table style="page-break-inside: avoid; border-collapse: collapse; margin: 1.5625rem 0 2.5rem; width: 100%; font-size: 1rem; line-height: 1.25em;">
                    <tr>
                        <td style="width: 50%; padding: 0.75rem 0.4375rem; font-weight: bold; word-break: break-word;">
                            {{ $translates['manufacturer'] }}
                        </td>
                        <td style="width: 50%; padding: 0.75rem 0.4375rem; font-weight: normal; word-break: break-word;">
                            {{ $machine['sub_manufacturer'] }}
                        </td>
                    </tr>
                    <tr>
                        <td style="border-top: 1px solid rgba(0, 0, 0, 0.12); width: 50%; padding: 0.75rem 0.4375rem; font-weight: bold; word-break: break-word;">
                            {{ $translates['equipment_group'] }}
                        </td>
                        <td style="border-top: 1px solid rgba(0, 0, 0, 0.12); width: 50%; padding: 0.75rem 0.4375rem; font-weight: normal; word-break: break-word;">
                            {{ $machine['sub_equipment_group'] }}
                        </td>
                    </tr>
                    <tr>
                        <td style="border-top: 1px solid rgba(0, 0, 0, 0.12); width: 50%; padding: 0.75rem 0.4375rem; font-weight: bold; word-break: break-word;">
                            {{ $translates['model_description'] }}
                        </td>
                        <td style="border-top: 1px solid rgba(0, 0, 0, 0.12); width: 50%; padding: 0.75rem 0.4375rem; font-weight: normal; word-break: break-word;">
                            {{ $machine['sub_model_description'] }}
                        </td>
                    </tr>
                    @if(isset($machine['sub_model_description.size']) && $machine['sub_model_description.size'] != null && $machine['sub_model_description.size'] != '')
                        <tr>
                            <td style="border-top: 1px solid rgba(0, 0, 0, 0.12); width: 50%; padding: 0.75rem 0.4375rem; font-weight: bold; word-break: break-word;">
                                {{ $translates['model_description.size'] }}
                            </td>
                            <td style="border-top: 1px solid rgba(0, 0, 0, 0.12); width: 50%; padding: 0.75rem 0.4375rem; font-weight: normal; word-break: break-word;">
                                {{ $machine['sub_model_description.size'] }} {{ $machine['sub_model_description.size_parameter'] }}
                            </td>
                        </tr>
                    @endif
                    @if(isset($machine['sub_model_description.type']) && $machine['sub_model_description.type'] != null && $machine['sub_model_description.type'] != '')
                        <tr>
                            <td style="border-top: 1px solid rgba(0, 0, 0, 0.12); width: 50%; padding: 0.75rem 0.4375rem; font-weight: bold; word-break: break-word;">
                                {{ $translates['model_description.type'] }}
                            </td>
                            <td style="border-top: 1px solid rgba(0, 0, 0, 0.12); width: 50%; padding: 0.75rem 0.4375rem; font-weight: normal; word-break: break-word;">
                                {{ $machine['sub_model_description.type'] }}
                            </td>
                        </tr>
                    @endif
                    <tr>
                        <td style="border-top: 1px solid rgba(0, 0, 0, 0.12); width: 50%; padding: 0.75rem 0.4375rem; font-weight: bold; word-break: break-word;">
                            {{ $translates['machine_serial_number'] }}
                        </td>
                        <td style="border-top: 1px solid rgba(0, 0, 0, 0.12); width: 50%; padding: 0.75rem 0.4375rem; font-weight: normal; word-break: break-word;">
                            {{ $machine['sub_machine_serial_number'] }}
                        </td>
                    </tr>
                </table>
        @endif
    @endforeach
</div>
<div style="page-break-inside: avoid; width: 100%; margin-top: 1.75rem;">
    <!-- title -->
    <span style="font-size: 1rem; color: #367c2b; margin-right: 10px; font-weight: bold; line-height: 1.2em;">
        {{ $translates['location'] }}
    </span>

    {{-- @todo сюда в href после "=" выводить координаты в формате latitude,longtitude--}}
    <a href="https://www.google.com/maps/search/?api=1&query={{$location_lat}},{{$location_long}}"
       target="_blank"
       style="text-decoration: none; color: #367c2b; font-size: 1rem; line-height: 1.25em;">
        {{ $location }}
    </a>
</div>
<div style="page-break-inside: avoid; width: 100%; margin-top: 1.75rem;">
    <table style="border: none; margin: 1.5625rem 0 2.5rem; border-collapse: collapse; width: 100%; font-size: 1rem; line-height: 1.25em;">
        <tr>
            <td style="padding: 0.75rem 0.4375rem; color: #367c2b; font-weight: bold; word-wrap: break-word; width: 35%;">
                {{ $translates['demo_assigment'] }}
            </td>
            <td style="padding: 0.75rem 0.4375rem; font-weight: bold; word-wrap: break-word; width: 65%;">
                {{ $assignment }}
            </td>
        </tr>
            <tr>
                @if(isset($features))
                    <td colspan="2">
                        <div style="padding: 0.75rem 0.4375rem 0.4375rem; color: #367c2b; font-weight: bold;">
                            {{ $translates['demo_resultes'] }}
                        </div>
                        {{-- @todo выводить заголовок для таблицы field condition--}}
                        <div style="padding: 0.75rem 0.4375rem 0.4375rem; font-size: 14px; font-weight: bold;">
                            {{ $translates['field_condition'] }}
                        </div>
                            <table style="page-break-inside: avoid; margin: 1rem  2px 1rem 0; width: 100%; border-radius: 4px; background-color: #fff;">
                                <thead>
                                <tr>
                                    <th style="width: 25%; min-width: 110px; word-wrap: break-word; font-size: 10px; padding: 0.45rem; font-weight: bold; text-align: left;"></th>
                                    <th style="width: 70px; word-wrap: break-word; font-size: 10px; padding: 0.45rem; font-weight: bold; text-align: left;">
                                        {{ $translates['units'] }}
                                    </th>
                                    <th style="font-size: 10px; padding: 0.45rem; font-weight: bold; text-align: left;">
                                        {{ $translates['values'] }}
                                    </th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($features as $feature)
                                    @if($feature['type'] == 1)
                                        <tr>
                                            <td style="border-top: 1px solid rgba(0, 0, 0, 0.1); padding: 0.75rem 0.4375rem; font-weight: bold; word-break: break-word; font-size: 10px;">
                                                {{ $feature['name'] }}
                                            </td>
                                            <td style="word-wrap: break-word; border-top: 1px solid rgba(0, 0, 0, 0.1); padding: 0.75rem 0.45rem; font-weight: bold; word-break: break-word; font-size: 10px;">
                                                {{ $feature['unit'] }}
                                            </td>
                                            @foreach($feature['group'] as $group)
                                                <td style="border-top: 1px solid rgba(0, 0, 0, 0.1); padding: 0.75rem 0.45rem; font-weight: bold; word-break: break-word; font-size: 10px;">
                                                    {{ $group['value'] ?? null}}
                                                </td>
                                            @endforeach
                                        </tr>
                                    @endif
                                @endforeach
                                <tr style="border-top: 1px solid rgba(0, 0, 0, 0.1);">
                                    <td style="border-top: 1px solid rgba(0, 0, 0, 0.1);"></td>
                                    <td style="border-top: 1px solid rgba(0, 0, 0, 0.1);"></td>
                                    @php($bordered = false)
                                    @foreach($features as $feature)
                                        @if($feature['type'] == 1 && !$bordered)
                                            @php($bordered = true)
                                            @foreach($feature['group'] as $group)
                                                <td style="border-top: 1px solid rgba(0, 0, 0, 0.1);"></td>
                                            @endforeach
                                        @endif
                                    @endforeach
                                </tr>
                                </tbody>
                            </table>
                        {{-- @todo не выводить эту таблицу если нет $feature с type === 2 --}}
                        {{-- @todo выводить заголовок для таблицы main machines--}}
                        <div style="padding: 0.75rem 0.4375rem 0.4375rem; font-size: 14px; font-weight: bold;">
                        {{ $translates['main_machines'] }}
                        </div>
                            <table style="max-width: 100%; page-break-inside: avoid; margin: 1rem  2px 1rem 0; width: 100%; border-radius: 4px; background-color: #fff;">
                                <thead>
                                <tr>
                                    <th style="width: 25%; min-width: 110px; word-wrap: break-word; font-size: 10px; padding: 0.45rem; font-weight: bold; text-align: left;"></th>
                                    <th style="width: 70px; word-wrap: break-word; font-size: 10px; padding: 0.45rem; font-weight: bold; text-align: left;">
                                        {{ $translates['units'] }}
                                    </th>
                                    @php($used_top = false)
                                    @foreach($features as $feature)
                                        @if($feature['type'] == 2 && !$used_top && !$feature['is_sub'])
                                            @php($used_top = true)
                                            @foreach($feature['group'] as $group)
                                                <th style="word-wrap: break-word; font-size: 10px; padding: 0.45rem; font-weight: bold; text-align: left;">
                                                    {{ $group['name']}}
                                                </th>
                                            @endforeach
                                        @endif
                                    @endforeach
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($features as $feature)
{{--                                    @dd($features)--}}
                                    @if($feature['type'] == 2 && (false == $feature['is_sub']))
                                        <tr>
                                            <td style="word-wrap: break-word; border-top: 1px solid rgba(0, 0, 0, 0.1); padding: 0.75rem 0.4375rem; font-weight: bold; word-break: break-word; font-size: 10px;">
                                                {{ $feature['name'] }}
                                            </td>
                                            <td style="word-wrap: break-word; border-top: 1px solid rgba(0, 0, 0, 0.1); padding: 0.75rem 0.45rem; font-weight: bold; word-break: break-word; font-size: 10px; text-align: left;">
                                                {{ $feature['unit'] }}
                                            </td>
                                            @foreach($feature['group'] as $group)
                                                <td style="word-wrap: break-word; border-top: 1px solid rgba(0, 0, 0, 0.1); padding: 0.75rem 0.45rem; font-weight: bold; word-break: break-word; font-size: 10px; text-align: left;">
                                                    {{ $group['value'] ?? null }}
                                                </td>
                                            @endforeach
                                        </tr>
                                    @endif
                                @endforeach
{{--                                <tr>--}}
{{--                                    <td style="border-top: 1px solid rgba(0, 0, 0, 0.1);"></td>--}}
{{--                                    <td style="border-top: 1px solid rgba(0, 0, 0, 0.1);"></td>--}}
{{--                                    @php($used = false)--}}
{{--                                    @foreach($features as $feature)--}}
{{--                                        @if($feature['type'] == 2 && !$used && !$feature['is_sub'])--}}
{{--                                            @php($used = true)--}}
{{--                                            @foreach($feature['group'] as $group)--}}
{{--                                                <td style="border-top: 1px solid rgba(0, 0, 0, 0.1);"></td>--}}
{{--                                            @endforeach--}}
{{--                                        @endif--}}
{{--                                    @endforeach--}}
{{--                                </tr>--}}
                                </tbody>
                            </table>
                        {{-- Прицепная техника --}}

                            <table style="max-width: 100%; page-break-inside: avoid; margin: 1rem  2px 1rem 0; width: 100%; border-radius: 4px; background-color: #fff;">
                                <thead>
                                <tr>
                                    @php($show_sub_title = false)
                                    @foreach($features as $feature)
                                        @if($feature['type'] == 2 && !$show_sub_title && $feature['is_sub'])
                                            @php($show_sub_title = true)
                                        @endif
                                    @endforeach

                                    @if($show_sub_title)
                                        <th style="width: 25%; min-width: 110px; word-wrap: break-word; font-size: 10px; padding: 0.45rem 0.45rem; font-weight: bold; text-align: left;"></th>
                                        <th style="width: 70px; word-wrap: break-word; font-size: 10px; padding: 0.45rem; font-weight: bold; text-align: left;">
                                            {{ $translates['units'] }}
                                        </th>
                                    @endif

                                    @php($used_top = false)
                                    @foreach($features as $feature)
                                        @if($feature['type'] == 2 && !$used_top && $feature['is_sub'])
                                            @php($used_top = true)
                                            @foreach($feature['group'] as $group)
                                                <th style="word-wrap: break-word; font-size: 10px; padding: 0.45rem; font-weight: bold; text-align: left;">
                                                    {{ $group['name']}}
                                                </th>
                                            @endforeach
                                        @endif
                                    @endforeach
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($features as $feature)
                                    @if($feature['type'] == 2 && $feature['is_sub'])
                                        <tr>
                                            <td style="word-wrap: break-word; border-top: 1px solid rgba(0, 0, 0, 0.1); padding: 0.75rem 0.4375rem; font-weight: bold; word-break: break-word; font-size: 10px;">
                                                {{ $feature['name'] }}
                                            </td>
                                            <td style="word-wrap: break-word; border-top: 1px solid rgba(0, 0, 0, 0.1); padding: 0.75rem 0.45rem; font-weight: bold; word-break: break-word; font-size: 10px; text-align: left;">
                                                {{ $feature['unit'] }}
                                            </td>
                                            @foreach($feature['group'] as $group)
                                                <td style="word-wrap: break-word; border-top: 1px solid rgba(0, 0, 0, 0.1); padding: 0.75rem 0.45rem; font-weight: bold; word-break: break-word; font-size: 10px; text-align: left;">
                                                    {{ $group['value'] ?? null }}
                                                </td>
                                            @endforeach
                                        </tr>
                                    @endif
                                @endforeach
{{--                                <tr>--}}
{{--                                    <td style="border-top: 1px solid rgba(0, 0, 0, 0.1);"></td>--}}
{{--                                    <td style="border-top: 1px solid rgba(0, 0, 0, 0.1);"></td>--}}
{{--                                    @php($used = false)--}}
{{--                                    @foreach($features as $feature)--}}
{{--                                        @if($feature['type'] == 2 && !$used && $feature['is_sub'])--}}
{{--                                            @php($used = true)--}}
{{--                                            @foreach($feature['group'] as $group)--}}
{{--                                                <td style="border-top: 1px solid rgba(0, 0, 0, 0.1);"></td>--}}
{{--                                            @endforeach--}}
{{--                                        @endif--}}
{{--                                    @endforeach--}}
{{--                                </tr>--}}
                                </tbody>
                            </table>
                    </td>
                @else
                    <td style="padding: 0.75rem 0.4375rem; color: #367c2b; font-weight: bold; word-wrap: break-word; width: 35%;">
                        {{ $translates['demo_resultes'] }}
                    </td>
                    <td style="padding: 0.75rem 0.4375rem; font-weight: bold; word-wrap: break-word; width: 65%;">
                        {{ $demo_result }}
                    </td>
                @endif
            </tr>

        <tr style="border: none;">
            <td style="padding: 0.75rem 0.4375rem; color: #367c2b; font-weight: bold; word-wrap: break-word;  width: 35%;">
                {{ $translates['client_comment'] }}
            </td>
            <td style="padding: 0.75rem 0.4375rem; font-weight: bold; word-wrap: break-word; width: 65%;">
                {{ $client_comment }}
            </td>
        </tr>
    </table>
</div>
<!-- image block -->
@if(isset($images['working_hours_at_the_beg']))
    <div style="display: block; page-break-inside: avoid;">
        <div style="font-size: 1rem; color: #367c2b; line-height: 1.2em; font-weight: bold; width: 100%; padding: 1.25rem 0;">
            {{ $translates['working_hours_at_the_beg'] }}
        </div>
        <div style="width: 100%; margin: {{ count($images['working_hours_at_the_beg']) > 1 ? '60px' : 0 }} 0 0;">
            <table style="border-collapse: collapse; width: 100%;">
				<tr>
					<td>
						<?php $count = 0 ?>
						@foreach($images['working_hours_at_the_beg'] as $image)
							@if($count < 2)
								<div style="margin-right: 20px; width: 280px; height: 280px; page-break-inside: avoid; display: inline-block; position: relative;">
									<div style="position: absolute; width: 100%; height: 100%; display: flex; align-items: center; overflow: hidden;">
										<img style="object-fit: cover; width: 100%; height: 100%; display: block; margin-right: 20px;" src="{{ $image }}" alt="">
									</div>
								</div>
								<?php $count++?>
							@endif
						@endforeach
					</td>
				</tr>
			</table>
		</div>
        <div style="width: 100%; margin-top: {{ count($images['working_hours_at_the_beg']) > 2 ? count($images['working_hours_at_the_beg']) > 3 ? '20px' : '-20px' : 0 }};">
            <?php $countSec = 0 ?>
            @foreach($images['working_hours_at_the_beg'] as $key => $image)
                @if($countSec >= 2)
                    <div style="margin-right: 20px; width: 280px; height: 280px; page-break-inside: avoid; display: inline-block; position: relative;">
                        <div style="position: absolute; width: 100%; height: 100%; display: flex; align-items: center; overflow: hidden;">
                            <img style="object-fit: cover; width: 100%; height: 100%; display: block; margin-right: 20px;" src="{{ $image }}" alt="">
                        </div>
                    </div>
                @endif
                <?php $countSec++?>
            @endforeach
        </div>
    </div>
@endif
<!-- image block -->
@if(isset($images['working_hours_at_the_end']))
    <div style="display: block; page-break-inside: avoid;">
        <div style="font-size: 1rem; color: #367c2b; line-height: 1.2em; font-weight: bold; width: 100%; padding: 1.25rem 0;">
            {{ $translates['working_hours_at_the_end'] }}
        </div>
        <div style="width: 100%; margin: {{ count($images['working_hours_at_the_end']) > 1 ? '60px' : 0 }} 0 0;">
            <table style="border-collapse: collapse; width: 100%;">
				<tr>
					<td>
						<?php $count = 0 ?>
						@foreach($images['working_hours_at_the_end'] as $image)
							@if($count < 2)
								<div style="margin-right: 20px; width: 280px; height: 280px; page-break-inside: avoid; display: inline-block; position: relative;">
									<div style="position: absolute; width: 100%; height: 100%; display: flex; align-items: center; overflow: hidden;">
										<img style="width: 100%; height: 100%; display: block; margin-right: 20px;" src="{{ $image }}" alt="">
									</div>
								</div>
								<?php $count++?>
							@endif
						@endforeach
					</td>
				</tr>
			</table>
		</div>
        <div style="width: 100%; margin-top: {{ count($images['working_hours_at_the_end']) > 2 ? count($images['working_hours_at_the_end']) > 3 ? '20px' : '-20px' : 0 }};">
            <?php $countSec = 0 ?>
            @foreach($images['working_hours_at_the_end'] as $key => $image)
                @if($countSec >= 2)
                    <div style="margin-right: 20px; width: 280px; height: 280px; page-break-inside: avoid; display: inline-block; position: relative;">
                        <div style="position: absolute; width: 100%; height: 100%; display: flex; align-items: center; overflow: hidden;">
                            <img style="object-fit: cover; width: 100%; height: 100%; display: block; margin-right: 20px;" src="{{ $image }}" alt="">
                        </div>
                    </div>
                @endif
                <?php $countSec++?>
            @endforeach
        </div>
    </div>
@endif
<!-- image block -->
@if(isset($images['equipment_on_the_field']))
    <div style="display: block; page-break-inside: avoid;">
        <div style="font-size: 1rem; color: #367c2b; line-height: 1.2em; font-weight: bold; width: 100%; padding: 1.25rem 0;">
            {{ $translates['equipment_on_the_field'] }}
        </div>
		<div style="width: 100%; margin: {{ count($images['equipment_on_the_field']) > 1 ? '60px' : 0 }} 0 0;">
			<table style="border-collapse: collapse; width: 100%;">
				<tr>
					<td>
						<?php $count = 0 ?>
						@foreach($images['equipment_on_the_field'] as $image)
							@if($count < 2)
								<div style="margin-right: 20px; width: 280px; height: 280px; page-break-inside: avoid; display: inline-block; position: relative;">
									<div style="position: absolute; width: 100%; height: 100%; display: flex; align-items: center; overflow: hidden;">
										<img style="object-fit: cover; width: 100%; height: 100%; display: block; margin-right: 20px;" src="{{ $image }}" alt="">
									</div>
								</div>
								<?php $count++?>
							@endif
						@endforeach
					</td>
				</tr>
			</table>
		</div>
        <div style="width: 100%; margin-top: {{ count($images['equipment_on_the_field']) > 2 ? count($images['equipment_on_the_field']) > 3 ? '20px' : '-20px' : 0 }};">
            <?php $countSec = 0 ?>
            @foreach($images['equipment_on_the_field'] as $key => $image)
                @if($countSec >= 2)
                    <div style="margin-right: 20px; width: 280px; height: 280px; page-break-inside: avoid; display: inline-block; position: relative;">
                        <div style="position: absolute; width: 100%; height: 100%; display: flex; align-items: center; overflow: hidden;">
                            <img style="object-fit: cover; width: 100%; height: 100%; display: block; margin-right: 20px;" src="{{ $image }}" alt="">
                        </div>
                    </div>
                @endif
                <?php $countSec++?>
            @endforeach
        </div>
    </div>
@endif
<!-- image block -->
@if(isset($images['me_and_equipment']))
    <div style="display: block; page-break-inside: avoid;">
        <div style="font-size: 1rem; color: #367c2b; line-height: 1.2em; font-weight: bold; width: 100%; padding: 1.25rem 0;">
            {{ $translates['me_and_equipment'] }}
        </div>
        <div style="width: 100%; margin: {{ count($images['me_and_equipment']) > 1 ? '60px' : 0 }} 0 0;">
            <table style="border-collapse: collapse; width: 100%;">
				<tr>
					<td>
						<?php $count = 0 ?>
						@foreach($images['me_and_equipment'] as $image)
							@if($count < 2)
								<div style="margin-right: 20px; width: 280px; height: 280px; page-break-inside: avoid; display: inline-block; position: relative;">
									<div style="position: absolute; width: 100%; height: 100%; display: flex; align-items: center; overflow: hidden;">
										<img style="object-fit: cover; width: 100%; height: 100%; display: block; margin-right: 20px;" src="{{ $image }}" alt="">
									</div>
								</div>
								<?php $count++?>
							@endif
						@endforeach
					</td>
				</tr>
			</table>
		</div>
        <div style="width: 100%; margin-top: {{ count($images['me_and_equipment']) > 2 ? count($images['me_and_equipment']) > 3 ? '20px' : '-20px' : 0 }};">
            <?php $countSec = 0 ?>
            @foreach($images['me_and_equipment'] as $key => $image)
                @if($countSec >= 2)
                    <div style="margin-right: 20px; width: 280px; height: 280px; page-break-inside: avoid; display: inline-block; position: relative;">
                        <div style="position: absolute; width: 100%; height: 100%; display: flex; align-items: center; overflow: hidden;">
                            <img style="object-fit: cover; width: 100%; height: 100%; display: block; margin-right: 20px;" src="{{ $image }}" alt="">
                        </div>
                    </div>
                @endif
                <?php $countSec++?>
            @endforeach
        </div>
    </div>
@endif
<!-- image block -->
@if(isset($images['others']))
    <div style="display: block; page-break-inside: avoid;">
        <div style="font-size: 1rem; color: #367c2b; line-height: 1.2em; font-weight: bold; width: 100%; padding: 1.25rem 0;">
            {{ $translates['others'] }}
        </div>
        <?php $count = 0 ?>
        <div style="width: 100%; margin: {{ count($images['others']) > 1 ? '60px' : 0 }} 0 0;">
            <table style="border-collapse: collapse; width: 100%;">
				<tr>
					<td>
						<?php $count = 0 ?>
						@foreach($images['others'] as $image)
							@if($count < 2)
								<div style="margin-right: 20px; width: 280px; height: 280px; page-break-inside: avoid; display: inline-block; position: relative;">
									<div style="position: absolute; width: 100%; height: 100%; display: flex; align-items: center; overflow: hidden;">
										<img style="object-fit: cover; width: 100%; height: 100%; display: block; margin-right: 20px;" src="{{ $image }}" alt="">
									</div>
								</div>
								<?php $count++?>
							@endif
						@endforeach
					</td>
				</tr>
			</table>
		</div>
        <div style="width: 100%; margin-top: {{ count($images['others']) > 2 ? count($images['others']) > 3 ? '20px' : '-20px' : 0 }};">
            <?php $countSec = 0 ?>
            @foreach($images['others'] as $key => $image)
                @if($countSec >= 2)
                    <div style="margin-right: 20px; width: 280px; height: 280px; page-break-inside: avoid; display: inline-block; position: relative;">
                        <div style="position: absolute; width: 100%; height: 100%; display: flex; align-items: center; overflow: hidden;">
                            <img style="object-fit: cover; width: 100%; height: 100%; display: block; margin-right: 20px;" src="{{ $image }}" alt="">
                        </div>
                    </div>
                @endif
                <?php $countSec++?>
            @endforeach
        </div>
    </div>
@endif
<!-- Video -->
@if(isset($video))
    <div style="page-break-inside: avoid; width: 100%; padding: 1.25rem 0;">
        <!-- title -->
        <div style="font-size: 1rem; color: #367c2b; font-weight: bold; line-height: 1.2em;">
            {{ $translates['video'] }}
        </div>
        <div style="padding: 1.25rem 0;">
            <div>
				<span style="font-size: 1rem; margin-right: 10px; font-weight: bold; line-height: 1.2em;">
					{{ $translates['download_link'] }}
				</span>
{{--				 @todo подставить ссылку на скачивание видео--}}
                <a href="#" target="_blank" style="text-decoration: none; color: #367c2b; font-size: 1rem; line-height: 1.25em;">
                    {{ $video }}
                </a>
            </div>
            <div>
                <!-- todo тут qr код -->
                <img style="max-width: 280px; margin-bottom: 20px; margin-top: 1.5rem; max-height: 280px;"
                     src="data:image/png;base64, {!! base64_encode(SimpleSoftwareIO\QrCode\Facades\QrCode::format('png')->size(100)->generate($video)) !!} "
                     alt="" download>
            </div>
        </div>
    </div>
@endif

@if($disclaimer)
    <!-- Disclaimer -->
    <div style="page-break-inside: avoid; width: 100%; margin: 1.75rem 0;">
        <!-- title -->
        <span style="font-size: 1rem; color: #367c2b; margin-right: 10px; font-weight: bold; line-height: 1.2em;">
            {{ $disclaimerTitle }}
        </span>
        <span style="font-size: 1rem; font-weight: bold; line-height: 1.2em;">
            {{ $disclaimer }}
        </span>
    </div>
@endif

@if(isset($images['signature']))
    <div style="display: block; page-break-inside: avoid;">
        <div style="font-size: 1rem; color: #367c2b; line-height: 1.2em; font-weight: bold; width: 100%; margin: 0 0 1.25rem;">
            {{ $translates['signature'] }}
        </div>
        <div style="margin-right: 20px; width: 280px; height: 280px; page-break-inside: avoid; display: inline-block; position: relative;">
            <div style="position: absolute; width: 100%; height: 100%; display: flex; align-items: center; overflow: hidden;">
                <img style="width: 100%; height: 100%;" src="{{ $images['signature'] }}" alt="">
            </div>
        </div>
    </div>
@endif
<div>
    @foreach($customers ?? [] as $customer)
        <table style="page-break-inside: avoid; border-collapse: collapse; margin: 1.5625rem 0 2.5rem; width: 100%; font-size: 1rem; line-height: 1.25em;">
            <tr>
                <td style="width: 100%; padding: 0.75rem 0; font-weight: bold; word-break: break-word;">
                    {{ $customer['first_name'] }} {{ $customer['last_name'] ?? null }}
                </td>
            </tr>
            <tr>
                <td style="width: 100%; padding: 0.75rem 0; word-break: break-word;">
                    {{ $customer['company_name'] }}
                </td>
            </tr>
        </table>
    @endforeach
</div>
</body>
</html>
