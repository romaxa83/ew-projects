<template>
	<div>
		<div class="row">
			<div class="col">
				<div class="panel-content">
					<div v-if="loading" class="d-flex justify-content-center">
						<div class="spinner-border" role="status">
							<span class="sr-only">Loading...</span>
						</div>
					</div>
					<div v-else class="row">
						<div class="col-sm-12 col-lg-2">
							<div
								class="nav flex-column nav-pills division-pills"
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
									aria-selected="false"
								>
									<span class="ml-1" v-text="v.title"></span>
								</a>
							</div>
						</div>
						<div class="col-md-12 col-lg-10">
							<div class="tab-content" id="v-pills-tabContent">
								<divisions-item
									v-for="(v, i) in records"
									:key="v.id"
									:i="i"
									:record="v"
									:updating="updating"
									:clearing="clearing"
									@submit="submit"
									@clear-cache="clearCache"
									@paymentAddEmpty="formatPayments"
									@paymentRemoveItem="paymentRemoveItem"
									@paymentsArrowDown="paymentsArrowDown"
									@paymentsArrowUp="paymentsArrowUp"
								></divisions-item>

								<div
									class="tab-pane fade"
									id="v-pills-profile"
									role="tabpanel"
									aria-labelledby="v-pills-settings-tab"
								>
									<h3>Settings</h3>
									<div class="alert alert-success">
										<strong> Settings saved </strong>
										<p class="m-0">
											All your settings changes have been
											saved!
										</p>
									</div>

									<div class="panel-container show">
										<div class="panel-content">
											<div class="panel-tag">
												The most minimalistic approach
												to tabs. We add
												<code>.nav.nav-tabs</code> to an
												UL and
												<code>.tab-content</code> to
												adjacet element. Tabs are
												triggered by the data attribute
												<code>data-toggle="tab"</code>
												and
												<code>href="#tab_content"</code>
												you will link the tab you would
												like to display
											</div>
											<ul
												class="nav nav-tabs"
												role="tablist"
											>
												<li class="nav-item">
													<a
														class="nav-link active"
														data-toggle="tab"
														href="#tab_default-1"
														role="tab"
														>Home</a
													>
												</li>
												<li class="nav-item">
													<a
														class="nav-link"
														data-toggle="tab"
														href="#tab_default-3"
														role="tab"
														>Set</a
													>
												</li>
											</ul>
											<div class="tab-content p-3">
												<div
													class="tab-pane fade show active"
													id="tab_default-1"
													role="tabpanel"
												>
													Raw denim you probably
													haven't heard of them jean
													shorts Austin. Nesciunt tofu
													stumptown aliqua, retro
													synth master cleanse.
													Mustache cliche tempor,
													williamsburg carles vegan
													helvetica. Reprehenderit
													butcher retro keffiyeh
													dreamcatcher synth. Cosby
													sweater eu banh mi, qui
													irure terry richardson ex
													squid. Aliquip placeat
													salvia cillum iphone.
												</div>
												<div
													class="tab-pane fade"
													id="tab_default-3"
													role="tabpanel"
												>
													Etsy mixtape wayfarers,
													ethical wes anderson tofu
													before they sold out
													mcsweeney's organic lomo
													retro fanny pack lo-fi
													farm-to-table readymade.
													Messenger bag gentrify
													pitchfork tattooed craft
													beer, iphone skateboard
													locavore carles etsy salvia
													banksy hoodie helvetica. DIY
													synth PBR banksy irony.
													Leggings gentrify squid
													8-bit cred pitchfork.
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
import { AxiosHelper } from '@/helpers/axiosHelper';
import DivisionsItem from '@components/Settings/Divisions/Item';

export default {
	name: 'SettingsDivisions',
	components: { DivisionsItem },
	data() {
		return {
			loading: true,
			updating: false,
			clearing: false,
			records: [],
		};
	},
	mounted() {
		this.init();
	},
	methods: {
		clearCache() {
			this.clearing = true;
			AxiosHelper({
				url: '/settings/divisions/clear-divisions-cache',
			})
				.then(() => App.Forms.showAlert('success', 'Session updated'))
				.finally(() => (this.clearing = false));
		},
		async init() {
			let { records } = await AxiosHelper({
				url: window.location.href,
				method: 'get',
			});

			this.records = records.map(function (item) {
				if (!item.authorize) {
					item.authorize = {
						active: false,
						title: null,
						login: null,
						transactionKey: null,
						payment_account_id: null,
					};
				}

				return item;
			});
			this.loading = false;

			this.formatPayments();
		},
		formatPayments(main_index = null) {
			let addIfEmpty = function (records, division_id) {
				let sort = 0;
				records
					.sort((a, b) => a.sort - b.sort)
					.map((item) => {
						sort++;
						item.sort = sort;

						return item;
					});

				let empty = records.filter((item) => !item.title && !item.id);

				if (!empty.length) {
					sort++;
					records.push({
						id: null,
						is_active: 0,
						title: null,
						sort,
						division_id,
					});
				}
			};

			if (main_index) {
				addIfEmpty(
					this.records[main_index].payment_accounts,
					this.records[main_index].id
				);
			} else
				this.records.forEach(function (item) {
					addIfEmpty(item.payment_accounts, item.id);
				});
		},
		paymentRemoveItem({ index, i }) {
			this.$delete(this.records[index].payment_accounts, i);
		},
		async submit(index) {
			let record = this.records[index];
			this.updating = true;
			await AxiosHelper({
				url: window.location.href,
				data: {
					record,
				},
			});
			await this.init();

			App.Forms.showAlert('success', 'Updated');
			this.formatPayments();

			this.updating = false;
		},
		paymentsArrowDown({ index, main_index }) {
			let nextIndex = index + 1;

			this.$set(
				this.records[main_index].payment_accounts[nextIndex],
				'sort',
				this.records[main_index].payment_accounts[nextIndex].sort - 1
			);
			this.$set(
				this.records[main_index].payment_accounts[index],
				'sort',
				this.records[main_index].payment_accounts[index].sort + 1
			);
		},
		paymentsArrowUp({ index, main_index }) {
			let prevIndex = index - 1;

			this.$set(
				this.records[main_index].payment_accounts[prevIndex],
				'sort',
				this.records[main_index].payment_accounts[prevIndex].sort + 1
			);
			this.$set(
				this.records[main_index].payment_accounts[index],
				'sort',
				this.records[main_index].payment_accounts[index].sort - 1
			);
		},
	},
};
</script>

<style>
.nav-pills.division-pills .nav-link.active,
.nav-pills.division-pills .show > .nav-link {
	background-color: #727b84;
	color: #fff;
}

.nav-pills.division-pills .nav-link {
	color: #727b84;
}
</style>
