window.inits = {};

inits.loader = {
    create () {
        let loader = document.createElement('div');
        let loaderBg = document.createElement('div');
        let loaderIcon = document.createElement('div');

        loader.className = 'loader is-active';
        loaderBg.className = 'loader-background';
        loaderIcon.className = 'loader-icon';

        loader.appendChild(loaderBg);
        loader.appendChild(loaderIcon);

        this.loader = loader;
    },
    add (target) {
        if (target instanceof HTMLElement) {
            this.create();
            target.appendChild(this.loader);
        }
    },
    remove (target) {
        if (target instanceof HTMLElement) {
            target.removeChild(this.loader);
            this.clear();
        }
    },
    clear () {
        this.loader = null;
    }
};

inits.sidebarSearch = function () {
    const sideBarNav = document.querySelector('#sidebarnav');

    if (!sideBarNav) {
        return false;
    }

    const search = (() => {
        const list = {};

        sideBarNav.querySelectorAll('[data-search-i]').forEach((item, index) => {
            let keyText = item.innerText.trim().toLowerCase();

            // if the same names
            if (keyText in list) {
                keyText += index;
            }

            list[keyText] = [];

            let node = item;

            // make list by nodes to the root ul (#sidebarnav)
            while (node) {
                if (node.matches('#sidebarnav')) {
                    node = null;
                    break;
                } else {
                    let nodeTagName = node.tagName.toLowerCase();
                    if (nodeTagName === 'ul' || nodeTagName === 'li') {
                        list[keyText].push(node);
                    }
                    node = node.parentElement;
                }
            }
        });

        return {
            list: list,
            keys: Object.keys(list),
            input: document.querySelector('#sidebar-search-input'),
            clear: document.querySelector('#sidebar-clear-search-input'),
        };
    })();

    if (!search.input || !search.keys.length) {
        return false;
    }

    const searchList = {
        activePath: (() => {
            let result = '';
            for (let key in search.list) {
                if (search.list.hasOwnProperty(key)) {
                    const listNodes = search.list[key];
                    const li = listNodes.filter(element => element.tagName.toLowerCase() === 'li');
                    const expandPath = li.every((element) => {
                        return element.classList.contains('active');
                    });
                    if (expandPath) {
                        if (result.length < listNodes.length) {
                            result = listNodes;
                        }
                    }
                }
            }
            return result;
        })(),

        noFoundElement: (() => {
            let element = sideBarNav.children[0];
            element.removeAttribute('hidden');
            sideBarNav.removeChild(element);
            return element.outerHTML;
        })(),

        showAll (setActivePath = true) {
            for (let key in search.list) {
                if (search.list.hasOwnProperty(key)) {
                    const listNodes = search.list[key];

                    for (let i = 0; i < listNodes.length; i++) {
                        let node = listNodes[i];
                        let nodeTagName = node.tagName.toLowerCase();

                        node.removeAttribute('style');

                        if (nodeTagName === 'li') {
                            node.classList.remove('search-arrow-active', 'active');
                            node.children[0].setAttribute('aria-expanded', false);
                        }
                        else if (nodeTagName === 'ul') {
                            node.classList.remove('show');
                            node.setAttribute('aria-expanded', false);
                        }
                    }
                }
            }

            if (setActivePath && this.activePath) {
                for (let i = 0; i < this.activePath.length; i++) {
                    let node = this.activePath[i];
                    let nodeTagName = node.tagName.toLowerCase();
                    if (nodeTagName === 'li') {
                        node.classList.add('active');
                        node.children[0].setAttribute('aria-expanded', true);
                    } else if (nodeTagName === 'ul') {
                        node.classList.add('show');
                        node.setAttribute('aria-expanded', true);
                    }
                }
            }
        },

        hideAll () {
            for (let key in search.list) {
                if (search.list.hasOwnProperty(key)) {
                    for (let i = 0; i < search.list[key].length; i++) {
                        search.list[key][i].style.display = 'none';
                    }
                }
            }
        },

        show (elements = []) {
            for (let i = 0; i < elements.length; i++) {
                let node = elements[i];
                let nodeTagName = node.tagName.toLowerCase();

                node.style.display = 'block';

                if (nodeTagName === 'li') {
                    if (node.children[0].href !== window.location.href + '#') {
                        node.classList.add('active');
                    } else {
                        node.classList.remove('active');
                        node.classList.add('search-arrow-active');
                    }
                }
            }
        },

        addAccessoryEvents () {
            $('a.has-arrow', sideBarNav).on('click', this._accessoryClickEvent);
        },

        removeAccessoryEvents () {
            $('a.has-arrow', sideBarNav).off('click', this._accessoryClickEvent);
        },

        _accessoryClickEvent (event) {
            search.clear.setAttribute('hidden', '');
            search.input.value = '';
            searchList.removeAccessoryEvents();
            searchList.showAll(false);

            // current click active path
            let node = event.currentTarget;
            while (node) {
                if (node.matches('#sidebarnav')) {
                    node = null;
                    break;
                } else {
                    if (node.tagName.toLowerCase() === 'li') {
                        node.classList.add('active');
                        node.children[0].setAttribute('aria-expanded', true);
                    } else if (node.tagName.toLowerCase() === 'ul') {
                        node.classList.add('show');
                        node.setAttribute('aria-expanded', true);
                    }
                    node = node.parentElement;
                }
            }
        }
    };

    let insert = false;
    let addEvent = false;

    $(search.input).on('input', function () {
        let value = this.value.trim().toLowerCase();
        let isSearch = false;

        if (value.length >= 2) {
            searchList.hideAll();
            search.keys.forEach((key) => {
                if (key.indexOf(value) !== -1) {
                    searchList.show(search.list[key]);
                    isSearch = true;
                }
            });

            if (!isSearch && !insert) {
                sideBarNav.insertAdjacentHTML('afterbegin', searchList.noFoundElement);
                insert = true;
            } else if (isSearch && insert) {
                sideBarNav.removeChild(sideBarNav.children[0]);
                insert = false;
            }
            if (!addEvent) {
                searchList.addAccessoryEvents();
                addEvent = true;
            }
        } else {
            if (insert) {
                sideBarNav.removeChild(sideBarNav.children[0]);
                insert = false;
            }
            if (addEvent) {
                searchList.removeAccessoryEvents();
                addEvent = false;
            }
            searchList.showAll();
        }

        if (value.length) {
            search.clear.removeAttribute('hidden');
        } else {
            search.clear.setAttribute('hidden', '');
        }
    });
    $('#sidebar-clear-search-input').on('click', () => {
        if (addEvent) {
            searchList.removeAccessoryEvents();
            addEvent = false;
        }
        if (insert) {
            sideBarNav.removeChild(sideBarNav.children[0]);
            insert = false;
        }
        if (search.input.value.trim().length >= 2) {
            searchList.showAll();
        }
        search.clear.setAttribute('hidden', '');
        search.input.value = '';
    });
};

