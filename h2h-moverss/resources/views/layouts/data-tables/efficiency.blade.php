@extends('layouts.app')

@push('extendHeader')
    <link href="{{ asset('/smartadmin/css/datagrid/datatables/datatables.bundle.css') }}" rel="stylesheet">
    <link href="{{ asset('css/datatables.css') }}" rel="stylesheet">
    <link rel="stylesheet" media="screen, print"
          href="{{ asset('/smartadmin/css/formplugins/bootstrap-daterangepicker/bootstrap-daterangepicker.css') }}">
    <link rel="stylesheet" media="screen, print"
          href="{{ asset('/smartadmin/css/formplugins/select2/select2.bundle.css') }}">
    <style>
        .metric-col {
            min-width: 250px;
        }

        .col-manager {
            min-width: 100px;
        }
    </style>
@endpush

@push('extendFooter')
    <script src="{{ asset('/smartadmin/js/datagrid/datatables/datatables.bundle.js') }}"></script>
    <script src="{{ asset('/smartadmin/js/formplugins/select2/select2.bundle.js') }}"></script>
    <script src="{{ asset('/smartadmin/js/formplugins/bootstrap-datepicker/bootstrap-datepicker.js') }}"></script>
    <script src="{{ asset('/smartadmin/js/formplugins/bootstrap-daterangepicker/bootstrap-daterangepicker.js') }}"></script>
    <script src="{{ mix('/js/reports.efficiency.js') }}"></script>
@endpush


@section('content')

    <div class="subheader">
        <h1 class="subheader-title">
            Efficiency Report
        </h1>
    </div>
    <div class="row">
        <div class="col-xl-12">

            <div id="panel-1" class="panel">
                <div class="panel-hdr">
                    <h2>
                        Filters
                    </h2>
                    <div class="panel-toolbar">
                        <button class="btn btn-panel" data-action="panel-collapse" data-toggle="tooltip" data-offset="0,10"
                                data-original-title="Collapse"></button>
                    </div>
                </div>
                <div class="panel-container show">
                    <div class="panel-content">
                        <form id="filter-form">
                            <div class="row">
                                <div class="col-xl-3 p-2">
                                    <div class="form-group">
                                        <label class="form-label">Period</label>
                                        <div class="d-flex">
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                <span class="input-group-text fs-xl">
                                                    <i class="fal fa-calendar"></i>
                                                </span>
                                                </div>
                                                <input type="text" class="form-control" placeholder="Select date" id="daterangepicker">
                                                <input type="hidden" name="start-range" id="start-range" value="">
                                                <input type="hidden" name="end-range" id="end-range" value="">
                                                <div style="width: 160px;">
                                                    <select name="period-type" class="form-control border-bottom-left-radius-0 border-top-left-radius-0">
                                                        <option value="by_creation">By creation date</option>
                                                        <option value="by_status_changed">By status changed date</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-2 p-2">
                                    <div class="form-group">
                                        <label class="form-label">Group By</label>
                                        <select class="form-control" name="groupBy">
                                            <option value="">No groupping</option>
                                            <option value="manager">Managers</option>
                                            <option value="source">Sources</option>
                                            <option value="day">Days</option>
                                            {{--                                            <option value="week">Weeks</option>--}}
                                            <option value="month">Months</option>
                                            <option value="year">Years</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-lg-3 p-2">
                                    <div class="form-group">
                                        <label class="form-label">Selected managers</label>
                                        <select class="form-control" name="managers[]" id="managers" multiple>
                                            @foreach($managers as $manager)
                                                <option value="{{$manager['id']}}">{{$manager['name']}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-lg-3 p-2">
                                    <div class="form-group">
                                        <label class="form-label">Selected sources</label>
                                        <select class="form-control" name="sources[]" id="sources-select" multiple>
                                            @foreach($sources as $source)
                                                <option value="{{$source->id}}">{{$source->title}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-lg-1 p-2">
                                    <div class="form-group">
                                        <label class="form-label">&nbsp;</label><br>
                                        <button class="btn btn-success" type="submit">Apply</button>
                                    </div>
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
                        <select class="custom-select custom-select-sm" id="show-cols">
                            <option value="all">All columns</option>
                            <option value="hide-no-data">Hide without data</option>
                        </select>
                    </div>
                </div>
                <div class="panel-container show">
                    <div class="panel-content">
                        <table class="table table-bordered table-hover table-striped w-100" id="dt-table"
                               data-route="{!! route('reports.efficiency.datatable') !!}">
                        </table>
                    </div>
                </div>
            </div>


        </div>
    </div>
@endsection
