@extends('layouts.app')

@push('extendHeader')
<link href="{{ asset('/smartadmin/css/datagrid/datatables/datatables.bundle.css') }}" rel="stylesheet">
<link href="{{ asset('css/datatables.css') }}" rel="stylesheet">
@endpush

@push('extendFooter')
<script src="{{ asset('/smartadmin/js/datagrid/datatables/datatables.bundle.js') }}"></script>
<script src="{{ mix('js/datatables-editor-bundle.js') }}"></script>
@endpush


@section('content')
<div class="subheader">
    <h1 class="subheader-title">
        Items:
    </h1>
</div>
<div class="row">
    <div class="col-xl-12">

        @include('layouts.settings.items.groups')

        @include('layouts.settings.items.items')

    </div>
</div>
@endsection
