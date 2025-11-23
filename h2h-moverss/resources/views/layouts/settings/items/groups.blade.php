@push('extendFooter')
{{$dtGroup->scripts()}}
<script>
$(function() {
    var DT = window.LaravelDataTables["dt-table-group"],
            DT_EDITOR = window.LaravelDataTables["dt-table-group-editor"];

    DT.on('click', 'tbody td', function (e) {
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
});
</script>
@endpush
<div id="panel-2" class="panel panel-collapsed">
    <div class="panel-hdr">
        <h2>
            Item Groups
        </h2>
        <div class="panel-toolbar">
            <button class="btn btn-panel" data-action="panel-collapse" data-toggle="tooltip" data-offset="0,10" data-original-title="Collapse"></button>
        </div>
    </div>
    <div class="panel-container collapse">
        <div class="panel-content">

            {{$dtGroup->table(['class' => 'table table-bordered table-hover table-striped w-100'])}}

        </div>
    </div>
</div>
