import Debounce from "lodash.debounce";

require('imports-loader?define=>false,this=>window!./vendor/bootstrap-typeahead/bootstrap3-typeahead.js')(window, $)

$.fn.dataTable.Api.register('processing()', function (show) {
    return this.iterator('table', function (ctx) {
        ctx.oApi._fnProcessingDisplay(ctx, show);
    });
});
let DT = {};

import Vue from 'vue';
import CommunicationsRecord from './components/Order/TabOverview/Communications/Record'

const changeOrderStatus = (orderID, currentStatusID, nextID, dtRow) => {

    return new Promise((resolve, reject) => {
        axios
            .post(`/orders/${orderID}/order/set-status`, {
                order_id: orderID,
                old_status: currentStatusID,
                status_id: nextID,
                is_roll_back: 0
            }).then(resp => {
            if (resp.data.success !== true) {
                reject(resp.data)
            }

            // обновляем дататэйблс
            axios
                .post($('#DT-Order-List').data('route') + '/' + orderID)
                .then(response => {
                    if (response.data.success !== true) {
                        reject(response.data)
                    }
                    // if (response.data.success === true) {
                    dtRow.data(response.data.data[0]).draw();
                    resolve();
                    // console.log(response.data.data[0]);
                    // }
                }).catch(error => {
                reject(error.response.data)
            })

        }).catch(error => {
            reject(error.response.data)
        })
    });
}

const mountTooltipster = (element) => {
    // console.log(element)
    const rowData = DT.row($(element).closest('tr')).data();
    // console.log(rowData);
    $(element).tooltipster({
        updateAnimation: 'fade',
        plugins: ['sideTip', 'scrollableTip'],
        trigger: 'hover',
        /*
                triggerOpen: {
                    click: true,
                    tap: true
                },
        */
        content: 'Loading...',
        functionBefore: function (instance, helper) {
            var $origin = $(helper.origin);
            if ($origin.data('loaded') !== true) {
                $.ajax({
                    url: '/orders/activity',
                    type: "POST",
                    data: {
                        order_id: rowData.details.id,
                        type: 'email',
                    }
                }).done(function (response) {
                    if (response.activities.length > 0) {
                        response.activities.forEach((item, i) => {
                            item.section = 'messages';
                            response.activities[i] = item;
                        })
                    }
                    // const el = $(instance._$tooltip[0]).find('.tooltipster-content')[0]
                    const el = document.createElement('div')
                    let html;
                    const vm = new Vue({
                        template: `
                            <div class="panel-container">
                                <div class="panel-content pt-0">
                                    <ul class="activity-timeline"
                                        :class="{ 'mb-0': section === 'short', 'mt-2': section === 'short' }">
                                        <communications-record v-for="v in validRecords"
                                                               :key="v.section+v.id"
                                                               :v="v"
                                                               :section="section"
                                                               :now="now"
                                        ></communications-record>
                                    </ul>
                                </div>
                            </div>`,
                        data() {
                            return {
                                validRecords: response.activities,
                                section: 'tab',
                                func: null,
                                page: 1,
                                // per_page: 10,
                                now: moment(),
                            }
                        },
                        components: {
                            CommunicationsRecord
                        }
                    });
                    vm.$mount(el)
                    vm.$nextTick(() => {
                        html = vm.$el.innerHTML
                        console.log(html)
                        instance.content(html);
                        $origin.data('loaded', true);
                    });
                })
            }
        },
        contentAsHTML: true,
        // contentCloning: true,
        side: 'right',
        //position: 'bottom-left',
        interactive: true,
        theme: 'tooltipster-shadow',
        minWidth: 400,
        // maxWidth: 450
    });

}

