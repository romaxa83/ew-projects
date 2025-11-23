import SpecificationValues from './components/SpecificationValues';
import BrandWithModel from './modules/brand_with_model';
import Products from './modules/products';

import Vue from 'vue';
import VeeValidate from 'vee-validate';
import BootstrapVue from 'bootstrap-vue';
import Sluggable from './components/sluggable';
import ColorPicker from './components/color-picker';
import Loading from 'vue-loading-overlay';
import 'vue-loading-overlay/dist/vue-loading.min.css';
import setLocale from './languages';

Vue.prototype.$http = axios;

Vue.config.productionTip = false;

Vue.use(VeeValidate, {
    fieldsBagName: 'veeFields'
});
Vue.use(BootstrapVue);
Vue.component('sluggable', Sluggable);
Vue.component('color-picker', ColorPicker);
Vue.component('loading', Loading);
Vue.component('specification-values', SpecificationValues);

// Add product template to select2Templates
if (typeof window.select2Templates !== 'undefined') {
    window.select2Templates['product'] = function (state) {
        let data = state.data || $(state.element).data();
        return $(`<div class="d-flex">
                    <div style="width: 50px;height: 50px;">
                        <img src="${data.image}" alt="${data.name}" style="max-height: 100%; max-width: 100%">
                    </div>
                    <div class="m-l-2">
                        <div class="f-s-18">${data.name}</div>
                        <div>
                            <div class="dib">${data.cost}</div>
                            <small>${data.currency}</small>
                            <div class="dib">${data.qty}</div>
                        </div>
                    </div>
                </div>`);
    };

    window.select2Templates['product-label'] = function (state) {
        let data = state.data || $(state.element).data();

        return $(`<div class="d-flex">
                    <div style="width: 42px; padding: 2px">
                        <img src="${data.image}" alt="${data.name}" style="max-height: 100%; max-width: 100%">
                    </div>
                    <div class="m-l-2 f-s-18">${data.name}</div>
                </div>`);
    };
}

$(document).ready(() => {
    setLocale($('html').attr('lang'));
    let brandWithModel = new BrandWithModel();
    brandWithModel.start();

    Products.costHandler();
    Products.discountCost();

    setTimeout(function () {
        let commentsWrapper = document.getElementById("comments-list-wrapper");
        if (commentsWrapper) {
            commentsWrapper.scrollTop = commentsWrapper.scrollHeight;
        }
    }, 500);

    if ($('.vue-specification').length) {
        const specifications = new Vue().$mount(document.querySelector('.vue-specification'));

        $('#form').submit(function (e) {
            const $this = $(this);

            if (!$this.data('saved-specifications')) {
                e.preventDefault();

                specifications.$refs.specifications.saveWithMainForm()
                    .then(() => {
                        $this.data('saved-specifications', true);

                        $this.submit();
                    }).catch(() => {
                        $this.data('saved-specifications', false);
                    });
                }
        })
    }

    if (typeof window.massActions === 'undefined') {
        window.massActions = {};
    }

    window.massActions['changeCategoryPopup'] = function (formData, $button, rootEl) {
        inits.loader.add(rootEl);

        axios.post(route('admin.products.change-category-popup'), formData).then((response) => {
            if (response.data) {
                $('#change-category .modal-content').html(response.data);
                $('#change-category').modal('show');
            }
            inits.loader.remove(rootEl);
            $button.prop('disabled', false);
        }).catch(() => {
            inits.loader.remove(rootEl);
            $button.prop('disabled', false);
        });
    };
});
