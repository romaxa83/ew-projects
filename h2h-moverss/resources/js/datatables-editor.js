// DataTables Editor
require('imports-loader?define=>false,this=>window!datatables.net-editor')(window, $);
require('imports-loader?define=>false,this=>window!datatables.net-editor-bs4')(window, $);

$.fn.dataTable.ext.errMode = function (settings, helpPage, message) {
    console.log(message);
};

DT_Helpers = {
    /**
     * Удалить запись.
     * @param {type} e - Event
     * @param {type} el - Елемент
     * @param {type} editor
     * @returns {undefined}
     */
    remove: function (e, el, editor) {
        e.preventDefault();

        Swal.fire({
            title: 'Delete record',
            text: "Are you sure you wish to remove this record?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!',
            showLoaderOnConfirm: true,
            preConfirm: () => {
                var D = new $.Deferred;
                editor.remove(el.closest('tr'), false).submit(
                () => {
                    D.resolve({result: true});
                },
                (data) => {
                    Swal.showValidationMessage(data.error);
                    Swal.disableLoading();
                    D.reject();
                });

                return D.promise();
            },
            allowOutsideClick: () => !Swal.isLoading()
        }).then();
    }
};
