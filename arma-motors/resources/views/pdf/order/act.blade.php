<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>ACT</title>
</head>
<body>
<style>

    @page  {
        margin: 1%;
        padding: 0;
    }

    body {
        width: 100%;
        color: #000000;
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        font-family: DejaVu Sans, sans-serif;
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    .work-akt__name {
        text-align: center;
        font-size: 18px;
        margin-top: 3px;
        border-bottom: 2px solid;
        padding-bottom: 13px;
    }

    .work-akt__img {
        background-color: #ccc;
        height: 75px;
        width: 234px;
    }

    .work-akt__img img {
        width: 100%;
        height: 100%;
        object-fit: contain;
    }

    .work-akt__title {
        font-size: 18px;
        font-weight: bold;
        margin-top: 17px;
        text-align: center;
    }

    .work-akt__title--small {
        font-size: 11px;
        margin-bottom: 3px;
        margin-top: 9px;
    }

    .table-border td {
        border-right: 1px solid;
        border-bottom: 1px solid;
    }

    .work-akt__text {
        font-size: 10px;
        margin: 20px 0;
        line-height: 1.2;
        font-weight: normal;
    }

    .work-akt__text ol {
        margin: 0;
        padding: 10px 0 0 11px;
    }

    .work-akt__text ol li {
        margin: 0 0 4px;
    }

    .work-akt__text strong {
        /*display: block;*/
        font-size: 11px;
    }

    .work-akt__frame {
        height: 20px;
        border-top: 1px solid;
        border-bottom: 1px solid;
    }

    .work-akt__descr-block {
        border-top: 1px solid;
        text-align: center;
        margin: 10px 0 5px;
    }

    .work-akt__ask {
        /*display: inline-block;*/
    }

    .work-akt__ask span {
        /*display: inline-block;*/
    }

    .work-akt__ask span:first-child {
        margin-right: 5px;
    }

    .work-akt__ask span:last-child {
        width: 80px;
    }


    .table {
        page-break-inside: auto;
    }

    .va-base {
        vertical-align: baseline;
    }

    .fs-11 {
        font-size: 11px;
    }

    .fs-12 {
        font-size: 12px;
    }

    .fs-14 {
        font-size: 14px;
    }

    .ta-c {
        text-align: center;
    }

    .lh11 {
        line-height: 1.1;
    }

</style>
<table>
    <tr>
        <td>
            <div class="work-akt__img">
                <img alt="arma_motors.png" width="234" height="60" src="data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBzdGFuZGFsb25lPSJubyI/Pgo8IURPQ1RZUEUgc3ZnIFBVQkxJQyAiLS8vVzNDLy9EVEQgU1ZHIDIwMDEwOTA0Ly9FTiIKICJodHRwOi8vd3d3LnczLm9yZy9UUi8yMDAxL1JFQy1TVkctMjAwMTA5MDQvRFREL3N2ZzEwLmR0ZCI+CjxzdmcgdmVyc2lvbj0iMS4wIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciCiB3aWR0aD0iMzc1LjAwMDAwMHB0IiBoZWlnaHQ9IjIxMy4wMDAwMDBwdCIgdmlld0JveD0iMCAwIDM3NS4wMDAwMDAgMjEzLjAwMDAwMCIKIHByZXNlcnZlQXNwZWN0UmF0aW89InhNaWRZTWlkIG1lZXQiPgoKPGcgdHJhbnNmb3JtPSJ0cmFuc2xhdGUoMC4wMDAwMDAsMjEzLjAwMDAwMCkgc2NhbGUoMC4xMDAwMDAsLTAuMTAwMDAwKSIKZmlsbD0iIzAwMDAwMCIgc3Ryb2tlPSJub25lIj4KPHBhdGggZD0iTTIyMzUgMTczNiBjLTM0MCAtODkgLTY3OSAtMzM2IC04NDYgLTYxNiAtNjMgLTEwNyAtNjggLTEwNSAyMjUgLTk3CjEzMiA0IDI0MiAxMCAyNDUgMTMgMyAzIC03IDI4IC0yMyA1NiAtNTIgOTIgLTM0IDE2MSA1NCAyMDYgNzggMzkgMTY1IDI3IDI0MAotMzQgbDM1IC0yOCAtMjUgMjggYy02NiA3MyAtMTk3IDkzIC0yODMgNDMgLTU4IC0zNCAtODEgLTgxIC03NCAtMTUxIDMgLTI4IDgKLTYxIDEyIC03MyBsNyAtMjMgLTE4NSAwIGMtMTAyIDAgLTE4OCA0IC0xOTEgOCAtMTEgMTggOTEgMTUxIDE4OCAyNDcgMTI1CjEyMyAyNDMgMjA4IDM5MCAyNzkgMTE0IDU2IDI5MyAxMjMgMzAyIDExNCAzIC0zIDAgLTUwIC01IC0xMDQgLTEwIC0xMDAgLTMKLTIyMSAxOCAtMzE1IDE2IC02OSA1OSAtMTgwIDgxIC0yMDkgMTYgLTIxIDE2IC0xNiAtNSAzNiAtNjMgMTU2IC04MSAzNDIgLTUxCjUyMyAxMCA1NSAxNCAxMDUgMTEgMTExIC04IDE0IC0xNiAxMyAtMTIwIC0xNHoiLz4KPHBhdGggZD0iTTIxODAgMTYzNCBjLTMwIC04IC05MSAtMzIgLTEzNSAtNTMgLTE1MyAtNzUgLTIxMSAtMTUyIC0xMjAgLTE1OQo1MCAtNSAyOTUgMTcgMzE0IDI3IDE1IDkgLTkzIDIxIC0xOTEgMjEgLTM4IDAgLTY4IDQgLTY4IDggMCAxNSA4NCA2OSAxODYKMTIxIDEwMyA1MiAxMDYgNTkgMTQgMzV6Ii8+CjxwYXRoIGQ9Ik01MzcgOTUzIGMtNCAtMyAtNyAtMTcgLTcgLTMwIGwwIC0yMyAxMzI4IDIgMTMyNyAzIDAgMjUgMCAyNSAtMTMyMQozIGMtNzI2IDEgLTEzMjQgLTEgLTEzMjcgLTV6Ii8+CjxwYXRoIGQ9Ik0yMDkzIDc5MCBjLTQxIC0yNCAtNjMgLTY3IC02MyAtMTIxIDAgLTM1IDYgLTQ4IDM5IC04MCAzNiAtMzYgNDMKLTM5IDk1IC0zOSA1MCAwIDYwIDQgOTIgMzMgOTAgODIgMzEgMjI3IC05MSAyMjcgLTIyIDAgLTU0IC05IC03MiAtMjB6IG0xMTEKLTUxIGM1MiAtNDEgMjUgLTEzOSAtMzkgLTEzOSAtNjQgMCAtOTEgOTggLTM5IDEzOSAxNSAxMiAzMiAyMSAzOSAyMSA3IDAgMjQKLTkgMzkgLTIxeiIvPgo8cGF0aCBkPSJNMjYwNCA4MDEgYy0zMiAtMTQgLTcyIC02OCAtODAgLTEwNSAtNCAtMjMgMCAtNDYgMTIgLTcxIDUwIC0xMDcKMjE1IC05OSAyNTMgMTIgMTMgMzcgMTMgNDUgLTMgODcgLTEwIDI2IC0yOSA1NCAtNDMgNjMgLTI4IDE5IC0xMDkgMjcgLTEzOQoxNHogbTEwOSAtNzMgYzIzIC0yNyAyMiAtNjkgLTQgLTEwMiAtNTAgLTY0IC0xNDEgNiAtMTA5IDg0IDIxIDUxIDc3IDU5IDExMwoxOHoiLz4KPHBhdGggZD0iTTMwOTYgNzg5IGMtMTggLTE0IC0yNiAtMzAgLTI2IC01MiAwIC00NCA4IC01MiA2MyAtNzYgMzUgLTE1IDQ4Ci0yNiA0NSAtMzggLTQgLTIzIC00MyAtMjggLTcyIC0xMCAtMjAgMTQgLTIzIDEzIC0zNCAtNCAtMTcgLTI3IC0xNSAtMzQgMTMKLTQ3IDc1IC0zNCAxNTUgMyAxNTUgNzEgMCAzOSAtMTcgNTggLTczIDgxIC01MCAyMCAtNDQgNDIgMTEgMzkgMzIgLTEgNDEgMgo0NSAxOCAxMSA0MSAtODEgNTQgLTEyNyAxOHoiLz4KPHBhdGggZD0iTTUyMSA2ODUgYy0yMiAtNjQgLTQxIC0xMjEgLTQxIC0xMjYgMCAtNSAxMyAtOSAyOCAtOSAyMSAwIDMyIDcgNDAKMjUgMTAgMjEgMTggMjUgNTcgMjUgMzkgMCA0NyAtMyA1NSAtMjUgNyAtMTkgMTcgLTI1IDQwIC0yNSAzOSAwIDM4IDkgLTkgMTQwCmwtMzggMTA1IC00NSAzIC00NiAzIC00MSAtMTE2eiBtMTEzIC0xNyBjNiAtMjYgNCAtMjggLTIzIC0yOCAtNDAgMCAtNDMgOQotMjQgNjYgbDE3IDQ5IDExIC0zMCBjNyAtMTcgMTUgLTQyIDE5IC01N3oiLz4KPHBhdGggZD0iTTc3MCA2NzUgbDAgLTEyNSAyOSAwIGMyOCAwIDMwIDIgMzMgNDggbDMgNDcgMjUgLTQ1IGMyMiAtMzkgMzAgLTQ1CjYzIC00OCAyMCAtMiAzNyAwIDM3IDMgMCA0IC0xMyAyNiAtMzAgNTAgLTM0IDQ5IC0zNiA1NSAtMTYgNTUgMzkgMCA1NCA4NCAyMQoxMjAgLTE1IDE3IC0zMCAyMCAtOTIgMjAgbC03MyAwIDAgLTEyNXogbTEwMCA3NSBjMTIgMCAyMCAtMjggMTQgLTQ1IC00IC04Ci0xNyAtMTUgLTMwIC0xNSAtMjEgMCAtMjQgNSAtMjQgMzYgMCAyOSAzIDM1IDE2IDMwIDkgLTMgMTkgLTYgMjQgLTZ6Ii8+CjxwYXRoIGQ9Ik0xMDE2IDc3MyBjLTMgLTE2IC0xMCAtNTkgLTE2IC05OCAtNiAtMzggLTEzIC04MiAtMTYgLTk3IC01IC0yNiAtMwotMjggMjQgLTI4IDMyIDAgNDEgMTYgNDMgNzUgMCAxNyA0IDM5IDkgNTAgOSAyMyAxMCAyMiAzNSAtNjcgMTMgLTQ5IDE5IC01OAozOSAtNTggMjAgMCAyNiAxMCA0NyA3OCBsMjMgNzcgMTIgLTc1IGM3IC00MSAxMyAtNzYgMTMgLTc3IDIgLTUgNDggLTMgNTMgMgozIDMgLTEgNDUgLTggOTMgLTI2IDE2NSAtMjEgMTUyIC01OSAxNTIgLTM0IDAgLTM1IC0xIC01NiAtNzEgLTEyIC0zOSAtMjUKLTY3IC0yOSAtNjIgLTMgNCAtMTUgMzYgLTI1IDcxIC0xNyA2MCAtMTkgNjIgLTUxIDYyIC0yNyAwIC0zMyAtNCAtMzggLTI3eiIvPgo8cGF0aCBkPSJNMTM0MyA2ODEgYy0yMyAtNjYgLTM5IC0xMjIgLTM1IC0xMjUgMyAtMyAxOCAtNiAzMyAtNiAyMiAwIDMwIDYgMzUKMjUgNiAyMiAxMSAyNSA1NSAyNSA0NCAwIDUxIC0zIDU5IC0yNSA3IC0xOSAxNyAtMjUgNDEgLTI1IDIxIDAgMzAgNCAyNyAxMwotMyA2IC0yMiA2MyAtNDIgMTI1IGwtMzggMTEyIC00NiAwIC00NyAwIC00MiAtMTE5eiBtMTIwIC0zMyBjLTYgLTExIC02MyAtMTAKLTYzIDEgMCA3IDEzIDUxIDI3IDkxIDMgOSAxMiAtNSAyMyAtMzUgOSAtMjcgMTUgLTUzIDEzIC01N3oiLz4KPHBhdGggZD0iTTE3MjYgNzg4IGMtNSAtMTYgLTM2IC0yMTMgLTM2IC0yMjggMCAtNSAxMyAtMTAgMjkgLTEwIDI4IDAgMzAgMgozNiA1OCAxMSA4OCAyMSA5NSA0MCAyNyA5IC0zMyAxOCAtNjYgMjAgLTcyIDMgLTcgMTUgLTEzIDI4IC0xMyAyMCAwIDI2IDEwCjQ3IDc4IGwyMyA3NyAxMSAtNzUgYzEwIC03MSAxMiAtNzUgMzkgLTc4IDI0IC0zIDI3IDAgMjcgMjUgMCAyNSAtMTAgOTcgLTI2CjE5MSAtNSAzMSAtNyAzMyAtNDEgMzAgLTM2IC0zIC0zNyAtNCAtNTggLTczIC0yMiAtNjkgLTIyIC02OSAtMzQgLTQyIC02IDE2Ci0xNiA0OCAtMjIgNzMgLTExIDQxIC0xMyA0NCAtNDYgNDQgLTE4IDAgLTM1IC02IC0zNyAtMTJ6Ii8+CjxwYXRoIGQ9Ik0yMzIwIDc3NSBjMCAtMjEgNSAtMjUgMzAgLTI1IGwzMCAwIDAgLTEwMSAwIC0xMDAgMzMgMyAzMiAzIDMgOTgKYzMgOTYgMyA5NyAyNyA5NyAyMCAwIDI1IDUgMjUgMjUgMCAyNSAtMSAyNSAtOTAgMjUgLTg5IDAgLTkwIDAgLTkwIC0yNXoiLz4KPHBhdGggZD0iTTI4NDIgNjc4IGwzIC0xMjMgMzAgMCBjMjggMCAzMCAzIDM1IDQ1IGw1IDQ1IDI4IC00NyBjMjUgLTQzIDMyCi00OCA2MiAtNDggMTkgMCAzNSAzIDM1IDYgMCAzIC0xNSAyNiAtMzIgNTEgLTIzIDMyIC0yOSA0NyAtMjAgNTAgMjcgMTAgNDIKMzYgNDIgNzIgMCA1OCAtMjEgNzEgLTExNCA3MSBsLTc3IDAgMyAtMTIyeiBtMTEzIDY2IGMyMyAtMjMgMTUgLTQ4IC0xOCAtNTIKLTI1IC0zIC0yNyAtMSAtMjcgMzIgMCA0MCAxNyA0NyA0NSAyMHoiLz4KPHBhdGggZD0iTTg5MCA0MzAgYzAgLTIwIDcgLTIwIDk2NSAtMjAgOTU4IDAgOTY1IDAgOTY1IDIwIDAgMjAgLTcgMjAgLTk2NQoyMCAtOTU4IDAgLTk2NSAwIC05NjUgLTIweiIvPgo8L2c+Cjwvc3ZnPg==">
{{--                <img src="./img/pdf/arma_motors.png" alt="arma_motors.png" width="234" height="60">--}}
            </div>
        </td>
        <td>
            <div class="work-akt__img" style="margin-left: auto;">
                <img src="/" alt="" width="234" height="60">
            </div>
        </td>
    </tr>
</table>
<div class="work-akt__name">
    {{$organization['name']}}
</div>
<div class="work-akt__title">
    <strong>АКТ ВИКОНАНИХ РОБІТ № {{$number}}</strong>
</div>
<!-- АКТО РОБОТ -->
<table class="fs-12"
       style="margin: 0 0 9px;"
>
    <tr>
        <td
            style="width: 90px"
        ></td>
        <td class="fs-14"
            style="width: 62.5%;"
        ></td>
        <td>{{$date}}</td>
    </tr>
</table>
<table class="fs-12"
       style="border-bottom: 2px solid; text-align: left;font-weight: normal"
>
    <tr class="va-base">
        <td style="width: 90px">
            <strong>Виконавець</strong>
        </td>
        <td style="width: 62.5%;">
            <strong class="fs-14">{{$organization['name']}}.</strong>
            <div class="fs-12">{{$organization['address']}}</div>
            <div class="fs-12">{{$organization['phone']}}</div>
        </td>
        <td>
            <strong>{{$repairType}}</strong>
        </td>
    </tr>
</table>
<table class="fs-11 lh11" style="text-align: left;border-bottom: 4px solid;padding: 4px 0 20px;">
    <tbody class="va-base">
    <tr>
        <td style="width: 35%">
            <strong>Власник:</strong>
        </td>
        <td style="width: 65%;">
            <strong>{{$owner['name']}}</strong>
        </td>
        <td style="width: 35%">
            Марка, модель ДТЗ
        </td>
        <td style="width: 65%">
            <strong>{{$model}}</strong>
        </td>
    </tr>
    <tr>
        <td style="width: 35%">
            Адреса
        </td>
        <td style="width: 65%;">
            {{$owner['address']}}
        </td>
        <td style="width: 35%"></td>
        <td style="width: 65%"></td>
    </tr>
    <tr>
        <td style="width: 35%">
            Телефон
        </td>
        <td style="width: 65%;">
            {{$owner['phone']}}
        </td>
        <td style="width: 35%">
            № кузова
        </td>
        <td style="width: 65%">
            <strong>{{$bodyNumber}}</strong>
        </td>
    </tr>
    <tr>
        <td style="width: 35%">
            Електр. пошта
        </td>
        <td style="width: 65%;">
            {{$owner['email']}}
        </td>
        <td style="width: 35%">
            Дата продажу
        </td>
        <td style="width: 65%">
            {{$dateOfSale}}
        </td>
    </tr>
    <tr>
        <td style="width: 35%">
            ІНН/ЄДРПОУ
        </td>
        <td style="width: 65%;">
            {{$owner['etc']}}
        </td>
        <td style="width: 35%"></td>
        <td style="width: 65%"></td>
    </tr>
    <tr>
        <td style="width: 35%">
            Свід.ПДВ
        </td>
        <td style="width: 65%;">
            {{$owner['certificate']}}
        </td>
        <td style="width: 35%">
            Держ. номеру
        </td>
        <td style="width: 65%">
            <strong>{{$stateNumber}}</strong>
        </td>
    </tr>
    <tr>
        <td style="width: 35%">
            <strong>Замовник:</strong>
        </td>
        <td style="width: 65%">
            <strong>{{$customer['name']}}</strong>
        </td>
        <td style="width: 35%"></td>
        <td style="width: 65%"></td>
    </tr>
    <tr>
        <td style="width: 35%">
            Телефон
        </td>
        <td style="width: 65%">
            {{$customer['phone']}}
        </td>
        <td style="width: 35%">
            Пробіг, км
        </td>
        <td style="width: 65%">
            <strong>{{$mileage}}</strong>
        </td>
    </tr>
    <tr>
        <td style="width: 35%">
            Доруч.№ / від
        </td>
        <td style="width: 65%">
            від
        </td>
        <td style="width: 35%"></td>
        <td style="width: 65%"></td>
    </tr>
    <tr>
        <td style="width: 35%">
            <strong>Платник:</strong>
        </td>
        <td style="width: 65%">
            <strong>{{$payer['name']}}</strong>
            <div>{{$payer['contract']}} № {{$payer['number']}} от {{$payer['date']}} р.</div>
        </td>
        <td style="width: 35%"></td>
        <td style="width: 65%"></td>
    </tr>
    </tbody>
</table>
<!-- ЗАПЧАСТИ -->
<table class="work-akt__title work-akt__title--small">
    <tr>
        <td>
            Запчастини
        </td>
    </tr>
</table>
<table class="table-border fs-11 ta-c lh11" style="border-left: 1px solid;">
    <thead class="ta-c" style="border-top: 1px solid">
    <tr>
        <td style="width: 19px">№ п/п</td>
        <td style="width: 61px">Виробник</td>
        <td style="width: 56px">Кат.№</td>
        <td style="width: 287px">Назва</td>
        <td style="width: 32px">Кіль- кість</td>
        <td style="width: 42px">Од. виміру</td>
        <td style="width: 51px">Ціна за од. без ПДВ</td>
        <td style="width: 15px">Знижка (+), Округлення (-), %</td>
        <td style="width: 63px">Відпускна ціна без ПДВ</td>
        <td style="width: 68px">Сума без ПДВ</td>
    </tr>
    </thead>
    <tbody>
    @foreach($parts ?? [] as $key => $part)
        <tr>
            <td>{{$key + 1}}</td>
            <td>{{$part['producer']}}</td>
            <td>{{$part['ref']}}</td>
            <td style="text-align: left;">{{$part['name']}}</td>
            <td>{{$part['quantity']}}</td>
            <td>{{$part['unit']}}</td>
            <td>{{$part['priceWithoutVAT']}}</td>
            <td>{{$part['rate']}}</td>
            <td>{{$part['price']}}</td>
            <td style="text-align: right">{{$part['amountWithoutVAT']}}</td>
        </tr>
    @endforeach
    </tbody>
</table>
<table class="fs-11 lh11">
    <tbody>
    <tr>
        <td style="width: 19px"></td>
        <td style="width: 61px"></td>
        <td style="width: 298px;border-bottom: 1px solid;">
            <strong> Всього без ПДВ, грн.</strong>
        </td>
        <td style="width: 36px;text-align: right;border: 1px solid;border-top: none;">
            <strong> {{$partsAmountWithoutVAT}}</strong>
        </td>
    </tr>
    </tbody>
</table>

<!-- РАБОТЫ -->
<table class="work-akt__title work-akt__title--small">
    <tr>
        <td>
            Роботы
        </td>
    </tr>
</table>
<table class="table-border fs-11 ta-c lh11" style="border-left: 1px solid;">
    <thead class="ta-c" style="border-top: 1px solid">
    <tr>
        <td style="width: 19px">№ п/п</td>
        <td style="width: 105px">Артикул</td>
        <td style="width: 287px">Назва</td>
        <td style="width: 32px">Коефіцієнт</td>
        <td style="width: 42px">Ціна з ПДВ</td>
        <td style="width: 51px">Ціна без ПДВ</td>
        <td style="width: 15px">Відсоток знижки , %</td>
        <td style="width: 63px">Відпускна ціна</td>
        <td style="width: 68px">Сума без ПДВ</td>
        <td style="width: 68px">Сума з ПДВ</td>
    </tr>
    </thead>
    <tbody>
    @foreach($jobs ?? [] as $key => $job)
        <tr>
            <td>{{$key + 1}}</td>
            <td>{{$job['ref']}}</td>
            <td>{{$job['name']}}</td>
            <td>{{$job['coefficient']}}</td>
            <td>{{$job['priceWithVAT']}}</td>
            <td>{{$job['priceWithoutVAT']}}</td>
            <td>{{$job['rate']}}</td>
            <td>{{$job['price']}}</td>
            <td>{{$job['amountWithoutVAT']}}</td>
            <td style="text-align: right">{{$job['amountIncludingVAT']}}</td>
        </tr>
    @endforeach
    </tbody>
</table>
<table class="fs-11 lh11">
    <tbody>
    <tr>
        <td style="width: 19px"></td>
        <td style="width: 61px"></td>
        <td style="width: 298px;border-bottom: 1px solid;">
            <strong> Всього без ПДВ, грн.</strong>
        </td>
        <td style="width: 36px;text-align: right;border: 1px solid;border-top: none;">
            <strong>  {{$jobsAmountWithoutVAT}}</strong>
        </td>
    </tr>
    </tbody>
</table>

<!-- РАЗОМ -->
<table class="work-akt__title work-akt__title--small">
    <tr>
        <td>
            Разом
        </td>
    </tr>
</table>
<table class="fs-11 lh11" style="border: 2px solid;">
    <tbody>
    <tr>
        <td style="width: 19px"></td>
        <td style="width: 61px"></td>
        <td style="width: 298px;border-bottom: 1px solid;">
            <strong> Всього без ПДВ, грн.</strong>
        </td>
        <td style="width: 36px;text-align: right;border-left: 1px solid;border-right: 1px solid;">
            <strong>  {{$AmountWithoutVAT}}</strong>
        </td>
    </tr>
    </tbody>
</table>
<table class="fs-11 lh11">
    <tbody>
    <tr>
        <td style="width: 76px;padding-right: 15px;box-sizing: border-box">
            <strong>Всього до сплати прописом</strong>
        </td>
        <td style="width: 298px;">
            {{$AmountInWords}}
        </td>
    </tr>
    </tbody>
</table>

<!-- СКИДКИ -->
<table class="work-akt__title work-akt__title--small">
    <tr>
        <td>
            Надані знижки (+)/округлення (-)
        </td>
    </tr>
</table>
<table class="fs-11 lh11" style="border: 1px solid;">
    <tbody>
    <tr style="border: 1px solid;">
        <td style="width: 19px;border-bottom: 1px solid;"></td>
        <td style="width: 61px;border-bottom: 1px solid;"></td>
        <td style="width: 298px;border-bottom: 1px solid;">
            Запчастини
        </td>
        <td style="width: 36px;text-align: right;border-left: 1px solid;border-right: 1px solid;border-bottom: 1px solid;">
            {{$discountParts}}
        </td>
    </tr>
    <tr  style="border: 1px solid;">
        <td style="width: 19px;border-bottom: 1px solid;"></td>
        <td style="width: 61px;border-bottom: 1px solid;"></td>
        <td style="width: 298px;border-bottom: 1px solid;">
            Роботи
        </td>
        <td style="width: 36px;text-align: right;border-left: 1px solid;border-right: 1px solid;border-bottom: 1px solid;">
            {{$discountJobs}}
        </td>
    </tr>
    <tr  style="border: 1px solid;">
        <td style="width: 19px;border-bottom: 1px solid;"></td>
        <td style="width: 61px;border-bottom: 1px solid;"></td>
        <td style="width: 298px;border-bottom: 1px solid;">
            <strong> Всього без ПДВ, грн.</strong>
        </td>
        <td style="width: 36px;text-align: right;border-left: 1px solid;border-right: 1px solid;border-bottom: 1px solid;">
            <strong> {{$discount}}</strong>
        </td>
    </tr>
    </tbody>
</table>

<!-- ТЕКСТ 1 -->

<table class="work-akt__text">
    <tr>
        <td>
            Умови гарантії (згідно умов гарантії заводу-виробника та ДСТУ 2322-93): 1(один) рік на встановлені оригінальні деталі, вузли і агрегати , на які розповсюджується гарантія заводу; 20
            діб але не більше 1500 км пробігу - на ремонт ДТЗ; 10 діб але не більше 500 км пробігу - на регулювальні роботи.
            <strong> Гарантія не розповсюджується на деталі, вузли і агрегати, надані для здійснення ремонту Замовником.</strong>
            УВАГА! Гарантія на виконані роботи та встановлені оригінальні деталі, вузли і агрегати надається Виконавцем за наступних умов: своєчасне проходження технічного обслуговування
            автомобілів тільки на авторизованій сервісній станції , виконання вимог (правил) експлуатації та зберігання автомобіля заводу-виробника, викладених у сервісній книжці та посібнику
            з експлуатації.
            <strong> Рекомендації:</strong>
        </td>
    </tr>
</table>

<table class="work-akt__frame" style="/*display: block*/"></table>

<!-- ТЕКСТ 2 -->
<table class="work-akt__text">
    <tr>
        <td>

            <strong>  Даний акт є Актом передання-прийняття ДТЗ, його складових після надання послуг з технічного обслуговування і ремонту.</strong>
            <ol>
                <li>Цей акт складено згідно {{$payer['contract']}} № {{$payer['number']}} від {{$payer['date']}} р. представником {{$organization['name']}}. в особі Консультант з сервісу Рено , що діє на
                    підставі доручення від № , з одного боку (надалі Виконавець), і власника ДТЗ чи його представника
                    {{$customer['name']}} (надалі Замовник), що діє за довіреністю від р. № , з
                    другого боку, про технічний стан ДТЗ шасі (кузов) № {{$bodyNumber}}, держ. реєстраційний № {{$stateNumber}} , який передається- приймається після надання послуг з технічного
                    обслуговування і ремонту.</li>
                <li>Наявність пломб лічильника ТАК / НІ (непотрібне закреслити)</li>
                <li>Надані послуги відповідають умовам договору / замовлення на обслуговування</li>
                <li>Претензій щодо якості, терміну та обсягу робіт Замовник не має</li>
                <li>Після надання послуг замовнику повернено:
                    <div class="work-akt__descr-block">
                        (найменування, номери складових частин, їхні ідентифікаційні дані, кількість, технічний стан)
                    </div>
                </li>
                <li>Експлуатаційна документація передана:
                    <div class="work-akt__ask">
                        <span>сервісна книжка</span>
                        <span>ТАК   \   NI</span>
                    </div>
                    <div class="work-akt__ask">
                        <span>техпаспорт</span>
                        <span>ТАК   \   NI</span>
                    </div>
                    (непотрібне закреслити)
                </li>
                <li>Майно повернено Замовнику.</li>
                <li>Претензій до комплектності та стану ДТЗ Замовник не має.</li>
                <li>Майно повернено Замовнику.</li>
                <li>Претензій до комплектності та стану ДТЗ Замовник не має.</li>
                <li>Майно повернено Замовнику.</li>
                <li>Претензій до комплектності та стану ДТЗ Замовник не має.</li>
                <li>Майно повернено Замовнику.</li>
                <li>Претензій до комплектності та стану ДТЗ Замовник не має.</li>
                <li>Майно повернено Замовнику.</li>
                <li>Претензій до комплектності та стану ДТЗ Замовник не має.</li>
            </ol>

        </td>
    </tr>
</table>

<table class="lh11" style="font-size: 13px;">
    <tbody>
    <tr>
        <td class="fs-11">
            ДТЗ (його складові), замінені (запасні) частини, невикористані матеріали передав. <br>
            <strong style="/*display: block*/">Виконавець, Консультант з сервісу Рено</strong>
        </td>
        <td style="width: 318px;">
            <table class="fs-12">
                <tr>
                    <td style="text-align: right"><div>/</div></td>
                    <td style="text-align: right"><div>/</div></div></td>
                    <td class="ta-c"><div>{{$closingDate}}</div></td>
                </tr>
                <tr>
                    <td style="height: 5px"></td>
                </tr>
                <tr class="ta-c">
                    <td style="border-top: 1px solid;"><div>Підпис</div></td>
                    <td style="border-top: 1px solid;"><div>Прізвище, ініціали</div></td>
                    <td style="border-top: 1px solid;"><div>Дата</div></td>
                </tr>
            </table>
        </td>
    </tr>
    </tbody>
</table>
<table class="lh11" style="font-size: 13px;margin-top: 30px">
    <tbody>
    <tr>
        <td class="fs-11">
            ДТЗ (його складові), замінені (запасні) частини, невикористані матеріали прийняв. З
            умовами гарантії ознайомлений. <br>
            <strong style="/*display: block*/">Замовник</strong>
        </td>
        <td style="width: 318px;">
            <table class="fs-12">
                <tr class="ta-c">
                    <td style="text-align: right"><div>/</div></td>
                    <td><div>{{$customer['FIO']}}</div></td>
                    <td><div>{{$closingDate}}</div></td>
                </tr>
                <tr>
                    <td style="height: 5px"></td>
                </tr>
                <tr class="ta-c">
                    <td style="border-top: 1px solid;"><div>Підпис</div></td>
                    <td style="border-top: 1px solid;"><div>Прізвище, ініціали</div></td>
                    <td style="border-top: 1px solid;"><div>Дата</div></td>
                </tr>
            </table>
        </td>
    </tr>
    </tbody>
    </tablec>
    </td>
    </tr>
    </tbody>
</table>

</body>
</html>
