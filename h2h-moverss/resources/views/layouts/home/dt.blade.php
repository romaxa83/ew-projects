@extends('layouts.app')

@push('extendHeader')
<link href="{{ asset('/smartadmin/css/datagrid/datatables/datatables.bundle.css') }}" rel="stylesheet">
<link href="{{ asset('css/datatables.css') }}" rel="stylesheet">
@endpush

@push('extendFooter')
<script src="{{ asset('/smartadmin/js/datagrid/datatables/datatables.bundle.js') }}"></script>
<script src="{{ mix('js/datatables-editor-bundle.js') }}"></script>
<script src="{{ mix('js/dt/helpers.js') }}"></script>
{{$dataTable->scripts()}}
@endpush

@section('content')
<div class="subheader">
    <h1 class="subheader-title">
        Users
    </h1>
</div>
<div class="row">
    <div class="col-xl-12">

        <div id="panel-1" class="panel">
            <div class="panel-hdr">
                <h2>
                    Users
                </h2>
                <div class="panel-toolbar">
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
