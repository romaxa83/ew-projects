<template>
	<div class="row">
		<div class="col-lg-12">
			<div class="panel">
				<div class="panel-hdr">
					<h2>
						{{ title }}
						<span class="fw-300"><i>periodic</i></span>
					</h2>
				</div>
				<div class="panel-container show">
					<div class="panel-content">
						<div class="d-flex flex-wrap">
							<div
								class="flex-fill mb-2 mr-3"
								v-for="(v, k) in wDays"
								:key="k"
							>
								<div class="custom-control custom-switch">
									<input
										type="checkbox"
										class="custom-control-input"
										:id="`wd-sw-${k}`"
										v-model.number="Records"
										:value="k"
									/>
									<label
										class="custom-control-label"
										:for="`wd-sw-${k}`"
										>{{ v.title }}</label
									>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
// 1=Monday, 7=Sunday
import Debounce from 'lodash.debounce';

export default {
	name: 'HolidaysTruckEmployee',
	props: {
		records: {
			type: Array,
			default: [],
			required: true,
		},
		title: {
			type: String,
			default: 'Holidays',
		},
	},
	computed: {
		Records: {
			get() {
				return this.records;
			},
			set(value) {
				this.$emit('update:records', value);
				this.saveChanges();
			},
		},
	},
	methods: {
		saveChanges: Debounce(function () {
			this.$emit('submit');
		}, 1000),
	},
	data() {
		return {
			wDays: {
				0: {
					title: 'Sunday',
				},
				1: {
					title: 'Monday',
				},
				2: {
					title: 'Tuesday',
				},
				3: {
					title: 'Wednesday',
				},
				4: {
					title: 'Thursday',
				},
				5: {
					title: 'Friday',
				},
				6: {
					title: 'Saturday',
				},
			},
		};
	},
};
</script>
