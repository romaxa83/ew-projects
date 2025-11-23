@extends('layouts.app')

@push('extendHeader')
<link href="{{ asset('/smartadmin/css/datagrid/datatables/datatables.bundle.css') }}" rel="stylesheet">
<link href="{{ asset('css/datatables.css') }}" rel="stylesheet">
<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/spectrum/1.8.0/spectrum.min.css">
@endpush

@push('extendFooter')
<script src="{{ asset('/smartadmin/js/datagrid/datatables/datatables.bundle.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/spectrum/1.8.0/spectrum.min.js"></script>
<script src="{{ mix('js/datatables-editor-bundle.js') }}"></script>
<script src="{{ mix('js/dt/type_ColorPicker.js') }}"></script>
<script src="{{ mix('js/dt/helpers.js') }}"></script>
{{$dataTable->scripts()}}
<script>
var DT = window.LaravelDataTables["dt-table"],
        DT_EDITOR = window.LaravelDataTables["dt-table-editor"];

DT.on('click', 'tbody td', function () {
    var cellIndex = DT.cell(this).index();
    if ($(DT.column(cellIndex.column).header()).hasClass('editable')) {
        DT_EDITOR.inline(cellIndex, {
            onBlur: 'submit',
            submit: 'allIfChanged'
        });
    }
});

DT.on('click', '.editor-delete', function (e) {
    DT_Helpers.remove(e, $(this), DT_EDITOR);
});

DT_EDITOR.field('group').input().on('change', function (e) {
    if (e.hasOwnProperty('originalEvent'))
        DT_EDITOR.submit();
});

$('#create').submit(function(e) {
    e.preventDefault();

    var form = $(this),
        color = form.find('[name="color"]').val(),
        title = form.find('[name="title"]').val(),
        group = form.find('[name="group"]').val();

DT_EDITOR
    .create(false)
    .set({
        color,
        title,
        group,
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
<style>
.colorSquare {
    width: 20px;
    height: 20px;
    margin:auto;
    float:left;
    border: 1px solid rgba(0, 0, 0, .2);
}
</style>
@endpush


@section('content')
<div class="subheader">
    <h1 class="subheader-title">
        Statuses
    </h1>
</div>
<div class="row">
    <div class="col-xl-12">
        <div id="panel-2" class="panel">
            <div class="panel-hdr">
                <h2>
                    New Status
                </h2>
            </div>
            <div class="panel-container show">
                <div class="panel-content">

                    <form id="create">
                        <div class="row">
                            <div class="col-lg-4">
                                <div class="form-group">
                                    <label class="form-label" for="color">Color</label>
                                    <input name="color" class="form-control" id="color" type="color" name="color" value="#727cf5">
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="form-group">
                                    <label class="form-label" for="group">Status Group</label>
                                    <select id="group" name="group" class="form-control" placeholder="Title">
                                        @foreach ($groups as $v)
                                        <option value="{{ $v['id'] }}">{{ $v['title'] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="form-group">
                                    <label class="form-label" for="title">Status Title</label>
                                    <div class="input-group">
                                        <input name="title" id="title" type="text" value="" class="form-control" placeholder="Title" required>
                                        <div class="input-group-append">
                                            <button class="btn btn-outline-primary waves-effect waves-themed" type="submit">Add</button>
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
                    Statuses <span class="fw-300"><i>for orders</i></span>
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
