<template>
	<div>
		<div v-for="(v, i) in records" :key="`emails-${i}`" class="form-group">
			<label v-if="inLine" class="form-label" :for="`emails-${i}`">
				Email {{ i + 1 }}:
			</label>
			<div class="input-group">
				<div class="input-group-prepend">
					<div class="input-group-text text-success">
						<i class="fa fa-at"></i>
					</div>
				</div>
				<input
					type="text"
					class="form-control"
					aria-label="Email"
					:id="`emails-${i}`"
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
						{{ v.is_primary ? ' (Primary)' : '' }}
					</button>
					<div class="dropdown-menu dropdown-menu-right">
						<a
							@click.prevent="setPrimary(records, i)"
							class="dropdown-item"
							href="#"
							>Set as primary</a
						>
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
			@click="addRecord('email', records)"
			type="button"
			class="btn btn-xs btn-default waves-effect waves-themed mb-2"
		>
			<span class="fal fa-check mr-1"></span>
			Add new email
		</button>
	</div>
</template>

<script>
import records from '@components/App/Attachments/Records.vue';

export default {
	name: 'ModalEmails',
	props: {
		inLine: {
			type: Boolean,
			default: true,
		},
		records: {
			type: Array,
			required: true,
		},
	},
	data() {
		return {
			prevCount: null,
		};
	},
	updated() {
		// guaranted dom change
		if (this.prevCount !== this.records.length) {
			this.maskInputs();
			this.prevCount = this.records.length;
		}
	},
	mounted() {
		this.prevCount = records.length;
		this.maskInputs();
	},
	methods: {
		maskInputs() {
			$('#client-modal .modal-body [id^="emails-"]').each(function () {
				// Inputmask("email", {regex: "^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+.[A-Za-z]+$", jitMasking: true}).mask(this);
				Inputmask('email').mask(this);
			});
		},
		// Проксируем запросы на родительский компонент
		addRecord(type, obj) {
			this.$emit('addRecord', type, obj);
		},
		deleteRecord(obj, index) {
			this.$emit('deleteRecord', obj, index);
		},
		setPrimary(obj, index) {
			this.$emit('setPrimary', obj, index);
		},
	},
};
</script>