inits.actionsForListItems = function () {
    const $window = $(window);
    const $body = $(document.body);

    function BindActionsList (element) {
        this.rootEl = element;
        this.$rootEl = $(this.rootEl);
        this.uuid = this.rootEl.dataset.controlList || '';
        this.listItems = this.rootEl.querySelectorAll('[data-list-item="' + this.uuid + '"]:not(:disabled)');
        this.selectAllEl = this.rootEl.querySelector('[data-select-all-list]');

        if (!this.selectAllEl && !this.listItems.length) {
            return this;
        }

        if (!this.listItems.length) {
            if (this.selectAllEl) {
                if (this.rootEl.hasAttribute('data-control-list') && !this.rootEl.querySelectorAll('[data-list-item="' + this.uuid + '"]:disabled').length) {
                    this.rootEl.classList.add('control-list-empty');
                } else {
                    this.selectAllEl.setAttribute('disabled', 'true');
                }
            }

            return this;
        }

        if (this.rootEl.hasAttribute('data-control-list')) {
            this.rootEl.querySelectorAll('.dd-item').forEach((item) => {
                item.classList.add('dd-control-item');
            });
        }

        this.$controlPane = $(this.rootEl.querySelector('[data-list-actions]'));
        this.$controlPane.remove();
        $body.append(this.$controlPane);

        const setControlPanePosition = ($inputEl) => {
            if (!$inputEl) {
                return false;
            }

            this.$currentCheckedInput = $inputEl;

            let $parent = this.$currentCheckedInput.parent();
            let position = $parent.offset();

            this.$controlPane.css({
                top: position.top - ($parent.height() / 3) + 1,
                left: position.left + $parent.outerWidth() + 5
            });
        };
        let resizeTimer = null;
        const resizeActionsPosition = () => {
            if (!this.$currentCheckedInput) {
                return false;
            }
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(() => setControlPanePosition(this.$currentCheckedInput), 100);
        };

        $window.on('resize', resizeActionsPosition);
        this.$rootEl
            .on('change', 'input[data-select-all-list]', _selectAllListEvent.bind(this))
            .on('change', 'input[data-list-item]', _changeListItemEvent.bind(this));
        this.$controlPane.on('click', 'button[data-list-action]', _clickListActionEvent.bind(this));

        function _selectAllListEvent (event) {
            this.selectAllEl = $(event.currentTarget);
            this.listItems = this.$rootEl.find('[data-list-item="' + this.uuid + '"]:not(:disabled)');

            if (this.selectAllEl.prop('checked')) {
                this.listItems.prop('checked', true);
                this.$controlPane.addClass('is-shown');
                setControlPanePosition(this.selectAllEl);
            } else {
                this.listItems.prop('checked', false);
                this.$controlPane.removeClass('is-shown');
            }
        }

        function _changeListItemEvent (event) {
            let $input = $(event.currentTarget);
            if ($input.prop('disabled')) {
                return false;
            }

            this.selectAllEl = this.$rootEl.find('input[data-select-all-list="' + this.uuid + '"]');
            this.listItems = this.$rootEl.find('[data-list-item="' + this.uuid + '"]');
            this.inputsChecked = this.$rootEl.find('[data-list-item="' + this.uuid + '"]:checked');

            setControlPanePosition($input);

            if (this.listItems.length === this.inputsChecked.length) {
                this.selectAllEl.prop('checked', true);
                this.selectAllEl.prop('indeterminate', false);
                this.$controlPane.addClass('is-shown');

                return true;
            }

            if (this.inputsChecked.length === 0) {
                this.selectAllEl.prop('checked', false);
                this.selectAllEl.prop('indeterminate', false);
                this.$controlPane.removeClass('is-shown');

                return true;
            }

            this.selectAllEl.prop('indeterminate', true);
            this.selectAllEl.prop('checked', false);
            this.$controlPane.addClass('is-shown');
        }

        function _clickListActionEvent (event) {
            const _this = this;

            let $button = $(event.currentTarget);

            function sendRequest (result) {
                if (!result.value) {
                    $button.prop('disabled', false);
                    return false;
                }

                let route = $button.data('route') || null;
                if (!route) {
                    console.warn('No button action route');
                    return false;
                }

                inits.loader.add(_this.$rootEl.get(0));
                axios.post(route, formData).then((response) => {
                    if (response.data.reload) {
                        window.location.reload();
                    } else {
                        inits.loader.remove(_this.$rootEl.get(0));
                        $button.prop('disabled', false);
                    }
                }).catch(() => {
                    inits.loader.remove(_this.$rootEl.get(0));
                    $button.prop('disabled', false);
                });
            }

            _this.listItems = _this.$rootEl.find('[data-list-item="' + _this.uuid + '"]');

            $button.prop('disabled', true);

            let action = $button.data('list-action');

            const formData = new window.FormData();
            _this.listItems.each(function (i, el) {
                if ((el.type === 'checkbox' || el.type === 'radio') && el.checked) {
                    formData.append(el.name, el.value);
                }
            });

            switch (action) {
                case 'delete':
                    inits.confirmDelete(null, null, $button.data('confirm-text'), null, null, sendRequest);
                    break;
                case 'restore':
                    swal({
                        type: 'question',
                        title: translations.confirmRestore.title,
                        confirmButtonColor: "#007bff",
                        confirmButtonText: translations.confirmRestore.yesText,
                        cancelButtonText: translations.confirmDelete.noText,
                        showCancelButton: true
                    }).then(sendRequest);
                    break;
                default:
                    if (window.massActions && typeof window.massActions[action] === 'function') {
                        window.massActions[action](formData, $button, _this.$rootEl.get(0));
                    }
                    break;
            }
        }

        // methods
        this.defaultState = () => {
            if (this.selectAllEl && this.selectAllEl.length && (this.selectAllEl.prop('checked') || this.selectAllEl.prop('indeterminate'))) {
                this.selectAllEl.prop('checked', false);
                this.selectAllEl.prop('indeterminate', false);

                if (this.listItems.length) {
                    this.listItems.prop('checked', false);
                }

                if (this.$controlPane.length) {
                    this.$controlPane.removeClass('is-shown');
                }
            }

            this.updateItemsList();
        };

        this.updateItemsList = () => {
            this.listItems = this.$rootEl.find('[data-list-item="' + this.uuid + '"]:not(:disabled)');

            if (!this.listItems.length) {
                if (this.selectAllEl) {
                    if (this.rootEl.hasAttribute('data-control-list') && !this.rootEl.querySelectorAll('[data-list-item="' + this.uuid + '"]:disabled').length) {
                        this.rootEl.classList.add('control-list-empty');
                    } else {
                        this.selectAllEl.setAttribute('disabled', 'true');
                    }
                }
            } else {
                if (this.selectAllEl) {
                    if (this.rootEl.hasAttribute('data-control-list') && !this.rootEl.querySelectorAll('[data-list-item="' + this.uuid + '"]:disabled').length) {
                        this.rootEl.classList.remove('control-list-empty');
                    } else {
                        this.selectAllEl.setAttribute('disabled', 'false');
                    }
                }
            }
        };

        this.updatePosition = () => {
            setControlPanePosition(this.$currentCheckedInput);
        };

        // instance
        this.$rootEl.data('ControlListInstance', this);
    }

    $('table, [data-control-list]').each(function (i, element) {
        new BindActionsList(element);
    });
};

