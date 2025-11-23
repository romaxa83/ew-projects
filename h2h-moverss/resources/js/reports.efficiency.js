var DT = {};

$.fn.dataTable.ext.errMode = function (settings, helpPage, message) {
    console.log(message);
};

$(function () {
    $("#managers").select2({
        placeholder: "Anyone",
    });
    $("#sources-select").select2({
        placeholder: "Anyone",
    });
    var dateRangeFrom = moment();
    // if ($("#start-range").val() == "")
    $("#start-range").val(moment().format("YYYY-MM-DD"));
    // else dateRangeFrom = moment($("#start-range").val());
    $("#end-range").val(moment().format("YYYY-MM-DD"));

    $("#filter-form").submit(function (e) {
        e.preventDefault();
        const formData = $("#filter-form").serializeFormObject();
        if ($.fn.dataTable.isDataTable($("#dt-table"))) {
            $("#dt-table").DataTable().destroy();
            $("#dt-table").empty();
        }
        let cols = [
            { data: "id", title: "ID", visible: false },
            { data: "title", title: "Metric", className: "metric-col" },
        ];
        if (formData.groupBy == "manager") {
            if (!$("#managers").val().length) {
                let data = "user_0";
                let title = "Without manager";
                cols.push({ data, title, className: "col-manager" });
            }
            $("#managers option").each(function () {
                let data = "user_" + $(this).prop("value");
                let title = $(this).text();
                if ($("#managers").val().length) {
                    if ($("#managers").val().includes($(this).prop("value")))
                        cols.push({ data, title, className: "col-manager" });
                    return true;
                }
                cols.push({ data, title, className: "col-manager" });
            });
        } else if (formData.groupBy == "source") {
            if (!$("#sources-select").val().length) {
                let data = "source_0";
                let title = "Without source";
                cols.push({ data, title, className: "col-manager" });
            }
            $("#sources-select option").each(function () {
                let data = "source_" + $(this).prop("value");
                let title = $(this).text();
                if ($("#sources-select").val().length) {
                    if (
                        $("#sources-select")
                            .val()
                            .includes($(this).prop("value"))
                    ) {
                        cols.push({ data, title, className: "col-manager" });
                    }
                    return true;
                }
                cols.push({ data, title, className: "col-manager" });
            });
        } else if (
            formData.groupBy == "day" ||
            formData.groupBy == "month" ||
            formData.groupBy == "year"
        ) {
            for (
                var dt = moment(formData["start-range"]);
                dt.isSameOrBefore(
                    moment(formData["end-range"]),
                    formData.groupBy
                );
                dt.add(1, formData.groupBy)
            ) {
                let data = "";
                let title = "";
                if (formData.groupBy == "day") {
                    data = dt.format("YYYY-MM-DD");
                    title = dt.format("MMM D, YYYY");
                } else if (formData.groupBy == "month") {
                    data = dt.format("YYYY-MM");
                    title = dt.format("MMM YYYY");
                } else if (formData.groupBy == "year") {
                    data = dt.format("YYYY");
                    title = dt.format("YYYY");
                }
                title = title.replace(/ /g, "&nbsp;");
                cols.push({ data, title });
            }
        } else {
            cols.push({ data: "total", title: "Total" });
        }
        // console.log(cols);
        initDatatable(cols);

        return false;
    });

    // $(".select2-select").each(function () {
    //     $(this).select2();
    // });

    $("#daterangepicker").daterangepicker(
        {
            minDate: moment("2020-01-01", "YYYY-MM-DD"),
            maxDate: moment(),
            startDate: dateRangeFrom,
            endDate: moment(),
            drops: "auto",
            locale: {
                format: "MMM DD, YYYY",
            },
            maxSpan: {
                days: 365,
            },
            alwaysShowCalendars: true,
            ranges: {
                Today: [moment(), moment()],
                Yesterday: [
                    moment().subtract(1, "days"),
                    moment().subtract(1, "days"),
                ],
                "Last 7 Days": [moment().subtract(6, "days"), moment()],
                "Last 30 Days": [moment().subtract(30, "days"), moment()],
                "This month": [moment().startOf("month"), moment()],
                "This year": [moment().startOf("year"), moment()],
            },
        },
        (start, end) => {
            $("#start-range").val(start.format("YYYY-MM-DD"));
            $("#end-range").val(end.format("YYYY-MM-DD"));
        }
    );
    //
    $("#show-cols").change(function () {
        analyzeCols($(this).val());
    });
});

const analyzeCols = (val) => {
    if ($.fn.DataTable.isDataTable("#dt-table")) {
        const api = $("#dt-table").DataTable();
        api.columns().every(function (index) {
            if (!index) {
                this.visible(false);
                return true;
            }
            var data = this.data();
            // console.log('index', index)
            // console.log('data', this.data())
            if (val == "all") {
                this.visible(true);
                return true;
            } else if ("hide-no-data") {
                // var hide = true;
                if (index > 1) {
                    var displayStatus = this.data()
                        .reduce(
                        function (acc, curr) {return acc + (curr == '' ? 0: 1)}, 0
                    );
                    this.visible(displayStatus);
                }
            }
        });
    }
};
const initDatatable = (columns) => {
    DT = $("#dt-table").DataTable({
        processing: true,
        searching: false,
        ordering: false,
        deferRender: true,
        orderClasses: false,
        columns,
        // serverSide: true,
        columnDefs: [],
        ajax: function (data, callback, settings) {
            return $.ajax({
                type: "POST",
                data: $.extend({}, data, {
                    filter: $("#filter-form").serializeFormObject(),
                }),
                url: $("#dt-table").data("route"),
            }).done(function (data) {
                callback(data);
                analyzeCols($("#show-cols").val());
                // drawPlot(data.plot)
            });
        },

        // ajax: {
        //     url: $('#dt-table').data('route'),
        //     method: 'POST',
        //     data: function (data, setting) {
        //         return $.extend({}, data, {filter: $('#filter-form').serializeFormObject()});
        //     }
        // },
        // scrollY: 400,
        scrollX: true,
        scrollCollapse: true,
        paging: false,
        //fixedColumns:   true,
        fixedColumns: {
            leftColumns: 2,
        },
        dom:
            "<'row'<'col-sm-12 col-md-6'><'col-sm-12 col-md-6'>>" +
            "<'row'<'col-sm-12'tr>>" +
            "<'row'<'col-sm-12 col-md-5'><'col-sm-12 col-md-7'>>",

        // lengthMenu: [25, 50, 100],
    });
};
