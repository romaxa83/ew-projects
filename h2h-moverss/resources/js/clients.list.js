import formatPhone from "@/filters/formatPhone.filter";

$.fn.dataTable.Api.register('processing()', function (show) {
    return this.iterator('table', function (ctx) {
        ctx.oApi._fnProcessingDisplay(ctx, show);
    });
});

var DT = {};

$(function () {
    App.RecordsList.init();

    DT = $('#dt-list-form').DataTable({
        processing: true,
        serverSide: true,
        search: false,
        order: [[0, "desc"]],
        ajax: function (d, callback, settings) {
            let filters = $('#list-form').serializeFormObject();
            let filterForm = $('#filter-btn').tooltipster('content').find('form')[0];
            filters = $.extend({}, filters, $(filterForm).serializeFormObject({checkboxes: {unchecked: null}}));

            let data = $.extend({}, d, {filters: filters});
            return $.ajax({
                type: "POST",
                data,
                url: $('#dt-list-form').data('route')
            }).done(function (data) {
                callback(data);
            });
        },
        columnDefs: [
            {
                targets: 'client',
                render: (data, type, row, meta) => {
                    let txt = `<div class="fs-md mb-2">
                                <a href="#open_client:${row.id}" class="editClient" data-id="${row.id}">#${row.id} ${row.name} ${row.lname}</a>`;

                    if (row.deleted_at)
                        txt += '<span class="badge border border-danger text-danger ml-2">Removed</span>';

                    txt += '</div>';


                    txt += '<div class="d-flex flex-wrap">';

                    // Total orders
                    txt += `<a target="_blank" href="/orders?filter-client[]=${row.id}" class="btn btn-xs mb-1 btn-default waves-effect waves-themed mr-4">
                                 Orders <span class="badge bg-primary-500 ml-2">${row.orders_count}</span>
                             </a>`;

                    // tags
                    if (row.tags) {
                        for (let v of row.tags) {
                            const color = v.color ?? '#6c757d',
                                icon = v.icon ? 'fa-' + v.icon : 'fa-tag';

                            txt += `<button type="button" class="btn btn-xs mb-1 btn-secondary waves-effect waves-themed mr-1"
                                 style="background-color: ${color};border-color: ${color}">
                                     <i class="fas mr-1 ${icon}"></i>
                                     ${v.title}
                                </button>`;
                        }
                    }
                    txt += '</div>';

                    return txt;
                }
            },
            {
                targets: 'phones',
                render: (data, type, row, meta) => {
                    let txt = '',
                        total = row.phones.length;

                    if (total) {
                        for (let [i, v] of row.phones.entries()) {
                            txt += '<div class="text-dark fs-nano">';
                            txt += formatPhone(v.value);
                            if (i === 2) {
                                if (total > 3)
                                    txt += ' and ' + (total - (i + 1)) + ' more';
                                break;
                            }

                            txt += '</div>';
                        }
                    }

                    return txt;
                }
            },
            {
                targets: 'emails',
                render: (data, type, row, meta) => {
                    let txt = '',
                        total = row.emails.length;

                    if (total) {
                        for (let [i, v] of row.emails.entries()) {
                            txt += '<div class="text-dark fs-nano">';
                            txt += `<a href="mailto:${v.value}">${v.value}</a>`;
                            if (i === 2) {
                                if (total > 3)
                                    txt += ' and ' + (total - (i + 1)) + ' more';
                                break;
                            }

                            txt += '</div>';
                        }
                    }

                    return txt;
                }
            },
            {
                targets: 'manage',
                render: (data, type, row, meta) => {
                    return `<div class="m-2 text-center">
                                <button type="button" class="btn btn-sm btn-primary waves-effect waves-themed editor-delete mr-2 editClient" data-id="${row.id}">
                                    View
                                </button>
                            </div>`;
                }
            },
        ],
        dom:
        // "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
            "<'row'<'col-sm-12'tr>>" +
            "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
    });

    $(document).on('change', ".custom-control-filter-all", function () {
        let status = this.checked;
        $(this).closest('.list-group').find('input[type="checkbox"][name]').each(function () {
            $(this).prop('checked', status).trigger('change');
        });
    });


    let el = $('#filter-ids');
    el.select2({
        allowClear: true,
        ajax: {
            url() {
                return el.data('route')
            },
            delay: 400,
            method: 'POST',
            dataType: 'json',
            data(params) {
                return {
                    q: params.term, // search term
                    interface: 'clients',
                    page: params.page || 1
                };
            },
            processResults(response, params) {
                if (response.data)
                    return {
                        results: response.data.results,
                        pagination: response.data.pagination
                    };
                return response;
            },
            cache: true
        },
        escapeMarkup(markup) {
            return markup;
        },
        minimumInputLength: 0,
        templateResult(data) {
            if (!data.disabled && el.data('route').includes('client')) {
                return App.Miscs.formatClient(data);
            }
        },
        templateSelection: function (v) {
            if (el.data('route').includes('client') && v.name) {
                return v.name + ' ' + v.lname;
            }
            return v.text;
        }
    });


    $('.change-control').change(function () {
        $('#list-form').submit();
    });

    $('#list-form').submit(function (e) {
        e.preventDefault();

        App.RecordsList._applyFilters();
    });

});

App.RecordsList = {

    _applyFilters: () => {
        $('#dt-list-form').DataTable().ajax.reload();
    },

    _bindFilter: function () {
        const self = this;
        $('#filter-btn').tooltipster({
            updateAnimation: 'fade',
            plugins: ['sideTip', 'scrollableTip'],
            trigger: 'custom',
            triggerOpen: {
                click: true,
                tap: true
            },
            contentAsHTML: true,
            side: 'bottom',
            //position: 'bottom-left',
            interactive: true,
            theme: 'tooltipster-shadow',
            minWidth: 450,
            maxWidth: 450,
            //maxWidth: 700,
            content: $('#filterContent'),
            // if you use a single element as content for several tooltips, set this option to true
            contentCloning: false,
            functionReady: (instance, helper) => {
                $(helper.tooltip).find('button.apply-filter').one('click', function (e) {
                    $(helper.origin).find('.badge').removeClass('d-none');
                    instance.close();
                    self._applyFilters();
                });
                $(helper.tooltip).find('button.clear-filter').one('click', function (e) {
                    $(helper.origin).find('.badge').addClass('d-none');
                    instance.close();
                    $('#filters-form')[0].reset();
                    self._applyFilters();
                });
            }
        });
    },

    _bindClientEdit() {
        $(document).on('click', '.editClient', function (e) {
            e.preventDefault();

            window.VueApp.$refs.clientModal.editClient($(this).data('id'));
        });
    },

    whenSaved() {
        // Reload table when updated
        $('#dt-list-form').DataTable()
            .ajax.reload();
    },

    init: function () {
        this._bindFilter();
        this._bindClientEdit();
    }

};
