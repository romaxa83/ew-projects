<template>
    <div class="position-relative" ref="fromTabs" style="min-height: 250px;">
        <loading :active.sync="isLoading" :is-full-page="false"/>
        <b-row class="mb-2">
            <b-col sm="3">
                <b-form-input
                    v-model="filter"
                    type="search"
                    size="sm"
                    :placeholder="translations.filter"/>
            </b-col>
        </b-row>
        <div class="pull-right mb-2">
            <b-btn v-b-modal.add-spec-value variant="info" size="sm">{{ translations.add }}</b-btn>
        </div>
        <b-tabs v-model="tabIndex" v-if="showLangTabs" nav-class="no-border">
            <b-tab v-for="language in locales" :title="language"/>
        </b-tabs>
        <b-form class="mb-2">
            <b-table striped hover bordered
                     :items="items"
                     :fields="fields"
                     :filter="filter"
                     :per-page="perPage"
                     :current-page="currentPage"
                     @filtered="onFiltered">

                <template slot="number" slot-scope="row">
                    {{ row.item.number }}
                </template>

                <template slot="name" slot-scope="row">
                    <b-tabs v-model="tabIndex" :no-nav-style="true" nav-class="d-none">
                        <b-tab v-for="(language, locale, index) in locales">
                            <template slot="tabs"/>
                            <b-form-group>
                                <b-form-input v-model="row.item[locale].name"
                                              :data-input="locale + '-' + row.index"
                                              :name="locale + '-name-' + row.item.number"
                                              v-validate="{ required: true, max: 255}"
                                              :data-slug-source="'row' + row.item.number + '-' + (index === 0 ? 'source' : '')"
                                              :data-vv-as="translations.name"/>
                                <b-form-invalid-feedback v-if="errors.has(locale + '-name-' + row.item.number)">
                                    {{ errors.first(locale + '-name-' + row.item.number) }}
                                </b-form-invalid-feedback>
                            </b-form-group>
                        </b-tab>
                    </b-tabs>
                </template>

                <template slot="slug" slot-scope="row">
                    <sluggable :source="'[data-slug-source=row' + row.item.number + '-source]'"
                               v-model="row.item.slug"
                               :validator="$validator"
                               :name="'slug-' + row.item.number"
                               :translations="translations"/>
                </template>

                <template slot="color" slot-scope="row">
                    <color-picker v-model="row.item.color"/>
                </template>

                <template slot="sort" slot-scope="row">
                    <b-form-input v-model="row.item.sort" type="number"/>
                </template>

                <template slot="published" slot-scope="row">
                    <b-form-checkbox v-model="row.item.published"/>
                </template>

                <template slot="control" slot-scope="row">
                    <b-button size="sm" variant="danger" @click.prevent="deleteItem(row.item.id)"
                              v-b-tooltip.hover :title="translations.delete"><i class="fa fa-trash"/></b-button>
                </template>
            </b-table>
        </b-form>
        <div class="row">
            <div class="col-lg-6">
                <b-pagination :total-rows="totalRows" :per-page="perPage" v-model="currentPage"/>
            </div>
            <div class="col-lg-6 pull-right">
                <b-form-group label-cols-sm="3" :label="translations.itemsPerPage">
                    <b-form-select :options="perPageOptions" v-model="perPage"/>
                </b-form-group>
            </div>
        </div>
        <div class="pull-right mt-2">
            <b-btn v-b-modal.add-spec-value variant="info" size="sm">{{ translations.add }}</b-btn>
        </div>
        <create-spec-value-modal :locales="locales" :create-url="createRoute()" :is-color="isColor"
                                 :translations="translations" @loadData="loadData()"/>
    </div>
</template>

<script>
import CreateSpecValueModal from './CreateSpecValueModal';

export default {
        name: 'specificationValues',
        props: {
            resourceUrl: String,
            specificationId: Number,
            isColor: {
                type: Boolean,
                default: false
            },
            locales: Object
        },
        components: {
            CreateSpecValueModal
        },
        data () {
            const translations = window.translations.specifications;

            let fields = [
                {
                    key: 'number',
                    label: '#',
                    class: 'small-column'
                },
                {
                    key: 'name',
                    label: translations.name
                },
                {
                    key: 'slug',
                    label: translations.slug
                }
            ];

            if (this.isColor) {
                fields.push({
                    key: 'color',
                    label: translations.color,
                });
            }

            fields = fields.concat([
                {
                    key: 'sort',
                    label: translations.position,
                    class: 'w-10'
                },
                {
                    key: 'published',
                    label: translations.published,
                    class: 'small-column text-center'
                },
                {
                    key: 'control',
                    label: translations.control,
                    class: 'small-column text-center'
                }
            ]);

            return {
                isLoading: true,
                tabIndex: 0,
                items: [],
                fields: fields,
                currentPage: 1,
                totalRows: 1,
                perPage: 10,
                filter: null,
                perPageOptions: [10, 25, 50, 100, 150],
                validateModal: false,
                translations: translations,
                showLangTabs: Object.keys(this.locales).length > 1
            };
        },
        methods: {
            createRoute () {
                return route(this.resourceUrl + '.create', this.specificationId);
            },
            loadData () {
                this.$http.get(route(this.resourceUrl + '.list', this.specificationId)).then(response => {
                    this.items = response.data.items;
                    this.isLoading = false;
                    this.totalRows = this.items.length;
                });
            },
            saveWithMainForm () {
              let $this = this;
              return $this.$validator.validateAll().then((result, reject) => {
                if (!result) {
                  reject();
                }

                return $this.$http.post(
                    route($this.resourceUrl + '.save', $this.specificationId),
                    {data: this.items}
                  );
              });
            },
            deleteItem (id) {
                window.inits.confirmDelete(null, null, null, null, null, (result) => {
                    if (result.value) {
                        this.isLoading = true;
                        this.$http.post(route(this.resourceUrl + '.delete', id))
                            .then((response) => {
                                if (response.data.success) {
                                    this.items.forEach((item, index) => {
                                        if (item.id === id) {
                                            this.items.splice(index, 1);
                                        }
                                    });
                                }
                                this.isLoading = false;
                            }).catch(() => {
                            this.isLoading = false;
                        });
                    }
                });
            },
            onFiltered (filteredItems) {
                this.totalRows = filteredItems.length;
                this.currentPage = 1;
            }
        },
        mounted () {
            this.loadData();
        }
    }
</script>
<style>
    .form-group {
        position: relative;
    }

    .vue-specification .form-group:only-child {
        margin-bottom: 0;
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
</style>
