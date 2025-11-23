@extends('layouts.app')

@push('extendHeader')
    <link href="{{ asset('/smartadmin/css/datagrid/datatables/datatables.bundle.css') }}" rel="stylesheet">
    <link href="{{ asset('css/datatables.css') }}" rel="stylesheet">

    <link rel="stylesheet" media="screen, print"
          href="{{ asset('/smartadmin/css/formplugins/bootstrap-daterangepicker/bootstrap-daterangepicker.css') }}">
    <link rel="stylesheet" media="screen, print"
          href="{{ asset('/smartadmin/css/formplugins/select2/select2.bundle.css') }}">
@endpush

@push('extendFooter')
    <script src="{{ asset('/smartadmin/js/datagrid/datatables/datatables.bundle.js') }}"></script>
    <script src="{{ asset('/smartadmin/js/formplugins/select2/select2.bundle.js') }}"></script>
    <script src="{{ asset('/smartadmin/js/formplugins/bootstrap-datepicker/bootstrap-datepicker.js') }}"></script>
    <script src="{{ asset('/smartadmin/js/formplugins/bootstrap-daterangepicker/bootstrap-daterangepicker.js') }}"></script>
    <script src="{{ mix('/js/reports.audit.js') }}"></script>
@endpush

@section('content')
    {{--@includeWhen(isset($breadcrumbs), 'layouts.app.breadcrumbs')--}}


    <div class="row">
        <div class="col-xl-12">

            <div id="panel-1" class="panel">
                <div class="panel-hdr">
                    <h2>
                        {{ 'Activity audit report' }}
                    </h2>
                </div>
                <div class="panel-container show">
                    <div class="panel-content">
                        <form id="filter-form">
                            <div class="row">
                                <div class="col-4 col-xl-3 p-2">
                                    <div class="form-group">
                                        <label class="form-label">Period</label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text fs-xl">
                                                    <i class="fal fa-calendar"></i>
                                                </span>
                                            </div>
                                            <input type="text" class="form-control" placeholder="Select date" id="daterangepicker">
                                            <input type="hidden" name="start-range" id="start-range"
                                                   value="@if(!empty($filter['from'])){{$filter['from']}}@endif">
                                            <input type="hidden" name="end-range" id="end-range" value="">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-4 col-xl-3 p-2">
                                    <div class="form-group">
                                        <label class="form-label">Event Author</label>
                                        <select class="form-control select2-select" name="author[]" data-placeholder="Select author" multiple>
                                            @foreach($users as $user)
                                                --
                                                <option value="{{$user['id']}}">{{$user['name']}}</option>--}}
                                            @endforeach
                                        </select>

                                    </div>
                                </div>
                                <div class="col-4 col-xl-3 p-2">
                                    <div class="form-group">
                                        <label class="form-label">By Order</label>
                                        <select class="form-control" name="order[]" id="filter-order"
                                                data-placeholder="Search Order (by #id, client name, phones, email)" multiple>
                                            @if(!empty($filter['order']))
                                                @if(is_array($filter['order']))
                                                    @foreach($filter['order'] as $order)
                                                        <option value="{{$order}}" selected>Order #{{$order}}</option>
                                                    @endforeach
                                                @else
                                                    <option value="{{$filter['order']}}" selected>Order #{{$filter['order']}}</option>
                                                @endif
                                            @endif
                                        </select>
                                    </div>
                                </div>
                                <div class="col-4 col-xl-3 p-2">
                                    <div class="form-group">
                                        <label class="form-label">By Client</label>
                                        <select class="form-control" name="client[]" id="filter-client"
                                                data-placeholder="Search Client (by client name, phones, email)" multiple>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-4 col-xl-3 p-2">
                                    <div class="form-group">
                                        <label class="form-label">By Objects</label>
                                        <select class="form-control select2-select" name="object[]" data-placeholder="Select object" multiple>
                                            @foreach($objects as $object)
                                                <option value="{{$object['id']}}">{{$object['text']}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-4 col-xl-3 p-2">
                                    <div class="form-group">
                                        <label class="form-label">Event type</label>
                                        <select class="form-control select2-select" name="event[]" data-placeholder="Select event" multiple>
                                            @foreach($events as $event)
                                                <option value="{{$event}}">{{$event}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-3 col-xl-2 p-2">
                                    <div class="form-group">
                                        <label class="form-label">&nbsp;</label><br>
                                        <button type="submit" name="create" class="btn btn-primary waves-effect waves-themed">
                                            Apply Filter
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div id="panel-10" class="panel">
                <div class="panel-hdr">
                    <h2>
                        Activity Chart <span class="fw-300"><i>-48h from period end, rounded to 5 min</i></span>
                    </h2>
                </div>
                <div class="panel-container show">
                    <div class="panel-content">
{{--                        <div class="panel-tag">--}}
{{--                            This Area chart has smooth curved lines to make it easy to read--}}
{{--                        </div>--}}
                        <div id="plot-area" style="width:100%; height:300px;"></div>
                    </div>
                </div>
            </div>

            <div id="panel-2" class="panel">
                <div class="panel-container show">
                    <div class="panel-content">
                        {{--                    <div class="table-responsive">--}}
                        <table class="table table-bordered" id="dt-table" data-route="{!! route('reports.audit.datatable') !!}">
                            <thead>
                            <tr>
                                {{--                                <th class="details-control-th"></th>--}}
                                <th data-data="id" data-visible="false">Id</th>
                                <th data-data="updated_at_division_tz" class="details-control-th">Updated At</th>
                                <th data-data="updated_at" data-visible="false">Updated At UTC</th>
                                <th data-data="event">Event</th>
                                <th data-data="user_id">Author</th>
                                <th data-data="auditable_type">Object</th>
                                <th data-data="order_id" class="order_id">Order ID</th>
                                <th data-data="auditable_id" data-visible="false">Object ID</th>
                                <th data-data="old_values_cutted" data-width="25%" data-orderable="false">Old values</th>
                                <th data-data="new_values_cutted" data-width="25%" data-orderable="false">New values</th>
                                <th data-data="new_values" data-visible="false"></th>
                                <th data-data="old_values" data-visible="false"></th>
                                <th data-data="client_id" data-visible="false">Client ID</th>
                                {{--                            <th data-data="created_at">Created At</th>--}}
                            </tr>
                            </thead>
                        </table>
                        {{--                    </div>--}}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
