(function ($, DataTable) {

    if (!DataTable.ext.editorFields) {
        DataTable.ext.editorFields = {};
    }
    var Editor = DataTable.Editor;
    var _fieldTypes = DataTable.ext.editorFields;

    _fieldTypes.colorpicker = {
        // создание записи
        create: function (conf) {

            conf._enabled = true;

            // Create the elements to use for the input
            conf._input = $(
                    '<div id="' + Editor.safeId(conf.id) + '">' +
                    '<input type="text" class="basic" id="spectrum"/>' +
                    '<em id="basic-log"></em>' +
                    '</div>');

            // Use the fact that we are called in the Editor instance's scope to call
            $("input.basic", conf._input).spectrum({
//                color: "#f00",
                change: function (color) {
//                    $("#basic-log").text(color.toHexString());
                }
            });
            return conf._input;
        },

        get: function (conf) {
            var val = $("input.basic", conf._input).spectrum("get").toHexString();
            return val;
        },

        set: function (conf, val) {
            var that = this;
            $("input.basic", conf._input).spectrum({
                color: val,
                showInput: true,
                showInitial: true,
                preferredFormat: "hex",
                change: function (color) {
//                    $("#basic-log").text("change color to: " + color.toHexString());
                    that.submit();
                }
            });
        },

        enable: function (conf) {
            conf._enabled = true;
            $(conf._input).removeClass('disabled');
        },

        disable: function (conf) {
            conf._enabled = false;
            $(conf._input).addClass('disabled');
        }
    };
})(jQuery, jQuery.fn.dataTable);