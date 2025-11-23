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
    <script src="{{ mix('/js/clients.list.js') }}"></script>
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
                        Clients
                    </div>
                </h1>
            </div>


            <form id="list-form" data-url="{{ route('clients.recordsDT') }}">
                <div class="row mb-4">

                    <div class="col-md-12 col-lg-6 col-xl-3 mb-2">
                        <select class="form-control change-control client-autocomplete" name="ids[]" id="filter-ids"
                                placeholder="Search by id"
                                data-placeholder="Search client (by name, email, phones)"
                                data-route="{{route('client.record.autocomplete')}}" multiple>
                        </select>
                    </div>

                    <div class="col-md-4 col-xl-2 mb-2 d-flex">
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
                    <div class="col-xl-1 col-md-2">
                        <find-duplicates/>
{{--                        <button type="button" class="btn text-nowrap btn-primary waves-effect waves-themed" id="find-duplicates">--}}
{{--                            Find duplicates--}}
{{--                        </button>--}}
                    </div>
                </div>
            </form>
            <table id="dt-list-form" data-route="{{route('clients.recordsDT')}}"
                   class="table table-bordered table-hover w-100">
                <thead>
                <tr>
                    <th data-width="40%" data-data="client" class="client-column client" data-orderable="true">Client</th>
                    <th data-width="20%" data-data="phones" class="client-column phones" data-orderable="false">Phone</th>
                    <th data-width="20%" data-data="emails" class="client-column emails" data-orderable="false">Email</th>
                    <th data-width="20%" data-data="manage" class="client-column manage" data-orderable="false">Manage</th>
                </tr>
                </thead>
                <tbody>
                </tbody>
                <tfoot>
            </table>
            {{--            <orders-datatables></orders-datatables>--}}
            <client-modal ref="clientModal" when-updated="App.RecordsList.whenSaved"></client-modal>

            <div class="d-none">
                @include('layouts.clients.filters')
            </div>
        </div>
    </div>
@endsection
