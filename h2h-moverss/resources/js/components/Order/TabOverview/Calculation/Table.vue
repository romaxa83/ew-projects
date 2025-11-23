<template>
	<div class="card border mb-g">
		<!--                                <div class="card-header pr-3 d-flex align-items-center flex-wrap">-->
		<!--                                    <div class="card-title">Estimate</div>-->
		<!--                                </div>-->
		<div class="card-body">
			<table class="table m-0">
				<tbody>
					<tr
						v-for="(item, index) in calculated"
						:key="item.id"
						:class="trClass(item)"
					>
						<td class="text-left fs-xl" :class="tdClass(+index)">
							<a
								v-if="item.title == 'materials'"
								@click.prevent="openExtra()"
								href="#"
							>
								<u>{{ item.description }}</u>
							</a>
							<template v-else>{{ item.description }}</template>
						</td>
						<td class="text-right fs-md" :class="tdClass(+index)">
							{{ item.value }}
						</td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>
</template>

<script>
import { mapGetters } from 'vuex';

export default {
	name: 'CalculationTable',
	data() {
		return {
			openExtraModal: false,
			open_id: null,
		};
	},
	computed: {
		...mapGetters({
			calculated: 'order/calculated',
			estimate: 'order/estimate',
			totalMaterials: 'order/totalMaterials',
		}),
	},
	methods: {
		openExtra() {
			this.$root.$refs.inventory.proxyExtrasModal();
		},
		tdClass(index) {
			return index === 0 ? 'border-top-0' : '';
		},
		trClass(item) {
			return {
				'bg-primary-500': item.title === 'total',
				'bg-primary-50 text-light': item.title === 'subtotal',
				'fw-700': item.title === 'total',
				'd-none': item.title === 'overpaid',
			};
		},
	},
};
</script>
