<tr>
    <td>
        <table border="0" cellspacing="0"
               cellpadding="0" width="100%"
               style="padding: 35px 30px; text-align: center">

            @isset($greeting)
                @include('notifications::components.greeting')
            @endisset

            @isset($introLines)
                @if(count($introLines))
                    @include('notifications::components.lines')
                @endif
            @endisset

            @isset($actionText)
                <tr>
                    <td>
                        <a class="button"
                           href="{{ $actionUrl }}"
                           target="_blank"
                           rel="noopener noreferrer"
                           data-auth="NotApplicable"
                           data-linkindex="0">
                            {{$actionText}}
                        </a>
                    </td>
                </tr>
            @endisset


            {{--<tr>--}}
            {{--    <td>--}}
            {{--        <span class="content-description">Please click the button below to verify your email address:</span>--}}
            {{--        <a class="content-link" href="#"--}}
            {{--           target="_blank"--}}
            {{--           rel="noopener noreferrer"--}}
            {{--           data-auth="NotApplicable"--}}
            {{--           data-linkindex="0">--}}
            {{--            Link--}}
            {{--        </a>--}}
            {{--    </td>--}}
            {{--</tr>--}}

            @isset($additional_info)
                @include('vendor.notifications.components.additional-block')
            @endisset

            <tr>
                    <td>
                        <p class="content-regards">
                            {{__('messages.regards')}},
                            {{ config('mail.from.name') }}
                            !
                        </p>
                    </td>
                </tr>
        </table>
    </td>
</tr>