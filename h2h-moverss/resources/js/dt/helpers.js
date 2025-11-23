$.fn.dataTable.render.colorRender = function () {
    return function (data, type) {
        if (type === 'display') {
            return '<div class="colorSquare" style="background:' + data + ';"></div>';
        }
        return data;
    }
}
$.fn.dataTable.render.yesNoRender = function () {
    return function (data, type) {
        if (type === 'display')
            return data ? '<span class="text-success">Yes</span>' : '<span class="text-danger">No</span>';
        return data;
    }
}
