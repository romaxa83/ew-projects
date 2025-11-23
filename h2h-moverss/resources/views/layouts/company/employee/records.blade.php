@extends('layouts.app')

@push('extendHeader')
    <link href="{{ asset('/smartadmin/css/datagrid/datatables/datatables.bundle.css') }}" rel="stylesheet">
    <link href="{{ asset('css/datatables.css') }}" rel="stylesheet">
@endpush

@push('extendFooter')
    <script src="{{ asset('/smartadmin/js/datagrid/datatables/datatables.bundle.js') }}"></script>
    <script src="{{ mix('js/datatables-editor-bundle.js') }}"></script>
    {{$dataTable->scripts()}}
    <script>
        let DT = window.LaravelDataTables["dt-table"],
            DT_EDITOR = window.LaravelDataTables["dt-table-editor"];

        {{--DT.on('click', 'tbody tr', function () {--}}
            {{--    let id = $('td', this).first().text();--}}
            {{--    window.location.href = '{{ route('company.employees.records') }}/' + id;--}}
            {{--});--}}
        if ($('#phone_new').length == 1)
            window.Inputmask({"mask": "(999) 999-9999"}).mask($('#phone_new')[0]);

        $('#status-filter').change(function () {
            DT.column('job_status:name').search($(this).val()).draw();
        });
        $('#roles-filter').change(function () {
            DT.column('role:name').search($(this).val()).draw();
        });
    </script>
@endpush


@section('content')
    @include('layouts.company.employee.add_record')

    <div class="subheader">
        <h1 class="subheader-title">
            Employees
        </h1>
    </div>
    <div class="row">
        <div class="col-xl-12">

            <div id="panel-1" class="panel">
                <div class="panel-hdr">
                    <h2>
                        Employees
                    </h2>
                    <div class="panel-toolbar">
                        <select class="custom-select custom-select-sm" id="roles-filter">
                            <option value="all">Any roles</option>
                            @foreach($roles as $role)
                                <option value="{{$role->id}}">{{$role->title}}</option>
                            @endforeach
                        </select>
                        {{--                            @include('layouts.app.helpers.table_style', ['id' => 'dt-table'])--}}
                        <select class="ml-2 custom-select custom-select-sm" id="status-filter">
                            <option value="1">Only In Service</option>
                            <option value="0">Only Fired</option>
                            <option value="all">Any status</option>
                        </select>
                        {{--                            @include('layouts.app.helpers.table_style', ['id' => 'dt-table'])--}}
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
