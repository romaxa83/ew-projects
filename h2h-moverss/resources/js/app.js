import {AxiosHelper} from "@/helpers/axiosHelper";
import {AxiosError} from 'axios';

require('./bootstrap');
require('inputmask');
const flatpickr = require("flatpickr");
window.flatpickr.l10ns.default.firstDayOfWeek = 0;

import Vue from 'vue';
import PortalVue from 'portal-vue'
import store from './store'

Vue.use(PortalVue);

window.moment = global.moment = require('moment-timezone');

window.App = global.App = {};

// // import * as Sentry from "@sentry/vue";
// import * as Sentry from "@sentry/browser";
// import {Integrations} from "@sentry/tracing";
//
// Sentry.init({
//     Vue,
//     dsn: 'https://ec2e493a749a46e69c6dcb5a0d1063c7@glitchtip.thebigidea.com.ua/1',
//     // integrations: [new Integrations.Vue({ Vue, attachProps: true })],
//     integrations: [
//         new Integrations.BrowserTracing()
//      ],
//     // // Set tracesSampleRate to 1.0 to capture 100%
//     // // of transactions for performance monitoring.
//     // // We recommend adjusting this value in production
//     // tracesSampleRate: 1.0,
// });


$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});

Object.filter = function (obj, predicate) {
    let result = {}, key;

    for (key in obj) {
        if (obj.hasOwnProperty(key) && predicate(obj[key])) {
            result[key] = obj[key];
        }
    }

    return result;
};


let myTooltipWhiteList = $.fn.tooltip.Constructor.Default.whiteList
myTooltipWhiteList['*'].push('style');

$.fn.serializeFormObject = function (params = {checkboxes: {unchecked: true}}) {
    var form = $(this[0]);

    function trim(str) {
        return str.replace(/^\s+|\s+$/g, "");
    }

    var o = {};
    var a = $(form).serializeArray();
    //unchecked checkboxes
    if (params.checkboxes.unchecked)
        $(form).find('input:checkbox:not(:checked)').map(function () {
            a.push({name: this.name, value: this.checked ? this.value : "0"});
        });

    $.each(a, function () {
        var nameParts = this.name.split('[');
        if (nameParts.length == 1) {
            // New value is not an array - so we simply add the new
            // value to the result object
            if (o[this.name] !== undefined) {
                if (!o[this.name].push) {
                    o[this.name] = [o[this.name]];
                }
                o[this.name].push(this.value || '');
            } else {
                o[this.name] = this.value || '';
            }
        } else {
            // New value is an array - we need to merge it into the
            // existing result object
            $.each(nameParts, function (index) {
                nameParts[index] = this.replace(/\]$/, '');
            });

            // This $.each merges the new value in, part by part
            var arrItem = this;
            var temp = o;
            $.each(nameParts, function (index) {
                var next;
                var nextNamePart;
                if (index >= nameParts.length - 1)
                    next = arrItem.value || '';
                else {
                    nextNamePart = nameParts[index + 1];
                    if (trim(this) != '' && temp[this] !== undefined)
                        next = temp[this];
                    else {
                        if (trim(nextNamePart) == '')
                            next = [];
                        else
                            next = {};
                    }
                }

                if (trim(this) == '') {
                    temp.push(next);
                } else
                    temp[this] = next;

                temp = next;
            });
        }
    });
    return o;
};

$(function () {

    let tpl_settings = initApp.getSettings();
    if (tpl_settings === 'mod-bg-1 mod-nav-link mod-clean-page-bg nav-function-fixed mod-hide-info-card header-function-fixed mod-skin-light') {
        // default
        initApp.pushSettings(tpl_settings + ' nav-function-minify');
        // initApp.updateTheme('/smartadmin/css/themes/cust-theme-3.css', true);
    }


    App.User.init();

    App.Miscs.tabTracked();

    // phpDebugBar для DT
    if (typeof window.phpdebugbar != "undefined") {
        $('body').on('xhr.dt', function (e, settings, data, xhr) {
            if (xhr.getAllResponseHeaders()) {
                phpdebugbar.ajaxHandler.handle(xhr);
            }
        });
    }

    $(document).on('click', '.custom-checkbox .custom-control-label', function () {
        if ($(this).prop('for').length == 0) {
            const input = $(this).closest('.custom-checkbox').find('input');
            input.prop('checked', !input.prop('checked')).trigger('change');
        }
    });
});


