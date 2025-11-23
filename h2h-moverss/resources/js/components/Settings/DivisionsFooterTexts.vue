<template>
	<div class="panel-content">
		<div v-if="loading" class="d-flex justify-content-center">
			<div class="spinner-border" role="status">
				<span class="sr-only">Loading...</span>
			</div>
		</div>
		<div v-else class="row">
			<div class="col-auto">
				<div
					class="nav flex-column nav-pills"
					id="v-pills-tab"
					role="tablist"
					aria-orientation="vertical"
				>
					<a
						class="nav-link"
						v-for="(v, i) in records"
						:key="v.id"
						:class="{ active: !i }"
						:id="`divisions-${v.id}-tab`"
						data-toggle="pill"
						:href="`#divisions_${v.id}`"
						role="tab"
						:aria-controls="`divisions_${v.id}`"
						:data-index="i"
						aria-selected="false"
					>
						<i class="fal fa-home"></i>
						<span
							class="hidden-sm-down ml-1"
							v-text="v.title"
						></span>
					</a>
				</div>
			</div>
			<div class="col">
				<div class="tab-content" id="v-pills-tabContent">
					<divisions-item
						v-for="(v, i) in records"
						:key="v.id"
						:i="i"
						:record="v"
						:updating="updating"
						:is_changed.sync="is_changed"
						@submit="submit"
					></divisions-item>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
import { AxiosHelper } from '@/helpers/axiosHelper';
import DivisionsItem from '@components/Settings/DivisionsFooterTexts/Item';

export default {
	name: 'SettingsDivisionsFooterTexts',
	components: { DivisionsItem },
	data() {
		return {
			loading: true,
			updating: false,
			is_changed: false,
			records: [],
		};
	},
	mounted() {
		this.init();

		let vm = this;
		$(document).on(
			'show.bs.tab',
			'#v-pills-tab a[data-toggle="pill"]',
			function (e) {
				if (vm.is_changed) {
					e.preventDefault();

					Swal.fire({
						title: 'You has unsaved changes. Do you want to save them?',
						icon: 'warning',
						showCancelButton: true,
						reverseButtons: true,
						confirmButtonColor: '#3085d6',
						cancelButtonColor: '#d33',
						cancelButtonText: 'Discard changes',
						confirmButtonText: 'Save changes',
					}).then((result) => {
						if (result.value === true) {
							// save changes
							vm.submit($(e.relatedTarget).data('index')).then(
								() =>
									$('#' + $(e.target).attr('id')).tab('show')
							);
						} else {
							// Skip changes
							vm.is_changed = false;
							$('#' + $(e.target).attr('id')).tab('show');
						}
					});
				}
			}
		);
	},
	methods: {
		init() {
			AxiosHelper({
				url: window.location.href,
				method: 'get',
			})
				.then(({ records }) => {
					this.records = records;
				})
				.finally(() => (this.loading = false));
		},
		submit(index) {
			let record = this.records[index];

			this.updating = true;
			return AxiosHelper({
				url: window.location.href,
				data: {
					record,
				},
			})
				.then(() => App.Forms.showAlert('success', 'Updated'))
				.finally(() => {
					this.updating = false;
					this.is_changed = false;
				});
		},
	},
};
</script>
