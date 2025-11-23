<template>
	<div class="row">
		<div class="col-lg-8 col-xl-8 order-lg-1 order-xl-1">
			<records
				:loading="loading"
				:records="records"
				@remove="remove"
			></records>
			<slot name="extra-list"></slot>
		</div>

		<div class="col-lg-4 col-xl-4 order-1">
			<add-new :type="type" :id="id" @loadData="getRecords"></add-new>
			<div class="hidden-lg-down hidden-md h-100"><!-- CSS Hack --></div>
		</div>
	</div>
</template>

<script>
import AddNew from './Attachments/AddNew';
import Records from './Attachments/Records';

export default {
	name: 'Attachments',
	components: {
		AddNew,
		Records,
	},
	props: {
		id: {
			type: Number,
			required: true,
		},
		type: {
			type: String,
			required: true,
		},
	},
	data() {
		return {
			loading: true,
			records: null,
		};
	},
	mounted() {
		this.getRecords(true);
	},
	methods: {
		getRecords(initial = false) {
			axios
				.post('/attachments', {
					type: this.type,
					id: this.id,
				})
				.then((resp) => {
					if (resp.data.success === true) {
						this.records = resp.data.records;
						if (!initial) {
							this.$emit('add-record');
						}
					} else {
						App.Forms.simpleErrors(resp.data);
					}
				})
				.catch((error) => {
					console.log('axios error', error);
					App.Forms.simpleErrors(error.response.data);
				})
				.finally(() => (this.loading = false));
		},
		remove({ id, hash }) {
			let index = this.records.findIndex((item) => item.id === id);

			window.Swal.fire({
				title: 'Are you sure?',
				text: 'Remove this item',
				icon: 'warning',
				showCancelButton: true,
				reverseButtons: true,
				confirmButtonColor: '#3085d6',
				cancelButtonColor: '#d33',
				confirmButtonText: 'Yes, do it!',
			}).then((result) => {
				if (result.value === true) {
					axios
						.post('/attachments/remove', { id, hash })
						.then((resp) => {
							if (resp.data.success === true) {
								this.$delete(this.records, index);
								this.$emit('remove-record');
							} else {
								throw {
									response: {
										data: resp.data,
									},
								};
							}
						})
						.catch((error) => {
							App.Forms.simpleErrors(error.response.data);
						});
				}
			});
		},
	},
};
</script>
