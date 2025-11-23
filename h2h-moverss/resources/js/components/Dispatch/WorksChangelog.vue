<template>
	<Changelog
		:changelog="changelog"
		:can-view-employee-card="canViewEmployeeCard"
		@refetch-changelog="refetchChangelog"
	/>
</template>

<script>
import Changelog from '@/components/Changelog/Changelog';
import { mapGetters } from 'vuex';

export default {
	name: 'DispatchWorksChangelog',
	components: { Changelog },
	data() {
		return {
			loading: true,
			// TODO Change this to state information, when it becomes available
			canViewEmployeeCard: true,
		};
	},
	computed: {
		...mapGetters({
			works: 'dispatch/getWorks',
		}),
		changelog() {
			return {
				loading: this.loading,
				...this.$store.state.dispatch.changelog,
			};
		},
	},
	mounted() {
		this.$store.dispatch('dispatch/isWorksLoaded').then(() => {
			this.loading = false;
		});
	},
	methods: {
		refetchChangelog(params) {
			this.$store.dispatch('dispatch/refetchChangelog', params);
		},
	},
};
</script>
