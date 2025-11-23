var DT = {};
var MatrixEditor = {};


const constructMatrixCols = (dt) => {
    let cols = [
        {
            title: 'Miles \\ Weight',
            data: 'miles_range',
            name: 'miles_range',
            orderable: false
        },
        {
            data: 'miles_range_from',
            visible: false,
            orderable: true
        }
    ];
    dt.rows().every(function (rowIdx, tableLoop, rowLoop) {
        const row = this.data();
        cols.push({
            id: row.id,
            orderable: false,
            title: row.from + ' - ' + row.to + ' lb',
            data: 'weights_range_' + row.id,
            render: function (data, type, row, meta) {
                if (type == 'display' && data) {
                    return data.coefficient
                }
                return data;
            },
            name: 'weights_range_' + row.id,
            // editField: 'weights_range_' + row.id + '.coefficient',
        })
    });
    return cols
}

const constructMatrixDatatable = () => {
    if ($.fn.DataTable.isDataTable('#dt-intrastate-coefficients-matrix')) {
        $('#dt-intrastate-coefficients-matrix').DataTable().destroy();
        $('#dt-intrastate-coefficients-matrix').empty();
    }
    // const cols = constructMatrixCols($('#dt-intrastate-weights').DataTable())
    // $('#dt-intrastate-coefficients-matrix thead').append('<tr><th data-data="miles_range" data-name="miles_range">Miles \\ Weight</th></tr>');
    // const tr = $('#dt-intrastate-coefficients-matrix thead tr');
    let editorFields = [];
    let datatableCols = constructMatrixCols($('#dt-intrastate-weights').DataTable());
    // console.log(datatableCols);


    // editorFields.push({
    //     label: 'coefficient',
    //     name: 'weights_range_' + col.id+'.coefficient',
    //     // data: 'weights_range_' + col.id+'.coefficient',
    // });


    for (const col of datatableCols) {
        if (col.id) {
            // tr.append(`<th data-data="weights_range_${col.id}.coefficient" data-name="weights_range_${col.id}">` + col.title + `</th>`);
            editorFields.push({
                label: 'coefficient',
                name: 'weights_range_' + col.id + '.coefficient',
                // data: 'weights_range_' + col.id+'.coefficient',
            });
        }
    }
    // console.log(editorFields);
    MatrixEditor = new $.fn.dataTable.Editor({
        ajax: $('#dt-intrastate-coefficients-matrix').data('route-editor'),
        table: "#dt-intrastate-coefficients-matrix",
        fields: editorFields
    });

    MatrixEditor.on('preSubmit', function (e, data, action) {
        const dt = $('#dt-intrastate-coefficients-matrix').DataTable().data().toArray();
        // const r = dt.filter()
        // console.log(dt.data().toArray());
        // console.log('data', data);
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

    $('#dt-intrastate-coefficients-matrix').on('click', 'tbody td:not(:first-child)', function (e) {
        // MatrixEditor.bubble( this );
        const d = $('#dt-intrastate-coefficients-matrix').DataTable().cell(this).data();
        // console.log('d', d);
        // console.log('weights_range_' + d.rate_weight_id + '.coefficient');
        MatrixEditor.inline(this, 'weights_range_' + d.rate_weight_id + '.coefficient', {
            onBlur: 'submit',
        });
    });

    $('#dt-intrastate-coefficients-matrix').DataTable({
        processing: true,
        ordering: true,
        // searching: false,
        paging: false,
        order: [[1, 'asc']],
        columns: datatableCols,
        dom: "<'row'<'col-sm-12 mb-2 col-md-6'>>" +
            "<'row'<'col-sm-12'tr>>",
        ajax: {
            type: "POST",
            url: $('#dt-intrastate-coefficients-matrix').data('route')
        },
        // ajax: function (data, callback) {
        //     return $.ajax({
        //         type: "POST",
        //         data,
        //         // data: $.extend({}, data, {filter: $('#filter-form').serializeFormObject()}),
        //         // data: $.extend({}, data, {filter: $('#filter-form').serializeFormObject()}),
        //         url: $('#dt-intrastate-miles').data('route')
        //     }).done(function (response) {
        //         callback(response);
        //     });
        // },
        // select: true,
        buttons: [
            {extend: "create", editor: MatrixEditor},
            {extend: "edit", editor: MatrixEditor},
            {extend: "remove", editor: MatrixEditor}
        ]
    });

}

$(function () {




    $('#filter-form').submit(function (e) {
        e.preventDefault();
        DT.ajax.reload();
        return false;
    });


    var milesEditor = new $.fn.dataTable.Editor({
        ajax: $('#dt-intrastate-miles').data('route-editor'),
        table: "#dt-intrastate-miles",
        display: "bootstrap",
        fields: [
            // {label: "Id:", name: "id"},
            {label: "From miles:", name: "from"},
            {label: "To miles:", name: "to"},
        ]
    });

    milesEditor.on('submitComplete', function (e) {
        constructMatrixDatatable()
    });


    $('#dt-intrastate-miles').DataTable({
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
        // ajax: {
        //     type: "POST",
        //     url: $('#dt-intrastate-miles').data('route')
        // },
        ajax: function (data, callback) {
            return $.ajax({
                type: "POST",
                data,
                // data: $.extend({}, data, {filter: $('#filter-form').serializeFormObject()}),
                // data: $.extend({}, data, {filter: $('#filter-form').serializeFormObject()}),
                url: $('#dt-intrastate-miles').data('route')
            }).done(function (response) {
                callback(response);
            });
        },
        select: true,
        buttons: [
            {extend: "create", editor: milesEditor, className: 'btn-sm btn-info waves-effect waves-themed', text: 'Add new range'},
            // { extend: "edit",   editor: milesEditor },
            // { extend: "remove", editor: milesEditor }
        ]
    });

    // Edit record
    $('#dt-intrastate-miles').on('click', '.editor-edit', function (e) {
        e.preventDefault();
        milesEditor.edit($(this).closest('tr'), {
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
    $('#dt-intrastate-miles').on('click', '.editor-delete', function (e) {
        e.preventDefault();

        milesEditor.remove($(this).closest('tr'), {
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


    var weightsEditor = new $.fn.dataTable.Editor({
        ajax: $('#dt-intrastate-weights').data('route-editor'),
        table: "#dt-intrastate-weights",
        display: "bootstrap",
        fields: [
            // {label: "Id:", name: "id"},
            {label: "From weight, lb:", name: "from"},
            {label: "To weight, lb:", name: "to"},
        ]
    });

    weightsEditor.on('submitComplete', function (e) {
        console.log('postSubmit weightsEditor')
        constructMatrixDatatable()
    });

    $('#dt-intrastate-weights').DataTable({
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
                url: $('#dt-intrastate-weights').data('route')
            }).done(function (response) {
                callback(response);
                constructMatrixDatatable();
                // $('#dt-intrastate-coefficients-matrix').DataTable().
                // constructMatrixCols($('#dt-intrastate-weights').DataTable());
            });
        },
        select: true,
        buttons: [
            {extend: "create", editor: weightsEditor, className: 'btn-sm btn-info waves-effect waves-themed', text: 'Add new range'},
            // { extend: "edit",   editor: milesEditor },
            // { extend: "remove", editor: milesEditor }
        ]
    });

    // Edit record
    $('#dt-intrastate-weights').on('click', '.editor-edit', function (e) {
        e.preventDefault();
        weightsEditor.edit($(this).closest('tr'), {
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
    $('#dt-intrastate-weights').on('click', '.editor-delete', function (e) {
        e.preventDefault();

        weightsEditor.remove($(this).closest('tr'), {
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

    var coefficientsEditor = new $.fn.dataTable.Editor({
        ajax: $('#dt-intrastate-coefficients').data('route-editor'),
        table: "#dt-intrastate-coefficients",
        display: "bootstrap",
        fields: [
            // {label: "Id:", name: "id"},
            // {label: "From weight:", name: "from"},
            {label: "Mile range:", name: "rate_distance_id", type: 'select', options: []},
            {label: "Weight range:", name: "rate_weight_id", type: 'select', options: []},
            {label: "Coefficient:", name: "coefficient"},
        ]
    });

    coefficientsEditor.on('open', function (e) {
        const milesApi = $('#dt-intrastate-miles').DataTable();
        this.field('rate_distance_id').update(milesApi.data().toArray().map(v => {
            return {value: v.id.toString(), label: v.from + ' - ' + v.to + ' mi'}
        }));
        const weightApi = $('#dt-intrastate-weights').DataTable();
        this.field('rate_weight_id').update(weightApi.data().toArray().map(v => {
            return {value: v.id.toString(), label: v.from + ' - ' + v.to + ' lb'}
        }));
    });


    // Edit record
    $('#dt-intrastate-coefficients').on('click', '.editor-edit', function (e) {
        e.preventDefault();
        coefficientsEditor.edit($(this).closest('tr'), {
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
    $('#dt-intrastate-coefficients').on('click', '.editor-delete', function (e) {
        e.preventDefault();

        coefficientsEditor.remove($(this).closest('tr'), {
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

    // const milesApi = $('#dt-intrastate-miles').DataTable();
    // console.log('data', milesApi.row(0).data());

    $('#dt-intrastate-coefficients').DataTable({
        processing: true,
        ordering: true,
        // searching: false,
        paging: false,
        order: [[1, 'asc']],
        columnDefs: [
            {
                targets: 'rate_distance_id',
                render: function (data, type, row, meta) {
                    if (type == 'display') {
                        if (row.distance_range) {
                            return row.distance_range.from + ' - ' + row.distance_range.to + ' mi';
                        }
                        return '???';
                    }
                    return data;
                }
            },
            {
                targets: 'rate_weight_id',
                render: function (data, type, row, meta) {
                    if (type == 'display') {
                        if (row.weight_range) {
                            return row.weight_range.from + ' - ' + row.weight_range.to + ' lb';
                        }
                        return '???';

                    }
                    return data;
                }
            },
            {
                targets: 'btns',
                "defaultContent": "<button type='button' class='mr-2 btn editor-edit btn-sm btn-outline-primary btn-icon waves-effect waves-themed'>" +
                    "<i class='fal fa-edit'></i></button>" +
                    "<button type='button' class='mr-2 btn editor-delete btn-sm btn-outline-danger btn-icon waves-effect waves-themed'><i class='fal fa-times'></i></button>"
            }
        ],
        dom: "<'row'<'col-sm-12 mb-2 col-md-6'B>>" +
            "<'row'<'col-sm-12'tr>>",
        ajax: {
            type: "POST",
            url: $('#dt-intrastate-coefficients').data('route')
        },
        select: true,
        buttons: [
            {extend: "create", editor: coefficientsEditor, className: 'btn-sm btn-info waves-effect waves-themed', text: 'Add new coefficient'},
            // { extend: "edit",   editor: milesEditor },
            // { extend: "remove", editor: milesEditor }
        ]
    });


    // $('#dt-intrastate-miles').on('click', 'tbody td:not(:first-child)', function (e) {
    //     editor.inline(this);
    // });


});
