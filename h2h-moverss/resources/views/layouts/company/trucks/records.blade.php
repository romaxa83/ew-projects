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
        {{--    window.location.href = '{{ route('company.trucks.records') }}/' + id;--}}
        {{--});--}}
        $('#status-filter').change(function () {
            DT.column('work_status:name').search($(this).val()).draw();
        });
    </script>
@endpush


@section('content')

    @if(!\Auth::user()->isPartner())
        <div class="panel">
            <div class="panel-hdr">
                <h2>
                    New Truck
                </h2>
            </div>
            <div class="panel-container show">
                <div class="panel-content">

                    <form method="post" action="{{ route('company.trucks.record.create') }}">
                        <div class="row">
                            @csrf
                            <div class="col-lg-2">
                                <div class="form-group">
                                    <label class="form-label" for="title"><sup>*</sup>Title</label>
                                    <input name="title" class="form-control @error('title') is-invalid @enderror" id="title"
                                           type="text" value="{{ old('title') }}" required>

                                    @error('title')
                                    <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-lg-2">
                                <div class="form-group">
                                    <label class="form-label" for="nickname"><sup>*</sup>Nickname</label>
                                    <input name="nickname" class="form-control @error('nickname') is-invalid @enderror" id="nickname"
                                           type="text" value="{{ old('nickname') }}" required>

                                    @error('nickname')
                                    <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-lg-1">
                                <div class="form-group">
                                    <label class="form-label" for="l_plate">License plate</label>
                                    <input name="l_plate" class="form-control @error('l_plate') is-invalid @enderror" id="l_plate"
                                           type="text" value="{{ old('l_plate') }}">

                                    @error('l_plate')
                                    <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-lg-2">
                                <div class="form-group">
                                    <label class="form-label" for="year">Year</label>
                                    <div class="input-group">
                                        <input name="year" id="year" type="text" value="{{ old('year') }}"
                                               class="form-control @error('year') is-invalid @enderror" placeholder="YYYY">
                                        <div class="input-group-append">
                                            <button class="btn btn-outline-primary waves-effect waves-themed" type="submit">
                                                Create
                                            </button>
                                        </div>

                                        @error('year')
                                        <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                        </div>
                    </form>

                </div>
            </div>
        </div>
    @endif

    <div class="subheader">
        <h1 class="subheader-title">
            Trucks
        </h1>

    </div>
    <div class="row">
        <div class="col-xl-12">

            <div id="panel-1" class="panel">
                <div class="panel-hdr">
                    <h2>
                        Trucks
                    </h2>
                    <div class="panel-toolbar">
                        <select class="custom-select custom-select-sm ml-2" id="status-filter">
                            <option value="1">Only In Service</option>
                            <option value="0">Only Sold</option>
                            <option value="all">All</option>
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
