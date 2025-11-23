var DT = {};

const constructMatrixEditorFields = (dt) => {
    const fields = [];
    dt.columns().every(function () {
        const node = $(this.header());
        if (node.data('name')) {
            fields.push({
                label: 'price',
                name: node.data('data') + '.price',
                // data: function ( data, type, set ) {
                //     console.log('fdata', data);
                //     return ''
                //     // if ( type === 'set' ) {
                //     //     data.name = set;
                //     // }
                //     // return data.name.split(' ')[0];
                // },
                //data: node.data('data')+'.price',
                attr: {
                    class: "state-coefficients-input input-sm"
                }
            });
        }
    });
    return fields
}

const initMatrix = () => {
    $('#matrix-panel').removeClass('d-none');
    DT = $('#dt-interstate-coefficients-matrix').DataTable({
        processing: true,
        ordering: false,
        // searching: false,
        paging: false,
        order: [[1, 'asc']],
        dom: "<'row'<'col-sm-12 mb-2 col-md-6'>>" +
            "<'row'<'col-sm-12'tr>>",
        ajax: {
            type: "POST",
            url: $('#dt-interstate-coefficients-matrix').data('route'),
            data: function () {
                return {
                    filter: {
                        range: $('#ranges-selector').val()
                    }
                }
            }
        },
        scrollX: true,
        scrollY: '450px',
        fixedColumns: {
            left: 1,
        },
        columnDefs: [
            {
                targets: 'state-column',
                // editField: 'price',
                render: function (data, type, row, meta) {
                    if (type == 'display')
                        return data.price;
                    return data;
                },
                createdCell: function (td, cellData) {
                    if (cellData.from_state == cellData.to_state) {
                        $(td).addClass('bg-danger-50')
                    }
                },
            }
        ],
        buttons: []
    });

    var MatrixEditor = new $.fn.dataTable.Editor({
        ajax: $('#dt-interstate-coefficients-matrix').data('route-editor'),
        table: "#dt-interstate-coefficients-matrix",
        fields: constructMatrixEditorFields($('#dt-interstate-coefficients-matrix').DataTable())
    });


    MatrixEditor.on('preSubmit', function (e, data, action) {
        const dt = $('#dt-interstate-coefficients-matrix').DataTable().data().toArray();
        const newData = {}
        $.each(data.data, function (key, changed) {
            // console.log('changed', changed);
            const row = dt.find(r => r.DT_RowId == key)
            if (row) {
                $.each(changed, function (k, v) {
                    if (row[k]) {
                        newData[row[k].id] = $.extend({}, {...row[k]}, v);
                        delete newData[row[k].id].id
                        if (!row[k].id)
                            data.action = 'create'
                    }
                })
            }
            // data.data[ key ][ 'numberField' ] = parseInt( values[ 'numberField' ], 10 );
        });
        data.data = newData
    });

    $('#dt-interstate-coefficients-matrix').on('click', 'tbody td:not(:first-child)', function (e) {
        const dt = $('#dt-interstate-coefficients-matrix').DataTable();
        // const rowData = dt.row($(this).closest('tr')).data()
        // console.log(rowData);
        const cellXY = dt.cell(this).index();
        const dataSrc = dt.column(cellXY.column).dataSrc();
        // console.log(dataSrc+'.price');
        const cellData = dt.cell(this).data();
        if (cellData.from_state == cellData.to_state) {
            return
        }

        // var cellXY = DT.cell(this).index();

        MatrixEditor.inline(dt.cell(this).index(), dataSrc + '.price', {
            onBlur: 'submit',
        });
    });
}

