@extends('layouts.app')

@push('extendHeader')
    <link href="{{ asset('/smartadmin/css/datagrid/datatables/datatables.bundle.css') }}" rel="stylesheet">
    <link href="{{ asset('css/datatables.css') }}" rel="stylesheet">
    <link rel="stylesheet" media="screen, print"
          href="{{ asset('/smartadmin/css/formplugins/bootstrap-daterangepicker/bootstrap-daterangepicker.css') }}">
    <link rel="stylesheet" media="screen, print"
          href="{{ asset('/smartadmin/css/formplugins/select2/select2.bundle.css') }}">
    <style>
        .metric-col { min-width: 250px; }
        .col-manager { min-width: 100px; }
    </style>
@endpush

@push('extendFooter')
    <script src="{{ asset('/smartadmin/js/datagrid/datatables/datatables.bundle.js') }}"></script>
    <script src="{{ asset('/smartadmin/js/formplugins/select2/select2.bundle.js') }}"></script>
    <script src="{{ asset('/smartadmin/js/formplugins/bootstrap-datepicker/bootstrap-datepicker.js') }}"></script>
    <script src="{{ asset('/smartadmin/js/formplugins/bootstrap-daterangepicker/bootstrap-daterangepicker.js') }}"></script>
    <script src="{{ mix('/js/reports.sales.js') }}"></script>
@endpush

@section('content')
    <div class="subheader">
        <h1 class="subheader-title">
            Sales Report
        </h1>
    </div>
    <div class="row">
        <div class="col-xl-12">

        <div id="panel-1" class="panel">
            <div class="panel-hdr">
                <h2>Filters</h2>
                <div class="panel-toolbar">
                    <button
                        class="btn btn-panel"
                        data-action="panel-collapse"
                        data-toggle="tooltip"
                        data-offset="0,10"
                        data-original-title="Toggle filter panel"
                        aria-label="Toggle filter panel"
                    ></button>
                </div>
            </div>
            <div class="panel-container show">
                <div class="panel-content">
                    <form id="filter-form">
                        <div class="row">
                            <div class="col-xl-3 mb-2">
                                <div class="form-group">
                                    <label class="form-label">Period</label>
                                    <div class="d-flex">
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span
                                                    class="input-group-text fs-xl">
                                                    <i class="fal fa-calendar"></i>
                                                </span>
                                            </div>
                                            <input type="text"
                                                   class="form-control"
                                                   placeholder="Select date"
                                                   id="daterangepicker">
                                            <input type="hidden" name="date"
                                                   id="filter-date" value="">
                                            <div style="width: 160px;">
                                                <select name="period-type"
                                                        class="form-control border-bottom-left-radius-0 border-top-left-radius-0">
                                                    <option value="by_creation">
                                                        By creation date
                                                    </option>
                                                    <option
                                                        value="by_status_changed">
                                                        By transition date
                                                    </option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-3 mb-2">
                                <div class="form-group">
                                    <label class="form-label">Managers</label>
                                    <select class="form-control"
                                            name="managers[]" id="managers"
                                            size="1"
                                            multiple>
                                        @foreach($managers as $manager)
                                        <option
                                            value="{{$manager['id']}}"
                                            data-sales_team="{{$manager['sales_team'] ?? ''}}"
                                        >
                                            {{ $manager['name'] }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-3 mb-2">
                                <div class="form-group">
                                    <label class="form-label">Sales team</label>
                                    <select class="form-control"
                                            name="sales_team" id="sales_team">
                                        <option value="{{null}}" selected>
                                            ------
                                        </option>
                                        @foreach($sales_teams as $team)
                                        <option value="{{$team['key']}}">
                                            {{ $team['title'] }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-3 mb-2">
                                <div class="form-group">
                                    <label class="form-label">Move type</label>
                                    <select class="form-control"
                                            name="move_type" id="move_type">
                                        <option value="{{null}}" selected>
                                            ------
                                        </option>
                                        @foreach($move_types as $type)
                                        <option value="{{$type['key']}}">
                                            {{ $type['title'] }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-auto mb-2 d-flex flex-wrap gap-2 pt-2">
                                <button
                                    class="btn btn-primary waves-effect waves-themed"
                                    type="submit">Show Report
                                </button>
                                <button
                                    data-endpoint="/reports/sales-datatable/export-csv"
                                    class="js-export-button btn-dis-loading btn btn-outline-secondary"
                                    type="button">
                                    <span
                                        class="spinner-border btn-dis-spinner fs-nano text-secondary position-absolute"
                                    ></span>
                                    Export CSV
                                </button>
                                <button
                                    data-endpoint="/reports/sales-datatable/export-excel"
                                    class="js-export-button btn-dis-loading btn btn-outline-secondary"
                                    type="button">
                                    <span
                                        class="spinner-border btn-dis-spinner fs-nano text-secondary position-absolute"
                                    ></span>
                                    Export Excel
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div id="panel-2" class="panel">
            <div class="panel-hdr">
                <h2>
                    Report
                </h2>
                <div class="panel-toolbar">
                    <select class="custom-select custom-select-sm"
                            id="show-cols">
                        <option value="all">All columns</option>
                        <option value="hide-no-data">Hide without data</option>
                    </select>
                </div>
            </div>
            <div class="panel-container show">
                <div class="panel-content">
                    <table
                        class="table table-bordered table-hover table-striped w-100"
                        id="dt-table"
                        data-route="{!! route('reports.sales.datatable') !!}">
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
