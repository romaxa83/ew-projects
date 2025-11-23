<template>
	<div>
		<attachments
			ref="attachments"
			v-if="is_loaded"
			type="order"
			:id="order_id"
			@add-record="updateChangelog"
			@remove-record="updateChangelog"
		>
			<template v-if="is_loaded && canViewChangelog" #extra-list>
				<Changelog
					:changelog="changelog"
					:can-view-employee-card="canViewEmployeeCard"
					@refetch-changelog="refetchChangelog"
				/>
			</template>
		</attachments>
	</div>
</template>

<script>
import Changelog from '@/components/Changelog/Changelog.vue';

const Attachments = () => import('@components/App/Attachments');

export default {
	name: 'OrderFiles',
	components: {
		Changelog,
		Attachments,
	},
	data() {
		return {
			is_loaded: false,
			order_id: parseInt(document.getElementById('order_id').textContent),
		};
	},
	mounted() {
		let vm = this;
		// Костыль, смотрим что нам надо подгрузить данные по табу
		$('#order-tabs').on('shown.bs.tab', function () {
			let tab = $('.active', this).attr('href').replace('#tab-', '');
			if (!vm.is_loaded && tab === 'files') {
				vm.is_loaded = true;
			}
		});
	},
	computed: {
		changelog() {
			return {
				loading: this.loading || false,
				...this.$store.state.order.changelog,
			};
		},
		canViewChangelog() {
			return this.$store.state.order.permissions.canViewChangelog;
		},
		canViewEmployeeCard() {
			return this.$store.state.order.permissions.canViewEmployeeCard;
		},
	},
	methods: {
		refetchChangelog(params) {
			this.$store.dispatch('order/refetchChangelog', params);
		},
		updateChangelog() {
			this.$store.dispatch('order/refetchChangelog', 'update');
		},
	},
};
</script>
