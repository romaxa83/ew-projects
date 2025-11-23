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
    <script src="{{ asset('/smartadmin/js/formplugins/select2/select2.bundle.js') }}"></script>
    <script src="{{ asset('/smartadmin/js/formplugins/bootstrap-datepicker/bootstrap-datepicker.js') }}"></script>
    <script
        src="{{ asset('/smartadmin/js/formplugins/bootstrap-daterangepicker/bootstrap-daterangepicker.js') }}">
    </script>
@endpush

@section('content')
{{--@includeWhen(isset($breadcrumbs), 'layouts.app.breadcrumbs')--}}

<div class="subheader">
    <h1 class="subheader-title d-flex flex-row">
        {{ $title }}
    </h1>
</div>


<div class="row">
    <div class="col-xl-12">
        @if($withoutPanel ?? false)
            <{!! $component . (isset($params) ? ' '.$params:'') !!}></{!! $component !!}>
        @else
        <div id="panel-1" class="panel">
            <div class="panel-hdr">
                <h2>
                    {{ $h2 ?? 'Report data' }}
                </h2>
            </div>
            <div class="panel-container show">
                <div class="panel-content">
                    <{!! $component . (isset($params) ? ' '.$params:'') !!}></{!! $component !!}>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