const fillRangesSelect = () => {
    $('#ranges-selector').empty();
    $('#ranges-selector').off('change');
    $('#dt-interstate-ranges').DataTable().rows().every(function () {
        const row = this.data();
        const text = 'Volume: from ' + row.cbft_from + ' CuFt to ' + row.cbft_to + ' CuFt';
        const O = new Option(text, row.id, null, null);
        $('#ranges-selector').append(O)
    });
    $('#ranges-selector').change(function () {
        DT.ajax.reload();
    });
}
$(function () {

    var rangesEditor = new $.fn.dataTable.Editor({
        ajax: $('#dt-interstate-ranges').data('route-editor'),
        table: "#dt-interstate-ranges",
        display: "bootstrap",
        fields: [
            // {label: "Id:", name: "id"},
            {label: "From volume, cuft:", name: "cbft_from"},
            {label: "To volume, cuft:", name: "cbft_to"},
        ]
    });

    rangesEditor.on('submitComplete', function (e) {
        fillRangesSelect()
        DT.ajax.reload();
    });

    // Edit record
    $('#dt-interstate-ranges').on('click', '.editor-edit', function (e) {
        e.preventDefault();
        rangesEditor.edit($(this).closest('tr'), {
            title: 'Edit record',
            buttons: {
                className: "btn btn-sm btn-primary",
                text: 'Update',
                action: function () {
                    this.submit();
                }
            }
        });
    });

    // Delete a record
    $('#dt-interstate-ranges').on('click', '.editor-delete', function (e) {
        e.preventDefault();
        rangesEditor.remove($(this).closest('tr'), {
            title: 'Delete record',
            message: 'Are you sure you wish to remove this record?',
            buttons: {
                className: "btn btn-sm btn-danger",
                text: 'Delete',
                action: function () {
                    this.submit();
                }

            }
        });
    });

    var shuttleRangesEditor = new $.fn.dataTable.Editor({
        ajax: $('#dt-interstate-shuttle').data('route-editor'),
        table: "#dt-interstate-shuttle",
        display: "bootstrap",
        fields: [
            // {label: "Id:", name: "id"},
            {label: "From volume, cuft:", name: "min"},
            {label: "To volume, cuft:", name: "max"},
            {label: "Rate, $:", name: "price"},
        ]
    });

    $('#dt-interstate-shuttle').on('click', '.editor-edit', function (e) {
        e.preventDefault();
        shuttleRangesEditor.edit($(this).closest('tr'), {
            title: 'Edit record',
            buttons: {
                className: "btn btn-sm btn-primary",
                text: 'Update',
                action: function () {
                    this.submit();
                }
            }
        });
    });

    // Delete a record
    $('#dt-interstate-shuttle').on('click', '.editor-delete', function (e) {
        e.preventDefault();
        shuttleRangesEditor.remove($(this).closest('tr'), {
            title: 'Delete record',
            message: 'Are you sure you wish to remove this record?',
            buttons: {
                className: "btn btn-sm btn-danger",
                text: 'Delete',
                action: function () {
                    this.submit();
                }

            }
        });
    });


    $('#dt-interstate-ranges').DataTable({
        processing: true,
        ordering: true,
        // searching: false,
        paging: false,
        order: [[1, 'asc']],
        columnDefs: [
            {
                targets: 'btns',
                "defaultContent": "<button type='button' class='mr-2 btn editor-edit btn-sm btn-outline-primary btn-icon waves-effect waves-themed'>" +
                    "<i class='fal fa-edit'></i></button>" +
                    "<button type='button' class='mr-2 btn editor-delete btn-sm btn-outline-danger btn-icon waves-effect waves-themed'><i class='fal fa-times'></i></button>"
            }
        ],
        dom: "<'row'<'col-sm-12 mb-2 col-md-6'B>>" +
            "<'row'<'col-sm-12'tr>>",
        ajax: function (data, callback) {
            return $.ajax({
                type: "POST",
                data,
                // data: $.extend({}, data, {filter: $('#filter-form').serializeFormObject()}),
                // data: $.extend({}, data, {filter: $('#filter-form').serializeFormObject()}),
                url: $('#dt-interstate-ranges').data('route')
            }).done(function (response) {
                callback(response);
                fillRangesSelect();
                initMatrix();
            });
        },
        select: true,
        buttons: [
            {extend: "create", editor: rangesEditor, className: 'btn-sm btn-info waves-effect waves-themed', text: 'Add new range'},
            // { extend: "edit",   editor: milesEditor },
            // { extend: "remove", editor: milesEditor }
        ]
    });

    $('#dt-interstate-shuttle').DataTable({
        processing: true,
        ordering: true,
        // searching: false,
        paging: false,
        order: [[1, 'asc']],
        columnDefs: [
            {
                targets: 'btns',
                "defaultContent": "<button type='button' class='mr-2 btn editor-edit btn-sm btn-outline-primary btn-icon waves-effect waves-themed'>" +
                    "<i class='fal fa-edit'></i></button>" +
                    "<button type='button' class='mr-2 btn editor-delete btn-sm btn-outline-danger btn-icon waves-effect waves-themed'><i class='fal fa-times'></i></button>"
            }
        ],
        dom: "<'row'<'col-sm-12 mb-2 col-md-6'B>>" +
            "<'row'<'col-sm-12'tr>>",
        ajax: function (data, callback) {
            return $.ajax({
                type: "POST",
                data,
                // data: $.extend({}, data, {filter: $('#filter-form').serializeFormObject()}),
                // data: $.extend({}, data, {filter: $('#filter-form').serializeFormObject()}),
                url: $('#dt-interstate-shuttle').data('route')
            }).done(function (response) {
                callback(response);
                // fillRangesSelect();
                // initMatrix();
            });
        },
        select: true,
        buttons: [
            {extend: "create", editor: shuttleRangesEditor, className: 'btn-sm btn-info waves-effect waves-themed', text: 'Add new range'},
            // { extend: "edit",   editor: milesEditor },
            // { extend: "remove", editor: milesEditor }
        ]
    });

});