inits.hideLangTabs = function () {
    if ($(document.documentElement).data('hide-lang-tabs')) {
        $('.js-lang-tabs').each((i, el) => {
            $(el).css('display', 'none');

            let $tabContent = $(el).next('.tab-content');
            $tabContent.css('border', 'none');
            $tabContent.children().css('padding', 0);
        });
    }
};

inits.tooltip = function () {
    $('[data-toggle="tooltip"]').tooltip({
        container: 'body',
        boundary: 'window'
    });
};

inits.confirmation = function (params = {}) {
    params = $.extend({
        btnOkClass: 'btn-danger',
        title: translations.confirmDelete.title,
        btnOkLabel: translations.confirmDelete.yesText,
        btnCancelLabel: translations.confirmDelete.noText,
    }, params);

    $('[data-toggle="confirmation"]').confirmation(params);
};

inits.confirmDelete = function (el, title, text, confirmText, cancelText, successFunction) {
    let $el = $(el);

    if (!successFunction) {
        successFunction = function (result) {
            if (result.value) {
                if ($el[0].nodeName === 'A') {
                    window.location.href = $el.attr('href');
                } else {
                    $el.closest('form').submit();
                }
            }
        };
    }

    swal({
        type: 'warning',
        title: title || translations.confirmDelete.title,
        text: text || translations.confirmDelete.text,
        confirmButtonColor: "#dc3545",
        confirmButtonText: confirmText || translations.confirmDelete.yesText,
        cancelButtonText: cancelText || translations.confirmDelete.noText,
        showCancelButton: true
    }).then(successFunction);

    return false;
};

inits.colorPicker = function ($selector) {
    let defaults = {
        theme: 'bootstrap',
        control: 'saturation',
        defaultValue: '#007bff'
    };

    $selector = $selector || $('.js-color-picker');
    $selector.each(function (i, el) {
        $(el).minicolors($.extend(true, {}, defaults, $(el).data('minicolors')));
    });
};

inits.alphaColorPicker = function ($selector) {
    let defaults = {
        theme: 'bootstrap',
        control: 'saturation',
        format: 'rgb',
        opacity: true,
        defaultValue: '#007bff'
    };

    $selector = $selector || $('.js-alpha-color-picker');
    $selector.each(function (i, el) {
        $(el).minicolors($.extend(true, {}, defaults, $(el).data('minicolors')));
    });
};

