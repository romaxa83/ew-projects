<tr>
    <td>
        <table border="0" cellspacing="0"
               cellpadding="0" width="100%"
               style="padding: 35px 30px; text-align: center" role="presentation">

            <tr>
                <td>
                    <table border="0" cellspacing="0"
                           cellpadding="0" width="100%"
                           style="padding: 35px 30px; text-align: center" role="presentation">
                        @foreach($additional_info as $key => $value)
                            <tr>
                                <td style="padding:0;Margin:0;padding-top:10px; @if($loop->last) {{ 'padding-bottom:10px;' }} @endif">
                                    <p style="text-align: left !important; padding-left: 12px !important;Margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-family:arial, 'helvetica neue', helvetica, sans-serif;line-height:21px;color:#333333;font-size:14px">
                                        {{$key}}:
                                    </p>
                                </td>
                                <td style="padding:0;Margin:0;padding-top:10px; @if($loop->last) {{ 'padding-bottom:10px;' }} @endif">
                                    <p style="text-align: left !important; padding-left: 12px !important;Margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-family:arial, 'helvetica neue', helvetica, sans-serif;line-height:21px;color:#333333;font-size:14px">
                                        <strong>{{$value}}</strong>
                                    </p>
                                </td>
                            </tr>
                        @endforeach
                    </table>
                </td>
            </tr>
        </table>
    </td>
</tr>


