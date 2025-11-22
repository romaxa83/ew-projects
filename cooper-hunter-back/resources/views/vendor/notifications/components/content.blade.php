<table align="center" border="0" cellspacing="0" cellpadding="0" width="100%"
       style="background-color: #f6f6f6; padding: 25px 0;">
    <tr>
        <td>
            <table class="table-content" align="center" cellspacing="0" cellpadding="0" width="600px"
                   style="border-left: 1px solid #e2e4e6; border-right: 1px solid #e2e4e6; border-bottom: 1px solid #e2e4e6; border-radius: 4px; border-collapse: collapse; max-width: 600px;">
                <tr>
                    <td>
                        <table class="table-content" cellspacing="0" cellpadding="0" width="600px"
                               style="border-left: 1px solid #d9dbdd; border-right: 1px solid #d9dbdd; border-bottom: 1px solid #d9dbdd; border-radius: 4px; border-collapse: collapse;">
                            <tr>
                                <td>
                                    <table class="table-content" cellspacing="0" cellpadding="0" width="600px"
                                           style="border-left: 1px solid #cecfd1; border-right: 1px solid #cecfd1; border-bottom: 1px solid #cecfd1; border-radius: 4px; border-collapse: collapse;">
                                        <tr>
                                            <td>
                                                <table class="table-content" cellspacing="0" cellpadding="0"
                                                       width="600px"
                                                       style="border-left: 1px solid #c5c7c9; border-right: 1px solid #c5c7c9; border-bottom: 1px solid #c5c7c9; border-radius: 4px; border-collapse: collapse;">
                                                    <tr>
                                                        <td>
                                                            <table class="table-content" border="0" cellspacing="0"
                                                                   cellpadding="0" width="600px"
                                                                   style="max-width: 600px; background-color: #ffffff; border-radius: 4px; border-collapse: collapse;">

                                                                @if(isset($warranty))
                                                                    <tr>
                                                                        <td>
                                                                            @include('notifications::components.warranty-block')
                                                                        </td>
                                                                    </tr>
                                                                @else
                                                                    @include('notifications::components.content-block')
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
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
