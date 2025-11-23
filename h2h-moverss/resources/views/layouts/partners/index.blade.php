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

        $('#status-filter').change(function () {
            DT.column('work_status:name').search($(this).val()).draw();
        });

        if ($('#phone_partner').length == 1) {
            window.Inputmask({"mask": "(999) 999-9999"}).mask($('#phone_partner')[0]);
        }
    </script>
@endpush


@section('content')
    @include('layouts.partners.add_record')


    <div class="subheader">
        <h1 class="subheader-title">
            Partners
        </h1>
    </div>

    <div class="row">
        <div class="col-xl-12">

            <div id="panel-1" class="panel">
                <div class="panel-hdr">
                    <h2>
                        Partners
                    </h2>
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