inits.formSubmit = function ($selector) {
    $selector = $selector || $('.js-form-submit');

    $selector.on('click', function (e) {
        e.preventDefault();
        let $this = $(this);
        let confirmText = $this.data('confirm');
        if (confirmText) {
            if (!confirm(confirmText)) {
                return;
            }
        }

        let action = $this.data('action');
        let $form = $this.closest('form');
        if (action && $form) {
            let $formAction = $('#form-action-input');
            if (!$formAction.length) {
                $form.append('<input type="hidden" id="form-action-input" name="form-action" value="' + action + '"/>');
            }
            $formAction.val(action);

            let redirectUrl = $this.data('redirect-url');
            if (redirectUrl) {
                $form.append('<input type="hidden" name="redirect-url" value="' + redirectUrl + '"/>');
            }
            $form.submit();
        }
    });
};

// Clone controls buttons to header
inits.cloneFormControls = function () {
    let $nawWrapper = $('.js-naw-wrapper');
    if ($nawWrapper.length && $('.js-form-controls').length) {
        let $buttons = $('.js-form-controls:first').clone();

        if ($buttons.children().length > 4) {
            $(document.body).addClass('many-header-button');
        }

        $buttons.removeClass('js-form-controls');
        $buttons.find('.btn').addClass('btn-sm');
        $buttons.find('.js-form-submit').on('click', function () {
            $('.js-form-controls:first .js-form-submit').eq($(this).index()).trigger('click');
        });
        $nawWrapper.html($buttons);
    }
};

inits.scrollSidebar = function ($selector) {
    $selector = $selector || $(".scroll-sidebar");

    $selector.slimScroll({
        position: "left",
        size: "5px",
        height: "100%",
        color: "#dcdcdc"
    });
};

inits.sidebarMenu = function ($selector) {
    $selector = $selector || $("#sidebarnav");
    $selector.metisMenu({collapseInClass: 'show'});
};

inits.sidebarToggle = function () {
    let $body = $(document.body);
    let calcSidebar = function () {
        if ((window.innerWidth || this.screen.width) < 1170) {
            $body.addClass("mini-sidebar");
            $(".navbar-brand span").hide();
            $(".scroll-sidebar, .slimScrollDiv").css("overflow-x", "visible").parent().css("overflow", "visible");
            $(".sidebartoggler i").addClass("ti-menu");
        } else {
            $body.removeClass("mini-sidebar");
            $(".navbar-brand span").show();
        }

        let height = (window.innerHeight || this.screen.height) - 71;
        if (height < 1) {
            height = 1;
        }
        if (height > 70) {
            $(".page-wrapper").css("min-height", height + "px");
        }
    };

    $(window).on("resize", calcSidebar);

    $(".sidebartoggler").on("click", function () {
        let $sidebar =  $(".scroll-sidebar, .slimScrollDiv");
        let $navbar =  $(".navbar-brand span");

        if ($body.hasClass("mini-sidebar")) {
            $body.trigger("resize");
            $sidebar.css("overflow", "hidden").parent().css("overflow", "visible");
            $body.removeClass("mini-sidebar");
            $navbar.show();
        } else {
            $body.trigger("resize");
            $sidebar.css("overflow-x", "visible").parent().css("overflow", "visible");
            $body.addClass("mini-sidebar");
            $navbar.hide();
        }
    });

    $(".fix-header .header").stick_in_parent();

    $(".nav-toggler").click(function () {
        $body.toggleClass("show-sidebar");
        $(".nav-toggler i").toggleClass("mdi mdi-menu").addClass("mdi mdi-close");
    });

    calcSidebar();
};

inits.preloader = {
    selector: '.preloader',
    start: function () {
        $(this.selector).fadeIn();
    },
    stop: function () {
        $(this.selector).fadeOut();
    }
};

inits.datePicker = function ($selector, options = {}) {
    $selector = $selector || $('.js-datepicker');

    options = $.extend({
        language: $(document.documentElement).attr('lang'),
        format: 'dd.mm.yyyy',
        autoclose: true
    }, options);

    $selector.datepicker(options);
    $selector.data('autocomplete', 'off').prop('autocomplete', 'off');

    if ($selector.data('start-date')) {
        $selector.datepicker('setStartDate', $selector.data('start-date'));
    }

    if ($selector.data('end-date')) {
        $selector.datepicker('setEndDate', $selector.data('end-date'));
    }
};

inits.dateTimeRangePicker = function ($selector) {
    $selector = $selector || $('.js-datetimerangepicker');

    let options = {
        timePicker: true,
        timePicker24Hour: true,
        timePickerSeconds: false,
        autoUpdateInput: false,
        locale: {
            format: 'DD.MM.YYYY HH:mm',
            "separator": " - ",
            "applyLabel": window.translations.daterangepicker.applyLabel,
            "cancelLabel": window.translations.daterangepicker.cancelLabel,
            "fromLabel": window.translations.daterangepicker.fromLabel,
            "toLabel": window.translations.daterangepicker.toLabel,
            "customRangeLabel": "Custom",
            "firstDay": 1
        }
    };

    $selector.daterangepicker(options, function (start_date, end_date) {
        $(this.element).val(start_date.format(this.locale.format) + ' - ' + end_date.format(this.locale.format));
    });
};

inits.timeRangePicker = function ($selector) {
    $selector = $selector || $('.js-timerangepicker');

    let options = {
        timePicker: true,
        timePicker24Hour: true,
        timePickerIncrement: 1,
        timePickerSeconds: false,
        locale: {
            format: 'HH:mm',
            "separator": " - ",
            "applyLabel": window.translations.daterangepicker.applyLabel,
            "cancelLabel": window.translations.daterangepicker.cancelLabel,
            "fromLabel": window.translations.daterangepicker.fromLabel,
            "toLabel": window.translations.daterangepicker.toLabel,
            "customRangeLabel": window.translations.daterangepicker.customRangeLabel,
        }
    };

    $selector.daterangepicker(options, function (start_date, end_date) {
        $(this.element).val(start_date.format('HH:mm') + ' - ' + end_date.format('HH:mm'));
    }).on('show.daterangepicker', function (ev, picker) {
        picker.container.find(".calendar-table").hide();
    });
};

