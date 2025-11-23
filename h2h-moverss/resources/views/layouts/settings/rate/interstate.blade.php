@extends('layouts.app')

@push('extendHeader')
    <link href="{{ asset('/smartadmin/css/datagrid/datatables/datatables.bundle.css') }}" rel="stylesheet">
    <link href="{{ asset('css/datatables.css') }}" rel="stylesheet">

    {{--    <link rel="stylesheet" media="screen, print"--}}
    {{--          href="{{ asset('/smartadmin/css/formplugins/bootstrap-daterangepicker/bootstrap-daterangepicker.css') }}">--}}
    <link rel="stylesheet" media="screen, print"
          href="{{ asset('/smartadmin/css/formplugins/select2/select2.bundle.css') }}">
    <style>
        .state-coefficients-input {
            padding-left: 0.3rem;
            padding-right: 0.3rem;
        }
    </style>
@endpush

@push('extendFooter')
    <script src="{{ asset('/smartadmin/js/datagrid/datatables/datatables.bundle.js') }}"></script>
    <script src="{{ mix('js/datatables-editor-bundle.js') }}"></script>

    {{--    <script src="{{ asset('/smartadmin/js/formplugins/select2/select2.bundle.js') }}"></script>--}}
    <script src="{{ mix('/js/settings.interstate.js') }}"></script>
@endpush

@section('content')
    {{--@includeWhen(isset($breadcrumbs), 'layouts.app.breadcrumbs')--}}

    <div class="row">
        <div class="col-lg-12">
            <div class="panel">
                <div class="panel-hdr">
                    <h2>
                        Interstate matrix
                    </h2>
                    <div class="panel-toolbar flex-fill">
                        <select class="custom-select custom-select-sm" id="ranges-selector">
{{--                            <option selected="">Select</option>--}}
{{--                            <option value="1">One</option>--}}
{{--                            <option value="2">Two</option>--}}
{{--                            <option value="3">Three</option>--}}
                        </select>
                    </div>
                </div>

                <div class="panel-container show">
                    <div class="panel-content d-none" id="matrix-panel">
                        <table class="table table-bordered" id="dt-interstate-coefficients-matrix"
                               data-route="{!! route('settings.rates.interstate.coefficients.matrix') !!}"
                               data-route-editor="{!! route('settings.rates.interstate.coefficients.matrix.editor') !!}">
                            <thead>
                            <tr>
                                <th data-data="from_abbr">FROM&nbsp;\&nbsp;TO</th>
                                @foreach ($states as $state=>$name)
                                    <th data-data="to_{{$state}}" data-name="to_{{$state}}" data-width="60px" class="state-column">{{$name}}</th>
                                @endforeach
                            </tr>
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
                        Interstate volume ranges
                    </h2>
                </div>
                <div class="panel-container show">
                    <div class="panel-content">

                        <table class="table table-bordered" id="dt-interstate-ranges"
                               data-route="{!! route('settings.rates.interstate.ranges.datatable') !!}"
                               data-route-editor="{!! route('settings.rates.interstate.ranges.datatable.editor') !!}">
                            <thead>
                            <tr>
                                <th data-width="10" data-data="id" data-visible="false">Range ID</th>
                                <th data-width="40" data-data="cbft_from">CuFt From</th>
                                <th data-width="40" data-data="cbft_to">CuFt To</th>
                                <th data-width="10" data-orderable="false" class="btns"></th>
                            </tr>
                            </thead>
                        </table>

                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="panel">
                <div class="panel-hdr">
                    <h2>
                        Interstate shuttle rates
                    </h2>
                </div>
                <div class="panel-container show">
                    <div class="panel-content">

                        <table class="table table-bordered" id="dt-interstate-shuttle"
                               data-route="{!! route('settings.rates.interstate.shuttle.datatable') !!}"
                               data-route-editor="{!! route('settings.rates.interstate.shuttle.datatable.editor') !!}">
                            <thead>
                            <tr>
                                <th data-width="10" data-data="id" data-visible="false">Range ID</th>
                                <th data-width="40" data-data="min">CuFt From</th>
                                <th data-width="40" data-data="max">CuFt To</th>
                                <th data-width="40" data-data="price">Rate, $</th>
                                <th data-width="10" data-orderable="false" class="btns"></th>
                            </tr>
                            </thead>
                        </table>

                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
