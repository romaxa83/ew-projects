<template>
    <div>
        <div class="row">
            <div class="col">
                <div class="d-flex">
                    <label for="calc_sizing" class="form-label mr-auto">
						<span
                            v-show="processing"
                            class="spinner-border spinner-border-sm mr-1"
                            role="status"
                            aria-hidden="true"
                        ></span>
                        Inventory size
                    </label>
                    <div
                        v-if="canManage"
                        class="custom-control d-flex custom-switch"
                    >
                        <input
                            id="calc_sizing_auto"
                            type="checkbox"
                            class="custom-control-input"
                            v-model="sizing_is_auto"
                            :disabled="isLocked"
                        />
                        <label
                            for="calc_sizing_auto"
                            @click.prevent="isLocked ? undefined : toggleAuto()"
                            class="custom-control-label fw-500"
                        >
                            Auto size
                        </label>
                    </div>
                </div>
                <div
                    class="input-group bg-white shadow-inset-2"
                    id="calc_sizing"
                >
                    <div class="input-group-prepend">
                        <label
                            for="inventories_vol_calc"
                            class="input-group-text"
                        >
                            CuFT
                        </label>
                    </div>
                    <input
                        id="inventories_vol_calc"
                        type="text"
                        class="form-control numeric-inputmask text-left pr-0"
                        v-model.number="sizing_volume"
                        placeholder="Volume"
                        :disabled="!!sizing_is_auto"
                    />
                    <input
                        id="inventories_wei_calc"
                        type="text"
                        class="form-control numeric-inputmask text-right pl-0"
                        v-model.number="sizing_weight"
                        placeholder="Weight"
                        :disabled="!!sizing_is_auto"
                    />
                    <div class="input-group-append">
                        <label
                            for="inventories_wei_calc"
                            class="input-group-text"
                        >
                            lb
                        </label>
                    </div>
                </div>
            </div>
        </div>
        <div class="row mb-2">
            <div class="col">
				<span class="help-block"
                >Auto: {{ totalVolume }}
					<span class="fs-xs fw-800">CuFT</span></span
                >
            </div>
            <div class="col text-right">
				<span class="help-block"
                >Auto: {{ totalWeight }}
					<span class="fs-xs fw-800">lb</span></span
                >
            </div>
        </div>
    </div>
</template>

<script>
import {mapGetters} from 'vuex';

export default {
    name: 'Sizing',
    props: {
        canManage: {
            type: Boolean,
            required: true,
        },
        isLocked: {
            type: Boolean,
            required: true,
        },
    },
    computed: {
        sizing_is_auto: {
            get() {
                return this.inventories.sizing_is_auto;
            },
        },
        sizing_volume: {
            get() {
                return this.inventories.sizing_volume;
            },
            set(value) {
                this.$store.commit('order/setSizingVolume', value);
            },
        },
        sizing_weight: {
            get() {
                return this.inventories.sizing_weight;
            },
            set(value) {
                this.$store.commit('order/setSizingWeight', value);
            },
        },
        totalVolume() {
            return this.formatRecords.reduce(function (sum, v) {
                return sum + v.volume;
            }, 0);
        },
        totalWeight() {
            return this.formatRecords.reduce(function (sum, v) {
                return sum + v.weight;
            }, 0);
        },
        ...mapGetters({
            inventories: 'order/inventories',
            formatRecords: 'order/inventoriesFormat',
            processing: 'order/inventoriesProcessing',
        }),
    },
    methods: {
        toggleAuto() {
            this.$root.$refs.inventory.proxyToggleAuto();
        },
    },
};
</script>
