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
        let DT = window.LaravelDataTables["dt-table"];
        $('.dt-filter').each(function () {
            $(this).change(function () {
                DT.column($(this).data('column') + ':name').search($(this).val()).draw();
            })
        });
    </script>
@endpush

@section('content')
    <div class="subheader">
        <h1 class="subheader-title">
            Local (Price per hour)
        </h1>
    </div>
    <div class="row">
        <div class="col-xl-12">

            <div id="panel-1" class="panel">
                <div class="panel-hdr">
                    <h2>
                        Local (Price per hour)
                    </h2>
                    <div class="panel-toolbar">
                        <div class="panel-toolbar">
                            <select class="custom-select custom-select-sm mr-2 dt-filter" data-column="season_text">
                                <option value="all">All seasons</option>
                                <option value="winter">Winter</option>
                                <option value="summer">Summer</option>
                            </select>
                            {{--                            @include('layouts.app.helpers.table_style', ['id' => 'dt-table'])--}}
                        </div>

                        {{--                        @include('layouts.app.helpers.table_style', ['id' => 'dt-table'])--}}
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
