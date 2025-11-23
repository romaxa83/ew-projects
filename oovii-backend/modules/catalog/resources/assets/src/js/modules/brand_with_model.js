export default class BrandWithModel {
    constructor() {
        this.$brandSelector = $('.js-brand-selector');
        this.$modelSelector = $('.js-model-selector');
    }

    start() {
        if (this.$brandSelector.length === 0 || this.$modelSelector.length === 0) {
            return false;
        }

        this.$brandSelector.change(() => {
            let brandId = this.$brandSelector.val();

            if (brandId) {
                this.$modelSelector.data('url', route('admin.models.search', {brand_id: brandId}));
            } else {
                this.$modelSelector.data('url', route('admin.models.search'));
            }

            this.$modelSelector.val('');
            this.$modelSelector.trigger('change');
            inits.ajaxSelect2(this.$modelSelector);
        });
    }
};
