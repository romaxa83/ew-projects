@extends('layouts.customer')

@push('extendHeader')
    <link id="vendorsbundle" rel="stylesheet" media="screen, print"
          href="{{ asset('/smartadmin/css/vendors.bundle.css') }}">
    <link rel="stylesheet" media="screen, print" href="{{ asset('/smartadmin/css/fa-light.css') }}">
    <link id="appbundle" rel="stylesheet" media="screen, print" href="{{ asset('/smartadmin/css/app.bundle.css') }}">
    <link id="myskin" rel="stylesheet" media="screen, print"
          href="{{ asset('/smartadmin/css/skins/skin-master.css') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('/smartadmin/img/favicon/apple-touch-icon.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('/smartadmin/img/favicon/favicon-32x32.png') }}">
    <link rel="mask-icon" href="{{ asset('/smartadmin/img/favicon/safari-pinned-tab.svg') }}" color="#5bbad5">
    <link rel="stylesheet" media="screen, print" href="{{ asset('/smartadmin/css/theme-demo.css') }}">
    <link id="mytheme" rel="stylesheet" href="/smartadmin/css/themes/cust-theme-3.css">

    <link rel="stylesheet" href="{{ asset('/css/customer-order.css') }}">

    <link rel="stylesheet" media="screen, print" href="{{ asset('/smartadmin/css/fa-solid.css') }}">
    <style>
        .counter-table {
            counter-reset: row-num;
        }

        .counter-table tbody tr {
            counter-increment: row-num;
        }

        .counter-table tr td:first-child::before {
            content: counter(row-num);
        }

        .counter-table tr td:first-child {
            text-align: center;
        }

        img.il-logo {
            width: 45px;
            height: 45px;
        }

        .page-logo-text.il-logo {
            font-weight: 400;
            font-size: 1.3rem;
            color: #366bc3;
        }

        .Table {
            width: 100%;
            color: #212529;
        }

        .MsoTableGrid tr:first-child td {
            border-top: 1px solid black !important;
        }
    </style>
    {{--    30 min refresh --}}
    <meta http-equiv="refresh" content="1800">
@endpush

@push('extendFooter')
    <script src="{{ asset('/smartadmin/js/vendors.bundle.js') }}"></script>
    <script src="{{ asset('/smartadmin/js/app.bundle.js') }}"></script>
    <script src="{{ mix('js/lib-bundle.js') }}"></script>
    <script src="{{ mix('js/customer.js') }}" defer></script>
@endpush


@section('content')

    <div id="customer-order" class="page-inner bg-brand-gradient">
        <div class="page-content-wrapper bg-transparent m-0">
            <div class="height-10 w-100 shadow-lg px-4 bg-brand-gradient">
                <div class="d-flex align-items-center container p-0">
                    <div
                        class="page-logo width-mobile-auto m-0 align-items-center justify-content-center p-0 bg-transparent bg-img-none shadow-0 height-9 border-0">
                        <a href="javascript:void(0)" class="page-logo-link press-scale-down d-flex align-items-center">
                            @if($record->division_id == 1)
                                <img class="il-logo" src="/images/logo/h2hmovers-logo.png" alt="H2Hmovers logo" aria-roledescription="logo">
                                <span class="page-logo-text mr-1 il-logo">H2Hmovers</span>
                            @else
                                <img src="/smartadmin/img/logo.png" alt="Allymovers" aria-roledescription="logo">
                                <span class="page-logo-text mr-1">Allymovers</span>
                            @endif
                        </a>
                    </div>
                    @if(!empty($record->division->miscs['phone']))
                        <div class="text-white ml-auto mr-2 hidden-sm-down">
                            <div class="opacity-50 text-right">Call us</div>
                            <div class="fw-600 fs-xl color-primary-700">
                                <a href="tel:{{ preg_replace('/[^+0-9]/', '', $record->division->miscs['phone']) }}">
                                    {{ $record->division->miscs['phone'] }}
                                </a>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
            <div class="flex-1" style="background: url('/smartadmin/img/svg/pattern-1.svg') no-repeat center bottom fixed; background-size: cover;">
                <div class="container px-4 px-sm-0">
                    <div class="row">
                        <div class="col-md-6 mt-3">
                            <h1>Order #{{ $record->id }}</h1>
                        </div>
                        <div class="col-md-6 my-3 text-right">
                            @if($record->is_estimate_available)
                            <h3>
                                <a href="#" id="toDocument">
                                    <u>Please read "UNDERSTANDING YOUR ESTIMATE"
                                        below</u>
                                </a>
                            </h3>
                            @endif
                        </div>
                    </div>
                    <div class="row">

                        <div class="col-lg-4 mb-3 order-lg-last">
                            @if($record->division_id == 1)
                                @include('layouts.order.customer.orderPublicView.cards.order-main-il')
                            @else
                                @include('layouts.order.customer.orderPublicView.cards.order-main')
                            @endif
                            @if($record->division_id == 1 && $record->is_estimate_available)
                                @include('layouts.order.customer.orderPublicView.cards.estimate-summary-il')
                            @endif
                            @if($record->division_id == 2 && $record->is_estimate_available)
                                @include('layouts.order.customer.orderPublicView.cards.estimate-summary-ca')
                            @endif
                        </div>
                        <div class="col-lg-8 mb-3">
                            <div class="row">
                                <div class="col-lg-6 mb-3">
                                    @includeWhen($record->manager, 'layouts.order.customer.orderPublicView.cards.sales')
                                </div>
                                <div class="col-lg-6 mb-3">
                                    @includeWhen($record->client, 'layouts.order.customer.orderPublicView.cards.client')
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-12 mb-3">
                                    @includeWhen($record->waypoints, 'layouts.order.customer.orderPublicView.cards.waypoints')
                                </div>
                            </div>
                                @if($record->is_estimate_available)
                                <div class="row">
                                    <div class="col-lg-12 mb-3">
                                        @include('layouts.order.customer.orderPublicView.cards.extra')
                                    </div>
                                </div>
                               @endif
                        </div>
                    </div>
                    <div class="row">
                        <div class="col mb-3">
                            <div class="card m-auto border">
                                <div class="card-header py-2 bg-primary-600 d-flex">
                                    <div class="card-title">
                                        Inventory
                                    </div>
                                    <div class="ml-auto">
                                        <div class="alert alert-secondary pt-0 pb-0 mb-0 px-3" role="alert">
                                            Volume / Weight: <b>{{$record->sizing_volume}} cbFt / {{$record->sizing_weight}} lbs</b>
                                        </div>
                                    </div>
                                </div>
                            <customer-inventory :division-id="{{ $record->division_id }}"></customer-inventory>
                            </div>
                        </div>
                    </div>
                    @if($record->is_estimate_available)
                    @includeWhen(!empty($record->afterwordText), 'layouts.order.customer.orderPublicView.cards.understanding')
                    @endif
                </div>
                <div class="pos-left pos-right p-3 text-center text-white">
                    {{ date('Y') }} © H2Hmovers <!--by&nbsp;<a href="http://allymovers.com/"
                                                            class="text-white opacity-40 fw-500"
                                                            target="_blank">allymovers.com</a> -->
                </div>
            </div>
        </div>
    </div>
@endsection
