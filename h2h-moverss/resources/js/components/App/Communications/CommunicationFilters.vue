<template>
	<div class="mx-3">
		<div class="form-group mb-2">
			<multiselect
				v-model="periodSelector"
				:options="periodList"
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
				<option value="myclients">My Leads</option>
				<option value="unassigned">Unassigned</option>
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

		<div class="form-group mb-2">
			<multiselect-checkboxes
				:options="channelsList"
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
					class="btn btn-sm btn-primary"
					@click="applyFilters"
					:disabled="isApplyDisabled"
				>
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
	name: 'CommunicationFilters',
	data() {
		return {
			periodList: [
				{
					label: 'Today',
					value: 'today',
				},
				{
					label: 'Yesterday',
					value: 'yesterday',
				},
				{
					label: 'Last 7 days',
					value: 'last7days',
				},
				{
					label: 'Last 30 days',
					value: 'last30days',
				},
				{
					label: 'Any',
					value: 'any',
				},
			],
			channelsList: [
				{
					label: 'Emails',
					value: 'gmail',
				},
				{
					label: 'Twilio SMS',
					value: 'twiliosms',
				},
				{
					label: 'ZadarmaPBX',
					value: 'zadarma',
				},
				{
					label: 'RingostatPBX',
					value: 'ringostat',
				},
			],
			selected: [],
		};
	},
	computed: {
		isApplyDisabled() {
			return (
				JSON.stringify(this.$store.state.communicationsFlow.filters) ==
				JSON.stringify(
					this.$store.state.communicationsFlow.filtersBackup
				)
			);
			// return false;
		},
		communicationsFilter: {
			get() {
				return this.$store.state.communicationsFlow.filters
					.communications;
			},
			set(value) {
				this.$store.commit(
					'communicationsFlow/setFiltersCommunications',
					value
				);
			},
		},
		communicationsStarred: {
			get() {
				return this.$store.state.communicationsFlow.filters.starred;
			},
			set(value) {
				this.$store.commit(
					'communicationsFlow/setFiltersStarred',
					value
				);
			},
		},
		contactsFilter: {
			get() {
				return this.$store.state.communicationsFlow.filters.contacts;
			},
			set(value) {
				this.$store.commit(
					'communicationsFlow/setFiltersContacts',
					value
				);
			},
		},
		periodSelector: {
			get() {
				return this.$store.state.communicationsFlow.filters.period;
			},
			set(payload) {
				this.$store.commit('communicationsFlow/updateFilters', {
					period: payload,
				});
			},
		},
		channelSelector: {
			get() {
				return this.$store.state.communicationsFlow.filters.channels;
			},
			set(payload) {
				this.$store.commit('communicationsFlow/updateFilters', {
					channels: payload,
				});
			},
		},
		responsibleList() {
			return this.$store.state.communicationsFlow.responsibleList;
		},
		responsibleSelector: {
			get() {
				return this.$store.state.communicationsFlow.filters.responsible;
			},
			set(payload) {
				this.$store.commit('communicationsFlow/updateFilters', {
					responsible: payload,
				});
			},
		},
	},
	methods: {
		applyFilters() {
			this.$store.commit('communicationsFlow/setFiltersBackup', null);
			this.$store.commit('communicationsFlow/updateFilters', {
				untill: null,
				ignoreList: null,
			});
			this.$store.commit('communicationsFlow/setFilterWindowState', null);
			this.$store.commit('communicationsFlow/clearContactsRecords');
			// this.$store.dispatch('communicationsFlow/fetchContactsRecords').then(() => {
			//
			// });
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