App.User = {

    chooseDivision(id) {
        AxiosHelper({
            url: '/company/choose-division',
            data: {
                id,
            }
        })
            .then(() => {
                const regex = new RegExp('/orders/([0-9]+)');
                const foundOrder = window.location.href.match(regex);
                if (foundOrder && foundOrder[1]) {
                    window.location.href = '/orders';
                } else {
                    location.reload();
                }
            });
    },

    logOut() {
        $.ajax({
            url: '/logout',
            method: 'POST',
            dataType: 'json'
        })
            .done(() => {
                window.location.href = '/';
            });
    },

    init() {
        $('[data-logout]').on('click', (e) => {
            e.preventDefault();

            this.logOut();
        });

        $('.choose-division .dropdown-item').on('click', function (e) {
            e.preventDefault();

            App.User.chooseDivision($(this).data('id'));
        });
    }
};

App.Orders = {

    createOrder(obj = {}) {
        return AxiosHelper({
            url: '/orders/create',
            data: {
                'move-type': 'local',
                source: null,
                work: {
                    date: null,
                    types: [1],
                },
                ...obj
            }
        })
            .then(resp => {
                App.Forms.showAlert('success', resp.msg);

                if (resp.redirect) {
                    setTimeout(function () {
                        window.location.href = resp.redirect;
                    }, 1000);
                }

                return resp;
            });
    }

};

App.Forms = {

    // Вызов объекта по строковому имени по объекту
    // (с) https://stackoverflow.com/questions/359788/how-to-execute-a-javascript-function-when-i-have-its-name-as-a-string
    _executeFunctionByNameObj(functionName, context, arg) {
        let args = Array.prototype.slice.call(arguments, 2),
            namespaces = functionName.split('.'),
            func = namespaces.pop();
        for (let i = 0; i < namespaces.length; i++) {
            context = context[namespaces[i]];
        }
        return context[func].apply(context, args);
    },

    // Вызов объекта по строковому имени
    executeFunction(RunFunc, params = {}) {
        let fn = window[RunFunc];
        if (typeof fn === 'function') {
            fn(params);
        } else {
            let fn = [RunFunc];
            if (typeof fn === 'object') {
                App.Forms._executeFunctionByNameObj(RunFunc, window, params);
            }
        }
    },

    simpleErrors(r) {
        let errorMsg = 'Cannot get AJAX info';
        let errorTitle = 'Error';
        const resp = r instanceof AxiosError ? r.response.data : r;
        if (resp)
            if (resp.errors) {
                errorMsg = '';
                // Выводим первую ошибку
                $.each(resp.errors, function (key, value) {
                    if (Array.isArray(value))
                        errorMsg += value[0];
                    else
                        errorMsg += value;

                    return false;
                });
            } else if (resp.exception === 'Exception') {
                errorMsg = resp.message;
            } else if (resp.msg || resp.error) {
                errorMsg = resp.msg ? resp.msg : resp.error;
            } else if (resp.titledError) {
                errorMsg = resp.titledError.message ? resp.titledError.message : '';
                errorTitle = resp.titledError.title;
            } else if (resp.message) {
                errorMsg = resp.message;
            }

        App.Forms.showAlert('error', errorTitle, errorMsg);
    },

    simpleNotices(notices, params = {}) {
        if (notices) {
            $.each(notices, function (k, v) {
                App.Forms.showAlert('warning', v, null, params);
            });
        }
    },

    showAlert(type, title, text = null, params = null) {
        let obj = {
            closeButton: false,
            debug: false,
            newestOnTop: true,
            progressBar: true,
            positionClass: 'toast-top-right',
            preventDuplicates: false,
            onclick: null,
            showDuration: 300,
            hideDuration: 300,
            timeOut: 7000,
            extendedTimeOut: 5000,
            showEasing: 'swing',
            hideEasing: 'linear',
            showMethod: 'fadeIn',
            hideMethod: 'fadeOut'
        }

        if (params)
            obj = Object.assign(obj, params);

        toastr.options = obj;
	    toastr[type](title, text || '');
	    console.warn(
			`[Toasts ${type.toUpperCase()}] ${title}`,
		    text ? `: ${text}` : 'Without text',
		    params
	    );
    }
};

