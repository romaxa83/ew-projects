<template>
	<div class="panel">
		<div class="panel-hdr">
			<h2>Attachments</h2>
		</div>
		<div class="panel-container collapse show">
			<div class="panel-content pt-2">
				<div
					v-if="loading"
					class="frame-wrap position-absolute w-100 h-100 opacity-50"
				>
					<div
						class="w-100 d-flex justify-content-center align-items-center"
					>
						<div
							class="spinner-border text-info position-absolute"
							style="top: 50%"
							role="status"
						>
							<span class="sr-only">Loading...</span>
						</div>
					</div>
				</div>
				<div v-else>
					<table class="table table-striped m-0">
						<thead class="thead-themed">
							<tr>
								<th>#</th>
								<th>Date</th>
								<th>Username</th>
								<th>File</th>
								<th>Description</th>
								<th></th>
							</tr>
						</thead>
						<tbody>
							<tr v-for="v in records" :key="v.id">
								<th scope="row" v-text="v.id"></th>
								<td>
									{{ v.created_at | formatDate }}
								</td>
								<td>{{ v.user_id | managerName }}</td>
								<td>
									<a
										title="DL File"
										:href="'/attachments/dl/' + v.hash"
										v-text="v.miscs.file.name"
										target="_blank"
									></a>
									{{ v.miscs.file.size }}
								</td>
								<td>
									{{ v.description ? v.description : '-' }}
								</td>
								<td>
									<button
										type="button"
										@click="remove(v.id, v.hash)"
										class="btn btn-xs btn-danger waves-effect waves-themed editor-delete"
									>
										<span class="fal fa-times mr-1"></span>
										Delete
									</button>
								</td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
import managerName from '@/filters/managerName.filter';
import { DateService } from '@/services/date';

export default {
	name: 'Records',
	filters: {
		managerName,
		formatDate(dateStr) {
			return new DateService(dateStr).format({ preset: 'attachment' });
		},
	},
	props: {
		loading: {
			type: Boolean,
			required: true,
		},
		records: {
			type: Array,
		},
	},
	methods: {
		remove(id, hash) {
			this.$emit('remove', { id, hash });
		},
	},
};
</script>
