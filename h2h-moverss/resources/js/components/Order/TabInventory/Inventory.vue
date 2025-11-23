<template>
    <div class="panel">
        <div
            v-if="isManualMode && processing"
            class="frame-wrap position-absolute w-100 h-100 opacity-50"
            style="z-index: 5"
        >
            <div
                class="w-100 h-100 d-flex justify-content-center align-items-center"
            >
                <div
                    class="spinner-border text-info position-absolute"
                    role="status"
                >
                    <span class="sr-only">Loading...</span>
                </div>
            </div>
        </div>
        <div class="panel-hdr">
            <h2>Inventory</h2>
            <div
                v-if="canManage"
                class="custom-control custom-checkbox custom-control-inline"
            >
                <input
                    type="checkbox"
                    class="custom-control-input"
                    id="creating_on_enter"
                    v-model="creating_on_enter"
                />
                <label class="custom-control-label" for="creating_on_enter"
                >Add New Item on Enter</label
                >
            </div>
            <div v-if="canManage" class="panel-toolbar">
                <button
                    class="btn btn-outline-info btn-sm mr-2 waves-effect waves-themed"
                    @click="addRoom"
                >
                    Add room
                </button>
            </div>
            <div v-if="canManage" class="panel-toolbar ml-1">
                <button
                    class="btn btn-outline-info btn-sm mr-2 waves-effect waves-themed"
                    @click="addItem"
                >
                    Add item
                </button>
            </div>
            <div v-if="canManage" class="panel-toolbar ml-1">
                <button
                    type="button"
                    class="btn btn-sm waves-effect waves-themed"
                    @click="saveChanges()"
                    :class="{
						'btn-danger': is_changed,
						'btn-default': !is_changed,
					}"
                    :disabled="!is_changed || isAutosaveMode"
                >
					<span
                        v-show="loading"
                        class="spinner-border spinner-border-sm mr-2"
                        role="status"
                        aria-hidden="true"
                    ></span>
                    <i class="fal fa-download mr-1"></i>
                    Save inventory
                </button>
            </div>
        </div>
        <div class="panel-container collapse show">
            <div class="panel-content pt-2">
                <!--
                {{--                    <div class="nestable-thead">--}}
                {{--                        <div class="d-inline-block px-1">--}}
                    {{--                            2--}}
                    {{--                        </div>--}}
                {{--                        <div class="d-inline-block px-1 width-10">--}}
                    {{--                            <span class="fw-700 fs-sm pr-2">Cuft</span>--}}
                    {{--                        </div>--}}
                {{--                        <div class="d-inline-block px-1 width-10">--}}
                    {{--                            <span class="fw-700 fs-sm">Weight</span>--}}
                    {{--                        </div>--}}
                {{--                        <div class="d-inline-block px-1 width-10">--}}
                    {{--                            <span class="fw-700 fs-sm">Price</span>--}}
                    {{--                        </div>--}}
                {{--                    </div>--}}
                -->
                <div class="dd nestable-inventory">
                    <ol class="dd-list"></ol>
                </div>

                <div class="inventory-items">
                    <inventory-draggable
                        :records="records"
                        :creating-on-enter="creating_on_enter"
                        :can-manage="canManage"
                        @addItem="addItem"
                        @removeItem="removeItem"
                        @updateIsChanged="updateIsChanged"
                        @triggerChanged="triggerChanged"
                    />
                </div>
                <div v-if="canManage" class="mt-2 ml-5">
                    <button
                        class="btn btn-outline-info btn-sm mr-2 waves-effect waves-themed"
                        @click="addRoom"
                    >
                        Add room
                    </button>
                    <button
                        class="btn btn-outline-info btn-sm mr-2 waves-effect waves-themed"
                        @click="addItem"
                    >
                        Add item
                    </button>
                    <button
                        v-if="isManualMode"
                        type="button"
                        class="btn btn-sm waves-effect waves-themed"
                        @click="saveChanges()"
                        :class="{
						'btn-danger': is_changed,
						'btn-default': !is_changed,
					}"
                        :disabled="!is_changed"
                    >
					<span
                        v-show="loading"
                        class="spinner-border spinner-border-sm mr-2"
                        role="status"
                        aria-hidden="true"
                    ></span>
                        <i class="fal fa-download mr-1"></i>
                        Save inventory
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import 'ant-design-vue/lib/input-number/style/index.css';
import InventoryDraggable
    from '@components/Order/TabInventory/Inventory/Draggable';
