@extends('layouts.app')

@push('extendHeader')
    <link href="{{ asset('/smartadmin/css/datagrid/datatables/datatables.bundle.css') }}" rel="stylesheet">
    <link href="{{ asset('css/datatables.css') }}" rel="stylesheet">
    <link rel="stylesheet" media="screen, print"
          href="{{ asset('/smartadmin/css/formplugins/select2/select2.bundle.css') }}">
    <link rel="stylesheet" media="screen, print"
          href="{{ asset('/smartadmin/css/formplugins/bootstrap-datepicker/bootstrap-datepicker.css') }}">
    <link rel="stylesheet" media="screen, print"
          href="{{ asset('/smartadmin/css/formplugins/bootstrap-daterangepicker/bootstrap-daterangepicker.css') }}">
    <link rel="stylesheet" media="screen, print" href="{{ mix('/css/order.css') }}">
    <style>
        .swal2-container {
            z-index: 2055;
        }

        .table td {
            padding: 5px .75rem;
        }
    </style>
@endpush

@push('extendFooter')
    <script src="{{ asset('/smartadmin/js/formplugins/select2/select2.bundle.js') }}"></script>
    <script src="{{ asset('/smartadmin/js/datagrid/datatables/datatables.bundle.js') }}"></script>
    <script src="{{ asset('/smartadmin/js/formplugins/bootstrap-datepicker/bootstrap-datepicker.js') }}"></script>
    <script
        src="{{ asset('/smartadmin/js/formplugins/bootstrap-daterangepicker/bootstrap-daterangepicker.js') }}">
    </script>
    <script src="{{ mix('/js/orders.list.js') }}"></script>
@endpush


@section('content')
    <div class="row">
        <div class="col">
            <div id="content-spinner" class="frame-wrap position-absolute w-100 h-100 opacity-50 d-none">
                <div class="w-100 d-flex justify-content-center align-items-center">
                    <div class="spinner-border text-info position-absolute" style="top:50%;" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                </div>
            </div>

            <div class="subheader">
                <h1 class="subheader-title d-flex flex-row">
                    <div>
                        Orders
                    </div>
                    <div class="ml-4 fs-md d-flex flex-row">
                        <div class="mr-2">
                            <button class="btn btn-success" id="btn-create-order">
                                Create order
                            </button>
                            <button class="btn btn-success" id="btn-create-order-modal" data-toggle="modal" data-target="#modal-order-create">
                                Quick Order
                            </button>
                        </div>
                        {{--                    <div>Today 1200</div>--}}
                        {{--                    <div class="ml-4">Tomorrow 1123</div>--}}
                        {{--                    <div class="ml-4">Next week 3990</div>--}}
                    </div>
                </h1>
            </div>


            <form id="order-list-form" action="{{ route('orders.records') }}" data-url="{{route('orders.recordsDT')}}">
                <div class="row mb-2">

                    <div class="col-md-12 col-lg-6 col-xl-3 mb-2">
                        <select class="form-control change-control" name="order_id[]" id="filter-order"
                                placeholder="Search order by id"
                                data-placeholder="Search order by id, e.g. #1000"
                                data-route="{{route('orders.autocomplete')}}" multiple>
                            @if(!empty($filteredOrders))
                                @foreach($filteredOrders as $v)
                                    <option selected value="{{$v->id}}">{{$v->title}}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>

                    <div class="col-md-12 col-lg-6 col-xl-3 mb-2 form-group">
                        <select class="form-control change-control" name="client[]" id="filter-client"
                                placeholder="Search client (by name, email, phones)"
                                data-placeholder="Search client (by name, email, phones)"
                                data-route="{{route('client.record.autocomplete')}}" multiple>
                            @if(!empty($filteredClients))
                                @foreach($filteredClients as $client)
                                    <option selected
                                            value="{{$client->id}}">{{$client->name}} {{$client->lname}}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                    <div class="col-md-4 col-xl-2 mb-sm-2 d-flex">
                        <div class="">
                            <div class="btn-group btn-group-toggle " data-toggle="buttons">
                                <label class="btn btn-outline-primary waves-effect waves-themed  position-relative"
                                       style="overflow: visible;">
                                    <span class="badge border border-light bg-danger-500 position-absolute pos-top pos-left"
                                          style="left: -9px; top: -8px;">{{$newOrders}}</span>
                                    <input type="checkbox" class="check-control" name="newLeads" id="newLeads"> New
                                </label>
                                <label class="btn btn-outline-primary waves-effect waves-themed">
                                    <input type="checkbox" class="check-control" name="myLeads" id="myLeads"> My
                                </label>
                            </div>
                        </div>

                        <div class="custom-control flex-fill">
                            <button type="button"
                                    class="text-nowrap btn btn-block d-inline-block btn-outline-primary waves-effect waves-themed position-static"
                                    id="filter-btn">
                                <span class="fas fa-filter mr-1"></span>
                                <span
                                    class="badge border border-light bg-danger-500 position-absolute pos-top pos-right px-2 d-none"
                                    style="top:-6px;">!</span>
                                Filter
                            </button>
                        </div>
                    </div>

                    <div class="col-md-8 col-xl-4">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text fs-xl">
                                    <i class="fal fa-calendar"></i>
                                </span>
                            </div>
                            <input type="text" class="form-control" placeholder="Select date" id="daterangepicker" disabled>
                            <select class="custom-select change-control" title="Daterange type" id="daterange-type" name="daterange-type" style="flex: 150px 0 1;">
                                <option value="by-none">None</option>
                                <option value="by-create-date">Creation date</option>
                                <option value="by-work-date">Service date</option>
                                <option value="by-transition-date">Stage transition date</option>
                            </select>
                            <input type="hidden" class="change-control" name="date-range[start]" id="daterange-start"
                                   value="{{ \Carbon\Carbon::now()->subDays(30)->format('Y-m-d') }}">
                            <input type="hidden" class="change-control" name="date-range[end]" id="daterange-end"
                                   value="{{ \Carbon\Carbon::now()->format('Y-m-d') }}">
                        </div>
                    </div>
                </div>
            </form>


            <orders-active-filters ref="activeFilters"></orders-active-filters>
            @push('extendFooter')
                <script type="text/javascript">
                    var params = @json($json, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
                </script>
            @endpush

            <table id="DT-Order-List" data-route="{{route('orders.recordsDT')}}"
                   class="table table-bordered table-hover w-100">
                <thead>
                <tr>
                    <th data-width="15%" data-data="details" class="orderDetails" data-orderable="true">Order</th>
                    <th data-width="20%" data-data="client" class="client-column" data-orderable="false">Client</th>
                    <th data-width="25%" data-data="work" data-orderable="false">Services</th>
                    <th data-width="12%" data-data="waypoints" data-orderable="false">Waypoints</th>
                    <th data-width="18%" data-data="estimate" data-orderable="false">Estimate</th>
                    <th data-width="10%" data-data="manager" data-orderable="false">Manager</th>
                </tr>
                </thead>
                <tbody>
                </tbody>
                <tfoot>
            </table>
            {{--            <orders-datatables></orders-datatables>--}}

            <div class="d-none">
                @include('layouts.order.records.filters')
            </div>
        </div>
    </div>
    @include('layouts.order.records.modal')
@endsection
