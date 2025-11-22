<table cellpadding="0" cellspacing="0" class="es-content" align="center" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;table-layout:fixed !important;width:100%">
    <tr style="border-collapse:collapse">
        <td align="center" style="padding:0;Margin:0">
            <table bgcolor="#ffffff" class="es-content-body" align="center" cellpadding="0" cellspacing="0" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;background-color:#FFFFFF;">
                <tr style="border-collapse:collapse">
                    <td align="left" style="Margin:0;padding:0px">
                        <table cellpadding="0" cellspacing="0" width="100%" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px">
                            <tr style="border-collapse:collapse">
                                <td align="center" valign="top" style="padding:0;Margin:0;width:560px">
                                    <table cellpadding="0" cellspacing="0" width="100%" role="presentation" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px">
                                        <tr style="border-collapse:collapse">
                                            <td align="center" style="padding:0;Margin:0;padding-top:10px;padding-bottom:10px;font-size:0">
                                                <table border="0" width="100%" height="100%" cellpadding="0" cellspacing="0" role="presentation" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px">
                                                    <tr style="border-collapse:collapse">
                                                        <td style="padding:0;Margin:0;border-bottom-width:1px;border-bottom-style:solid;border-bottom-color:#CCCCCC;background-image:none;height:1px;width:100%;margin:0px;background-position:initial initial;background-repeat:initial initial"></td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
                                        <tr style="border-collapse:collapse">
                                            <td align="left" style="padding:0;Margin:0;padding-top:5px;padding-bottom:5px">
                                                <p style="Margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-size:14px;font-family:arial, 'helvetica neue', helvetica, sans-serif;line-height:21px;color:#999999">Thank you for choosing us. We greatly value your business and look forward to serving you again.
                                                </p>
                                            </td>
                                        </tr>
                                        @if(isset($companyName))
                                            <tr style="border-collapse:collapse">
                                                <td align="left" style="padding:0;Margin:0;padding-top:5px">
                                                    <p style="Margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-size:14px;font-family:arial, 'helvetica neue', helvetica, sans-serif;line-height:21px;color:#999999">If you have any questions please contact "{{ $companyName }}"</p>
                                                </td>
                                            </tr>
                                        @endif
                                        @if(isset($companyContactString))
                                            <tr style="border-collapse:collapse">
                                                <td align="left" style="padding:0;Margin:0">
                                                    <p style="Margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-size:14px;font-family:arial, 'helvetica neue', helvetica, sans-serif;line-height:21px;color:#999999">
                                                        {{ $companyContactString }}
                                                    </p>
                                                </td>
                                            </tr>
                                        @endif
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
