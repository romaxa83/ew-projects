/******/ (function(modules) { // webpackBootstrap
/******/ 	// The module cache
/******/ 	var installedModules = {};
/******/
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/
/******/ 		// Check if module is in cache
/******/ 		if(installedModules[moduleId]) {
/******/ 			return installedModules[moduleId].exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = installedModules[moduleId] = {
/******/ 			i: moduleId,
/******/ 			l: false,
/******/ 			exports: {}
/******/ 		};
/******/
/******/ 		// Execute the module function
/******/ 		modules[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/
/******/ 		// Flag the module as loaded
/******/ 		module.l = true;
/******/
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/
/******/
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = modules;
/******/
/******/ 	// expose the module cache
/******/ 	__webpack_require__.c = installedModules;
/******/
/******/ 	// define getter function for harmony exports
/******/ 	__webpack_require__.d = function(exports, name, getter) {
/******/ 		if(!__webpack_require__.o(exports, name)) {
/******/ 			Object.defineProperty(exports, name, { enumerable: true, get: getter });
/******/ 		}
/******/ 	};
/******/
/******/ 	// define __esModule on exports
/******/ 	__webpack_require__.r = function(exports) {
/******/ 		if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 			Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 		}
/******/ 		Object.defineProperty(exports, '__esModule', { value: true });
/******/ 	};
/******/
/******/ 	// create a fake namespace object
/******/ 	// mode & 1: value is a module id, require it
/******/ 	// mode & 2: merge all properties of value into the ns
/******/ 	// mode & 4: return value when already ns object
/******/ 	// mode & 8|1: behave like require
/******/ 	__webpack_require__.t = function(value, mode) {
/******/ 		if(mode & 1) value = __webpack_require__(value);
/******/ 		if(mode & 8) return value;
/******/ 		if((mode & 4) && typeof value === 'object' && value && value.__esModule) return value;
/******/ 		var ns = Object.create(null);
/******/ 		__webpack_require__.r(ns);
/******/ 		Object.defineProperty(ns, 'default', { enumerable: true, value: value });
/******/ 		if(mode & 2 && typeof value != 'string') for(var key in value) __webpack_require__.d(ns, key, function(key) { return value[key]; }.bind(null, key));
/******/ 		return ns;
/******/ 	};
/******/
/******/ 	// getDefaultExport function for compatibility with non-harmony modules
/******/ 	__webpack_require__.n = function(module) {
/******/ 		var getter = module && module.__esModule ?
/******/ 			function getDefault() { return module['default']; } :
/******/ 			function getModuleExports() { return module; };
/******/ 		__webpack_require__.d(getter, 'a', getter);
/******/ 		return getter;
/******/ 	};
/******/
/******/ 	// Object.prototype.hasOwnProperty.call
/******/ 	__webpack_require__.o = function(object, property) { return Object.prototype.hasOwnProperty.call(object, property); };
/******/
/******/ 	// __webpack_public_path__
/******/ 	__webpack_require__.p = "/";
/******/
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = 0);
/******/ })
/************************************************************************/
/******/ ({

/***/ "./resources/assets/src/css/less/style.less":
/*!**************************************************!*\
  !*** ./resources/assets/src/css/less/style.less ***!
  \**************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

// removed by extract-text-webpack-plugin

/***/ }),

/***/ "./resources/assets/src/js/entry.js":
/*!******************************************!*\
  !*** ./resources/assets/src/js/entry.js ***!
  \******************************************/
/*! no exports provided */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _plugins_sweetalert2_sweetalert2_all__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./../plugins/sweetalert2/sweetalert2.all */ "./resources/assets/src/plugins/sweetalert2/sweetalert2.all.js");
/* harmony import */ var _plugins_sweetalert2_sweetalert2_all__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_plugins_sweetalert2_sweetalert2_all__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _inits__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./inits */ "./resources/assets/src/js/inits.js");


window.swal = _plugins_sweetalert2_sweetalert2_all__WEBPACK_IMPORTED_MODULE_0___default.a;
window.toast = _plugins_sweetalert2_sweetalert2_all__WEBPACK_IMPORTED_MODULE_0___default.a.mixin({
  toast: true,
  position: 'top-end',
  showConfirmButton: true
});
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest'; // Handle error message

window.axios.interceptors.response.use(function (response) {
  if (response.data.message) {
    if (response.data.success === true) {
      toast({
        type: "success",
        title: response.data.message,
        timer: 5000
      });
    } else {
      toast({
        type: "error",
        title: response.data.message
      });
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

      toast({
        type: "error",
        title: error.response.data.message,
        html: text
      });
    } else {
      toast({
        type: "error",
        title: "Server error"
      });
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
    headers: {
      'X-CSRF-TOKEN': token.content
    }
  });
} else {
  console.error('CSRF token not found: https://laravel.com/docs/csrf#csrf-x-csrf-token');
} // Call inits


$(document).ready(function () {
  var $body = $(document.body);
  moment.locale(document.documentElement.lang);
  _inits__WEBPACK_IMPORTED_MODULE_1__["default"].scrollSidebar();
  _inits__WEBPACK_IMPORTED_MODULE_1__["default"].sidebarToggle();
  _inits__WEBPACK_IMPORTED_MODULE_1__["default"].sidebarSearch();
  _inits__WEBPACK_IMPORTED_MODULE_1__["default"].slimScroll();
  _inits__WEBPACK_IMPORTED_MODULE_1__["default"].actionsForListItems();
  _inits__WEBPACK_IMPORTED_MODULE_1__["default"].preloader.stop();
  _inits__WEBPACK_IMPORTED_MODULE_1__["default"].sidebarMenu();
  _inits__WEBPACK_IMPORTED_MODULE_1__["default"].formSubmit();
  _inits__WEBPACK_IMPORTED_MODULE_1__["default"].cloneFormControls();
  window.confirmDelete = _inits__WEBPACK_IMPORTED_MODULE_1__["default"].confirmDelete;
  _inits__WEBPACK_IMPORTED_MODULE_1__["default"].tooltip();
  _inits__WEBPACK_IMPORTED_MODULE_1__["default"].confirmation();
  _inits__WEBPACK_IMPORTED_MODULE_1__["default"].datePicker();
  _inits__WEBPACK_IMPORTED_MODULE_1__["default"].dateTimePicker();
  _inits__WEBPACK_IMPORTED_MODULE_1__["default"].dateTimeRangePicker();
  _inits__WEBPACK_IMPORTED_MODULE_1__["default"].timeRangePicker();
  _inits__WEBPACK_IMPORTED_MODULE_1__["default"].dateRangePicker();
  _inits__WEBPACK_IMPORTED_MODULE_1__["default"].tinyMCE();
  _inits__WEBPACK_IMPORTED_MODULE_1__["default"].tinyWysiwyg();
  _inits__WEBPACK_IMPORTED_MODULE_1__["default"].statusSwitcher();
  _inits__WEBPACK_IMPORTED_MODULE_1__["default"].select2();
  _inits__WEBPACK_IMPORTED_MODULE_1__["default"].ajaxSelect2();
  _inits__WEBPACK_IMPORTED_MODULE_1__["default"].multipleInputs();
  _inits__WEBPACK_IMPORTED_MODULE_1__["default"].dropDownload();
  _inits__WEBPACK_IMPORTED_MODULE_1__["default"].attachableUploader();
  _inits__WEBPACK_IMPORTED_MODULE_1__["default"].sortable();
  _inits__WEBPACK_IMPORTED_MODULE_1__["default"].nestable();
  _inits__WEBPACK_IMPORTED_MODULE_1__["default"].checkChild();
  _inits__WEBPACK_IMPORTED_MODULE_1__["default"].simpleAjaxFormSubmit();
  _inits__WEBPACK_IMPORTED_MODULE_1__["default"].dataTable();
  _inits__WEBPACK_IMPORTED_MODULE_1__["default"].multiSelect();
  _inits__WEBPACK_IMPORTED_MODULE_1__["default"].colorPicker();
  _inits__WEBPACK_IMPORTED_MODULE_1__["default"].alphaColorPicker();
  _inits__WEBPACK_IMPORTED_MODULE_1__["default"].generateSlug();
  _inits__WEBPACK_IMPORTED_MODULE_1__["default"].perPage();
  _inits__WEBPACK_IMPORTED_MODULE_1__["default"].googleMap();
  _inits__WEBPACK_IMPORTED_MODULE_1__["default"].yandexMap();
  _inits__WEBPACK_IMPORTED_MODULE_1__["default"].copyLangFields();
  $('.dropdown-toggle').dropdown();
  $('.js-mark-notifications-as-read').click(function (event) {
    event.preventDefault();
    var $this = $(this);
    window.axios.post(route('admin.mark-notifications-as-read')).then(function (response) {
      if (response.data.success) {
        var $notify = $this.closest('.nav-item').find('.notify');

        if ($notify.length) {
          $notify.remove();
        }
      }
    });
  });
  $('.js-mark-notification-as-read').click(function (event) {
    event.preventDefault();
    var $this = $(this);
    window.axios.post(route('admin.mark-notification-as-read', $this.data('notification-id'))).then(function () {
      window.location.href = $this.attr('href');
    })["catch"](function () {
      window.location.href = $this.attr('href');
    });
  });
  $(document).on('click', '[data-toggle="modal"]', function () {
    var remote = $(this).data('remote');

    if (remote) {
      $($(this).data("target") + ' .modal-content').load(remote);
    }
  });
  $('body').on('hidden.bs.modal', '.modal', function () {
    if ($(this).data('clear-body')) {
      $(this).removeData('bs.modal').find('.modal-body').html('');
    }
  });
  $body.on('shown.bs.modal', '.modal', function () {
    _inits__WEBPACK_IMPORTED_MODULE_1__["default"].hideLangTabs();
    _inits__WEBPACK_IMPORTED_MODULE_1__["default"].simpleAjaxFormSubmit();
  });
});

/***/ }),

/***/ "./resources/assets/src/js/inits.js":
/*!******************************************!*\
  !*** ./resources/assets/src/js/inits.js ***!
  \******************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _maps_yandex_YandexMultipleMarkersMap__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./maps/yandex/YandexMultipleMarkersMap */ "./resources/assets/src/js/maps/yandex/YandexMultipleMarkersMap.js");
/* harmony import */ var _maps_yandex_YandexSingleMarkerMap__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./maps/yandex/YandexSingleMarkerMap */ "./resources/assets/src/js/maps/yandex/YandexSingleMarkerMap.js");
/* harmony import */ var _maps_google_GoogleMultipleMarkersMap__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./maps/google/GoogleMultipleMarkersMap */ "./resources/assets/src/js/maps/google/GoogleMultipleMarkersMap.js");
/* harmony import */ var _maps_google_GoogleSingleMarkerMap__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./maps/google/GoogleSingleMarkerMap */ "./resources/assets/src/js/maps/google/GoogleSingleMarkerMap.js");




window.inits = {};
inits.loader = {
  create: function create() {
    var loader = document.createElement('div');
    var loaderBg = document.createElement('div');
    var loaderIcon = document.createElement('div');
    loader.className = 'loader is-active';
    loaderBg.className = 'loader-background';
    loaderIcon.className = 'loader-icon';
    loader.appendChild(loaderBg);
    loader.appendChild(loaderIcon);
    this.loader = loader;
  },
  add: function add(target) {
    if (target instanceof HTMLElement) {
      this.create();
      target.appendChild(this.loader);
    }
  },
  remove: function remove(target) {
    if (target instanceof HTMLElement) {
      target.removeChild(this.loader);
      this.clear();
    }
  },
  clear: function clear() {
    this.loader = null;
  }
};