inits.dateRangePicker = function ($selector) {
    $selector = $selector || $('.js-daterange');

    let options = {
        timePicker: false,
        locale: {
            "format": "DD.MM",
            "separator": " - ",
            "applyLabel": window.translations.daterangepicker.applyLabel,
            "cancelLabel": window.translations.daterangepicker.cancelLabel,
            "fromLabel": window.translations.daterangepicker.fromLabel,
            "toLabel": window.translations.daterangepicker.toLabel,
            "customRangeLabel": window.translations.daterangepicker.customRangeLabel,
        }
    };

    $selector.daterangepicker(options, function (start_date, end_date) {
        $(this.element).val(start_date.format(this.locale.format) + ' - ' + end_date.format(this.locale.format));
    }).on('show.daterange', function (ev, picker) {
        picker.container.find(".calendar-table").hide();
    });
};

inits.tinyMCE = function (selector = '.js-wysiwyg') {
    let language = $(document.documentElement).attr('lang') || 'en';
    if (language === 'ua') {
        language = 'uk';
    }

    $.each($(selector), (i, el) => {
        let $el = $(el);
        $el.tinymce($.extend(window.tinyConfig || {}, {
            selector: selector,
            language: language,
            default_language: language,
            height: $el.height()
        }));
    });
};

inits.tinyWysiwyg = function (selector = '.js-tiny-wysiwyg') {
    let language = $(document.documentElement).attr('lang') || 'en';
    if (language === 'ua') {
        language = 'uk';
    }

    $.each($(selector), (i, el) => {
        let $el = $(el);
        $el.tinymce({
            selector: selector,
            menubar: false,
            plugins: [],
            toolbar1: "undo redo pastetext | bold italic underline",
            body_class: "wysiwyg",
            toolbar: "preview",
            mobile: {
                theme: 'mobile',
                height: "200",
                plugins: ['autosave', 'lists', 'autolink'],
                toolbar: ['undo', 'bold', 'italic']
            },
            language: language,
            default_language: language,
            height: $el.height()
        });
    });
};

inits.statusSwitcher = function ($el) {
    $el = $el || $('.js-status-switcher');

    if (!$el.length) {
        return;
    }

    let model = $el.data('model');

    if (!model) {
        console.warn('Not specified model for status switcher');
        return;
    }

    function changeStyle ($button, published) {
        if (published) {
            $button.removeClass('btn-outline-secondary').addClass('btn-success');
        } else {
            $button.removeClass('btn-success').addClass('btn-outline-secondary');
        }
    }

    $el.on('click', 'button', function (e) {
        e.preventDefault();

        let $this = $(this);
        $this.prop('disabled', true);

        let id = $this.data('id'),
            status = +$this.data('status'),
            locale = $this.data('locale'),
            field = $this.data('field');

        axios.post(route('admin.ajax.change-status'), {
            id: id,
            status: status,
            field: field,
            model: model,
            locale: locale,
            text_on: $el.data('text-on'),
            text_off: $el.data('text-off'),
            model_request: $el.data('model-request')
        }).then(function (response) {
            $this.prop('disabled', false);
            if (response.data.success) {
                changeStyle($this, response.data.status);
                $this.data('status', response.data.status);
                // TODO don`t use this logic
                if (!locale) {
                    if ($el.data('type') === 'small') {
                        $this.html(response.data.status
                            ? '<i class="fa fa-check-square-o"></i>'
                            : '<i class="fa fa-square-o"></i>');
                        $this.tooltip('hide').tooltip('dispose').attr('title', response.data.button_text).tooltip();
                    } else {
                        $this.html(response.data.button_text);
                    }
                }
            }
        }).catch(function (error) {
            $this.prop('disabled', false);
        });
    });
};

inits.select2 = function ($el) {
    $el = $el || $('.js-select2');

    $el.each(function (i, currentEl) {
        let $currentEl = $(currentEl);

        let config = {
            width: '100%'
        };

        try {
            let templateKey = $currentEl.data('template');
            if (templateKey && window.select2Templates.hasOwnProperty(templateKey)) {
                config['templateResult'] = function (state, option) {
                    if (!state.id) {
                        return state.text;
                    }

                    return window.select2Templates[templateKey](state, option);
                };

                config['templateSelection'] = function (state, option) {
                    if (!state.id) {
                        return state.text;
                    }

                    $.each(state.data, function(key, value) {
                        state.element.dataset[key] = value;
                    });

                    return window.select2Templates[templateKey](state, option);
                };
            }
        } catch (e) {
            console.error(e);
        }

        $currentEl.select2(config).on("select2:close", function () {
            $(this).valid();
        });
    });
};

