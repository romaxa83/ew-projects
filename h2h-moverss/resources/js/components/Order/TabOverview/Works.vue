<template>
	<div class="panel">
		<div class="panel-hdr">
			<h2>
				Services
				<span
					v-show="loading"
					class="spinner-border spinner-border-sm ml-1"
					role="status"
					aria-hidden="true"
				></span>
			</h2>
			<div v-if="canAddServices" class="panel-toolbar">
				<button
					@click="clickModal(-1)"
					class="btn btn-sm btn-secondary mr-1 shadow-0 waves-effect waves-themed"
				>
					<i class="fal fa-plus"></i> Add service
				</button>
			</div>
		</div>
		<div class="panel-container collapse show" v-if="!loading">
			<div class="panel-content">
				<works-item
					v-for="(record, i) in works.records"
					:key="record.id"
					:types="types"
					:record="record"
					:i="i"
					:can-manage-item="canManageItem"
					@clickModal="clickModal"
					@cloneRecord="cloneRecord"
					@removeRecord="removeRecord"
				></works-item>
			</div>
		</div>

		<div
			class="modal fade"
			id="modal-works"
			tabindex="-1"
			role="dialog"
			aria-hidden="true"
		>
			<div
				class="modal-dialog modal-lg modal-dialog-centered"
				role="document"
			>
				<order-works-modal
					v-if="openModal"
					:record.sync="recordCloned"
					:types="types"
					@recalculate="recalculate"
				></order-works-modal>
			</div>
		</div>
	</div>
</template>

<script>
import cloneDeep from 'lodash.clonedeep';
import { mapGetters } from 'vuex';

import WorksItem from './WorksModal/Item'; // const OrderWorksModal = () => import(/* webpackChunkName: "OrderWorksModal" */ './WorksModal/Modal');
import OrderWorksModal from './WorksModal/Modal';

let order_id = document.getElementById('order_id').textContent;

export default {
	name: 'OrderWorks',
	components: {
		OrderWorksModal,
		WorksItem,
	},
	props: {
		canAddServices: {
			type: Boolean,
			required: true,
		},
		canManageItem: {
			type: Boolean,
			required: true,
		},
	},
	data() {
		return {
			loading: true,
			errors: null,
			openModal: false,
			recordCloned: null,
			types: null,
		};
	},
	computed: {
		...mapGetters({
			works: 'order/works',
			estimate: 'order/estimate',
		}),
	},
	mounted() {
		this.loading = true;
		this.errors = null;

		this.$store.dispatch('getSession').then(({ types }) => {
			this.types = types.works;

			this.loading = false;
		});
	},
	methods: {
		clickModal(index) {
			let diffMessage = '';
			if (index >= 0) {
				let obj = cloneDeep(this.works.records[index]);
				obj.work_types_checked = obj.work_types.map(function (v) {
					return v.pivot.work_type_id;
				});

				obj.init_duration = obj.duration;
				// obj.init_trucks = obj.trucks;
				// obj.init_employees = obj.employees;

				this.recordCloned = obj;
				// check with estimate
				if (
					Number(this.recordCloned.trucks) !=
					Number(this.estimate.trucks)
				) {
					diffMessage += `<div>Trucks qty at services = ${this.recordCloned.trucks} is different from estimate trucks = ${this.estimate.trucks}</div>`;
				}
				if (
					Number(this.recordCloned.employees) !=
					Number(this.estimate.crews)
				) {
					diffMessage += `<div>Crew qty at services = ${this.recordCloned.employees} is different from estimate crew = ${this.estimate.crews}</div>`;
				}
				if (
					this.estimate.type == 'local' &&
					Number(this.estimate.local.hours_max) !=
						Number(this.recordCloned.duration)
				) {
					diffMessage += `<div>Duration at services = ${this.recordCloned.employees} is different from estimate max duration = ${this.estimate.local.hours_max}</div>`;
				}
			} else {
				const duration =
					this.estimate.type == 'local' && this.estimate.local
						? this.estimate.local.hours_max
						: 0;
				this.recordCloned = {
					notes: null,
					order_id,
					start_date: null,
					trucks: this.estimate.trucks,
					employees: this.estimate.crews,
					start_time: null,
					start_time_to: null,
					work_types: [],
					work_types_checked: [],
					dispatch_trucks_count: 0,
					dispatch_employees_count: 0,
					duration,
					init_duration: duration,
					// init_trucks: this.estimate.trucks,
					// init_employees: this.estimate.crews,
					in_dispatch: null,
				};
			}

			$('#modal-works').off('show.bs.modal');
			if (diffMessage != '') {
				console.log(diffMessage);
				// const vue = this;
				// diffMessage += '<div class="text-primary"><b>Do you want replace it with estimate values?</b></div>'
				// $('#modal-works').on('show.bs.modal', function (e) {
				//     Swal.fire({
				//         icon: 'question',
				//         title: 'Differences found between services and estimate',
				//         html: diffMessage,
				//         confirmButtonText: 'Replace',
				//         confirmButtonColor: '#4679cc',
				//         showCancelButton: true,
				//     }).then((result) => {
				//         if (result.value) {
				//             vue.recordCloned.trucks = vue.estimate.trucks;
				//             vue.recordCloned.employees = vue.estimate.crews;
				//             if (vue.estimate.type == 'local' && vue.estimate.local) {
				//                 // console.log(vue.estimate.local);
				//                 vue.recordCloned.duration = Number(vue.estimate.local.hours_max);
				//             }
				//         }
				//     })
				// });
			}

			if (!this.openModal) {
				this.openModal = true;
			} else {
				$('#modal-works').modal('show');
			}
		},
		cloneRecord(index) {
			let obj = cloneDeep(this.works.records[index]);
			obj.work_types_checked = obj.work_types.map(function (v) {
				return v.pivot.work_type_id;
			});
			obj.id = null;
			this.recordCloned = obj;

			if (!this.openModal) this.openModal = true;
			else $('#modal-works').modal('show');
		},
		recalculate() {
			this.$emit('recalculate');
		},
		removeRecord(id) {
			axios
				.post('/orders/works/remove', {
					order_id,
					id,
				})
				.then((resp) => {
					if (resp.data.success === true) {
						this.$store.dispatch(
							'order/updateWorks',
							resp.data.records
						);

						this.recalculate();
					} else {
						App.Forms.simpleErrors(resp.data);
					}
				})
				.catch((error) => {
					App.Forms.simpleErrors(error.response.data);
				});
		},
	},
};
</script>
