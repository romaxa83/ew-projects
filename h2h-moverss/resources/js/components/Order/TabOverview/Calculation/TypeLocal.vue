<template>
    <div>
        <div class="row mb-2">
            <div class="col">
                <div class="form-group mb-0">
                    <label class="form-label">Duration, hrs (min - max)</label>
                    <div class="input-group bg-white shadow-inset-2">
                        <div class="input-group-prepend">
                            <label for="hoursMin" class="input-group-text">
                                <i class="fas fa-hourglass-half"></i>
                            </label>
                        </div>
                        <input
                            v-if="canManage"
                            v-model="hoursMin"
                            @change="inputChanged"
                            id="hoursMin"
                            type="text"
                            class="onChanged form-control"
                            placeholder="hr Min"
                            :disabled="!!is_locked"
                        />
                        <div v-else class="form-control">{{ hoursMin }}</div>
                        <input
                            v-if="canManage"
                            v-model="hoursMax"
                            @change="inputChanged"
                            id="hoursMax"
                            type="text"
                            class="onChanged form-control text-right"
                            placeholder="hr Max"
                            :disabled="!!is_locked"
                        />
                        <div v-else class="form-control text-right">
                            {{ hoursMax }}
                        </div>
                        <div class="input-group-append">
                            <label for="hoursMax" class="input-group-text">
                                <i class="fas fa-hourglass-half"></i>
                            </label>
                        </div>
                    </div>
                    <span class="help-block"
                    >In services:
						{{ WorksCountValues.Duration }} hr(s)</span
                    >
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col">
                <div class="form-group mb-0">
                    <label for="hourlyRate" class="form-label">
                        Rate per hour (RPH)
                    </label>
                    <div
                        class="input-group"
                        :style="[
							is_locked ? { 'background-color': '#f3f3f3' } : '',
						]"
                    >
                        <div class="input-group-prepend">
                            <label for="hourlyRate" class="input-group-text">
                                <i class="fas fa-dollar-sign"></i>
                            </label>
                        </div>
                        <input
                            v-if="canManage"
                            v-model="hourlyRate"
                            id="hourlyRate"
                            type="text"
                            @change="inputChanged"
                            :class="{ 'bg-transparent': !hourlyRateIsAuto }"
                            class="onChanged form-control border-left-0"
                            placeholder="Rate"
                            :disabled="!!hourlyRateIsAuto || !!is_locked"
                        />
                        <div v-else class="form-control">
                            {{ hourlyRate }}
                        </div>
                        <div v-if="canManage" class="input-group-append">
                            <div class="input-group-text">
                                <div
                                    class="custom-control d-flex custom-switch"
                                >
                                    <input
                                        v-model="hourlyRateIsAuto"
                                        id="local_auto"
                                        type="checkbox"
                                        class="custom-control-input"
                                        :disabled="!!is_locked"
                                    />
                                    <label
                                        class="custom-control-label fw-500"
                                        for="local_auto"
                                    >
                                        Auto
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <span v-if="canManage" class="help-block"
                    >Auto: {{ rate_auto | currencyFilter }}/hr</span
                    >
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import currencyFilter from '@/filters/currency.filter';

export default {
    name: 'TypeLocal',
    filters: {
        currencyFilter,
    },
    props: {
        WorksCountValues: {
            required: true,
        },
        hours_max: {
            required: true,
        },
        hours_min: {
            required: true,
        },
        is_auto: {
            required: true,
        },
        is_locked: {
            type: [Number, Boolean],
        },
        rate: {
            required: true,
        },
        canManage: {
            type: Boolean,
            required: true,
        },
    },
    computed: {
        hourlyRate: {
            get() {
                return this.rate;
            },
            set(value) {
                this.$emit('update:rate', value);
            },
        },
        hourlyRateIsAuto: {
            get() {
                return this.is_auto;
            },
            set(value) {
                this.$emit('update:is_auto', value ? 1 : 0);
            },
        },
        hoursMax: {
            get() {
                return this.hours_max;
            },
            set(value) {
                this.$emit('update:hours_max', value);
            },
        },
        hoursMin: {
            get() {
                return this.hours_min;
            },
            set(value) {
                this.$emit('update:hours_min', value);
            },
        },
        rate_auto() {
            return this.$store.state.session.order.estimate &&
            this.$store.state.session.order.estimate.local
                ? this.$store.state.session.order.estimate.local.rate_auto
                : 0;
        },
    },
    mounted() {
        // input-group borders on focus
        initApp.appForms('.input-group', 'has-length', 'has-disabled');

        // Тригерим обновы если нет рейта
        if (!this.rate_auto) {
            this.$nextTick(() => (this.hourlyRateIsAuto = true));
        }
    },
    methods: {
        inputChanged(e) {
            this.$emit('inputChanged', e);
        },
    },
};
</script>