inits.ajaxSelect2 = function ($el) {
    $el = $el || $('.js-ajax-select2');

    $el.each(function (i, currentEl) {
        let $currentEl = $(currentEl);

        let config = {
            width: '100%',
            ajax: {
                url: $currentEl.data('url'),
                dataType: 'JSON'
            }
        };

        try {
            let templateKey = $currentEl.data('template');
            if (templateKey && window.select2Templates.hasOwnProperty(templateKey)) {
                config['templateResult'] = function (state, option) {
                    if (!state.id) {
                        return state.text;
                    }

                    return window.select2Templates[templateKey](state, option);
                };

                config['templateSelection'] = function (state, option) {
                    if (!state.id) {
                        return state.text;
                    }

                    $.each(state.data, function(key, value) {
                        state.element.dataset[key] = value;
                    });

                    return window.select2Templates[templateKey](state, option);
                };
            }
        } catch (e) {
            console.error(e);
        }

        $currentEl.select2(config).on("select2:close", function () {
            $(this).valid();
        });
    });
};
console.log('dd');
inits.multipleInputs = function () {
    let $wrapper = $('.js-multiple-input-wrapper');
    let templateSelector = '.js-multiple-input-template';
    let itemSelector = '.js-multiple-input-item';
    let deleteSelector = '.js-multiple-input-item .js-remove-row';
    let buttonAddNewRowSelector = '.js-add-new-row';
    let listSelector = '.js-multiple-input-list';

    function toggleEmptyMessage ($listSelector) {
        if ($listSelector.find(itemSelector).length) {
            $listSelector.find('.js-empty').css('display', 'none');
        } else {
            $listSelector.find('.js-empty').css('display', 'block');
        }
    }

    $wrapper.each(function () {
        let $this = $(this);

        $this.find(listSelector).sortable({
            handle: '.drag-cursor'
        }).disableSelection();

        // Add
        $this.on('click', buttonAddNewRowSelector, function () {
            let $newEl = $this.find(templateSelector + ' ' + itemSelector).clone();
            let $input = $newEl.find('.js-input');
            $input.attr('name', $input.data('name')).removeAttr('data-name');

            $newEl.appendTo($this.find(listSelector));
            $input.focus();
            inits.confirmation();
            toggleEmptyMessage($this.find(listSelector));
        });

        // Remove
        $this.on('click', deleteSelector, function () {
            $(this).closest(itemSelector).remove();
            toggleEmptyMessage($this.find(listSelector));
        });
    });
};

inits.dropDownload = function (selector = '.dropDownload') {
    let $el = $(selector);

    // Sortable
    $el.sortable({
        connectWith: ".loadedBlock",
        handle: ".loadedDrag",
        cancel: '.loadedControl',
        placeholder: "loadedBlockPlaceholder",
        update: function () {
            let positions = [];
            $(this).find('.loadedBlock').each(function () {
                positions.push($(this).data('image'));
            });
            let options = $(this).data('options');
            let params = options.params;
            params.positions = positions;

            axios.post(options.urls.sort, params);
        }
    });

    // Mark as default
    $el.on('click', '.loadedCover .btn.btn-success', function () {
        let it = $(this),
            itP = it.closest('.loadedBlock'),
            id = itP.data('image'),
            $dropDownload = it.closest(selector);
        let options = $dropDownload.data('options');
        let params = options.params;
        params.id = id;

        axios.post(options.urls.setAsDefault, params);
    });

    let $dropModule = $('.dropModule');

    $dropModule.on('click', '.checkAll', function () {
        let block = $(this).closest('.loadedBox').find(selector).find('.loadedBlock');
        block.addClass('chk');
        block.find('.loadedCheck').find('input').prop('checked', true);
        $('.loadedBox .uncheckAll, .loadedBox .removeCheck').fadeIn(300);
    });

    $dropModule.on('click', '.uncheckAll', function () {
        let block = $(this).closest('.loadedBox').find(selector).find('.loadedBlock');
        block.removeClass('chk');
        block.find('.loadedCheck').find('input').prop('checked', false);
        $('.loadedBox .uncheckAll, .loadedBox .removeCheck').fadeOut(300);
    });

    $el.on('click', '.loadedCheck label', function () {
        if ($(this).children('input').is(':checked')) {
            $(this).closest('.loadedBlock').addClass('chk');
        } else {
            $(this).closest('.loadedBlock').removeClass('chk');
        }
        if ($('.chk').length > 0) {
            $('.loadedBox .uncheckAll, .loadedBox .removeCheck').fadeIn(300);
        } else {
            $('.loadedBox .uncheckAll, .loadedBox .removeCheck').fadeOut(300);
        }
    });
};

