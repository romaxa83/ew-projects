<template>
	<div>
		<div v-if="loading" class="d-flex justify-content-center">
			<div class="spinner-border" role="status">
				<span class="sr-only">Loading...</span>
			</div>
		</div>
		<div v-else class="row">
			<div class="col-sm-6">
				<tags-item
					v-for="(v, i) in sorted"
					:key="i"
					:v="v"
					:i="i"
					:total="total"
					:section="section"
					@addEmpty="addEmpty"
					@removeItem="removeItem"
					@arrowUp="arrowUp"
					@arrowDown="arrowDown"
				></tags-item>

				<button
					type="button"
					class="btn btn-primary"
					@click="submit"
					:disabled="!changed"
				>
					<span
						v-if="updating"
						role="status"
						aria-hidden="true"
						class="spinner-border spinner-border-sm"
					></span>
					Save changes
				</button>
			</div>
		</div>
	</div>
</template>

<script>
import { AxiosHelper } from '@/helpers/axiosHelper';
import TagsItem from '@components/Settings/Tags/Item.vue';

export default {
	name: 'SettingsTags',
	components: { TagsItem },
	props: {
		section: {
			type: String,
			required: true,
		},
	},
	data() {
		return {
			loading: true,
			changed: false,
			updating: false,
			records: [],
		};
	},
	computed: {
		changedData() {
			let str = '';

			if (this.records) {
				for (let item of this.records)
					str += item.color + item.class + item.title + item.icon;
			}

			return str;
		},
		sorted() {
			return this.records.sort((a, b) => a.sort - b.sort);
		},
		total() {
			return this.records.length - 1;
		},
	},
	watch: {
		changedData() {
			if (!this.loading) this.changed = true;
		},
	},
	mounted() {
		AxiosHelper({
			url: window.location.href,
			data: {
				section: this.section,
			},
		}).then((resp) => {
			this.records = resp.records;
			this.addEmpty();

			this.$nextTick(() => (this.loading = false));
		});
	},
	methods: {
		addEmpty() {
			let empty = this.records.filter((item) => !item.title && !item.id);

			if (!empty.length) {
				let sort = this.records[this.records.length - 1].sort + 1;

				this.records.push({
					id: null,
					title: null,
					warning: null,
					color: null,
					icon: null,
					sort,
				});
			}
		},
		arrowDown(index) {
			let nextIndex = index + 1;

			this.$set(
				this.records[nextIndex],
				'sort',
				this.records[nextIndex].sort - 1
			);
			this.$set(
				this.records[index],
				'sort',
				this.records[index].sort + 1
			);
		},
		arrowUp(index) {
			let prevIndex = index - 1;

			this.$set(
				this.records[prevIndex],
				'sort',
				this.records[prevIndex].sort + 1
			);
			this.$set(
				this.records[index],
				'sort',
				this.records[index].sort - 1
			);
		},
		removeItem(i) {
			if (!this.records[i].id) {
				this.$delete(this.records, i);
				return;
			}

			Swal.fire({
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
					this.$delete(this.records, i);
					this.submit();
				}
			});
		},
		submit() {
			this.updating = true;
			let sort = 1;

			let records = this.sorted
				.filter((item) => item.id || item.title)
				.sort((a, b) => a.sort - b.sort)
				.map((item) => {
					// reset sort
					item.sort = sort++;

					return item;
				});

			AxiosHelper({
				url: window.location.href + '/save',
				data: {
					section: this.section,
					records,
				},
			})
				.then((resp) => {
					App.Forms.showAlert('success', resp.msg);
					this.changed = false;
				})
				.finally(() => (this.updating = false));
		},
	},
};
</script>
