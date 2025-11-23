<template>
	<div class="mx-3">
		<div class="form-group mb-2">
			<div v-if="fetchingFilterParams" class="form-control fs-nano">
				Retrieving periods...
			</div>
			<multiselect
				v-else
				v-model="periodSelector"
				:options="periodOptions"
				:closeOnSelect="true"
				:searchable="false"
				placeholder="Period"
				label="label"
				:showLabels="false"
			>
				<template v-slot:singleLabel="{ option }">
					<span class="fs-nano"> Period: {{ option.label }} </span>
				</template>
				<template v-slot:placeholder>
					<div class="fs-nano ml-2">Period: any</div>
				</template>
				<template v-slot:option="option">
					<div class="fs-nano ml-2">
						{{ option.option.label }}
					</div>
				</template>
			</multiselect>
		</div>
		<div class="form-group mb-2">
			<select
				class="form-control form-control-sm"
				v-model="communicationsFilter"
			>
				<option value="all">All answer statuses</option>
				<option value="unanswered">Unanswered</option>
			</select>
		</div>
		<div class="form-group mb-2">
			<select
				class="form-control form-control-sm"
				v-model="contactsFilter"
			>
				<option value="all">All Contacts</option>
				<option value="myclients">Registered</option>
				<option value="unassigned">Not Registered</option>
			</select>
		</div>
		<div class="form-group mb-2">
			<select
				class="form-control form-control-sm"
				v-model="communicationsStarred"
			>
				<option value="all">All conversations</option>
				<option value="starred">Starred conversations</option>
				<option value="notstarred">Not starred conversations</option>
			</select>
		</div>

		<!--
        Channels are conflicting with the new Tabs above
        Maybe we can use this in the future with individual channels for each tab
    <div class="form-group mb-2">
      <div v-if="fetchingFilterParams" class="form-control fs-nano">
        Retrieving channels...
      </div>
      <multiselect-checkboxes
        v-else
        :options="channelOptions"
        v-model="channelSelector"
        :multiselectProps="{
          placeholder: 'Channels',
          multiple: true,
          closeOnSelect: false,
          searchable: false,
          'track-by': 'value',
          showLabels: false,
        }"
      />
    </div>
    -->
		<div class="form-group mb-2" v-if="responsibleList.length">
			<multiselect-checkboxes
				:options="responsibleList"
				v-model="responsibleSelector"
				:multiselectProps="{
					placeholder: 'Responsible',
					multiple: true,
					closeOnSelect: false,
					searchable: true,
					'track-by': 'value',
					label: 'label',
					showLabels: false,
				}"
			/>
		</div>
		<div class="form-group mb-2 d-flex">
			<div class="ml-auto">
				<button
					class="btn btn-sm btn-outline-info"
					@click="clearFilters"
				>
					Clear
				</button>
			</div>
			<div class="ml-2">
				<button class="btn btn-sm btn-primary" @click="applyFilters">
					Apply
				</button>
			</div>
		</div>
	</div>
</template>

<script>
import MultiselectCheckboxes from '@components/App/Communications/MultiselectCheckboxes.vue';
import vueSelect2 from '@components/VueSelect2.vue';
import Multiselect from 'vue-multiselect';