inits.map = function (selector = '.js-map') {
    function MapInitializer (el) {
        let $that = this;

        this.$el = $(el);
        this.$mapContainer = null;
        this.gMap = null;
        this.geocoder = null;
        this.input = {
            el: null,
            set value (value) {
                value.zoom = $that.gMap.zoom;
                this.el.val(JSON.stringify(value));
            },
            get value () {
                return JSON.parse(this.el.val());
            }
        };

        this.input.el = this.$el.find('.js-input');
        this.$mapContainer = this.$el.find('.js-map-container');

        // Functions
        this.startMultiple = function () {
            let markersList = [];

            // Restore old markers
            const oldMarkers = this.input.value.markers || [];
            oldMarkers.forEach(function (coordinates) {
                _createMarker(coordinates);
            });

            // Add marker
            window.google.maps.event.addListener(this.gMap, 'click', function (e) {
                _createMarker(e.latLng);
                _setMultiMarkersInputValue();
            });

            window.google.maps.event.addListener(this.gMap, 'dragend', _setMultiMarkersInputValue);
            window.google.maps.event.addListener(this.gMap, 'zoom_changed', _setMultiMarkersInputValue);

            function _createMarker (coordinates) {
                let marker = new window.google.maps.Marker({position: coordinates, draggable: true});

                marker.setMap($that.gMap);
                markersList.push(marker);
                window.google.maps.event.addListener(marker, 'dragend', _setMultiMarkersInputValue);

                // Delete marker
                window.google.maps.event.addListener(marker, 'click', function () {
                    markersList.forEach(function (m, i) {
                        if (m === marker) {
                            delete markersList[i];
                            return false;
                        }
                    });
                    marker.setMap(null);
                    _setMultiMarkersInputValue();
                });
            }

            function _setMultiMarkersInputValue () {
                let data = {
                    markers: [],
                    center: $that.gMap.getCenter().toJSON()
                };

                markersList.forEach(function (marker) {
                    data.markers.push(marker.getPosition().toJSON());
                });

                $that.input.value = data;
            }
        };

        this.startSingle = function () {
            let position = this.input.value ? this.input.value.marker : {lat: 46.9648674, lng: 31.973737};
            const marker = new window.google.maps.Marker({position: position, draggable: true});
            marker.setMap(this.gMap);

            this.geocoder = new google.maps.Geocoder();

            window.google.maps.event.addListener(marker, 'dragend', _setSingleMarkerInputValue);
            window.google.maps.event.addListener(this.gMap, 'click', function (e) {
                marker.setPosition(e.latLng);
                _setSingleMarkerInputValue();
            });

            window.google.maps.event.addListener(this.gMap, 'zoom_changed', _setSingleMarkerInputValue);
            window.google.maps.event.addListener(this.gMap, 'dragend', _setSingleMarkerInputValue);

            function _setSingleMarkerInputValue () {
                $that.input.value = {
                    marker: marker.getPosition() ? marker.getPosition().toJSON() : {},
                    center: $that.gMap.getCenter().toJSON()
                };
            }

            this.$el.find('.js-search-input').autocomplete({
                source: function (request, response) {
                    $that.geocoder.geocode({'address': request.term}, function (results, status) {
                        response($.map(results, function (item) {
                            return {
                                label: item.formatted_address,
                                value: item.formatted_address,
                                latitude: item.geometry.location.lat(),
                                longitude: item.geometry.location.lng()
                            }
                        }));
                    })
                },
                select: function (event, ui) {
                    let position = {lat: ui.item.latitude, lng: ui.item.longitude};
                    marker.setPosition(position);
                    $that.gMap.setCenter(position);
                    _setSingleMarkerInputValue();
                }
            });
        };

        this._getMapConfig = function () {
            return $.extend(
                {zoom: 16, center: {lat: 46.9648674, lng: 31.973737}},
                this.$mapContainer.data('config'),
                this.input.value
            );
        };

        // Start
        this.gMap = new window.google.maps.Map(this.$mapContainer[0], this._getMapConfig());

        if (this.$el.data('multiple')) {
            this.startMultiple();
        } else {
            this.startSingle();
        }
    }

    $(selector).each(function (i, el) {
        new MapInitializer(el);
    });
};

inits.sortable = function ($el) {
    $el = $el || $('.js-sortable');

    let options = {
        helper: function (e, ui) {
            ui.children().each(function () {
                $(this).width($(this).width());
            });
            return ui;
        },
        handle: '.js-sortable-handle'
    };

    let params = $el.data('params');
    if (params) {
        options.update = function () {
            let positions = [];
            $(this).find('tr').each(function () {
                positions.push($(this).data('id'));
            });
            params.positions = positions;
            axios.post(route('admin.ajax.update-sort'), params);
        };
    }

    $el.sortable(options).disableSelection();
};

inits.slimScroll = function () {
    $(".js-slim-scroll").slimScroll({
        position: "right",
        size: "5px",
        color: "#dcdcdc"
    });
};

inits.nestable = function (selector) {
    let $el = $(selector || '.js-nestable');

    $el.each(function (index, el) {
        let $el = $(el);
        let prevControlItemsInstance = null;
        let options = {
            maxDepth: $el.data('settings-depth') || 5,
            callback: function ($root, $item, pos) {
                let data = $root.data('params') || {};
                let controlItemsInstance = $root.data('ControlListInstance');

                if (prevControlItemsInstance) {
                    prevControlItemsInstance.updateItemsList();
                }

                if (controlItemsInstance) {
                    controlItemsInstance.defaultState();
                }

                data.items = $root.nestable('serialize');
                axios.post(route('admin.ajax.update-nestable-sort'), data);
            },
            onDragStart ($root, $item, pos) {
                prevControlItemsInstance = $root.data('ControlListInstance');

                if (prevControlItemsInstance) {
                    prevControlItemsInstance.defaultState();
                }
            }
        };

        $el.nestable(options);
    });
};

inits.checkChild = function (selector = '.js-check-child', wrapper = '.check-wrapper') {
    let $el = $(selector);

    $el.on('change', function () {
        let $chidlrens = $(this).closest(wrapper).find('input[type="checkbox"]').not(this);
        $chidlrens.prop('checked', $(this).is(':checked'));
    });

    $el.each(function (i, item) {
        if ($(item).prop('checked') === false && $(item).closest(wrapper).find('input[type="checkbox"][value!=""]:not(:checked)').not(item).length === 0) {
            $(item).prop('checked', true);
        }
    });
};

inits.simpleAjaxFormSubmit = function (selector = '.js-ajax-form') {
    let $el = $(selector);

    $el.off('submit').on('submit', function (e) {
        inits.loader.add($el.get(0));

        e.preventDefault();

        let $this = $(this);

        axios({
            method: $this.attr('method'),
            url: $this.attr('action'),
            data: $this.serialize(),
        }).then(function (response) {
            if (response.data.action === 'close-modal') {
                $('.modal').modal('hide');
            }
            if (response.data.reload) {
                window.location.reload();
            } else if (response.data.redirect) {
                window.location.href = response.data.redirect;
            }
            inits.loader.remove($el.get(0));
        }).catch(() => {
            inits.loader.remove($el.get(0));
        });
    });
};

inits.forceValid = function (selector = '.js-force-valid') {
    $(selector).valid();
};

inits.dataTable = function (selector = '.js-data-table') {
    $(selector).DataTable({
        'lengthChange': false,
        'info': false,
        language: {
            url: '/vendor/cms/core/plugins/datatables/i18n/' + (document.documentElement.lang) + '.json'
        }
    });
};

