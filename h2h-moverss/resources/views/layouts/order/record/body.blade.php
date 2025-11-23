@extends('layouts.app')

@push('extendHeader')
    <link rel="stylesheet" media="screen, print"
          href="{{ asset('/smartadmin/css/formplugins/select2/select2.bundle.css') }}">
    <link rel="stylesheet" media="screen, print" href="{{ asset('/css/bootstrap-multiselect.css') }}">
    <link rel="stylesheet" media="screen, print" href="{{ mix('/css/flatpicker.css') }}">
    <link rel="stylesheet" media="screen, print" href="{{ mix('/css/order.css') }}">
@endpush

@push('extendFooter')
    <script src="{{ asset('/js/multiselect.js') }}"></script>
    <script src="{{ asset('/smartadmin/js/formplugins/select2/select2.bundle.js') }}"></script>
    <script src="{{ mix('/js/flatpicker-plugins.js') }}"></script>
    <script src="{{ mix('/js/order.js') }}"></script>
    <script type="application/javascript">
        var statuses_list = @json($statuses->keyBy('id'));
        var closing_statuses = @json($closing_statuses);
        var closing_statuses_with_groups = @json($closing_statuses_with_groups);
        var email_templates = @json($email_templates_v2);
        var order_managers = @json($order_managers);

        function mapInit() {
        }
    </script>
    <script defer
            src="https://maps.googleapis.com/maps/api/js?key={{ config('app.google.maps.key') }}&v=3.exp&libraries=geometry,places&language=en&callback=mapInit"></script>
@endpush


@section('content')

{{--    @includeWhen(isset($breadcrumbs), 'layouts.app.breadcrumbs')--}}

    <span class="d-none" id="order_id">{{ $record->id }}</span>{{--    FIXME remove     --}}

    @include('layouts.order.record.tabs')
@endsection
