<template>
	<div class="panel">
		<div class="panel-hdr">
			<h2>Notes{{ has_tasks ? ' and Tasks' : '' }}</h2>
			<div class="panel-toolbar">
				<select
					v-if="sort"
					v-model="orderBy"
					class="custom-select custom-select-sm rounded-0 border-top-0 border-left-0 border-right-0"
				>
					<option value="asc">By date, Asc</option>
					<option value="desc">By date, Desc</option>
				</select>
				<button
					v-else-if="in_overview"
					@click="toNotesTab()"
					class="btn btn-sm btn-secondary mr-1 shadow-0 waves-effect waves-themed"
				>
					<i class="fal fa-plus"></i> Add note
				</button>
			</div>
		</div>
		<div class="panel-container">
			<div class="panel-content mt-2">
				<div class="card mb-2" v-for="v in records" :key="v.id">
					<template
						v-if="!v.hasOwnProperty('type') || v.type === 'note'"
					>
						<div
							:class="{
								'bg-white': !v.is_pinned,
								'bg-primary-300': v.is_pinned,
							}"
							class="card-header py-2 pr-2 d-flex"
						>
							<div v-if="v.is_pinned" class="mr-2">
								<i class="fas fa-map-pin"></i>
							</div>
							<div class="fs-sm fw-500">
								{{ v.user_id | managerName }}
							</div>
							<div class="d-flex position-relative ml-auto pr-4">
								<span class="fs-sm">{{
									v.created_at | formatDate
								}}</span>
							</div>
							<div
								class="mr-2 my-0 position-absolute pos-right"
								style="top: 3px"
							>
								<button
									data-toggle="dropdown"
									aria-expanded="false"
									:class="{ 'bg-primary-300': v.is_pinned }"
									class="btn py-0 px-2 waves-effect waves-themed"
								>
									<i class="fal fa-2x fa-ellipsis-v"></i>
								</button>
								<div
									class="dropdown-menu dropdown-menu-animated dropdown-menu-right"
								>
									<button
										@click="removeRecord(v.id)"
										class="dropdown-item btn-danger"
									>
										Remove
									</button>
								</div>
							</div>
						</div>
						<div
							class="card-body notes-content"
							v-html="v.text"
						></div>
					</template>
					<template
						v-else-if="
							v.hasOwnProperty('type') || v.type === 'task'
						"
					>
						<div
							class="card-header py-2 pr-2 d-flex bg-white cursor-pointer"
							@click="viewTask(v.id)"
						>
							<div class="mr-2">Task to</div>
							<div class="fs-sm fw-500">
								{{ v.user_id | managerName }}
							</div>
							<div class="d-flex position-relative ml-auto pr-4">
								<div class="mr-2">
									<strong>Status:</strong>
									<span
										:class="'badge badge-' + v.status.class"
										v-text="v.status.title"
									></span>
								</div>
								<div v-if="v.due_date">
									<strong>Deadline:</strong>
									{{ v.due_date | formatDate }}
								</div>
							</div>
						</div>
						<div
							class="card-body cursor-pointer"
							@click="viewTask(v.id)"
						>
							{{ v.title }}
						</div>
					</template>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
let order_id = document.getElementById('order_id').textContent;

import formatDate from '@/filters/formatDate.filter';
import managerName from '@/filters/managerName.filter';

export default {
	name: 'OrderPanelNotes',
	filters: {
		formatDate,
		managerName,
	},
	props: {
		has_tasks: {
			type: Number,
		},
		in_overview: {
			type: Boolean,
			required: false,
		},
		records: {
			type: [Array, Object],
			required: true,
		},
		sort: {
			type: String,
			required: false,
		},
	},
	computed: {
		orderBy: {
			get() {
				return this.sort;
			},
			set($value) {
				this.$emit('update:sort', $value);
			},
		},
	},
	methods: {
		removeRecord(id) {
			axios
				.post('/orders/notes/remove', {
					order_id,
					id,
				})
				.then((resp) => {
					if (resp.data.success === true) {
						this.$store.dispatch(
							'order/updateNotes',
							resp.data.records
						);
					} else {
						App.Forms.simpleErrors(resp.data);
					}
				})
				.catch((error) => {
					App.Forms.simpleErrors(error.response.data);
				});
		},
		toNotesTab() {
			$('[href="#tab-notes"]').trigger('click');
			window.scrollTo(0, 180);
		},
		viewTask(id) {
			this.$root.$refs.tasks.openViewModal({ id });
		},
	},
};
</script>
