require('nestable2'); // drag and drop waypoints
require('imports-loader?define=>false,this=>window!./vendor/bootstrap-typeahead/bootstrap3-typeahead.js')(window, $)

let order_id = parseInt(document.getElementById('order_id').textContent);
window.VueApp.$store.dispatch({
    type: 'initSession',
    id: order_id
});


$(function () {

    $('#order-tabs a').click(function (e) {
        // console.log($(this).attr('tab'));
        const $clickedTab = $(this);
        const currentHref = $('#order-tabs a.active').attr('href');
        const targetHref = $clickedTab.attr('href');

        const isLeavingInventoryTab = currentHref === '#tab-inventory' && targetHref !== '#tab-inventory';
        if (isLeavingInventoryTab && window.isInventoryChanged) {
            const confirmed = window.confirm('You have unsaved changes. If you leave now, any unsaved data may be lost. Are you sure you want to proceed?');
            if (!confirmed) {
                e.preventDefault();
                return false;
            }
        }

        if ($clickedTab.attr('tab')) {
            if (currentHref === '#tab-overview') return true;

            const scrollTo = $(targetHref);
            // console.log('scrollTo', scrollTo);
            e.preventDefault();
            e.stopPropagation();
            $('#order-tabs a[href="' + $clickedTab.attr('tab') + '"]')
                .off('shown.bs.tab')
                .on('shown.bs.tab', function () {
                    // $('html,body').animate({scrollTop: scrollTo.offset().top}, 'smooth');
                    window.scrollTo({top: scrollTo.offset().top, behavior: 'smooth'});
                });
            $('#order-tabs a[href="' + $clickedTab.attr('tab') + '"]').tab('show');

            return false;
        } else
            $('#order-tabs a[data-toggle="tab"]')
                .off('shown.bs.tab')
                .on('shown.bs.tab', function () {
                    window.scrollTo({top: 0, behavior: 'smooth'});
                });

    });

    $('.select2').select2();

    App.Miscs.select2($(".js-select2-icons"), {
        minimumResultsForSearch: 1 / 0,
        templateSelection: (elm) => {
            return elm.id ? "<i class='" + $(elm.element).data("icon") + " mr-2'></i>" + elm.text : elm.text;
        },
    });


});


App.Order = {

    clientAutocomplete(vueObj) {
        // autocomplete
        $('.client-autocomplete:not(.bound)')
            .each(function () {
                let el = $(this);

                el
                    .addClass('bound')
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
                            vueObj.newClient.id = item.db.id;
                            vueObj.newClient.name = item.db.name;
                            vueObj.newClient.lname = item.db.lname;
                            if (item.db.phones.length > 0) {
                                vueObj.newClient.phone = item.db.phones[0].value;
                            }
                            if (item.db.emails.length > 0) {
                                vueObj.newClient.email = item.db.emails[0].value;
                            }

                            vueObj.disabledInput = true;

                            this.$element.blur();
                        },
                        items: 5,
                        autoSelect: false
                    });
            });
    },

};

