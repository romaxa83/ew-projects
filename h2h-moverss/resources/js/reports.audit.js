import ApexCharts from 'apexcharts'

function formatValues(d) {
    const oldValues = JSON.parse(d.old_values.replace(/&quot;/g, '"'));
    const newValues = JSON.parse(d.new_values.replace(/&quot;/g, '"'));
    return '<table class="table table-bordered">' + '<tr><td class="font-weight-bold">Old Value</td><td><pre>' + JSON.stringify(oldValues, null, 2) + '</pre></td></tr>' + '<tr><td class="font-weight-bold">New Value</td><td><pre>' + JSON.stringify(newValues, null, 2) + '</pre></td></tr>' + '</table>'
}

function drawPlotOld(plotData) {
    $.plot($('#plot-area'), plotData, {
        series: {
            // lines:
            // {
            //     show: true,
            //     lineWidth: 0,
            //     fill: 0.8
            // },
            lines: {
                show: false
            }, splines: {
                show: true, tension: 0.4, lineWidth: 0, fill: 0.8
            }, shadowSize: 0
        }, points: {
            show: false,
        }, legend: {
            noColumns: 1, position: 'nw'
        }, grid: {
            hoverable: true, clickable: true, borderColor: '#ddd', borderWidth: 0, labelMargin: 5, backgroundColor: '#fff'
        }, yaxis: {
            min: 0, max: 15, color: '#eee', font: {
                size: 10, color: '#999'
            }
        }, xaxis: {
            mode: "time", color: '#eee', font: {
                size: 10, color: '#999'
            }
        }
    });
}

var chart = null;

function drawPlot(series) {
    var options = {
        series,
        // series: [{
        //     name: "STOCK ABC",
        //     data: series.monthDataSeries1.prices
        // }],
        chart: {
            type: 'area',
            height: 300,
            zoom: {
                type: "x",
                enabled: true,
                autoScaleYaxis: false
            },
            toolbar: {
                show: true,
                tools: {
                    download: false,
                    selection: false,
                    zoom: true,
                    zoomin: true,
                    zoomout: true,
                    pan: false,
                    reset: false,
                    // customIcons: []
                }
            }
        },
        dataLabels: {
            enabled: false
        },
        stroke: {
            curve: 'smooth'
        },

        // title: {
        //     text: 'Fundamental Analysis of Stocks',
        //     align: 'left'
        // },
        // subtitle: {
        //     text: 'Price Movements',
        //     align: 'left'
        // },
        // labels: series.monthDataSeries1.dates,
        xaxis: {
            type: 'datetime',
        },
        yaxis: {
            title: {
                text: "Activity"
            },
            // opposite: true
        },
        tooltip: {
            x: {
                format: "MMM dd, yyyy hh:mm TT"
            }
        },
        legend: {
            position: 'top'
            // horizontalAlign: 'left'
        }
    };

    if (!chart) {
        chart = new ApexCharts(document.querySelector("#plot-area"), options);
        chart.render();

    } else
        chart.updateSeries(series);
}

var DT = {};

