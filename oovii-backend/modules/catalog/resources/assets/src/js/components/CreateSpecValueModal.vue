<template>
    <b-modal id="add-spec-value"
             :title="translations.createNewSpecValue"
             ref="modal"
             :cancel-title="translations.cancel"
             :ok-title="translations.save"
             :no-close-on-backdrop="true"
             @ok="createSpecValue"
             @hidden="resetNewSpecValue"
             @shown="hideLangTabs"
    >
        <b-form ref="specForm">
            <b-form-group class="form-group">
                <b-form-checkbox v-model="published">{{ translations.published }}</b-form-checkbox>
            </b-form-group>
            <b-tabs nav-class="js-lang-tabs">
                <b-tab v-for="(language, locale, index) in locales" :title="language">
                    <b-form-group :label="translations.name" label-class="required">
                        <b-form-input v-model="name[locale]"
                                      :name="locale + '[name]'"
                                      :data-vv-as="translations.name"
                                      v-validate="{ required: true, max: 255}"
                                      :data-slug-source="index === 0"
                                      :class="{ 'is-invalid': errors.has(locale + '[name]') }"></b-form-input>
                        <b-form-invalid-feedback v-if="errors.has(locale + '[name]')">
                            {{ errors.first(locale + '[name]') }}
                        </b-form-invalid-feedback>
                    </b-form-group>
                </b-tab>
            </b-tabs>
            <b-form-group :label="translations.slug" label-class="required">
                <sluggable :source="'[data-slug-source=true]'"
                           name="slug"
                           v-model="slug"
                           :validator="$validator"
                           :translations="translations"></sluggable>
            </b-form-group>
            <b-form-group :label="translations.color" v-if="isColor">
                <color-picker v-model="color"></color-picker>
            </b-form-group>
        </b-form>
    </b-modal>
</template>

<script>
    export default {
        name: "CreateSpecValueModal",
        props: {
            locales: Object,
            createUrl: String,
            isColor: Boolean,
            translations: Object
        },
        data() {
            return {
                published: true,
                slug: '',
                name: {},
                color: '',
            };
        },
        methods: {
            hideLangTabs() {
                window.inits.hideLangTabs();
            },
            createSpecValue(event) {
                event.preventDefault();
                this.$validator.validate().then((result) => {
                    if (result) {
                        let data = {
                            published: this.published,
                            slug: this.slug,
                            name: this.name,
                            color: this.color,
                        };

                        this.$http.post(this.createUrl, data)
                            .then((response) => {
                                if (response.data.success) {
                                    this.$refs.modal.hide();
                                    this.isLoading = true;
                                    this.$emit('loadData');
                                }
                                return false;
                            }).catch((e) => {
                            console.warn(e)
                        });
                    } else {
                        if ($(this.$refs.specForm).find('.form-control.is-invalid').length) {
                            let tabs = $(this.$refs.specForm).find('.tab-pane');

                            $.each(tabs, function (i, tab) {
                                if ($(tab).find('.form-control.is-invalid').length) {
                                    let link = $('#' + $(tab).attr('aria-lablelledby'));
                                    if (!link.hasClass('active')) {
                                        link.addClass('flash animated bg-danger');
                                        let t = setTimeout(function () {
                                            clearTimeout(t);
                                            link.removeClass('flash animated bg-danger');
                                        }, 500);
                                    }
                                }
                            });
                            return false;
                        }
                    }
                })

            },
            resetNewSpecValue() {
                this.published = true;
                this.slug = '';
                this.name = {};
                this.color = '';
            }
        }
    }
</script>
<style>
    .form-group {
        position: relative;
    }

    .invalid-feedback {
        display: block;
    }

    .invalid-feedback:empty {
        display: none !important;
    }

    .invalid-feedback {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        line-height: 1;
        color: rgb(220, 53, 69);
        background-color: rgba(220, 53, 69, 0.2);
        padding: 5px 10px;
        transform: translateY(100%);
    }
    .modal.show+div+.modal-backdrop {
        opacity: .5;
    }
</style>