export default {
	name: 'CommunicationFiltersNew',
	data() {
		return {
			channelsList: [
				{
					label: 'Emails',
					value: 'gmail-messages',
				},
				{
					label: 'Twilio SMS',
					value: 'twilio-sms',
				},
				{
					label: 'ZadarmaPBX',
					value: 'zadarma-calls-event',
				},
				{
					label: 'RingostatPBX',
					value: 'ringostat-event_after_call',
				},
			],
			selected: [],
			fetchingFilterParams: true,
		};
	},
	async mounted() {
		return axios
			.get('/communications/filter-data')
			.then((response) =>
				this.$store.commit(
					'communicationsFlow/updateFilterParams',
					response.data
				)
			)
			.catch((error) => App.Forms.simpleErrors(error?.response?.data))
			.finally(() => (this.fetchingFilterParams = false));
	},
	computed: {
		isApplyDisabled() {
			return (
				JSON.stringify(this.$store.state.communicationsFlow.filters) ==
				JSON.stringify(this.$store.state.communicationsFlow.tempFilters)
			);
			// return false;
		},
		communicationsFilter: {
			get() {
				return (
					this.$store.state.communicationsFlow.tempFilters
						.communications ||
					this.$store.state.communicationsFlow.filters.communications
				);
			},
			set(value) {
				this.$store.commit('communicationsFlow/updateTempFilters', {
					communications: value,
				});
			},
		},
		communicationsStarred: {
			get() {
				return (
					this.$store.state.communicationsFlow.tempFilters.starred ||
					this.$store.state.communicationsFlow.filters.starred
				);
			},
			set(value) {
				this.$store.commit('communicationsFlow/updateTempFilters', {
					starred: value,
				});
			},
		},
		contactsFilter: {
			get() {
				return (
					this.$store.state.communicationsFlow.tempFilters.contacts ||
					this.$store.state.communicationsFlow.filters.contacts
				);
			},
			set(value) {
				this.$store.commit('communicationsFlow/updateTempFilters', {
					contacts: value,
				});
			},
		},
		periodOptions() {
			return this.$store.state.communicationsFlow.filterParams
				.periodOptions;
		},
		periodSelector: {
			get() {
				const value =
					this.$store.state.communicationsFlow.tempFilters.period ||
					this.$store.state.communicationsFlow.filters.period;
				return (
					this.periodOptions.find(
						(option) => option.value === value
					) || null
				);
			},
			set(payload) {
				this.$store.commit('communicationsFlow/updateTempFilters', {
					period: payload.value,
				});
			},
		},
		// channelOptions() {
		// 	return this.$store.state.communicationsFlow.filterParams
		// 		.channelOptions;
		// },
		// channelSelector: {
		// 	get() {
		// 		console.log(
		// 			'channelSelector get:',
		// 			this.$store.state.communicationsFlow.filters.channels
		// 		);
		// 		return this.$store.state.communicationsFlow.filters.channels;
		// 	},
		// 	set(payload) {
		// 		console.log('channelSelector set:', payload);
		// 		this.$store.commit('communicationsFlow/updateFilters', {
		// 			channels: payload,
		// 		});
		// 	},
		// },
		responsibleList() {
			return this.$store.state.communicationsFlow.responsibleList;
		},
		responsibleSelector: {
			get() {
				const values =
					this.$store.state.communicationsFlow.tempFilters
						.responsible ||
					this.$store.state.communicationsFlow.filters.responsible ||
					[];
				return this.responsibleList
					.filter((item) => values.includes(item.value))
					.map((item) => ({
						...item,
						checked: true,
					}));
			},
			set(payload) {
				this.$store.commit('communicationsFlow/updateTempFilters', {
					responsible: payload.map((item) => item.value),
				});
			},
		},
	},
	methods: {
		updateFilters(payload) {
			this.$store.commit('communicationsFlow/setFiltersBackup', null);
			this.$store.commit('communicationsFlow/updateFilters', payload);
			this.$store.commit('communicationsFlow/setFilterWindowState', null);
		},
		clearFilters() {
			this.updateFilters(
				this.$store.state.communicationsFlow.initialFilters
			);
			this.$store.commit('communicationsFlow/clearTempFilters');
			this.$emit('clear-filters', null);
		},
		applyFilters() {
			this.updateFilters(
				Object.keys(
					this.$store.state.communicationsFlow.tempFilters
				).reduce((acc, key) => {
					acc[key] =
						this.$store.state.communicationsFlow.tempFilters[key] ||
						this.$store.state.communicationsFlow.filters[key];
					return acc;
				}, {})
			);
			this.$emit('apply-filters', null);
			this.$store.commit('communicationsFlow/clearTempFilters');
		},
	},

	components: { MultiselectCheckboxes, vueSelect2, Multiselect },
};
</script>

<style>
.multiselect__placeholder {
	margin-left: 0 !important;
}

.checkbox-label {
	display: block;
}

.test {
	position: absolute;
	right: 1vw;
}
</style>
