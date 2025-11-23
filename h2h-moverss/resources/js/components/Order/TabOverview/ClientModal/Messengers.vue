<template>
	<div>
		<div
			v-for="(v, i) in records"
			:key="`messengers-${i}`"
			class="form-group"
		>
			<label v-if="inLine" class="form-label" :for="`messengers-${i}`">
				Messenger {{ i + 1 }}:
			</label>
			<div class="input-group">
				<div class="input-group-prepend">
					<div class="input-group-text text-success">
						<i :class="'text-muted fab ' + v.type.icon"></i>
					</div>
				</div>
				<input
					type="text"
					class="form-control"
					aria-label="Phone"
					:id="`messengers-${i}`"
					v-model="v.value"
				/>
				<div class="input-group-append">
					<button
						class="btn btn-outline-default dropdown-toggle"
						type="button"
						data-toggle="dropdown"
						aria-haspopup="true"
						aria-expanded="false"
					>
						{{ types[v.type_id].title }}
					</button>
					<div class="dropdown-menu dropdown-menu-right">
						<a
							v-for="(type, ind) in types"
							:key="`messengers-${i}-${ind}`"
							class="dropdown-item"
							href="#"
							@click.prevent="setType(v, ind)"
							:class="{ active: v.type_id == ind }"
						>
							<i
								:class="
									'text-muted mr-2 fs-md fab ' + type.icon
								"
							></i>
							Type: {{ type.title }}
						</a>
						<div role="separator" class="dropdown-divider"></div>
						<a
							class="dropdown-item"
							href="#"
							@click.prevent="deleteRecord(records, i)"
							>Delete</a
						>
					</div>
				</div>
			</div>
		</div>

		<button
			v-if="inLine"
			@click="addRecord('phone', records)"
			type="button"
			class="btn btn-xs btn-default waves-effect waves-themed mb-2"
		>
			<span class="fal fa-check mr-1"></span>
			Add new messenger
		</button>
	</div>
</template>

<script>
export default {
	name: 'ModalMessengers',
	props: {
		inLine: {
			type: Boolean,
			default: true,
		},
		records: {
			type: Array,
			required: true,
		},
		types: {
			type: Object,
			required: true,
		},
	},
	methods: {
		// Проксируем запросы на родительский компонент
		addRecord(type, obj) {
			this.$emit('addRecord', type, obj);
		},
		deleteRecord(obj, index) {
			this.$emit('deleteRecord', obj, index);
		},
		setType(record, id) {
			this.$emit('setType', record, id);
		},
	},
};
</script>