inits.generateSlug = function (selector) {
    let $components = $(selector || '.js-slug-generator');

    $components.each(function () {
        let $component = $(this);

        let $source = $($component.data('source'));
        let $target = $component.find('input');
        if ($source.length === 0) {
            console.warn('Cant`t find sluggable source [' + $component.data('source') + ']');
            return;
        }

        let handler = () => {
            axios.post(route('admin.ajax.generate-slug'), {slug: $source.val()})
                .then(function (response) {
                    if (response.data.success) {
                        $target.val(response.data.slug);
                    }
                });
        };

        $component.find('button').on('click', handler);

        if ($target.hasClass('js-live-slug')) {
            $source.on('change', handler);
        }
    });
};

inits.multiSelect = function (selector = '.js-multi-select') {
	const needSort = (select) => select.hasAttribute('data-multi-sort');
	const getSortedValues = ($sortableUl, isOptgroup = false) => {
		return isOptgroup ?
			$.map($sortableUl.find("li.ms-optgroup-label:visible"), (li) => $(li).find('span').text().toLowerCase())
			:
			$.map($sortableUl.children(":visible"), (li) => $(li).data('ms-value'));
	};
	const sortSelect = ($select, values = [], isOptgroup = false) => {
		if (!values.length) return false;

		let sortedItems = [];

		if (isOptgroup) {
			values.forEach((value) => {
				$select.find("optgroup").each((i, optgroup) => {
					let $optgroup = $(optgroup);

					if ($optgroup.attr('label').toLowerCase() === value) {
						sortedItems.push(optgroup);
						$optgroup.remove();
						return false;
					}
				});
			});

			if (sortedItems.length) {
				$select.prepend(sortedItems);
				sortedItems = null;
			}
		} else {
			let $options = $select.find('option').prop('selected', false);

			values.forEach((value) => {
				$options.each((i, option) => {
					let $option = $(option);
					if ($option.val() === value) {
						option.selected = true;
						sortedItems.push(option);
						$option.remove();
						return false;
					}
				});
			});

			if (sortedItems.length) {
				$select.prepend(sortedItems);
				sortedItems = null;
			}

			$options = null;
		}
	};

	$(selector).each(function () {
		if ($(this).prop('multiple')) {
			let isOptgroup = false;

			$(this).multiSelect({
				keepOrder: true,
				selectableHeader: "<input type='text' class='search-input form-control' autocomplete='off'>",
				selectionHeader: "<input type='text' class='search-input form-control' autocomplete='off'>",
				afterInit: function () {
					let that = this,
						$selectableSearch = that.$selectableUl.prev(),
						$selectionSearch = that.$selectionUl.prev(),
						selectableSearchString = '#' + that.$container.attr('id') + ' .ms-elem-selectable:not(.ms-selected)',
						selectionSearchString = '#' + that.$container.attr('id') + ' .ms-elem-selection.ms-selected';

					that.qs1 = $selectableSearch.quicksearch(selectableSearchString, {
						hide: function () {
							$(this).hide();
							if ($(this).parent().find('.ms-elem-selectable').css('display') === 'none') {
								$(this).parent().children('.ms-optgroup-label').hide();
							}
						},
						show: function () {
							$(this).show();
							if ($(this).parent().find('.ms-elem-selectable').is(':visible')) {
								$(this).parent().children('.ms-optgroup-label').show();
							}
						}
					})
					.on('keydown', function (e) {
						if (e.which === 40) {
							that.$selectableUl.focus();
							return false;
						}
					});

					that.qs2 = $selectionSearch.quicksearch(selectionSearchString, {
						hide: function () {
							$(this).hide();
							if ($(this).parent().find('.ms-elem-selection').css('display') === 'none') {
								$(this).parent().children('.ms-optgroup-label').hide();
							}
						},
						show: function () {
							$(this).show();
							if ($(this).parent().find('.ms-elem-selection').is(':visible')) {
								$(this).parent().children('.ms-optgroup-label').show();
							}
						}
					})
					.on('keydown', function (e) {
						if (e.which === 40) {
							that.$selectionUl.focus();
							return false;
						}
					});

					// sortable
					if (needSort(that.$element.get(0))) {
						isOptgroup = that.$selectionUl.children('.ms-optgroup-container').length > 0;

						if (isOptgroup) {
							that.$selectionUl.find('li.ms-optgroup-label').each(function () {
								$(this).prepend('<i class="fa fa-arrows"></i>');
							});
						} else {
							that.$selectionUl.children().each(function () {
								$(this).prepend('<i class="fa fa-arrows"></i>');
							});
						}
						that.$selectionUl.sortable({
							helper: (event, ui) => {
								ui.children().each(function () {
									$(this).width($(this).width());
								});
								return ui;
							},
							update: () => {
								sortSelect(this.$element, getSortedValues(this.$selectionUl, isOptgroup), isOptgroup);
							}
						});
					}
				},
				afterSelect: function () {
					if (needSort(this.$element.get(0))) {
						sortSelect(this.$element, getSortedValues(this.$selectionUl, isOptgroup), isOptgroup);
					}
					this.qs1.cache();
					this.qs2.cache();
				},
				afterDeselect: function () {
					if (needSort(this.$element.get(0))) {
						sortSelect(this.$element, getSortedValues(this.$selectionUl, isOptgroup), isOptgroup);
					}
					this.qs1.cache();
					this.qs2.cache();
				}
			});
		}
	});
};

inits.perPage = function (selector = '.js-select-per-page') {
    const $el = $(selector);
    if (!$el) {
        return;
    }

    $el.on('change', function () {
        let url = $el.data('url');

        let joinSymbol = url.includes('?') ? '&' : '?';

        window.location.href = url + joinSymbol + $el.attr('name') + '=' + $el.val();
    });
};

export default inits;