import cloneDeep from 'lodash.clonedeep';
import Debounce from 'lodash.debounce';
import { mapGetters } from 'vuex';

let order_id = document.getElementById('order_id').textContent;

export default {
    name: 'TabInventory',
    components: {
        InventoryDraggable,
    },
    props: {
        is_changed: {
            type: Boolean,
            required: false,
        },
        canManage: {
            type: Boolean,
            required: true,
        },
    },
    data() {
        return {
            loading: false,
            creating_on_enter: true,
            enabled: true,
            records: [],
        };
    },
    mounted() {
        this.$store
            .dispatch('getSession')
            .then(({ order }) => {
                this.formatRecords(order.inventories);
            })
            .finally(() => {
                if (window.Echo) {
                    window.Echo.channel(`order.${order_id}`).listen(
                        '.order.client.edit.inventory',
                        (e) => {
                            const response = e[0];
                            this.formatRecords(response.inventories);
                            this.updateIsChanged(false);
                            this.$store.dispatch('order/updateInventory', response);
                            this.$store.dispatch('order/refetchChangelog');
                        }
                    );
                }
            });
    },
    computed: {
        isManualMode() {
          return !this.isAutosaveMode;
        },
        ...mapGetters({
            processing: 'order/inventoriesProcessing',
            isAutosaveMode: 'order/inventoryIsAutosaveMode',
        }),
    },
    methods: {
        addItem({parent_index = -1, index = -1, isFolder = false}) {
            let new_item = {
                children: [],
                id: null,
                is_section: 0,
                item_id: 0,
                order_id,
                price: null,
                qty: 1,
                title: null,
                volume: null,
                weight: null,
                autocompleteData: [],
                isLoading: false,
                randomRef: App.Miscs.generateToken(),
            };
            let focus = `.el_${parent_index}_${index}`;

            if (parent_index === -1 && index === -1) {
                this.records.push(new_item);
                index = this.records.length - 1;

                focus = `.el_${parent_index}_${index}`;
            } else {
                // detect position
                if (parent_index === -1 && !isFolder) {
                    this.records.splice(index, 0, new_item);
                } else if (isFolder && !this.records[parent_index]) {
                    console.log(
                        `add child to folder parent_index: ${parent_index} index: ${index}`
                    );

                    this.records[index].children.push(new_item);
                    focus = `.el_${index}_0`;
                } else {
                    let empty_exists =
                        this.records[parent_index].children[
                        this.records[parent_index].children.length - 1
                            ].title === null;
                    console.log('add child empty_exists', empty_exists);

                    if (!empty_exists)
                        this.records[parent_index].children.splice(
                            index,
                            0,
                            new_item
                        );
                }
            }

            this.updateIsChanged(true);

            // Focus
            this.$nextTick(() => {
                $(`${focus} .ant-input-number-input`).focus().select();
                $(`${focus}`)[0]?.scrollIntoView({
                    block: 'center',
                    behavior: 'smooth',
                });
            });
        },
        addRoom() {
            this.records.push({
                id: null,
                order_id,
                is_section: 1,
                title: null,
                title_back: null,
                sort: this.records.length + 1,
                children: [],
                autocompleteData: [],
                isLoading: false,
                randomRef: App.Miscs.generateToken(),
            });

            this.creating_on_enter = true;
            this.updateIsChanged(true);

            // Focus
            let parent_index = this.records.length - 1;
            this.$nextTick(() => $(`.el_${parent_index}`).focus());
        },
        formatRecords(records) {
            this.records = cloneDeep(records)
                .sort((a, b) => a.sort - b.sort)
                .map((item) => {
                    item.autocompleteData = [];
                    item.title_back = item.title;
                    item.isLoading = false;
                    item.randomRef = App.Miscs.generateToken();

                    item.children
                        .sort((a, b) => a.sort - b.sort)
                        .map((item) => {
                            item.autocompleteData = [];
                            item.title_back = item.title;
                            item.isLoading = false;
                            item.randomRef = App.Miscs.generateToken();

                            return item;
                        });

                    return item;
                });
        },
        removeItem({parent_index = -1, index = -1}) {
            if (parent_index === -1) this.$delete(this.records, index);
            else this.$delete(this.records[parent_index].children, index);
            this.updateIsChanged(true);
        },
        saveChanges(promise = null) {
            if (!this.canManage) {
                App.Forms.showAlert(
                    'error',
                    'Forbidden',
                    'You have no permission to manage inventory'
                );
                return;
            }
            this.loading = true;
            this.$store.commit('order/setInventoryProcessing', true);

            // Changing sort
            let records = cloneDeep(this.records);
            let sort = 1;
            records.map((item) => {
                item.sort = sort++;

                let sortChild = 1;
                item.children.map((item) => {
                    item.sort = sortChild++;

                    return item;
                });

                return item;
            });

            return axios
                .post('/orders/inventory/save', {
                    order_id,
                    records,
                })
                .then((resp) => {
                    if (resp.data.success === true) {
                        if (resp.data.record) {
                            this.$store.commit(
                                'order/setInventoryRecords',
                                resp.data.record.inventories
                            );

                            this.$store.commit(
                                'order/setSizingVolume',
                                resp.data.record.sizing_volume
                            );
                            this.$store.commit(
                                'order/setSizingWeight',
                                resp.data.record.sizing_weight
                            );

                            // Map Virtual items
                            if (
                                resp.data.hasOwnProperty('ref2id') &&
                                Object.keys(resp.data.ref2id).length
                            ) {
                                this.records.forEach((parentItem, pK) => {
                                    if (
                                        !parentItem.id &&
                                        resp.data.ref2id[parentItem.randomRef]
                                    ) {
                                        // Fill parent keys
                                        this.$set(
                                            this.records[pK],
                                            'id',
                                            resp.data.ref2id[
                                                parentItem.randomRef
                                                ]
                                        );
                                    }

                                    // fill children
                                    parentItem.children.forEach(
                                        (childItem, cK) => {
                                            if (
                                                !childItem.id &&
                                                resp.data.ref2id[
                                                    childItem.randomRef
                                                    ]
                                            ) {
                                                // Fill parent keys
                                                this.$set(
                                                    this.records[pK].children[
                                                        cK
                                                        ],
                                                    'id',
                                                    resp.data.ref2id[
                                                        childItem.randomRef
                                                        ]
                                                );
                                            }
                                        }
                                    );
                                });
                            }

                            // this.formatRecords(resp.data.record.inventories); // При перегенерации блимало
                        }
                        this.updateIsChanged(false);

                        $(
                            '.nestable-inventory .dd-list .is-invalid'
                        ).removeClass('is-invalid');
                        if (promise) promise.resolve();
                    } else {
                        throw {
                            response: {
                                data: resp.data,
                            },
                        };
                    }
                })
                .then(() => {
                    this.$store.dispatch('order/refetchChangelog', 'update');
                })
                .catch((error) => {
                    App.Forms.simpleErrors(error.response.data);
                    if (promise) promise.reject(error.response.data);
                })
                .finally(() => {
                    this.loading = false;
                    this.$store.commit('order/setInventoryProcessing', false);
                });
        },
        saveChangesDeb: Debounce(function (promise = null) {
            this.saveChanges(promise);
        }, 2000),
        updateIsChanged(value) {
            this.$emit('update:is_changed', !!value);
        },
        triggerChanged: Debounce(function () {
            this.$store.commit(
                'order/setInventoryRecords',
                cloneDeep(this.records)
            );
            this.updateIsChanged(true);

            // Пуш на апдейт
            this.saveChangesDeb();
        }, 100),
    },
};
</script>

<style>
.fix-height {
    margin-top: 5px;
}

.dd-list .list-group-item-action:focus {
    background-color: #c6c8ca;
}
</style>
