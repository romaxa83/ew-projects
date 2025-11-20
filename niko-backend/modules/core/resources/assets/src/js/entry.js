import Swal from './../plugins/sweetalert2/sweetalert2.all';
import inits from './inits';

window.swal = Swal;

window.toast = Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: true
});

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Handle error message
window.axios.interceptors.response.use(function (response) {
    if (response.data.message) {
        if (response.data.success === true) {
            toast({type: "success", title: response.data.message, timer: 5000});
        } else {
            toast({type: "error", title: response.data.message});
        }
    }
    return response;
}, function (error) {
    if (error.response) {
        if (error.response.data.message) {
            var text = null;

            if (error.response.data.errors) {
                var errors = [];
                for (var errorIndex in error.response.data.errors) {
                    errors.push(error.response.data.errors[errorIndex]);
                }
                text = errors.join('<br>');
            }

            toast({type: "error", title: error.response.data.message, html: text});
        } else {
            toast({type: "error", title: "Server error"});
        }
    }

    return Promise.reject(error);
});

/**
 * Next we will register the CSRF Token as a common header with Axios so that
 * all outgoing HTTP requests automatically have it attached. This is just
 * a simple convenience so we don't have to attach every token manually.
 */

var token = document.head.querySelector('meta[name="csrf-token"]');

if (token) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
    $.ajaxSetup({
        headers: {'X-CSRF-TOKEN': token.content}
    });
} else {
    console.error('CSRF token not found: https://laravel.com/docs/csrf#csrf-x-csrf-token');
}

// Call inits

$(document).ready(function () {
    let $body = $(document.body);

    moment.locale(document.documentElement.lang);

    inits.scrollSidebar();
    inits.sidebarToggle();
    inits.sidebarSearch();

    inits.slimScroll();
    inits.actionsForListItems();
    inits.hideLangTabs();
    inits.preloader.stop();
    inits.sidebarMenu();
    inits.formSubmit();
    inits.cloneFormControls();
    window.confirmDelete = inits.confirmDelete;
    inits.tooltip();
    inits.confirmation();
    inits.datePicker();
    inits.dateTimeRangePicker();
    inits.timeRangePicker();
    inits.dateRangePicker();
    inits.tinyMCE();
    inits.tinyWysiwyg();
    inits.statusSwitcher();
    inits.select2();
    inits.ajaxSelect2();
    inits.multipleInputs();
    inits.dropDownload();
    inits.map();
    inits.sortable();
    inits.nestable();
    inits.checkChild();
    inits.simpleAjaxFormSubmit();
    inits.dataTable();
    inits.multiSelect();
    inits.colorPicker();
    inits.alphaColorPicker();
    inits.generateSlug();
    inits.perPage();

    $('.dropdown-toggle').dropdown();

    $('.js-mark-notifications-as-read').click(function (event) {
        event.preventDefault();
        let $this = $(this);
        window.axios.post(route('admin.mark-notifications-as-read')).then(function (response) {
            if (response.data.success) {
                let $notify = $this.closest('.nav-item').find('.notify');
                if ($notify.length) {
                    $notify.remove();
                }
            }
        });
    });

    $('.js-mark-notification-as-read').click(function (event) {
        event.preventDefault();
        let $this = $(this);
        window.axios.post(route('admin.mark-notification-as-read', $this.data('notification-id')))
        .then(function () {
            window.location.href = $this.attr('href');
        }).catch(function () {
            window.location.href = $this.attr('href');
        });
    });

    $(document).on('click', '[data-toggle="modal"]', function(){
        let remote = $(this).data('remote');
        if (remote) {
            $($(this).data("target")+' .modal-content').load(remote);
        }
    });

    $('body').on('hidden.bs.modal', '.modal', function () {
        if ($(this).data('clear-body')) {
            $(this).removeData('bs.modal').find('.modal-body').html('');
        }
    });

    $body.on('shown.bs.modal', '.modal', function () {
        inits.hideLangTabs();
        inits.simpleAjaxFormSubmit();
    });
});