$(function () {

    $(document).on('click', '.change-order-status', function (e) {
        $('#content-spinner').toggleClass('d-none');
        const dtRow = DT.row($(this.closest('tr')));
        const details = dtRow.data().details;
        // console.log(DT.row($(this.closest('tr'))).data().details)
        changeOrderStatus(details.id, details.status.id, $(this).data('next-id'), dtRow)
            .then(() => {
                //turnoff spinner
                $('#content-spinner').toggleClass('d-none');
            })
            .catch(error => {
                $('#content-spinner').toggleClass('d-none');
                App.Forms.simpleErrors(error);
            })
        return false;
    });


    $(document).on('change', ".custom-control-filter-all", function () {
        let status = this.checked;
        $(this).closest('.list-group').find('input[type="checkbox"][name]').each(function () {
            $(this).prop('checked', status).trigger('change');
        });
    });


    $('#filter-client, #filter-order').each(function () {
        let el = $(this);
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
                        division_id: App.Miscs.getCurrentDivision(),
                        interface: 'orders',
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
                } else if (!data.disabled && el.data('route').includes('orders')) {
                    let orderId = `<div class="fs-md oid">Order: #${data.id} <span class="badge badge-warning">${data.division.title}</span></div>`;
                    if (!data.client) {
                        return `<div className="clearfix">${orderId}</div>`;
                    }

                    let client = $(App.Miscs.formatClient(data.client));

                    $(client).find('.fs-md').parents().prepend(orderId);
                    $(client).find('.fs-md:not(.oid)').removeClass('fs-md');

                    return client;
                }
            },
            templateSelection: function (v) {
                if (el.data('route').includes('orders') && v.id)
                    return 'Order #' + v.id;
                else if (el.data('route').includes('client') && v.name) {
                    return v.name + ' ' + v.lname;
                }
                return v.text;
            }
        });
    })


    $('#myLeads.check-control').change(function () {
        if ($(this).prop('checked') && $('#newLeads').prop('checked')) {
            // $('#newLeads').removeClass('check-control').prop('checked', false).addClass('check-control');
            $('#newLeads').removeClass('check-control').trigger('click').addClass('check-control');
        }
        $('#order-list-form').submit();
    });

    $('#newLeads.check-control').change(function () {
        if ($(this).prop('checked') && $('#myLeads').prop('checked')) {
            // $('#myLeads').removeClass('check-control').prop('checked', false).addClass('check-control');
            $('#myLeads').removeClass('check-control').trigger('click').addClass('check-control');
        }
        $('#order-list-form').submit();
    });


    $('.change-control').change(function () {
        $('#order-list-form').submit();
    });

    $('#order-list-form').submit(function (e) {
        e.preventDefault();

        App.OrderList._applyFilters();
        return false;
    });

    let createForm = $('#modal-order-create');

    // Сброс клиента
    $('[name="reset"]', createForm).click(function () {
        $('input[name^=client]', createForm).each(function () {
            $(this)
                .val('')
                .prop('readonly', false);
        });
        $('input[name="client[id]"]', createForm).val(0);
        $(this).addClass('d-none');
    });

    $('[name="daterange-type"]').change(function () {
        if ($(this).val() == 'by-none') {
            $('#daterangepicker').prop('disabled', true)
            App.OrderList._applyFilters();
        } else
            $('#daterangepicker').prop('disabled', false)

    })

});

