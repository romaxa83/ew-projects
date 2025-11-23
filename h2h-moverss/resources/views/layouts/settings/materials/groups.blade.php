@push('extendFooter')
{{$dtGroup->scripts()}}
<script>
    $(function () {
        var DT_GROUPS = window.LaravelDataTables["dt-table-group"],
            DT_ITEMS = window.LaravelDataTables["dt-table-items"],
            DT_ITEMS_E = window.LaravelDataTables["dt-table-items-editor"],
            DT_G_EDITOR = window.LaravelDataTables["dt-table-group-editor"];

        function reInitOptions() {
            let options = Object.values(DT_GROUPS.data())
                .slice()
                .map(item => {
                    return {
                        value: item.id,
                        label: item.title,
                    }
                })
                .filter(item => item.label)

            DT_ITEMS_E.field('group').update(options)
            DT_ITEMS.ajax.reload();
        }

        DT_GROUPS.on('click', 'tbody td', function (e) {
            var cellIndex = DT_GROUPS.cell(this).index();
            if ($(DT_GROUPS.column(cellIndex.column).header()).hasClass('editable')) {
                DT_G_EDITOR.inline(cellIndex, {
                    onBlur: 'submit',
                    submit: 'allIfChanged'
                });
            }
        });

        DT_G_EDITOR.on('create', function (e, json, data) {
            reInitOptions()
        });

        DT_G_EDITOR.on('remove', function (e, json, data) {
            reInitOptions()
        });

        DT_GROUPS.on('change', function (e, json) {
            if (!json) {
                console.log('update options');
                reInitOptions()
            }
        });

        DT_GROUPS.on('click', '.editor-delete', function (e) {
            DT_Helpers.remove(e, $(this), DT_G_EDITOR);
            reInitOptions()
        });
    });
</script>
@endpush

<div id="panel-2" class="panel">
    <div class="panel-hdr">
        <h2>
            Item Groups
        </h2>
    </div>
    <div class="panel-container show">
        <div class="panel-content">

            {{$dtGroup->table(['class' => 'table table-bordered table-hover table-striped w-100'])}}

        </div>
    </div>
</div>
