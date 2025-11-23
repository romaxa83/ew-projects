<template>
    <div class="panel" id="calculation_panel">
        <div class="panel-hdr">
            <h2>
                Calculation
                <span
                    v-show="processing"
                    class="spinner-border spinner-border-sm ml-1"
                    role="status"
                    aria-hidden="true"
                ></span>
            </h2>
            <div class="panel-toolbar ml-2">
                <div v-if="getCalculationWarnings" class="mr-2">
                    <button
                        type="button"
                        @click="showCalculationWarnings"
                        class="btn btn-sm btn-warning btn-icon position-relative js-waves-off"
                    >
                        <i class="fas fa-exclamation-triangle"></i>
                        <span
                            class="badge border border-light rounded-pill bg-danger-500 position-absolute pos-top pos-right"
                            style="top: -6px; right: -7px"
                        >{{ getCalculationWarnings.length }}</span
                        >
                    </button>
                </div>
                <div
                    v-if="canManage"
                    class="custom-control d-flex custom-switch"
                >
                    <input
                        v-model="is_locked"
                        id="calc_locked"
                        type="checkbox"
                        class="custom-control-input"
                    />
                    <label
                        class="custom-control-label fw-500"
                        for="calc_locked"
                    >
                        <i class="fas fa-lock"></i>
                    </label>
                </div>
            </div>
        </div>
        <div class="panel-container show">
            <div class="panel-content px-1">
                <div
                    v-show="processing"
                    class="frame-wrap position-absolute w-100 h-100 opacity-50"
                    style="z-index: 10"
                >
                    <div
                        class="w-100 d-flex justify-content-center align-items-center"
                    >
                        <div
                            class="spinner-border text-info position-absolute"
                            style="top: 30%"
                            role="status"
                        >
                            <span class="sr-only">Loading...</span>
                        </div>
                    </div>
                </div>
                <div class="container">
                    <CalculationSizing
                        :can-manage="canManage"
                        :is-locked="!!is_locked"
                    />
                    <div class="row">
                        <div class="col mb-0">
                            <div class="form-group mb-0">
                                <label for="calc_trucks" class="form-label"
                                >Trucks</label
                                >
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <div class="input-group-text">
                                            <i class="fas fa-truck"></i>
                                        </div>
                                    </div>
                                    <select
                                        v-if="canManage"
                                        v-model.number="trucks"
                                        id="calc_trucks"
                                        type="text"
                                        :class="{
											'bg-transparent': !is_locked,
										}"
                                        class="form-control"
                                        :disabled="!!is_locked"
                                    >
                                        <option
                                            v-for="index in rangeTrucks"
                                            :key="index"
                                            v-bind:value="index"
                                        >
                                            {{ index }}
                                        </option>
                                    </select>
                                    <div v-else class="form-control">
                                        {{ trucks }}
                                    </div>
                                </div>
                                <span class="help-block"
                                >In services:
									{{ WorksCountValues.trucksMinMax }}</span
                                >
                            </div>
                        </div>
                        <div class="col mb-0">
                            <div class="form-group mb-0">
                                <label for="calc_crews" class="form-label"
                                >Crew</label
                                >
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <div class="input-group-text">
                                            <i class="fas fa-user-friends"></i>
                                        </div>
                                    </div>
                                    <select
                                        v-if="canManage"
                                        v-model.number="crews"
                                        id="calc_crews"
                                        type="text"
                                        :class="{
											'bg-transparent': !is_locked,
										}"
                                        class="form-control"
                                        :disabled="!!is_locked"
                                    >
                                        <option
                                            v-for="index in rangeCrews"
                                            :key="index"
                                            v-bind:value="index"
                                        >
                                            {{ index }}
                                        </option>
                                    </select>
                                    <div v-else class="form-control">
                                        {{ crews }}
                                    </div>
                                </div>
                                <span class="help-block"
                                >In services:
									{{ WorksCountValues.employeesMinMax }}</span
                                >
                                <!--                                        <span class="help-block">help text</span>-->
                            </div>
                        </div>
                    </div>
                    <hr/>
                    <type-local
                        v-if="estimate.type === 'local'"
                        :settings="settings"
                        :is_locked="is_locked"
                        :WorksCountValues="WorksCountValues"
                        :hours_min.sync="local.hours_min"
                        :hours_max.sync="local.hours_max"
                        :rate.sync="local.rate"
                        :is_auto.sync="local.is_auto"
                        :can-manage="canManage"
                        @inputChanged="inputChanged"
                    ></type-local>
                    <type-intrastate
                        v-else-if="estimate.type === 'intrastate'"
                        :settings="settings"
                        :is_locked="is_locked"
                        :rate_auto="intrastate.rate_auto"
                        :rate.sync="intrastate.rate"
                        :is_auto.sync="intrastate.is_auto"
                        :moving_distance.sync="calculated_moving_distance"
                        :moving_distance_is_auto.sync="
							calculated_moving_distance_is_auto
						"
                        :moving_distance_auto="
							estimate.calculated_moving_distance_auto
						"
                        :can-manage="canManage"
                        @inputChanged="inputChanged"
                    ></type-intrastate>
                    <type-interstate
                        v-else-if="estimate.type === 'interstate'"
                        :settings="settings"
                        :is_locked="is_locked"
                        :rate_auto="interstate.rate_auto"
                        :estimate_rate="EstimateRate"
                        :rate.sync="interstate.rate"
                        :is_auto.sync="interstate.is_auto"
                        :packing.sync="interstate.packing"
                        :unpacking.sync="interstate.unpacking"
                        :shuttle_pickup.sync="interstate.shuttle_pickup"
                        :shuttle_delivery.sync="interstate.shuttle_delivery"
                        :delivery_days.sync="interstate.delivery_days"
                        :can-manage="canManage"
                        @inputChanged="inputChanged"
                    ></type-interstate>
                    <hr/>
                    <div class="row mb-3">
                        <div class="col">
                            <label class="form-label" for="calc_fee_type"
                            >Travel Fee</label
                            >
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <select
                                        v-model="fee_type"
                                        id="calc_fee_type"
                                        class="custom-select border-right-0 border-top-0 border-left-0 rounded-0"
                                        :disabled="
											!canManage ||
											!!is_locked ||
											feeDisabled
										"
                                    >
                                        <option
                                            v-if="
												optionsAllowed &&
												optionsAllowed.fee_type &&
												optionsAllowed.fee_type.includes(
													'percent'
												)
											"
                                            value="percent"
                                        >
                                            Fee hr
                                        </option>
                                        <option
                                            v-if="
												optionsAllowed &&
												optionsAllowed.fee_type &&
												optionsAllowed.fee_type.includes(
													'sum'
												)
											"
                                            value="sum"
                                        >
                                            $
                                        </option>
                                    </select>
                                </div>
                                <input
                                    v-if="canManage"
                                    v-model.number="travel_fee"
                                    type="text"
                                    :class="{ 'bg-transparent': !is_locked }"
                                    class="onChanged form-control border-left-0 rounded-0 border-right-0 border-top-0 pl-0 numeric-inputmask"
                                    placeholder="0"
                                    :disabled="!!is_locked || feeDisabled"
                                />
                                <div v-else class="form-control">
                                    {{ travel_fee || '0' }}
                                </div>
                            </div>
                        </div>
                        <div class="col">
                            <div class="form-group mb-0">
                                <label
                                    for="calc_discount_type"
                                    class="form-label"
                                >Discount</label
                                >
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <select
                                            v-model="discount_type"
                                            id="calc_discount_type"
                                            class="custom-select border-right-0 border-top-0 border-left-0 rounded-0"
                                            :disabled="
												!canManage || !!is_locked
											"
                                        >
                                            <option value="sum">$</option>
                                            <option value="percent">%</option>
                                        </select>
                                    </div>
                                    <input
                                        v-if="canManage"
                                        v-model.number="discount_value"
                                        type="text"
                                        :class="{
											'bg-transparent': !is_locked,
										}"
                                        class="onChanged form-control border-left-0 border-top-0 border-right-0 pl-1 rounded-0 numeric-inputmask"
                                        placeholder="0"
                                        :disabled="!!is_locked"
                                    />
                                    <div v-else class="form-control">
                                        {{ discount_value || '0' }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <calculation-table></calculation-table>
                    <button
                        @click="generateInvoice()"
                        class="btn btn-block btn-success"
                    >
                        Generate Invoice
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import {AxiosHelper} from '@/helpers/axiosHelper';

import lodashRange from 'lodash.range';
import {mapGetters} from 'vuex';
import CalculationSizing from './Calculation/Sizing';
import CalculationTable from './Calculation/Table';
import TypeInterstate from './Calculation/TypeInterstate';
import TypeIntrastate from './Calculation/TypeIntrastate';
import TypeLocal from './Calculation/TypeLocal';

window.timer = window.timer || false;
let order_id = document.getElementById('order_id').textContent;

export default {
    name: 'OrderCalculation',
    components: {
        CalculationSizing,
        CalculationTable,
        TypeLocal,
        TypeIntrastate,
        TypeInterstate,
    },
    props: {
        processing: {
            type: Boolean,
            required: true,
        },
        canManage: {
            type: Boolean,
            required: true,
        },
    },
    data() {
        return {
            updated: false,
            is_locked: null,
            trucks: null,
            crews: null,
            discount_value: null,
            discount_type: null,
            fee_type: null,
            travel_fee: null,
            calculated_moving_distance: null,
            calculated_moving_distance_is_auto: null,
            local: {
                hours_min: null,
                hours_max: null,
                rate: null,
                is_auto: null,
            },
            intrastate: {
                rate: null,
                rate_auto: null,
                is_auto: null,
            },
            interstate: {
                rate: null,
                estimate_rate: null,
                rate_auto: null,
                is_auto: null,
                packing: null,
                unpacking: null,
                shuttle_pickup: null,
                shuttle_delivery: null,
                delivery_days: null,
            },
        };
    },
    computed: {
        getCalculationWarnings() {
            if (this.$store.state.order.calculationWarnings.length)
                return this.$store.state.order.calculationWarnings;
            return null;
        },
        EstimateRate() {
            return this.estimate.interstate
                ? this.estimate.interstate.estimate_rate
                : null;
        },
        WorksCountValues() {
            let trucksMinMax = 'n/a',
                employeesMinMax = 'n/a',
                keys = {
                    trucks: [],
                    employees: [],
                };

            // Наполяем значения для выборки
            this.works.records.forEach((item) => {
                if (item.trucks) keys.trucks.push(item.trucks);
                if (item.employees) keys.employees.push(item.employees);
            });

            let trucks_min = Math.min(...keys.trucks),
                trucks_max = Math.max(...keys.trucks),
                employees_min = Math.min(...keys.employees),
                employees_max = Math.max(...keys.employees),
                Duration = Object.values(this.works.records).reduce(function (
                        sum,
                        item
                    ) {
                        return sum + (item.duration ? parseInt(item.duration) : 0);
                    },
                    0);

            if (trucks_min || trucks_max) {
                trucksMinMax =
                    trucks_min !== trucks_max
                        ? trucks_min + ' - ' + trucks_max
                        : trucks_min;
            }
            if (employees_min || employees_max) {
                employeesMinMax =
                    employees_min !== employees_max
                        ? employees_min + ' - ' + employees_max
                        : employees_min;
            }

            return {
                trucksMinMax,
                employeesMinMax,
                Duration,
            };
        },
        noDelayUpdate() {
            // Смотрим если какое-то из полей менялось, оптравляем запрос на апдейт
            return [
                this.is_locked,
                this.local.is_auto,
                this.intrastate.is_auto,
                this.interstate.is_auto,
                this.trucks,
                this.crews,
                this.fee_type,
                this.discount_type,
                this.calculated_moving_distance_is_auto,
            ]
                .filter(function (el) {
                    return el !== null;
                })
                .join();
        },
        propertiesChanged() {
            // Смотрим если какое-то из полей менялось, оптравляем отложенный запрос на апдейт
            let val = [this.discount_value, this.travel_fee].filter(function (
                el
            ) {
                return el !== null;
            });
            if (this.estimate.type === 'local') {
                let arr = [this.local.hours_min, this.local.hours_max].filter(
                    function (el) {
                        return el !== null;
                    }
                );
                // Если не rate auto тогда контролим рейт
                if (!this.local.is_auto) arr.push(this.local.rate);

                val = [...val, ...arr];
            } else if (this.estimate.type === 'intrastate') {
                let arr = [this.calculated_moving_distance].filter(function (
                    el
                ) {
                    return el !== null;
                });

                // Если не rate auto тогда контролим рейт
                if (!this.intrastate.is_auto) arr.push(this.intrastate.rate);

                val = [...val, ...arr];
            } else if (this.estimate.type === 'interstate') {
                let arr = [
                    this.interstate.packing,
                    this.interstate.unpacking,
                    this.interstate.shuttle_pickup,
                    this.interstate.shuttle_delivery,
                    this.interstate.delivery_days,
                ].filter(function (el) {
                    return el !== null;
                });

                // Если не rate auto тогда контролим рейт
                if (!this.interstate.is_auto) arr.push(this.interstate.rate);

                val = [...val, ...arr];
            }
            return val.join();
        },
        rangeCrews() {
            let min = this.settings.min_crew
                ? Number(this.settings.min_crew)
                : 1;
            let max = this.settings.max_crew
                ? Number(this.settings.max_crew)
                : 25;
            return lodashRange(min, max + 1, 1);
        },
        rangeTrucks() {
            let min = this.settings.min_trucks
                ? Number(this.settings.min_trucks)
                : 1;
            let max = this.settings.max_trucks
                ? Number(this.settings.max_trucks)
                : 10;
            return lodashRange(min, max + 1, 1);
        },
        settings() {
            return this.settingsEstimate(this.estimate.type);
        },
        optionsAllowed() {
            return this.optionsEstimate(this.estimate.type);
        },
        feeDisabled() {
            if (
                !this.optionsAllowed.fee_type ||
                !this.optionsAllowed.fee_type.length
            ) {
                return true;
            }
            return false;
        },
        ...mapGetters({
            inventories: 'order/inventories',
            estimate: 'order/estimate',
            works: 'order/works',
            settingsEstimate: 'order/settingsEstimate',
            optionsEstimate: 'order/optionsEstimate',
        }),
    },
    watch: {
        noDelayUpdate(new_v, old_v) {
            if (old_v) {
                this.sendUpdate();
            }
        },
        propertiesChanged(new_v, old_v) {
            if (old_v) {
                this.updated = true;

                clearTimeout(window.timer);
                window.timer = setTimeout(() => {
                    this.sendUpdate();
                }, 1000);
            }
        },
    },
    mounted() {
        this.initData();
        $('.numeric-inputmask').each(function () {
            Inputmask({
                alias: 'numeric',
                digits: 2,
                min: 0,
                digitsOptional: true,
                clearMaskOnLostFocus: false,
                placeholder: '0',
                rightAlign: false,
                allowMinus: false,
            }).mask(this);
        });
    },
    methods: {
        async showCalculationWarnings() {
            // тянем из стора и показываем
            const lastWarning = await this.$store.dispatch(
                'order/fetchFirstCalculationWarning'
            );
            App.Forms.simpleNotices([lastWarning]);
        },
        generateInvoice() {
            alert('FIXME generateInvoice');
        },
        initData() {
            this.is_locked = this.estimate.is_locked;
            this.trucks = this.estimate.trucks;
            this.crews = this.estimate.crews;
            this.discount_value = this.estimate.discount_value;
            this.discount_type = this.estimate.discount_type;
            this.fee_type = this.estimate.fee_type;
            if (
                this.optionsAllowed &&
                this.optionsAllowed.fee_type &&
                !this.optionsAllowed.fee_type.includes(this.fee_type)
            ) {
                App.Forms.showAlert(
                    'warning',
                    'Error',
                    "Travel Fee type '" +
                    this.estimate.fee_type +
                    "' not allowed! Switched to " +
                    this.optionsAllowed.fee_type[0]
                );
                this.fee_type = this.optionsAllowed.fee_type[0];
            }
            this.travel_fee = this.estimate.travel_fee;
            this.calculated_moving_distance =
                this.estimate.calculated_moving_distance;
            this.calculated_moving_distance_is_auto =
                this.estimate.calculated_moving_distance_is_auto;

            let type = this.estimate.type;
            if (this.estimate[type]) this[type] = {...this.estimate[type]};
            else {
                if (type === 'local' && this.settings.min_hours) {
                    this[type].hours_min = this.settings.min_hours;
                    this[type].hours_max = this.settings.min_hours;
                    this[type].is_auto = true;
                    this[type].is_auto = true;
                }
            }
        },
        inputChanged(ev) {
            if ($(ev.target).hasClass('onChanged')) {
                clearTimeout(window.timer);
                this.sendUpdate();
            }
        },
        sendUpdate() {
            if (!this.canManage) {
                App.Forms.showAlert(
                    'error',
                    'Forbidden',
                    'You have no permission to manage order'
                );
                this.$emit('update:processing', false);
                return Promise.resolve();
            }
            this.$emit('update:processing', true);

            let data = {
                order_id,
                type: this.estimate.type,
                is_locked: this.is_locked,
                trucks: this.trucks,
                crews: this.crews,
                discount_value: this.discount_value,
                discount_type: this.discount_type,
                fee_type: this.fee_type,
                travel_fee: this.travel_fee,
                calculated_moving_distance: this.calculated_moving_distance,
                calculated_moving_distance_is_auto:
                this.calculated_moving_distance_is_auto,
            };

            let type = this.estimate.type;

            data = {
                [type]: this[type],
                ...data,
            };

            return AxiosHelper({
                url: '/orders/estimates/save',
                data,
            })
                .then(({estimate, calculated, notices, updatedServices}) => {
                    if (estimate) {
                        this.$store.dispatch('order/updateEstimate', estimate);
                        this.$store.dispatch(
                            'order/updateCalculated',
                            calculated
                        );

                        this.calculated_moving_distance =
                            estimate.calculated_moving_distance;
                        this[type] = {...estimate[type]};
                        this.updated = false;

                        if (notices)
                            this.$store.commit(
                                'order/storeCalculationWarnings',
                                notices
                            );
                    }
                    if (updatedServices) {
                        this.$store.dispatch(
                            'order/updateWorks',
                            updatedServices
                        );
                    }
                })
                .then(() => {
                    console.log('trigger');
                    this.$store.dispatch('order/refetchChangelog', 'update');
                })
                .finally(() => this.$emit('update:processing', false));
        },
    },
};
</script>
