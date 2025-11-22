<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta content="width=device-width, initial-scale=1" name="viewport">
    <meta name="x-apple-disable-message-reformatting">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta content="telephone=no" name="format-detection">
    <title>{{ config('app.name') }}</title>
    <style type="text/css">
        .logo {
            width: 30% !important;
            height: auto !important;
            max-height: fit-content !important;
        }

        .content-title {
            box-sizing: border-box;
            margin: 0;
            line-height: 48px;
            font-family: arial, helvetica, sans-serif;
            font-size: 40px;
            font-style: normal;
            font-weight: bold;
            color: rgb(51, 51, 51) !important;
            text-align: center !important;
            padding-bottom: 10px;
        }

        .content-description {
            box-sizing: border-box;
            margin: 0;
            font-family: arial, helvetica, sans-serif;
            line-height: 24px;
            color: rgb(51, 51, 51) !important;
            font-size: 18px;
            text-align: center !important;
        }

        .content-link {
            text-decoration: none !important;
            font-family: arial, helvetica, sans-serif;
            font-size: 18px !important;
            font-style: normal;
            color: rgb(0, 110, 195) !important;
        }

        .content-regards {
            text-align: start;
            font-family: arial, helvetica, sans-serif;
            font-size: 16px !important;
            color: rgb(51, 51, 51) !important;
            margin: 16px 0 !important;
        }

        .button {
            box-sizing: border-box;
            text-decoration: none;
            color: rgb(255, 255, 255) !important;
            background: #004B91 !important;
            border-radius: 8px;
            font-size: 18px;
            padding: 14px 40px;
            margin: 10px 0;
            display: inline-block;
            font-family: arial, helvetica, sans-serif;
            font-weight: normal;
            font-style: normal;
            line-height: 24px;
            width: auto;
            text-align: center;
        }

        @media screen and (max-width: 600px) {
            .table-main {
                width: 100% !important;
                max-width: 100% !important;
            }

            .table-footer {
                width: 100% !important;
                max-width: 100% !important;
            }

            .table-content {
                width: 100% !important;
                max-width: 100% !important;
            }

            .content-title {
                font-size: 36px !important;
            }
        }

    </style>
</head>
<body style="padding: 0; margin: 0;">
<!--Header-->
@include('notifications::components.header')
<!--End Header-->
<!--Content-->
@include('notifications::components.content')
<!--End Content-->
<!--Footer-->
@include('notifications::components.footer')
<!--End Footer-->
</body>
</html>