$(function () {
    var dateRangeFrom = moment();
    if ($('#start-range').val() == '') $('#start-range').val(moment().format('YYYY-MM-DD')); else dateRangeFrom = moment($('#start-range').val())
    $('#end-range').val(moment().format('YYYY-MM-DD'));

    $(document).on('click', '.order-href', function (e) {
        e.preventDefault();
        const text = $(this).text();
        const val = $(this).data('order');
        const newOption = new Option(text, val, false, false);
        $('#filter-order').val(null).trigger('change');
        if ($('#filter-order').find(`option[value='${val}']`).length == 0) $('#filter-order').append(newOption);
        $('#filter-order').val(val).trigger('change');
        $('#filter-form').submit();
        return false;
    })


    $('#filter-form').submit(function (e) {
        e.preventDefault();
        DT.ajax.reload();
        return false;
    });
    DT = $('#dt-table').DataTable({
        processing: true, serverSide: true, columnDefs: [{
            targets: 'order_id', render(data, type, row, meta) {
                if (type == 'display' && Number(data) > 0) {
                    return `<a href="#" class="order-href" data-order="${data}">Order #${data}</a>`
                }
                return data;
            },
        },

            {
                targets: 'details-control-th', className: 'dtr-control',
            }], ajax: function (data, callback, settings) {
            return $.ajax({
                type: "POST", data: $.extend({}, data, {filter: $('#filter-form').serializeFormObject()}), url: $('#dt-table').data('route')
            }).done(function (data) {
                callback(data);
                drawPlot(data.plot)
            });
        },


        // ajax: {
        //     url: $('#dt-table').data('route'),
        //     method: 'POST',
        //     data: function (data, setting) {
        //         return $.extend({}, data, {filter: $('#filter-form').serializeFormObject()});
        //     }
        // },
        "lengthMenu": [25, 50, 100]
    });

    $('#dt-table').on('click', 'td.dtr-control', function () {
        var tr = $(this).closest('tr');
        var row = DT.row(tr);

        if (row.child.isShown()) {
            // This row is already open - close it
            row.child.hide();
            tr.removeClass('shown');
        } else {
            // Open this row
            row.child(formatValues(row.data()), 'shown-child').show();
            tr.addClass('shown');
        }
    });

    $('.select2-select').each(function () {
        $(this).select2();
    });


    $('#daterangepicker').daterangepicker({
        minDate: moment('2020-01-01', 'YYYY-MM-DD'), maxDate: moment(), startDate: dateRangeFrom, endDate: moment(), drops: 'auto', locale: {
            format: 'MMM DD, YYYY'
        }, maxSpan: {
            days: 365
        }, alwaysShowCalendars: true, ranges: {
            'Today': [moment(), moment()],
            'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
            'Last 7 Days': [moment().subtract(6, 'days'), moment()],
            'Last 30 Days': [moment().subtract(30, 'days'), moment()],
        }
    }, (start, end) => {
        $('#start-range').val(start.format('YYYY-MM-DD'));
        $('#end-range').val(end.format('YYYY-MM-DD'));
    });
    //
    $('#filter-order').select2({
        allowClear: true, ajax: {
            url: '/orders/autocomplete', method: 'POST', dataType: 'json', data(params) {
                return {
                    q: params.term, // search term
                    interface: 'orders', page: params.page || 1
                };
            }, processResults(response, params) {
                if (response.data) return {
                    results: response.data.results, pagination: response.data.pagination
                };
                return response;
            }, cache: true
        }, escapeMarkup(markup) {
            return markup;
        }, minimumInputLength: 0, templateResult(data) {
            if (data.loading) return data.text;
            let orderId = `<div class="fs-md oid">Order: #${data.id} <span class="badge badge-warning">${data.division.title}</span></div>`;
            if (!data.client) {
                return `<div className="clearfix">${orderId}</div>`;
            }

            let client = $(App.Miscs.formatClient(data.client));

            $(client).find('.fs-md').parents().prepend(orderId);
            $(client).find('.fs-md:not(.oid)').removeClass('fs-md');

            return client;
        }, templateSelection: function (v) {
            if (v && v.id) return 'Order #' + v.id;
            return v.text;
        }
    });
    $('#filter-client').select2({
        allowClear: true, ajax: {
            url: '/client/profile/autocomplete', method: 'POST', dataType: 'json', data(params) {
                return {
                    q: params.term, // search term
                    interface: 'orders', page: params.page || 1
                };
            }, processResults(response, params) {
                if (response.data) return {
                    results: response.data.results, pagination: response.data.pagination
                };
                return response;
            }, cache: true
        }, escapeMarkup(markup) {
            return markup;
        }, minimumInputLength: 0, templateResult: App.Miscs.formatClient, templateSelection: function (v) {
            if (v.name) return v.name + ' ' + v.lname;
        }, // templateSelection: function (v) {
        //     if (v && v.id)
        //         return 'Order #' + v.id;
        //     return v.text;
        // }
    });

});