App.OrderList = {

    initDT() {

        // data, callback, settings
        // $('[data-toggle="tooltip"]').tooltip()
        DT = $('#DT-Order-List').DataTable({
            processing: true,
            serverSide: true,
            search: false,
            order: [[0, "desc"]],
            ajax: function (d, callback, settings) {
                let filters = $('#order-list-form').serializeFormObject();
                let filterForm = $('#filter-btn').tooltipster('content').find('form')[0];
                filters = $.extend({}, filters, $(filterForm).serializeFormObject({checkboxes: {unchecked: null}}));

                window.VueApp.$refs.activeFilters.passData(filters);
                // filters = $(filterForm).serializeFormObject({checkboxes: {unchecked: null}});
                // return
                let data = $.extend({}, d, {filters: filters});
                return $.ajax({
                    type: "POST",
                    data,
                    url: $('#DT-Order-List').data('route')
                }).done(function (data) {
                    callback(data);
                    $('.orders-sent-emails').each(function () {
                        mountTooltipster($(this)[0])
                    });
                    $('[data-toggle="tooltip"]').tooltip()
                });
            },
            // ajax: {
            //     url: $('#DT-Order-List').data('route'),
            //     type: "POST",
            //     data: function (d) {
            //         let filters = $('#order-list-form').serializeFormObject();
            //         let filterForm = $('#filter-btn').tooltipster('content').find('form')[0];
            //         filters = $.extend({}, filters, $(filterForm).serializeFormObject({checkboxes: {unchecked: null}}));
            //         // filters = $(filterForm).serializeFormObject({checkboxes: {unchecked: null}});
            //         return $.extend({}, d, {filters: filters});
            //     }
            // },
            columnDefs: [
                {
                    targets: 'orderDetails',
                    render: (data, type, row, meta) => {
                        let html = `
                            <div class="mb-1 tooltipOrderInfo d-flex">
                                <div>
                                    <a href="${data.url}"># ${data.id}</a>
                                </div>
                                <h6 class="ml-2">
                                    <span class="badge badge-info">${data.division.short}</span>
                                </h6>
                                <div>
                                    <span class="text-muted fs-xs ml-2">${data.estimate}</span>
                                </div>

                            </div>
                            <button class="btn btn-xs btn-secondary dropdown-toggle"
                                    type="button" data-toggle="dropdown"
                                    aria-haspopup="true" aria-expanded="false"
                                    style="border-color: ${data.status.color}; background-color: ${data.status.color};">
                                <i class="ni ni-energy mr-1"></i> ${data.status.title}
                            </button>

                        <div class="dropdown-menu">`;
                        for (const i of Object.keys(data.status_routes)) {
                            const next = data.status_routes[i];
                            html += `
                                    <a class="dropdown-item change-order-status" data-next-id="${next.id}" href="#">
                                        <i class="fas fa-circle pr-2" style="color: ${next.color}"></i>
                                        ${next.title}
                                    </a>
                                `;
                        }
                        html += `</div>

                <div class="mt-2 text-dark fs-nano">${data.created_at}</div>`;

                        if (data.tasks_count) {
                            html += `<div class="flex-fill text-right">
                                        <a href="javascript:void(0);"
                                           class="btn btn-sm btn-secondary btn-icon rounded-circle position-relative js-waves-off ml-2">
                                            <i class="fas fa-alarm-clock"></i>
                                            <span class="badge border border-light rounded-pill bg-warning-700 position-absolute pos-phone">${data.tasks_count}</span>
                                        </a>
                                    </div>`;
                        }

                        if (data.order_tags.length) {
                            html += '<div class="d-flex flex-wrap mt-2">';

                            data.order_tags
                                .forEach(item => {
                                    let color = item.color ?? '#6c757d',
                                        icon = item.icon ? 'fa-' + item.icon : 'fa-tag';

                                    html += `<button type="button" class="btn btn-xs mb-1 btn-secondary waves-effect waves-themed mr-1"
                                                style="background-color: ${color};border-color: ${color}">
                                            <i class="fas mr-1 ${icon}"></i>
                                            ${item.title}
                                         </button>`;
                                })

                            html += '</div>';
                        }

                        return html;
                    }
                },
                {className: 'column-text-overflow', targets: 'client-column'}
            ],
            dom:
            // "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
                "<'row'<'col-sm-12'tr>>" +
                "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
        });
    },

    _applyFilters: () => {
        $('#DT-Order-List').DataTable().ajax.reload();
        // // $('#content-spinner').removeClass('d-none');
        // console.log('MainForm');
        // console.log($('#order-list-form').serializeFormObject());
        // console.log('Filters Button');
        // let filterForm = $('#filter-btn').tooltipster('content').find('form')[0];
        // console.log($(filterForm).serializeFormObject({checkboxes: {unchecked: null}}));

        // window.location.href= $('#order-list-form').attr('action') + '?' +$('#order-list-form').serialize() + '&' + $(filterForm).serialize();

    },

    _bindFilter: function () {
        const self = this;
        $('#filter-btn').tooltipster({
            // updateAnimation: 'fade',
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
                $(helper.tooltip).find('select.filter-sources').select2({
                    allowClear: true,
                    debug: true,
                    dropdownParent: '#filterContent',
                });
                $(helper.tooltip).find('select.filter-managers').select2({
                    allowClear: true,
                    debug: true,
                    dropdownParent: '#filterContent',
                });
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

                // $(helper.tooltip).find("input.range_1").ionRangeSlider(
                //     {
                //         skin: "round",
                //         type: "double",
                //         grid: true,
                //         min: 0,
                //         max: 10000,
                //         from: 50,
                //         to: 200,
                //         prefix: "$"
                //     });
            },
            // content: 'Loading...',
            // functionBefore: (instance, helper) => {
            //     let $origin = $(helper.origin);
            //     // we set a variable so the data is only loaded once via Ajax, not every time the tooltip opens
            //     if ($origin.data('loaded') !== true) {
            //         let html = $('#filterContent')[0];
            //
            //         App.Miscs.select2dropdown($('#filterContent .select2-dropdown-popover'), {
            //             dropdownParent: $('#filterContent')
            //         });
            //
            //         instance.content(html);
            //         $origin.data('loaded', true);
            //     }
            // }
        });
        $('#filter-btn').click(function () {
            let status = $(this).tooltipster('status');
            if (status.state == 'stable' && status.open === true)
                $(this).tooltipster('close');
        });
    },

    _bindCreateOrderFromModal() {
        Inputmask({"mask": "(999) 999-9999"}).mask('#modal-phone');
        Inputmask("email").mask('#modal-email');
        // Inputmask({
        //     mask: "*{1,20}[.*{1,20}][.*{1,20}][.*{1,20}]@*{1,20}[.*{2,6}][.*{1,2}]",
        //     greedy: false,
        //     onBeforePaste: function (pastedValue, opts) {
        //         pastedValue = pastedValue.toLowerCase();
        //         return pastedValue.replace("mailto:", "");
        //     },
        //     definitions: {
        //         '*': {
        //             validator: "[0-9A-Za-z!#$%&'*+/=?^_`{|}~\-]",
        //             casing: "lower"
        //         }
        //     }
        // }).mask('#modal-email');
        $('#modal-reset-selected').click(function (e) {
            App.OrderList.resetClientFields($('#modal-reset-selected').closest('form')[0])
        })

        App.OrderList.clientAutocomplete();
        App.OrderList.zipAutocomplete();
        $('#modal-order-create').on('shown.bs.modal', function (e) {
            const modal = this;
            const today = new Date();
            let tomorrow = new Date()
            tomorrow.setDate(today.getDate() + 1)
            flatpickr('#modal-work-date', {
                enableTime: false,
                dateFormat: "Y-m-d",
                altInput: true,
                altFormat: "F j, Y",
                minDate: tomorrow,
            });
            $(this).find('#modal-work-type').select2({
                dropdownParent: modal
            });
            $(this).find('#modal-source').select2({
                dropdownParent: modal
            });

            $('button[name="create"]', modal).click(async function () {
                if (!$('[name="source"]', modal).val()) {
                    App.Forms.showAlert('error', 'Source is not selected');
                    return;
                }

                $('#content-spinner').toggleClass('d-none');
                let submitBtn = $('[name="create"]', modal);

                const order = {
                    branch: App.Miscs.getCurrentDivision(),
                    'move-type': $('[name="move-type"]', modal).val(),
                    move_size_id: $('[name="move_size_id"]', modal).val(),
                    source: $('[name="source"]', modal).val(),
                    work: {
                        date: $('#modal-work-date', modal).val(),
                        types: $('#modal-work-type', modal).val(),
                    },
                    pickup: {
                        zip: $('[name="pickup[zip]"]', modal).val(),
                        address: $('[name="pickup[address]"]', modal).val(),
                        stairs: $('[name="pickup[stairs]"]', modal).val(),
                        elevator: ($('[name="pickup[elevator]"]', modal).is(':checked') ? 1 : 0),
                    },
                    destination: {
                        zip: $('[name="destination[zip]"]', modal).val(),
                        address: $('[name="destination[address]"]', modal).val(),
                        stairs: $('[name="destination[stairs]"]', modal).val(),
                        elevator: ($('[name="destination[elevator]"]', modal).is(':checked') ? 1 : 0),
                    },
                    client: {
                        id: $('[name="client[id]"]', modal).val(),
                        name: $('[name="client[name]"]', modal).val(),
                        lname: $('[name="client[lname]"]', modal).val(),
                        phone: $('[name="client[phone]"]', modal).val(),
                        email: $('[name="client[email]"]', modal).val(),
                    }
                };

                if (submitBtn.data('is-loading')) {
                    return true;
                } else {
                    submitBtn.data('is-loading', true)
                    $('.loading', submitBtn).removeClass('d-none');
                }

                try {
                    await App.Orders.createOrder(order);
                    //submitBtn.data('is-loading', false)
                    // $('.loading', submitBtn).addClass('d-none');
                } catch (e) {
                    $('#content-spinner').toggleClass('d-none');
                    submitBtn.data('is-loading', false)
                    $('.loading', submitBtn).addClass('d-none');
                }

            });
        })
    },

    _bindCreateOrder() {
        $('#btn-create-order').one('click', function () {
            App.Orders.createOrder();
        });
    },
    fillClientFields(form, ClientData) {
        $(form).find('[name="client[id]"]').val(ClientData.id);
        $(form).find('[name="client[name]"]').val(ClientData.name).prop('readOnly', true);
        $(form).find('[name="client[lname]"]').val(ClientData.lname).prop('readOnly', true);
        $(form).find('[name="client[phone]"]').val('');
        if (ClientData.phones.length > 0)
            $(form).find('[name="client[phone]"]').val(ClientData.phones[0].value);
        $(form).find('[name="client[phone]"]').prop('readOnly', true);
        $(form).find('[name="client[email]"]').val('');
        if (ClientData.emails.length > 0)
            $(form).find('[name="client[email]"]').val(ClientData.emails[0].value);
        $(form).find('[name="client[email]"]').prop('readOnly', true);
        $(form).find("#modal-reset-selected").prop('disabled', false);
    },
    resetClientFields(form) {
        $(form).find('[name="client[name]"]').val('').prop('readOnly', false);
        $(form).find('[name="client[id]"]').val('').prop('readOnly', false);
        $(form).find('[name="client[lname]"]').val('').prop('readOnly', false);
        $(form).find('[name="client[phone]"]').val('').prop('readOnly', false);
        $(form).find('[name="client[email]"]').val('').prop('readOnly', false);
        $(form).find('[name="client[email]"]').val('').prop('readOnly', false);
        $(form).find("#modal-reset-selected").prop('disabled', true);
    },

    clientAutocomplete() {
        const self = this;
        // autocomplete
        $('.client-autocomplete:not(.bound)')
            .each(function () {
                const form = $(this).closest('form')[0];
                let el = $(this);

                el.addClass('bound')
                    .typeahead({
                        matcher: () => $(el).is(':focus'),
                        source: function (query, process) {
                            let ajaxQuery = query;
                            if (this.$element[0].inputmask)
                                ajaxQuery = this.$element[0].inputmask.unmaskedvalue();
                            ajaxQuery = ajaxQuery.replace(/@\s*$/, '');
                            ajaxQuery = ajaxQuery.replace(/@?_?\.?_$/, '');
                            $.ajax({
                                dataType: "json",
                                method: "post",
                                url: '/client/profile/autocomplete',
                                data: {q: ajaxQuery},
                            }).done(function (data) {
                                let results = [];
                                $.each(data.data.results, function () {
                                    results.push({
                                        name: this.name + ' ' + this.lname,
                                        db: this
                                    });
                                });
                                process(results);
                            });
                        },
                        highlighter: function (item) {
                            if (this.query.length < 2)
                                return item;

                            let query = this.query.replace(/[\-\[\]{}()*+?.,\\\^$|#\s]/g, '\\$&');
                            return item.replace(new RegExp('(' + query + ')', 'ig'), function ($1, match) {
                                return '<strong>' + match + '</strong>'
                            });
                        },
                        displayText: function (item) {
                            return App.Miscs.formatClient(item.db);
                        },
                        minLength: 2,
                        afterSelect: function (item) {
                            self.fillClientFields(form, item.db);

                            this.$element.blur();
                        },
                        items: 5,
                        autoSelect: false
                    });
            });
    },

    zipAutocomplete() {
        // autocomplete
        $('.zip-autocomplete:not(.bound)')
            .each(function () {
                let ajaxQuery = '';
                $(this)
                    .addClass('bound')
                    .typeahead({
                        matcher: function () {
                            return true;
                        },
                        source: Debounce(function (query, process) {
                            ajaxQuery = query;
                            if (this.$element[0].inputmask)
                                ajaxQuery = this.$element[0].inputmask.unmaskedvalue();
                            ajaxQuery = ajaxQuery.replace(/@\s*$/, '');
                            $.ajax({
                                dataType: "json",
                                method: "post",
                                url: '/orders/waypoints/zipGeoInfo',
                                data: {q: ajaxQuery},
                            }).done(function (data) {
                                let results = [
                                    {
                                        ...data.result
                                    }
                                ];
                                process(results);
                            });
                        }, 300),
                        highlighter: function (item) {
                            if (this.query.length < 2)
                                return item;

                            let query = this.query.replace(/[\-\[\]{}()*+?.,\\\^$|#\s]/g, '\\$&');
                            return item.replace(new RegExp('(' + query + ')', 'ig'), function ($1, match) {
                                return '<strong>' + match + '</strong>'
                            });
                        },
                        displayText: function (res) {
                            if (res.formatted_address === 'United States') {
                                return (parseInt(ajaxQuery).length === 5 ? 'ZIP Not found' : 'Enter full ZIP');
                            }

                            let markup = "<div class='clearfix'>" +
                                `<div class='fs-md'>${res.formatted_address}</div>` +
                                "</div>";

                            return markup;
                        },
                        minLength: 2,
                        afterSelect: function (item) {
                            this.$element.blur();
                            if (item.hasOwnProperty('address_data'))
                                this.$element.val(item.address_data.postal_code);
                        },
                        items: 5,
                        autoSelect: false
                    });
            });
    },

    initDaterangepicker: function () {
        $('#daterangepicker').daterangepicker({
            // timePicker: true,
            minDate: moment('2020-01-01', 'YYYY-MM-DD'),
            maxDate: moment().add(6, 'month'),
            startDate: moment($('#daterange-start').val(), 'YYYY-MM-DD'),
            endDate: moment($('#daterange-end').val(), 'YYYY-MM-DD'),
            drops: 'auto',
            locale: {
                format: 'MMM DD, YYYY'
            },
            alwaysShowCalendars: true,
            ranges: {
                'Today': [moment(), moment()],
                'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                'This Month': [moment().startOf('month'), moment().endOf('month')],
                'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
                'This Year': [moment().startOf('year'), moment().subtract(1, 'month').endOf('year')]
            }
        }, function (start, end, label) {
            this.element.closest('.input-group').find('input[name="date-range[start]"]').val(start.format('YYYY-MM-DD'));
            this.element.closest('.input-group').find('input[name="date-range[end]"]').val(end.format('YYYY-MM-DD'));
            App.OrderList._applyFilters();
        });
    },

    init: function () {
        this._bindFilter();
        this.initDaterangepicker();
        this._bindCreateOrder();
        this._bindCreateOrderFromModal();
        this.initDT();
    }

};
