@extends('layouts.app')

@push('extendHeader')
    <link rel="stylesheet" media="screen, print"
          href="{{ asset('/smartadmin/css/formplugins/select2/select2.bundle.css') }}">
@endpush

@push('extendFooter')
    <script src="https://unpkg.com/popper.js@1/dist/umd/popper.min.js"></script>
    <script src="https://unpkg.com/tippy.js@4"></script>
    <script src="{{ asset('/smartadmin/js/formplugins/select2/select2.bundle.js') }}"></script>
    <script src="{{ asset('/smartadmin/js/formplugins/bootstrap-datepicker/bootstrap-datepicker.js') }}"></script>
@endpush

@section('content')
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
                <i class='subheader-icon fal fa-plus-circle'></i> Partner Edit
            </div>
        </h1>
    </div>

    <partner></partner>
@endsection