App.Miscs = {

    // Табы с сохранением ссылки. Открывает нужный по хешу
    tabTracked() {
        let el = $('[role="tablist-link-tracked"]');
        if (el.length) {
            $('a[data-toggle="tab-link-tracked"]', el)
                .on('click', function () {
                    const $this = $(this);
                    $this.tab('show');
                    const event = $this.data('trigger-global-event');
                    if (event) {
                        $(window).trigger(event, $this);
                    }
                });

            let hash = window.location.hash;
            if (hash) {
                $(`[role="tablist-link-tracked"] a[data-toggle="tab-link-tracked"][href="${hash}"]`).tab('show');
            }
        }
    },

    generateToken() {
        return Math.random().toString(36).substring(2);
    },

    select2dropdown: (el, props = {}) => {
        App.Miscs.select2(el, {
            theme: 'default', // select2-dropdown-theme
            minimumResultsForSearch: 1 / 0,
            // dropdownCssClass: 'select2-dropdown-results',
            // selectionCssClass: 'select2-dropdown-select',
            // templateSelection: (elm) => {
            //     return '<span class="select2-inner">' + elm.text + ' <i class="fal fa-angle-down d-inline-block ml-1 fs-md"></i></span>';
            // },
            ...props
        });
    },

    select2: (el, props = {}) => {
        $(el).select2({
            escapeMarkup: function (elm) {
                return elm;
            },
            ...props
        });
    },

    templateSelect2Order(data) {
        if (!data.disabled) {
            let orderStatus = '';
            if (data.status)
                orderStatus = `[${data.status.title}]`;
            let orderId = `<div class="fs-md oid">Order: #${data.id} <span class="fs-xs opacity-80">${orderStatus}</span></div>`;
            if (data.client) {
                let client = $(App.Miscs.formatClient(data.client));
                $(client).find('.fs-md').parents().prepend(orderId);
                $(client).find('.fs-md:not(.oid)').removeClass('fs-md');
                return client;
            }
            return orderId;
        }
    },

    formatClient(client) {
        if (client.loading) {
            return client.text;
        }

        let markup = "<div class='clearfix'>" +
            `<div class='fs-md'>[${client.id}] ${client.name} ${client.lname}</div>` +
            "</div>";
        if (client.phones.length > 0 || client.emails.length > 0) {
            markup += "<div class='opacity-80 fs-nano mt-1'>";
            //phones
            if (client.phones.length > 0) {
                markup += "<div class=''>";
                $.each(client.phones, function () {
                    markup += "<span class='pr-2'><i class='fas fa-mobile-alt pr-1'></i> " + Inputmask.format(this.value, {"mask": "(999) 999-9999"}) + "</span>";
                });
                markup += "</div>";
            }
            //emails
            if (client.emails.length > 0) {
                markup += "<div class=''>";
                $.each(client.emails, function () {
                    markup += "<span class='pr-2'><i class='fas fa-envelope'></i> " + this.value + "</span>";
                });
                markup += "</div>";
            }

            markup += "</div>";
        }

        return markup;
    },

    getCurrentDivision: () => $('.choose-division a.active').data('id'),

    data_divisions: {},

    getDivisions() {
        let divisions = App.Miscs.data_divisions;
        if (!jQuery.isEmptyObject(divisions))
            return divisions;

        $('.choose-division .dropdown-item').each(function () {
            let el = $(this),
                title = el.text().trim(),
                id = el.data('id');

            divisions[id] = title;
        });

        return divisions;
    }
};

