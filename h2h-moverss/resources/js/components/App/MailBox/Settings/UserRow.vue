<template>
	<tr :style="{ opacity: record.active ? 1 : '0.5' }">
		<th scope="row" v-text="record.id"></th>
		<th scope="row" v-text="record.division_title"></th>
		<td>
			<div class="d-inline-flex">
				<div class="ml-0 mr-3 mx-lg-3">
					<img
						:src="`https://www.gravatar.com/avatar/${record.md5}?d=identicon`"
						alt=""
						class="profile-image profile-image-md rounded-circle"
					/>
				</div>
				<div class="mt-2">
					<span v-text="record.email"></span>

					<span v-if="record.active" class="badge badge-success"
						>Active</span
					>
					<span v-else class="badge badge-warning">Disabled</span>
				</div>
			</div>
		</td>
		<td>
			<div v-if="!record.is_holder && !is_admin">
				Shared access for you
			</div>
			<div v-else>
				<a @click.prevent="toggle()" href="#"
					>Access granted to {{ record.users.length }} user/s</a
				>

				<div v-if="is_open" class="mt-2">
					<div
						class="custom-control custom-checkbox"
						v-for="v in usersInDivision"
						:key="v.id"
					>
						<input
							type="checkbox"
							class="custom-control-input"
							:value="v.id"
							:id="'users_' + index + v.id"
							v-model="record.users"
						/>
						<label
							class="custom-control-label"
							:for="'users_' + index + v.id"
						>
							{{ v.name }} <small>({{ v.email }})</small>
						</label>
					</div>
					<button
						:disabled="!is_changed"
						@click="updatePermissions"
						type="button"
						class="mt-2 btn btn-primary waves-effect waves-themed"
					>
						{{ loading ? 'Saving changes...' : 'Save changes' }}
					</button>
				</div>
			</div>
		</td>
		<td v-if="record.msg">
			<span class="text-danger">{{ record.msg }}</span>
			<a
				v-if="record.error_type === 'invalid_grant'"
				:href="'/mailbox/join?reconnect=' + record.id"
				class="btn btn-warning waves-effect waves-themed mr-4"
				>Reconnect</a
			>
		</td>
		<td v-else>No errors</td>
		<td>
			<div v-if="record.is_holder || is_admin">
				<button
					v-if="record.active"
					@click="toggleAccountStatus"
					type="button"
					class="btn btn-secondary waves-effect waves-themed"
					title="Pause Sync and Stop errors"
				>
					<span
						class="fal"
						:class="{
							'fa-pause': !loading,
							'fa-spinner fa-spin': loading,
						}"
					></span>
				</button>
				<button
					v-else
					@click="toggleAccountStatus"
					type="button"
					class="btn btn-primary waves-effect waves-themed"
					title="Start Sync"
				>
					<span
						class="fal"
						:class="{
							'fa-play': !loading,
							'fa-spinner fa-spin': loading,
						}"
					></span>
				</button>
			</div>
			<span v-else>&mdash;</span>
		</td>
	</tr>
</template>

<script>
export default {
	name: 'UserRow',
	props: {
		index: {
			type: Number,
			required: true,
		},
		is_admin: {
			type: Boolean,
			required: true,
		},
		record: {
			type: Object,
			required: true,
		},
		users: {
			type: Array,
			required: true,
		},
	},
	data() {
		return {
			is_open: false,
			is_changed: false,
			loading: false,
		};
	},
	computed: {
		usersInDivision() {
			return this.users
				.slice()
				.filter((item) =>
					item.division_ids?.includes(this.record.division_id)
				);
		},
	},
	watch: {
		record: {
			handler() {
				this.is_changed = true;
			},
			deep: true,
		},
	},
	methods: {
		toggle() {
			this.is_open = !this.is_open;
		},
		toggleAccountStatus() {
			this.loading = true;
			new Promise((resolve, reject) =>
				this.$emit('toggleAccountStatus', this.index, {
					resolve,
					reject,
				})
			).finally(() => (this.loading = false));
		},
		updatePermissions() {
			this.loading = true;
			new Promise((resolve, reject) =>
				this.$emit('updateAccountPermissions', this.index, {
					resolve,
					reject,
				})
			)
				.then(
					() => (this.is_changed = false),
					() => {}
				)
				.finally(() => (this.loading = false));
		},
	},
};
</script>
