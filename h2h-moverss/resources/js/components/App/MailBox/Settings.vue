<template>
	<div class="d-flex flex-column flex-grow-1 bg-white">
		<h4 class="mt-4 mb-4">Gmail Accounts</h4>

		<table class="table m-0">
			<thead>
				<tr>
					<th>#</th>
					<th>Branch</th>
					<th>Email</th>
					<th>Shared access</th>
					<th>Error MSG</th>
					<th>Functions</th>
				</tr>
			</thead>
			<tbody>
				<tr v-if="loading">
					<td colspan="4">Loading...</td>
				</tr>
				<template v-else>
					<user-row
						v-for="(v, i) in accounts"
						:key="v.id"
						:index="i"
						:record="v"
						:users="users"
						:is_admin="is_admin"
						@updateAccountPermissions="updateAccountPermissions"
						@toggleAccountStatus="toggleAccountStatus"
					></user-row>
				</template>
			</tbody>
		</table>
		<br />

		<div>
			<button
				@click="addAccount"
				type="button"
				class="btn btn-primary waves-effect waves-themed"
			>
				Add new account
			</button>
		</div>
	</div>
</template>

<script>
import { getUsers } from '@/api/crm';
import { AxiosHelper } from '@/helpers/axiosHelper';
import UserRow from '@components/App/MailBox/Settings/UserRow';

export default {
	name: 'Settings',
	components: { UserRow },
	data() {
		return {
			loading: true,
			accounts: [],
			users: [],
			is_admin: false,
		};
	},
	mounted() {
		Promise.all([this.initUsers(), this.initAccounts()]).then(
			() => (this.loading = false)
		);
	},
	methods: {
		addAccount() {
			if (this.loading) return;

			window.location.href = '/mailbox/join';
		},
		initAccounts() {
			return AxiosHelper({
				url: '/mailbox/accounts',
			}).then(({ records }) => {
				this.accounts = records.map((item) => {
					let users = item.users.map((v) => v.id);

					item.users = users;

					return item;
				});
			});
		},
		initUsers() {
			return getUsers().then(({ records, whoami }) => {
				this.users = records
					.filter((item) => item.active)
					.map((item) => {
						let roles = item.roles.map((v) => v.id);

						item.roles = roles;

						return item;
					})
					.filter(
						(item) =>
							item.roles.includes(1) || item.roles.includes(5)
					);

				if (whoami.is_admin) this.is_admin = true;
			});
		},
		updateAccountPermissions(index, promise = null) {
			let record = this.accounts[index];

			return AxiosHelper({
				url: '/mailbox/account-set-permissions',
				data: {
					account_id: record.id,
					users: record.users,
				},
			})
				.then(({ msg }) => {
					App.Forms.showAlert('success', msg);
					if (promise) promise.resolve();
				})
				.catch((error) => {
					if (promise) promise.reject(error.data);
				});
		},
		toggleAccountStatus(index, promise = null) {
			let record = this.accounts[index];

			return AxiosHelper({
				url: '/mailbox/account-status-toggle',
				data: {
					account_id: record.id,
				},
			})
				.then(({ msg }) => {
					this.$set(
						this.accounts[index],
						'active',
						!this.accounts[index].active
					);

					App.Forms.showAlert('success', msg);
					if (promise) promise.resolve();
				})
				.catch((error) => {
					if (promise) promise.reject(error.data);
				});
		},
	},
};
</script>
