require('./bootstrap');
import Vue from 'vue';

require('imports-loader?define=>false,this=>window!./vendor/bootstrap-typeahead/bootstrap3-typeahead.js')(window, $)
import '../../public/smartadmin/css/notifications/sweetalert2/sweetalert2.bundle.css';
import 'ispin/dist/ispin.css';

window.App = global.App = {};
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});

import CustomerInventory from "./components/Customer/Inventory";

new Vue({
    el: '#app',
    components: {
        CustomerInventory,
    }
});

(function ($) {
    $('#customer-order .card-body').find('table').each(function () {
        $(this).wrap('<div class="table-responsive"></div>');
    });
})(window.jQuery);
