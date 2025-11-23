<template>
	<li :data-id="record.id" class="waypoint-item">
		<div
			class="waypoints-timeline-icon"
			:class="{
				'dd-handle': canManage,
			}"
		>
			<i class="fas fa-2x fa-map-marker-alt"></i>
		</div>
		<div class="dd-content">
			<div class="card mb-2 py-2">
				<div class="row mx-2">
					<div class="col-lg-12">
						<b-dropdown
							v-if="canManage"
							:text="ucFirst(waypointType)"
							size="sm"
							variant="outline-primary"
							class="rounded"
						>
							<b-dropdown-item-button
								:active="waypointType == 'pickup'"
								@click="changeType('pickup')"
							>
								Pickup
							</b-dropdown-item-button>
							<b-dropdown-item-button
								:active="waypointType == 'destination'"
								@click="changeType('destination')"
							>
								Destination
							</b-dropdown-item-button>
						</b-dropdown>
						<div v-else class="fs-xl mt-1">
							<strong>{{ currentWaypoint }}</strong>
						</div>
					</div>
					<div class="col-lg-12 mt-2">
						<a
							v-if="canManage"
							:href="`#id:${record.id}`"
							@click.prevent="clickModal(i)"
							class="fs-md"
						>
							{{ routeName }}
						</a>
						<span class="fs-md" v-else>{{ routeName }}</span>
						<div class="mt-1 fs-sm">
							<span class="mr-2">{{
								types_building[record.building_type_id].title
							}}</span>
							<span v-if="record.ap" class="mr-2"
								><i class="fal fa-hashtag"></i>
								{{ record.ap }}</span
							>
							<span v-if="record.flights_id" class="mr-2"
								><i class="fal fa-layer-group"></i>
								{{
									types_flights[record.flights_id].title
								}}</span
							>
							<span class="mr-2"
								><i class="fal fa-parking"></i>
								{{ record.parking_type.title }}</span
							>
							<span v-if="record.has_elevator" class="mr-2"
								><i class="fal fa-sort-circle-up"></i>
								Elevator</span
							>
						</div>
					</div>

					<hr class="col-lg-11 mt-2 mb-2" v-if="record.notes[0]" />

					<div
						class="col-lg-12"
						v-for="(v, i) in record.notes"
						:key="`notes-${i}`"
					>
						<div class="d-flex"></div>
						<div class="panel-tag mb-2 p-2">
							<div class="d-flex">
								<div class="flex-grow-1 fs-sm">
									{{ v.value }}
								</div>
								<div
									class="fs-nano opacity-70 color-success-700 flex-nowrap"
								>
									<template v-if="v.id">
										{{
											v.created_at
												| formatDate('ll, [at] h:mm a')
										}}
										by {{ v.user_id | managerName }}
									</template>
									<template v-else
										>Not saved yet, by You
									</template>
								</div>
							</div>
						</div>
					</div>

					<div
						v-if="canManage"
						class="position-absolute pos-right pos-top"
					>
						<button
							data-toggle="dropdown"
							aria-expanded="false"
							class="btn mt-1 mx-1 waves-effect waves-themed"
						>
							<i class="fal fa-2x fa-ellipsis-v"></i>
						</button>
						<div
							class="dropdown-menu dropdown-menu-animated dropdown-menu-right"
						>
							<button
								@click="clickModal(i)"
								class="dropdown-item waypoint-modal"
							>
								Edit
							</button>
							<button
								@click="removeRecord(record.id)"
								class="dropdown-item btn-danger"
							>
								Remove
							</button>
						</div>
					</div>
				</div>
			</div>
		</div>
	</li>
</template>

<script>
import formatDate from '@/filters/formatDate.filter';
import managerName from '@/filters/managerName.filter';
import { BDropdown, BDropdownItemButton } from 'bootstrap-vue';

export default {
	name: 'WaypointItem',
	filters: {
		formatDate,
		managerName,
	},
	props: {
		i: {
			type: Number,
			required: true,
		},
		record: {
			type: Object,
			required: false,
		},
		types_building: {
			type: Object,
			required: false,
		},
		types_flights: {
			type: Object,
			required: false,
		},
		canManage: {
			type: Boolean,
			required: true,
		},
	},
	computed: {
		routeName() {
			let names = [];
			if (this.record.address) names.push(this.record.address);

			if (
				this.record.city &&
				!this.record.address?.includes(this.record.city)
			)
				names.push(this.record.city);

			let string = names.length ? names.join(', ') + ', ' : '';

			if (this.record.state && !string.includes(this.record.state))
				string += ' ' + this.record.state;
			if (this.record.zip && !string.includes(this.record.zip))
				string += ' ' + this.record.zip;

			return string;
		},
		waypointType: {
			get() {
				return this.record.type;
			},
			set(value) {
				this.changeType(value);
			},
		},
		currentWaypoint() {
			return this.waypointType == 'pickup' ? 'Pickup' : 'Destination';
		},
	},
	methods: {
		ucFirst(string) {
			return string[0].toUpperCase() + string.slice(1).toLowerCase();
		},
		changeType(type) {
			this.$emit('saveRecord', {
				...this.record,
				type,
			});
		},
		clickModal(index) {
			this.$emit('clickModal', index);
		},
		removeRecord(id) {
			this.$emit('removeRecord', id);
		},
	},
	components: {
		BDropdown,
		BDropdownItemButton,
	},
};
</script>
