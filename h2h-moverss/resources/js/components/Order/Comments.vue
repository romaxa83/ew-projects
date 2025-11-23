<template>
	<div class="panel">
		<div class="panel-hdr">
			<h2>Comments</h2>
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
								<th width="50px">#</th>
								<th width="200px">Date</th>
								<th width="200px">Username</th>
								<th width="400px">Text</th>
							</tr>
						</thead>
						<tbody>
							<tr v-for="v of records" :key="v.id">
								<th scope="row" v-text="v.id"></th>
								<td>
									{{ v.created_at | formatDate }}
								</td>
								<td>
									{{ v.foreman.name }} {{ v.foreman.l_name }}
								</td>
								<td>
									{{ v.text || '-' }}
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
import { DateService } from '@/services/date';

export default {
	name: 'Comments',
	filters: {
		formatDate(dateStr) {
			return new DateService(dateStr).format({ preset: 'attachment' });
		},
	},
	data() {
		return {
			loading: true,
		};
	},
	mounted() {
		this.$store.dispatch('getSession').then(() => {
			this.loading = false;
		});
	},
	computed: {
		records() {
			return this.$store.state.order.comments || [];
		},
	},
};
</script>

<style lang="scss">
.table {
	th,
	td {
		vertical-align: middle;
	}
}
</style>