inits.sidebarSearch = function () {
  var sideBarNav = document.querySelector('#sidebarnav');

  if (!sideBarNav) {
    return false;
  }

  var search = function () {
    var list = {};
    sideBarNav.querySelectorAll('[data-search-i]').forEach(function (item, index) {
      var keyText = item.innerText.trim().toLowerCase(); // if the same names

      if (keyText in list) {
        keyText += index;
      }

      list[keyText] = [];
      var node = item; // make list by nodes to the root ul (#sidebarnav)

      while (node) {
        if (node.matches('#sidebarnav')) {
          node = null;
          break;
        } else {
          var nodeTagName = node.tagName.toLowerCase();

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
      clear: document.querySelector('#sidebar-clear-search-input')
    };
  }();

  if (!search.input || !search.keys.length) {
    return false;
  }

  var searchList = {
    activePath: function () {
      var result = '';

      for (var key in search.list) {
        if (search.list.hasOwnProperty(key)) {
          var listNodes = search.list[key];
          var li = listNodes.filter(function (element) {
            return element.tagName.toLowerCase() === 'li';
          });
          var expandPath = li.every(function (element) {
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
    }(),
    noFoundElement: function () {
      var element = sideBarNav.children[0];
      element.removeAttribute('hidden');
      sideBarNav.removeChild(element);
      return element.outerHTML;
    }(),
    showAll: function showAll() {
      var setActivePath = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : true;

      for (var key in search.list) {
        if (search.list.hasOwnProperty(key)) {
          var listNodes = search.list[key];

          for (var i = 0; i < listNodes.length; i++) {
            var node = listNodes[i];
            var nodeTagName = node.tagName.toLowerCase();
            node.removeAttribute('style');

            if (nodeTagName === 'li') {
              node.classList.remove('search-arrow-active', 'active');
              node.children[0].setAttribute('aria-expanded', false);
            } else if (nodeTagName === 'ul') {
              node.classList.remove('show');
              node.setAttribute('aria-expanded', false);
            }
          }
        }
      }

      if (setActivePath && this.activePath) {
        for (var _i = 0; _i < this.activePath.length; _i++) {
          var _node = this.activePath[_i];

          var _nodeTagName = _node.tagName.toLowerCase();

          if (_nodeTagName === 'li') {
            _node.classList.add('active');

            _node.children[0].setAttribute('aria-expanded', true);
          } else if (_nodeTagName === 'ul') {
            _node.classList.add('show');

            _node.setAttribute('aria-expanded', true);
          }
        }
      }
    },
    hideAll: function hideAll() {
      for (var key in search.list) {
        if (search.list.hasOwnProperty(key)) {
          for (var i = 0; i < search.list[key].length; i++) {
            search.list[key][i].style.display = 'none';
          }
        }
      }
    },
    show: function show() {
      var elements = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : [];

      for (var i = 0; i < elements.length; i++) {
        var node = elements[i];
        var nodeTagName = node.tagName.toLowerCase();
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
    addAccessoryEvents: function addAccessoryEvents() {
      $('a.has-arrow', sideBarNav).on('click', this._accessoryClickEvent);
    },
    removeAccessoryEvents: function removeAccessoryEvents() {
      $('a.has-arrow', sideBarNav).off('click', this._accessoryClickEvent);
    },
    _accessoryClickEvent: function _accessoryClickEvent(event) {
      search.clear.setAttribute('hidden', '');
      search.input.value = '';
      searchList.removeAccessoryEvents();
      searchList.showAll(false); // current click active path

      var node = event.currentTarget;

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
  var insert = false;
  var addEvent = false;
  $(search.input).on('input', function () {
    var value = this.value.trim().toLowerCase();
    var isSearch = false;

    if (value.length >= 2) {
      searchList.hideAll();
      search.keys.forEach(function (key) {
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
  $('#sidebar-clear-search-input').on('click', function () {
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
  var $window = $(window);
  var $body = $(document.body);

  function BindActionsList(element) {
    var _this2 = this;

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
      this.rootEl.querySelectorAll('.dd-item').forEach(function (item) {
        item.classList.add('dd-control-item');
      });
    }

    this.$controlPane = $(this.rootEl.querySelector('[data-list-actions]'));
    this.$controlPane.remove();
    $body.append(this.$controlPane);

    var setControlPanePosition = function setControlPanePosition($inputEl) {
      if (!$inputEl) {
        return false;
      }

      _this2.$currentCheckedInput = $inputEl;

      var $parent = _this2.$currentCheckedInput.parent();

      var position = $parent.offset();

      _this2.$controlPane.css({
        top: position.top - $parent.height() / 3 + 1,
        left: position.left + $parent.outerWidth() + 5
      });
    };

    var resizeTimer = null;

    var resizeActionsPosition = function resizeActionsPosition() {
      if (!_this2.$currentCheckedInput) {
        return false;
      }

      clearTimeout(resizeTimer);
      resizeTimer = setTimeout(function () {
        return setControlPanePosition(_this2.$currentCheckedInput);
      }, 100);
    };

    $window.on('resize', resizeActionsPosition);
    this.$rootEl.on('change', 'input[data-select-all-list]', _selectAllListEvent.bind(this)).on('change', 'input[data-list-item]', _changeListItemEvent.bind(this));
    this.$controlPane.on('click', 'button[data-list-action]', _clickListActionEvent.bind(this));

    function _selectAllListEvent(event) {
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

    function _changeListItemEvent(event) {
      var $input = $(event.currentTarget);

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

    function _clickListActionEvent(event) {
      var _this = this;

      var $button = $(event.currentTarget);

      function sendRequest(result) {
        if (!result.value) {
          $button.prop('disabled', false);
          return false;
        }

        var route = $button.data('route') || null;

        if (!route) {
          console.warn('No button action route');
          return false;
        }

        inits.loader.add(_this.$rootEl.get(0));
        axios.post(route, formData).then(function (response) {
          if (response.data.reload) {
            window.location.reload();
          } else {
            inits.loader.remove(_this.$rootEl.get(0));
            $button.prop('disabled', false);
          }
        })["catch"](function () {
          inits.loader.remove(_this.$rootEl.get(0));
          $button.prop('disabled', false);
        });
      }

      _this.listItems = _this.$rootEl.find('[data-list-item="' + _this.uuid + '"]');
      $button.prop('disabled', true);
      var action = $button.data('list-action');
      var formData = new window.FormData();

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
    } // methods


    this.defaultState = function () {
      if (_this2.selectAllEl && _this2.selectAllEl.length && (_this2.selectAllEl.prop('checked') || _this2.selectAllEl.prop('indeterminate'))) {
        _this2.selectAllEl.prop('checked', false);

        _this2.selectAllEl.prop('indeterminate', false);

        if (_this2.listItems.length) {
          _this2.listItems.prop('checked', false);
        }

        if (_this2.$controlPane.length) {
          _this2.$controlPane.removeClass('is-shown');
        }
      }

      _this2.updateItemsList();
    };

    this.updateItemsList = function () {
      _this2.listItems = _this2.$rootEl.find('[data-list-item="' + _this2.uuid + '"]:not(:disabled)');

      if (!_this2.listItems.length) {
        if (_this2.selectAllEl) {
          if (_this2.rootEl.hasAttribute('data-control-list') && !_this2.rootEl.querySelectorAll('[data-list-item="' + _this2.uuid + '"]:disabled').length) {
            _this2.rootEl.classList.add('control-list-empty');
          } else {
            _this2.selectAllEl.setAttribute('disabled', 'true');
          }
        }
      } else {
        if (_this2.selectAllEl) {
          if (_this2.rootEl.hasAttribute('data-control-list') && !_this2.rootEl.querySelectorAll('[data-list-item="' + _this2.uuid + '"]:disabled').length) {
            _this2.rootEl.classList.remove('control-list-empty');
          } else {
            _this2.selectAllEl.setAttribute('disabled', 'false');
          }
        }
      }
    };

    this.updatePosition = function () {
      setControlPanePosition(_this2.$currentCheckedInput);
    }; // instance


    this.$rootEl.data('ControlListInstance', this);
  }

  $('table, [data-control-list]').each(function (i, element) {
    new BindActionsList(element);
  });
};

inits.hideLangTabs = function () {
  if ($(document.documentElement).data('hide-lang-tabs')) {
    $('.js-lang-tabs:not([hidden])').attr('hidden', 'hidden');
  }
};

inits.tooltip = function () {
  $('[data-toggle="tooltip"]').tooltip({
    container: 'body',
    boundary: 'window'
  });
};

inits.confirmation = function () {
  var params = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
  params = $.extend({
    btnOkClass: 'btn-danger',
    title: translations.confirmDelete.title,
    btnOkLabel: translations.confirmDelete.yesText,
    btnCancelLabel: translations.confirmDelete.noText
  }, params);
  $('[data-toggle="confirmation"]').confirmation(params);
};

inits.confirmDelete = function (el, title, text, confirmText, cancelText, successFunction) {
  var $el = $(el);

  if (!successFunction) {
    successFunction = function successFunction(result) {
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
  var defaults = {
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
  var defaults = {
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
    var $this = $(this);
    var confirmText = $this.data('confirm');

    if (confirmText) {
      if (!confirm(confirmText)) {
        return;
      }
    }

    var action = $this.data('action');
    var $form = $this.closest('form');

    if (action && $form) {
      var $formAction = $('#form-action-input');

      if (!$formAction.length) {
        $form.append('<input type="hidden" id="form-action-input" name="form-action" value="' + action + '"/>');
      }

      $formAction.val(action);
      var redirectUrl = $this.data('redirect-url');

      if (redirectUrl) {
        $form.append('<input type="hidden" name="redirect-url" value="' + redirectUrl + '"/>');
      }

      $form.submit();
    }
  });
}; // Clone controls buttons to header


inits.cloneFormControls = function () {
  var $nawWrapper = $('.js-naw-wrapper');

  if ($nawWrapper.length && $('.js-form-controls').length) {
    var $buttons = $('.js-form-controls:first').clone();

    if ($buttons.children().length > 4) {
      $(document.body).addClass('many-header-button');
    }

    $buttons.removeClass('js-form-controls');
    $buttons.find('.btn').addClass('btn-sm');
    $buttons.find('.js-form-submit').on('click', function () {
      $('.js-form-controls:first').children().eq($(this).index()).trigger('click');
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
    color: "#99abb4"
  });
};

inits.sidebarMenu = function ($selector) {
  $selector = $selector || $("#sidebarnav");
  $selector.metisMenu({
    collapseInClass: 'show'
  });
};

inits.sidebarToggle = function () {
  var $body = $(document.body);

  var calcSidebar = function calcSidebar() {
    if ((window.innerWidth || this.screen.width) < 1170) {
      $body.addClass("mini-sidebar");
      $(".navbar-brand span").hide();
      $(".scroll-sidebar, .slimScrollDiv").css("overflow-x", "visible").parent().css("overflow", "visible");
      $(".sidebartoggler i").addClass("ti-menu");
    } else {
      $body.removeClass("mini-sidebar");
      $(".navbar-brand span").show();
    }

    var height = (window.innerHeight || this.screen.height) - 71;

    if (height < 1) {
      height = 1;
    }

    if (height > 70) {
      $(".page-wrapper").css("min-height", height + "px");
    }
  };

  $(window).on("resize", calcSidebar);
  $(".sidebartoggler").on("click", function () {
    var $sidebar = $(".scroll-sidebar, .slimScrollDiv");
    var $navbar = $(".navbar-brand span");

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
  start: function start() {
    $(this.selector).fadeIn(150);
  },
  stop: function stop() {
    $(this.selector).fadeOut(150);
  }
};

inits.datePicker = function ($selector) {
  var options = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
  $selector = $selector || $('.js-datepicker');
  options = $.extend({
    language: $(document.documentElement).attr('lang'),
    format: 'dd.mm.yyyy',
    autoclose: true
  }, options); // bootstrap datepicker https://github.com/uxsolutions/bootstrap-datepicker

  $selector.datepicker(options);
  $selector.data('autocomplete', 'off').prop('autocomplete', 'off');

  if ($selector.data('start-date')) {
    $selector.datepicker('setStartDate', $selector.data('start-date'));
  }

  if ($selector.data('end-date')) {
    $selector.datepicker('setEndDate', $selector.data('end-date'));
  }
};

inits.dateTimePicker = function ($selector) {
  var options = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
  $selector = $selector || $('.js-datetimepicker');
  $selector.attr('autocomplete', 'off').prop('autocomplete', 'off');
  $.datetimepicker.setLocale($(document.documentElement).attr('lang'));
  options = $.extend({
    step: 1,
    format: 'd.m.Y H:i',
    formatTime: 'H:i',
    formatDate: 'd.m.Y',
    dayOfWeekStart: 1
  }, options);
  $selector.datetimepicker(options);
};

inits.dateTimeRangePicker = function ($selector) {
  $selector = $selector || $('.js-datetimerangepicker');
  var options = {
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
  var options = {
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
      "customRangeLabel": window.translations.daterangepicker.customRangeLabel
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
  var options = {
    timePicker: false,
    locale: {
      "format": "DD.MM",
      "separator": " - ",
      "applyLabel": window.translations.daterangepicker.applyLabel,
      "cancelLabel": window.translations.daterangepicker.cancelLabel,
      "fromLabel": window.translations.daterangepicker.fromLabel,
      "toLabel": window.translations.daterangepicker.toLabel,
      "customRangeLabel": window.translations.daterangepicker.customRangeLabel
    }
  };
  $selector.daterangepicker(options, function (start_date, end_date) {
    $(this.element).val(start_date.format(this.locale.format) + ' - ' + end_date.format(this.locale.format));
  }).on('show.daterange', function (ev, picker) {
    picker.container.find(".calendar-table").hide();
  });
};

inits.tinyMCE = function () {
  var selector = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : '.js-wysiwyg';
  var language = $(document.documentElement).attr('lang') || 'en';

  if (language === 'ua') {
    language = 'uk';
  }

  $.each($(selector), function (i, el) {
    var $el = $(el);
    $el.tinymce($.extend(window.tinyConfig || {}, {
      selector: selector,
      language: language,
      default_language: language,
      height: $el.height()
    }));
  });
};

inits.tinyWysiwyg = function () {
  var selector = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : '.js-tiny-wysiwyg';
  var language = $(document.documentElement).attr('lang') || 'en';

  if (language === 'ua') {
    language = 'uk';
  }

  $.each($(selector), function (i, el) {
    var $el = $(el);
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
      theme: "wezom",
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

  var model = $el.data('model');

  if (!model) {
    console.warn('Not specified model for status switcher');
    return;
  }

  function changeStyle($button, published) {
    if (published) {
      $button.removeClass('btn-outline-secondary').addClass('btn-success');
    } else {
      $button.removeClass('btn-success').addClass('btn-outline-secondary');
    }
  }

  $el.on('click', 'button', function (e) {
    e.preventDefault();
    var $this = $(this);
    $this.prop('disabled', true);
    var id = $this.data('id'),
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
        $this.data('status', response.data.status); // TODO don`t use this logic

        if (!locale) {
          if ($el.data('type') === 'small') {
            $this.html(response.data.status ? '<i class="fa fa-check-square-o"></i>' : '<i class="fa fa-square-o"></i>');
            $this.tooltip('hide').tooltip('dispose').attr('title', response.data.button_text).tooltip();
          } else {
            $this.html(response.data.button_text);
          }
        }
      }
    })["catch"](function (error) {
      $this.prop('disabled', false);
    });
  });
};

inits.select2 = function ($el) {
  $el = $el || $('.js-select2');
  $el.each(function (i, currentEl) {
    var $currentEl = $(currentEl);
    var config = {
      width: '100%'
    };

    try {
      var templateKey = $currentEl.data('template');

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

          $.each(state.data, function (key, value) {
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
    var $currentEl = $(currentEl);
    var config = {
      width: '100%',
      ajax: {
        url: $currentEl.data('url'),
        dataType: 'JSON'
      }
    };

    try {
      var templateKey = $currentEl.data('template');

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

          $.each(state.data, function (key, value) {
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

inits.multipleInputs = function () {
  var $wrapper = $('.js-multiple-input-wrapper');
  var templateSelector = '.js-multiple-input-template';
  var itemSelector = '.js-multiple-input-item';
  var deleteSelector = '.js-multiple-input-item .js-remove-row';
  var buttonAddNewRowSelector = '.js-add-new-row';
  var listSelector = '.js-multiple-input-list';

  function toggleEmptyMessage($listSelector) {
    if ($listSelector.find(itemSelector).length) {
      $listSelector.find('.js-empty').css('display', 'none');
    } else {
      $listSelector.find('.js-empty').css('display', 'block');
    }
  }

  $wrapper.each(function () {
    var $this = $(this);
    $this.find(listSelector).sortable({
      handle: '.drag-cursor'
    }).disableSelection(); // Add

    $this.on('click', buttonAddNewRowSelector, function () {
      var $newEl = $this.find(templateSelector + ' ' + itemSelector).clone();
      var $input = $newEl.find('.js-input');
      $input.attr('name', $input.data('name')).removeAttr('data-name');
      $input.attr('id', $input.data('name') + Math.random().toString(36).substring(7));
      $newEl.appendTo($this.find(listSelector));
      $input.focus();
      inits.confirmation();
      toggleEmptyMessage($this.find(listSelector));
    }); // Remove

    $this.on('click', deleteSelector, function () {
      $(this).closest(itemSelector).remove();
      toggleEmptyMessage($this.find(listSelector));
    });
  });
};

inits.dropDownload = function () {
  var selector = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : '.dropDownload';
  var $el = $(selector); // Sortable

  $el.sortable({
    connectWith: ".loadedBlock",
    handle: ".loadedDrag",
    cancel: '.loadedControl',
    placeholder: "loadedBlockPlaceholder",
    update: function update() {
      var positions = [];
      $(this).find('.loadedBlock').each(function () {
        positions.push($(this).data('image'));
      });
      var options = $(this).data('options');
      var params = options.params;
      params.positions = positions;
      axios.post(options.urls.sort, params);
    }
  }); // Mark as default

  $el.on('click', '.loadedCover .btn.btn-success', function () {
    var it = $(this),
        itP = it.closest('.loadedBlock'),
        id = itP.data('image'),
        $dropDownload = it.closest(selector);
    var options = $dropDownload.data('options');
    var params = options.params;
    params.id = id;
    axios.post(options.urls.setAsDefault, params);
  });
  var $dropModule = $('.dropModule');
  $dropModule.on('click', '.checkAll', function () {
    var block = $(this).closest('.loadedBox').find(selector).find('.loadedBlock');
    block.addClass('chk');
    block.find('.loadedCheck').find('input').prop('checked', true);
    $('.loadedBox .uncheckAll, .loadedBox .removeCheck').fadeIn(300);
  });
  $dropModule.on('click', '.uncheckAll', function () {
    var block = $(this).closest('.loadedBox').find(selector).find('.loadedBlock');
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

inits.attachableUploader = function () {
  var selector = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : '.js-attachable-uploader';
  var previewSelector = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : '.js-attachable-preview';
  // TODO add ajax-upload
  $(selector).on('click', '.js-delete-attachable', function () {
    var $button = $(this);
    var $container = $button.closest(selector);
    $button.prop('disabled', true);
    axios["delete"]($button.data('url')).then(function (response) {
      if (response.data.success) {
        $container.find(previewSelector).remove();
        $container.find('input').removeAttr('hidden');
      }
    })["finally"](function () {
      $button.prop('disabled', false);
    });
  });
};

inits.googleMap = function () {
  var selector = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : '.js-google-map';
  $(selector).each(function (i, el) {
    if ($(el).data('multiple')) {
      new _maps_google_GoogleMultipleMarkersMap__WEBPACK_IMPORTED_MODULE_2__["default"](el).main();
    } else {
      new _maps_google_GoogleSingleMarkerMap__WEBPACK_IMPORTED_MODULE_3__["default"](el).main();
    }
  });
};

inits.yandexMap = function () {
  var selector = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : '.js-yandex-map';
  $(selector).each(function (i, el) {
    if ($(el).data('multiple')) {
      new _maps_yandex_YandexMultipleMarkersMap__WEBPACK_IMPORTED_MODULE_0__["default"](el).main();
    } else {
      new _maps_yandex_YandexSingleMarkerMap__WEBPACK_IMPORTED_MODULE_1__["default"](el).main();
    }
  });
};

inits.sortable = function ($el) {
  $el = $el || $('.js-sortable');
  var options = {
    helper: function helper(e, ui) {
      ui.children().each(function () {
        $(this).width($(this).width());
      });
      return ui;
    },
    handle: '.js-sortable-handle'
  };
  var params = $el.data('params');

  if (params) {
    options.update = function () {
      var positions = [];
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
  var $el = $(selector || '.js-nestable');
  $el.each(function (index, el) {
    var $el = $(el);
    var prevControlItemsInstance = null;
    var options = {
      maxDepth: $el.data('settings-depth') || 5,
      callback: function callback($root, $item, pos) {
        var data = $root.data('params') || {};
        var controlItemsInstance = $root.data('ControlListInstance');

        if (prevControlItemsInstance) {
          prevControlItemsInstance.updateItemsList();
        }

        if (controlItemsInstance) {
          controlItemsInstance.defaultState();
        }

        data.items = $root.nestable('serialize');
        axios.post(route('admin.ajax.update-nestable-sort'), data);
      },
      onDragStart: function onDragStart($root, $item, pos) {
        prevControlItemsInstance = $root.data('ControlListInstance');

        if (prevControlItemsInstance) {
          prevControlItemsInstance.defaultState();
        }
      }
    };
    $el.nestable(options);
  });
};

inits.checkChild = function () {
  var selector = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : '.js-check-child';
  var wrapper = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : '.check-wrapper';
  var $el = $(selector);
  $el.on('change', function () {
    var $chidlrens = $(this).closest(wrapper).find('input[type="checkbox"]').not(this);
    $chidlrens.prop('checked', $(this).is(':checked'));
  });
  $el.each(function (i, item) {
    if ($(item).prop('checked') === false && $(item).closest(wrapper).find('input[type="checkbox"][value!=""]:not(:checked)').not(item).length === 0) {
      $(item).prop('checked', true);
    }
  });
};

inits.simpleAjaxFormSubmit = function () {
  var selector = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : '.js-ajax-form';
  var $el = $(selector);
  $el.off('submit').on('submit', function (e) {
    inits.loader.add($el.get(0));
    e.preventDefault();
    var $this = $(this);
    axios({
      method: $this.attr('method'),
      url: $this.attr('action'),
      data: $this.serialize()
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
    })["catch"](function () {
      inits.loader.remove($el.get(0));
    });
  });
};

inits.forceValid = function () {
  var selector = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : '.js-force-valid';
  $(selector).valid();
};

inits.dataTable = function () {
  var selector = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : '.js-data-table';
  $(selector).DataTable({
    'lengthChange': false,
    'info': false,
    language: {
      url: '/vendor/cms/core/plugins/datatables/i18n/' + document.documentElement.lang + '.json'
    }
  });
};

inits.generateSlug = function (selector) {
  var $components = $(selector || '.js-slug-generator');
  $components.each(function () {
    var $component = $(this);
    var $source = $($component.data('source'));
    var $target = $component.find('input');

    if ($source.length === 0) {
      console.warn('Cant`t find sluggable source [' + $component.data('source') + ']');
      return;
    }

    var handler = function handler() {
      axios.post(route('admin.ajax.generate-slug'), {
        slug: $source.val()
      }).then(function (response) {
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

inits.multiSelect = function () {
  var selector = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : '.js-multi-select';

  var needSort = function needSort(select) {
    return select.hasAttribute('data-multi-sort');
  };

  var getSortedValues = function getSortedValues($sortableUl) {
    var isOptgroup = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : false;
    return isOptgroup ? $.map($sortableUl.find("li.ms-optgroup-label:visible"), function (li) {
      return $(li).find('span').text().toLowerCase();
    }) : $.map($sortableUl.children(":visible"), function (li) {
      return $(li).data('ms-value');
    });
  };

  var sortSelect = function sortSelect($select) {
    var values = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : [];
    var isOptgroup = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : false;
    if (!values.length) return false;
    var sortedItems = [];

    if (isOptgroup) {
      values.forEach(function (value) {
        $select.find("optgroup").each(function (i, optgroup) {
          var $optgroup = $(optgroup);

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
      var $options = $select.find('option').prop('selected', false);
      values.forEach(function (value) {
        $options.each(function (i, option) {
          var $option = $(option);

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
      var isOptgroup = false;
      $(this).multiSelect({
        keepOrder: true,
        selectableHeader: "<input type='text' class='search-input form-control' autocomplete='off'>",
        selectionHeader: "<input type='text' class='search-input form-control' autocomplete='off'>",
        afterInit: function afterInit() {
          var _this3 = this;

          var that = this,
              $selectableSearch = that.$selectableUl.prev(),
              $selectionSearch = that.$selectionUl.prev(),
              selectableSearchString = '#' + that.$container.attr('id') + ' .ms-elem-selectable:not(.ms-selected)',
              selectionSearchString = '#' + that.$container.attr('id') + ' .ms-elem-selection.ms-selected';
          that.qs1 = $selectableSearch.quicksearch(selectableSearchString, {
            hide: function hide() {
              $(this).hide();

              if ($(this).parent().find('.ms-elem-selectable').css('display') === 'none') {
                $(this).parent().children('.ms-optgroup-label').hide();
              }
            },
            show: function show() {
              $(this).show();

              if ($(this).parent().find('.ms-elem-selectable').is(':visible')) {
                $(this).parent().children('.ms-optgroup-label').show();
              }
            }
          }).on('keydown', function (e) {
            if (e.which === 40) {
              that.$selectableUl.focus();
              return false;
            }
          });
          that.qs2 = $selectionSearch.quicksearch(selectionSearchString, {
            hide: function hide() {
              $(this).hide();

              if ($(this).parent().find('.ms-elem-selection').css('display') === 'none') {
                $(this).parent().children('.ms-optgroup-label').hide();
              }
            },
            show: function show() {
              $(this).show();

              if ($(this).parent().find('.ms-elem-selection').is(':visible')) {
                $(this).parent().children('.ms-optgroup-label').show();
              }
            }
          }).on('keydown', function (e) {
            if (e.which === 40) {
              that.$selectionUl.focus();
              return false;
            }
          }); // sortable

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
              helper: function helper(event, ui) {
                ui.children().each(function () {
                  $(this).width($(this).width());
                });
                return ui;
              },
              update: function update() {
                sortSelect(_this3.$element, getSortedValues(_this3.$selectionUl, isOptgroup), isOptgroup);
              }
            });
          }
        },
        afterSelect: function afterSelect() {
          if (needSort(this.$element.get(0))) {
            sortSelect(this.$element, getSortedValues(this.$selectionUl, isOptgroup), isOptgroup);
          }

          this.qs1.cache();
          this.qs2.cache();
        },
        afterDeselect: function afterDeselect() {
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

inits.perPage = function () {
  var selector = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : '.js-select-per-page';
  var $el = $(selector);

  if (!$el) {
    return;
  }

  $el.on('change', function () {
    var url = $el.data('url');
    var joinSymbol = url.includes('?') ? '&' : '?';
    window.location.href = url + joinSymbol + $el.attr('name') + '=' + $el.val();
  });
};

inits.copyLangFields = function () {
  var selector = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : '.js-copy-lang-fields';

  if (!$('html').data('copy-lang-fields')) {
    return;
  }

  $(selector).each(function (index, tabs) {
    var locales = $(tabs).data('locales');

    if (locales.length < 2) {
      return;
    }

    $(tabs).next('.tab-content').find('input').filter('[type="text"],[type="number"],[type="email"]').each(function (i, el) {
      var $el = $(el),
          $formGroup = $el.closest('.form-group');
      var $inputGroup = $formGroup.find('.input-group');

      if ($inputGroup.length === 0) {
        $el.wrap('<div class="input-group"></div>');
        $inputGroup = $formGroup.find('.input-group');
      }

      var $copyButton = $formGroup.find('.js-copy-button'); // if doesnt have "copy" button - create

      if ($copyButton.length === 0) {
        $copyButton = $('<button class="btn btn-xs btn-outline-dark"' + ' type="button"' + ' title="' + translations.copyToAnotherLangFields + '"><i class="fa fa-copy"></i></button>');

        if ($inputGroup.find('.input-group-append').length === 0) {
          $inputGroup.append('<div class="input-group-append"></div>');
        }

        $inputGroup.find('.input-group-append').append($copyButton);
      } // listen click event


      $copyButton.click(function () {
        var name = $el.attr('name');

        if (!name) {
          return;
        }

        var currentLocale = $el.closest('.tab-pane').data('locale');

        if (!currentLocale) {
          return;
        }

        locales.forEach(function (locale) {
          if (locale !== currentLocale) {
            var newName = name.includes("[".concat(currentLocale, "]")) ? name.replace("[".concat(currentLocale, "]"), "[".concat(locale, "]")) : name.replace(new RegExp("^\\b".concat(currentLocale, "\\b")), locale);
            $(tabs).next('.tab-content').find("input[name=\"".concat(newName, "\"]")).val($el.val());
          }
        });
      });
    });
  });
};

/* harmony default export */ __webpack_exports__["default"] = (inits);

/***/ }),

/***/ "./resources/assets/src/js/maps/MultipleMarkersMap.js":
/*!************************************************************!*\
  !*** ./resources/assets/src/js/maps/MultipleMarkersMap.js ***!
  \************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "default", function() { return MultipleMarkersMap; });
function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } }

function _createClass(Constructor, protoProps, staticProps) { if (protoProps) _defineProperties(Constructor.prototype, protoProps); if (staticProps) _defineProperties(Constructor, staticProps); return Constructor; }

var MultipleMarkersMap = /*#__PURE__*/function () {
  function MultipleMarkersMap(el) {
    _classCallCheck(this, MultipleMarkersMap);

    this.$container = $(el);
    this.selectors = {
      mapSelector: '.js-map-container'
    };
    this.map = null;
    this.markers = [];
    this.defaultCoordinates = {
      lat: 46.9648674,
      lng: 31.973737
    };
    this.defaultZoom = 16;
    this.$input = this.$container.find('.js-input');
  }

  _createClass(MultipleMarkersMap, [{
    key: "inputValue",
    get: function get() {
      var _this = this;

      var data = JSON.parse(this.$input.val());

      if (data === null) {
        return {};
      }

      if (data.hasOwnProperty('markers')) {
        data.markers = data.markers.map(function (coordinates) {
          return _this.decodeCoordinates(coordinates);
        });
      }

      if (data.hasOwnProperty('center')) {
        data.center = this.decodeCoordinates(data.center);
      }

      return data;
    },
    set: function set(value) {
      var _this2 = this;

      if (value) {
        if (value.hasOwnProperty('markers')) {
          value.markers = value.markers.map(function (coordinates) {
            return _this2.encodeCoordinates(coordinates);
          });
        }

        if (value.hasOwnProperty('center')) {
          value.center = this.encodeCoordinates(value.center);
        }
      }

      this.$input.val(JSON.stringify(value));
    }
  }, {
    key: "main",
    value: function main() {
      var $mapContainer = this.$container.find(this.selectors.mapSelector); // Transform container center coordinates

      var containerConfig = $mapContainer.data('config');

      if (containerConfig && containerConfig.hasOwnProperty('center')) {
        containerConfig.center = this.decodeCoordinates(containerConfig.center);
      }

      var mapConfig = $.extend({
        zoom: this.defaultZoom,
        center: this.decodeCoordinates(this.defaultCoordinates)
      }, containerConfig, this.inputValue); // Create map

      this.map = this.createMap($mapContainer[0], mapConfig); // Register map event listeners

      this.registerMapEventListeners(); // Restore old makers

      this.restoreMarkers();
    }
  }, {
    key: "createMap",
    value: function createMap(container, mapConfig) {
      throw new Error('Method "createMap" does not implemented');
    }
  }, {
    key: "createMarker",
    value: function createMarker(coordinates) {
      throw new Error('Method "createMarker" does not implemented');
    }
  }, {
    key: "createAndAddMarkerToMap",
    value: function createAndAddMarkerToMap(coordinates) {
      var marker = this.createMarker(coordinates);
      this.markers.push(marker);
      this.addMarkerToMap(marker);
      this.registerMarkerEventListeners(marker);
      this.updateHiddenInputValue();
    }
  }, {
    key: "restoreMarkers",
    value: function restoreMarkers() {
      var _this3 = this;

      var markers = this.inputValue && this.inputValue.markers ? this.inputValue.markers : [];
      markers.forEach(function (coordinates) {
        return _this3.createAndAddMarkerToMap(coordinates);
      });
    }
  }, {
    key: "addMarkerToMap",
    value: function addMarkerToMap(marker) {
      throw new Error('Method "addMarkerToMap" does not implemented');
    }
  }, {
    key: "deleteMarkerFromMap",
    value: function deleteMarkerFromMap(marker) {
      throw new Error('Method "deleteMarkerFromMap" does not implemented');
    }
  }, {
    key: "updateHiddenInputValue",
    value: function updateHiddenInputValue() {
      throw new Error('Method "updateHiddenInputValue" does not implemented');
    }
  }, {
    key: "deleteMarker",
    value: function deleteMarker(marker) {
      var _this4 = this;

      this.markers.forEach(function (m, i) {
        if (m === marker) {
          delete _this4.markers[i];
          return false;
        }
      });
      this.markers = this.markers.filter(function (coordinates) {
        return coordinates;
      });
      this.deleteMarkerFromMap(marker);
      this.updateHiddenInputValue();
    }
  }, {
    key: "decodeCoordinates",
    value: function decodeCoordinates(coordinates) {
      return coordinates;
    }
  }, {
    key: "encodeCoordinates",
    value: function encodeCoordinates(coordinates) {
      return coordinates;
    }
  }, {
    key: "registerMapEventListeners",
    value: function registerMapEventListeners() {}
  }, {
    key: "registerMarkerEventListeners",
    value: function registerMarkerEventListeners(marker) {}
  }]);

  return MultipleMarkersMap;
}();



/***/ }),

/***/ "./resources/assets/src/js/maps/SingleMarkerMap.js":
/*!*********************************************************!*\
  !*** ./resources/assets/src/js/maps/SingleMarkerMap.js ***!
  \*********************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "default", function() { return SingleMarkerMap; });
function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } }

function _createClass(Constructor, protoProps, staticProps) { if (protoProps) _defineProperties(Constructor.prototype, protoProps); if (staticProps) _defineProperties(Constructor, staticProps); return Constructor; }

var SingleMarkerMap = /*#__PURE__*/function () {
  function SingleMarkerMap(el) {
    _classCallCheck(this, SingleMarkerMap);

    this.$container = $(el);
    this.selectors = {
      liveSearchInput: '.js-search-input',
      mapSelector: '.js-map-container'
    };
    this.map = null;
    this.marker = null;
    this.geocoder = null;
    this.defaultCoordinates = {
      lat: 46.9648674,
      lng: 31.973737
    };
    this.defaultZoom = 16;
    this.$input = this.$container.find('.js-input');
  }

  _createClass(SingleMarkerMap, [{
    key: "inputValue",
    get: function get() {
      var data = JSON.parse(this.$input.val());

      if (data === null) {
        return {};
      }

      if (data.hasOwnProperty('marker')) {
        data.marker = this.decodeCoordinates(data.marker);
      }

      if (data.hasOwnProperty('center')) {
        data.center = this.decodeCoordinates(data.center);
      }

      return data;
    },
    set: function set(value) {
      if (value) {
        if (value.hasOwnProperty('marker')) {
          value.marker = this.encodeCoordinates(value.marker);
        }

        if (value.hasOwnProperty('center')) {
          value.center = this.encodeCoordinates(value.center);
        }
      }

      this.$input.val(JSON.stringify(value));
    }
  }, {
    key: "main",
    value: function main() {
      var $mapContainer = this.$container.find(this.selectors.mapSelector); // Transform container center coordinates

      var containerConfig = $mapContainer.data('config');

      if (containerConfig && containerConfig.hasOwnProperty('center')) {
        containerConfig.center = this.decodeCoordinates(containerConfig.center);
      }

      var mapConfig = $.extend({
        zoom: this.defaultZoom,
        center: this.decodeCoordinates(this.defaultCoordinates)
      }, containerConfig, this.inputValue); // Create map

      this.map = this.createMap($mapContainer[0], mapConfig);
      this.marker = this.createMarker(this.getInitMarkerCoordinates());
      this.addMarkerToMap(this.marker); // Register event listeners

      this.registerEventListeners(); // Init live search

      this.initLiveSearch();
    }
  }, {
    key: "createMap",
    value: function createMap(container, mapConfig) {
      throw new Error('Method "createMap" does not implemented');
    }
  }, {
    key: "createMarker",
    value: function createMarker(coordinates) {
      throw new Error('Method "createMarker" does not implemented');
    }
  }, {
    key: "getInitMarkerCoordinates",
    value: function getInitMarkerCoordinates() {
      return this.inputValue.marker ? this.inputValue.marker : this.decodeCoordinates(this.defaultCoordinates);
    }
  }, {
    key: "addMarkerToMap",
    value: function addMarkerToMap(marker) {
      throw new Error('Method "addMarkerToMap" does not implemented');
    }
  }, {
    key: "decodeCoordinates",
    value: function decodeCoordinates(coordinates) {
      return coordinates;
    }
  }, {
    key: "encodeCoordinates",
    value: function encodeCoordinates(coordinates) {
      return coordinates;
    }
  }, {
    key: "registerEventListeners",
    value: function registerEventListeners() {}
  }, {
    key: "initLiveSearch",
    value: function initLiveSearch() {
      var liveSearchInput = this.$container.find(this.selectors.liveSearchInput);

      if (liveSearchInput.length) {
        liveSearchInput.autocomplete({
          source: this.liveSearchSource.bind(this),
          select: this.liveSearchSelect.bind(this)
        });
      }
    }
  }, {
    key: "liveSearchSource",
    value: function liveSearchSource(request, response) {
      response([]);
    }
  }, {
    key: "liveSearchSelect",
    value: function liveSearchSelect(event, ui) {}
  }]);

  return SingleMarkerMap;
}();



/***/ }),

/***/ "./resources/assets/src/js/maps/google/GoogleMultipleMarkersMap.js":
/*!*************************************************************************!*\
  !*** ./resources/assets/src/js/maps/google/GoogleMultipleMarkersMap.js ***!
  \*************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "default", function() { return GoogleMultipleMarkersMap; });
/* harmony import */ var _MultipleMarkersMap__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../MultipleMarkersMap */ "./resources/assets/src/js/maps/MultipleMarkersMap.js");
function _typeof(obj) { "@babel/helpers - typeof"; if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } }

function _createClass(Constructor, protoProps, staticProps) { if (protoProps) _defineProperties(Constructor.prototype, protoProps); if (staticProps) _defineProperties(Constructor, staticProps); return Constructor; }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function"); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, writable: true, configurable: true } }); if (superClass) _setPrototypeOf(subClass, superClass); }

function _setPrototypeOf(o, p) { _setPrototypeOf = Object.setPrototypeOf || function _setPrototypeOf(o, p) { o.__proto__ = p; return o; }; return _setPrototypeOf(o, p); }

function _createSuper(Derived) { var hasNativeReflectConstruct = _isNativeReflectConstruct(); return function _createSuperInternal() { var Super = _getPrototypeOf(Derived), result; if (hasNativeReflectConstruct) { var NewTarget = _getPrototypeOf(this).constructor; result = Reflect.construct(Super, arguments, NewTarget); } else { result = Super.apply(this, arguments); } return _possibleConstructorReturn(this, result); }; }

function _possibleConstructorReturn(self, call) { if (call && (_typeof(call) === "object" || typeof call === "function")) { return call; } return _assertThisInitialized(self); }

function _assertThisInitialized(self) { if (self === void 0) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return self; }

function _isNativeReflectConstruct() { if (typeof Reflect === "undefined" || !Reflect.construct) return false; if (Reflect.construct.sham) return false; if (typeof Proxy === "function") return true; try { Boolean.prototype.valueOf.call(Reflect.construct(Boolean, [], function () {})); return true; } catch (e) { return false; } }

function _getPrototypeOf(o) { _getPrototypeOf = Object.setPrototypeOf ? Object.getPrototypeOf : function _getPrototypeOf(o) { return o.__proto__ || Object.getPrototypeOf(o); }; return _getPrototypeOf(o); }



var GoogleMultipleMarkersMap = /*#__PURE__*/function (_MultipleMarkersMap) {
  _inherits(GoogleMultipleMarkersMap, _MultipleMarkersMap);

  var _super = _createSuper(GoogleMultipleMarkersMap);

  function GoogleMultipleMarkersMap() {
    _classCallCheck(this, GoogleMultipleMarkersMap);

    return _super.apply(this, arguments);
  }

  _createClass(GoogleMultipleMarkersMap, [{
    key: "createMap",
    value: function createMap(container, mapConfig) {
      return new window.google.maps.Map(container, mapConfig);
    }
  }, {
    key: "createMarker",
    value: function createMarker(coordinates) {
      return new window.google.maps.Marker({
        position: coordinates,
        draggable: true
      });
    }
  }, {
    key: "addMarkerToMap",
    value: function addMarkerToMap(marker) {
      marker.setMap(this.map);
    }
  }, {
    key: "deleteMarkerFromMap",
    value: function deleteMarkerFromMap(marker) {
      marker.setMap(null);
    }
  }, {
    key: "updateHiddenInputValue",
    value: function updateHiddenInputValue() {
      this.inputValue = {
        markers: this.markers.map(function (marker) {
          return marker.getPosition().toJSON();
        }),
        zoom: this.map.getZoom(),
        center: this.map.getCenter().toJSON()
      };
    }
  }, {
    key: "registerMapEventListeners",
    value: function registerMapEventListeners() {
      var _this = this;

      // Add marker
      window.google.maps.event.addListener(this.map, 'click', function (e) {
        return _this.createAndAddMarkerToMap(e.latLng);
      });
      window.google.maps.event.addListener(this.map, 'zoom_changed', this.updateHiddenInputValue.bind(this));
      window.google.maps.event.addListener(this.map, 'dragend', this.updateHiddenInputValue.bind(this));
    }
  }, {
    key: "registerMarkerEventListeners",
    value: function registerMarkerEventListeners(marker) {
      var _this2 = this;

      window.google.maps.event.addListener(marker, 'dragend', this.updateHiddenInputValue.bind(this));
      window.google.maps.event.addListener(marker, 'click', function () {
        return _this2.deleteMarker(marker);
      });
    }
  }]);

  return GoogleMultipleMarkersMap;
}(_MultipleMarkersMap__WEBPACK_IMPORTED_MODULE_0__["default"]);



/***/ }),

/***/ "./resources/assets/src/js/maps/google/GoogleSingleMarkerMap.js":
/*!**********************************************************************!*\
  !*** ./resources/assets/src/js/maps/google/GoogleSingleMarkerMap.js ***!
  \**********************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "default", function() { return GoogleSingleMarkerMap; });
/* harmony import */ var _SingleMarkerMap__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../SingleMarkerMap */ "./resources/assets/src/js/maps/SingleMarkerMap.js");
function _typeof(obj) { "@babel/helpers - typeof"; if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } }

function _createClass(Constructor, protoProps, staticProps) { if (protoProps) _defineProperties(Constructor.prototype, protoProps); if (staticProps) _defineProperties(Constructor, staticProps); return Constructor; }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function"); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, writable: true, configurable: true } }); if (superClass) _setPrototypeOf(subClass, superClass); }

function _setPrototypeOf(o, p) { _setPrototypeOf = Object.setPrototypeOf || function _setPrototypeOf(o, p) { o.__proto__ = p; return o; }; return _setPrototypeOf(o, p); }

function _createSuper(Derived) { var hasNativeReflectConstruct = _isNativeReflectConstruct(); return function _createSuperInternal() { var Super = _getPrototypeOf(Derived), result; if (hasNativeReflectConstruct) { var NewTarget = _getPrototypeOf(this).constructor; result = Reflect.construct(Super, arguments, NewTarget); } else { result = Super.apply(this, arguments); } return _possibleConstructorReturn(this, result); }; }

function _possibleConstructorReturn(self, call) { if (call && (_typeof(call) === "object" || typeof call === "function")) { return call; } return _assertThisInitialized(self); }

function _assertThisInitialized(self) { if (self === void 0) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return self; }

function _isNativeReflectConstruct() { if (typeof Reflect === "undefined" || !Reflect.construct) return false; if (Reflect.construct.sham) return false; if (typeof Proxy === "function") return true; try { Boolean.prototype.valueOf.call(Reflect.construct(Boolean, [], function () {})); return true; } catch (e) { return false; } }

function _getPrototypeOf(o) { _getPrototypeOf = Object.setPrototypeOf ? Object.getPrototypeOf : function _getPrototypeOf(o) { return o.__proto__ || Object.getPrototypeOf(o); }; return _getPrototypeOf(o); }



var GoogleSingleMarkerMap = /*#__PURE__*/function (_SingleMarkerMap) {
  _inherits(GoogleSingleMarkerMap, _SingleMarkerMap);

  var _super = _createSuper(GoogleSingleMarkerMap);

  function GoogleSingleMarkerMap(el) {
    var _this;

    _classCallCheck(this, GoogleSingleMarkerMap);

    _this = _super.call(this, el);
    _this.geocoder = new window.google.maps.Geocoder();
    return _this;
  }

  _createClass(GoogleSingleMarkerMap, [{
    key: "createMap",
    value: function createMap(container, mapConfig) {
      return new window.google.maps.Map(container, mapConfig);
    }
  }, {
    key: "createMarker",
    value: function createMarker(coordinates) {
      return new window.google.maps.Marker({
        position: coordinates,
        draggable: true
      });
    }
  }, {
    key: "addMarkerToMap",
    value: function addMarkerToMap(marker) {
      marker.setMap(this.map);
    }
  }, {
    key: "registerEventListeners",
    value: function registerEventListeners() {
      var _this2 = this;

      // marker events
      window.google.maps.event.addListener(this.marker, 'dragend', this.updateHiddenInputValue.bind(this)); // map events

      window.google.maps.event.addListener(this.map, 'click', function (e) {
        _this2.marker.setPosition(e.latLng);

        _this2.updateHiddenInputValue.bind(_this2)();
      });
      window.google.maps.event.addListener(this.map, 'zoom_changed', this.updateHiddenInputValue.bind(this));
      window.google.maps.event.addListener(this.map, 'dragend', this.updateHiddenInputValue.bind(this));
    }
  }, {
    key: "updateHiddenInputValue",
    value: function updateHiddenInputValue() {
      this.inputValue = {
        marker: this.marker.getPosition() ? this.marker.getPosition().toJSON() : {},
        zoom: this.map.getZoom(),
        center: this.map.getCenter().toJSON()
      };
    }
  }, {
    key: "liveSearchSource",
    value: function liveSearchSource(request, response) {
      this.geocoder.geocode({
        'address': request.term
      }, function (results, status) {
        response($.map(results, function (item) {
          return {
            label: item.formatted_address,
            value: item.formatted_address,
            latitude: item.geometry.location.lat(),
            longitude: item.geometry.location.lng()
          };
        }));
      });
    }
  }, {
    key: "liveSearchSelect",
    value: function liveSearchSelect(event, ui) {
      var position = {
        lat: ui.item.latitude,
        lng: ui.item.longitude
      };
      this.marker.setPosition(position);
      this.map.setCenter(position);
      this.updateHiddenInputValue();
    }
  }]);

  return GoogleSingleMarkerMap;
}(_SingleMarkerMap__WEBPACK_IMPORTED_MODULE_0__["default"]);



/***/ }),

/***/ "./resources/assets/src/js/maps/yandex/YandexMultipleMarkersMap.js":
/*!*************************************************************************!*\
  !*** ./resources/assets/src/js/maps/yandex/YandexMultipleMarkersMap.js ***!
  \*************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "default", function() { return YandexMultipleMarkersMap; });
/* harmony import */ var _MultipleMarkersMap__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../MultipleMarkersMap */ "./resources/assets/src/js/maps/MultipleMarkersMap.js");
function _typeof(obj) { "@babel/helpers - typeof"; if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } }

function _createClass(Constructor, protoProps, staticProps) { if (protoProps) _defineProperties(Constructor.prototype, protoProps); if (staticProps) _defineProperties(Constructor, staticProps); return Constructor; }

function _get(target, property, receiver) { if (typeof Reflect !== "undefined" && Reflect.get) { _get = Reflect.get; } else { _get = function _get(target, property, receiver) { var base = _superPropBase(target, property); if (!base) return; var desc = Object.getOwnPropertyDescriptor(base, property); if (desc.get) { return desc.get.call(receiver); } return desc.value; }; } return _get(target, property, receiver || target); }

function _superPropBase(object, property) { while (!Object.prototype.hasOwnProperty.call(object, property)) { object = _getPrototypeOf(object); if (object === null) break; } return object; }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function"); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, writable: true, configurable: true } }); if (superClass) _setPrototypeOf(subClass, superClass); }

function _setPrototypeOf(o, p) { _setPrototypeOf = Object.setPrototypeOf || function _setPrototypeOf(o, p) { o.__proto__ = p; return o; }; return _setPrototypeOf(o, p); }

function _createSuper(Derived) { var hasNativeReflectConstruct = _isNativeReflectConstruct(); return function _createSuperInternal() { var Super = _getPrototypeOf(Derived), result; if (hasNativeReflectConstruct) { var NewTarget = _getPrototypeOf(this).constructor; result = Reflect.construct(Super, arguments, NewTarget); } else { result = Super.apply(this, arguments); } return _possibleConstructorReturn(this, result); }; }

function _possibleConstructorReturn(self, call) { if (call && (_typeof(call) === "object" || typeof call === "function")) { return call; } return _assertThisInitialized(self); }

function _assertThisInitialized(self) { if (self === void 0) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return self; }

function _isNativeReflectConstruct() { if (typeof Reflect === "undefined" || !Reflect.construct) return false; if (Reflect.construct.sham) return false; if (typeof Proxy === "function") return true; try { Boolean.prototype.valueOf.call(Reflect.construct(Boolean, [], function () {})); return true; } catch (e) { return false; } }

function _getPrototypeOf(o) { _getPrototypeOf = Object.setPrototypeOf ? Object.getPrototypeOf : function _getPrototypeOf(o) { return o.__proto__ || Object.getPrototypeOf(o); }; return _getPrototypeOf(o); }



var YandexMultipleMarkersMap = /*#__PURE__*/function (_MultipleMarkersMap) {
  _inherits(YandexMultipleMarkersMap, _MultipleMarkersMap);

  var _super = _createSuper(YandexMultipleMarkersMap);

  function YandexMultipleMarkersMap() {
    _classCallCheck(this, YandexMultipleMarkersMap);

    return _super.apply(this, arguments);
  }

  _createClass(YandexMultipleMarkersMap, [{
    key: "main",
    value: function main() {
      var _this = this;

      window.ymaps.ready().then(function () {
        _get(_getPrototypeOf(YandexMultipleMarkersMap.prototype), "main", _this).call(_this);
      })["catch"](function (e) {
        return console.error(e);
      });
    }
  }, {
    key: "createMap",
    value: function createMap(container, mapConfig) {
      mapConfig.controls = ['fullscreenControl', 'typeSelector', 'zoomControl'];
      return new window.ymaps.Map(container, mapConfig);
    }
  }, {
    key: "createMarker",
    value: function createMarker(coordinates) {
      return new window.ymaps.Placemark(coordinates, {}, {
        draggable: true
      });
    }
  }, {
    key: "addMarkerToMap",
    value: function addMarkerToMap(marker) {
      this.map.geoObjects.add(marker);
    }
  }, {
    key: "deleteMarkerFromMap",
    value: function deleteMarkerFromMap(marker) {
      marker.setParent(null);
    }
  }, {
    key: "registerMapEventListeners",
    value: function registerMapEventListeners() {
      var _this2 = this;

      // Add marker
      this.map.events.add('click', function (e) {
        return _this2.createAndAddMarkerToMap(e.get('coords'));
      });
      this.map.events.add('boundschange', this.updateHiddenInputValue.bind(this));
    }
  }, {
    key: "registerMarkerEventListeners",
    value: function registerMarkerEventListeners(marker) {
      var _this3 = this;

      marker.events.add('dragend', this.updateHiddenInputValue.bind(this));
      marker.events.add('click', function () {
        return _this3.deleteMarker(marker);
      });
    }
  }, {
    key: "updateHiddenInputValue",
    value: function updateHiddenInputValue() {
      this.inputValue = {
        markers: this.markers.map(function (marker) {
          return marker.geometry.getCoordinates();
        }),
        zoom: this.map.getZoom(),
        center: this.map.getCenter()
      };
    }
  }, {
    key: "decodeCoordinates",
    value: function decodeCoordinates(coordinates) {
      return [coordinates.lat, coordinates.lng];
    }
  }, {
    key: "encodeCoordinates",
    value: function encodeCoordinates(coordinates) {
      return {
        lat: coordinates[0],
        lng: coordinates[1]
      };
    }
  }]);

  return YandexMultipleMarkersMap;
}(_MultipleMarkersMap__WEBPACK_IMPORTED_MODULE_0__["default"]);



/***/ }),

/***/ "./resources/assets/src/js/maps/yandex/YandexSingleMarkerMap.js":
/*!**********************************************************************!*\
  !*** ./resources/assets/src/js/maps/yandex/YandexSingleMarkerMap.js ***!
  \**********************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "default", function() { return YandexSingleMarkerMap; });
/* harmony import */ var _SingleMarkerMap__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../SingleMarkerMap */ "./resources/assets/src/js/maps/SingleMarkerMap.js");
function _typeof(obj) { "@babel/helpers - typeof"; if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } }

function _createClass(Constructor, protoProps, staticProps) { if (protoProps) _defineProperties(Constructor.prototype, protoProps); if (staticProps) _defineProperties(Constructor, staticProps); return Constructor; }

function _get(target, property, receiver) { if (typeof Reflect !== "undefined" && Reflect.get) { _get = Reflect.get; } else { _get = function _get(target, property, receiver) { var base = _superPropBase(target, property); if (!base) return; var desc = Object.getOwnPropertyDescriptor(base, property); if (desc.get) { return desc.get.call(receiver); } return desc.value; }; } return _get(target, property, receiver || target); }

function _superPropBase(object, property) { while (!Object.prototype.hasOwnProperty.call(object, property)) { object = _getPrototypeOf(object); if (object === null) break; } return object; }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function"); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, writable: true, configurable: true } }); if (superClass) _setPrototypeOf(subClass, superClass); }

function _setPrototypeOf(o, p) { _setPrototypeOf = Object.setPrototypeOf || function _setPrototypeOf(o, p) { o.__proto__ = p; return o; }; return _setPrototypeOf(o, p); }

function _createSuper(Derived) { var hasNativeReflectConstruct = _isNativeReflectConstruct(); return function _createSuperInternal() { var Super = _getPrototypeOf(Derived), result; if (hasNativeReflectConstruct) { var NewTarget = _getPrototypeOf(this).constructor; result = Reflect.construct(Super, arguments, NewTarget); } else { result = Super.apply(this, arguments); } return _possibleConstructorReturn(this, result); }; }

function _possibleConstructorReturn(self, call) { if (call && (_typeof(call) === "object" || typeof call === "function")) { return call; } return _assertThisInitialized(self); }

function _assertThisInitialized(self) { if (self === void 0) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return self; }

function _isNativeReflectConstruct() { if (typeof Reflect === "undefined" || !Reflect.construct) return false; if (Reflect.construct.sham) return false; if (typeof Proxy === "function") return true; try { Boolean.prototype.valueOf.call(Reflect.construct(Boolean, [], function () {})); return true; } catch (e) { return false; } }

function _getPrototypeOf(o) { _getPrototypeOf = Object.setPrototypeOf ? Object.getPrototypeOf : function _getPrototypeOf(o) { return o.__proto__ || Object.getPrototypeOf(o); }; return _getPrototypeOf(o); }



var YandexSingleMarkerMap = /*#__PURE__*/function (_SingleMarkerMap) {
  _inherits(YandexSingleMarkerMap, _SingleMarkerMap);

  var _super = _createSuper(YandexSingleMarkerMap);

  function YandexSingleMarkerMap() {
    _classCallCheck(this, YandexSingleMarkerMap);

    return _super.apply(this, arguments);
  }

  _createClass(YandexSingleMarkerMap, [{
    key: "main",
    value: function main() {
      var _this = this;

      window.ymaps.ready().then(function () {
        _get(_getPrototypeOf(YandexSingleMarkerMap.prototype), "main", _this).call(_this);
      })["catch"](function (e) {
        return console.error(e);
      });
    }
  }, {
    key: "createMap",
    value: function createMap(container, mapConfig) {
      mapConfig.controls = ['fullscreenControl', 'typeSelector', 'zoomControl'];
      return new window.ymaps.Map(container, mapConfig);
    }
  }, {
    key: "createMarker",
    value: function createMarker(coordinates) {
      return new window.ymaps.Placemark(coordinates, {}, {
        draggable: true
      });
    }
  }, {
    key: "addMarkerToMap",
    value: function addMarkerToMap(marker) {
      this.map.geoObjects.add(marker);
    }
  }, {
    key: "registerEventListeners",
    value: function registerEventListeners() {
      var _this2 = this;

      this.marker.events.add('dragend', this.updateHiddenInputValue.bind(this));
      this.map.events.add('boundschange', this.updateHiddenInputValue.bind(this));
      this.map.events.add('click', function (e) {
        _this2.marker.geometry.setCoordinates(e.get('coords'));

        _this2.updateHiddenInputValue();
      });
    }
  }, {
    key: "updateHiddenInputValue",
    value: function updateHiddenInputValue() {
      this.inputValue = {
        marker: this.marker.geometry.getCoordinates(),
        zoom: this.map.getZoom(),
        center: this.map.getCenter()
      };
    }
  }, {
    key: "decodeCoordinates",
    value: function decodeCoordinates(coordinates) {
      return [coordinates.lat, coordinates.lng];
    }
  }, {
    key: "encodeCoordinates",
    value: function encodeCoordinates(coordinates) {
      return {
        lat: coordinates[0],
        lng: coordinates[1]
      };
    }
  }, {
    key: "liveSearchSource",
    value: function liveSearchSource(request, response) {
      window.ymaps.geocode(request.term).then(function (res) {
        var result = [];
        res.geoObjects.each(function (item) {
          result.push({
            label: item.properties.get('text'),
            value: item.properties.get('text'),
            coordinates: item.geometry.getCoordinates()
          });
        });
        response(result);
      });
    }
  }, {
    key: "liveSearchSelect",
    value: function liveSearchSelect(event, ui) {
      this.map.setCenter(ui.item.coordinates);
      this.marker.geometry.setCoordinates(ui.item.coordinates);
      this.updateHiddenInputValue();
    }
  }]);

  return YandexSingleMarkerMap;
}(_SingleMarkerMap__WEBPACK_IMPORTED_MODULE_0__["default"]);



/***/ }),

/***/ "./resources/assets/src/plugins/bootstrap/css/bootstrap.scss":
/*!*******************************************************************!*\
  !*** ./resources/assets/src/plugins/bootstrap/css/bootstrap.scss ***!
  \*******************************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

// removed by extract-text-webpack-plugin

/***/ }),

/***/ "./resources/assets/src/plugins/select2/css/select2.scss":
/*!***************************************************************!*\
  !*** ./resources/assets/src/plugins/select2/css/select2.scss ***!
  \***************************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

// removed by extract-text-webpack-plugin

/***/ }),

/***/ "./resources/assets/src/plugins/sweetalert2/sweetalert2.all.js":
/*!*********************************************************************!*\
  !*** ./resources/assets/src/plugins/sweetalert2/sweetalert2.all.js ***!
  \*********************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

var __WEBPACK_AMD_DEFINE_FACTORY__, __WEBPACK_AMD_DEFINE_RESULT__;function _typeof2(obj) { "@babel/helpers - typeof"; if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof2 = function _typeof2(obj) { return typeof obj; }; } else { _typeof2 = function _typeof2(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof2(obj); }

/*!
* sweetalert2 v7.33.1
* Released under the MIT License.
*/
(function (global, factory) {
  ( false ? undefined : _typeof2(exports)) === 'object' && typeof module !== 'undefined' ? module.exports = factory() :  true ? !(__WEBPACK_AMD_DEFINE_FACTORY__ = (factory),
				__WEBPACK_AMD_DEFINE_RESULT__ = (typeof __WEBPACK_AMD_DEFINE_FACTORY__ === 'function' ?
				(__WEBPACK_AMD_DEFINE_FACTORY__.call(exports, __webpack_require__, exports, module)) :
				__WEBPACK_AMD_DEFINE_FACTORY__),
				__WEBPACK_AMD_DEFINE_RESULT__ !== undefined && (module.exports = __WEBPACK_AMD_DEFINE_RESULT__)) : undefined;
})(this, function () {
  'use strict';

  function _typeof(obj) {
    if (typeof Symbol === "function" && _typeof2(Symbol.iterator) === "symbol") {
      _typeof = function _typeof(obj) {
        return _typeof2(obj);
      };
    } else {
      _typeof = function _typeof(obj) {
        return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : _typeof2(obj);
      };
    }

    return _typeof(obj);
  }

  function _classCallCheck(instance, Constructor) {
    if (!(instance instanceof Constructor)) {
      throw new TypeError("Cannot call a class as a function");
    }
  }

  function _defineProperties(target, props) {
    for (var i = 0; i < props.length; i++) {
      var descriptor = props[i];
      descriptor.enumerable = descriptor.enumerable || false;
      descriptor.configurable = true;
      if ("value" in descriptor) descriptor.writable = true;
      Object.defineProperty(target, descriptor.key, descriptor);
    }
  }

  function _createClass(Constructor, protoProps, staticProps) {
    if (protoProps) _defineProperties(Constructor.prototype, protoProps);
    if (staticProps) _defineProperties(Constructor, staticProps);
    return Constructor;
  }

  function _extends() {
    _extends = Object.assign || function (target) {
      for (var i = 1; i < arguments.length; i++) {
        var source = arguments[i];

        for (var key in source) {
          if (Object.prototype.hasOwnProperty.call(source, key)) {
            target[key] = source[key];
          }
        }
      }

      return target;
    };

    return _extends.apply(this, arguments);
  }

  function _inherits(subClass, superClass) {
    if (typeof superClass !== "function" && superClass !== null) {
      throw new TypeError("Super expression must either be null or a function");
    }

    subClass.prototype = Object.create(superClass && superClass.prototype, {
      constructor: {
        value: subClass,
        writable: true,
        configurable: true
      }
    });
    if (superClass) _setPrototypeOf(subClass, superClass);
  }

  function _getPrototypeOf(o) {
    _getPrototypeOf = Object.setPrototypeOf ? Object.getPrototypeOf : function _getPrototypeOf(o) {
      return o.__proto__ || Object.getPrototypeOf(o);
    };
    return _getPrototypeOf(o);
  }

  function _setPrototypeOf(o, p) {
    _setPrototypeOf = Object.setPrototypeOf || function _setPrototypeOf(o, p) {
      o.__proto__ = p;
      return o;
    };

    return _setPrototypeOf(o, p);
  }

  function isNativeReflectConstruct() {
    if (typeof Reflect === "undefined" || !Reflect.construct) return false;
    if (Reflect.construct.sham) return false;
    if (typeof Proxy === "function") return true;

    try {
      Date.prototype.toString.call(Reflect.construct(Date, [], function () {}));
      return true;
    } catch (e) {
      return false;
    }
  }

  function _construct(Parent, args, Class) {
    if (isNativeReflectConstruct()) {
      _construct = Reflect.construct;
    } else {
      _construct = function _construct(Parent, args, Class) {
        var a = [null];
        a.push.apply(a, args);
        var Constructor = Function.bind.apply(Parent, a);
        var instance = new Constructor();
        if (Class) _setPrototypeOf(instance, Class.prototype);
        return instance;
      };
    }

    return _construct.apply(null, arguments);
  }

  function _assertThisInitialized(self) {
    if (self === void 0) {
      throw new ReferenceError("this hasn't been initialised - super() hasn't been called");
    }

    return self;
  }

  function _possibleConstructorReturn(self, call) {
    if (call && (_typeof2(call) === "object" || typeof call === "function")) {
      return call;
    }

    return _assertThisInitialized(self);
  }

  function _superPropBase(object, property) {
    while (!Object.prototype.hasOwnProperty.call(object, property)) {
      object = _getPrototypeOf(object);
      if (object === null) break;
    }

    return object;
  }

  function _get(target, property, receiver) {
    if (typeof Reflect !== "undefined" && Reflect.get) {
      _get = Reflect.get;
    } else {
      _get = function _get(target, property, receiver) {
        var base = _superPropBase(target, property);

        if (!base) return;
        var desc = Object.getOwnPropertyDescriptor(base, property);

        if (desc.get) {
          return desc.get.call(receiver);
        }

        return desc.value;
      };
    }

    return _get(target, property, receiver || target);
  }

  var consolePrefix = 'SweetAlert2:';
  /**
   * Filter the unique values into a new array
   * @param arr
   */

  var uniqueArray = function uniqueArray(arr) {
    var result = [];

    for (var i = 0; i < arr.length; i++) {
      if (result.indexOf(arr[i]) === -1) {
        result.push(arr[i]);
      }
    }

    return result;
  };
  /**
   * Convert NodeList to Array
   * @param nodeList
   */


  var toArray = function toArray(nodeList) {
    return Array.prototype.slice.call(nodeList);
  };
  /**
   * Converts `inputOptions` into an array of `[value, label]`s
   * @param inputOptions
   */


  var formatInputOptions = function formatInputOptions(inputOptions) {
    var result = [];

    if (typeof Map !== 'undefined' && inputOptions instanceof Map) {
      inputOptions.forEach(function (value, key) {
        result.push([key, value]);
      });
    } else {
      Object.keys(inputOptions).forEach(function (key) {
        result.push([key, inputOptions[key]]);
      });
    }

    return result;
  };
  /**
   * Standardise console warnings
   * @param message
   */


  var warn = function warn(message) {
    console.warn("".concat(consolePrefix, " ").concat(message));
  };
  /**
   * Standardise console errors
   * @param message
   */


  var error = function error(message) {
    console.error("".concat(consolePrefix, " ").concat(message));
  };
  /**
   * Private global state for `warnOnce`
   * @type {Array}
   * @private
   */


  var previousWarnOnceMessages = [];
  /**
   * Show a console warning, but only if it hasn't already been shown
   * @param message
   */

  var warnOnce = function warnOnce(message) {
    if (!(previousWarnOnceMessages.indexOf(message) !== -1)) {
      previousWarnOnceMessages.push(message);
      warn(message);
    }
  };
  /**
   * If `arg` is a function, call it (with no arguments or context) and return the result.
   * Otherwise, just pass the value through
   * @param arg
   */


  var callIfFunction = function callIfFunction(arg) {
    return typeof arg === 'function' ? arg() : arg;
  };

  var isPromise = function isPromise(arg) {
    return arg && Promise.resolve(arg) === arg;
  };

  var DismissReason = Object.freeze({
    cancel: 'cancel',
    backdrop: 'overlay',
    close: 'close',
    esc: 'esc',
    timer: 'timer'
  });

  var argsToParams = function argsToParams(args) {
    var params = {};

    switch (_typeof(args[0])) {
      case 'object':
        _extends(params, args[0]);

        break;

      default:
        ['title', 'html', 'type'].forEach(function (name, index) {
          switch (_typeof(args[index])) {
            case 'string':
              params[name] = args[index];
              break;

            case 'undefined':
              break;

            default:
              error("Unexpected type of ".concat(name, "! Expected \"string\", got ").concat(_typeof(args[index])));
          }
        });
    }

    return params;
  };
  /**
   * Adapt a legacy inputValidator for use with expectRejections=false
   */


  var adaptInputValidator = function adaptInputValidator(legacyValidator) {
    return function adaptedInputValidator(inputValue, extraParams) {
      return legacyValidator.call(this, inputValue, extraParams).then(function () {
        return undefined;
      }, function (validationMessage) {
        return validationMessage;
      });
    };
  };

  var swalPrefix = 'swal2-';

  var prefix = function prefix(items) {
    var result = {};

    for (var i in items) {
      result[items[i]] = swalPrefix + items[i];
    }

    return result;
  };

  var swalClasses = prefix(['container', 'shown', 'height-auto', 'iosfix', 'popup', 'modal', 'no-backdrop', 'toast', 'toast-shown', 'toast-column', 'fade', 'show', 'hide', 'noanimation', 'close', 'title', 'header', 'content', 'actions', 'confirm', 'cancel', 'footer', 'icon', 'icon-text', 'image', 'input', 'file', 'range', 'select', 'radio', 'checkbox', 'label', 'textarea', 'inputerror', 'validation-message', 'progresssteps', 'activeprogressstep', 'progresscircle', 'progressline', 'loading', 'styled', 'top', 'top-start', 'top-end', 'top-left', 'top-right', 'center', 'center-start', 'center-end', 'center-left', 'center-right', 'bottom', 'bottom-start', 'bottom-end', 'bottom-left', 'bottom-right', 'grow-row', 'grow-column', 'grow-fullscreen', 'rtl']);
  var iconTypes = prefix(['success', 'warning', 'info', 'question', 'error']);
  var states = {
    previousBodyPadding: null
  };

  var hasClass = function hasClass(elem, className) {
    return elem.classList.contains(className);
  };

  var focusInput = function focusInput(input) {
    input.focus(); // place cursor at end of text in text input

    if (input.type !== 'file') {
      // http://stackoverflow.com/a/2345915
      var val = input.value;
      input.value = '';
      input.value = val;
    }
  };

  var addOrRemoveClass = function addOrRemoveClass(target, classList, add) {
    if (!target || !classList) {
      return;
    }

    if (typeof classList === 'string') {
      classList = classList.split(/\s+/).filter(Boolean);
    }

    classList.forEach(function (className) {
      if (target.forEach) {
        target.forEach(function (elem) {
          add ? elem.classList.add(className) : elem.classList.remove(className);
        });
      } else {
        add ? target.classList.add(className) : target.classList.remove(className);
      }
    });
  };

  var addClass = function addClass(target, classList) {
    addOrRemoveClass(target, classList, true);
  };

  var removeClass = function removeClass(target, classList) {
    addOrRemoveClass(target, classList, false);
  };

  var getChildByClass = function getChildByClass(elem, className) {
    for (var i = 0; i < elem.childNodes.length; i++) {
      if (hasClass(elem.childNodes[i], className)) {
        return elem.childNodes[i];
      }
    }
  };

  var show = function show(elem) {
    elem.style.opacity = '';
    elem.style.display = elem.id === swalClasses.content ? 'block' : 'flex';
  };

  var hide = function hide(elem) {
    elem.style.opacity = '';
    elem.style.display = 'none';
  }; // borrowed from jquery $(elem).is(':visible') implementation


  var isVisible = function isVisible(elem) {
    return elem && (elem.offsetWidth || elem.offsetHeight || elem.getClientRects().length);
  };

  var contains = function contains(haystack, needle) {
    if (typeof haystack.contains === 'function') {
      return haystack.contains(needle);
    }
  };

  var getContainer = function getContainer() {
    return document.body.querySelector('.' + swalClasses.container);
  };

  var elementByClass = function elementByClass(className) {
    var container = getContainer();
    return container ? container.querySelector('.' + className) : null;
  };

  var getPopup = function getPopup() {
    return elementByClass(swalClasses.popup);
  };

  var getIcons = function getIcons() {
    var popup = getPopup();
    return toArray(popup.querySelectorAll('.' + swalClasses.icon));
  };

  var getTitle = function getTitle() {
    return elementByClass(swalClasses.title);
  };

  var getContent = function getContent() {
    return elementByClass(swalClasses.content);
  };

  var getImage = function getImage() {
    return elementByClass(swalClasses.image);
  };

  var getProgressSteps = function getProgressSteps() {
    return elementByClass(swalClasses.progresssteps);
  };

  var getValidationMessage = function getValidationMessage() {
    return elementByClass(swalClasses['validation-message']);
  };

  var getConfirmButton = function getConfirmButton() {
    return elementByClass(swalClasses.confirm);
  };

  var getCancelButton = function getCancelButton() {
    return elementByClass(swalClasses.cancel);
  };
  /* @deprecated */

  /* istanbul ignore next */


  var getButtonsWrapper = function getButtonsWrapper() {
    warnOnce("swal.getButtonsWrapper() is deprecated and will be removed in the next major release, use swal.getActions() instead");
    return elementByClass(swalClasses.actions);
  };

  var getActions = function getActions() {
    return elementByClass(swalClasses.actions);
  };

  var getFooter = function getFooter() {
    return elementByClass(swalClasses.footer);
  };

  var getCloseButton = function getCloseButton() {
    return elementByClass(swalClasses.close);
  };

  var getFocusableElements = function getFocusableElements() {
    var focusableElementsWithTabindex = toArray(getPopup().querySelectorAll('[tabindex]:not([tabindex="-1"]):not([tabindex="0"])')) // sort according to tabindex
    .sort(function (a, b) {
      a = parseInt(a.getAttribute('tabindex'));
      b = parseInt(b.getAttribute('tabindex'));

      if (a > b) {
        return 1;
      } else if (a < b) {
        return -1;
      }

      return 0;
    }); // https://github.com/jkup/focusable/blob/master/index.js

    var otherFocusableElements = toArray(getPopup().querySelectorAll('a[href], area[href], input:not([disabled]), select:not([disabled]), textarea:not([disabled]), button:not([disabled]), iframe, object, embed, [tabindex="0"], [contenteditable], audio[controls], video[controls]')).filter(function (el) {
      return el.getAttribute('tabindex') !== '-1';
    });
    return uniqueArray(focusableElementsWithTabindex.concat(otherFocusableElements)).filter(function (el) {
      return isVisible(el);
    });
  };

  var isModal = function isModal() {
    return !isToast() && !document.body.classList.contains(swalClasses['no-backdrop']);
  };

  var isToast = function isToast() {
    return document.body.classList.contains(swalClasses['toast-shown']);
  };

  var isLoading = function isLoading() {
    return getPopup().hasAttribute('data-loading');
  }; // Detect Node env


  var isNodeEnv = function isNodeEnv() {
    return typeof window === 'undefined' || typeof document === 'undefined';
  };

  var sweetHTML = "\n <div aria-labelledby=\"".concat(swalClasses.title, "\" aria-describedby=\"").concat(swalClasses.content, "\" class=\"").concat(swalClasses.popup, "\" tabindex=\"-1\">\n   <div class=\"").concat(swalClasses.header, "\">\n     <ul class=\"").concat(swalClasses.progresssteps, "\"></ul>\n     <div class=\"").concat(swalClasses.icon, " ").concat(iconTypes.error, "\">\n       <span class=\"swal2-x-mark\"><span class=\"swal2-x-mark-line-left\"></span><span class=\"swal2-x-mark-line-right\"></span></span>\n     </div>\n     <div class=\"").concat(swalClasses.icon, " ").concat(iconTypes.question, "\">\n       <span class=\"").concat(swalClasses['icon-text'], "\">?</span>\n      </div>\n     <div class=\"").concat(swalClasses.icon, " ").concat(iconTypes.warning, "\">\n       <span class=\"").concat(swalClasses['icon-text'], "\">!</span>\n      </div>\n     <div class=\"").concat(swalClasses.icon, " ").concat(iconTypes.info, "\">\n       <span class=\"").concat(swalClasses['icon-text'], "\">i</span>\n      </div>\n     <div class=\"").concat(swalClasses.icon, " ").concat(iconTypes.success, "\">\n       <div class=\"swal2-success-circular-line-left\"></div>\n       <span class=\"swal2-success-line-tip\"></span> <span class=\"swal2-success-line-long\"></span>\n       <div class=\"swal2-success-ring\"></div> <div class=\"swal2-success-fix\"></div>\n       <div class=\"swal2-success-circular-line-right\"></div>\n     </div>\n     <img class=\"").concat(swalClasses.image, "\" />\n     <h2 class=\"").concat(swalClasses.title, "\" id=\"").concat(swalClasses.title, "\"></h2>\n     <button type=\"button\" class=\"").concat(swalClasses.close, "\">\xD7</button>\n   </div>\n   <div class=\"").concat(swalClasses.content, "\">\n     <div id=\"").concat(swalClasses.content, "\"></div>\n     <input class=\"").concat(swalClasses.input, "\" />\n     <input type=\"file\" class=\"").concat(swalClasses.file, "\" />\n     <div class=\"").concat(swalClasses.range, "\">\n       <input type=\"range\" />\n       <output></output>\n     </div>\n     <select class=\"").concat(swalClasses.select, "\"></select>\n     <div class=\"").concat(swalClasses.radio, "\"></div>\n     <label for=\"").concat(swalClasses.checkbox, "\" class=\"").concat(swalClasses.checkbox, "\">\n       <input type=\"checkbox\" />\n       <span class=\"").concat(swalClasses.label, "\"></span>\n     </label>\n     <textarea class=\"").concat(swalClasses.textarea, "\"></textarea>\n     <div class=\"").concat(swalClasses['validation-message'], "\" id=\"").concat(swalClasses['validation-message'], "\"></div>\n   </div>\n   <div class=\"").concat(swalClasses.actions, "\">\n     <button type=\"button\" class=\"").concat(swalClasses.confirm, "\">OK</button>\n     <button type=\"button\" class=\"").concat(swalClasses.cancel, "\">Cancel</button>\n   </div>\n   <div class=\"").concat(swalClasses.footer, "\">\n   </div>\n </div>\n").replace(/(^|\n)\s*/g, '');
  /*
   * Add modal + backdrop to DOM
   */

  var init = function init(params) {
    // Clean up the old popup if it exists
    var c = getContainer();

    if (c) {
      c.parentNode.removeChild(c);
      removeClass([document.documentElement, document.body], [swalClasses['no-backdrop'], swalClasses['toast-shown'], swalClasses['has-column']]);
    }
    /* istanbul ignore if */


    if (isNodeEnv()) {
      error('SweetAlert2 requires document to initialize');
      return;
    }

    var container = document.createElement('div');
    container.className = swalClasses.container;
    container.innerHTML = sweetHTML;
    var targetElement = typeof params.target === 'string' ? document.querySelector(params.target) : params.target;
    targetElement.appendChild(container);
    var popup = getPopup();
    var content = getContent();
    var input = getChildByClass(content, swalClasses.input);
    var file = getChildByClass(content, swalClasses.file);
    var range = content.querySelector(".".concat(swalClasses.range, " input"));
    var rangeOutput = content.querySelector(".".concat(swalClasses.range, " output"));
    var select = getChildByClass(content, swalClasses.select);
    var checkbox = content.querySelector(".".concat(swalClasses.checkbox, " input"));
    var textarea = getChildByClass(content, swalClasses.textarea); // a11y

    popup.setAttribute('role', params.toast ? 'alert' : 'dialog');
    popup.setAttribute('aria-live', params.toast ? 'polite' : 'assertive');

    if (!params.toast) {
      popup.setAttribute('aria-modal', 'true');
    } // RTL


    if (window.getComputedStyle(targetElement).direction === 'rtl') {
      addClass(getContainer(), swalClasses.rtl);
    }

    var oldInputVal; // IE11 workaround, see #1109 for details

    var resetValidationMessage = function resetValidationMessage(e) {
      if (Swal.isVisible() && oldInputVal !== e.target.value) {
        Swal.resetValidationMessage();
      }

      oldInputVal = e.target.value;
    };

    input.oninput = resetValidationMessage;
    file.onchange = resetValidationMessage;
    select.onchange = resetValidationMessage;
    checkbox.onchange = resetValidationMessage;
    textarea.oninput = resetValidationMessage;

    range.oninput = function (e) {
      resetValidationMessage(e);
      rangeOutput.value = range.value;
    };

    range.onchange = function (e) {
      resetValidationMessage(e);
      range.nextSibling.value = range.value;
    };

    return popup;
  };

  var parseHtmlToContainer = function parseHtmlToContainer(param, target) {
    if (!param) {
      return hide(target);
    } // DOM element


    if (param instanceof HTMLElement) {
      target.appendChild(param); // JQuery element(s)
    } else if (_typeof(param) === 'object') {
      target.innerHTML = '';

      if (0 in param) {
        for (var i = 0; (i in param); i++) {
          target.appendChild(param[i].cloneNode(true));
        }
      } else {
        target.appendChild(param.cloneNode(true));
      }
    } else if (param) {
      target.innerHTML = param;
    }

    show(target);
  };

  var animationEndEvent = function () {
    // Prevent run in Node env

    /* istanbul ignore if */
    if (isNodeEnv()) {
      return false;
    }

    var testEl = document.createElement('div');
    var transEndEventNames = {
      'WebkitAnimation': 'webkitAnimationEnd',
      'OAnimation': 'oAnimationEnd oanimationend',
      'animation': 'animationend'
    };

    for (var i in transEndEventNames) {
      if (transEndEventNames.hasOwnProperty(i) && typeof testEl.style[i] !== 'undefined') {
        return transEndEventNames[i];
      }
    }

    return false;
  }(); // Measure width of scrollbar
  // https://github.com/twbs/bootstrap/blob/master/js/modal.js#L279-L286


  var measureScrollbar = function measureScrollbar() {
    var supportsTouch = 'ontouchstart' in window || navigator.msMaxTouchPoints;

    if (supportsTouch) {
      return 0;
    }

    var scrollDiv = document.createElement('div');
    scrollDiv.style.width = '50px';
    scrollDiv.style.height = '50px';
    scrollDiv.style.overflow = 'scroll';
    document.body.appendChild(scrollDiv);
    var scrollbarWidth = scrollDiv.offsetWidth - scrollDiv.clientWidth;
    document.body.removeChild(scrollDiv);
    return scrollbarWidth;
  };

  var renderActions = function renderActions(params) {
    var actions = getActions();
    var confirmButton = getConfirmButton();
    var cancelButton = getCancelButton(); // Actions (buttons) wrapper

    if (!params.showConfirmButton && !params.showCancelButton) {
      hide(actions);
    } else {
      show(actions);
    } // Cancel button


    if (params.showCancelButton) {
      cancelButton.style.display = 'inline-block';
    } else {
      hide(cancelButton);
    } // Confirm button


    if (params.showConfirmButton) {
      confirmButton.style.removeProperty('display');
    } else {
      hide(confirmButton);
    } // Edit text on confirm and cancel buttons


    confirmButton.innerHTML = params.confirmButtonText;
    cancelButton.innerHTML = params.cancelButtonText; // ARIA labels for confirm and cancel buttons

    confirmButton.setAttribute('aria-label', params.confirmButtonAriaLabel);
    cancelButton.setAttribute('aria-label', params.cancelButtonAriaLabel); // Add buttons custom classes

    confirmButton.className = swalClasses.confirm;
    addClass(confirmButton, params.confirmButtonClass);
    cancelButton.className = swalClasses.cancel;
    addClass(cancelButton, params.cancelButtonClass); // Buttons styling

    if (params.buttonsStyling) {
      addClass([confirmButton, cancelButton], swalClasses.styled); // Buttons background colors

      if (params.confirmButtonColor) {
        confirmButton.style.backgroundColor = params.confirmButtonColor;
      }

      if (params.cancelButtonColor) {
        cancelButton.style.backgroundColor = params.cancelButtonColor;
      } // Loading state


      var confirmButtonBackgroundColor = window.getComputedStyle(confirmButton).getPropertyValue('background-color');
      confirmButton.style.borderLeftColor = confirmButtonBackgroundColor;
      confirmButton.style.borderRightColor = confirmButtonBackgroundColor;
    } else {
      removeClass([confirmButton, cancelButton], swalClasses.styled);
      confirmButton.style.backgroundColor = confirmButton.style.borderLeftColor = confirmButton.style.borderRightColor = '';
      cancelButton.style.backgroundColor = cancelButton.style.borderLeftColor = cancelButton.style.borderRightColor = '';
    }
  };

  var renderContent = function renderContent(params) {
    var content = getContent().querySelector('#' + swalClasses.content); // Content as HTML

    if (params.html) {
      parseHtmlToContainer(params.html, content); // Content as plain text
    } else if (params.text) {
      content.textContent = params.text;
      show(content);
    } else {
      hide(content);
    }
  };

  var renderIcon = function renderIcon(params) {
    var icons = getIcons();

    for (var i = 0; i < icons.length; i++) {
      hide(icons[i]);
    }

    if (params.type) {
      if (Object.keys(iconTypes).indexOf(params.type) !== -1) {
        var icon = Swal.getPopup().querySelector(".".concat(swalClasses.icon, ".").concat(iconTypes[params.type]));
        show(icon); // Animate icon

        if (params.animation) {
          addClass(icon, "swal2-animate-".concat(params.type, "-icon"));
        }
      } else {
        error("Unknown type! Expected \"success\", \"error\", \"warning\", \"info\" or \"question\", got \"".concat(params.type, "\""));
      }
    }
  };

  var renderImage = function renderImage(params) {
    var image = getImage();

    if (params.imageUrl) {
      image.setAttribute('src', params.imageUrl);
      image.setAttribute('alt', params.imageAlt);
      show(image);

      if (params.imageWidth) {
        image.setAttribute('width', params.imageWidth);
      } else {
        image.removeAttribute('width');
      }

      if (params.imageHeight) {
        image.setAttribute('height', params.imageHeight);
      } else {
        image.removeAttribute('height');
      }

      image.className = swalClasses.image;

      if (params.imageClass) {
        addClass(image, params.imageClass);
      }
    } else {
      hide(image);
    }
  };

  var renderProgressSteps = function renderProgressSteps(params) {
    var progressStepsContainer = getProgressSteps();
    var currentProgressStep = parseInt(params.currentProgressStep === null ? Swal.getQueueStep() : params.currentProgressStep, 10);

    if (params.progressSteps && params.progressSteps.length) {
      show(progressStepsContainer);
      progressStepsContainer.innerHTML = '';

      if (currentProgressStep >= params.progressSteps.length) {
        warn('Invalid currentProgressStep parameter, it should be less than progressSteps.length ' + '(currentProgressStep like JS arrays starts from 0)');
      }

      params.progressSteps.forEach(function (step, index) {
        var circle = document.createElement('li');
        addClass(circle, swalClasses.progresscircle);
        circle.innerHTML = step;

        if (index === currentProgressStep) {
          addClass(circle, swalClasses.activeprogressstep);
        }

        progressStepsContainer.appendChild(circle);

        if (index !== params.progressSteps.length - 1) {
          var line = document.createElement('li');
          addClass(line, swalClasses.progressline);

          if (params.progressStepsDistance) {
            line.style.width = params.progressStepsDistance;
          }

          progressStepsContainer.appendChild(line);
        }
      });
    } else {
      hide(progressStepsContainer);
    }
  };

  var renderTitle = function renderTitle(params) {
    var title = getTitle();

    if (params.titleText) {
      title.innerText = params.titleText;
    } else if (params.title) {
      if (typeof params.title === 'string') {
        params.title = params.title.split('\n').join('<br />');
      }

      parseHtmlToContainer(params.title, title);
    }
  };

  var fixScrollbar = function fixScrollbar() {
    // for queues, do not do this more than once
    if (states.previousBodyPadding !== null) {
      return;
    } // if the body has overflow


    if (document.body.scrollHeight > window.innerHeight) {
      // add padding so the content doesn't shift after removal of scrollbar
      states.previousBodyPadding = parseInt(window.getComputedStyle(document.body).getPropertyValue('padding-right'));
      document.body.style.paddingRight = states.previousBodyPadding + measureScrollbar() + 'px';
    }
  };

  var undoScrollbar = function undoScrollbar() {
    if (states.previousBodyPadding !== null) {
      document.body.style.paddingRight = states.previousBodyPadding;
      states.previousBodyPadding = null;
    }
  };
  /* istanbul ignore next */


  var iOSfix = function iOSfix() {
    var iOS = /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream;

    if (iOS && !hasClass(document.body, swalClasses.iosfix)) {
      var offset = document.body.scrollTop;
      document.body.style.top = offset * -1 + 'px';
      addClass(document.body, swalClasses.iosfix);
    }
  };
  /* istanbul ignore next */


  var undoIOSfix = function undoIOSfix() {
    if (hasClass(document.body, swalClasses.iosfix)) {
      var offset = parseInt(document.body.style.top, 10);
      removeClass(document.body, swalClasses.iosfix);
      document.body.style.top = '';
      document.body.scrollTop = offset * -1;
    }
  };

  var isIE11 = function isIE11() {
    return !!window.MSInputMethodContext && !!document.documentMode;
  }; // Fix IE11 centering sweetalert2/issues/933

  /* istanbul ignore next */


  var fixVerticalPositionIE = function fixVerticalPositionIE() {
    var container = getContainer();
    var popup = getPopup();
    container.style.removeProperty('align-items');

    if (popup.offsetTop < 0) {
      container.style.alignItems = 'flex-start';
    }
  };
  /* istanbul ignore next */


  var IEfix = function IEfix() {
    if (typeof window !== 'undefined' && isIE11()) {
      fixVerticalPositionIE();
      window.addEventListener('resize', fixVerticalPositionIE);
    }
  };
  /* istanbul ignore next */


  var undoIEfix = function undoIEfix() {
    if (typeof window !== 'undefined' && isIE11()) {
      window.removeEventListener('resize', fixVerticalPositionIE);
    }
  }; // Adding aria-hidden="true" to elements outside of the active modal dialog ensures that
  // elements not within the active modal dialog will not be surfaced if a user opens a screen
  // reader’s list of elements (headings, form controls, landmarks, etc.) in the document.


  var setAriaHidden = function setAriaHidden() {
    var bodyChildren = toArray(document.body.children);
    bodyChildren.forEach(function (el) {
      if (el === getContainer() || contains(el, getContainer())) {
        return;
      }

      if (el.hasAttribute('aria-hidden')) {
        el.setAttribute('data-previous-aria-hidden', el.getAttribute('aria-hidden'));
      }

      el.setAttribute('aria-hidden', 'true');
    });
  };

  var unsetAriaHidden = function unsetAriaHidden() {
    var bodyChildren = toArray(document.body.children);
    bodyChildren.forEach(function (el) {
      if (el.hasAttribute('data-previous-aria-hidden')) {
        el.setAttribute('aria-hidden', el.getAttribute('data-previous-aria-hidden'));
        el.removeAttribute('data-previous-aria-hidden');
      } else {
        el.removeAttribute('aria-hidden');
      }
    });
  };

  var RESTORE_FOCUS_TIMEOUT = 100;
  var globalState = {};

  var restoreActiveElement = function restoreActiveElement() {
    return new Promise(function (resolve) {
      var x = window.scrollX;
      var y = window.scrollY;
      globalState.restoreFocusTimeout = setTimeout(function () {
        if (globalState.previousActiveElement && globalState.previousActiveElement.focus) {
          globalState.previousActiveElement.focus();
          globalState.previousActiveElement = null;
        } else if (document.body) {
          document.body.focus();
        }

        resolve();
      }, RESTORE_FOCUS_TIMEOUT); // issues/900

      if (typeof x !== 'undefined' && typeof y !== 'undefined') {
        // IE doesn't have scrollX/scrollY support
        window.scrollTo(x, y);
      }
    });
  };
  /*
   * Global function to close sweetAlert
   */


  var close = function close(onClose, onAfterClose) {
    var container = getContainer();
    var popup = getPopup();

    if (!popup) {
      return;
    }

    if (onClose !== null && typeof onClose === 'function') {
      onClose(popup);
    }

    removeClass(popup, swalClasses.show);
    addClass(popup, swalClasses.hide);

    var removePopupAndResetState = function removePopupAndResetState() {
      if (!isToast()) {
        restoreActiveElement().then(function () {
          return triggerOnAfterClose(onAfterClose);
        });
        globalState.keydownTarget.removeEventListener('keydown', globalState.keydownHandler, {
          capture: globalState.keydownListenerCapture
        });
        globalState.keydownHandlerAdded = false;
      } else {
        triggerOnAfterClose(onAfterClose);
      }

      if (container.parentNode) {
        container.parentNode.removeChild(container);
      }

      removeClass([document.documentElement, document.body], [swalClasses.shown, swalClasses['height-auto'], swalClasses['no-backdrop'], swalClasses['toast-shown'], swalClasses['toast-column']]);

      if (isModal()) {
        undoScrollbar();
        undoIOSfix();
        undoIEfix();
        unsetAriaHidden();
      }
    }; // If animation is supported, animate


    if (animationEndEvent && !hasClass(popup, swalClasses.noanimation)) {
      popup.addEventListener(animationEndEvent, function swalCloseEventFinished() {
        popup.removeEventListener(animationEndEvent, swalCloseEventFinished);

        if (hasClass(popup, swalClasses.hide)) {
          removePopupAndResetState();
        }
      });
    } else {
      // Otherwise, remove immediately
      removePopupAndResetState();
    }
  };

  var triggerOnAfterClose = function triggerOnAfterClose(onAfterClose) {
    if (onAfterClose !== null && typeof onAfterClose === 'function') {
      setTimeout(function () {
        onAfterClose();
      });
    }
  };
  /*
   * Global function to determine if swal2 popup is shown
   */


  var isVisible$1 = function isVisible() {
    return !!getPopup();
  };
  /*
   * Global function to click 'Confirm' button
   */


  var clickConfirm = function clickConfirm() {
    return getConfirmButton().click();
  };
  /*
   * Global function to click 'Cancel' button
   */


  var clickCancel = function clickCancel() {
    return getCancelButton().click();
  };

  function fire() {
    var Swal = this;

    for (var _len = arguments.length, args = new Array(_len), _key = 0; _key < _len; _key++) {
      args[_key] = arguments[_key];
    }

    return _construct(Swal, args);
  }
  /**
   * Extends a Swal class making it able to be instantiated without the `new` keyword (and thus without `Swal.fire`)
   * @param ParentSwal
   * @returns {NoNewKeywordSwal}
   */


  function withNoNewKeyword(ParentSwal) {
    var NoNewKeywordSwal = function NoNewKeywordSwal() {
      for (var _len = arguments.length, args = new Array(_len), _key = 0; _key < _len; _key++) {
        args[_key] = arguments[_key];
      }

      if (!(this instanceof NoNewKeywordSwal)) {
        return _construct(NoNewKeywordSwal, args);
      }

      Object.getPrototypeOf(NoNewKeywordSwal).apply(this, args);
    };

    NoNewKeywordSwal.prototype = _extends(Object.create(ParentSwal.prototype), {
      constructor: NoNewKeywordSwal
    });

    if (typeof Object.setPrototypeOf === 'function') {
      Object.setPrototypeOf(NoNewKeywordSwal, ParentSwal);
    } else {
      // Android 4.4

      /* istanbul ignore next */
      // eslint-disable-next-line
      NoNewKeywordSwal.__proto__ = ParentSwal;
    }

    return NoNewKeywordSwal;
  }

  var defaultParams = {
    title: '',
    titleText: '',
    text: '',
    html: '',
    footer: '',
    type: null,
    toast: false,
    customClass: '',
    customContainerClass: '',
    target: 'body',
    backdrop: true,
    animation: true,
    heightAuto: true,
    allowOutsideClick: true,
    allowEscapeKey: true,
    allowEnterKey: true,
    stopKeydownPropagation: true,
    keydownListenerCapture: false,
    showConfirmButton: true,
    showCancelButton: false,
    preConfirm: null,
    confirmButtonText: 'OK',
    confirmButtonAriaLabel: '',
    confirmButtonColor: null,
    confirmButtonClass: null,
    cancelButtonText: 'Cancel',
    cancelButtonAriaLabel: '',
    cancelButtonColor: null,
    cancelButtonClass: null,
    buttonsStyling: true,
    reverseButtons: false,
    focusConfirm: true,
    focusCancel: false,
    showCloseButton: false,
    closeButtonAriaLabel: 'Close this dialog',
    showLoaderOnConfirm: false,
    imageUrl: null,
    imageWidth: null,
    imageHeight: null,
    imageAlt: '',
    imageClass: null,
    timer: null,
    width: null,
    padding: null,
    background: null,
    input: null,
    inputPlaceholder: '',
    inputValue: '',
    inputOptions: {},
    inputAutoTrim: true,
    inputClass: null,
    inputAttributes: {},
    inputValidator: null,
    validationMessage: null,
    grow: false,
    position: 'center',
    progressSteps: [],
    currentProgressStep: null,
    progressStepsDistance: null,
    onBeforeOpen: null,
    onAfterClose: null,
    onOpen: null,
    onClose: null,
    useRejections: false,
    expectRejections: false
  };
  var deprecatedParams = ['useRejections', 'expectRejections', 'extraParams'];
  var toastIncompatibleParams = ['allowOutsideClick', 'allowEnterKey', 'backdrop', 'focusConfirm', 'focusCancel', 'heightAuto', 'keydownListenerCapture'];
  /**
   * Is valid parameter
   * @param {String} paramName
   */

  var isValidParameter = function isValidParameter(paramName) {
    return defaultParams.hasOwnProperty(paramName) || paramName === 'extraParams';
  };
  /**
   * Is deprecated parameter
   * @param {String} paramName
   */


  var isDeprecatedParameter = function isDeprecatedParameter(paramName) {
    return deprecatedParams.indexOf(paramName) !== -1;
  };
  /**
   * Show relevant warnings for given params
   *
   * @param params
   */


  var showWarningsForParams = function showWarningsForParams(params) {
    for (var param in params) {
      if (!isValidParameter(param)) {
        warn("Unknown parameter \"".concat(param, "\""));
      }

      if (params.toast && toastIncompatibleParams.indexOf(param) !== -1) {
        warn("The parameter \"".concat(param, "\" is incompatible with toasts"));
      }

      if (isDeprecatedParameter(param)) {
        warnOnce("The parameter \"".concat(param, "\" is deprecated and will be removed in the next major release."));
      }
    }
  };

  var deprecationWarning = "\"setDefaults\" & \"resetDefaults\" methods are deprecated in favor of \"mixin\" method and will be removed in the next major release. For new projects, use \"mixin\". For past projects already using \"setDefaults\", support will be provided through an additional package.";
  var defaults = {};

  function withGlobalDefaults(ParentSwal) {
    var SwalWithGlobalDefaults = /*#__PURE__*/function (_ParentSwal) {
      _inherits(SwalWithGlobalDefaults, _ParentSwal);

      function SwalWithGlobalDefaults() {
        _classCallCheck(this, SwalWithGlobalDefaults);

        return _possibleConstructorReturn(this, _getPrototypeOf(SwalWithGlobalDefaults).apply(this, arguments));
      }

      _createClass(SwalWithGlobalDefaults, [{
        key: "_main",
        value: function _main(params) {
          return _get(_getPrototypeOf(SwalWithGlobalDefaults.prototype), "_main", this).call(this, _extends({}, defaults, params));
        }
      }], [{
        key: "setDefaults",
        value: function setDefaults(params) {
          warnOnce(deprecationWarning);

          if (!params || _typeof(params) !== 'object') {
            throw new TypeError('SweetAlert2: The argument for setDefaults() is required and has to be a object');
          }

          showWarningsForParams(params); // assign valid params from `params` to `defaults`

          Object.keys(params).forEach(function (param) {
            if (ParentSwal.isValidParameter(param)) {
              defaults[param] = params[param];
            }
          });
        }
      }, {
        key: "resetDefaults",
        value: function resetDefaults() {
          warnOnce(deprecationWarning);
          defaults = {};
        }
      }]);

      return SwalWithGlobalDefaults;
    }(ParentSwal); // Set default params if `window._swalDefaults` is an object


    if (typeof window !== 'undefined' && _typeof(window._swalDefaults) === 'object') {
      SwalWithGlobalDefaults.setDefaults(window._swalDefaults);
    }

    return SwalWithGlobalDefaults;
  }
  /**
   * Returns an extended version of `Swal` containing `params` as defaults.
   * Useful for reusing Swal configuration.
   *
   * For example:
   *
   * Before:
   * const textPromptOptions = { input: 'text', showCancelButton: true }
   * const {value: firstName} = await Swal({ ...textPromptOptions, title: 'What is your first name?' })
   * const {value: lastName} = await Swal({ ...textPromptOptions, title: 'What is your last name?' })
   *
   * After:
   * const TextPrompt = Swal.mixin({ input: 'text', showCancelButton: true })
   * const {value: firstName} = await TextPrompt('What is your first name?')
   * const {value: lastName} = await TextPrompt('What is your last name?')
   *
   * @param mixinParams
   */


  function mixin(mixinParams) {
    return withNoNewKeyword( /*#__PURE__*/function (_this) {
      _inherits(MixinSwal, _this);

      function MixinSwal() {
        _classCallCheck(this, MixinSwal);

        return _possibleConstructorReturn(this, _getPrototypeOf(MixinSwal).apply(this, arguments));
      }

      _createClass(MixinSwal, [{
        key: "_main",
        value: function _main(params) {
          return _get(_getPrototypeOf(MixinSwal.prototype), "_main", this).call(this, _extends({}, mixinParams, params));
        }
      }]);

      return MixinSwal;
    }(this));
  } // private global state for the queue feature


  var currentSteps = [];
  /*
   * Global function for chaining sweetAlert popups
   */

  var queue = function queue(steps) {
    var swal = this;
    currentSteps = steps;

    var resetQueue = function resetQueue() {
      currentSteps = [];
      document.body.removeAttribute('data-swal2-queue-step');
    };

    var queueResult = [];
    return new Promise(function (resolve) {
      (function step(i, callback) {
        if (i < currentSteps.length) {
          document.body.setAttribute('data-swal2-queue-step', i);
          swal(currentSteps[i]).then(function (result) {
            if (typeof result.value !== 'undefined') {
              queueResult.push(result.value);
              step(i + 1, callback);
            } else {
              resetQueue();
              resolve({
                dismiss: result.dismiss
              });
            }
          });
        } else {
          resetQueue();
          resolve({
            value: queueResult
          });
        }
      })(0);
    });
  };
  /*
   * Global function for getting the index of current popup in queue
   */


  var getQueueStep = function getQueueStep() {
    return document.body.getAttribute('data-swal2-queue-step');
  };
  /*
   * Global function for inserting a popup to the queue
   */


  var insertQueueStep = function insertQueueStep(step, index) {
    if (index && index < currentSteps.length) {
      return currentSteps.splice(index, 0, step);
    }

    return currentSteps.push(step);
  };
  /*
   * Global function for deleting a popup from the queue
   */


  var deleteQueueStep = function deleteQueueStep(index) {
    if (typeof currentSteps[index] !== 'undefined') {
      currentSteps.splice(index, 1);
    }
  };
  /**
   * Show spinner instead of Confirm button and disable Cancel button
   */


  var showLoading = function showLoading() {
    var popup = getPopup();

    if (!popup) {
      Swal('');
    }

    popup = getPopup();
    var actions = getActions();
    var confirmButton = getConfirmButton();
    var cancelButton = getCancelButton();
    show(actions);
    show(confirmButton);
    addClass([popup, actions], swalClasses.loading);
    confirmButton.disabled = true;
    cancelButton.disabled = true;
    popup.setAttribute('data-loading', true);
    popup.setAttribute('aria-busy', true);
    popup.focus();
  };
  /**
   * If `timer` parameter is set, returns number of milliseconds of timer remained.
   * Otherwise, returns undefined.
   */


  var getTimerLeft = function getTimerLeft() {
    return globalState.timeout && globalState.timeout.getTimerLeft();
  };
  /**
   * Stop timer. Returns number of milliseconds of timer remained.
   * If `timer` parameter isn't set, returns undefined.
   */


  var stopTimer = function stopTimer() {
    return globalState.timeout && globalState.timeout.stop();
  };
  /**
   * Resume timer. Returns number of milliseconds of timer remained.
   * If `timer` parameter isn't set, returns undefined.
   */


  var resumeTimer = function resumeTimer() {
    return globalState.timeout && globalState.timeout.start();
  };
  /**
   * Resume timer. Returns number of milliseconds of timer remained.
   * If `timer` parameter isn't set, returns undefined.
   */


  var toggleTimer = function toggleTimer() {
    var timer = globalState.timeout;
    return timer && (timer.running ? timer.stop() : timer.start());
  };
  /**
   * Increase timer. Returns number of milliseconds of an updated timer.
   * If `timer` parameter isn't set, returns undefined.
   */


  var increaseTimer = function increaseTimer(n) {
    return globalState.timeout && globalState.timeout.increase(n);
  };
  /**
   * Check if timer is running. Returns true if timer is running
   * or false if timer is paused or stopped.
   * If `timer` parameter isn't set, returns undefined
   */


  var isTimerRunning = function isTimerRunning() {
    return globalState.timeout && globalState.timeout.isRunning();
  };

  var staticMethods = Object.freeze({
    isValidParameter: isValidParameter,
    isDeprecatedParameter: isDeprecatedParameter,
    argsToParams: argsToParams,
    adaptInputValidator: adaptInputValidator,
    close: close,
    closePopup: close,
    closeModal: close,
    closeToast: close,
    isVisible: isVisible$1,
    clickConfirm: clickConfirm,
    clickCancel: clickCancel,
    getContainer: getContainer,
    getPopup: getPopup,
    getTitle: getTitle,
    getContent: getContent,
    getImage: getImage,
    getIcons: getIcons,
    getCloseButton: getCloseButton,
    getButtonsWrapper: getButtonsWrapper,
    getActions: getActions,
    getConfirmButton: getConfirmButton,
    getCancelButton: getCancelButton,
    getFooter: getFooter,
    getFocusableElements: getFocusableElements,
    getValidationMessage: getValidationMessage,
    isLoading: isLoading,
    fire: fire,
    mixin: mixin,
    queue: queue,
    getQueueStep: getQueueStep,
    insertQueueStep: insertQueueStep,
    deleteQueueStep: deleteQueueStep,
    showLoading: showLoading,
    enableLoading: showLoading,
    getTimerLeft: getTimerLeft,
    stopTimer: stopTimer,
    resumeTimer: resumeTimer,
    toggleTimer: toggleTimer,
    increaseTimer: increaseTimer,
    isTimerRunning: isTimerRunning
  }); // https://github.com/Riim/symbol-polyfill/blob/master/index.js

  /* istanbul ignore next */

  var _Symbol = typeof Symbol === 'function' ? Symbol : function () {
    var idCounter = 0;

    function _Symbol(key) {
      return '__' + key + '_' + Math.floor(Math.random() * 1e9) + '_' + ++idCounter + '__';
    }

    _Symbol.iterator = _Symbol('Symbol.iterator');
    return _Symbol;
  }(); // WeakMap polyfill, needed for Android 4.4
  // Related issue: https://github.com/sweetalert2/sweetalert2/issues/1071
  // http://webreflection.blogspot.fi/2015/04/a-weakmap-polyfill-in-20-lines-of-code.html

  /* istanbul ignore next */


  var WeakMap$1 = typeof WeakMap === 'function' ? WeakMap : function (s, dP, hOP) {
    function WeakMap() {
      dP(this, s, {
        value: _Symbol('WeakMap')
      });
    }

    WeakMap.prototype = {
      'delete': function del(o) {
        delete o[this[s]];
      },
      get: function get(o) {
        return o[this[s]];
      },
      has: function has(o) {
        return hOP.call(o, this[s]);
      },
      set: function set(o, v) {
        dP(o, this[s], {
          configurable: true,
          value: v
        });
      }
    };
    return WeakMap;
  }(_Symbol('WeakMap'), Object.defineProperty, {}.hasOwnProperty);
  /**
   * This module containts `WeakMap`s for each effectively-"private  property" that a `swal` has.
   * For example, to set the private property "foo" of `this` to "bar", you can `privateProps.foo.set(this, 'bar')`
   * This is the approach that Babel will probably take to implement private methods/fields
   *   https://github.com/tc39/proposal-private-methods
   *   https://github.com/babel/babel/pull/7555
   * Once we have the changes from that PR in Babel, and our core class fits reasonable in *one module*
   *   then we can use that language feature.
   */

  var privateProps = {
    promise: new WeakMap$1(),
    innerParams: new WeakMap$1(),
    domCache: new WeakMap$1()
  };
  /**
   * Enables buttons and hide loader.
   */

  function hideLoading() {
    var innerParams = privateProps.innerParams.get(this);
    var domCache = privateProps.domCache.get(this);

    if (!innerParams.showConfirmButton) {
      hide(domCache.confirmButton);

      if (!innerParams.showCancelButton) {
        hide(domCache.actions);
      }
    }

    removeClass([domCache.popup, domCache.actions], swalClasses.loading);
    domCache.popup.removeAttribute('aria-busy');
    domCache.popup.removeAttribute('data-loading');
    domCache.confirmButton.disabled = false;
    domCache.cancelButton.disabled = false;
  }

  function getInput(inputType) {
    var innerParams = privateProps.innerParams.get(this);
    var domCache = privateProps.domCache.get(this);
    inputType = inputType || innerParams.input;

    if (!inputType) {
      return null;
    }

    switch (inputType) {
      case 'select':
      case 'textarea':
      case 'file':
        return getChildByClass(domCache.content, swalClasses[inputType]);

      case 'checkbox':
        return domCache.popup.querySelector(".".concat(swalClasses.checkbox, " input"));

      case 'radio':
        return domCache.popup.querySelector(".".concat(swalClasses.radio, " input:checked")) || domCache.popup.querySelector(".".concat(swalClasses.radio, " input:first-child"));

      case 'range':
        return domCache.popup.querySelector(".".concat(swalClasses.range, " input"));

      default:
        return getChildByClass(domCache.content, swalClasses.input);
    }
  }

  function enableButtons() {
    var domCache = privateProps.domCache.get(this);
    domCache.confirmButton.disabled = false;
    domCache.cancelButton.disabled = false;
  }

  function disableButtons() {
    var domCache = privateProps.domCache.get(this);
    domCache.confirmButton.disabled = true;
    domCache.cancelButton.disabled = true;
  }

  function enableConfirmButton() {
    var domCache = privateProps.domCache.get(this);
    domCache.confirmButton.disabled = false;
  }

  function disableConfirmButton() {
    var domCache = privateProps.domCache.get(this);
    domCache.confirmButton.disabled = true;
  }

  function enableInput() {
    var input = this.getInput();

    if (!input) {
      return false;
    }

    if (input.type === 'radio') {
      var radiosContainer = input.parentNode.parentNode;
      var radios = radiosContainer.querySelectorAll('input');

      for (var i = 0; i < radios.length; i++) {
        radios[i].disabled = false;
      }
    } else {
      input.disabled = false;
    }
  }

  function disableInput() {
    var input = this.getInput();

    if (!input) {
      return false;
    }

    if (input && input.type === 'radio') {
      var radiosContainer = input.parentNode.parentNode;
      var radios = radiosContainer.querySelectorAll('input');

      for (var i = 0; i < radios.length; i++) {
        radios[i].disabled = true;
      }
    } else {
      input.disabled = true;
    }
  }

  function showValidationMessage(error$$1) {
    var domCache = privateProps.domCache.get(this);
    domCache.validationMessage.innerHTML = error$$1;
    var popupComputedStyle = window.getComputedStyle(domCache.popup);
    domCache.validationMessage.style.marginLeft = "-".concat(popupComputedStyle.getPropertyValue('padding-left'));
    domCache.validationMessage.style.marginRight = "-".concat(popupComputedStyle.getPropertyValue('padding-right'));
    show(domCache.validationMessage);
    var input = this.getInput();

    if (input) {
      input.setAttribute('aria-invalid', true);
      input.setAttribute('aria-describedBy', swalClasses['validation-message']);
      focusInput(input);
      addClass(input, swalClasses.inputerror);
    }
  } // Hide block with validation message


  function resetValidationMessage() {
    var domCache = privateProps.domCache.get(this);

    if (domCache.validationMessage) {
      hide(domCache.validationMessage);
    }

    var input = this.getInput();

    if (input) {
      input.removeAttribute('aria-invalid');
      input.removeAttribute('aria-describedBy');
      removeClass(input, swalClasses.inputerror);
    }
  } // @deprecated

  /* istanbul ignore next */


  function resetValidationError() {
    warnOnce("Swal.resetValidationError() is deprecated and will be removed in the next major release, use Swal.resetValidationMessage() instead");
    resetValidationMessage.bind(this)();
  } // @deprecated

  /* istanbul ignore next */


  function showValidationError(error$$1) {
    warnOnce("Swal.showValidationError() is deprecated and will be removed in the next major release, use Swal.showValidationMessage() instead");
    showValidationMessage.bind(this)(error$$1);
  }

  function getProgressSteps$1() {
    var innerParams = privateProps.innerParams.get(this);
    return innerParams.progressSteps;
  }

  function setProgressSteps(progressSteps) {
    var innerParams = privateProps.innerParams.get(this);

    var updatedParams = _extends({}, innerParams, {
      progressSteps: progressSteps
    });

    privateProps.innerParams.set(this, updatedParams);
    renderProgressSteps(updatedParams);
  }

  function showProgressSteps() {
    var domCache = privateProps.domCache.get(this);
    show(domCache.progressSteps);
  }

  function hideProgressSteps() {
    var domCache = privateProps.domCache.get(this);
    hide(domCache.progressSteps);
  }

  var Timer = function Timer(callback, delay) {
    _classCallCheck(this, Timer);

    var id,
        started,
        remaining = delay;
    this.running = false;

    this.start = function () {
      if (!this.running) {
        this.running = true;
        started = new Date();
        id = setTimeout(callback, remaining);
      }

      return remaining;
    };

    this.stop = function () {
      if (this.running) {
        this.running = false;
        clearTimeout(id);
        remaining -= new Date() - started;
      }

      return remaining;
    };

    this.increase = function (n) {
      var running = this.running;

      if (running) {
        this.stop();
      }

      remaining += n;

      if (running) {
        this.start();
      }

      return remaining;
    };

    this.getTimerLeft = function () {
      if (this.running) {
        this.stop();
        this.start();
      }

      return remaining;
    };

    this.isRunning = function () {
      return this.running;
    };

    this.start();
  };

  var defaultInputValidators = {
    email: function email(string, extraParams) {
      return /^[a-zA-Z0-9.+_-]+@[a-zA-Z0-9.-]+\.[a-zA-Z0-9-]{2,24}$/.test(string) ? Promise.resolve() : Promise.reject(extraParams && extraParams.validationMessage ? extraParams.validationMessage : 'Invalid email address');
    },
    url: function url(string, extraParams) {
      // taken from https://stackoverflow.com/a/3809435 with a small change from #1306
      return /^https?:\/\/(www\.)?[-a-zA-Z0-9@:%._+~#=]{2,256}\.[a-z]{2,63}\b([-a-zA-Z0-9@:%_+.~#?&//=]*)$/.test(string) ? Promise.resolve() : Promise.reject(extraParams && extraParams.validationMessage ? extraParams.validationMessage : 'Invalid URL');
    }
  };
  /**
   * Set type, text and actions on popup
   *
   * @param params
   * @returns {boolean}
   */

  function setParameters(params) {
    // Use default `inputValidator` for supported input types if not provided
    if (!params.inputValidator) {
      Object.keys(defaultInputValidators).forEach(function (key) {
        if (params.input === key) {
          params.inputValidator = params.expectRejections ? defaultInputValidators[key] : Swal.adaptInputValidator(defaultInputValidators[key]);
        }
      });
    } // params.extraParams is @deprecated


    if (params.validationMessage) {
      if (_typeof(params.extraParams) !== 'object') {
        params.extraParams = {};
      }

      params.extraParams.validationMessage = params.validationMessage;
    } // Determine if the custom target element is valid


    if (!params.target || typeof params.target === 'string' && !document.querySelector(params.target) || typeof params.target !== 'string' && !params.target.appendChild) {
      warn('Target parameter is not valid, defaulting to "body"');
      params.target = 'body';
    } // Animation


    if (typeof params.animation === 'function') {
      params.animation = params.animation.call();
    }

    var popup;
    var oldPopup = getPopup();
    var targetElement = typeof params.target === 'string' ? document.querySelector(params.target) : params.target; // If the model target has changed, refresh the popup

    if (oldPopup && targetElement && oldPopup.parentNode !== targetElement.parentNode) {
      popup = init(params);
    } else {
      popup = oldPopup || init(params);
    } // Set popup width


    if (params.width) {
      popup.style.width = typeof params.width === 'number' ? params.width + 'px' : params.width;
    } // Set popup padding


    if (params.padding) {
      popup.style.padding = typeof params.padding === 'number' ? params.padding + 'px' : params.padding;
    } // Set popup background


    if (params.background) {
      popup.style.background = params.background;
    }

    var popupBackgroundColor = window.getComputedStyle(popup).getPropertyValue('background-color');
    var successIconParts = popup.querySelectorAll('[class^=swal2-success-circular-line], .swal2-success-fix');

    for (var i = 0; i < successIconParts.length; i++) {
      successIconParts[i].style.backgroundColor = popupBackgroundColor;
    }

    var container = getContainer();
    var closeButton = getCloseButton();
    var footer = getFooter(); // Title

    renderTitle(params); // Content

    renderContent(params); // Backdrop

    if (typeof params.backdrop === 'string') {
      getContainer().style.background = params.backdrop;
    } else if (!params.backdrop) {
      addClass([document.documentElement, document.body], swalClasses['no-backdrop']);
    }

    if (!params.backdrop && params.allowOutsideClick) {
      warn('"allowOutsideClick" parameter requires `backdrop` parameter to be set to `true`');
    } // Position


    if (params.position in swalClasses) {
      addClass(container, swalClasses[params.position]);
    } else {
      warn('The "position" parameter is not valid, defaulting to "center"');
      addClass(container, swalClasses.center);
    } // Grow


    if (params.grow && typeof params.grow === 'string') {
      var growClass = 'grow-' + params.grow;

      if (growClass in swalClasses) {
        addClass(container, swalClasses[growClass]);
      }
    } // Close button


    if (params.showCloseButton) {
      closeButton.setAttribute('aria-label', params.closeButtonAriaLabel);
      show(closeButton);
    } else {
      hide(closeButton);
    } // Default Class


    popup.className = swalClasses.popup;

    if (params.toast) {
      addClass([document.documentElement, document.body], swalClasses['toast-shown']);
      addClass(popup, swalClasses.toast);
    } else {
      addClass(popup, swalClasses.modal);
    } // Custom Class


    if (params.customClass) {
      addClass(popup, params.customClass);
    }

    if (params.customContainerClass) {
      addClass(container, params.customContainerClass);
    } // Progress steps


    renderProgressSteps(params); // Icon

    renderIcon(params); // Image

    renderImage(params); // Actions (buttons)

    renderActions(params); // Footer

    parseHtmlToContainer(params.footer, footer); // CSS animation

    if (params.animation === true) {
      removeClass(popup, swalClasses.noanimation);
    } else {
      addClass(popup, swalClasses.noanimation);
    } // showLoaderOnConfirm && preConfirm


    if (params.showLoaderOnConfirm && !params.preConfirm) {
      warn('showLoaderOnConfirm is set to true, but preConfirm is not defined.\n' + 'showLoaderOnConfirm should be used together with preConfirm, see usage example:\n' + 'https://sweetalert2.github.io/#ajax-request');
    }
  }
  /**
   * Open popup, add necessary classes and styles, fix scrollbar
   *
   * @param {Array} params
   */


  var openPopup = function openPopup(params) {
    var container = getContainer();
    var popup = getPopup();

    if (params.onBeforeOpen !== null && typeof params.onBeforeOpen === 'function') {
      params.onBeforeOpen(popup);
    }

    if (params.animation) {
      addClass(popup, swalClasses.show);
      addClass(container, swalClasses.fade);
      removeClass(popup, swalClasses.hide);
    } else {
      removeClass(popup, swalClasses.fade);
    }

    show(popup); // scrolling is 'hidden' until animation is done, after that 'auto'

    container.style.overflowY = 'hidden';

    if (animationEndEvent && !hasClass(popup, swalClasses.noanimation)) {
      popup.addEventListener(animationEndEvent, function swalCloseEventFinished() {
        popup.removeEventListener(animationEndEvent, swalCloseEventFinished);
        container.style.overflowY = 'auto';
      });
    } else {
      container.style.overflowY = 'auto';
    }

    addClass([document.documentElement, document.body, container], swalClasses.shown);

    if (params.heightAuto && params.backdrop && !params.toast) {
      addClass([document.documentElement, document.body], swalClasses['height-auto']);
    }

    if (isModal()) {
      fixScrollbar();
      iOSfix();
      IEfix();
      setAriaHidden(); // sweetalert2/issues/1247

      setTimeout(function () {
        container.scrollTop = 0;
      });
    }

    if (!isToast() && !globalState.previousActiveElement) {
      globalState.previousActiveElement = document.activeElement;
    }

    if (params.onOpen !== null && typeof params.onOpen === 'function') {
      setTimeout(function () {
        params.onOpen(popup);
      });
    }
  };

  function _main(userParams) {
    var _this = this;

    showWarningsForParams(userParams);

    var innerParams = _extends({}, defaultParams, userParams);

    setParameters(innerParams);
    Object.freeze(innerParams);
    privateProps.innerParams.set(this, innerParams); // clear the previous timer

    if (globalState.timeout) {
      globalState.timeout.stop();
      delete globalState.timeout;
    } // clear the restore focus timeout


    clearTimeout(globalState.restoreFocusTimeout);
    var domCache = {
      popup: getPopup(),
      container: getContainer(),
      content: getContent(),
      actions: getActions(),
      confirmButton: getConfirmButton(),
      cancelButton: getCancelButton(),
      closeButton: getCloseButton(),
      validationMessage: getValidationMessage(),
      progressSteps: getProgressSteps()
    };
    privateProps.domCache.set(this, domCache);
    var constructor = this.constructor;
    return new Promise(function (resolve, reject) {
      // functions to handle all resolving/rejecting/settling
      var succeedWith = function succeedWith(value) {
        constructor.closePopup(innerParams.onClose, innerParams.onAfterClose); // TODO: make closePopup an *instance* method

        if (innerParams.useRejections) {
          resolve(value);
        } else {
          resolve({
            value: value
          });
        }
      };

      var dismissWith = function dismissWith(dismiss) {
        constructor.closePopup(innerParams.onClose, innerParams.onAfterClose);

        if (innerParams.useRejections) {
          reject(dismiss);
        } else {
          resolve({
            dismiss: dismiss
          });
        }
      };

      var errorWith = function errorWith(error$$1) {
        constructor.closePopup(innerParams.onClose, innerParams.onAfterClose);
        reject(error$$1);
      }; // Close on timer


      if (innerParams.timer) {
        globalState.timeout = new Timer(function () {
          dismissWith('timer');
          delete globalState.timeout;
        }, innerParams.timer);
      } // Get the value of the popup input


      var getInputValue = function getInputValue() {
        var input = _this.getInput();

        if (!input) {
          return null;
        }

        switch (innerParams.input) {
          case 'checkbox':
            return input.checked ? 1 : 0;

          case 'radio':
            return input.checked ? input.value : null;

          case 'file':
            return input.files.length ? input.files[0] : null;

          default:
            return innerParams.inputAutoTrim ? input.value.trim() : input.value;
        }
      }; // input autofocus


      if (innerParams.input) {
        setTimeout(function () {
          var input = _this.getInput();

          if (input) {
            focusInput(input);
          }
        }, 0);
      }

      var confirm = function confirm(value) {
        if (innerParams.showLoaderOnConfirm) {
          constructor.showLoading(); // TODO: make showLoading an *instance* method
        }

        if (innerParams.preConfirm) {
          _this.resetValidationMessage();

          var preConfirmPromise = Promise.resolve().then(function () {
            return innerParams.preConfirm(value, innerParams.extraParams);
          });

          if (innerParams.expectRejections) {
            preConfirmPromise.then(function (preConfirmValue) {
              return succeedWith(preConfirmValue || value);
            }, function (validationMessage) {
              _this.hideLoading();

              if (validationMessage) {
                _this.showValidationMessage(validationMessage);
              }
            });
          } else {
            preConfirmPromise.then(function (preConfirmValue) {
              if (isVisible(domCache.validationMessage) || preConfirmValue === false) {
                _this.hideLoading();
              } else {
                succeedWith(preConfirmValue || value);
              }
            }, function (error$$1) {
              return errorWith(error$$1);
            });
          }
        } else {
          succeedWith(value);
        }
      }; // Mouse interactions


      var onButtonEvent = function onButtonEvent(e) {
        var target = e.target;
        var confirmButton = domCache.confirmButton,
            cancelButton = domCache.cancelButton;
        var targetedConfirm = confirmButton && (confirmButton === target || confirmButton.contains(target));
        var targetedCancel = cancelButton && (cancelButton === target || cancelButton.contains(target));

        switch (e.type) {
          case 'click':
            // Clicked 'confirm'
            if (targetedConfirm && constructor.isVisible()) {
              _this.disableButtons();

              if (innerParams.input) {
                var inputValue = getInputValue();

                if (innerParams.inputValidator) {
                  _this.disableInput();

                  var validationPromise = Promise.resolve().then(function () {
                    return innerParams.inputValidator(inputValue, innerParams.extraParams);
                  });

                  if (innerParams.expectRejections) {
                    validationPromise.then(function () {
                      _this.enableButtons();

                      _this.enableInput();

                      confirm(inputValue);
                    }, function (validationMessage) {
                      _this.enableButtons();

                      _this.enableInput();

                      if (validationMessage) {
                        _this.showValidationMessage(validationMessage);
                      }
                    });
                  } else {
                    validationPromise.then(function (validationMessage) {
                      _this.enableButtons();

                      _this.enableInput();

                      if (validationMessage) {
                        _this.showValidationMessage(validationMessage);
                      } else {
                        confirm(inputValue);
                      }
                    }, function (error$$1) {
                      return errorWith(error$$1);
                    });
                  }
                } else if (!_this.getInput().checkValidity()) {
                  _this.enableButtons();

                  _this.showValidationMessage(innerParams.validationMessage);
                } else {
                  confirm(inputValue);
                }
              } else {
                confirm(true);
              } // Clicked 'cancel'

            } else if (targetedCancel && constructor.isVisible()) {
              _this.disableButtons();

              dismissWith(constructor.DismissReason.cancel);
            }

            break;

          default:
        }
      };

      var buttons = domCache.popup.querySelectorAll('button');

      for (var i = 0; i < buttons.length; i++) {
        buttons[i].onclick = onButtonEvent;
        buttons[i].onmouseover = onButtonEvent;
        buttons[i].onmouseout = onButtonEvent;
        buttons[i].onmousedown = onButtonEvent;
      } // Closing popup by close button


      domCache.closeButton.onclick = function () {
        dismissWith(constructor.DismissReason.close);
      };

      if (innerParams.toast) {
        // Closing popup by internal click
        domCache.popup.onclick = function () {
          if (innerParams.showConfirmButton || innerParams.showCancelButton || innerParams.showCloseButton || innerParams.input) {
            return;
          }

          dismissWith(constructor.DismissReason.close);
        };
      } else {
        var ignoreOutsideClick = false; // Ignore click events that had mousedown on the popup but mouseup on the container
        // This can happen when the user drags a slider

        domCache.popup.onmousedown = function () {
          domCache.container.onmouseup = function (e) {
            domCache.container.onmouseup = undefined; // We only check if the mouseup target is the container because usually it doesn't
            // have any other direct children aside of the popup

            if (e.target === domCache.container) {
              ignoreOutsideClick = true;
            }
          };
        }; // Ignore click events that had mousedown on the container but mouseup on the popup


        domCache.container.onmousedown = function () {
          domCache.popup.onmouseup = function (e) {
            domCache.popup.onmouseup = undefined; // We also need to check if the mouseup target is a child of the popup

            if (e.target === domCache.popup || domCache.popup.contains(e.target)) {
              ignoreOutsideClick = true;
            }
          };
        };

        domCache.container.onclick = function (e) {
          if (ignoreOutsideClick) {
            ignoreOutsideClick = false;
            return;
          }

          if (e.target !== domCache.container) {
            return;
          }

          if (callIfFunction(innerParams.allowOutsideClick)) {
            dismissWith(constructor.DismissReason.backdrop);
          }
        };
      } // Reverse buttons (Confirm on the right side)


      if (innerParams.reverseButtons) {
        domCache.confirmButton.parentNode.insertBefore(domCache.cancelButton, domCache.confirmButton);
      } else {
        domCache.confirmButton.parentNode.insertBefore(domCache.confirmButton, domCache.cancelButton);
      } // Focus handling


      var setFocus = function setFocus(index, increment) {
        var focusableElements = getFocusableElements(innerParams.focusCancel); // search for visible elements and select the next possible match

        for (var _i = 0; _i < focusableElements.length; _i++) {
          index = index + increment; // rollover to first item

          if (index === focusableElements.length) {
            index = 0; // go to last item
          } else if (index === -1) {
            index = focusableElements.length - 1;
          }

          return focusableElements[index].focus();
        } // no visible focusable elements, focus the popup


        domCache.popup.focus();
      };

      var keydownHandler = function keydownHandler(e, innerParams) {
        if (innerParams.stopKeydownPropagation) {
          e.stopPropagation();
        }

        var arrowKeys = ['ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown', 'Left', 'Right', 'Up', 'Down' // IE11
        ];

        if (e.key === 'Enter' && !e.isComposing) {
          if (e.target && _this.getInput() && e.target.outerHTML === _this.getInput().outerHTML) {
            if (['textarea', 'file'].indexOf(innerParams.input) !== -1) {
              return; // do not submit
            }

            constructor.clickConfirm();
            e.preventDefault();
          } // TAB

        } else if (e.key === 'Tab') {
          var targetElement = e.target;
          var focusableElements = getFocusableElements(innerParams.focusCancel);
          var btnIndex = -1;

          for (var _i2 = 0; _i2 < focusableElements.length; _i2++) {
            if (targetElement === focusableElements[_i2]) {
              btnIndex = _i2;
              break;
            }
          }

          if (!e.shiftKey) {
            // Cycle to the next button
            setFocus(btnIndex, 1);
          } else {
            // Cycle to the prev button
            setFocus(btnIndex, -1);
          }

          e.stopPropagation();
          e.preventDefault(); // ARROWS - switch focus between buttons
        } else if (arrowKeys.indexOf(e.key) !== -1) {
          // focus Cancel button if Confirm button is currently focused
          if (document.activeElement === domCache.confirmButton && isVisible(domCache.cancelButton)) {
            domCache.cancelButton.focus(); // and vice versa
          } else if (document.activeElement === domCache.cancelButton && isVisible(domCache.confirmButton)) {
            domCache.confirmButton.focus();
          } // ESC

        } else if ((e.key === 'Escape' || e.key === 'Esc') && callIfFunction(innerParams.allowEscapeKey) === true) {
          e.preventDefault();
          dismissWith(constructor.DismissReason.esc);
        }
      };

      if (globalState.keydownHandlerAdded) {
        globalState.keydownTarget.removeEventListener('keydown', globalState.keydownHandler, {
          capture: globalState.keydownListenerCapture
        });
        globalState.keydownHandlerAdded = false;
      }

      if (!innerParams.toast) {
        globalState.keydownHandler = function (e) {
          return keydownHandler(e, innerParams);
        };

        globalState.keydownTarget = innerParams.keydownListenerCapture ? window : domCache.popup;
        globalState.keydownListenerCapture = innerParams.keydownListenerCapture;
        globalState.keydownTarget.addEventListener('keydown', globalState.keydownHandler, {
          capture: globalState.keydownListenerCapture
        });
        globalState.keydownHandlerAdded = true;
      }

      _this.enableButtons();

      _this.hideLoading();

      _this.resetValidationMessage();

      if (innerParams.toast && (innerParams.input || innerParams.footer || innerParams.showCloseButton)) {
        addClass(document.body, swalClasses['toast-column']);
      } else {
        removeClass(document.body, swalClasses['toast-column']);
      } // inputs


      var inputTypes = ['input', 'file', 'range', 'select', 'radio', 'checkbox', 'textarea'];

      var setInputPlaceholder = function setInputPlaceholder(input) {
        if (!input.placeholder || innerParams.inputPlaceholder) {
          input.placeholder = innerParams.inputPlaceholder;
        }
      };

      var input;

      for (var _i3 = 0; _i3 < inputTypes.length; _i3++) {
        var inputClass = swalClasses[inputTypes[_i3]];
        var inputContainer = getChildByClass(domCache.content, inputClass);
        input = _this.getInput(inputTypes[_i3]); // set attributes

        if (input) {
          for (var j in input.attributes) {
            if (input.attributes.hasOwnProperty(j)) {
              var attrName = input.attributes[j].name;

              if (attrName !== 'type' && attrName !== 'value') {
                input.removeAttribute(attrName);
              }
            }
          }

          for (var attr in innerParams.inputAttributes) {
            // Do not set a placeholder for <input type="range">
            // it'll crash Edge, #1298
            if (inputTypes[_i3] === 'range' && attr === 'placeholder') {
              continue;
            }

            input.setAttribute(attr, innerParams.inputAttributes[attr]);
          }
        } // set class


        inputContainer.className = inputClass;

        if (innerParams.inputClass) {
          addClass(inputContainer, innerParams.inputClass);
        }

        hide(inputContainer);
      }

      var populateInputOptions;

      switch (innerParams.input) {
        case 'text':
        case 'email':
        case 'password':
        case 'number':
        case 'tel':
        case 'url':
          {
            input = getChildByClass(domCache.content, swalClasses.input);

            if (typeof innerParams.inputValue === 'string' || typeof innerParams.inputValue === 'number') {
              input.value = innerParams.inputValue;
            } else if (!isPromise(innerParams.inputValue)) {
              warn("Unexpected type of inputValue! Expected \"string\", \"number\" or \"Promise\", got \"".concat(_typeof(innerParams.inputValue), "\""));
            }

            setInputPlaceholder(input);
            input.type = innerParams.input;
            show(input);
            break;
          }

        case 'file':
          {
            input = getChildByClass(domCache.content, swalClasses.file);
            setInputPlaceholder(input);
            input.type = innerParams.input;
            show(input);
            break;
          }

        case 'range':
          {
            var range = getChildByClass(domCache.content, swalClasses.range);
            var rangeInput = range.querySelector('input');
            var rangeOutput = range.querySelector('output');
            rangeInput.value = innerParams.inputValue;
            rangeInput.type = innerParams.input;
            rangeOutput.value = innerParams.inputValue;
            show(range);
            break;
          }

        case 'select':
          {
            var select = getChildByClass(domCache.content, swalClasses.select);
            select.innerHTML = '';

            if (innerParams.inputPlaceholder) {
              var placeholder = document.createElement('option');
              placeholder.innerHTML = innerParams.inputPlaceholder;
              placeholder.value = '';
              placeholder.disabled = true;
              placeholder.selected = true;
              select.appendChild(placeholder);
            }

            populateInputOptions = function populateInputOptions(inputOptions) {
              inputOptions.forEach(function (inputOption) {
                var optionValue = inputOption[0];
                var optionLabel = inputOption[1];
                var option = document.createElement('option');
                option.value = optionValue;
                option.innerHTML = optionLabel;

                if (innerParams.inputValue.toString() === optionValue.toString()) {
                  option.selected = true;
                }

                select.appendChild(option);
              });
              show(select);
              select.focus();
            };

            break;
          }

        case 'radio':
          {
            var radio = getChildByClass(domCache.content, swalClasses.radio);
            radio.innerHTML = '';

            populateInputOptions = function populateInputOptions(inputOptions) {
              inputOptions.forEach(function (inputOption) {
                var radioValue = inputOption[0];
                var radioLabel = inputOption[1];
                var radioInput = document.createElement('input');
                var radioLabelElement = document.createElement('label');
                radioInput.type = 'radio';
                radioInput.name = swalClasses.radio;
                radioInput.value = radioValue;

                if (innerParams.inputValue.toString() === radioValue.toString()) {
                  radioInput.checked = true;
                }

                var label = document.createElement('span');
                label.innerHTML = radioLabel;
                label.className = swalClasses.label;
                radioLabelElement.appendChild(radioInput);
                radioLabelElement.appendChild(label);
                radio.appendChild(radioLabelElement);
              });
              show(radio);
              var radios = radio.querySelectorAll('input');

              if (radios.length) {
                radios[0].focus();
              }
            };

            break;
          }

        case 'checkbox':
          {
            var checkbox = getChildByClass(domCache.content, swalClasses.checkbox);

            var checkboxInput = _this.getInput('checkbox');

            checkboxInput.type = 'checkbox';
            checkboxInput.value = 1;
            checkboxInput.id = swalClasses.checkbox;
            checkboxInput.checked = Boolean(innerParams.inputValue);
            var label = checkbox.querySelector('span');
            label.innerHTML = innerParams.inputPlaceholder;
            show(checkbox);
            break;
          }

        case 'textarea':
          {
            var textarea = getChildByClass(domCache.content, swalClasses.textarea);
            textarea.value = innerParams.inputValue;
            setInputPlaceholder(textarea);
            show(textarea);
            break;
          }

        case null:
          {
            break;
          }

        default:
          error("Unexpected type of input! Expected \"text\", \"email\", \"password\", \"number\", \"tel\", \"select\", \"radio\", \"checkbox\", \"textarea\", \"file\" or \"url\", got \"".concat(innerParams.input, "\""));
          break;
      }

      if (innerParams.input === 'select' || innerParams.input === 'radio') {
        var processInputOptions = function processInputOptions(inputOptions) {
          return populateInputOptions(formatInputOptions(inputOptions));
        };

        if (isPromise(innerParams.inputOptions)) {
          constructor.showLoading();
          innerParams.inputOptions.then(function (inputOptions) {
            _this.hideLoading();

            processInputOptions(inputOptions);
          });
        } else if (_typeof(innerParams.inputOptions) === 'object') {
          processInputOptions(innerParams.inputOptions);
        } else {
          error("Unexpected type of inputOptions! Expected object, Map or Promise, got ".concat(_typeof(innerParams.inputOptions)));
        }
      } else if (['text', 'email', 'number', 'tel', 'textarea'].indexOf(innerParams.input) !== -1 && isPromise(innerParams.inputValue)) {
        constructor.showLoading();
        hide(input);
        innerParams.inputValue.then(function (inputValue) {
          input.value = innerParams.input === 'number' ? parseFloat(inputValue) || 0 : inputValue + '';
          show(input);
          input.focus();

          _this.hideLoading();
        })["catch"](function (err) {
          error('Error in inputValue promise: ' + err);
          input.value = '';
          show(input);
          input.focus();

          _this.hideLoading();
        });
      }

      openPopup(innerParams);

      if (!innerParams.toast) {
        if (!callIfFunction(innerParams.allowEnterKey)) {
          if (document.activeElement && typeof document.activeElement.blur === 'function') {
            document.activeElement.blur();
          }
        } else if (innerParams.focusCancel && isVisible(domCache.cancelButton)) {
          domCache.cancelButton.focus();
        } else if (innerParams.focusConfirm && isVisible(domCache.confirmButton)) {
          domCache.confirmButton.focus();
        } else {
          setFocus(-1, 1);
        }
      } // fix scroll


      domCache.container.scrollTop = 0;
    });
  }

  var instanceMethods = Object.freeze({
    hideLoading: hideLoading,
    disableLoading: hideLoading,
    getInput: getInput,
    enableButtons: enableButtons,
    disableButtons: disableButtons,
    enableConfirmButton: enableConfirmButton,
    disableConfirmButton: disableConfirmButton,
    enableInput: enableInput,
    disableInput: disableInput,
    showValidationMessage: showValidationMessage,
    resetValidationMessage: resetValidationMessage,
    resetValidationError: resetValidationError,
    showValidationError: showValidationError,
    getProgressSteps: getProgressSteps$1,
    setProgressSteps: setProgressSteps,
    showProgressSteps: showProgressSteps,
    hideProgressSteps: hideProgressSteps,
    _main: _main
  });
  var currentInstance; // SweetAlert constructor

  function SweetAlert() {
    // Prevent run in Node env

    /* istanbul ignore if */
    if (typeof window === 'undefined') {
      return;
    } // Check for the existence of Promise

    /* istanbul ignore if */


    if (typeof Promise === 'undefined') {
      error('This package requires a Promise library, please include a shim to enable it in this browser (See: https://github.com/sweetalert2/sweetalert2/wiki/Migration-from-SweetAlert-to-SweetAlert2#1-ie-support)');
    }

    currentInstance = this;

    for (var _len = arguments.length, args = new Array(_len), _key = 0; _key < _len; _key++) {
      args[_key] = arguments[_key];
    }

    var outerParams = Object.freeze(this.constructor.argsToParams(args));
    Object.defineProperties(this, {
      params: {
        value: outerParams,
        writable: false,
        enumerable: true
      }
    });

    var promise = this._main(this.params);

    privateProps.promise.set(this, promise);
  } // `catch` cannot be the name of a module export, so we define our thenable methods here instead


  SweetAlert.prototype.then = function (onFulfilled, onRejected) {
    var promise = privateProps.promise.get(this);
    return promise.then(onFulfilled, onRejected);
  };

  SweetAlert.prototype["catch"] = function (onRejected) {
    var promise = privateProps.promise.get(this);
    return promise["catch"](onRejected);
  };

  SweetAlert.prototype["finally"] = function (onFinally) {
    var promise = privateProps.promise.get(this);
    return promise["finally"](onFinally);
  }; // Assign instance methods from src/instanceMethods/*.js to prototype


  _extends(SweetAlert.prototype, instanceMethods); // Assign static methods from src/staticMethods/*.js to constructor


  _extends(SweetAlert, staticMethods); // Proxy to instance methods to constructor, for now, for backwards compatibility


  Object.keys(instanceMethods).forEach(function (key) {
    SweetAlert[key] = function () {
      if (currentInstance) {
        var _currentInstance;

        return (_currentInstance = currentInstance)[key].apply(_currentInstance, arguments);
      }
    };
  });
  SweetAlert.DismissReason = DismissReason;
  /* istanbul ignore next */

  SweetAlert.noop = function () {};

  var Swal = withNoNewKeyword(withGlobalDefaults(SweetAlert));
  Swal["default"] = Swal;
  return Swal;
});

if (typeof window !== 'undefined' && window.Sweetalert2) {
  window.Sweetalert2.version = '7.33.1';
  window.swal = window.sweetAlert = window.Swal = window.SweetAlert = window.Sweetalert2;
}

"undefined" != typeof document && function (e, t) {
  var n = e.createElement("style");
  if (e.getElementsByTagName("head")[0].appendChild(n), n.styleSheet) n.styleSheet.disabled || (n.styleSheet.cssText = t);else try {
    n.innerHTML = t;
  } catch (e) {
    n.innerText = t;
  }
}(document, "@-webkit-keyframes swal2-show{0%{-webkit-transform:scale(.7);transform:scale(.7)}45%{-webkit-transform:scale(1.05);transform:scale(1.05)}80%{-webkit-transform:scale(.95);transform:scale(.95)}100%{-webkit-transform:scale(1);transform:scale(1)}}@keyframes swal2-show{0%{-webkit-transform:scale(.7);transform:scale(.7)}45%{-webkit-transform:scale(1.05);transform:scale(1.05)}80%{-webkit-transform:scale(.95);transform:scale(.95)}100%{-webkit-transform:scale(1);transform:scale(1)}}@-webkit-keyframes swal2-hide{0%{-webkit-transform:scale(1);transform:scale(1);opacity:1}100%{-webkit-transform:scale(.5);transform:scale(.5);opacity:0}}@keyframes swal2-hide{0%{-webkit-transform:scale(1);transform:scale(1);opacity:1}100%{-webkit-transform:scale(.5);transform:scale(.5);opacity:0}}@-webkit-keyframes swal2-animate-success-line-tip{0%{top:1.1875em;left:.0625em;width:0}54%{top:1.0625em;left:.125em;width:0}70%{top:2.1875em;left:-.375em;width:3.125em}84%{top:3em;left:1.3125em;width:1.0625em}100%{top:2.8125em;left:.875em;width:1.5625em}}@keyframes swal2-animate-success-line-tip{0%{top:1.1875em;left:.0625em;width:0}54%{top:1.0625em;left:.125em;width:0}70%{top:2.1875em;left:-.375em;width:3.125em}84%{top:3em;left:1.3125em;width:1.0625em}100%{top:2.8125em;left:.875em;width:1.5625em}}@-webkit-keyframes swal2-animate-success-line-long{0%{top:3.375em;right:2.875em;width:0}65%{top:3.375em;right:2.875em;width:0}84%{top:2.1875em;right:0;width:3.4375em}100%{top:2.375em;right:.5em;width:2.9375em}}@keyframes swal2-animate-success-line-long{0%{top:3.375em;right:2.875em;width:0}65%{top:3.375em;right:2.875em;width:0}84%{top:2.1875em;right:0;width:3.4375em}100%{top:2.375em;right:.5em;width:2.9375em}}@-webkit-keyframes swal2-rotate-success-circular-line{0%{-webkit-transform:rotate(-45deg);transform:rotate(-45deg)}5%{-webkit-transform:rotate(-45deg);transform:rotate(-45deg)}12%{-webkit-transform:rotate(-405deg);transform:rotate(-405deg)}100%{-webkit-transform:rotate(-405deg);transform:rotate(-405deg)}}@keyframes swal2-rotate-success-circular-line{0%{-webkit-transform:rotate(-45deg);transform:rotate(-45deg)}5%{-webkit-transform:rotate(-45deg);transform:rotate(-45deg)}12%{-webkit-transform:rotate(-405deg);transform:rotate(-405deg)}100%{-webkit-transform:rotate(-405deg);transform:rotate(-405deg)}}@-webkit-keyframes swal2-animate-error-x-mark{0%{margin-top:1.625em;-webkit-transform:scale(.4);transform:scale(.4);opacity:0}50%{margin-top:1.625em;-webkit-transform:scale(.4);transform:scale(.4);opacity:0}80%{margin-top:-.375em;-webkit-transform:scale(1.15);transform:scale(1.15)}100%{margin-top:0;-webkit-transform:scale(1);transform:scale(1);opacity:1}}@keyframes swal2-animate-error-x-mark{0%{margin-top:1.625em;-webkit-transform:scale(.4);transform:scale(.4);opacity:0}50%{margin-top:1.625em;-webkit-transform:scale(.4);transform:scale(.4);opacity:0}80%{margin-top:-.375em;-webkit-transform:scale(1.15);transform:scale(1.15)}100%{margin-top:0;-webkit-transform:scale(1);transform:scale(1);opacity:1}}@-webkit-keyframes swal2-animate-error-icon{0%{-webkit-transform:rotateX(100deg);transform:rotateX(100deg);opacity:0}100%{-webkit-transform:rotateX(0);transform:rotateX(0);opacity:1}}@keyframes swal2-animate-error-icon{0%{-webkit-transform:rotateX(100deg);transform:rotateX(100deg);opacity:0}100%{-webkit-transform:rotateX(0);transform:rotateX(0);opacity:1}}body.swal2-toast-shown .swal2-container{background-color:transparent}body.swal2-toast-shown .swal2-container.swal2-shown{background-color:transparent}body.swal2-toast-shown .swal2-container.swal2-top{top:0;right:auto;bottom:auto;left:50%;-webkit-transform:translateX(-50%);transform:translateX(-50%)}body.swal2-toast-shown .swal2-container.swal2-top-end,body.swal2-toast-shown .swal2-container.swal2-top-right{top:0;right:0;bottom:auto;left:auto}body.swal2-toast-shown .swal2-container.swal2-top-left,body.swal2-toast-shown .swal2-container.swal2-top-start{top:0;right:auto;bottom:auto;left:0}body.swal2-toast-shown .swal2-container.swal2-center-left,body.swal2-toast-shown .swal2-container.swal2-center-start{top:50%;right:auto;bottom:auto;left:0;-webkit-transform:translateY(-50%);transform:translateY(-50%)}body.swal2-toast-shown .swal2-container.swal2-center{top:50%;right:auto;bottom:auto;left:50%;-webkit-transform:translate(-50%,-50%);transform:translate(-50%,-50%)}body.swal2-toast-shown .swal2-container.swal2-center-end,body.swal2-toast-shown .swal2-container.swal2-center-right{top:50%;right:0;bottom:auto;left:auto;-webkit-transform:translateY(-50%);transform:translateY(-50%)}body.swal2-toast-shown .swal2-container.swal2-bottom-left,body.swal2-toast-shown .swal2-container.swal2-bottom-start{top:auto;right:auto;bottom:0;left:0}body.swal2-toast-shown .swal2-container.swal2-bottom{top:auto;right:auto;bottom:0;left:50%;-webkit-transform:translateX(-50%);transform:translateX(-50%)}body.swal2-toast-shown .swal2-container.swal2-bottom-end,body.swal2-toast-shown .swal2-container.swal2-bottom-right{top:auto;right:0;bottom:0;left:auto}body.swal2-toast-column .swal2-toast{flex-direction:column;align-items:stretch}body.swal2-toast-column .swal2-toast .swal2-actions{flex:1;align-self:stretch;height:2.2em;margin-top:.3125em}body.swal2-toast-column .swal2-toast .swal2-loading{justify-content:center}body.swal2-toast-column .swal2-toast .swal2-input{height:2em;margin:.3125em auto;font-size:1em}body.swal2-toast-column .swal2-toast .swal2-validation-message{font-size:1em}.swal2-popup.swal2-toast{flex-direction:row;align-items:center;width:auto;padding:.625em;box-shadow:0 0 .625em #d9d9d9;overflow-y:hidden}.swal2-popup.swal2-toast .swal2-header{flex-direction:row}.swal2-popup.swal2-toast .swal2-title{flex-grow:1;justify-content:flex-start;margin:0 .6em;font-size:1em}.swal2-popup.swal2-toast .swal2-footer{margin:.5em 0 0;padding:.5em 0 0;font-size:.8em}.swal2-popup.swal2-toast .swal2-close{position:initial;width:.8em;height:.8em;line-height:.8}.swal2-popup.swal2-toast .swal2-content{justify-content:flex-start;font-size:1em}.swal2-popup.swal2-toast .swal2-icon{width:2em;min-width:2em;height:2em;margin:0}.swal2-popup.swal2-toast .swal2-icon-text{font-size:2em;font-weight:700;line-height:1em}.swal2-popup.swal2-toast .swal2-icon.swal2-success .swal2-success-ring{width:2em;height:2em}.swal2-popup.swal2-toast .swal2-icon.swal2-error [class^=swal2-x-mark-line]{top:.875em;width:1.375em}.swal2-popup.swal2-toast .swal2-icon.swal2-error [class^=swal2-x-mark-line][class$=left]{left:.3125em}.swal2-popup.swal2-toast .swal2-icon.swal2-error [class^=swal2-x-mark-line][class$=right]{right:.3125em}.swal2-popup.swal2-toast .swal2-actions{height:auto;margin:0 .3125em}.swal2-popup.swal2-toast .swal2-styled{margin:0 .3125em;padding:.3125em .625em;font-size:1em}.swal2-popup.swal2-toast .swal2-styled:focus{box-shadow:0 0 0 .0625em #fff,0 0 0 .125em rgba(50,100,150,.4)}.swal2-popup.swal2-toast .swal2-success{border-color:#a5dc86}.swal2-popup.swal2-toast .swal2-success [class^=swal2-success-circular-line]{position:absolute;width:2em;height:2.8125em;-webkit-transform:rotate(45deg);transform:rotate(45deg);border-radius:50%}.swal2-popup.swal2-toast .swal2-success [class^=swal2-success-circular-line][class$=left]{top:-.25em;left:-.9375em;-webkit-transform:rotate(-45deg);transform:rotate(-45deg);-webkit-transform-origin:2em 2em;transform-origin:2em 2em;border-radius:4em 0 0 4em}.swal2-popup.swal2-toast .swal2-success [class^=swal2-success-circular-line][class$=right]{top:-.25em;left:.9375em;-webkit-transform-origin:0 2em;transform-origin:0 2em;border-radius:0 4em 4em 0}.swal2-popup.swal2-toast .swal2-success .swal2-success-ring{width:2em;height:2em}.swal2-popup.swal2-toast .swal2-success .swal2-success-fix{top:0;left:.4375em;width:.4375em;height:2.6875em}.swal2-popup.swal2-toast .swal2-success [class^=swal2-success-line]{height:.3125em}.swal2-popup.swal2-toast .swal2-success [class^=swal2-success-line][class$=tip]{top:1.125em;left:.1875em;width:.75em}.swal2-popup.swal2-toast .swal2-success [class^=swal2-success-line][class$=long]{top:.9375em;right:.1875em;width:1.375em}.swal2-popup.swal2-toast.swal2-show{-webkit-animation:showSweetToast .5s;animation:showSweetToast .5s}.swal2-popup.swal2-toast.swal2-hide{-webkit-animation:hideSweetToast .2s forwards;animation:hideSweetToast .2s forwards}.swal2-popup.swal2-toast .swal2-animate-success-icon .swal2-success-line-tip{-webkit-animation:animate-toast-success-tip .75s;animation:animate-toast-success-tip .75s}.swal2-popup.swal2-toast .swal2-animate-success-icon .swal2-success-line-long{-webkit-animation:animate-toast-success-long .75s;animation:animate-toast-success-long .75s}@-webkit-keyframes showSweetToast{0%{-webkit-transform:translateY(-.625em) rotateZ(2deg);transform:translateY(-.625em) rotateZ(2deg);opacity:0}33%{-webkit-transform:translateY(0) rotateZ(-2deg);transform:translateY(0) rotateZ(-2deg);opacity:.5}66%{-webkit-transform:translateY(.3125em) rotateZ(2deg);transform:translateY(.3125em) rotateZ(2deg);opacity:.7}100%{-webkit-transform:translateY(0) rotateZ(0);transform:translateY(0) rotateZ(0);opacity:1}}@keyframes showSweetToast{0%{-webkit-transform:translateY(-.625em) rotateZ(2deg);transform:translateY(-.625em) rotateZ(2deg);opacity:0}33%{-webkit-transform:translateY(0) rotateZ(-2deg);transform:translateY(0) rotateZ(-2deg);opacity:.5}66%{-webkit-transform:translateY(.3125em) rotateZ(2deg);transform:translateY(.3125em) rotateZ(2deg);opacity:.7}100%{-webkit-transform:translateY(0) rotateZ(0);transform:translateY(0) rotateZ(0);opacity:1}}@-webkit-keyframes hideSweetToast{0%{opacity:1}33%{opacity:.5}100%{-webkit-transform:rotateZ(1deg);transform:rotateZ(1deg);opacity:0}}@keyframes hideSweetToast{0%{opacity:1}33%{opacity:.5}100%{-webkit-transform:rotateZ(1deg);transform:rotateZ(1deg);opacity:0}}@-webkit-keyframes animate-toast-success-tip{0%{top:.5625em;left:.0625em;width:0}54%{top:.125em;left:.125em;width:0}70%{top:.625em;left:-.25em;width:1.625em}84%{top:1.0625em;left:.75em;width:.5em}100%{top:1.125em;left:.1875em;width:.75em}}@keyframes animate-toast-success-tip{0%{top:.5625em;left:.0625em;width:0}54%{top:.125em;left:.125em;width:0}70%{top:.625em;left:-.25em;width:1.625em}84%{top:1.0625em;left:.75em;width:.5em}100%{top:1.125em;left:.1875em;width:.75em}}@-webkit-keyframes animate-toast-success-long{0%{top:1.625em;right:1.375em;width:0}65%{top:1.25em;right:.9375em;width:0}84%{top:.9375em;right:0;width:1.125em}100%{top:.9375em;right:.1875em;width:1.375em}}@keyframes animate-toast-success-long{0%{top:1.625em;right:1.375em;width:0}65%{top:1.25em;right:.9375em;width:0}84%{top:.9375em;right:0;width:1.125em}100%{top:.9375em;right:.1875em;width:1.375em}}body.swal2-shown:not(.swal2-no-backdrop):not(.swal2-toast-shown){overflow:hidden}body.swal2-height-auto{height:auto!important}body.swal2-no-backdrop .swal2-shown{top:auto;right:auto;bottom:auto;left:auto;background-color:transparent}body.swal2-no-backdrop .swal2-shown>.swal2-modal{box-shadow:0 0 10px rgba(0,0,0,.4)}body.swal2-no-backdrop .swal2-shown.swal2-top{top:0;left:50%;-webkit-transform:translateX(-50%);transform:translateX(-50%)}body.swal2-no-backdrop .swal2-shown.swal2-top-left,body.swal2-no-backdrop .swal2-shown.swal2-top-start{top:0;left:0}body.swal2-no-backdrop .swal2-shown.swal2-top-end,body.swal2-no-backdrop .swal2-shown.swal2-top-right{top:0;right:0}body.swal2-no-backdrop .swal2-shown.swal2-center{top:50%;left:50%;-webkit-transform:translate(-50%,-50%);transform:translate(-50%,-50%)}body.swal2-no-backdrop .swal2-shown.swal2-center-left,body.swal2-no-backdrop .swal2-shown.swal2-center-start{top:50%;left:0;-webkit-transform:translateY(-50%);transform:translateY(-50%)}body.swal2-no-backdrop .swal2-shown.swal2-center-end,body.swal2-no-backdrop .swal2-shown.swal2-center-right{top:50%;right:0;-webkit-transform:translateY(-50%);transform:translateY(-50%)}body.swal2-no-backdrop .swal2-shown.swal2-bottom{bottom:0;left:50%;-webkit-transform:translateX(-50%);transform:translateX(-50%)}body.swal2-no-backdrop .swal2-shown.swal2-bottom-left,body.swal2-no-backdrop .swal2-shown.swal2-bottom-start{bottom:0;left:0}body.swal2-no-backdrop .swal2-shown.swal2-bottom-end,body.swal2-no-backdrop .swal2-shown.swal2-bottom-right{right:0;bottom:0}.swal2-container{display:flex;position:fixed;top:0;right:0;bottom:0;left:0;flex-direction:row;align-items:center;justify-content:center;padding:10px;background-color:transparent;z-index:1060;overflow-x:hidden;-webkit-overflow-scrolling:touch}.swal2-container.swal2-top{align-items:flex-start}.swal2-container.swal2-top-left,.swal2-container.swal2-top-start{align-items:flex-start;justify-content:flex-start}.swal2-container.swal2-top-end,.swal2-container.swal2-top-right{align-items:flex-start;justify-content:flex-end}.swal2-container.swal2-center{align-items:center}.swal2-container.swal2-center-left,.swal2-container.swal2-center-start{align-items:center;justify-content:flex-start}.swal2-container.swal2-center-end,.swal2-container.swal2-center-right{align-items:center;justify-content:flex-end}.swal2-container.swal2-bottom{align-items:flex-end}.swal2-container.swal2-bottom-left,.swal2-container.swal2-bottom-start{align-items:flex-end;justify-content:flex-start}.swal2-container.swal2-bottom-end,.swal2-container.swal2-bottom-right{align-items:flex-end;justify-content:flex-end}.swal2-container.swal2-grow-fullscreen>.swal2-modal{display:flex!important;flex:1;align-self:stretch;justify-content:center}.swal2-container.swal2-grow-row>.swal2-modal{display:flex!important;flex:1;align-content:center;justify-content:center}.swal2-container.swal2-grow-column{flex:1;flex-direction:column}.swal2-container.swal2-grow-column.swal2-bottom,.swal2-container.swal2-grow-column.swal2-center,.swal2-container.swal2-grow-column.swal2-top{align-items:center}.swal2-container.swal2-grow-column.swal2-bottom-left,.swal2-container.swal2-grow-column.swal2-bottom-start,.swal2-container.swal2-grow-column.swal2-center-left,.swal2-container.swal2-grow-column.swal2-center-start,.swal2-container.swal2-grow-column.swal2-top-left,.swal2-container.swal2-grow-column.swal2-top-start{align-items:flex-start}.swal2-container.swal2-grow-column.swal2-bottom-end,.swal2-container.swal2-grow-column.swal2-bottom-right,.swal2-container.swal2-grow-column.swal2-center-end,.swal2-container.swal2-grow-column.swal2-center-right,.swal2-container.swal2-grow-column.swal2-top-end,.swal2-container.swal2-grow-column.swal2-top-right{align-items:flex-end}.swal2-container.swal2-grow-column>.swal2-modal{display:flex!important;flex:1;align-content:center;justify-content:center}.swal2-container:not(.swal2-top):not(.swal2-top-start):not(.swal2-top-end):not(.swal2-top-left):not(.swal2-top-right):not(.swal2-center-start):not(.swal2-center-end):not(.swal2-center-left):not(.swal2-center-right):not(.swal2-bottom):not(.swal2-bottom-start):not(.swal2-bottom-end):not(.swal2-bottom-left):not(.swal2-bottom-right):not(.swal2-grow-fullscreen)>.swal2-modal{margin:auto}@media all and (-ms-high-contrast:none),(-ms-high-contrast:active){.swal2-container .swal2-modal{margin:0!important}}.swal2-container.swal2-fade{transition:background-color .1s}.swal2-container.swal2-shown{background-color:rgba(0,0,0,.4)}.swal2-popup{display:none;position:relative;flex-direction:column;justify-content:center;width:32em;max-width:100%;padding:1.25em;border-radius:.3125em;background:#fff;font-family:inherit;font-size:1rem;box-sizing:border-box}.swal2-popup:focus{outline:0}.swal2-popup.swal2-loading{overflow-y:hidden}.swal2-popup .swal2-header{display:flex;flex-direction:column;align-items:center}.swal2-popup .swal2-title{display:block;position:relative;max-width:100%;margin:0 0 .4em;padding:0;color:#595959;font-size:1.875em;font-weight:600;text-align:center;text-transform:none;word-wrap:break-word}.swal2-popup .swal2-actions{flex-wrap:wrap;align-items:center;justify-content:center;margin:1.25em auto 0;z-index:1}.swal2-popup .swal2-actions:not(.swal2-loading) .swal2-styled[disabled]{opacity:.4}.swal2-popup .swal2-actions:not(.swal2-loading) .swal2-styled:hover{background-image:linear-gradient(rgba(0,0,0,.1),rgba(0,0,0,.1))}.swal2-popup .swal2-actions:not(.swal2-loading) .swal2-styled:active{background-image:linear-gradient(rgba(0,0,0,.2),rgba(0,0,0,.2))}.swal2-popup .swal2-actions.swal2-loading .swal2-styled.swal2-confirm{width:2.5em;height:2.5em;margin:.46875em;padding:0;border:.25em solid transparent;border-radius:100%;border-color:transparent;background-color:transparent!important;color:transparent;cursor:default;box-sizing:border-box;-webkit-animation:swal2-rotate-loading 1.5s linear 0s infinite normal;animation:swal2-rotate-loading 1.5s linear 0s infinite normal;-webkit-user-select:none;-moz-user-select:none;-ms-user-select:none;user-select:none}.swal2-popup .swal2-actions.swal2-loading .swal2-styled.swal2-cancel{margin-right:30px;margin-left:30px}.swal2-popup .swal2-actions.swal2-loading :not(.swal2-styled).swal2-confirm::after{display:inline-block;width:15px;height:15px;margin-left:5px;border:3px solid #999;border-radius:50%;border-right-color:transparent;box-shadow:1px 1px 1px #fff;content:'';-webkit-animation:swal2-rotate-loading 1.5s linear 0s infinite normal;animation:swal2-rotate-loading 1.5s linear 0s infinite normal}.swal2-popup .swal2-styled{margin:.3125em;padding:.625em 2em;font-weight:500;box-shadow:none}.swal2-popup .swal2-styled:not([disabled]){cursor:pointer}.swal2-popup .swal2-styled.swal2-confirm{border:0;border-radius:.25em;background:initial;background-color:#3085d6;color:#fff;font-size:1.0625em}.swal2-popup .swal2-styled.swal2-cancel{border:0;border-radius:.25em;background:initial;background-color:#aaa;color:#fff;font-size:1.0625em}.swal2-popup .swal2-styled:focus{outline:0;box-shadow:0 0 0 2px #fff,0 0 0 4px rgba(50,100,150,.4)}.swal2-popup .swal2-styled::-moz-focus-inner{border:0}.swal2-popup .swal2-footer{justify-content:center;margin:1.25em 0 0;padding:1em 0 0;border-top:1px solid #eee;color:#545454;font-size:1em}.swal2-popup .swal2-image{max-width:100%;margin:1.25em auto}.swal2-popup .swal2-close{position:absolute;top:0;right:0;justify-content:center;width:1.2em;height:1.2em;padding:0;transition:color .1s ease-out;border:none;border-radius:0;outline:initial;background:0 0;color:#ccc;font-family:serif;font-size:2.5em;line-height:1.2;cursor:pointer;overflow:hidden}.swal2-popup .swal2-close:hover{-webkit-transform:none;transform:none;color:#f27474}.swal2-popup>.swal2-checkbox,.swal2-popup>.swal2-file,.swal2-popup>.swal2-input,.swal2-popup>.swal2-radio,.swal2-popup>.swal2-select,.swal2-popup>.swal2-textarea{display:none}.swal2-popup .swal2-content{justify-content:center;margin:0;padding:0;color:#545454;font-size:1.125em;font-weight:300;line-height:normal;z-index:1;word-wrap:break-word}.swal2-popup #swal2-content{text-align:center}.swal2-popup .swal2-checkbox,.swal2-popup .swal2-file,.swal2-popup .swal2-input,.swal2-popup .swal2-radio,.swal2-popup .swal2-select,.swal2-popup .swal2-textarea{margin:1em auto}.swal2-popup .swal2-file,.swal2-popup .swal2-input,.swal2-popup .swal2-textarea{width:100%;transition:border-color .3s,box-shadow .3s;border:1px solid #d9d9d9;border-radius:.1875em;font-size:1.125em;box-shadow:inset 0 1px 1px rgba(0,0,0,.06);box-sizing:border-box}.swal2-popup .swal2-file.swal2-inputerror,.swal2-popup .swal2-input.swal2-inputerror,.swal2-popup .swal2-textarea.swal2-inputerror{border-color:#f27474!important;box-shadow:0 0 2px #f27474!important}.swal2-popup .swal2-file:focus,.swal2-popup .swal2-input:focus,.swal2-popup .swal2-textarea:focus{border:1px solid #b4dbed;outline:0;box-shadow:0 0 3px #c4e6f5}.swal2-popup .swal2-file::-webkit-input-placeholder,.swal2-popup .swal2-input::-webkit-input-placeholder,.swal2-popup .swal2-textarea::-webkit-input-placeholder{color:#ccc}.swal2-popup .swal2-file:-ms-input-placeholder,.swal2-popup .swal2-input:-ms-input-placeholder,.swal2-popup .swal2-textarea:-ms-input-placeholder{color:#ccc}.swal2-popup .swal2-file::-ms-input-placeholder,.swal2-popup .swal2-input::-ms-input-placeholder,.swal2-popup .swal2-textarea::-ms-input-placeholder{color:#ccc}.swal2-popup .swal2-file::placeholder,.swal2-popup .swal2-input::placeholder,.swal2-popup .swal2-textarea::placeholder{color:#ccc}.swal2-popup .swal2-range input{width:80%}.swal2-popup .swal2-range output{width:20%;font-weight:600;text-align:center}.swal2-popup .swal2-range input,.swal2-popup .swal2-range output{height:2.625em;margin:1em auto;padding:0;font-size:1.125em;line-height:2.625em}.swal2-popup .swal2-input{height:2.625em;padding:0 .75em}.swal2-popup .swal2-input[type=number]{max-width:10em}.swal2-popup .swal2-file{font-size:1.125em}.swal2-popup .swal2-textarea{height:6.75em;padding:.75em}.swal2-popup .swal2-select{min-width:50%;max-width:100%;padding:.375em .625em;color:#545454;font-size:1.125em}.swal2-popup .swal2-checkbox,.swal2-popup .swal2-radio{align-items:center;justify-content:center}.swal2-popup .swal2-checkbox label,.swal2-popup .swal2-radio label{margin:0 .6em;font-size:1.125em}.swal2-popup .swal2-checkbox input,.swal2-popup .swal2-radio input{margin:0 .4em}.swal2-popup .swal2-validation-message{display:none;align-items:center;justify-content:center;padding:.625em;background:#f0f0f0;color:#666;font-size:1em;font-weight:300;overflow:hidden}.swal2-popup .swal2-validation-message::before{display:inline-block;width:1.5em;min-width:1.5em;height:1.5em;margin:0 .625em;border-radius:50%;background-color:#f27474;color:#fff;font-weight:600;line-height:1.5em;text-align:center;content:'!';zoom:normal}@supports (-ms-accelerator:true){.swal2-range input{width:100%!important}.swal2-range output{display:none}}@media all and (-ms-high-contrast:none),(-ms-high-contrast:active){.swal2-range input{width:100%!important}.swal2-range output{display:none}}@-moz-document url-prefix(){.swal2-close:focus{outline:2px solid rgba(50,100,150,.4)}}.swal2-icon{position:relative;justify-content:center;width:5em;height:5em;margin:1.25em auto 1.875em;border:.25em solid transparent;border-radius:50%;line-height:5em;cursor:default;box-sizing:content-box;-webkit-user-select:none;-moz-user-select:none;-ms-user-select:none;user-select:none;zoom:normal}.swal2-icon-text{font-size:3.75em}.swal2-icon.swal2-error{border-color:#f27474}.swal2-icon.swal2-error .swal2-x-mark{position:relative;flex-grow:1}.swal2-icon.swal2-error [class^=swal2-x-mark-line]{display:block;position:absolute;top:2.3125em;width:2.9375em;height:.3125em;border-radius:.125em;background-color:#f27474}.swal2-icon.swal2-error [class^=swal2-x-mark-line][class$=left]{left:1.0625em;-webkit-transform:rotate(45deg);transform:rotate(45deg)}.swal2-icon.swal2-error [class^=swal2-x-mark-line][class$=right]{right:1em;-webkit-transform:rotate(-45deg);transform:rotate(-45deg)}.swal2-icon.swal2-warning{border-color:#facea8;color:#f8bb86}.swal2-icon.swal2-info{border-color:#9de0f6;color:#3fc3ee}.swal2-icon.swal2-question{border-color:#c9dae1;color:#87adbd}.swal2-icon.swal2-success{border-color:#a5dc86}.swal2-icon.swal2-success [class^=swal2-success-circular-line]{position:absolute;width:3.75em;height:7.5em;-webkit-transform:rotate(45deg);transform:rotate(45deg);border-radius:50%}.swal2-icon.swal2-success [class^=swal2-success-circular-line][class$=left]{top:-.4375em;left:-2.0635em;-webkit-transform:rotate(-45deg);transform:rotate(-45deg);-webkit-transform-origin:3.75em 3.75em;transform-origin:3.75em 3.75em;border-radius:7.5em 0 0 7.5em}.swal2-icon.swal2-success [class^=swal2-success-circular-line][class$=right]{top:-.6875em;left:1.875em;-webkit-transform:rotate(-45deg);transform:rotate(-45deg);-webkit-transform-origin:0 3.75em;transform-origin:0 3.75em;border-radius:0 7.5em 7.5em 0}.swal2-icon.swal2-success .swal2-success-ring{position:absolute;top:-.25em;left:-.25em;width:100%;height:100%;border:.25em solid rgba(165,220,134,.3);border-radius:50%;z-index:2;box-sizing:content-box}.swal2-icon.swal2-success .swal2-success-fix{position:absolute;top:.5em;left:1.625em;width:.4375em;height:5.625em;-webkit-transform:rotate(-45deg);transform:rotate(-45deg);z-index:1}.swal2-icon.swal2-success [class^=swal2-success-line]{display:block;position:absolute;height:.3125em;border-radius:.125em;background-color:#a5dc86;z-index:2}.swal2-icon.swal2-success [class^=swal2-success-line][class$=tip]{top:2.875em;left:.875em;width:1.5625em;-webkit-transform:rotate(45deg);transform:rotate(45deg)}.swal2-icon.swal2-success [class^=swal2-success-line][class$=long]{top:2.375em;right:.5em;width:2.9375em;-webkit-transform:rotate(-45deg);transform:rotate(-45deg)}.swal2-progresssteps{align-items:center;margin:0 0 1.25em;padding:0;font-weight:600}.swal2-progresssteps li{display:inline-block;position:relative}.swal2-progresssteps .swal2-progresscircle{width:2em;height:2em;border-radius:2em;background:#3085d6;color:#fff;line-height:2em;text-align:center;z-index:20}.swal2-progresssteps .swal2-progresscircle:first-child{margin-left:0}.swal2-progresssteps .swal2-progresscircle:last-child{margin-right:0}.swal2-progresssteps .swal2-progresscircle.swal2-activeprogressstep{background:#3085d6}.swal2-progresssteps .swal2-progresscircle.swal2-activeprogressstep~.swal2-progresscircle{background:#add8e6}.swal2-progresssteps .swal2-progresscircle.swal2-activeprogressstep~.swal2-progressline{background:#add8e6}.swal2-progresssteps .swal2-progressline{width:2.5em;height:.4em;margin:0 -1px;background:#3085d6;z-index:10}[class^=swal2]{-webkit-tap-highlight-color:transparent}.swal2-show{-webkit-animation:swal2-show .3s;animation:swal2-show .3s}.swal2-show.swal2-noanimation{-webkit-animation:none;animation:none}.swal2-hide{-webkit-animation:swal2-hide .15s forwards;animation:swal2-hide .15s forwards}.swal2-hide.swal2-noanimation{-webkit-animation:none;animation:none}.swal2-rtl .swal2-close{right:auto;left:0}.swal2-animate-success-icon .swal2-success-line-tip{-webkit-animation:swal2-animate-success-line-tip .75s;animation:swal2-animate-success-line-tip .75s}.swal2-animate-success-icon .swal2-success-line-long{-webkit-animation:swal2-animate-success-line-long .75s;animation:swal2-animate-success-line-long .75s}.swal2-animate-success-icon .swal2-success-circular-line-right{-webkit-animation:swal2-rotate-success-circular-line 4.25s ease-in;animation:swal2-rotate-success-circular-line 4.25s ease-in}.swal2-animate-error-icon{-webkit-animation:swal2-animate-error-icon .5s;animation:swal2-animate-error-icon .5s}.swal2-animate-error-icon .swal2-x-mark{-webkit-animation:swal2-animate-error-x-mark .5s;animation:swal2-animate-error-x-mark .5s}@-webkit-keyframes swal2-rotate-loading{0%{-webkit-transform:rotate(0);transform:rotate(0)}100%{-webkit-transform:rotate(360deg);transform:rotate(360deg)}}@keyframes swal2-rotate-loading{0%{-webkit-transform:rotate(0);transform:rotate(0)}100%{-webkit-transform:rotate(360deg);transform:rotate(360deg)}}@media print{body.swal2-shown:not(.swal2-no-backdrop):not(.swal2-toast-shown){overflow-y:scroll!important}body.swal2-shown:not(.swal2-no-backdrop):not(.swal2-toast-shown)>[aria-hidden=true]{display:none}body.swal2-shown:not(.swal2-no-backdrop):not(.swal2-toast-shown) .swal2-container{position:initial!important}}");

/***/ }),

/***/ 0:
/*!***************************************************************************************************************************************************************************************************************!*\
  !*** multi ./resources/assets/src/js/entry.js ./resources/assets/src/css/less/style.less ./resources/assets/src/plugins/bootstrap/css/bootstrap.scss ./resources/assets/src/plugins/select2/css/select2.scss ***!
  \***************************************************************************************************************************************************************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

__webpack_require__(/*! /home/roman/projects/oovii/modules/core/resources/assets/src/js/entry.js */"./resources/assets/src/js/entry.js");
__webpack_require__(/*! /home/roman/projects/oovii/modules/core/resources/assets/src/css/less/style.less */"./resources/assets/src/css/less/style.less");
__webpack_require__(/*! /home/roman/projects/oovii/modules/core/resources/assets/src/plugins/bootstrap/css/bootstrap.scss */"./resources/assets/src/plugins/bootstrap/css/bootstrap.scss");
module.exports = __webpack_require__(/*! /home/roman/projects/oovii/modules/core/resources/assets/src/plugins/select2/css/select2.scss */"./resources/assets/src/plugins/select2/css/select2.scss");


/***/ })

/******/ });
