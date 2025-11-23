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
var DT = window.LaravelDataTables["dt-table"],
        DT_EDITOR = window.LaravelDataTables["dt-table-editor"];

DT.on('click', 'tbody td', function (e) {
    var cellIndex = DT.cell(this).index();
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

$('#create').submit(function(e) {
    e.preventDefault();

    var form = $(this),
        title = form.find('[name="title"]').val();

DT_EDITOR
    .create( false )
    .set({
        title: title
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
        Statuses Groups
    </h1>
</div>
<div class="row">
    <div class="col-xl-12">
        <div id="panel-2" class="panel">
            <div class="panel-hdr">
                <h2>
                    New Group
                </h2>
            </div>
            <div class="panel-container show">
                <div class="panel-content">

                    <form id="create">
                        <div class="row">
                            <div class="col-lg-6">
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
                    Statuses Groups
                </h2>
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
