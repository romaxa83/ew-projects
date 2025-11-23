<template>
	<div class="row h-100">
		<div class="col-lg-8 col-xl-8 order-2 order-lg-1" v-if="notes">
			<order-panel-notes
				:records="orderRecords"
				:sort.sync="sort"
			></order-panel-notes>
		</div>

		<div class="col-lg-4 col-xl-4 order-1">
			<div class="sticky-top">
				<add-note></add-note>
			</div>
			<div class="hidden-lg-down hidden-md h-100"><!-- CSS Hack --></div>
		</div>
	</div>
</template>

<script>
const OrderPanelNotes = () =>
	import(/* webpackChunkName: "OrderPanelNotes" */ './TabNotes/Notes');

let order_id = document.getElementById('order_id').textContent;

import AddNote from './TabNotes/AddNote';
import { mapGetters } from 'vuex';

export default {
	name: 'OrderNotes',
	components: {
		OrderPanelNotes,
		AddNote,
	},
	data() {
		return {
			sort: 'desc',
		};
	},
	computed: {
		orderRecords() {
			return this.notes
				.slice() // Хак, отвязываемся от объекта Vuex
				.sort((a, b) => {
					// Сортировка по дате
					if (this.sort === 'desc')
						return new Date(b.created_at) - new Date(a.created_at);
					else return new Date(a.created_at) - new Date(b.created_at);
				})
				.sort((a, b) => {
					// Поднимаем запиненые
					return b.is_pinned - a.is_pinned;
				});
		},
		...mapGetters({
			notes: 'order/notes',
		}),
	},
};
</script>