// Компоненты которые запускаются с html
const AppTasks = () => import(/* webpackChunkName: "AppTasks" */ './components/App/Tasks');
const AppTasksCalendar = () => import(/* webpackChunkName: "AppTasksCalendar" */ './components/App/TasksCalendar');
const AppOrdersPipeline = () => import(/* webpackChunkName: "AppOrdersPipeline" */ './components/App/OrdersPipeline');
const AppMailBox = () => import(/* webpackChunkName: "AppMailBox" */ './components/App/MailBox');
const OrderActivities = () => import(/* webpackChunkName: "OrderActivities" */ './components/Order/TabOverview/Activities');
const OrderOverview = () => import(/* webpackChunkName: "OrderOverview" */ './components/Order/Overview');
const OrderInventory = () => import(/* webpackChunkName: "OrderInventory" */ './components/Order/Inventory');
const OrderNotes = () => import(/* webpackChunkName: "OrderNotes" */ './components/Order/Notes');
const OrderPayments = () => import(/* webpackChunkName: "OrderPayments" */ './components/Order/Payments');
const OrderFiles = () => import(/* webpackChunkName: "OrderFiles" */ './components/Order/Files');
const OrderComments = () => import(/* webpackChunkName: "OrderFiles" */ './components/Order/Comments');
const OrderCommunications = () => import(/* webpackChunkName: "OrderCommunications" */ './components/Order/TabOverview/Communications');
const OrderCommunicationsCalls = () => import(/* webpackChunkName: "OrderCommunicationsCalls" */ './components/Order/TabOverview/Communications/Calls');
const OrderCallsZadarma = () => import(/* webpackChunkName: "OrderCallsZadarma" */ './components/Order/TabOverview/Communications/CallsZadarma');
const OrderTotalEstimateSum = () => import(/* webpackChunkName: "OrderTotalEstimateSum" */ './components/Order/Blocks/TotalEstimateSum');
const OrdersActiveFilters = () => import(/* webpackChunkName: "OrdersActiveFilters" */ './components/Orders/ActiveFilters.vue');
const AppCommunicationsList = () => import(/* webpackChunkName: "AppCommunicationsList" */ './components/App/Communications');
const AppCommunicationsListNew = () => import(/* webpackChunkName: "AppCommunicationsListNew" */ './components/App/CommunicationsNew');

const CalendarHeader = () => import(/* webpackChunkName: "CalendarHeader" */ './components/CalendarHeader');
const DispatchWorksHeader = () => import(/* webpackChunkName: "DispatchWorksHeader" */ './components/Dispatch/WorksHeader');
const VuePortals = () => import(/* webpackChunkName: "DispatchWorksHeader" */ './components/VuePortals');
const DispatchWorksCrewsTopPanel = () => import(/* webpackChunkName: "DispatchWorksCrewsTopPanel" */ './components/Dispatch/WorksCrewsTopPanel');
const DispatchWorksChangelog = () => import(/* webpackChunkName: "DispatchWorksCrewsTopPanel" */ './components/Dispatch/WorksChangelog');
const DispatchWorksTrucksTopPanel = () => import(/* webpackChunkName: "DispatchWorksTrucksTopPanel" */ './components/Dispatch/WorksTrucksTopPanel');
const WorksPanel = () => import(/* webpackChunkName: "DispatchWorkPanel" */ './components/Dispatch/WorkPanel');
const CompanyTrucks = () => import(/* webpackChunkName: "CompanyTrucks" */ './components/Company/Trucks');
const CompanyEmployee = () => import(/* webpackChunkName: "CompanyEmployee" */ './components/Company/Employee');
const CompanyPeakDate = () => import(/* webpackChunkName: "CompanyPeakDate" */ './components/Company/PeakDate');
const Partner = () => import(/* webpackChunkName: "CompanyPeakDate" */ './components/Partners/Partner');
const SettingsRouteList = () => import(/* webpackChunkName: "SettingsRouteList" */ './components/Settings/RouteList');
const SettingsEmailTemplates = () => import(/* webpackChunkName: "SettingsEmailTemplates" */ './components/Settings/EmailTemplates');
const SettingsTasksTypes = () => import(/* webpackChunkName: "SettingsTasksTypes" */ '@components/Settings/TasksTypes');
const SettingsDivisions = () => import(/* webpackChunkName: "SettingsDivisions" */ '@components/Settings/Divisions');
const SettingsDivisionsFooterTexts = () => import(/* webpackChunkName: "SettingsDivisionsFooterTexts" */ '@components/Settings/DivisionsFooterTexts');
const SettingsTags = () => import(/* webpackChunkName: "SettingsTags" */ '@components/Settings/Tags');
const ReportOne = () => import(/* webpackChunkName: "ReportsReportOne" */ './components/Reports/Report1/ReportOne');
const ReportEffectiveActions = () => import(/* webpackChunkName: "ReportEffectiveActions" */ './components/Reports/ReportEffectiveActions/ReportEffectiveActions');
const ReportByManagers = () => import(/* webpackChunkName: "ReportByManagers" */ './components/Reports/ReportByManagers/ReportByManagers');
const ReportAuthorizeTransactions = () => import(/* webpackChunkName: "ReportAuthorizeTransactions" */ './components/Reports/Authorize/Transactions');
const ReportFinancialOrder = () => import(/* webpackChunkName: "ReportAuthorizeTransactions" */ './components/Reports/Financial/Order');
const ReportSalesFunelOrder = () => import(/* webpackChunkName: "ReportAuthorizeTransactions" */ './components/Reports/SalesFunel/Order');
const ReportForemanCash = () => import(/* webpackChunkName: "ReportAuthorizeTransactions" */ './components/Reports/ReportForemanCash');
const ReportAuthorizeOnlineCharge = () => import(/* webpackChunkName: "ReportAuthorizeOnlineCharge" */ './components/Reports/Authorize/OnlineCharge');
const TasksNotifications = () => import(/* webpackChunkName: "TasksNotifications" */ './components/App/TasksNotifications');
const ReportActivityAudit = () => import(/* webpackChunkName: "ReportActivityAudit" */ './components/Reports/Activity/AuditReport');
const CashRegistryForemans = () => import(/* webpackChunkName: "ReportAuthorizeTransactions" */ './components/CashRegistry/Foremans');
const CashRegistryOperations = () => import(/* webpackChunkName: "ReportAuthorizeTransactions" */ './components/CashRegistry/Operations');
// const CallWidget = () => import(/* webpackChunkName: "CallWidget2" */ './components/App/CallWidget');
import CallWidget from "@components/App/CallWidget";

