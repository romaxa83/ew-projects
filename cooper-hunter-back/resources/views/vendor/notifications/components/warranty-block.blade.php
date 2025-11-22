@php
    use App\Models\Technicians\Technician;
    use App\Models\Warranty\WarrantyRegistration;

    /**
     * @var $warranty WarrantyRegistration
     */
@endphp

<table width="100%" cellspacing="0" cellpadding="0">
    <tr>
        <td valign="top">
            <table cellspacing="0" cellpadding="0"
                   style="font-family: arial, 'helvetica neue', helvetica, sans-serif; margin: 0 auto;">
                <tr>
                    <td style="padding: 0; margin: 0;">
                        <table width="600px">
                            <tr>
                                <td style="margin: 0; padding: 20px 20px 0;">
                                    <table>
                                        <tr>
                                            <td>
                                                <table width="100%" cellspacing="0" cellpadding="0">
                                                    <tr>
                                                        <td style="padding: 0; margin: 0;">
                                                            <p style="margin: 0; line-height: 20px; color: #333333;">{{ __('messages.warranty.notification.greeting') }}</p>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td style="padding: 0; margin: 0; padding-top: 10px; padding-bottom: 10px;">
                                                            <p style="margin: 0; line-height: 20px; color: #333333; font-size: 15px;">
                                                                <strong>
                                                                    "{{ $warranty->warranty_status->description }}"
                                                                </strong>
                                                            </p>
                                                        </td>
                                                    </tr>
                                                    @if(($warrantyNotice = $warranty->notice) && $warranty->warranty_status->hasNotice())
                                                        <tr>
                                                            <td style="padding: 0; margin: 0;">
                                                                <p style="margin: 0; line-height: 17px; color: #333333; font-size: 12px;">
                                                                    <b>
                                                                        <b>
                                                                            {{ __('messages.warranty.notification.reason') }}:&nbsp;{{ $warrantyNotice }}
                                                                        </b>
                                                                    </b>
                                                                </p>
                                                            </td>
                                                        </tr>
                                                    @endif
                                                    <tr>
                                                        <td style="padding: 0; margin: 0; padding-top: 10px; padding-bottom: 10px;">
                                                            <p style="margin: 0; line-height: 33px; color: #333333; font-size: 22px;">
                                                                <b>
                                                                    <b>{{ __('messages.warranty.notification.limited_warranty') }}</b>
                                                                </b>
                                                            </p>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td style="padding: 0; margin: 0;">
                                                            <p style="margin: 0; line-height: 18px; color: #333333; font-size: 12px;">
                                                                <strong>{{ __('messages.warranty.notification.warranty_service_disclaimer') }}</strong>
                                                            </p>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td style="padding: 0; margin: 0; padding-top: 10px; padding-bottom: 10px;">
                                                            <p style="margin: 0; line-height: 21px; color: #333333; font-size: 14px;">
                                                                <u>{{ __('messages.warranty.notification.customer_details') }}</u>
                                                            </p>
                                                        </td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                            <tr>
                                <td style="padding: 0; margin: 0; padding-bottom: 10px; padding-left: 20px; padding-right: 20px;">
                                    <table cellpadding="0" cellspacing="0" style="float: left;">
                                        <tr>
                                            <td style="padding: 0; margin: 0; width: 270px;">
                                                <table cellpadding="0" cellspacing="0" width="100%">
                                                    <tr>
                                                        <td style="padding: 0; margin: 0;">
                                                            <p style="margin: 0; line-height: 21px; color: #333333; font-size: 14px;">
                                                                <b>
                                                                    <b>{{ __('messages.warranty.notification.first_name') }}:&nbsp;</b>
                                                                </b>
                                                                {{ $warranty->user_info->first_name }}
                                                            </p>
                                                        </td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
                                    </table>
                                    <table cellpadding="0" cellspacing="0" style="float: right;">
                                        <tr>
                                            <td style="padding: 0; margin: 0; width: 270px;">
                                                <table cellpadding="0" cellspacing="0" width="100%">
                                                    <tr>
                                                        <td style="padding: 0; margin: 0;">
                                                            <p style="margin: 0; line-height: 21px; color: #333333; font-size: 14px;">
                                                                <b>
                                                                    <b>{{ __('messages.warranty.notification.last_name') }}:&nbsp;</b>
                                                                </b>
                                                                {{ $warranty->user_info->last_name }}
                                                            </p>
                                                        </td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                            <tr>
                                <td style="padding: 0; margin: 0; padding-bottom: 10px; padding-left: 20px; padding-right: 20px;">
                                    <table cellpadding="0" cellspacing="0" style="float: left;">
                                        <tr>
                                            <td style="padding: 0; margin: 0; width: 270px;">
                                                <table cellpadding="0" cellspacing="0" width="100%">
                                                    <tr>
                                                        <td style="padding: 0; margin: 0;">
                                                            <p style="margin: 0; line-height: 21px; color: #333333; font-size: 14px;">
                                                                <b>
                                                                    <b>{{ __('messages.warranty.notification.email') }}:&nbsp;</b>
                                                                </b>
                                                                <a href="mailto:DLCOTHERN@BCHSI.ORG"
                                                                   style="text-decoration: underline; color: #0054a3; font-size: 14px;">
                                                                    <u>{{ $warranty->user_info->email }}</u>
                                                                </a>
                                                            </p>
                                                        </td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
                                    </table>
                                    {{--                                    <table cellpadding="0" cellspacing="0" style="float: right;">--}}
                                    {{--                                        <tr>--}}
                                    {{--                                            <td style="padding: 0; margin: 0; width: 270px;">--}}
                                    {{--                                                <table cellpadding="0" cellspacing="0" width="100%">--}}
                                    {{--                                                    <tr>--}}
                                    {{--                                                        <td style="padding: 0; margin: 0;">--}}
                                    {{--                                                            <p style="margin: 0; line-height: 21px; color: #333333; font-size: 14px;">--}}
                                    {{--                                                                <b>--}}
                                    {{--                                                                    <b>{{ __('messages.warranty.notification.phone') }}:&nbsp;</b>--}}
                                    {{--                                                                </b>--}}
                                    {{--                                                                <a href="tel:+1-786-953-6706"--}}
                                    {{--                                                                   style="text-decoration: underline; color: #0054a3; font-size: 14px;">+1-786-953-6706</a>--}}
                                    {{--                                                            </p>--}}
                                    {{--                                                        </td>--}}
                                    {{--                                                    </tr>--}}
                                    {{--                                                </table>--}}
                                    {{--                                            </td>--}}
                                    {{--                                        </tr>--}}
                                    {{--                                    </table>--}}
                                </td>
                            </tr>
                            <tr>
                                <td style="padding: 0; margin: 0; padding-bottom: 10px; padding-left: 20px; padding-right: 20px;">
                                    <table cellpadding="0" cellspacing="0" style="float: left;">
                                        <tr>
                                            <td style="padding: 0; margin: 0; width: 270px;">
                                                <table cellpadding="0" cellspacing="0" width="100%">
                                                    <tr>
                                                        <td style="padding: 0; margin: 0;">
                                                            <p style="margin: 0; line-height: 21px; color: #333333; font-size: 14px;">
                                                                <b>
                                                                    <b>{{ __('messages.warranty.notification.address') }}:&nbsp;</b>
                                                                </b>
                                                                {{ $warranty->address->street }}
                                                            </p>
                                                        </td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
                                    </table>
                                    <table cellpadding="0" cellspacing="0" style="float: right;">
                                        <tr>
                                            <td style="padding: 0; margin: 0; width: 270px;">
                                                <table cellpadding="0" cellspacing="0" width="100%">
                                                    <tr>
                                                        <td style="padding: 0; margin: 0;">
                                                            <p style="margin: 0; line-height: 21px; color: #333333; font-size: 14px;">
                                                                <b>
                                                                    <b>{{ __('messages.warranty.notification.city') }}:&nbsp;</b>
                                                                </b>
                                                                {{ $warranty->address->city }}
                                                            </p>
                                                        </td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                            <tr>
                                <td style="padding: 0; margin: 0; padding-bottom: 10px; padding-left: 20px; padding-right: 20px;">
                                    @if($warrantyCompanyName = $warranty->user_info->company_name)
                                        <table cellpadding="0" cellspacing="0" style="float: left;">
                                            <tr>
                                                <td style="padding: 0; margin: 0; width: 270px;">
                                                    <table cellpadding="0" cellspacing="0" width="100%">
                                                        <tr>
                                                            <td style="padding: 0; margin: 0;">
                                                                <p style="margin: 0; line-height: 21px; color: #333333; font-size: 14px;">
                                                                    <b>
                                                                        <b>{{ __('messages.warranty.notification.contractor_name') }}:&nbsp;</b>
                                                                    </b>
                                                                    {{ $warrantyCompanyName }}
                                                                </p>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                        </table>
                                    @endif
                                    @if($warranty->member_type === Technician::MORPH_NAME && $member = $warranty->member)
                                        <table cellpadding="0" cellspacing="0" style="float: right;">
                                            <tr>
                                                <td style="padding: 0; margin: 0; width: 270px;">
                                                    <table cellpadding="0" cellspacing="0" width="100%">
                                                        <tr>
                                                            <td style="padding: 0; margin: 0;">
                                                                <p style="margin: 0; line-height: 21px; color: #333333; font-size: 14px;">
                                                                    <b>
                                                                        <b>{{ __('messages.warranty.notification.license') }}
                                                                            #:&nbsp;</b>
                                                                    </b>
                                                                    {{ /** @var $member Technician */ $member->getLicense() }}
                                                                </p>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                        </table>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td style="padding: 0; margin: 0; padding-left: 20px; padding-right: 20px;">
                                    <table cellpadding="0" cellspacing="0" width="100%">
                                        <tr>
                                            <td valign="top" style="padding: 0; margin: 0; width: 560px;">
                                                <table cellpadding="0" cellspacing="0" width="100%">
                                                    <tr>
                                                        <td style="padding: 0; margin: 0; padding-top: 10px; padding-bottom: 10px;">
                                                            <p style="margin: 0; line-height: 21px; color: #333333; font-size: 14px;">
                                                                <u>{{ __('messages.warranty.notification.installation_details') }}</u>
                                                            </p>
                                                        </td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                            <tr>
                                <td style="padding: 0; margin: 0; padding-bottom: 10px; padding-left: 20px; padding-right: 20px;">
                                    <table cellpadding="0" cellspacing="0" style="float: left;">
                                        <tr>
                                            <td style="padding: 0; margin: 0; width: 270px;">
                                                <table cellpadding="0" cellspacing="0" width="100%">
                                                    <tr>
                                                        <td style="padding: 0; margin: 0;">
                                                            <p style="margin: 0; line-height: 21px; color: #333333; font-size: 14px;">
                                                                <b>
                                                                    <b>{{ __('messages.warranty.notification.purchase_date') }}:&nbsp;</b>
                                                                </b>
                                                                {{ $warranty->product_info->getPurchaseDateAsFormat() }}
                                                            </p>
                                                        </td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
                                    </table>
                                    <table cellpadding="0" cellspacing="0" style="float: right;">
                                        <tr>
                                            <td style="padding: 0; margin: 0; width: 270px;">
                                                <table cellpadding="0" cellspacing="0" width="100%">
                                                    <tr>
                                                        <td style="padding: 0; margin: 0;">
                                                            <p style="margin: 0; line-height: 21px; color: #333333; font-size: 14px;">
                                                                <b>
                                                                    <b>{{ __('messages.warranty.notification.purchase_place') }}:&nbsp;</b>
                                                                </b>
                                                                {{ $warranty->product_info->purchase_place }}
                                                            </p>
                                                        </td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                            <tr>
                                <td style="padding: 0; margin: 0; padding-bottom: 10px; padding-left: 20px; padding-right: 20px;">
                                    <table cellpadding="0" cellspacing="0" style="float: left;">
                                        <tr>
                                            <td style="padding: 0; margin: 0; width: 270px;">
                                                <table cellpadding="0" cellspacing="0" width="100%">
                                                    <tr>
                                                        <td style="padding: 0; margin: 0;">
                                                            <p style="margin: 0; line-height: 21px; color: #333333; font-size: 14px;">
                                                                <b>
                                                                    <b>{{ __('messages.warranty.notification.installation_date') }}:&nbsp;</b>
                                                                </b>
                                                                {{ $warranty->product_info->getInstallationDateAsFormat() }}
                                                            </p>
                                                        </td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
                                    </table>
                                    <table cellpadding="0" cellspacing="0" style="float: right;">
                                        <tr>
                                            <td style="padding: 0; margin: 0; width: 270px;">
                                                <table cellpadding="0" cellspacing="0" width="100%">
                                                    <tr>
                                                        <td style="padding: 0; margin: 0;">
                                                            <p style="margin: 0; line-height: 21px; color: #333333; font-size: 14px;">
                                                                <b>
                                                                    <b>{{ __('messages.warranty.notification.warranty_submitted') }}:&nbsp;</b>
                                                                </b>
                                                                {{ $warranty->created_at?->format('m/d/Y') }}
                                                            </p>
                                                        </td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
                                    </table>
                                    <!--[if mso]>
                                    </td>
                                    </tr>
                                    </table>
                                    <![endif]-->
                                </td>
                            </tr>
                            <tr>
                                <td style="padding: 0; margin: 0; padding-left: 20px; padding-right: 20px;">
                                    <table cellpadding="0" cellspacing="0" width="100%">
                                        <tr>
                                            <td valign="top" style="padding: 0; margin: 0; width: 560px;">
                                                <table cellpadding="0" cellspacing="0" width="100%">
                                                    <tr>
                                                        <td style="padding: 0; margin: 0; padding-top: 10px; padding-bottom: 10px;">
                                                            <p style="margin: 0; line-height: 21px; color: #333333; font-size: 14px;">
                                                                <u>{{ __('messages.warranty.notification.registered_products') }}</u>
                                                            </p>
                                                        </td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                            @foreach($warranty->units as $unit)
                                <tr>
                                    <td style="padding: 0; margin: 0; padding-bottom: 10px; padding-left: 20px; padding-right: 20px;">
                                        <table cellpadding="0" cellspacing="0" style="float: left;">
                                            <tr>
                                                <td style="padding: 0; margin: 0; width: 220px;">
                                                    <table cellpadding="0" cellspacing="0" width="100%">
                                                        <tr>
                                                            <td style="padding: 0; margin: 0;">
                                                                <p style="margin: 0; line-height: 21px; color: #333333; font-size: 14px;">
                                                                    <b>
                                                                        <b>{{ __('messages.warranty.notification.model_name') }}:&nbsp;</b>
                                                                    </b>
                                                                    {{ $unit->title }}
                                                                </p>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                        </table>
                                        <table cellpadding="0" cellspacing="0" style="float: right;">
                                            <tr>
                                                <td style="padding: 0; margin: 0; width: 320px;">
                                                    <table cellpadding="0" cellspacing="0" width="100%">
                                                        <tr>
                                                            <td style="padding: 0; margin: 0;">
                                                                <p style="margin: 0; line-height: 21px; color: #333333; font-size: 14px;">
                                                                    <b>
                                                                        <b>{{ __('messages.warranty.notification.serial_number') }}:&nbsp;</b>
                                                                    </b>
                                                                    {{ $unit->unit->serial_number }}
                                                                </p>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            @endforeach

                            <tr>
                                <td style="padding: 0; margin: 0; padding-top: 20px; padding-left: 20px; padding-right: 20px;">
                                    <table cellpadding="0" cellspacing="0" width="100%">
                                        <tr>
                                            <td valign="top" style="padding: 0; margin: 0; width: 560px;">
                                                <table cellpadding="0" cellspacing="0" width="100%">
                                                    <tr>
                                                        <td style="padding: 0; margin: 0;">
                                                            <p style="margin: 0; line-height: 21px; color: #f10903; font-size: 14px;">
                                                                <b>
                                                                    <b>*{{ __('messages.warranty.notification.faq_note') }} </b>
                                                                </b>
                                                                <a href="{{ config('front_routes.faq') }}"
                                                                   style="text-decoration: underline; color: #3d85c6; font-size: 14px;">
                                                                    <b>
                                                                        <b>
                                                                            <u>{{ config('front_routes.faq') }}</u>
                                                                        </b>
                                                                    </b>
                                                                </a>
                                                            </p>
                                                        </td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
