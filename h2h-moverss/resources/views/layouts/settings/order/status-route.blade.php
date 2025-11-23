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

        DT.on('click', 'tbody td', function (e) {
            let cellIndex = DT.cell(this).index();
            if ($(DT.column(cellIndex.column).header()).hasClass('editable')) {
                DT_EDITOR.inline(cellIndex, {
                    onBlur: 'submit',
                    submit: 'allIfChanged'
                });
            }
        });

        // Inline editing on tab focus
        DT.on('key-focus', function (e, datatable, cell) {
            DT_EDITOR.inline(cell.index(), {
                onBlur: 'submit',
                submit: 'allIfChanged'
            });
        });

        DT.on('click', '.editor-delete', function (e) {
            DT_Helpers.remove(e, $(this), DT_EDITOR);
        });

        DT_EDITOR.field('status_from').input().on('change', function (e) {
            if (e.hasOwnProperty('originalEvent'))
                DT_EDITOR.submit();
        });
        DT_EDITOR.field('status_to').input().on('change', function (e) {
            if (e.hasOwnProperty('originalEvent'))
                DT_EDITOR.submit();
        });

        $('#create').submit(function (e) {
            e.preventDefault();

            let form = $(this),
                status_from = form.find('[name="status_from"]').val(),
                status_to = form.find('[name="status_to"]').val();

            DT_EDITOR
                .create(false)
                .set({
                    status_from,
                    status_to
                })
                .submit(
                    function () {
                        toastr.success('Form successfully submitted!');
                        $(form)[0].reset();
                    },
                    function (data) {
                        console.log(data);
                        toastr.error('Form encountered an error :-(');
                    }
                );
        });
    </script>
@endpush


@section('content')
<div class="subheader">
    <h1 class="subheader-title">
        Status Routes
    </h1>
</div>
<div class="row">
    <div class="col-xl-12">
        <div id="panel-2" class="panel">
            <div class="panel-hdr">
                <h2>
                    New Route
                </h2>
            </div>
            <div class="panel-container show">
                <div class="panel-content">

                    <form id="create">
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label class="form-label" for="status_from">From Status</label>
                                    <select id="status_from" name="status_from" class="form-control"
                                            placeholder="Status src">
                                        @foreach ($statuses as $v)
                                            <option value="{{ $v['id'] }}">{{ $v['title'] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label class="form-label" for="status_to">To Status</label>
                                    <div class="input-group">
                                        <select id="status_to" name="status_to"
                                                class="form-control" placeholder="Status dst">
                                            @foreach ($statuses as $v)
                                                <option value="{{ $v['id'] }}">{{ $v['title'] }}</option>
                                            @endforeach
                                        </select>
                                        <div class="input-group-append">
                                            <button class="btn btn-outline-primary waves-effect waves-themed"
                                                    type="submit">Add
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div id="panel-1" class="panel">
            <div class="panel-hdr">
                <h2>
                    Routes
                </h2>
                <div class="panel-toolbar">
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
