@push('extendFooter')
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/searchpanes/1.0.1/css/searchPanes.bootstrap4.min.css"/>
    <style>.sorting { cursor: grab}</style>
@endpush

@push('extendFooter')
<!-- DT SearchPane -->
<script type="text/javascript" src="https://cdn.datatables.net/searchpanes/1.0.1/js/dataTables.searchPanes.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/searchpanes/1.0.1/js/searchPanes.bootstrap4.min.js"></script>
{{$dtItems->scripts()}}
<script>
$(function() {
    var DT = window.LaravelDataTables["dt-table-items"],
            DT_EDITOR = window.LaravelDataTables["dt-table-items-editor"];

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

    DT_EDITOR.field('group').input().on('change', function (e) {
        if ($(e.currentTarget).closest('tr').length && e.hasOwnProperty('originalEvent')) {
            DT_EDITOR.submit();
        }
    });

    // Sorting
    DT.on('row-reorder', function (e, diff, edit) {
        let myArray = [];
        for (let i = 0, ien = diff.length; i < ien; i++) {
            let rowData = DT.row(diff[i].node).data();
            myArray.push({
                id: rowData.id,			// record id from datatable
                position: diff[i].newData		// new position
            });
        }
        let jsonString = JSON.stringify(myArray);
        $.ajax({
            url: '{{ route('settings.materials.records.records') }}',
            type: 'POST',
            data: jsonString,
            dataType: 'json',
            success: function (json) {
                DT.ajax.reload();
            }
        });
    });
});
</script>
@endpush
<div id="panel-1" class="panel">
    <div class="panel-hdr">
        <h2>
            Items
        </h2>
        <div class="panel-toolbar">
{{--            @include('layouts.app.helpers.table_style', ['id' => 'dt-table-items'])--}}
        </div>
    </div>
    <div class="panel-container show">
        <div class="panel-content">

            {{$dtItems->table(['class' => 'table table-bordered table-hover table-striped w-100'])}}

        </div>
    </div>
</div>
