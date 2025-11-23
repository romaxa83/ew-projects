@extends('layouts.app')

@push('extendHeader')
    @if($assets)
        @foreach($assets as $asset)
            <link rel="stylesheet" media="screen, print" href="{{ asset($asset) }}">
        @endforeach
    @endif

    <link rel="stylesheet" media="screen, print"
          href="{{ asset('/smartadmin/css/formplugins/bootstrap-daterangepicker/bootstrap-daterangepicker.css') }}">
    <link rel="stylesheet" media="screen, print"
          href="{{ asset('/smartadmin/css/formplugins/select2/select2.bundle.css') }}">
@endpush

@push('extendFooter')
    @if($mixed)
        @foreach($mixed as $script)
            <script src="{{ mix($script) }}"></script>
        @endforeach
    @endif
    <script src="{{ asset('/smartadmin/js/formplugins/select2/select2.bundle.js') }}"></script>
    <script src="{{ asset('/smartadmin/js/formplugins/bootstrap-datepicker/bootstrap-datepicker.js') }}"></script>
    <script
        src="{{ asset('/smartadmin/js/formplugins/bootstrap-daterangepicker/bootstrap-daterangepicker.js') }}">
    </script>
@endpush

@section('content')
{{--    @includeWhen(isset($breadcrumbs), 'layouts.app.breadcrumbs')--}}

    @if($title)
    <div class="subheader">
        <h1 class="subheader-title d-flex flex-row">
            <div>
                {{ $title }}
            </div>
            {{--                <div class="ml-4 fs-md d-flex flex-row">--}}
            {{--                    <div class="mr-2">--}}
            {{--                        <button class="btn btn-md btn-success waves-effect waves-themed">--}}
            {{--                            <span class="fal fa-save mr-2"></span>Save all--}}
            {{--                        </button>--}}
            {{--                    </div>--}}
            {{--                </div>--}}
        </h1>
    </div>
    @endif

    <div class="row">
        <div class="col-xl-12">
            <{!! $component !!}>
        </{!! $component !!}>
    </div>
    </div>
@endsection
