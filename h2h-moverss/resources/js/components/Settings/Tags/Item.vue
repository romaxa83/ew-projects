<template>
	<div class="input-group mb-3">
		<div class="input-group-prepend">
			<button
				type="button"
				class="btn btn-xs btn-secondary waves-effect waves-themed cursor-default w-80 text-center order"
			>
				<div class="d-flex align-items-center flex-fill">
					<div
						v-show="i"
						class="flex-fill move-icon fal fa-arrow-up cursor-pointer"
						@click="arrowUp(i)"
					></div>
					<div
						v-show="i !== total"
						class="move-icon flex-fill fal fa-arrow-down cursor-pointer"
						:class="{ 'ml-2': i !== total && i }"
						@click="arrowDown(i)"
					></div>
				</div>
			</button>

			<div class="input-group-text text-success">
				<label :for="'color_' + i" class="mb-0">
					<span
						style="height: 20px; width: 20px"
						:style="{ background: v.color }"
						class="rounded-circle d-block"
					></span>
				</label>
				<input
					:id="'color_' + i"
					type="color"
					class="d-none"
					v-model="v.color"
				/>
			</div>
		</div>

		<input
			type="text"
			class="form-control"
			placeholder="Add new tag"
			@keyup="addEmpty"
			v-model="v.title"
		/>
		<div class="input-group-append">
			<button
				class="btn btn-outline-default dropdown-toggle w-130"
				type="button"
				data-toggle="dropdown"
				aria-haspopup="true"
				aria-expanded="false"
			>
				<i
					:class="'fs-xl fal mr-4 fa-' + v.icon"
					:style="{ color: v.color }"
				></i>
				Icon
			</button>
			<div class="dropdown-menu dropdown-menu-right p-3">
				<button
					v-for="(icon, i_key) in icons"
					class="btn btn-default btn-xl btn-icon rounded-circle waves-effect waves-themed ml-1 mb-1"
					@click="v.icon = icon"
					:key="i_key"
				>
					<i :class="'fs-xl fal fa-' + icon"></i>
				</button>
				<input
					v-model="v.icon"
					placeholder="Custom class"
					class="d-block"
				/>
				<a
					href="https://www.gotbootstrap.com/themes/smartadmin/4.5.1/icons_fontawesome_light.html"
					class="d-block"
					target="_blank"
				>
					<small>You can choose more</small>
				</a>
			</div>
			<button
				@click="removeItem(i)"
				type="button"
				class="btn btn-xs btn-danger waves-effect waves-themed"
			>
				<span class="fal fa-times mr-1"></span> Delete
			</button>
		</div>

		<button
			v-if="v.id"
			@click="openUsedRecordsWithTag(v.id)"
			type="button"
			class="btn btn-outline-default waves-effect waves-themed w-130 ml-4"
		>
			<b>Used:</b>
			{{ section === 'clients' ? v.clients_count : v.orders_count }}
		</button>
	</div>
</template>

<script>
export default {
	name: 'TagsItem',
	props: {
		i: {
			type: Number,
			required: true,
		},
		section: {
			type: String,
			required: true,
		},
		total: {
			type: Number,
			required: true,
		},
		v: {
			type: Object,
			required: true,
		},
	},
	data() {
		return {
			icons: [
				'eye',
				'bullseye',
				'at',
				'asterisk',
				'bell',
				'calendar-alt',
				'chevron-circle-down',
				'clock',
				'coffee',
				'comment-alt',
				'dice-d6',
				'envelope',
				'grin-beam',
				'heart',
				'home-alt',
				'hourglass',
				'lightbulb',
				'location-arrow',
				'lock-alt',
				'map-marker',
				'phone',
				'phone-rotary',
				'phone-slash',
				'smile',
				'star',
				'truck',
				'user',
				'frown',
				'money-bill',
				'calculator-alt',
				'comment-dollar',
				'file-check',
			],
		};
	},
	methods: {
		addEmpty() {
			this.$emit('addEmpty');
		},
		arrowDown(index) {
			this.$emit('arrowDown', index);
		},
		arrowUp(index) {
			this.$emit('arrowUp', index);
		},
		openUsedRecordsWithTag(tag_id) {
			if (this.section === 'clients')
				window.open(
					`/clients?filter-client-tags[]=${tag_id}`,
					'_blank'
				);
			else if (this.section === 'orders')
				window.open(`/orders?filter-order-tags[]=${tag_id}`, '_blank');
		},
		removeItem(index) {
			this.$emit('removeItem', index);
		},
	},
};
</script>

<style scoped>
.w-80 {
	width: 80px;
}

.w-130 {
	width: 130px;
}
</style>
