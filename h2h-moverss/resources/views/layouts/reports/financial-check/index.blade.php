@extends('layouts.app')

@push('extendHeader')
    <link href="{{ asset('/smartadmin/css/datagrid/datatables/datatables.bundle.css') }}" rel="stylesheet">
    <link href="{{ asset('css/datatables.css') }}" rel="stylesheet">
    <link rel="stylesheet" media="screen, print"
          href="{{ asset('/smartadmin/css/formplugins/bootstrap-daterangepicker/bootstrap-daterangepicker.css') }}">
@endpush

@push('extendFooter')
    <script src="{{ asset('/smartadmin/js/datagrid/datatables/datatables.bundle.js') }}"></script>
    <script src="{{ mix('js/datatables-editor-bundle.js') }}"></script>
    <script
        src="{{ asset('/smartadmin/js/formplugins/bootstrap-daterangepicker/bootstrap-daterangepicker.js') }}">
    </script>
    {{$dataTable->scripts()}}
    <script>
        let DT = window.LaravelDataTables["dt-table"],
            DT_EDITOR = window.LaravelDataTables["dt-table-editor"],
            startDate = moment().subtract(8, 'days'),
            endDate = moment().subtract(1, 'day')
        ;

        $('#user-filter').change(function () {
            DT.column('user:name').search($(this).val()).draw();
        });

        // $('#roles-filter').change(function () {
        //     DT.column('role:name').search($(this).val()).draw();
        // });

        $('#dateRangePicker').daterangepicker({
            minDate: moment('2022-05-01', 'YYYY-MM-DD'),
            maxDate: moment(),
            startDate,
            endDate,
            drops: 'auto',
            locale: {
                format: 'MMM DD, YYYY'
            },
            maxSpan: {
                days: 365
            },
            alwaysShowCalendars: true,
            ranges: {
                'Today': [moment(), moment()],
                'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                'This Month': [moment().startOf('month'), moment()],
                // 'Last 14 Days': [moment().subtract(13, 'days'), moment()],
            }
        }, (start, end) => {
            DT.column('date_range:name').search([start.format('YYYY-MM-DD'), end.format('YYYY-MM-DD')]).draw();
        });

    </script>
@endpush


@section('content')
    <div class="subheader">
        <h1 class="subheader-title">
            Financial check report
        </h1>
    </div>

    <div class="row">
        <div class="col-xl-12">

            <div id="panel-1" class="panel">
                <div class="panel-hdr">
                    <h2>
                        Financial check report
                    </h2>
                    <div class="panel-toolbar">
                        <div style="width: 250px;" class="mr-2">
                            <input type="text" class="ml-2 form-control form-control-sm" placeholder="Select date" id="dateRangePicker">
                        </div>
                        <select class="ml-2 custom-select custom-select-sm" id="user-filter" style="width: 250px;">
                            <option value="all">All users</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}">
                                    {{ $user->name }} ({{ !is_null($user->employee) ? $user->employee->name . ' ' . $user->employee->l_name: null}})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="panel-container show">
                    <div class="panel-content">

                        {{$dataTable->table(['class' => 'table table-bordered table-hover table-striped w-100'])}}

                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

