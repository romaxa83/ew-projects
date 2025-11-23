@extends('layouts.app')

@push('extendHeader')
    <link href="{{ asset('/smartadmin/css/datagrid/datatables/datatables.bundle.css') }}" rel="stylesheet">
    <link href="{{ asset('css/datatables.css') }}" rel="stylesheet">

    {{--    <link rel="stylesheet" media="screen, print"--}}
    {{--          href="{{ asset('/smartadmin/css/formplugins/bootstrap-daterangepicker/bootstrap-daterangepicker.css') }}">--}}
    <link rel="stylesheet" media="screen, print"
          href="{{ asset('/smartadmin/css/formplugins/select2/select2.bundle.css') }}">
@endpush

@push('extendFooter')
    <script src="{{ asset('/smartadmin/js/datagrid/datatables/datatables.bundle.js') }}"></script>
    <script src="{{ mix('js/datatables-editor-bundle.js') }}"></script>

    {{--    <script src="{{ asset('/smartadmin/js/formplugins/select2/select2.bundle.js') }}"></script>--}}
    <script src="{{ mix('/js/settings.intrastate.js') }}"></script>
@endpush

@section('content')
    {{--@includeWhen(isset($breadcrumbs), 'layouts.app.breadcrumbs')--}}

    <div class="row">
        <div class="col-lg-12">
            <div class="panel">
                <div class="panel-hdr">
                    <h2>
                        Intrastate matrix
                    </h2>
                </div>

                <div class="panel-container show">
                    <div class="panel-content">
                        <table class="table table-bordered" id="dt-intrastate-coefficients-matrix"
                               data-route="{!! route('settings.rates.intrastate.coefficients.matrix') !!}"
                               data-route-editor="{!! route('settings.rates.intrastate.coefficients.matrix.editor') !!}">
                            <thead>
                            </thead>
                        </table>

                    </div>
                </div>
            </div>
        </div>

    </div>


    <div class="row">
        <div class="col-lg-6">
            <div class="panel">
                <div class="panel-hdr">
                    <h2>
                        Intrastate mile ranges
                    </h2>
                </div>
                <div class="panel-container show">
                    <div class="panel-content">
                        <table class="table table-bordered" id="dt-intrastate-miles"
                               data-route="{!! route('settings.rates.intrastate.miles.datatable') !!}"
                               data-route-editor="{!! route('settings.rates.intrastate.miles.datatable.editor') !!}">
                            <thead>
                            <tr>
                                <th data-width="10" data-data="id" data-visible="false">Range ID</th>
                                <th data-width="40" data-data="from">Miles From</th>
                                <th data-width="40" data-data="to">Miles To</th>
                                <th data-width="10" data-orderable="false" class="btns"></th>
                            </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>


{{--            <div class="panel">--}}
{{--                <div class="panel-hdr">--}}
{{--                    <h2>--}}
{{--                        Intrastate coefficients--}}
{{--                    </h2>--}}
{{--                </div>--}}

{{--                <div class="panel-container show">--}}
{{--                    <div class="panel-content">--}}
{{--                       --}}
{{--                        <table class="table table-bordered" id="dt-intrastate-coefficients"--}}
{{--                               data-route="{!! route('settings.rates.intrastate.coefficients.datatable') !!}"--}}
{{--                               data-route-editor="{!! route('settings.rates.intrastate.coefficients.datatable.editor') !!}">--}}
{{--                            <thead>--}}
{{--                            <tr>--}}
{{--                                <th data-width="5" data-data="id" data-visible="false">RowID</th>--}}
{{--                                <th data-width="30" data-data="rate_distance_id" class="rate_distance_id">Mile range</th>--}}
{{--                                <th data-width="30" data-data="rate_weight_id" class="rate_weight_id">Weight range</th>--}}
{{--                                <th data-width="15" data-data="coefficient">Coefficient</th>--}}
{{--                                <th data-width="20" data-orderable="false" class="btns"></th>--}}
{{--                            </tr>--}}
{{--                            </thead>--}}
{{--                        </table>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--            </div>--}}
        </div>
        <div class="col-lg-6">
            <div class="panel">
                <div class="panel-hdr">
                    <h2>
                        Intrastate factors range
                    </h2>
                    <div class="panel-toolbar pr-3 align-self-end">
                        <ul class="nav nav-tabs border-bottom-0" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link nav-factors active" data-toggle="tab" href="#tab_factor_weight" role="tab">Weight ranges</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link nav-factors disabled" data-toggle="tab" href="#tab_factor_volume" role="tab">Volume ranges</a>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="panel-container show">
                    <div class="panel-content">
                        <div class="tab-content">
                            <div class="tab-pane fade active show" id="tab_factor_weight">
                                <table class="table table-bordered" id="dt-intrastate-weights"
                                       data-route="{!! route('settings.rates.intrastate.weights.datatable') !!}"
                                       data-route-editor="{!! route('settings.rates.intrastate.weights.datatable.editor') !!}">
                                    <thead>
                                    <tr>
                                        <th data-width="10" data-data="id" data-visible="false">Range ID</th>
                                        <th data-width="40" data-data="from">Weight From, lb</th>
                                        <th data-width="40" data-data="to">Weight To, lb</th>
                                        <th data-width="10" data-orderable="false" class="btns"></th>
                                    </tr>
                                    </thead>
                                </table>
                            </div>
                            <div class="tab-pane fade" id="tab_factor_volume">
                                {{--                                <table class="table table-bordered" id="dt-intrastate-volume"--}}
                                {{--                                       data-route="{!! route('settings.rates.intrastate.volume.datatable') !!}"--}}
                                {{--                                       data-route-editor="{!! route('settings.rates.intrastate.volume.datatable.editor') !!}">--}}
                                {{--                                    <thead>--}}
                                {{--                                    <tr>--}}
                                {{--                                        <th data-width="10" data-data="id">Range ID</th>--}}
                                {{--                                        <th data-width="40" data-data="from">volume From</th>--}}
                                {{--                                        <th data-width="40" data-data="to">volume To</th>--}}
                                {{--                                        <th data-width="10" data-orderable="false" class="btns"></th>--}}
                                {{--                                    </tr>--}}
                                {{--                                    </thead>--}}
                                {{--                                </table>--}}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