const CallWidgetStatus = () => import(/* webpackChunkName: "CallWidgetStatus" */ './components/App/CallWidgetStatus');
const ClientModal = () => import(/* webpackChunkName: "ClientModal" */ './components/App/ClientModal');
const FindDuplicates = () => import(/* webpackChunkName: "FindedDuplicatesModal" */ '@components/App/Clients/FindDuplicates.vue');
// const OrdersDatatables = () => import(/* webpackChunkName: "OrdersDatatables" */ './components/Orders/OrdersDatatables');
// import OrdersDatatables from "@components/Orders/OrdersDatatables";
// import TasksNotifications from "@components/App/TasksNotifications";// 63


window.VueApp = new Vue({
    el: '#app',
    store,
    components: {
        TasksNotifications,
        AppTasks,
        AppTasksCalendar,
        AppOrdersPipeline,
        AppMailBox,
        AppCommunicationsList,
        AppCommunicationsListNew,
        OrderActivities,
        OrderOverview,
        OrderInventory,
        OrderNotes,
        OrderPayments,
        OrderFiles,
        OrderComments,
        OrderCommunications,
        OrderCommunicationsCalls,
        OrderCallsZadarma,
        OrderTotalEstimateSum,
        OrdersActiveFilters,
        CalendarHeader,
        DispatchWorksHeader,
        DispatchWorksTrucksTopPanel,
        DispatchWorksCrewsTopPanel,
        DispatchWorksChangelog,
        FindDuplicates,
        WorksPanel,
        ClientModal,
        CompanyTrucks,
        CompanyEmployee,
        Partner,
        CompanyPeakDate,
        SettingsRouteList,
        SettingsEmailTemplates,
        SettingsTasksTypes,
        SettingsDivisions,
        SettingsDivisionsFooterTexts,
        SettingsTags,
        SettingsTeamsPlans: () => import(/* webpackChunkName: "SettingsTeamsPlans" */ '@components/Settings/TeamsPlans/Panel.vue'),
        ReportOne,
        ReportEffectiveActions,
        ReportByManagers,
        ReportAuthorizeTransactions,
        ReportFinancialOrder,
        ReportSalesFunelOrder,
        ReportForemanCash,
        ReportAuthorizeOnlineCharge,
        ReportActivityAudit,
        CashRegistryForemans,
        CashRegistryOperations,
        CallWidget,
        CallWidgetStatus,
        VuePortals,
    }
});
